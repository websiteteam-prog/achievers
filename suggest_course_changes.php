<?php

error_reporting(E_ALL);
ini_set('display_errors',1);

session_start();

include 'db_config.php';

/*
|--------------------------------------------------------------------------
| AUTH CHECK
|--------------------------------------------------------------------------
*/

if(!isset($_SESSION['teacher_id'])){
    echo "<script>window.location.href='teacher_login.php';</script>";
    exit;
}

$teacher_id=(int)$_SESSION['teacher_id'];

/*
|--------------------------------------------------------------------------
| FLASH MESSAGE (set by suggest_course_submit.php)
|--------------------------------------------------------------------------
*/

$flash_success = $_SESSION['suggestion_success'] ?? null;
$flash_error   = $_SESSION['suggestion_error'] ?? null;
unset($_SESSION['suggestion_success'], $_SESSION['suggestion_error']);

/*
|--------------------------------------------------------------------------
| FETCH SUBJECTS TAUGHT BY THIS TEACHER
|--------------------------------------------------------------------------
*/

$subject_sql = "
SELECT
s.id,
s.subject_name,
s.grade
FROM teacher_subjects ts
INNER JOIN subjects s
ON s.id = ts.subject_id
WHERE ts.teacher_id = ?
ORDER BY s.grade,s.subject_name
";

$subject_stmt = $conn->prepare($subject_sql);
$subject_stmt->bind_param("i", $teacher_id);
$subject_stmt->execute();
$subject_result = $subject_stmt->get_result();

$subjects = [];
while ($row = $subject_result->fetch_assoc()) {
    $subjects[] = $row;
}
$subject_stmt->close();

$grades=[];

foreach($subjects as $sub){

    if(!in_array($sub['grade'],$grades)){
        $grades[]=$sub['grade'];
    }

}

sort($grades);
/*
|--------------------------------------------------------------------------
| FETCH THIS TEACHER'S PAST SUGGESTIONS
|--------------------------------------------------------------------------
*/

$history_sql = "
SELECT
cs.id,
cs.suggestion,
cs.status,
cs.created_at,
s.subject_name,
s.grade
FROM course_suggestions cs
LEFT JOIN subjects s
ON s.id = cs.subject_id
WHERE cs.teacher_id = ?
ORDER BY cs.created_at DESC
";

$history_stmt = $conn->prepare($history_sql);
$history_stmt->bind_param("i", $teacher_id);
$history_stmt->execute();
$history_result = $history_stmt->get_result();
$total_suggestions = $history_result->num_rows;

?>

<style>

.students-container{
padding:5px;
}

.students-header{
display:flex;
justify-content:space-between;
align-items:center;
margin-bottom:25px;
flex-wrap:wrap;
gap:15px;
}

.header-left{
display:flex;
align-items:center;
gap:15px;
}

