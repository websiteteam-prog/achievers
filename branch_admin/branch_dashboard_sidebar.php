<?php
$currentpage = basename($_SERVER['PHP_SELF']);
?>
<div class="topbar d-md-none">
  <button class="menu-toggle" id="sidebarToggle" type="button" aria-label="Open menu">
    <i class="bi bi-list"></i>
  </button>
  <span class="topbar-title">Branch Panel</span>
</div>

<div class="sidebar-overlay" id="sidebarOverlay"></div>

<div class="sidebar" id="sidebar">
  <h2>Branch Panel</h2>
  <nav class="nav flex-column">
    <a class="nav-link <?=($currentpage == 'branch_dashboard.php') ? 'active' : ''?>" href="branch_dashboard.php"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a>
    <a class="nav-link <?=($currentpage == 'branch_manage_users.php') ? 'active' : ''?>" href="branch_manage_users.php"><i class="bi bi-people me-2"></i>Manage Users</a>
    <a class="nav-link <?=($currentpage == 'branch_calender.php') ? 'active' : ''?>" href="branch_calender.php"><i class="bi bi-calendar3 me-2"></i>Branch Calendar</a>
    <a class="nav-link <?=($currentpage == 'financial_report.php') ? 'active' : ''?>" href="financial_report.php"><i class="bi bi-bar-chart me-2"></i>Financial Report</a>
    <a class="nav-link <?=($currentpage == 'click_attendance.php' || $currentpage == 'view_attendance.php' || $currentpage == 'month_attendance.php') ? 'active' : ''?>" href="click_attendance.php"><i class="bi bi-clipboard-data me-2"></i>Attendance</a>
    <a class="nav-link" href="#"><i class="bi bi-folder-plus me-2"></i>Course Management</a>
    <a class="nav-link <?=($currentpage == 'parent_meetings.php') ? 'active' : ''?>" href="parent_meetings.php"><i class="bi bi-person-lines-fill me-2"></i>Parent Meetings</a>
    <a class="nav-link" href="logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a>
  </nav>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
  var toggle = document.getElementById('sidebarToggle');
  var sidebar = document.getElementById('sidebar');
  var overlay = document.getElementById('sidebarOverlay');

  if (toggle && sidebar && overlay) {
    toggle.addEventListener('click', function () {
      sidebar.classList.toggle('show');
      overlay.classList.toggle('show');
    });
    overlay.addEventListener('click', function () {
      sidebar.classList.remove('show');
      overlay.classList.remove('show');
    });
  }
});
</script>
