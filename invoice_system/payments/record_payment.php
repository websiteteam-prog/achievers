<?php
include "../../db_config.php";

/* ✅ ALWAYS TOP */
require "../../dompdf/autoload.inc.php";
require "../../PHPMailer/PHPMailer.php";
require "../../PHPMailer/SMTP.php";
require "../../PHPMailer/Exception.php";
require "receipt_function.php";

use Dompdf\Dompdf;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
$env = parse_ini_file(__DIR__ . '/../../.env');
$baseUrl = rtrim($env['APP_URL'], '/');
$id = $_GET['invoice_id'] ?? 0;

if($_SERVER['REQUEST_METHOD']=="POST"){

$invoice_id = (int)$_POST['invoice_id'];
$amount = $_POST['amount'];
$method = $_POST['method'];

/* SAVE PAYMENT */
// get student_id from invoice
$invData = mysqli_fetch_assoc(mysqli_query($conn,"
SELECT student_id FROM invoices WHERE id='$invoice_id'
"));

$student_id = $invData['student_id'] ?? 0;

// generate receipt number
$receipt_no = "RCPT-" . date("Ymd") . "-" . rand(1000,9999);

$checkPaid = mysqli_fetch_assoc(mysqli_query($conn,"
SELECT status FROM invoices WHERE id='$invoice_id'
"));

if($checkPaid['status'] == "Paid"){
    echo "<script>
    alert('Payment already done');
    window.location='teacher_dashboard.php?page=invoice_system/payments/payment_list.php';
    </script>";
    exit;
}

mysqli_query($conn,"
INSERT INTO payments
(invoice_id, student_id, amount, payment_method, payment_date, receipt_number)
VALUES
('$invoice_id', '$student_id', '$amount', '$method', CURDATE(), '$receipt_no')
");

/* UPDATE INVOICE */
mysqli_query($conn,"
UPDATE invoices
SET status='Paid'
WHERE id='$invoice_id'
");

/* FETCH DATA */
$data = mysqli_fetch_assoc(mysqli_query($conn,"
SELECT 
    payments.*, 
    invoices.*, 
    enrollment_inquiries.*, 
    enrollment_inquiries.status AS enroll_status
FROM payments
JOIN invoices 
    ON payments.invoice_id = invoices.id
LEFT JOIN enrollment_inquiries 
    ON invoices.student_id = enrollment_inquiries.student_id
WHERE payments.invoice_id = '$invoice_id'
ORDER BY payments.id DESC
LIMIT 1
"));

if(!$data){
    die("Payment data not found");
}

if($data['enroll_status'] == "Cancelled"){
    die("Enrollment Cancelled - Payment not allowed");
}

/* EMAIL DECISION */
if($data['payment_by'] == "Guardian"){
    $email_to = $data['guardian_email'];
    $name_to = $data['guardian_name'];
}
elseif($data['payment_by'] == "Mother"){
    $email_to = $data['mother_email'] ?: $data['guardian_email'];
    $name_to = $data['mother_name'];
}
else{
    $email_to = $data['father_email'] ?: $data['guardian_email'];
    $name_to = $data['father_name'];
}

/* GENERATE PDF */
$logo="data:image/png;base64,".base64_encode(file_get_contents("../../images/logo.png"));
$paid="data:image/png;base64,".base64_encode(file_get_contents("../../images/paid.png"));
$html = generateReceiptHTML($data);

$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper("A4","portrait");
$dompdf->render();

$file = "../../temp_receipt_".$data['id'].".pdf";
file_put_contents($file, $dompdf->output());
$mailStatus = "";

/* SEND EMAIL */
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
$mail->addAddress($email_to,$name_to);

$mail->Subject = "Payment Receipt Confirmation for ".$data['first_name'].", ".date("F Y");

$mail->isHTML(true);

$mail->Body = "
<p><b>Dear Parents/Guardians,</b></p>

<p>
This is to confirm that we have received your payment. 
Please find the payment receipt attached for your records.
</p>

<p>
Thank you for your prompt payment and continued support.
</p>

<br>

<p>
Best regards,<br>
<b>Team Achiever's Castle</b>
</p>
";

$mail->addAttachment($file,"receipt.pdf");

if($mail->send()){
    $mailStatus = "Receipt sent successfully";
} else {
    $mailStatus = "Receipt sending failed";
}


} catch(Exception $e){
    $mailStatus = "Receipt email failed";
}


/* ==============================
SEND RESET PASSWORD LINK
============================== */

$studentData = mysqli_fetch_assoc(mysqli_query($conn,"
SELECT id, email, first_name 
FROM students 
WHERE id = (SELECT student_id FROM invoices WHERE id='$invoice_id')
"));

if($studentData){

    // check if already sent
    $checkToken = mysqli_fetch_assoc(mysqli_query($conn,"
    SELECT reset_token, token_expiry 
    FROM students 
    WHERE id='".$studentData['id']."'
    "));

    // agar token nahi hai ya expire ho chuka hai tab naya banao
    if(empty($checkToken['reset_token']) || strtotime($checkToken['token_expiry']) < time()){

        $token = bin2hex(random_bytes(32));
        $expiry = date("Y-m-d H:i:s", strtotime("+24 hours"));

        mysqli_query($conn,"
        UPDATE students 
        SET reset_token='$token', token_expiry='$expiry'
        WHERE id='".$studentData['id']."'
        ");

    // 🔗 create reset link
    $reset_link = $baseUrl."/Student_dashboard/auth/reset_password.php?token=".$token;

    // 📧 send email
    $mail2 = new PHPMailer(true);

    try{

        $mail2->isSMTP();
        $mail2->Host='smtp.hostinger.com';
        $mail2->SMTPAuth=true;
        $mail2->Username='info@achieverscastle.com';
        $mail2->Password='Amplic@@7408';
        $mail2->SMTPSecure='ssl';
        $mail2->Port=465;

        $mail2->setFrom('info@achieverscastle.com','Achiever\'s Castle');
        $mail2->addAddress($studentData['email'],$studentData['first_name']);

        $mail2->Subject = "Set Your Password";

        $mail2->isHTML(true);

         $mail2->Body = "
        <h3>Welcome to Achiever's Castle 🎉</h3>

        <p>
        Your account has been successfully created.
        </p>

        <hr>

        <h3>🔐 Login Details</h3>

        <p><b>Email:</b> ".$studentData['email']."</p>

        <p>
        Please click below to set your password:
        </p>

        <a href='$reset_link' 
        style='padding:12px 25px;background:#e8063c;color:#fff;text-decoration:none;border-radius:5px;display:inline-block;'>
        Set Your Password
        </a>

        <p style='margin-top:10px;'>
        ⏳ <b>This link will expire in 24 hours.</b>
        </p>

        <hr>

        <p>
        If you did not request this, please ignore this email.
        </p>

        <p>
        Thanks,<br>
        <b>Team Achiever's Castle</b>
        </p>
        ";
        $mail2->send();

     } catch(Exception $e){
        echo "Reset email failed: " . $e->getMessage();
    }
}
}
/* REDIRECT */
echo "<script>
alert('Payment Recorded & Receipt Sent');
window.location='teacher_dashboard.php?page=invoice_system/payments/payment_list.php&id=$invoice_id';
</script>";

exit;
}
?>
<link href="https://fonts.googleapis.com/css2?family=Love+Ya+Like+A+Sister&display=swap" rel="stylesheet">
<div class="payment-page">

<div class="payment-header">
<h3><i class="bi bi-cash"></i> Record Payment</h3>
</div>

<div class="payment-card">

<form id="paymentForm">

<input type="hidden" name="invoice_id" value="<?php echo $id; ?>">

<div class="form-group">

<label>Amount</label>

<input
class="form-control"
name="amount"
value="<?php

$inv = mysqli_fetch_assoc(mysqli_query($conn,"SELECT total FROM invoices WHERE id='$id'"));
echo $inv['total'];

?>"
required>

</div>

<div class="form-group">

<label>Payment Method</label>

<select class="form-control" name="method" required>

<option value="">Select Method</option>
<option>Cash</option>
<option>E-Transfer</option>
<option>Debit Card</option>
<option>Credit Card</option>

</select>

</div>

<button class="btn btn-success" name="pay">

<i class="bi bi-check-circle"></i> Record Payment

</button>

</form>

<script>

$("#paymentForm").submit(function(e){
    e.preventDefault();

    let btn = $("button[name='pay']");
    btn.prop("disabled", true);
    btn.html("Processing...");

    let formData=$(this).serialize();

    $.ajax({
        url:"invoice_system/payments/record_payment.php?invoice_id=<?php echo $id; ?>",
        type:"POST",
        data:formData,

        success:function(res){
            $("#content-area").html(res);
        },

        error:function(){
            alert("Payment failed");
            btn.prop("disabled", false);
            btn.html("Record Payment");
        }
    });
});

</script>

</div>

</div>

<style>

.payment-page{
padding:10px;
}

.payment-header{
margin-bottom:20px;
}

.payment-header h3{
font-family:"Love Ya Like A Sister", cursive;
font-size:30px;
color:#05364d;
margin-bottom:30px;
}

.payment-card{
background:white;
padding:25px;
border-radius:15px;
box-shadow:0 5px 15px rgba(0,0,0,0.05);
max-width:400px;
}

.form-group{
margin-bottom:15px;
}

.form-group label{
font-size:14px;
font-weight:500;
}

.form-control{
padding:10px;
border-radius:8px;
border:1px solid #ddd;
width:100%;
}

</style>