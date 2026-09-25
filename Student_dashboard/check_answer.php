<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();
include "../db_config.php";

if (!isset($_SESSION['student_id'])) {
    header("Location: student_dashboard.php");
    exit();
}

$student_id = $_SESSION['student_id'];
$topic_id   = (int)($_GET['topic_id'] ?? 0);
$earned_points = 0;
$max_points    = 0;
if ($topic_id <= 0) die("Invalid topic ID");

// === Get Topic Title ===
$topic_title = "Quiz";
$stmt = $conn->prepare("SELECT t.title FROM topics t WHERE t.id = ?");
$stmt->bind_param("i", $topic_id);
$stmt->execute();
$res = $stmt->get_result();
if ($row = $res->fetch_assoc()) {
    $topic_title = htmlspecialchars($row['title'] ?: "Quiz");
}
$stmt->close();

// === Get Latest Attempt Timestamp ===
$stmt = $conn->prepare("SELECT MAX(created_at) AS latest_time FROM student_answers WHERE student_id = ? AND quiz_id = ?");
$stmt->bind_param("ii", $student_id, $topic_id);
$stmt->execute();
$res = $stmt->get_result();
$latest_time = $res->fetch_assoc()['latest_time'] ?? null;
if (!$latest_time) die("No quiz attempt found.");

// === Fetch Questions ===
$sql = "SELECT qq.id, qq.question_text, qq.correct_answer, qq.question_payload, 
               qq.question_type, qq.question_image, sa.student_answer, sa.is_correct, sa.correct_count, sa.total_count
        FROM quiz_questions qq
        LEFT JOIN student_answers sa 
          ON qq.id = sa.question_id 
         AND sa.student_id = ? 
         AND sa.quiz_id = ? 
         AND sa.created_at = ?
        WHERE qq.instruction_id IN (SELECT i.id FROM instructions i WHERE i.topic_id = ?)
        ORDER BY qq.id";

$stmt = $conn->prepare($sql);
$stmt->bind_param("iisi", $student_id, $topic_id, $latest_time, $topic_id);
$stmt->execute();
$result = $stmt->get_result();

$questions = [];
$correct = $wrong = $skipped = $pending_review = 0;

while ($row = $result->fetch_assoc()) {

    $payload = json_decode($row['question_payload'] ?? '{}', true);
    $isInstruction = !empty($payload['instruction']);

    if ($isInstruction) {
        continue;
    }

    $questions[] = $row;
    $ans = trim($row['student_answer'] ?? '');

    // ---- MARKS (partial scoring) ----
    $tc = $row['total_count'];
    $cc = $row['correct_count'];
    if ($tc === null) {
        $tc = 1;
        if ($row['question_type'] === 'coordinate_points_input') {
            $exp = json_decode($row['correct_answer'] ?? '', true);
            if (is_array($exp) && $exp && array_keys($exp) !== range(0, count($exp) - 1)) {
                $tc = count($exp);
            }
        }
        $cc = ($row['is_correct'] == 1) ? $tc : 0;
    }
    $tc = (int)$tc;
    $cc = (int)$cc;

    $max_points    += $tc;
    $earned_points += $cc;

    if ($ans === '') {
        $skipped += $tc;                  
    } elseif ($row['question_type'] === 'perimeter_word_problem') {
        $pending_review += $tc;
    } else {
        $correct += $cc;                  
        $wrong   += ($tc - $cc);            
    }

  
} 

