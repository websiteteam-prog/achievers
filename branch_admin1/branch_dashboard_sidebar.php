<?php
$currentpage = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE HTML>
<html lang ='en'>
    <head>
          <meta charset="UTF-8" />
          <meta name="viewport" content="width=device-width, initial-scale=1.0" />
          <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
          <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
        <title>Dashboard Sidebar</title>
        
    </head>    
    
    <body>
  
  <div class="sidebar">
    <h2>Branch Panel</h2>
    <nav class="nav flex-column">
      <a class="nav-link <?=($currentpage == 'branch_dashboard.php') ? 'active' : ''?> " href="branch_dashboard.php"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a>
      <a class="nav-link <?=($currentpage == 'branch_manage_users.php') ? 'active' : ''?> " href="branch_manage_users.php"><i class="bi bi-people me-2"></i>Manage Users</a>
      <a class="nav-link <?=($currentpage == 'branch_calender.php') ? 'active' : ''?> " href="branch_calender.php"><i class="bi bi-calendar3 me-2"></i>Branch Calendar</a>
      <a class="nav-link <?=($currentpage == 'financial_report.php')? 'active' : ''?>" href="financial_report.php"><i class="bi bi-bar-chart me-2"></i>Financial Report</a>
      <a class="nav-link <?=($currentpage == 'click_attendance.php' || $currentpage == 'view_attendance.php' || $currentpage == 'month_attendance.php') ? 'active' : ''?>" href="click_attendance.php"><i class="bi bi-clipboard-data me-2"></i>Attendance</a>
      <a class="nav-link" href="#"><i class="bi bi-folder-plus me-2"></i>Course Management</a>
      <a class="nav-link <?=($currentpage == 'parent_meetings.php') ? 'active' : ''?> " href="parent_meetings.php"><i class="bi bi-person-lines-fill me-2"></i>Parent Meetings</a>
      <a class="nav-link" href="logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a>
    </nav>
  </div>
  </body>
  
  </html>