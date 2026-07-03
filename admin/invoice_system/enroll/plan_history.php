<?php 
include "../../../db_config.php";

$student_id = $_GET['student_id'] ?? 0;

if (!$student_id) {
    echo "<p style='color:red;text-align:center;padding:20px;'>Invalid Student ID</p>";
    exit;
}

$student = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT * FROM enrollment_inquiries WHERE student_id = '$student_id'
"));

if (!$student) {
    echo "<p style='color:red;text-align:center;padding:20px;'>Student not found</p>";
    exit;
}

/* Plan & Invoice History */
$plan_result = mysqli_query($conn, "
    SELECT * 
    FROM student_plan_history
    WHERE student_id = '$student_id'
    ORDER BY id DESC
");

$invoice_result = mysqli_query($conn, "
    SELECT * 
    FROM invoices
    WHERE student_id = '$student_id'
    ORDER BY invoice_date DESC
");

/* Payments / Receipts */
$payments_result = mysqli_query($conn, "
    SELECT pay.*, i.invoice_number 
    FROM payments pay
    LEFT JOIN invoices i ON pay.invoice_id = i.id
    WHERE i.student_id = '$student_id'
    ORDER BY pay.payment_date DESC
");
?>

<link href="https://fonts.googleapis.com/css2?family=Love+Ya+Like+A+Sister&display=swap" rel="stylesheet">

<div class="student-history">

    <h2 class="dashboard-title">
        👨‍🎓 Student Full History - <?php echo htmlspecialchars($student['first_name'] . " " . $student['last_name']); ?>
    </h2>

    <!-- Student & Guardian Details -->
    <div class="detail-card">
        <h3>Student & Guardian Details</h3>
        <div class="detail-grid">

            <?php if(!empty($student['first_name']) || !empty($student['last_name'])): ?>
            <div class="detail-item"><strong>Student Name:</strong> 
                <?php echo htmlspecialchars($student['first_name']." ".$student['last_name']); ?>
            </div>
            <?php endif; ?>

            <?php if(!empty($student['dob'])): ?>
            <div class="detail-item"><strong>Date of Birth:</strong> 
                <?php echo date("d M Y", strtotime($student['dob'])); ?>
            </div>
            <?php endif; ?>

            <?php if(!empty($student['grade'])): ?>
            <div class="detail-item"><strong>Grade:</strong> <?php echo htmlspecialchars($student['grade']); ?></div>
            <?php endif; ?>

            <?php if(!empty($student['mode_of_education'])): ?>
            <div class="detail-item"><strong>Mode of Education:</strong> 
                <?php echo htmlspecialchars($student['mode_of_education']); ?>
            </div>
            <?php endif; ?>

            <?php if(!empty($student['program'])): ?>
            <div class="detail-item"><strong>Program:</strong> <?php echo htmlspecialchars($student['program']); ?></div>
            <?php endif; ?>

            <?php if(!empty($student['specific_subject'])): ?>
            <div class="detail-item"><strong>Subjects:</strong> 
                <?php echo htmlspecialchars($student['specific_subject']); ?>
            </div>
            <?php endif; ?>

            <?php if(!empty($student['enroll_date'])): ?>
            <div class="detail-item"><strong>Enroll Date:</strong> 
                <?php echo date("d M Y", strtotime($student['enroll_date'])); ?>
            </div>
            <?php endif; ?>

            <div class="divider"></div>

            <?php if(!empty($student['guardian_name'])): ?>
            <div class="detail-item"><strong>Guardian Name:</strong> 
                <?php echo htmlspecialchars($student['guardian_name']); ?>
            </div>
            <?php endif; ?>

            <?php if(!empty($student['guardian_email'])): ?>
            <div class="detail-item"><strong>Guardian Email:</strong> 
                <?php echo htmlspecialchars($student['guardian_email']); ?>
            </div>
            <?php endif; ?>

            <?php if(!empty($student['guardian_phone'])): ?>
            <div class="detail-item"><strong>Guardian Phone:</strong> 
                <?php echo htmlspecialchars($student['guardian_phone']); ?>
            </div>
            <?php endif; ?>

            <?php if(!empty($student['payment_by'])): ?>
            <div class="detail-item"><strong>Payment By:</strong> 
                <?php echo htmlspecialchars($student['payment_by']); ?>
            </div>
            <?php endif; ?>

        </div>
    </div>

    <!-- Parent Information (only if any data exists) -->
    <?php if(!empty($student['mother_name']) || !empty($student['father_name'])): ?>
    <div class="detail-card">
        <h3>Parent Information</h3>
        <div class="detail-grid">
            <?php if(!empty($student['mother_name'])): ?>
            <div class="detail-item"><strong>Mother:</strong> 
                <?php echo htmlspecialchars($student['mother_name']); ?>
                <?php if(!empty($student['mother_email']) || !empty($student['mother_phone'])): ?>
                    (<?php echo $student['mother_email'] ?: ''; ?> 
                     <?php echo $student['mother_phone'] ? '/ '.$student['mother_phone'] : ''; ?>)
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <?php if(!empty($student['father_name'])): ?>
            <div class="detail-item"><strong>Father:</strong> 
                <?php echo htmlspecialchars($student['father_name']); ?>
                <?php if(!empty($student['father_email']) || !empty($student['father_phone'])): ?>
                    (<?php echo $student['father_email'] ?: ''; ?> 
                     <?php echo $student['father_phone'] ? '/ '.$student['father_phone'] : ''; ?>)
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Emergency & Authorized -->
    <?php if(!empty($student['emergency_name']) || !empty($student['authorized_name']) || !empty($student['message'])): ?>
    <div class="detail-card">
        <h3>Emergency & Additional Information</h3>
        <div class="detail-grid">
            <?php if(!empty($student['emergency_name'])): ?>
            <div class="detail-item"><strong>Emergency Contact:</strong> 
                <?php echo htmlspecialchars($student['emergency_name']); ?>
                <?php echo $student['emergency_phone'] ? ' ('.$student['emergency_phone'].')' : ''; ?>
            </div>
            <?php endif; ?>

            <?php if(!empty($student['authorized_name'])): ?>
            <div class="detail-item"><strong>Authorized Pickup:</strong> 
                <?php echo htmlspecialchars($student['authorized_name']); ?>
                <?php echo $student['authorized_relation'] ? ' ('.$student['authorized_relation'].')' : ''; ?>
            </div>
            <?php endif; ?>

            <?php if(!empty($student['message'])): ?>
            <div class="detail-item full-width"><strong>Admin Notes:</strong><br>
                <?php echo nl2br(htmlspecialchars($student['message'])); ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Plan & Invoice History -->
    <div class="detail-card">
        <h3>Plan & Invoice History</h3>
        <div class="table-scroll">
            <table class="history-table">
                <thead>
                <tr>
                    <th>Program</th>
                    <th>Count</th>
                    <th>Subjects</th>
                    <th>Price</th>
                    <th>Start</th>
                    <th>End</th>
                    <th>Plan Status</th>
                </tr>
                </thead>
                <tbody>
                <?php if(mysqli_num_rows($plan_result) == 0): ?>
                    <tr><td colspan="11" class="no-data">No plan history found</td></tr>
                <?php else: while($row = mysqli_fetch_assoc($plan_result)): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['program']); ?></td>
                        <td><?php echo $row['program_count']; ?></td>
                        <td><?php echo htmlspecialchars($row['subjects']); ?></td>
                        <td>$<?php echo number_format($row['price'], 2); ?></td>
                        <td><?php echo date("d M Y", strtotime($row['start_date'])); ?></td>
                        <td><?php echo $row['end_date'] ? date("d M Y", strtotime($row['end_date'])) : '-'; ?></td>
                        <td><?php echo $row['status'] == "Active" ? 
                            '<span class="badge success">Active</span>' : 
                            '<span class="badge secondary">Expired</span>'; ?></td>
                     
                    </tr>
                <?php endwhile; endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Payment Receipts -->
    <?php if(mysqli_num_rows($payments_result) > 0): ?>
    <div class="detail-card">
        <h3>Payment Receipts</h3>
        <div class="table-scroll">
            <table class="history-table">
                <thead>
                <tr>
                    <th>Date</th>
                    <th>Amount</th>
                    <th>Method</th>
                    <th>Invoice</th>
                    <th>Receipt</th>
                </tr>
                </thead>
                <tbody>
                <?php while($pay = mysqli_fetch_assoc($payments_result)): ?>
                    <tr>
                        <td><?php echo date("d M Y", strtotime($pay['payment_date'])); ?></td>
                        <td>$<?php echo number_format($pay['amount'], 2); ?></td>
                        <td><span class="badge info"><?php echo htmlspecialchars($pay['payment_method']); ?></span></td>
                        <td><?php echo $pay['invoice_number'] ?? '-'; ?></td>
                        <td>
                            <a href="invoice_system/payments/generate_receipt_pdf.php?payment_id=<?php echo $pay['id']; ?>" 
                               class="btn-receipt" target="_blank"><i class="bi bi-file-earmark-pdf"></i> Receipt</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>
    <?php if(mysqli_num_rows($invoice_result) > 0): ?>
<div class="detail-card">
    <h3>All Invoices</h3>
    <div class="table-scroll">
        <table class="history-table">
            <thead>
            <tr>
                <th>Date</th>
                <th>Invoice No</th>
                <th>Status</th>
                <th>Amount</th>
                <th>Discount</th>
                <th>Extra</th>
                <th>Action</th>
            </tr>
            </thead>
            <tbody>
            <?php while($inv = mysqli_fetch_assoc($invoice_result)): ?>
                <tr>
                    <td><?php echo date("d M Y", strtotime($inv['invoice_date'])); ?></td>
                    <td><?php echo htmlspecialchars($inv['invoice_number']); ?></td>
                    <td>
                        <?php echo $inv['status'] == "Paid" 
                            ? '<span class="badge success">Paid</span>' 
                            : '<span class="badge warning">Pending</span>'; ?>
                    </td>
                    <td>$<?php echo number_format($inv['total'], 2); ?></td>
                                        <td>
                        <?php if(!empty($inv['discount_type']) && $inv['discount_amount'] > 0): ?>
                            <span class="badge info">
                                <?php echo ucfirst(str_replace("_"," ",$inv['discount_type'])); ?>
                                - $<?php echo number_format($inv['discount_amount'], 2); ?>
                            </span>
                        <?php else: ?> - <?php endif; ?>
                    </td>
                    <td>
                        <?php if(($inv['extra_type'] ?? '') !== '' && $inv['extra_amount'] > 0): ?>
                            <span class="badge warning">
                                + $<?php echo number_format($inv['extra_amount'], 2); ?>
                                (<?php echo $inv['extra_type'] === 'permanent' ? 'Permanent' : 'One Time'; ?>)
                            </span>
                        <?php else: ?> - <?php endif; ?>
                    </td>
                    <td>
                       <a href="#"
                        class="btn-view menu-link"
                        data-page="invoice_system/invoice/invoice_view.php?id=<?php echo $inv['id']; ?>">
                        View Invoice
                        </a>
                    </td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

    <?php endif; ?>

</div>

<style>
.student-history { width: 100%; padding: 10px; }
.dashboard-title {
    font-size: 32px;
    color: #05364d;
    margin-bottom: 25px;
    font-family: "Love Ya Like A Sister", cursive;
}

.detail-card {
    background: #fff;
    border-radius: 16px;
    padding: 24px;
    margin-bottom: 25px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.06);
}

.detail-card h3 {
    color: #05364d;
    margin-bottom: 18px;
    font-size: 20px;
    border-bottom: 2px solid #e8063c;
    padding-bottom: 8px;
}

.detail-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 14px 25px;
    line-height: 1.7;
}

