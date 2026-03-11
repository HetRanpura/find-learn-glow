<?php
require_once 'db.php';
requireLogin();
$pageTitle = 'Dashboard – FindLearnGlow';

$userId = $_SESSION['user_id'];
$role   = $_SESSION['user_role'];

$uStmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
$uStmt->bind_param('i', $userId);
$uStmt->execute();
$userInfo = $uStmt->get_result()->fetch_assoc();
$uStmt->close();

$tutorInfo = null;
$tutorId   = 0;
if ($role === 'tutor') {
    $tStmt = $conn->prepare("SELECT * FROM tutors WHERE user_id = ?");
    $tStmt->bind_param('i', $userId);
    $tStmt->execute();
    $tutorInfo = $tStmt->get_result()->fetch_assoc();
    $tStmt->close();
    $tutorId = $tutorInfo['tutor_id'] ?? 0;
}

// ── POST ACTIONS ──────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = sanitize($conn, $_POST['action'] ?? '');

    if ($role === 'tutor' && $tutorId > 0) {

        if ($action === 'accept_application') {
            $appId = (int)$_POST['app_id'];
            $s = $conn->prepare("UPDATE applications SET status='accepted' WHERE application_id=? AND tutor_id=?");
            $s->bind_param('ii', $appId, $tutorId); $s->execute(); $s->close();
            setFlash('success', 'Application accepted! Add the student to a batch from the Batches tab.');
            redirect('dashboard.php?tab=applications');
        }

        if ($action === 'reject_application') {
            $appId = (int)$_POST['app_id'];
            $s = $conn->prepare("UPDATE applications SET status='rejected' WHERE application_id=? AND tutor_id=?");
            $s->bind_param('ii', $appId, $tutorId); $s->execute(); $s->close();
            setFlash('info', 'Application rejected.');
            redirect('dashboard.php?tab=applications');
        }

        if ($action === 'add_course') {
            $sn = sanitize($conn, $_POST['course_subject'] ?? '');
            $sy = sanitize($conn, $_POST['syllabus']       ?? '');
            $du = sanitize($conn, $_POST['duration']       ?? '');
            if ($sn && $sy && $du) {
                $s = $conn->prepare("INSERT INTO tutor_courses (tutor_id,subject_name,syllabus,duration) VALUES (?,?,?,?)");
                $s->bind_param('isss', $tutorId, $sn, $sy, $du); $s->execute(); $s->close();
                setFlash('success', 'Course "' . htmlspecialchars($sn) . '" added!');
            } else { setFlash('error', 'Please fill all course fields.'); }
            redirect('dashboard.php?tab=courses');
        }

        if ($action === 'delete_course') {
            $cid = (int)$_POST['course_id'];
            $s = $conn->prepare("DELETE FROM tutor_courses WHERE course_id=? AND tutor_id=?");
            $s->bind_param('ii', $cid, $tutorId); $s->execute(); $s->close();
            setFlash('info', 'Course removed.');
            redirect('dashboard.php?tab=courses');
        }

        if ($action === 'create_batch') {
            $bn = sanitize($conn, $_POST['batch_name']    ?? '');
            $ci = (int)($_POST['course_id'] ?? 0);
            $sd = sanitize($conn, $_POST['schedule_date'] ?? '');
            $st = sanitize($conn, $_POST['schedule_time'] ?? '');
            $ms = max(1, (int)($_POST['max_students'] ?? 20));
            $no = sanitize($conn, $_POST['notes']         ?? '');
            if ($bn && $sd && $st) {
                // type string: i i s s s i s = 7 params ✓
                $s = $conn->prepare("INSERT INTO batches (tutor_id,course_id,batch_name,schedule_date,schedule_time,max_students,notes) VALUES (?,?,?,?,?,?,?)");
                $s->bind_param('iisssis', $tutorId, $ci, $bn, $sd, $st, $ms, $no);
                $s->execute(); $s->close();
                setFlash('success', 'Batch "' . htmlspecialchars($bn) . '" created!');
            } else { setFlash('error', 'Batch name, date and time are required.'); }
            redirect('dashboard.php?tab=batches');
        }

        if ($action === 'enroll_student') {
            $bid = (int)$_POST['batch_id'];
            $sid = (int)$_POST['student_id'];
            $aid = (int)($_POST['app_id'] ?? 0);
            if ($bid && $sid) {
                $chk = $conn->prepare("SELECT enrollment_id FROM batch_enrollments WHERE batch_id=? AND student_id=?");
                $chk->bind_param('ii', $bid, $sid); $chk->execute();
                $alreadyIn = $chk->get_result()->num_rows > 0; $chk->close();
                if ($alreadyIn) {
                    setFlash('error', 'Student already enrolled in this batch.');
                } else {
                    $s = $conn->prepare("INSERT INTO batch_enrollments (batch_id,student_id,application_id) VALUES (?,?,?)");
                    $s->bind_param('iii', $bid, $sid, $aid); $s->execute(); $s->close();
                    setFlash('success', 'Student added to batch!');
                }
            }
            redirect('dashboard.php?tab=batches');
        }

        if ($action === 'mark_batch_done') {
            $bid = (int)$_POST['batch_id'];
            $s  = $conn->prepare("UPDATE batches SET status='completed' WHERE batch_id=? AND tutor_id=?");
            $s->bind_param('ii', $bid, $tutorId); $s->execute(); $s->close();
            $s2 = $conn->prepare("UPDATE batch_enrollments SET status='completed' WHERE batch_id=?");
            $s2->bind_param('i', $bid); $s2->execute(); $s2->close();
            setFlash('success', 'Batch marked as completed! Students will now see their session as done.');
            redirect('dashboard.php?tab=batches');
        }
    }
}

