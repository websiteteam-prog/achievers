<?php
include "../../../db_config.php";

/* ---------------- FILTER ---------------- */
$program_filter = $_GET['program'] ?? '';
$student_filter = $_GET['student_name'] ?? '';

$where = "WHERE status='Active'";

if($program_filter != ''){
    $program_filter = mysqli_real_escape_string($conn,$program_filter);
    $where .= " AND program='$program_filter'";
}

if($student_filter != ''){
    $student_filter = mysqli_real_escape_string($conn,$student_filter);
    $where .= " AND CONCAT(first_name,' ',last_name) LIKE '%$student_filter%'";
}

/* ---------------- PAGINATION ---------------- */
$limit = 10;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

/* ---------------- COUNT ---------------- */
$count_query = "SELECT COUNT(*) as total FROM enrollment_inquiries $where";
$count_result = mysqli_query($conn, $count_query);
$total_row = mysqli_fetch_assoc($count_result);

$total_records = (int)$total_row['total'];
$total_pages = max(1, ceil($total_records / $limit));
if($page > $total_pages){
    $page = 1;
}

$offset = ($page - 1) * $limit;
/* ---------------- MAIN QUERY ---------------- */
$query = "SELECT
            student_id,
            first_name,
            last_name,
            program,
            program_count,
            specific_subject,
            grade,
            (SELECT image_path
               FROM student_images
              WHERE student_id = enrollment_inquiries.student_id
              ORDER BY id DESC
              LIMIT 1) AS image_path
          FROM enrollment_inquiries
          $where
          ORDER BY id DESC
          LIMIT $limit OFFSET $offset";

$result = mysqli_query($conn, $query);
?>

<link href="https://fonts.googleapis.com/css2?family=Love+Ya+Like+A+Sister&display=swap" rel="stylesheet">

<div class="invoice-dashboard">

<h2 class="dashboard-title">
<i class="bi bi-pencil-square"></i> Manage Enrollment
</h2>

<div class="invoice-table">

<!-- TOP BAR -->
<div class="top-bar">

<div class="showing-info">
<?php
if($total_records == 0){
    echo "Showing 0 to 0 of 0 students";
}else{
    echo "Showing ".($offset + 1)." to ".min($offset + $limit, $total_records)." of ".$total_records." students";
}
?>
</div>

<form id="filterForm" class="filter-box">

<input type="text" name="student_name" id="student_name"
placeholder="Search Student"
value="<?=htmlspecialchars($student_filter);?>">

<select name="program" id="program_filter">
<option value="">All Programs</option>
<option value="Early Starters" <?=($program_filter=="Early Starters")?'selected':'';?>>Early Starters</option>
<option value="Elementary" <?=($program_filter=="Elementary")?'selected':'';?>>Elementary</option>
<option value="Advanced Learners" <?=($program_filter=="Advanced Learners")?'selected':'';?>>Advanced Learners</option>
</select>

</form>
</div>

<div class="table-scroll">
<table class="table table-hover">
<thead>
<tr>
<th width="60">Sr</th>
<th>Student</th>
<th>Grade</th>
<th>Program</th>
<th class="nowrap">Program Count</th>
<th>Subjects</th>
<th>Action</th>
</tr>
</thead>

<tbody>
<?php if(mysqli_num_rows($result)==0){ ?>
<tr>
<td colspan="7" style="text-align:center;">No students found</td>
</tr>
<?php } else {
    $sr = $offset + 1;               // continue numbering across pages
    while($row=mysqli_fetch_assoc($result)){ ?>
<tr>

<td><strong><?=$sr;?></strong></td>

<td>
  <div class="student-cell">
    <?php $nm = trim($row['first_name']." ".$row['last_name']); ?>
    <?php if(!empty($row['image_path'])): ?>
      <img src="../Student_dashboard/<?=htmlspecialchars($row['image_path']);?>"
           class="student-photo" alt="Student">
    <?php else: ?>
      <div class="avatar"><?=strtoupper(substr($nm,0,1));?></div>
    <?php endif; ?>
    <span class="student-name"><?=htmlspecialchars($nm);?></span>
  </div>
</td>
<td><?=htmlspecialchars($row['grade']);?></td>
<td><?=htmlspecialchars($row['program']);?></td>
<td><?=htmlspecialchars($row['program_count']);?></td>
<td><?=htmlspecialchars($row['specific_subject']);?></td>

<td class="action-btns">

<a href="#" class="btn btn-edit menu-link"
data-page="invoice_system/enroll/edit_enrollment.php?student_id=<?=$row['student_id'];?>">
<i class="bi bi-pencil"></i> Edit
</a>

<a href="#" class="btn btn-history menu-link"
data-page="invoice_system/enroll/plan_history.php?student_id=<?=$row['student_id'];?>">
<i class="bi bi-clock-history"></i> History
</a>

<a href="#" class="btn btn-delete delete-student"
data-id="<?=$row['student_id'];?>"
data-name="<?=htmlspecialchars($row['first_name']." ".$row['last_name']);?>">
<i class="bi bi-trash"></i> Delete
</a>

</td>
</tr>
<?php $sr++; }} ?>
</tbody>
</table>
</div>

