<?php
include "../../../db_config.php";
require "../../../dompdf/autoload.inc.php";
use Dompdf\Dompdf;

$student_id = $_POST['student_id'];
$program = $_POST['program'];
$program_count = $_POST['program_count'];
$subjectsArr = $_POST['subjects'] ?? [];
$discount_type   = $_POST['discount_type'] ?? '';
$discount_amount = floatval($_POST['discount_amount'] ?? 0);
$discount_removed = $_POST['discount_removed'] ?? 0;
$discount_description = $_POST['discount_description'] ?? '';
$extra_type        = $_POST['extra_type'] ?? '';
$extra_amount      = floatval($_POST['extra_amount'] ?? 0);
$extra_description = $_POST['extra_description'] ?? '';
if(empty($extra_type)){
    $extra_amount = 0;
}
if(empty($discount_type)){
    $discount_amount = 0;
}

/* =============================
   REMOVE + RECURRING LOGIC
============================= */

if($discount_removed == 1){
    // ✅ admin removed discount
    $discount_type = '';
    $discount_amount = 0;
}
elseif(empty($discount_type)){
    // ✅ auto apply sibling if exists
    $prev = mysqli_fetch_assoc(mysqli_query($conn,"
        SELECT discount_type, discount_amount 
        FROM enrollment_inquiries  
        WHERE student_id='$student_id'
        ORDER BY id DESC LIMIT 1
    "));

    if($prev && $prev['discount_type'] == 'sibling'){
        $discount_type = 'sibling';
        $discount_amount = $prev['discount_amount'];
    }
}

if(empty($subjectsArr)){
    // fallback DB se lo
    $oldSubjects = mysqli_fetch_assoc(mysqli_query($conn,"
        SELECT specific_subject FROM enrollment_inquiries 
        WHERE student_id='$student_id'
    "))['specific_subject'];

    $subjects = $oldSubjects ?: "All Programs";
}else{
    $subjects = implode(", ", $subjectsArr);
}

/* EXPIRE OLD PLAN */
/* GET THE CURRENT ACTIVE PLAN'S INVOICE (before expiring) */
$activePlan = mysqli_fetch_assoc(mysqli_query($conn,"
    SELECT invoice_id 
    FROM student_plan_history
    WHERE student_id='$student_id' AND status='Active'
    ORDER BY id DESC LIMIT 1
"));
$old_invoice_id = $activePlan['invoice_id'] ?? 0;

/* EXPIRE OLD PLAN */
mysqli_query($conn,"
UPDATE student_plan_history 
SET status='Expired', end_date=CURDATE()
WHERE student_id='$student_id' AND status='Active'
");

/* CANCEL THE OLD PLAN'S INVOICE — only if it was never paid */
if($old_invoice_id){
    mysqli_query($conn,"
    UPDATE invoices 
    SET status='Cancelled'
    WHERE id='$old_invoice_id' AND status='Pending'
    ");
}

/* GET GRADE */
$grade = $_POST['grade'];

/* -----------------------------
CALCULATE PRICE AGAIN
-----------------------------*/

if($grade == "Pre-School" || $grade == "Grade 1" || $grade == "Grade 2"){
    $price = 150;
}
elseif(in_array($grade, ["Grade 3","Grade 4","Grade 5","Grade 6","Grade 7","Grade 8"])){

    if($program_count == 1){
        $price = 140;
    }
    elseif($program_count == 2){
        $price = 270;
    }
    else{
        $price = 400;
    }

}
elseif(in_array($grade, ["Grade 9","Grade 10","Grade 11","Grade 12"])){

    if($program_count == 1){
        $price = 160;
    }
    elseif($program_count == 2){
        $price = 310;
    }
    else{
        $price = 460;
    }

}

if(!isset($price)){
    $price = 150;
}

/* -----------------------------
APPLY DISCOUNT
-----------------------------*/

if(!empty($discount_type)){
    $price -= $discount_amount;
}
/* APPLY EXTRA AMOUNT */
if($extra_type === "one_time" || $extra_type === "permanent"){
    $price += $extra_amount;
}
if($price < 0){
    $price = 0;
}

/* INSERT NEW PLAN */
mysqli_query($conn,"
INSERT INTO student_plan_history
(student_id, program, program_count, subjects, price, start_date)
VALUES
('$student_id', '$program', '$program_count', '$subjects', '$price', CURDATE())
");

/* UPDATE ENROLLMENT */
mysqli_query($conn,"
UPDATE enrollment_inquiries 
SET program='$program', 
    program_count='$program_count', 
    specific_subject='$subjects',
    grade='$grade',
    discount_type='$discount_type',
    discount_amount='$discount_amount',
    discount_description='$discount_description',
    extra_type='$extra_type',
    extra_amount='$extra_amount',
    extra_description='$extra_description'
    WHERE student_id='$student_id'
");

/* GST */
    $gst = $price * (5/105);
    $total = $price; 

mysqli_query($conn,"
INSERT INTO invoices
(student_id, invoice_date, due_date, price, gst, total, status, discount_type, discount_amount, discount_description, extra_type, extra_amount, extra_description)
VALUES
('$student_id', CURDATE(), DATE_ADD(CURDATE(), INTERVAL 15 DAY),
'$price', '$gst', '$total', 'Pending', '$discount_type', '$discount_amount', '$discount_description', '$extra_type', '$extra_amount', '$extra_description')
");

$invoice_id = mysqli_insert_id($conn);

$year = date("y");
$invoice_number = "AC-$year-" . str_pad($invoice_id, 4, "0", STR_PAD_LEFT);

mysqli_query($conn,"
UPDATE invoices 
SET invoice_number='$invoice_number'
WHERE id='$invoice_id'
");

/* LINK INVOICE TO PLAN */
mysqli_query($conn,"
UPDATE student_plan_history 
SET invoice_id='$invoice_id'
WHERE student_id='$student_id'
ORDER BY id DESC LIMIT 1
");

/* PDF GENERATE */

$studentData = mysqli_fetch_assoc(mysqli_query($conn,"
SELECT * FROM enrollment_inquiries 
WHERE student_id='$student_id'
"));
$email_to = $studentData['guardian_email'];
$name_to  = $studentData['guardian_name'];
$student = [
    "first_name" => $studentData['first_name'],
    "last_name"  => $studentData['last_name'],
    "email"      => $studentData['guardian_email'],
    "student_email" => $studentData['guardian_email'],
    "course_title"  => $subjects, 
    "created_at"    => date("Y-m-d"),
    "invoice_number"=> $invoice_number,
    "program"       => $program,
    "program_count" => $program_count,
    "payment_by"    => $studentData['payment_by'],
    "payer_name"    => $studentData['guardian_name']
];

$student['invoice_number'] = $invoice_number;

$logoBase64="data:image/png;base64,".base64_encode(file_get_contents("../../../images/logo.png"));
$invoice = [
    "discount_type" => $discount_type,
    "discount_amount" => $discount_amount,
    "price_after_discount" => $price,
    "gst" => $gst,
    "total" => $total,
    "extra_type" => $extra_type,
    "extra_amount" => $extra_amount,
    "extra_description" => $extra_description,
];
ob_start();
include "../../../invoice_template.php";
$html = ob_get_clean();

$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper("A4");
$dompdf->render();

$file = "../../temp_invoice_".$invoice_id.".pdf";
file_put_contents($file, $dompdf->output());

/* SEND EMAIL */
require "../../../PHPMailer/PHPMailer.php";
require "../../../PHPMailer/SMTP.php";
require "../../../PHPMailer/Exception.php";

use PHPMailer\PHPMailer\PHPMailer;

$mail = new PHPMailer(true);

try{
    $mail->isSMTP();
    $mail->Host='smtp.hostinger.com';
    $mail->SMTPAuth=true;
    $mail->Username='info@achieverscastle.com';
    $mail->Password='Amplic@@7408';
    $mail->SMTPSecure='ssl';
    $mail->Port=465;

    $mail->setFrom('info@achieverscastle.com','Achiever\'s Castle');
    $mail->addAddress($studentData['guardian_email'], $studentData['guardian_name']);

    $mail->Subject="Updated Plan Invoice";
    $mail->isHTML(true);
    $mail->Body = "
<h3>Hello $name_to,</h3>

<p>
Your child's plan has been <b>successfully updated</b> at <b>Achiever's Castle</b>.
</p>

<p>
Please find the updated invoice attached.
</p>

<br>

<p>
If you have any questions, feel free to contact us.
</p>

<p>
Thank you,<br>
<b>Team Achiever's Castle</b>
</p>
";

    $mail->addAttachment($file,"invoice.pdf");
    $mail->send();

    if(file_exists($file)){
    unlink($file);
}

}catch(Exception $e){
    error_log("Mail failed: " . $e->getMessage());
}

/* REDIRECT */
echo "<script>
alert('Plan updated & New invoice generated');
window.location='dashboard.php?page=invoice_system/dashboard/invoice_dashboard.php';
</script>";