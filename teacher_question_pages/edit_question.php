<?php
session_start();
include '../db_config.php';

if (!isset($_SESSION['teacher_id'])) {
    exit('<div class="alert alert-danger">Session expired</div>');
}

/* keep teacher active */
$teacher_id = $_SESSION['teacher_id'];
$now = date('Y-m-d H:i:s');
mysqli_query($conn,"UPDATE teachers SET last_activity='$now' WHERE id='$teacher_id'");

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

$stmt = $conn->prepare("SELECT * FROM quiz_questions WHERE id=?");
$stmt->bind_param("i",$id);
$stmt->execute();
$result=$stmt->get_result();

if($result->num_rows==0){
    exit('<div class="alert alert-danger">Question not found</div>');
}

$data=$result->fetch_assoc();

$payload=json_decode($data['question_payload'],true) ?? [];

$msg="";


/*
|--------------------------------------------------------------------------
| UPDATE QUESTION
|--------------------------------------------------------------------------
*/

if($_SERVER['REQUEST_METHOD']=="POST"){

$question_text=$_POST['question_text'] ?? '';
$correct_answer=$_POST['correct_answer'] ?? '';
$unit=$_POST['unit'] ?? '';

$image_path=$data['question_image'];


/* IMAGE UPLOAD */

if(!empty($_FILES['question_image']['name'])){

$uploadDir="../uploads/questions/";

if(!is_dir($uploadDir)){
mkdir($uploadDir,0777,true);
}

$fileName=time().'_'.$_FILES['question_image']['name'];

$targetFile=$uploadDir.$fileName;

if(move_uploaded_file($_FILES['question_image']['tmp_name'],$targetFile)){

$image_path="uploads/questions/".$fileName;

}

}


/* UPDATE QUERY */

$stmt=$conn->prepare("
UPDATE quiz_questions
SET question_text=?, correct_answer=?, unit=?, question_image=?
WHERE id=?
");

$stmt->bind_param(
"ssssi",
$question_text,
$correct_answer,
$unit,
$image_path,
$id
);

if($stmt->execute()){

$msg='<div class="alert alert-success">Question updated successfully</div>';

$data['question_text']=$question_text;
$data['correct_answer']=$correct_answer;
$data['unit']=$unit;
$data['question_image']=$image_path;

}
else{

$msg='<div class="alert alert-danger">Update failed</div>';

}

}

?>

<link
href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css"
rel="stylesheet"
>

<style>

*{
box-sizing:border-box;
}

.page-container{
padding:5px;
width:100%;
max-width:100%;
}

/* ========================= */
/* HEADER - matches manage_questions.php */
/* ========================= */

.questions-header{
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
flex-shrink:0;
}

.header-title{
margin:0;
font-size:24px;
font-weight:700;
color:#1e3c72;
letter-spacing:.2px;
word-break:break-word;
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

.btn-back{
display:inline-flex;
align-items:center;
gap:8px;
background:#f1f3f7;
color:#374151;
border:none;
padding:10px 22px;
border-radius:40px;
font-weight:600;
font-size:14px;
text-decoration:none;
transition:.25s ease;
cursor:pointer;
}

.btn-back:hover{
background:#e5e7eb;
color:#374151;
transform:translateY(-2px);
}

/* ========================= */
/* CARD - matches manage_questions.php card style */
/* ========================= */

.card{
border:none;
border-radius:18px;
overflow:hidden;
box-shadow:0 10px 30px rgba(17,24,39,.08);
animation:fadeInUp .4s ease;
background:#fff;
}

@keyframes fadeInUp{
from{opacity:0;transform:translateY(10px);}
to{opacity:1;transform:translateY(0);}
}

.card-body-custom{
padding:25px;
width:100%;
max-width:100%;
}

/* Alerts */
.alert{
border:none;
border-radius:12px;
padding:14px 18px;
font-size:14px;
font-weight:500;
margin-bottom:20px;
}

.alert-success{
background:#dcfce7;
color:#15803d;
}

.alert-danger{
background:#fee2e2;
color:#b91c1c;
}

/* Labels */
.form-label{
font-size:12px;
color:#6b7280;
text-transform:uppercase;
letter-spacing:.5px;
font-weight:600;
margin-bottom:6px;
}

/* Inputs */
.form-control{
width:100%;
max-width:100%;
border-radius:10px;
border:1px solid #e5e7eb;
font-size:14px;
padding:11px 14px;
color:#374151;
background:#f8fbff;
transition:.2s ease;
}

.form-control:focus{
border-color:#2a5298;
box-shadow:0 0 0 .2rem rgba(42,82,152,.15);
background:#fff;
outline:none;
}

.form-control[readonly]{
background:#eef1f7;
color:#6b7280;
cursor:not-allowed;
}

/* Textarea */
textarea.form-control{
min-height:100px;
resize:vertical;
}

.json-box{
background:#0b1220 !important;
color:#00ff9c !important;
border:none !important;
font-family:monospace;
font-size:13px;
border-radius:12px !important;
}

/* Image */
.question-img{
max-width:100%;
height:auto;
margin-top:12px;
border-radius:12px;
box-shadow:0 8px 20px rgba(17,24,39,.1);
}

/* Buttons */
.btn-update{
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

.btn-update:hover{
transform:translateY(-2px);
box-shadow:0 12px 26px rgba(30,60,114,.3);
color:#fff;
}

.btn-cancel{
display:inline-flex;
align-items:center;
gap:8px;
background:#f1f3f7;
color:#374151;
border:none;
padding:11px 26px;
border-radius:40px;
font-weight:600;
font-size:14px;
transition:.25s ease;
}

.btn-cancel:hover{
background:#e5e7eb;
color:#374151;
transform:translateY(-2px);
}

.form-actions{
display:flex;
gap:12px;
flex-wrap:wrap;
margin-top:5px;
}


/* ========================= */
/* MOBILE */
/* ========================= */

@media(max-width:768px){

.questions-header{
flex-direction:column;
align-items:flex-start;
}

.header-right{
width:100%;
}

.btn-back{
width:100%;
justify-content:center;
}

.header-title{
font-size:22px;
}

}

@media(max-width:575px){

.row.g-4 > div{
width:100%;
max-width:100%;
flex:0 0 100%;
}

.form-control{
font-size:15px;
padding:10px;
}

.form-actions{
flex-direction:column;
}

.btn-update,
.btn-cancel{
width:100%;
justify-content:center;
}

.page-container{
padding:5px;
}

.card-body-custom{
padding:16px;
}

}

@media(max-width:360px){

.header-icon{
width:45px;
height:45px;
font-size:20px;
border-radius:12px;
}

.header-title{
font-size:17px;
}

.form-control{
font-size:14px;
padding:9px;
}

}

@media(max-width:300px){

.card-body-custom{
padding:12px;
}

.form-control{
font-size:13px;
padding:8px;
}

textarea.form-control{
min-height:80px;
}

.btn-update,
.btn-cancel{
font-size:13px;
padding:9px;
}

.question-img{
max-height:150px;
}

}

</style>



<div class="page-container">

<!-- HEADER -->
<div class="questions-header">

<div class="header-left">

<div class="header-icon">
<i class="bi bi-pencil-square"></i>
</div>

<div>
<!-- <h2 class="header-title">Edit Question #<?= $id ?></h2> -->
<h2 class="header-title">Edit Question</h2>
<p class="header-subtitle">Update question details below</p>
</div>

</div>

<div class="header-right">
<button onclick="goBackPage()" class="btn-back">
<i class="bi bi-arrow-left"></i>
Back
</button>
</div>

</div>


<!-- CARD -->
<div class="card">
<div class="card-body-custom">

<?= $msg ?>

<form method="POST" enctype="multipart/form-data"
      action="teacher_question_pages/edit_question.php?id=<?= $id ?>">

<div class="row g-4">


<div class="col-12">

<label class="form-label">Question Text</label>

<textarea name="question_text"
class="form-control"
rows="3"
required><?= htmlspecialchars($data['question_text']) ?></textarea>

</div>



<div class="col-md-6">

<label class="form-label">Question Type</label>

<input type="text"
class="form-control"
value="<?= htmlspecialchars($data['question_type']) ?>"
readonly>

</div>



<div class="col-md-6">

<label class="form-label">Correct Answer</label>

<input type="text"
name="correct_answer"
class="form-control"
required
value="<?= htmlspecialchars($data['correct_answer']) ?>">

</div>



<div class="col-md-6">

<label class="form-label">Unit</label>

<input type="text"
name="unit"
class="form-control"
value="<?= htmlspecialchars($data['unit']) ?>">

</div>



<div class="col-md-6">

<label class="form-label">Upload New Image</label>

<input type="file"
name="question_image"
class="form-control">

<?php if(!empty($data['question_image'])): ?>

<img src="<?= htmlspecialchars($data['question_image']) ?>"
class="question-img">

<?php endif; ?>

</div>



<div class="col-12">

<label class="form-label">Payload JSON</label>

<textarea class="form-control json-box"
rows="6"
readonly><?= json_encode($payload,JSON_PRETTY_PRINT) ?></textarea>

</div>



<div class="col-12">

<div class="form-actions">

<button type="submit" class="btn-update">
<i class="bi bi-check-circle"></i>
Update Question
</button>


<button type="button"
onclick="goBackPage()"
class="btn-cancel">

<i class="bi bi-arrow-left"></i>
Back

</button>

</div>

</div>


</div>

</form>

</div>
</div>

</div>


<script>

function goBackPage(){

if(window.history.length > 1){

window.history.back();

}
else{

loadPage('teacher_question_pages/manage_questions.php');

}

}

$(document).on("submit", "form", function(e){
    if ($(this).attr("enctype") === "multipart/form-data" && $(this).closest("#content-area").length) {
        e.preventDefault();

        let formData = new FormData(this);
        let url = $(this).attr("action") || window.location.href;

        $.ajax({
            url: url,
            type: "POST",
            data: formData,
            processData: false,
            contentType: false,
            success: function(data){
                $("#content-area").html(data);
            },
            error: function(){
                alert("Update failed to load response");
            }
        });
    }
});

</script>