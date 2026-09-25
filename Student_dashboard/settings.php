<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include "../db_config.php";

if (!isset($_SESSION['student_id'])) {
    header("Location: ../student_login.php");
    exit();
}

$student_id = $_SESSION['student_id'];

// Fetch Student Data
$sql = "SELECT first_name, last_name, email, phone, dob, address 
        FROM students WHERE id = " . (int)$student_id;
$res = mysqli_query($conn, $sql);
$student = mysqli_fetch_assoc($res) ?? [];

if (!$student) {
    die("Student record not found!");
}

// Fetch Profile Picture
$sql_img = "SELECT image_path FROM student_images WHERE student_id = " . (int)$student_id;
$res_img = mysqli_query($conn, $sql_img);
$student_img = $res_img ? mysqli_fetch_assoc($res_img) : null;

$student_avatar = htmlspecialchars($student_img['image_path'] ?? '../images/default-avatar.png');
$student_full_name = trim(($student['first_name'] ?? '') . ' ' . ($student['last_name'] ?? ''));

$dob_input_value = (!empty($student['dob']) && $student['dob'] !== '0000-00-00') 
    ? date('Y-m-d', strtotime($student['dob'])) : '';

// Fetch Recent Announcements for Notifications Tab
$announcements = [];
$sql_ann = "SELECT id, title, message, created_at 
            FROM announcements 
            ORDER BY created_at DESC LIMIT 5";
