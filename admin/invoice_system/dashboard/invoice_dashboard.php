<!-- Google Font -->
  <link href="https://fonts.googleapis.com/css2?family=Love+Ya+Like+A+Sister&display=swap" rel="stylesheet">

<?php
include "../../../db_config.php";

$total=mysqli_fetch_assoc(mysqli_query($conn,"
SELECT COUNT(*) c 
FROM invoices 
LEFT JOIN enrollment_inquiries 
ON invoices.student_id=enrollment_inquiries.student_id
WHERE invoices.status != 'Cancelled'
"))['c'];
$paid=mysqli_fetch_assoc(mysqli_query($conn,"
SELECT COUNT(*) c 
FROM invoices 
LEFT JOIN enrollment_inquiries 
ON invoices.student_id=enrollment_inquiries.student_id
WHERE invoices.status='Paid'
"))['c'];

$pending=mysqli_fetch_assoc(mysqli_query($conn,"
SELECT COUNT(*) c 
FROM invoices 
LEFT JOIN enrollment_inquiries 
ON invoices.student_id=enrollment_inquiries.student_id
WHERE invoices.status='Pending'
"))['c'];


$search = $_GET['search'] ?? '';
$date   = $_GET['date'] ?? '';
$status = $_GET['status'] ?? '';
$enroll_status = $_GET['enroll_status'] ?? '';
$billing = $_GET['billing'] ?? '';
// ✅ PAGINATION
$allowed_limits = [5, 10, 20, 50, 100];
$limit = (isset($_GET['per_page']) && in_array((int)$_GET['per_page'], $allowed_limits))
    ? (int)$_GET['per_page']
    : 5;
$page_no = isset($_GET['p']) ? (int)$_GET['p'] : 1;

if($page_no < 1) $page_no = 1;

$offset = ($page_no - 1) * $limit;
$where = "WHERE 1=1";
$where .= " AND invoices.status != 'Cancelled'";

// 🔁 BILLING FILTER
if($billing === 'paused'){
    $where .= " AND enrollment_inquiries.billing_paused = 1";
} elseif($billing === 'active'){
    $where .= " AND enrollment_inquiries.billing_paused = 0";
}

if(!empty($enroll_status)){
    $enroll_status = mysqli_real_escape_string($conn, $enroll_status);

    if($enroll_status == "Cancelled"){
        $where .= " AND enrollment_inquiries.status = 'Cancelled'";
    } elseif($enroll_status == "Expired"){
        $where .= " AND sph.status = 'Expired'";
    } elseif($enroll_status == "Active"){
        $where .= " AND enrollment_inquiries.status != 'Cancelled' AND (sph.status = 'Active' OR sph.status IS NULL)";
    }
}

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
    $where .= " AND DATE(enrollment_inquiries.enroll_date) = '$date'";
}

// 📌 STATUS FILTER
if(!empty($status)){
    $status = mysqli_real_escape_string($conn, $status);
    $where .= " AND invoices.status = '$status'";
}

$recent=mysqli_query($conn,"
SELECT invoices.*, 
       enrollment_inquiries.first_name, 
       enrollment_inquiries.enroll_date, 
       enrollment_inquiries.billing_paused, 

       CASE 
    WHEN enrollment_inquiries.status = 'Cancelled' THEN 'Cancelled'
    WHEN sph.status = 'Expired' THEN 'Expired'
    WHEN sph.status = 'Active' THEN 'Active'
    ELSE 'Active'
    END AS enroll_status

FROM invoices
LEFT JOIN enrollment_inquiries
ON invoices.student_id = enrollment_inquiries.student_id

LEFT JOIN student_plan_history sph
ON sph.id = (
    SELECT s2.id
    FROM student_plan_history s2
    WHERE s2.student_id = invoices.student_id
      AND s2.start_date <= invoices.invoice_date
    ORDER BY s2.start_date DESC, s2.id DESC
    LIMIT 1
)

$where
ORDER BY invoices.id DESC
LIMIT $offset, $limit
");

// ✅ TOTAL RECORDS FOR PAGINATION
$total_query = mysqli_query($conn,"
SELECT COUNT(*) as total
FROM invoices
LEFT JOIN enrollment_inquiries
ON invoices.student_id = enrollment_inquiries.student_id

LEFT JOIN student_plan_history sph
ON sph.id = (
    SELECT s2.id
    FROM student_plan_history s2
    WHERE s2.student_id = invoices.student_id
      AND s2.start_date <= invoices.invoice_date
    ORDER BY s2.start_date DESC, s2.id DESC
    LIMIT 1
)

$where
");

$total_records = mysqli_fetch_assoc($total_query)['total'];
$total_pages = ceil($total_records / $limit);

?>

<div class="invoice-dashboard">

<h2 class="dashboard-title">
<i class="bi bi-wallet2"></i> Invoice Dashboard
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
<form class="filter-bar" id="filterForm">

<!-- 🔍 SEARCH -->
<div class="filter-item search-box">
<input type="text" name="search" placeholder="🔍 Search name / invoice..."
value="<?php echo $_GET['search'] ?? ''; ?>">
</div>

<!-- 📅 DATE -->
<div class="filter-item">
<input type="date" name="date"
value="<?php echo $_GET['date'] ?? ''; ?>">
</div>

<!-- 📌 STATUS -->
<div class="filter-item">
<select name="status">
<option value="">All Status</option>
<option value="Paid" <?php if(($_GET['status'] ?? '')=='Paid') echo 'selected'; ?>>Paid</option>
<option value="Pending" <?php if(($_GET['status'] ?? '')=='Pending') echo 'selected'; ?>>Pending</option>
</select>
</div>

<div class="filter-item">
<select name="billing">
<option value="">All Billing</option>
<option value="active" <?php if(($_GET['billing'] ?? '')=='active') echo 'selected'; ?>>Active</option>
<option value="paused" <?php if(($_GET['billing'] ?? '')=='paused') echo 'selected'; ?>>Paused</option>
</select>
</div>

<div class="filter-item">
<select name="enroll_status">
<option value="">All Enrollment</option>

<option value="Active" <?php if(($_GET['enroll_status'] ?? '')=='Active') echo 'selected'; ?>>
Active
</option>

<option value="Cancelled" <?php if(($_GET['enroll_status'] ?? '')=='Cancelled') echo 'selected'; ?>>
Cancelled
</option>

<option value="Expired" <?php if(($_GET['enroll_status'] ?? '')=='Expired') echo 'selected'; ?>>
Expired
</option>

</select>
</div>

<!-- 🔘 BUTTONS -->
<div class="filter-actions">
<button type="submit">Apply</button>

<a href="?page=invoice_system/dashboard/invoice_dashboard.php" 
class="reset-btn">
Reset
</a>

<a href="invoice_system/dashboard/export_excel.php?search=<?php echo $search; ?>&date=<?php echo $date; ?>&status=<?php echo $status; ?>&enroll_status=<?php echo $enroll_status; ?>&billing=<?php echo $billing; ?>" 
class="btn-success">
Download 
</a>
</div>

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
<th>Invoice Date</th>
<th>Enroll Date</th>
<th>Status</th>
<th>Enrollment</th>
<th>Billing</th>
<th>Action</th>
</tr>
</thead>

<tbody>

<?php if(mysqli_num_rows($recent) == 0){ ?>
<tr>
<td colspan="9" style="text-align:center;">No invoices found</td>
</tr>
<?php }  else { ?>

<?php while($row=mysqli_fetch_assoc($recent)){ ?>
<?php
$today = date("Y-m-d");
$is_overdue = ($row['due_date'] < $today && strtolower($row['status']) != "paid");
?>
<tr class="<?php echo (strtolower($row['enroll_status'])=='cancelled') ? 'cancel-row' : ''; ?> <?php echo (($row['billing_paused'] ?? 0)==1) ? 'paused-row' : ''; ?>">
<td><?php echo $row['invoice_number']?></td>
<td><?php echo $row['first_name']?></td>
<td>
    $<?php echo number_format($row['total'], 2); ?>
</td>

<td>
    <?php 
    echo !empty($row['invoice_date']) 
        ? date("d M Y", strtotime($row['invoice_date'])) 
        : '-'; 
    ?>
</td>

<td>
    <?php 
    echo !empty($row['enroll_date']) 
        ? date("d M Y", strtotime($row['enroll_date'])) 
        : '-'; 
    ?>
</td>
<td>
<?php if($row['status']=="Paid"){ ?>

<span class="badge bg-success">Paid</span>

<?php } elseif($is_overdue){ ?>

<span class="badge bg-danger">Overdue</span>

<?php } else { ?>

<span class="badge bg-warning text-dark">Pending</span>

<?php } ?>
</td>

<td>
<?php 
$status = strtolower($row['enroll_status']);

if($status == "cancelled"){
    echo '<span class="badge bg-danger">Cancelled</span>';
}
elseif($status == "expired"){
    echo '<span class="badge bg-secondary">Expired</span>';
}
elseif($status == "active"){
    echo '<span class="badge bg-success">Active</span>';
}
else{
    echo '<span class="badge bg-dark">Unknown</span>';
}
?>
</td>
<td>
<?php 
$estatus = strtolower($row['enroll_status']);

if($estatus == "cancelled" || $estatus == "expired"): 
?>
    <span class="billing-status na"><i class="bi bi-dash-circle"></i> N/A</span>

<?php elseif(($row['billing_paused'] ?? 0) == 1): ?>
    <div class="billing-wrap">
        <span class="billing-status paused"><i class="bi bi-pause-circle-fill"></i> Paused</span>
        <button class="billing-btn resume toggle-billing"
                data-student="<?php echo $row['student_id']; ?>" data-pause="0">
            <i class="bi bi-play-fill"></i> Resume
        </button>
    </div>

<?php else: ?>
    <div class="billing-wrap">
        <span class="billing-status active"><i class="bi bi-broadcast"></i> Active</span>
        <button class="billing-btn pause toggle-billing"
                data-student="<?php echo $row['student_id']; ?>" data-pause="1">
            <i class="bi bi-pause-fill"></i> Pause
        </button>
    </div>

<?php endif; ?>
</td>
<td class="action-btns">

<!-- 👀 VIEW (UNCHANGED) -->
<a href="#"
   class="btn btn-view menu-link"
   data-page="invoice_system/invoice/invoice_view.php?id=<?php echo $row['id']; ?>">
   <i class="bi bi-eye"></i> View
</a>

<?php 
$enroll = strtolower($row['enroll_status']);
$payment = strtolower($row['status']);

$btnClass = "";
$btnText = "";
$icon = "";
$link = "#";
$extraClass = "";

/* 🎯 LOGIC */
if($payment == "paid"){
    $btnClass = "btn-paid";
    $btnText = "Paid";
    $icon = "bi-wallet2";
    $extraClass = "disabled-btn";

} elseif($enroll == "cancelled"){
    $btnClass = "btn-cancelled";
    $btnText = "Cancelled";
    $icon = "bi-x-circle";
    $extraClass = "disabled-btn";

} elseif($enroll == "expired"){
    $btnClass = "btn-expired";
    $btnText = "Expired";
    $icon = "bi-clock";
    $extraClass = "disabled-btn";

} else {
    $btnClass = "btn-pay";
    $btnText = $is_overdue ? "Pay Late" : "Pay";
    $icon = "bi-wallet2";
    $link = "invoice_system/payments/record_payment.php?invoice_id=".$row['id'];
}
?>

<a href="#"
   class="btn <?php echo $btnClass; ?> <?php echo $extraClass; ?> menu-link"
   data-page="<?php echo $link; ?>">

   <i class="bi <?php echo $icon; ?>"></i> 
   <?php echo $btnText; ?>

</a>

</td>
</tr>

<?php } ?>

<?php } ?>

</tbody>

</table>
</div>
<!-- PAGINATION -->
<div class="pagination-box">

    <span class="pg-info">Page <?php echo $page_no; ?> of <?php echo max($total_pages,1); ?></span>

    <button type="button" class="page-nav prev-btn"
        <?php echo ($page_no <= 1) ? 'disabled' : ''; ?>
        data-page="<?php echo $page_no - 1; ?>">
        <i class="bi bi-chevron-left"></i> Previous
    </button>

    <select class="per-page-select" id="perPageSelect">
        <?php foreach($allowed_limits as $opt){ ?>
            <option value="<?php echo $opt; ?>" <?php echo ($opt == $limit) ? 'selected' : ''; ?>>
                <?php echo $opt; ?>
            </option>
        <?php } ?>
    </select>

    <button type="button" class="page-nav next-btn"
        <?php echo ($page_no >= $total_pages) ? 'disabled' : ''; ?>
        data-page="<?php echo $page_no + 1; ?>">
        Next <i class="bi bi-chevron-right"></i>
    </button>

</div>




</div>

</div>


<style>
  .filter-actions{
  display:flex;
  gap:10px;
}

.filter-actions > *{
  flex:1;
}
.pagination-box{
  display:flex;
  align-items:center;
  justify-content:flex-end;
  gap:14px;
  margin-top:20px;
  flex-wrap:wrap;
}

.pg-info{
  font-size:13px;
  color:#8a94a6;
  margin-right:auto;
}

.page-nav{
  padding:10px 20px;
  border:none;
  border-radius:30px;
  background:linear-gradient(180deg,#1e3c72,#2a5298);
  color:#fff;
  font-size:14px;
  font-weight:600;
  cursor:pointer;
  display:inline-flex;
  align-items:center;
  gap:6px;
  transition:.2s ease;
}
.page-nav:hover:not(:disabled){
  background:#0a4c6c;
  transform:translateY(-1px);
}
.page-nav:disabled{
  opacity:.4;
  cursor:not-allowed;
  transform:none;
}

.per-page-select{
  padding:9px 16px;
  border:2px solid #05364d;
  border-radius:30px;
  color:#05364d;
  font-weight:600;
  font-size:14px;
  background:#fff;
  cursor:pointer;
}

@media(max-width:768px){
  .pagination-box{ justify-content:center; }
  .pg-info{ margin-right:0; width:100%; text-align:center; order:-1; }
}

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
  margin:20px 0;
  align-items:center;
}

.filter-item{
  flex:1;
  min-width:140px;
}

.search-box{
  flex:2;
}

.filter-bar input,
.filter-bar select{
  width:100%;
  padding:10px;
  border:1px solid #ddd;
  border-radius:10px;
  font-size:14px;
}

.filter-actions{
  display:flex;
  gap:10px;
}

.filter-bar button{
  background:linear-gradient(180deg,#1e3c72,#2a5298);
  color:#fff;
  border:none;
  padding:10px 16px;
  border-radius:10px;
  cursor:pointer;
}

.overdue-text{
  font-size:11px;
  color:#dc2626;
  display:block;
  margin-bottom:3px;
}

.reset-btn{
  background:grey;
  padding:10px 16px;
  border-radius:10px;
  text-decoration:none;
  color:#fff;
}

.btn-success{
  background: linear-gradient(160deg, #15803d, #22c55e);
  padding:10px 16px;
  border-radius:10px;
  text-decoration:none;
  color:#fff;
}

.btn-view,
.btn-pay{
  min-width: 90px;
  justify-content: center;
  box-shadow: 0 4px 10px rgba(0,0,0,0.2);
}

.action-btns .btn{
  height: 36px;
   min-width: 110px; 
  justify-content:center;
}

/* ===== CARDS ===== */
.stats-grid{
  display:grid;
  grid-template-columns:1fr;
  gap:15px;
}

/* Disabled action buttons (Paid/Cancelled/Expired) — click hi na ho */
.action-btns .disabled-btn{
    pointer-events: none;
    cursor: not-allowed;
    opacity: .9;
}

/* Action cell ko table-cell rakho taaki row ki line seedhi rahe */
.invoice-table .table td.action-btns{
    display: table-cell;
    vertical-align: middle;
    text-align: center;
    white-space: nowrap;
}
.invoice-table .table td.action-btns .btn{
    display: inline-flex;
    vertical-align: middle;
}
.invoice-table .table td.action-btns .btn + .btn{
    margin-left: 10px;
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
  -webkit-overflow-scrolling: touch;
}
.table{
  width:100%;
  min-width: 900px;
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
  width:160px;
}

.table th:nth-child(7),
.table td:nth-child(7){
  min-width:130px;
}

.table th:last-child,
.table td:last-child{
  width:180px;
}

/* .table thead th:last-child{
  text-align:left;
} */

/* ===== BUTTONS ===== */

.action-btns{
  display:flex;
  justify-content:space-between;
  align-items:center;
  gap:8px;
  flex-wrap:nowrap;
   min-width: 250px;
}

.action-btns .btn{
  min-width:110px;
  height:36px;
  justify-content:center;
  white-space:nowrap;
  flex-shrink:0;
}
/* ===== COMMON BUTTON STYLE ===== */
.btn{
  padding:6px 14px;
  border-radius:25px;
  font-size:13px;
  display:flex;
  align-items:center;
  gap:6px;
  color:#fff;
  box-shadow: 0 6px 16px rgba(0,0,0,0.25);
  transition:0.2s ease;
}

/* 👀 VIEW (already hai but ensure same look) */
.btn-view{
  background: linear-gradient(160deg, #1e3a8a, #2563eb);
}

/* 💚 PAY / PAID */
.btn-pay,
.btn-paid{
  background: linear-gradient(160deg, #15803d, #22c55e);
}

/* ❌ CANCELLED */
.btn-cancelled{
  background: linear-gradient(160deg, #b91c1c, #ef4444);
  cursor:not-allowed;
  opacity:0.9;
}

/* ⏳ EXPIRED */
.btn-expired{
  background: linear-gradient(160deg, #6b7280, #9ca3af);
  cursor:not-allowed;
  opacity:0.9;
}

/* ✨ HOVER EFFECT */
.btn:hover{
  transform: translateY(-2px);
  box-shadow: 0 8px 18px rgba(0,0,0,0.3);
}
.action-btns:hover{color :white;}

/* ===== BILLING PAUSE/RESUME ===== */
.billing-wrap{
  display:flex;
  flex-direction:row;    
  align-items:center;
  justify-content:center;
  gap:8px;
  white-space:nowrap;
}

/* status pill */
.billing-status{
  display:inline-flex;
  align-items:center;
  gap:5px;
  font-size:11px;
  font-weight:600;
  padding:3px 10px;
  border-radius:20px;
  letter-spacing:.2px;
  display:none;
}
.billing-status.active{
  background:#e7f7ee;
  color:#158a52;
}
.billing-status.paused{
  background:#fdeaea;
  color:#c0392b;
}
.billing-status.active i{ color:#22c55e; }

/* toggle button */
.billing-btn{
  display:inline-flex;
  align-items:center;
  justify-content:center;
  gap:6px;
  border:none;
  cursor:pointer;
  font-size:13px;          
  font-weight:600;
  color:#fff;
  padding:8px 18px;       
  min-width:110px;        
  height:36px;           
  border-radius:25px;     
  transition:all .2s ease;
  box-shadow:0 4px 12px rgba(0,0,0,.15);
}

.billing-btn.pause{
  background:linear-gradient(160deg,#f59e0b,#f97316);
}
.billing-btn.resume{
  background:linear-gradient(160deg,#0ea5e9,#2563eb);
}
.billing-btn:hover{
  transform:translateY(-2px);
  box-shadow:0 8px 18px rgba(0,0,0,.22);
}
.billing-btn:active{ transform:translateY(0); }
.billing-btn:disabled{ opacity:.6; cursor:not-allowed; }

/* dim the whole row when paused */
.paused-row td{
  background:#fbfbfd;
  opacity:.85;
}
.paused-row td:first-child{
  box-shadow: inset 3px 0 0 #f59e0b;   /* amber left strip */
}

/* toast */
.billing-toast{
  position:fixed;
  bottom:25px;
  left:50%;
  transform:translateX(-50%) translateY(20px);
  background:#05364d;
  color:#fff;
  padding:10px 22px;
  border-radius:30px;
  font-size:13px;
  font-weight:500;
  box-shadow:0 10px 30px rgba(0,0,0,.25);
  opacity:0;
  transition:all .3s ease;
  z-index:9999;
}
.billing-toast.show{
  opacity:1;
  transform:translateX(-50%) translateY(0);
}

.billing-status.na{
  display:inline-flex;
  align-items:center;
  justify-content:center;
  gap:6px;
  font-size:13px;
  font-weight:600;
  padding:8px 18px;
  min-width:110px;        
  height:36px;
  border-radius:25px;
  background:#eef0f3;
  color:#8a94a6;
}

.invoice-table .table th,
.invoice-table .table td{
  padding:14px 12px;
  vertical-align:middle;
  border-bottom:1px solid #eef1f5;
  text-align:left;
}

/* header look */
.invoice-table .table thead th{
  font-weight:600;
  color:#05364d;
  background:#f1f3f6;
  border-bottom:2px solid #e6e9ef;
  white-space:nowrap;
}

/* center the badge / button columns: Status, Enrollment, Billing, Action */
.invoice-table .table th:nth-child(5), .invoice-table .table td:nth-child(5),
.invoice-table .table th:nth-child(6), .invoice-table .table td:nth-child(6),
.invoice-table .table th:nth-child(7), .invoice-table .table td:nth-child(7),
.invoice-table .table th:nth-child(8), .invoice-table .table td:nth-child(8){
  text-align:center;
}

/* invoice number: one line, bold */
.invoice-table .table td:first-child{
  white-space:nowrap;
  font-weight:600;
}

/* enroll date: keep on one line */
.invoice-table .table td:nth-child(4),
.invoice-table .table th:nth-child(4),
.invoice-table .table td:nth-child(5),
.invoice-table .table th:nth-child(5){
  white-space:nowrap;
}

/* remove the fixed 180px that squeezed the Action column */
.invoice-table .table td:last-child,
.invoice-table .table th:last-child{
  width:auto;
}

/* ACTION column: keep buttons centered & together, no overflow */
.invoice-table .action-btns{
  display:flex;
  justify-content:center;
  align-items:center;
  gap:10px;
  min-width:auto;
  flex-wrap:nowrap;
}
.invoice-table .action-btns .btn{
  min-width:100px;
  flex-shrink:0;
}

/* BILLING column centered */
.invoice-table .billing-wrap{
  justify-content:center;
}

@media(max-width:768px){
  .billing-wrap{ min-width:120px; }
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
    display:flex;
    flex-wrap: nowrap;        
    overflow-x:auto;        
    gap:6px;
  }

   .action-btns .btn{
    min-width:100px;
    font-size:12px;
  }

  .filter-bar{
    flex-direction: column;  
    gap:12px;
  }

  .filter-item{
    width:100%;
  }

  .filter-actions{
    width:100%;
    display:flex;
    flex-wrap:wrap;
    gap:8px;
  }

  .filter-actions button,
  .filter-actions a{
    flex:1;
    text-align:center;
  }

  .action-btns::-webkit-scrollbar{
    display:none;            
  }

  .btn-view,
  .btn-pay{
    width:auto;
    padding:5px 10px;
    font-size:12px;
    flex-shrink: 0;
    justify-content:center; 
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

<script>
// Submit (AJAX)
$('#filterForm').off('submit').on('submit', function(e){
    e.preventDefault();

    let urlParams = new URLSearchParams(window.location.search);
    let per_page = urlParams.get('per_page') || 5;

    let query = $(this).serialize() + '&p=1&per_page=' + per_page;

    history.pushState(null, '', '?page=invoice_system/dashboard/invoice_dashboard.php&' + query);

    $('#page-body').html('<div class="text-center py-5"><div class="loading-spinner"></div></div>');

    $.get('invoice_system/dashboard/invoice_dashboard.php?' + query, function(data){
        $('#page-body').html(data);
    });
});

// Instant filter
$('#filterForm input, #filterForm select').off('change').on('change', function(){
    $('#filterForm').submit();
});

// PREV / NEXT
$(document).off('click.pageNav').on('click.pageNav', '.page-nav', function(){
    if ($(this).prop('disabled')) return;

    let page = $(this).data('page');
    let urlParams = new URLSearchParams(window.location.search);
    urlParams.set('p', page);
    urlParams.set('per_page', $('#perPageSelect').val() || 5);

    history.pushState(null, '', '?' + urlParams.toString());

    $('#page-body').html('<div class="text-center py-5"><div class="loading-spinner"></div></div>');
    $.get('invoice_system/dashboard/invoice_dashboard.php?' + urlParams.toString(), function(data){
        $('#page-body').html(data);
    });
});

// PER PAGE CHANGE
$(document).off('change.perPage').on('change.perPage', '#perPageSelect', function(){
    let urlParams = new URLSearchParams(window.location.search);
    urlParams.set('per_page', $(this).val());
    urlParams.set('p', 1);

    history.pushState(null, '', '?' + urlParams.toString());

    $('#page-body').html('<div class="text-center py-5"><div class="loading-spinner"></div></div>');
    $.get('invoice_system/dashboard/invoice_dashboard.php?' + urlParams.toString(), function(data){
        $('#page-body').html(data);
    });
});

// PAUSE / RESUME billing
$(document).off('click.toggleBilling').on('click.toggleBilling', '.toggle-billing', function(e){
    e.preventDefault();

    let btn   = $(this);
    let sid   = btn.data('student');
    let pause = btn.data('pause');

    btn.prop('disabled', true);

    $.post('invoice_system/dashboard/toggle_billing.php', { student_id: sid, pause: pause }, function(){

        let msg = (pause == 1) ? 'Billing paused' : 'Billing resumed';
        let $toast = $('<div class="billing-toast">'+ msg +'</div>').appendTo('body');
        setTimeout(function(){ $toast.addClass('show'); }, 30);
        setTimeout(function(){ $toast.removeClass('show'); }, 1800);
        setTimeout(function(){ $toast.remove(); }, 2200);

        let urlParams = new URLSearchParams(window.location.search);
        $.get('invoice_system/dashboard/invoice_dashboard.php?' + urlParams.toString(), function(data){
            $('#page-body').html(data);
        });
    });
});
</script>