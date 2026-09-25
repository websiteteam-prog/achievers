<?php
include '../db_config.php';

$teacher_id = (int)($_POST['id'] ?? 0);

if ($teacher_id <= 0) {
    echo json_encode(['status' => false, 'message' => 'Invalid teacher id.']);
    exit;
}

/* SOFT DELETE — teacher ko list se hatao bina uski class/attendance
   history todhe. FK constraint (class_sessions) block nahi karega. */
try {
    $stmt = $conn->prepare("UPDATE teachers SET status='deleted' WHERE id = ?");
    $stmt->bind_param("i", $teacher_id);
    $stmt->execute();
    echo json_encode(['status' => true, 'message' => 'Teacher removed successfully']);
} catch (mysqli_sql_exception $e) {
    echo json_encode(['status' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>