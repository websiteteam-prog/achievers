<?php
require_once __DIR__ . '/auth.php';
require_branch_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect_with_flash('danger', 'Invalid request.');
}

$subject_id = (int) ($_POST['subject'] ?? 0);
$student_id = (int) ($_POST['student_id'] ?? 0);
$action     = $_POST['action'] ?? '';
$branch_id  = $_SESSION['branch_id'];

if ($subject_id <= 0 || $student_id <= 0) {
    redirect_with_flash('danger', 'Please provide a valid student ID and subject.');
}

// Student must belong to this branch
$check = $conn->prepare("SELECT id FROM students WHERE id = ? AND branch_id = ?");
$check->bind_param("ii", $student_id, $branch_id);
$check->execute();
$check->store_result();
$exists = $check->num_rows > 0;
$check->close();
if (!$exists) {
    redirect_with_flash('danger', 'Student ID does not exist in your branch.');
}

try {
    if ($action === 'add') {
        $dup = $conn->prepare("SELECT student_id FROM student_subjects WHERE student_id = ? AND subject_id = ?");
        $dup->bind_param("ii", $student_id, $subject_id);
        $dup->execute();
        $dup->store_result();
        $already = $dup->num_rows > 0;
        $dup->close();
        if ($already) {
            redirect_with_flash('warning', 'That subject is already assigned to the student.');
        }
        $stmt = $conn->prepare("INSERT INTO student_subjects (student_id, subject_id) VALUES (?, ?)");
        $stmt->bind_param("ii", $student_id, $subject_id);
        $stmt->execute();
        redirect_with_flash('success', 'Subject added successfully.');
    } elseif ($action === 'delete') {
        $stmt = $conn->prepare("DELETE FROM student_subjects WHERE student_id = ? AND subject_id = ?");
        $stmt->bind_param("ii", $student_id, $subject_id);
        $stmt->execute();
        redirect_with_flash('success', 'Subject removed successfully.');
    } else {
        redirect_with_flash('danger', 'Invalid action.');
    }
} catch (mysqli_sql_exception $e) {
    redirect_with_flash('danger', 'Could not update the student subjects.');
}
