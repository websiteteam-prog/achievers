<?php

include "../../db_config.php";

/* RUN ONLY ON 1ST */
if (date('d') != '01') {
    exit;
}

/* MAIL */
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require "../../PHPMailer/PHPMailer.php";
require "../../PHPMailer/SMTP.php";
require "../../PHPMailer/Exception.php";

/* PDF */
require "../../dompdf/autoload.inc.php";
use Dompdf\Dompdf;

/* FETCH STUDENTS */
$result = mysqli_query($conn, "
SELECT student_id, first_name, last_name, guardian_email, guardian_name, 
mother_email, mother_name, father_email, father_name, payment_by, grade, specific_subject, program
FROM enrollment_inquiries 
WHERE status='Active'
");

while ($row = mysqli_fetch_assoc($result)) {

    $student_id = $row['student_id'];

    /* AVOID DUPLICATE */
    $check = mysqli_query($conn, "
    SELECT id FROM invoices 
    WHERE student_id='$student_id' 
    AND DATE_FORMAT(invoice_date,'%Y-%m') = DATE_FORMAT(CURDATE(),'%Y-%m')
    ");

    if (mysqli_num_rows($check) > 0) {
        continue;
    }

    /* EMAIL LOGIC */
    $email_to = $row['guardian_email'];
    $name_to  = $row['guardian_name'];
    $payer_name = $row['guardian_name'];

    if($row['payment_by'] == "Mother"){
        $email_to = $row['mother_email'] ?: $row['guardian_email'];
        $name_to  = $row['mother_name'];
        $payer_name = $row['mother_name'];
    }
    elseif($row['payment_by'] == "Father"){
        $email_to = $row['father_email'] ?: $row['guardian_email'];
        $name_to  = $row['father_name'];
        $payer_name = $row['father_name'];
    }

    /* PRICE LOGIC (GST INCLUDED SYSTEM) */

    $price = 150;

    if(in_array($row['grade'], ["Grade 3","Grade 4","Grade 5","Grade 6","Grade 7","Grade 8"])){
        $price = 140;
    }
    elseif(in_array($row['grade'], ["Grade 9","Grade 10","Grade 11","Grade 12"])){
        $price = 160;
    }

    /* GST FIX (IMPORTANT) */
    $total = $price;
    $gst = $total * (5/105);
    $subtotal = $total - $gst;

    mysqli_query($conn, "
        INSERT INTO invoices
        (student_id,invoice_date,due_date,price,gst,total,status)
        VALUES
        ('$student_id',CURDATE(),DATE_ADD(CURDATE(),INTERVAL 15 DAY),'$subtotal','$gst','$total','Pending')
        ");

        $invoice_id = mysqli_insert_id($conn);

        $year = date("y");
        $invoice_number = "AC-$year-" . str_pad($invoice_id, 4, "0", STR_PAD_LEFT);

        mysqli_query($conn, "
        UPDATE invoices 
        SET invoice_number='$invoice_number' 
        WHERE id='$invoice_id'
        ");

    /* PDF DATA (IMPORTANT FIX) */
    $student = [
        "first_name"=>$row['first_name'],
        "last_name"=>$row['last_name'],
        "email"=>$email_to,
        "course_title"=>$row['specific_subject'],
        "created_at"=>date("Y-m-d"),
        "invoice_number"=>$invoice_number,
        "program"=>$row['program'],
        "payer_name"=>$payer_name
    ];

    $price = $subtotal;

    $logoBase64 = "data:image/png;base64," . base64_encode(file_get_contents("../../images/logo.png"));

    ob_start();
    include "../../invoice_template.php";
    $html = ob_get_clean();

    $dompdf = new Dompdf();
    $dompdf->loadHtml($html);
    $dompdf->setPaper("A4");
    $dompdf->render();

    $file = "../../temp_invoice_".$invoice_id.".pdf";
    file_put_contents($file, $dompdf->output());

    /* MAIL */
    $mail = new PHPMailer(true);

    try{
        $mail->isSMTP();
        $mail->Host='smtp.hostinger.com';
        $mail->SMTPAuth=true;
        $mail->Username='info@achieverscastle.com';
        $mail->Password='Amplic@@7408';
        $mail->SMTPSecure='ssl';
        $mail->Port=465;

        $mail->setFrom('info@achieverscastle.com','Achievers Castle');
        $mail->addAddress($email_to,$name_to);

        $mail->Subject="Monthly Invoice - ".$row['first_name']." ".$row['last_name'];

        $mail->isHTML(true);
        $mail->Body="
        Dear Parents/Guardians,<br><br>

        Please note that the monthly invoice has been generated. Kindly arrange to pay the fees at your earliest convenience.<br><br>

        Thank you for your cooperation.<br><br>

        Best regards,<br>
        <b>Team Achiever's Castle</b>
        ";

        $mail->addAttachment($file,"invoice.pdf");
        $mail->send();

    }catch(Exception $e){
        echo "Mail Error: ".$mail->ErrorInfo."<br>";
    }

}