<?php
require_once 'db.php';
requireLogin();
$pageTitle = 'Book a Session – FindLearnGlow';

// ─── Load tutor
$tutor_id = (int)($_GET['tutor_id'] ?? 0);
if (!$tutor_id) {
    setFlash('error', 'No tutor selected.');
    redirect('tutors.php');
}

$tutorStmt = $conn->prepare("
    SELECT t.tutor_id, u.full_name, t.subjects, t.hourly_rate, t.rating, t.total_reviews,
           t.experience_years, t.qualification, u.city, u.profile_photo
    FROM tutors t
    JOIN users u ON t.user_id = u.user_id
    WHERE t.tutor_id = ? AND t.verification_status = 'approved'
");
$tutorStmt->bind_param('i', $tutor_id);
$tutorStmt->execute();
$tutorResult = $tutorStmt->get_result();
if ($tutorResult->num_rows === 0) {
    setFlash('error', 'Tutor not found or not yet verified.');
    redirect('tutors.php');
}
$tutor = $tutorResult->fetch_assoc();
$tutorStmt->close();

$errors = [];

// ─────────────────────────────────────────────────────────────
// FORM PROCESSING
// ─────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $session_date   = sanitize($conn, $_POST['session_date']   ?? '');
    $session_time   = sanitize($conn, $_POST['session_time']   ?? '');
    $duration_hours = max(1, min(8, (int)($_POST['duration_hours'] ?? 1)));
    $subject_booked = sanitize($conn, $_POST['subject_booked'] ?? '');
    $session_mode   = in_array($_POST['session_mode'] ?? '', ['online','home']) ? $_POST['session_mode'] : 'online';
    $address        = sanitize($conn, $_POST['address']        ?? '');
    $special_notes  = sanitize($conn, $_POST['special_notes']  ?? '');
    $payment_method = in_array($_POST['payment_method'] ?? '', ['upi','card','cod']) ? $_POST['payment_method'] : 'upi';
    $upi_txn_id     = sanitize($conn, $_POST['upi_txn_id']     ?? '');

    // Validation
    if (empty($session_date))   $errors[] = 'Please pick a session date.';
    if (empty($session_time))   $errors[] = 'Please pick a session time.';
    if (empty($subject_booked)) $errors[] = 'Please select a subject.';
    if ($session_date && strtotime($session_date) < strtotime('today')) {
        $errors[] = 'Session date must be today or in the future.';
    }
    if ($payment_method === 'upi' && empty($upi_txn_id)) {
        $errors[] = 'Please enter your UPI Transaction ID.';
    }
    if ($payment_method === 'upi' && $upi_txn_id && !preg_match('/^[A-Za-z0-9\-_\.]{6,50}$/', $upi_txn_id)) {
        $errors[] = 'UPI Transaction ID appears invalid.';
    }
    if ($session_mode === 'home' && empty($address)) {
        $errors[] = 'Please provide your address for home sessions.';
    }

    // Compute amounts
    $subtotal     = (float)$tutor['hourly_rate'] * $duration_hours;
    $platform_fee = round($subtotal * 0.05, 2);
    $total_amount = $subtotal + $platform_fee;

    // Insert booking
    if (empty($errors)) {
        /*
         * UPI  → 'pending_verification'  (admin must verify the transaction ID)
         * Card → 'confirmed'             (mock instant approval)
         * COD  → 'pending'              (collect on day)
         */
        $booking_status = match($payment_method) {
            'upi'   => 'pending_verification',
            'card'  => 'confirmed',
            default => 'pending',
        };

        // Type string: ii=student+tutor, sssiss=text fields, ddd=amounts, sss=payment+status
        // Total 15 bind params: i i s s s i s s s d d d s s s
        $stmt = $conn->prepare("
            INSERT INTO bookings
                (student_id, tutor_id, subject_booked, session_date, session_time,
                 duration_hours, session_mode, address, special_notes,
                 amount, platform_fee, total_amount,
                 payment_method, upi_txn_id, status)
            VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)
        ");

        $stmt->bind_param(
    'iisssisssdddsss',
    $_SESSION['user_id'],  // i - student_id
    $tutor_id,             // i - tutor_id
    $subject_booked,       // s - subject
    $session_date,         // s - date
    $session_time,         // s - time
    $duration_hours,       // i - duration
    $session_mode,         // s - mode
    $address,              // s - address
    $special_notes,        // s - notes
    $subtotal,             // d - amount
    $platform_fee,         // d - platform fee
    $total_amount,         // d - total
    $payment_method,       // s - payment method
    $upi_txn_id,           // s - UPI txn id
    $booking_status        // s - status
);

        if ($stmt->execute()) {
            $booking_id = $conn->insert_id;
            $stmt->close();

            $msg = match($payment_method) {
                'upi'   => "Booking #{$booking_id} submitted! Your UPI payment (Ref: {$upi_txn_id}) is under review — confirmation within 2 hours.",
                'card'  => "Booking #{$booking_id} confirmed! Payment received successfully.",
                default => "Booking #{$booking_id} placed! Keep ₹" . number_format($total_amount, 0) . " ready for your tutor.",
            };
            setFlash('success', $msg);
            redirect('dashboard.php');
        } else {
            $errors[] = 'Could not save booking: ' . $stmt->error;
            $stmt->close();
        }
    }
}

