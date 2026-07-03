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
    --text-light: #f1f5ff;
}

/* Main Sidebar */
.sidebar {
    width: 270px;
    background: linear-gradient(160deg, #1e3a8a, #2563eb);
    color: white;
    position: fixed;
    top: 0;
    left: 0;
    height: 100vh;
    overflow-y: auto;
    box-shadow: 8px 0 30px rgba(0,0,0,0.35);
    z-index: 1050;

    scrollbar-width: none;
    -ms-overflow-style: none;
}

.sidebar::-webkit-scrollbar {
    display: none;
}

/* Header */
/* Header - reduce top spacing */
.sidebar-header {
    padding: 12px 15px 8px;   /* pehle 25px tha - ab kam kar diya */
    text-align: center;
    border-bottom: 1px solid rgba(255,255,255,0.08);
}

/* Logo - bigger & tighter */
.sidebar-logo {
    width: 190px;     /* Size increase */
    max-width: 100%;
    height: auto;

    margin: 0 auto 5px;  /* Bottom gap kam */
    display: block;

    filter: drop-shadow(0 6px 12px rgba(0,0,0,0.4));
    transition: 0.3s ease;
}

.sidebar-logo:hover {
    transform: scale(1.05);
}

/* Navigation */
.sidebar nav {
    margin-top: 10px; /* Dashboard upar aayega */
}

/* Links */
.sidebar nav a {
    display: flex;
    align-items: center;
    gap: 14px;

    color: var(--text-light);
    padding: 13px 20px;
    margin: 6px 15px;

    border-radius: 10px;
    text-decoration: none;
    font-size: 15px;
    font-weight: 500;

    transition: all 0.3s ease;
}

/* Hover */
.sidebar nav a:hover {
    background: rgba(255,255,255,0.12);
    transform: translateX(6px);
}

/* Active */
.sidebar nav a.active {
    background: linear-gradient(90deg, #ef4444, #dc2626);
    color: white;
    box-shadow: 0 5px 15px rgba(239,68,68,0.45);
}

/* Icons */
.sidebar nav i {
    font-size: 18px;
    min-width: 22px;
}

/* Logout */
/* Logout Button */
.sidebar nav a.logout-link {
    margin-top: 25px;

    background: linear-gradient(90deg, #ef4444, #dc2626);
    color: #fff;

    font-weight: 600;
    text-align: center;

    box-shadow: 0 5px 15px rgba(239,68,68,0.45);
}

/* Hover Effect */
.sidebar nav a.logout-link:hover {
    background: linear-gradient(90deg, #dc2626, #b91c1c);
    transform: translateX(6px) scale(1.02);
    box-shadow: 0 8px 22px rgba(220,38,38,0.6);
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
        <!-- Replace the src with your actual logo path -->
        <img src="../images/logo3.png" 
             alt="Achiever's Castle Logo" 
             class="sidebar-logo">
    </div>

    <nav class="nav flex-column">
        <a href="student_dashboard.php" class="<?= ($currentpage === 'student_dashboard.php') ? 'active' : '' ?>">
            <i class="bi bi-house-door-fill"></i> Dashboard
        </a>
        <a href="enrolled_subjects.php" class="<?= ($currentpage === 'enrolled_subjects.php') ? 'active' : '' ?>">
            <i class="bi bi-journal-bookmark-fill"></i> Enrolled Subjects
        </a>
        <a href="student_documents.php" class="<?= ($currentpage === 'student_documents.php') ? 'active' : '' ?>">
            <i class="bi bi-file-earmark-arrow-up-fill"></i> My Documents
        </a>
        <a href="student_assessments.php" class="<?= ($currentpage === 'student_assessments.php') ? 'active' : '' ?>">
            <i class="bi bi-file-earmark-check-fill"></i> My Assessments
        </a>
        <a href="#progress">
            <i class="bi bi-graph-up-arrow"></i> Progress Tracker
        </a>
        <a href="purchase_history.php" class="<?= ($currentpage === 'purchase_history.php') ? 'active' : '' ?>">
            <i class="bi bi-receipt-cutoff"></i> Purchase History
        </a>
        <a href="#settings">
            <i class="bi bi-gear-fill"></i> Settings
        </a>
        <a href="student_logout.php" class="logout-link">
            <i class="bi bi-box-arrow-right"></i> Logout
        </a>
    </nav>
</div>