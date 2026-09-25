<?php
session_start();
include '../db_config.php';

if (!isset($_SESSION['teacher_id']))
{
    header('Location: ../teacher_login.php');
    exit();
}

$teacher_id = $_SESSION['teacher_id'];

$now = date('Y-m-d H:i:s');
mysqli_query($conn,"UPDATE teachers SET last_activity='$now' WHERE id='$teacher_id'");

$is_ajax =
    !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
    strtolower($_SERVER['HTTP_X_REQUESTED_WITH'])==='xmlhttprequest';

$sql="
SELECT
a.id,
a.title,
a.description,
a.time_limit_minutes,
a.is_published,
a.created_at,
a.due_date,

COUNT(aa.student_id) assigned_count,

SUM(
CASE
WHEN aa.submitted_at IS NOT NULL
THEN 1 ELSE 0
END
) submitted_count,

COALESCE(qcount.qcount,0) total_questions

FROM assessments a

LEFT JOIN assessment_assignments aa
ON a.id=aa.assessment_id

LEFT JOIN
(
SELECT assessment_id,COUNT(*) qcount
FROM assessment_questions
GROUP BY assessment_id
) qcount

ON a.id=qcount.assessment_id

WHERE a.teacher_id=?

GROUP BY a.id

ORDER BY a.created_at DESC
";

$stmt=$conn->prepare($sql);
$stmt->bind_param("i",$teacher_id);
$stmt->execute();
$result=$stmt->get_result();

$total_assessments = $result->num_rows;
?>

<!DOCTYPE html>
<html>
<head>

<meta name="viewport" content="width=device-width, initial-scale=1">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">

<style>

/* =====================================================
   MANAGE ASSESSMENTS — matched to "My Students" theme
===================================================== */

body{
    margin:0;
    background:#f4f6fb;
    font-family:Arial, sans-serif;
}

.main-content{
    padding:20px;
}

@media(max-width:768px){
    .main-content{ margin-left:0; padding:12px; }
}

.assessments-container{
    max-width:1100px;
    margin:auto;
    padding:5px;
}

