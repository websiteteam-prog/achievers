<?php

session_start();
include 'db_config.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['teacher_id'])) {
    header("Location: teacher_login.php");
    exit;
}

$teacher_id = (int)$_SESSION['teacher_id'];
$date_today = date('Y-m-d');

/*
------------------------------------
STEP 1: Get teacher subjects (with grade)
------------------------------------
*/

$teacher_subjects_query = mysqli_query($conn, "
    SELECT s.id AS subject_id, s.subject_name, s.grade
    FROM subjects s
    INNER JOIN teacher_subjects ts ON ts.subject_id = s.id
    WHERE ts.teacher_id = '$teacher_id'
    ORDER BY s.grade, s.subject_name
");

$teacher_subjects = [];
while ($row = mysqli_fetch_assoc($teacher_subjects_query)) {
    $teacher_subjects[] = $row;
}

/*
------------------------------------
STEP 2: Selected subject (validated to this teacher)
------------------------------------
*/

$selected_subject_id = $_GET['subject_id'] ?? ($_POST['selected_subject_id'] ?? '');

$valid_subject_ids = array_column($teacher_subjects, 'subject_id');

if (!empty($teacher_subjects) &&
    ($selected_subject_id === '' || !in_array($selected_subject_id, $valid_subject_ids))) {
    $selected_subject_id = $teacher_subjects[0]['subject_id'];
}

// info of the currently selected subject (for the header badge)
$selected_info = null;
foreach ($teacher_subjects as $ts) {
    if ($ts['subject_id'] == $selected_subject_id) {
        $selected_info = $ts;
        break;
    }
}

/*
------------------------------------
STEP 3: Already marked today for this subject/grade?
------------------------------------
*/

$attendance_already_done = false;

if (!empty($teacher_subjects)) {
    $check_query = mysqli_query($conn, "
        SELECT id
        FROM attendance_records
        WHERE teacher_id = '$teacher_id'
        AND date = '$date_today'
        AND subject_id = '$selected_subject_id'
    ");
    $attendance_already_done = mysqli_num_rows($check_query) > 0;
}

/*
------------------------------------
STEP 4: SAVE attendance
------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$attendance_already_done && !empty($_POST['attendance'])) {

    $post_subject_id = $_POST['selected_subject_id'] ?? $selected_subject_id;

    foreach ($_POST['attendance'] as $student_id => $status) {

        $student_id = (int)$student_id;
        $subject_id = (int)($_POST['subject'][$student_id] ?? $post_subject_id);
        $status     = mysqli_real_escape_string($conn, $status);

        $check = mysqli_query($conn, "
            SELECT id FROM attendance_records
            WHERE teacher_id = '$teacher_id'
            AND student_id = '$student_id'
            AND subject_id = '$subject_id'
            AND date = '$date_today'
        ");

        if (mysqli_num_rows($check) == 0) {
            mysqli_query($conn, "
                INSERT INTO attendance_records
                (teacher_id, student_id, subject_id, date, status)
                VALUES
                ('$teacher_id', '$student_id', '$subject_id', '$date_today', '$status')
            ");
        }
    }

    exit;
}

/*
------------------------------------
STEP 5: Fetch students for the SELECTED subject (bound to teacher_subjects)
------------------------------------
*/

$student_query = false;

if (!empty($teacher_subjects)) {
    $student_query = mysqli_query($conn, "
        SELECT s.id, s.first_name, s.last_name,
               MAX(si.image_path) AS image_path, ss.subject_id
        FROM students s
        INNER JOIN student_subjects ss ON s.id = ss.student_id
        INNER JOIN teacher_subjects ts ON ts.subject_id = ss.subject_id
        LEFT JOIN student_images si ON si.student_id = s.id
        WHERE ts.teacher_id = '$teacher_id'
          AND ss.subject_id = '$selected_subject_id'
        GROUP BY s.id, s.first_name, s.last_name, ss.subject_id
        ORDER BY s.first_name
    ");
}

$total_students = $student_query ? mysqli_num_rows($student_query) : 0;
?>

<link href="https://fonts.googleapis.com/css2?family=Love+Ya+Like+A+Sister&display=swap" rel="stylesheet">

<style>
.students-container{ padding:5px; }

.students-header{
    display:flex; justify-content:space-between; align-items:center;
    margin-bottom:25px; flex-wrap:wrap; gap:15px;
}
.header-left{ display:flex; align-items:center; gap:15px; }
.header-icon{
    width:55px; height:55px; border-radius:16px;
    background:linear-gradient(135deg,#1e3c72,#2a5298);
    display:flex; align-items:center; justify-content:center;
    color:#fff; font-size:24px; box-shadow:0 8px 20px rgba(30,60,114,.25);
}
.header-title{
    margin:0; font-size:24px; font-weight:700; color:#1e3c72; letter-spacing:.2px;
    font-family:"Love Ya Like A Sister", cursive;
}
.header-subtitle{ margin:0; font-size:14px; color:#6b7280; }
.header-right{ display:flex; align-items:center; gap:12px; flex-wrap:wrap; }

.subject-badge{
    background:linear-gradient(135deg,#1e3c72,#2a5298); color:#fff;
    padding:8px 18px; border-radius:40px; font-weight:600; font-size:13px;
    box-shadow:0 5px 15px rgba(0,0,0,.12);
}
.subject-select{
    border:2px solid #e5e9f2; border-radius:40px; padding:9px 18px;
    font-weight:600; font-size:13px; color:#1e3c72; background:#fff;
    outline:none; min-width:220px; cursor:pointer;
}
.btn-view-monthly{
    display:inline-flex; align-items:center; gap:8px;
    background:linear-gradient(135deg,#0dcaf0,#0aa2c0); color:#fff; border:none;
    padding:10px 22px; border-radius:40px; font-weight:600; font-size:14px;
    text-decoration:none; box-shadow:0 8px 20px rgba(13,202,240,.25); transition:.25s ease;
}
.btn-view-monthly:hover{ transform:translateY(-2px); color:#fff; }

.card{
    border:none; border-radius:0; overflow:hidden;
    box-shadow:0 10px 30px rgba(17,24,39,.08); animation:fadeInUp .4s ease;
}
@keyframes fadeInUp{ from{opacity:0;transform:translateY(10px);} to{opacity:1;transform:translateY(0);} }

.students-table{ width:100%; border-collapse:collapse; }
.students-table th{
    background:#2a5298; color:#fff; padding:16px 15px; font-size:13px;
    text-transform:uppercase; letter-spacing:.6px; font-weight:600; text-align:center;
}
.students-table th.text-start{ text-align:left; }
.students-table td{
    padding:14px 15px; border-bottom:1px solid #edf1f7; vertical-align:middle;
    font-size:14.5px; color:#374151; text-align:center;
}
.students-table td.text-start{ text-align:left; }
.students-table tbody tr{ transition:background .2s ease; }
.students-table tbody tr:hover{ background:#f8fbff; }

.student-cell{ display:flex; align-items:center; gap:12px; }
.avatar{
    width:46px; height:46px; border-radius:50%;
    display:flex; align-items:center; justify-content:center; overflow:hidden;
    background:linear-gradient(135deg,#1e3c72,#2a5298); color:#fff; font-weight:700; flex-shrink:0;
}
.student-photo{
    width:46px; height:46px; border-radius:50%; object-fit:cover; object-position:center;
    border:3px solid #fff; box-shadow:0 3px 12px rgba(0,0,0,.18); flex-shrink:0;
}

/* status radio pills */
.status-pill{
    display:inline-flex; align-items:center; justify-content:center;
    width:26px; height:26px; cursor:pointer;
}
.status-pill input{ width:18px; height:18px; cursor:pointer; }
.p-present input{ accent-color:#198754; }
.p-absent  input{ accent-color:#dc3545; }
.p-late    input{ accent-color:#ffc107; }
.p-leave   input{ accent-color:#0dcaf0; }

.btn-save{
    display:inline-flex; align-items:center; gap:8px;
    background:linear-gradient(135deg,#198754,#146c43); color:#fff; border:none;
    padding:10px 26px; border-radius:40px; font-weight:600; font-size:14px;
    box-shadow:0 8px 20px rgba(25,135,84,.25); transition:.25s ease;
}
.btn-save:hover{ transform:translateY(-2px); color:#fff; }
.btn-cancel{
    display:inline-flex; align-items:center; gap:8px;
    background:#eef1f6; color:#475569; border:none;
    padding:10px 26px; border-radius:40px; font-weight:600; font-size:14px;
    text-decoration:none; transition:.25s ease;
}
.btn-cancel:hover{ background:#e2e6ee; color:#475569; }

.empty-state{ padding:70px 20px; text-align:center; }
.empty-state i{ font-size:75px; color:#d8d8d8; }

@media(max-width:768px){
    .students-header{ flex-direction:column; align-items:flex-start; }
    .header-right{ width:100%; justify-content:space-between; }
    .students-table{ min-width:640px; }
}
</style>

<div class="students-container">

<?php if (empty($teacher_subjects)): ?>

    <div class="empty-state">
        <i class="bi bi-clipboard-x"></i>
        <h3 class="mt-4">No Subjects Assigned</h3>
        <p class="text-muted">Please contact admin to get subjects assigned to you.</p>
        <a href="#" class="btn-view-monthly js-nav" data-page="dashboard_home.php">
            <i class="bi bi-arrow-left"></i> Back to Dashboard
        </a>
    </div>

<?php else: ?>

    <!-- HEADER -->
    <div class="students-header">

        <div class="header-left">
            <div class="header-icon"><i class="bi bi-clipboard-check"></i></div>
            <div>
                <h2 class="header-title">Mark Attendance</h2>
                <p class="header-subtitle">
                    <?= date('d M Y', strtotime($date_today)) ?>
                    <?php if ($selected_info): ?>
                        &middot; Grade <?= htmlspecialchars($selected_info['grade']) ?>
                    <?php endif; ?>
                </p>
            </div>
        </div>

        <div class="header-right">
            <select id="subjectSelect" class="subject-select">
                <?php foreach ($teacher_subjects as $ts): ?>
                    <option value="<?= $ts['subject_id'] ?>" <?= ($selected_subject_id == $ts['subject_id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($ts['subject_name']) ?> (Grade <?= htmlspecialchars($ts['grade']) ?>)
                    </option>
                <?php endforeach; ?>
            </select>

            <a href="#" class="btn-view-monthly js-nav" data-page="view_attendance.php">
                <i class="bi bi-calendar-check"></i> View Monthly Attendance
            </a>
        </div>

    </div>

    <?php if ($attendance_already_done): ?>

        <div class="card">
            <div class="card-body p-4 text-center">
                <i class="bi bi-check-circle-fill" style="font-size:60px;color:#198754;"></i>
                <h4 class="mt-3 mb-1" style="color:#1e3c72;">Attendance Already Marked</h4>
                <p class="text-muted mb-4">
                    Today's attendance for this subject / grade is already saved.
                </p>
                <a href="#" class="btn-view-monthly js-nav" data-page="view_attendance.php">
                    <i class="bi bi-calendar-check"></i> View Monthly Attendance
                </a>
            </div>
        </div>

    <?php else: ?>

        <form method="POST" id="attendanceForm">

            <input type="hidden" name="selected_subject_id" value="<?= $selected_subject_id ?>">

            <div class="card">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="students-table">
                            <thead>
                                <tr>
                                    <th width="60">Sr</th>
                                    <th class="text-start">Student</th>
                                    <th>Present</th>
                                    <th>Absent</th>
                                    <th>Late</th>
                                    <th>Leave</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php
                            $i = 1;
                            if ($total_students > 0):
                                while ($student = mysqli_fetch_assoc($student_query)):
                                    $sid  = $student['id'];
                                    $name = trim($student['first_name'] . ' ' . $student['last_name']);
                                    $subj = $student['subject_id'];
                            ?>
                                <tr>
                                    <td><strong><?= $i ?></strong></td>

                                    <td class="text-start">
                                        <div class="student-cell">
                                            <?php if (!empty($student['image_path'])): ?>
                                                <img src="Student_dashboard/<?= htmlspecialchars($student['image_path']) ?>"
                                                     class="student-photo" alt="Student">
                                            <?php else: ?>
                                                <div class="avatar"><?= strtoupper(substr($name, 0, 1)) ?></div>
                                            <?php endif; ?>
                                            <div class="fw-bold"><?= htmlspecialchars($name) ?></div>
                                        </div>
                                    </td>

                                    <td>
                                        <label class="status-pill p-present">
                                            <input type="radio" name="attendance[<?= $sid ?>]" value="Present" required>
                                        </label>
                                    </td>
                                    <td>
                                        <label class="status-pill p-absent">
                                            <input type="radio" name="attendance[<?= $sid ?>]" value="Absent">
                                        </label>
                                    </td>
                                    <td>
                                        <label class="status-pill p-late">
                                            <input type="radio" name="attendance[<?= $sid ?>]" value="Late">
                                        </label>
                                    </td>
                                    <td>
                                        <label class="status-pill p-leave">
                                            <input type="radio" name="attendance[<?= $sid ?>]" value="Leave">
                                        </label>
                                    </td>

                                    <input type="hidden" name="subject[<?= $sid ?>]" value="<?= $subj ?>">
                                </tr>
                            <?php
                                    $i++;
                                endwhile;
                            else:
                            ?>
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted">
                                        No students found for this subject / grade.
                                    </td>
                                </tr>
                            <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <?php if ($total_students > 0): ?>
            <div class="d-flex flex-wrap gap-2 mt-3">
                <button type="submit" class="btn-save">
                    <i class="bi bi-check-circle"></i> Save Attendance
                </button>
                <a href="#" class="btn-cancel js-nav" data-page="dashboard_home.php">
                    Cancel
                </a>
            </div>
            <?php endif; ?>

        </form>

    <?php endif; ?>

<?php endif; ?>

</div>

<script>
(function(){

    function navTo(page){
        $("#content-area").load(page);
        history.pushState({page:page}, "", "?page=" + encodeURIComponent(page));
    }

    // self-contained nav (blink-free, no dependence on parent handler)
    $(".js-nav").off("click.att").on("click.att", function(e){
        e.preventDefault();
        navTo($(this).data("page"));
    });

    // change subject/grade -> reload table for that subject
    const subject = document.getElementById("subjectSelect");
    if (subject) {
        subject.addEventListener("change", function(){
            navTo("attendance.php?subject_id=" + this.value);
        });
    }

    // intercept POST save
    const form = document.getElementById("attendanceForm");
    if (form) {
        form.addEventListener("submit", function(e){
            e.preventDefault();
            const formData  = $(this).serialize();
            const subjectId = document.querySelector('input[name="selected_subject_id"]').value;
            $.post("attendance.php?subject_id=" + subjectId, formData, function(){
                alert("Attendance saved successfully");
                navTo("attendance.php?subject_id=" + subjectId);
            });
        });
    }

})();
</script>