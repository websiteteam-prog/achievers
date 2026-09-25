<?php

session_start();
include 'db_config.php';

if (!isset($_SESSION['teacher_id'])) {
    echo "<div class='alert alert-danger m-4'>Session expired. Please login again.</div>";
    exit;
}

$teacher_id       = (int)$_SESSION['teacher_id'];
$selected_subject = $_GET['subject_id'] ?? '';
$selected_grade   = $_GET['grade'] ?? '';
$search_name      = trim($_GET['search_name'] ?? '');

// default date range: last 30 days
$default_start = date('Y-m-d', strtotime('-30 days'));
$default_end   = date('Y-m-d');

$start_date = $_GET['start_date'] ?? $default_start;
$end_date   = $_GET['end_date'] ?? $default_end;

/*
------------------------------------
Teacher subjects (with grade)
------------------------------------
*/

$subject_query = mysqli_query($conn, "
    SELECT DISTINCT s.id, s.subject_name AS name, s.grade
    FROM subjects s
    INNER JOIN teacher_subjects ts ON s.id = ts.subject_id
    WHERE ts.teacher_id = '$teacher_id'
    ORDER BY s.grade, s.subject_name
");

$subjects_for_options = [];
$grades_for_options   = [];

while ($row = mysqli_fetch_assoc($subject_query)) {
    $subjects_for_options[] = $row;
    if (!in_array($row['grade'], $grades_for_options)) {
        $grades_for_options[] = $row['grade'];
    }
}
sort($grades_for_options);

// All subject IDs CURRENTLY assigned to this teacher (used to scope results)
$teacher_subject_ids = array_map('intval', array_column($subjects_for_options, 'id'));

/*
------------------------------------
If a grade is selected, limit subject dropdown to that grade
------------------------------------
*/

$filtered_subjects_for_options = [];

if ($selected_grade !== '') {

    $filtered_subjects_for_options = array_filter(
        $subjects_for_options,
        function ($s) use ($selected_grade) {
            return $s['grade'] == $selected_grade;
        }
    );

    $valid_ids_for_grade = array_column($filtered_subjects_for_options, 'id');

    if ($selected_subject !== '' && !in_array($selected_subject, $valid_ids_for_grade)) {
        $selected_subject = '';
    }
}

/*
------------------------------------
Build WHERE clause
------------------------------------
*/

$where_clause = "ar.date BETWEEN '$start_date' AND '$end_date' AND ar.teacher_id = '$teacher_id'";

// only records for subjects CURRENTLY assigned to this teacher
if (!empty($teacher_subject_ids)) {
    $where_clause .= " AND ar.subject_id IN (" . implode(',', $teacher_subject_ids) . ")";
} else {
    $where_clause .= " AND 1=0";
}

if ($selected_subject !== '') {
    $selected_subject_int = (int)$selected_subject;
    $where_clause .= " AND ar.subject_id = '$selected_subject_int'";
}
if ($selected_grade !== '') {
    $selected_grade_esc = mysqli_real_escape_string($conn, $selected_grade);
    $where_clause .= " AND sub.grade = '$selected_grade_esc'";
}
if ($search_name !== '') {
    $search_escaped = mysqli_real_escape_string($conn, $search_name);
    $where_clause  .= " AND CONCAT(st.first_name,' ',st.last_name) LIKE '%$search_escaped%'";
}

$order_by = "sub.grade, st.first_name, ar.date";

$attendance_query = mysqli_query($conn, "
    SELECT ar.*, CONCAT(st.first_name,' ',st.last_name) AS student_name,
           sub.subject_name AS subject_name, sub.grade AS grade
    FROM attendance_records ar
    INNER JOIN students st  ON st.id = ar.student_id
    INNER JOIN subjects sub ON sub.id = ar.subject_id
    WHERE $where_clause
    ORDER BY $order_by
");

if (!$attendance_query) {
    die("SQL Error: " . mysqli_error($conn));
}

$total_rows = mysqli_num_rows($attendance_query);
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
.btn-mark{
    display:inline-flex; align-items:center; gap:8px;
    background:linear-gradient(135deg,#e8063c,#c40530); color:#fff; border:none;
    padding:10px 22px; border-radius:40px; font-weight:600; font-size:14px;
    text-decoration:none; box-shadow:0 8px 20px rgba(232,6,60,.25); transition:.25s ease;
}
.btn-mark:hover{ transform:translateY(-2px); color:#fff; }

/* filter bar */
.filter-card{
    5border-radius:16px; padding:18px;
    box-shadow:0 10px 30px rgba(17,24,39,.06); margin-bottom:22px;
}
.filter-card .form-label{ font-weight:600; color:#1e3c72; font-size:13px; }
.filter-card .form-control, .filter-card .form-select{ border-radius:12px; }
.btn-filter{
    background:linear-gradient(135deg,#1e3c72,#2a5298); color:#fff; border:none;
    border-radius:12px; font-weight:600; width:100%;
}
.btn-filter:hover{ color:#fff; opacity:.92; }

.card{
    border:none; border-radius:0; overflow:hidden;
    box-shadow:0 10px 30px rgba(17,24,39,.08); animation:fadeInUp .4s ease;
}
@keyframes fadeInUp{ from{opacity:0;transform:translateY(10px);} to{opacity:1;transform:translateY(0);} }

.students-table{ width:100%; border-collapse:collapse; }
.students-table th{
    background:#2a5298; color:#fff; padding:16px 15px; font-size:13px;
    text-transform:uppercase; letter-spacing:.6px; font-weight:600;
}
.students-table td{
    padding:14px 15px; border-bottom:1px solid #edf1f7; vertical-align:middle;
    font-size:14.5px; color:#374151;
}
.students-table tbody tr{ transition:background .2s ease; }
.students-table tbody tr:hover{ background:#f8fbff; }

.student-cell{ display:flex; align-items:center; gap:12px; }
.avatar{
    width:44px; height:44px; border-radius:50%;
    display:flex; align-items:center; justify-content:center;
    background:linear-gradient(135deg,#1e3c72,#2a5298); color:#fff; font-weight:700; flex-shrink:0;
}
.status-badge{
    display:inline-block; padding:6px 14px; border-radius:30px; font-size:12px; font-weight:600;
}
.s-present{ background:#d1f5e0; color:#0f7a44; }
.s-absent { background:#ffe0e6; color:#c40530; }
.s-late   { background:#fff3cd; color:#8a6d00; }
.s-leave  { background:#d9f3ff; color:#0c7abf; }

.empty-state{ padding:70px 20px; text-align:center; }
.empty-state i{ font-size:75px; color:#d8d8d8; }

@media(max-width:768px){
    .students-header{ flex-direction:column; align-items:flex-start; }
    .header-right{ width:100%; justify-content:space-between; }
    .students-table{ min-width:760px; }
}
</style>

<div class="students-container">

    <!-- HEADER -->
    <div class="students-header">

        <div class="header-left">
            <div class="header-icon"><i class="bi bi-bar-chart-fill"></i></div>
            <div>
                <h2 class="header-title">Attendance Records</h2>
                <p class="header-subtitle">
                    <?= $total_rows ?> record<?= $total_rows == 1 ? '' : 's' ?> found
                </p>
            </div>
        </div>

        <div class="header-right">
            <span class="subject-badge">
                <i class="bi bi-calendar-range me-1"></i>
                <?= date('d M Y', strtotime($start_date)) ?> &ndash; <?= date('d M Y', strtotime($end_date)) ?>
            </span>
            <a href="#" class="btn-mark js-nav" data-page="attendance.php">
                <i class="bi bi-clipboard-check"></i> Mark Attendance
            </a>
        </div>

    </div>

    <!-- FILTERS -->
    <form method="GET" data-page="view_attendance.php" class="filter-card row g-3 align-items-end attendance-filter-form">

        <div class="col-md-2">
            <label class="form-label">From:</label>
            <input type="date" name="start_date" class="form-control" value="<?= htmlspecialchars($start_date) ?>">
        </div>

        <div class="col-md-2">
            <label class="form-label">To:</label>
            <input type="date" name="end_date" class="form-control" value="<?= htmlspecialchars($end_date) ?>">
        </div>

        <div class="col-md-2">
            <label class="form-label">Grade:</label>
            <select name="grade" id="gradeFilter" class="form-select">
                <option value="">All Grades</option>
                <?php foreach ($grades_for_options as $g): ?>
                    <option value="<?= htmlspecialchars($g) ?>" <?= ($selected_grade == $g) ? 'selected' : '' ?>>
                        Grade <?= htmlspecialchars($g) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="col-md-2">
            <label class="form-label">Subject:</label>
            <select name="subject_id" class="form-select">
                <?php if ($selected_grade == ''): ?>
                    <option value="">Select Grade First</option>
                <?php else: ?>
                    <option value="">All Subjects</option>
                    <?php foreach ($filtered_subjects_for_options as $row): ?>
                        <option value="<?= $row['id'] ?>" <?= ($selected_subject == $row['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($row['name']) ?>
                        </option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
        </div>

        <div class="col-md-2">
            <label class="form-label">Search Student:</label>
            <input type="text" name="search_name" class="form-control" placeholder="Student name..." value="<?= htmlspecialchars($search_name) ?>">
        </div>

        <div class="col-md-2">
            <label class="form-label d-none d-md-block">&nbsp;</label>
            <button type="submit" class="btn btn-filter">
                <i class="bi bi-search"></i> View
            </button>
        </div>

    </form>

    <!-- TABLE -->
    <?php if ($total_rows > 0): ?>

        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="students-table">
                        <thead>
                            <tr>
                                <th width="60">SR</th>
                                <th>Student</th>
                                <th>Subject</th>
                                <th>Grade</th>
                                <th>Status</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php $i = 1; while ($row = mysqli_fetch_assoc($attendance_query)): ?>
                            <tr>
                                <td><strong><?= $i++ ?></strong></td>
                                <td>
                                    <div class="student-cell">
                                        <div class="avatar"><?= strtoupper(substr($row['student_name'], 0, 1)) ?></div>
                                        <div class="fw-bold"><?= htmlspecialchars($row['student_name']) ?></div>
                                    </div>
                                </td>
                                <td><?= htmlspecialchars($row['subject_name']) ?></td>
                                <td><?= htmlspecialchars($row['grade']) ?></td>
                                <td>
                                    <?php
                                    $status = $row['status'];
                                    if ($status == 'Present')     echo "<span class='status-badge s-present'>Present</span>";
                                    elseif ($status == 'Absent')  echo "<span class='status-badge s-absent'>Absent</span>";
                                    elseif ($status == 'Late')    echo "<span class='status-badge s-late'>Late</span>";
                                    elseif ($status == 'Leave')   echo "<span class='status-badge s-leave'>Leave</span>";
                                    else echo "<span class='status-badge s-late'>" . htmlspecialchars($status) . "</span>";
                                    ?>
                                </td>
                                <td><?= date('d M Y', strtotime($row['date'])) ?></td>
                            </tr>
                        <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    <?php else: ?>

        <div class="empty-state">
            <i class="bi bi-clipboard-x"></i>
            <h3 class="mt-4">No Attendance Records</h3>
            <p class="text-muted">No records match the selected filters.</p>
        </div>

    <?php endif; ?>

</div>

<script>
(function(){

    function navTo(page){
        $("#content-area").load(page);
        history.pushState({page:page}, "", "?page=" + encodeURIComponent(page));
    }

    // self-contained nav (blink-free)
    $(".js-nav").off("click.va").on("click.va", function(e){
        e.preventDefault();
        navTo($(this).data("page"));
    });

    const form = document.querySelector('.attendance-filter-form');
    if (form && !form.dataset.bound) {

        form.dataset.bound = "1";

        const grade = document.getElementById("gradeFilter");
        if (grade) {
            grade.addEventListener("change", function(){
                form.requestSubmit();
            });
        }

        form.addEventListener('submit', function(e){
            e.preventDefault();
            const params = new URLSearchParams(new FormData(form)).toString();
            $("#content-area").load("view_attendance.php?" + params);
        });
    }

})();
</script>