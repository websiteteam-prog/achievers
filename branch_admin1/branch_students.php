<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);






session_start();
include '../db_config.php';

// echo '<link rel="stylesheet" href="branch.css">';

include 'branch_dashboard_sidebar.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['role'] != 'branch_admin') {
    header("Location: login.php");
    exit();
}



// to get the columns from the students table to edit the detail
$table = "students";
$database = "mrmukpe4_creativetheka";


$sql2 = "select column_name from information_schema.columns
         where table_name = '$table' and table_schema = '$database'";
         
$result2 = mysqli_query($conn, $sql2); 





$branch_id = $_SESSION['branch_id'];
$admin_email = $_SESSION['admin_email'];


$sql = "SELECT 
            students.id, 
            students.first_name, 
            students.grade, 
            students.email,
            students.phone,
            students.parent_name, 
            students.parent_contact,
            students.mode_of_education,
            GROUP_CONCAT(subjects.subject_name SEPARATOR ', ') AS subject_name 
        FROM students
        JOIN student_subjects ON students.id = student_subjects.student_id
        JOIN subjects ON subjects.id = student_subjects.subject_id
        WHERE students.branch_id = '$branch_id'
        GROUP BY students.id";

$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) > 0) {
    echo "<div class = 'main'><h2>Students in the Branch</h2>";
    echo "<table border='1' cellpadding='8'>";
    echo "<tr>
            <th>Student ID</th>
            <th>Name</th>
            <th>Grade</th>
            <th>Email</th>
            <th>Contact</th>
            <th>Parent's Name</th>
            <th>Parent's Contact</th>
            <th>Enrolled Subject</th>
            <th>Mode</th>
            <th>Action</th>
          </tr></div>";
          
          echo "        <script>
            function toggleOption(btn){
                const optionsDiv = btn.nextElementSibling;
                optionsDiv.classList.toggle('d-none');
            }
        </script>";

    while ($row = mysqli_fetch_assoc($result)) {
        echo "<tr>
                <td>{$row['id']}</td>
                <td>{$row['first_name']}</td>
                <td>{$row['grade']}</td>
                <td>{$row['email']}</td>
                <td>{$row['phone']}</td>
                <td>{$row['parent_name']}</td>
                <td>{$row['parent_contact']}</td>
                <td>{$row['subject_name']}</td>
                <td>{$row['mode_of_education']}</td>
                 <td> <button type ='button' class = 'btn btn-outline-success' onclick = 'toggleOption(this)'>Edit</button>
                      <div class = 'edit-options d-none mt-2'>
                          <button type = 'button' class = 'btn btn-outline-primary btn-sm' data-bs-toggle= 'modal' data-bs-target='#updateStudentModal'>Edit Detail</button>
                          <button  type = 'button' class = 'btn btn-outline-primary btn-sm' data-bs-toggle = 'modal' data-bs-target = '#updateStudentSubjectModal'>Edit Subject</button>
                      </div>      
              </td>
              </tr>";
    }

    echo "</table>";
} else {
    echo "No students found for this branch.";
}
?>

<!DOCTYPE HTML>
<html lang ='en'>
    <head>
        <meta charset = "UTF-8"/>
        <meta name ="viewport" content = "width = device-width, initial-scale = 1.0"/>
        <title>Students in a Branch</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
        <link rel ="stylesheet" href = "branch.css"/>
        <style>
               .d-none { display: none; }
        </style>
    </head>
    
    <body>
        
        <!--Bootstrap Modal for Updating the detail of the teacher-->
        <div class ="modal fade" id ="updateStudentModal" tabindex = "-1" aria-labelledby = "updateStudentModalLabel" aria-hidden = "true">
            <div class ="modal-dialog modal-dialog-centered">
                <div class = "modal-content">
                    
                    
                    <div class ="modal-header">
                        <h5 class ="modal-title" id = "updateStudentModalLabel">Update Student Detail</h5>
                        <button type = "button" class ="btn-close" data-bs-dismiss ="modal" aria-label ="close"></button>
                    </div>
                    
                    <form action = "update_student_detail.php" method ="POST">
                        <div class ="modal-body">
                            
                            <div class ="mb-3">
                                <label>Choose detail to update</label>
                                <select class ="form-select" name = "detail" required>
                                 <option></option>
                                 <?php
                                 $skip_columns = ['id' , 'password', 'gender', 'created_at', 'branch_id', 'course_id', 'course_title', 'relationship', 'total', 'dob', 'payment_id', 'last_name', 'payment_status', 'price', 'gst', 'payment_type'];
                                    
                                    if(mysqli_num_rows($result2)>0){
                                        while ($rows = mysqli_fetch_assoc($result2)){
                                            $col = $rows['column_name'];
                                             if (in_array($col, $skip_columns)) {
                                                    continue; // Skip this column
                                                }
                                            echo "<option value ='" . htmlspecialchars($rows['column_name']) ."'>". htmlspecialchars($rows['column_name']) . "</option>";
                                        }
                                    }else{
                                        echo "<option disabled>No Column Found</option>";
                                    }
                                 ?>
                                </select>
                            </div>
                            
                            <div class ="mb-3">
                                <label>Student ID</label>
                                <input type ="number" class ="form-control" name ="student_id" required/>
                                
                            </div>
                            
                            <div class= "mb-3">
                                <label>New Value</label>
                                <input type = "text" class ="form-control" name ="new_value" required/>
                            </div>
                            
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-success">Update Student</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        
        <!--Bootstrap modal for adding and deleting the teacher-->
        <div class ="modal fade" id = "updateStudentSubjectModal" tab-index = -1 aria-labelledby = "updateStudentSubjectModalLabel">
            <div class ="modal-dialog modal-dialog-centered">
                <div class ="modal-content">
                    
                    
                    <div class = "modal-header">
                        <h5 class = "modal-title" id = "updateStudentSubjectModalLabel">Update Subjects of Student</h5>
                        <button type = "button" class= "btn-close" data-bs-dismiss = "modal" aria-label ="close"></button>
                    </div>
                    
                    <form action ="update_student_subject.php" method = "POST">
                        <div class ="modal-body">
                            
                            <div class ="mb-3">
                                <label>Choose Subject</label>
                                <select class ="form-select" name = "subject" required/>
                                <option>Choose Subject</option>
                                <option value = "1">English</option>
                                <option value = "2">Science</option>
                                <option value = "3">Maths</option>
                                <option value = "4">History</option>
                                </select>
                            </div>
                            
                            <div class ="mb-3">
                                <label>Student ID</label>
                                <input type = "number" class = "form-select" name ="student_id" required/>
                            </div>
                            
                            <div class ="modal-footer">
                                <button type ="submit" name = "action" value ="add" class = "btn btn-success">Add Subject</button>
                                <button type ="submit" name = "action" value ="delete" class = "btn btn-danger">Remove Subject</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
         

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    </body>
</html>

