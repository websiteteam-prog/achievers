<?php
require_once __DIR__ . '/auth.php';
require_branch_admin();

$branch_id = $_SESSION['branch_id'];

// Subjects
$subjects = [];
$subStmt = $conn->prepare("SELECT id, subject_name FROM subjects");
$subStmt->execute();
$subRes = $subStmt->get_result();
while ($s = $subRes->fetch_assoc()) {
    $subjects[] = $s;
}

// Editable columns of `teachers`
$skip_columns = ['id', 'password', 'created_at', 'updated_at', 'is_active', 'allowed_ip', 'last_activity', 'status'];
$editable_columns = [];
$colStmt = $conn->prepare("SELECT column_name FROM information_schema.columns WHERE table_name = 'teachers' AND table_schema = DATABASE()");
$colStmt->execute();
$colRes = $colStmt->get_result();
while ($c = $colRes->fetch_assoc()) {
    if (!in_array($c['column_name'], $skip_columns, true)) {
        $editable_columns[] = $c['column_name'];
    }
}

// Teachers in this branch
$stmt = $conn->prepare(
    "SELECT teachers.id, teachers.name, teachers.email,
            GROUP_CONCAT(subjects.subject_name SEPARATOR ', ') AS subjects,
            teachers.contact_no, teachers.branch
     FROM teachers
     JOIN branches ON teachers.branch = branches.branch_name
     JOIN teacher_subjects ON teachers.id = teacher_subjects.teacher_id
     JOIN subjects ON teacher_subjects.subject_id = subjects.id
     WHERE branches.id = ?
     GROUP BY teachers.id"
);
$stmt->bind_param("i", $branch_id);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Teachers in the Branch</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
  <link rel="stylesheet" href="branch.css" />
</head>
<body>
  <?php include 'branch_dashboard_sidebar.php'; ?>

  <main class="main">
    <?php flash_render(); ?>
    <h2 class="mb-4">Teachers in the Branch</h2>

    <?php if ($result->num_rows > 0): ?>
      <div class="table-responsive">
        <table class="table table-bordered table-striped align-middle">
          <thead>
            <tr>
              <th>Teacher ID</th>
              <th>Name</th>
              <th>Email</th>
              <th>Subject</th>
              <th>Contact No.</th>
              <th>Branch</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
              <tr>
                <td><?= e($row['id']) ?></td>
                <td><?= e($row['name']) ?></td>
                <td><?= e($row['email']) ?></td>
                <td><?= e($row['subjects']) ?></td>
                <td><?= e($row['contact_no']) ?></td>
                <td><?= e($row['branch']) ?></td>
                <td>
                  <button type="button" class="btn btn-outline-success btn-sm" onclick="toggleOption(this)">Edit</button>
                  <div class="edit-options d-none mt-2">
                    <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#updateTeacherModal">Edit Detail</button>
                    <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#updateTeacherSubjectModal">Edit Subject</button>
                  </div>
                </td>
              </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    <?php else: ?>
      <div class="alert alert-info">No teachers found for this branch.</div>
    <?php endif; ?>
  </main>

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
            <div class="mb-3">
              <label class="form-label">Choose detail to update</label>
              <select class="form-select" name="detail" required>
                <option value="">Select field</option>
                <?php foreach ($editable_columns as $col): ?>
                  <option value="<?= e($col) ?>"><?= e(ucwords(str_replace('_', ' ', $col))) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="mb-3">
              <label class="form-label">Teacher ID</label>
              <input type="number" class="form-control" name="teacher_id" required />
            </div>
            <div class="mb-3">
              <label class="form-label">New Value</label>
              <input type="text" class="form-control" name="new_value" required />
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-success">Update Teacher</button>
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
            <h5 class="modal-title">Update Subjects of Teacher</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="mb-3">
              <label class="form-label">Choose Subject</label>
              <select class="form-select" name="subject" required>
                <option value="">Choose Subject</option>
                <?php foreach ($subjects as $sub): ?>
                  <option value="<?= e($sub['id']) ?>"><?= e($sub['subject_name']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="mb-3">
              <label class="form-label">Teacher ID</label>
              <input type="number" class="form-control" name="teacher_id" required />
            </div>
          </div>
          <div class="modal-footer">
            <button type="submit" name="action" value="add" class="btn btn-success">Add Subject</button>
            <button type="submit" name="action" value="delete" class="btn btn-danger">Remove Subject</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <script>
    function toggleOption(btn) { btn.nextElementSibling.classList.toggle('d-none'); }
  </script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
