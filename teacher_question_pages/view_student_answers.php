<?php
session_start();
include '../db_config.php';
ini_set('display_errors', 1);
error_reporting(E_ALL);

if (!isset($_SESSION['teacher_id'])) {
    header('Location: ../teacher_login.php');
    exit();
}

$teacher_id = (int)$_SESSION['teacher_id'];
$assessment_id = (int)($_GET['assessment_id'] ?? 0);
$student_id = (int)($_GET['student_id'] ?? 0);
$submitted_at = $_GET['submitted_at'] ?? '';
$submitted_at = urldecode($submitted_at);
$submitted_at = str_replace('+', ' ', $submitted_at);

if ($assessment_id <= 0 || $student_id <= 0 || empty($submitted_at)) {
    die("Invalid request.");
}

// Teacher owns assessment?
$stmt = $conn->prepare("SELECT title FROM assessments WHERE id = ? AND teacher_id = ?");
$stmt->bind_param("ii", $assessment_id, $teacher_id);
$stmt->execute();
$res = $stmt->get_result();
if ($res->num_rows === 0) die("Access denied.");
$row = $res->fetch_assoc();
$assessment_title = $row['title'] ?? 'Unknown Assessment';
$stmt->close();

// Student name
$stmt = $conn->prepare("
SELECT
    CONCAT(COALESCE(s.first_name,''), ' ', COALESCE(s.last_name,'')) AS name,
    si.image_path
FROM students s
LEFT JOIN student_images si
    ON si.student_id = s.id
WHERE s.id = ?
");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$student_name = $row['name'] ?? 'Unknown Student';

$image_path = $row['image_path'] ?? '';

$student_avatar = "images/default-avatar.png";
$student_avatar_file = "";

if (!empty($image_path)) {
    $student_avatar = "Student_dashboard/" . $image_path;
    $student_avatar_file = dirname(__DIR__) . "/Student_dashboard/" . $image_path;
}

$stmt->close();

// YE HAI ASLI FIX — Sirf student_id + assessment_id + question_id se answers fetch karo
// created_at ka chakkar chhodo — ek student ek assessment mein ek baar hi answer deta hai!
$sql = "
    SELECT
        qq.id AS question_id,
        qq.question_text,
        qq.correct_answer,
        qq.question_type,
        qq.question_payload,
        qq.question_image,
        sa.student_answer
    FROM assessment_questions aq
    JOIN quiz_questions qq ON aq.question_id = qq.id
    LEFT JOIN assessment_student_answers sa
        ON sa.question_id = qq.id
       AND sa.student_id = ?
       AND sa.assessment_id = ?
    WHERE aq.assessment_id = ?
    ORDER BY aq.question_order
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("iii", $student_id, $assessment_id, $assessment_id);
$stmt->execute();
$result = $stmt->get_result();

$questions = [];
$correct = $wrong = $skipped = 0;

while ($row = $result->fetch_assoc()) {
    $student_ans = trim($row['student_answer'] ?? '');
    $correct_ans = trim($row['correct_answer'] ?? '');

    $is_correct = false;
    if ($student_ans !== '') {
        if (strtolower($student_ans) === strtolower($correct_ans)) {
            $is_correct = true;
            $correct++;
        } else {
            $wrong++;
        }
    } else {
        $skipped++;
    }

    $row['student_answer_display'] = $student_ans ?: '(Not Attempted)';
    $row['is_correct'] = $is_correct ? 1 : 0;
    $questions[] = $row;
}

$total = count($questions);
$score = $correct * 10;
$percent = $total > 0 ? round(($correct / $total) * 100) : 0;
?>

  <style>
body { background:transparent; padding:0; }

/* scoped so it won't touch other .card elements in the dashboard */
.answers-page .card{
    max-width:1100px;
    margin:auto;
    border-radius:22px;
    overflow:hidden;
    border:none;
    box-shadow:0 15px 40px rgba(17,24,39,.12);
}

/* ---------- HEADER (navy theme) ---------- */
.header-card{
    background:linear-gradient(135deg,#1e3c72,#2a5298);
    color:#fff;
    padding:32px 40px;
}
.header-card small{ letter-spacing:.4px; opacity:.85; }
.header-card h2{ font-size:1.5rem;  margin:0; }
.header-card h4{ font-size:1.15rem; margin:0; }
.header-card h6{ font-size:1rem;    margin:0; }

.student-avatar{
    width:74px;
    height:74px;
    border-radius:50%;
    object-fit:cover;
    border:4px solid rgba(255,255,255,.35);
    background:rgba(255,255,255,.15);
    color:#fff;
    flex-shrink:0;
}

/* ---------- SCORE CIRCLE ---------- */
.score-circle{
    width:170px;
    height:170px;
    background:linear-gradient(45deg,#1e3c72,#2a5298);
    color:#fff;
    border-radius:50%;
    display:flex;
    flex-direction:column;
    align-items:center;
    justify-content:center;
    font-size:3.6rem;
    font-weight:800;
    margin:16px auto;
    box-shadow:0 12px 30px rgba(30,60,114,.35);
}
.score-circle small{ font-size:1.5rem; font-weight:600; opacity:.9; }

/* ---------- SUMMARY TILES ---------- */
.summary-tile{
    border-radius:14px;
    padding:20px 12px;
    color:#fff;
    font-weight:700;
    font-size:1.05rem;
    box-shadow:0 8px 20px rgba(0,0,0,.10);
}

/* ---------- QUESTION BOX ---------- */
.question-box{
    background:#fff;
    border-radius:18px;
    padding:28px;
    margin:22px 0;
    box-shadow:0 8px 25px rgba(17,24,39,.08);
    border:2px solid #e3e9f5;
    border-left:5px solid #1e3c72;
    position:relative;
}
.badge-status{
    width:54px;
    height:54px;
    font-size:1.6rem;
    display:flex;
    align-items:center;
    justify-content:center;
}

/* ---------- BACK BUTTON ---------- */
.back-btn{
    display:inline-flex;
    align-items:center;
    background:linear-gradient(135deg,#1e3c72,#2a5298);
    color:#fff;
    border:none;
    border-radius:40px;
    padding:12px 30px;
    font-weight:600;
    text-decoration:none;
    box-shadow:0 8px 20px rgba(30,60,114,.25);
    transition:.25s ease;
}
.back-btn:hover{ transform:translateY(-2px); color:#fff; box-shadow:0 12px 26px rgba(30,60,114,.32); }

/* =========================================================
   RESPONSIVE
========================================================= */
@media (max-width:768px){
    .header-card{ padding:24px 18px; text-align:center; }
    .header-card h2{ font-size:1.35rem; }
    .header-card h4{ font-size:1.05rem; }

    .card-body{ padding:1.25rem !important; }

    .score-circle{ width:140px; height:140px; font-size:2.8rem; }
    .score-circle small{ font-size:1.2rem; }

    .question-box{ padding:20px 18px; }
    .question-box .ps-5{ padding-left:1rem !important; }
    .badge-status{ width:46px; height:46px; font-size:1.3rem; }
}

@media (max-width:575.98px){
    .score-circle{ width:120px; height:120px; font-size:2.3rem; }
    .display-4{ font-size:2.2rem; }
    .summary-tile{ font-size:.95rem; padding:16px 10px; }

    /* answer alert: badge on its own line instead of float overlap */
    .question-box .float-end{ float:none !important; display:block; margin-top:8px; }
}
</style>

<!-- <!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($student_name) ?> - Answers</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/mathjax@3/es5/tex-mml-chtml.js"></script>
    <style>
        body { background:#f8f9fa; padding:40px 0; }
        .card { max-width:1100px; margin:auto; border-radius:25px; overflow:hidden; box-shadow:0 15px 40px rgba(0,0,0,0.2); }
        .header { background:linear-gradient(135deg,#667eea,#764ba2); color:white; padding:40px; text-align:center; }
        .score-circle { width:180px; height:180px; background:linear-gradient(45deg,#56ab2f,#a8e6cf); color:white; border-radius:50%; display:flex; flex-direction:column; align-items:center; justify-content:center; font-size:4.5rem; font-weight:bold; margin:20px auto; box-shadow:0 10px 30px rgba(0,0,0,0.3); }
        .question-box { background:white; border-radius:20px; padding:30px; margin:25px 0; box-shadow:0 8px 25px rgba(0,0,0,0.1); border:3px solid #0d6efd; position:relative; }
        .badge-status { width:60px; height:60px; font-size:1.8rem; display:flex; align-items:center; justify-content:center; }
    </style>
</head>
<body> -->
<div class="container answers-page py-3">
    <div class="card">
<div class="header-card">

    <div class="row align-items-center text-center text-md-start">

        <div class="col-md-5 mb-3 mb-md-0">
            <div class="d-flex align-items-center justify-content-center justify-content-md-start">
              <div class="me-3">

               <?php if(!empty($student_avatar_file) && file_exists($student_avatar_file)): ?>

                <img src="<?= htmlspecialchars($student_avatar) ?>"
                    class="student-avatar"
                    alt="Student">

                <?php else: ?>

                <div class="student-avatar d-flex align-items-center justify-content-center">
                    <i class="bi bi-person-fill fs-1"></i>
                </div>

                <?php endif; ?>

                </div>

                <div>
                    <small class="text-white-50 d-block">Student</small>
                    <h2 class="mb-0 fw-bold">
                        <?= htmlspecialchars($student_name) ?>
                    </h2>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-3 mb-md-0">
            <small class="text-white-50 d-block">Assessment</small>
            <h4 class="mb-0 fw-semibold">
                <?= htmlspecialchars($assessment_title) ?>
            </h4>
        </div>
        <div class="col-md-3">

            <small class="text-white-50 d-block">
                Submitted On
            </small>

            <h6 class="mb-0 fw-semibold">
                <?= date('d M Y, h:i A', strtotime($submitted_at)) ?>
            </h6>

        </div>
    </div>

</div>
        <div class="card-body bg-light p-3 p-md-5">
            <div class="text-center mb-5">
                <div class="score-circle"><?= $score ?><small style="font-size:2rem">pts</small></div>
                <h1 class="display-4 <?= $percent >= 60 ? 'text-success' : 'text-danger' ?>"><?= $percent ?>%</h1>
            </div>
            <div class="row text-center mb-5 g-3 row-cols-1 row-cols-sm-3">
            <div class="col"><div class="summary-tile bg-success"><?= $correct ?> Correct</div></div>
            <div class="col"><div class="summary-tile bg-danger"><?= $wrong ?> Wrong</div></div>
            <div class="col"><div class="summary-tile bg-secondary"><?= $skipped ?> Skipped</div></div>
        </div>

            <h3 class="text-center mb-5 text-dark fw-bold">Detailed Answers</h3>

            <?php foreach ($questions as $i => $q):
                $ans = $q['student_answer_display'];
                $is_correct = $q['is_correct'];
                $badge = $ans === '(Not Attempted)' ? 'bg-secondary' : ($is_correct ? 'bg-success' : 'bg-danger');
                $icon = $ans === '(Not Attempted)' ? 'bi-circle' : ($is_correct ? 'bi-check-circle-fill' : 'bi-x-circle-fill');
                
                $index = $i;
                $char = chr(97 + ($i % 26));
                $q['id'] = $q['question_id'];
            ?>
            <div class="question-box">
                <div class="position-absolute top-0 start-0 translate-middle-y ms-3">
                    <span class="badge <?= $badge ?> badge-status rounded-circle shadow">
                        <i class="bi <?= $icon ?>"></i>
                    </span>
                </div>
                <div class="ps-5">
                    <h5 class="fw-bold text-primary">Question <?= $i+1 ?></h5>
                    <div class="border rounded-4 p-4 bg-white mb-4">
                        <?php
                        switch ($q['question_type']) {
                            case 'fill_blank2': include '../Student_dashboard/templates/fill_blank2.php'; break;
                            case 'fill_blank': include '../Student_dashboard/templates/fill_blank.php'; break;
                            case 'compare': include '../Student_dashboard/templates/compare.php'; break;
                            case 'compare2': include '../Student_dashboard/templates/compare2.php'; break;
                            case 'BODMAS': include '../Student_dashboard/templates/bodmas.php'; break;
                            case 'long_division': include '../Student_dashboard/templates/long_division.php'; break;
                            case 'fill_blank_underline': include '../Student_dashboard/templates/fill_blank_underline.php'; break;
                            case 'fill_blank_models': include '../Student_dashboard/templates/fill_blank_models.php'; break;
                            case 'order_arrange': include '../Student_dashboard/templates/order_arrange.php'; break;
                            case 'bodmas_fill_blank': include '../Student_dashboard/templates/bodmas_fill_blank.php'; break;
                            case 'fraction_diagram':
                            case 'fraction_fill_diagram': include '../Student_dashboard/templates/Fraction/fraction_diagram3.1.php'; break;
                            case 'fraction_improper': include '../Student_dashboard/templates/Fraction/fraction_improper.php'; break;
                            case 'fraction_mixed_to_improper': include '../Student_dashboard/templates/Fraction/fraction_mixed_to_improper.php'; break;
                            case 'fraction_mixed_to_improper_fill': include '../Student_dashboard/templates/Fraction/fraction_mixed_to_improper_fill.php'; break;
                            case 'fraction_order_diagram': include '../Student_dashboard/templates/Fraction/fraction_order_diagram.php'; break;
                            case 'equation_missing': include '../Student_dashboard/templates/equation/equation_missing.php'; break;
                            case 'equation_diagram': include '../Student_dashboard/templates/equation/equation_diagram.php'; break;
                            case 'equation_volume': include '../Student_dashboard/templates/equation/equation_volume.php'; break;
                            case 'display_angles': include '../Student_dashboard/templates/Angles/display_angles.php'; break;
                            case 'angles_classification': include '../Student_dashboard/templates/Angles/angles_classification.php'; break;
                            case 'types_angles': include '../Student_dashboard/templates/Angles/types_angles.php'; break;
                            case 'polygons_intro': include '../Student_dashboard/templates/Angles/polygons_intro.php'; break;
                            case 'color_prisms_pyramids': include '../Student_dashboard/templates/PrismsPyramids/color_prisms_pyramids.php'; break;
                            case 'question_renderer':
                            case 'complete_table':
                            case 'match_nets': include '../Student_dashboard/templates/PrismsPyramids/question_renderer.php'; break;
                            case 'money_question_renderer': include '../Student_dashboard/templates/Money/money_question_renderer.php'; break;
                            case 'money_addsub':
                            case 'picture_money_word': include '../Student_dashboard/templates/Money/money_addsub_renderer.php'; break;
                            case 'fullsize_diagram_only': include '../Student_dashboard/templates/fullsize_diagram_only.php'; break;
                            case 'coordinate_points_input': include '../Student_dashboard/templates/Coordinate/coordinate_points_input.php'; break;
                            case 'fill_outcomes': include '../Student_dashboard/templates/Probability/probability_question.php'; break;
                            case 'factor': include '../Student_dashboard/templates/Factor/factor.php'; break;
                            case 'fill_outcomes_with_images': include '../Student_dashboard/templates/Probability/probability_fill_with_images.php'; break;
                            case 'statistics_universal': include '../Student_dashboard/templates/statistics/statistics_universal.php';break;
                            default:
                                echo "<p><em>Question type: " . htmlspecialchars($q['question_type']) . "</em></p>";
                        }
                        ?>
                    </div>

                    <?php if ($ans === '(Not Attempted)'): ?>
                        <div class="alert alert-secondary text-center fs-5 fw-bold">Not Attempted</div>
                    <?php else: ?>
                        <div class="alert <?= $is_correct ? 'alert-success' : 'alert-danger' ?> p-3">
                            <strong>Student's Answer:</strong> <?= htmlspecialchars($q['student_answer']) ?>
                            <span class="badge <?= $is_correct ? 'bg-success' : 'bg-danger' ?> float-end fs-6">
                                <?= $is_correct ? 'Correct' : 'Wrong' ?>
                            </span>
                        </div>
                        <?php if (!$is_correct): ?>
                            <div class="alert alert-info p-3 mt-2">
                                <strong>Correct Answer:</strong> <?= htmlspecialchars($q['correct_answer']) ?>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>

            <div class="text-center mt-5">
                <a href="javascript:window.history.back()" class="back-btn">
    <i class="bi bi-arrow-left-circle me-2"></i> Back to Results
</a>
            </div>
        </div>
    </div>
</div>

<script>
    if (typeof MathJax !== 'undefined') {
        MathJax.typesetPromise();
    }
</script>
<!-- </body>
</html> -->