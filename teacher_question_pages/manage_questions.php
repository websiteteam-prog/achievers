<?php

session_start();

include '../db_config.php';

if (!isset($_SESSION['teacher_id']))
{
    exit('Unauthorized access.');
}


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

while ($row = mysqli_fetch_assoc($res))
{
    $grades[] = $row['grade'];
}


/*
|--------------------------------------------------------------------------
| FETCH SUBJECTS
|--------------------------------------------------------------------------
*/

$subjects = [];

$res = mysqli_query(
    $conn,
    "
    SELECT id, subject_name
    FROM subjects
    ORDER BY subject_name
    "
);

while ($row = mysqli_fetch_assoc($res))
{
    $subjects[] = $row;
}


/*
|--------------------------------------------------------------------------
| FILTER QUERY
|--------------------------------------------------------------------------
*/
$where  = [];
$params = [];
$types  = "";

if (!empty($_GET['grade']))
{
    $where[]  = "s.grade = ?";
    $params[] = (int)$_GET['grade'];
    $types   .= "i";
}

if (!empty($_GET['subject_id']))
{
    $where[]  = "s.id = ?";
    $params[] = (int)$_GET['subject_id'];
    $types   .= "i";
}

if (!empty($_GET['chapter_id']))
{
    $where[]  = "c.id = ?";
    $params[] = (int)$_GET['chapter_id'];
    $types   .= "i";
}

if (!empty($_GET['topic_id']))
{
    $where[]  = "t.id = ?";
    $params[] = (int)$_GET['topic_id'];
    $types   .= "i";
}

if (!empty($_GET['instruction_id']))
{
    $where[]  = "i.id = ?";
    $params[] = (int)$_GET['instruction_id'];
    $types   .= "i";
}

$where_clause = "";

if (!empty($where))
{
    $where_clause = "WHERE " . implode(" AND ", $where);
}
/*
|--------------------------------------------------------------------------
| FETCH QUESTIONS
|--------------------------------------------------------------------------
*/

$sql = "

SELECT
    q.id,
    q.question_type,
    q.unit,
    i.instruction,
    t.title AS topic,
    c.chapter_name,
    s.subject_name,
    s.grade

FROM quiz_questions q

LEFT JOIN instructions i ON q.instruction_id = i.id
LEFT JOIN topics t ON i.topic_id = t.id
LEFT JOIN chapters c ON t.chapter_id = c.id
LEFT JOIN subjects s ON c.subject_id = s.id

$where_clause

ORDER BY q.id DESC

";

$stmt = $conn->prepare($sql);

