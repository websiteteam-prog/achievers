<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

include '../db_config.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// PHPMailer include
require '../PHPMailer/PHPMailer.php';
require '../PHPMailer/SMTP.php';
require '../PHPMailer/Exception.php';

if(!isset($_SESSION['admin_logged_in']) || $_SESSION['role'] !== 'branch_admin') {
    header("Location: login.php");
    exit();
}

$branch_id = $_SESSION['branch_id'];

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $name = $_POST['name'];
    $phone = $_POST['phone'];
    $email = $_POST['studentsEmail'];
    $address = $_POST['address'];
    $gender = $_POST['gender'];
    $dob = $_POST['dob'];
    $subject = $_POST['subject'];
    $grade = $_POST['grade'];
    $mode = $_POST['mode'];
    $parentname = $_POST['parentsName'];
    $parentcontact = $_POST['parentsPhone'];
    $parentemail = $_POST['parentsEmail'];
    $paymentMode = $_POST['modeOfPayment'];
   
   $conn->begin_transaction();
   
   try {
    
    $sql = "INSERT INTO students 
            (first_name, parent_email, grade, email, phone, gender, dob, branch_id, parent_name, parent_contact, address, payment_type, mode_of_education) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssssssssss", $name, $parentemail, $grade, $email, $phone, $gender, $dob, $branch_id, $parentname, $parentcontact, $address, $paymentMode, $mode);
    $result = $stmt->execute();
    
    if($result){
        $student_id = $stmt->insert_id;
        
        $insertSubjectMap = $conn->prepare("INSERT INTO student_subjects (student_id, subject_id) VALUES (?, ?)");
        
        foreach($subject as $subject_id){
            $insertSubjectMap->bind_param("ii", $student_id, $subject_id);
            $insertSubjectMap->execute();
        }
        
        $conn->commit();

        // ✅ Send email to student
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

            $mail->isHTML(true);
            $mail->Subject = "Welcome to Achievers Castel!";
            $mail->Body    = "
                <h2>Welcome, $name!</h2>
                <p>We are happy to inform you that you have been successfully enrolled in <b>Achievers Castel</b>.</p>
                <p><b>Grade:</b> $grade<br>
                   <b>Mode of Education:</b> $mode<br>
                   <b>Payment Mode:</b> $paymentMode</p>
                <p>Our team will contact you soon for further details.</p>
                <br>
                <p style='color:gray;'>This is an automated email, please do not reply.</p>
            ";

            $mail->send();
            echo "<script>alert('Student added and email sent successfully!'); window.location.href='students_list.php';</script>";
        } catch (Exception $e) {
            echo "<script>alert('Student added, but email could not be sent. Error: ".$mail->ErrorInfo."'); window.location.href='students_list.php';</script>";
        }

    }
    
    }catch(mysqli_sql_exception $e){
        $conn->rollback();
        echo "error:" . $e->getMessage();
    }
    $stmt->close();
}
?>
