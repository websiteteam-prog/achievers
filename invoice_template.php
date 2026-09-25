<?php
$fullName = $student['first_name'] . ' ' . $student['last_name'];
$date = !empty($student['invoice_date']) 
    ? date('F d, Y', strtotime($student['invoice_date'])) 
    : date('F d, Y');
$course   = $student['course_title'] ?? 'All Programs';
$programName = $student['program'] ?? 'Program';
$subjectsArray = array_map('trim', explode(",", $course));
$subjectCount  = count($subjectsArray);
$programCount = $student['program_count'] ?? '';

if($programName === "Early Starters"){
    $courseDisplay = "Reading, Writing, Learning (All Programs)";
}
elseif(!empty($course)){

    $subjectsArray = array_map('trim', explode(",", $course));
    $subjectsArray = array_filter($subjectsArray);

    if($programCount == "all"){
        $courseDisplay = implode(", ", $subjectsArray) . " (All Programs)";
    }
    elseif($programCount == "2"){
        $courseDisplay = implode(", ", $subjectsArray) . " (2 Programs)";
    }
    elseif($programCount == "1"){
        $courseDisplay = $subjectsArray[0] ?? '';
    }
    else{
        $courseDisplay = implode(", ", $subjectsArray);
    }

}
else{
    $courseDisplay = "All Programs";
}

$discount_type   = $invoice['discount_type'] ?? '';
$discount_amount = (float)($invoice['discount_amount'] ?? 0);
$extra_type      = $invoice['extra_type'] ?? '';
$extra_amount    = (float)($invoice['extra_amount'] ?? 0);

// ⭐ ONE-TIME ENROLLMENT FEE — first invoice pe 50, monthly/normal pe 0
$enrollment_fee  = (float)($invoice['enrollment_fee'] ?? 0);

$disc      = in_array($discount_type, ['one_time','sibling']) ? $discount_amount : 0;
$extra_add = in_array($extra_type, ['one_time','permanent']) ? $extra_amount : 0;

// $total = final amount. Base tuition nikaalo (enrollment fee ALAG rakho):
$gross_tuition = $total + $disc - $extra_add - $enrollment_fee;

// GST sirf tuition part ke andar included (enrollment fee flat/GST-free):
$taxable = $total - $enrollment_fee;
$gst = $taxable - ($taxable / 1.05);
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Invoice</title>

<style>
@page { margin: 30px; }

body {
    font-family: DejaVu Sans, sans-serif;
    font-size: 13px;
    color: #333;
}

.invoice-wrapper { width: 100%; }

.header-table { width: 100%; margin-bottom: 20px; }
.header-table td { vertical-align: top; }
.logo img { width: 140px; }
.invoice-title { text-align: right; }
.invoice-title h1 { margin: 0; font-size: 30px; letter-spacing: 1px; }
.company-address { text-align: right; font-size: 12px; line-height: 18px; }

