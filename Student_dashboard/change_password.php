<?php
session_start();

include "../db_config.php";

if (!isset($_SESSION['student_id'])) {
    header("Location: ../student_login.php");
    exit();
}

$student_id = (int)$_SESSION['student_id'];

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    header("Location: settings.php#security");
    exit();
}

$current_password = trim($_POST['current_password'] ?? '');
$new_password     = trim($_POST['new_password'] ?? '');
$confirm_password = trim($_POST['confirm_password'] ?? '');

/* -----------------------------
   Basic Validation
------------------------------ */

if (
    empty($current_password) ||
    empty($new_password) ||
    empty($confirm_password)
) {

    echo "<script>
            alert('Please fill all fields.');
            window.location='settings.php#security';
          </script>";
    exit();
}

if ($new_password != $confirm_password) {

    echo "<script>
            alert('New password and Confirm password do not match.');
            window.location='settings.php#security';
          </script>";
    exit();
}

/* -----------------------------
   Get Current Password
------------------------------ */

$sql = "SELECT password
        FROM students
        WHERE id=$student_id
        LIMIT 1";

$result = mysqli_query($conn,$sql);

if(!$result || mysqli_num_rows($result)==0){

    die("Student not found.");

}

$row = mysqli_fetch_assoc($result);

/* -----------------------------
   Check Old Password
------------------------------ */

$db_password = $row['password'];

/*
If passwords are stored using password_hash()
*/
if(password_verify($current_password,$db_password)){

$new_hash = password_hash($new_password, PASSWORD_DEFAULT);

mysqli_query(
    $conn,
    "UPDATE students
     SET password='$new_hash'
     WHERE id=$student_id"
);

// Destroy session
session_unset();
session_destroy();

// Redirect to login
echo "<script>
        alert('Password updated successfully. Please login again.');
        window.location='./student_login.php';
      </script>";
exit();

}

/*
If passwords are stored as plain text
Comment the above block
and use this instead.

if($current_password==$db_password){

    mysqli_query(
        $conn,
        \"UPDATE students
         SET password='$new_password'
         WHERE id=$student_id\"
    );

    echo \"<script>
            alert('Password updated successfully.');
            window.location='settings.php#security';
          </script>\";

    exit();

}
*/

echo "<script>
        alert('Current password is incorrect.');
        window.location='settings.php#security';
      </script>";

exit();

?>