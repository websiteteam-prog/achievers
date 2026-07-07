<?php
session_start();
include "../db_config.php";
include "student_sidebar.php";    

if (!isset($_SESSION['student_id'])) {
    header("Location: student_login.php");
    exit();
}

$student_id = (int)$_SESSION['student_id'];
$ass_id = (int)($_GET['id'] ?? 0);
if ($ass_id <= 0) {
    die("Invalid assessment ID.");
}

// ============= FETCH ASSESSMENT =============
$stmt = $conn->prepare("
    SELECT a.*, ass.started_at, ass.submitted_at, a.allow_retake, a.time_limit_minutes, a.due_date
    FROM assessments a
    JOIN assessment_assignments ass ON a.id = ass.assessment_id
    WHERE a.id = ? AND ass.student_id = ? AND a.is_published = 1
");
$stmt->bind_param("ii", $ass_id, $student_id);
$stmt->execute();
$ass = $stmt->get_result()->fetch_assoc();

if (!$ass) {
    die("Assessment not found or not assigned to you.");
}

// ============= DUE DATE EXPIRED =============
if (!empty($ass['due_date'])) {
    $due = new DateTime($ass['due_date']);
    $now = new DateTime();
    if ($now > $due) {
        if (empty($ass['submitted_at'])) {
            $upd = $conn->prepare("UPDATE assessment_assignments SET submitted_at = NOW() WHERE assessment_id = ? AND student_id = ?");
            $upd->bind_param("ii", $ass_id, $student_id);
            $upd->execute();
        }
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Assessment Expired</title>
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
            <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
            <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" rel="stylesheet">
            <style>
                body { background: linear-gradient(160deg, #1e3a8a, #2563eb); min-height: 100vh; font-family: 'Segoe UI', sans-serif; margin:0; padding:0; display:flex; align-items:center; justify-content:center; margin-left: 21%;}
                .card-expired { max-width: 600px; border-radius: 30px; overflow: hidden; box-shadow: 0 25px 60px rgba(0,0,0,0.4); }
                .header { background: linear-gradient(45deg, #ff6b6b, #ee5a52); color: white; padding: 33px 0px; text-align: center; }
                .header i { font-size: 4.5rem; animation: beat 1.5s infinite; }
                @keyframes beat { 0%,100% {transform:scale(1)} 50% {transform:scale(1.1)} }
                .body { background: white; padding: 24px 30px; text-align: center; }
                .due { font-size: 1.5rem; color: #e74c3c; font-weight: 600; margin: 20px 0; }
                .btn-back { background: linear-gradient(45deg, #667eea, #764ba2); color: white; border: none; padding: 15px 40px; font-size: 1.2rem; border-radius: 50px; text-decoration: none; display: inline-block; margin-top: 20px; transition: 0.3s; }
                .btn-back:hover { transform: translateY(-5px); box-shadow: 0 15px 30px rgba(102,126,234,0.4); color:white; }
            </style>
        </head>
        <body>
            <div class="card-expired">
                <div class="header">
                    <i class="fas fa-clock"></i>
                    <h1 class="mb-0 mt-3">Assessment Expired!</h1>
                </div>
                <div class="body">
                    <h3>Sorry, the deadline has passed</h3>
                    <p class="text-muted">You can no longer attempt this assessment.</p>
                    <div class="due">
                        <i class="fas fa-calendar-alt"></i> Due: <?= date('d M Y', strtotime($ass['due_date'])) ?><br>
                        <i class="fas fa-clock"></i> <?= date('h:i A', strtotime($ass['due_date'])) ?>
                    </div>
                    <p class="text-muted mt-3">Please contact your teacher for any queries.</p>
                    <a href="student_dashboard.php" class="btn-back">
                        <i class="fas fa-home"></i> Back to Dashboard
                    </a>
                </div>
            </div>
        </body>
        </html>
        <?php
        exit();
    }
}

// ============= ALREADY SUBMITTED (NO RETAKE) =============
if (!empty($ass['submitted_at'])) {
    if (!$ass['allow_retake']) {
        ?>
        <!DOCTYPE html>
        <html><head><meta charset="UTF-8"><title>Already Submitted</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" rel="stylesheet">
        <style>
            body { background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%); min-height: 100vh; display:flex; align-items:center; justify-content:center; }
            .card { max-width: 500px; border-radius: 30px; box-shadow: 0 20px 50px rgba(0,0,0,0.2); }
            .header { background: linear-gradient(45deg, #56ab2f, #a8e6cf); padding: 50px; text-align:center; color:white; border-radius: 30px 30px 0 0; }
            .header i { font-size: 4rem; }
        </style>
        </head>
        <body>
        <div class="card">
            <div class="header">
                <i class="fas fa-check-circle"></i>
                <h1>Already Submitted!</h1>
            </div>
            <div class="card-body text-center p-5 bg-white">
                <p>You have already completed this assessment.</p>
                <a href="student_dashboard.php" class="btn btn-success btn-lg px-5">Back to Dashboard</a>
            </div>
        </div>
        </body></html>
        <?php
        exit();
    } else {
        $reset = $conn->prepare("UPDATE assessment_assignments SET started_at = NULL, submitted_at = NULL WHERE assessment_id = ? AND student_id = ?");
        $reset->bind_param("ii", $ass_id, $student_id);
        $reset->execute();
        header("Location: take_assessment.php?id=$ass_id");
        exit();
    }
}

// ============= 100% FIXED TIMER LOGIC (30:00 + RESUME + NO CHEATING) =============
$time_limit_minutes = (int)$ass['time_limit_minutes'];
$remaining_seconds = 0;

if ($time_limit_minutes > 0) {
    $now = new DateTime();

    if (empty($ass['started_at'])) {
        // FIRST TIME — START NOW & GIVE FULL TIME
        $upd = $conn->prepare("UPDATE assessment_assignments SET started_at = NOW() WHERE assessment_id = ? AND student_id = ?");
        $upd->bind_param("ii", $ass_id, $student_id);
        $upd->execute();
        $remaining_seconds = $time_limit_minutes * 60; // ← YE THI MISSING!
    } else {
        // RESUME CASE
        $started = new DateTime($ass['started_at']);
        $end = clone $started;
        $end->modify("+{$time_limit_minutes} minutes");

        if ($now >= $end) {
            $upd = $conn->prepare("UPDATE assessment_assignments SET submitted_at = NOW() WHERE assessment_id = ? AND student_id = ?");
            $upd->bind_param("ii", $ass_id, $student_id);
            $upd->execute();
            ?>
            <!DOCTYPE html>
            <html><head><meta charset="UTF-8"><title>Time Up!</title>
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
            <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
            <style>
                body { background: linear-gradient(135deg, #ff9a9e 0%, #fad0c4 100%); min-height: 100vh; display:flex; align-items:center; justify-content:center; }
                .card { max-width: 500px; border-radius: 30px; box-shadow: 0 20px 50px rgba(0,0,0,0.3); }
                .header { background: linear-gradient(45deg, #ff4757, #ff3742); padding: 50px; text-align:center; color:white; border-radius: 30px 30px 0 0; }
                .header i { font-size: 4rem; }
            </style>
            </head>
            <body>
            <div class="card">
                <div class="header">
                    <i class="fas fa-stopwatch"></i>
                    <h1>Time's Up!</h1>
                </div>
                <div class="card-body text-center p-5 bg-white">
                    <h4>Your assessment has been automatically submitted.</h4>
                    <a href="student_dashboard.php" class="btn btn-primary btn-lg mt-4 px-5">Back to Dashboard</a>
                </div>
            </div>
            </body></html>
            <?php
            exit();
        }

        $remaining_seconds = $end->getTimestamp() - $now->getTimestamp();

        // Extra safety: agar koi cheat kare (future date) → full time do
        if ($remaining_seconds > ($time_limit_minutes * 60 + 60) || $remaining_seconds < 0) {
            $remaining_seconds = $time_limit_minutes * 60;
        }
    }
}

// ============= LOAD QUESTIONS FROM assessment_questions TABLE =============
$all_questions = [];

$qstmt = $conn->prepare("
    SELECT qq.*
    FROM assessment_questions aq
    JOIN quiz_questions qq ON aq.question_id = qq.id
    WHERE aq.assessment_id = ?
    ORDER BY aq.question_order
");
$qstmt->bind_param("i", $ass_id);
$qstmt->execute();
$result = $qstmt->get_result();

while ($row = $result->fetch_assoc()) {
    $all_questions[] = $row;
}
$qstmt->close();

if (empty($all_questions)) {
    die("No questions found in this assessment.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($ass['title']) ?> - Take Assessment</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" rel="stylesheet">
    <!-- Google Font -->
<link href="https://fonts.googleapis.com/css2?family=Love+Ya+Like+A+Sister&display=swap" rel="stylesheet">
  
    <script src="https://cdn.jsdelivr.net/npm/mathjax@3/es5/tex-mml-chtml.js"></script>
    <style>
        html, body{
            overflow-x:hidden;
            margin:0;
            }
        body { background:#f8f9fa; font-family:'Segoe UI',sans-serif; }
        .card { max-width:1200px; margin:auto; border-radius:30px; overflow:hidden; box-shadow:0 20px 60px rgba(0,0,0,0.3); }
        .header-gradient { background: linear-gradient(45deg,#4e54c8,#8f94fb); }
        .timer { font-size:1.5rem; font-weight:bold; background:rgba(255,255,255,0.25); padding:12px 28px; border-radius:50px; }
        .question-badge { width:65px; height:65px; font-size:1.8rem; display:flex; align-items:center; justify-content:center; background:#667eea; color:white; }
        .template-output { background:white; padding:30px; border-radius:20px; box-shadow:0 8px 25px rgba(0,0,0,0.1); margin:20px 0; border: 3px solid #667eea; }
        .main-content{
        margin-left:260px; /* sidebar width */
        padding:40px;
        max-width:calc(100% - 260px);
        }

        .main-content h1 {
        font-size: 42px;
        font-weight: 400;
        margin-bottom: 6px !important;
        background: linear-gradient(to right, #e02121, #2f55a4);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        font-family: "Love Ya Like A Sister", cursive;
        margin-left: 8px;
        }
       
        @media (max-width:768px){
        .main-content{
        margin-left:0;
        }
        }
    </style>
</head>
<body>
<div class="container main-content">
    <div class="card mt-4">
        <div class="card-header text-center text-white py-5 header-gradient">
            <h1><?= htmlspecialchars($ass['title']) ?></h1>
            <h4>Take Assessment</h4>
            <small class="d-block mt-2 text-light opacity-75">
                Time Limit: <strong><?= $time_limit_minutes ?> minutes</strong>
                <?= $ass['due_date'] ? ' | Due: ' . date('d M Y, h:i A', strtotime($ass['due_date'])) : '' ?>
            </small>
            <?php if ($time_limit_minutes > 0): ?>
                <div class="timer mt-3" id="timer">Calculating...</div>
            <?php endif; ?>
        </div>

        <div class="card-body bg-light p-5">
            <form method="POST" action="submit_assessment.php" id="assessmentForm">
                <input type="hidden" name="assessment_id" value="<?= $ass_id ?>">

                <div class="text-center mb-5">
                    <strong class="fs-4">Jump to Question:</strong><br>
                    <div class="d-flex flex-wrap justify-content-center gap-2 mt-3">
                        <?php foreach ($all_questions as $i => $q): ?>
                            <a href="#q<?= $i+1 ?>" class="btn btn-outline-primary btn-sm rounded-pill px-3"><?= $i+1 ?></a>
                        <?php endforeach; ?>
                    </div>
                </div>
                <hr class="my-5">

                <?php foreach ($all_questions as $i => $q):
                    $num = $i + 1;
                    $index = $i; 
                    $payload = json_decode($q['question_payload'], true) ?: [];
                    $type = $q['question_type'];
                ?>
                <div id="q<?= $num ?>" class="bg-white rounded-4 shadow-lg p-5 mb-5 border position-relative">
                    <div class="position-absolute top-0 start-0 translate-middle-y ms-3">
                        <span class="badge question-badge shadow"><?= $num ?></span>
                    </div>
                    <div class="ps-5">
                        <h5 class="mb-4 text-primary fw-bold">Question <?= $num ?>:</h5>
                        <div class="template-output">
                            <?php
                         switch ($type) {

                      case 'fill_blank2':          include 'templates/fill_blank2.php'; break;
                            case 'fill_blank':           include 'templates/fill_blank.php'; break;
                            case 'compare':              include 'templates/compare.php'; break;
                            case 'compare2':             include 'templates/compare2.php'; break;
                            case 'BODMAS':               include 'templates/bodmas.php'; break;
                            case 'long_division':        include 'templates/long_division.php'; break;
                            case 'fill_blank_underline': include 'templates/fill_blank_underline.php'; break;
                            case 'fill_blank_models':    include 'templates/fill_blank_models.php'; break;
                            case 'order_arrange':        include 'templates/order_arrange.php'; break;
                            case 'bodmas_fill_blank':    include 'templates/bodmas_fill_blank.php'; break;
                            case 'fraction_diagram':     include 'templates/Fraction/fraction_diagram3.1.php'; break;
                            case 'fraction_fill_diagram': include 'templates/Fraction/fraction_diagram3.2.php'; break;
                            case 'fraction_improper':    include 'templates/Fraction/fraction_improper.php'; break;
                            case 'fraction_mixed_to_improper':     include 'templates/Fraction/fraction_mixed_to_improper.php'; break;
                            case 'fraction_mixed_to_improper_fill':include 'templates/Fraction/fraction_mixed_to_improper_fill.php'; break;
                            case 'fraction_order_diagram': include 'templates/Fraction/fraction_order_diagram.php'; break;
                            case 'BODMAS_fraction': include 'templates/Fraction/BODMAS_fraction.php'; break; 
                            case 'fraction_numberline_multi_fill_compare':
                                include 'templates/Fraction/fraction_numberline_multi_fill_compare.php';
                                break;
                            case 'fraction_order_list':
                                include 'templates/Fraction/fraction_order_list.php';
                                break;
                            case 'fraction_compare':
                                include 'templates/Fraction/fraction_compare.php';
                                break;
                                
                            case 'add_and_sub_fractions':
                                include 'templates/Fraction/add_and_sub_fractions.php';
                                break;  
                            case 'equation_missing':     include 'templates/equation/equation_missing.php'; break;
                            case 'equation_diagram':     include 'templates/equation/equation_diagram.php'; break;
                            case 'equation_volume':      include 'templates/equation/equation_volume.php'; break;
                            case 'equation_star':      include 'templates/equation/equation_star.php'; break;
                            case 'display_angles':       include 'templates/Angles/display_angles.php'; break;
                            case 'angles_classification':include 'templates/Angles/angles_classification.php'; break;
                            case 'types_angles':         include 'templates/Angles/types_angles.php'; break;
                            case 'polygons_intro':       include 'templates/Angles/polygons_intro.php'; break;
                            case 'draw_angle_protractor_single': include 'templates/Angles/draw_angle_protractor_single.php'; break;
                            case 'draw_angle_protractor_range': include 'templates/Angles/draw_angle_protractor_range.php'; break;
                            case 'color_prisms_pyramids': include 'templates/PrismsPyramids/color_prisms_pyramids.php'; break;
                            case 'question_renderer':     include 'templates/PrismsPyramids/question_renderer.php'; break;
                            case 'complete_table':       include 'templates/PrismsPyramids/question_renderer.php'; break;
                            case 'match_nets':           include 'templates/PrismsPyramids/question_renderer.php'; break;
                            case 'money_question_renderer': include 'templates/Money/money_question_renderer.php'; break;
                            case 'money_addsub':         include 'templates/Money/money_addsub_renderer.php'; break;
                            case 'picture_money_word':   include 'templates/Money/money_addsub_renderer.php'; break;
                            case 'fullsize_diagram_only':include 'templates/fullsize_diagram_only.php'; break;
                            case 'coordinate_points_input': include 'templates/Coordinate/coordinate_points_input.php'; break;
                            case 'fill_outcomes':        include 'templates/Probability/probability_question.php'; break;
                            case 'number_pattern_complete' :
                            case 'pattern_rule_mcq' :
                            case 'pattern_extend_rule' :
                            case 'pattern_match_rule' :   include 'templates/Probability/number_pattern_complete.php'; break;
                            case 'problem_solving':
                        include 'templates/problem_solving.php';
                      break;
                            case 'factor':               include 'templates/Factor/factor.php'; break;
                            case 'fill_outcomes_with_images': include 'templates/Probability/probability_fill_with_images.php'; break;

                        default:
                            echo '<div class="p-4 text-muted fst-italic">
                                Question type: '.htmlspecialchars($type).'
                                </div>';
                    }
                            ?>
                        </div>
                        <?php if (!empty($q['question_image']) && $type !== 'fullsize_diagram_only'): ?>
                            <div class="text-center my-4">
                                <img src="../uploads/questions/<?= htmlspecialchars($q['question_image']) ?>" class="img-fluid rounded shadow" style="max-height:350px;">
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>

                <div class="text-center mt-5">
                    <button type="submit" class="btn btn-success btn-lg px-5 py-4 shadow">
                        Submit Assessment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php if ($time_limit_minutes > 0): ?>
<script>
let timeLeft = <?= $remaining_seconds ?>;

const timerEl = document.getElementById('timer');
const form = document.getElementById('assessmentForm');

function tick() {
    if (timeLeft <= 0) {
        timerEl.innerHTML = "<span class='text-danger fw-bold'>TIME OVER!</span>";
        alert("Time is up! Submitting your assessment...");
        form.submit();
        return;
    }
    const m = Math.floor(timeLeft / 60);
    const s = timeLeft % 60;
    timerEl.innerHTML = `Time left: <strong>${m}:${s < 10 ? '0' : ''}${s}</strong>`;
    timeLeft--;
    setTimeout(tick, 1000);
}
tick();

window.addEventListener('pageshow', e => {
    if (e.persisted || performance.getEntriesByType?.('navigation')[0]?.type === 'back_forward') {
        location.reload();
    }
});
</script>
<?php endif; ?>

<script>
document.addEventListener("DOMContentLoaded", () => {
    if (typeof MathJax !== 'undefined') MathJax.typesetPromise();
});
</script>
</body>
</html>