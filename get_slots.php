<?php
/* =========================================================
   get_slots.php
   Branch + Date ke hisaab se slots return karta hai.
   Ab slots FIXED nahi hain – branch admin apne dashboard se
   jo slots (branch_slots table me) add karega, sirf wahi
   show honge. Jo slot us date pe pehle se book ho chuka hai
   usko "booked: true" mark karta hai taaki double booking na ho.
   ========================================================= */

include 'db_config.php';
header('Content-Type: application/json');

$branch_id = isset($_GET['branch_id']) ? (int) $_GET['branch_id'] : 0;
$date      = isset($_GET['date']) ? mysqli_real_escape_string($conn, trim($_GET['date'])) : '';

if ($branch_id <= 0 || $date === '') {
    echo json_encode([]);
    exit;
}

/* Branch valid hai? */
$res = mysqli_query($conn, "SELECT id FROM branches WHERE id='$branch_id'");
if (!$res || mysqli_num_rows($res) === 0) {
    echo json_encode([]);
    exit;
}

/* ---- Branch ne is date ke liye jo slots rakhe hain wahi lao ---- */
$slots = [];
$sres = mysqli_query($conn, "
    SELECT time_slot, mode
    FROM branch_slots
    WHERE branch_id='$branch_id'
      AND slot_date='$date'
    ORDER BY id ASC
");
if ($sres) {
    while ($row = mysqli_fetch_assoc($sres)) {
        $slots[] = ["label" => $row['time_slot'], "mode" => $row['mode']];
    }
}

/* Agar branch ne is date pe koi slot nahi rakha to khaali array */
if (empty($slots)) {
    echo json_encode([]);
    exit;
}

/* ---- Us branch + date pe jo slots already booked hain ---- */
$booked = [];
$bres = mysqli_query($conn, "
    SELECT time_slot FROM appointments
    WHERE branch_id='$branch_id'
      AND appointment_date='$date'
      AND status != 'Cancelled'
");
if ($bres) {
    while ($row = mysqli_fetch_assoc($bres)) {
        $booked[$row['time_slot']] = true;
    }
}

/* ---- Har slot pe booked flag laga do ---- */
$out = [];
foreach ($slots as $s) {
    $s['booked'] = isset($booked[$s['label']]);
    $out[] = $s;
}

echo json_encode($out);
exit;