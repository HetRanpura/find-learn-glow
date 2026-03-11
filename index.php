<?php
require_once 'db.php';
$pageTitle = 'FindLearnGlow – Expert Home Tutors Near You';

// Fetch featured tutors (approved, ordered by rating)
$featuredQuery = "
    SELECT t.tutor_id, u.full_name, t.subjects, t.hourly_rate, t.rating, t.total_reviews, u.city, u.profile_photo
    FROM tutors t
    JOIN users u ON t.user_id = u.user_id
    WHERE t.verification_status = 'approved'
    ORDER BY t.rating DESC, t.total_reviews DESC
    LIMIT 6
";
$featuredResult = $conn->query($featuredQuery);

// Stats
$statsQuery = "SELECT
    (SELECT COUNT(*) FROM tutors WHERE verification_status='approved') AS tutors,
    (SELECT COUNT(*) FROM users WHERE role='student')                  AS students,
    (SELECT COUNT(*) FROM bookings WHERE status IN ('completed','confirmed')) AS sessions
";
$stats = $conn->query($statsQuery)->fetch_assoc();

include 'partials/header.php';
?>

<!-- ═══════════════════════════════════════════
     HERO
═══════════════════════════════════════════ -->
<section class="mesh-bg relative overflow-hidden" style="padding: 96px 0 80px;">
  <!-- Decorative orbs -->
  <div style="position:absolute; top:-80px; left:-80px; width:350px; height:350px; background:radial-gradient(circle, rgba(163,230,53,0.08) 0%, transparent 70%); pointer-events:none;"></div>
  <div style="position:absolute; bottom:-60px; right:-60px; width:280px; height:280px; background:radial-gradient(circle, rgba(34,211,238,0.07) 0%, transparent 70%); pointer-events:none;"></div>

  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center relative z-10">

    <!-- Badge -->
    <div class="inline-flex items-center gap-2 rounded-full px-4 py-1.5 mb-6 fade-up" style="background:rgba(163,230,53,0.1); border:1px solid rgba(163,230,53,0.25);">
      <span style="width:6px;height:6px;border-radius:50%;background:var(--lime);display:inline-block;animation:glowPulse 2s infinite;"></span>
      <span style="color:var(--lime); font-size:0.8rem; font-weight:600; letter-spacing:0.06em;">VERIFIED TUTORS ONLY</span>
    </div>

    <h1 class="fade-up-2" style="font-size: clamp(2.4rem, 5vw, 4rem); font-weight:700; line-height:1.1; margin-bottom:1.25rem;">
      Find Your Perfect<br>
      <span class="gradient-text">Home Tutor</span> Today
    </h1>

    <p class="fade-up-3 text-slate-400 max-w-xl mx-auto mb-10" style="font-size:1.05rem; line-height:1.65;">
      Browse expert tutors across every subject. Book sessions, track progress, and pay securely — all in one place.
    </p>

    <!-- Search Bar -->
    <form action="tutors.php" method="GET" class="fade-up-3 max-w-2xl mx-auto">
      <div class="flex gap-3" style="background:var(--navy-800); border:1px solid rgba(255,255,255,0.08); border-radius:0.875rem; padding:6px;">
        <div class="flex items-center gap-2 px-3 flex-1">
          <i data-lucide="search" style="color:var(--cyan); width:18px; height:18px; flex-shrink:0;"></i>
          <input type="text" name="subject" placeholder="Subject (e.g. Mathematics, Physics…)"
            class="form-input" style="background:transparent; border:none; padding:0.5rem 0; box-shadow:none;" />
        </div>
        <div class="flex items-center gap-2 px-3" style="border-left:1px solid rgba(255,255,255,0.08);">
          <i data-lucide="map-pin" style="color:var(--cyan); width:18px; height:18px; flex-shrink:0;"></i>
          <input type="text" name="city" placeholder="City"
            class="form-input" style="background:transparent; border:none; padding:0.5rem 0; box-shadow:none; width:130px;" />
        </div>
        <button type="submit" class="btn-lime px-6 py-3 rounded-lg text-sm flex-shrink-0">Search</button>
      </div>
    </form>

    <!-- Quick subject tags -->
    <div class="flex flex-wrap justify-center gap-2 mt-5 fade-up-3">
      <?php foreach(['Mathematics','Physics','Chemistry','English','Biology','Computer Science','Economics'] as $s): ?>
        <a href="tutors.php?subject=<?= urlencode($s) ?>" class="text-xs px-3 py-1 rounded-full text-slate-400 hover:text-white transition-colors"
           style="background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.08);"><?= $s ?></a>
      <?php endforeach; ?>
    </div>

  </div>