$total   = count($questions);
$score   = $earned_points * 10;
$percent = $max_points > 0 ? round(($earned_points / $max_points) * 100) : 0;
$retake_url = "quiz.php?id=1&topic_id=$topic_id"; // Change subject id if needed
$is_result_page = true;
function displayAnswer($value)
{
    $decoded = json_decode($value, true);

    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {

        array_walk_recursive($decoded, function (&$item) {

            if (is_string($item)) {
                $item = preg_replace_callback(
                    '/\\\\u([0-9a-fA-F]{4})/',
                    function ($m) {
                        return mb_convert_encoding(
                            pack('H*', $m[1]),
                            'UTF-8',
                            'UTF-16BE'
                        );
                    },
                    $item
                );
            }
        });

        return htmlspecialchars(
            json_encode(
                $decoded,
                JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
            ),
            ENT_QUOTES,
            'UTF-8'
        );
    }

    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz Result - <?= $topic_title ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/mathjax@3/es5/tex-mml-chtml.js"></script>
    <style>
        body { background:#f8f9fa; padding:40px 0; font-family:'Segoe UI',sans-serif; }
        .card { max-width:1200px; margin:auto; border-radius:30px; overflow:visible; box-shadow:0 20px 60px rgba(0,0,0,0.3); }
        .score-circle { width:220px; height:220px; background:linear-gradient(45deg,#56ab2f,#a8e6cf); color:white; border-radius:50%; display:flex; flex-direction:column; align-items:center; justify-content:center; font-size:6rem; font-weight:bold; margin:auto; box-shadow:0 25px 60px rgba(0,0,0,0.5); }
        .question-badge { width:65px; height:65px; font-size:1.8rem; display:flex; align-items:center; justify-content:center; }
        .template-output { background:white; padding:30px; border-radius:20px; box-shadow:0 8px 25px rgba(0,0,0,0.1); margin:20px 0; }
        .card{
    width:100%;
    max-width:1200px;
    margin:auto;
}

.template-output{
    overflow-x:auto;
    overflow-y:hidden;
    padding:20px !important;
}

.template-output img{
    max-width:100%;
    height:auto;
}

.template-output table{
    width:100%;
    display:block;
    overflow-x:auto;
}

.template-output svg,
.template-output canvas{
    max-width:100%;
    height:auto;
}

.question-badge{
    width:50px;
    height:50px;
    font-size:1rem;
}

.template-output{
    padding:0 !important;
    border:none !important;
    background:transparent !important;
    box-shadow:none !important;
    overflow:visible !important;
}

.template-output *{
    max-width:100%;
}

.ps-5{
    padding-left:0 !important;
}
        /* ================= RESPONSIVE FIXES ================= */

/* Prevent horizontal overflow everywhere */
* {
    box-sizing: border-box;
}

body {
    padding: 20px 0;
    overflow-x: hidden;
}

/* Main card */
.card {
    max-width: 100%;
    margin: 0 10px;
}

/* Score circle responsive */
.score-circle {
    width: 160px;
    height: 160px;
    font-size: 4rem;
}

.score-circle small {
    font-size: 1.8rem !important;
}

/* Question badge fix */
.question-badge {
    width: 50px;
    height: 50px;
    font-size: 1.4rem;
}
/* Tablet */

@media (max-width:992px){

    .card-body{
        padding:20px !important;
    }

    .ps-5{
        padding-left:0 !important;
    }

    .template-output{
        padding:15px !important;
    }

    .score-circle{
        width:140px;
        height:140px;
        font-size:3rem;
    }

    .score-circle small{
        font-size:1.2rem !important;
    }

    .alert{
        font-size:14px !important;
    }

    .alert .badge{
        float:none !important;
        display:block;
        margin-top:10px;
        width:fit-content;
    }
}

/* Remove excessive left padding on small screens */
@media (max-width: 768px) {
    .ps-5 {
        padding-left: 0 !important;
    }
  body{
        padding:10px 0;
    }

    .card{
        border-radius:15px;
        margin:0 5px;
    }

    .card-header{
        padding:25px 15px !important;
    }

    .card-header h1{
        font-size:28px;
    }

    .card-header h3{
        font-size:18px;
    }

    .card-body{
        padding:15px !important;
    }

    .score-circle{
        width:120px;
        height:120px;
        font-size:2.5rem;
    }

    .score-circle small{
        font-size:1rem !important;
    }

    .display-4{
        font-size:1.8rem !important;
    }

    #q1,
    #q2,
    #q3,
    #q4,
    #q5{
        scroll-margin-top:80px;
    }

    .template-output{
        padding:10px !important;
        border-width:1px !important;
    }

    .question-badge{
        width:40px;
        height:40px;
        font-size:.8rem;
    }

    .alert{
        padding:12px !important;
        font-size:14px !important;
    }

    .alert .badge{
        display:block;
        float:none !important;
        margin-top:10px;
        width:fit-content;
    }

    .btn-lg{
        width:100%;
    }
}

/* Small Mobile */

@media (max-width:480px){

    .row.text-center .col-md-4{
        margin-bottom:10px;
    }

    .row.text-center .p-5{
        padding:20px !important;
    }

    .template-output{
        padding:8px !important;
    }

    .template-output *{
        max-width:100%;
    }

    h5{
        font-size:16px;
    }
}

/* Reduce card-body padding on mobile */
@media (max-width: 576px) {
    .card-body {
        padding: 20px !important;
    }

    .template-output {
        padding: 15px !important;
    }

    .score-circle {
        width: 130px;
        height: 130px;
        font-size: 3rem;
    }

    h1 {
        font-size: 1.6rem;
    }

    h3 {
        font-size: 1.2rem;
    }

    h5 {
        font-size: 1rem;
    }
}

/* Make quick navigation buttons wrap nicely */
.d-flex.flex-wrap {
    row-gap: 8px;
}

/* Make template content safe */
.template-output {
        overflow: visible;
}

/* Ensure all SVG / tables / math fit */
svg, table, canvas {
    max-width: 100%;
    height: auto;
}

    </style>
</head>
<body>
<div class="container">
    <div class="card mt-4">
        <div class="card-header text-center text-white py-5" style="background:linear-gradient(45deg,#4e54c8,#8f94fb);">
            <h1>Quiz Complete!</h1>
            <h3><?= $topic_title ?></h3>
        </div>

        <div class="card-body bg-light p-5">
            <div class="text-center mb-5">
                <div class="score-circle"><?= $score ?><small style="font-size:2.5rem;">pts</small></div>
                <h2 class="mt-4 text-success display-4"><?= $percent ?>% Correct</h2>
            </div>

        <div class="row text-center g-4 mb-5">
            <div class="col-md-3 col-6 d-flex">
                <div class="p-4 p-md-5 bg-success text-white rounded-4 shadow-lg w-100 d-flex flex-column justify-content-center align-items-center">
                    <h3 class="mb-1"><?= $correct ?></h3>
                    <p class="mb-0">Correct</p>
                </div>
            </div>
            <div class="col-md-3 col-6 d-flex">
                <div class="p-4 p-md-5 bg-danger text-white rounded-4 shadow-lg w-100 d-flex flex-column justify-content-center align-items-center">
                    <h3 class="mb-1"><?= $wrong ?></h3>
                    <p class="mb-0">Wrong</p>
                </div>
            </div>
            <div class="col-md-3 col-6 d-flex">
                <div class="p-4 p-md-5 bg-warning text-dark rounded-4 shadow-lg w-100 d-flex flex-column justify-content-center align-items-center">
                    <h3 class="mb-1"><?= $pending_review ?></h3>
                    <p class="mb-0 text-center">Pending Review</p>
                </div>
            </div>
            <div class="col-md-3 col-6 d-flex">
                <div class="p-4 p-md-5 bg-secondary text-white rounded-4 shadow-lg w-100 d-flex flex-column justify-content-center align-items-center">
                    <h3 class="mb-1"><?= $skipped ?></h3>
                    <p class="mb-0 text-center">Not Attempted</p>
                </div>
            </div>
        </div>

            <hr class="my-5">

            <div class="text-center mb-5">
                <strong class="fs-4">Quick Navigation:</strong><br>
                <div class="d-flex flex-wrap justify-content-center gap-2 mt-3">
                    <?php foreach ($questions as $i => $q):
                    $ans = trim($q['student_answer'] ?? '');
                    $btn = $ans === '' ? "btn-outline-secondary"
                         : ($q['question_type'] === 'perimeter_word_problem' ? "btn-outline-warning"
                         : ($q['is_correct'] == 1 ? "btn-outline-success" : "btn-outline-danger"));
                ?>
                        <a href="#q<?= $i+1 ?>" class="btn <?= $btn ?> btn-sm rounded-pill px-3"><?= $i+1 ?></a>
                    <?php endforeach; ?>
                </div>
            </div>

            <h3 class="text-center mb-5 text-dark">Detailed Answers</h3>

            <?php foreach ($questions as $i => $q):
                $ans = trim($q['student_answer'] ?? '');
                $question_number = $i + 1;

                // FIX: Define variables expected by ALL templates
                $index = $i;
                $char  = chr(97 + ($i % 26)); // a, b, c, ...

                $tcq = (int)($q['total_count'] ?? 1);
                $ccq = (int)($q['correct_count'] ?? ($q['is_correct'] == 1 ? 1 : 0));
                if ($ans === '') {
                    $badge_class = "bg-secondary"; $icon = "fa-circle";
                } elseif ($tcq > 1 && $ccq > 0 && $ccq < $tcq) {
                    $badge_class = "bg-warning";   $icon = "fa-adjust";        // partial
                } elseif (($tcq > 1 && $ccq === $tcq) || $q['is_correct'] == 1) {
                    $badge_class = "bg-success";   $icon = "fa-check-circle";
                } else {
                    $badge_class = "bg-danger";    $icon = "fa-times-circle";
                }
                $q_payload = ($q['question_payload'] !== null && $q['question_payload'] !== '') 
             ? json_decode($q['question_payload'], true) ?: [] 
             : [];
                $question_type = $q['question_type'];
            ?>

           <div id="q<?= $question_number ?>"
             class="bg-white rounded-4 shadow-sm p-3 p-md-4 p-lg-5 mb-4 border position-relative">
                <div class="position-absolute top-0 start-0 translate-middle-y ms-3">
                    <span class="badge <?= $badge_class ?> text-white question-badge shadow">
                        <i class="fas <?= $icon ?> me-1"></i><?= $question_number ?>
                    </span>
                </div>

                <div class="ps-5">
                    <h5 class="mb-4 text-primary fw-bold">Question <?= $question_number ?>:</h5>

                    <div class="template-output">
                        <?php
                        switch ($q['question_type']) {
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
                            case 'verify_triangle_angles': include 'templates/Angles/verify_triangle_angles.php'; break;
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
                            case 'pattern_match_rule' :  include 'templates/Probability/number_pattern_complete.php'; break;
                            case 'dynamic_fill_table':
                                include 'templates/exponent/dynamic_fill_table.php';
                                break;
                            case 'problem_solving':
                        include 'templates/problem_solving.php';
                      break;
                            case 'factor': include 'templates/Factor/factor.php'; break;
                            case 'decimal_percent_steps': include 'templates/percents/decimal_percent_steps.php'; break;
                            case 'percent_to_decimal_table': include 'templates/percents/percent_to_decimal_table.php'; break;
                            case 'fraction_to_percent': include 'templates/percents/fraction_to_percent.php'; break;
                            case 'percent_to_fraction_table': include 'templates/percents/percent_to_fraction_table.php'; break;
                            case 'find_whole_percent': include 'templates/percents/find_whole_percent.php'; break;
                            case 'percent_of_number': include 'templates/percents/percent_of_number.php'; break;
                            case 'percent_diagram': include 'templates/percents/percent_diagram.php'; break;
                            case 'fill_outcomes_with_images': include 'templates/Probability/probability_fill_with_images.php'; break;
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
                            case 'mcq':
                            include 'templates/Numbers/mcq.php';
                            break;
                            case 'place_value_table':
                            include 'templates/Numbers/place_value_table.php';
                            break; 
                            case 'place_value_identify':
                            include 'templates/Numbers/place_value_identify.php';
                            break;    
                            case 'multi_column_table':
                            include 'templates/Numbers/multi_column_table.php';
                            break;  
                            case 'number_scramble':
                            include 'templates/Numbers/number_scramble.php';
                            break;            
                            case 'number_order_dual':
                            include 'templates/Numbers/number_order_dual.php';
                            break;            
                            case 'image_question_panel':
                            include 'templates/Numbers/image_question_panel.php';
                            break;                                          
                            case 'train_number_panel':
                            include 'templates/Numbers/train_number_panel.php';
                            break;                                          
                            case 'greatest_smallest_number':
                            include 'templates/Numbers/greatest_smallest_number.php';
                            break;                                          
                            case 'place_value_digit':
                            include 'templates/Numbers/place_value_digit.php';
                            break;                                          
                            case 'spelling_number_names':
                            include 'templates/Numbers/spelling_number_names.php';
                            break;                                          
                            case 'expanded_form_5box':
                            include 'templates/Numbers/expanded_form_5box.php';
                            break;                                          
                            case 'rounding_judgement':
                            include 'templates/Numbers/rounding_judgement.php';
                            break;                                          
                            case 'odd_even_worksheet':
                            include 'templates/Numbers/odd_even_worksheet.php';
                            break;                                          
                            case 'prime_composite_worksheet':
                            include 'templates/Factor/prime_composite_worksheet.php';
                            break; 
                            case 'perimeter_word_problem':
                            include 'templates/equation/perimeter_word_problem.php';
                            break; 
                            case 'ratio_three_ways':
                            include 'templates/AreaPerimeter/ratio_three_ways_template.php';
                            break;                                               
                            case 'proportion_chain':
                            include 'templates/Numbers/proportion_chain.php';
                            break;
                            case 'visual_math_worksheet':
                            include 'templates/diagram/visual_math_worksheet.php';
                            break;                                               
                            default:
                                echo '<div class="p-4 text-muted fst-italic">Question type: ' . htmlspecialchars($q['question_type']) . '</div>';
                        }
                        ?>
                    </div>

                    <?php if ($ans === ''): ?>
                        <div class="alert alert-secondary text-center mt-3 py-3">Not Attempted</div>
                    
                    <?php elseif ($q['question_type'] === 'perimeter_word_problem'): ?>
                        <div class="alert alert-info mt-4 p-4 fs-5">
                            <strong>Your Answer:</strong> 
                            <span class="fw-bold text-primary"><?= displayAnswer($ans) ?></span>
                            <span class="badge bg-warning text-dark float-end fs-6 px-4 py-2">
                                <i class="fas fa-hourglass-half me-1"></i> Diagram Pending Review
                            </span>
                        </div>
                        <div class="alert alert-light border mt-3 p-3 fs-6 text-muted">
                            <i class="fas fa-info-circle me-1"></i>
                            Your teacher will review your drawing and confirm the final result.
                        </div>
                    
                                     <?php else: ?>
                        <?php $isMulti = ($tcq > 1); ?>
                        <div class="alert alert-info mt-4 p-4 fs-5">
                            <strong>Your Answer:</strong> 
                            <span class="fw-bold text-primary"><?= displayAnswer($ans) ?></span>
                            <?php if ($isMulti): ?>
                                <span class="badge bg-<?= $ccq === $tcq ? 'success' : ($ccq > 0 ? 'warning text-dark' : 'danger') ?> float-end fs-5 px-4 py-2">
                                    <?= $ccq ?>/<?= $tcq ?> Correct
                                </span>
                            <?php else: ?>
                                <span class="badge bg-<?= $q['is_correct'] == 1 ? 'success' : 'danger' ?> float-end fs-5 px-4 py-2">
                                    <?= $q['is_correct'] == 1 ? 'Correct' : 'Wrong' ?>
                                </span>
                            <?php endif; ?>
                        </div>
                        <?php if ($ccq !== $tcq): ?>
                            <div class="alert alert-success mt-3 p-4 fs-5">
                                <strong>Correct Answer:</strong> <?= displayAnswer($q['correct_answer']) ?>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
<?php
// if (strtolower(trim($topic_title)) === 'problem solving') {
//     // Fetch static answers from DB
//     $static_answers = [];  // key = temp_id, value = ['student_answer' => ..., 'is_correct' => ...]
//     $stmt_static = $conn->prepare("SELECT question_id, student_answer, is_correct 
//                                   FROM student_answers 
//                                   WHERE student_id = ? 
//                                     AND quiz_id = ? 
//                                     AND question_id >= 10000 
//                                     AND created_at = ?");
//     $stmt_static->bind_param("iii", $student_id, $topic_id, $latest_time);
//     $stmt_static->execute();
//     $res_static = $stmt_static->get_result();
//     while ($row = $res_static->fetch_assoc()) {
//         $static_answers[$row['question_id']] = [
//             'student_answer' => trim($row['student_answer'] ?? ''),
//             'is_correct' => $row['is_correct']
//         ];
//     }
//     $stmt_static->close();

//     // Update counters using fetched data
//     foreach ($static_answers as $sa) {
//         $ans = $sa['student_answer'];
//         if ($ans === '') {
//             $skipped++;
//         } elseif ($sa['is_correct'] == 1) {
//             $correct++;
//         } else {
//             $wrong++;
//         }
//     }

//     $total += count($static_answers);
//     $score = $correct * 10;
//     $percent = $total > 0 ? round(($correct / $total) * 100) : 0;

//     echo '<h3 class="text-center my-5 text-primary fw-bold">Problem Solving Questions</h3>';

//     // Pass data to template
//     $static_student_answers = $static_answers;  
//     include 'templates/problem_solving.php';
// }
// ?>
            <div class="text-center mt-5">
                <a href="<?= $retake_url ?>" class="btn btn-success btn-lg px-5 py-3 shadow-lg fs-4">
                    Retake Quiz
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
</body>
</html>