<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include '../db_config.php';


/*
|--------------------------------------------------------------------------
| AUTH CHECK
|--------------------------------------------------------------------------
*/

if (!isset($_SESSION['teacher_id']))
{
    header('Location: ../teacher_login.php');
    exit();
}


$teacher_id = (int)$_SESSION['teacher_id'];

$msg = "";


/*
|--------------------------------------------------------------------------
| FETCH GRADES
|--------------------------------------------------------------------------
*/

$grades = [];

$res = mysqli_query(
    $conn,
    "
    SELECT DISTINCT grade
    FROM subjects
    WHERE grade IS NOT NULL
    AND grade != ''
    ORDER BY CAST(grade AS UNSIGNED)
    "
);

while ($r = mysqli_fetch_assoc($res))
{
    $grades[] = $r['grade'];
}



/*
|--------------------------------------------------------------------------
| FETCH STUDENTS
|--------------------------------------------------------------------------
*/

$students = [];

$stmt = $conn->prepare(
    "
    SELECT
        s.id,
        si.image_path,
        TRIM(
            CONCAT(
                COALESCE(s.first_name,''),
                IF(s.first_name IS NOT NULL AND s.last_name IS NOT NULL,' ',''),
                COALESCE(s.last_name,'')
            )
        ) AS name

    FROM teacher_subjects ts

    INNER JOIN student_subjects ss
    ON ts.subject_id = ss.subject_id

    INNER JOIN students s
    ON s.id = ss.student_id

    LEFT JOIN student_images si
    ON si.student_id = s.id

    WHERE ts.teacher_id = ?

    GROUP BY
    s.id,
    si.image_path,
    s.first_name,
    s.last_name

    ORDER BY s.first_name,s.last_name,s.id
    "
);

$stmt->bind_param("i", $teacher_id);

$stmt->execute();

$result = $stmt->get_result();

while ($row = $result->fetch_assoc())
{
    $name = !empty($row['name'])
        ? $row['name']
        : "Student #" . $row['id'];

    $students[] =
    [
        'id'         => $row['id'],
        'name'       => $name,
        'image_path' => $row['image_path']
    ];
}

$stmt->close();



