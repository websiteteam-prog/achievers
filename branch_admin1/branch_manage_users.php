<?php
session_start();
include '../db_config.php';
include 'branch_dashboard_sidebar.php';

// Check if branch admin logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['role'] !== 'branch_admin') {
    header("Location: login.php");
    exit();
}

$branch_id = $_SESSION['branch_id'];
$admin_email = $_SESSION['admin_email'];

// Get subjects
$subjects = [];
$subSql = "SELECT id, subject_name FROM subjects";
$subRes = mysqli_query($conn, $subSql);
if ($subRes && mysqli_num_rows($subRes) > 0) {
    while ($row = mysqli_fetch_assoc($subRes)) {
        $subjects[] = $row;
    }
}

// Get teachers table columns (for update)
$columns = [];
$table = "teachers";
$database = "mrmukpe4_creativetheka";

$colSql = "SELECT column_name FROM information_schema.columns 
           WHERE table_name = '$table' AND table_schema = '$database'";
$colRes = mysqli_query($conn, $colSql);

$skip_columns = ['id', 'created_at', 'updated_at', 'is_active', 'allowed_ip', 'last_activity', 'status'];
if ($colRes && mysqli_num_rows($colRes) > 0) {
    while ($row = mysqli_fetch_assoc($colRes)) {
        if (!in_array($row['column_name'], $skip_columns)) {
            $columns[] = $row['column_name'];
        }
    }
}

// Get students table columns (for update)
$columns2 = [];
$table2 = "students";
$database = "mrmukpe4_creativetheka";

$col2sql = "select column_name from information_schema.columns
            where table_name = '$table2' And table_schema = '$database'";
$col2Res = mysqli_query($conn, $col2sql);

$skip_columns = ['id', 'dob', 'created_at', 'payment_id', 'price', 'gst', 'total', 'payment_type', 'relationship', 'password'];
if($col2Res && mysqli_num_rows($col2Res) > 0){
    while ($row = mysqli_fetch_assoc($col2Res)){
        if(!in_array($row['column_name'], $skip_columns)) {
            $columns2[] = $row['column_name'];
        }
    }
}
?>
<!DOCTYPE HTML>
<html lang="en">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/> 
    <title>Branch Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"/>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet"/>
    <link rel="stylesheet" href="branch.css"/>
</head>
<body>

<!-- Teachers Section -->
<section class="teachers main">
    <div class="row">
        <div class="card-box">
            <div class="dashboard-card d-flex justify-content-between align-items-center">
                
                <!-- Heading -->
                <h2 class="mb-0">Teachers</h2>

                <!-- Buttons -->
                <div class="d-flex gap-2">
                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addTeacherModal">
                        Add Teacher
                    </button>
                    <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#updateTeacherModal">
                        Update Teacher Detail
                    </button>
                    <button class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#updateTeacherSubjectModal">
                        Update Teacher Subjects
                    </button>
                </div>

            </div>
        </div>
    </div>
</section>
<!-- Students Section -->
<section class="students main mt-4">
    <div class="row">
        <div class="card-box">
            <div class="dashboard-card d-flex justify-content-between align-items-center">
                
                    <h2 class="mb-0">Students</h2>
                    
                    <div class ="d-flex gap-2">
                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addStudentModal">Add Student</button>
                    <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#updateStudentModal">
                        Update Student Detail
                    </button>
                    <button class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#updateStudentSubjectModal">
                        Update Student Subjects
                    </button>
                    </div>
                
            </div>
        </div>
    </div>
</section>


<!-- ========== Modals Start ========== -->