</section>

<!-- ═══════════════════════════════════════════
     STATS STRIP
═══════════════════════════════════════════ -->
<section style="background: var(--navy-800); border-top:1px solid rgba(255,255,255,0.05); border-bottom:1px solid rgba(255,255,255,0.05);">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="grid grid-cols-3 gap-6 text-center">
      <div>
        <div class="gradient-text font-bold" style="font-size:2rem; font-family:'Space Grotesk',sans-serif;"><?= number_format($stats['tutors'] ?? 240) ?>+</div>
        <div class="text-slate-500 text-sm mt-1">Verified Tutors</div>
      </div>
      <div>
        <div class="gradient-text font-bold" style="font-size:2rem; font-family:'Space Grotesk',sans-serif;"><?= number_format($stats['students'] ?? 1850) ?>+</div>
        <div class="text-slate-500 text-sm mt-1">Happy Students</div>
      </div>
      <div>
        <div class="gradient-text font-bold" style="font-size:2rem; font-family:'Space Grotesk',sans-serif;"><?= number_format($stats['sessions'] ?? 12400) ?>+</div>
        <div class="text-slate-500 text-sm mt-1">Sessions Completed</div>
      </div>
    </div>
  </div>
</section>

<!-- ═══════════════════════════════════════════
     FEATURED TUTORS
