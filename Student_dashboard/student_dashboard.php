<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include "../db_config.php";
include "student_sidebar.php";

if (!isset($_SESSION['student_id'])) {
    header("Location: ../student_login.php");
    exit();
}

$student_id = $_SESSION['student_id'];

// Fetch profile picture
$sql_img = "SELECT image_path FROM student_images WHERE student_id = " . (int)$student_id;
$res_img = mysqli_query($conn, $sql_img);
$student_img = $res_img ? mysqli_fetch_assoc($res_img) : null;

// Fetch student info (phone, dob, address added for the profile card)
$sql_student = "SELECT first_name, last_name, email, grade, phone, dob, address FROM students WHERE id = " . (int)$student_id;
$res_student = mysqli_query($conn, $sql_student);
$student = $res_student ? mysqli_fetch_assoc($res_student) : null;

// Subjects count
$subjects_count = 0;
$sql_sub = "SELECT COUNT(*) as cnt FROM student_subjects WHERE student_id = " . (int)$student_id;
$res_sub = mysqli_query($conn, $sql_sub);
if ($res_sub) $subjects_count = (int) mysqli_fetch_assoc($res_sub)['cnt'];

$materials_count = 0;
$course_ids = [];
$sql_courses = "SELECT c.id AS course_id
                FROM student_subjects ss
                JOIN courses c ON c.subject_id = ss.subject_id
                WHERE ss.student_id = " . (int)$student_id;
$res_courses = mysqli_query($conn, $sql_courses);
while ($r = mysqli_fetch_assoc($res_courses)) $course_ids[] = (int)$r['course_id'];
$course_ids = array_values(array_unique($course_ids));
if (!empty($course_ids)) {
    $ids = implode(',', $course_ids);
    $sql_mat = "SELECT COUNT(*) as cnt FROM course_materials WHERE course_id IN ($ids)";
    $res_mat = mysqli_query($conn, $sql_mat);
    if ($res_mat) $materials_count = (int) mysqli_fetch_assoc($res_mat)['cnt'];
}

// This month's attendance percentage
$attendance_percentage = 0;
$month_start = date('Y-m-01');
$month_end   = date('Y-m-t');
$sql_att = "SELECT 
    SUM(CASE 
        WHEN status = 'Present' OR status = 'Late' THEN 1 
        ELSE 0 
        END) AS attended,
        COUNT(*) AS total
    FROM attendance_records 
    WHERE student_id = " . (int)$student_id . " 
    AND date BETWEEN '$month_start' AND '$month_end'";
$res_att = mysqli_query($conn, $sql_att);
if ($res_att && $row = mysqli_fetch_assoc($res_att)) {
    if ($row['total'] > 0) {
        $attendance_percentage = round(($row['attended'] / $row['total']) * 100, 1);
    }
}

// Fetch latest announcements
$announcements = [];

$sql_ann = "
    SELECT id, title, message, created_at
    FROM announcements
    ORDER BY created_at DESC
    LIMIT 5
";

$res_ann = mysqli_query($conn, $sql_ann);

if ($res_ann) {
    while ($row = mysqli_fetch_assoc($res_ann)) {
        $announcements[] = $row;
    }
}

$notif_count = count($announcements);

$student_full_name = trim(($student['first_name'] ?? 'Student') . ' ' . ($student['last_name'] ?? ''));
$student_avatar = htmlspecialchars($student_img['image_path'] ?? '../images/default-avatar.png');
$dob_formatted = (!empty($student['dob']) && $student['dob'] !== '0000-00-00')
    ? date('d F Y', strtotime($student['dob']))
    : '—';
$dob_input_value = (!empty($student['dob']) && $student['dob'] !== '0000-00-00')
    ? date('Y-m-d', strtotime($student['dob']))
    : '';

// Fetch extra profile details from enrollment_inquiries (linked via student_id)
$sql_enroll = "SELECT * FROM enrollment_inquiries 
               WHERE student_id = " . (int)$student_id . " 
               ORDER BY id DESC LIMIT 1";
