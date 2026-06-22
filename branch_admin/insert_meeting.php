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
$id     = (int) ($_POST['id'] ?? 0);
$agenda = trim($_POST['agenda'] ?? '');

if ($date === '' || $id <= 0 || $agenda === '') {
    json_response(false, 'Please fill all the details.');
}

try {
    // Ensure the student belongs to this branch
    $check = $conn->prepare("SELECT id FROM students WHERE id = ? AND branch_id = ?");
    $check->bind_param("ii", $id, $branch_id);
    $check->execute();
    $check->store_result();
    if ($check->num_rows === 0) {
        $check->close();
        json_response(false, 'Student ID not found in your branch.');
    }
    $check->close();

    $stmt = $conn->prepare("INSERT INTO meetings (students_id, agenda, date) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $id, $agenda, $date);
    $stmt->execute();
    json_response(true, 'Meeting scheduled successfully.');
} catch (mysqli_sql_exception $e) {
    json_response(false, 'Failed to schedule meeting.');
}