// ── FETCH TAB DATA ────────────────────────────────────────────
$activeTab = sanitize($conn, $_GET['tab'] ?? 'applications');

if ($role === 'tutor' && $tutorId > 0) {

    // All applications for this tutor
    $as = $conn->prepare("SELECT a.*,u.full_name AS student_name,u.city AS student_city FROM applications a JOIN users u ON a.student_id=u.user_id WHERE a.tutor_id=? ORDER BY FIELD(a.status,'pending','accepted','rejected'),a.created_at DESC");
    $as->bind_param('i', $tutorId); $as->execute();
    $applications = $as->get_result()->fetch_all(MYSQLI_ASSOC); $as->close();

    // Courses
    $cs = $conn->prepare("SELECT * FROM tutor_courses WHERE tutor_id=? ORDER BY created_at DESC");
    $cs->bind_param('i', $tutorId); $cs->execute();
    $myCourses = $cs->get_result()->fetch_all(MYSQLI_ASSOC); $cs->close();

    // Batches
    $bs = $conn->prepare("SELECT b.*,tc.subject_name AS course_subject,tc.duration AS course_duration FROM batches b LEFT JOIN tutor_courses tc ON b.course_id=tc.course_id WHERE b.tutor_id=? ORDER BY FIELD(b.status,'upcoming','ongoing','completed'),b.schedule_date ASC");
    $bs->bind_param('i', $tutorId); $bs->execute();
    $myBatches = $bs->get_result()->fetch_all(MYSQLI_ASSOC); $bs->close();

    // Enrollments indexed by batch_id
    $enrollMap = [];
    if (!empty($myBatches)) {
        $bIds = array_column($myBatches, 'batch_id');
        $ph   = implode(',', array_fill(0, count($bIds), '?'));
        $es   = $conn->prepare("SELECT be.*,u.full_name AS student_name FROM batch_enrollments be JOIN users u ON be.student_id=u.user_id WHERE be.batch_id IN ($ph)");
        $es->bind_param(str_repeat('i', count($bIds)), ...$bIds); $es->execute();
        foreach ($es->get_result()->fetch_all(MYSQLI_ASSOC) as $e) $enrollMap[$e['batch_id']][] = $e;
        $es->close();
    }

    // Accepted applications (for enroll dropdown)
    $acc = $conn->prepare("SELECT a.application_id,a.student_id,a.subject,u.full_name FROM applications a JOIN users u ON a.student_id=u.user_id WHERE a.tutor_id=? AND a.status='accepted'");
    $acc->bind_param('i', $tutorId); $acc->execute();
    $acceptedStudents = $acc->get_result()->fetch_all(MYSQLI_ASSOC); $acc->close();

} else {
    // Student: applications
    $as = $conn->prepare("SELECT a.*,u.full_name AS tutor_name FROM applications a JOIN tutors t ON a.tutor_id=t.tutor_id JOIN users u ON t.user_id=u.user_id WHERE a.student_id=? ORDER BY a.created_at DESC");
    $as->bind_param('i', $userId); $as->execute();
    $myApplications = $as->get_result()->fetch_all(MYSQLI_ASSOC); $as->close();

    // Student: sessions
    $ss = $conn->prepare("SELECT be.status AS enrollment_status,be.enrolled_at,b.batch_id,b.batch_name,b.schedule_date,b.schedule_time,b.notes,b.status AS batch_status,b.max_students,tc.subject_name,tc.duration,u.full_name AS tutor_name FROM batch_enrollments be JOIN batches b ON be.batch_id=b.batch_id JOIN tutors t ON b.tutor_id=t.tutor_id JOIN users u ON t.user_id=u.user_id LEFT JOIN tutor_courses tc ON b.course_id=tc.course_id WHERE be.student_id=? ORDER BY FIELD(b.status,'upcoming','ongoing','completed'),b.schedule_date ASC");
    $ss->bind_param('i', $userId); $ss->execute();
    $mySessions = $ss->get_result()->fetch_all(MYSQLI_ASSOC); $ss->close();
}

