<?php
require_once __DIR__ . '/auth.php';
require_branch_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect_with_flash('danger', 'Invalid request.');
}

$column     = $_POST['detail'] ?? '';
$student_id = (int) ($_POST['student_id'] ?? 0);
$new_value  = trim($_POST['new_value'] ?? '');
$branch_id  = $_SESSION['branch_id'];

$allowed_columns = ['first_name', 'parent_email', 'grade', 'email', 'phone', 'parent_name', 'parent_contact', 'address', 'mode_of_education'];
if (!in_array($column, $allowed_columns, true)) {
    redirect_with_flash('danger', 'Invalid field selected.');
}
if ($student_id <= 0 || $new_value === '') {
    redirect_with_flash('danger', 'Please provide a valid student ID and value.');
}

// Student must belong to this branch
$check = $conn->prepare("SELECT id FROM students WHERE id = ? AND branch_id = ?");
$check->bind_param("ii", $student_id, $branch_id);
$check->execute();
$check->store_result();
if ($check->num_rows === 0) {
    $check->close();
    redirect_with_flash('danger', 'Student ID does not exist in your branch.');
}
$check->close();

try {
    $sql = "UPDATE students SET `$column` = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $new_value, $student_id);
    $stmt->execute();
    redirect_with_flash('success', 'Student detail updated successfully.');
} catch (mysqli_sql_exception $e) {
    redirect_with_flash('danger', 'Failed to update student detail.');
}
