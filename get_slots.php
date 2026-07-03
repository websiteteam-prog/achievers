<?php
/* =========================================================
   get_slots.php
   Branch + Date ke hisaab se slots return karta hai.
   Slots fixed hain, lekin jo slot us date pe pehle se book
   ho chuka hai usko "booked: true" mark karta hai taaki
   double booking na ho.
   ========================================================= */

include 'db_config.php';
header('Content-Type: application/json');

$branch_id = isset($_GET['branch_id']) ? (int)$_GET['branch_id'] : 0;
$date      = isset($_GET['date']) ? mysqli_real_escape_string($conn, $_GET['date']) : '';

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

/* Fixed slots list */
$slots = [
    ["label" => "10:00 - 11:00 AM", "mode" => "In Center"],
    ["label" => "12:00 - 1:00 PM",  "mode" => "In Center"],
    ["label" => "4:00 - 5:00 PM",   "mode" => "In Center"],
    ["label" => "6:00 - 7:00 PM",   "mode" => "In Center"],
    ["label" => "7:00 - 8:00 PM",   "mode" => "Online"],
];

/* Us branch + date pe jo slots already booked hain, unhe nikaalo */
$booked = [];
$bres = mysqli_query($conn, "
    SELECT time_slot FROM appointments
    WHERE branch_id='$branch_id'
      AND appointment_date='$date'
      AND status != 'Cancelled'
");
while ($row = mysqli_fetch_assoc($bres)) {
    $booked[$row['time_slot']] = true;
}

/* Har slot pe booked flag laga do */
$out = [];
foreach ($slots as $s) {
    $s['booked'] = isset($booked[$s['label']]);
    $out[] = $s;
}

echo json_encode($out);
exit;