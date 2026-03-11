<?php
require_once 'db.php';
$pageTitle = 'Register – FindLearnGlow';

$errors = [];
$role   = sanitize($conn, $_GET['role'] ?? 'student');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $role        = sanitize($conn, $_POST['role']      ?? 'student');
    $full_name   = sanitize($conn, $_POST['full_name'] ?? '');
    $email       = sanitize($conn, $_POST['email']     ?? '');
    $phone       = sanitize($conn, $_POST['phone']     ?? '');
    $city        = sanitize($conn, $_POST['city']      ?? '');
    $password    = $_POST['password']  ?? '';
    $password2   = $_POST['password2'] ?? '';

    // Tutor fields
    $subjects      = sanitize($conn, $_POST['subjects']      ?? '');
    $hourly_rate   = (float)($_POST['hourly_rate']   ?? 0);
    $experience    = (int)($_POST['experience']      ?? 0);
    $qualification = sanitize($conn, $_POST['qualification'] ?? '');
    $bio           = sanitize($conn, $_POST['bio']           ?? '');
    // Course fields
    $course_subject = sanitize($conn, $_POST['course_subject'] ?? '');
    $syllabus       = sanitize($conn, $_POST['syllabus']       ?? '');
    $duration       = sanitize($conn, $_POST['duration']       ?? '');

    // Validation
    if (empty($full_name)) {
        $errors[] = 'Full name is required.';
    } elseif (!preg_match('/^[A-Za-z\s]+$/', $_POST['full_name'])) {
        $errors[] = 'Name must contain letters only — no numbers or special characters.';
    } elseif (strlen(trim($full_name)) < 2) {
        $errors[] = 'Name must be at least 2 characters.';
    }

    if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) $errors[] = 'Enter a valid email address.';
    if (strlen($password) < 8) $errors[] = 'Password must be at least 8 characters.';
    if ($password !== $password2) $errors[] = 'Passwords do not match.';

    $chk = $conn->prepare("SELECT user_id FROM users WHERE email=?");
    $chk->bind_param('s', $_POST['email']); $chk->execute();
    if ($chk->get_result()->num_rows > 0) $errors[] = 'An account with this email already exists.';
    $chk->close();

    if ($role === 'tutor') {
        if (empty($subjects))    $errors[] = 'Please enter at least one subject.';
        if ($hourly_rate <= 0)   $errors[] = 'Enter a valid hourly rate.';
        if (empty($qualification)) $errors[] = 'Qualification is required.';
    }

    // Certificate upload
    $cert_filename = null;
    if ($role === 'tutor' && isset($_FILES['certificate']) && $_FILES['certificate']['error'] !== UPLOAD_ERR_NO_FILE) {
        $file = $_FILES['certificate'];
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'Certificate upload failed (error code: ' . $file['error'] . ')';
        } else {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime  = finfo_file($finfo, $file['tmp_name']); finfo_close($finfo);
            $allowed = ['application/pdf','image/jpeg','image/png','image/jpg'];
            if (!in_array($mime, $allowed)) {
                $errors[] = 'Certificate must be PDF, JPG, or PNG.';
            } elseif ($file['size'] > 5*1024*1024) {
                $errors[] = 'Certificate file too large (max 5 MB).';
            } else {
                $dir = __DIR__ . '/uploads/certificates/';
                if (!is_dir($dir)) mkdir($dir, 0755, true);
                $ext  = match($mime){ 'application/pdf'=>'.pdf','image/png'=>'.png', default=>'.jpg' };
                $cert_filename = time() . '_' . bin2hex(random_bytes(6)) . $ext;
                if (!move_uploaded_file($file['tmp_name'], $dir . $cert_filename)) {
                    $errors[] = 'Could not save certificate. Check uploads/certificates/ is writable.';
                    $cert_filename = null;
                }
            }
        }
    }

    if (empty($errors)) {
        $hashed = password_hash($password, PASSWORD_BCRYPT);
        $conn->begin_transaction();
        try {
            // Insert user
            $s = $conn->prepare("INSERT INTO users (full_name,email,phone,city,password_hash,role) VALUES (?,?,?,?,?,?)");
            $s->bind_param('ssssss', $full_name, $_POST['email'], $phone, $city, $hashed, $role);
            $s->execute(); $user_id = $conn->insert_id; $s->close();

            if ($role === 'tutor') {
                $cert_path = $cert_filename ? 'certificates/'.$cert_filename : null;
                // Insert tutor profile
                $s2 = $conn->prepare("INSERT INTO tutors (user_id,subjects,hourly_rate,experience_years,qualification,bio,certificate_path,verification_status) VALUES (?,?,?,?,?,?,?,'pending')");
                $s2->bind_param('isdisss', $user_id, $subjects, $hourly_rate, $experience, $qualification, $bio, $cert_path);
                $s2->execute(); $tutor_id = $conn->insert_id; $s2->close();

                // Insert initial course if provided
                if ($course_subject && $syllabus && $duration) {
                    $s3 = $conn->prepare("INSERT INTO tutor_courses (tutor_id,subject_name,syllabus,duration) VALUES (?,?,?,?)");
                    $s3->bind_param('isss', $tutor_id, $course_subject, $syllabus, $duration);
                    $s3->execute(); $s3->close();
                }
            }

            $conn->commit();
            $_SESSION['user_id']   = $user_id;
            $_SESSION['user_name'] = $full_name;
            $_SESSION['user_role'] = $role;
            setFlash('success', $role === 'tutor'
                ? 'Welcome! Your profile is under review. Add more courses from your dashboard.'
                : 'Welcome to FindLearnGlow! Start finding tutors.');
            redirect('dashboard.php');

        } catch (Exception $e) {
            $conn->rollback();
            $errors[] = 'Registration failed: ' . $e->getMessage();
        }
    }
}

