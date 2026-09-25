<?php
require_once __DIR__ . '/auth.php';
require_branch_admin();
header('Content-Type: application/json');

$branch_id = $_SESSION['branch_id'];
$id = $_POST['id'] ?? 0;

$sql = "DELETE FROM calendar_events
        WHERE id = ? AND creator_role = 'branch_admin' AND branch_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $id, $branch_id);

if ($stmt->execute() && $stmt->affected_rows > 0) {
    echo json_encode(['status' => 'success', 'message' => 'Event deleted.']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Could not delete event.']);
}