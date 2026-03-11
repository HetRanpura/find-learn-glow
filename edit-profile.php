<?php
require_once 'db.php';
requireLogin();
$pageTitle = 'Edit Profile – FindLearnGlow';

$userId = $_SESSION['user_id'];
$role   = $_SESSION['user_role'];

$uStmt = $conn->prepare("SELECT * FROM users WHERE user_id=?");
$uStmt->bind_param('i', $userId); $uStmt->execute();
$userInfo = $uStmt->get_result()->fetch_assoc(); $uStmt->close();

$tutorInfo = null; $tutorId = 0;
if ($role === 'tutor') {
    $tStmt = $conn->prepare("SELECT * FROM tutors WHERE user_id=?");
    $tStmt->bind_param('i', $userId); $tStmt->execute();
    $tutorInfo = $tStmt->get_result()->fetch_assoc(); $tStmt->close();
    $tutorId = $tutorInfo['tutor_id'] ?? 0;
}

$errors  = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'update_profile';

    // ── Update basic profile ──────────────────────────────────
    if ($action === 'update_profile') {
        $full_name = sanitize($conn, $_POST['full_name'] ?? '');
        $phone     = sanitize($conn, $_POST['phone']     ?? '');
        $city      = sanitize($conn, $_POST['city']      ?? '');

        // Name validation
        if (empty($full_name)) {
            $errors[] = 'Full name is required.';
        } elseif (!preg_match('/^[A-Za-z\s]+$/', $_POST['full_name'])) {
            $errors[] = 'Name must contain letters only — no numbers or symbols.';
        }

        // Tutor-specific
        $hourly_rate   = (float)($_POST['hourly_rate']   ?? 0);
        $experience    = (int)($_POST['experience']      ?? 0);
        $qualification = sanitize($conn, $_POST['qualification'] ?? '');
        $bio           = sanitize($conn, $_POST['bio']           ?? '');
        $subjects      = sanitize($conn, $_POST['subjects']      ?? '');

        if (empty($errors)) {
            // Update users table
            $s = $conn->prepare("UPDATE users SET full_name=?,phone=?,city=? WHERE user_id=?");
            $s->bind_param('sssi', $full_name, $phone, $city, $userId);
            $s->execute(); $s->close();

            // Update session name
            $_SESSION['user_name'] = $full_name;

            // Update tutors table
            if ($role === 'tutor' && $tutorId > 0) {
                $s2 = $conn->prepare("UPDATE tutors SET subjects=?,hourly_rate=?,experience_years=?,qualification=?,bio=? WHERE tutor_id=?");
                $s2->bind_param('sdissi', $subjects, $hourly_rate, $experience, $qualification, $bio, $tutorId);
                $s2->execute(); $s2->close();
            }

            setFlash('success', 'Profile updated successfully!');
            redirect('dashboard.php?tab=profile');
        }
    }

    // ── Change password ───────────────────────────────────────
    if ($action === 'change_password') {
        $current = $_POST['current_password'] ?? '';
        $new     = $_POST['new_password']     ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        $hStmt = $conn->prepare("SELECT password_hash FROM users WHERE user_id=?");
        $hStmt->bind_param('i', $userId); $hStmt->execute();
        $hash = $hStmt->get_result()->fetch_assoc()['password_hash']; $hStmt->close();

        if (!password_verify($current, $hash)) {
            $errors[] = 'Current password is incorrect.';
        } elseif (strlen($new) < 8) {
            $errors[] = 'New password must be at least 8 characters.';
        } elseif ($new !== $confirm) {
            $errors[] = 'New passwords do not match.';
        } else {
            $newHash = password_hash($new, PASSWORD_BCRYPT);
            $s = $conn->prepare("UPDATE users SET password_hash=? WHERE user_id=?");
            $s->bind_param('si', $newHash, $userId); $s->execute(); $s->close();
            setFlash('success', 'Password changed successfully!');
            redirect('dashboard.php?tab=profile');
        }
    }
}

// Re-fetch updated data
$uStmt2 = $conn->prepare("SELECT * FROM users WHERE user_id=?");
$uStmt2->bind_param('i', $userId); $uStmt2->execute();
$userInfo = $uStmt2->get_result()->fetch_assoc(); $uStmt2->close();

if ($role === 'tutor' && $tutorId > 0) {
    $tStmt2 = $conn->prepare("SELECT * FROM tutors WHERE user_id=?");
    $tStmt2->bind_param('i', $userId); $tStmt2->execute();
    $tutorInfo = $tStmt2->get_result()->fetch_assoc(); $tStmt2->close();
}

include 'partials/header.php';
?>

