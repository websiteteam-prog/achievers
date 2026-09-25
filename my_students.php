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
| FETCH STUDENTS
|--------------------------------------------------------------------------
| Fallback: if a student's own phone/email is missing, pull it from
| enrollment_inquiries (matched via student_id) — guardian, then mother,
| then father, whichever is available first.
*/

$sql="

SELECT

s.id,
si.image_path,

CONCAT(
s.first_name,
' ',
IFNULL(s.last_name,'')
) AS name,

MAX(COALESCE(NULLIF(s.email,''), ei.guardian_email, ei.mother_email, ei.father_email)) AS email,
MAX(COALESCE(NULLIF(s.phone,''), ei.guardian_phone, ei.mother_phone, ei.father_phone)) AS phone,

s.dob,

COUNT(DISTINCT sd.id) AS documents

FROM teacher_subjects ts

INNER JOIN student_subjects ss
ON ts.subject_id=ss.subject_id

INNER JOIN students s
ON s.id=ss.student_id

LEFT JOIN student_images si
ON si.student_id = s.id

LEFT JOIN student_documents sd
ON sd.student_id=s.id
AND sd.subject_id=ss.subject_id

LEFT JOIN enrollment_inquiries ei
ON ei.student_id = s.id

WHERE ts.teacher_id=?

GROUP BY

s.id,
s.first_name,
s.last_name,
s.dob

ORDER BY s.first_name

";

$stmt=$conn->prepare($sql);
$stmt->bind_param("i",$teacher_id);
$stmt->execute();

$result=$stmt->get_result();

$total_students=$result->num_rows;

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
transition:background .2s ease, transform .2s ease;
}

.students-table tbody tr:hover{
background:#f8fbff;
}

.student-cell{
display:flex;
align-items:center;
gap:12px;
}

.avatar{

    width:54px;
    height:54px;

    border-radius:50%;

    display:flex;
    align-items:center;
    justify-content:center;

    overflow:hidden;

    background:linear-gradient(135deg,#1e3c72,#2a5298);
    color:#fff;
    font-weight:700;
    font-size:18px;

    border:3px solid #fff;
    box-shadow:0 3px 12px rgba(0,0,0,.18);

    flex-shrink:0;

}

.student-photo{

    width:54px;
    height:54px;

    border-radius:50%;

    object-fit:cover;
    object-position:center;

    border:3px solid #fff;

    box-shadow:0 3px 12px rgba(0,0,0,.18);

    flex-shrink:0;

    image-rendering:auto;

}

.btn-view{
border-radius:30px;
padding:7px 18px;
font-size:13px;
font-weight:600;
transition:.2s ease;
}

.btn-view:hover{
transform:translateY(-1px);
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

.btn-view {
padding: 7px 14px;
}

.header-right{
width:100%;
justify-content:space-between;
}

.header-title{
font-size:22px;
}

.students-table{
min-width:850px;
}

}

</style>

<div class="students-container">

<div class="students-header">

<div class="header-left">

<div class="header-icon">
<i class="bi bi-people-fill"></i>
</div>

<div>
<h2 class="header-title">My Students</h2>
<p class="header-subtitle">
<?= $total_students ?> student<?= $total_students==1?'':'s' ?> assigned to your subjects
</p>
</div>

</div>

<div class="header-right">

<span class="subject-badge">
<i class="bi bi-mortarboard-fill me-1"></i>
Total : <?= $total_students ?>
</span>

</div>

</div>

<!-- TABLE -->

<?php if ($result->num_rows > 0): ?>

<div class="card">

<div class="card-body p-0">

<div class="table-responsive">

<table class="students-table">

<thead>

<tr>

<th width="60">Sr</th>

<th>Student</th>

<th>Email</th>

<th>Phone</th>

<th>DOB</th>

<th width="170">Documents</th>

</tr>

</thead>

<tbody>

<?php

$i=1;

while($row=$result->fetch_assoc()):

$btnClass=$row['documents']>0
? "btn-primary"
: "btn-outline-secondary";

$phone = $row['phone'] ?? '';
$email = $row['email'] ?? '';
$dob = $row['dob'] ?? null;

?>

<tr>

<td>
<strong><?= $i ?></strong>
</td>

<td>

<div class="student-cell">

<?php if(!empty($row['image_path'])): ?>

<img
    src="Student_dashboard/<?= htmlspecialchars($row['image_path']) ?>"
    class="student-photo"
    alt="Student">

<?php else: ?>

<div class="avatar">
    <?= strtoupper(substr($row['name'],0,1)); ?>
</div>

<?php endif; ?>

<div>
<div class="fw-bold">
<?= htmlspecialchars($row['name']) ?>
</div>
</div>

</div>

</td>

<td>
<?= $email !== '' ? htmlspecialchars($email) : '<span class="text-muted">—</span>' ?>
</td>

<td>
<?= $phone !== '' ? htmlspecialchars($phone) : '<span class="text-muted">—</span>' ?>
</td>

<td>
<?= $dob ? date("d M Y",strtotime($dob)) : '<span class="text-muted">—</span>' ?>
</td>

<td>

<button
class="btn <?= $btnClass ?> btn-sm btn-view view-documents"
data-student="<?= $row['id'] ?>"
data-name="<?= htmlspecialchars($row['name']) ?>">

<?php if($row['documents']>0){ ?>

View
<span class="badge bg-light text-dark ms-1">
<?= $row['documents'] ?>
</span>

<?php }else{ ?>

<i class="bi bi-folder me-1"></i>
No Files

<?php } ?>

</button>

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

<i class="bi bi-people"></i>

<h3 class="mt-4">
No Students Assigned
</h3>

<p class="text-muted">
Students assigned to your subjects will appear here.
</p>

</div>

<?php endif; ?>

</div>

<?php
$stmt->close();
?>

<!-- ===================== DOCUMENT MODAL ===================== -->

<div
class="modal fade"
id="documentsModal"
tabindex="-1"
aria-hidden="true">

<div class="modal-dialog modal-lg modal-dialog-scrollable">

<div class="modal-content border-0 shadow rounded-4">

<div class="modal-header bg-primary text-white">

<h5
class="modal-title"
id="modalTitle">

<i class="bi bi-folder2-open me-2"></i>
Student Documents

</h5>

<button
type="button"
class="btn-close btn-close-white"
data-bs-dismiss="modal">
</button>

</div>

<div
class="modal-body"
id="documentsBody">

<div class="text-center py-5">

<div
class="spinner-border text-primary"
role="status">
</div>

<p class="mt-3 mb-0">
Loading documents...
</p>

</div>

</div>

</div>

</div>

</div>

<script>

$(document).on(

'click',

'.view-documents',

function(){

let studentId=$(this).data('student');

let studentName=$(this).data('name');

$("#modalTitle").html(

'<i class="bi bi-folder2-open me-2"></i>'

+

studentName+

"'s Documents"

);

$("#documentsBody").html(

'<div class="text-center py-5">'+

'<div class="spinner-border text-primary"></div>'+

'<p class="mt-3">Loading documents...</p>'+

'</div>'

);

const modal=new bootstrap.Modal(

document.getElementById("documentsModal")

);

modal.show();

$("#documentsBody").load(

"teacher_student_documents.php?student_id="+studentId,

function(response,status){

if(status==="error"){

$("#documentsBody").html(

'<div class="alert alert-danger">'+
'<i class="bi bi-exclamation-circle me-2"></i>'+
'Unable to load student documents.'+
'</div>'

);

}

}

);

});

</script>