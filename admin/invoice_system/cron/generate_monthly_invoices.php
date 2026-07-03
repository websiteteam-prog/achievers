    <?php

    include "../../../db_config.php";

    set_time_limit(0);
    ini_set('max_execution_time', 0);
    ini_set('memory_limit', '1024M');

    /* RUN ONLY ON 1ST */
    // if (date('d') != '01') {
    //     exit;
    // }

    /* MAIL */
    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;

    require "../../../PHPMailer/PHPMailer.php";
    require "../../../PHPMailer/SMTP.php";
    require "../../../PHPMailer/Exception.php";

    /* PDF */
    require "../../../dompdf/autoload.inc.php";
    use Dompdf\Dompdf;

    /* FETCH STUDENTS */
    $result = mysqli_query($conn, "
    SELECT student_id, first_name, last_name, guardian_email, guardian_name, 
    mother_email, mother_name, father_email, father_name, payment_by, grade, 
    specific_subject, program, program_count,
    discount_type, discount_amount, extra_type, extra_amount
    FROM enrollment_inquiries 
    WHERE status='Active' AND billing_paused = 0
    ");

    while ($row = mysqli_fetch_assoc($result)) {

    $student_id = $row['student_id'];

    /* AVOID DUPLICATE */
    $check = mysqli_query($conn, "
    SELECT id FROM invoices 
    WHERE student_id='$student_id' 
    AND DATE_FORMAT(invoice_date,'%Y-%m') = DATE_FORMAT(CURDATE(),'%Y-%m')
    ");

    if (mysqli_num_rows($check) > 0) {
        continue;
    }

    /* EMAIL LOGIC */
    $email_to = $row['guardian_email'];
    $name_to  = $row['guardian_name'];
    $payer_name = $row['guardian_name'];

    if($row['payment_by'] == "Mother"){
        $email_to = $row['mother_email'] ?: $row['guardian_email'];
        $name_to  = $row['mother_name'];
        $payer_name = $row['mother_name'];
    }
    elseif($row['payment_by'] == "Father"){
        $email_to = $row['father_email'] ?: $row['guardian_email'];
        $name_to  = $row['father_name'];
        $payer_name = $row['father_name'];
    }

    /* PRICE LOGIC (GST INCLUDED SYSTEM) */

    $program_count = $row['program_count'];
    $grade_raw = trim($row['grade']);  

    if(strtolower($grade_raw) == "pre-school"){
        $grade = "Pre-School";
    }else{
        $grade = (int) filter_var($grade_raw, FILTER_SANITIZE_NUMBER_INT);
    }
    $discount_type   = $row['discount_type'] ?? '';
    $discount_amount = floatval($row['discount_amount'] ?? 0);
    $extra_type   = $row['extra_type'] ?? '';
    $extra_amount = floatval($row['extra_amount'] ?? 0);

    if($grade == "Pre-School" || $grade == 1 || $grade == 2){

        $total = 150;

    }
    elseif(in_array($grade, [3,4,5,6,7,8])){

        if($program_count == 1){
            $total = 140;
        }
        elseif($program_count == 2){
            $total = 270;
        }
        else{
            $total = 400;
        }

    }
    elseif(in_array($grade, [9,10,11,12])){

        if($program_count == 1){
            $total = 160;
        }
        elseif($program_count == 2){
            $total = 310;
        }
        else{
            $total = 460;
        }

    }
    else{
        $total = 150;
    }

    // only sibling discount monthly apply
      // only sibling discount monthly apply
    if($discount_type === "sibling"){
        $total -= $discount_amount;
    }

    // only PERMANENT extra monthly apply
    if($extra_type === "permanent"){
        $total += $extra_amount;
    }

    // safety
    if($total < 0){
        $total = 0;
    }
    /* GST */
    $gst = $total * (5/105);
    $subtotal = $total - $gst;
  
        $final_extra_type = '';
    $final_extra_amount = 0;

    $final_discount_type = '';
    $final_discount_amount = 0;

      // only sibling store hoga monthly
    if($discount_type === "sibling"){
        $final_discount_type = $discount_type;
        $final_discount_amount = $discount_amount;
    }
    // only permanent extra stored monthly
    if($extra_type === "permanent"){
        $final_extra_type = $extra_type;
        $final_extra_amount = $extra_amount;
    }

  

    

    mysqli_query($conn, "
    INSERT INTO invoices
    (student_id,invoice_date,due_date,price,gst,total,status,discount_type,discount_amount,extra_type,extra_amount)
    VALUES
    ('$student_id',CURDATE(),DATE_ADD(CURDATE(),INTERVAL 15 DAY),
    '$subtotal','$gst','$total','Pending','$final_discount_type','$final_discount_amount','$final_extra_type','$final_extra_amount')
    ");

        $invoice_id = mysqli_insert_id($conn);

        $year = date("y");
        $invoice_number = "AC-$year-" . str_pad($invoice_id, 4, "0", STR_PAD_LEFT);

        mysqli_query($conn, "
        UPDATE invoices 
        SET invoice_number='$invoice_number' 
        WHERE id='$invoice_id'
        ");

    /* PDF DATA (IMPORTANT FIX) */
    $student = [
        "first_name"=>$row['first_name'],
        "last_name"=>$row['last_name'],
        "email"=>$email_to,
        "course_title"=>$row['specific_subject'],
        "invoice_date"=>date("Y-m-d"),
        "invoice_number"=>$invoice_number,
        "program"=>$row['program'],
        "payer_name"=>$payer_name
    ];
    $invoice = [
        "discount_type" => $final_discount_type,
        "discount_amount" => $final_discount_amount,
        "price_after_discount" => $total,
        "gst" => $gst,
        "total" => $total,
        "extra_type" => $final_extra_type,
        "extra_amount" => $final_extra_amount,
    ];
    $price = $subtotal;

    $logoBase64 = "data:image/png;base64," . base64_encode(file_get_contents("../../../images/logo.png"));

    ob_start();
    include "../../../invoice_template.php";
    $html = ob_get_clean();

    $dompdf = new Dompdf();
    $dompdf->loadHtml($html);
    $dompdf->setPaper("A4");
    $dompdf->render();

    $file = "../../temp_invoice_".$invoice_id.".pdf";
    file_put_contents($file, $dompdf->output());

    /* MAIL */
    $mail = new PHPMailer(true);

    try{
        $mail->isSMTP();
        $mail->Host='smtp.hostinger.com';
        $mail->SMTPAuth=true;
        $mail->Username='info@achieverscastle.com';
        $mail->Password='Amplic@@7408';
        $mail->SMTPSecure='ssl';
        $mail->Port=465;

        $mail->setFrom('info@achieverscastle.com','Achievers Castle');
        $mail->addAddress($email_to,$name_to);

        $mail->Subject="Monthly Invoice - ".$row['first_name']." ".$row['last_name'];

        $mail->isHTML(true);
        $mail->Body="
        Dear Parents/Guardians,<br><br>

        Please note that the monthly invoice has been generated. Kindly arrange to pay the fees at your earliest convenience.<br><br>

        Thank you for your cooperation.<br><br>

        Best regards,<br>
        <b>Team Achiever's Castle</b>
        ";

        $mail->addAttachment($file,"invoice.pdf");
        $mail->send();

    }catch(Exception $e){
        echo "Mail Error: ".$mail->ErrorInfo."<br>";
    }

}