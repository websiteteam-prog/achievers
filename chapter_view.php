<?php
session_start();
include 'config.php';

if (!isset($_SESSION['student_id'])) {
    header("Location: student_login.php");
    exit;
}

$student_id = $_SESSION['student_id'];

// Chapter ID from URL
if (!isset($_GET['chapter_id'])) {
    echo "Chapter ID is missing.";
    exit;
}
$chapter_id = intval($_GET['chapter_id']);

// Get chapter info (must belong to this student)
$stmt = $conn->prepare("SELECT * FROM assigned_chapters WHERE id = ? AND student_id = ?");
$stmt->bind_param("ii", $chapter_id, $student_id);
$stmt->execute();
$chapter = $stmt->get_result()->fetch_assoc();

if (!$chapter) {
    echo "Chapter not found.";
    exit;
}

// Get one question from this chapter (can be randomized later)
$q_stmt = $conn->prepare("SELECT * FROM quiz_questions WHERE chapter_id = ? LIMIT 1");
$q_stmt->bind_param("i", $chapter_id);
$q_stmt->execute();
$question = $q_stmt->get_result()->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($chapter['chapter_title']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container py-5">
    <h3 class="mb-3"><?= htmlspecialchars($chapter['chapter_title']) ?></h3>

    <!-- Video -->
    <?php if (!empty($chapter['video_url'])): ?>
        <div class="mb-4">
            <iframe width="100%" height="360" src="<?= htmlspecialchars($chapter['video_url']) ?>" frameborder="0" allowfullscreen></iframe>
        </div>
    <?php endif; ?>

    <!-- Question Section -->
    <?php if ($question): ?>
        <div class="card p-4">
            <h5><?= htmlspecialchars($question['question']) ?></h5>

            <form action="submit_answer.php" method="POST">
                <input type="hidden" name="question_id" value="<?= $question['id'] ?>">
                <input type="hidden" name="chapter_id" value="<?= $chapter_id ?>">

                <div class="form-check">
                    <input class="form-check-input" type="radio" name="answer" value="A" required>
                    <label class="form-check-label"><?= htmlspecialchars($question['option_a']) ?></label>
                </div>

                <div class="form-check">
                    <input class="form-check-input" type="radio" name="answer" value="B">
                    <label class="form-check-label"><?= htmlspecialchars($question['option_b']) ?></label>
                </div>

                <div class="form-check">
                    <input class="form-check-input" type="radio" name="answer" value="C">
                    <label class="form-check-label"><?= htmlspecialchars($question['option_c']) ?></label>
                </div>

                <div class="form-check">
                    <input class="form-check-input" type="radio" name="answer" value="D">
                    <label class="form-check-label"><?= htmlspecialchars($question['option_d']) ?></label>
                </div>

                <button type="submit" class="btn btn-primary mt-3">Submit Answer</button>
            </form>
        </div>
    <?php else: ?>
        <p class="text-muted mt-4">No question added for this chapter.</p>
    <?php endif; ?>

    <a href="chapter_list.php?subject_id=<?= $chapter['subject_id'] ?>" class="btn btn-secondary mt-4">⬅️ Back to Chapters</a>
</div>

</body>
</html>