<!-- Add Teacher Modal -->
<div class="modal fade" id="addTeacherModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="insert_new_teacher.php" method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Teacher</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="text" class="form-control mb-2" name="name" placeholder="Name" required>
                    <input type="email" class="form-control mb-2" name="email" placeholder="Email" required>
                    <input type="password" class="form-control mb-2" name="password" placeholder="Password" required>
                    <input type="text" class="form-control mb-2" name="branch" placeholder="Branch" required>
                    <input type="tel" class="form-control mb-2" name="contact_no" placeholder="Contact No." required>
                    
                    <label>Subjects</label>
                    <select class="form-select" name="subject[]" multiple required>
                        <option disabled>Select Subjects</option>
                        <?php foreach ($subjects as $sub): ?>
                            <option value="<?= $sub['id'] ?>"><?= htmlspecialchars($sub['subject_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <small class="form-text text-muted">Hold Ctrl/Cmd to select multiple</small>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button class="btn btn-success" type="submit">Add Teacher</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Update Teacher Detail Modal -->
<div class="modal fade" id="updateTeacherModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="update_teacher_detail.php" method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">Update Teacher Detail</h5>
                    <button class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <label>Choose Field</label>
                    <select class="form-select mb-2" name="detail" required>
                        <option disabled selected>Select Field</option>
                        <?php foreach ($columns as $col): ?>
                            <option value="<?= $col ?>"><?= ucfirst($col) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <input type="number" class="form-control mb-2" name="teacher_id" placeholder="Teacher ID" required>
                    <input type="text" class="form-control mb-2" name="new_value" placeholder="New Value" required>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button class="btn btn-success" type="submit">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Update Teacher Subject Modal -->
<div class="modal fade" id="updateTeacherSubjectModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="update_teacher_subject.php" method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">Update Teacher Subjects</h5>
                    <button class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <select class="form-select mb-2" name="subject" required>
                        <option disabled selected>Select Subject</option>
                        <?php foreach ($subjects as $sub): ?>
                            <option value="<?= $sub['id'] ?>"><?= htmlspecialchars($sub['subject_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <input type="number" class="form-control mb-2" name="teacher_id" placeholder="Teacher ID" required>
                </div>
                <div class="modal-footer">
                    <button type="submit" name="action" value="add" class="btn btn-success">Add Subject</button>
                    <button type="submit" name="action" value="delete" class="btn btn-danger">Remove Subject</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Student Modal -->
<div class="modal fade" id="addStudentModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="insert_new_student.php" method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Student</h5>
                    <button class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="text" class="form-control mb-2" name="name" placeholder="Name" required>
                    <input type="tel" class="form-control mb-2" name="phone" placeholder="Phone" required>
                    <input type="email" class="form-control mb-2" name="studentsEmail" placeholder="Email" required>
                    <input type="text" class="form-control mb-2" name="address" placeholder="Address" required>
                    
                    <div class="mb-2">
                        <label>Gender:</label><br>
                        <input type="radio" name="gender" value="male" required> Male
                        <input type="radio" name="gender" value="female"> Female
                        <input type="radio" name="gender" value="others"> Others
                    </div>
                    
                    <input type="date" class="form-control mb-2" name="dob" required>
                    
                    <label>Subjects</label>
                    <select class="form-select mb-2" name="subject[]" multiple required>
                        <option disabled>Select Subjects</option>
                        <?php foreach ($subjects as $sub): ?>
                            <option value="<?= $sub['id'] ?>"><?= htmlspecialchars($sub['subject_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    
                    <input type="number" class="form-control mb-2" name="grade" placeholder="Grade" required>
                    
                    <label>Mode of Education</label>
                    <select class="form-select mb-2" name="mode" required>
                        <option disabled selected>Select Mode</option>
                        <option value="physical">Offline</option>
                        <option value="online">Online</option>
                    </select>
                    
                    <input type="text" class="form-control mb-2" name="parentsName" placeholder="Parent's Name" required>
                    <input type="tel" class="form-control mb-2" name="parentsPhone" placeholder="Parent's Phone" required>
                    <input type="email" class="form-control mb-2" name="parentsEmail" placeholder="Parent's Email" required>
                    
                    <label>Mode of Payment</label>
                    <select class="form-select mb-2" name="modeOfPayment" required>
                        <option disabled selected>Select Payment</option>
                        <option value="cash">Cash</option>
                        <option value="online">Online</option>
                    </select>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button class="btn btn-success" type="submit">Add Student</button>
                </div>
            </form>
        </div>
    </div>
</div>


<!--Update Student Modal-->
<div class = "modal fade" id = "updateStudentModal" tabindex = "-1">
    <div class = "modal-dialog modal-dialog-centered">
        <div class = "modal-content">
            <form action = "update_student_detail.php" method = "POST">
                <div class = "modal-header">
                    <h5 class = "modal-title">Update Student Detail</h5>
                    <button class ="btn-close" data-bs-dismiss ="modal"></button>
                </div>
                <div class = "modal-body">
                    <label>Choose Field</label>
                    <select class="form-select mb-2" name="detail" required>
                        <option disabled selected>Select Field</option>
                        <?php foreach ($columns2 as $col): ?>
                            <option value="<?= $col ?>"><?= ucfirst($col) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <input type="number" class="form-control mb-2" name="student_id" placeholder="Student ID" required>
                    <input type="text" class="form-control mb-2" name="new_value" placeholder="New Value" required>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button class="btn btn-success" type="submit">Update</button>
                </div>
                </div>
            </form>
        </div>
    </div>
</div>


<!--Update subjects of the student-->

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

<!-- ========== Modals End ========== -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
