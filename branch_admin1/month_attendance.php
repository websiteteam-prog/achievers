<?php
ini_set("display_errors", 1);
ini_set("display_startup_errors", 1);
error_reporting(E_ALL);

session_start();

include '../db_config.php';
include 'branch_dashboard_sidebar.php';

if(!isset($_SESSION['admin_logged_in']) || $_SESSION['role'] != 'branch_admin'){
    header("Location: ../login.php");
    exit();
}

$branch_id = $_SESSION['branch_id'];

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $grade = $_POST['grade'];
    $subject = $_POST['subject_id'];
    $month = $_POST['month'];
    
    // fetch students
    $sql1 = $conn->prepare("SELECT students.id as student_id, students.first_name 
                            FROM students
                            WHERE students.grade = ? AND students.branch_id = ?");
    $sql1->bind_param("ii", $grade, $branch_id);
    $sql1->execute();
    $studentResult = $sql1->get_result();
    
    // fetch dates
    $sql2 = $conn->prepare("SELECT DISTINCT date FROM attendance_records
                            WHERE subject_id = ? AND MONTH(date) = ?
                            AND YEAR(date) = YEAR(CURDATE())
                            ORDER BY date ASC");
    $sql2->bind_param("ii", $subject, $month);
    $sql2->execute();
    $datesResult = $sql2->get_result();
    $dates = [];
    
    while($rows = mysqli_fetch_assoc($datesResult)){
        $dates[] = $rows['date'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Grade Wise Attendance</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"/>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet"/>
    <link rel="stylesheet" href="branch.css"/>
    <style>
        .attendance-card {
            background: #fff;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            margin: 20px auto;
            max-width: 95%;
        }
        .attendance-header {
            background: linear-gradient(135deg, #4facfe, #00f2fe);
            color: white;
            padding: 15px;
            border-radius: 12px;
            margin-bottom: 20px;
            text-align: center;
        }
        .attendance-header h4 {
            margin: 0;
            font-weight: 600;
        }
        table {
            border-radius: 12px;
            overflow: hidden;
        }
        table th {
            text-align: center;
            font-weight: 600;
            background-color: #f1f3f4 !important;
        }
        table td {
            text-align: center;
            vertical-align: middle;
        }
        .badge {
            font-size: 0.9rem;
            padding: 8px 12px;
            border-radius: 8px;
        }
    </style>
</head>
<body>
<section class="main">
    <div class="attendance-card">
        <div class="attendance-header">
            <h4>
                📊 Attendance - 
                Grade <?= htmlspecialchars($grade) ?> | 
                <?php
                    echo date("F", mktime(0, 0, 0, $month, 1)); 
                    echo " | ";
                    if($subject == '1'){
                        echo "English";
                    } elseif($subject == '2'){
                        echo "Science";
                    } elseif($subject == '3'){
                        echo "Maths";
                    } else {
                        echo "History";
                    }
                ?>
            </h4>
        </div>
        
        <div class="table-responsive">
            <table class="table table-bordered table-hover table-striped align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Student's Name</th>
                        <?php foreach ($dates as $date): ?>
                            <th><?= date("d M", strtotime($date)) ?></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                <?php while ($student = $studentResult->fetch_assoc()): ?>
                    <tr>
                        <td class="fw-medium text-start ps-3"><?= htmlspecialchars($student['first_name']) ?></td>
                        <?php
                        foreach ($dates as $date) {
                            $stmt = $conn->prepare("SELECT status FROM attendance_records 
                                                    WHERE student_id = ? AND subject_id = ? AND date = ?");
                            $stmt->bind_param("iis", $student['student_id'], $subject, $date);
                            $stmt->execute();
                            $res = $stmt->get_result();
                            if ($row = $res->fetch_assoc()) {
                                $status = strtolower($row['status']);
                                if ($status === "present") {
                                    echo '<td><span class="badge bg-success">Present</span></td>';
                                } elseif ($status === "absent") {
                                    echo '<td><span class="badge bg-danger">Absent</span></td>';
                                } elseif ($status === "late") {
                                    echo '<td><span class="badge bg-warning text-dark">Late</span></td>';
                                } else {
                                    echo '<td><span class="badge bg-secondary">--</span></td>';
                                }
                            } else {
                                echo '<td><span class="badge bg-light text-dark">--</span></td>';
                            }
                        }
                        ?>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>
</body>
</html>
