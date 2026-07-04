<?php
session_start();
if (!isset($_SESSION['teacher_id'])) {
    echo "<script>window.location.href='teacher_login.php';</script>";
    exit;
}
include 'db_config.php';
$sql = "SELECT id, CONCAT(first_name, ' ', IFNULL(last_name,'')) AS name, email, phone, gender, dob, created_at
        FROM students ORDER BY id DESC";
$result = mysqli_query($conn, $sql);
?>
<div class="container-fluid mt-2">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="mb-0">Manage Students</h2>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addStudentModal">
      + Add Student
    </button>
  </div>
  <div class="table-responsive">
    <table class="table table-bordered table-hover">
      <thead class="table-dark">
        <tr>
          <th>Name</th>
          <th>Email</th>
          <th>Phone</th>
          <th>Gender</th>
          <th>DOB</th>
          <th>Created At</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
      <?php while ($row = mysqli_fetch_assoc($result)) { ?>
        <tr>
          <td><?= htmlspecialchars($row['name']) ?></td>
          <td><?= htmlspecialchars($row['email']) ?></td>
          <td><?= htmlspecialchars($row['phone']) ?></td>
          <td><?= htmlspecialchars($row['gender']) ?></td>
          <td><?= htmlspecialchars($row['dob']) ?></td>
          <td><?= htmlspecialchars($row['created_at']) ?></td>
          <td>
            <form method="POST" action="delete_student.php" onsubmit="return confirm('Delete this student?');">
              <input type="hidden" name="id" value="<?= (int)$row['id'] ?>">
              <button type="submit" class="btn btn-sm btn-danger">Delete</button>
            </form>
          </td>
        </tr>
      <?php } ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Add Student Modal: loads the real enrollment form in an isolated iframe -->
<div class="modal fade" id="addStudentModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Enroll New Student</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body p-0">
        <iframe id="enrollFrame" src="about:blank" style="width:100%; height:80vh; border:0;"></iframe>
      </div>
    </div>
  </div>
</div>

<script>
$('#addStudentModal').on('show.bs.modal', function () {
  document.getElementById('enrollFrame').src = 'teacher_enroll_student.php';
});
$('#addStudentModal').on('hidden.bs.modal', function () {
  document.getElementById('enrollFrame').src = 'about:blank';
});
</script>
