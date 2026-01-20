<?php
session_start();
include '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: login.php");
    exit();
}

$email = trim($_POST['email']);
$password = $_POST['password'];

/* Validation */
if (empty($email) || empty($password)) {
    $_SESSION['error'] = "All fields are required.";
    header("Location: login.php");
    exit();
}

/* Fetch user with role */
$query = "
    SELECT u.id, u.name, u.password, u.role_id, r.role_name, u.status
    FROM users u
    LEFT JOIN roles r ON u.role_id = r.id
    WHERE u.email = ?
    LIMIT 1
";

$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "s", $email);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) !== 1) {
    $_SESSION['error'] = "Invalid email or password.";
    header("Location: login.php");
    exit();
}

$user = mysqli_fetch_assoc($result);

/* Verify password */
if (!password_verify($password, $user['password'])) {
    $_SESSION['error'] = "Invalid email or password.";
    header("Location: login.php");
    exit();
}

/* Check role and status */
if (empty($user['role_id']) || empty($user['role_name'])) {
    $_SESSION['error'] = "Role not assigned. Contact Admin.";
    header("Location: login.php");
    exit();
}

if ($user['status'] != 1) {
    $_SESSION['error'] = "Account inactive. Contact Admin.";
    header("Location: login.php");
    exit();
}

/* LOGIN SUCCESS */
$_SESSION['user_id']   = $user['id'];
$_SESSION['username']  = $user['name'];   // use 'username' key consistently
$_SESSION['role']      = $user['role_name'];

/* ROLE-BASED REDIRECT */
switch (strtolower($user['role_name'])) {
    case 'admin':
        header("Location: ../admin/dashboard.php");
        break;

    case 'agent':
        header("Location: ../agent/dashboard.php");
        break;

    case 'client':
        header("Location: ../client/dashboard.php");
        break;

    default:
        $_SESSION['error'] = "Role not assigned. Contact Admin.";
        header("Location: login.php");
        break;
}
exit();
