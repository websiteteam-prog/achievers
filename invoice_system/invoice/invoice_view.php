<?php
include "../../db_config.php";

$id = $_GET['id'];

$data = mysqli_fetch_assoc(mysqli_query($conn,"
SELECT 
    invoices.*, 
    invoices.status AS payment_status,
    enrollment_inquiries.*, 
    enrollment_inquiries.status AS enroll_status
FROM invoices
LEFT JOIN enrollment_inquiries
ON invoices.student_id = enrollment_inquiries.student_id
WHERE invoices.id='$id'
"));

if(!$data){
    die("Invoice not found or relation broken");
}
?>
<link href="https://fonts.googleapis.com/css2?family=Love+Ya+Like+A+Sister&display=swap" rel="stylesheet">

<div class="invoice-view-page">

<div class="invoice-header">
<h3><i class="bi bi-receipt"></i> Invoice Details</h3>
</div>

<div class="invoice-card">

<div class="invoice-grid">

<div class="info-box">
<label>Invoice Number</label>
<p><?php echo $data['invoice_number']?></p>
</div>

<div class="info-box">
<label>Student</label>
<p><?php echo $data['first_name']?> <?php echo $data['last_name']?></p>
</div>

<div class="info-box">
<label>Total Amount</label>
<p>$<?php echo $data['total']?></p>
</div>

<!-- PAYMENT STATUS -->
<div class="info-box">
<label>Payment Status</label>

<?php if($data['payment_status']=="Paid"){ ?>
<span class="badge bg-success">Paid</span>
<?php } else { ?>
<span class="badge bg-warning text-dark">Pending</span>
<?php } ?>

</div>

<!-- ENROLLMENT STATUS -->
<div class="info-box">
<label>Enrollment Status</label>

<?php if($data['enroll_status']=="Cancelled"){ ?>
<span class="badge bg-danger">Cancelled</span>
<?php } else { ?>
<span class="badge bg-success">Active</span>
<?php } ?>

</div>

</div>

<div class="invoice-actions">

<a class="btn btn-primary"
href="invoice_system/invoice/generate_invoice_pdf.php?invoice_id=<?php echo $id?>">
<i class="bi bi-download"></i> Download Invoice
</a>

<?php if($data['enroll_status'] == "Cancelled"){ ?>

<a class="btn btn-secondary disabled-btn" href="javascript:void(0)">
<i class="bi bi-x-circle"></i> Cancelled
</a>

<?php } elseif($data['payment_status'] == "Paid"){ ?>

<a class="btn btn-success disabled-btn" href="javascript:void(0)">
<i class="bi bi-check-circle"></i> Paid
</a>

<?php } else { ?>

<a class="btn btn-success"
href="teacher_dashboard.php?page=invoice_system/payments/record_payment.php&invoice_id=<?php echo $id?>">
<i class="bi bi-cash"></i> Record Payment
</a>

<?php } ?>

<?php if($data['enroll_status'] == "Cancelled"){ ?>

<a class="btn btn-danger disabled-btn" href="javascript:void(0)">
<i class="bi bi-x-circle"></i> Cancelled
</a>

<?php } else { ?>

<a class="btn btn-danger"
href="teacher_dashboard.php?page=invoice_system/invoice/cancel_enrollment.php?id=<?php echo $data['id']; ?>">
<i class="bi bi-x-circle"></i> Cancel Enrollment
</a>

<?php } ?>

</div>

</div>

</div>

<style>

.invoice-actions .btn{
  border-radius:25px;
}

.invoice-header{
margin-bottom:20px;
}

.invoice-header h3{
font-family:"Love Ya Like A Sister", cursive;
font-size:30px;
color:#05364d;
margin-bottom:30px;
}

.invoice-card{
background:white;
padding:25px;
border-radius:15px;
box-shadow:0 5px 15px rgba(0,0,0,0.05);
width:495px;
}

.invoice-grid{
display:grid;
grid-template-columns:repeat(2,1fr);
gap:20px;
margin-bottom:20px;
}

.info-box label{
font-size:13px;
color:#777;
display:block;
}

.info-box p{
font-size:16px;
font-weight:600;
margin-top:5px;
}

.invoice-actions{
display:flex;
gap:10px;
}

  .disabled-btn{
  pointer-events: none;
  opacity: 0.6;
  cursor: not-allowed;
}

/* MOBILE */
@media (max-width:768px){

  .invoice-card{
    padding:18px;
    width:100%;
  }

  .invoice-grid{
    grid-template-columns:1fr;
    gap:15px;
  }

  .info-box p{
    font-size:15px;
  }

  .invoice-actions{
    flex-direction:column;
    gap:10px;
  }

  .invoice-actions .btn{
    width:100%;
    justify-content:center;
    font-size:14px;
    padding:10px;
  }

  .invoice-header h3{
    font-size:20px;
  }
}
</style>