<!-- PAGINATION -->
<?php if($total_records > 0): ?>

<div class="pagination-container">
<nav class="pagination-nav">
<ul class="pagination">

<li class="page-item <?=($page<=1)?'disabled':'';?>">
<a href="#" class="page-link ajax-page" data-page="<?=max(1,$page-1);?>">Prev</a>
</li>

<?php for($i=1;$i<=$total_pages;$i++){ ?>
<li class="page-item <?=($page==$i)?'active':'';?>">
<a href="#" class="page-link ajax-page" data-page="<?=$i;?>"><?=$i;?></a>
</li>
<?php } ?>

<li class="page-item <?=($page>=$total_pages)?'disabled':'';?>">
<a href="#" class="page-link ajax-page" data-page="<?=min($total_pages,$page+1);?>">Next</a>
</li>

</ul>
</nav>
</div>
<?php endif; ?>

</div>
</div>

<script>
$(function(){

let searchTimer;

/* Main Ajax Load */
function loadData(page = 1){

    let program = $("#program_filter").val();
    let student = $("#student_name").val();

    $.ajax({
        url:"invoice_system/enroll/manage_enrollment.php",
        type:"GET",
        data:{
            page:page,
            program:program,
            student_name:student
        },
        beforeSend:function(){
            $(".table-scroll").css("opacity","0.5");
        },
        success:function(res){
            $("#page-body").html(res);
        }
    });
}

/* Program Filter */
$(document).off("change","#program_filter").on("change","#program_filter",function(){
    loadData(1);
});


$(document).off("keyup","#student_name").on("keyup","#student_name",function(){

    clearTimeout(searchTimer);

    searchTimer = setTimeout(function(){
        loadData(1);
    },500);

});

/* Pagination Click */
$(document).off("click",".ajax-page").on("click",".ajax-page",function(e){

    e.preventDefault();

    if($(this).closest("li").hasClass("disabled") || $(this).closest("li").hasClass("active")){
        return;
    }

    let page = $(this).data("page");

    loadData(page);

});

/* Reset Filter Button */
$(document).off("click","#resetFilter").on("click","#resetFilter",function(){

    $("#student_name").val('');
    $("#program_filter").val('');

    loadData(1);

});

});

$(document).off("click.delStudent").on("click.delStudent", ".delete-student", function(e){
    e.preventDefault();

    let id   = $(this).data("id");
    let name = $(this).data("name");

    if(!confirm("Delete " + name + " ?\nThe student will be cancelled and removed from this list.")) return;

    $.post("delete_student.php", { id: id, mode: "cancel" })
     .done(function(){
         // reload the current enrollment list, keeping active filters
         let program = $("#program_filter").val() || '';
         let student = $("#student_name").val()   || '';
         $.get("invoice_system/enroll/manage_enrollment.php",
               { page: 1, program: program, student_name: student },
               function(res){ $("#page-body").html(res); });
     })
     .fail(function(){
         alert("Unable to delete student. Please try again.");
     });
});
</script>

<style>
    
.top-filter-bar{
    margin-bottom:15px;
}

.top-filter-bar select{
    padding:10px 14px;
    border:1px solid #ddd;
    border-radius:10px;
    min-width:220px;
}

.invoice-dashboard{
  width:100%;
}

.dashboard-title{
  font-size:30px;
  color:#05364d;
  margin-bottom:25px;
  font-family:"Love Ya Like A Sister", cursive;
}

