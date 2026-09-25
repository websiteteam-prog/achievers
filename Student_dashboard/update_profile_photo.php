<?php
session_start();
include "../db_config.php";

if (!isset($_SESSION['student_id'])) {
    header("Location: student_login.php");
    exit();
}

$student_id = (int) $_SESSION['student_id'];

if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] === UPLOAD_ERR_OK) {

    $file = $_FILES['profile_photo'];
    $allowed_types = ['image/jpeg', 'image/png', 'image/webp'];
    $max_size = 3 * 1024 * 1024; // 3MB

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime  = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (in_array($mime, $allowed_types) && $file['size'] <= $max_size) {

        $ext_map = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
        $ext = $ext_map[$mime];

        $target_dir = "uploads/profile_photos/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0755, true);
        }

        $filename = "student_{$student_id}_" . time() . "." . $ext;
        $target_path = $target_dir . $filename;

        if (move_uploaded_file($file['tmp_name'], $target_path)) {

            $safe_path = mysqli_real_escape_string($conn, $target_path);

            $check = mysqli_query($conn, "SELECT id FROM student_images WHERE student_id = $student_id");

            if ($check && mysqli_num_rows($check) > 0) {
                mysqli_query($conn, "UPDATE student_images SET image_path = '$safe_path' WHERE student_id = $student_id");
            } else {
                mysqli_query($conn, "INSERT INTO student_images (student_id, image_path) VALUES ($student_id, '$safe_path')");
            }
        }
    }
}

header("Location: student_dashboard.php");
exit();