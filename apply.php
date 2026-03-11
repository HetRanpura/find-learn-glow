<?php
require_once 'db.php';
requireLogin();

// Only students can apply
if ($_SESSION['user_role'] === 'tutor') {
    setFlash('error', 'Tutors cannot apply to other tutors.');
    redirect('tutors.php');
}

$pageTitle = 'Apply to Tutor – FindLearnGlow';

// Load tutor
$tutor_id = (int)($_GET['tutor_id'] ?? 0);
if (!$tutor_id) { setFlash('error', 'No tutor selected.'); redirect('tutors.php'); }

$tStmt = $conn->prepare("
    SELECT t.tutor_id, t.subjects, t.hourly_rate, t.rating, t.total_reviews,
           t.qualification, t.experience_years,
           u.full_name, u.city, u.profile_photo
    FROM tutors t
    JOIN users u ON t.user_id = u.user_id
    WHERE t.tutor_id = ? AND t.verification_status = 'approved'
");
$tStmt->bind_param('i', $tutor_id);
$tStmt->execute();
$tRes = $tStmt->get_result();
if ($tRes->num_rows === 0) { setFlash('error', 'Tutor not found.'); redirect('tutors.php'); }
$tutor = $tRes->fetch_assoc();
$tStmt->close();

// Load tutor's detailed courses (if any)
$cStmt = $conn->prepare("SELECT * FROM tutor_courses WHERE tutor_id = ? ORDER BY created_at ASC");
$cStmt->bind_param('i', $tutor_id);
$cStmt->execute();
$courses = $cStmt->get_result()->fetch_all(MYSQLI_ASSOC);
$cStmt->close();

// Check if already applied (pending/accepted)
$dupStmt = $conn->prepare("
    SELECT application_id, status FROM applications
    WHERE student_id = ? AND tutor_id = ? AND status IN ('pending','accepted')
    LIMIT 1
");
$sid = $_SESSION['user_id'];
$dupStmt->bind_param('ii', $sid, $tutor_id);
$dupStmt->execute();
$existing = $dupStmt->get_result()->fetch_assoc();
$dupStmt->close();

$success = false;
$errors  = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$existing) {

    $subject = sanitize($conn, $_POST['subject'] ?? '');
    $message = sanitize($conn, $_POST['message'] ?? '');

    if (empty($subject)) $errors[] = 'Please select or enter a subject.';

    if (empty($errors)) {
        $insStmt = $conn->prepare("
            INSERT INTO applications (student_id, tutor_id, subject, message)
            VALUES (?, ?, ?, ?)
        ");
        // i i s s  = 4 params ✓
        $insStmt->bind_param('iiss', $sid, $tutor_id, $subject, $message);
        if ($insStmt->execute()) {
            $success = true;
        } else {
            $errors[] = 'Could not submit application: ' . $insStmt->error;
        }
        $insStmt->close();
    }
}

include 'partials/header.php';
?>

<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

  <!-- Breadcrumb -->
  <div class="flex items-center gap-2 text-sm text-slate-500 mb-6">
    <a href="tutors.php" style="color:var(--cyan);">Find Tutors</a>
    <i data-lucide="chevron-right" style="width:14px;height:14px;"></i>
    <a href="tutor-profile.php?id=<?= $tutor_id ?>" style="color:var(--cyan);"><?= htmlspecialchars($tutor['full_name']) ?></a>
    <i data-lucide="chevron-right" style="width:14px;height:14px;"></i>
    <span style="color:white;">Apply</span>
  </div>

  <?php if ($success): ?>
    <!-- ─── SUCCESS STATE ─── -->
    <div style="text-align:center; padding: 60px 20px;">
      <div style="width:80px;height:80px;margin:0 auto 1.5rem;border-radius:50%;background:rgba(163,230,53,0.12);border:2px solid rgba(163,230,53,0.4);display:flex;align-items:center;justify-content:center;">
        <i data-lucide="check-circle" style="width:42px;height:42px;color:var(--lime);"></i>
      </div>
      <h1 style="font-size:1.75rem;font-weight:700;margin-bottom:0.75rem;font-family:'Space Grotesk',sans-serif;">
        Application Sent Successfully! 🎉
      </h1>
      <p class="text-slate-400 max-w-md mx-auto mb-4" style="line-height:1.7;">
        Your application has been sent to <strong style="color:white;"><?= htmlspecialchars($tutor['full_name']) ?></strong>.
        You will be notified on your dashboard once the tutor schedules your session.
      </p>
      <div style="background:rgba(34,211,238,0.08);border:1px solid rgba(34,211,238,0.2);border-radius:0.875rem;padding:1.25rem 1.5rem;max-width:420px;margin:0 auto 2rem;text-align:left;">
        <p style="color:var(--cyan);font-weight:600;font-size:0.875rem;margin-bottom:0.5rem;">What happens next?</p>
        <ul class="text-slate-400 text-sm space-y-1.5">
          <li class="flex items-start gap-2"><i data-lucide="arrow-right" style="width:13px;height:13px;flex-shrink:0;margin-top:3px;color:var(--lime);"></i> The tutor reviews your application</li>
          <li class="flex items-start gap-2"><i data-lucide="arrow-right" style="width:13px;height:13px;flex-shrink:0;margin-top:3px;color:var(--lime);"></i> They accept and create a batch schedule for you</li>
          <li class="flex items-start gap-2"><i data-lucide="arrow-right" style="width:13px;height:13px;flex-shrink:0;margin-top:3px;color:var(--lime);"></i> You'll see your session date &amp; time in your dashboard</li>
        </ul>
      </div>
      <div class="flex flex-wrap justify-center gap-3">
        <a href="dashboard.php" class="btn-lime px-6 py-3 rounded-lg">Go to My Dashboard</a>
        <a href="tutors.php" class="btn-cyan px-6 py-3 rounded-lg">Browse More Tutors</a>
      </div>
    </div>

  <?php elseif ($existing): ?>
    <!-- ─── ALREADY APPLIED ─── -->
    <div style="text-align:center;padding:60px 20px;">
      <div style="width:72px;height:72px;margin:0 auto 1.25rem;border-radius:50%;background:rgba(251,191,36,0.1);border:2px solid rgba(251,191,36,0.35);display:flex;align-items:center;justify-content:center;">
        <i data-lucide="clock" style="width:36px;height:36px;color:#fbbf24;"></i>
      </div>
      <h2 style="font-size:1.4rem;font-weight:700;margin-bottom:0.5rem;">You've Already Applied</h2>
      <p class="text-slate-400 mb-2">
        Your application to <strong style="color:white;"><?= htmlspecialchars($tutor['full_name']) ?></strong> is
        <span class="badge-<?= $existing['status']==='accepted' ? 'lime' : 'pending' ?> text-sm px-2.5 py-0.5 rounded-full"><?= ucfirst($existing['status']) ?></span>
      </p>
      <p class="text-slate-500 text-sm mb-6">Check your dashboard to see the latest status.</p>
      <a href="dashboard.php" class="btn-lime px-6 py-2.5 rounded-lg">View Dashboard</a>
    </div>

  <?php else: ?>
    <!-- ─── APPLY FORM ─── -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

      <!-- Form -->
      <div class="lg:col-span-2">
        <h1 style="font-size:1.6rem;font-weight:700;margin-bottom:0.25rem;">Apply to This Tutor</h1>
        <p class="text-slate-400 text-sm mb-6">Send your application to <strong style="color:white;"><?= htmlspecialchars($tutor['full_name']) ?></strong>. They'll review and schedule your session.</p>

        <?php if (!empty($errors)): ?>
          <div style="background:rgba(239,68,68,0.1);border:1px solid rgba(239,68,68,0.3);border-radius:0.75rem;padding:1rem 1.25rem;margin-bottom:1.5rem;">
            <?php foreach($errors as $e): ?>
              <p style="color:#fca5a5;font-size:0.875rem;">• <?= htmlspecialchars($e) ?></p>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>

        <form method="POST" action="apply.php?tutor_id=<?= $tutor_id ?>" class="card p-6 flex flex-col gap-5">

          <!-- Subject -->
          <div>
            <label class="block text-slate-400 text-sm mb-1.5">Subject / Course You Want to Learn *</label>
            <?php if (!empty($courses)): ?>
              <select name="subject" class="form-input" required>
                <option value="">-- Select a course --</option>
                <?php foreach($courses as $c): ?>
                  <option value="<?= htmlspecialchars($c['subject_name']) ?>"
                    <?= ($_POST['subject']??'')===$c['subject_name']?'selected':'' ?>>
                    <?= htmlspecialchars($c['subject_name']) ?> (<?= htmlspecialchars($c['duration']) ?>)
                  </option>
                <?php endforeach; ?>
                <?php foreach(explode(',',$tutor['subjects']) as $s):
                  $s=trim($s);
                  $alreadyListed = false;
                  foreach($courses as $c) { if($c['subject_name']===$s) { $alreadyListed=true; break; } }
                  if (!$alreadyListed): ?>
                  <option value="<?= htmlspecialchars($s) ?>" <?= ($_POST['subject']??'')===$s?'selected':'' ?>><?= htmlspecialchars($s) ?></option>
                <?php endif; endforeach; ?>
              </select>
            <?php else: ?>
              <select name="subject" class="form-input" required>
                <option value="">-- Select a subject --</option>
                <?php foreach(explode(',',$tutor['subjects']) as $s): $s=trim($s); ?>
                  <option value="<?= htmlspecialchars($s) ?>" <?= ($_POST['subject']??'')===$s?'selected':'' ?>><?= htmlspecialchars($s) ?></option>
                <?php endforeach; ?>
              </select>
            <?php endif; ?>
          </div>

          <!-- Message -->
          <div>
            <label class="block text-slate-400 text-sm mb-1.5">Message to Tutor (optional)</label>
            <textarea name="message" class="form-input" rows="4"
              placeholder="Tell the tutor about your current level, goals, preferred days/times, or any specific topics you want to focus on…"><?= htmlspecialchars($_POST['message']??'') ?></textarea>
          </div>

          <!-- Course Detail Preview (if courses exist) -->
          <?php if (!empty($courses)): ?>
            <div id="coursePreview" style="display:none;background:rgba(34,211,238,0.06);border:1px solid rgba(34,211,238,0.2);border-radius:0.75rem;padding:1rem 1.25rem;">
              <p style="color:var(--cyan);font-weight:600;font-size:0.8rem;letter-spacing:0.06em;text-transform:uppercase;margin-bottom:0.6rem;">Course Details</p>
              <p id="previewSyllabus" class="text-slate-400 text-sm leading-relaxed mb-2"></p>
              <p id="previewDuration" class="text-xs" style="color:var(--lime);"></p>
            </div>
          <?php endif; ?>

          <button type="submit" class="btn-lime w-full py-3.5 rounded-xl text-base flex items-center justify-center gap-2 glow-pulse">
            <i data-lucide="send" style="width:18px;height:18px;"></i>
            Send Application
          </button>

          <p class="text-slate-600 text-xs text-center">
            The tutor will review your application and schedule a session for you.
          </p>
        </form>
      </div>

      <!-- Tutor Sidebar -->
      <div>
        <div style="position:sticky;top:80px;" class="flex flex-col gap-4">

          <!-- Tutor Card -->
          <div class="card p-5">
            <div class="flex items-center gap-3 mb-4">
              <div style="width:56px;height:56px;border-radius:50%;overflow:hidden;border:2px solid rgba(163,230,53,0.3);flex-shrink:0;">
                <?php if($tutor['profile_photo']): ?>
                  <img src="uploads/<?= htmlspecialchars($tutor['profile_photo']) ?>" alt="" style="width:100%;height:100%;object-fit:cover;">
                <?php else: ?>
                  <div style="width:100%;height:100%;background:linear-gradient(135deg,#1a2744,#111d35);display:flex;align-items:center;justify-content:center;">
                    <span style="font-family:'Space Grotesk',sans-serif;font-weight:700;color:var(--lime);font-size:1.2rem;"><?= strtoupper(substr($tutor['full_name'],0,1)) ?></span>
                  </div>
                <?php endif; ?>
              </div>
              <div>
                <p style="font-weight:700;font-family:'Space Grotesk',sans-serif;"><?= htmlspecialchars($tutor['full_name']) ?></p>
                <p class="text-slate-500 text-xs"><?= htmlspecialchars($tutor['qualification']) ?></p>
                <div class="flex items-center gap-1 mt-0.5">
                  <span class="star" style="font-size:0.8rem;">★</span>
                  <span style="font-size:0.85rem;font-weight:600;"><?= number_format($tutor['rating']??0,1) ?></span>
                  <span class="text-slate-600 text-xs">(<?= $tutor['total_reviews']??0 ?>)</span>
                </div>
              </div>
            </div>
            <div class="flex flex-wrap gap-1.5">
              <?php foreach(explode(',',$tutor['subjects']) as $s): ?>
                <span class="badge-cyan text-xs px-2 py-0.5 rounded-full"><?= htmlspecialchars(trim($s)) ?></span>
              <?php endforeach; ?>
            </div>
          </div>

          <!-- Courses offered -->
          <?php if (!empty($courses)): ?>
            <div class="card p-5">
              <p style="color:var(--lime);font-size:0.8rem;font-weight:600;letter-spacing:0.07em;text-transform:uppercase;margin-bottom:0.875rem;">Courses Offered</p>
              <div class="flex flex-col gap-3">
                <?php foreach($courses as $c): ?>
                  <div style="border-bottom:1px solid rgba(255,255,255,0.06);padding-bottom:0.75rem;">
                    <p style="font-weight:600;font-size:0.875rem;font-family:'Space Grotesk',sans-serif;"><?= htmlspecialchars($c['subject_name']) ?></p>
                    <p class="text-slate-500 text-xs mt-0.5 mb-1"><?= htmlspecialchars(substr($c['syllabus'],0,80)) ?>…</p>
                    <span style="color:var(--lime);font-size:0.75rem;font-weight:600;">⏱ <?= htmlspecialchars($c['duration']) ?></span>
                  </div>
                <?php endforeach; ?>
              </div>
            </div>
          <?php endif; ?>

        </div>
      </div>
    </div>
  <?php endif; ?>
</div>

<script>
<?php if (!empty($courses)): ?>
// Course preview on subject select
const courseData = <?= json_encode(array_column($courses, null, 'subject_name')) ?>;
const selectEl = document.querySelector('select[name="subject"]');
if (selectEl) {
    selectEl.addEventListener('change', function() {
        const preview = document.getElementById('coursePreview');
        const data = courseData[this.value];
        if (data && preview) {
            document.getElementById('previewSyllabus').textContent = data.syllabus;
            document.getElementById('previewDuration').textContent = '⏱ Duration: ' + data.duration;
            preview.style.display = 'block';
        } else if (preview) {
            preview.style.display = 'none';
        }
    });
}
<?php endif; ?>
lucide.createIcons();
</script>

<?php include 'partials/footer.php'; ?>
