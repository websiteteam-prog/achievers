<?php
include "../../db_config.php";

$result=mysqli_query($conn,"
SELECT 
    payments.*, 
    enrollment_inquiries.first_name,
    enrollment_inquiries.status AS enroll_status
FROM payments
LEFT JOIN invoices 
    ON payments.invoice_id=invoices.id
LEFT JOIN enrollment_inquiries 
    ON invoices.student_id=enrollment_inquiries.student_id
ORDER BY payments.id DESC
");

?>
<link href="https://fonts.googleapis.com/css2?family=Love+Ya+Like+A+Sister&display=swap" rel="stylesheet">
<div class="payment-page">

<div class="page-header">

<h2>
<i class="bi bi-cash-coin"></i>
Payments
</h2>

</div>

<div class="payment-card">

<table class="table table-hover align-middle">

<thead>

<tr>
<th>Sr No</th>
<th>Student</th>
<th>Amount</th>
<th>Method</th>
<th>Date</th>
<th>Receipt</th>
<th>Status</th>
</tr>

</thead>

<tbody>

<?php 
$sr = 1; 
while($row=mysqli_fetch_assoc($result)){ 
  ?>

<tr>

<td>
<b><?php echo $sr++; ?></b>
</td>

<td>
<?php echo $row['first_name']?>
</td>

<td>
$<?php echo $row['amount']?>
</td>

<td>

<span class="badge bg-info text-dark">

<?php echo $row['payment_method']?>

</span>

</td>

<td>
<?php echo $row['payment_date']?>
</td>

<td>

<a class="btn btn-sm btn-primary"

href="invoice_system/payments/generate_receipt_pdf.php?payment_id=<?php echo $row['id']?>"

target="_blank">

<i class="bi bi-file-earmark-pdf"></i>

Receipt

</a>

</td>
<td>
<?php if($row['enroll_status']=="Cancelled"){ ?>
    <span class="badge bg-danger">Cancelled</span>
<?php } else { ?>
    <span class="badge bg-success">Active</span>
<?php } ?>
</td>
</tr>

<?php } ?>

</tbody>

</table>

</div>

</div>


<style>

.page-header{
margin-bottom:20px;
}

.page-header h2{
font-family:"Love Ya Like A Sister", cursive;
font-size:30px;
color:#05364d;
margin-bottom:30px;
}

.payment-card{
background:white;
padding:20px;
border-radius:15px;
box-shadow:0 5px 15px rgba(0,0,0,0.05);
}
/* ================= MOBILE RESPONSIVE ================= */

@media (max-width:768px){

  /* 🔥 table scrollable banao */
  .payment-card{
    overflow-x:auto;
  }

  table{
    min-width:650px; /* horizontal scroll enable */
  }

  /* header font */
  .page-header h2{
    font-size:30px;
  }

  /* table compact */
  .table th,
  .table td{
    padding:10px 8px;
    font-size:13px;
    white-space:nowrap;
  }

  /* badge compact */
  .badge{
    font-size:11px;
    padding:5px 8px;
  }

  /* button compact */
  .btn{
    font-size:12px;
    padding:5px 10px;
  }

}
</style>