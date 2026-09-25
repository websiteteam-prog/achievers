<?php
session_start();
header('Content-Type: application/json');
include 'db_config.php';

if (!isset($_SESSION['teacher_id'])) {
    echo json_encode([]);
    exit;
}

$teacher_id = $_SESSION['teacher_id'];

$branch_id = 0;
$bstmt = $conn->prepare(
    "SELECT b.id AS branch_id
     FROM teachers t
     JOIN branches b ON t.branch = b.branch_name
     WHERE t.id = ?"
);
if ($bstmt) {
    $bstmt->bind_param("i", $teacher_id);
    $bstmt->execute();
    $bres = $bstmt->get_result();
    if ($brow = $bres->fetch_assoc()) $branch_id = (int)$brow['branch_id'];
}

$colors = [
    'teacher'      => '#1e88e5', // blue
    'admin'        => '#e63946', // red
    'branch_admin' => '#0f766e', // teal
];

/*
 * Teacher ko dikhega:
 *  - apne events / apne ko target kiye gaye events   (teacher_id = self)
 *  - admin ke global events                          (admin + teacher_id NULL)
 *  - branch admin ke poori-branch events             (branch_admin + teacher_id NULL + same branch)
 */
$sql = "SELECT id, title, description, event_datetime, creator_role
        FROM calendar_events
        WHERE teacher_id = ?
           OR (creator_role = 'admin' AND teacher_id IS NULL)
           OR (creator_role = 'branch_admin' AND teacher_id IS NULL AND branch_id = ?)
        ORDER BY event_datetime ASC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $teacher_id, $branch_id);
$stmt->execute();
$res = $stmt->get_result();

$events = [];
while ($row = $res->fetch_assoc()) {
    $role = $row['creator_role'];
    $events[] = [
        'id'          => $row['id'],
        'title'       => $row['title'],
        'start'       => str_replace(' ', 'T', $row['event_datetime']),
        'description' => $row['description'],
        'creatorRole' => $role,
        'color'       => $colors[$role] ?? $colors['teacher'],
        'display'     => 'block',
    ];
}

echo json_encode($events);