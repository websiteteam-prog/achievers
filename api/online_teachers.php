<?php
include '../db_config.php';

header('Content-Type: application/json');
date_default_timezone_set('Asia/Kolkata');

$sql = "
SELECT COUNT(*) AS online_count
FROM teachers
WHERE last_activity IS NOT NULL
AND last_activity >= DATE_SUB(NOW(), INTERVAL 5 MINUTE)
";

$result = mysqli_query($conn, $sql);

if ($result) {
    $row = mysqli_fetch_assoc($result);

    echo json_encode([
        "success" => true,
        "data" => (int)$row['online_count']
    ]);
} else {
    echo json_encode([
        "success" => false,
        "message" => mysqli_error($conn)
    ]);
}