<?php
date_default_timezone_set('Asia/Kolkata');
include '../db_config.php';
header('Content-Type: application/json');

$threshold = date("Y-m-d H:i:s", strtotime("-5 minutes"));

$sql = "
    SELECT 
        name, 
        subject, 
        last_activity,
        CASE WHEN last_activity >= '$threshold' THEN 1 ELSE 0 END AS is_online
    FROM teachers
    ORDER BY is_online DESC, (last_activity IS NULL), last_activity DESC
    LIMIT 6
";

$result = mysqli_query($conn, $sql);
$teachers = [];

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $teachers[] = [
            "name"      => $row['name']    ?: 'Unknown',
            "subject"   => $row['subject']  ?: '',
            "online"    => (int)$row['is_online'] === 1,
            "last_seen" => $row['last_activity']
        ];
    }
    echo json_encode(["success" => true, "data" => $teachers]);
} else {
    echo json_encode(["success" => false, "message" => "Database error"]);
}
?>