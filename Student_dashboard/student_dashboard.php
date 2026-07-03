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

// Fetch student info
$sql_student = "SELECT first_name, last_name, email, grade FROM students WHERE id = " . (int)$student_id;
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

// =======================
// Fetch Purchase History
// =======================

$purchases = [];

$sql_pay = "
    SELECT 
        course_title,
        price,
        gst,
        total,
        payment_id,
        payment_status,
        mode_of_education,
        payment_type,
        created_at
    FROM students
    WHERE id = $student_id
      AND payment_status = 'success'
    ORDER BY created_at DESC
";

$res_pay = mysqli_query($conn, $sql_pay);

if ($res_pay) {
    while ($row = mysqli_fetch_assoc($res_pay)) {
        $purchases[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Achiever's Castle • Dashboard</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Love+Ya+Like+A+Sister&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary: #1e40af;
            --primary-light: #3b82f6;
            --accent: #ef4444;
            --light-bg: #f9fbff;
            --card-bg: #ffffff;
            --text: #1f2937;
            --gray: #6b7280;
            --shadow: 0 6px 20px rgba(0, 0, 0, 0.07);
            --shadow-hover: 0 12px 32px rgba(0, 0, 0, 0.12);
        }

        body {
            background: var(--light-bg);
            color: var(--text);
            font-family: system-ui, -apple-system, sans-serif;
        }

        .main-content {
            margin-left: 270px;
            padding: 32px 40px;
            width: 100%;
            min-height: 100vh;
        }

        @media (max-width: 992px) {
            .main-content {
                margin-left: 0;
                padding: 28px 28px;
            }
        }

        .welcome-card {
            background: var(--card-bg);
            border-radius: 20px;
            padding: 36px 40px;
            margin-bottom: 40px;
            box-shadow: var(--shadow);
            border: 1px solid rgba(30, 64, 175, 0.06);
        }

        .welcome-title {
            font-size: 42px;
            font-weight: 400;
            margin-bottom: 6px !important;
            background: linear-gradient(to right, #e02121, #2f55a4);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            font-family: "Love Ya Like A Sister", cursive;
        }

        .avatar-glow {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid white;
            box-shadow: 0 0 0 8px rgba(30, 64, 175, 0.15);
            transition: all 0.3s ease;
        }

        .avatar-glow:hover {
            transform: scale(1.06);
            box-shadow: 0 0 0 12px rgba(30, 64, 175, 0.22);
        }

        .stat-pill {
            background: rgba(30, 64, 175, 0.05);
            border-radius: 12px;
            padding: 16px 20px;
            text-align: center;
            transition: all 0.25s;
            height: 110px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }

        .stat-pill:hover {
            background: rgba(239, 68, 68, 0.08);
            transform: translateY(-3px);
        }

        .stat-pill h5 {
            font-size: 1.6rem;
        }

        .stat-pill small {
            line-height: 1.2;
        }

        /* .notif-badge {
            position: absolute;
            top: -8px;
            right: -8px;
            background: var(--accent);
            color: white;
            font-size: 0.75rem;
            font-weight: 700;
            width: 22px;
            height: 22px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 6px rgba(239,68,68,0.4);
        } */
        .section-title {
            font-weight: 700;
            color: var(--primary);
            position: relative;
            margin-bottom: 1.25rem;
        }

        /* .section-title::after {
            content: '';
            position: absolute;
            left: 0;
            bottom: -6px;
            width: 50px;
            height: 3px;
            background: var(--accent);
            border-radius: 3px;
        } */
        .profile-img {
            width: 130px;
            height: 130px;
            object-fit: cover;
            border-radius: 50%;
            border: 3px solid #3b82f6;
            box-shadow: var(--shadow);
        }

        .card-material {
            height:100%;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: var(--shadow);
            transition: all 0.3s;
        }
        
            .card-material .p-3{
            height:100%;
            min-height:300px;
            display:flex;
            flex-direction:column;
             }
         
         .material-body{
            flex:1;
            display:flex;
            justify-content:center;
            align-items:center;
            flex-direction:column;
        }

        .card-material:hover {
            transform: translateY(-6px);
            box-shadow: var(--shadow-hover);
        }

        .pdf-iframe {
            width: 100%;
            height: 240px;
            border: none;
        }

        footer {
            margin-top: 90px;
            padding: 35px 0;
            text-align: center;
            color: var(--gray);
            border-top: 1px solid #e5e7eb;
        }

        .stat-card {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 22px 20px;
            border-radius: 16px;
            background: linear-gradient(180deg, #fff, #f9fbff);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.06);
            transition: 0.3s ease;
            height: 100%;
            min-height: 150px;
            width: 100%;
        }

        .stat-card:hover {
            transform: translateY(-6px) scale(1.02);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
        }

        .stat-card h3 {
            font-size: 28px;
            margin: 0;
            font-weight: 700;
        }

        .stat-card p {
            margin: 0;
            font-size: 14px;
            color: #6b7280;
        }

        .stat-icon {
            font-size: 28px;
            width: 55px;
            height: 55px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
            color: white;
        }

        .welcome-card .col-lg-4 {
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .welcome-avatar {
            width: 55px;
            height: 55px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #3b82f6;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
        }

        /* Different Colors */
        .blue .stat-icon {
            background: #3b82f6;
        }

        .purple .stat-icon {
            background: #8b5cf6;
        }

        .green .stat-icon {
            background: #10b981;
        }

        .red .stat-icon {
            background: #ef4444;
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

        /* Tablet */
        @media(max-width:1200px) {
            .main-content {
                padding: 28px 28px;
            }
        }

        /* Mobile */
        @media(max-width:576px) {
            .main-content {
                padding: 70px 16px 20px;
            }

            .welcome-avatar {
                width: 42px;
            }

            .stat-card {
                flex-direction: column;
                text-align: center;
                gap: 10px;
                min-height: 120px;
            }

            .stat-icon {
                width: 48px;
                height: 48px;
                font-size: 22px;
            }

            .stat-card h3 {
                font-size: 22px;
            }

            .stat-card p {
                font-size: 13px;
            }
        }

        @media(max-width:992px) {
            .stat-card {
                min-height: 130px;
                padding: 18px;
            }

            #studentSidebar {
                position: fixed;
                top: 0;
                left: -260px;
                width: 260px;
                height: 100vh;
                background: #1e40af;
                z-index: 1150;
                transition: 0.3s ease;
                overflow-y: auto;
            }

            #studentSidebar.show {
                left: 0;
            }
        }

        @media(max-width:768px) {
            .welcome-card {
                padding: 24px 22px;
            }

            .welcome-title {
                font-size: 30px;
            }

            .profile-img {
                width: 100px;
                height: 100px;
            }

            .pdf-iframe {
                height: 200px;
            }
        }

        @media(max-width:480px) {
            .res-card {
                width: 100%;
            }

            .welcome-title {
                font-size: 24px;
            }

            .profile-img {
                width: 80px;
                height: 80px;
            }

            .pdf-iframe {
                height: 170px;
            }
        }
    </style>
</head>

<body>
    <!-- Mobile Sidebar Toggle -->
    <button class="btn btn-primary d-lg-none position-fixed"
        id="sidebarToggle"
        style="top:15px;left:15px;z-index:1200;border-radius:50%;width:48px;height:48px;">
        <i class="bi bi-list fs-4"></i>
    </button>
    <div class="container-fluid p-0">
        <div class="d-flex">
            <!-- Sidebar included -->

            <main class="main-content flex-grow-1">

                <!-- Professional Welcome Card -->
                <div class="welcome-card">
                    <div class="row align-items-center mb-4">

                        <!-- Left: Title + Text -->
                        <div class="col-12">

                            <div class="d-flex align-items-center gap-3 mb-2">

                                <!-- Small Profile -->
                                <img src="<?= htmlspecialchars($student_img['image_path'] ?? '../images/default-avatar.png') ?>"
                                    class="welcome-avatar" alt="Profile">

                                <h1 class="welcome-title mb-0">
                                    Welcome back, <?= htmlspecialchars($student['first_name'] ?? 'Student') ?>
                                </h1>

                            </div>

                            <p class="fs-5 text-muted mb-3">
                                Your learning journey continues • <?= date('l, d F Y') ?>
                            </p>

                            <!-- Stats -->
                            <div class="row g-4 align-items-stretch">

                                <div class="col-lg-3 col-md-6 col-6 res-card">
                                    <div class="stat-card blue">
                                        <div class="stat-icon">
                                            <i class="bi bi-journal-bookmark"></i>
                                        </div>
                                        <div>
                                            <h3><?= $subjects_count ?></h3>
                                            <p>Enrolled Subjects</p>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-lg-3 col-md-6 col-6 res-card">
                                    <div class="stat-card purple">
                                        <div class="stat-icon">
                                            <i class="bi bi-folder2-open"></i>
                                        </div>
                                        <div>
                                            <h3><?= $materials_count ?></h3>
                                            <p>Study Materials</p>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-lg-3 col-md-6 col-6 res-card">
                                    <div class="stat-card green">
                                        <div class="stat-icon">
                                            <i class="bi bi-graph-up"></i>
                                        </div>
                                        <div>
                                            <h3><?= $attendance_percentage ?>%</h3>
                                            <p>This Month Attendance</p>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-lg-3 col-md-6 col-6 res-card">
                                    <div class="stat-card red">
                                        <div class="stat-icon">
                                            <i class="bi bi-bell"></i>
                                        </div>
                                        <div>
                                            <h3><?= $notif_count ?></h3>
                                            <p>Announcements</p>
                                        </div>
                                    </div>
                                </div>

                            </div>

                        </div>

                    </div>
                </div>

                <!-- My Profile -->
                <section class="mb-5">
                    <h4 class="section-title"><i class="bi bi-person-circle me-2"></i> My Profile</h4>
                    <div class="card border-0 shadow-sm p-4">
                        <div class="d-flex flex-wrap align-items-center gap-4">
                            <img src="<?= htmlspecialchars($student_img['image_path'] ?? 'default-profile.png') ?>"
                                alt="Profile" class="profile-img">
                            <div>
                                <h4 class="fw-bold mb-2"><?= htmlspecialchars($student['first_name'] . ' ' . ($student['last_name'] ?? '')) ?></h4>
                                <p class="mb-2"><i class="bi bi-envelope me-2 text-primary"></i><?= htmlspecialchars($student['email'] ?? '—') ?></p>
                                <p class="mb-0"><i class="bi bi-mortarboard me-2 text-primary"></i>Grade: <?= htmlspecialchars($student['grade'] ?? 'N/A') ?></p>
                            </div>
                        </div>
                    </div>
                </section>

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
                                <div class="col-xl-3 col-lg-4 col-md-6 col-12">
                                    <div class="card border-0 shadow-sm text-center py-4">
                                        <i class="bi bi-book fs-2 text-primary mb-3 d-block"></i>
                                        <h6 class="fw-bold"><?= htmlspecialchars($sub['subject_name']) ?></h6>
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
                <section class="mb-5">
                    <h4 class="section-title">
                        <i class="bi bi-bell-fill me-2"></i> Announcements
                        <?php if ($notif_count > 0): ?>
                            <span class="notif-badge"><?= $notif_count ?></span>
                        <?php endif; ?>
                    </h4>

                    <div class="card border-0 shadow-sm p-4">

                        <?php if (!empty($announcements)): ?>

                            <?php foreach ($announcements as $ann): ?>

                                <div class="border-bottom pb-3 mb-3">

                                    <h6 class="fw-bold mb-1 text-primary">
                                        <?= htmlspecialchars($ann['title']) ?>
                                    </h6>

                                    <p class="mb-1 text-muted small">
                                        <?= nl2br(htmlspecialchars($ann['message'])) ?>
                                    </p>

                                    <small class="text-secondary">
                                        <i class="bi bi-clock me-1"></i>
                                        <?= date("d M Y, h:i A", strtotime($ann['created_at'])) ?>
                                    </small>

                                </div>

                            <?php endforeach; ?>

                        <?php else: ?>

                            <div class="text-center py-4">
                                <i class="bi bi-info-circle display-5 text-muted mb-3"></i>
                                <p class="text-muted fw-medium mb-0">
                                    No new announcements at this time.
                                </p>
                            </div>

                        <?php endif; ?>

                    </div>
                </section>

                            <!-- Study Materials -->
                <section class="mb-5">
                    <h4 class="section-title"><i class="bi bi-folder2-open me-2"></i> Recent Study Materials</h4>
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
                                         ? $path                                              // already a full URL
                                         : rtrim($pdf_folder, '/') . '/' . ltrim($path, '/'); // folder + stored path
                                }

                                // Does the PDF actually exist on the server?
                                $pdf_available = false;
                                if ($url) {
                                    if (preg_match('#^https?://#i', $url)) {
                                        $pdf_available = true;                                  // remote URL – assume ok
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
                </section>
                <!-- Progress & History -->
                <section class="mb-5">
                    <h4 class="section-title"><i class="bi bi-graph-up-arrow me-2"></i> Learning Progress</h4>
                    <div class="card border-0 shadow-sm text-center py-5">
                        <i class="bi bi-graph-up-arrow display-1 text-muted opacity-50"></i>
                        <p class="mt-3 text-muted fw-medium">Your progress will appear after completing assessments</p>
                    </div>
                </section>

                <section class="mb-5">

                    <h4 class="section-title mb-3">
                        <i class="bi bi-receipt me-2"></i> Purchase History
                    </h4>


                    <!-- ================= SUMMARY CARDS ================= -->

                    <?php
                    $total_spent = 0;
                    $success_count = 0;
                    $last_date = null;

                    foreach ($purchases as $p) {
                        $total_spent += $p['total'];
                        $success_count++;

                        if (!$last_date || strtotime($p['created_at']) > strtotime($last_date)) {
                            $last_date = $p['created_at'];
                        }
                    }
                    ?>

                    <div class="row g-3 mb-4">

                        <div class="col-md-4">
                            <div class="p-4 rounded-4 shadow-sm text-white"
                                style="background:linear-gradient(135deg,#4f46e5,#3b82f6);">
                                <small>Total Spent</small>
                                <h3 class="fw-bold">₹<?= number_format($total_spent, 2) ?></h3>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="p-4 rounded-4 shadow-sm text-white"
                                style="background:linear-gradient(135deg,#16a34a,#22c55e);">
                                <small>Successful Payments</small>
                                <h3 class="fw-bold"><?= $success_count ?></h3>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="p-4 rounded-4 shadow-sm text-white"
                                style="background:linear-gradient(135deg,#f97316,#fb923c);">
                                <small>Last Purchase</small>
                                <h3 class="fw-bold">
                                    <?= $last_date ? date("d M Y", strtotime($last_date)) : '--' ?>
                                </h3>
                            </div>
                        </div>

                    </div>


                    <!-- ================= GRAPH ================= -->

                    <div class="card border-0 shadow-sm rounded-4 mb-4">
                        <div class="card-body">

                            <h6 class="fw-bold text-primary mb-3">
                                <i class="bi bi-graph-up-arrow me-2"></i> Spending Overview
                            </h6>

                            <!-- responsive wrapper -->
                            <div class="chart-container">
                                <canvas id="purchaseChart"></canvas>
                            </div>

                        </div>
                    </div>


                    <!-- ================= TABLE ================= -->

                    <div class="card border-0 shadow-sm rounded-4">

                        <div class="card-body p-0">

                            <!-- Responsive wrapper -->
                            <div class="table-responsive">

                                <table class="table table-hover align-middle mb-0">

                                    <thead class="table-light">
                                        <tr>
                                            <th class="text-nowrap">Course</th>
                                            <th class="text-nowrap">Total</th>
                                            <th class="text-nowrap">Status</th>
                                            <th class="text-nowrap">Mode</th>
                                            <th class="text-nowrap">Date</th>
                                        </tr>
                                    </thead>

                                    <tbody>

                                        <?php if (!empty($purchases)): ?>

                                            <?php foreach ($purchases as $row): ?>

                                                <tr>

                                                    <td class="text-nowrap">
                                                        <?= htmlspecialchars($row['course_title']) ?>
                                                    </td>

                                                    <td class="fw-bold text-success text-nowrap">
                                                        ₹<?= number_format($row['total'], 2) ?>
                                                    </td>

                                                    <td class="text-nowrap">
                                                        <span class="badge rounded-pill bg-success px-3 py-2">
                                                            <i class="bi bi-check-circle me-1"></i> Success
                                                        </span>
                                                    </td>

                                                    <td class="text-nowrap">
                                                        <?= ucfirst($row['mode_of_education']) ?>
                                                    </td>

                                                    <td class="text-nowrap">
                                                        <?= date("d M Y", strtotime($row['created_at'])) ?>
                                                    </td>

                                                </tr>

                                            <?php endforeach; ?>

                                        <?php else: ?>

                                            <tr>
                                                <td colspan="5" class="text-center py-4 text-muted">
                                                    No purchase history found
                                                </td>
                                            </tr>

                                        <?php endif; ?>

                                    </tbody>

                                </table>

                            </div>

                        </div>

                    </div>

                </section>
                <footer>
                    © <?= date('Y') ?> Achiever's Castle • All Rights Reserved
                </footer>

            </main>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        const sidebar = document.getElementById('studentSidebar');

        document.getElementById('sidebarToggle')?.addEventListener('click', () => {
            sidebar.classList.toggle('show');
        });

        // For purchase chart
        const ctx = document.getElementById('purchaseChart');

        let labels = <?= json_encode(array_column($purchases, 'course_title')) ?>;
        let data = <?= json_encode(array_column($purchases, 'total')) ?>;

        // TEST PURPOSE: agar sirf 1 record ho
        if (labels.length === 1) {
            labels.unshift("Previous");
            data.unshift(0);
        }

        new Chart(ctx, {
            type: 'line',

            data: {
                labels: labels,

                datasets: [{
                    label: 'Amount (₹)',
                    data: data,

                    borderColor: '#4f46e5',
                    backgroundColor: 'rgba(79,70,229,0.15)',

                    borderWidth: 3,
                    tension: 0.45,
                    fill: true,

                    pointRadius: 0,
                    pointHoverRadius: 5
                }]
            },

            options: {
                responsive: true,
                maintainAspectRatio: false,

                plugins: {
                    legend: {
                        display: false
                    }
                },

                scales: {
                    y: {
                        beginAtZero: true
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
    </script>
</body>

</html>