<!-- Google Font -->
  <link href="https://fonts.googleapis.com/css2?family=Love+Ya+Like+A+Sister&display=swap" rel="stylesheet">

<?php
include "../../db_config.php";

$total=mysqli_fetch_assoc(mysqli_query($conn,"
SELECT COUNT(*) c 
FROM invoices 
JOIN enrollment_inquiries 
ON invoices.student_id=enrollment_inquiries.student_id
WHERE enrollment_inquiries.status != 'Cancelled'
"))['c'];
$paid=mysqli_fetch_assoc(mysqli_query($conn,"
SELECT COUNT(*) c 
FROM invoices 
JOIN enrollment_inquiries 
ON invoices.student_id=enrollment_inquiries.student_id
WHERE invoices.status='Paid' 
AND enrollment_inquiries.status != 'Cancelled'
"))['c'];

$pending=mysqli_fetch_assoc(mysqli_query($conn,"
SELECT COUNT(*) c 
FROM invoices 
JOIN enrollment_inquiries 
ON invoices.student_id=enrollment_inquiries.student_id
WHERE invoices.status='Pending' 
AND enrollment_inquiries.status != 'Cancelled'
"))['c'];

/* ✅ PAGINATION */
$limit = 7;
$page = isset($_GET['p']) ? (int)$_GET['p'] : 1;
if($page < 1) $page = 1;

$offset = ($page - 1) * $limit;

$search = $_GET['search'] ?? '';
$date   = $_GET['date'] ?? '';
$status = $_GET['status'] ?? '';

$where = "WHERE enrollment_inquiries.status != 'Cancelled'";

// 🔍 SEARCH (name + invoice number)
if(!empty($search)){
    $search = mysqli_real_escape_string($conn, $search);
    $where .= " AND (
        enrollment_inquiries.first_name LIKE '%$search%' 
        OR invoices.invoice_number LIKE '%$search%'
    )";
}

// 📅 DATE FILTER
if(!empty($date)){
    $where .= " AND DATE(invoices.created_at) = '$date'";
}

// 📌 STATUS FILTER
if(!empty($status)){
    $status = mysqli_real_escape_string($conn, $status);
    $where .= " AND invoices.status = '$status'";
}

$recent=mysqli_query($conn,"
SELECT invoices.*, enrollment_inquiries.first_name, enrollment_inquiries.status AS enroll_status
FROM invoices
LEFT JOIN enrollment_inquiries
ON invoices.student_id = enrollment_inquiries.student_id
$where
ORDER BY invoices.id DESC
LIMIT $limit OFFSET $offset
");

$total_rows = mysqli_fetch_assoc(mysqli_query($conn,"
SELECT COUNT(*) c 
FROM invoices
LEFT JOIN enrollment_inquiries
ON invoices.student_id = enrollment_inquiries.student_id
$where
"))['c'];

$total_pages = ceil($total_rows / $limit);

?>

<div class="invoice-dashboard">

<h2 class="dashboard-title">
<i class="bi bi-receipt"></i> Invoice Dashboard
</h2>

<div class="stats-grid">

<div class="stat-card total">
<div class="stat-icon"><i class="bi bi-file-earmark-text"></i></div>
<div>
<h3>Total Invoices</h3>
<h1><?php echo $total ?></h1>
</div>
</div>

<div class="stat-card paid">
<div class="stat-icon"><i class="bi bi-check-circle"></i></div>
<div>
<h3>Paid</h3>
<h1><?php echo $paid ?></h1>
</div>
</div>

<div class="stat-card pending">
<div class="stat-icon"><i class="bi bi-hourglass-split"></i></div>
<div>
<h3>Pending</h3>
<h1><?php echo $pending ?></h1>
</div>
</div>

</div>
<!-- 🔍 FILTER BAR -->
<form method="GET" class="filter-bar">

<input type="hidden" name="page" value="invoice_system/dashboard/invoice_dashboard.php">

<input type="text" name="search" placeholder="Search by name / invoice no..."
value="<?php echo $_GET['search'] ?? ''; ?>">

<input type="date" name="date"
value="<?php echo $_GET['date'] ?? ''; ?>">

<select name="status">
<option value="">All Status</option>
<option value="Paid" <?php if(($_GET['status'] ?? '')=='Paid') echo 'selected'; ?>>Paid</option>
<option value="Pending" <?php if(($_GET['status'] ?? '')=='Pending') echo 'selected'; ?>>Pending</option>
</select>

<button type="submit">Search</button>

<a href="teacher_dashboard.php?page=invoice_system/dashboard/invoice_dashboard.php" class="reset-btn">
Reset
</a>

</form>

<div class="invoice-table">

<!-- <h4>Recent Invoices</h4> -->

<div class="table-scroll">

<table class="table table-hover">

<thead>
<tr>
<th>Invoice</th>
<th>Student</th>
<th>Total</th>
<th>Date</th>
<th>Status</th>
<th>Action</th>
</tr>
</thead>

<tbody>

<?php if(mysqli_num_rows($recent) == 0){ ?>
<tr>
<td colspan="5" style="text-align:center;">No invoices found</td>
</tr>
<?php } else { ?>

<?php while($row=mysqli_fetch_assoc($recent)){ ?>

<tr>
<td><?php echo $row['invoice_number']?></td>
<td><?php echo $row['first_name']?></td>
<td>$<?php echo number_format($row['total'],2)?></td>
<td><?php echo date("d M Y", strtotime($row['created_at'])); ?></td>
<td>
<?php if($row['status']=="Paid"){ ?>
<span class="badge bg-success">Paid</span>
<?php } else { ?>
<span class="badge bg-warning text-dark">Pending</span>
<?php } ?>
</td>

<td class="action-btns">
<a class="btn btn-view"
href="teacher_dashboard.php?page=invoice_system/invoice/invoice_view.php&id=<?php echo $row['id']; ?>">
<i class="bi bi-eye"></i> View
</a>

<?php if($row['status'] == "Paid"){ ?>
<a class="btn btn-pay disabled-btn" href="javascript:void(0)">
<i class="bi bi-cash"></i> Paid
</a>
<?php } else { ?>
<a class="btn btn-pay"
href="teacher_dashboard.php?page=invoice_system/payments/record_payment.php&invoice_id=<?php echo $row['id']; ?>">
<i class="bi bi-cash"></i> Pay
</a>
<?php } ?>

</td>
</tr>

<?php } ?>

<?php } ?>

</tbody>

</table>

</div>

<?php if($total_pages > 1){ ?>
<div class="pagination-box">

<a href="?page=invoice_system/dashboard/invoice_dashboard.php&p=<?php echo $page-1; ?>&search=<?php echo $search; ?>&date=<?php echo $date; ?>&status=<?php echo $status; ?>" 
class="pg-btn <?php if($page<=1) echo 'disabled'; ?>">← Prev</a>

<span class="pg-info"><?php echo $page; ?> / <?php echo $total_pages; ?></span>

<a href="?page=invoice_system/dashboard/invoice_dashboard.php&p=<?php echo $page+1; ?>&search=<?php echo $search; ?>&date=<?php echo $date; ?>&status=<?php echo $status; ?>" 
class="pg-btn <?php if($page>=$total_pages) echo 'disabled'; ?>">Next →</a>

</div>
<?php } ?>

</div>

</div>


<style>

/* ===== GLOBAL ===== */
*{box-sizing:border-box;}

.invoice-dashboard{
  width:100%;
}

.dashboard-title{
font-size: 30px;
color: #05364d;
margin-bottom: 25px;
font-family: "Love Ya Like A Sister", cursive;
}

.filter-bar{
  display:flex;
  flex-wrap:wrap;
  gap:10px;
  margin-bottom:20px;
  margin-top: 30px;
}

.filter-bar input,
.filter-bar select{
  padding:8px 12px;
  border:1px solid #ccc;
  border-radius:8px;
  font-size:14px;
}

.filter-bar button{
  background:#05364d;
  color:#fff;
  border:none;
  padding:8px 16px;
  border-radius:8px;
  cursor:pointer;
}

.reset-btn{
  background:#ccc;
  padding:8px 16px;
  border-radius:8px;
  text-decoration:none;
  color:black;
}

/* ===== CARDS ===== */
.stats-grid{
  display:grid;
  grid-template-columns:1fr;
  gap:15px;
}

@media(min-width:768px){
  .stats-grid{
    grid-template-columns:repeat(3,1fr);
  }
}

.stat-card{
  display:flex;
  align-items:center;
  gap:12px;
  padding:15px; /* original padding */
  border-radius:15px;
  color:white;
}

.total{background:linear-gradient(180deg,#1e3c72,#2a5298);}
.paid{background:linear-gradient(135deg,#11998e,#38ef7d);}
.pending{background:linear-gradient(135deg,#ff9966,#ff5e62);}

.stat-icon{
  font-size:22px;
  padding:10px;
  border-radius:10px;
  background:rgba(255,255,255,0.2);
}

/* ===== TABLE ===== */
.invoice-table {
    background: white;
    padding: 20px 26px;
    border-radius: 15px;
    margin-top:30px;
}
.table-scroll{
  overflow-x:auto;
}
.table{
  width:100%;
  min-width:650px;
}
/* remove hover */
.table tbody tr:hover{
  background:transparent !important;
}

/* header grey */
.table thead{
  background:#f1f3f6;
}

.table th,
.table td{
  padding:12px 10px;
  vertical-align:middle;
}

/* fix column spacing */
.table th:nth-child(4),
.table td:nth-child(4){
  width:120px;
}

.table th:last-child,
.table td:last-child{
  width:180px;
  padding-right:8px;
}
.table thead th:last-child{
  text-align:center; 
}
/* ===== BUTTONS ===== */
.btn-view:hover,
.btn-pay:hover{
  background: white;
  transform: none !important;
  box-shadow: none !important;
  color:black !important;
}
.action-btns{
  display:flex;
  justify-content:flex-end;
  align-items:center;
  gap:8px;
}

.btn-view{
  background:#2d6cdf;
  color:#fff;
  padding:6px 12px;
  border-radius:20px;
  font-size:13px;
  display:flex;
  align-items:center;
  gap:5px;
}

.btn-pay{
  background:#198754;
  color:#fff;
  padding:6px 12px;
  border-radius:20px;
  font-size:13px;
  display:flex;
  align-items:center;
  gap:5px;
}

/* ===== PAGINATION ===== */

.pagination-box{
  display:flex;
  justify-content:flex-end;
  align-items:center;
  gap:12px;
  margin-top:18px;
}

.pg-btn{
  padding:6px 14px;
  background:#e60023;
  color:#fff;
  border-radius:6px;
  text-decoration:none;
}

.pg-btn.disabled{
  pointer-events:none;
  background:#ccc;
}

.disabled-btn{
  background: #ccc !important;
  color: #666 !important;
  cursor: not-allowed;
  pointer-events: none;
  opacity: 0.7;
}

.pg-info{
  font-weight:600;
}

/* ===== MOBILE ===== */

@media (max-width:768px){

.invoice-dashboard{
        padding: 0px;
    }
.invoice-table {
    padding: 20px 0px; 
}
  .stat-card{
    padding:12px;
  }

  .stat-card h1{
    font-size:22px;
  }

  .stat-icon{
    font-size:20px;
  }

  /* keep buttons in row */
  .action-btns{
    flex-direction:row;
    justify-content:flex-end;
  }

  .btn-view,
  .btn-pay{
    width:auto;
    padding:5px 10px;
    font-size:12px;
  }
  .main-content {
    padding: 30px 20px;
}
.dashboard-card{padding:0px;}
.pagination-box {
    justify-content: center;
   
}
}

</style>