.bill-table { width: 100%; margin-top: 20px; }
.bill-table td { vertical-align: top; font-size: 13px; }
.bill-to { width: 55%; }
.invoice-meta { width: 45%; }
.invoice-meta table { width: 100%; }
.invoice-meta td { padding: 3px 0; }
.highlight { background: #f3f3f3; font-weight: bold; }

.items-table { width: 100%; border-collapse: collapse; margin-top: 30px; }
.items-table th { background: #3b3b3b; color: #fff; padding: 10px; text-align: left; }
.items-table td { padding: 10px; border-bottom: 1px solid #ddd; }

.totals-table { width: 100%; margin-top: 20px; }
.totals-table td { padding: 6px; }
.totals-label { text-align: right; width: 80%; }
.total-amount { font-weight: bold; border-top: 2px solid #333; }
.amount-due { background: #f3f3f3; font-weight: bold; }

.notes { margin-top: 40px; font-size: 12px; color: #555; }
.footer { position: fixed; bottom: 20px; left: 0; width: 100%; text-align: center; font-size: 11px; color: #777; }
</style>
</head>

<body>
<div class="invoice-wrapper">

<!-- HEADER -->
<table class="header-table">
<tr>
    <td class="logo">
        <img src="<?php echo $logoBase64; ?>" alt="Logo">
    </td>
    <td class="invoice-title">
        <h1>INVOICE</h1>
        <div class="company-address">
            <strong>Achievers Castle Learning Centre Ltd.</strong><br>
            11-102 Cope Crescent<br>
            Saskatoon, Saskatchewan S7T 0C7<br>
            Canada<br><br>
            (639) 384-2844
        </div>
    </td>
</tr>
</table>

<hr>

<!-- BILLING -->
<table class="bill-table">
<tr>
   <td class="bill-to">
    <strong>BILL TO</strong><br><br>
    <strong><?php echo $student['first_name'] . ' ' . $student['last_name']; ?></strong><br>
    <?php 
    $payer = trim($student['payer_name'] ?? '');
    $studentName = trim($student['first_name'].' '.$student['last_name']);
    ?>
    <?php if(!empty($payer) && $payer !== $studentName): ?>
        C/O <?php echo $payer; ?><br>
    <?php endif; ?>
    <?php echo $student['email'] ?? ''; ?>
</td>
    <td class="invoice-meta">
        <table>
            <tr>
                <td><strong>Invoice Number:</strong></td>
                <td><?php echo $student['invoice_number']; ?></td>
            </tr>
            <tr>
                <td><strong>Invoice Date:</strong></td>
                <td><?php echo $date; ?></td>
            </tr>
            <tr>
                <td><strong>Payment Due:</strong></td>
                <td><?php echo $date; ?></td>
            </tr>
            <tr class="highlight">
                <td>Amount Due (CAD):</td>
                <td>$<?php echo number_format($total, 2); ?></td>
            </tr>
        </table>
    </td>
</tr>
</table>

<!-- ITEMS -->
<table class="items-table">
<thead>
<tr>
    <th>Items</th>
    <th>Quantity</th>
    <th>Price</th>
    <th>Amount</th>
</tr>
</thead>
<tbody>
<tr>
   <td>
    <strong>Tuition Fees - <?php echo $programName; ?></strong><br>
    <?php echo $courseDisplay; ?>
    </td>
    <td>1</td>
    <td>$<?php echo number_format($gross_tuition, 2); ?></td>
    <td>$<?php echo number_format($gross_tuition, 2); ?></td>
</tr>

<?php if($enrollment_fee > 0): ?>
<!-- ⭐ ONE-TIME ENROLLMENT FEE LINE -->
<tr>
   <td>
    <strong>Enrollment Fee (One-time)</strong><br>
    First-time registration charge
    </td>
    <td>1</td>
    <td>$<?php echo number_format($enrollment_fee, 2); ?></td>
    <td>$<?php echo number_format($enrollment_fee, 2); ?></td>
</tr>
<?php endif; ?>
</tbody>
</table>

<!-- TOTALS -->
<table class="totals-table">
<tr>
    <td class="totals-label">Tuition Fees (incl. GST):</td>
    <td>$<?php echo number_format($gross_tuition, 2); ?></td>
</tr>

<?php if($disc > 0): ?>
<tr>
    <td class="totals-label">Discount (<?php echo ucfirst(str_replace("_"," ",$discount_type)); ?>):</td>
    <td>- $<?php echo number_format($disc, 2); ?></td>
</tr>
<?php endif; ?>

<?php if($extra_add > 0): ?>
<tr>
    <td class="totals-label">Extra Charge (<?php echo $extra_type === 'permanent' ? 'Permanent' : 'One Time'; ?>):</td>
    <td>+ $<?php echo number_format($extra_add, 2); ?></td>
</tr>
<?php endif; ?>

<?php if($enrollment_fee > 0): ?>
<!-- ⭐ ENROLLMENT FEE TOTALS ROW -->
<tr>
    <td class="totals-label">Enrollment Fee (One-time):</td>
    <td>+ $<?php echo number_format($enrollment_fee, 2); ?></td>
</tr>
<?php endif; ?>

<tr>
    <td class="totals-label">GST 5% (713080158RT0001) — included:</td>
    <td>$<?php echo number_format($gst, 2); ?></td>
</tr>

<tr class="total-amount">
    <td class="totals-label">Total:</td>
    <td>$<?php echo number_format($total, 2); ?></td>
</tr>
<tr>
    <td class="totals-label">Payment on <?php echo $date; ?> :</td>
    <td>$<?php echo number_format($total, 2); ?></td>
</tr>
<tr class="amount-due">
    <td class="totals-label">Amount Due (CAD):</td>
    <td>$<?php echo number_format($total, 2); ?></td>
</tr>
</table>

<!-- NOTES -->
<div class="notes">
<strong>Notes / Terms</strong><br>
Make all cheques payable to Achiever's Castle Learning Centre Ltd.<br>
Total due in 15 days. Overdue accounts subject to a service charge of 1% per month.
</div>

<div class="footer">
    Education is an investment. Thank you for investing with us!
</div>

</div>
</body>
</html>