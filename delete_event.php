<?php
session_start();
header('Content-Type: application/json');
include 'db_config.php';

if (!isset($_SESSION['teacher_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$teacher_id = $_SESSION['teacher_id'];
$id         = $_POST['id'] ?? 0;

// Sirf apne (teacher-created) event delete honge, admin events nahi.
$sql = "DELETE FROM calendar_events
        WHERE id = ? AND creator_id = ? AND creator_role = 'teacher'";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $id, $teacher_id);

if ($stmt->execute() && $stmt->affected_rows > 0) {
    echo json_encode(['status' => 'success', 'message' => 'Event deleted.']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Could not delete (only your own events can be deleted).']);
}
?>