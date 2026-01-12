<?php
session_start();
include '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: login.php");
    exit();
}

$email = $_POST['email'];
$token = $_POST['token'];
$password = $_POST['password'];
$confirm = $_POST['confirm_password'];

/* Validation */
if (empty($password) || empty($confirm)) {
    $_SESSION['error'] = "All fields required.";
    header("Location: reset_password.php?token=$token");
    exit();
}

if ($password !== $confirm) {
    $_SESSION['error'] = "Passwords do not match.";
    header("Location: reset_password.php?token=$token");
    exit();
}

if (strlen($password) < 6) {
    $_SESSION['error'] = "Password must be at least 6 characters.";
    header("Location: reset_password.php?token=$token");
    exit();
}

/* Verify token again */
$check = mysqli_query($conn, "
    SELECT id 
    FROM password_resets 
    WHERE email='$email' AND token='$token'
");

if (mysqli_num_rows($check) !== 1) {
    $_SESSION['error'] = "Invalid reset request.";
    header("Location: forgot_password.php");
    exit();
}

/* Update password */
$hashed = password_hash($password, PASSWORD_BCRYPT);

mysqli_query($conn, "
    UPDATE users 
    SET password='$hashed' 
    WHERE email='$email'
");

/* Remove used token */
mysqli_query($conn, "
    DELETE FROM password_resets 
    WHERE email='$email'
");

$_SESSION['success'] = "Password updated successfully. Please login.";
header("Location: login.php");
exit();
