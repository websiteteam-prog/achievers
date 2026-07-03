<?php
require_once __DIR__ . '/auth.php';

if (!is_branch_admin()) {
    json_response(false, 'Unauthorized.');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(false, 'Invalid request method.');
}

$branch_id = $_SESSION['branch_id'];
$date   = trim($_POST['date'] ?? '');
$title  = trim($_POST['title'] ?? '');
$agenda = trim($_POST['agenda'] ?? '');

if ($date === '' || $title === '' || $agenda === '') {
    json_response(false, 'Please fill all the details.');
}

try {
    $stmt = $conn->prepare("INSERT INTO events (branch_id, event_name, description, date) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $branch_id, $title, $agenda, $date);
    $stmt->execute();
    json_response(true, 'Event added successfully.');
} catch (mysqli_sql_exception $e) {
    json_response(false, 'Event could not be added.');
}