/*
|--------------------------------------------------------------------------
| FORM SUBMIT
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST')
{

    $title       = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');

    $due_date =
        !empty($_POST['due_date'])
        ? $_POST['due_date']
        : null;

    $time_limit =
        max(
            5,
            (int)($_POST['time_limit'] ?? 30)
        );

    $allow_retake =
        isset($_POST['allow_retake'])
        ? 1
        : 0;

    $topic_id =
        !empty($_POST['topic_id'])
        ? (int)$_POST['topic_id']
        : null;

    $selected_questions =
        $_POST['questions'] ?? [];

    $student_ids =
        $_POST['student_ids'] ?? [];


    /*
    |--------------------------------------------------------------------------
    | VALIDATION
    |--------------------------------------------------------------------------
    */

    if (empty($title))
    {
        $msg =
        "
        <div class='alert alert-danger alert-dismissible fade show' role='alert'>
            <i class='bi bi-exclamation-circle-fill me-2'></i>
            Assessment title required
            <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
        </div>
        ";

        goto end_submit;
    }



    /*
    |--------------------------------------------------------------------------
    | START TRANSACTION
    |--------------------------------------------------------------------------
    */

    $conn->begin_transaction();


    try
    {

        /*
        |--------------------------------------------------------------------------
        | INSERT ASSESSMENT
        |--------------------------------------------------------------------------
        */

        $stmt = $conn->prepare(
            "
            INSERT INTO assessments
            (
                teacher_id,
                title,
                description,
                topic_id,
                due_date,
                time_limit_minutes,
                allow_retake
            )
            VALUES (?,?,?,?,?,?,?)
            "
        );

        $stmt->bind_param(
            "issisii",
            $teacher_id,
            $title,
            $description,
            $topic_id,
            $due_date,
            $time_limit,
            $allow_retake
        );

        $stmt->execute();

        $assessment_id = $conn->insert_id;

        $stmt->close();

        /*
        |--------------------------------------------------------------------------
        | INSERT QUESTIONS
        |--------------------------------------------------------------------------
        */

        if ($topic_id && empty($selected_questions))
        {

            $qsel = $conn->prepare(
                "
                SELECT id
                FROM quiz_questions
                WHERE instruction_id IN
                (
                    SELECT id
                    FROM instructions
                    WHERE topic_id = ?
                )
                "
            );

            $qsel->bind_param("i", $topic_id);

            $qsel->execute();

            $qres = $qsel->get_result();


            $qinsert = $conn->prepare(
                "
                INSERT INTO assessment_questions
                (
                    assessment_id,
                    question_id,
                    question_order
                )
                VALUES (?,?,?)
                "
            );

            $order = 1;

            while ($row = $qres->fetch_assoc())
            {
                $qid = (int)$row['id'];

                $qinsert->bind_param(
                    "iii",
                    $assessment_id,
                    $qid,
                    $order
                );

                $qinsert->execute();

                $order++;
            }

        }


        elseif (!empty($selected_questions))
        {

            $qinsert = $conn->prepare(
                "
                INSERT INTO assessment_questions
                (
                    assessment_id,
                    question_id,
                    question_order
                )
                VALUES (?,?,?)
                "
            );

            foreach ($selected_questions as $index=>$qid)
            {

                $order = $index + 1;

                $qid = (int)$qid;

                $qinsert->bind_param(
                    "iii",
                    $assessment_id,
                    $qid,
                    $order
                );

                $qinsert->execute();

            }

        }



        $total_questions = 0;

        $qcount = $conn->prepare("
        SELECT COUNT(*) 
        FROM assessment_questions 
        WHERE assessment_id = ?
        ");

        $qcount->bind_param("i", $assessment_id);
        $qcount->execute();
        $qcount->bind_result($total_questions);
        $qcount->fetch();
        $qcount->close();

        /*
        |--------------------------------------------------------------------------
        | ASSIGN STUDENTS
        |--------------------------------------------------------------------------
        */

        if (!empty($student_ids))
        {

          $assign = $conn->prepare(
            "
            INSERT INTO assessment_assignments
            (
            assessment_id,
            student_id,
            total_questions
            )
            VALUES (?,?,?)
            "
            );

            foreach ($student_ids as $sid)
            {

                $sid = (int)$sid;

              $assign->bind_param(
                "iii",
                $assessment_id,
                $sid,
                $total_questions
                );

                $assign->execute();

            }

        }



        $conn->commit();

      echo "<script>
        alert('Assessment Created Successfully');
        window.location='../teacher_dashboard.php?page=teacher_question_pages/manage_assessments.php';
        </script>";
        exit();
    }

    catch(Exception $e)
    {

        $conn->rollback();

        $msg =
        "
        <div class='alert alert-danger alert-dismissible fade show' role='alert'>
            <i class='bi bi-exclamation-circle-fill me-2'></i>
            ".$e->getMessage()."
            <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
        </div>
        ";

    }

}

end_submit:
?>



<link
href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css"
rel="stylesheet"
>



<style>

.assign-container{
padding:5px;
}

