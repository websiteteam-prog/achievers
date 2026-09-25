<?php
session_start();
header('Content-Type: application/json');
include '../db_config.php';

if (!isset($_SESSION['admin_logged_in'])) {
    echo json_encode([]);
    exit;
}

// Admin ko admin-created saare events dikhenge
$sql = "SELECT e.id, e.title, e.description, e.event_datetime, e.teacher_id, t.name AS teacher_name
        FROM calendar_events e
        LEFT JOIN teachers t ON t.id = e.teacher_id
        WHERE e.creator_role = 'admin'
        ORDER BY e.event_datetime ASC";

$result = $conn->query($sql);

$events = [];
while ($row = $result->fetch_assoc()) {
    $isGlobal = is_null($row['teacher_id']);
    $events[] = [
        'id'          => $row['id'],
        'title'       => $row['title'],
        'start'       => str_replace(' ', 'T', $row['event_datetime']),
        'description' => $row['description'],
        'teacherId'   => $row['teacher_id'],
        'teacherName' => $row['teacher_name'],
        // global (all teachers) = red, kisi ek teacher ko = blue
        'color'       => $isGlobal ? '#e63946' : '#1e88e5',
        'display'     => 'block',
    ];
}

echo json_encode($events);