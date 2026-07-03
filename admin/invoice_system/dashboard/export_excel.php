<?php 
include "../../../db_config.php";
$base_url = (isset($_SERVER['HTTPS']) ? "https://" : "http://") 
          . $_SERVER['HTTP_HOST']
          . explode('/invoice_system', $_SERVER['SCRIPT_NAME'])[0] . "/";
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=invoice_full_report.xls");

$search = $_GET['search'] ?? '';
$date   = $_GET['date'] ?? '';
$status = $_GET['status'] ?? '';
$enroll_status = $_GET['enroll_status'] ?? '';
$billing = $_GET['billing'] ?? '';

$where = "WHERE 1=1";

// 🔁 BILLING FILTER
if($billing === 'paused'){
    $where .= " AND enrollment_inquiries.billing_paused = 1";
} elseif($billing === 'active'){
    $where .= " AND enrollment_inquiries.billing_paused = 0";
}

// 🔍 SEARCH
if(!empty($search)){
    $search = mysqli_real_escape_string($conn, $search);
    $where .= " AND (
        enrollment_inquiries.first_name LIKE '%$search%' 
        OR invoices.invoice_number LIKE '%$search%'
    )";
}

// 📅 DATE
if(!empty($date)){
    $where .= " AND DATE(enrollment_inquiries.enroll_date) = '$date'";
}

// 📌 INVOICE STATUS
if(!empty($status)){
    $where .= " AND invoices.status = '$status'";
}

// 📌 ENROLL STATUS
if(!empty($enroll_status)){
    $where .= " AND enrollment_inquiries.status = '$enroll_status'";
}

// ✅ FULL QUERY
$query = mysqli_query($conn,"
SELECT 
    invoices.id,
    invoices.invoice_number,
    invoices.price,
    invoices.gst,
    invoices.total,
    invoices.status AS invoice_status,

    enrollment_inquiries.first_name,
    enrollment_inquiries.last_name,
    enrollment_inquiries.program,
    enrollment_inquiries.specific_subject,
    enrollment_inquiries.status AS enroll_status,
    enrollment_inquiries.billing_paused,

    payments.id AS payment_id,
    payments.amount,
    payments.payment_date,
    payments.payment_method,
    payments.receipt_number

FROM invoices

LEFT JOIN enrollment_inquiries
ON invoices.student_id = enrollment_inquiries.student_id

LEFT JOIN payments
ON invoices.id = payments.invoice_id

$where

ORDER BY invoices.id DESC
");

// ✅ HEADER
echo "Invoice No\tStudent\tTotal\tInvoice Status\tEnrollment\tBilling\tPayment\tDate\tMethod\tReceipt No\tInvoice PDF\tReceipt PDF\n";
// ✅ DATA
while($row = mysqli_fetch_assoc($query)){

    $student = $row['first_name']." ".$row['last_name'];

    $invoice_link = $base_url . "invoice_system/invoice/generate_invoice_pdf.php?invoice_id=".$row['id'];

    $receipt_link = !empty($row['payment_id']) 
        ? $base_url . "invoice_system/payments/generate_receipt_pdf.php?payment_id=".$row['payment_id']
        : "";

    echo ($row['invoice_number'] ?? 'N/A') . "\t";
    echo ($student ?: 'N/A') . "\t";
    echo ($row['total'] ?? '0') . "\t";
    echo ($row['invoice_status'] ?? '-') . "\t";
    echo ($row['enroll_status'] ?? '-') . "\t";
    echo (($row['billing_paused'] ?? 0) == 1 ? 'Paused' : 'Active') . "\t";

    echo ($row['amount'] ?? 'No Payment') . "\t";
    echo ($row['payment_date'] ?? '-') . "\t";
    echo ($row['payment_method'] ?? '-') . "\t";
    echo ($row['receipt_number'] ?? '-') . "\t";

    // ✅ Invoice link
    echo "=HYPERLINK(\"".$invoice_link."\",\"Download Invoice\")\t";

    // ✅ Receipt link
    if(!empty($receipt_link)){
        echo "=HYPERLINK(\"".$receipt_link."\",\"Download Receipt\")\n";
    } else {
        echo "No Receipt\n";
    }
}
?>