═══════════════════════════════════════════ -->
<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
  <div class="flex items-end justify-between mb-8">
    <div>
      <p style="color:var(--lime); font-size:0.8rem; font-weight:600; letter-spacing:0.1em; text-transform:uppercase; margin-bottom:0.4rem;">Top Rated</p>
      <h2 style="font-size:1.75rem; font-weight:700;">Featured Tutors</h2>
    </div>
    <a href="tutors.php" class="btn-cyan text-sm px-4 py-2 rounded-lg flex items-center gap-1.5">
      View All <i data-lucide="arrow-right" style="width:15px; height:15px;"></i>
    </a>
  </div>

  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
    <?php if ($featuredResult && $featuredResult->num_rows > 0): ?>
      <?php while ($tutor = $featuredResult->fetch_assoc()): ?>
        <div class="card p-5 flex flex-col gap-4">
          <!-- Header -->
          <div class="flex items-start gap-3">
            <div style="width:52px; height:52px; border-radius:50%; overflow:hidden; border:2px solid rgba(163,230,53,0.3); flex-shrink:0;">
              <?php if ($tutor['profile_photo']): ?>
                <img src="uploads/<?= htmlspecialchars($tutor['profile_photo']) ?>" alt="" style="width:100%;height:100%;object-fit:cover;">
              <?php else: ?>
                <div style="width:100%;height:100%;background:linear-gradient(135deg,var(--navy-600),var(--navy-700)); display:flex;align-items:center;justify-content:center;">
                  <span style="font-family:'Space Grotesk',sans-serif; font-weight:700; color:var(--lime); font-size:1.1rem;"><?= strtoupper(substr($tutor['full_name'],0,1)) ?></span>
                </div>
              <?php endif; ?>
            </div>
            <div class="flex-1 min-w-0">
              <h3 style="font-weight:600; font-size:1rem; font-family:'Space Grotesk',sans-serif; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                <?= htmlspecialchars($tutor['full_name']) ?>
              </h3>
              <div class="flex items-center gap-1 mt-0.5">
                <i data-lucide="map-pin" style="width:12px;height:12px; color:var(--cyan); flex-shrink:0;"></i>
                <span class="text-slate-500 text-xs"><?= htmlspecialchars($tutor['city'] ?? 'Online') ?></span>
              </div>
            </div>
            <div class="text-right flex-shrink-0">
              <div style="color:var(--lime); font-weight:700; font-family:'Space Grotesk',sans-serif;">₹<?= number_format($tutor['hourly_rate']) ?></div>
              <div class="text-slate-500 text-xs">/hr</div>
            </div>
          </div>

          <!-- Subjects -->
          <div class="flex flex-wrap gap-1.5">
            <?php foreach(explode(',', $tutor['subjects']) as $sub): ?>
              <span class="badge-cyan text-xs px-2 py-0.5 rounded-full"><?= htmlspecialchars(trim($sub)) ?></span>
            <?php endforeach; ?>
          </div>

          <!-- Rating + Book -->
          <div class="flex items-center justify-between mt-auto pt-3" style="border-top:1px solid rgba(255,255,255,0.06);">
            <div class="flex items-center gap-1.5">
              <span class="star">★</span>
              <span style="font-weight:600; font-size:0.9rem;"><?= number_format($tutor['rating'] ?? 0, 1) ?></span>
              <span class="text-slate-500 text-xs">(<?= $tutor['total_reviews'] ?? 0 ?> reviews)</span>
            </div>
            <a href="tutor-profile.php?id=<?= $tutor['tutor_id'] ?>" class="btn-lime text-xs px-4 py-1.5 rounded-lg">Book Now</a>
          </div>
        </div>
      <?php endwhile; ?>
    <?php else: ?>
      <!-- Placeholder cards when DB is empty -->
      <?php
      $demo = [
        ['Priya Sharma','Mathematics, Physics','₹600','4.9','28','Mumbai'],
        ['Rahul Mehta','Chemistry, Biology','₹550','4.8','41','Delhi'],
        ['Ananya Krishnan','English, History','₹500','4.7','19','Bengaluru'],
        ['Vikram Nair','Computer Science','₹750','5.0','33','Hyderabad'],
        ['Sneha Patel','Economics, Accounts','₹480','4.6','15','Ahmedabad'],
        ['Arjun Das','Mathematics','₹620','4.8','52','Kolkata'],
      ];
      foreach($demo as $i => $d):
      ?>
        <div class="card p-5 flex flex-col gap-4">
          <div class="flex items-start gap-3">
            <div style="width:52px; height:52px; border-radius:50%; background:linear-gradient(135deg,var(--navy-600),var(--navy-700)); display:flex;align-items:center;justify-content:center; border:2px solid rgba(163,230,53,0.3); flex-shrink:0;">
              <span style="font-family:'Space Grotesk',sans-serif; font-weight:700; color:var(--lime); font-size:1.1rem;"><?= strtoupper($d[0][0]) ?></span>
            </div>
            <div class="flex-1 min-w-0">
              <h3 style="font-weight:600; font-size:1rem; font-family:'Space Grotesk',sans-serif;"><?= $d[0] ?></h3>
              <div class="flex items-center gap-1 mt-0.5">
                <i data-lucide="map-pin" style="width:12px;height:12px; color:var(--cyan);"></i>
                <span class="text-slate-500 text-xs"><?= $d[5] ?></span>
              </div>
            </div>
            <div class="text-right flex-shrink-0">
              <div style="color:var(--lime); font-weight:700; font-family:'Space Grotesk',sans-serif;"><?= $d[2] ?></div>
              <div class="text-slate-500 text-xs">/hr</div>
            </div>
          </div>
          <div class="flex flex-wrap gap-1.5">
            <?php foreach(explode(',',$d[1]) as $sub): ?>
              <span class="badge-cyan text-xs px-2 py-0.5 rounded-full"><?= trim($sub) ?></span>
            <?php endforeach; ?>
          </div>
          <div class="flex items-center justify-between mt-auto pt-3" style="border-top:1px solid rgba(255,255,255,0.06);">
            <div class="flex items-center gap-1.5">
              <span class="star">★</span>
              <span style="font-weight:600; font-size:0.9rem;"><?= $d[3] ?></span>
              <span class="text-slate-500 text-xs">(<?= $d[4] ?> reviews)</span>
            </div>
            <a href="tutors.php" class="btn-lime text-xs px-4 py-1.5 rounded-lg">Book Now</a>
          </div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
