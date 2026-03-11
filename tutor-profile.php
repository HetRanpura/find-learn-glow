<?php
require_once 'db.php';
$tutor_id = (int)($_GET['id'] ?? 0);
if (!$tutor_id) { redirect('tutors.php'); }

$stmt = $conn->prepare("
    SELECT t.*, u.full_name, u.email, u.phone, u.city, u.profile_photo, u.created_at AS member_since
    FROM tutors t JOIN users u ON t.user_id = u.user_id
    WHERE t.tutor_id = ? AND t.verification_status = 'approved'
");
$stmt->bind_param('i', $tutor_id); $stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) { setFlash('error','Tutor not found.'); redirect('tutors.php'); }
$tutor = $result->fetch_assoc(); $stmt->close();

// Load courses
$cStmt = $conn->prepare("SELECT * FROM tutor_courses WHERE tutor_id=? ORDER BY created_at ASC");
$cStmt->bind_param('i', $tutor_id); $cStmt->execute();
$courses = $cStmt->get_result()->fetch_all(MYSQLI_ASSOC); $cStmt->close();

// Reviews
$rStmt = $conn->prepare("SELECT r.*,u.full_name AS reviewer_name FROM reviews r JOIN users u ON r.student_id=u.user_id WHERE r.tutor_id=? ORDER BY r.created_at DESC LIMIT 5");
$rStmt->bind_param('i', $tutor_id); $rStmt->execute();
$reviews = $rStmt->get_result(); $rStmt->close();

// Check if current student already applied
$alreadyApplied = false;
if (isset($_SESSION['user_id']) && $_SESSION['user_role'] === 'student') {
    $chk = $conn->prepare("SELECT application_id FROM applications WHERE student_id=? AND tutor_id=? AND status IN ('pending','accepted') LIMIT 1");
    $chk->bind_param('ii', $_SESSION['user_id'], $tutor_id); $chk->execute();
    $alreadyApplied = $chk->get_result()->num_rows > 0; $chk->close();
}

