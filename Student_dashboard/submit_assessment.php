<?php
session_start();
include "../db_config.php";
include "student_sidebar.php";

if (!isset($_SESSION['student_id'])) {
    die("Unauthorized");
}

$student_id = (int)$_SESSION['student_id'];
$ass_id = (int)($_POST['assessment_id'] ?? 0);

if ($ass_id <= 0) {
    die("Invalid assessment ID");
}

// AB YAHAN DAALO DEBUG — SAB KUCH DEFINE HONE KE BAAD
file_put_contents('debug_log.txt', "=== NEW SUBMIT START ===\nAssessment ID: $ass_id\nStudent ID: $student_id\n\n", FILE_APPEND);

$conn->autocommit(false);
$score = 0;
$total_questions = 0;

try {
    // 1. Ensure assignment record
    $check = $conn->prepare("SELECT 1 FROM assessment_assignments WHERE assessment_id = ? AND student_id = ?");
    $check->bind_param("ii", $ass_id, $student_id);
    $check->execute();
    $exists = $check->get_result()->num_rows > 0;
    $check->close();

    if (!$exists) {
        $ins = $conn->prepare("INSERT INTO assessment_assignments (assessment_id, student_id, created_at) VALUES (?, ?, NOW())");
        $ins->bind_param("ii", $ass_id, $student_id);
        $ins->execute();
        $ins->close();
    }

    // 2. Mark submitted
    $stmt = $conn->prepare("UPDATE assessment_assignments SET submitted_at = NOW() WHERE assessment_id = ? AND student_id = ?");
    $stmt->bind_param("ii", $ass_id, $student_id);
    $stmt->execute();
    $stmt->close();

    // 3. Save answers
       // 3. Save student answers — AB ASSESSMENT KE LIYE ALAG TABLE
    $student_answers = $_POST['answer'] ?? [];
    file_put_contents('debug_log.txt', "Student Answers Received:\n" . print_r($student_answers, true) . "\n\n", FILE_APPEND);

    if (!empty($student_answers)) {
        $ins = $conn->prepare("
            INSERT INTO assessment_student_answers 
            (student_id, assessment_id, question_id, student_answer) 
            VALUES (?, ?, ?, ?) 
            ON DUPLICATE KEY UPDATE student_answer = VALUES(student_answer)
        ");
        
        foreach ($student_answers as $qid => $ans) {
    $qid = (int)$qid;

    // Handle cases where answer is submitted as an array (multi-part answers)
    if (is_array($ans)) {
        $ans = implode(',', array_map('trim', $ans));
    } else {
        $ans = trim($ans);
    }

    if ($ans !== '') {
        $ins->bind_param("iiis", $student_id, $ass_id, $qid, $ans);
        $ins->execute();
    }
        }
    }

    // 4. Fetch correct answers — YE PAKKA CHALEGA AB
    $correct = [];
    $debug_questions = [];

    $q = $conn->prepare("
        SELECT qq.id, qq.correct_answer
        FROM assessment_questions aq
        JOIN quiz_questions qq ON aq.question_id = qq.id
        WHERE aq.assessment_id = ?
    ");
    $q->bind_param("i", $ass_id);
    $q->execute();
    $res = $q->get_result();

    while ($row = $res->fetch_assoc()) {
        $cid = $row['id'];
        $correct_ans = trim($row['correct_answer'] ?? '');
        $debug_questions[] = "QID: $cid | Correct: '$correct_ans'";
        $correct[$cid] = $correct_ans;
        $total_questions++;
    }
    $q->close();

    file_put_contents('debug_log.txt', "Questions Found in DB ($total_questions):\n" . implode("\n", $debug_questions) . "\n\n", FILE_APPEND);

    // 5. Score calculation
    foreach ($student_answers as $qid => $ans) {
        if (!isset($correct[$qid])) continue;

        $stu = is_array($ans) ? implode(',', array_map('trim', $ans)) : trim($ans);
        $cor = $correct[$qid];

        $stu_norm = preg_replace('/\s+/', '', $stu);
        $stu_norm = str_replace(['×','x','X','*'], '×', $stu_norm);
        $stu_norm = str_replace(['÷','/'], '÷', $stu_norm);
        $stu_norm = str_replace(['−','–','—','-'], '-', $stu_norm);

        $cor_norm = preg_replace('/\s+/', '', $cor);
        $cor_norm = str_replace(['×','x','X','*'], '×', $cor_norm);
        $cor_norm = str_replace(['÷','/'], '÷', $cor_norm);
        $cor_norm = str_replace(['−','–','—','-'], '-', $cor_norm);

        if ($stu_norm === $cor_norm && $stu_norm !== '') {
            $score++;
            file_put_contents('debug_log.txt', "MATCH! QID $qid: '$stu' === '$cor'\n", FILE_APPEND);
        } else {
            file_put_contents('debug_log.txt', "NO MATCH QID $qid: '$stu_norm' vs '$cor_norm'\n", FILE_APPEND);
        }
    }

    file_put_contents('debug_log.txt', "FINAL SCORE: $score / $total_questions\n=== SUBMIT END ===\n\n", FILE_APPEND);

    // 6. Save score
    $upd = $conn->prepare("UPDATE assessment_assignments SET score = ?, total_questions = ? WHERE assessment_id = ? AND student_id = ?");
    $upd->bind_param("iiii", $score, $total_questions, $ass_id, $student_id);
    $upd->execute();
    $upd->close();

    $conn->commit();

} catch (Exception $e) {
    $conn->rollback();
    file_put_contents('debug_log.txt', "ERROR: " . $e->getMessage() . "\n\n", FILE_APPEND);
    $score = 0;
    $total_questions = 0;
}

$conn->autocommit(true);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submitted Successfully!</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --primary: #1e40af;
            --primary-light: #3b82f6;
            --primary-dark: #1e3a8a;
            --accent: #ef4444;
            --light-bg: #f5f7fb;
            --gray: #6b7280;
            --shadow: 0 6px 20px rgba(0, 0, 0, 0.06);
        }

        * { box-sizing: border-box; }

        html, body {
            max-width: 100%;
            overflow-x: hidden;
        }

        body {
            background: var(--light-bg);
            font-family: system-ui, -apple-system, sans-serif;
            margin: 0;
        }

        .main-content {
            margin-left: 260px;
            padding: 40px;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .card {
            max-width: 480px;
            width: 100%;
            margin: auto;
            border-radius: 22px;
            overflow: hidden;
            border: none;
            box-shadow: 0 8px 30px rgba(0, 0, 0, .08);
            background: #fff;
        }

        .header {
            background: linear-gradient(135deg, #1e3c72, #2a5298);
            padding: 45px 20px;
            text-align: center;
            color: white;
        }

        .header i {
            font-size: 4rem;
        }

        .header h1 {
            font-size: 1.7rem;
            font-weight: 700;
            margin: 16px 0 0;
        }

        .score {
            font-size: 1.6rem;
            font-weight: 700;
            color: #fff;
            margin-top: 10px;
        }

        .card-body {
            padding: 36px;
            text-align: center;
        }

        .btn-home {
            background: linear-gradient(135deg, #1e3c72, #2a5298);
            color: white;
            border: none;
            padding: 13px 34px;
            font-size: 1rem;
            font-weight: 600;
            border-radius: 50px;
            text-decoration: none;
            display: inline-block;
            transition: .25s;
        }

        .btn-home:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 24px rgba(30, 60, 114, .32);
            color: white;
        }

        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
                padding: 24px;
            }
        }
    </style>
</head>
<body>
<div class="main-content">
    <div class="card">

        <div class="header">
            <i class="bi bi-check-circle-fill"></i>
            <h1>Submitted Successfully!</h1>
            <div class="score">Your Score: <?= $score ?> / <?= $total_questions ?></div>
        </div>

        <div class="card-body">
            <a href="student_dashboard.php" class="btn-home">
                <i class="bi bi-house-door-fill me-1"></i> Back to Dashboard
            </a>
        </div>

    </div>
</div>
</body>
</html>