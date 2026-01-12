<?php
session_start();
include '../includes/db.php';


/* Show errors (disable in production) */
ini_set('display_errors', 1);
error_reporting(E_ALL);


require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: forgot_password.php");
    exit();
}

$email = trim($_POST['email']);

/* Validation */
if (empty($email)) {
    $_SESSION['error'] = "Email is required.";
    header("Location: forgot_password.php");
    exit();
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['error'] = "Invalid email format.";
    header("Location: forgot_password.php");
    exit();
}

/* Check user exists */
$result = mysqli_query($conn, "SELECT id FROM users WHERE email='$email' LIMIT 1");

if (mysqli_num_rows($result) !== 1) {
    $_SESSION['error'] = "Email not registered.";
    header("Location: forgot_password.php");
    exit();
}

/* Generate token */
$token = bin2hex(random_bytes(32));
$expires_at = date("Y-m-d H:i:s", strtotime("+15 minutes"));

mysqli_query($conn, "DELETE FROM password_resets WHERE email='$email'");
mysqli_query($conn, "
    INSERT INTO password_resets (email, token, expires_at)
    VALUES ('$email', '$token', '$expires_at')
");

/* Reset link */
$reset_link = "http://localhost/erms/auth/reset_password.php?token=$token";

/* Send Email */
$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;

    $mail->Username = 'sathishs2202@gmail.com';
    $mail->Password = 'iegaktnuladdhjsm'; // ✅ REAL APP PASSWORD

    $mail->SMTPSecure = 'tls';
    $mail->Port = 587;

    // Enable only if debugging
    // $mail->SMTPDebug = 2;
    // $mail->Debugoutput = 'html';

    $mail->setFrom('sathishs2202@gmail.com', 'ERMS Support');
    $mail->addAddress($email);

    $mail->isHTML(true);
    $mail->Subject = 'Reset Your ERMS Password';
    $mail->Body = "
        <h3>Password Reset Request</h3>
        <p>Click the link below to reset your password:</p>
        <p><a href='$reset_link'>Reset Password</a></p>
        <p>This link expires in 15 minutes.</p>
    ";

    $mail->send();
    $_SESSION['success'] = "Password reset link has been sent to your email.";
} catch (Exception $e) {
    $_SESSION['error'] = "Mailer Error: " . $mail->ErrorInfo;
}

header("Location: forgot_password.php");
exit();
