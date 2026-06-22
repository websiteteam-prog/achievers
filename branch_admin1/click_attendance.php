<?php
session_start();

if(!isset($_SESSION['admin_logged_in']) || $_SESSION['role'] != 'branch_admin'){
    header:("Location: Login.php");
    exit();
}

include 'branch_dashboard_sidebar.php';
?>

<!DOCTYPE HTML>
<html lang ='en'>
<head>
    <meta charset ="UTF_8"/>
    <meta name ="viewport" content = "width=content-width, initial-scale=1.0"/>
    <title>View Attendance</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
    <link rel ="stylesheet" href = "branch.css"/>
</head>

<body>
    <section class ="main">
        <div class = "card-box">
        <div class ="dashboard-card">
            <h5>Select Grade and Subject to view attendance</h5>
            
            <!--Buttons-->
            <form method ="post" action="view_attendance.php" class ="d-flex gap-2 flex-wrap mb-4">
                <select class = "form-select" name = "grade">
                    <option value = "1">Grade 1</option>
                    <option value = "2">Grade 2</option>
                    <option value = "3">Grade 3</option>
                    <option value = "4">Grade 4</option>
                    <option value = "5">Grade 5</option>
                    <option value = "6">Grade 6</option>
                    <option value = "7">Grade 7</option>
                    <option value = "8">Grade 8</option>
                    <option value = "9">Grade 9</option>
                    <option value = "10">Grade 10</option>
                </select>
                <select class ="form-select" name = "subject_id">
                    <option value = "1">English</option>
                    <option value = "2">Science</option>
                    <option value = "3">Maths</option>
                    <option value = "4">History</option>
                </select>
                <button type ="submit" class ="btn btn-primary">View Attendance</button>
            
            </form>
        </div>
        
        <div class ="dashboard-card">
            <h5>Previous month's attendance</h5>
             <!--Buttons-->
            <form method ="post" action="month_attendance.php" class ="d-flex gap-2 flex-wrap mb-4">
                <select class = "form-select" name = "grade">
                    <option value = "1">Grade 1</option>
                    <option value = "2">Grade 2</option>
                    <option value = "3">Grade 3</option>
                    <option value = "4">Grade 4</option>
                    <option value = "5">Grade 5</option>
                    <option value = "6">Grade 6</option>
                    <option value = "7">Grade 7</option>
                    <option value = "8">Grade 8</option>
                    <option value = "9">Grade 9</option>
                    <option value = "10">Grade 10</option>
                </select>
                <select class ="form-select" name = "subject_id">
                    <option value = "1">English</option>
                    <option value = "2">Science</option>
                    <option value = "3">Maths</option>
                    <option value = "4">History</option>
                </select>
                <select class ="form-select" name = "month">
                    <option value = '01'>January</option>
                    <option value = '02'>February</option>
                    <option value = '03'>March</option>
                    <option value = '04'>April</option>
                    <option value = '05'>May</option>
                    <option value = '06'>June</option>
                    <option value = '07'>July</option>
                    <option value = '08'>August</option>
                    <option value = '09'>September</option>
                    <option value = '10'>October</option>
                    <option value = '11'>November</option>
                    <option value = '12'>December</option>
                </select>
                <button type ="submit" class ="btn btn-primary">View Attendance</button>
            
            </form>
        </div>
        </div>
    </section>
</body>