<div class="max-w-3xl mx-auto px-4 sm:px-6 py-10">

  <!-- Back link -->
  <a href="dashboard.php?tab=profile" class="inline-flex items-center gap-1.5 text-sm mb-6" style="color:var(--cyan);">
    <i data-lucide="arrow-left" style="width:15px;height:15px;"></i> Back to Dashboard
  </a>

  <h1 style="font-size:1.6rem;font-weight:700;font-family:'Space Grotesk',sans-serif;margin-bottom:0.25rem;">Edit Profile</h1>
  <p class="text-slate-400 text-sm mb-7">Update your personal information and preferences.</p>

  <?php if (!empty($errors)): ?>
    <div style="background:rgba(239,68,68,0.1);border:1px solid rgba(239,68,68,0.3);border-radius:0.75rem;padding:1rem 1.25rem;margin-bottom:1.5rem;">
      <?php foreach($errors as $e): ?><p style="color:#fca5a5;font-size:0.875rem;">• <?= htmlspecialchars($e) ?></p><?php endforeach; ?>
    </div>
  <?php endif; ?>

  <!-- ── PERSONAL INFO ── -->
  <form method="POST" class="card p-6 flex flex-col gap-5 mb-5">
    <input type="hidden" name="action" value="update_profile">
    <h2 style="font-size:1rem;font-weight:600;font-family:'Space Grotesk',sans-serif;color:var(--cyan);">Personal Information</h2>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
      <div>
        <label class="block text-slate-400 text-sm mb-1.5">Full Name *</label>
        <input type="text" name="full_name" class="form-input"
               value="<?= htmlspecialchars($userInfo['full_name']) ?>"
               pattern="[A-Za-z\s]+" title="Letters and spaces only"
               oninput="this.value=this.value.replace(/[^A-Za-z\s]/g,'')" required>
      </div>
      <div>
        <label class="block text-slate-400 text-sm mb-1.5">Phone</label>
        <input type="tel" name="phone" class="form-input" value="<?= htmlspecialchars($userInfo['phone']??'') ?>" placeholder="+91 98765 43210">
      </div>
      <div class="sm:col-span-2">
        <label class="block text-slate-400 text-sm mb-1.5">City</label>
        <input type="text" name="city" class="form-input" value="<?= htmlspecialchars($userInfo['city']??'') ?>" placeholder="Mumbai">
      </div>
    </div>

    <?php if ($role === 'tutor'): ?>
      <div style="height:1px;background:rgba(255,255,255,0.06);"></div>
      <h2 style="font-size:1rem;font-weight:600;font-family:'Space Grotesk',sans-serif;color:var(--lime);">Tutor Profile</h2>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div class="sm:col-span-2">
          <label class="block text-slate-400 text-sm mb-1.5">Subjects (comma separated)</label>
          <input type="text" name="subjects" class="form-input"
                 value="<?= htmlspecialchars($tutorInfo['subjects']??'') ?>"
                 placeholder="Mathematics, Physics, Chemistry">
          <p class="text-slate-600 text-xs mt-1">These appear in search results. Add detailed courses from your Dashboard → My Courses tab.</p>
        </div>
        <div>
          <label class="block text-slate-400 text-sm mb-1.5">Hourly Rate (₹)</label>
          <input type="number" name="hourly_rate" class="form-input" min="0"
                 value="<?= htmlspecialchars($tutorInfo['hourly_rate']??'') ?>">
        </div>
        <div>
          <label class="block text-slate-400 text-sm mb-1.5">Experience (years)</label>
          <input type="number" name="experience" class="form-input" min="0"
                 value="<?= htmlspecialchars($tutorInfo['experience_years']??0) ?>">
        </div>
        <div class="sm:col-span-2">
          <label class="block text-slate-400 text-sm mb-1.5">Qualification</label>
          <input type="text" name="qualification" class="form-input"
                 value="<?= htmlspecialchars($tutorInfo['qualification']??'') ?>"
                 placeholder="B.Sc Mathematics, IIT Delhi">
        </div>
        <div class="sm:col-span-2">
          <label class="block text-slate-400 text-sm mb-1.5">Bio / About You</label>
          <textarea name="bio" class="form-input" rows="3"
            placeholder="Describe your teaching style, achievements, and what makes you a great tutor…"><?= htmlspecialchars($tutorInfo['bio']??'') ?></textarea>
        </div>
      </div>
    <?php endif; ?>

    <button type="submit" class="btn-lime w-full py-3 rounded-lg text-sm">Save Changes</button>
  </form>

  <!-- ── CHANGE PASSWORD ── -->
  <form method="POST" class="card p-6 flex flex-col gap-4">
    <input type="hidden" name="action" value="change_password">
    <h2 style="font-size:1rem;font-weight:600;font-family:'Space Grotesk',sans-serif;color:var(--cyan);">Change Password</h2>

    <div>
      <label class="block text-slate-400 text-sm mb-1.5">Current Password</label>
      <input type="password" name="current_password" class="form-input" placeholder="Enter current password">
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
      <div>
        <label class="block text-slate-400 text-sm mb-1.5">New Password</label>
        <input type="password" name="new_password" class="form-input" placeholder="Min. 8 characters">
      </div>
      <div>
        <label class="block text-slate-400 text-sm mb-1.5">Confirm New Password</label>
        <input type="password" name="confirm_password" class="form-input" placeholder="Repeat new password">
      </div>
    </div>
    <button type="submit" class="btn-cyan py-3 rounded-lg text-sm">Change Password</button>
  </form>

</div>

<?php include 'partials/footer.php'; ?>
