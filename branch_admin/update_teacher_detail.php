<?php
require_once __DIR__ . '/auth.php';
require_branch_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect_with_flash('danger', 'Invalid request.');
}

$column     = $_POST['detail'] ?? '';
$teacher_id = (int) ($_POST['teacher_id'] ?? 0);
$new_value  = trim($_POST['new_value'] ?? '');
$branch_id  = $_SESSION['branch_id'];

$allowed_columns = ['name', 'email', 'contact_no', 'branch'];
if (!in_array($column, $allowed_columns, true)) {
    redirect_with_flash('danger', 'Invalid field selected.');
}
if ($teacher_id <= 0 || $new_value === '') {
    redirect_with_flash('danger', 'Please provide a valid teacher ID and value.');
}

// Resolve this admin's branch name, then ensure the teacher belongs to it
$bn = $conn->prepare("SELECT branch_name FROM branches WHERE id = ?");
$bn->bind_param("i", $branch_id);
$bn->execute();
$branchName = $bn->get_result()->fetch_assoc()['branch_name'] ?? '';
$bn->close();

$check = $conn->prepare("SELECT id FROM teachers WHERE id = ? AND branch = ?");
$check->bind_param("is", $teacher_id, $branchName);
$check->execute();
$check->store_result();
if ($check->num_rows === 0) {
    $check->close();
    redirect_with_flash('danger', 'Teacher ID does not exist in your branch.');
}
$check->close();

try {
    $sql = "UPDATE teachers SET `$column` = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $new_value, $teacher_id);
    $stmt->execute();
    redirect_with_flash('success', 'Teacher detail updated successfully.');
} catch (mysqli_sql_exception $e) {
    redirect_with_flash('danger', 'Failed to update teacher detail.');
}
