<?php
require_once 'db.php';
$pageTitle = 'Find Tutors – FindLearnGlow';

$subject  = sanitize($conn, $_GET['subject']  ?? '');
$city     = sanitize($conn, $_GET['city']     ?? '');
$max_rate = (int)($_GET['max_rate'] ?? 0);
$sort     = in_array($_GET['sort'] ?? '', ['rating','rate_asc','rate_desc','newest']) ? $_GET['sort'] : 'rating';

$where  = ["t.verification_status = 'approved'"];
$params = []; $types = '';

if ($subject) { $where[] = "t.subjects LIKE ?"; $params[] = '%'.$subject.'%'; $types .= 's'; }
if ($city)    { $where[] = "u.city LIKE ?";      $params[] = '%'.$city.'%';    $types .= 's'; }
if ($max_rate > 0) { $where[] = "t.hourly_rate <= ?"; $params[] = $max_rate;  $types .= 'i'; }

$orderBy = match($sort) {
    'rate_asc'  => 'ORDER BY t.hourly_rate ASC',
    'rate_desc' => 'ORDER BY t.hourly_rate DESC',
    'newest'    => 'ORDER BY t.created_at DESC',
    default     => 'ORDER BY t.rating DESC, t.total_reviews DESC',
};

$sql = "SELECT t.tutor_id, u.full_name, t.subjects, t.hourly_rate, t.rating, t.total_reviews,
               t.experience_years, t.qualification, t.bio, u.city, u.profile_photo
        FROM tutors t JOIN users u ON t.user_id = u.user_id
        WHERE " . implode(' AND ', $where) . " $orderBy";

$stmt = $conn->prepare($sql);
if ($types && $params) $stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