include 'partials/header.php';
?>

<div class="max-w-2xl mx-auto px-4 sm:px-6 py-12">

  <div class="text-center mb-8">
    <h1 style="font-size:2rem;font-weight:700;margin-bottom:0.5rem;">Create Your Account</h1>
    <p class="text-slate-400 text-sm">Already have one? <a href="login.php" style="color:var(--cyan);">Log in here</a></p>
  </div>

  <!-- Role Toggle -->
  <div style="background:var(--navy-800);border:1px solid rgba(255,255,255,0.08);border-radius:0.875rem;padding:5px;display:flex;gap:4px;margin-bottom:1.5rem;">
    <button type="button" id="tab-student" onclick="switchRole('student')"
      class="flex-1 py-2.5 text-sm font-semibold rounded-lg transition-all" style="font-family:'Space Grotesk',sans-serif;">Student</button>
    <button type="button" id="tab-tutor" onclick="switchRole('tutor')"
      class="flex-1 py-2.5 text-sm font-semibold rounded-lg transition-all" style="font-family:'Space Grotesk',sans-serif;">Tutor</button>
  </div>

  <?php if (!empty($errors)): ?>
    <div style="background:rgba(239,68,68,0.1);border:1px solid rgba(239,68,68,0.25);border-radius:0.75rem;padding:1rem 1.25rem;margin-bottom:1.5rem;">
      <div class="flex items-center gap-2 mb-1.5"><i data-lucide="x-circle" style="width:16px;height:16px;color:#f87171;"></i><span style="color:#f87171;font-weight:600;font-size:0.875rem;">Please fix the following:</span></div>
      <?php foreach($errors as $e): ?><p style="color:#fca5a5;font-size:0.85rem;padding-left:1.5rem;">• <?= htmlspecialchars($e) ?></p><?php endforeach; ?>
    </div>
  <?php endif; ?>

  <form method="POST" action="register.php" enctype="multipart/form-data" class="card p-7 flex flex-col gap-5">
    <input type="hidden" name="role" id="roleInput" value="<?= htmlspecialchars($role) ?>">

    <!-- Personal Info -->
    <h3 style="font-size:0.8rem;font-weight:600;color:var(--cyan);letter-spacing:0.08em;text-transform:uppercase;">Personal Information</h3>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
      <div>
        <label class="block text-slate-400 text-sm mb-1.5">Full Name *</label>
        <input type="text" name="full_name" id="fullName" class="form-input" placeholder="Priya Sharma"
               value="<?= htmlspecialchars($_POST['full_name']??'') ?>"
               pattern="[A-Za-z\s]+" title="Letters and spaces only"
               oninput="validateName(this)" required>
        <p id="nameError" class="text-xs mt-1" style="color:#f87171;display:none;">⚠ Name must contain letters only — no numbers or symbols.</p>
      </div>
      <div>
        <label class="block text-slate-400 text-sm mb-1.5">Phone</label>
        <input type="tel" name="phone" class="form-input" placeholder="+91 98765 43210" value="<?= htmlspecialchars($_POST['phone']??'') ?>">
      </div>
      <div>
        <label class="block text-slate-400 text-sm mb-1.5">Email *</label>
        <input type="email" name="email" class="form-input" placeholder="you@example.com" value="<?= htmlspecialchars($_POST['email']??'') ?>" required>
      </div>
      <div>
        <label class="block text-slate-400 text-sm mb-1.5">City</label>
        <input type="text" name="city" class="form-input" placeholder="Mumbai" value="<?= htmlspecialchars($_POST['city']??'') ?>">
      </div>
      <div>
        <label class="block text-slate-400 text-sm mb-1.5">Password *</label>
        <div style="position:relative;">
          <input type="password" name="password" id="pwd" class="form-input" placeholder="Min. 8 characters" required style="padding-right:2.5rem;">
          <button type="button" onclick="togglePwd('pwd','eye1')" style="position:absolute;right:10px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;">
            <i id="eye1" data-lucide="eye" style="width:16px;height:16px;color:#4a5568;"></i>
          </button>
        </div>
      </div>
      <div>
        <label class="block text-slate-400 text-sm mb-1.5">Confirm Password *</label>
        <div style="position:relative;">
          <input type="password" name="password2" id="pwd2" class="form-input" placeholder="Repeat password" required style="padding-right:2.5rem;">
          <button type="button" onclick="togglePwd('pwd2','eye2')" style="position:absolute;right:10px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;">
            <i id="eye2" data-lucide="eye" style="width:16px;height:16px;color:#4a5568;"></i>
          </button>
        </div>
      </div>
    </div>

    <!-- Tutor-only section -->
    <div id="tutorFields" style="display:none;flex-direction:column;gap:1.25rem;">
      <div style="height:1px;background:rgba(255,255,255,0.06);"></div>
      <h3 style="font-size:0.8rem;font-weight:600;color:var(--lime);letter-spacing:0.08em;text-transform:uppercase;">Tutor Profile</h3>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div class="sm:col-span-2">
          <label class="block text-slate-400 text-sm mb-1.5">Subjects (comma-separated) *</label>
          <input type="text" name="subjects" class="form-input" placeholder="Mathematics, Physics, Chemistry" value="<?= htmlspecialchars($_POST['subjects']??'') ?>">
        </div>
        <div>
          <label class="block text-slate-400 text-sm mb-1.5">Hourly Rate (₹) *</label>
          <input type="number" name="hourly_rate" class="form-input" placeholder="500" min="50" value="<?= htmlspecialchars($_POST['hourly_rate']??'') ?>">
        </div>
        <div>
          <label class="block text-slate-400 text-sm mb-1.5">Experience (years)</label>
          <input type="number" name="experience" class="form-input" placeholder="3" min="0" value="<?= htmlspecialchars($_POST['experience']??'') ?>">
        </div>
        <div class="sm:col-span-2">
          <label class="block text-slate-400 text-sm mb-1.5">Qualification *</label>
          <input type="text" name="qualification" class="form-input" placeholder="B.Sc Mathematics, IIT Delhi" value="<?= htmlspecialchars($_POST['qualification']??'') ?>">
        </div>
        <div class="sm:col-span-2">
          <label class="block text-slate-400 text-sm mb-1.5">Bio (optional)</label>
          <textarea name="bio" class="form-input" rows="2" placeholder="Briefly describe your teaching style…"><?= htmlspecialchars($_POST['bio']??'') ?></textarea>
        </div>
      </div>

      <!-- COURSE DETAILS (new) -->
      <div style="height:1px;background:rgba(255,255,255,0.06);"></div>
      <div>
        <h3 style="font-size:0.8rem;font-weight:600;color:var(--lime);letter-spacing:0.08em;text-transform:uppercase;margin-bottom:0.25rem;">Add Your First Course <span class="text-slate-600 normal-case" style="font-size:0.75rem;letter-spacing:0;">(optional — add more from dashboard)</span></h3>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label class="block text-slate-400 text-sm mb-1.5">Course / Subject Name</label>
          <input type="text" name="course_subject" class="form-input" placeholder="e.g. Python Programming Basics" value="<?= htmlspecialchars($_POST['course_subject']??'') ?>">
        </div>
        <div>
          <label class="block text-slate-400 text-sm mb-1.5">Course Duration</label>
          <input type="text" name="duration" class="form-input" placeholder="e.g. 1 month, 3 weeks, 45 days" value="<?= htmlspecialchars($_POST['duration']??'') ?>">
        </div>
        <div class="sm:col-span-2">
          <label class="block text-slate-400 text-sm mb-1.5">Syllabus / What students will learn</label>
          <textarea name="syllabus" class="form-input" rows="3"
            placeholder="Week 1: Introduction & variables&#10;Week 2: Loops and conditions&#10;Week 3: Functions&#10;Week 4: Mini project"><?= htmlspecialchars($_POST['syllabus']??'') ?></textarea>
        </div>
      </div>

      <!-- Certificate Upload -->
      <div>
        <label class="block text-slate-400 text-sm mb-1.5">Upload Certificate / Degree</label>
        <div id="dropZone" onclick="document.getElementById('certificate').click()"
             style="border:2px dashed rgba(163,230,53,0.25);border-radius:0.75rem;padding:2rem;text-align:center;cursor:pointer;transition:border-color 0.2s;"
             ondragover="event.preventDefault();this.style.borderColor='var(--lime)';"
             ondragleave="this.style.borderColor='rgba(163,230,53,0.25)';"
             ondrop="handleDrop(event)">
          <i data-lucide="upload-cloud" style="width:36px;height:36px;color:var(--lime);margin:0 auto 0.75rem;display:block;"></i>
          <p class="text-slate-400 text-sm mb-1">Click to browse or drag &amp; drop</p>
          <p class="text-slate-600 text-xs">PDF, JPG, PNG — Max 5 MB</p>
          <p id="fileName" class="mt-2 text-xs" style="color:var(--lime);display:none;"></p>
        </div>
        <input type="file" name="certificate" id="certificate" accept=".pdf,.jpg,.jpeg,.png" style="display:none;" onchange="updateFileName(this)">
      </div>
    </div>

    <button type="submit" class="btn-lime w-full py-3 rounded-lg text-base mt-1">Create Account</button>
    <p class="text-slate-600 text-xs text-center">By registering you agree to our <a href="#" style="color:var(--cyan);">Terms of Service</a>.</p>
  </form>