$res_enroll = mysqli_query($conn, $sql_enroll);
$enroll_info = $res_enroll ? mysqli_fetch_assoc($res_enroll) : null;    
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Achiever's Castle • Dashboard</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Love+Ya+Like+A+Sister&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary: #1e40af;
            --primary-light: #3b82f6;
            --primary-dark: #1e3a8a;
            --accent: #ef4444;
            --light-bg: #f5f7fb;
            --card-bg: #ffffff;
            --text: #1f2937;
            --gray: #6b7280;
            --shadow: 0 6px 20px rgba(0, 0, 0, 0.06);
            --shadow-hover: 0 12px 32px rgba(0, 0, 0, 0.1);
        }

        * {
            box-sizing: border-box;
        }

        html, body {
            max-width: 100%;
            overflow-x: hidden;
        }

        body {
            background: var(--light-bg);
            color: var(--text);
            font-family: system-ui, -apple-system, sans-serif;
        }

        .main-content {
            margin-left: 270px;
            padding: 28px 40px 40px;
            width: auto;
            min-height: 100vh;
        }

        /* ============ TOP BAR ============ */
        .topbar {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            gap: 22px;
            margin-bottom: 22px;
        }

        .bell-btn {
            position: relative;
            width: 46px;
            height: 46px;
            border-radius: 50%;
            background: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: var(--shadow);
            color: var(--text);
            font-size: 20px;
            border: none;
            flex-shrink: 0;
        }

        .bell-btn .bell-count {
            position: absolute;
            top: -4px;
            right: -4px;
            background: var(--accent);
            color: #fff;
            font-size: 0.7rem;
            font-weight: 700;
            min-width: 20px;
            height: 20px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 6px rgba(239, 68, 68, 0.45);
        }

        .topbar-profile {
            display: flex;
            align-items: center;
            gap: 12px;
            background: #fff;
            padding: 6px 16px 6px 6px;
            border-radius: 50px;
            box-shadow: var(--shadow);
            cursor: pointer;
            border: none;
            max-width: 100%;
        }

        .topbar-profile img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            flex-shrink: 0;
        }

        .topbar-profile .profile-content {
            min-width: 0;
        }

        .topbar-profile .name {
            font-weight: 700;
            font-size: 14px;
            line-height: 1.1;
            color: var(--text);
            text-align: left;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 120px;
        }

        .topbar-profile .role {
            font-size: 12px;
            color: var(--gray);
        }

        /* ============ DASHBOARD HEADER ============ */
        .dashboard-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 25px;
            margin-bottom: 35px;
            margin-top: -10px;
            flex-wrap: wrap;
        }

        .dashboard-illustration {
            width: 180px;
            flex-shrink: 0;
        }

        .dashboard-illustration img {
            width: 180px;
            max-width: 100%;
            height: auto;
        }

        .welcome-text {
            flex: 1 1 240px;
            display: flex;
            justify-content: center;
            align-items: center;
            min-width: 0;
        }

        .welcome-gradient{
        font-size: 42px;
        font-weight: 400;
        margin-bottom: 6px !important;
        background: linear-gradient(to right, #e02121, #2f55a4);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        color: transparent;
        font-family: "Love Ya Like A Sister", cursive;
        margin-left: 8px;
        }

        .student-name{
            font-size: 42px;
            font-weight: 400;
            margin-bottom: 6px !important;
            color: var(--primary-light); /* Same blue as before */
            font-family: "Love Ya Like A Sister", cursive;
        }

        .welcome-title {
            font-family: 'Love Ya Like A Sister', cursive;
            font-size: clamp(26px, 3vw, 52px);
            font-weight: 800;
            margin: 0;
            line-height: 1.15;
            text-align: center;
            word-break: break-word;
        }

        .welcome-title span {
            color: var(--primary-light);
        }

        .dashboard-actions {
            flex-shrink: 0;
            display: flex;
            align-items: center;
            gap: 18px;
        }

        .profile-arrow {
            margin-left: 6px;
            font-size: 15px;
            transition: .3s;
        }

        .topbar-profile.show .profile-arrow {
            transform: rotate(180deg);
        }

        .profile-dropdown {
            width: 230px;
            border: none;
            border-radius: 15px;
            padding: 10px;
        }

        .profile-dropdown .dropdown-item {
            border-radius: 10px;
            padding: 12px 14px;
            font-weight: 500;
            transition: .25s;
        }

        .profile-dropdown .dropdown-item:hover {
            background: #eef4ff;
            color: #2563eb;
        }

        .profile-dropdown .dropdown-item.text-danger:hover {
            background: #fff1f2;
            color: #dc2626 !important;
        }

        /* ============ QUICK ACTIONS ============ */
        .quick-actions-grid {
            display: grid;
            grid-template-columns: repeat(6, 1fr);
            gap: 18px;
            margin-bottom: 40px;
        }

        .quick-action {
            background: #fff;
            border: none;
            border-radius: 14px;
            padding: 1.3rem 1rem;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
            text-decoration: none;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.06);
            transition: 0.25s;
            cursor: pointer;
        }

        .quick-action i {
            font-size: 26px;
            color: var(--accent);
            transition: 0.25s;
        }

        .quick-action span {
            font-size: 14px;
            font-weight: 600;
            text-align: center;
            color: var(--text);
            transition: 0.25s;
        }

        .quick-action:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 24px rgba(0, 0, 0, 0.12);
            background: linear-gradient(135deg, #1e3c72, #2a5298);
        }

        .quick-action:hover i,
        .quick-action:hover span {
            color: #fff;
        }

        /* ============ PROFILE SECTION ============ */
        .section-title {
            font-size: 1.2rem;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 1rem;
            padding-left: 0.6rem;
            border-left: 4px solid var(--accent);
        }

        .profile-card {
            background: #fff;
            border-radius: 22px;
            padding: 35px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 35px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, .08);
            flex-wrap: wrap;
        }

        .avatar-upload-wrap {
            position: relative;
            flex-shrink: 0;
            width: 140px;
            height: 140px;
        }

        .profile-img {
            width: 140px;
            height: 140px;
            border-radius: 50%;
            object-fit: cover;
        }

        .avatar-edit-btn {
            position: absolute;
            bottom: 2px;
            right: 2px;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: var(--primary-light);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.25);
            cursor: pointer;
            border: 3px solid #fff;
            font-size: 15px;
            transition: 0.2s;
        }

        .avatar-edit-btn:hover {
            background: var(--primary-dark);
        }

        .profile-info {
            flex: 1 1 240px;
            min-width: 0;
        }

        .profile-info h4 {
            font-weight: 800;
            margin-bottom: 12px;
            word-break: break-word;
        }

        .profile-info p {
            margin-bottom: 10px;
            color: #334155;
            word-break: break-word;
        }

        .profile-info i {
            color: var(--primary-light);
            width: 20px;
        }

        /* .edit-profile-btn {
            background: #e8f0fe;
            color: var(--primary);
            border: none;
            font-weight: 600;
            padding: 12px 28px;
            border-radius: 12px;
            white-space: nowrap;
        }

        .edit-profile-btn:hover {
            background: #d7e6fd;
            color: var(--primary-dark);
        } */

        .edit-profile-btn {
        background: var(--primary-light);
        color: #fff;
        border: none;
        font-weight: 600;
        font-size: 14px;
        padding: 12px 26px;
        border-radius: 50px;
        white-space: nowrap;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        box-shadow: 0 4px 14px rgba(30, 60, 114, 0.3);
        transition: 0.25s;
        }

        .edit-profile-btn i {
            font-size: 16px;
        }

        .edit-profile-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(30, 60, 114, 0.4);
            color: var(--primary-light);
        }    

        /* ============ MISC (materials / footer / chart) ============ */
        .card-material {
            height: 100%;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: var(--shadow);
            transition: all 0.3s;
        }

        .card-material .p-3 {
            height: 100%;
            min-height: 300px;
            display: flex;
            flex-direction: column;
        }

        .material-body {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
        }

        .card-material:hover {
            transform: translateY(-6px);
            box-shadow: var(--shadow-hover);
        }

        footer {
            margin-top: 90px;
            padding: 35px 0;
            text-align: center;
            color: var(--gray);
            border-top: 1px solid #e5e7eb;
        }

        .chart-container {
            position: relative;
            width: 100%;
            height: clamp(160px, 25vw, 320px);
        }

        .chart-container canvas {
            width: 100% !important;
            height: 100% !important;
        }

        /* ============================================================
           RESPONSIVE BREAKPOINTS
           ============================================================ */

        /* Large desktops → 4 columns */
        @media (max-width: 1400px) {
            .quick-actions-grid {
                grid-template-columns: repeat(4, 1fr);
            }
        }

        /* Small desktops / large tablets landscape → 3 columns */
        @media (max-width: 1200px) {
            .quick-actions-grid {
                grid-template-columns: repeat(3, 1fr);
            }

            .dashboard-illustration {
                width: 150px;
            }

            .dashboard-illustration img {
                width: 150px;
            }
        }

        /* Tablets — sidebar collapses to off-canvas */
        @media (max-width: 992px) {
            .main-content {
                margin-left: 0;
                padding: 80px 24px 28px;
            }

            .dashboard-header {
                flex-wrap: wrap;
                gap: 16px;
                margin-top: 0;
            }
           /* Actions pinned to the top-right */
            .dashboard-actions {
                order: 1;
                margin-left: auto;
            }

            .dashboard-illustration {
                order: 2;
            }

            /* Greeting drops to its own full-width row */
            .welcome-text {
                order: 3;
                flex: 1 1 100%;
                justify-content: center;
            }

            .welcome-title {
                text-align: center;
            }
        }

        /* Small tablets / large phones */
        @media (max-width: 768px) {
            .dashboard-illustration {
                display: none;
            }

            /* Row 1: bell + profile, full width, aligned right */
            .dashboard-actions {
                order: 1;
                flex: 1 1 100%;
                justify-content: flex-end;
            }

            /* Row 2: greeting, full width, left-aligned */
            .welcome-text {
                order: 2;
                flex: 1 1 100%;
                justify-content: flex-start;
            }

            .welcome-title {
                text-align: left;
                font-size: clamp(24px, 6vw, 34px);
            }

            .profile-card {
                flex-direction: column;
                text-align: center;
                gap: 20px;
                padding: 30px 24px;
            }

            .profile-info {
                flex-basis: 100%;
                text-align: center;
            }

            .profile-info p {
                text-align: left;
                display: inline-block;
            }

            .edit-profile-btn {
                width: 100%;
                max-width: 320px;
            }
        }

        /* Phones */
        @media (max-width: 576px) {
            .main-content {
                padding: 78px 16px 24px;
            }

            .quick-actions-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 12px;
            }

            .quick-action {
                padding: 1rem 0.6rem;
            }

            .quick-action i {
                font-size: 22px;
            }

            .quick-action span {
                font-size: 12.5px;
            }

            .dashboard-actions {
                gap: 12px;
                margin-top: -77px;
            }

            /* Hide the name/role text on tiny screens — avatar + arrow only */
            .topbar-profile .profile-content {
                display: none;
            }

            .topbar-profile {
                padding: 5px;
            }

            .welcome-title {
                font-size: 26px;
            }

            .profile-card {
                padding: 24px 18px;
                border-radius: 18px;
            }

            .profile-img,
            .avatar-upload-wrap {
                width: 110px;
                height: 110px;
            }

            .section-title {
                font-size: 1.15rem;
            }

            footer {
                margin-top: 60px;
                padding: 28px 0;
                font-size: 0.85rem;
            }
        }

        /* Very small phones */
        @media (max-width: 360px) {
            .quick-action span {
                font-size: 11.5px;
            }

            .bell-btn {
                width: 42px;
                height: 42px;
                font-size: 18px;
            }
        }
    </style>
