    <?php
    include "../../db_config.php";
    include "../../terms.php";
    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;

    require "../../PHPMailer/PHPMailer.php";
    require "../../PHPMailer/SMTP.php";
    require "../../PHPMailer/Exception.php";


    $first_name=$_POST['first_name'];
    $last_name=$_POST['last_name'];
    $dob=$_POST['dob'];
    $grade=$_POST['grade'];
    $subjects = $_POST['subjects'] ?? [];

    if(empty($subjects)){
        $subject = "All Programs";
    } else {
        $subject = implode(", ", $subjects);
    }

    $program = $_POST['program']; 
    $program_count = $_POST['program_count'] ?? '';
    $student['program'] = $program;
    $programName = $student['program'];
    $student['payment_by'] = $payment_by;
    $guardian_name=$_POST['guardian_name'];
    $guardian_email=$_POST['guardian_email'];
    $guardian_phone=$_POST['guardian_phone'];

    $mother_name=$_POST['mother_name'];
    $mother_phone=$_POST['mother_phone'];

    $father_name=$_POST['father_name'];
    $father_phone=$_POST['father_phone'];

    $emergency_name=$_POST['emergency_name'];
    $emergency_phone=$_POST['emergency_phone'];

    $authorized_name=$_POST['authorized_name'];
    $authorized_relation=$_POST['authorized_relation'];

    $message=$_POST['message'];

    $payment_by=$_POST['payment_by'];
    $payment_type=$_POST['payment_type'];
    $mode=$_POST['mode_of_education'];

    $enroll_date=$_POST['enroll_date'];

    $email_to = "";
    $name_to = "";

    // Decide payer
    switch($payment_by){
    case "Guardian":
        $email_to = $guardian_email;
        $name_to  = $guardian_name;
        break;

    case "Mother":
        if(empty($mother_name) || empty($_POST['mother_email']) || empty($mother_phone)){
            die("Mother name, email and phone are required");
        }
        $email_to = $_POST['mother_email'];
        $name_to  = $mother_name;
        break;

    case "Father":
        if(empty($father_name) || empty($_POST['father_email']) || empty($father_phone)){
            die("Father name, email and phone are required");
        }
        $email_to = $_POST['father_email'];
        $name_to  = $father_name;
        break;
        }

        if(!isset($_POST['terms_agreed'])){
        die("Terms & Conditions must be accepted.");
        }

        if(empty($_POST['payment_type'])){
        die("Payment type is required");
        }
        $email = $guardian_email;

        /* CHECK EMAIL EXISTS */
        $check = mysqli_query($conn,"SELECT id FROM students WHERE email='$email'");

        if(mysqli_num_rows($check) > 0){

            echo "<script>
            alert('⚠️ This email is already registered. Please use another email.');
            window.history.back();
            </script>";
            exit;
        }else{

    // ✅ NEW STUDENT → CREATE LOGIN
    $plain_password = rand(100000,999999); // send later after payment
    $password = password_hash($plain_password, PASSWORD_DEFAULT);

    $insert = mysqli_query($conn,"
    INSERT INTO students (first_name,last_name,email,password,grade,payment_type,dob)
    VALUES ('$first_name','$last_name','$email','$password', '$grade', '$payment_type', '$dob')
    ");

    if(!$insert){
        die("Student Insert Error: " . mysqli_error($conn));
    }

    $student_login_id = mysqli_insert_id($conn);

}

    /* -----------------------------
    SAVE STUDENT
    -----------------------------*/

    mysqli_query($conn,"
    INSERT INTO enrollment_inquiries
    (
    student_id,
    first_name,last_name,dob,grade,specific_subject,program,
    guardian_name,guardian_email,guardian_phone,
    mother_name,mother_email,mother_phone,
    father_name,father_email,father_phone,
    emergency_name,emergency_phone,
    authorized_name,authorized_relation,
    message,terms_agreed,
    payment_by,payment_type,mode_of_education,
    enrolled_by,enroll_date,program_count
    )
    VALUES
    (
    '$student_login_id',
    '$first_name','$last_name','$dob','$grade','$subject','$program',
    '$guardian_name','$guardian_email','$guardian_phone',
    '$mother_name','{$_POST['mother_email']}','$mother_phone',
    '$father_name','{$_POST['father_email']}','$father_phone',
    '$emergency_name','$emergency_phone',
    '$authorized_name','$authorized_relation',
    '$message','{$_POST['terms_agreed']}',
    '$payment_by','$payment_type','$mode',
    'admin','$enroll_date','$program_count'
    )
    ");

    $enrollment_id = mysqli_insert_id($conn);

    /* -----------------------------
    CALCULATE FEES
    -----------------------------*/

    $program_count = $_POST['program_count'];



    /* PRE SCHOOL → GRADE 2 */

    if($grade == "Pre-School" || $grade == "Grade 1" || $grade == "Grade 2"){

        $price = 150;

    }


    /* GRADE 3 → 8 */

    elseif(in_array($grade, ["Grade 3","Grade 4","Grade 5","Grade 6","Grade 7","Grade 8"])){

    if($program_count == 1){
        $price = 140;
        }
        elseif($program_count == 2){
            $price = 270;
        }
        elseif($program_count == "all"){
            $price = 400;
        }

    }


    /* GRADE 9 → 12 */

    elseif(in_array($grade, ["Grade 9","Grade 10","Grade 11","Grade 12"])){

        if($program_count == 1){
            $price = 160;
        }
        elseif($program_count == 2){
            $price = 310;
        }
        else{
            $price = 460;
        }

    }
    if(!isset($price)){
        $price = 150;
    }
    $total = $price; 
    $gst = $total * (5/105); 
    $price = $total - $gst; 

    /* -----------------------------
    CREATE INVOICE
    -----------------------------*/


    mysqli_query($conn,"
    INSERT INTO invoices
    (student_id,invoice_date,due_date,price,gst,total,status)
    VALUES
    ('$student_login_id',CURDATE(),DATE_ADD(CURDATE(),INTERVAL 15 DAY),'$price','$gst','$total','Pending')
    ");

    $invoice_id = mysqli_insert_id($conn);
    $year = date("y");
    $invoice_number = "AC-$year-" . str_pad($invoice_id, 4, "0", STR_PAD_LEFT);

    mysqli_query($conn,"
    UPDATE invoices 
    SET invoice_number='$invoice_number'
    WHERE id='$invoice_id'
    ");



    /* -----------------------------
    GENERATE INVOICE PDF
    -----------------------------*/

    require "../../dompdf/autoload.inc.php";

    use Dompdf\Dompdf;

    if(empty($name_to)){
        $name_to = $guardian_name; 
    }

    $student = [
    "id"=>$student_login_id,
    "first_name"=>$first_name,  
    "last_name"=>$last_name,
    "email"=>$email_to,        
    "student_email"=>$guardian_email, 
    "course_title"=>$subject,
    "created_at"=>date("Y-m-d"),
    "invoice_number"=>$invoice_number,
    "program"=>$program,
    "payment_by"=>$payment_by,
    "payer_name"=>$name_to  
    ];

    $logoBase64="data:image/png;base64,".base64_encode(file_get_contents("../../images/logo.png"));

    ob_start();

    $student['invoice_number'] = $invoice_number;
    include "../../invoice_template.php";

    $html=ob_get_clean();

    $dompdf=new Dompdf();

    $dompdf->loadHtml($html);

    $dompdf->setPaper("A4");

    $dompdf->render();

    $pdf=$dompdf->output();

    $file="../../temp_invoice.pdf";

    file_put_contents($file,$pdf);



    /* -----------------------------
    SEND EMAIL
    -----------------------------*/

    $mail=new PHPMailer(true);

    try{

    $mail->isSMTP();

    $mail->Host='smtp.hostinger.com';

    $mail->SMTPAuth=true;

    $mail->Username='info@achieverscastle.com';

    $mail->Password='Amplic@@7408';

    $mail->SMTPSecure='ssl';

    $mail->Port=465;

    $mail->setFrom('info@achieverscastle.com','Achiever\'s Castle');

    $mail->addAddress($email_to,$name_to);

    $mail->Subject="Invoice for $first_name $last_name";

    $mail->isHTML(true);

    $mail->Body = "
    <h3>Hello $name_to,</h3>

    <p>
    We are pleased to inform you that your child <b>$first_name $last_name</b> has been successfully enrolled at <b>Achiever's Castle</b>.
    </p>

    <p>
    Please find the invoice attached for your reference.
    </p>

    <hr>

    <h3>Terms & Conditions</h3>
    $terms_content

    <br><br>

    <p>
    If you have any questions, feel free to contact us.
    </p>

    <p>
    Thank you,<br>
    <b>Team Achiever's Castle</b>
    </p>
    ";

    $mail->addAttachment($file,"invoice.pdf");

    $mail->send();

    }catch(Exception $e){

    }



    echo "<script>

    alert('Student enrolled and invoice sent');

    window.location='../../teacher_dashboard.php?page=invoice_system/dashboard/invoice_dashboard.php';

    </script>";