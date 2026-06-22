<?php

require "../../dompdf/autoload.inc.php";
use Dompdf\Dompdf;

include "../../db_config.php";
require "receipt_function.php";

$id = (int)($_GET['payment_id'] ?? 0);

if(!$id){
    die("Invalid Payment ID");
}

$data = mysqli_fetch_assoc(mysqli_query($conn,"
SELECT payments.*, invoices.*, enrollment_inquiries.*
FROM payments
JOIN invoices ON payments.invoice_id=invoices.id
JOIN enrollment_inquiries ON invoices.student_id=enrollment_inquiries.student_id
WHERE payments.id='$id'
"));

if(!$data){
    die("Payment not found");
}

$html = generateReceiptHTML($data);

$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper("A4","portrait");
$dompdf->render();

$dompdf->stream("receipt.pdf",["Attachment"=>0]);