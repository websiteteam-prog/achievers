<?php
include '../db_config.php';

if (!isset($_GET['id'])) {
  die("Invalid Request");
}

$id = intval($_GET['id']);

$result = mysqli_query($conn, "SELECT * FROM students WHERE id = $id");
$data = mysqli_fetch_assoc($result);

if (!$data) {
  die("Student not found");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

  $name   = mysqli_real_escape_string($conn, $_POST['name']);
  $email  = mysqli_real_escape_string($conn, $_POST['email']);
  $phone  = mysqli_real_escape_string($conn, $_POST['phone']);
  $gender = mysqli_real_escape_string($conn, $_POST['gender']);
  $dob    = mysqli_real_escape_string($conn, $_POST['dob']);

  $update = "UPDATE students SET 
                first_name='$name',
                email='$email',
                phone='$phone',
                gender='$gender',
                dob='$dob'
               WHERE id=$id";

  if (mysqli_query($conn, $update)) {
    echo "<script>
alert('Student updated successfully');
window.location='dashboard.php?page=manage_students.php';
</script>";
    exit;
  } else {
    echo "Error: " . mysqli_error($conn);
  }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <title>Edit Student</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

  <style>
    body {
      background: #f4f7fb;
      font-family: 'Poppins', 'Segoe UI', sans-serif;
    }

    /* Card */

    .form-card {
      max-width: 600px;
      margin: auto;
      margin-top: 50px;
      padding: 30px;
      border-radius: 18px;
      background: white;
      box-shadow: 0 8px 30px rgba(0, 0, 0, .05);
    }

    /* Title */

    .form-title {
      font-size: 22px;
      font-weight: 600;
      margin-bottom: 20px;
      background: linear-gradient(45deg, #1e88e5, #42a5f5);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
    }

    /* Inputs */

    .form-control {
      border-radius: 10px;
      padding: 10px 12px;
      border: 1px solid #e5e7eb;
    }

    .form-control:focus {
      border-color: #1e88e5;
      box-shadow: 0 0 0 3px rgba(30, 136, 229, .15);
    }

    /* Buttons */

    .btn-update {
      background: linear-gradient(45deg, #1e88e5, #42a5f5);
      border: none;
      color: white;
      border-radius: 8px;
      padding: 8px 18px;
    }

    .btn-update:hover {
      opacity: .9;
      color: white;
    }

    .btn-back {
      border-radius: 8px;
      padding: 8px 18px;
    }

    /* Responsive */

    @media(max-width:768px) {

      .form-card {
        margin-top: 30px;
        padding: 22px;
      }

      .form-title {
        font-size: 20px;
      }

    }

    @media(max-width:480px) {

      .form-card {
        padding: 18px;
      }

    }

    @media(max-width:300px) {

      .form-card {
        padding: 15px;
      }

    }
  </style>

</head>

<body>

  <div class="container-fluid">

    <div class="form-card">

      <h4 class="form-title">
        Edit Student
      </h4>

      <form method="POST" action="edit_student.php?id=<?=$id?>" enctype="multipart/form-data">

        <div class="mb-3">

          <label class="form-label fw-semibold">
            Name
          </label>

          <input
            type="text"
            name="name"
            class="form-control"
            value="<?= $data['first_name'] ?>"
            required>

        </div>


        <div class="mb-3">

          <label class="form-label fw-semibold">
            Email
          </label>

          <input
            type="email"
            name="email"
            class="form-control"
            value="<?= $data['email'] ?>"
            required>

        </div>


        <div class="mb-3">

          <label class="form-label fw-semibold">
            Phone
          </label>

          <input
            type="text"
            name="phone"
            class="form-control"
            value="<?= $data['phone'] ?>"
            required>

        </div>


        <div class="mb-3">

          <label class="form-label fw-semibold">
            Gender
          </label>

          <select name="gender" class="form-control" required>

            <option value="Male" <?= ($data['gender'] == 'Male') ? 'selected' : '' ?>>
              Male
            </option>

            <option value="Female" <?= ($data['gender'] == 'Female') ? 'selected' : '' ?>>
              Female
            </option>

          </select>

        </div>


        <div class="mb-3">

          <label class="form-label fw-semibold">
            Date of Birth
          </label>

          <input
            type="date"
            name="dob"
            class="form-control"
            value="<?= $data['dob'] ?>"
            required>

        </div>


        <div class="d-flex gap-2 flex-wrap">

          <button type="submit" class="btn btn-update">
            Update Student
          </button>

          <a href="dashboard.php?page=manage_students.php" class="btn btn-secondary btn-back">
            Back
          </a>

        </div>

      </form>

    </div>

  </div>

</body>

</html>