</section>

<!-- ═══════════════════════════════════════════
     HOW IT WORKS
═══════════════════════════════════════════ -->
<section style="background: var(--navy-800); border-top:1px solid rgba(255,255,255,0.05);">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 text-center">
    <p style="color:var(--cyan); font-size:0.8rem; font-weight:600; letter-spacing:0.1em; text-transform:uppercase; margin-bottom:0.4rem;">Simple Process</p>
    <h2 style="font-size:1.75rem; font-weight:700; margin-bottom:3rem;">How It Works</h2>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
      <?php
      $steps = [
        ['search', 'Find a Tutor', 'Search by subject, location, and budget. View verified profiles and reviews.', 'var(--lime)'],
        ['calendar-check', 'Book a Session', 'Choose your preferred time slot and book instantly. UPI and other payments accepted.', 'var(--cyan)'],
        ['graduation-cap', 'Start Learning', 'Connect with your tutor and begin your personalised learning journey.', 'var(--lime)'],
      ];
      foreach ($steps as $n => $step):
      ?>
        <div class="flex flex-col items-center gap-4">
          <div style="width:64px; height:64px; border-radius:16px; background:rgba(<?= $step[3]==='var(--lime)' ? '163,230,53' : '34,211,238' ?>,0.1); border:1px solid rgba(<?= $step[3]==='var(--lime)' ? '163,230,53' : '34,211,238' ?>,0.25); display:flex; align-items:center; justify-content:center;">
            <i data-lucide="<?= $step[0] ?>" style="width:28px;height:28px; color:<?= $step[3] ?>;"></i>
          </div>
          <div style="width:24px; height:24px; border-radius:50%; background:var(--navy-600); display:flex;align-items:center;justify-content:center; font-size:0.75rem; font-weight:700; color:var(--lime); font-family:'Space Grotesk',sans-serif;"><?= $n+1 ?></div>
          <h3 style="font-weight:600; font-family:'Space Grotesk',sans-serif;"><?= $step[1] ?></h3>
          <p class="text-slate-500 text-sm leading-relaxed max-w-xs"><?= $step[2] ?></p>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ═══════════════════════════════════════════
     CTA SECTION
═══════════════════════════════════════════ -->
<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
  <div style="background: linear-gradient(135deg, rgba(163,230,53,0.08) 0%, rgba(34,211,238,0.08) 100%); border: 1px solid rgba(163,230,53,0.2); border-radius:1.5rem; padding: 56px 40px; text-align:center; position:relative; overflow:hidden;">
    <div style="position:absolute;inset:0;background:radial-gradient(ellipse at center, rgba(163,230,53,0.05) 0%, transparent 70%);"></div>
    <div style="position:relative; z-index:1;">
      <h2 style="font-size: clamp(1.5rem,3vw,2.25rem); font-weight:700; margin-bottom:0.75rem;">Are You a Tutor?</h2>
      <p class="text-slate-400 mb-8 max-w-md mx-auto">Join our verified tutor network. Upload your credentials, set your schedule, and start earning.</p>
      <div class="flex flex-wrap justify-center gap-4">
        <a href="register.php?role=tutor" class="btn-lime px-8 py-3 rounded-lg glow-pulse">Register as Tutor</a>
        <a href="tutors.php" class="btn-cyan px-8 py-3 rounded-lg">Browse Tutors</a>
      </div>
    </div>
  </div>
</section>

<?php include 'partials/footer.php'; ?>