include 'partials/header.php';
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
  <div class="mb-8">
    <h1 style="font-size:1.75rem;font-weight:700;margin-bottom:0.3rem;">Find Your Tutor</h1>
    <p class="text-slate-400 text-sm">All tutors are verified. Apply to book a session.</p>
  </div>

  <!-- Filters -->
  <form method="GET" action="tutors.php" style="background:var(--navy-800);border:1px solid rgba(255,255,255,0.07);border-radius:1rem;padding:1.25rem 1.5rem;margin-bottom:2rem;">
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 items-end">
      <div><label class="block text-slate-500 text-xs mb-1.5 uppercase tracking-wider">Subject</label><input type="text" name="subject" class="form-input" placeholder="e.g. Physics" value="<?= htmlspecialchars($subject) ?>"></div>
      <div><label class="block text-slate-500 text-xs mb-1.5 uppercase tracking-wider">City</label><input type="text" name="city" class="form-input" placeholder="e.g. Delhi" value="<?= htmlspecialchars($city) ?>"></div>
      <div><label class="block text-slate-500 text-xs mb-1.5 uppercase tracking-wider">Max Rate (₹/hr)</label><input type="number" name="max_rate" class="form-input" placeholder="1000" min="0" value="<?= $max_rate?:'' ?>"></div>
      <div><label class="block text-slate-500 text-xs mb-1.5 uppercase tracking-wider">Sort By</label>
        <select name="sort" class="form-input">
          <option value="rating"   <?= $sort==='rating'   ?'selected':''?>>Top Rated</option>
          <option value="rate_asc" <?= $sort==='rate_asc' ?'selected':''?>>Price: Low→High</option>
          <option value="rate_desc"<?= $sort==='rate_desc'?'selected':''?>>Price: High→Low</option>
          <option value="newest"   <?= $sort==='newest'   ?'selected':''?>>Newest</option>
        </select>
      </div>
    </div>
    <div class="flex gap-3 mt-4">
      <button type="submit" class="btn-lime px-6 py-2.5 rounded-lg text-sm flex items-center gap-2"><i data-lucide="search" style="width:15px;height:15px;"></i> Apply Filters</button>
      <a href="tutors.php" class="btn-cyan px-5 py-2.5 rounded-lg text-sm">Clear</a>
    </div>
  </form>

  <!-- Results -->
  <?php if ($result->num_rows === 0): ?>
    <div class="text-center py-16">
      <i data-lucide="search-x" style="width:56px;height:56px;color:#334155;margin:0 auto 1rem;display:block;"></i>
      <h3 style="font-size:1.1rem;font-weight:600;color:#475569;">No tutors found</h3>
      <p class="text-slate-600 text-sm mt-1"><a href="tutors.php" style="color:var(--cyan);">Clear filters</a> to see all tutors.</p>
    </div>
  <?php else: ?>
    <div class="mb-4 text-slate-500 text-sm"><?= $result->num_rows ?> tutor<?= $result->num_rows!==1?'s':''?> found</div>
    <div class="flex flex-col gap-4">
      <?php while ($tutor = $result->fetch_assoc()): ?>
        <div class="card p-5" style="display:grid;grid-template-columns:auto 1fr auto;gap:1.25rem;align-items:start;">
          <!-- Avatar -->
          <div style="width:72px;height:72px;border-radius:50%;overflow:hidden;border:2px solid rgba(163,230,53,0.3);flex-shrink:0;">
            <?php if($tutor['profile_photo']): ?>
              <img src="uploads/<?= htmlspecialchars($tutor['profile_photo']) ?>" alt="" style="width:100%;height:100%;object-fit:cover;">
            <?php else: ?>
              <div style="width:100%;height:100%;background:linear-gradient(135deg,#1a2744,#111d35);display:flex;align-items:center;justify-content:center;">
                <span style="font-family:'Space Grotesk',sans-serif;font-weight:700;color:var(--lime);font-size:1.5rem;"><?= strtoupper(substr($tutor['full_name'],0,1)) ?></span>
              </div>
            <?php endif; ?>
          </div>

          <!-- Details -->
          <div>
            <div class="flex flex-wrap items-center gap-2 mb-1">
              <h3 style="font-weight:700;font-size:1.05rem;font-family:'Space Grotesk',sans-serif;"><?= htmlspecialchars($tutor['full_name']) ?></h3>
              <span class="badge-lime text-xs px-2 py-0.5 rounded-full">✓ Verified</span>
            </div>
            <p class="text-slate-400 text-sm mb-2"><?= htmlspecialchars($tutor['qualification']) ?></p>
            <div class="flex flex-wrap gap-1.5 mb-2">
              <?php foreach(explode(',',$tutor['subjects']) as $s): ?>
                <span class="badge-cyan text-xs px-2 py-0.5 rounded-full"><?= htmlspecialchars(trim($s)) ?></span>
              <?php endforeach; ?>
            </div>
            <?php if($tutor['bio']): ?>
              <p class="text-slate-500 text-sm" style="overflow:hidden;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;"><?= htmlspecialchars($tutor['bio']) ?></p>
            <?php endif; ?>
            <div class="flex flex-wrap gap-4 mt-2.5 text-xs text-slate-500">
              <span class="flex items-center gap-1"><span class="star">★</span><strong style="color:#e2e8f0;"><?= number_format($tutor['rating']??0,1) ?></strong>(<?= $tutor['total_reviews']??0 ?>)</span>
              <span class="flex items-center gap-1"><i data-lucide="briefcase" style="width:12px;height:12px;color:var(--cyan);"></i><?= $tutor['experience_years']??0 ?> yrs</span>
              <?php if($tutor['city']): ?><span class="flex items-center gap-1"><i data-lucide="map-pin" style="width:12px;height:12px;color:var(--cyan);"></i><?= htmlspecialchars($tutor['city']) ?></span><?php endif; ?>
            </div>
          </div>

          <!-- Price + CTA -->
          <div class="text-right flex flex-col items-end gap-3">
            <div>
              <div style="color:var(--lime);font-weight:700;font-size:1.3rem;font-family:'Space Grotesk',sans-serif;">₹<?= number_format($tutor['hourly_rate']) ?></div>
              <div class="text-slate-500 text-xs">per hour</div>
            </div>
            <a href="tutor-profile.php?id=<?= $tutor['tutor_id'] ?>" class="btn-cyan px-5 py-2 rounded-lg text-sm">View Profile</a>
            <a href="<?= isset($_SESSION['user_id']) ? 'apply.php?tutor_id='.$tutor['tutor_id'] : 'login.php' ?>"
               class="btn-lime px-5 py-2 rounded-lg text-sm flex items-center gap-1.5">
              <i data-lucide="send" style="width:13px;height:13px;"></i> Apply
            </a>
          </div>
        </div>
      <?php endwhile; ?>
    </div>
  <?php endif; ?>
</div>

<?php include 'partials/footer.php'; ?>