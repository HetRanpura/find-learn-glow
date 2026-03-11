<?php
require_once 'db.php';
$pageTitle = 'Login – FindLearnGlow';

if ($isLoggedIn ?? false) redirect('dashboard.php');

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email']    ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($email) || empty($password)) {
        $errors[] = 'Please enter your email and password.';
    } else {
        $stmt = $conn->prepare("SELECT user_id, full_name, password_hash, role FROM users WHERE email = ? LIMIT 1");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            $errors[] = 'No account found with that email.';
        } else {
            $user = $result->fetch_assoc();
            if (!password_verify($password, $user['password_hash'])) {
                $errors[] = 'Incorrect password. Please try again.';
            } else {
                $_SESSION['user_id']   = $user['user_id'];
                $_SESSION['user_name'] = $user['full_name'];
                $_SESSION['user_role'] = $user['role'];
                setFlash('success', 'Welcome back, ' . $user['full_name'] . '!');
                redirect('dashboard.php');
            }
        }
        $stmt->close();
    }
}

include 'partials/header.php';
?>

<div class="max-w-md mx-auto px-4 sm:px-6 py-16">
  <div class="text-center mb-8">
    <div style="width:56px;height:56px;margin:0 auto 1.25rem;border-radius:14px;background:linear-gradient(135deg,var(--lime),var(--cyan));display:flex;align-items:center;justify-content:center;">
      <i data-lucide="log-in" style="width:26px;height:26px;color:#04090f;"></i>
    </div>
    <h1 style="font-size:1.75rem; font-weight:700; margin-bottom:0.4rem;">Welcome Back</h1>
    <p class="text-slate-400 text-sm">Don't have an account? <a href="register.php" style="color:var(--cyan);">Register here</a></p>
  </div>

  <?php if (!empty($errors)): ?>
    <div style="background:rgba(239,68,68,0.1);border:1px solid rgba(239,68,68,0.25);border-radius:0.75rem;padding:1rem 1.25rem;margin-bottom:1.25rem;">
      <?php foreach($errors as $e): ?>
        <p style="color:#fca5a5;font-size:0.875rem;"><?= htmlspecialchars($e) ?></p>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <form method="POST" action="login.php" class="card p-7 flex flex-col gap-5">
    <div>
      <label class="block text-slate-400 text-sm mb-1.5">Email Address</label>
      <input type="email" name="email" class="form-input" placeholder="you@example.com"
             value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
    </div>

    <div>
      <div class="flex items-center justify-between mb-1.5">
        <label class="text-slate-400 text-sm">Password</label>
        <a href="#" class="text-xs" style="color:var(--cyan);">Forgot password?</a>
      </div>
      <div style="position:relative;">
        <input type="password" name="password" id="password" class="form-input" placeholder="Your password" required style="padding-right:2.5rem;">
        <button type="button" onclick="togglePwd()" style="position:absolute;right:10px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;">
          <i id="eyeIcon" data-lucide="eye" style="width:16px;height:16px;color:#4a5568;"></i>
        </button>
      </div>
    </div>

    <div class="flex items-center gap-2">
      <input type="checkbox" name="remember" id="remember" style="accent-color:var(--lime); width:15px;height:15px;">
      <label for="remember" class="text-slate-400 text-sm">Keep me logged in</label>
    </div>

    <button type="submit" class="btn-lime w-full py-3 rounded-lg text-base mt-1">Log In</button>
  </form>
</div>

<script>
  function togglePwd() {
    const inp = document.getElementById('password');
    const ico = document.getElementById('eyeIcon');
    inp.type = inp.type === 'password' ? 'text' : 'password';
    ico.setAttribute('data-lucide', inp.type === 'password' ? 'eye' : 'eye-off');
    lucide.createIcons();
  }
</script>

<?php include 'partials/footer.php'; ?>