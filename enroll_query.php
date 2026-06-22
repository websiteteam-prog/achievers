  <?php
  use PHPMailer\PHPMailer\PHPMailer;
  use PHPMailer\PHPMailer\Exception;

  require 'PHPMailer/PHPMailer.php';
  require 'PHPMailer/SMTP.php';
  require 'PHPMailer/Exception.php';

  include 'db_config.php';

  if (isset($_POST['submit_query'])) {
      // Personal Details
      $first_name    = mysqli_real_escape_string($conn, trim($_POST['first_name']));
      $last_name     = mysqli_real_escape_string($conn, trim($_POST['last_name']));
      $dob           = mysqli_real_escape_string($conn, trim($_POST['dob']));
      $grade         = mysqli_real_escape_string($conn, trim($_POST['grade']));
      $subject       = mysqli_real_escape_string($conn, trim($_POST['subject'])); // specific_subject
      $program       = mysqli_real_escape_string($conn, trim($_POST['program'] ?? ''));

      // Guardian / Parent
      $guardian_name   = mysqli_real_escape_string($conn, trim($_POST['guardian_name']));
      $guardian_email  = mysqli_real_escape_string($conn, trim($_POST['guardian_email']));
      $guardian_phone  = mysqli_real_escape_string($conn, trim($_POST['guardian_phone']));
      $mother_name     = mysqli_real_escape_string($conn, trim($_POST['mother_name'] ?? ''));
      $mother_phone    = mysqli_real_escape_string($conn, trim($_POST['mother_phone'] ?? ''));
      $father_name     = mysqli_real_escape_string($conn, trim($_POST['father_name'] ?? ''));
      $father_phone    = mysqli_real_escape_string($conn, trim($_POST['father_phone'] ?? ''));

      // Emergency & Pickup
      $emergency_name     = mysqli_real_escape_string($conn, trim($_POST['emergency_name'] ?? ''));
      $emergency_phone    = mysqli_real_escape_string($conn, trim($_POST['emergency_phone'] ?? ''));
      $authorized_name    = mysqli_real_escape_string($conn, trim($_POST['authorized_name'] ?? ''));
      $authorized_relation = mysqli_real_escape_string($conn, trim($_POST['authorized_relation'] ?? ''));

      $message       = mysqli_real_escape_string($conn, trim($_POST['message'] ?? ''));
      $terms_agreed  = isset($_POST['terms_agreed']) ? 1 : 0;

     if ($terms_agreed !== 1) {
    echo "<script>alert('You must agree to the Terms & Conditions to submit.');</script>";
    exit;
}

        /* =========================
        STEP 1: CHECK EMAIL
        ========================= */
        $email = $guardian_email;

        $check = mysqli_query($conn,"SELECT id FROM students WHERE email='$email'");

        if(mysqli_num_rows($check) > 0){
            echo "<script>
            alert('⚠️ This email is already registered. Please use another email.');
            window.history.back();
            </script>";
            exit;
        }

        /* =========================
        STEP 2: CREATE STUDENT
        ========================= */
        $plain_password = rand(100000,999999);
        $password = password_hash($plain_password, PASSWORD_DEFAULT);

        $insert = mysqli_query($conn,"
        INSERT INTO students (first_name,last_name,email,password,grade,dob)
        VALUES ('$first_name','$last_name','$email','$password','$grade','$dob')
        ");

        if(!$insert){
            die("Student Insert Error: " . mysqli_error($conn));
        }

        $student_id = mysqli_insert_id($conn);

        /* =========================
        STEP 3: SAVE ENROLLMENT
        ========================= */
        $sql = "INSERT INTO enrollment_inquiries 
        (
        student_id,
        first_name, last_name, dob, grade, specific_subject, program, 
        guardian_name,guardian_email,guardian_phone, 
        mother_name, mother_phone, father_name, father_phone,
        emergency_name, emergency_phone, authorized_name, authorized_relation, 
        message, terms_agreed
        )
        VALUES 
        (
        '$student_id',
        '$first_name', '$last_name', '$dob', '$grade', '$subject', '$program',
        '$guardian_name', '$guardian_email', '$guardian_phone',
        '$mother_name', '$mother_phone', '$father_name', '$father_phone',
        '$emergency_name', '$emergency_phone', '$authorized_name', '$authorized_relation',
        '$message', '$terms_agreed'
        )";

        if (!mysqli_query($conn, $sql)) {
            die("Database Error: " . mysqli_error($conn));
        }
        
            /* =========================
            STEP 4: CREATE INVOICE
            ========================= */

            // Basic price (simple version – later dynamic kar denge)
            $total = 150;

            // GST calculation (5%)
            $gst = $total * (5/105);
            $price = $total - $gst;

            // Insert invoice
           $invoice_insert = mysqli_query($conn,"
            INSERT INTO invoices
            (student_id, invoice_date, due_date, price, gst, total, status)
            VALUES
            ('$student_id', CURDATE(), DATE_ADD(CURDATE(), INTERVAL 15 DAY), '$price', '$gst', '$total', 'Pending')
            ");

            if(!$invoice_insert){
                die("Invoice Error: " . mysqli_error($conn));
            }
            // Get invoice ID
            $invoice_id = mysqli_insert_id($conn);

            // Generate invoice number
            $year = date("y");
            $invoice_number = "AC-$year-" . str_pad($invoice_id, 4, "0", STR_PAD_LEFT);

            // Update invoice number
           if(!mysqli_query($conn,"
            UPDATE invoices 
            SET invoice_number='$invoice_number'
            WHERE id='$invoice_id'
            ")){
                die("Invoice Update Error: " . mysqli_error($conn));
            }
            include_once 'terms.php';
              // Email
              $admin_email = "info@achieverscastle.com";
              $mail_subject = "New Student Enrolled for $subject - $first_name $last_name";

              $email_body = "
              <h2>New Enrollment Form Submission</h2>
              <h3>Personal Details</h3>
              <b>First Name:</b> $first_name<br>
              <b>Last Name:</b> $last_name<br>
              <b>Date of Birth:</b> $dob<br>
              <b>Grade:</b> $grade<br>
              <b>Program:</b> $program<br><br>
              <b>Subject of Interest:</b> $subject<br><br>
              <b>Invoice Number:</b> $invoice_number<br>
              <b>Total Amount:</b> $$total<br><br>
              <h3>Guardian Information</h3>
              <b>Guardian's Name:</b> $guardian_name<br>
              <b>Email:</b> $guardian_email<br>
              <b>Contact Number:</b> $guardian_phone<br><br>

              <h3>Parent Information</h3>
              <b>Mother's Name:</b> $mother_name<br>
              <b>Mother's Cell:</b> $mother_phone<br>
              <b>Father's Name:</b> $father_name<br>
              <b>Father's Cell:</b> $father_phone<br><br>

              <h3>Emergency Contact</h3>
              <b>Name:</b> $emergency_name<br>
              <b>Contact Number:</b> $emergency_phone<br><br>

              <h3>Authorized Pickup</h3>
              <b>Name:</b> $authorized_name<br>
              <b>Relation:</b> $authorized_relation<br><br>

              <h3>Comments / Message</h3>
              $message<br><br>

              <hr>
              <h3>Terms & Conditions (Agreed)</h3>
                $terms_content

                <br><br>
                <b>Terms Accepted:</b> Yes<br>
                Submitted from Achiever's Castle Website
                ";

             $mail = new PHPMailer(true);

            try {
                $mail->isSMTP();
                $mail->Host       = 'smtp.hostinger.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'info@achieverscastle.com';
                $mail->Password   = 'Amplic@@7408';
                $mail->SMTPSecure = 'ssl';
                $mail->Port       = 465;

                $mail->setFrom('info@achieverscastle.com', 'Achiever\'s Castle');
                $mail->addAddress($admin_email);
                $mail->addReplyTo($guardian_email, $first_name . ' ' . $last_name);

                $mail->isHTML(true);
                $mail->Subject = $mail_subject;
                $mail->Body    = $email_body;

                if($mail->send()){
                    echo "<script>
                    alert('Enrollment submitted & email sent successfully!');
                    window.location='enroll_query.php';
                    </script>";
                    exit;
                } else {
                    echo "<script>
                    alert('Enrollment saved but email failed');
                    window.location='enroll_query.php';
                    </script>";
                    exit;
                }

            } catch (Exception $e) {
                echo "<script>
                alert('Mail error: ".$mail->ErrorInfo."');
                window.location='enroll_query.php';
                </script>";
                exit;
            }
  }
  ?>

  <!DOCTYPE html>
  <html lang="en">
  <head>
      <meta charset="UTF-8">
      <title>Student Enrollment | Achiever's Castle</title>
      <meta name="viewport" content="width=device-width, initial-scale=1">
      <link rel="shortcut icon" href="assets/img/favicon.ico">
      <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
      <link href="https://fonts.googleapis.com/css2?family=Love+Ya+Like+A+Sister&display=swap" rel="stylesheet">
      <style>
      
    *{
        margin:0;
        padding:0;
        box-sizing:border-box;
        font-family: "Poppins", sans-serif;
      }

    /* ===== ENROLL SECTION ===== */

    .enroll-section{
    padding:60px 20px 70px;
    background:#f7f9fc;
    }

    .enroll-title{
    font-family:"Love Ya Like A Sister", cursive;
    font-size:48px;
    text-align:center;
    color:#05364d;
    margin-bottom:35px;
    }

    .enroll-form{
    max-width:900px;
    margin:0 auto;
    background:#fff;
    padding:45px 40px;
    border-radius:22px;
    box-shadow:0 20px 45px rgba(0,0,0,0.08);
    }

    .form-row{
    display:flex;
    gap:22px;
    margin-bottom:20px;
    }

    .form-group{
    flex:1;
    display:flex;
    flex-direction:column;
    }

    .form-group label{
    font-weight:600;
    margin-bottom:6px;
    font-size:15px;
    color:#333;
    }

    .form-group input,
    .form-group select,
    .form-group textarea{
    padding:14px 16px;
    border-radius:12px;
    border:1px solid #ddd;
    font-size:14px;
    width:100%;
    }

    .form-group textarea{
    height:130px;
    resize:none;
    }

    .submit-btn{
    background:#e8063c;
    color:#fff;
    border:none;
    padding:14px 50px;
    border-radius:30px;
    font-size:16px;
    font-weight:600;
    cursor:pointer;
    transition:0.3s;
    margin-top:15px;
    }

    .submit-btn:hover{
    background:#111;
    }

    .price-box{
    background:#f0f5ff;
    border-radius:12px;
    padding:12px;
    margin-bottom:20px;
    display:none;
    font-weight:600;
    text-align:center;
    color:#05364d;
    }

    .section-title { 
    font-size: 1.4rem; 
    margin: 30px 0 15px; 
    color: #05364d; 
    border-bottom: 2px solid #e8063c; 
    padding-bottom: 8px;
    }
    .terms-box {
    background: #fff8e1;
    padding: 20px;
    border-radius: 12px;
    margin: 25px 0;
    font-size: 0.95rem;
    line-height: 1.6;
    }
    .form-check-label { cursor: pointer; }
                
        /* TABLET */

    @media(max-width:1024px){

    .enroll-title{
    font-size:42px;
    }

    .enroll-form{
    padding:35px 30px;
    }

    }

        /* MOBILE */

    @media(max-width:768px){

    .enroll-section{
    padding:40px 15px 60px;
    }

    .enroll-title{
    font-size:32px;
    margin-bottom:25px;
    }

    .form-row{
    flex-direction:column;
    gap:15px;
    }

    .enroll-form{
    padding:25px;
    border-radius:18px;
    }

    .submit-btn{
    width:100%;
    padding:14px;
    }

    }
    </style>
    </head>
    <body>
  <?php include 'header.php'; ?>

  <section class="enroll-section">
      <div class="container">
          <h2 class="enroll-title">Student Enrollment Form</h2>

          <form method="POST" class="enroll-form">

              <!-- Personal Details -->
              <h3 class="section-title">Student Details</h3>
              <div class="form-row">
                  <div class="form-group">
                      <label>First Name *</label>
                      <input type="text" name="first_name" required placeholder="Enter First Name">
                  </div>
                  <div class="form-group">
                      <label>Last Name *</label>
                      <input type="text" name="last_name" required placeholder="Enter Last Name">
                  </div>
              </div>

              <div class="form-row">
                  <div class="form-group">
                      <label>Date of Birth *</label>
                      <input type="date" name="dob" required>
                  </div>
                  <div class="form-group">
                      <label>Grade *</label>
                      <select name="grade" required>
                          <option value="">Select Grade</option>
                          <option value="Pre-School">Pre-School</option>
                          <!--<option value="Pre-Kindergarten">Pre-Kindergarten</option>-->
                          <option value="Kindergarten">Kindergarten</option>
                          <option value="Grade 1">Grade 1</option>
                          <option value="Grade 2">Grade 2</option>
                          <option value="Grade 3">Grade 3</option>
                          <option value="Grade 4">Grade 4</option>
                          <option value="Grade 5">Grade 5</option>
                          <option value="Grade 6">Grade 6</option>
                          <option value="Grade 7">Grade 7</option>
                          <option value="Grade 8">Grade 8</option>
                          <option value="Grade 9">Grade 9</option>
                          <option value="Grade 10">Grade 10</option>
                          <option value="Grade 11">Grade 11</option>
                          <option value="Grade 12">Grade 12</option>
                      </select>
                  </div>
              </div>

              <div class="form-row">
                  <div class="form-group">
                      <label>Program (optional)</label>
                      <input type="text" name="program" placeholder="e.g. Early Learner, Elementary, Advanced Learner etc.">
                  </div>
                  <div class="form-group">
                      <label>Subject of Interest *</label>
                      <select name="subject" required>
                          <option value="">Select Subject</option>
                          <option value="Mathematics">Mathematics</option>
                          <option value="Science">Science</option>
                          <option value="Reading">Reading & Writing </option>
                          <!--<option value="Writing">Writing</option>-->
                      </select>
                  </div>
              </div>

              <!-- Guardian Information -->
            <h3 class="section-title">Guardian Information</h3>
              <div class="form-row">
                  <div class="form-group">
                      <label>Guardian's Name *</label>
                      <input type="text" name="guardian_name" required placeholder="Enter Guardian's Full Name">
                  </div>
              </div>
              <div class="form-row">
                  <div class="form-group">
                      <label>Guardian's Email *</label>
                      <input type="email" name="guardian_email" required placeholder="guardian@example.com">
                  </div>
                  <div class="form-group">
                      <label>Guardian's Contact Number *</label>
                      <input type="text" name="guardian_phone" required 
                            placeholder="Enter 10-digit number" 
                            pattern="[0-9]{10}" maxlength="10" 
                            oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0,10);">
                  </div>
              </div>

              <!-- Parent Information -->
              <h3 class="section-title">Parent Information</h3>
              <div class="form-row">
                  <div class="form-group">
                      <label>Mother's Name</label>
                      <input type="text" name="mother_name" placeholder="Enter Mother's Name">
                  </div>
                  <div class="form-group">
                      <label>Mother's Contact Number</label>
                      <input type="text" name="mother_phone" placeholder="Enter Mother's Phone" placeholder="Enter 10-digit number" 
                            pattern="[0-9]{10}" maxlength="10" 
                            oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0,10);">
                  </div>
              </div>
              <div class="form-row">
                  <div class="form-group">
                      <label>Father's Name</label>
                      <input type="text" name="father_name" placeholder="Enter Father's Name">
                  </div>
                  <div class="form-group">
                      <label>Father's Contact Number</label>
                      <input type="text" name="father_phone" placeholder="Enter Father's Phone" placeholder="Enter 10-digit number" 
                            pattern="[0-9]{10}" maxlength="10" 
                            oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0,10);">
                  </div>
              </div>

              <!-- Emergency Contact & Authorized Pickup -->
              <h3 class="section-title">Emergency Contact & Authorized Pickup</h3>
              <div class="form-row">
                  <div class="form-group">
                      <label>Emergency Contact Name</label>
                      <input type="text" name="emergency_name" placeholder="Emergency Contact Name">
                  </div>
                  <div class="form-group">
                      <label>Emergency Contact Number</label>
                      <input type="text" name="emergency_phone" placeholder="Emergency Phone" pattern="[0-9]{10}" maxlength="10" 
                            oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0,10);">
                  </div>
              </div>
              <div class="form-row">
                  <div class="form-group">
                      <label>Authorized Pickup Name</label>
                      <input type="text" name="authorized_name" placeholder="Name of Authorized Person">
                  </div>
                  <div class="form-group">
                      <label>Relation</label>
                      <input type="text" name="authorized_relation" placeholder="e.g. Parent, Aunt, etc.">
                  </div>
              </div>

              <!-- Message -->
              <div class="form-group">
                  <label>Comments / Additional Message</label>
                  <textarea name="message" placeholder="Any additional comments or questions..."></textarea>
              </div>

              <!-- Terms & Conditions -->
            <?php include 'terms.php'; ?>

            <div class="terms-box">
                <p><strong>Terms & Conditions:</strong></p>
                <?php echo $terms_content; ?>

                <div class="form-check mt-3">
                    <input class="form-check-input" type="checkbox" name="terms_agreed" required>
                    <label class="form-check-label">
                        <strong>I agree to the Terms & Conditions</strong>
                    </label>
                </div>
            </div>
              <div style="text-align:center">
                  <button type="submit" name="submit_query" class="submit-btn">Submit</button>
              </div>
          </form>
      </div>
  </section>

  <?php include 'footer.php'; ?>
  </body>
  </html>