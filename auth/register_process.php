<?php
include '../includes/db.php'; // DB connection

// Get form values and trim spaces
$name = trim($_POST['name']);
$email = trim($_POST['email']);
$password = $_POST['password'];
$confirm_password = $_POST['confirm_password'];
$terms = isset($_POST['terms']) ? true : false;

// Initialize an error array
$errors = [];

// 1️⃣ Validate Name
if (empty($name)) {
    $errors[] = "Full Name is required.";
} elseif (!preg_match("/^[a-zA-Z ]+$/", $name)) {
    $errors[] = "Full Name can contain only letters and spaces.";
}

// 2️⃣ Validate Email
if (empty($email)) {
    $errors[] = "Email is required.";
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "Invalid email format.";
} else {
    // Check if email already exists
    $email_check = mysqli_query($conn, "SELECT * FROM users WHERE email='$email'");
    if (mysqli_num_rows($email_check) > 0) {
        $errors[] = "Email is already registered.";
    }
}

// 3️⃣ Validate Password
if (empty($password)) {
    $errors[] = "Password is required.";
} elseif (strlen($password) < 6) {
    $errors[] = "Password must be at least 6 characters.";
}

// 4️⃣ Validate Confirm Password
if ($password !== $confirm_password) {
    $errors[] = "Passwords do not match.";
}

// 5️⃣ Validate Terms
if (!$terms) {
    $errors[] = "You must agree to the terms.";
}

// If there are errors, redirect back with error messages
if (!empty($errors)) {
    $_SESSION['error'] = implode("<br>", $errors);
    header("Location: register.php");
    exit();
}

// ✅ All validations passed
$role_id = 2; // Default user role
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Insert into database
$sql = "INSERT INTO users (role_id, name, email, password) 
        VALUES ('$role_id', '$name', '$email', '$hashed_password')";

if (mysqli_query($conn, $sql)) {
    $_SESSION['success'] = "Registration successful. Please login.";
    header("Location: login.php");
    exit();
} else {
    $_SESSION['error'] = "Database error: " . mysqli_error($conn);
    header("Location: register.php");
    exit();
}

mysqli_close($conn);
?>
