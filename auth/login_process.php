<?php
session_start();
include '../includes/db.php';

/* Allow only POST */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: login.php");
    exit;
}

/* Get inputs */
$email    = trim($_POST['email']);
$password = $_POST['password'];

/* Basic validation */
if ($email === '' || $password === '') {
    $_SESSION['error'] = "All fields are required.";
    header("Location: login.php");
    exit;
}

/*
    Fetch user + role
    Assumption:
    - One primary role per user
    - Can be extended to multiple roles later
*/
$sql = "
    SELECT 
        u.id,
        u.name,
        u.email,
        u.password,
        u.status,
        r.role_name
    FROM users u
    INNER JOIN roles r ON u.role_id = r.id
    WHERE u.email = ?
    LIMIT 1
";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "s", $email);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

/* User exists? */
if (!$result || mysqli_num_rows($result) !== 1) {
    $_SESSION['error'] = "Invalid email or password.";
    header("Location: login.php");
    exit;
}

$user = mysqli_fetch_assoc($result);

/* Password check */
if (!password_verify($password, $user['password'])) {
    $_SESSION['error'] = "Invalid email or password.";
    header("Location: login.php");
    exit;
}

/* Status check */
if ((int)$user['status'] !== 1) {
    $_SESSION['error'] = "Account inactive. Contact Admin.";
    header("Location: login.php");
    exit;
}

/* ROLE CHECK */
if (empty($user['role_name'])) {
    $_SESSION['error'] = "Role not assigned. Contact Admin.";
    header("Location: login.php");
    exit;
}

/* =================================
   LOGIN SUCCESS — SESSION STRUCTURE
   ================================= */

$_SESSION['user_id']     = $user['id'];

$_SESSION['role']        = $user['role_name'];  // original role
$_SESSION['active_role'] = $user['role_name'];  // start with same panel
$_SESSION['user_name'] = $user['name'];



/* =================================
   ROLE-BASED REDIRECT
   ================================= */

switch ($_SESSION['active_role']) {
    case 'Admin':
        header("Location: ../admin/dashboard.php");
        break;

    case 'Agent':
        header("Location: ../agent/dashboard.php");
        break;

    case 'Client':
        header("Location: ../client/dashboard.php");
        break;

    default:
        session_destroy();
        $_SESSION['error'] = "Invalid role.";
        header("Location: login.php");
        break;
}

exit;
