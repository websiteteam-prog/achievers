<?php
require_once __DIR__ . '/auth.php';
require_branch_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect_with_flash('danger', 'Invalid request.');
}

$subject_id = (int) ($_POST['subject'] ?? 0);
$teacher_id = (int) ($_POST['teacher_id'] ?? 0);
$action     = $_POST['action'] ?? '';
$branch_id  = $_SESSION['branch_id'];

if ($subject_id <= 0 || $teacher_id <= 0) {
    redirect_with_flash('danger', 'Please provide a valid teacher ID and subject.');
}

// Resolve branch name and ensure the teacher belongs to it
$bn = $conn->prepare("SELECT branch_name FROM branches WHERE id = ?");
$bn->bind_param("i", $branch_id);
$bn->execute();
$branchName = $bn->get_result()->fetch_assoc()['branch_name'] ?? '';
$bn->close();

$check = $conn->prepare("SELECT id FROM teachers WHERE id = ? AND branch = ?");
$check->bind_param("is", $teacher_id, $branchName);
$check->execute();
$check->store_result();
$exists = $check->num_rows > 0;
$check->close();
if (!$exists) {
    redirect_with_flash('danger', 'Teacher ID does not exist in your branch.');
}

try {
    if ($action === 'add') {
        $dup = $conn->prepare("SELECT teacher_id FROM teacher_subjects WHERE teacher_id = ? AND subject_id = ?");
        $dup->bind_param("ii", $teacher_id, $subject_id);
        $dup->execute();
        $dup->store_result();
        $already = $dup->num_rows > 0;
        $dup->close();
        if ($already) {
            redirect_with_flash('warning', 'That subject is already assigned to the teacher.');
        }
        $stmt = $conn->prepare("INSERT INTO teacher_subjects (teacher_id, subject_id) VALUES (?, ?)");
        $stmt->bind_param("ii", $teacher_id, $subject_id);
        $stmt->execute();
        redirect_with_flash('success', 'Subject added successfully.');
    } elseif ($action === 'delete') {
        $stmt = $conn->prepare("DELETE FROM teacher_subjects WHERE teacher_id = ? AND subject_id = ?");
        $stmt->bind_param("ii", $teacher_id, $subject_id);
        $stmt->execute();
        redirect_with_flash('success', 'Subject removed successfully.');
    } else {
        redirect_with_flash('danger', 'Invalid action.');
    }
} catch (mysqli_sql_exception $e) {
    redirect_with_flash('danger', 'Could not update the teacher subjects.');
}
