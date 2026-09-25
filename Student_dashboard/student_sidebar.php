<?php
// student_sidebar.php

$currentpage = basename($_SERVER['PHP_SELF']);

// If variables are not already set (when included from dashboard)
if (!isset($student)) {
    include "../db_config.php";

    if (!isset($_SESSION['student_id'])) {
        header("Location: student_login.php");
        exit();
    }

    $student_id = $_SESSION['student_id'];
    $sql = "SELECT first_name, last_name FROM students WHERE id = $student_id";
    $res = mysqli_query($conn, $sql);
    $student = mysqli_fetch_assoc($res);
}
?>

<!-- Sidebar Styles -->
<style>
:root {
    --primary: #1e40af;
    --primary-dark: #1e3a8a;
    --accent: #ef4444;
    --accent-dark: #dc2626;
    --text-dark: #1e293b;
    --text-muted: #64748b;
}

/* Main Sidebar */
.sidebar {
    width: 270px;
    background: #ffffff;
    color: var(--text-dark);
    position: fixed;
    top: 0;
    left: 0;
    height: 100vh;
    overflow-y: auto;
    box-shadow: 4px 0 24px rgba(15, 23, 42, 0.06);
    border-right: 1px solid #eef1f6;
    z-index: 1050;

    display: flex;
    flex-direction: column;

    scrollbar-width: none;
    -ms-overflow-style: none;
}

.sidebar::-webkit-scrollbar {
    display: none;
}

/* Header */
.sidebar-header {
    padding: 22px 15px 16px;
    text-align: center;
    border-bottom: 1px solid #eef1f6;
}

.sidebar-logo {
    width: 150px;
    height: 100px;
}

.sidebar-logo:hover {
    transform: scale(1.03);
}

/* Navigation */
.sidebar nav {
    margin-top: 14px;
    display: flex;
    flex-direction: column;
    flex: 1;
}

/* Links */
.sidebar nav a {
    display: flex;
    align-items: center;
    gap: 14px;

    color: #64748b;
    padding: 13px 20px;
    margin: 4px 15px;

    border-radius: 10px;
    text-decoration: none;
    font-size: 15px;
    font-weight: 600;

    transition: all 0.25s ease;
}

/* Hover */
.sidebar nav a:hover {
    background: rgba(30, 64, 175, 0.06);
    color: var(--primary-dark);
    transform: translateX(4px);
}

/* Hover */
.sidebar nav a:hover:not(.active):not(.logout-link) {
    background: #eef2ff;
    color: #1e40af;
    transform: translateX(0);
}

.sidebar nav a:hover:not(.active):not(.logout-link) i {
    color: #1e40af;
}

.sidebar nav a.active {
    background: #eef2ff;
    color: #1e40af;
}

.sidebar nav a.active i {
    color: #1e40af;
}

.sidebar nav a.active:hover {
    background: #eef2ff;
    color: #1e40af;
}

.sidebar nav a.active:hover i {
    color: #1e40af;
}
/* Icons */
.sidebar nav i {
    font-size: 18px;
    min-width: 22px;
}

/* Logout - pinned near the bottom */
.sidebar nav a.logout-link {
    margin-top: auto;
    margin-bottom: 25px;

    background: linear-gradient(90deg, #ef4444, #dc2626);
    color: #fff;

    font-weight: 700;
    text-align: center;
    justify-content: center;

    box-shadow: 0 6px 16px rgba(239, 68, 68, 0.35);
}

.sidebar nav a.logout-link:hover {
    background: linear-gradient(90deg, #dc2626, #b91c1c);
    transform: translateX(0) scale(1.02);
    box-shadow: 0 8px 22px rgba(220, 38, 38, 0.45);
    color: #fff;
}

/* Mobile */
@media (max-width: 992px) {
    .sidebar {
        transform: translateX(-100%);
        transition: 0.4s ease;
    }

    .sidebar.show {
        transform: translateX(0);
    }
}

@media (max-width: 576px) {
    .sidebar {
        width: 240px;
    }

    .sidebar-logo {
        width: 130px;
    }
}
</style>

<div class="sidebar" id="studentSidebar">
    <div class="sidebar-header">
        <img src="../images/logo.png"
             alt="Achiever's Castle Logo"
             class="sidebar-logo">
    </div>

    <nav class="nav flex-column">
        <a href="student_dashboard.php" class="<?= ($currentpage === 'student_dashboard.php') ? 'active' : '' ?>">
            <i class="bi bi-house"></i> Dashboard
        </a>
        <a href="enrolled_subjects.php" class="<?= ($currentpage === 'enrolled_subjects.php') ? 'active' : '' ?>">
            <i class="bi bi-book"></i> Enrolled Subjects
        </a>
        <a href="student_documents.php" class="<?= ($currentpage === 'student_documents.php') ? 'active' : '' ?>">
            <i class="bi bi-folder2-open"></i> My Documents
        </a>
        <a href="student_assessments.php" class="<?= ($currentpage === 'student_assessments.php') ? 'active' : '' ?>">
            <i class="bi bi-file-earmark-check"></i> My Assessments
        </a>
        <!-- <a href="#progress">
            <i class="bi bi-graph-up-arrow"></i> Progress Tracker
        </a> -->
        <a href="purchase_history.php" class="<?= ($currentpage === 'purchase_history.php') ? 'active' : '' ?>">
            <i class="bi bi-bag"></i> Purchase History
        </a>
       <a href="settings.php"
            class="<?= ($currentpage === 'settings.php') ? 'active' : '' ?>">

                <i class="bi bi-gear"></i>
                Settings

            </a>
        <a href="student_logout.php" class="logout-link">
            <i class="bi bi-box-arrow-right"></i> Logout
        </a>
    </nav>
</div>