.detail-item strong { color: #2a5298; }

.divider {
    grid-column: 1 / -1;
    height: 1px;
    background: #eee;
    margin: 10px 0;
}

.full-width { grid-column: 1 / -1; }

.history-table {
    width: 100%;
    min-width: 900px;
    border-collapse: collapse;
}

.history-table th, .history-table td {
    padding: 12px 10px;
    text-align: center;
    border-bottom: 1px solid #f0f0f0;
}

.btn-view, .btn-receipt{
    min-width: 110px;
    justify-content: center;
}

.history-table thead {
    background: #f8f9fa;
}

.badge {
    padding: 5px 10px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
}
.success { background: #d4edda; color: #155724; }
.secondary { background: #e2e3e5; color: #383d41; }
.warning { background: #fff3cd; color: #856404; }
.info { background: #d1ecf1; color: #0c5460; }

.btn-view, .btn-receipt {
    background: linear-gradient(160deg, #1e3a8a, #2563eb);
    color: white;
    box-shadow: 8px 0 15px rgba(0,0,0,0.35);
    padding: 6px 14px;
    border-radius: 20px;
    font-size: 13px;
    text-decoration: none;
    color: white;
    display: inline-flex;
    align-items: center;
    gap: 5px;
}

.table-scroll { overflow-x: auto; }

.no-data { text-align: center; padding: 30px; color: #777; }

.btn-view.disabled {
    background: linear-gradient(160deg, #9ca3af, #cbd5f5);
    box-shadow: none;
    cursor: not-allowed;
    opacity: 0.8;
}

/* Mobile */
@media (max-width: 768px) {
    .detail-grid { grid-template-columns: 1fr; gap: 12px; }
    .history-table { min-width: 700px; }
}
</style>