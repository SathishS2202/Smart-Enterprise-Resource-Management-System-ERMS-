<?php
session_start();
include '../includes/db.php';

// Get form values
$name     = trim($_POST['name']);
$username = trim($_POST['username']);
$email    = trim($_POST['email']);
$phone    = trim($_POST['phone']);
$password = $_POST['password'];
$confirm_password = $_POST['confirm_password'];
$terms = isset($_POST['terms']);

$errors = [];

/* 1️⃣ Name Validation */
if (empty($name)) {
    $errors[] = "Full Name is required.";
} elseif (!preg_match("/^[a-zA-Z ]+$/", $name)) {
    $errors[] = "Full Name can contain only letters and spaces.";
}

/* 2️⃣ Username Validation */
if (empty($username)) {
    $errors[] = "Username is required.";
} elseif (!preg_match("/^[a-zA-Z0-9_]+$/", $username)) {
    $errors[] = "Username can contain letters, numbers, and underscore only.";
} else {
    $check_username = mysqli_query($conn, "SELECT id FROM users WHERE username='$username'");
    if (mysqli_num_rows($check_username) > 0) {
        $errors[] = "Username already exists.";
    }
}

/* 3️⃣ Email Validation */
if (empty($email)) {
    $errors[] = "Email is required.";
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "Invalid email format.";
} else {
    $check_email = mysqli_query($conn, "SELECT id FROM users WHERE email='$email'");
    if (mysqli_num_rows($check_email) > 0) {
        $errors[] = "Email is already registered.";
    }
}

/* 4️⃣ Phone Validation */
if (empty($phone)) {
    $errors[] = "Phone number is required.";
} elseif (!preg_match("/^[6-9][0-9]{9}$/", $phone)) {
    $errors[] = "Enter a valid 10-digit phone number.";
}

/* 5️⃣ Password Validation */
if (empty($password)) {
    $errors[] = "Password is required.";
} elseif (strlen($password) < 6) {
    $errors[] = "Password must be at least 6 characters.";
}

/* 6️⃣ Confirm Password */
if ($password !== $confirm_password) {
    $errors[] = "Passwords do not match.";
}

/* 7️⃣ Terms */
if (!$terms) {
    $errors[] = "You must agree to the terms.";
}

/* ❌ If errors exist */
if (!empty($errors)) {
    $_SESSION['error'] = implode("<br>", $errors);
    header("Location: register.php");
    exit();
}

/* ✅ Insert User */
$role_id = 2;
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

$sql = "INSERT INTO users (role_id, name, username, email, phone, password)
        VALUES ('$role_id', '$name', '$username', '$email', '$phone', '$hashed_password')";

if (mysqli_query($conn, $sql)) {
    $_SESSION['success'] = "Registration successful. Please login.";
    header("Location: login.php");
} else {
    $_SESSION['error'] = "Database error: " . mysqli_error($conn);
    header("Location: register.php");
}

mysqli_close($conn);
?>
