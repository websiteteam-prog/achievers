<?php
/* =========================================================
   schedule_appointment.php
   Achiever's Castle - Schedule Appointment
   Flow:
     Step 1) Branch select + Day & Time
     Step 2) Child + Parent/Contact Info
     Step 3) Review & Confirm  -> save + 2 emails (branch admin + parent)
   Double-booking prevention: same branch+date+slot dobara book nahi hoga.
   ========================================================= */

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/PHPMailer.php';
require 'PHPMailer/SMTP.php';
require 'PHPMailer/Exception.php';

include 'db_config.php';

/* ============ HANDLE FINAL SUBMIT ============ */
if (isset($_POST['confirm_appointment'])) {

    $branch_id   = (int) ($_POST['branch_id'] ?? 0);

    $appointment_date = mysqli_real_escape_string($conn, trim($_POST['appointment_date'] ?? ''));
    $time_slot        = mysqli_real_escape_string($conn, trim($_POST['time_slot'] ?? ''));
    $mode             = mysqli_real_escape_string($conn, trim($_POST['mode'] ?? 'In Center'));

    $child_first = mysqli_real_escape_string($conn, trim($_POST['child_first_name'] ?? ''));
    $child_last  = mysqli_real_escape_string($conn, trim($_POST['child_last_name'] ?? ''));
    $child_grade = mysqli_real_escape_string($conn, trim($_POST['child_grade'] ?? ''));
    $child_subj  = mysqli_real_escape_string($conn, trim($_POST['child_subject'] ?? ''));

    $p_first = mysqli_real_escape_string($conn, trim($_POST['parent_first_name'] ?? ''));
    $p_last  = mysqli_real_escape_string($conn, trim($_POST['parent_last_name'] ?? ''));
    $p_email = mysqli_real_escape_string($conn, trim($_POST['parent_email'] ?? ''));
    $p_phone = mysqli_real_escape_string($conn, trim($_POST['parent_phone'] ?? ''));

    $notes        = mysqli_real_escape_string($conn, trim($_POST['notes'] ?? ''));
    $sms_consent  = isset($_POST['sms_consent']) ? 1 : 0;
    $terms_agreed = isset($_POST['terms_agreed']) ? 1 : 0;

    /* ---- Basic validation ---- */
    if ($branch_id <= 0 || $appointment_date === '' || $time_slot === '' ||
        $child_first === '' || $p_first === '' || $p_email === '' || $p_phone === '') {
        echo "<script>alert('Please fill all required fields.');window.history.back();</script>";
        exit;
    }
    if ($terms_agreed !== 1) {
        echo "<script>alert('You must agree to the Terms & Conditions.');window.history.back();</script>";
        exit;
    }

    /* ---- DOUBLE BOOKING CHECK (server-side) ---- */
    $chk = mysqli_query($conn, "
        SELECT id FROM appointments
        WHERE branch_id='$branch_id'
          AND appointment_date='$appointment_date'
          AND time_slot='$time_slot'
          AND status != 'Cancelled'
    ");
    if ($chk && mysqli_num_rows($chk) > 0) {
        echo "<script>
            alert('Sorry! This time slot was just booked by someone else. Please choose another slot.');
            window.location='schedule_appointment.php';
        </script>";
        exit;
    }

    /* ---- Get branch details (branch admin email) ---- */
    $bres = mysqli_query($conn, "SELECT * FROM branches WHERE id='$branch_id'");
    if (!$bres || mysqli_num_rows($bres) === 0) {
        echo "<script>alert('Invalid branch selected.');window.history.back();</script>";
        exit;
    }
    $branch = mysqli_fetch_assoc($bres);
    $branch_name  = mysqli_real_escape_string($conn, $branch['branch_name']);
    $branch_email = $branch['branch_email'];
    $branch_addr  = $branch['branch_address'];

    /* ---- Save appointment ---- */
    $sql = "INSERT INTO appointments
        (branch_id, branch_name, appointment_date, time_slot, mode,
         child_first_name, child_last_name, child_grade, child_subject,
         parent_first_name, parent_last_name, parent_email, parent_phone,
         notes, sms_consent, terms_agreed, status)
        VALUES
        ('$branch_id', '$branch_name', '$appointment_date', '$time_slot', '$mode',
         '$child_first', '$child_last', '$child_grade', '$child_subj',
         '$p_first', '$p_last', '$p_email', '$p_phone',
         '$notes', '$sms_consent', '$terms_agreed', 'Booked')";

    if (!mysqli_query($conn, $sql)) {
        /* Agar unique key se duplicate aaya (race condition) */
        if (mysqli_errno($conn) == 1062) {
            echo "<script>
                alert('Sorry! This time slot was just booked. Please pick another slot.');
                window.location='schedule_appointment.php';
            </script>";
            exit;
        }
        die("Database Error: " . mysqli_error($conn));
    }

    $appt_id  = mysqli_insert_id($conn);
    $year = date("y", strtotime($appointment_date));

    $appt_no = "AC-APT-$year-" . str_pad(
        $appt_id,
        4,
        "0",
        STR_PAD_LEFT
    );
    mysqli_query($conn, "UPDATE appointments SET appointment_no='$appt_no' WHERE id='$appt_id'");

    $nice_date = date("l, F j, Y", strtotime($appointment_date));

    /* ---- Email body (shared) ---- */
    $details_html = "
      <h2 style='color:#05364d;'>Assessment Appointment Details</h2>
      <p><b>Appointment No:</b> $appt_no</p>
      <table cellpadding='6' style='border-collapse:collapse;font-size:14px;'>
        <tr><td><b>Branch / Center:</b></td><td>$branch_name</td></tr>
        <tr><td><b>Address:</b></td><td>$branch_addr</td></tr>
        <tr><td><b>When:</b></td><td>$nice_date</td></tr>
        <tr><td><b>Time:</b></td><td>$time_slot ($mode)</td></tr>
        <tr><td colspan='2'><hr></td></tr>
        <tr><td><b>Child Name:</b></td><td>$child_first $child_last</td></tr>
        <tr><td><b>Grade:</b></td><td>$child_grade</td></tr>
        <tr><td><b>Subject:</b></td><td>$child_subj</td></tr>
        <tr><td colspan='2'><hr></td></tr>
        <tr><td><b>Parent Name:</b></td><td>$p_first $p_last</td></tr>
        <tr><td><b>Email:</b></td><td>$p_email</td></tr>
        <tr><td><b>Phone:</b></td><td>$p_phone</td></tr>
        <tr><td><b>Notes:</b></td><td>$notes</td></tr>
      </table>
    ";

    /* ===== MAIL 1: BRANCH ADMIN ===== */
    $sent_ok = false;
    try {
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host       = 'smtp.hostinger.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'info@achieverscastle.com';
        $mail->Password   = 'Amplic@@7408'; // <-- apna SMTP password daalo
        $mail->SMTPSecure = 'ssl';
        $mail->Port       = 465;

        $mail->setFrom('info@achieverscastle.com', "Achiever's Castle");
        $mail->addAddress($branch_email);            // branch admin
        $mail->addReplyTo($p_email, "$p_first $p_last");
        $mail->isHTML(true);
        $mail->Subject = "New Appointment Booked - $child_first $child_last ($branch_name)";
        $mail->Body    = "<h3>New Schedule Appointment</h3>" . $details_html;
        $mail->send();
        $sent_ok = true;
    } catch (Exception $e) {
        // continue
    }

    /* ===== MAIL 2: PARENT / BOOKER ===== */
    try {
        $mail2 = new PHPMailer(true);
        $mail2->isSMTP();
        $mail2->Host       = 'smtp.hostinger.com';
        $mail2->SMTPAuth   = true;
        $mail2->Username   = 'info@achieverscastle.com';
        $mail2->Password   = 'Amplic@@7408'; // <-- apna SMTP password daalo
        $mail2->SMTPSecure = 'ssl';
        $mail2->Port       = 465;

        $mail2->setFrom('info@achieverscastle.com', "Achiever's Castle");
        $mail2->addAddress($p_email, "$p_first $p_last");  // parent / booker
        $mail2->isHTML(true);
        $mail2->Subject = "Your Free Assessment is Confirmed - Achiever's Castle";
        $mail2->Body    = "
            <h3>Hello $p_first,</h3>
            <p>Your free assessment appointment has been <b>confirmed</b>. Your time slot is reserved.</p>
            $details_html
            <p style='margin-top:15px;'>Please bring your child to the assessment so our instructor can evaluate their skills.</p>
            <p>Thank you,<br><b>Team Achiever's Castle</b></p>";
        $mail2->send();
    } catch (Exception $e) {
        // ignore
    }

   header("Location: schedule_appointment.php?done=1");
   exit;
}

/* Load branches for the dropdown */
$branches = mysqli_query($conn, "SELECT * FROM branches ORDER BY branch_name ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Schedule Appointment | Achiever's Castle</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://fonts.googleapis.com/css2?family=Love+Ya+Like+A+Sister&family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
<style>
*{margin:0;padding:0;box-sizing:border-box;font-family:"Poppins",sans-serif;}
body{background:#f7f9fc;color:#222;}

.appt-wrap{max-width:760px;margin:0 auto;padding:50px 18px 80px;}
.appt-title{font-family:"Love Ya Like A Sister",cursive;font-size:40px;text-align:center;color:#111;margin-bottom:6px;}
.appt-sub{text-align:center;color:#666;font-size:14px;margin-bottom:30px;}

/* Stepper */
.stepper{display:flex;align-items:center;justify-content:center;gap:0;margin-bottom:40px;}
.step{display:flex;flex-direction:column;align-items:center;width:120px;}
.step .num{width:34px;height:34px;border-radius:50%;background:#e3e3e3;color:#555;display:flex;align-items:center;justify-content:center;font-weight:700;}
.step.active .num{background:#111;color:#fff;}
.step .lbl{font-size:12px;margin-bottom:8px;color:#777;font-weight:600;}
.step.active .lbl{color:#111;}
.bar{height:2px;width:60px;background:#ddd;margin:0 -4px 0 -4px;align-self:flex-end;margin-bottom:16px;}

.card{background:#fff;border-radius:20px;padding:34px 30px;box-shadow:0 18px 40px rgba(0,0,0,.07);}
.center-head{text-align:center;color:#05364d;font-weight:700;margin-bottom:18px;display:flex;gap:8px;justify-content:center;align-items:center;}

label{font-weight:600;font-size:14px;color:#333;display:block;margin-bottom:6px;}
input,select,textarea{width:100%;padding:13px 15px;border:1px solid #ddd;border-radius:11px;font-size:14px;}
textarea{height:110px;resize:none;}
.row{display:flex;gap:18px;margin-bottom:18px;}
.col{flex:1;}
.required{color:#e8063c;}

/* Calendar */
.cal{border:1px solid #eee;border-radius:14px;padding:14px;max-width:480px;margin:0 auto;}
.cal-head{display:flex;align-items:center;justify-content:space-between;font-weight:700;margin-bottom:10px;}
.cal-head button{width:30px;height:30px;border-radius:50%;border:none;background:#111;color:#fff;cursor:pointer;}
.cal-grid{display:grid;grid-template-columns:repeat(7,1fr);gap:4px;text-align:center;}
.cal-grid .dow{font-size:12px;color:#888;padding:6px 0;font-weight:600;}
.cal-grid .day{padding:9px 0;border-radius:9px;font-size:14px;cursor:pointer;color:#333;}
.cal-grid .day.muted{color:#ccc;cursor:not-allowed;}
.cal-grid .day.sel{background:#2e9e74;color:#fff;font-weight:700;}
.cal-grid .day:not(.muted):hover{background:#eef6f1;}

.slots{display:flex;flex-wrap:wrap;gap:12px;justify-content:center;margin-top:16px;}
.slot{border:1px solid #ddd;border-radius:10px;padding:12px 18px;text-align:center;cursor:pointer;min-width:150px;background:#fff;}
.slot small{display:block;color:#777;font-size:12px;}
.slot.sel{border-color:#2e9e74;background:#eef6f1;font-weight:700;}
.slot.booked{background:#f3f3f3;color:#bbb;cursor:not-allowed;border-style:dashed;}
.slot.booked small{color:#c0392b;}

.next-btn{width:100%;border:2px solid #2e9e74;background:#fff;color:#2e9e74;padding:15px;border-radius:10px;font-size:16px;font-weight:700;cursor:pointer;margin-top:26px;letter-spacing:1px;}
.next-btn:hover{background:#2e9e74;color:#fff;}
.back-link{display:inline-block;margin-top:14px;color:#777;cursor:pointer;font-size:13px;text-decoration:underline;background:none;border:none;}

.checkbox-row{display:flex;gap:8px;align-items:flex-start;margin:14px 0;font-size:13px;color:#555;}
.checkbox-row input{width:auto;margin-top:3px;}

.review-line{display:flex;justify-content:space-between;border-bottom:1px solid #eee;padding:14px 0;}
.review-line .k{color:#05364d;font-weight:600;}
.hidden{display:none;}
.done-box{text-align:center;padding:40px 20px;}
.done-box h2{font-family:"Love Ya Like A Sister",cursive;font-size:34px;color:#2e9e74;margin-bottom:10px;}
@media(max-width:600px){.row{flex-direction:column;gap:14px;}.appt-title{font-size:30px;}.slot{min-width:130px;}}
</style>
</head>
<body>

<?php include 'header.php'; ?>

<div class="appt-wrap">

<?php if (isset($_GET['done'])): ?>
  <div class="card done-box">
    <h2>Appointment Confirmed!</h2>
    <p>Your free assessment slot is reserved. Confirmation emails have been sent.</p>
    <button class="next-btn" style="max-width:240px;margin:24px auto 0;" onclick="location.href='schedule_appointment.php'">Book Another</button>
  </div>
<?php else: ?>

  <h1 class="appt-title">Schedule Appointment</h1>
  <p class="appt-sub">Your assessment is completely free and there is no obligation to enroll.</p>

  <!-- Stepper -->
  <div class="stepper">
    <div class="step active" id="s1"><span class="lbl">Day &amp; Time</span><span class="num">1</span></div>
    <div class="bar"></div>
    <div class="step" id="s2"><span class="lbl">Contact Info</span><span class="num">2</span></div>
    <div class="bar"></div>
    <div class="step" id="s3"><span class="lbl">Confirm</span><span class="num">3</span></div>
  </div>

  <form method="POST" id="apptForm">
    <input type="hidden" name="branch_id" id="branch_id">
    <input type="hidden" name="appointment_date" id="appointment_date">
    <input type="hidden" name="time_slot" id="time_slot">
    <input type="hidden" name="mode" id="mode">

    <!-- ============ STEP 1: BRANCH + DAY + TIME ============ -->
    <div class="card step-panel" id="panel1">

      <div class="center-head">📍 Select a Branch</div>
      <select id="branch_select" required>
        <option value="">-- Choose a Center --</option>
        <?php while ($b = mysqli_fetch_assoc($branches)): ?>
          <option value="<?= $b['id'] ?>"
                  data-name="<?= htmlspecialchars($b['branch_name']) ?>"
                  data-addr="<?= htmlspecialchars($b['branch_address']) ?>">
            <?= htmlspecialchars($b['branch_name']) ?>
          </option>
        <?php endwhile; ?>
      </select>

      <div id="dt_block" class="hidden">
        <div class="center-head" style="margin-top:26px;">📅 Select a Day</div>
        <div class="cal">
          <div class="cal-head">
            <button type="button" id="prevMonth">‹</button>
            <span id="monthLabel"></span>
            <button type="button" id="nextMonth">›</button>
          </div>
          <div class="cal-grid" id="calGrid"></div>
        </div>

        <div class="center-head" style="margin-top:26px;">🕐 Select a Time</div>
        <div class="slots" id="slotBox"><p style="color:#999;font-size:13px;">Select a day first.</p></div>
      </div>

      <button type="button" class="next-btn" onclick="goStep2()">NEXT</button>
    </div>

    <!-- ============ STEP 2: CHILD + PARENT ============ -->
    <div class="card step-panel hidden" id="panel2">

      <div class="center-head">🧒 Child Information</div>
      <div class="row">
        <div class="col"><label>First Name <span class="required">*</span></label><input type="text" name="child_first_name" required></div>
        <div class="col"><label>Last Name <span class="required">*</span></label><input type="text" name="child_last_name" required></div>
      </div>
      <div class="row">
        <div class="col">
          <label>Grade <span class="required">*</span></label>
          <select name="child_grade" required>
            <option value="">Select</option>
            <option>Pre-School</option><option>Kindergarten</option>
            <option>Grade 1</option><option>Grade 2</option><option>Grade 3</option>
            <option>Grade 4</option><option>Grade 5</option><option>Grade 6</option>
            <option>Grade 7</option><option>Grade 8</option><option>Grade 9</option>
            <option>Grade 10</option><option>Grade 11</option><option>Grade 12</option>
          </select>
        </div>
        <div class="col">
          <label>Subject <span class="required">*</span></label>
          <select name="child_subject" required>
            <option value="">Select</option>
            <option>All Subjects</option><option>Mathematics</option><option>Science</option><option>Reading and Writing</option>
          </select>
        </div>
      </div>

      <div class="center-head" style="margin-top:24px;">👪 Parent Information</div>
      <div class="row">
        <div class="col"><label>First Name <span class="required">*</span></label><input type="text" name="parent_first_name" required></div>
        <div class="col"><label>Last Name <span class="required">*</span></label><input type="text" name="parent_last_name" required></div>
      </div>
      <div class="row">
        <div class="col"><label>Email <span class="required">*</span></label><input type="email" name="parent_email" required></div>
        <div class="col"><label>Mobile Phone <span class="required">*</span></label>
          <input type="text" name="parent_phone" required pattern="[0-9]{10}" maxlength="10"
                 oninput="this.value=this.value.replace(/[^0-9]/g,'').slice(0,10);">
        </div>
      </div>

      <div class="checkbox-row">
        <input type="checkbox" name="sms_consent" id="sms_consent">
        <label for="sms_consent" style="font-weight:400;">By checking the box, you consent to receive communications via text message. Message and data rates may apply. You can text STOP to cancel at any time.</label>
      </div>

      <label>Notes</label>
      <textarea name="notes" placeholder="Any additional notes or comments to help us prepare for your appointment?"></textarea>

      <div class="checkbox-row" style="margin-top:14px;">
        <input type="checkbox" name="terms_agreed" id="terms_agreed" required>
        <label for="terms_agreed" style="font-weight:400;">I consent to the terms and conditions of Achiever's Castle.</label>
      </div>

      <button type="button" class="next-btn" onclick="goStep3()">NEXT</button>
      <div style="text-align:center;"><button type="button" class="back-link" onclick="backTo(1)">&larr; Back</button></div>
    </div>

    <!-- ============ STEP 3: REVIEW ============ -->
    <div class="card step-panel hidden" id="panel3">
      <div class="center-head" style="font-size:20px;">REVIEW &amp; CONFIRM</div>
      <p style="text-align:center;color:#666;font-size:14px;margin-bottom:18px;">Please review your information and click CONFIRM APPOINTMENT below.</p>

      <div class="review-line"><span class="k">📅 When</span><span id="rv_when"></span></div>
      <div class="review-line"><span class="k">📍 Where</span><span id="rv_where" style="text-align:right;"></span></div>
      <div class="review-line"><span class="k">🧒 For</span><span id="rv_child"></span></div>
      <div class="review-line"><span class="k">✉️ Reached at</span><span id="rv_contact" style="text-align:right;"></span></div>

      <button type="submit" name="confirm_appointment" class="next-btn">CONFIRM APPOINTMENT</button>
      <p style="text-align:center;color:#888;font-size:12px;margin-top:10px;">Your free assessment time will be reserved once you confirm.</p>
      <div style="text-align:center;"><button type="button" class="back-link" onclick="backTo(2)">&larr; Back</button></div>
    </div>

  </form>
<?php endif; ?>
</div>

<?php if (file_exists('footer.php')) include 'footer.php'; ?>

<script>
const branchSelect = document.getElementById('branch_select');
const dtBlock = document.getElementById('dt_block');
const slotBox = document.getElementById('slotBox');

let selectedDate = null;
let selectedSlot = null;

if (branchSelect) {
  branchSelect.addEventListener('change', function(){
    const id = this.value;
    document.getElementById('branch_id').value = id;
    selectedDate = null; selectedSlot = null;
    if (id === '') { dtBlock.classList.add('hidden'); return; }
    dtBlock.classList.remove('hidden');
    slotBox.innerHTML = '<p style="color:#999;font-size:13px;">Select a day first.</p>';
    renderCalendar();
  });
}

let viewDate = new Date();
const monthNames = ["JANUARY","FEBRUARY","MARCH","APRIL","MAY","JUNE","JULY","AUGUST","SEPTEMBER","OCTOBER","NOVEMBER","DECEMBER"];

function renderCalendar(){
  const grid = document.getElementById('calGrid');
  const label = document.getElementById('monthLabel');
  if (!grid) return;
  grid.innerHTML = '';
  label.textContent = monthNames[viewDate.getMonth()] + ' ' + viewDate.getFullYear();

  ["Su","Mo","Tu","We","Th","Fr","Sa"].forEach(d=>{
    const c=document.createElement('div');c.className='dow';c.textContent=d;grid.appendChild(c);
  });

  const y=viewDate.getFullYear(), m=viewDate.getMonth();
  const first=new Date(y,m,1).getDay();
  const days=new Date(y,m+1,0).getDate();
  const today=new Date(); today.setHours(0,0,0,0);

  for(let i=0;i<first;i++){grid.appendChild(document.createElement('div'));}

  for(let d=1;d<=days;d++){
    const cell=document.createElement('div');
    cell.className='day';
    cell.textContent=d;
    const cur=new Date(y,m,d);
    if(cur<today){
      cell.classList.add('muted');
    } else {
      cell.onclick=()=>{
        document.querySelectorAll('.cal-grid .day.sel').forEach(e=>e.classList.remove('sel'));
        cell.classList.add('sel');
        const mm=String(m+1).padStart(2,'0');
        const dd=String(d).padStart(2,'0');
        selectedDate=`${y}-${mm}-${dd}`;
        selectedSlot=null;
        loadSlots();
      };
    }
    grid.appendChild(cell);
  }
}

document.addEventListener('click',function(e){
  if(e.target && e.target.id==='prevMonth'){viewDate.setMonth(viewDate.getMonth()-1);renderCalendar();}
  if(e.target && e.target.id==='nextMonth'){viewDate.setMonth(viewDate.getMonth()+1);renderCalendar();}
});

/* Slots from server (branch + date based, booked ones disabled) */
function loadSlots(){
  const id=document.getElementById('branch_id').value;
  slotBox.innerHTML='Loading...';
  fetch('get_slots.php?branch_id='+id+'&date='+selectedDate)
    .then(r=>r.json())
    .then(data=>{
      slotBox.innerHTML='';
      if(!data.length){slotBox.innerHTML='<p style="color:#999;">No slots available.</p>';return;}
      data.forEach(s=>{
        const el=document.createElement('div');
        if(s.booked){
          el.className='slot booked';
          el.innerHTML=`<b>${s.label}</b><small>Booked</small>`;
          // disabled - no click
        } else {
          el.className='slot';
          el.innerHTML=`<b>${s.label}</b><small>${s.mode}</small>`;
          el.onclick=()=>{
            document.querySelectorAll('.slot.sel').forEach(x=>x.classList.remove('sel'));
            el.classList.add('sel');
            selectedSlot=s;
          };
        }
        slotBox.appendChild(el);
      });
    });
}

function setStep(n){
  ['1','2','3'].forEach(i=>{
    document.getElementById('panel'+i).classList.toggle('hidden', i!=n);
    document.getElementById('s'+i).classList.toggle('active', i<=n);
  });
  window.scrollTo({top:0,behavior:'smooth'});
}
function backTo(n){setStep(n);}

function goStep2(){
  if(!branchSelect.value){alert('Please select a branch.');return;}
  if(!selectedDate){alert('Please select a day.');return;}
  if(!selectedSlot){alert('Please select a time slot.');return;}
  document.getElementById('appointment_date').value=selectedDate;
  document.getElementById('time_slot').value=selectedSlot.label;
  document.getElementById('mode').value=selectedSlot.mode;
  setStep(2);
}

function goStep3(){
  const f=document.getElementById('apptForm');
  const req=['child_first_name','child_last_name','child_grade','child_subject','parent_first_name','parent_last_name','parent_email','parent_phone'];
  for(const name of req){
    const el=f.querySelector(`[name="${name}"]`);
    if(!el.value.trim()){alert('Please fill all required fields.');el.focus();return;}
  }
  if(!document.getElementById('terms_agreed').checked){alert('Please agree to the Terms & Conditions.');return;}

  const opt=branchSelect.options[branchSelect.selectedIndex];
  const niceDate=new Date(selectedDate+'T00:00:00').toLocaleDateString('en-US',{weekday:'long',year:'numeric',month:'long',day:'numeric'});

  document.getElementById('rv_when').textContent=niceDate+', '+selectedSlot.label;
  document.getElementById('rv_where').innerHTML=opt.dataset.name+'<br><small>'+opt.dataset.addr+'</small>';
  document.getElementById('rv_child').textContent=f.child_first_name.value+' '+f.child_last_name.value+' ('+f.child_grade.value+', '+f.child_subject.value+')';
  document.getElementById('rv_contact').innerHTML=f.parent_email.value+'<br>'+f.parent_phone.value;
  setStep(3);
}
</script>
</body>
</html>