$timeSlots = ['08:00','09:00','10:00','11:00','12:00','13:00','14:00','15:00','16:00','17:00','18:00','19:00','20:00'];
$pm = $_POST['payment_method'] ?? 'upi';

include 'partials/header.php';
?>

<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

  <!-- Breadcrumb -->
  <div class="flex items-center gap-2 text-sm text-slate-500 mb-6">
    <a href="tutors.php" style="color:var(--cyan);">Find Tutors</a>
    <i data-lucide="chevron-right" style="width:14px;height:14px;"></i>
    <a href="tutor-profile.php?id=<?= $tutor_id ?>" style="color:var(--cyan);"><?= htmlspecialchars($tutor['full_name']) ?></a>
    <i data-lucide="chevron-right" style="width:14px;height:14px;"></i>
    <span style="color:white;">Book Session</span>
  </div>

  <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

    <!-- ─── FORM ─── -->
    <div class="lg:col-span-2">
      <h1 style="font-size:1.6rem;font-weight:700;margin-bottom:0.25rem;">Book a Session</h1>
      <p class="text-slate-400 text-sm mb-7">With <strong style="color:white;"><?= htmlspecialchars($tutor['full_name']) ?></strong></p>

      <?php if (!empty($errors)): ?>
        <div style="background:rgba(239,68,68,0.1);border:1px solid rgba(239,68,68,0.3);border-radius:0.75rem;padding:1rem 1.25rem;margin-bottom:1.5rem;">
          <div class="flex items-center gap-2 mb-1.5">
            <i data-lucide="alert-circle" style="width:16px;height:16px;color:#f87171;flex-shrink:0;"></i>
            <span style="color:#f87171;font-weight:600;font-size:0.875rem;">Please fix the following errors:</span>
          </div>
          <?php foreach($errors as $e): ?>
            <p style="color:#fca5a5;font-size:0.825rem;padding-left:1.5rem;">• <?= htmlspecialchars($e) ?></p>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <form method="POST" action="checkout.php?tutor_id=<?= $tutor_id ?>" class="flex flex-col gap-6">

        <!-- Session Details -->
        <div class="card p-6 flex flex-col gap-4">
          <h2 style="font-size:1rem;font-weight:600;font-family:'Space Grotesk',sans-serif;color:var(--cyan);">📅 Session Details</h2>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-slate-400 text-sm mb-1.5">Subject *</label>
              <select name="subject_booked" class="form-input" required>
                <option value="">-- Select Subject --</option>
                <?php foreach(explode(',', $tutor['subjects']) as $sub): $sub=trim($sub); ?>
                  <option value="<?= htmlspecialchars($sub) ?>" <?= ($_POST['subject_booked']??'')===$sub?'selected':'' ?>><?= htmlspecialchars($sub) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div>
              <label class="block text-slate-400 text-sm mb-1.5">Duration *</label>
              <select name="duration_hours" id="durationSelect" class="form-input" onchange="updateSummary()" required>
                <?php for($h=1;$h<=8;$h++): ?>
                  <option value="<?= $h ?>" <?= (int)($_POST['duration_hours']??1)===$h?'selected':'' ?>><?= $h ?> hour<?= $h>1?'s':'' ?></option>
                <?php endfor; ?>
              </select>
            </div>
            <div>
              <label class="block text-slate-400 text-sm mb-1.5">Session Date *</label>
              <input type="date" name="session_date" class="form-input" min="<?= date('Y-m-d') ?>"
                     value="<?= htmlspecialchars($_POST['session_date']??'') ?>" required>
            </div>
            <div>
              <label class="block text-slate-400 text-sm mb-1.5">Session Time *</label>
              <select name="session_time" class="form-input" required>
                <option value="">-- Select Time --</option>
                <?php foreach($timeSlots as $slot): ?>
                  <option value="<?= $slot ?>" <?= ($_POST['session_time']??'')===$slot?'selected':'' ?>><?= $slot ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <div>
            <label class="block text-slate-400 text-sm mb-2">Session Mode</label>
            <div class="flex gap-4">
              <?php foreach(['online'=>'💻 Online','home'=>'🏠 At Home'] as $v=>$l): ?>
                <label style="display:flex;align-items:center;gap:0.5rem;cursor:pointer;padding:0.65rem 1rem;border-radius:0.5rem;border:1px solid rgba(255,255,255,0.1);flex:1;transition:border-color 0.2s;">
                  <input type="radio" name="session_mode" value="<?= $v ?>"
                         <?= ($_POST['session_mode']??'online')===$v?'checked':'' ?>
                         onchange="toggleAddress()" style="accent-color:var(--lime);">
                  <span class="text-sm"><?= $l ?></span>
                </label>
              <?php endforeach; ?>
            </div>
          </div>

          <div id="addressField" style="display:<?= ($_POST['session_mode']??'online')==='home'?'block':'none' ?>">
            <label class="block text-slate-400 text-sm mb-1.5">Your Address *</label>
            <textarea name="address" class="form-input" rows="2" placeholder="Street, Area, City, Pincode"><?= htmlspecialchars($_POST['address']??'') ?></textarea>
          </div>

          <div>
            <label class="block text-slate-400 text-sm mb-1.5">Special Notes (optional)</label>
            <textarea name="special_notes" class="form-input" rows="2" placeholder="e.g. Focus on integration…"><?= htmlspecialchars($_POST['special_notes']??'') ?></textarea>
          </div>
        </div>

        <!-- Payment -->
        <div class="card p-6 flex flex-col gap-5">
          <h2 style="font-size:1rem;font-weight:600;font-family:'Space Grotesk',sans-serif;color:var(--lime);">💳 Payment Method</h2>

          <div class="flex flex-col gap-3">
            <!-- UPI -->
            <label class="pay-opt" style="display:flex;align-items:flex-start;gap:1rem;padding:1rem 1.25rem;border-radius:0.75rem;border:1.5px solid <?= $pm==='upi'?'rgba(163,230,53,0.4)':'rgba(255,255,255,0.08)' ?>;background:<?= $pm==='upi'?'rgba(163,230,53,0.05)':'transparent' ?>;cursor:pointer;">
              <input type="radio" name="payment_method" value="upi" <?= $pm==='upi'?'checked':'' ?> onchange="showPayment('upi')" style="accent-color:var(--lime);margin-top:3px;flex-shrink:0;">
              <div>
                <div class="flex items-center gap-2 mb-0.5">
                  <span style="font-weight:600;font-family:'Space Grotesk',sans-serif;">UPI</span>
                  <span class="badge-lime text-xs px-2 py-0.5 rounded-full">Recommended</span>
                </div>
                <p class="text-slate-500 text-xs">Google Pay · PhonePe · Paytm · BHIM</p>
              </div>
            </label>

            <!-- Card -->
            <label class="pay-opt" style="display:flex;align-items:flex-start;gap:1rem;padding:1rem 1.25rem;border-radius:0.75rem;border:1.5px solid <?= $pm==='card'?'rgba(163,230,53,0.4)':'rgba(255,255,255,0.08)' ?>;background:<?= $pm==='card'?'rgba(163,230,53,0.05)':'transparent' ?>;cursor:pointer;">
              <input type="radio" name="payment_method" value="card" <?= $pm==='card'?'checked':'' ?> onchange="showPayment('card')" style="accent-color:var(--lime);margin-top:3px;flex-shrink:0;">
              <div>
                <div style="font-weight:600;font-family:'Space Grotesk',sans-serif;" class="mb-0.5">Credit / Debit Card</div>
                <p class="text-slate-500 text-xs">Visa · Mastercard · RuPay</p>
              </div>
            </label>

            <!-- COD -->
            <label class="pay-opt" style="display:flex;align-items:flex-start;gap:1rem;padding:1rem 1.25rem;border-radius:0.75rem;border:1.5px solid <?= $pm==='cod'?'rgba(163,230,53,0.4)':'rgba(255,255,255,0.08)' ?>;background:<?= $pm==='cod'?'rgba(163,230,53,0.05)':'transparent' ?>;cursor:pointer;">
              <input type="radio" name="payment_method" value="cod" <?= $pm==='cod'?'checked':'' ?> onchange="showPayment('cod')" style="accent-color:var(--lime);margin-top:3px;flex-shrink:0;">
              <div>
                <div style="font-weight:600;font-family:'Space Grotesk',sans-serif;" class="mb-0.5">Pay at Session</div>
                <p class="text-slate-500 text-xs">Pay cash directly to your tutor.</p>
              </div>
            </label>
          </div>

          <!-- UPI instructions + transaction field -->
          <div id="upiBlock" style="display:<?= $pm==='upi'?'block':'none' ?>">
            <div style="background:var(--navy-700);border:1px solid rgba(163,230,53,0.15);border-radius:0.75rem;padding:1.25rem;">
              <h3 style="font-size:0.875rem;font-weight:600;color:var(--lime);margin-bottom:1rem;">UPI Payment Steps</h3>
              <ol class="text-slate-400 text-sm leading-relaxed mb-5" style="list-style:decimal;padding-left:1.25rem;">
                <li>Open Google Pay / PhonePe / Paytm / BHIM on your phone.</li>
                <li>Send <strong style="color:white;" id="upiAmountDisplay">₹<?= number_format($tutor['hourly_rate'] + round($tutor['hourly_rate']*0.05)) ?></strong> to UPI ID: <strong style="color:var(--lime);">findlearnglow@upi</strong></li>
                <li>Copy the 12-digit UTR / Transaction ID from the success screen.</li>
                <li>Paste it below — your booking will be confirmed within 2 hours.</li>
              </ol>

              <label class="block text-slate-400 text-sm mb-1.5">UPI Transaction ID / UTR *</label>
              <input type="text" name="upi_txn_id" id="upiTxnInput" class="form-input"
                     placeholder="e.g. 412345678912 or T24092612ABCDE"
                     value="<?= htmlspecialchars($_POST['upi_txn_id']??'') ?>"
                     maxlength="50" <?= $pm==='upi'?'required':'' ?>>
              <p class="text-slate-600 text-xs mt-1.5">Find this under "Transaction Details" in your UPI app after payment.</p>
            </div>
          </div>

          <!-- Card UI (demo only) -->
          <div id="cardBlock" style="display:<?= $pm==='card'?'block':'none' ?>">
            <div style="background:var(--navy-700);border:1px solid rgba(255,255,255,0.08);border-radius:0.75rem;padding:1.25rem;display:flex;flex-direction:column;gap:1rem;">
              <div>
                <label class="block text-slate-400 text-sm mb-1.5">Card Number</label>
                <input type="text" class="form-input" placeholder="1234 5678 9012 3456" maxlength="19"
                       oninput="this.value=this.value.replace(/\D/g,'').replace(/(.{4})/g,'$1 ').trim()">
              </div>
              <div class="grid grid-cols-2 gap-4">
                <div><label class="block text-slate-400 text-sm mb-1.5">Expiry</label><input type="text" class="form-input" placeholder="MM/YY" maxlength="5"></div>
                <div><label class="block text-slate-400 text-sm mb-1.5">CVV</label><input type="password" class="form-input" placeholder="•••" maxlength="4"></div>
              </div>
              <p style="color:var(--cyan);font-size:0.75rem;" class="flex items-center gap-1.5">
                <i data-lucide="info" style="width:13px;height:13px;"></i>
                Demo only — integrate Razorpay/PayU for live card payments.
              </p>
            </div>
          </div>

          <!-- COD note -->
          <div id="codBlock" style="display:<?= $pm==='cod'?'block':'none' ?>">
            <div style="background:rgba(251,191,36,0.08);border:1px solid rgba(251,191,36,0.2);border-radius:0.75rem;padding:1rem;">
              <p style="color:#fbbf24;font-size:0.875rem;" class="flex items-center gap-2">
                <i data-lucide="banknote" style="width:16px;height:16px;flex-shrink:0;"></i>
                Keep exact cash ready. Tutor collects at the session start.
              </p>
            </div>
          </div>
        </div>

        <button type="submit" class="btn-lime w-full py-4 rounded-xl text-base glow-pulse flex items-center justify-center gap-2">
          <i data-lucide="calendar-check" style="width:20px;height:20px;"></i>
          Confirm Booking
        </button>

        <p class="text-slate-600 text-xs text-center">
          By confirming you agree to our <a href="#" style="color:var(--cyan);">Cancellation Policy</a>.
          Full refund for cancellations 24+ hrs before the session.
        </p>
      </form>
    </div>

    <!-- ─── PRICE SUMMARY SIDEBAR ─── -->
    <div>
      <div style="position:sticky;top:80px;">
        <div class="card p-5 mb-4">
          <div class="flex items-center gap-3 mb-3">
            <div style="width:52px;height:52px;border-radius:50%;overflow:hidden;border:2px solid rgba(163,230,53,0.3);flex-shrink:0;">
              <?php if ($tutor['profile_photo']): ?>
                <img src="uploads/<?= htmlspecialchars($tutor['profile_photo']) ?>" alt="" style="width:100%;height:100%;object-fit:cover;">
              <?php else: ?>
                <div style="width:100%;height:100%;background:linear-gradient(135deg,#1a2744,#111d35);display:flex;align-items:center;justify-content:center;">
                  <span style="font-family:'Space Grotesk',sans-serif;font-weight:700;color:var(--lime);font-size:1.15rem;"><?= strtoupper(substr($tutor['full_name'],0,1)) ?></span>
                </div>
              <?php endif; ?>
            </div>
            <div>
              <p style="font-weight:600;font-family:'Space Grotesk',sans-serif;"><?= htmlspecialchars($tutor['full_name']) ?></p>
              <p class="text-slate-500 text-xs mt-0.5 flex items-center gap-1"><span class="star" style="font-size:0.8rem;">★</span> <?= number_format($tutor['rating']??0,1) ?> (<?= $tutor['total_reviews']??0 ?>)</p>
            </div>
          </div>
          <div class="flex flex-wrap gap-1.5">
            <?php foreach(explode(',',$tutor['subjects']) as $s): ?>
              <span class="badge-cyan text-xs px-2 py-0.5 rounded-full"><?= htmlspecialchars(trim($s)) ?></span>
            <?php endforeach; ?>
          </div>
        </div>

        <div class="card p-5 mb-4">
          <h3 style="font-size:0.9rem;font-weight:600;font-family:'Space Grotesk',sans-serif;margin-bottom:1.25rem;">Price Summary</h3>
          <div class="flex flex-col gap-2.5 text-sm">
            <div class="flex justify-between"><span class="text-slate-400">Rate</span><span>₹<?= number_format($tutor['hourly_rate']) ?>/hr</span></div>
            <div class="flex justify-between"><span class="text-slate-400">Duration</span><span id="sumDuration">1 hour</span></div>
            <div class="flex justify-between"><span class="text-slate-400">Subtotal</span><span id="sumSubtotal">₹<?= number_format($tutor['hourly_rate']) ?></span></div>
            <div class="flex justify-between"><span class="text-slate-400">Platform fee (5%)</span><span id="sumFee">₹<?= number_format(round($tutor['hourly_rate']*0.05)) ?></span></div>
            <div style="height:1px;background:rgba(255,255,255,0.07);"></div>
            <div class="flex justify-between" style="font-size:1.05rem;">
              <span style="font-weight:700;">Total</span>
              <span id="sumTotal" style="color:var(--lime);font-weight:700;font-family:'Space Grotesk',sans-serif;">₹<?= number_format($tutor['hourly_rate']+round($tutor['hourly_rate']*0.05)) ?></span>
            </div>
          </div>
        </div>

        <div class="flex flex-col gap-2">
          <?php foreach([['shield-check','Verified Tutor','Background checked'],['refresh-cw','Free Cancellation','24hrs before session'],['headphones','24/7 Support','We\'re here']] as $b): ?>
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