</head>

<body>
<!-- Mobile Sidebar Toggle -->
<button
    class="btn btn-primary d-lg-none position-fixed"
    id="sidebarToggle"
    style="top:15px;left:15px;z-index:1200;border-radius:50%;width:48px;height:48px;">
    <i class="bi bi-list fs-4"></i>
</button>

<div class="container-fluid p-0">
    <div class="d-flex">

        <main class="main-content flex-grow-1">

            <!-- Dashboard Header -->
            <div class="dashboard-header">

                <!-- Left Illustration -->
                <div class="dashboard-illustration">
                    <img src="../images/welcome-illustration.png" alt="Welcome">
                </div>

                <!-- Welcome Text -->
                <div class="welcome-text">
                   <h1 class="welcome-title">
                    <span class="welcome-gradient">Welcome,</span>
                    <span class="student-name">
                        <?= htmlspecialchars($student['first_name'] ?? 'Student') ?>
                    </span>
                </h1>
                </div>

                <!-- Right Actions -->
                <div class="dashboard-actions">

                    <button
                        class="bell-btn"
                        onclick="document.getElementById('announcements').scrollIntoView({behavior:'smooth'})">

                        <i class="bi bi-bell"></i>

                        <?php if($notif_count>0): ?>
                            <span class="bell-count"><?= $notif_count ?></span>
                        <?php endif; ?>

                    </button>

                    <div class="dropdown">

                        <button class="topbar-profile"
                            data-bs-toggle="dropdown"
                            aria-expanded="false">

                            <img src="<?= $student_avatar ?>" alt="Profile">

                            <div class="profile-content">
                                <div class="name">
                                    <?= htmlspecialchars($student['first_name']) ?>
                                </div>

                                <div class="role">Student</div>
                            </div>

                            <i class="bi bi-chevron-down profile-arrow"></i>

                        </button>

                        <ul class="dropdown-menu dropdown-menu-end profile-dropdown shadow">

                            <li>
                                <a class="dropdown-item" href="settings.php#security">
                                    <i class="bi bi-shield-lock me-2"></i>
                                    Change Password
                                </a>
                            </li>

                            <li>
                                <a class="dropdown-item" href="student_documents.php">
                                    <i class="bi bi-file-earmark-arrow-up-fill me-2"></i>
                                    My Documents
                                </a>
                            </li>

                            <li>
                                <a class="dropdown-item" href="settings.php">
                                    <i class="bi bi-gear me-2"></i>
                                    Settings
                                </a>
                            </li>

                            <li><hr class="dropdown-divider"></li>

                            <li>
                                <a class="dropdown-item text-danger" href="student_logout.php">
                                    <i class="bi bi-box-arrow-right me-2"></i>
                                    Logout
                                </a>
                            </li>

                        </ul>

                    </div>
                </div>

            </div>

            <!-- My Profile -->
            <section class="mb-5">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                    <h4 class="section-title mb-0"> My Profile</h4>
                    <!-- <h4 class="section-title mb-0"><i class="bi bi-person-circle me-2"></i> My Profile</h4> -->
                </div>

                <div class="profile-card">
                    <div class="avatar-upload-wrap">
                        <img src="<?= $student_avatar ?>" alt="Profile" class="profile-img">

                        <form action="update_profile_photo.php" method="POST" enctype="multipart/form-data" id="photoForm">
                            <label for="profilePhotoInput" class="avatar-edit-btn" title="Change photo">
                                <i class="bi bi-camera-fill"></i>
                            </label>
                            <input type="file" name="profile_photo" id="profilePhotoInput" accept="image/png, image/jpeg, image/webp" hidden>
                        </form>
                    </div>

                    <div class="profile-info">
                        <h4><?= htmlspecialchars($student_full_name) ?></h4>
                        <p><i class="bi bi-envelope me-2"></i><?= htmlspecialchars($student['email'] ?? '—') ?></p>
                        <p><i class="bi bi-telephone me-2"></i><?= htmlspecialchars($student['phone'] ?? '—') ?></p>
                        <p><i class="bi bi-calendar3 me-2"></i><?= $dob_formatted ?></p>
                        <p class="mb-0"><i class="bi bi-geo-alt me-2"></i><?= htmlspecialchars($student['address'] ?? '—') ?></p>
                    </div>

                    <!-- <button type="button" class="edit-profile-btn btn" data-bs-toggle="modal" data-bs-target="#editProfileModal">
                        <i class="bi bi-pencil me-1"></i> Edit Profile
                    </button> -->

                   <button type="button" class="edit-profile-btn btn" data-bs-toggle="modal" data-bs-target="#viewProfileModal">
                    <i class="bi bi-person-lines-fill"></i> View Profile
                </button>
                </div>
            </section>

            <!-- Quick Actions -->
            <h4 class="section-title"></i>Quick Access</h4>
            <!-- <h4 class="section-title"><i class="bi bi-lightning-charge-fill me-2"></i>Quick Access</h4> -->
            <div class="quick-actions-grid">
                <a href="enrolled_subjects.php" class="quick-action">
                    <i class="bi bi-journal-bookmark-fill"></i>
                    <span>Enrolled Subjects</span>
                </a>
                <a href="student_documents.php" class="quick-action">
                    <i class="bi bi-file-earmark-arrow-up-fill"></i>
                    <span>My Documents</span>
                </a>
                <a href="student_assessments.php" class="quick-action">
                    <i class="bi bi-file-earmark-check-fill"></i>
                    <span>My Assessments</span>
                </a>
                <!-- <a href="#materials" class="quick-action">
                    <i class="bi bi-folder2-open"></i>
                    <span>Study Materials</span>
                </a> -->
                <a href="purchase_history.php" class="quick-action">
                    <i class="bi bi-receipt-cutoff"></i>
                    <span>Purchase History</span>
                </a>
                <!-- <a href="#announcements" class="quick-action">
                    <i class="bi bi-bell-fill"></i>
                    <span>Announcements</span>
                </a> -->
            </div>

            <!-- Enrolled Subjects -->
            <section class="mb-5">
                <h4 class="section-title"><i class="bi bi-journal-bookmark-fill me-2"></i> Enrolled Subjects</h4>
                <?php if ($subjects_count > 0): ?>
                    <div class="row g-3 mt-3 align-items-stretch">
                        <?php
                        $sql_subs = "SELECT s.subject_name 
                                 FROM student_subjects ss 
                                 JOIN subjects s ON ss.subject_id = s.id 
                                 WHERE ss.student_id = " . (int)$student_id;
                        $res_subs = mysqli_query($conn, $sql_subs);
                        while ($sub = mysqli_fetch_assoc($res_subs)):
                        ?>
                            <div class="col-xl-3 col-lg-4 col-sm-6 col-12">
                                <div class="card border-0 shadow-sm text-center py-4 h-100">
                                    <i class="bi bi-book fs-2 text-primary mb-3 d-block"></i>
                                    <h6 class="fw-bold px-2"><?= htmlspecialchars($sub['subject_name']) ?></h6>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <div class="alert alert-light text-center py-5 border-0 shadow-sm">
                        <i class="bi bi-journal-x display-4 text-muted opacity-50"></i>
                        <p class="mt-3 fw-medium">No subjects enrolled yet</p>
                        <small class="text-muted">Explore courses to begin your learning journey</small>
                    </div>
                <?php endif; ?>
            </section>

            <!-- Announcements -->
            <section class="mb-5" id="announcements">
                <h4 class="section-title">
                    <!-- <i class="bi bi-bell-fill me-2"></i> -->
                     Announcements
                </h4>

                <div class="card border-0 shadow-sm p-3 p-md-4">
                    <?php if (!empty($announcements)): ?>
                        <?php foreach ($announcements as $ann): ?>
                            <div class="border-bottom pb-3 mb-3">
                                <h6 class="fw-bold mb-1 text-primary"><?= htmlspecialchars($ann['title']) ?></h6>
                                <p class="mb-1 text-muted small"><?= nl2br(htmlspecialchars($ann['message'])) ?></p>
                                <small class="text-secondary">
                                    <i class="bi bi-clock me-1"></i>
                                    <?= date("d M Y, h:i A", strtotime($ann['created_at'])) ?>
                                </small>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="bi bi-info-circle display-5 text-muted mb-3"></i>
                            <p class="text-muted fw-medium mb-0">No new announcements at this time.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </section>

                <!-- Study Materials -->
                <!-- <section class="mb-5">
                    <h4 class="section-title">
                        <i class="bi bi-folder2-open me-2"></i>
                     Recent Study Materials</h4>

                    <?php
                  
                    $pdf_folder = "../courses_meterial/";

                    // Materials for the subjects this student is enrolled in.
                    $materials = [];
                    if (!empty($course_ids)) {
                        $sql_mats = "SELECT cm.*, s.subject_name
                                 FROM course_materials cm
                                 JOIN courses c ON cm.course_id = c.id
                                 JOIN subjects s ON c.subject_id = s.id
                                 WHERE cm.course_id IN (" . implode(',', $course_ids) . ")
                                 LIMIT 6";
                        $res_mats = mysqli_query($conn, $sql_mats);
                        while ($res_mats && $mat = mysqli_fetch_assoc($res_mats)) {
                            $materials[] = $mat;
                        }
                    }
                    ?>
                    <?php if (!empty($materials)): ?>
                        <div class="row g-4">
                            <?php
                            foreach ($materials as $mat):
                                $path = $mat['file_path'] ?? '';

                                // Full link the browser will open
                                $url = '';
                                if ($path) {
                                    $url = preg_match('#^https?://#i', $path)
                                         ? $path                                              
                                         : rtrim($pdf_folder, '/') . '/' . ltrim($path, '/'); 
                                }

                                $pdf_available = false;
                                if ($url) {
                                    if (preg_match('#^https?://#i', $url)) {
                                        $pdf_available = true;                                 
                                    } else {
                                        $pdf_available = file_exists(__DIR__ . '/' . ltrim($url, '/'));
                                    }
                                }
                            ?>
                                <div class="col-lg-4 col-md-6">
                                    <div class="card-material">
                                        <div class="p-3">                                                                   <h6 class="fw-bold mb-2 text-uppercase"><?= htmlspecialchars($mat['file_name'] ?: 'Material') ?></h6>
                                            <small class="text-muted mb-3 d-block"><?= htmlspecialchars($mat['subject_name'] ?? '—') ?></small>

                                          <?php if ($pdf_available): ?>

                                    <div class="material-body">
                                        <i class="bi bi-file-earmark-pdf display-1 text-danger"></i>
                                        <h4 class="fw-bold mt-3">PDF Available</h4>
                                    </div>
                                
                                    <a href="<?= htmlspecialchars($url) ?>"
                                       target="_blank"
                                       class="btn btn-primary w-100">
                                        <i class="bi bi-box-arrow-up-right me-2"></i> View PDF
                                    </a>
                                
                                <?php else: ?>
                                
                                    <div class="material-body">
                                        <i class="bi bi-file-earmark-x display-1 text-secondary"></i>
                                        <h5 class="text-danger mt-3">PDF not available</h5>
                                    </div>
                                
                                    <button class="btn btn-secondary w-100" disabled>
                                        <i class="bi bi-x-circle me-2"></i> No PDF
                                    </button>
                                
                                <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-light text-center py-5 border-0 shadow-sm">
                            <i class="bi bi-folder-x display-4 text-muted opacity-50"></i>
                            <p class="mt-3 fw-medium">No study materials available yet</p>
                            <small class="text-muted">Materials will appear after course enrollment</small>
                        </div>
                    <?php endif; ?>
                </section> -->

            <footer>
                © <?= date('Y') ?> Achiever's Castle • All Rights Reserved
            </footer>

        </main>
    </div>
