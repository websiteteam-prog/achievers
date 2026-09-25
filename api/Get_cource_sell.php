<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include '../db_config.php';
header('Content-Type: application/json');

$data = [];

/* 1) THIS MONTH — revenue actually collected (from payments) */
$q = mysqli_query($conn, "
    SELECT COUNT(*) AS cnt, COALESCE(SUM(amount),0) AS revenue
    FROM payments
    WHERE YEAR(payment_date)  = YEAR(CURDATE())
      AND MONTH(payment_date) = MONTH(CURDATE())
");
$r = mysqli_fetch_assoc($q);
$data['month_revenue'] = (float)$r['revenue'];
$data['month_count']   = (int)$r['cnt'];

/* 2) LIFETIME — all money collected so far */
$q = mysqli_query($conn, "
    SELECT COUNT(*) AS cnt, COALESCE(SUM(amount),0) AS revenue
    FROM payments
");
$r = mysqli_fetch_assoc($q);
$data['total_revenue'] = (float)$r['revenue'];
$data['total_count']   = (int)$r['cnt'];

/* 3) OUTSTANDING — invoices raised but not paid */
$q = mysqli_query($conn, "
    SELECT COUNT(*) AS cnt, COALESCE(SUM(total),0) AS amount
    FROM invoices
    WHERE status = 'Pending'
");
$r = mysqli_fetch_assoc($q);
$data['pending_amount'] = (float)$r['amount'];
$data['pending_count']  = (int)$r['cnt'];

/* 4) NEW INVOICES THIS MONTH (new enrollments billed) */
$q = mysqli_query($conn, "
    SELECT COUNT(*) AS cnt
    FROM invoices
    WHERE YEAR(invoice_date)  = YEAR(CURDATE())
      AND MONTH(invoice_date) = MONTH(CURDATE())
      AND status != 'Cancelled'
");
$r = mysqli_fetch_assoc($q);
$data['new_invoices_month'] = (int)$r['cnt'];

echo json_encode(["success" => true, "data" => $data]);
?>