<?php
session_start();
include "../db_config.php"; 

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
            <link href="https://fonts.googleapis.com/css2?family=Love+Ya+Like+A+Sister&display=swap" rel="stylesheet">
            <style>
                :root{
                    --primary:#1e40af;
                    --primary-light:#3b82f6;
                    --primary-dark:#1e3a8a;
                    --accent:#ef4444;
                    --light-bg:#f5f7fb;
                    --gray:#6b7280;
                    --shadow:0 6px 20px rgba(0,0,0,.06);
                }
                * { box-sizing:border-box; }
                body { background: var(--light-bg); min-height:100vh; font-family: system-ui,-apple-system,sans-serif; margin:0; padding:0 24px; display:flex; align-items:center; justify-content:center; }
                .card-expired { max-width: 600px; width:100%; border-radius: 22px; overflow: hidden; box-shadow: 0 8px 30px rgba(0,0,0,.08); background:#fff; }
                .header { background: linear-gradient(135deg,#1e3c72,#2a5298); color: white; padding: 40px 20px; text-align: center; }
                .header i { font-size: 4rem; }
                .body { background: white; padding: 30px; text-align: center; }
                .due { font-size: 1.3rem; color: var(--accent); font-weight: 700; margin: 20px 0; }
                .btn-back { background: linear-gradient(135deg,#1e3c72,#2a5298); color: white; border: none; padding: 13px 34px; font-size: 1rem; font-weight:600; border-radius: 50px; text-decoration: none; display: inline-block; margin-top: 18px; transition: .25s; }
                .btn-back:hover { transform: translateY(-3px); box-shadow: 0 10px 24px rgba(30,60,114,.32); color:white; }
            </style>
        </head>
        <body>
            <div class="card-expired">
                <div class="header">
                    <i class="bi bi-clock-history"></i>
                    <h1 class="mb-0 mt-3">Assessment Expired!</h1>
                </div>
                <div class="body">
                    <h4>Sorry, the deadline has passed</h4>
                    <p class="text-muted">You can no longer attempt this assessment.</p>
                    <div class="due">
                        <i class="bi bi-calendar3"></i> Due: <?= date('d M Y', strtotime($ass['due_date'])) ?><br>
                        <i class="bi bi-clock"></i> <?= date('h:i A', strtotime($ass['due_date'])) ?>
                    </div>
                    <p class="text-muted mt-3">Please contact your teacher for any queries.</p>
                    <a href="student_dashboard.php" class="btn-back">
                        <i class="bi bi-house-door-fill me-1"></i> Back to Dashboard
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
        <link href="https://fonts.googleapis.com/css2?family=Love+Ya+Like+A+Sister&display=swap" rel="stylesheet">
        <style>
            :root{ --light-bg:#f5f7fb; }
            * { box-sizing:border-box; }
            body { background: var(--light-bg); min-height:100vh; font-family: system-ui,-apple-system,sans-serif; margin:0; padding:0 24px; display:flex; align-items:center; justify-content:center; }
            .card { max-width: 500px; width:100%; border-radius: 22px; box-shadow: 0 8px 30px rgba(0,0,0,.08); overflow:hidden; }
            .header { background: linear-gradient(135deg,#1e3c72,#2a5298); padding: 45px 20px; text-align:center; color:white; }
            .header i { font-size: 3.6rem; }
            .btn-success { background: linear-gradient(135deg,#1e3c72,#2a5298); border:none; border-radius:50px; font-weight:600; }
        </style>
        </head>
        <body>
            <?php include "student_sidebar.php"; ?>
        <div class="card">
            <div class="header">
                <i class="bi bi-check-circle-fill"></i>
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
        header("Location: ./take_assessment.php?id=$ass_id");
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
            <link href="https://fonts.googleapis.com/css2?family=Love+Ya+Like+A+Sister&display=swap" rel="stylesheet">
            <style>
                :root{ --light-bg:#f5f7fb; --accent:#ef4444; }
                * { box-sizing:border-box; }
                body { background: var(--light-bg); min-height:100vh; font-family: system-ui,-apple-system,sans-serif; margin:0; padding:0 24px; display:flex; align-items:center; justify-content:center; }
                .card { max-width: 500px; width:100%; border-radius: 22px; box-shadow: 0 8px 30px rgba(0,0,0,.08); overflow:hidden; }
                .header { background: linear-gradient(135deg,var(--accent),#c0392b); padding: 45px 20px; text-align:center; color:white; }
                .header i { font-size: 3.6rem; }
                .btn-primary { background: linear-gradient(135deg,#1e3c72,#2a5298); border:none; border-radius:50px; font-weight:600; }
            </style>
            </head>
            <body>
            <div class="card">
                <div class="header">
                    <i class="bi bi-stopwatch-fill"></i>
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
    <link href="https://fonts.googleapis.com/css2?family=Love+Ya+Like+A+Sister&display=swap" rel="stylesheet">

    <script src="https://cdn.jsdelivr.net/npm/mathjax@3/es5/tex-mml-chtml.js"></script>
    <style>
        :root {
            --primary: #1e40af;
            --primary-light: #3b82f6;
            --primary-dark: #1e3a8a;
            --accent: #ef4444;
            --light-bg: #f5f7fb;
            --card-bg: #ffffff;
            --text: #1f2937;
            --gray: #6b7280;
            --shadow: 0 6px 20px rgba(0, 0, 0, 0.06);
            --shadow-hover: 0 12px 32px rgba(0, 0, 0, 0.1);
        }

        * { box-sizing: border-box; }

        html, body {
            max-width: 100%;
            overflow-x: hidden;
        }

        body {
            background: var(--light-bg);
            color: var(--text);
            font-family: system-ui, -apple-system, sans-serif;
        }

        .main-content {
            margin-left: 270px;
            padding: 28px 40px 40px;
            width: auto;
            min-height: 100vh;
        }

        .card {
            max-width: 1200px;
            margin: auto;
            border-radius: 22px;
            overflow: hidden;
            border: none;
            box-shadow: 0 8px 30px rgba(0, 0, 0, .08);
            background: var(--card-bg);
        }

        .header-gradient {
            background: linear-gradient(135deg, #1e3c72, #2a5298);
        }

        .header-gradient h1 {
            font-family: "Love Ya Like A Sister", cursive;
            font-size: 42px;
            font-weight: 400;
            margin-bottom: 6px;
        }

        .timer {
            font-size: 1.4rem;
            font-weight: 700;
            background: rgba(255, 255, 255, .2);
            padding: 10px 26px;
            border-radius: 50px;
            display: inline-block;
        }

        .question-badge {
            width: 60px;
            height: 60px;
            font-size: 1.6rem;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #1e3c72, #2a5298);
            color: white;
            border-radius: 50%;
            box-shadow: var(--shadow);
        }

        .template-output {
            background: white;
            padding: 28px;
            border-radius: 16px;
            box-shadow: var(--shadow);
            margin: 20px 0;
            border: 1px solid #e5e7eb;
        }

        .question-card {
            background: #fff;
            border-radius: 16px;
            padding: 40px;
            margin-bottom: 30px;
            box-shadow: var(--shadow);
            border: none;
            position: relative;
            transition: .25s;
        }

        .question-card:hover {
            box-shadow: var(--shadow-hover);
        }

        .section-title {
            font-size: 1.2rem;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 1rem;
            padding-left: 0.6rem;
            border-left: 4px solid var(--accent);
        }

        .jump-nav {
            background: #fff;
            border-radius: 16px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: var(--shadow);
        }

        .jump-nav .btn-outline-primary {
            border-color: var(--primary-light);
            color: var(--primary);
            font-weight: 600;
        }

        .jump-nav .btn-outline-primary:hover {
            background: linear-gradient(135deg, #1e3c72, #2a5298);
            border-color: transparent;
            color: #fff;
        }

        .btn-submit-assessment {
            background: linear-gradient(135deg, #1e3c72, #2a5298);
            color: #fff;
            border: none;
            padding: 16px 50px;
            font-size: 1.1rem;
            font-weight: 700;
            border-radius: 50px;
            box-shadow: 0 8px 20px rgba(30, 60, 114, .25);
            transition: .25s;
        }

        .btn-submit-assessment:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 26px rgba(30, 60, 114, .35);
            color: #fff;
        }

        @media (max-width: 992px) {
            .main-content {
                margin-left: 0;
                padding: 80px 24px 28px;
            }
        }

        @media (max-width: 768px) {
            .header-gradient h1 {
                font-size: 30px;
            }

            .question-card {
                padding: 24px;
            }

            .template-output {
                padding: 18px;
            }
        }

        @media (max-width: 576px) {
            .main-content {
                padding: 78px 16px 24px;
            }

            .question-badge {
                width: 48px;
                height: 48px;
                font-size: 1.2rem;
            }

            .btn-submit-assessment {
                width: 100%;
                padding: 14px 20px;
            }
        }
    </style>
</head> 
<body>
<div class="container-fluid p-0">
<div class="d-flex">
<?php include "student_sidebar.php"; ?>
<div class="main-content flex-grow-1">
    <div class="card mt-4">
        <div class="card-header text-center text-white py-5 header-gradient">
            <h1><?= htmlspecialchars($ass['title']) ?></h1>
            <h5 class="mb-0 opacity-75">Take Assessment</h5>
            <small class="d-block mt-2 text-light opacity-75">
                Time Limit: <strong><?= $time_limit_minutes ?> minutes</strong>
                <?= $ass['due_date'] ? ' | Due: ' . date('d M Y, h:i A', strtotime($ass['due_date'])) : '' ?>
            </small>
            <?php if ($time_limit_minutes > 0): ?>
                <div class="timer mt-3" id="timer">Calculating...</div>
            <?php endif; ?>
        </div>

        <div class="card-body p-4 p-md-5">
            <form method="POST" action="submit_assessment.php" id="assessmentForm">
                <input type="hidden" name="assessment_id" value="<?= $ass_id ?>">

                <div class="jump-nav text-center">
                    <h6 class="section-title d-inline-block mb-3">Jump to Question</h6>
                    <div class="d-flex flex-wrap justify-content-center gap-2">
                        <?php foreach ($all_questions as $i => $q): ?>
                            <a href="#q<?= $i+1 ?>" class="btn btn-outline-primary btn-sm rounded-pill px-3"><?= $i+1 ?></a>
                        <?php endforeach; ?>
                    </div>
                </div>

                <?php foreach ($all_questions as $i => $q):
                    $num = $i + 1;
                    $index = $i; 
                    $payload = json_decode($q['question_payload'], true) ?: [];
                    $type = $q['question_type'];
                ?>
                <div id="q<?= $num ?>" class="question-card">
                    <div class="d-flex align-items-center gap-3 mb-4">
                        <span class="question-badge"><?= $num ?></span>
                        <h5 class="mb-0 text-primary fw-bold">Question <?= $num ?></h5>
                    </div>
                    <div>
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
                            case 'verify_triangle_angles':
                                include 'templates/Angles/verify_triangle_angles.php';
                                break; 
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
                            case 'primary_secondary':
                            include 'templates/DataHandling/primary_secondary.php'; 
                            break;
                            case 'histogram_table':
                            include 'templates/DataHandling/histogram_table.php';
                            break;
                            case 'pie_chart_table':
                            include 'templates/DataHandling/pie_chart_table.php';
                            break;
                            case 'statistics_question_mcq':
                            include 'templates/statistics/statical-que.php';
                            break;
                            case 'statistics_data_single':
                            include 'templates/statistics/statistics_data_single.php';
                            break;
                            case 'statistics_universal':
                            include 'templates/statistics/statistics_universal.php';
                            break;
                            case 'statistics_central_tendency':
                            include 'templates/statistics/statistics_central_tendency.php';
                            break;
                            case 'surface_area_rectangular_solid':
                            include 'templates/volumn&surface/surface_area_rectangular_solid.php';
                            break;
                            case 'square_complete':
                            case 'square_missing_digit':
                            case 'square_match':
                            case 'perfect_square_root':
                            include 'templates/square/square_numbers.php';
                            break;
                            case 'math_expression':
                            include 'templates/square/math_expression.php';
                            break;
                            case 'number_line_square_root':
                            include 'templates/square/number_line_square_root.php';
                            break;
                            case 'square_side_length':
                            include 'templates/square/square_side_length.php';
                            break;
                            case 'identify_lines':
                            include 'templates/lineAngles/identifylines.php';
                            break;
                            case 'angle_bisector_check':
                            include 'templates/lineAngles/angle_bisector_check.php';  
                            break; 
                            case 'draw_perpendicular_bisector_midpoint':
                            include 'templates/lineAngles/draw_perpendicular_bisector_midpoint.php';
                            break;
                            case 'draw_angle_bisector_canvas':
                            include 'templates/lineAngles/draw_angle_bisector_canvas.php';
                            break;
                            case 'geometry_multi_blank':
                            include 'templates/lineAngles/geometry_multi_blank.php';
                            break;
                            case 'geometry_congruence_rule':
                            include 'templates/TrianglesCongruence/geometry_congruence_rule.php';
                            break;
                            case 'geometry_congruence_prove':
                            include 'templates/TrianglesCongruence/geometry_congruence_prove.php';  
                            break;
                            case 'rectangle_perimeter':
                            include 'templates/AreaPerimeter/rectangle_perimeter.php';
                            break;
                            case 'algebra_expression':
                            include 'templates/Algebra/algebra_expression.php';
                            break;
                            case 'expression_equation_table':
                            include 'templates/Algebra/expression_equation_table.php';  
                            break;
                            case 'exponent_universal':
                            include 'templates/Algebra/exponent_universal.php'; 
                            break;
                            case 'algebra_universal':
                            include 'templates/Algebra/algebra_universal.php';  
                            break;
                            case 'compare_powers':
                            include 'templates/Algebra/compare_powers.php';
                            break;
                            case 'integer_order_list':
                            include 'templates/Integer/integer_order_list.php';
                            break;
                            case 'coordinate_points_input_negative':
                            include 'templates/Integer/coordinate_points_input_negative.php';
                            break;
                            case 'integer_number_line':
                            include 'templates/Integer/integer_number_line.php';
                            break;
                            case 'dynamic_fill_table':
                            include 'templates/exponent/dynamic_fill_table.php';
                            break;   
                        case 'decimal_percent_steps':
                        include 'templates/percents/decimal_percent_steps.php';
                        break;                         
                        case 'fraction_to_percent':
                        include 'templates/percents/fraction_to_percent.php';
                        break;
                        case 'find_whole_percent':
                        include 'templates/percents/find_whole_percent.php';
                        break;     
                        case 'percent_diagram':
                        include 'templates/percents/percent_diagram.php';
                        break;     
                        case 'percent_of_number':
                        include 'templates/percents/percent_of_number.php';
                        break;  
                        case 'percent_to_decimal_table':
                        include 'templates/percents/percent_to_decimal_table.php';
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
                    <button type="submit" class="btn-submit-assessment">
                        <i class="bi bi-send-check-fill me-2"></i>Submit Assessment
                    </button>
                </div>
            </form>
        </div>
    </div>
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
        timerEl.innerHTML = "<span class='text-warning fw-bold'>TIME OVER!</span>";
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener("DOMContentLoaded", () => {
    if (typeof MathJax !== 'undefined') MathJax.typesetPromise();
});
</script>
</body>
</html>