</div>

<!-- Edit Profile Modal -->
<!-- <div class="modal fade" id="editProfileModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4">
            <form action="update_profile.php" method="POST">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Edit Profile</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">First Name</label>
                        <input type="text" name="first_name" class="form-control"
                            value="<?= htmlspecialchars($student['first_name'] ?? '') ?>" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Last Name</label>
                        <input type="text" name="last_name" class="form-control"
                            value="<?= htmlspecialchars($student['last_name'] ?? '') ?>" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Phone</label>
                        <input type="tel" name="phone" class="form-control"
                            value="<?= htmlspecialchars($student['phone'] ?? '') ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Date of Birth</label>
                        <input type="date" name="dob" class="form-control"
                            value="<?= $dob_input_value ?>"readonly>
                    </div>
                    <div class="mb-1">
                        <label class="form-label fw-semibold">Address</label>
                        <input type="text" name="address" class="form-control"
                            placeholder="City, State, Country"
                            value="<?= htmlspecialchars($student['address'] ?? '') ?>">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div> -->

<!-- View Profile Modal -->
<div class="modal fade" id="viewProfileModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content rounded-4">
            <div class="modal-header">
                <h5 class="modal-title fw-bold"><i class="bi bi-person-vcard me-2 text-primary"></i>My Profile</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <?php if ($enroll_info): ?>
                <div class="row g-3">

                    <div class="col-md-6">
                        <small class="text-muted d-block">Grade</small>
                        <p class="fw-semibold mb-0"><?= htmlspecialchars($enroll_info['grade'] ?? '—') ?></p>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted d-block">Program</small>
                        <p class="fw-semibold mb-0"><?= htmlspecialchars($enroll_info['program'] ?? '—') ?></p>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted d-block">Subjects</small>
                        <p class="fw-semibold mb-0"><?= htmlspecialchars($enroll_info['specific_subject'] ?? '—') ?></p>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted d-block">Mode of Education</small>
                        <p class="fw-semibold mb-0"><?= htmlspecialchars($enroll_info['mode_of_education'] ?? '—') ?></p>
                    </div>

                    <div class="col-12"><hr class="my-1"></div>

                    <div class="col-md-6">
                        <small class="text-muted d-block">Guardian Name</small>
                        <p class="fw-semibold mb-0"><?= htmlspecialchars($enroll_info['guardian_name'] ?? '—') ?> 
                            <span class="text-muted small">(<?= htmlspecialchars($enroll_info['authorized_relation'] ?? '—') ?>)</span>
                        </p>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted d-block">Guardian Contact</small>
                        <p class="fw-semibold mb-0"><?= htmlspecialchars($enroll_info['guardian_phone'] ?? '—') ?> · <?= htmlspecialchars($enroll_info['guardian_email'] ?? '—') ?></p>
                    </div>

                    <div class="col-md-6">
                        <small class="text-muted d-block">Father's Name</small>
                        <p class="fw-semibold mb-0"><?= htmlspecialchars($enroll_info['father_name'] ?? '—') ?></p>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted d-block">Father's Contact</small>
                        <p class="fw-semibold mb-0"><?= htmlspecialchars($enroll_info['father_phone'] ?? '—') ?> · <?= htmlspecialchars($enroll_info['father_email'] ?? '—') ?></p>
                    </div>

                    <div class="col-md-6">
                        <small class="text-muted d-block">Mother's Name</small>
                        <p class="fw-semibold mb-0"><?= htmlspecialchars($enroll_info['mother_name'] ?? '—') ?></p>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted d-block">Mother's Contact</small>
                        <p class="fw-semibold mb-0"><?= htmlspecialchars($enroll_info['mother_phone'] ?? '—') ?> · <?= htmlspecialchars($enroll_info['mother_email'] ?? '—') ?></p>
                    </div>

                    <div class="col-12"><hr class="my-1"></div>

                    <div class="col-md-6">
                        <small class="text-muted d-block">Emergency Contact</small>
                        <p class="fw-semibold mb-0"><?= htmlspecialchars($enroll_info['emergency_name'] ?? '—') ?> · <?= htmlspecialchars($enroll_info['emergency_phone'] ?? '—') ?></p>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted d-block">Enrollment Date</small>
                        <p class="fw-semibold mb-0">
                            <?= !empty($enroll_info['enroll_date']) ? date('d M Y', strtotime($enroll_info['enroll_date'])) : '—' ?>
                        </p>
                    </div>

                </div>
                <?php else: ?>
                    <div class="text-center py-4">
                        <i class="bi bi-info-circle display-5 text-muted mb-3"></i>
                        <p class="text-muted fw-medium mb-0">No enrollment details found.</p>
                    </div>
                <?php endif; ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Change Password Modal -->
