<?php
require_once __DIR__ . '/auth.php';
require_branch_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect_with_flash('danger', 'Invalid request.');
}

$name       = trim($_POST['name'] ?? '');
$email      = trim($_POST['email'] ?? '');
$password   = $_POST['password'] ?? '';
$subjects   = $_POST['subject'] ?? [];
$branch     = trim($_POST['branch'] ?? '');
$contact_no = trim($_POST['contact_no'] ?? '');

if ($name === '' || $email === '' || $password === '' || $branch === '' || $contact_no === '' || empty($subjects)) {
    redirect_with_flash('danger', 'Please fill all teacher details.');
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    redirect_with_flash('danger', 'Please enter a valid email address.');
}

// Reject duplicate email
$dup = $conn->prepare("SELECT id FROM teachers WHERE email = ?");
$dup->bind_param("s", $email);
$dup->execute();
$dup->store_result();
if ($dup->num_rows > 0) {
    $dup->close();
    redirect_with_flash('danger', 'This email is already registered. Please use a different one.');
}
$dup->close();

$hash = password_hash($password, PASSWORD_DEFAULT);

$conn->begin_transaction();
try {
    $stmt = $conn->prepare("INSERT INTO teachers (name, email, password, branch, contact_no) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $name, $email, $hash, $branch, $contact_no);
    $stmt->execute();
    $teacher_id = $stmt->insert_id;
    $stmt->close();

    $map = $conn->prepare("INSERT INTO teacher_subjects (teacher_id, subject_id) VALUES (?, ?)");
    foreach ($subjects as $subject_id) {
        $sid = (int) $subject_id;
        $map->bind_param("ii", $teacher_id, $sid);
        $map->execute();
    }
    $map->close();

    $conn->commit();
    redirect_with_flash('success', 'Teacher added successfully.');
} catch (mysqli_sql_exception $e) {
    $conn->rollback();
    redirect_with_flash('danger', 'Failed to add teacher. Please try again.');
}