$res_ann = mysqli_query($conn, $sql_ann);
if ($res_ann) {
    while ($row = mysqli_fetch_assoc($res_ann)) {
        $announcements[] = $row;
    }
}
$notif_count = count($announcements);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Achiever's Castle • Settings</title>

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

        body {
            background: var(--light-bg);
            color: var(--text);
            font-family: system-ui, -apple-system, sans-serif;
        }

        .main-content {
            margin-left: 270px;
            padding: 28px 40px 40px;
            width: 100%;
            min-height: 100vh;
        }

        .input-group:focus-within{
            box-shadow: none !important;
        }

        .input-group .form-control:focus{
            box-shadow: none !important;
            border-color: #dee2e6 !important;
        }

        .input-group .form-control{
            border-right: 0;
        }

        .input-group-text{
            background: #fff;
            border-left: 0;
        }

       @media (max-width:992px){

            .main-content{
                margin-left:0;
                padding:80px 24px 28px;
            }

            .page-heading{
                gap:14px;
            }

            .icon-style{
                font-size:42px;
            }

            .page-title{
                font-size:34px;
            }

            .page-sub{
                margin-left:58px;
            }

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
        }

        .topbar-profile img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
        }

        .topbar-profile .name {
            font-weight: 700;
            font-size: 14px;
            line-height: 1.1;
            color: var(--text);
            text-align: left;
        }

        .topbar-profile .role {
            font-size: 12px;
            color: var(--gray);
        }

        .profile-arrow {
            margin-left: 10px;
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

       .page-header{
            margin-bottom:30px;
            margin-top:-10px;
        }

        .page-heading{
            display:flex;
            align-items:center;
            gap:18px;
            margin-bottom:8px;
        }

        .icon-style{
            font-size:48px;
            line-height:1;
            flex-shrink:0;
            background:linear-gradient(to right,#e02121,#2f55a4);
            -webkit-background-clip:text;
            -webkit-text-fill-color:transparent;
            background-clip:text;
            color:transparent;
        }

        .page-title{
            margin:0 !important;
            font-size:42px;
            font-weight:400;
            background:linear-gradient(to right,#e02121,#2f55a4);
            -webkit-background-clip:text;
            -webkit-text-fill-color:transparent;
            background-clip:text;
            color:transparent;
            font-family:"Love Ya Like A Sister", cursive;
        }

        .page-sub{
            color:#64748b;
            font-size:16px;
            margin-left:66px;
            margin-top: -10px;
        }

        /* ============ SECTION TITLE ============ */
        .section-title {
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 1.25rem;
        }

        /* ============ SETTINGS CARD ============ */
        .settings-card {
            background: var(--card-bg);
            border-radius: 20px;
            box-shadow: var(--shadow);
            padding: 35px;
            margin-bottom: 30px;
            border: 1px solid rgba(30, 64, 175, 0.06);
        }

        /* Profile picture card */
        .avatar-upload-wrap {
            position: relative;
            width: 140px;
            height: 140px;
            margin: 0 auto;
        }

        .profile-img {
            width: 140px;
            height: 140px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid #fff;
            box-shadow: 0 0 0 6px rgba(30, 64, 175, 0.15);
        }

        .avatar-edit-btn {
            position: absolute;
            bottom: 2px;
            right: 2px;
            width: 42px;
            height: 42px;
            border-radius: 50%;
            background: var(--primary-light);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.25);
            cursor: pointer;
            border: 3px solid #fff;
            font-size: 16px;
            transition: 0.2s;
        }

        .avatar-edit-btn:hover {
            background: var(--primary-dark);
        }

        /* Tabs */
        .nav-tabs {
            border-bottom: 1px solid #e5e7eb;
        }

        .nav-tabs .nav-link {
            color: var(--gray);
            font-weight: 600;
            border: none;
            padding: 10px 18px;
        }

        .nav-tabs .nav-link.active {
            color: var(--primary);
            background: transparent;
            border: none;
            border-bottom: 3px solid var(--primary);
            font-weight: 700;
        }

        .nav-tabs .nav-link:hover {
            border-color: transparent;
            color: var(--primary-light);
        }

        .form-label {
            color: var(--text);
            font-size: 14px;
        }

        .form-control {
            border-radius: 10px;
            border: 1px solid #e2e8f0;
            padding: 10px 14px;
        }

        .form-control:focus {
            border-color: var(--primary-light);
            box-shadow: 0 0 0 0.2rem rgba(59, 130, 246, 0.15);
        }

        .btn-primary {
            background: var(--primary);
            border: none;
            border-radius: 12px;
            font-weight: 600;
        }

        .btn-primary:hover {
            background: var(--primary-dark);
        }

        .btn-danger {
            border-radius: 12px;
            font-weight: 600;
        }

        .announcement-item {
            border-left: 4px solid var(--primary);
            background: #f8fafc;
            border-radius: 12px;
        }

        footer {
            margin-top: 90px;
            padding: 35px 0;
            text-align: center;
            color: var(--gray);
            border-top: 1px solid #e5e7eb;
        }

       @media (max-width:576px){

            .settings-card{
                padding:24px;
            }

            .page-heading{
                gap:12px;
            }

            .icon-style{
                font-size:36px;
            }

            .page-title{
                font-size:28px;
            }

            .page-sub{
                margin-left:48px;
                font-size:14px;
            }

        }
    </style>
</head>
<body>
<?php include "student_sidebar.php"; ?>
<button class="btn btn-primary d-lg-none position-fixed" id="sidebarToggle"
        style="top:15px;left:15px;z-index:1200;border-radius:50%;width:48px;height:48px;">
    <i class="bi bi-list fs-4"></i>
</button>

<div class="container-fluid p-0">
    <div class="d-flex">
        <main class="main-content flex-grow-1">

            <!-- Page Header -->
           <div class="page-header">

            <div class="page-heading">

                <i class="bi bi-gear icon-style"></i>

                <h1 class="page-title mb-0">
                    Settings
                </h1>

            </div>

            <p class="page-sub">
                Manage your profile, security and notifications.
            </p>

        </div>

            <div class="row">
                <!-- Profile Picture -->
                <div class="col-lg-4 mb-4">
                    <div class="settings-card text-center h-100">
                        <div class="avatar-upload-wrap mb-3">
                            <img src="<?= $student_avatar ?>" alt="Profile" class="profile-img">
                            <form action="update_profile_photo.php" method="POST" enctype="multipart/form-data" id="photoForm">
                                <label for="profilePhotoInput" class="avatar-edit-btn" title="Change photo">
                                    <i class="bi bi-camera-fill"></i>
                                </label>
                                <input type="file" name="profile_photo" id="profilePhotoInput" accept="image/png, image/jpeg, image/webp" hidden>
                            </form>
                        </div>
                        <h4 class="fw-bold"><?= htmlspecialchars($student_full_name) ?></h4>
                        <p class="text-muted mb-0"><?= htmlspecialchars($student['email'] ?? '') ?></p>
                    </div>
                </div>

                <!-- Settings Tabs -->
                <div class="col-lg-8">
                    <div class="settings-card">
                        <ul class="nav nav-tabs mb-4">
                            <!-- <li class="nav-item">
                                <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#profile">
                                    <i class="bi bi-person-fill me-1"></i> Profile
                                </button>
                            </li> -->
                            <li class="nav-item">
                                <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#security">
                                    <i class="bi bi-shield-lock-fill me-1"></i> Security
                                </button>
                            </li>
                            <li class="nav-item">
                                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#notifications">
                                    <i class="bi bi-bell-fill me-1"></i> Notifications
                                </button>
                            </li>
                        </ul>

                        <div class="tab-content">
                            <!-- Profile Tab -->
                            <!-- <div class="tab-pane fade show active" id="profile">
                                <form action="update_profile.php" method="POST">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-semibold">First Name</label>
                                            <input type="text" name="first_name" class="form-control" value="<?= htmlspecialchars($student['first_name'] ?? '') ?>" readonly>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-semibold">Last Name</label>
                                            <input type="text" name="last_name" class="form-control" value="<?= htmlspecialchars($student['last_name'] ?? '') ?>"readonly>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Email</label>
                                        <input type="email" class="form-control" value="<?= htmlspecialchars($student['email'] ?? '') ?>" readonly>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-semibold">Phone</label>
                                            <input type="tel" name="phone" class="form-control" value="<?= htmlspecialchars($student['phone'] ?? '') ?>">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-semibold">Date of Birth</label>
                                            <input type="date" name="dob" class="form-control" value="<?= $dob_input_value ?>"readonly>
                                        </div>
                                    </div>

                                    <div class="mb-4">
                                        <label class="form-label fw-semibold">Address</label>
                                        <textarea name="address" class="form-control" rows="3"><?= htmlspecialchars($student['address'] ?? '') ?></textarea>
                                    </div>

                                    <button type="submit" class="btn btn-primary px-5">
                                        <i class="bi bi-check-circle me-1"></i> Save Profile
                                    </button>
                                </form>
                            </div> -->

                           <!-- Security Tab -->
                            <div class="tab-pane fade show active" id="security">
                                <form action="change_password.php" method="POST">

                                    <!-- Current Password -->
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Current Password</label>
                                        <div class="input-group">
                                            <input type="password" name="current_password"
                                                class="form-control password-field" required>

                                            <span class="input-group-text toggle-password" style="cursor:pointer;">
                                                <i class="bi bi-eye"></i>
                                            </span>
                                        </div>
                                    </div>

                                    <!-- New Password -->
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">New Password</label>
                                        <div class="input-group">
                                            <input type="password" name="new_password"
                                                class="form-control password-field" required>

                                            <span class="input-group-text toggle-password" style="cursor:pointer;">
                                                <i class="bi bi-eye"></i>
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Confirm Password -->
                                    <div class="mb-4">
                                        <label class="form-label fw-semibold">Confirm New Password</label>
                                        <div class="input-group">
                                            <input type="password" name="confirm_password"
                                                class="form-control password-field" required>

                                            <span class="input-group-text toggle-password" style="cursor:pointer;">
                                                <i class="bi bi-eye"></i>
                                            </span>
                                        </div>
                                    </div>

                                    <button type="submit" class="btn btn-danger px-5">
                                        <i class="bi bi-shield-lock me-1"></i>
                                        Update Password
                                    </button>

                                </form>
                            </div>

                            <!-- Notifications Tab -->
                            <div class="tab-pane fade" id="notifications">
                                <h5 class="mb-3 fw-bold"><i class="bi bi-bell-fill text-primary"></i> Recent Announcements</h5>

                                <?php if (!empty($announcements)): ?>
                                    <div class="list-group">
                                        <?php foreach ($announcements as $ann): ?>
                                            <div class="list-group-item announcement-item mb-2 p-3 border-0">
                                                <h6 class="fw-bold"><?= htmlspecialchars($ann['title']) ?></h6>
                                                <p class="mb-1 text-muted"><?= nl2br(htmlspecialchars($ann['message'])) ?></p>
                                                <small class="text-secondary">
                                                    <i class="bi bi-clock"></i> <?= date("d M Y, h:i A", strtotime($ann['created_at'])) ?>
                                                </small>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else: ?>
                                    <div class="alert alert-info">No announcements yet.</div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <footer>
                © <?= date('Y') ?> Achiever's Castle • All Rights Reserved
            </footer>
        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>

document.addEventListener("DOMContentLoaded", function () {

    // Sidebar Toggle
    const sidebar = document.getElementById("studentSidebar");
    const toggle = document.getElementById("sidebarToggle");

    if (sidebar && toggle) {
        toggle.addEventListener("click", function () {
            sidebar.classList.toggle("show");
        });
    }

    // Profile Photo Upload
    const profilePhoto = document.getElementById("profilePhotoInput");

    if (profilePhoto) {
        profilePhoto.addEventListener("change", function () {

            if (this.files.length > 0) {
                this.form.submit();
            }

        });
    }

    // Open Security tab after password change
    if (window.location.hash === "#security") {

        const securityTab = document.querySelector(
            'button[data-bs-target="#security"]'
        );

        if (securityTab) {

            bootstrap.Tab.getOrCreateInstance(securityTab).show();

            history.replaceState(null, null, window.location.pathname);

        }

    }

});

// Show / Hide Password
document.querySelectorAll(".toggle-password").forEach(function(btn){

    btn.addEventListener("click", function(){

        const input = this.previousElementSibling;
        const icon = this.querySelector("i");

        if(input.type === "password"){
            input.type = "text";
            icon.classList.remove("bi-eye");
            icon.classList.add("bi-eye-slash");
        }else{
            input.type = "password";
            icon.classList.remove("bi-eye-slash");
            icon.classList.add("bi-eye");
        }

    });

});

</script>

</body>
</html>