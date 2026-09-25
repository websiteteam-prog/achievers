<?php

require "../../../dompdf/autoload.inc.php";

use Dompdf\Dompdf;

include "../../../db_config.php";

$id=$_GET['invoice_id'];

$data=mysqli_fetch_assoc(mysqli_query($conn,"
SELECT 
    invoices.*,
    enrollment_inquiries.first_name,
    enrollment_inquiries.last_name,
    enrollment_inquiries.guardian_email,
    enrollment_inquiries.guardian_name,
    enrollment_inquiries.mother_name,
    enrollment_inquiries.mother_email,
    enrollment_inquiries.father_name,
    enrollment_inquiries.father_email,
    enrollment_inquiries.payment_by,
    enrollment_inquiries.program,
    enrollment_inquiries.program_count,
    enrollment_inquiries.specific_subject
FROM invoices
JOIN enrollment_inquiries
ON invoices.student_id = enrollment_inquiries.student_id
WHERE invoices.id='$id'
"));

$price=$data['price'];
$gst=$data['gst'];
$total=$data['total'];

$student=$data;
$student['invoice_date'] = $data['invoice_date'];
$student['program'] = !empty($data['program']) ? $data['program'] : 'Program';
$student['course_title'] = !empty($data['specific_subject']) ? $data['specific_subject'] : '';
$student['program_count'] = $data['program_count'] ?? '';
switch($data['payment_by']){

    case "Mother":
        $student['payer_name'] = $data['mother_name'];
        $student['email'] = !empty($data['mother_email']) 
            ? $data['mother_email'] 
            : $data['guardian_email'];
        break;

    case "Father":
        $student['payer_name'] = $data['father_name'];
        $student['email'] = !empty($data['father_email']) 
            ? $data['father_email'] 
            : $data['guardian_email'];
        break;

    default:
        $student['payer_name'] = $data['guardian_name'];
        $student['email'] = $data['guardian_email'];
        break;
}


$logoBase64="data:image/png;base64,".base64_encode(file_get_contents("../../../images/logo.png"));


$invoice = [
    "discount_type" => $data['discount_type'],
    "discount_amount" => $data['discount_amount'],
    "price_after_discount" => $data['price'], 
    "gst" => $data['gst'],
    "total" => $data['total'],
    "enrollment_fee" => $data['enrollment_fee'] ?? 0,  
    "extra_type" => $data['extra_type'] ?? '',
    "extra_amount" => $data['extra_amount'] ?? 0
];

$discount_description = $data['discount_description'] ?? '';

ob_start();

include "../../../invoice_template.php";

$html=ob_get_clean();

$dompdf=new Dompdf();

$dompdf->loadHtml($html);

$dompdf->setPaper("A4");

$dompdf->render();

$dompdf->stream("invoice.pdf",["Attachment"=>0]);