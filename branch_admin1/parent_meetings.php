<?php
ini_set("display_errors", 1);
ini_set("display_startup_errors", 1);
error_reporting(E_ALL);

session_start();

include '../db_config.php';
include 'branch_dashboard_sidebar.php';

if(!isset($_SESSION['admin_logged_in']) || $_SESSION['role'] != 'branch_admin'){
    header("Location:../login.php");
    exit();
}

$branch_id = $_SESSION['branch_id'];

$stmt = $conn->prepare("select students.first_name, students.parent_name, students.parent_contact, meetings.agenda, meetings.date from meetings
                        JOIN students on students.id = meetings.students_id
                        where date BETWEEN DATE_SUB(CURDATE(), INTERVAL 7 DAY) AND CURDATE() 
   OR date > CURDATE() and branch_id = ?");
$stmt->bind_param("i", $branch_id);
$stmt->execute();

$result = $stmt->get_result();
?>

<!DOCTYPE HTML>
<html lang="en">
<head>
    <meta charset = "UTF-8">
    <meta name = "viewport" content = "width=content-width initial-scale =1.0">
    <title>Parents Meetings</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
    <link rel ="stylesheet" href = "branch.css"/>
</head>

<body>
    <section class = "main">
        <div class = "meeting-info">
            <?php
            if(mysqli_num_rows($result)>0){
                echo "<table border ='1' cellpadding = '8'>";
                echo "<tr>
                      <th>Student's Name</th>
                      <th>Parent's Name</th>
                      <th>Parent's Contact</th>
                      <th>Agenda</th>
                      <th>Date</th>";
                      
                      while($rows = mysqli_fetch_assoc($result)){
                          echo "<tr>
                                <td>{$rows['first_name']}</td>
                                <td>{$rows['parent_name']}</td>
                                <td>{$rows['parent_contact']}</td>
                                <td>{$rows['agenda']}</td>
                                <td>{$rows['date']}</td>
                                </tr>";
                      }
                      echo "</table>";
            }
            ?>
        </div>
    </section>
</body>