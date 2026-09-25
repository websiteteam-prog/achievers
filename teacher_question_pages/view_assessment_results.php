<?php
session_start();
include '../db_config.php';

if (!isset($_SESSION['teacher_id'])) {
    header('Location: ../teacher_login.php');
    exit();
}

$teacher_id = (int)$_SESSION['teacher_id'];
$assessment_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($assessment_id <= 0) {
    die('<h3 class="text-danger text-center mt-5">Invalid assessment ID.</h3>');
}

// Keep teacher active
$now = date('Y-m-d H:i:s');
$conn->query("UPDATE teachers SET last_activity = '$now' WHERE id = $teacher_id");

// Fetch assessment + stats
$sql = "
SELECT 
a.*,
COUNT(aa.student_id) AS assigned_count,
SUM(CASE WHEN aa.submitted_at IS NOT NULL THEN 1 ELSE 0 END) AS submitted_count,
(SELECT COUNT(*) FROM assessment_questions WHERE assessment_id = a.id) AS total_questions
FROM assessments a
LEFT JOIN assessment_assignments aa ON a.id = aa.assessment_id
WHERE a.id = ? AND a.teacher_id = ?
GROUP BY a.id
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $assessment_id, $teacher_id);
$stmt->execute();
$result = $stmt->get_result();
$assessment = $result->fetch_assoc();
$stmt->close();

if (!$assessment) {
    die('<h3 class="text-danger text-center mt-5">Assessment not found or access denied.</h3>');
}

$completion = $assessment['assigned_count'] > 0
    ? round(($assessment['submitted_count'] / $assessment['assigned_count']) * 100)
    : 0;

// Fetch results
$results_sql = "
SELECT 
aa.student_id,
CONCAT(COALESCE(s.first_name,''),' ',COALESCE(s.last_name,'')) AS student_name,
s.email,
aa.score,
aa.submitted_at,
aa.started_at,
TIMESTAMPDIFF(SECOND, aa.started_at, aa.submitted_at) AS time_taken_seconds
FROM assessment_assignments aa
JOIN students s ON aa.student_id = s.id
WHERE aa.assessment_id = ?
AND aa.submitted_at IS NOT NULL
ORDER BY aa.submitted_at DESC
";

$stmt2 = $conn->prepare($results_sql);
$stmt2->bind_param("i", $assessment_id);
$stmt2->execute();
$results = $stmt2->get_result();
?>

<style>
/* =====================================================
   VIEW ASSESSMENT RESULTS — matched to app navy theme
===================================================== */

@keyframes fadeInUp{
    from{opacity:0;transform:translateY(10px);}
    to{opacity:1;transform:translateY(0);}
}

