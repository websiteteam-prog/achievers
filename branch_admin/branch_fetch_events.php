<?php
require_once __DIR__ . '/auth.php';
require_branch_admin();
header('Content-Type: application/json');

$branch_id = $_SESSION['branch_id'];

$sql = "SELECT e.id, e.title, e.description, e.event_datetime, e.teacher_id, t.name AS teacher_name
        FROM calendar_events e
        LEFT JOIN teachers t ON t.id = e.teacher_id
        WHERE e.creator_role = 'branch_admin' AND e.branch_id = ?
        ORDER BY e.event_datetime ASC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $branch_id);
$stmt->execute();
$res = $stmt->get_result();

$events = [];
while ($row = $res->fetch_assoc()) {
    $isAll = is_null($row['teacher_id']);
    $events[] = [
        'id'          => $row['id'],
        'title'       => $row['title'],
        'start'       => str_replace(' ', 'T', $row['event_datetime']),
        'description' => $row['description'],
        'teacherId'   => $row['teacher_id'],
        'teacherName' => $row['teacher_name'],
        // all-branch = teal, specific teacher = blue
        'color'       => $isAll ? '#0f766e' : '#1e88e5',
        'display'     => 'block',
    ];
}

echo json_encode($events);