.invoice-table{
  background:white;
  padding:20px 26px;
  border-radius:15px;
}

/* TOP BAR */
.top-bar{
    display:flex;
    justify-content:space-between;
    align-items:center;
    gap:15px;
    margin-bottom:15px;
    flex-wrap:wrap;
}

.showing-info{
    font-size:15px;
    font-weight:500;
}

.filter-box{
    display:flex;
    gap:10px;
    flex-wrap:wrap;
}

.filter-box input,
.filter-box select{
    padding:10px 14px;
    border:1px solid #ddd;
    border-radius:10px;
    min-width:200px;
}

.table-scroll{
  overflow-x:auto;
}

.table{
  width:100%;
  min-width:1100px;
}

.table thead{
  background:#f1f3f6;
}

.table th,
.table td{
  padding:12px 10px;
  vertical-align:middle;
}

.nowrap{
    white-space:nowrap;
}

.action-btns{
  display:flex;
  gap:8px;
  justify-content:flex-start;
  align-items:center;
}

.table td{
  vertical-align:middle !important;
}

/* BUTTONS */
.btn-edit{
  background: linear-gradient(160deg,#1e3a8a,#2563eb);
  color:white;
  padding:6px 12px;
  border-radius:20px;
  font-size:13px;
}

.btn-delete{
  background: linear-gradient(160deg,#b91c1c,#ef4444);
  color:white;
  padding:6px 12px;
  border-radius:20px;
  font-size:13px;
  border:none;
  cursor:pointer;
}
.btn-delete:hover{ color:#fff; opacity:.92; }

.btn-history{
  background: linear-gradient(160deg,#166534,#22c55e);
  color:white;
  padding:6px 12px;
  border-radius:20px;
  font-size:13px;
}

.student-cell{
  display:flex;
  align-items:center;
  gap:12px;
}
.avatar{
  width:44px; height:44px;
  border-radius:50%;
  display:flex; align-items:center; justify-content:center;
  background:linear-gradient(135deg,#1e3c72,#2a5298);
  color:#fff; font-weight:700; font-size:16px;
  overflow:hidden;
  border:3px solid #fff;
  box-shadow:0 3px 10px rgba(0,0,0,.18);
  flex-shrink:0;
}
.student-photo{
  width:44px; height:44px;
  border-radius:50%;
  object-fit:cover;
  border:3px solid #fff;
  box-shadow:0 3px 10px rgba(0,0,0,.18);
  flex-shrink:0;
}
.student-name{
  font-weight:600;
  color:#1f2937;
  white-space:nowrap;   
}

.pagination-container {
    margin-top: 25px;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 12px;
}

.pagination-info {
    font-size: 14px;
    color: #666;
}

.pagination-nav .pagination {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 5px;
    list-style: none;
    padding: 0;
    margin: 0;
}

.pagination .page-link {
    padding: 8px 12px;
    border: 1px solid #ddd;
    color: #05364d;
    text-decoration: none;
    border-radius: 6px;
    min-width: 38px;
    text-align: center;
    transition: all 0.2s;
}

.pagination .page-link:hover { 
    background:#f8f9fa; 
    border-color: #bbb;
}

.pagination .page-link.active {
    background: #2563eb;
    color: white;
    border-color: #2563eb;
}

.pagination .page-item.disabled .page-link {
    color: #aaa;
    pointer-events: none;
    background: #f8f9fa;
}

/* Mobile Optimizations */
@media(max-width: 768px) {
    .invoice-table { padding: 15px 12px; }
    
    .action-btns {
        flex-direction: column;
        gap: 6px;
    }
    
    .btn-edit, .btn-history {
        font-size: 12px;
        padding: 6px 10px;
        text-align: center;
    }

    .pagination-container {
        gap: 10px;
    }
    
    .pagination .page-link {
        padding: 6px 10px;
        min-width: 32px;
        font-size: 13px;
    }
    
    .showing-info {
        font-size: 13px;
    }

    .top-bar{
    flex-direction:column;
    align-items:flex-start;
}

    .filter-box{
        width:100%;
    }

    .filter-box input,
    .filter-box select{
        width:100%;
    }
}

@media(max-width: 480px) {
    .pagination .page-link {
        min-width: 28px;
        padding: 5px 8px;
    }
}
</style>