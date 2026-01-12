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
    SELECT users.id, users.name, users.password, roles.role_name
    FROM users
    JOIN roles ON users.role_id = roles.id
    WHERE users.email = ?
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

/* LOGIN SUCCESS */
$_SESSION['user_id']   = $user['id'];
$_SESSION['user_name'] = $user['name'];
$_SESSION['role']      = $user['role_name'];

/* ROLE-BASED REDIRECT */
switch ($user['role_name']) {

    case 'Admin':
        header("Location: ../admin/dashboard.php");
        break;

    case 'Manager':
        header("Location: ../manager/dashboard.php");
        break;

    case 'Employee':
        header("Location: ../employee/dashboard.php");
        break;

    default:
        $_SESSION['error'] = "Role not assigned.";
        header("Location: login.php");
}

exit();
