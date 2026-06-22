<?php
session_start();


include '../db_config.php';
include 'branch_dashboard_sidebar.php';

// Check if logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['role'] !== 'branch_admin') {
    header("Location: login.php");
    exit();
}

$branch_id = $_SESSION['branch_id'];
$admin_email = $_SESSION['admin_email'];


$sql = "select admins.name as admin_name, branches.branch_name , branches.branch_address from admins join branches on admins.branch_id = branches.id where email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $admin_email);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();
?>




<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Branch Admin Dashboard</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
  <link rel ="stylesheet" href = "branch.css"/>
  
  <style>
     .dashboard-img {
         height = 120px !important;
         width = 120px !important;
      }
  </style>
</head>

<body>
  
  <div class="main">
       <div class="my-5">
    <div class="card p-4 shadow">
        <h2 class="mb-4">Welcome, <?php echo $data['admin_name']; ?> 👋</h2>
        <p><strong>Branch Name:</strong> <?php echo $data['branch_name']; ?></p>
        <p><strong>Branch Address:</strong> <?php echo $data['branch_address']; ?></p>
    </div>
</div>
    <h1>Branch Admin Dashboard</h1>
    <div class="card-box">
      <div class="dashboard-card">
        <h5>Manage Teachers & Students</h5>
        <div class = "row">
            
            <!--Students section-->
            
            <div class = "col-6 border-end">
                <h6>Students</h6>
                <img src = "../images/vector-student-1.png" alt = "student's image"/>
                <a href= "branch_students.php" class = "btn btn-primary btn-sm">View Students</a>
            </div>
            
            <!--Teacher Section-->
            <div class = "col-6 border-end">
                <h6>Teachers</h6>
                <img src = "../images/teacher-2-1.png" alt = "teacher's image"/>
                <a href ="branch_teacher.php" class = "btn btn-primary btn-sm">View Teachers</a>
            </div>
        </div>
        <!--<button class="btn btn-success">Add</button>-->
        <!--<button class="btn btn-danger">Delete</button>-->
      </div>

      <div class="dashboard-card">
        <h5>Suggest Course Changes</h5>
        <textarea class="form-control" id = "suggestion" placeholder="Enter your suggestions..."></textarea>
        <select class ="form-control" id = "subject">
            <option value =''>Choose Subject</option>
            <option value ='1'>English</option>
            <option value ='2'>Science</option>
            <option value ='3'>Maths</option>
            <option value ='4'>History</option>
        </select>
        <button class="btn btn-warning" id = "course_suggest">Submit</button>
      </div>

      <div class="dashboard-card">
        <h5>Branch Calendar</h5>
        <input type="date" class="form-control" id ="event_date">
        <input type="text" class="form-control" placeholder="Event Title" id ="event_title">
        <input type="text" class="form-control" placeholder="Event Agenda" id ="event_agenda">
        <button type ="button" class="btn btn-info text-white" id ="add_event">Add Event</button>
      </div>

      <div class="dashboard-card">
        <h5>Branch Financials</h5>
          <button class="btn btn-primary" onclick="window.location.href='financial_report.php'">View Report</button>
        <img src = "../images/financial_icon.png" alt = "financial's image" class = "dashboard-img"/>
      
      </div>

      <div class="dashboard-card">
        <h5>Parent Meetings</h5>
        <input type="date" class="form-control" id="meeting_date">
        <input type="number" class="form-control" placeholder="Enter Student ID" id="student_id">
        <input type="text" class="form-control" placeholder="Agenda of Meeting" id="agenda">
        <button type ="button" class="btn btn-secondary" id="add_meeting">Schedule</button>
      </div>

      <div class="dashboard-card">
        <h5>Enrollment</h5>
        <button class="btn btn-dark">Open Form</button>
      </div>

      <div class="dashboard-card">
        <h5>Teacher Calendar</h5>
        <button class="btn btn-outline-primary">View</button>
      </div>

      <!--<div class="dashboard-card">-->
      <!--  <h5>Student Attendance</h5>-->
      <!--  <button class="btn btn-outline-success" onclick = "fetch_attendance()">Track</button>-->
      <!--</div>-->

      <div class="dashboard-card">
        <h5>Assign Chapter</h5>
        <select class="form-control">
          <option>Select Student</option>
        </select>
        <select class="form-control">
          <option>Select Chapter</option>
        </select>
        <button class="btn btn-success">Assign</button>
      </div>

      <div class="dashboard-card">
        <h5>Upload Exam Results</h5>
        <input type="file" class="form-control">
        <button class="btn btn-outline-secondary">Upload</button>
      </div>
    </div>
  </div>
  <script src ="branch_dashboard.js"></script>
</body>

<script>
   function fetch_attendance() {
  fetch('attendance.php')
    .then(response => response.text()) // or .json() if you're returning JSON
    .then(data => {
      // Show attendance data in alert OR insert in DOM
      alert("Attendance Data:\n" + data);
    })
    .catch(error => {
      console.error('Error fetching attendance:', error);
    });
}

</script>

</html>