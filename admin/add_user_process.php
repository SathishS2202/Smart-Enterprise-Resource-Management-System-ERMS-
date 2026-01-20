<?php
include '../includes/auth_check.php';
checkRole('Admin');  // Only allow Admin
?>

<?php
session_start();
include '../includes/db.php';

/* Only Admin */
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
     header("Location: ../auth/login.php");
    exit;
}

/* Only POST */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: add_user.php");
    exit;
}

/* Sanitize inputs */
$name     = trim($_POST['name'] ?? '');
$username = trim($_POST['username'] ?? '');
$email    = trim($_POST['email'] ?? '');
$phone    = trim($_POST['phone'] ?? '');
$role_id  = intval($_POST['role_id'] ?? 0);
$password = trim($_POST['password'] ?? '');

$errors = [];

/* Server-side validations */
if($name==='') $errors[] = "Full Name is required";
if($username==='') $errors[] = "Username is required";
if($email==='' || !filter_var($email,FILTER_VALIDATE_EMAIL)) $errors[] = "Valid Email is required";
if($phone==='') $errors[] = "Phone is required";
if($role_id<=0) $errors[] = "Select a Role";
if($password==='') $errors[] = "Password is required";

/* Check duplicate email */
if(mysqli_num_rows(mysqli_query($conn, "SELECT id FROM users WHERE email='$email'"))>0){
    $errors[] = "Email already exists!";
}

/* If any errors, redirect back */
if(count($errors)>0){
    $_SESSION['error'] = implode("<br>", $errors);
    header("Location: add_user.php");
    exit;
}

/* Hash password */
$hashed_pass = password_hash($password,PASSWORD_DEFAULT);

/* Insert */
$insert = mysqli_query($conn, "
    INSERT INTO users (name, username, email, phone, role_id, password)
    VALUES ('$name','$username','$email','$phone','$role_id','$hashed_pass')
");

if($insert){
    $_SESSION['success'] = "User created successfully!";
    header("Location: users.php");
}else{
    $_SESSION['error'] = "Something went wrong! ".mysqli_error($conn);
    header("Location: add_user.php");
}
exit;
?>
