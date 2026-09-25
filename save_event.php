<?php
session_start();
header('Content-Type: application/json');
include 'db_config.php';

if (!isset($_SESSION['teacher_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$teacher_id  = $_SESSION['teacher_id'];
$title       = $_POST['title'] ?? '';
$date        = $_POST['date'] ?? '';
$time        = $_POST['time'] ?? '';
$description = $_POST['description'] ?? '';

if (!$title || !$date || !$time) {
    echo json_encode(['status' => 'error', 'message' => 'Please fill all required fields.']);
    exit;
}

$datetime = $date . ' ' . $time;

// Teacher apna event bana raha hai:
//  creator_role = 'teacher', creator_id = teacher_id, teacher_id = teacher_id
$sql = "INSERT INTO calendar_events
        (title, description, event_datetime, creator_role, creator_id, teacher_id)
        VALUES (?, ?, ?, 'teacher', ?, ?)";

$stmt = $conn->prepare($sql);
$stmt->bind_param("sssii", $title, $description, $datetime, $teacher_id, $teacher_id);

if ($stmt->execute()) {
    echo json_encode(['status' => 'success', 'message' => 'Event saved successfully.']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to save event.']);
}
?>