<div class="modal fade" id="changePasswordModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4">
            <form action="change_password.php" method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">Change Password</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Current Password</label>
                        <input type="password" class="form-control" name="current_password" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">New Password</label>
                        <input type="password" class="form-control" name="new_password" required>
                    </div>
                    <div>
                        <label class="form-label">Confirm Password</label>
                        <input type="password" class="form-control" name="confirm_password" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-light" data-bs-dismiss="modal" type="button">Cancel</button>
                    <button class="btn btn-primary" type="submit">Update Password</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
    // Sidebar toggle (mobile)
    document.getElementById('sidebarToggle')?.addEventListener('click', () => {
        document.getElementById('studentSidebar')?.classList.toggle('show');
    });

    // Auto-submit the photo form as soon as a file is chosen
    document.getElementById('profilePhotoInput')?.addEventListener('change', function () {
        if (this.files.length > 0) {
            this.form.submit();
        }
    });

    // Activate correct tab based on URL hash (#security)
    document.addEventListener('DOMContentLoaded', function () {
        const hash = window.location.hash;
        if (hash) {
            const tabTrigger = document.querySelector(`[data-bs-target="${hash}"]`);
            if (tabTrigger) {
                new bootstrap.Tab(tabTrigger).show();
            }
        }
    });
</script>
</body>

</html>