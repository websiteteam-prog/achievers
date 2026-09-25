<?php
session_start();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/PHPMailer.php';
require 'PHPMailer/SMTP.php';
require 'PHPMailer/Exception.php';

// Enable error reporting
ini_set('display_errors', 1);
error_reporting(E_ALL);

$studentInfo = $_POST['student_info'] ?? '';

$parts = explode('||', $studentInfo);

$to = $parts[0] ?? '';
$student_name = $parts[1] ?? 'Parent';
$subject      = $_POST['subject'] ?? '';
$message      = $_POST['message'] ?? '';

$mail = new PHPMailer(true);

try {
    // Validate email
    if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
        throw new Exception("Invalid email address: $to");
    }

    // SMTP Setup (same as save_admin_enrollment.php)
    $mail->isSMTP();
    $mail->Host       = 'smtp.hostinger.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'info@achieverscastle.com';
    $mail->Password   = 'Amplic@@7408';
    $mail->SMTPSecure = 'ssl';
    $mail->Port       = 465;

    // Email Setup
    $mail->setFrom('info@achieverscastle.com', "Achiever's Castle");
    $mail->addAddress($to);
    $mail->isHTML(true);
    $mail->Subject = trim($subject) . " | Achiever's Castle";

    $greetingName = !empty($student_name) ? htmlspecialchars($student_name) : "there";
    $safeMessage  = nl2br(htmlspecialchars($message));

   $mail->Body = "

    <p style='font-family:Segoe UI,Arial,sans-serif;font-size:15px;color:#333;'>
    Dear <strong>$greetingName</strong>,
    </p>

    <p style='font-family:Segoe UI,Arial,sans-serif;font-size:15px;color:#333;line-height:1.7;'>
    We hope you are doing well.
    </p>

    <p style='font-family:Segoe UI,Arial,sans-serif;font-size:15px;color:#333;line-height:1.7;'>
    Please find the latest update regarding your child below.
    </p>

    <div style='
    background:#f8f9fc;
    border-left:4px solid #1e3c72;
    padding:18px;
    margin:25px 0;
    border-radius:6px;
    font-family:Segoe UI,Arial,sans-serif;
    font-size:15px;
    line-height:1.8;
    color:#333;
    '>

    <div style='
    font-size:13px;
    font-weight:700;
    color:#1e3c72;
    margin-bottom:10px;
    text-transform:uppercase;
    '>
    Teacher's Message
    </div>

    $safeMessage

    </div>";

        // File attachment
    if (!empty($_FILES['attachment']['name']) && $_FILES['attachment']['error'] === 0) {

        $mail->addAttachment(
            $_FILES['attachment']['tmp_name'],
            $_FILES['attachment']['name']
        );

        $mail->Body .= "

        <p style='font-family:Segoe UI,Arial,sans-serif;font-size:14px;color:#555;'>

        📎 <strong>Attachment:</strong>
        ".$_FILES['attachment']['name']."

        </p>";

        }

        $mail->Body .= "

        <hr style='border:none;border-top:1px solid #ddd;margin:30px 0;'>

        <p style='font-family:Segoe UI,Arial,sans-serif;
        font-size:15px;
        color:#333;
        line-height:1.7;'>

        If you have any questions, please feel free to contact us.

        </p>

        <p style='font-family:Segoe UI,Arial,sans-serif;
        font-size:15px;
        color:#333;
        line-height:1.7;'>

        Regards,<br>

        <strong>Team Achiever's Castle</strong><br>

        <a href='mailto:info@achieverscastle.com'>
        info@achieverscastle.com
        </a>

        </p>";

            $mail->send();
            $_SESSION['success'] = "Email sent successfully.";
            header("Location: teacher_dashboard.php?page=send_email_updates");
            exit;
        } catch (Exception $e) {
            echo "<h4 style='color:red;'>Error: " . $mail->ErrorInfo . "</h4>";
        }