$pageTitle = htmlspecialchars($tutor['full_name']) . ' – FindLearnGlow';
include 'partials/header.php';
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

  <!-- Breadcrumb -->
  <div class="flex items-center gap-2 text-sm text-slate-500 mb-6">
    <a href="tutors.php" style="color:var(--cyan);">Tutors</a>
    <i data-lucide="chevron-right" style="width:14px;height:14px;"></i>
    <span style="color:white;"><?= htmlspecialchars($tutor['full_name']) ?></span>
  </div>

  <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

    <!-- LEFT -->
    <div class="lg:col-span-2 flex flex-col gap-6">

      <!-- Profile Card -->
      <div class="card p-6" style="background:linear-gradient(135deg,rgba(163,230,53,0.05) 0%,rgba(34,211,238,0.05) 100%);">
        <div class="flex flex-col sm:flex-row gap-5 items-start">
          <div style="width:96px;height:96px;border-radius:50%;overflow:hidden;border:3px solid rgba(163,230,53,0.4);flex-shrink:0;">
            <?php if($tutor['profile_photo']): ?>
              <img src="uploads/<?= htmlspecialchars($tutor['profile_photo']) ?>" alt="" style="width:100%;height:100%;object-fit:cover;">
            <?php else: ?>
              <div style="width:100%;height:100%;background:linear-gradient(135deg,var(--navy-600),var(--navy-700));display:flex;align-items:center;justify-content:center;">
                <span style="font-family:'Space Grotesk',sans-serif;font-weight:700;color:var(--lime);font-size:2rem;"><?= strtoupper(substr($tutor['full_name'],0,1)) ?></span>
              </div>
            <?php endif; ?>
          </div>
          <div class="flex-1">
            <div class="flex flex-wrap items-center gap-3 mb-1">
              <h1 style="font-size:1.5rem;font-weight:700;font-family:'Space Grotesk',sans-serif;"><?= htmlspecialchars($tutor['full_name']) ?></h1>
              <span class="badge-lime text-xs px-2.5 py-1 rounded-full">✓ Verified</span>
            </div>
            <p class="text-slate-400 text-sm mb-3"><?= htmlspecialchars($tutor['qualification']) ?></p>
            <div class="flex flex-wrap gap-4 text-sm mb-4">
              <div class="flex items-center gap-1.5"><span class="star">★</span><span style="font-weight:600;"><?= number_format($tutor['rating']??0,1) ?></span><span class="text-slate-500">(<?= $tutor['total_reviews']??0 ?> reviews)</span></div>
              <div class="flex items-center gap-1.5 text-slate-400"><i data-lucide="briefcase" style="width:14px;height:14px;color:var(--cyan);"></i><?= $tutor['experience_years']??0 ?> years exp.</div>
              <?php if($tutor['city']): ?><div class="flex items-center gap-1.5 text-slate-400"><i data-lucide="map-pin" style="width:14px;height:14px;color:var(--cyan);"></i><?= htmlspecialchars($tutor['city']) ?></div><?php endif; ?>
              <div class="flex items-center gap-1.5 text-slate-400"><i data-lucide="calendar" style="width:14px;height:14px;color:var(--cyan);"></i>Since <?= date('M Y',strtotime($tutor['member_since'])) ?></div>
            </div>
            <div class="flex flex-wrap gap-2">
              <?php foreach(explode(',',$tutor['subjects']) as $s): ?>
                <span class="badge-cyan text-sm px-3 py-1 rounded-full"><?= htmlspecialchars(trim($s)) ?></span>
              <?php endforeach; ?>
            </div>
          </div>
        </div>
      </div>

      <!-- About -->
      <?php if($tutor['bio']): ?>
      <div class="card p-6">
        <h2 style="font-size:1rem;font-weight:600;font-family:'Space Grotesk',sans-serif;color:var(--cyan);margin-bottom:0.875rem;">About</h2>
        <p class="text-slate-400 leading-relaxed text-sm"><?= nl2br(htmlspecialchars($tutor['bio'])) ?></p>
      </div>
      <?php endif; ?>

      <!-- Courses Offered -->
      <?php if (!empty($courses)): ?>
      <div class="card p-6">
        <h2 style="font-size:1rem;font-weight:600;font-family:'Space Grotesk',sans-serif;color:var(--lime);margin-bottom:1.25rem;">Courses Offered</h2>
        <div class="flex flex-col gap-5">
          <?php foreach($courses as $c): ?>
            <div style="border-bottom:1px solid rgba(255,255,255,0.06);padding-bottom:1.25rem;">
              <div class="flex flex-wrap items-center gap-2.5 mb-2">
                <h3 style="font-weight:700;font-family:'Space Grotesk',sans-serif;"><?= htmlspecialchars($c['subject_name']) ?></h3>
                <span style="background:rgba(163,230,53,0.1);border:1px solid rgba(163,230,53,0.25);color:var(--lime);font-size:0.75rem;font-weight:600;border-radius:999px;padding:2px 10px;">⏱ <?= htmlspecialchars($c['duration']) ?></span>
              </div>
              <div style="background:var(--navy-700);border-radius:0.5rem;padding:0.875rem;font-size:0.85rem;color:#94a3b8;line-height:1.65;white-space:pre-wrap;"><?= htmlspecialchars($c['syllabus']) ?></div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endif; ?>

      <!-- Reviews -->
      <div class="card p-6">
        <h2 style="font-size:1rem;font-weight:600;font-family:'Space Grotesk',sans-serif;color:var(--lime);margin-bottom:1.25rem;">Reviews (<?= $tutor['total_reviews']??0 ?>)</h2>
        <?php if($reviews->num_rows === 0): ?>
          <p class="text-slate-600 text-sm">No reviews yet.</p>
        <?php else: ?>
          <div class="flex flex-col gap-5">
            <?php while($rev = $reviews->fetch_assoc()): ?>
              <div style="padding-bottom:1.25rem;border-bottom:1px solid rgba(255,255,255,0.05);">
                <div class="flex items-center justify-between mb-2">
                  <div><span style="font-weight:600;font-family:'Space Grotesk',sans-serif;font-size:0.9rem;"><?= htmlspecialchars($rev['reviewer_name']) ?></span><span class="text-slate-600 text-xs ml-2"><?= date('d M Y',strtotime($rev['created_at'])) ?></span></div>
                  <div class="flex"><?php for($s=1;$s<=5;$s++): ?><span style="color:<?= $s<=$rev['rating']?'#fbbf24':'#334155' ?>;font-size:0.875rem;">★</span><?php endfor; ?></div>
                </div>
                <p class="text-slate-400 text-sm"><?= htmlspecialchars($rev['comment']) ?></p>
              </div>
            <?php endwhile; ?>
          </div>
        <?php endif; ?>
      </div>
    </div>

    <!-- SIDEBAR -->
    <div>
      <div style="position:sticky;top:80px;" class="flex flex-col gap-4">
        <!-- Rate card -->
        <div class="card p-6">
          <div class="text-center mb-5">
            <div style="font-size:2rem;font-weight:700;font-family:'Space Grotesk',sans-serif;color:var(--lime);">₹<?= number_format($tutor['hourly_rate']) ?></div>
            <div class="text-slate-500 text-sm">per hour</div>
          </div>

          <?php if ($alreadyApplied): ?>
            <!-- Already applied -->
            <div style="background:rgba(163,230,53,0.08);border:1.5px solid rgba(163,230,53,0.25);border-radius:0.875rem;padding:1rem;text-align:center;margin-bottom:0.75rem;">
              <i data-lucide="check-circle" style="width:28px;height:28px;color:var(--lime);margin:0 auto 0.5rem;display:block;"></i>
              <p style="color:var(--lime);font-weight:600;font-size:0.875rem;">Application Sent!</p>
              <p class="text-slate-500 text-xs mt-0.5">Waiting for tutor to schedule your session.</p>
            </div>
            <a href="dashboard.php?tab=applications" class="btn-cyan w-full py-3 rounded-xl text-sm flex items-center justify-center gap-2">
              <i data-lucide="layout-dashboard" style="width:16px;height:16px;"></i> View in Dashboard
            </a>

          <?php elseif (isset($_SESSION['user_id']) && $_SESSION['user_role'] === 'tutor'): ?>
            <!-- Tutor can't apply -->
            <p class="text-slate-500 text-sm text-center py-2">Tutors cannot apply to other tutors.</p>

          <?php else: ?>
            <!-- Apply button -->
            <a href="<?= isset($_SESSION['user_id']) ? 'apply.php?tutor_id='.$tutor_id : 'login.php' ?>"
               class="btn-lime w-full py-3.5 rounded-xl text-base flex items-center justify-center gap-2 glow-pulse mb-3">
              <i data-lucide="send" style="width:18px;height:18px;"></i>
              Apply for a Session
            </a>
            <?php if (!isset($_SESSION['user_id'])): ?>
              <p class="text-slate-600 text-xs text-center">You need to <a href="login.php" style="color:var(--cyan);">log in</a> first.</p>
            <?php endif; ?>
          <?php endif; ?>

          <div style="height:1px;background:rgba(255,255,255,0.06);margin:1.25rem 0;"></div>

          <!-- How it works mini -->
          <p style="color:var(--cyan);font-size:0.75rem;font-weight:600;letter-spacing:0.06em;text-transform:uppercase;margin-bottom:0.875rem;">How It Works</p>
          <div class="flex flex-col gap-2.5 text-sm">
            <?php foreach([
              ['1','Send your application','send'],
              ['2','Tutor reviews & accepts','check'],
              ['3','Tutor schedules your batch','calendar'],
              ['4','You get session details','bell'],
            ] as $step): ?>
              <div class="flex items-center gap-2.5">
                <span style="width:20px;height:20px;border-radius:50%;background:rgba(163,230,53,0.15);color:var(--lime);font-size:0.65rem;font-weight:700;display:flex;align-items:center;justify-content:center;flex-shrink:0;"><?= $step[0] ?></span>
                <span class="text-slate-400 text-xs"><?= $step[1] ?></span>
              </div>
            <?php endforeach; ?>
          </div>
        </div>

        <!-- Trust markers -->
        <div class="flex flex-col gap-2">
          <?php foreach([['shield-check','Verified Tutor','Background checked'],['book-open','Structured Courses','Syllabus & duration listed'],['headphones','24/7 Support','We\'re here']] as $b): ?>
            <div class="flex items-center gap-2 text-xs text-slate-500">
              <i data-lucide="<?= $b[0] ?>" style="width:14px;height:14px;color:var(--cyan);flex-shrink:0;"></i>
              <span><strong style="color:#94a3b8;"><?= $b[1] ?></strong> — <?= $b[2] ?></span>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>

  </div>
</div>

<?php include 'partials/footer.php'; ?>