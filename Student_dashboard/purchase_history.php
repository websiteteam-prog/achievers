<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include "../db_config.php";

if (!isset($_SESSION['student_id'])) {
    header("Location: ../student_login.php");
    exit();
}

$student_id = (int)$_SESSION['student_id'];

// Student details (for sidebar + name)
$stmt = $conn->prepare("SELECT * FROM students WHERE id = ?");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();

$student_name = trim(($student['first_name'] ?? '') . ' ' . ($student['last_name'] ?? ''));
$currentpage  = basename($_SERVER['PHP_SELF']);

// ==========================================================
// Fetch ALL invoices for this student (one per month)
// ==========================================================
$invoices = [];
$stmt = $conn->prepare("
SELECT
    i.id,
    i.invoice_number,
    i.invoice_date,
    i.due_date,
    i.price,
    i.gst,
    i.discount_amount,
    i.extra_amount,
    i.total,
    i.status,
    i.created_at,
    p.id AS payment_id
FROM invoices i

LEFT JOIN payments p
ON p.invoice_id = i.id

WHERE i.student_id = ?

ORDER BY i.invoice_date DESC, i.id DESC
");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) {
    $invoices[] = $row;
}

// ==========================================================
// Summary stats
// ==========================================================
$total_paid    = 0;
$paid_count    = 0;
$pending_count = 0;
$last_date     = null;