/* ---------- HEADER (same block as My Students) ---------- */
.assessments-header{
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

.btn-create-assessment{
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

.btn-create-assessment:hover{
    transform:translateY(-2px);
    box-shadow:0 12px 26px rgba(232,6,60,.32);
    color:#fff;
}

/* ---------- CARD ---------- */
.assessment-card{
    background:#fff;
    border:none;
    border-radius:16px;
    overflow:hidden;
    box-shadow:0 10px 30px rgba(17,24,39,.08);
    height:100%;
    animation:fadeInUp .4s ease;
    transition:transform .2s ease, box-shadow .2s ease;
}

.assessment-card:hover{
    transform:translateY(-3px);
    box-shadow:0 16px 34px rgba(17,24,39,.12);
}

@keyframes fadeInUp{
    from{opacity:0;transform:translateY(10px);}
    to{opacity:1;transform:translateY(0);}
}

.assessment-header{
    background:linear-gradient(135deg,#1e3c72,#2a5298);
    color:#fff;
    padding:16px 16px;
    font-size:15.5px;
    font-weight:600;
    display:flex;
    align-items:center;
    gap:8px;
}

.assessment-body{
    padding:16px;
}

/* ---------- SOFT PILL BADGES (My Students style) ---------- */
.pill-badge{
    display:inline-flex;
    align-items:center;
    gap:5px;
    padding:6px 13px;
    font-size:12px;
    font-weight:600;
    border-radius:30px;
    margin:0 4px 6px 0;
}

.badge-questions{ background:#e3f0ff; color:#0c63d4; }
.badge-time{      background:#d9f3ff; color:#0c7abf; }
.badge-assigned{  background:#fff4d6; color:#b8860b; }
.badge-status{    background:#dcfce7; color:#15803d; }
.badge-draft{     background:#eef1f7; color:#64748b; }

/* ---------- META + PROGRESS ---------- */
.assessment-meta{
    font-size:13px;
    color:#6b7280;
    margin:10px 0 4px;
    display:flex;
    align-items:center;
    gap:6px;
}

.progress-wrap{
    margin:12px 0 16px;
}

.progress-label{
    display:flex;
    justify-content:space-between;
    font-size:12px;
    font-weight:600;
    color:#374151;
    margin-bottom:5px;
}

.progress{
    height:8px;
    border-radius:30px;
    background:#eef1f7;
}

.progress-bar{
    background:linear-gradient(135deg,#1e3c72,#2a5298);
    border-radius:30px;
}

/* ---------- ACTION BUTTONS ---------- */
.btn-view{
    background:linear-gradient(135deg,#1e3c72,#2a5298);
    color:#fff;
    border:none;
    border-radius:30px;
    padding:8px 16px;
    font-size:13px;
    font-weight:600;
    transition:.2s ease;
}

.btn-view:hover{
    transform:translateY(-1px);
    color:#fff;
    box-shadow:0 8px 18px rgba(30,60,114,.28);
}

.btn-delete{
    background:linear-gradient(135deg,#e8063c,#c40530);
    color:#fff;
    border:none;
    border-radius:30px;
    padding:8px 16px;
    font-size:13px;
    font-weight:600;
    transition:.2s ease;
}

.btn-delete:hover{
    transform:translateY(-1px);
    color:#fff;
    box-shadow:0 8px 18px rgba(232,6,60,.3);
}

/* ---------- EMPTY STATE ---------- */
.empty-state{
    background:#fff;
    padding:70px 20px;
    border-radius:16px;
    text-align:center;
    box-shadow:0 10px 30px rgba(17,24,39,.08);
}

.empty-state i{
    font-size:75px;
    color:#d8d8d8;
}

/* ---------- RESPONSIVE ---------- */
@media(max-width:768px){
    .assessments-header{
        flex-direction:column;
        align-items:flex-start;
    }
    .header-right{
        width:100%;
        justify-content:space-between;
    }
    .header-title{ font-size:22px; }
    .col-md-6,.col-lg-4{ width:100%; }
}

</style>

</head>

<body>

<?php if(!$is_ajax) include 'sidebar.php'; ?>

<div class="main-content">

<div class="assessments-container">

    <!-- HEADER -->
    <div class="assessments-header">

        <div class="header-left">
            <div class="header-icon">
                <i class="bi bi-clipboard2-check-fill"></i>
            </div>
            <div>
                <h2 class="header-title">Manage Assessments</h2>
                <p class="header-subtitle">
                    <?= $total_assessments ?> assessment<?= $total_assessments==1?'':'s' ?> created
                </p>
            </div>
        </div>

        <div class="header-right">
            <span class="subject-badge">
                <i class="bi bi-journal-text me-1"></i>
                Total : <?= $total_assessments ?>
            </span>
            <a href="teacher_dashboard.php?page=teacher_question_pages/assign_assessment.php"
               class="btn-create-assessment">
                <i class="bi bi-plus-circle"></i>
                Create New Assessment
            </a>
        </div>

    </div>

    <!-- CARDS / EMPTY -->
    <?php if($result->num_rows==0): ?>

        <div class="empty-state">
            <i class="bi bi-clipboard2-x"></i>
            <h4 class="mt-4">No Assessments Created Yet</h4>
            <p class="text-muted">
                Click the button above to create your first assessment.
            </p>
        </div>

    <?php else: ?>

        <div class="row g-3">

        <?php while($a=$result->fetch_assoc()):

            $assigned  = (int)$a['assigned_count'];
            $submitted = (int)$a['submitted_count'];

            $completion = $assigned>0
                ? round(($submitted/$assigned)*100)
                : 0;
        ?>

            <div class="col-md-6 col-lg-4">

                <div class="assessment-card">

                    <div class="assessment-header">
                        <i class="bi bi-file-earmark-text"></i>
                        <?= htmlspecialchars($a['title']) ?>
                    </div>

                    <div class="assessment-body">

                        <!-- badges -->
                        <div class="mb-1">
                            <span class="pill-badge badge-questions">
                                <i class="bi bi-question-circle"></i>
                                Questions: <?= $a['total_questions'] ?>
                            </span>
                            <span class="pill-badge badge-time">
                                <i class="bi bi-clock"></i>
                                <?= $a['time_limit_minutes'] ?>m
                            </span>
                            <span class="pill-badge badge-assigned">
                                <i class="bi bi-people"></i>
                                Assigned: <?= $assigned ?>
                            </span>
                            <?php if($a['is_published']): ?>
                                <span class="pill-badge badge-status">
                                    <i class="bi bi-check-circle"></i> Published
                                </span>
                            <?php else: ?>
                                <span class="pill-badge badge-draft">
                                    <i class="bi bi-pencil"></i> Draft
                                </span>
                            <?php endif; ?>
                        </div>

                        <!-- due date -->
                        <?php if(!empty($a['due_date'])): ?>
                            <div class="assessment-meta">
                                <i class="bi bi-calendar3"></i>
                                Due: <?= date('d M Y, h:i A', strtotime($a['due_date'])) ?>
                            </div>
                        <?php endif; ?>

                        <!-- completion progress -->
                        <div class="progress-wrap">
                            <div class="progress-label">
                                <span>Submissions</span>
                                <span><?= $submitted ?>/<?= $assigned ?> (<?= $completion ?>%)</span>
                            </div>
                            <div class="progress">
                                <div class="progress-bar"
                                     role="progressbar"
                                     style="width: <?= $completion ?>%"
                                     aria-valuenow="<?= $completion ?>"
                                     aria-valuemin="0"
                                     aria-valuemax="100"></div>
                            </div>
                        </div>

                        <!-- actions -->
                        <div class="d-flex gap-2">
                            <a href="#"
                               class="btn btn-view flex-fill menu-link"
                               data-page="teacher_question_pages/view_assessment_results.php?id=<?= $a['id'] ?>">
                                <i class="bi bi-eye me-1"></i> View
                            </a>
                            <button
                                onclick="if(confirm('Delete this assessment?')) location.href='teacher_question_pages/delete_assessment.php?id=<?= $a['id'] ?>'"
                                class="btn btn-delete">
                                <i class="bi bi-trash me-1"></i> Delete
                            </button>
                        </div>

                    </div>

                </div>

            </div>

        <?php endwhile; ?>

        </div>

    <?php endif; ?>

</div>

</div>

</body>
</html>