.header-icon{
width:55px;
height:55px;
border-radius:16px;
background:linear-gradient(135deg,#1e3c72,#2a5298);
display:flex;
align-items:center;
justify-content:center;
color:#fff;
font-size:24px;
box-shadow:0 8px 20px rgba(30,60,114,.25);
}

.header-title{
margin:0;
font-size:24px;
font-weight:700;
color:#1e3c72;
letter-spacing:.2px;
}

.header-subtitle{
margin:0;
font-size:14px;
color:#6b7280;
}

.header-right{
display:flex;
align-items:center;
gap:12px;
flex-wrap:wrap;
}

.subject-badge{
background:linear-gradient(135deg,#1e3c72,#2a5298);
color:#fff;
padding:8px 18px;
border-radius:40px;
font-weight:600;
font-size:13px;
box-shadow:0 5px 15px rgba(0,0,0,.12);
}

.btn-manage-student{
display:inline-flex;
align-items:center;
gap:8px;
background:linear-gradient(135deg,#e8063c,#c40530);
color:#fff;
border:none;
padding:10px 22px;
border-radius:40px;
font-weight:600;
font-size:14px;
text-decoration:none;
box-shadow:0 8px 20px rgba(232,6,60,.25);
transition:.25s ease;
}

.btn-manage-student:hover{
transform:translateY(-2px);
box-shadow:0 12px 26px rgba(232,6,60,.32);
color:#fff;
}

.card{
border:none;
border-radius:0px;
overflow:hidden;
box-shadow:0 10px 30px rgba(17,24,39,.08);
animation:fadeInUp .4s ease;
}

@keyframes fadeInUp{
from{opacity:0;transform:translateY(10px);}
to{opacity:1;transform:translateY(0);}
}

.form-card{
padding:28px;
}

.form-card label{
font-weight:600;
font-size:13.5px;
color:#374151;
margin-bottom:6px;
}

.form-card .form-select,
.form-card .form-control{
border-radius:10px;
border:1px solid #e2e8f0;
padding:10px 14px;
font-size:14.5px;
}

.form-card .form-select:focus,
.form-card .form-control:focus{
border-color:#2a5298;
box-shadow:0 0 0 .2rem rgba(42,82,152,.15);
}

.btn-submit-suggestion{
display:inline-flex;
align-items:center;
gap:8px;
background:linear-gradient(135deg,#1e3c72,#2a5298);
color:#fff;
border:none;
padding:11px 26px;
border-radius:40px;
font-weight:600;
font-size:14px;
transition:.25s ease;
}

.btn-submit-suggestion:hover{
transform:translateY(-2px);
box-shadow:0 12px 26px rgba(30,60,114,.32);
color:#fff;
}

.students-table{
width:100%;
border-collapse:collapse;
}

.students-table th{
background:#2a5298;
color:#fff;
padding:16px 15px;
font-size:13px;
text-transform:uppercase;
letter-spacing:.6px;
font-weight:600;
}

.students-table td{
padding:16px 15px;
border-bottom:1px solid #edf1f7;
vertical-align:middle;
font-size:14.5px;
color:#374151;
}

.students-table tbody tr{
transition:background .2s ease;
}

.students-table tbody tr:hover{
background:#f8fbff;
}

.status-badge{
display:inline-flex;
align-items:center;
gap:6px;
padding:6px 14px;
border-radius:30px;
font-size:12px;
font-weight:600;
text-transform:capitalize;
}

.status-pending{
background:#fff4d6;
color:#a15c00;
}

.status-approved{
background:#d9f3ff;
color:#0c7abf;
}

.status-rejected{
background:#ffe0ef;
color:#d63384;
}

.suggestion-text{
max-width:320px;
white-space:normal;
}

.section-heading{
font-size:18px;
font-weight:700;
color:#1e3c72;
margin:35px 0 15px;
display:flex;
align-items:center;
gap:8px;
}

.empty-state{
padding:70px 20px;
text-align:center;
}

.empty-state i{
font-size:75px;
color:#d8d8d8;
}

@media(max-width:768px){

.students-header{
flex-direction:column;
align-items:flex-start;
}

.header-right{
width:100%;
justify-content:space-between;
}

.header-title{
font-size:22px;
}

.students-table{
min-width:750px;
}

}

</style>

<div class="students-container">

<div class="students-header">

<div class="header-left">

<div class="header-icon">
<i class="bi bi-signpost-split-fill"></i>
</div>

<div>
<h2 class="header-title">Suggest Course Changes</h2>
<p class="header-subtitle">
Recommend new courses or changes for your subjects and grades
</p>
</div>

</div>

<div class="header-right">

<span class="subject-badge">
<i class="bi bi-chat-left-text-fill me-1"></i>
Total : <?= $total_suggestions ?>
</span>

</div>

</div>

<?php if ($flash_success): ?>
<div class="alert alert-success alert-dismissible fade show" role="alert">
<i class="bi bi-check-circle-fill me-2"></i>
<?= htmlspecialchars($flash_success) ?>
<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<?php if ($flash_error): ?>
<div class="alert alert-danger alert-dismissible fade show" role="alert">
<i class="bi bi-exclamation-circle-fill me-2"></i>
<?= htmlspecialchars($flash_error) ?>
<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<!-- ===================== SUGGESTION FORM ===================== -->

<div class="card">

<div class="form-card">

<form method="POST" action="suggest_course_submit.php">

<div class="row">

<div class="col-md-6 mb-3">

<label>Select Grade</label>

<select id="gradeSelect" class="form-select" required>

<option value="">-- Select Grade --</option>

<?php foreach($grades as $g): ?>

<option value="<?= $g ?>">
Grade <?= $g ?>
</option>

<?php endforeach; ?>

</select>

</div>

<div class="col-md-6 mb-3">

<label>Select Subject</label>

<select
id="subjectSelect"
name="subject_id"
class="form-select"
required>

<option value="">
Select Grade First
</option>

</select>

</div>

</div>

<div class="mb-3">
<label>Your Suggestion</label>
<textarea
name="suggestion"
class="form-control"
rows="5"
placeholder="Describe the course change or new course you'd like to suggest..."
required></textarea>
</div>

<button type="submit" class="btn-submit-suggestion">
<i class="bi bi-send-fill"></i>
Submit Suggestion
</button>

</form>

</div>

</div>

<!-- ===================== SUGGESTION HISTORY ===================== -->

<h3 class="section-heading">
<i class="bi bi-clock-history"></i>
Your Past Suggestions
</h3>

<?php if ($total_suggestions > 0): ?>

<div class="card">

<div class="card-body p-0">

<div class="table-responsive">

<table class="students-table">

<thead>
<tr>
<th width="60">Sr</th>
<th>Subject</th>
<th>Grade</th>
<th>Suggestion</th>
<th>Status</th>
<th>Submitted On</th>
</tr>
</thead>

<tbody>

<?php
$i = 1;
while ($row = $history_result->fetch_assoc()):

    $status = strtolower($row['status'] ?? 'pending');

    $statusClass = match($status){
        'approved' => 'status-approved',
        'rejected' => 'status-rejected',
        default    => 'status-pending',
    };

    $statusIcon = match($status){
        'approved' => 'bi-check-circle-fill',
        'rejected' => 'bi-x-circle-fill',
        default    => 'bi-hourglass-split',
    };
?>

<tr>

<td><strong><?= $i ?></strong></td>

<td>
<?= htmlspecialchars($row['subject_name'] ?? '—') ?>
</td>

<td>
Grade <?= htmlspecialchars($row['grade'] ?? '—') ?>
</td>

<td class="suggestion-text">
<?= nl2br(htmlspecialchars($row['suggestion'])) ?>
</td>

<td>
<span class="status-badge <?= $statusClass ?>">
<i class="bi <?= $statusIcon ?>"></i>
<?= htmlspecialchars(ucfirst($status)) ?>
</span>
</td>

<td>
<?= date("d M Y", strtotime($row['created_at'])) ?>
</td>

</tr>

<?php
$i++;
endwhile;
?>

</tbody>

</table>

</div>

</div>

</div>

<?php else: ?>

<div class="empty-state">

<i class="bi bi-signpost-split"></i>

<h3 class="mt-4">No Suggestions Yet</h3>

<p class="text-muted">
Suggestions you submit for course changes will appear here.
</p>

</div>

<?php endif; ?>

</div>

<?php
$history_stmt->close();
?>

<script>

const allSubjects = <?= json_encode($subjects) ?>;

const gradeSelect=document.getElementById("gradeSelect");
const subjectSelect=document.getElementById("subjectSelect");

gradeSelect.addEventListener("change",function(){

subjectSelect.innerHTML="";

if(this.value==""){

subjectSelect.innerHTML="<option>Select Grade First</option>";

return;

}

subjectSelect.innerHTML='<option value="">-- Select Subject --</option>';

allSubjects.forEach(function(sub){

if(sub.grade==gradeSelect.value){

subjectSelect.innerHTML+=`
<option value="${sub.id}">
${sub.subject_name}
</option>`;

}

});

});

</script>