<?php
session_start();
include 'config.php'; // DB connection file

// Check login
if (!isset($_SESSION['student_id'])) {
    header("Location: student_login.php");
    exit;
}

// Get subject ID from URL
if (!isset($_GET['subject_id'])) {
    echo "Subject ID missing.";
    exit;
}

$subject_id = intval($_GET['subject_id']);
$student_id = (int)$_SESSION['student_id'];

// Fetch subject name
$subject_sql = $conn->prepare("SELECT name FROM subjects WHERE id = ?");
$subject_sql->bind_param("i", $subject_id);
$subject_sql->execute();
$subject_result = $subject_sql->get_result()->fetch_assoc();
$subject_name = $subject_result['name'] ?? 'Unknown Subject';

// Fetch only chapters assigned to this student for this subject
$chapter_sql = $conn->prepare("SELECT id, chapter_title FROM assigned_chapters WHERE subject_id = ? AND student_id = ?");
$chapter_sql->bind_param("ii", $subject_id, $student_id);
$chapter_sql->execute();
$chapters = $chapter_sql->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($subject_name) ?> Chapters</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container py-5">
    <h2 class="mb-4"><?= htmlspecialchars($subject_name) ?> - Chapters</h2>

    <?php if ($chapters->num_rows > 0): ?>
        <div class="list-group">
            <?php while ($row = $chapters->fetch_assoc()): ?>
                <a href="chapter_view.php?chapter_id=<?= $row['id'] ?>" class="list-group-item list-group-item-action">
                    <?= htmlspecialchars($row['chapter_title']) ?>
                </a>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <p class="text-muted">No chapters have been assigned to you yet for this subject.</p>
    <?php endif; ?>

    <a href="student_dashboard.php" class="btn btn-secondary mt-4">⬅️ Back to Dashboard</a>
</div>

</body>
</html>
