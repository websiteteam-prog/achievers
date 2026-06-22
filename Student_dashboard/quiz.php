<?php
session_start();
include "../db_config.php";

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$subject_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$topic_id   = isset($_GET['topic_id']) ? intval($_GET['topic_id']) : 0;

if ($subject_id == 0) {
  die("Invalid subject ID");
}

$student_id = $_SESSION['student_id'] ?? 1; // testing ke liye

$selected_topic = null;
$instructions = []; // ✅ CHANGE (old $quiz_questions removed)

if ($topic_id) {

  // Topic details
  $sql3 = "SELECT title, content, video_path FROM topics WHERE id = ?";
  $stmt3 = $conn->prepare($sql3);
  $stmt3->bind_param("i", $topic_id);
  $stmt3->execute();
  $result3 = $stmt3->get_result();
  $selected_topic = $result3->fetch_assoc();

  // Instructions + Questions
  $sql4 = "SELECT i.instruction, qq.instruction_id, qq.id, qq.question_type, 
             qq.question_text, qq.question_payload, qq.correct_answer, qq.question_image
             FROM instructions i 
             JOIN quiz_questions qq ON i.id = qq.instruction_id 
             WHERE i.topic_id = ? 
             ORDER BY i.id ASC, qq.id ASC";

  $stmt4 = $conn->prepare($sql4);
  $stmt4->bind_param("i", $topic_id);
  $stmt4->execute();
  $result4 = $stmt4->get_result();

  // ✅ GROUP BY instruction
  while ($row = $result4->fetch_assoc()) {
    $instructions[$row['instruction_id']]['instruction'] = $row['instruction'];
    $instructions[$row['instruction_id']]['questions'][] = $row;
  }
}

/* ======================
    PAGINATION LOGIC
  ====================== */
$instruction_ids = array_keys($instructions);
$total_instructions = count($instruction_ids);

$page = isset($_GET['page']) ? (int)$_GET['page'] : 0;

if ($page < 0) $page = 0;
if ($page >= $total_instructions) $page = $total_instructions - 1;

$current_instruction_id = $instruction_ids[$page] ?? null;
$current_instruction = $instructions[$current_instruction_id] ?? null;


