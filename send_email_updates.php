<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

include 'db_config.php';

/*
|--------------------------------------------------------------------------
| AUTH CHECK
|--------------------------------------------------------------------------
*/

if (!isset($_SESSION['teacher_id'])) {
    echo "
        <div class='alert alert-danger'>
            Unauthorized access.
        </div>
    ";
    exit;
}

$teacher_id = (int) $_SESSION['teacher_id'];

/*
|--------------------------------------------------------------------------
| FETCH TEACHER'S ASSIGNED STUDENTS + CORRECT PAYER EMAIL
|--------------------------------------------------------------------------
*/

$sql = "
SELECT DISTINCT
    s.id,
    s.first_name,
    s.last_name,
    e.payment_by,
    e.guardian_name,
    e.mother_name,
    e.father_name,
    CASE e.payment_by
        WHEN 'Guardian' THEN e.guardian_email
        WHEN 'Mother'   THEN e.mother_email
        WHEN 'Father'   THEN e.father_email
    END AS payer_email,
    CASE e.payment_by
        WHEN 'Guardian' THEN e.guardian_name
        WHEN 'Mother'   THEN e.mother_name
        WHEN 'Father'   THEN e.father_name
    END AS payer_name
    FROM teacher_subjects ts
    INNER JOIN student_subjects ss
        ON ts.subject_id = ss.subject_id
    INNER JOIN students s
        ON s.id = ss.student_id
    INNER JOIN enrollment_inquiries e
        ON e.student_id = s.id
    WHERE
        ts.teacher_id = ?
    HAVING payer_email IS NOT NULL AND payer_email != ''
    ORDER BY s.first_name
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $teacher_id);
$stmt->execute();

$students_q = $stmt->get_result();

$total_students = $students_q->num_rows;

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

}

</style>

<div class="students-container">
<?php if(isset($_SESSION['success'])): ?>

<div class="alert alert-success alert-dismissible fade show mb-3" role="alert">
    <i class="bi bi-check-circle-fill me-2"></i>
    <?= $_SESSION['success']; ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>

<?php unset($_SESSION['success']); endif; ?>
<div class="students-header">

<div class="header-left">

<div class="header-icon">
<i class="bi bi-envelope-fill"></i>
</div>

<div>
<h2 class="header-title">Send Email Updates</h2>
<p class="header-subtitle">
Send updates or messages to parents/guardians of your students
</p>
</div>

</div>

<div class="header-right">

<span class="subject-badge">
<i class="bi bi-people-fill me-1"></i>
Total : <?= $total_students ?>
</span>

</div>

</div>

<!-- ===================== EMAIL FORM ===================== -->

<?php if ($total_students > 0): ?>

<div class="card">

<div class="form-card">

<form method="POST" action="send_email_action.php" enctype="multipart/form-data">

<div class="mb-3">

<label>Select Student</label>

<select name="student_info" class="form-select" required>

    <option value="">Select Student</option>

    <?php while ($row = $students_q->fetch_assoc()): ?>

    <option
     value="<?= htmlspecialchars($row['payer_email'] . '||' . $row['payer_name']) ?>">

        <?= htmlspecialchars($row['first_name'].' '.$row['last_name']) ?>
        (<?= htmlspecialchars($row['payment_by']) ?>:
        <?= htmlspecialchars($row['payer_email']) ?>)

    </option>

    <?php endwhile; ?>

</select>

</div>

<div class="mb-3">
<label>Email Subject</label>
<input
type="text"
name="subject"
class="form-control"
required>
</div>

<div class="mb-3">
<label>Message</label>
<textarea
name="message"
class="form-control"
rows="5"
placeholder="Write your update or message here..."
required></textarea>
</div>

<div class="mb-3">
<label>Attachment</label>
<input
type="file"
name="attachment"
class="form-control">
</div>

<button type="submit" class="btn-submit-suggestion">
<i class="bi bi-send-fill"></i>
Send Email
</button>

</form>

</div>

</div>

<?php else: ?>

<div class="empty-state">

<i class="bi bi-envelope"></i>

<h3 class="mt-4">No Students Found</h3>

<p class="text-muted">
Students assigned to your subjects will appear here once available.
</p>

</div>

<?php endif; ?>

</div>

<?php $stmt->close(); ?>
