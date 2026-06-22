<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


session_start();

include '../db_config.php';
include 'branch_dashboard_sidebar.php';

if(!isset($_SESSION['admin_logged_in']) || $_SESSION['role'] != 'branch_admin') {
    header("Location: login.php");
    exit();
}

$branch_id = $_SESSION['branch_id'];
$admin_email = $_SESSION['admin_email'];




// to get the subject names from the database

$sql1 = "select id, subject_name from subjects";
$result1 = mysqli_query($conn, $sql1);


// to get the column name from the database which can be updated

$table = "teachers";
$database = "mrmukpe4_creativetheka";


$sql2 = "select column_name from information_schema.columns
         where table_name = '$table' and table_schema = '$database'";
         
$result2 = mysqli_query($conn, $sql2); 






$sql = "SELECT 
             teachers.id, 
             teachers.name, 
             teachers.email, 
             GROUP_CONCAT(subjects.subject_name SEPARATOR ', ') AS subjects, 
             teachers.contact_no, 
             teachers.branch
            FROM teachers
                    JOIN branches ON teachers.branch = branches.branch_name
                    JOIN teacher_subjects ON teachers.id = teacher_subjects.teacher_id
                    JOIN subjects ON teacher_subjects.subject_id = subjects.id
                     WHERE branches.id = '$branch_id'
                     Group by teachers.id";

$result = mysqli_query($conn, $sql);




if(mysqli_num_rows($result)>0){
    echo "<div class ='main'>";
    echo "<h2>Teachers in the branch</h2>";
    echo "<table border = '1' cellpadding = '8'>";
    echo "<tr>
          <th>Teacher ID</th>
          <th>Name</th>
          <th>Email</th>
          <th>Subject</th>
          <th>Contact No.</th>
          <th>Branch</th>
          <th>Action</th>
          </tr></div>";
          
    while($row = mysqli_fetch_assoc($result))  {
        echo "<tr>
              <td>{$row['id']}</td>
              <td>{$row['name']}</td>
              <td>{$row['email']}</td>
              <td>{$row['subjects']}</td>
              <td>{$row['contact_no']}</td>
              <td>{$row['branch']}</td>
              <td> <button type ='button' class = 'btn btn-outline-success' onclick = 'toggleOption(this)'>Edit</button>
                      <div class = 'edit-options d-none mt-2'>
                          <button type = 'button' class = 'btn btn-outline-primary btn-sm' data-bs-toggle= 'modal' data-bs-target='#updateTeacherModal'>Edit Detail</button>
                          <button  type = 'button' class = 'btn btn-outline-primary btn-sm' data-bs-toggle = 'modal' data-bs-target = '#updateTeacherSubjectModal'>Edit Subject</button>
                      </div>      
              </td>
              </tr>";
    }      
    
    echo "</table>";
}

else {
    echo "No Teachers in this Branch";
}
?>


<!DOCTYPE HTML>
<html lang ='en'>
    <head>
        <meta charset = "UTF-8"/>
        <meta name ="viewport" content = "width = device-width, initial-scale = 1.0"/>
        <title>Teachers in a Branch</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
        <link rel ="stylesheet" href = "branch.css"/>
        <style>
               .d-none { display: none; }
        </style>
    </head>
    
    <body>
        
        <!--Bootstrap Modal for Updating the detail of the teacher-->
        <div class ="modal fade" id ="updateTeacherModal" tabindex = "-1" aria-labelledby = "updateTeacherModal" aria-hidden = "true">
            <div class ="modal-dialog modal-dialog-centered">
                <div class = "modal-content">
                    
                    
                    <div class ="modal-header">
                        <h5 class ="modal-title" id = "updateTeacherModalLabel">Update Teacher Detail</h5>
                        <button type = "button" class ="btn-close" data-bs-dismiss ="modal" aria-label ="close"></button>
                    </div>
                    
                    <form action = "update_teacher_detail.php" method ="POST">
                        <div class ="modal-body">
                            
                            <div class ="mb-3">
                                <label>Choose detail to update</label>
                                <select class ="form-select" name = "detail" required>
                                    <option></option>
                                    <?php
                                    
                                    
                                    $skip_columns = ['id', 'created_at', 'updated_at', 'is_active', 'allowed_ip', 'last_activity', 'status'];
                                    
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
                                <label>Teacher ID</label>
                                <input type ="number" class ="form-control" name ="teacher_id" required/>
                                
                            </div>
                            
                            <div class= "mb-3">
                                <label>New Value</label>
                                <input type = "text" class ="form-control" name ="new_value" required/>
                            </div>
                            
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-success">Update Teacher</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        
        <!--Bootstrap modal for adding and deleting the teacher-->
        <div class ="modal fade" id = "updateTeacherSubjectModal" tab-index = -1 aria-labelledby = "updateTeacherSubjectModal">
            <div class ="modal-dialog modal-dialog-centered">
                <div class ="modal-content">
                    
                    
                    <div class = "modal-header">
                        <h5 class = "modal-title" id = "updateTeacherSubjectModalLabel">Update Subjects of Teacher</h5>
                        <button type = "button" class= "btn-close" data-bs-dismiss = "modal" aria-label ="close"></button>
                    </div>
                    
                    <form action ="update_teacher_subject.php" method = "POST">
                        <div class ="modal-body">
                            
                            <div class ="mb-3">
                                <label>Choose Subject</label>
                                <select class ="form-select" name = "subject" required/>
                                <option></option>
                                <?php
                                
                                if(mysqli_num_rows($result1)>0){
                                    while ($rows = mysqli_fetch_assoc($result1)){
                                        echo "<option value ='". htmlspecialchars($rows['id'])."' > ".htmlspecialchars($rows['subject_name']) . "</option>";
                                    }
                                }
                                ?>
                                </select>
                            </div>
                            
                            <div class ="mb-3">
                                <label>Teacher ID</label>
                                <input type = "number" class = "form-select" name ="teacher_id" required/>
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
         
        <script >
            function toggleOption(btn){
                const optionsDiv = btn.nextElementSibling;
                optionsDiv.classList.toggle('d-none');
                
            }
            
         
        </script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    </body>
</html>

               