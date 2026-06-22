<?php
include "../../db_config.php";

$id = $_GET['id'] ?? 0;

if(!$id){
    die("Invalid Invoice ID");
}

$data = mysqli_fetch_assoc(mysqli_query($conn,"
SELECT invoices.*, enrollment_inquiries.*
FROM invoices
JOIN enrollment_inquiries
ON invoices.student_id=enrollment_inquiries.student_id
WHERE invoices.id='$id'
"));
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

<div class="info-box">
<label>Status</label>

<?php if($data['status']=="Paid"){ ?>

<span class="badge bg-success">Paid</span>

<?php } else { ?>

<span class="badge bg-warning text-dark">Pending</span>

<?php } ?>

</div>

</div>

<div class="invoice-actions">

<a class="btn btn-primary"
href="invoice_system/invoice/generate_invoice_pdf.php?invoice_id=<?php echo $id?>">

<i class="bi bi-download"></i> Download Invoice

</a>

<?php if($data['status'] != "Paid" && $data['status'] != "Cancelled"){ ?>

<a class="btn btn-success"
href="teacher_dashboard.php?page=invoice_system/payments/record_payment.php&invoice_id=<?php echo $id?>">

<i class="bi bi-cash"></i> Record Payment
</a>

<?php } ?>

<a class="btn btn-danger"
href="cancel_enrollment.php?id=<?php echo $data['id']; ?>">
Cancel Enrollment
</a>

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
width:430px;
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
/* ================= MOBILE RESPONSIVE ================= */

@media (max-width:768px){

  .invoice-card{
    padding:18px;
    width:100%;
  }

  /* 🔥 grid ko single column */
  .invoice-grid{
    grid-template-columns:1fr;
    gap:15px;
  }

  .info-box p{
    font-size:15px;
  }

  /* 🔥 buttons stack */
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

  /* header size */
  .invoice-header h3{
    font-size:20px;
  }

}
</style>