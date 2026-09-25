<?php
session_start();
header('Content-Type: application/json');
include 'db_config.php';

if (!isset($_SESSION['teacher_id'])) {
    echo json_encode([]);
    exit;
}

$teacher_id = $_SESSION['teacher_id'];

$sql = "SELECT id, title, description, event_datetime, creator_role
        FROM calendar_events
        WHERE teacher_id = ? OR teacher_id IS NULL
        ORDER BY event_datetime ASC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $teacher_id);
$stmt->execute();
$result = $stmt->get_result();

// Role ke hisaab se color
$colors = [
    'teacher'      => '#0d6efd', // blue  - apne events
    'admin'        => '#fd7e14', // orange- admin ke events
    'branch_admin' => '#6f42c1'  // purple- branch admin ke events
];

$events = [];
while ($row = $result->fetch_assoc()) {
    $role = $row['creator_role'];
    $events[] = [
        'id'          => $row['id'],
        'title'       => $row['title'],
        'start'       => $row['event_datetime'],   // FullCalendar 'start'
        'description' => $row['description'],
        'creatorRole' => $role,                    // extendedProps me chala jayega
        'color'       => $colors[$role] ?? '#0d6efd'
    ];
}

echo json_encode($events);