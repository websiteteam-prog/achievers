<?php
require_once __DIR__ . '/auth.php';
require_branch_admin();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/../PHPMailer/PHPMailer.php';
require __DIR__ . '/../PHPMailer/SMTP.php';
require __DIR__ . '/../PHPMailer/Exception.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect_with_flash('danger', 'Invalid request.');
}

$branch_id = $_SESSION['branch_id'];

$name          = trim($_POST['name'] ?? '');
$phone         = trim($_POST['phone'] ?? '');
$email         = trim($_POST['studentsEmail'] ?? '');
$address       = trim($_POST['address'] ?? '');
$gender        = $_POST['gender'] ?? '';
$dob           = $_POST['dob'] ?? '';
$subjects      = $_POST['subject'] ?? [];
$grade         = $_POST['grade'] ?? '';
$mode          = $_POST['mode'] ?? '';
$parentname    = trim($_POST['parentsName'] ?? '');
$parentcontact = trim($_POST['parentsPhone'] ?? '');
$parentemail   = trim($_POST['parentsEmail'] ?? '');
$paymentMode   = $_POST['modeOfPayment'] ?? '';

if ($name === '' || $email === '' || empty($subjects)) {
    redirect_with_flash('danger', 'Please fill all required student details.');
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    redirect_with_flash('danger', 'Please enter a valid student email address.');
}

$conn->begin_transaction();
try {
    $sql = "INSERT INTO students
            (first_name, parent_email, grade, email, phone, gender, dob, branch_id, parent_name, parent_contact, address, payment_type, mode_of_education)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssssssssss", $name, $parentemail, $grade, $email, $phone, $gender, $dob, $branch_id, $parentname, $parentcontact, $address, $paymentMode, $mode);
    $stmt->execute();
    $student_id = $stmt->insert_id;
    $stmt->close();

    $map = $conn->prepare("INSERT INTO student_subjects (student_id, subject_id) VALUES (?, ?)");
    foreach ($subjects as $subject_id) {
        $sid = (int) $subject_id;
        $map->bind_param("ii", $student_id, $sid);
        $map->execute();
    }
    $map->close();

    $conn->commit();
} catch (mysqli_sql_exception $e) {
    $conn->rollback();
    redirect_with_flash('danger', 'Failed to add student. Please try again.');
}

// Student saved - try to send a welcome email (failure is non-fatal)
$mail = new PHPMailer(true);
try {
    $mail->isSMTP();
    $mail->Host       = 'mail.creativetheka.in';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'test@creativetheka.in';
    $mail->Password   = 'Ay.)My%qVStG';
    $mail->SMTPSecure = 'tls';
    $mail->Port       = 587;

    $mail->setFrom('test@creativetheka.in', 'Achievers Castel');
    $mail->addAddress($email, $name);

    $safeName    = e($name);
    $safeGrade   = e($grade);
    $safeMode    = e($mode);
    $safePayment = e($paymentMode);

    $mail->isHTML(true);
    $mail->Subject = 'Welcome to Achievers Castel!';
    $mail->Body    = "<h2>Welcome, {$safeName}!</h2>"
        . "<p>You have been successfully enrolled in <b>Achievers Castel</b>.</p>"
        . "<p><b>Grade:</b> {$safeGrade}<br>"
        . "<b>Mode of Education:</b> {$safeMode}<br>"
        . "<b>Payment Mode:</b> {$safePayment}</p>"
        . "<p>Our team will contact you soon with further details.</p>"
        . "<br><p style='color:gray;'>This is an automated email, please do not reply.</p>";

    $mail->send();
    redirect_with_flash('success', 'Student added and welcome email sent successfully.');
} catch (Exception $e) {
    redirect_with_flash('warning', 'Student added, but the welcome email could not be sent.');
}