include 'partials/header.php';
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

  <!-- Header -->
  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6">
    <div>
      <p style="color:var(--lime);font-size:0.75rem;font-weight:600;letter-spacing:0.1em;text-transform:uppercase;margin-bottom:0.2rem;"><?= ucfirst($role) ?> Dashboard</p>
      <h1 style="font-size:1.6rem;font-weight:700;font-family:'Space Grotesk',sans-serif;">Welcome, <?= htmlspecialchars($userInfo['full_name']) ?>! 👋</h1>
    </div>
    <?php if ($role === 'student'): ?>
      <a href="tutors.php" class="btn-lime px-5 py-2.5 rounded-lg text-sm flex items-center gap-2 self-start sm:self-auto">
        <i data-lucide="search" style="width:15px;height:15px;"></i> Find a Tutor
      </a>
    <?php endif; ?>
  </div>

  <!-- Verification Banner -->
  <?php if ($role === 'tutor' && $tutorInfo):
    if ($tutorInfo['verification_status'] === 'pending'): ?>
    <div style="background:rgba(251,191,36,0.08);border:1px solid rgba(251,191,36,0.25);border-radius:0.875rem;padding:1rem 1.25rem;margin-bottom:1.25rem;display:flex;gap:1rem;align-items:center;">
      <i data-lucide="clock" style="width:18px;height:18px;color:#fbbf24;flex-shrink:0;"></i>
      <div><p style="font-weight:600;color:#fbbf24;font-size:0.9rem;">Profile Under Verification</p><p class="text-slate-500 text-sm">Usually approved within 24 hours.</p></div>
    </div>
  <?php elseif ($tutorInfo['verification_status'] === 'approved'): ?>
    <div style="background:rgba(163,230,53,0.07);border:1px solid rgba(163,230,53,0.2);border-radius:0.875rem;padding:0.75rem 1.25rem;margin-bottom:1.25rem;display:flex;gap:0.75rem;align-items:center;">
      <i data-lucide="shield-check" style="width:16px;height:16px;color:var(--lime);flex-shrink:0;"></i>
      <p style="color:var(--lime);font-weight:600;font-size:0.875rem;">Your profile is verified and visible to students!</p>
    </div>
  <?php endif; endif; ?>

  <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">

    <!-- SIDEBAR -->
    <div class="lg:col-span-1">
      <div class="card p-2" style="position:sticky;top:80px;">
        <?php
        $tabs = $role === 'tutor'
          ? ['applications'=>['inbox','Applications'],'courses'=>['book-open','My Courses'],'batches'=>['calendar','Batches'],'profile'=>['user','Profile']]
          : ['applications'=>['send','My Applications'],'sessions'=>['calendar-days','My Sessions'],'profile'=>['user','Profile']];
        foreach($tabs as $k => $v):
          // Badge
          $badge = '';
          if ($k==='applications' && $role==='tutor') {
            $pc = count(array_filter($applications??[], fn($a)=>$a['status']==='pending'));
            if ($pc>0) $badge = "<span style='margin-left:auto;background:var(--lime);color:#04090f;font-weight:700;font-size:0.7rem;border-radius:999px;padding:1px 7px;'>$pc</span>";
          }
          if ($k==='applications' && $role==='student') {
            $pc = count(array_filter($myApplications??[], fn($a)=>$a['status']==='pending'));
            if ($pc>0) $badge = "<span style='margin-left:auto;background:rgba(251,191,36,0.2);color:#fbbf24;font-weight:700;font-size:0.7rem;border-radius:999px;padding:1px 7px;'>$pc</span>";
          }
        ?>
          <button onclick="showTab('<?= $k ?>')" id="btn-<?= $k ?>"
            class="tab-btn w-full flex items-center gap-3 px-3.5 py-2.5 rounded-lg text-sm text-left transition-all mb-0.5"
            style="color:#94a3b8;background:transparent;">
            <i data-lucide="<?= $v[0] ?>" style="width:16px;height:16px;flex-shrink:0;"></i>
            <span><?= $v[1] ?></span>
            <?= $badge ?>
          </button>
        <?php endforeach; ?>

        <div style="margin-top:0.5rem;padding:0.75rem;border-top:1px solid rgba(255,255,255,0.06);">
          <div class="flex items-center gap-2.5">
            <div style="width:34px;height:34px;border-radius:50%;background:linear-gradient(135deg,var(--navy-600),var(--navy-500));display:flex;align-items:center;justify-content:center;border:1.5px solid rgba(163,230,53,0.3);">
              <span style="font-family:'Space Grotesk',sans-serif;font-weight:700;color:var(--lime);font-size:0.875rem;"><?= strtoupper(substr($userInfo['full_name'],0,1)) ?></span>
            </div>
            <div class="min-w-0">
              <p style="font-size:0.78rem;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= htmlspecialchars($userInfo['full_name']) ?></p>
              <p class="text-slate-600 text-xs"><?= ucfirst($role) ?></p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- MAIN -->
    <div class="lg:col-span-3">

      <!-- ═══ APPLICATIONS TAB ═══ -->
      <div id="tab-applications" class="tab-pane">
        <?php if ($role === 'tutor'): ?>
          <h2 class="tab-title">Student Applications</h2>
          <?php if (empty($applications)): ?>
            <div class="card p-10 text-center"><i data-lucide="inbox" style="width:48px;height:48px;color:#334155;margin:0 auto 1rem;display:block;"></i><p style="color:#475569;font-weight:600;">No applications yet</p><p class="text-slate-600 text-sm mt-1">Students who apply to you will appear here.</p></div>
          <?php else: ?>
            <div class="flex flex-col gap-3">
              <?php foreach($applications as $app):
                $sb = match($app['status']){
                  'pending' =>['badge-pending','⏳ Pending'],
                  'accepted'=>['badge-lime','✓ Accepted'],
                  'rejected'=>['','✗ Rejected'],
                  default=>['','']};
              ?>
                <div class="card p-5">
                  <div class="flex flex-wrap items-start justify-between gap-3">
                    <div class="flex-1 min-w-0">
                      <div class="flex flex-wrap items-center gap-2 mb-1">
                        <p style="font-weight:600;font-family:'Space Grotesk',sans-serif;"><?= htmlspecialchars($app['student_name']) ?></p>
                        <?php if($app['student_city']): ?><span class="text-slate-500 text-xs flex items-center gap-1"><i data-lucide="map-pin" style="width:11px;height:11px;"></i><?= htmlspecialchars($app['student_city']) ?></span><?php endif; ?>
                        <span class="text-xs px-2.5 py-0.5 rounded-full <?= $sb[0] ?>"><?= $sb[1] ?></span>
                      </div>
                      <p class="text-slate-400 text-sm mb-1">Wants to learn: <strong style="color:#e2e8f0;"><?= htmlspecialchars($app['subject']) ?></strong></p>
                      <?php if($app['message']): ?>
                        <p class="text-slate-500 text-sm italic" style="border-left:2px solid rgba(163,230,53,0.3);padding-left:0.75rem;">"<?= htmlspecialchars(substr($app['message'],0,150)) ?><?= strlen($app['message'])>150?'…':''?>"</p>
                      <?php endif; ?>
                      <p class="text-slate-600 text-xs mt-2"><?= date('d M Y, h:i A',strtotime($app['created_at'])) ?></p>
                    </div>
                    <?php if($app['status']==='pending'): ?>
                      <div class="flex gap-2 flex-shrink-0">
                        <form method="POST"><input type="hidden" name="action" value="accept_application"><input type="hidden" name="app_id" value="<?= $app['application_id'] ?>">
                          <button type="submit" class="btn-lime text-xs px-4 py-2 rounded-lg flex items-center gap-1.5"><i data-lucide="check" style="width:13px;height:13px;"></i> Accept</button></form>
                        <form method="POST"><input type="hidden" name="action" value="reject_application"><input type="hidden" name="app_id" value="<?= $app['application_id'] ?>">
                          <button type="submit" class="btn-cyan text-xs px-4 py-2 rounded-lg flex items-center gap-1.5" onclick="return confirm('Reject this application?')"><i data-lucide="x" style="width:13px;height:13px;"></i> Reject</button></form>
                      </div>
                    <?php elseif($app['status']==='accepted'): ?>
                      <p style="color:var(--lime);font-size:0.75rem;font-weight:600;" class="flex items-center gap-1 flex-shrink-0"><i data-lucide="check-circle" style="width:13px;height:13px;"></i> Add to batch →</p>
                    <?php endif; ?>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>

        <?php else: /* Student */ ?>
          <h2 class="tab-title">My Applications</h2>
          <?php if (empty($myApplications)): ?>
            <div class="card p-10 text-center"><i data-lucide="send" style="width:48px;height:48px;color:#334155;margin:0 auto 1rem;display:block;"></i><p style="color:#475569;font-weight:600;">No applications yet</p><a href="tutors.php" class="btn-lime inline-block mt-4 px-5 py-2.5 rounded-lg text-sm">Find a Tutor</a></div>
          <?php else: ?>
            <div class="flex flex-col gap-3">
              <?php foreach($myApplications as $app):
                $sb = match($app['status']){
                  'pending' =>['badge-pending','⏳ Pending — waiting for tutor review'],
                  'accepted'=>['badge-lime','✓ Accepted — check your Sessions tab!'],
                  'rejected'=>['','✗ Not accepted'],
                  default=>['','']};
              ?>
                <div class="card p-5">
                  <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                      <p style="font-weight:600;font-family:'Space Grotesk',sans-serif;margin-bottom:0.25rem;"><?= htmlspecialchars($app['tutor_name']) ?></p>
                      <p class="text-slate-400 text-sm mb-2">Applied for: <strong style="color:#e2e8f0;"><?= htmlspecialchars($app['subject']) ?></strong></p>
                      <span class="text-xs px-2.5 py-1 rounded-full <?= $sb[0] ?>"><?= $sb[1] ?></span>
                    </div>
                    <p class="text-slate-600 text-xs"><?= date('d M Y',strtotime($app['created_at'])) ?></p>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        <?php endif; ?>
      </div>

      <!-- ═══ COURSES TAB (tutor only) ═══ -->
      <?php if ($role === 'tutor'): ?>
      <div id="tab-courses" class="tab-pane" style="display:none;">
        <div class="flex items-center justify-between mb-5">
          <h2 class="tab-title" style="margin:0;">My Courses</h2>
          <button onclick="toggleEl('addCourseForm')" class="btn-lime text-sm px-4 py-2 rounded-lg flex items-center gap-1.5">
            <i data-lucide="plus" style="width:15px;height:15px;"></i> Add Course
          </button>
        </div>

        <div id="addCourseForm" style="display:none;margin-bottom:1.5rem;">
          <form method="POST" class="card p-5 flex flex-col gap-4" style="border-color:rgba(163,230,53,0.3);">
            <input type="hidden" name="action" value="add_course">
            <h3 style="font-size:0.9rem;font-weight:600;color:var(--lime);font-family:'Space Grotesk',sans-serif;">Add New Course</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="block text-slate-400 text-sm mb-1.5">Subject Name *</label>
                <input type="text" name="course_subject" class="form-input" placeholder="e.g. Python Programming Basics" required>
              </div>
              <div>
                <label class="block text-slate-400 text-sm mb-1.5">Course Duration *</label>
                <input type="text" name="duration" class="form-input" placeholder="e.g. 1 month, 3 weeks, 45 days" required>
              </div>
            </div>
            <div>
              <label class="block text-slate-400 text-sm mb-1.5">Syllabus / What students will learn *</label>
              <textarea name="syllabus" class="form-input" rows="4" required placeholder="Week 1: Variables, Data types, I/O&#10;Week 2: Loops and conditions&#10;Week 3: Functions and modules&#10;Week 4: Mini project"></textarea>
            </div>
            <div class="flex gap-3">
              <button type="submit" class="btn-lime px-5 py-2.5 rounded-lg text-sm">Save Course</button>
              <button type="button" onclick="toggleEl('addCourseForm')" class="btn-cyan px-5 py-2.5 rounded-lg text-sm">Cancel</button>
            </div>
          </form>
        </div>

        <?php if (empty($myCourses)): ?>
          <div class="card p-10 text-center"><i data-lucide="book-open" style="width:48px;height:48px;color:#334155;margin:0 auto 1rem;display:block;"></i><p style="color:#475569;font-weight:600;">No courses yet</p><p class="text-slate-600 text-sm mt-1">Add courses with their syllabus so students know what they'll learn.</p></div>
        <?php else: ?>
          <div class="flex flex-col gap-4">
            <?php foreach($myCourses as $c): ?>
              <div class="card p-5">
                <div class="flex items-start justify-between gap-3">
                  <div class="flex-1 min-w-0">
                    <div class="flex flex-wrap items-center gap-2 mb-2">
                      <h3 style="font-weight:700;font-family:'Space Grotesk',sans-serif;"><?= htmlspecialchars($c['subject_name']) ?></h3>
                      <span style="background:rgba(163,230,53,0.1);border:1px solid rgba(163,230,53,0.25);color:var(--lime);font-size:0.75rem;font-weight:600;border-radius:999px;padding:2px 10px;">⏱ <?= htmlspecialchars($c['duration']) ?></span>
                    </div>
                    <div style="background:var(--navy-700);border-radius:0.5rem;padding:0.75rem;font-size:0.85rem;color:#94a3b8;line-height:1.65;white-space:pre-wrap;"><?= htmlspecialchars($c['syllabus']) ?></div>
                    <p class="text-slate-600 text-xs mt-2">Added <?= date('d M Y',strtotime($c['created_at'])) ?></p>
                  </div>
                  <form method="POST" class="flex-shrink-0">
                    <input type="hidden" name="action" value="delete_course">
                    <input type="hidden" name="course_id" value="<?= $c['course_id'] ?>">
                    <button type="submit" style="color:#f87171;background:rgba(239,68,68,0.1);border:1px solid rgba(239,68,68,0.2);border-radius:0.5rem;padding:0.4rem 0.7rem;font-size:0.75rem;cursor:pointer;" onclick="return confirm('Delete this course?')">
                      <i data-lucide="trash-2" style="width:13px;height:13px;display:inline;"></i>
                    </button>
                  </form>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>

      <!-- ═══ BATCHES TAB (tutor only) ═══ -->
      <div id="tab-batches" class="tab-pane" style="display:none;">
        <div class="flex items-center justify-between mb-5">
          <h2 class="tab-title" style="margin:0;">Batch Schedules</h2>
          <button onclick="toggleEl('addBatchForm')" class="btn-lime text-sm px-4 py-2 rounded-lg flex items-center gap-1.5">
            <i data-lucide="plus" style="width:15px;height:15px;"></i> Create Batch
          </button>
        </div>

        <div id="addBatchForm" style="display:none;margin-bottom:1.5rem;">
          <form method="POST" class="card p-5 flex flex-col gap-4" style="border-color:rgba(34,211,238,0.3);">
            <input type="hidden" name="action" value="create_batch">
            <h3 style="font-size:0.9rem;font-weight:600;color:var(--cyan);font-family:'Space Grotesk',sans-serif;">New Batch Schedule</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div class="sm:col-span-2">
                <label class="block text-slate-400 text-sm mb-1.5">Batch Name *</label>
                <input type="text" name="batch_name" class="form-input" placeholder="e.g. Python Basics — Batch A (March 2026)" required>
              </div>
              <div>
                <label class="block text-slate-400 text-sm mb-1.5">Linked Course (optional)</label>
                <select name="course_id" class="form-input">
                  <option value="0">-- No specific course --</option>
                  <?php foreach($myCourses as $c): ?>
                    <option value="<?= $c['course_id'] ?>"><?= htmlspecialchars($c['subject_name']) ?> (<?= htmlspecialchars($c['duration']) ?>)</option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div>
                <label class="block text-slate-400 text-sm mb-1.5">Max Students</label>
                <input type="number" name="max_students" class="form-input" value="20" min="1" max="200">
              </div>
              <div>
                <label class="block text-slate-400 text-sm mb-1.5">Session Date *</label>
                <input type="date" name="schedule_date" class="form-input" min="<?= date('Y-m-d') ?>" required>
              </div>
              <div>
                <label class="block text-slate-400 text-sm mb-1.5">Session Time *</label>
                <input type="time" name="schedule_time" class="form-input" required>
              </div>
            </div>
            <div>
              <label class="block text-slate-400 text-sm mb-1.5">Notes / Instructions for Students</label>
              <textarea name="notes" class="form-input" rows="2" placeholder="e.g. Join via Google Meet. Bring notebooks. We'll cover Chapter 3 — Loops."></textarea>
            </div>
            <div class="flex gap-3">
              <button type="submit" class="btn-lime px-5 py-2.5 rounded-lg text-sm">Create Batch</button>
              <button type="button" onclick="toggleEl('addBatchForm')" class="btn-cyan px-5 py-2.5 rounded-lg text-sm">Cancel</button>
            </div>
          </form>
        </div>

        <?php if (empty($myBatches)): ?>
          <div class="card p-10 text-center"><i data-lucide="calendar-x" style="width:48px;height:48px;color:#334155;margin:0 auto 1rem;display:block;"></i><p style="color:#475569;font-weight:600;">No batches created yet</p><p class="text-slate-600 text-sm mt-1">Create a batch to schedule sessions for your students.</p></div>
        <?php else: ?>
          <div class="flex flex-col gap-5">
            <?php foreach($myBatches as $batch):
              $bs = $enrollMap[$batch['batch_id']] ?? [];
              $sc = match($batch['status']){
                'upcoming' =>['badge-cyan','📅 Upcoming'],
                'ongoing'  =>['badge-lime','🟢 Ongoing'],
                'completed'=>['badge-pending','✅ Completed'],
                default=>['','']};
            ?>
              <div class="card p-5">
                <!-- Batch header -->
                <div class="flex flex-wrap items-start justify-between gap-3 mb-4">
                  <div>
                    <div class="flex flex-wrap items-center gap-2 mb-1">
                      <h3 style="font-weight:700;font-family:'Space Grotesk',sans-serif;"><?= htmlspecialchars($batch['batch_name']) ?></h3>
                      <span class="text-xs px-2.5 py-0.5 rounded-full <?= $sc[0] ?>"><?= $sc[1] ?></span>
                    </div>
                    <?php if($batch['course_subject']): ?>
                      <p class="text-slate-400 text-sm">Course: <strong style="color:#e2e8f0;"><?= htmlspecialchars($batch['course_subject']) ?></strong> <span style="color:var(--lime);font-size:0.75rem;">(<?= htmlspecialchars($batch['course_duration']) ?>)</span></p>
                    <?php endif; ?>
                    <div class="flex flex-wrap gap-4 mt-1.5 text-xs text-slate-500">
                      <span class="flex items-center gap-1"><i data-lucide="calendar" style="width:12px;height:12px;color:var(--cyan);"></i><?= date('D, d M Y',strtotime($batch['schedule_date'])) ?></span>
                      <span class="flex items-center gap-1"><i data-lucide="clock" style="width:12px;height:12px;color:var(--cyan);"></i><?= date('h:i A',strtotime($batch['schedule_time'])) ?></span>
                      <span class="flex items-center gap-1"><i data-lucide="users" style="width:12px;height:12px;color:var(--cyan);"></i><?= count($bs) ?>/<?= $batch['max_students'] ?> students</span>
                    </div>
                    <?php if($batch['notes']): ?><p class="text-slate-500 text-xs mt-1.5 italic"><?= htmlspecialchars(substr($batch['notes'],0,120)) ?><?= strlen($batch['notes'])>120?'…':'' ?></p><?php endif; ?>
                  </div>

                  <!-- MARK AS DONE BUTTON -->
                  <?php if ($batch['status'] !== 'completed'): ?>
                    <form method="POST" class="flex-shrink-0">
                      <input type="hidden" name="action" value="mark_batch_done">
                      <input type="hidden" name="batch_id" value="<?= $batch['batch_id'] ?>">
                      <button type="submit"
                        onclick="return confirm('Mark batch as COMPLETED?\n\nAll enrolled students will see their session as ✅ Done.')"
                        style="background:rgba(163,230,53,0.1);border:2px solid rgba(163,230,53,0.4);color:var(--lime);font-weight:700;font-family:'Space Grotesk',sans-serif;font-size:0.8rem;border-radius:0.75rem;padding:0.6rem 1.1rem;cursor:pointer;transition:all 0.2s;display:flex;align-items:center;gap:0.5rem;"
                        onmouseover="this.style.background='rgba(163,230,53,0.2)';this.style.boxShadow='0 0 16px rgba(163,230,53,0.3)'"
                        onmouseout="this.style.background='rgba(163,230,53,0.1)';this.style.boxShadow='none'">
                        <i data-lucide="check-circle-2" style="width:16px;height:16px;"></i>
                        Mark as Done
                      </button>
                    </form>
                  <?php else: ?>
                    <div style="color:var(--lime);font-size:0.8rem;font-weight:600;display:flex;align-items:center;gap:0.5rem;flex-shrink:0;">
                      <i data-lucide="check-circle-2" style="width:15px;height:15px;"></i> Completed
                    </div>
                  <?php endif; ?>
                </div>

                <!-- Enrolled Students -->
                <div style="border-top:1px solid rgba(255,255,255,0.06);padding-top:1rem;">
                  <p style="font-size:0.75rem;font-weight:600;color:var(--cyan);letter-spacing:0.06em;text-transform:uppercase;margin-bottom:0.75rem;">Enrolled Students (<?= count($bs) ?>)</p>
                  <?php if (!empty($bs)): ?>
                    <div class="flex flex-wrap gap-2 mb-3">
                      <?php foreach($bs as $enr): ?>
                        <div style="background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.08);border-radius:0.5rem;padding:0.35rem 0.875rem;display:flex;align-items:center;gap:0.625rem;font-size:0.8rem;">
                          <div style="width:22px;height:22px;border-radius:50%;background:linear-gradient(135deg,var(--navy-600),var(--navy-500));display:flex;align-items:center;justify-content:center;font-size:0.65rem;font-weight:700;color:var(--lime);flex-shrink:0;"><?= strtoupper(substr($enr['student_name'],0,1)) ?></div>
                          <span style="color:#e2e8f0;"><?= htmlspecialchars($enr['student_name']) ?></span>
                          <?php if($enr['status']==='completed'): ?>
                            <span style="color:var(--lime);font-size:0.7rem;font-weight:600;">✅ Done</span>
                          <?php else: ?>
                            <span style="color:var(--cyan);font-size:0.7rem;">📚 Scheduled</span>
                          <?php endif; ?>
                        </div>
                      <?php endforeach; ?>
                    </div>
                  <?php else: ?>
                    <p class="text-slate-600 text-sm mb-3">No students enrolled yet.</p>
                  <?php endif; ?>

                  <!-- Add Student -->
                  <?php if ($batch['status'] !== 'completed' && !empty($acceptedStudents)): ?>
                    <details style="margin-top:0.25rem;">
                      <summary style="cursor:pointer;color:var(--cyan);font-size:0.8rem;font-weight:600;list-style:none;display:flex;align-items:center;gap:0.5rem;width:fit-content;">
                        <i data-lucide="user-plus" style="width:14px;height:14px;"></i> Add Student to this Batch
                      </summary>
                      <form method="POST" style="margin-top:0.75rem;display:flex;gap:0.75rem;flex-wrap:wrap;align-items:flex-end;">
                        <input type="hidden" name="action" value="enroll_student">
                        <input type="hidden" name="batch_id" value="<?= $batch['batch_id'] ?>">
                        <input type="hidden" name="app_id" id="aid_<?= $batch['batch_id'] ?>" value="0">
                        <div style="flex:1;min-width:220px;">
                          <select class="form-input text-sm"
                            onchange="document.getElementById('aid_<?= $batch['batch_id'] ?>').value=this.options[this.selectedIndex].dataset.appid||0;document.getElementById('sid_<?= $batch['batch_id'] ?>').value=this.value;">
                            <option value="">-- Select accepted student --</option>
                            <?php foreach($acceptedStudents as $as): ?>
                              <option value="<?= $as['student_id'] ?>" data-appid="<?= $as['application_id'] ?>">
                                <?= htmlspecialchars($as['full_name']) ?> (<?= htmlspecialchars($as['subject']) ?>)
                              </option>
                            <?php endforeach; ?>
                          </select>
                        </div>
                        <input type="hidden" name="student_id" id="sid_<?= $batch['batch_id'] ?>" value="0">
                        <button type="submit" class="btn-cyan text-sm px-4 py-2 rounded-lg">Add</button>
                      </form>
                    </details>
                  <?php elseif ($batch['status'] !== 'completed'): ?>
                    <p class="text-slate-600 text-xs mt-1">Accept student applications first to add them here.</p>
                  <?php endif; ?>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
      <?php endif; /* end tutor tabs */ ?>

      <!-- ═══ SESSIONS TAB (student only) ═══ -->
      <?php if ($role === 'student'): ?>
      <div id="tab-sessions" class="tab-pane" style="display:none;">
        <h2 class="tab-title">My Sessions</h2>
        <?php if (empty($mySessions)): ?>
          <div class="card p-10 text-center">
            <i data-lucide="calendar-days" style="width:48px;height:48px;color:#334155;margin:0 auto 1rem;display:block;"></i>
            <p style="color:#475569;font-weight:600;">No sessions yet</p>
            <p class="text-slate-600 text-sm mt-1">Once your tutor accepts your application and schedules a batch, it will appear here with full details.</p>
          </div>
        <?php else: ?>
          <div class="flex flex-col gap-4">
            <?php foreach($mySessions as $sess):
              $isDone = $sess['batch_status']==='completed' || $sess['enrollment_status']==='completed';
            ?>
              <div class="card p-5" style="<?= $isDone?'border-color:rgba(163,230,53,0.25);':'' ?>">
                <div class="flex flex-wrap items-start justify-between gap-3 mb-4">
                  <div>
                    <div class="flex flex-wrap items-center gap-2 mb-1">
                      <h3 style="font-weight:700;font-family:'Space Grotesk',sans-serif;"><?= htmlspecialchars($sess['batch_name']) ?></h3>
                      <?php if($isDone): ?>
                        <span class="badge-lime text-xs px-2.5 py-0.5 rounded-full">✅ Completed</span>
                      <?php elseif($sess['batch_status']==='upcoming'): ?>
                        <span class="badge-cyan text-xs px-2.5 py-0.5 rounded-full">📅 Upcoming</span>
                      <?php else: ?>
                        <span class="badge-lime text-xs px-2.5 py-0.5 rounded-full">🟢 Ongoing</span>
                      <?php endif; ?>
                    </div>
                    <p class="text-slate-400 text-sm">Tutor: <strong style="color:white;"><?= htmlspecialchars($sess['tutor_name']) ?></strong></p>
                    <?php if($sess['subject_name']): ?>
                      <p class="text-slate-400 text-sm">Course: <strong style="color:#e2e8f0;"><?= htmlspecialchars($sess['subject_name']) ?></strong>
                        <?php if($sess['duration']): ?> <span style="color:var(--lime);font-size:0.75rem;">— <?= htmlspecialchars($sess['duration']) ?></span><?php endif; ?>
                      </p>
                    <?php endif; ?>
                  </div>
                  <?php if($isDone): ?>
                    <div style="background:rgba(163,230,53,0.1);border:2px solid rgba(163,230,53,0.35);border-radius:0.875rem;padding:0.75rem 1.25rem;text-align:center;flex-shrink:0;">
                      <p style="font-size:1.25rem;">✅</p>
                      <p style="color:var(--lime);font-weight:700;font-size:0.8rem;font-family:'Space Grotesk',sans-serif;">SESSION<br>COMPLETED</p>
                    </div>
                  <?php endif; ?>
                </div>

                <!-- Info Grid -->
                <div style="background:var(--navy-700);border-radius:0.75rem;padding:1rem;display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:0.875rem;margin-bottom:<?= $sess['notes']?'0.875rem':'0' ?>;">
                  <div class="flex items-center gap-2">
                    <i data-lucide="calendar" style="width:15px;height:15px;color:var(--cyan);flex-shrink:0;"></i>
                    <div><p class="text-slate-500 text-xs">Date</p><p style="font-weight:600;font-size:0.875rem;"><?= date('D, d M Y',strtotime($sess['schedule_date'])) ?></p></div>
                  </div>
                  <div class="flex items-center gap-2">
                    <i data-lucide="clock" style="width:15px;height:15px;color:var(--cyan);flex-shrink:0;"></i>
                    <div><p class="text-slate-500 text-xs">Time</p><p style="font-weight:600;font-size:0.875rem;"><?= date('h:i A',strtotime($sess['schedule_time'])) ?></p></div>
                  </div>
                  <div class="flex items-center gap-2">
                    <i data-lucide="users" style="width:15px;height:15px;color:var(--cyan);flex-shrink:0;"></i>
                    <div><p class="text-slate-500 text-xs">Format</p><p style="font-weight:600;font-size:0.875rem;">Group Batch</p></div>
                  </div>
                </div>

                <?php if($sess['notes']): ?>
                  <div style="background:rgba(34,211,238,0.06);border:1px solid rgba(34,211,238,0.15);border-radius:0.625rem;padding:0.75rem 1rem;">
                    <p style="color:var(--cyan);font-size:0.75rem;font-weight:600;margin-bottom:0.25rem;">📌 Notes from your tutor:</p>
                    <p class="text-slate-400 text-sm"><?= htmlspecialchars($sess['notes']) ?></p>
                  </div>
                <?php endif; ?>
                <p class="text-slate-600 text-xs mt-2.5">Enrolled <?= date('d M Y',strtotime($sess['enrolled_at'])) ?></p>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
      <?php endif; ?>

      <!-- ═══ PROFILE TAB ═══ -->
      <div id="tab-profile" class="tab-pane" style="display:none;">
        <h2 class="tab-title">Your Profile</h2>
        <div class="card p-6">
          <div class="flex items-center gap-4 mb-5">
            <div style="width:60px;height:60px;border-radius:50%;background:linear-gradient(135deg,var(--navy-600),var(--navy-500));display:flex;align-items:center;justify-content:center;border:2px solid rgba(163,230,53,0.35);">
              <span style="font-family:'Space Grotesk',sans-serif;font-weight:700;color:var(--lime);font-size:1.4rem;"><?= strtoupper(substr($userInfo['full_name'],0,1)) ?></span>
            </div>
            <div>
              <h3 style="font-size:1.1rem;font-weight:700;font-family:'Space Grotesk',sans-serif;"><?= htmlspecialchars($userInfo['full_name']) ?></h3>
              <p class="text-slate-400 text-sm"><?= htmlspecialchars($userInfo['email']) ?></p>
            </div>
          </div>
          <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(190px,1fr));gap:1rem;margin-bottom:1.5rem;">
            <?php
            $pf = [['phone','Phone',$userInfo['phone']??'Not set'],['map-pin','City',$userInfo['city']??'Not set'],['calendar','Joined',date('d M Y',strtotime($userInfo['created_at']))]];
            if($role==='tutor'&&$tutorInfo){ $pf[]=[ 'indian-rupee','Rate','₹'.number_format($tutorInfo['hourly_rate']).'/hr']; $pf[]=['briefcase','Experience',($tutorInfo['experience_years']??0).' years']; $pf[]=['graduation-cap','Qualification',$tutorInfo['qualification']]; }
            foreach($pf as $f):
            ?>
              <div style="background:var(--navy-700);border-radius:0.625rem;padding:0.875rem;" class="flex items-start gap-2.5">
                <i data-lucide="<?= $f[0] ?>" style="width:14px;height:14px;color:var(--cyan);flex-shrink:0;margin-top:2px;"></i>
                <div><p class="text-slate-500 text-xs mb-0.5"><?= $f[1] ?></p><p style="font-size:0.875rem;color:#e2e8f0;"><?= htmlspecialchars($f[2]) ?></p></div>
              </div>
            <?php endforeach; ?>
          </div>
          <a href="edit-profile.php" class="btn-lime inline-flex items-center gap-2 px-5 py-2.5 rounded-lg text-sm">
            <i data-lucide="edit-3" style="width:15px;height:15px;"></i> Edit Profile
          </a>
        </div>
      </div>

    </div><!-- /main -->
  </div><!-- /grid -->
</div>

<style>
.tab-title { font-size:1.1rem;font-weight:700;font-family:'Space Grotesk',sans-serif;margin-bottom:1.25rem; }
.tab-btn.active-tab { background:rgba(163,230,53,0.1)!important;color:var(--lime)!important;border-left:3px solid var(--lime); }
</style>
<script>
function showTab(n){
  document.querySelectorAll('.tab-pane').forEach(e=>e.style.display='none');
  document.querySelectorAll('.tab-btn').forEach(e=>e.classList.remove('active-tab'));
  var p=document.getElementById('tab-'+n),b=document.getElementById('btn-'+n);
  if(p) p.style.display='block';
  if(b) b.classList.add('active-tab');
  history.replaceState(null,'','?tab='+n);
}
function toggleEl(id){ var e=document.getElementById(id); if(e) e.style.display=e.style.display==='none'?'block':'none'; }
showTab('<?= addslashes($activeTab) ?>');
lucide.createIcons();
</script>

<?php include 'partials/footer.php'; ?>