.assign-header{
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
display:block;
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

.form-check{
padding-left:1.9em;
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

.panel-box{
border:1px solid #e2e8f0;
border-radius:10px;
padding:16px;
max-height:300px;
overflow:auto;
background:#fbfcfe;
}

.checkbox-list label{
display:flex;
align-items:center;
gap:12px;
padding:10px 12px;
border-radius:8px;
cursor:pointer;
border-bottom:1px solid #edf1f7;
margin-bottom:4px;
}

.checkbox-list label:last-child{
border-bottom:none;
margin-bottom:0;
}

.checkbox-list label:hover{
background:#f8fbff;
}

.checkbox-list input[type="checkbox"]{
flex-shrink:0;
}

.student-cell{
display:flex;
align-items:center;
gap:12px;
}

.avatar{
width:38px;
height:38px;
border-radius:50%;
display:flex;
align-items:center;
justify-content:center;
overflow:hidden;
background:linear-gradient(135deg,#1e3c72,#2a5298);
color:#fff;
font-weight:600;
font-size:14px;
flex-shrink:0;
}

.student-photo{
width:38px;
height:38px;
border-radius:50%;
object-fit:cover;
object-position:center;
border:2px solid #fff;
box-shadow:0 3px 10px rgba(0,0,0,.15);
flex-shrink:0;
}

.student-name{
font-weight:600;
font-size:14.5px;
color:#374151;
}

.empty-state-mini{
padding:40px 20px;
text-align:center;
color:#9ca3af;
}

.empty-state-mini i{
font-size:45px;
color:#d8d8d8;
display:block;
margin-bottom:12px;
}

.btn-submit-assign{
display:inline-flex;
align-items:center;
gap:8px;
background:linear-gradient(135deg,#1e3c72,#2a5298);
color:#fff;
border:none;
padding:12px 34px;
border-radius:40px;
font-weight:600;
font-size:15px;
transition:.25s ease;
}

.btn-submit-assign:hover{
transform:translateY(-2px);
box-shadow:0 12px 26px rgba(30,60,114,.32);
color:#fff;
}

hr{
margin:30px 0;
border-top:1px solid #edf1f7;
}


/* ============================= */
/* MOBILE RESPONSIVE FIX */
/* ============================= */

@media (max-width: 575px)
{

.assign-container{
padding:0;
}

.assign-header{
flex-direction:column;
align-items:flex-start;
}

.header-right{
width:100%;
justify-content:space-between;
}

.header-title{
font-size:18px;
}

.form-card{
padding:16px;
}

.row.g-4 > div{
width:100%;
flex:0 0 100%;
max-width:100%;
}

input[type="datetime-local"]{
width:100%;
}

select.form-select{
width:100%;
}

input[type="number"]{
width:100%;
}

.panel-box{
max-height:250px;
padding:10px;
}

.checkbox-list label{
font-size:14px;
word-break:break-word;
}

.btn-submit-assign{
width:100%;
padding:12px;
font-size:16px;
justify-content:center;
}

}


/* ================================= */
/* ULTRA SMALL MOBILE FIX (300px+) */
/* ================================= */

@media (max-width: 575px)
{

.form-card{
padding:12px;
}

.row.g-4{
row-gap:14px !important;
}

.form-control,
.form-select{
width:100%;
padding:10px 12px;
font-size:15px;
border-radius:8px;
}

textarea.form-control{
padding:12px;
min-height:90px;
resize:vertical;
}

input[type="datetime-local"]{
padding:10px 12px;
}

input[type="number"]{
padding:10px 12px;
}

.form-check{
padding-left:28px;
}

.form-check-input{
margin-left:-28px;
margin-top:4px;
}

select.form-select{
padding:10px 12px;
}

.panel-box{
padding:12px;
max-height:220px;
}

.checkbox-list label{
padding:6px 4px;
font-size:14px;
}

}


/* ================================= */
/* EXTREME SMALL DEVICES (300px) */
/* ================================= */

@media (max-width: 360px)
{

.form-card{
padding:10px;
}

.header-title{
font-size:16px;
}

.form-control,
.form-select{
font-size:14px;
padding:9px 10px;
}

textarea.form-control{
min-height:80px;
}

.btn-submit-assign{
font-size:15px;
padding:10px;
}

}

</style>



<div class="assign-container">

<div class="assign-header">

<div class="header-left">

<div class="header-icon">
<i class="bi bi-clipboard-plus"></i>
</div>

<div>
<h2 class="header-title">Assign Assessment</h2>
<p class="header-subtitle">
Create a new assessment and assign it to your students
</p>
</div>

</div>

<div class="header-right">

<span class="subject-badge">
<i class="bi bi-mortarboard-fill me-1"></i>
Students : <?= count($students) ?>
</span>

</div>

</div>


<?= $msg ?>


<div class="card">

<div class="form-card">

<form method="POST" action="teacher_question_pages/assign_assessment.php">


<div class="row g-4">


<div class="col-lg-8">

<label>Assessment Title</label>

<input
type="text"
name="title"
class="form-control form-control-lg"
placeholder="Assessment Title"
required>

</div>



<div class="col-lg-4">

<label>Due Date</label>

<input
type="datetime-local"
name="due_date"
class="form-control form-control-lg">

</div>



<div class="col-12">

<label>Description</label>

<textarea
name="description"
class="form-control"
rows="3"
placeholder="Description"></textarea>

</div>



<div class="col-md-4">

<label>Time Limit (minutes)</label>

<input
type="number"
name="time_limit"
class="form-control"
value="30">

</div>



<div class="col-md-4">

<label>&nbsp;</label>

<div class="form-check mt-2">

<input
type="checkbox"
name="allow_retake"
class="form-check-input"
checked>

<label class="form-check-label">
Allow Retake
</label>

</div>

</div>



<div class="col-12"><hr></div>



<div class="col-md-3">

<label>Grade</label>

<select id="grade" class="form-select">

<option value="">
-- Grade --
</option>

<?php foreach($grades as $g): ?>

<option value="<?= $g ?>">

Grade <?= $g ?>

</option>

<?php endforeach; ?>

</select>

</div>



<div class="col-md-3">

<label>Subject</label>

<select id="subject_id" class="form-select">

<option>
-- Subject --
</option>

</select>

</div>



<div class="col-md-3">

<label>Chapter</label>

<select id="chapter_id" class="form-select">

<option>
-- Chapter --
</option>

</select>

</div>



<div class="col-md-3">

<label>Topic</label>

<select name="topic_id" id="topic_id" class="form-select">

<option>
-- Topic --
</option>

</select>

</div>


<div class="col-12">

<h3 class="section-heading">
<i class="bi bi-question-circle"></i>
Select Questions
</h3>

<div id="question-list" class="panel-box">
Select topic to load questions
</div>

</div>


<div class="col-12">

<h3 class="section-heading">
<i class="bi bi-people"></i>
Assign Students
</h3>

<div class="panel-box checkbox-list">

<?php if (!empty($students)): ?>

<?php foreach($students as $s): ?>

<label>

<input type="checkbox" name="student_ids[]" value="<?= $s['id'] ?>">

<div class="student-cell">

<?php if(!empty($s['image_path'])): ?>

<img
    src="Student_dashboard/<?= htmlspecialchars($s['image_path']) ?>"
    class="student-photo"
    alt="Student">

<?php else: ?>

<div class="avatar">
    <?= strtoupper(substr($s['name'],0,1)); ?>
</div>

<?php endif; ?>

<span class="student-name">
<?= htmlspecialchars($s['name']) ?>
</span>

</div>

</label>

<?php endforeach; ?>

<?php else: ?>

<div class="empty-state-mini">
<i class="bi bi-people"></i>
No students are currently assigned to your subjects.
</div>

<?php endif; ?>

</div>

</div>



<div class="col-12 mt-4">

<button type="submit" class="btn-submit-assign">
<i class="bi bi-send-fill"></i>
Assign Assessment
</button>

</div>


</div>


</form>

</div>

</div>


</div>

<script>

$(document).ready(function(){


// Grade -> Subjects
$("#grade").change(function(){

let grade=$(this).val();

$.get("teacher_question_pages/fetch_subjects.php",
{grade:grade},
function(data){

$("#subject_id").html('<option value="">All Subjects</option>'+data);

$("#chapter_id").html('<option value="">All Chapters</option>');

$("#topic_id").html('<option value="">All Topics</option>');

});

});



// Subject -> Chapters
$("#subject_id").change(function(){

let sid=$(this).val();

$.get("teacher_question_pages/fetch_chapters.php",
{subject_id:sid},
function(data){

$("#chapter_id").html('<option value="">All Chapters</option>'+data);

$("#topic_id").html('<option value="">All Topics</option>');

});

});



// Chapter -> Topics
$("#chapter_id").change(function(){

let cid=$(this).val();

$.get("teacher_question_pages/fetch_topics.php",
{chapter_id:cid},
function(data){

$("#topic_id").html('<option value="">All Topics</option>'+data);

});

});



});

// Topic -> Questions
$("#topic_id").change(function(){

let tid=$(this).val();

if(!tid){
$("#question-list").html("No topic selected");
return;
}

$("#question-list").html("Loading questions...");

$.get(
"teacher_question_pages/fetch_questions_for_assessment.php",
{topic_id:tid},
function(data){

$("#question-list").html(data);

});

});

</script>