<?php
session_start();
include "../db_config.php";
include "student_sidebar.php"; 

// 🔐 Security check
if (!isset($_SESSION['student_id'])) {
    header("Location: ../student_login.php");
    exit();
}

$student_id = (int) $_SESSION['student_id'];

/*
|--------------------------------------------------------------------------
| 1️⃣ First check if any assessment is assigned to this student
|--------------------------------------------------------------------------
*/

$count_sql = "
    SELECT COUNT(*) AS total
    FROM assessment_assignments
    WHERE student_id = $student_id
";

$count_res = mysqli_query($conn, $count_sql);

if (!$count_res) {
    die("Count query failed: " . mysqli_error($conn));
}

$count_row = mysqli_fetch_assoc($count_res);
$total_assessments = (int)$count_row['total'];


/*
|--------------------------------------------------------------------------
| 2️⃣ Fetch assessment details (only if assigned)
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT 
        a.id, 
        a.title, 
        a.due_date, 
        ass.started_at,
        ass.submitted_at,
        ass.score,
        ass.total_questions
    FROM assessments a
    INNER JOIN assessment_assignments ass 
        ON a.id = ass.assessment_id
    WHERE ass.student_id = $student_id
      AND a.is_published = 1
    ORDER BY a.due_date ASC
";

$res = mysqli_query($conn, $sql);

if (!$res) {
    die("Main query failed: " . mysqli_error($conn));
}

$currentpage = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html>
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title>My Assessments</title>

<link href="student.css" rel="stylesheet">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
<!-- Google Font -->
<link href="https://fonts.googleapis.com/css2?family=Love+Ya+Like+A+Sister&display=swap" rel="stylesheet">
  
<style>

/* ===== MAIN LAYOUT ===== */
body {
    background: #f5f7fb;
    margin: 0;
    font-family: 'Segoe UI', system-ui, sans-serif;
}

.main-layout{
display:flex;
min-height:100vh;
}

/* ===== CONTENT AREA ===== */

.content-area{

margin-left:260px;

width:calc(100% - 260px);
max-width:calc(100% - 260px);

padding:30px;

}

/* ===== TABLE CARD ===== */

.card{

    background:#fff !important;
    border:1px solid #e9ecef;
    border-radius:12px;
}

.card-body{
    background:#fff !important;
}

/* ===== TABLE SCROLL ===== */

.table-responsive{

overflow-x:auto;
-webkit-overflow-scrolling:touch;

}

.table{
    background:#fff !important;
    white-space:nowrap;
    margin-bottom:0;
}

.table thead th{
    background:#fff !important;
    color:var(--primary);
    border-bottom:1px solid #dee2e6;
    font-weight:700;
}

.table tbody tr,
.table tbody td{
    background:#fff !important;
}

/* Hover */
.table-hover tbody tr:hover td{
    background:#f8f9fc !important;
}

/* ===== BADGES ===== */

.badge{

font-size:12px;
padding:6px 10px;

}

.page-title{
    display:flex;
    align-items:center;
    gap:18px;
    margin-bottom:30px;
}

.icon-style{
    font-size:50px;
    line-height:1;
    margin:0 !important;
    background:linear-gradient(to right,#e02121,#2f55a4);
    -webkit-background-clip:text;
    -webkit-text-fill-color:transparent;
    background-clip:text;
    color:transparent;
    flex-shrink:0;
}

.page-title h3{
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

.page-subtitle{
    color:#6b7280;
    margin-top:-6px;
    font-size:16px;
}

/* .container-fluid h3{
   font-size: 42px;
   font-weight: 400;
   margin-bottom: 6px !important;
   background: linear-gradient(to right, #e02121, #2f55a4);
   -webkit-background-clip: text;
   -webkit-text-fill-color: transparent;
   font-family: "Love Ya Like A Sister", cursive;
   margin-left: 8px;
} */

/* ===== MOBILE ===== */

@media(max-width:992px){

.content-area{

margin-left:0;
width:100%;
max-width:100%;

padding:80px 15px 20px;

}

.page-subtitle{
    font-size:13px;
}

/* sidebar mobile */

#studentSidebar{

position:fixed;
left:-260px;
top:0;
width:260px;
height:100%;
z-index:1200;
transition:.3s;

}

#studentSidebar.show{

left:0;

}

}

/* mobile text */

@media(max-width:576px){

    .page-title{
        gap:12px;
        margin-bottom:20px;
    }

    .icon-style{
        font-size:38px;
    }

    .page-title h3{
        font-size:30px;
    }

}

</style>

</head>

<body>

<div class="main-layout">


<!-- MOBILE TOGGLE -->
<button class="btn btn-primary d-lg-none position-fixed"
id="sidebarToggle"
style="top:15px;left:15px;z-index:1300;border-radius:50%;width:48px;height:48px;">

<i class="bi bi-list"></i>

</button>


<!-- CONTENT -->
<div class="content-area container-fluid">
<div class="page-title">

    <i class="bi bi-file-earmark-check icon-style"></i>

    <div>

        <h3 class="mb-0">
            My Assessments
        </h3>

        <div class="page-subtitle">
            View your assigned assessments, submission status and scores.
        </div>

    </div>

</div>

<div class="card shadow-sm">

<div class="card-body">

<div class="table-responsive">

<table class="table table-hover align-middle">

<thead>

<tr>

<th>Title</th>
<th>Due Date</th>
<th>Status</th>
<th>Score</th>
<th>Action</th>

</tr>

</thead>

<tbody>

                    <?php if($total_assessments == 0): ?>
<tr>

<td colspan="5" class="text-center text-muted py-4">

  No assessments assigned.

</td>

</tr>

<?php else: ?>

<?php while($r=mysqli_fetch_assoc($res)): ?>

<?php

$title=htmlspecialchars($r['title']);

$due_date = !empty($r['due_date']) 
? date("d M Y",strtotime($r['due_date']))
: "No Due Date";

if($r['submitted_at'])
$status="<span class='badge bg-success'>Submitted</span>";

elseif($r['started_at'])
$status="<span class='badge bg-warning text-dark'>In Progress</span>";

else
$status="<span class='badge bg-secondary'>Not Started</span>";

$score=$r['submitted_at']
? "<strong>{$r['score']} / {$r['total_questions']}</strong>"
: "—";

$id=(int)$r['id'];

?>

<tr>

<td><?= $title ?></td>

<td><?= $due_date ?></td>

<td><?= $status ?></td>

<td><?= $score ?></td>

<td>

<a href="take_assessment.php?id=<?= $id ?>"
class="btn btn-sm btn-primary">

Take / View

</a>

</td>

</tr>

<?php endwhile; ?>

<?php endif; ?>

</tbody>

</table>

</div>

</div>

</div>

</div>

</div>


<script>

const sidebar=document.getElementById('studentSidebar');

document.getElementById('sidebarToggle')
.addEventListener('click',()=>{

sidebar.classList.toggle('show');

});

</script>

</body>
</html>