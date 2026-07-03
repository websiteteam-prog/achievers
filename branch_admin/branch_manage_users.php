<?php
require_once __DIR__ . '/auth.php';
require_branch_admin();

$branch_id = $_SESSION['branch_id'];

// Subjects
$subjects = [];
$subStmt = $conn->prepare("SELECT id, subject_name FROM subjects");
$subStmt->execute();
$subRes = $subStmt->get_result();
while ($row = $subRes->fetch_assoc()) {
    $subjects[] = $row;
}

// Editable teacher columns
$teacherSkip = ['id', 'password', 'created_at', 'updated_at', 'is_active', 'allowed_ip', 'last_activity', 'status'];
$teacherCols = [];
$tcStmt = $conn->prepare("SELECT column_name FROM information_schema.columns WHERE table_name = 'teachers' AND table_schema = DATABASE()");
$tcStmt->execute();
$tcRes = $tcStmt->get_result();
while ($row = $tcRes->fetch_assoc()) {
    if (!in_array($row['column_name'], $teacherSkip, true)) {
        $teacherCols[] = $row['column_name'];
    }
}

// Editable student columns
$studentSkip = ['id', 'password', 'dob', 'created_at', 'payment_id', 'price', 'gst', 'total', 'payment_type', 'relationship', 'branch_id', 'course_id'];
$studentCols = [];
$scStmt = $conn->prepare("SELECT column_name FROM information_schema.columns WHERE table_name = 'students' AND table_schema = DATABASE()");
$scStmt->execute();
$scRes = $scStmt->get_result();
while ($row = $scRes->fetch_assoc()) {
    if (!in_array($row['column_name'], $studentSkip, true)) {
        $studentCols[] = $row['column_name'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Manage Users</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
  <link rel="stylesheet" href="branch.css" />
</head>
<body>
  <?php include 'branch_dashboard_sidebar.php'; ?>

  <main class="main">
    <?php flash_render(); ?>
    <h1>Manage Users</h1>

    <div class="card-box">
      <div class="dashboard-card">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
          <h5 class="mb-0">Teachers</h5>
          <div class="d-flex gap-2 flex-wrap">
            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addTeacherModal">Add Teacher</button>
            <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#updateTeacherModal">Update Detail</button>
            <button class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#updateTeacherSubjectModal">Update Subjects</button>
          </div>
        </div>
      </div>

      <div class="dashboard-card">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
          <h5 class="mb-0">Students</h5>
          <div class="d-flex gap-2 flex-wrap">
            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addStudentModal">Add Student</button>
            <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#updateStudentModal">Update Detail</button>
            <button class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#updateStudentSubjectModal">Update Subjects</button>
          </div>
        </div>
      </div>
    </div>
  </main>

  <!-- Add Teacher -->
  <div class="modal fade" id="addTeacherModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <form action="insert_new_teacher.php" method="POST">
          <div class="modal-header">
            <h5 class="modal-title">Add New Teacher</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <input type="text" class="form-control mb-2" name="name" placeholder="Name" required />
            <input type="email" class="form-control mb-2" name="email" placeholder="Email" required />
            <input type="password" class="form-control mb-2" name="password" placeholder="Password" required />
            <input type="text" class="form-control mb-2" name="branch" placeholder="Branch" required />
            <input type="tel" class="form-control mb-2" name="contact_no" placeholder="Contact No." required />
            <label class="form-label">Subjects</label>
            <select class="form-select" name="subject[]" multiple required>
              <?php foreach ($subjects as $sub): ?>
                <option value="<?= e($sub['id']) ?>"><?= e($sub['subject_name']) ?></option>
              <?php endforeach; ?>
            </select>
            <small class="form-text text-muted">Hold Ctrl/Cmd to select multiple</small>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-success">Add Teacher</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Update Teacher Detail -->
  <div class="modal fade" id="updateTeacherModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <form action="update_teacher_detail.php" method="POST">
          <div class="modal-header">
            <h5 class="modal-title">Update Teacher Detail</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <label class="form-label">Choose Field</label>
            <select class="form-select mb-2" name="detail" required>
              <option value="">Select Field</option>
              <?php foreach ($teacherCols as $col): ?>
                <option value="<?= e($col) ?>"><?= e(ucwords(str_replace('_', ' ', $col))) ?></option>
              <?php endforeach; ?>
            </select>
            <input type="number" class="form-control mb-2" name="teacher_id" placeholder="Teacher ID" required />
            <input type="text" class="form-control mb-2" name="new_value" placeholder="New Value" required />
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-success">Update</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Update Teacher Subjects -->
  <div class="modal fade" id="updateTeacherSubjectModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <form action="update_teacher_subject.php" method="POST">
          <div class="modal-header">
            <h5 class="modal-title">Update Teacher Subjects</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <select class="form-select mb-2" name="subject" required>
              <option value="">Select Subject</option>
              <?php foreach ($subjects as $sub): ?>
                <option value="<?= e($sub['id']) ?>"><?= e($sub['subject_name']) ?></option>
              <?php endforeach; ?>
            </select>
            <input type="number" class="form-control mb-2" name="teacher_id" placeholder="Teacher ID" required />
          </div>
          <div class="modal-footer">
            <button type="submit" name="action" value="add" class="btn btn-success">Add Subject</button>
            <button type="submit" name="action" value="delete" class="btn btn-danger">Remove Subject</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Add Student -->
  <div class="modal fade" id="addStudentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <form action="insert_new_student.php" method="POST">
          <div class="modal-header">
            <h5 class="modal-title">Add New Student</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <input type="text" class="form-control mb-2" name="name" placeholder="Name" required />
            <input type="tel" class="form-control mb-2" name="phone" placeholder="Phone" required />
            <input type="email" class="form-control mb-2" name="studentsEmail" placeholder="Email" required />
            <input type="text" class="form-control mb-2" name="address" placeholder="Address" required />
            <div class="mb-2">
              <label class="form-label d-block">Gender</label>
              <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="gender" value="male" id="genderMale" required />
                <label class="form-check-label" for="genderMale">Male</label>
              </div>
              <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="gender" value="female" id="genderFemale" />
                <label class="form-check-label" for="genderFemale">Female</label>
              </div>
              <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="gender" value="others" id="genderOthers" />
                <label class="form-check-label" for="genderOthers">Others</label>
              </div>
            </div>
            <label class="form-label">Date of Birth</label>
            <input type="date" class="form-control mb-2" name="dob" required />
            <label class="form-label">Subjects</label>
            <select class="form-select mb-2" name="subject[]" multiple required>
              <?php foreach ($subjects as $sub): ?>
                <option value="<?= e($sub['id']) ?>"><?= e($sub['subject_name']) ?></option>
              <?php endforeach; ?>
            </select>
            <input type="number" class="form-control mb-2" name="grade" placeholder="Grade" required />
            <label class="form-label">Mode of Education</label>
            <select class="form-select mb-2" name="mode" required>
              <option value="" selected disabled>Select Mode</option>
              <option value="physical">Offline</option>
              <option value="online">Online</option>
            </select>
            <input type="text" class="form-control mb-2" name="parentsName" placeholder="Parent's Name" required />
            <input type="tel" class="form-control mb-2" name="parentsPhone" placeholder="Parent's Phone" required />
            <input type="email" class="form-control mb-2" name="parentsEmail" placeholder="Parent's Email" required />
            <label class="form-label">Mode of Payment</label>
            <select class="form-select mb-2" name="modeOfPayment" required>
              <option value="" selected disabled>Select Payment</option>
              <option value="cash">Cash</option>
              <option value="online">Online</option>
            </select>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-success">Add Student</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Update Student Detail -->
  <div class="modal fade" id="updateStudentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <form action="update_student_detail.php" method="POST">
          <div class="modal-header">
            <h5 class="modal-title">Update Student Detail</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <label class="form-label">Choose Field</label>
            <select class="form-select mb-2" name="detail" required>
              <option value="">Select Field</option>
              <?php foreach ($studentCols as $col): ?>
                <option value="<?= e($col) ?>"><?= e(ucwords(str_replace('_', ' ', $col))) ?></option>
              <?php endforeach; ?>
            </select>
            <input type="number" class="form-control mb-2" name="student_id" placeholder="Student ID" required />
            <input type="text" class="form-control mb-2" name="new_value" placeholder="New Value" required />
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-success">Update</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Update Student Subjects -->
  <div class="modal fade" id="updateStudentSubjectModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <form action="update_student_subject.php" method="POST">
          <div class="modal-header">
            <h5 class="modal-title">Update Subjects of Student</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <label class="form-label">Choose Subject</label>
            <select class="form-select mb-2" name="subject" required>
              <option value="">Choose Subject</option>
              <?php foreach ($subjects as $sub): ?>
                <option value="<?= e($sub['id']) ?>"><?= e($sub['subject_name']) ?></option>
              <?php endforeach; ?>
            </select>
            <input type="number" class="form-control mb-2" name="student_id" placeholder="Student ID" required />
          </div>
          <div class="modal-footer">
            <button type="submit" name="action" value="add" class="btn btn-success">Add Subject</button>
            <button type="submit" name="action" value="delete" class="btn btn-danger">Remove Subject</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
