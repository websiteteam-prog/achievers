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
    s.subject_name

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

?>


<link
href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css"
rel="stylesheet"
>


<style>

.page-container
{
    padding:10px;
}

.page-header
{
    display:flex;
    justify-content:space-between;
    align-items:center;
    flex-wrap:wrap;
    gap:10px;
    margin-bottom:20px;
}

.page-title
{
    font-size:22px;
    font-weight:600;
}

.table-responsive{
    overflow-x:hidden !important;
}

.custom-table td{
    word-break: break-word;
}

.custom-table th:last-child,
.custom-table td:last-child{
    width:110px;
    text-align:center;
}

.btn-add
{
    background:#16a34a;
    color:white;
    border:none;
    padding:8px 16px;
    border-radius:8px;
    text-decoration:none;
    display:inline-flex;
    align-items:center;
    gap:6px;
}

.filter-card
{
    background:transparent;
    padding:20px;
    border-radius:12px;
    margin-bottom:20px;
}

.custom-table{
    background:white;
    border-radius:12px;
    width:100%;
}

.custom-table thead
{
    background:#111827;
    color:white;
}

.custom-table th,
.custom-table td
{
    padding:14px;
    vertical-align:middle;
    word-wrap: break-word;
    white-space: normal;
}

.action-group{
    display:flex;
    gap:6px;
    justify-content:center;
}

.icon-btn{
    border:none;
    padding:6px 8px;
    border-radius:6px;
    color:white;
    display:inline-flex;
    align-items:center;
    justify-content:center;
}

.btn-view   { background:#06b6d4; }
.btn-edit   { background:#f59e0b; }
.btn-delete { background:#ef4444; }

.truncate-text {
    display: -webkit-box;
    -webkit-line-clamp: 2; 
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.truncate-text {
    display: -webkit-box;
    -webkit-line-clamp: 2; 
    -webkit-box-orient: vertical;
    overflow: hidden;
    cursor: pointer;
}

/* NEW FIXED TOOLTIP */
.custom-tooltip {
   position: fixed;
    background: #111827;
    color: #fff;
    padding: 8px 12px;
    border-radius: 8px;
    font-size: 13px;
    z-index: 9999;
    display: none;
    box-shadow: 0 8px 20px rgba(0,0,0,0.2);

    width: fit-content;
    max-width: 500px; /* optional limit */
    white-space: normal;
}

/* SR (increase a bit) */
.custom-table th:nth-child(1),
.custom-table td:nth-child(1) {
    width: 70px;
    text-align: center;
}

/* SUBJECT (more space) */
.custom-table th:nth-child(2),
.custom-table td:nth-child(2) {
    width: 220px;
}

/* CHAPTER (slightly more) */
.custom-table th:nth-child(3),
.custom-table td:nth-child(3) {
    width: 200px;
}

/* TOPIC */
.custom-table th:nth-child(4),
.custom-table td:nth-child(4) {
    width: 170px;
}

/* INSTRUCTION */
.custom-table th:nth-child(5),
.custom-table td:nth-child(5) {
    width: 200px;
}

/* TYPE (reduce space) */
.custom-table th:nth-child(6),
.custom-table td:nth-child(6) {
    width: 130px;
    text-align: center;
}

/* UNIT (small) */
.custom-table th:nth-child(7),
.custom-table td:nth-child(7) {
    width: 80px;
    text-align: center;
}
</style>



 <div class="page-container">



<!-- HEADER -->

<div class="page-header">

    <div class="page-title">
        Manage Questions
    </div>


    <a
        href="teacher_dashboard.php?page=teacher_question_pages/add_question.php"
        class="btn-add"
    >

        <i class="bi bi-plus-circle"></i>

        Add New Question

    </a>

</div>



<!-- FILTER -->

<div class="filter-card">

<form method="GET" action="teacher_dashboard.php">

<input type="hidden" name="page" value="teacher_question_pages/manage_questions.php">

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



<div class="col-md-2">

<button class="btn btn-primary w-100 mb-1">
Filter
</button>


<a
href="teacher_dashboard.php?page=teacher_question_pages/manage_questions.php"
class="btn btn-secondary w-100"
>

Clear

</a>

</div>

<div id="tooltipBox" class="custom-tooltip"></div>

</div>

</form>

</div>



<!-- TABLE -->

<div class="table-responsive">


<table class="table custom-table">


<thead>

<tr>

<th>Sr</th>
<th>Subject</th>
<th>Chapter</th>
<th>Topic</th>
<th>Instruction</th>
<th>Type</th>
<th>Unit</th>
<th>Action</th>

</tr>

</thead>



<tbody>


<?php

if ($result->num_rows == 0)
{
?>

<tr>

<td colspan="8" class="text-center">

No Questions Found

</td>

</tr>

<?php
}
else
{

$sr = 1;

while ($row = $result->fetch_assoc())
{
?>


<tr>

<td><?= $sr++ ?></td>

<td><?= htmlspecialchars($row['subject_name']) ?></td>

<td><?= htmlspecialchars($row['chapter_name']) ?></td>

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


<td>

<span class="badge bg-primary">

<?= $row['question_type'] ?>

</span>

</td>


<td>

<?= $row['unit'] ?: "-" ?>

</td>


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
}
?>


</tbody>

</table>


</div>



</div>


<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
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

$(document).ready(function() {

    // Grade -> Subjects
    $('#grade').change(function () {

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
    $('#subject_id').change(function() {

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
    $('#chapter_id').change(function() {

        const cid = $(this).val();

        $('#topic_id').html('<option>Loading...</option>');
        $('#instruction_id').html('<option>Loading...</option>');

        $.get('teacher_question_pages/fetch_topics.php', {chapter_id: cid}, function(data) {

            $('#topic_id').html('<option value="">All Topics</option>' + data);
            $('#instruction_id').html('<option value="">All Instructions</option>');

        });

    });


    // Topic -> Instructions
    $('#topic_id').change(function() {

        const tid = $(this).val();

        $('#instruction_id').html('<option>Loading...</option>');

        $.get('teacher_question_pages/fetch_instructions.php', {topic_id: tid}, function(data) {

            $('#instruction_id').html('<option value="">All Instructions</option>' + data);

        });

    });

});
// ✅ TOOLTIP FIX
const tooltip = $('#tooltipBox');

$('.truncate-text').on('mouseenter', function(e){

    const text = $(this).attr('data-full');

    if(!text) return;

    tooltip.text(text).fadeIn(150);

}).on('mousemove', function(e){

    let x = e.clientX + 15;
    let y = e.clientY + 15;

    const tooltipHeight = tooltip.outerHeight();
    const windowHeight = $(window).height();

    // prevent bottom cut
    if (y + tooltipHeight > windowHeight) {
        y = e.clientY - tooltipHeight - 15;
    }

    tooltip.css({
        top: y + 'px',
        left: x + 'px'
    });

}).on('mouseleave', function(){

    tooltip.hide();

});
</script>