// ---------------------------
// ATTEMPT TIMESTAMP (same as your code)
// ---------------------------
if ($topic_id) {
  $sess_key = 'attempt_time_for_topic_' . $topic_id;
  if (empty($_SESSION[$sess_key])) {
    $_SESSION[$sess_key] = date("Y-m-d H:i:s");
  }
  $attempt_time = $_SESSION[$sess_key];
}
?>
<!DOCTYPE html>
<html>

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="student.css" rel="stylesheet" />
  <title>Student Dashboard</title>
  <style>
    /* Existing styles remain the same */
    body {
      min-height: 100vh;
      font-family: 'Segoe UI', sans-serif;
      background-color: #f5f5f5;
    }

    .main {
      margin-left: 0px;
      padding: 30px;
      overflow-x: hidden;
    }

    .explaination-box {
      display: flex;
      flex-direction: column;
      gap: 12px;
      margin-bottom: 20px;
    }

    .info-box {
      display: flex;
      justify-content: space-between;
      align-items: center;
      border-radius: 50px;
      padding: 14px 25px;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
      flex-wrap: wrap;
      gap: 10px;
    }

    .info-yellow {
      background-color: #FFD700;
      color: #000;
    }

    .info-blue {
      background-color: #1F669C;
      color: #fff;
    }

    .info-box .text {
      font-size: 15px;
      font-weight: 500;
    }

    .info-box .small-text {
      font-size: 13px;
      color: #333;
    }

    .video-btn {
      background-color: #e8063c;
      color: #fff;
      padding: 8px 16px;
      border-radius: 30px;
      font-size: 14px;
      text-decoration: none;
      font-weight: 500;
      transition: 0.3s;
    }

    .video-btn:hover {
      background-color: #c50533;
    }

    .see-explanation {
      background-color: black;
      color: #fff;
      padding: 8px 16px;
      border-radius: 30px;
      font-size: 14px;
      text-decoration: none;
      font-weight: 500;
      transition: 0.3s;
    }

    .see-explanation:hover {
      background-color: grey;
    }

    .quiz-box {
      background: white;
      border-radius: 15px;
      padding: 30px;
      box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
      margin-top: 20px;
      animation: fadeIn 0.6s ease-in-out;
    }

    @keyframes fadeIn {
      from {
        opacity: 0;
        transform: translateY(20px);
      }

      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    .next-btn {
      background: #e8063c;
      border: none;
      padding: 12px 28px;
      border-radius: 30px;
      color: #fff;
      font-size: 16px;
      font-weight: bold;
      transition: 0.3s;
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
    }

    .next-btn:hover {
      background: linear-gradient(45deg, #ff1744, #d50000);
      transform: scale(1.05);
    }

    .quiz-input {
      width: 60%;
      padding: 8px 5px;
      font-size: 16px;
      border: none;
      border-bottom: 2px solid #ccc;
      border-radius: 0;
      outline: none;
      background-color: transparent;
      transition: border-color 0.3s;
    }

    .quiz-input:focus {
      border-bottom-color: #007bff;
    }

    .question-image {
      max-width: 200px;
      height: auto;
      margin-left: 10px;
    }

    .bottom-explain-box {
      display: flex;
      justify-content: space-between;
      align-items: center;
      border-radius: 50px;
      padding: 14px 25px;
      background-color: #FFD700;
      color: #000;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
      margin-top: 25px;
      flex-wrap: wrap;
      gap: 10px;
    }

    .bottom-explain-box a {
      background-color: #e8063c;
      color: #fff;
      padding: 8px 16px;
      border-radius: 30px;
      font-size: 14px;
      text-decoration: none;
      font-weight: 500;
      transition: 0.3s;
    }

    .bottom-explain-box a:hover {
      background-color: #c50533;
    }

    .bottom-explain-box .text {
      font-size: 15px;
      font-weight: 500;
    }

    .bottom-explain-box .small-text {
      font-size: 13px;
      color: #333;
    }

    @media (max-width: 768px) {
      .main {
        padding: 15px;
      }

      .info-box,
      .bottom-explain-box {
        flex-direction: column;
        align-items: flex-start;
        text-align: left;
        border-radius: 20px;
        padding: 15px;
      }

      .info-box .text,
      .bottom-explain-box .text {
        font-size: 14px;
      }

      .info-box .small-text,
      .bottom-explain-box .small-text {
        font-size: 12px;
      }

      .video-btn,
      .bottom-explain-box a {
        width: 100%;
        text-align: center;
        margin-top: 8px;
      }

      .next-btn {
        width: 100%;
        padding: 14px;
        font-size: 15px;
      }

      h5 {
        font-size: 16px;
      }
    }

    @media (max-width: 480px) {
      .blue-box {
        width: 100%;
        font-size: 14px;
        padding: 6px;
      }
    }
  </style>
</head>

<body>
  <section class="main">

    <!-- Top Section -->
    <div class="explaination-box">
      <div class="info-box info-yellow">
        <div class="left-content">
          <strong>Quiz:</strong> <?= htmlspecialchars($selected_topic['title'] ?? '') ?><br>
          <span class="small-text"><?= htmlspecialchars($selected_topic['content'] ?? '') ?></span>
        </div>
        <div class="right-buttons">
          <a href="#" class="see-explanation">See Explanation</a>
          <a href="#" class="video-btn" data-bs-toggle="modal" data-bs-target="#videoModal">
            Watch Video
          </a>
        </div>
      </div>

      <div class="info-box info-blue">
        <?php if (!isset($_SESSION['score'])) $_SESSION['score'] = 0; ?>
        <div class="text">Score: <?= intval($_SESSION['score']) ?></div>
        <div class="text">Total Questions: <?= $total_instructions ?></div>
        <div class="text">Grade</div>
      </div>
    </div>

    <!-- Quiz Form -->
    <form method="post" action="submit_quiz.php" class="mt-4">
      <?php
      // 🔥 Inject previous answers as hidden inputs (NO TEMPLATE CHANGE NEEDED)
      if (!empty($_SESSION['quiz_answers'])) {
        foreach ($_SESSION['quiz_answers'] as $qid => $ans) {

          // Skip current page questions (they already exist)
          if (!empty($current_instruction)) {
            foreach ($current_instruction['questions'] as $qtemp) {
              if ($qtemp['id'] == $qid) {
                continue 2;
              }
            }
          }

          $value = is_array($ans) ? json_encode($ans) : $ans;
      ?>
          <input type="hidden" name="answer[<?= $qid ?>]" value="<?= htmlspecialchars($value) ?>">
      <?php
        }
      }
      ?>
      <input type="hidden" name="quiz_id" value="<?= $topic_id ?>">
      <input type="hidden" name="submit_quiz" value="1">

      <?php if (!empty($attempt_time)): ?>
        <input type="hidden" name="attempt_time" value="<?= htmlspecialchars($attempt_time) ?>">
      <?php endif; ?>

      <div class="row">
        <?php
        $char = 'a';

        if (!empty($current_instruction)):
        ?>

          <h5>
            Ques.<?= ($page + 1) . ') ' . htmlspecialchars($current_instruction['instruction']); ?>
          </h5>

          <?php
          foreach ($current_instruction['questions'] as $index => $q) {

            switch ($q['question_type']) {

              case 'fill_blank2':
                include 'templates/fill_blank2.php';
                break;

              case 'theory':
                include 'templates/theory.php';
                break;

              case 'fill_blank':
                include 'templates/fill_blank.php';
                break;

              case 'compare':
                include 'templates/compare.php';
                break;

              case 'compare2':
                include 'templates/compare2.php';
                break;

              case 'BODMAS':
                include 'templates/bodmas.php';
                break;

              case 'long_division':
                include 'templates/long_division.php';
                break;

              case 'fill_blank_underline':
                include 'templates/fill_blank_underline.php';
                break;

              case 'fill_blank_models':
                include 'templates/fill_blank_models.php';
                break;

              case 'order_arrange':
                include 'templates/order_arrange.php';
                break;

              case 'bodmas_fill_blank':
                include 'templates/bodmas_fill_blank.php';
                break;

              case 'fraction_diagram':
                include 'templates/Fraction/fraction_diagram3.1.php';
                break;

              case 'fraction_fill_diagram':
                include 'templates/Fraction/fraction_diagram3.2.php';
                break;

              case 'fraction_improper':
                include 'templates/Fraction/fraction_improper.php';
                break;

              case 'fraction_mixed_to_improper':
                include 'templates/Fraction/fraction_mixed_to_improper.php';
                break;

              case 'fraction_mixed_to_improper_fill':
                include 'templates/Fraction/fraction_mixed_to_improper_fill.php';
                break;

              case 'fraction_order_diagram':
                include 'templates/Fraction/fraction_order_diagram.php';
                break;

              case 'fraction_numberline_multi_fill_compare':
                include 'templates/Fraction/fraction_numberline_multi_fill_compare.php';
                break;

              case 'fraction_order_list':
                include 'templates/Fraction/fraction_order_list.php';
                break;

              case 'add_and_sub_fractions':
                include 'templates/Fraction/add_and_sub_fractions.php';
                break;

              case 'fraction_compare':
                include 'templates/Fraction/fraction_compare.php';
                break;

              case 'BODMAS_fraction':
                include 'templates/Fraction/BODMAS_fraction.php';
                break;

              case 'equation_missing':
                include 'templates/equation/equation_missing.php';
                break;

              case 'equation_diagram':
                include 'templates/equation/equation_diagram.php';
                break;

              case 'equation_star':
                include 'templates/equation/equation_star.php';
                break;

              case 'equation_volume':
                include 'templates/equation/equation_volume.php';
                break;

              case 'display_angles':
                include 'templates/Angles/display_angles.php';
                break;

              case 'angles_classification':
                include 'templates/Angles/angles_classification.php';
                $char++;
                break;

              case 'types_angles':
                include 'templates/Angles/types_angles.php';
                break;

              case 'polygons_intro':
                include 'templates/Angles/polygons_intro.php';
                break;

              case 'draw_angle_protractor_single':
                include 'templates/Angles/draw_angle_protractor_single.php';
                $char++;
                break;

              case 'draw_angle_protractor_range':
                include 'templates/Angles/draw_angle_protractor_range.php';
                $char++;
                break;

              case 'color_prisms_pyramids':
                include 'templates/PrismsPyramids/color_prisms_pyramids.php';
                break;

              case 'question_renderer':
              case 'complete_table':
              case 'match_nets':
                include 'templates/PrismsPyramids/question_renderer.php';
                break;

              case 'money_question_renderer':
                include 'templates/Money/money_question_renderer.php';
                break;

              case 'money_addsub':
              case 'picture_money_word':
                include 'templates/Money/money_addsub_renderer.php';
                break;

              case 'match':
                include 'templates/match.php';
                break;

              case 'fullsize_diagram_only':
                include 'templates/fullsize_diagram_only.php';
                break;

              case 'coordinate_points_input':
                include 'templates/Coordinate/coordinate_points_input.php';
                break;

              case 'fill_outcomes':
                include 'templates/Probability/probability_question.php';
                break;

              case 'factor':
                include 'templates/Factor/factor.php';
                break;
              case 'decimal_percent_steps':
                include 'templates/percents/decimal_percent_steps.php';
                break;
              case 'percent_to_decimal_table':
                include 'templates/percents/percent_to_decimal_table.php';
                break;
              case 'fraction_to_percent':
                include 'templates/percents/fraction_to_percent.php';
                break;
              case 'percent_to_fraction_table':
                include 'templates/percents/percent_to_fraction_table.php';
                break;
              case 'percent_of_number':
                include 'templates/percents/percent_of_number.php';
                break;
              case 'find_whole_percent':
                include 'templates/percents/find_whole_percent.php';
                break;
              case 'percent_diagram':
                include 'templates/percents/percent_diagram.php';
                break;

              case 'fill_outcomes_with_images':
                include 'templates/Probability/probability_fill_with_images.php';
                break;

              case 'problem_solving':
                include 'templates/problem_solving.php';
                break;
              case 'number_pattern_complete':
              case 'pattern_rule_mcq':
              case 'pattern_extend_rule':
              case 'pattern_match_rule':
                include 'templates/Probability/number_pattern_complete.php';
                break;
            }
          }
          ?>

        <?php else: ?>
          <p>No instructions found.</p>
        <?php endif; ?>
      </div>

      <!-- Pagination Buttons -->
      <div class="text-center mt-3">

        <?php if ($page > 0): ?>
          <button type="submit" name="save_page" value="<?= $page - 1 ?>"
            class="btn btn-secondary">Previous</button>
        <?php endif; ?>

        <?php if ($page < $total_instructions - 1): ?>
          <button type="submit" name="save_page" value="<?= $page + 1 ?>"
            class="btn btn-primary">Next</button>
        <?php endif; ?>

      </div>

      <!-- Submit -->
      <div class="text-center mt-4">
        <button type="submit" class="next-btn">Submit Quiz</button>
      </div>

    </form>

    <!-- ðŸŒŸ Bottom Explanation Section -->
    <div class="bottom-explain-box">
      <div class="text">
        <strong>See Explanation:</strong> <span class="small-text">Click below to review answers and explanations.</span>
      </div>
      <a href="explanation_page.php?topic_id=<?= $topic_id ?>">See Explanation</a>
    </div>

    <!-- Video Modal -->
    <div class="modal fade" id="videoModal" tabindex="-1" aria-labelledby="videoModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="videoModalLabel"><?= htmlspecialchars($selected_topic['title'] ?? '') ?> - Video</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <?php if (!empty($selected_topic['video_path'])): ?>
              <?php
              $video = $selected_topic['video_path'];
              if (strpos($video, "youtube.com/watch") !== false) {
                $video_id = explode("v=", $video)[1];
                $video_id = explode("&", $video_id)[0];
                $embed_url = "https://www.youtube.com/embed/" . $video_id;
              }
              ?>
              <div class="ratio ratio-16x9">
                <iframe src="<?= htmlspecialchars($embed_url ?? $video) ?>"
                  title="Topic Video"
                  frameborder="0"
                  allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                  allowfullscreen>
                </iframe>
              </div>
            <?php else: ?>
              <p>No video available for this topic.</p>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  </section>

  <script src="https://cdn.jsdelivr.net/npm/mathjax@3/es5/mml-chtml.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    document.addEventListener("DOMContentLoaded", () => MathJax.typesetPromise());

    var videoModal = document.getElementById('videoModal');
    videoModal.addEventListener('hidden.bs.modal', function() {
      var iframe = videoModal.querySelector('iframe');
      if (iframe) {
        iframe.src = iframe.src;
      }
    });
  </script>
</body>

</html>