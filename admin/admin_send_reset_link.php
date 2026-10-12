<?php
include "../db_config.php";

/* ================= LOAD ENV ================= */
$env = parse_ini_file(__DIR__ . '/../.env');
if (!$env || empty($env['APP_URL'])) {
    die("Configuration error");
}

$baseUrl = rtrim($env['APP_URL'], '/');

/* ================= RESPONSE FUNCTION ================= */
function showMessage($type, $title, $message, $btnText = "Back", $btnLink = "admin_forgot_password.php")
{
    $color1 = $type == "success" ? "#0d47a1" : "#dc3545";
    $color2 = $type == "success" ? "#1565c0" : "#ff4d4d";
    $icon   = $type == "success" ? "bi-check-circle-fill" : "bi-exclamation-triangle-fill";

    echo '
    <!DOCTYPE html>
    <html lang="en">
    <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>'.$title.'</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Love+Ya+Like+A+Sister&display=swap" rel="stylesheet">

    <style>
    body{
        margin:0;
        min-height:100vh;
        display:flex;
        justify-content:center;
        align-items:center;
        background:linear-gradient(135deg,#0d47a1,#1565c0,#ef5350);
        font-family:Segoe UI,sans-serif;
        padding:20px;
    }
    .box{
        width:500px;
        max-width:100%;
        background:#fff;
        border-radius:20px;
        padding:35px 30px;
        text-align:center;
        box-shadow:0 20px 50px rgba(0,0,0,.18);
    }
    .icon{
        width:85px;
        height:85px;
        margin:auto auto 18px;
        border-radius:50%;
        display:flex;
        align-items:center;
        justify-content:center;
        color:#fff;
        font-size:34px;
        background:linear-gradient(135deg,'.$color1.','.$color2.');
    }
    h2{
        font-weight:400;
        font-size:36px;
        margin-bottom:10px;
        background: linear-gradient(to right, #e02121, #2f55a4);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        font-family:"Love Ya Like A Sister", cursive;
    }
    p{
        color:#555;
        font-size:15px;
        line-height:1.7;
        margin-bottom:25px;
    }
    .btnx{
        display:inline-block;
        padding:12px 24px;
        border-radius:30px;
        color:#fff;
        text-decoration:none;
        font-weight:700;
        background:linear-gradient(135deg,'.$color1.','.$color2.');
        box-shadow:0 10px 22px rgba(0,0,0,.15);
    }
    </style>
    </head>
    <body>

    <div class="box">
        <div class="icon"><i class="bi '.$icon.'"></i></div>
        <h2>'.$title.'</h2>
        <p>'.$message.'</p>
        <a href="'.$btnLink.'" class="btnx">'.$btnText.'</a>
    </div>

    </body>
    </html>';
    exit;
}

/* ================= VALIDATE INPUT ================= */
if (empty($_POST['email'])) {
    showMessage("error", "Invalid Request", "Please enter your registered email address.");
}

$email = mysqli_real_escape_string($conn, $_POST['email']);

/* ================= CHECK EMAIL ================= */
$q = mysqli_query($conn, "
    SELECT name,email 
    FROM admins 
    WHERE email='$email'
");

if (mysqli_num_rows($q) != 1) {
    showMessage(
        "error",
        "Email Not Found",
        "We could not find any admin account linked with this email address.",
        "Try Again",
        "admin_forgot_password.php"
    );
}

$user = mysqli_fetch_assoc($q);

/* ================= GENERATE TOKEN ================= */
$token = bin2hex(random_bytes(32));

/* ================= UPDATE TOKEN ================= */
$update = mysqli_query($conn, "
    UPDATE admins
    SET reset_token='$token',
        token_expiry = DATE_ADD(NOW(), INTERVAL 15 MINUTE)
    WHERE email='{$user['email']}'
");

if (!$update) {
    showMessage("error", "Server Error", "Unable to process request right now. Please try again.");
}

/* ================= RESET LINK ================= */
$resetLink = $baseUrl . "/admin/admin_reset_password.php?token=$token";

/* ================= PHPMAILER ================= */
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require "../PHPMailer/PHPMailer.php";
require "../PHPMailer/SMTP.php";
require "../PHPMailer/Exception.php";


$mail = new PHPMailer(true);

try {

    $mail->isSMTP();
    $mail->Host='smtp.hostinger.com';
    $mail->SMTPAuth   = true;
    $mail->Username='info@achieverscastle.com';
    $mail->Password='7|fl@1wBj';
    $mail->SMTPSecure='ssl';
    $mail->Port=465;

    $mail->SMTPOptions = [
        'ssl' => [
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true
        ]
    ];

    $mail->setFrom('info@achieverscastle.com', 'Achiever\'s Castle');
    $mail->addAddress($user['email'], $user['name']);

    $mail->isHTML(false);
    $mail->Subject = "Password Reset Link";

    $mail->Body =
"Hello {$user['name']},

Open the link below to reset your password:

$resetLink

This link expires in 15 minutes.

Achiever's Castle";

    $mail->send();

    showMessage(
        "success",
        "Email Sent",
        "Password reset link has been sent successfully to your registered email address.",
        "Back to Login",
        "login.php"
    );

} catch (Exception $e) {
    die($mail->ErrorInfo);
}