if ($params)
{
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();

$result = $stmt->get_result();

$total_questions = $result->num_rows;

?>


<link
href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css"
rel="stylesheet"
>


<style>

.questions-container{
padding:5px;
}

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

.btn-add{
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

.btn-add:hover{
transform:translateY(-2px);
box-shadow:0 12px 26px rgba(232,6,60,.32);
color:#fff;
}

/* FILTER CARD */

.filter-card{
background:transparent;
padding:22px;
border-radius:18px;
margin-bottom:25px;
}

.filter-card .form-select{

    padding-right:50px;

    background-position:right 16px center;

    overflow:hidden;

    text-overflow:ellipsis;

    white-space:nowrap;
}

.filter-card .form-select:focus{
border-color:#2a5298;
box-shadow:0 0 0 .2rem rgba(42,82,152,.15);
}

.btn-filter{
background:linear-gradient(135deg,#1e3c72,#2a5298);
border:none;
color:#fff;
border-radius:10px;
font-weight:600;
font-size:13.5px;
padding:9px 12px;
transition:.2s ease;
}

.btn-filter:hover{
transform:translateY(-1px);
box-shadow:0 8px 18px rgba(30,60,114,.25);
color:#fff;
}

.btn-clear{
background:#f1f3f7;
border:none;
color:#374151;
border-radius:10px;
font-weight:600;
font-size:13.5px;
padding:9px 12px;
text-decoration:none;
display:inline-block;
text-align:center;
transition:.2s ease;
}

.btn-clear:hover{
background:#e5e7eb;
color:#374151;
}

/* TABLE CARD */

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

.table-responsive{
overflow-x:auto;
}

.questions-table{
width:100%;
border-collapse:collapse;
}

.questions-table th{
background:#2a5298;
color:#fff;
padding:16px 15px;
font-size:13px;
text-transform:uppercase;
letter-spacing:.6px;
font-weight:600;
white-space:nowrap;
}

.questions-table td{
padding:16px 15px;
border-bottom:1px solid #edf1f7;
vertical-align:middle;
font-size:14.5px;
color:#374151;
word-break:break-word;
}

.questions-table tbody tr{
transition:background .2s ease;
}

.questions-table tbody tr:hover{
background:#f8fbff;
}

.type-badge{
    display:inline-flex;
    align-items:center;
    justify-content:center;

    background:#d9f3ff;
    color:#0c7abf;

    padding:8px 14px;
    border-radius:20px;

    font-size:12px;
    font-weight:600;

    min-height:34px;
    min-width:170px;
    max-width:280px;

    white-space:nowrap;
}

.truncate-text{
display:-webkit-box;
-webkit-line-clamp:2;
-webkit-box-orient:vertical;
overflow:hidden;
cursor:pointer;
}

.custom-tooltip{
position:fixed;
background:#111827;
color:#fff;
padding:8px 12px;
border-radius:8px;
font-size:13px;
z-index:9999;
display:none;
box-shadow:0 8px 20px rgba(0,0,0,0.2);
width:fit-content;
max-width:500px;
white-space:normal;
}

.action-group{
display:flex;
gap:6px;
justify-content:center;
}

.icon-btn{
border:none;
padding:7px 10px;
border-radius:8px;
color:#fff;
display:inline-flex;
align-items:center;
justify-content:center;
transition:.2s ease;
}

.icon-btn:hover{
transform:translateY(-1px);
color:#fff;
}

.btn-view{ background:#06b6d4; }
.btn-edit{ background:#f59e0b; }
.btn-delete{ background:#ef4444; }

.empty-state{
padding:70px 20px;
text-align:center;
}

.empty-state i{
font-size:75px;
color:#d8d8d8;
}

.questions-table td:first-child,
.questions-table th:first-child{
    width:90px;
    min-width:90px;
    white-space:nowrap;
    text-align:center;
    font-weight:700;
}

@media(max-width:768px){

.questions-header{
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

.questions-table{
min-width:900px;
}

}

</style>



<div class="questions-container">

<!-- HEADER -->

<div class="questions-header">

<div class="header-left">

<div class="header-icon">
<i class="bi bi-question-circle-fill"></i>
</div>

<div>
<h2 class="header-title">Manage Questions</h2>
<p class="header-subtitle">
<?= $total_questions ?> question<?= $total_questions==1?'':'s' ?> found
</p>
</div>

</div>

<div class="header-right">

<span class="subject-badge">
<i class="bi bi-collection-fill me-1"></i>
Total : <?= $total_questions ?>
</span>

<a
    href="teacher_dashboard.php?page=teacher_question_pages/add_question.php"
    class="btn-add"
>

    <i class="bi bi-plus-circle"></i>

    Add New Question

</a>

</div>

</div>



<!-- FILTER -->

<div class="filter-card">

<form id="filterForm">

<div class="row g-3">


<div class="col-md-2">

<select name="grade" id="grade" class="form-select">

<option value="">All Grades</option>

<?php foreach ($grades as $g): ?>

<option
    value="<?= $g ?>"
    <?= isset($_GET['grade']) && $_GET['grade'] == $g ? "selected" : "" ?>
>

Grade <?= $g ?>

</option>

<?php endforeach; ?>

</select>

</div>



<div class="col-md-2">

<select name="subject_id" id="subject_id" class="form-select">

<option value="">All Subjects</option>

<?php
if (!empty($_GET['grade'])) {

    $gid = (int)$_GET['grade'];

    $sub = mysqli_query($conn,"SELECT id, subject_name FROM subjects WHERE grade=$gid ORDER BY subject_name");

    while ($s = mysqli_fetch_assoc($sub)) {

        $sel = (!empty($_GET['subject_id']) && $_GET['subject_id']==$s['id']) ? "selected" : "";

        echo "<option value='{$s['id']}' $sel>{$s['subject_name']}</option>";
    }
}
?>

</select>

</div>



<div class="col-md-2">
<select name="chapter_id" id="chapter_id" class="form-select">

<option value="">All Chapters</option>

<?php
if (!empty($_GET['subject_id'])) {

    $sid = (int)$_GET['subject_id'];

    $chap = mysqli_query($conn,"SELECT id,chapter_name FROM chapters WHERE subject_id=$sid ORDER BY chapter_name");

    while ($c = mysqli_fetch_assoc($chap)) {

        $sel = (!empty($_GET['chapter_id']) && $_GET['chapter_id']==$c['id']) ? "selected" : "";

        echo "<option value='{$c['id']}' $sel>{$c['chapter_name']}</option>";
    }
}
?>

</select>
</div>


<div class="col-md-2">
<select name="topic_id" id="topic_id" class="form-select">

<option value="">All Topics</option>

<?php
if (!empty($_GET['chapter_id'])) {

    $cid = (int)$_GET['chapter_id'];

    $top = mysqli_query($conn,"SELECT id,title FROM topics WHERE chapter_id=$cid ORDER BY title");

    while ($t = mysqli_fetch_assoc($top)) {

        $sel = (!empty($_GET['topic_id']) && $_GET['topic_id']==$t['id']) ? "selected" : "";

        echo "<option value='{$t['id']}' $sel>{$t['title']}</option>";
    }
}
?>

</select>
</div>


<div class="col-md-2">
<select name="instruction_id" id="instruction_id" class="form-select">

<option value="">All Instructions</option>

<?php
if (!empty($_GET['topic_id'])) {

    $tid = (int)$_GET['topic_id'];

    $ins = mysqli_query($conn,"SELECT id,instruction FROM instructions WHERE topic_id=$tid ORDER BY instruction");

    while ($i = mysqli_fetch_assoc($ins)) {

        $sel = (!empty($_GET['instruction_id']) && $_GET['instruction_id']==$i['id']) ? "selected" : "";

        echo "<option value='{$i['id']}' $sel>{$i['instruction']}</option>";
    }
}
?>

</select>
</div>



<div class="col-md-2 d-flex gap-2">

<button type="submit" class="btn-filter w-50">
<!-- <i class="bi bi-funnel-fill me-1"></i> -->
Filter
</button>

<button
type="button"
id="clearFilter"
class="btn-clear w-50">
Clear
</button>

</div>

<div id="tooltipBox" class="custom-tooltip"></div>

</div>

</form>

</div>



<!-- TABLE -->

<?php if ($result->num_rows > 0): ?>

<div class="card">

<div class="card-body p-0">

<div class="table-responsive">

<table class="questions-table ">

<thead class="head text-center">

<tr>

<th width="90">Sr</th>
<th>Grade</th>
<th style="min-width:120px;">Subject</th>
<th>Chapter</th>
<th>Topic</th>
<th>Instruction</th>
<th>Type</th>
<!-- <th width="80">Unit</th> -->
<th width="150">Action</th>

</tr>

</thead>



<tbody>


<?php

$sr = 1;

while ($row = $result->fetch_assoc())
{
?>


<tr>

<td><strong><?= $sr++ ?></strong></td>

<td>
    <span class="badge bg-primary">
        Grade <?= htmlspecialchars($row['grade']) ?>
    </span>
</td>

<td><?= htmlspecialchars($row['subject_name']) ?></td>

<td class="position-relative">
    <div class="truncate-text" data-full="<?= htmlspecialchars($row['chapter_name']) ?>">
        <?= htmlspecialchars($row['chapter_name']) ?>
    </div>
</td>
<td class="position-relative">
    <div class="truncate-text" data-full="<?= htmlspecialchars($row['topic']) ?>">
        <?= htmlspecialchars($row['topic']) ?>
    </div>
</td>

<td class="position-relative">
    <div class="truncate-text" data-full="<?= htmlspecialchars($row['instruction']) ?>">
        <?= htmlspecialchars($row['instruction']) ?>
    </div>
</td>


<td class= "question text-center">

<span
class="type-badge"
title="<?= htmlspecialchars($row['question_type']) ?>"
>

<?= htmlspecialchars($row['question_type']) ?>

</span>

</td>


<!-- <td>

<?= $row['unit'] ? htmlspecialchars($row['unit']) : "-" ?>

</td> -->


<td>

<div class="action-group">


<a
href="teacher_dashboard.php?page=teacher_question_pages/view_question.php?id=<?= $row['id'] ?>"
class="icon-btn btn-view"
>

<i class="bi bi-eye"></i>

</a>


<a
href="teacher_dashboard.php?page=teacher_question_pages/edit_question.php?id=<?= $row['id'] ?>"
class="icon-btn btn-edit"
>

<i class="bi bi-pencil"></i>

</a>


<button
onclick="deleteQuestion(<?= $row['id'] ?>)"
class="icon-btn btn-delete"
>

<i class="bi bi-trash"></i>

</button>


</div>

</td>

</tr>


<?php
}
?>


</tbody>

</table>


</div>

</div>

</div>

<?php else: ?>

<div class="empty-state">

<i class="bi bi-question-circle"></i>

<h3 class="mt-4">
No Questions Found
</h3>

<p class="text-muted">
Try adjusting your filters or add a new question.
</p>

</div>

<?php endif; ?>



</div>


<!-- <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> -->
<script>

function deleteQuestion(id)
{
    if (confirm("Delete question?"))
    {
        fetch(
            "teacher_question_pages/delete_question.php",
            {
                method:"POST",

                headers:
                {
                    "Content-Type":"application/x-www-form-urlencoded"
                },

                body:"id="+id
            }
        )
        .then(() => location.reload());
    }
}

// $(document).ready(function() {

    // Grade -> Subjects
    $(document).off('change','#grade');

    $(document).on('change','#grade',function () {

        const grade = $(this).val();

        $('#subject_id').html('<option>Loading...</option>');
        $('#chapter_id').html('<option>Loading...</option>');
        $('#topic_id').html('<option>Loading...</option>');
        $('#instruction_id').html('<option>Loading...</option>');

        $.get('teacher_question_pages/fetch_subjects.php', { grade: grade }, function (data) {

            $('#subject_id').html('<option value="">All Subjects</option>' + data);
            $('#chapter_id').html('<option value="">All Chapters</option>');
            $('#topic_id').html('<option value="">All Topics</option>');
            $('#instruction_id').html('<option value="">All Instructions</option>');

        });

    });


    // Subject -> Chapters
    $(document).off('change','#subject_id');

    $(document).on('change','#subject_id',function() {

        const sid = $(this).val();

        $('#chapter_id').html('<option>Loading...</option>');
        $('#topic_id').html('<option>Loading...</option>');
        $('#instruction_id').html('<option>Loading...</option>');

        $.get('teacher_question_pages/fetch_chapters.php', {subject_id: sid}, function(data) {

            $('#chapter_id').html('<option value="">All Chapters</option>' + data);
            $('#topic_id').html('<option value="">All Topics</option>');
            $('#instruction_id').html('<option value="">All Instructions</option>');

        });

    });


    // Chapter -> Topics
    $(document).off('change','#chapter_id');

    $(document).on('change','#chapter_id',function() {

        const cid = $(this).val();

        $('#topic_id').html('<option>Loading...</option>');
        $('#instruction_id').html('<option>Loading...</option>');

        $.get('teacher_question_pages/fetch_topics.php', {chapter_id: cid}, function(data) {

            $('#topic_id').html('<option value="">All Topics</option>' + data);
            $('#instruction_id').html('<option value="">All Instructions</option>');

        });

    });


    // Topic -> Instructions
    $(document).off('change','#topic_id');

    $(document).on('change','#topic_id',function() {

        const tid = $(this).val();

        $('#instruction_id').html('<option>Loading...</option>');

        $.get('teacher_question_pages/fetch_instructions.php', {topic_id: tid}, function(data) {

            $('#instruction_id').html('<option value="">All Instructions</option>' + data);

        });

    });

// });
// ===========================
// FILTER USING AJAX
// ===========================

$(document).off("submit","#filterForm");

$(document).on("submit","#filterForm",function(e){

    e.preventDefault();

    let query=$(this).serialize();

    $("#content-area").load(
        "teacher_question_pages/manage_questions.php?"+query
    );

});

$(document).off("click","#clearFilter");

$(document).on("click","#clearFilter",function(){

    $("#content-area").load(
        "teacher_question_pages/manage_questions.php"
    );

});
// ✅ TOOLTIP FIX
var tooltip = $('#tooltipBox');

// Mouse Enter
$(document)
.off('mouseenter', '.truncate-text')
.on('mouseenter', '.truncate-text', function () {

    const text = $(this).data('full');

    if (!text) return;

    tooltip.text(text).fadeIn(150);

});

// Mouse Move
$(document)
.off('mousemove', '.truncate-text')
.on('mousemove', '.truncate-text', function (e) {

    let x = e.clientX + 15;
    let y = e.clientY + 15;

    const tooltipHeight = tooltip.outerHeight();
    const windowHeight = $(window).height();

    if (y + tooltipHeight > windowHeight) {
        y = e.clientY - tooltipHeight - 15;
    }

    tooltip.css({
        top: y + 'px',
        left: x + 'px'
    });

});

// Mouse Leave
$(document)
.off('mouseleave', '.truncate-text')
.on('mouseleave', '.truncate-text', function () {

    tooltip.hide();

});
</script>