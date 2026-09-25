<?php
session_start();
header('Content-Type: application/json');
include '../db_config.php';

if (!isset($_SESSION['admin_logged_in'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$id = $_POST['id'] ?? 0;

$sql = "DELETE FROM calendar_events WHERE id = ? AND creator_role = 'admin'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);

if ($stmt->execute() && $stmt->affected_rows > 0) {
    echo json_encode(['status' => 'success', 'message' => 'Event deleted.']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Could not delete event.']);
}
?>