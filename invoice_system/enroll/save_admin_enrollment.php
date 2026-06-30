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
    $discount_description = $_POST['discount_description'] ?? '';
    if(empty($subjects)){
        $subject = "All Programs";
    } else {
        $subject = implode(", ", $subjects);
    }

    $program = $_POST['program'];
    $program_count = $_POST['program_count'] ?? '';
    $student['program'] = $program;
    $programName = $student['program'];
    $payment_by = $_POST['payment_by'];
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


    $payment_type=$_POST['payment_type'];
    $mode=$_POST['mode_of_education'];

    $enroll_date=$_POST['enroll_date'];

    $email_to = "";
    $name_to = "";
    $discount_type   = $_POST['discount_type'] ?? '';
    $discount_amount = floatval($_POST['discount_amount'] ?? 0);
    $discount_description = $_POST['discount_description'] ?? '';

    /* EXTRA AMOUNT (admin can add or reduce) */
    $extra_type        = $_POST['extra_type'] ?? '';        // '', 'add', 'subtract'
    $extra_amount      = floatval($_POST['extra_amount'] ?? 0);
    $extra_description = $_POST['extra_description'] ?? '';

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

        // if(empty($_POST['payment_type'])){
        // die("Payment type is required");
        // }
        $email = $guardian_email;

        /* CHECK EMAIL EXISTS */
        $check = mysqli_query($conn,"SELECT id FROM students WHERE email='$email'");

        // if(mysqli_num_rows($check) > 0){

        //    echo "<script>
        //     alert('⚠️ This email is already registered. Please use another email.');
        //     window.location.href=document.referrer;
        //     </script>";
        //     exit;
        // }
        // else{

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

// }

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
    enrolled_by,enroll_date,program_count,
    discount_type,discount_amount,discount_description,
    extra_type,extra_amount,extra_description
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
    'admin','$enroll_date','$program_count',
    '$discount_type','$discount_amount','$discount_description',
    '$extra_type','$extra_amount','$extra_description'
    )
    ");

    $enrollment_id = mysqli_insert_id($conn);


    /* -----------------------------
    CALCULATE FEES
    -----------------------------*/

    $program_count = $_POST['program_count'];
    $discount_type   = $_POST['discount_type'] ?? '';
    $discount_amount = floatval($_POST['discount_amount'] ?? 0);


    /* PRE SCHOOL → GRADE 2 */

  if($grade == "Pre-School" || $grade == "Grade 1" || $grade == "Grade 2"){
    $price = 150;
    }
    elseif(in_array($grade, ["Grade 3","Grade 4","Grade 5","Grade 6","Grade 7","Grade 8"])){

        if($program_count == 1){
            $price = 140;
        }
        elseif($program_count == 2){
            $price = 270;
        }
        else{
            $price = 400;
        }

    }
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

    /* -----------------------------
    15 DAYS HALF RULE (GLOBAL)
    -----------------------------*/

    $original_price = $price;
    $day = (int)date("d", strtotime($enroll_date));

    if($day > 15){
        $price = $price / 2;
        }


    /* APPLY DISCOUNT */

    if($discount_type === "one_time"){
        $price -= $discount_amount;
    }

    if($discount_type === "sibling"){
        $price -= $discount_amount;
    }

    /* APPLY EXTRA AMOUNT (add or reduce) */

    if($extra_type === "add"){
        $price += $extra_amount;
    }
    elseif($extra_type === "subtract"){
        $price -= $extra_amount;
    }

    /* SAFETY */

    if($price < 0){
        $price = 0;
    }

    /* GST */

    $gst = $price * (5/105);
    $total = $price;


    /* -----------------------------
    CREATE INVOICE
    -----------------------------*/


   mysqli_query($conn,"
    INSERT INTO invoices
    (student_id,invoice_date,due_date,price,gst,total,status,discount_type,discount_amount,discount_description,extra_type,extra_amount,extra_description)
    VALUES
    ('$student_login_id',CURDATE(),DATE_ADD(CURDATE(),INTERVAL 15 DAY),
    '$price','$gst','$total','Pending','$discount_type','$discount_amount','$discount_description','$extra_type','$extra_amount','$extra_description')
    ");

    $invoice_id = mysqli_insert_id($conn);
    $year = date("y");
    $invoice_number = "AC-$year-" . str_pad($invoice_id, 4, "0", STR_PAD_LEFT);

    mysqli_query($conn,"
    UPDATE invoices
    SET invoice_number='$invoice_number'
    WHERE id='$invoice_id'
    ");

    mysqli_query($conn,"
    INSERT INTO student_plan_history
    (student_id, program, program_count, subjects, price, start_date, invoice_id)
    VALUES
    ('$student_login_id', '$program', '$program_count', '$subject', '$total', CURDATE(), '$invoice_id')
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
    "payer_name"=>$name_to,
    "invoice_date" => date("Y-m-d")
    ];

    $logoBase64="data:image/png;base64,".base64_encode(file_get_contents("../../images/logo.png"));

    $invoice = [
    "discount_type" => $discount_type,
    "discount_amount" => $discount_amount,
    "price_after_discount" => $price,
    "extra_type" => $extra_type,
    "extra_amount" => $extra_amount,
    "extra_description" => $extra_description,
    "gst" => $gst,
    "total" => $total
];

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

    $mail->Password='';

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
    echo "<script>alert('Mail Error: ".$mail->ErrorInfo."');</script>";
    }



  echo "
    <script>
    alert('Student enrolled and invoice sent');

    history.pushState(null, '', '?page=invoice_system/dashboard/invoice_dashboard.php');

    $.get('invoice_system/dashboard/invoice_dashboard.php', function(data){
        $('#page-content').html(data);
    });
    </script>
    ";
