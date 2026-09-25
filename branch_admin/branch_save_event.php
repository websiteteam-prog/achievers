<?php
require_once __DIR__ . '/auth.php';
require_branch_admin();
header('Content-Type: application/json');

$branch_id  = $_SESSION['branch_id'];
$creator_id = $_SESSION['user_id'] ?? 0;   // 👈 apne branch-admin id session key se adjust karo

$event_id    = $_POST['event_id'] ?? '';
$title       = trim($_POST['title'] ?? '');
$date        = $_POST['date'] ?? '';
$time        = $_POST['time'] ?? '';
$description = $_POST['description'] ?? '';
$audience    = $_POST['audience'] ?? 'branch';

if (!$title || !$date || !$time) {
    echo json_encode(['status' => 'error', 'message' => 'Please fill all required fields.']);
    exit;
}

// audience: branch = poori branch (teacher_id NULL); teacher = ek specific teacher
$teacher_id = null;
if ($audience === 'teacher') {
    $teacher_id = !empty($_POST['target_teacher']) ? intval($_POST['target_teacher']) : null;
    if (!$teacher_id) {
        echo json_encode(['status' => 'error', 'message' => 'Please select a teacher.']);
        exit;
    }
    $chk = $conn->prepare(
        "SELECT t.id FROM teachers t
         JOIN branches b ON t.branch = b.branch_name
         WHERE t.id = ? AND b.id = ?"
    );
    $chk->bind_param("ii", $teacher_id, $branch_id);
    $chk->execute();
    if (!$chk->get_result()->fetch_assoc()) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid teacher for this branch.']);
        exit;
    }
}

$datetime = $date . ' ' . $time;

if (!empty($event_id)) {
    // UPDATE (sirf apni branch ke events)
    $sql = "UPDATE calendar_events
            SET title = ?, description = ?, event_datetime = ?, teacher_id = ?
            WHERE id = ? AND creator_role = 'branch_admin' AND branch_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssiii", $title, $description, $datetime, $teacher_id, $event_id, $branch_id);
    $ok = $stmt->execute();
    $msg = $ok ? 'Event updated successfully.' : 'Failed to update event.';
} else {
    // INSERT
    $sql = "INSERT INTO calendar_events
            (title, description, event_datetime, creator_role, creator_id, teacher_id, branch_id)
            VALUES (?, ?, ?, 'branch_admin', ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssiii", $title, $description, $datetime, $creator_id, $teacher_id, $branch_id);
    $ok = $stmt->execute();
    $msg = $ok ? 'Event added successfully.' : 'Failed to add event.';
}

echo json_encode(['status' => $ok ? 'success' : 'error', 'message' => $msg]);