</div>

<script>
function switchRole(r) {
  document.getElementById('roleInput').value = r;
  const tf = document.getElementById('tutorFields');
  const ts = document.getElementById('tab-student');
  const tt = document.getElementById('tab-tutor');
  if (r === 'tutor') {
    tf.style.display = 'flex';
    tt.style.background = 'var(--lime)'; tt.style.color = '#04090f';
    ts.style.background = 'transparent'; ts.style.color = '#94a3b8';
  } else {
    tf.style.display = 'none';
    ts.style.background = 'var(--lime)'; ts.style.color = '#04090f';
    tt.style.background = 'transparent'; tt.style.color = '#94a3b8';
  }
}
switchRole('<?= $role ?>');

function validateName(input) {
  const valid = /^[A-Za-z\s]*$/.test(input.value);
  const err = document.getElementById('nameError');
  if (!valid) {
    input.value = input.value.replace(/[^A-Za-z\s]/g, '');
    if (err) err.style.display = 'block';
  } else {
    if (err) err.style.display = 'none';
  }
  if (input.value.startsWith(' ')) input.value = input.value.trimStart();
}
document.getElementById('fullName')?.addEventListener('keypress', function(e) {
  if (!/[A-Za-z\s]/.test(e.key)) e.preventDefault();
});

function togglePwd(inputId, iconId) {
  const inp = document.getElementById(inputId);
  const ico = document.getElementById(iconId);
  inp.type = inp.type === 'password' ? 'text' : 'password';
  ico.setAttribute('data-lucide', inp.type === 'password' ? 'eye' : 'eye-off');
  lucide.createIcons();
}

function updateFileName(input) {
  if (input.files && input.files[0]) {
    const el = document.getElementById('fileName');
    el.textContent = '✓ ' + input.files[0].name; el.style.display = 'block';
  }
}
function handleDrop(e) {
  e.preventDefault();
  document.getElementById('dropZone').style.borderColor = 'rgba(163,230,53,0.25)';
  const f = e.dataTransfer.files[0];
  if (f) { const dt = new DataTransfer(); dt.items.add(f); document.getElementById('certificate').files = dt.files; updateFileName(document.getElementById('certificate')); }
}
lucide.createIcons();
</script>

<?php include 'partials/footer.php'; ?>