/* Header banner */
.header-card{
    background:linear-gradient(135deg,#1e3c72,#2a5298);
    color:#fff;
    padding:26px 30px;
    border-radius:18px;
    margin:6px 0 24px;
    box-shadow:0 10px 30px rgba(30,60,114,.25);
    animation:fadeInUp .4s ease;
}
.header-card h2{ font-weight:700; margin:0; }
.opacity-90{ opacity:.9; }

/* Stat cards */
.stat-card{
    background:#fff;
    border-radius:16px;
    padding:22px 16px;
    text-align:center;
    height:100%;
    box-shadow:0 10px 30px rgba(17,24,39,.08);
    animation:fadeInUp .4s ease;
    transition:transform .2s ease, box-shadow .2s ease;
}
.stat-card:hover{
    transform:translateY(-3px);
    box-shadow:0 16px 34px rgba(17,24,39,.12);
}
.stat-number{
    font-size:30px;
    font-weight:800;
    color:#1e3c72;
    margin-top:8px;
    line-height:1;
}

/* Results table card */
.table-modern{
    border:none;
    border-radius:18px;
    overflow:hidden;
    box-shadow:0 10px 30px rgba(17,24,39,.08);
    animation:fadeInUp .4s ease;
}
.table-modern .card-header h4{ color:#1e3c72; font-weight:700; }
.table-modern thead th{
    background:#2a5298;
    color:#fff;
    text-transform:uppercase;
    font-size:12.5px;
    letter-spacing:.5px;
    padding:14px 15px;
    border:none;
    white-space:nowrap;
    vertical-align:middle;
}

.btn-view-answers{
    display:inline-flex;
    align-items:center;
    gap:6px;
    background:linear-gradient(135deg,#1e3c72,#2a5298);
    color:#fff;
    border:none;
    border-radius:30px;
    padding:9px 22px;
    font-size:13px;
    font-weight:600;
    white-space:nowrap;
    box-shadow:0 6px 16px rgba(30,60,114,.25);
    transition:.2s ease;
    text-decoration: none;
}
.btn-view-answers:hover{
    transform:translateY(-2px);
    color:#fff;
    box-shadow:0 10px 22px rgba(30,60,114,.32);
}

.table-modern tbody td{
    padding:14px 15px;
    border-bottom:1px solid #edf1f7;
}
.table-modern tbody tr:hover{ background:#f8fbff; }

/* Score badge */
.badge-score{
    border-radius:30px;
    font-weight:600;
    font-size:13px;
}
.bg-orange{ background:#fd7e14 !important; color:#fff !important; }

/* Back button */
.back-btn{
    display:inline-flex;
    align-items:center;
    background:linear-gradient(135deg,#1e3c72,#2a5298);
    color:#fff;
    border:none;
    border-radius:40px;
    padding:11px 26px;
    font-weight:600;
    text-decoration:none;
    box-shadow:0 8px 20px rgba(30,60,114,.25);
    transition:.25s ease;
}
.back-btn:hover{
    transform:translateY(-2px);
    color:#fff;
    box-shadow:0 12px 26px rgba(30,60,114,.32);
}

@media(max-width:768px){
    .table-responsive table{ min-width:760px; }
    .stat-number{ font-size:24px; }
}
</style>

<div class="container-fluid">

    <!-- Header -->
    <div class="header-card">
        <h2 class="mb-2"><?= htmlspecialchars($assessment['title']) ?></h2>
        <?php if (!empty($assessment['description'])): ?>
            <p class="mb-0 opacity-90">
                <?= nl2br(htmlspecialchars($assessment['description'])) ?>
            </p>
        <?php endif; ?>
    </div>

    <!-- Stats -->
    <div class="row g-4 mb-4">

        <div class="col-md-3 col-6">
            <div class="stat-card">
                <i class="bi bi-people fs-1 text-primary"></i>
                <div class="stat-number"><?= number_format($assessment['assigned_count']) ?></div>
                <div class="text-muted">Assigned</div>
            </div>
        </div>

        <div class="col-md-3 col-6">
            <div class="stat-card">
                <i class="bi bi-check2-square fs-1 text-success"></i>
                <div class="stat-number"><?= number_format($assessment['submitted_count']) ?></div>
                <div class="text-muted">Submitted</div>
            </div>
        </div>

        <div class="col-md-3 col-6">
            <div class="stat-card">
                <i class="bi bi-graph-up fs-1 text-warning"></i>
                <div class="stat-number"><?= $completion ?>%</div>
                <div class="text-muted">Completion</div>
            </div>
        </div>

        <div class="col-md-3 col-6">
            <div class="stat-card">
                <i class="bi bi-question-lg fs-1 text-info"></i>
                <div class="stat-number"><?= $assessment['total_questions'] ?></div>
                <div class="text-muted">Questions</div>
            </div>
        </div>

    </div>

    <!-- Results -->
    <div class="card table-modern">

        <div class="card-header bg-transparent border-0 pt-4 px-4">
            <h4 class="mb-0">
                <i class="bi bi-trophy text-warning"></i>
                Student Results
                <span class="text-muted fs-5">
                    (<?= $assessment['submitted_count'] ?> submission<?= $assessment['submitted_count']!=1?'s':'' ?>)
                </span>
            </h4>
        </div>

        <div class="card-body p-0">

        <?php if ($results->num_rows === 0): ?>

            <div class="text-center py-5">
                <i class="bi bi-inbox fs-1 text-muted opacity-50"></i>
                <p class="mt-3 text-muted fs-5">
                    No submissions yet.<br>
                    <small>Students are still working on it.</small>
                </p>
            </div>

        <?php else: ?>

            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
    <thead>
        <tr>
            <th width="22%">Student Name</th>
            <th width="22%">Email</th>
            <th width="15%" class="text-center">Score</th>
            <th width="12%" class="text-center">Time Taken</th>
            <th width="17%">Submitted On</th>
            <th width="12%" class="text-center">Actions</th>
        </tr>
    </thead>
    <tbody>

    <?php while ($row = $results->fetch_assoc()):

        $student_name = trim($row['student_name']) ?: 'Student (No Name)';

        $max   = (int)$assessment['total_questions'];
        $score = (int)$row['score'];

        $percent = $max>0 ? round(($score/$max)*100) : 0;

        $badge_class =
            $percent >= 80 ? 'bg-success' :
            ($percent >= 60 ? 'bg-warning text-dark' :
            ($percent >= 40 ? 'bg-orange' : 'bg-danger'));

        $time = '—';
        if ($row['time_taken_seconds'] > 0) {
            $mins = floor($row['time_taken_seconds']/60);
            $secs = $row['time_taken_seconds']%60;
            $time = sprintf('%02dm %02ds', $mins, $secs);
        }
    ?>

        <tr>
            <td><strong><?= htmlspecialchars($student_name) ?></strong></td>

            <td>
                <small class="text-muted"><?= htmlspecialchars($row['email']) ?></small>
            </td>

            <td class="text-center">
                <span class="badge <?= $badge_class ?> badge-score px-3 py-2">
                    <?= $score ?> / <?= $max ?>
                    <small>(<?= $percent ?>%)</small>
                </span>
            </td>

            <td class="text-center fw-semibold"><?= $time ?></td>

            <td>
                <small class="text-success">
                    <?= date('d M Y', strtotime($row['submitted_at'])) ?><br>
                    <?= date('h:i A', strtotime($row['submitted_at'])) ?>
                </small>
            </td>

            <td class="text-center">
                <a href="#"
                   class="btn-view-answers menu-link"
                   data-page="teacher_question_pages/view_student_answers.php?assessment_id=<?= $assessment_id ?>&student_id=<?= $row['student_id'] ?>&submitted_at=<?= urlencode($row['submitted_at']) ?>">
                    <i class="bi bi-eye"></i> View Answers
                </a>
            </td>
        </tr>

    <?php endwhile; ?>

    </tbody>
</table>

            </div>

        <?php endif; ?>

        </div>

        <div class="card-footer bg-light text-center py-4">
            <a href="teacher_dashboard.php?page=teacher_question_pages/manage_assessments.php"
               class="back-btn">
                <i class="bi bi-arrow-left-circle me-2"></i>
                Back to Assessments
            </a>
        </div>

    </div>

</div>

<script>
$(function(){
    $('.sidebar a').removeClass('active');
    $('.sidebar a[href*="manage_assessments"]').addClass('active');
});
</script>

<?php
$stmt2->close();
// NOTE: do NOT close $conn here — see explanation
?>