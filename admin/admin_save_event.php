<?php
session_start();
header('Content-Type: application/json');
include '../db_config.php';

if (!isset($_SESSION['admin_logged_in'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

// login.php me agar admin id set hoti hai to use karo, warna 0
$admin_id = $_SESSION['admin_id'] ?? 0;

$event_id    = $_POST['event_id'] ?? '';
$title       = $_POST['title'] ?? '';
$date        = $_POST['date'] ?? '';
$time        = $_POST['time'] ?? '';
$description = $_POST['description'] ?? '';
$audience    = $_POST['audience'] ?? 'all';

if (!$title || !$date || !$time) {
    echo json_encode(['status' => 'error', 'message' => 'Please fill all required fields.']);
    exit;
}

// Audience: all -> teacher_id NULL (sab teachers), teacher -> specific teacher id
$teacher_id = null;
if ($audience === 'teacher') {
    $teacher_id = !empty($_POST['target_teacher']) ? intval($_POST['target_teacher']) : null;
    if (!$teacher_id) {
        echo json_encode(['status' => 'error', 'message' => 'Please select a teacher.']);
        exit;
    }
}

$datetime = $date . ' ' . $time;

if (!empty($event_id)) {
    // UPDATE
    $sql = "UPDATE calendar_events
            SET title = ?, description = ?, event_datetime = ?, teacher_id = ?
            WHERE id = ? AND creator_role = 'admin'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssii", $title, $description, $datetime, $teacher_id, $event_id);
    $ok = $stmt->execute();
    $msg = $ok ? 'Event updated successfully.' : 'Failed to update event.';
} else {
    // INSERT
    $sql = "INSERT INTO calendar_events
            (title, description, event_datetime, creator_role, creator_id, teacher_id)
            VALUES (?, ?, ?, 'admin', ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssii", $title, $description, $datetime, $admin_id, $teacher_id);
    $ok = $stmt->execute();
    $msg = $ok ? 'Event added successfully.' : 'Failed to add event.';
}

echo json_encode(['status' => $ok ? 'success' : 'error', 'message' => $msg]);