foreach ($invoices as $inv) {
    if (strtolower($inv['status']) === 'paid') {
        $total_paid += (float)$inv['total'];
        $paid_count++;
    } else {
        $pending_count++;
    }
    $d = $inv['invoice_date'] ?: $inv['created_at'];
    if ($d && (!$last_date || strtotime($d) > strtotime($last_date))) {
        $last_date = $d;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Purchase History</title>
<meta name="viewport" content="width=device-width, initial-scale=1">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
<link href="student.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Love+Ya+Like+A+Sister&display=swap" rel="stylesheet">

<style>
:root {
    --primary: #1e40af;
    --primary-light: #3b82f6;
    --gray: #6b7280;
    --shadow: 0 6px 20px rgba(0,0,0,0.07);
}

* { box-sizing: border-box; }

*{
    -webkit-tap-highlight-color:transparent;
}

html, body { max-width: 100%; overflow-x: hidden; }

body {
    background: linear-gradient(135deg,#f8f9ff,#e0e7ff);
    margin: 0;
    font-family: 'Segoe UI', system-ui, sans-serif;
}

.main-layout {
    display: flex;
    min-height: 100vh;
}

/* CONTENT AREA */
.content-area {
    margin-left: 260px;
    padding: 30px;
    width: calc(100% - 260px);
    max-width: calc(100% - 260px);
}

/* PAGE TITLE */
.page-title {
    font-size: clamp(28px, 4vw, 42px);
    font-weight: 400;
    margin-bottom: 6px;
    background: linear-gradient(to right, #e02121, #2f55a4);
    -webkit-background-clip: text;
    background-clip: text;
    -webkit-text-fill-color: transparent;
    font-family: "Love Ya Like A Sister", cursive;
    margin-left: 4px;
}

.page-sub {
    color: var(--gray);
    font-size: 15px;
    margin-top:-12px;
    margin-left: 72px;
}

/* SUMMARY TILES */
.summary-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 18px;
    margin-bottom: 26px;
}

.summary-card {
    border-radius: 18px;
    padding: 22px 24px;
    color: #fff;
    box-shadow: var(--shadow);
}

.summary-card small {
    opacity: 0.9;
    font-size: 13px;
    text-transform: uppercase;
    letter-spacing: .5px;
}

.summary-card h3 {
    font-weight: 700;
    margin: 6px 0 0;
    font-size: 26px;
    word-break: break-word;
}

.sc-blue   { background: linear-gradient(135deg,#4f46e5,#3b82f6); }
.sc-green  { background: linear-gradient(135deg,#16a34a,#22c55e); }
.sc-orange { background: linear-gradient(135deg,#f97316,#fb923c); }

/* TABLE CARD */
.table-container{
    background:#fff;
    border-radius:22px;
    padding:30px;
    box-shadow:0 12px 35px rgba(0,0,0,.08);
    overflow:hidden;
}

.table-responsive{
    overflow-x:auto;
    overflow-y:hidden;
    -webkit-overflow-scrolling:touch;
    scrollbar-width:none;
    -ms-overflow-style:none;
}

.table-responsive::-webkit-scrollbar{
    display:none;
}

.table{
    width:100%;
    margin-bottom:0;
    white-space:nowrap;
}

.table td{
    padding:18px 14px;
    vertical-align:middle;
    border-bottom:1px solid #eef2f7;
}

.table th{
    padding:18px 14px;
    vertical-align:middle;
    border-bottom:2px solid #e5e7eb;
}

.table tbody tr{
    background:#fff;
}

.table tbody tr:hover,
.table tbody tr:active,
.table tbody tr:focus{
    background:#fff !important;
}

.table-hover tbody tr:hover{
    background:#fff !important;
}

.table tbody td{
    background:#fff;
}

.table thead th {
    font-size: 13px;
    text-transform: uppercase;
    letter-spacing: .4px;
    color: var(--primary);
    border-bottom: 2px solid #e5e7eb;
}

.status-badge {
    padding: 5px 12px;
    border-radius: 50px;
    font-size: 12.5px;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 5px;
}

.page-header{
    margin-bottom:30px;
}

.page-heading{
    display:flex;
    align-items:center;
    gap:18px;
    margin-bottom:8px;
}

.icon-style{
    font-size:50px;
    line-height:1;
    flex-shrink:0;
    background:linear-gradient(to right,#e02121,#2f55a4);
    -webkit-background-clip:text;
    -webkit-text-fill-color:transparent;
    background-clip:text;
    color:transparent;
}

.page-title{
    margin:0 !important;
    font-size:42px;
    font-weight:400;
    background:linear-gradient(to right,#e02121,#2f55a4);
    -webkit-background-clip:text;
    -webkit-text-fill-color:transparent;
    background-clip:text;
    color:transparent;
    font-family:"Love Ya Like A Sister", cursive;
}

.status-paid    { background: #dcfce7; color: #15803d; }
.status-pending { background: #fef3c7; color: #b45309; }

/* Keep table readable — horizontal scroll on smaller screens */
@media (max-width: 1280px) {
    .table { min-width: 980px; }
}

/* TABLET */
@media (max-width: 992px) {
    .content-area {
        margin-left: 0;
        width: 100%;
        max-width: 100%;
        padding: 80px 20px 24px;
    }

    .summary-grid {
        grid-template-columns: 1fr;
        gap: 14px;
    }
}

/* MOBILE */
@media (max-width: 576px) {

    .page-heading{
        gap:12px;
    }

    .icon-style{
        font-size:38px;
    }

    .page-title{
        font-size:30px;
    }

    .page-sub{
        margin-left:50px;
        font-size:14px;
    }
    .content-area {
        padding: 75px 14px 20px;
    }

    .table-container {
        padding: 12px;
    }

    .summary-card {
        padding: 18px 20px;
    }

    .summary-card h3 {
        font-size: 22px;
    }
}

/* SIDEBAR BUTTON */
#sidebarToggle {
    top: 15px;
    left: 15px;
    z-index: 1100;
    border-radius: 50%;
    width: 48px;
    height: 48px;
}

/* ===============================
   ACTION BUTTONS
================================ */

.action-btns{
    display:flex;
    justify-content:center;
    align-items:center;
    gap:12px;
    flex-wrap:nowrap;
}

.action-btns .btn{
    width:120px;
    height:42px;
    flex-shrink:0;
}

.table th:nth-child(4),
.table td:nth-child(4){
    text-align:center;
    width:140px;
}

.table th:nth-child(5),
.table td:nth-child(5){
    text-align:center;
    width:150px;
}

.action-btns .btn{
    display:flex;
    align-items:center;
    justify-content:center;
    gap:6px;
    width:120px;
    height:42px;
    border-radius:25px;
    color:#fff !important;
    font-size:14px;
    font-weight:600;
    text-decoration:none;
    transition:.25s;
    box-shadow:0 6px 16px rgba(0,0,0,.18);
}

/* Invoice Button */
.btn-view{
    background:linear-gradient(160deg,#1e3a8a,#2563eb);
}

/* Receipt Button */
.btn-paid{
    background:linear-gradient(160deg,#15803d,#22c55e);
}

/* Hover */
.action-btns .btn:hover{
    transform:translateY(-2px);
    box-shadow:0 8px 18px rgba(0,0,0,.28);
    color:#fff !important;
}

/* Icons */
.action-btns .btn i{
    font-size:14px;
}

.table th:last-child,
.table td:last-child{
    width:290px;
    text-align:center;
}
.receipt-unavailable{
    display:flex;
    align-items:center;
    justify-content:center;
    gap:6px;
    width:130px;
    height:42px;
    border-radius:25px;
    background:#f3f4f6;
    color:#9ca3af;
    border:1px solid #e5e7eb;
    font-size:13px;
    font-weight:600;
    white-space:nowrap;
}

.receipt-unavailable i{
    font-size:14px;
}
/* Mobile */
@media(max-width:768px){

    .content-area{
        padding:80px 15px 20px;
    }

    .summary-grid{
        grid-template-columns:1fr;
    }

    .table-container{
        padding:18px;
    }

    .table{
        min-width:720px;
    }

    .action-btns{
        flex-direction:row;
        justify-content:center;
        gap:8px;
    }

    .action-btns .btn{
        width:110px;
        height:38px;
        font-size:13px;
    }

    .page-title{
        font-size:32px;
    }

    .page-sub{
        margin-left:50px;
        font-size:14px;
    }

}

@media(max-width:480px){

    .content-area{
        padding:75px 10px 20px;
    }

    .table-container{
        padding:14px;
    }

    .action-btns .btn{
        width:100px;
        font-size:12px;
    }

    .summary-card h3{
        font-size:22px;
    }

}
</style>
</head>

<body>
<div class="main-layout">

    <!-- SIDEBAR -->
    <?php include "student_sidebar.php"; ?>

    <!-- MOBILE SIDEBAR BUTTON -->
    <button class="btn btn-primary d-lg-none position-fixed" id="sidebarToggle">
        <i class="bi bi-list fs-4"></i>
    </button>

    <!-- CONTENT -->
    <div class="content-area">

        <div class="page-header">

    
        <div class="page-heading">
        <i class="bi bi-bag icon-style"></i>

        <h3 class="page-title mb-0">
            Purchase History
        </h3>
    </div>

    <p class="page-sub">
        Your monthly invoices and payment records
    </p>

</div>

        <!-- SUMMARY TILES -->
        <div class="summary-grid">
            <div class="summary-card sc-blue">
                <small>Total Paid</small>
                <h3>$<?= number_format($total_paid, 2) ?></h3>
            </div>
            <div class="summary-card sc-green">
                <small>Paid Invoices</small>
                <h3><?= $paid_count ?><?php if ($pending_count > 0): ?>
                    <span style="font-size:14px;font-weight:500;opacity:.85;">
                        &nbsp;(<?= $pending_count ?> pending)
                    </span>
                <?php endif; ?></h3>
            </div>
            <div class="summary-card sc-orange">
                <small>Latest Invoice</small>
                <h3><?= $last_date ? date("d M Y", strtotime($last_date)) : '—' ?></h3>
            </div>
        </div>

        <!-- TABLE -->
        <div class="table-container">
            <div class="table-responsive">
               <table class="table table-borderless align-middle">
                    <thead>
                        <tr>
                            <th>Invoice Id</th>
                            <th>Invoice Date</th>
                            <th>Due Date</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Invoice</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($invoices)): ?>
                        <tr>
                            <td colspan="9" class="text-center text-muted py-5">
                                <i class="bi bi-inbox display-6 d-block mb-2 opacity-50"></i>
                                No invoices found yet.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($invoices as $inv):
                            $is_paid = strtolower($inv['status']) === 'paid';
                        ?>
                            <tr>
                                <td class="fw-semibold"><?= htmlspecialchars($inv['invoice_number'] ?? '—') ?></td>
                                <td><?= !empty($inv['invoice_date']) ? date("d M Y", strtotime($inv['invoice_date'])) : '—' ?></td>
                                <td><?= !empty($inv['due_date']) ? date("d M Y", strtotime($inv['due_date'])) : '—' ?></td>
                                <td class="fw-bold text-success">$<?= number_format((float)$inv['total'], 2) ?></td>
                                <td>
                                    <?php if ($is_paid): ?>
                                        <span class="status-badge status-paid">
                                            <i class="bi bi-check-circle-fill"></i> Paid
                                        </span>
                                    <?php else: ?>
                                        <span class="status-badge status-pending">
                                            <i class="bi bi-clock-fill"></i> Pending
                                        </span>
                                    <?php endif; ?>
                                </td>
                          <td class="action-btns">

                        <a href="../admin/invoice_system/invoice/generate_invoice_pdf.php?invoice_id=<?= $inv['id']; ?>"
                        class="btn btn-view"
                        target="_blank">
                            <i class="bi bi-file-earmark-pdf"></i>
                            Invoice
                        </a>

                        <?php if (strtolower($inv['status']) === 'paid' && !empty($inv['payment_id'])): ?>

                            <a href="../admin/invoice_system/payments/generate_receipt_pdf.php?payment_id=<?= $inv['payment_id']; ?>"
                            class="btn btn-paid"
                            target="_blank">
                                <i class="bi bi-receipt"></i>
                                Receipt
                            </a>

                        <?php else: ?>

                            <span class="receipt-unavailable">
                            <i class="bi bi-receipt"></i>
                            Not Available
                        </span>

                        <?php endif; ?>

                    </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('sidebarToggle')?.addEventListener('click', function () {
    document.getElementById('studentSidebar')?.classList.toggle('show');
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>