<script>
const RATE = <?= (float)$tutor['hourly_rate'] ?>;
function fmt(n){ return '₹'+Math.round(n).toLocaleString('en-IN'); }

function updateSummary(){
  const d=parseInt(document.getElementById('durationSelect').value)||1;
  const sub=RATE*d, fee=Math.round(sub*0.05), tot=sub+fee;
  document.getElementById('sumDuration').textContent=d+' hour'+(d>1?'s':'');
  document.getElementById('sumSubtotal').textContent=fmt(sub);
  document.getElementById('sumFee').textContent=fmt(fee);
  document.getElementById('sumTotal').textContent=fmt(tot);
  const u=document.getElementById('upiAmountDisplay'); if(u) u.textContent=fmt(tot);
}

function showPayment(m){
  ['upiBlock','cardBlock','codBlock'].forEach(id=>{
    const el=document.getElementById(id); if(el) el.style.display='none';
  });
  const upi=document.getElementById('upiTxnInput');
  if(upi) upi.removeAttribute('required');
  const blk={upi:'upiBlock',card:'cardBlock',cod:'codBlock'};
  const el=document.getElementById(blk[m]); if(el) el.style.display='block';
  if(m==='upi'&&upi) upi.setAttribute('required','');
  document.querySelectorAll('.pay-opt').forEach(el=>{
    const r=el.querySelector('input[type=radio]');
    el.style.borderColor=r.checked?'rgba(163,230,53,0.4)':'rgba(255,255,255,0.08)';
    el.style.background=r.checked?'rgba(163,230,53,0.05)':'transparent';
  });
}

function toggleAddress(){
  const h=document.querySelector('input[name=session_mode]:checked')?.value==='home';
  document.getElementById('addressField').style.display=h?'block':'none';
}

updateSummary();
lucide.createIcons();
</script>

<?php include 'partials/footer.php'; ?>