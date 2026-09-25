<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
  header("Location: login.php");
  exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <title>Admin Dashboard</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <!-- Same accent font used on the teacher side -->
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
      font-family: 'Segoe UI', sans-serif;
      background: #f4f7fb;
      margin: 0;
      min-height: 100vh;
      overflow-x: hidden;
    }

    /* Layout */

    #wrapper {
      display: flex;
      min-height: 100vh;
    }

    /* Sidebar - same soft blue/purple gradient as teacher side */

    #sidebar {
      width: 260px;
      position: fixed;
      left: 0;
      top: 0;
      height: 100vh;
      overflow-y: auto;
      background: linear-gradient(135deg, #c9d9f5, #e5ddf7);
      padding: 0 15px 20px;
      color: #2a5298;
      transition: .3s;
      scrollbar-width: none;
      z-index: 1000;
    }

    #sidebar::-webkit-scrollbar {
      width: 0;
      display: none;
    }

    .sidebar-logo {
      text-align: center;
      margin-top: 18px;
      margin-bottom: 10px;
    }

    .sidebar-logo img {
      width: 130px;
      height: auto;
    }

    .sidebar-title img{
      text-align: center;
      margin-bottom: 36px;
      width: 150px !important;
      height: 100px !important;
    }

    .sidebar-title {
      margin-top:18px;
      margin-bottom:-20px;
    }

    /* Menu */

    #sidebar .nav-link {
      color: #2a5298 !important;
      padding: 10px 15px;
      border-radius: 12px;
      margin-bottom: 10px;
      transition: .3s;
      font-weight: 500;
      font-size: 15px;
      display: flex;
      align-items: center;
      gap: 10px;
    }

    #sidebar .nav-link i {
      font-size: 18px;
      width: 22px;
    }

    #sidebar .nav-link:hover {
      background: rgba(255, 255, 255, .5);
      transform: translateX(5px);
      color: #2a5298 !important;
    }

    #sidebar .nav-link.active {
      background: #ffffff;
      color: #2a5298 !important;
      font-weight: bold;
      box-shadow: 0 6px 20px rgba(0, 0, 0, .08);
    }

    /* Logout */

    .logout-btn {
      background: linear-gradient(135deg, #1e3c72, #2a5298);
      padding: 12px;
      border-radius: 12px;
      text-align: center;
      color: white !important;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      margin-top: 20px;
      text-decoration: none;
      font-weight: 500;
      box-shadow: 0 4px 12px rgba(30, 60, 114, 0.3);
      transition: .3s;
    }

    .logout-btn:hover {
      color: white;
      box-shadow: 0 6px 18px rgba(232, 6, 60, 0.35);
      background: linear-gradient(135deg, #e8063c, #c40530);
    }

    /* Page */

    #page-content {
      margin-left: 260px;
      flex: 1;
      padding: 30px;
      overflow-x: auto;
      transition: .3s;
    }

    /* Header */

    .top-header {
      background: white;
      padding: 15px 25px;
      border-radius: 15px;
      margin-bottom: 20px;
      box-shadow: 0 5px 20px rgba(0, 0, 0, .05);
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .top-header h4 {
      margin: 0;
      font-weight: 700;
      color: var(--primary-dark);
      font-family: "Love Ya Like A Sister", cursive;
      font-size: 28px;
    }

    .admin-info {
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .admin-avatar {
      width: 40px;
      height: 40px;
      border-radius: 50%;
      background: linear-gradient(135deg, #1e3c72, #2a5298);
      color: white;
      display: flex;
      align-items: center;
      justify-content: center;
      flex-shrink: 0;
    }

    #refresh-btn {
      border-radius: 30px;
      font-weight: 500;
      background: linear-gradient(135deg, #1e3c72, #2a5298);
      color: #fff;
      border: none;
      transition: .3s;
      box-shadow: 0 4px 12px rgba(30, 60, 114, 0.3);
      padding: 10px 22px;
      font-size: 15px;
    }

    #refresh-btn:hover {
      background: #2a5298;
      color: #fff;
      box-shadow: 0 6px 18px rgba(232, 6, 60, 0.4);
    }

    /* Cards - same white gradient card w/ colored top-border accent used on teacher dashboard */

    .dashboard-card {
      background: linear-gradient(135deg, #ffffff, #f8fbff);
      border-radius: 18px;
      box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
      transition: all .3s ease;
      padding: 1.6rem;
      border: none;
      position: relative;
      overflow: hidden;
      color: var(--text);
    }

    .dashboard-card::before {
      content: "";
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 4px;
    }

    .dashboard-card:hover {
      transform: translateY(-6px);
      box-shadow: 0 15px 35px rgba(0, 0, 0, .12);
    }

    .card-blue::before   { background: linear-gradient(90deg, #36d1dc, #5b86e5); }
    .card-green::before  { background: linear-gradient(90deg, #11998e, #38ef7d); }
    .card-orange::before { background: linear-gradient(90deg, #f7971e, #ffd200); }
    .card-purple::before { background: linear-gradient(90deg, #834d9b, #d04ed6); }

    .dashboard-card .stat-icon {
      width: 60px;
      height: 60px;
      display: flex;
      align-items: center;
      justify-content: center;
      margin-bottom: 12px;
      background: #f1f3f5;
      border-radius: 50%;
      font-size: 28px;
      color: var(--accent);
    }

    .dashboard-card .stat-number {
      font-size: 40px;
      font-weight: 700;
      margin: 0;
      font-family: "Love Ya Like A Sister", cursive;
      color: var(--primary-dark);
    }

    .dashboard-card .stat-label {
      font-size: 16px;
      color: var(--primary);
      font-weight: 700;
      margin: 0;
    }

    /* Box */

    .white-box {
      background: white;
      padding: 20px;
      border-radius: 15px;
      box-shadow: 0 5px 15px rgba(0, 0, 0, .05);
    }

    .btn {
      border-radius: 25px;
    }

    /* Mobile toggle + overlay (same behavior as teacher dashboard) */

    .overlay {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 0, 0, .4);
      display: none;
      z-index: 999;
    }

    .overlay.show { display: block; }

    .mobile-toggle {
      display: none;
      font-size: 24px;
      cursor: pointer;
    }

    .sidebar-close {
      display: none;
      font-size: 22px;
      cursor: pointer;
      text-align: right;
      padding: 10px 5px;
      color: #2a5298;
    }

    @media(max-width:991px) {

      #sidebar {
        left: -260px;
      }

      #sidebar.active {
        left: 0;
      }

      #page-content {
        margin-left: 0;
        padding: 20px 15px;
      }

      .mobile-toggle {
        display: block;
      }

      .sidebar-close {
        display: block;
      }
    }

    .loading-spinner {
      width: 2rem;
      height: 2rem;
      border: 3px solid rgba(30, 60, 114, .15);
      border-top: 3px solid var(--primary);
      border-radius: 50%;
      animation: spin 1s linear infinite;
    }

    @keyframes spin {
      100% {
        transform: rotate(360deg);
      }
    }
  </style>

</head>

<body>

  <div class="overlay" id="overlay"></div>

  <div id="wrapper">

    <!-- Sidebar -->

    <div id="sidebar">

      <div class="sidebar-close">
        <i class="bi bi-x-lg" id="closeSidebar"></i>
      </div>

      <div class="sidebar-title">
        <img src="../images/logo.png" alt="Logo" style="width:32px;height:32px;object-fit:contain;" onerror="this.style.display='none'">
      </div>

      <ul class="nav flex-column" style="list-style:none; padding-left:0;">

        <li>
          <a class="nav-link active menu-link" data-page="dashboard_home.php">
            <i class="bi bi-speedometer2"></i>
            Dashboard
          </a>
        </li>

        <li>
          <a class="nav-link menu-link" data-page="manage_teachers.php">
            <i class="bi bi-person-badge"></i>
            Manage Teachers
          </a>
        </li>

        <li>
          <a href="#" class="nav-link menu-link" data-page="admin_events_calendar.php">
            <i class="bi bi-calendar-event"></i>
            Events Calendar
          </a>
        </li>

        <li>
          <a class="nav-link menu-link" data-page="manage_branches.php">
            <i class="bi bi-diagram-3"></i>
            Manage Branches
          </a>
        </li>

        <li>
          <a class="nav-link menu-link" data-page="manage_courses.php">
            <i class="bi bi-book"></i>
            Manage Courses
          </a>
        </li>

        <li>
          <a class="nav-link menu-link" data-page="admin_course_suggestions.php">
            <i class="bi bi-signpost-split-fill"></i>
            Course Suggestions
          </a>
        </li>

        <!-- <li>
          <a class="nav-link menu-link" data-page="manage_students.php">
            <i class="bi bi-people"></i>
            Students
          </a>
        </li> -->

        <li>
          <a href="#" class="nav-link menu-link" data-page="invoice_system/dashboard/invoice_dashboard.php">
            <i class="bi bi-receipt"></i>
            Invoice Dashboard
          </a>
        </li>

        <li>
          <a href="#" class="nav-link menu-link" data-page="invoice_system/enroll/admin_enroll_student.php">
            <i class="bi bi-person-plus"></i>
            Enroll Student
          </a>
        </li>

        <li>
          <a href="#" class="nav-link menu-link" data-page="invoice_system/enroll/manage_enrollment.php">
            <i class="bi bi-pencil-square"></i>
            Manage Enrollment
          </a>
        </li>

        <!-- Optional -->
        <!--
        <li>
          <a href="#" class="nav-link menu-link" data-page="invoice_system/invoice/invoice_list.php">
            <i class="bi bi-file-earmark-text"></i>
            Invoices
          </a>
        </li>
        -->

        <li>
          <a href="#" class="nav-link menu-link" data-page="invoice_system/payments/payment_list.php">
            <i class="bi bi-cash-coin"></i>
            Payments
          </a>
        </li>

        <li>
          <a class="nav-link menu-link" data-page="admin_attendance.php">
            <i class="bi bi-calendar-check"></i>
            Attendance
          </a>
        </li>

        <li>
          <a href="logout.php" class="logout-btn">
            <i class="bi bi-box-arrow-right"></i>
            Logout
          </a>
        </li>

      </ul>

    </div>

    <!-- Content -->

    <div id="page-content">

      <i class="bi bi-list mobile-toggle mb-3" id="openSidebar"></i>

      <div id="page-body">
        <div class="text-center py-5">
          <div class="loading-spinner"></div>
          <div>Loading...</div>
        </div>
      </div>

    </div>

  </div>

  <script>
    $(document).ready(function() {

      /* Load from URL */

      let urlParams = new URLSearchParams(window.location.search);
      let currentPage = urlParams.get('page') || 'dashboard_home.php';

      loadPage(currentPage);

      /* Set active menu */

      $('.menu-link').each(function() {

        if ($(this).data('page') === currentPage) {

          $('.menu-link').removeClass('active');
          $(this).addClass('active');

        }

      });


      /* Menu Click */

      $(document).on('click', '.menu-link', function(e) {

        e.preventDefault();

        let page = $(this).data('page');

        history.pushState(null, '', '?page=' + page);

        loadPage(page);

        /* sidebar active */

        $('.menu-link').removeClass('active');

        $('.menu-link[data-page="' + page + '"]').addClass('active');

        /* auto close on mobile */
        if (window.innerWidth < 992) {
          $('#sidebar').removeClass('active');
          $('#overlay').removeClass('show');
        }

      });


      /* Load Page */

      function loadPage(page) {

        $('#page-body').html('<div class="text-center py-5"><div class="loading-spinner"></div></div>');

        let params = new URLSearchParams(window.location.search);

        params.delete("page");

        let url = page;

        if (params.toString() !== "") {
            url += "?" + params.toString();
        }

        $.get(url, function(data) {
            $('#page-body').html(data);
        });

    }

    $(document).on("click", ".filter-tabs a", function(e){

    e.preventDefault();

    let href = $(this).attr("href");

    history.pushState({}, "", href);

    let params = new URLSearchParams(href.split("?")[1]);

    loadPage(params.get("page"));

});

      /* Refresh button */

      $(document).on('click', '#refresh-btn', function() {

        let page = $('.menu-link.active').data('page');

        loadPage(page);

      });


      /* Browser Back Button */

      window.onpopstate = function() {

        let urlParams = new URLSearchParams(window.location.search);

        let page = urlParams.get('page') || 'dashboard_home.php';

        loadPage(page);

        $('.menu-link').removeClass('active');

        $('.menu-link').each(function() {

          if ($(this).data('page') === page) {

            $(this).addClass('active');

          }

        });

      };


      /* Mobile toggle (matches teacher dashboard behavior) */

      $('#openSidebar').click(function() {
        $('#sidebar').addClass('active');
        $('#overlay').addClass('show');
      });

      $('#closeSidebar, #overlay').click(function() {
        $('#sidebar').removeClass('active');
        $('#overlay').removeClass('show');
      });

    });
  </script>

</body>

</html>