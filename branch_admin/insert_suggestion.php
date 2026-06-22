<?php
require_once __DIR__ . '/auth.php';

if (!is_branch_admin()) {
    json_response(false, 'Unauthorized.');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(false, 'Invalid request method.');
}

$branch_id  = $_SESSION['branch_id'];
$suggestion = trim($_POST['suggestion'] ?? '');
$subject    = (int) ($_POST['subject'] ?? 0);

if ($suggestion === '' || $subject <= 0) {
    json_response(false, 'Please fill all details.');
}

try {
    $stmt = $conn->prepare("INSERT INTO course_suggestions (subject_id, suggestion, branch_id) VALUES (?, ?, ?)");
    $stmt->bind_param("isi", $subject, $suggestion, $branch_id);
    $stmt->execute();
    json_response(true, 'Suggestion sent successfully.');
} catch (mysqli_sql_exception $e) {
    json_response(false, 'Failed to send suggestion.');
}
