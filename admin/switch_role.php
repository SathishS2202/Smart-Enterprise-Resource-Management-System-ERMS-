<?php
include '../includes/auth_check.php';
checkRole('Admin');  // Only allow Admin
?>

<?php
session_start();
include('../includes/db.php');

if(!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

if(!isset($_GET['role'])) {
    header("Location: dashboard.php");
    exit;
}

$role = $_GET['role'];
$user_id = $_SESSION['user_id'];

// Check if role is valid
if($role === 'agent') {
    $_SESSION['role'] = 'Agent';
    // Optional: store current agent ID if needed
    $_SESSION['agent_view_id'] = $user_id;
} elseif($role === 'client') {
    $_SESSION['role'] = 'Client';
    // Optional: store client ID
    $_SESSION['client_view_id'] = $user_id;
} else {
    $_SESSION['role'] = 'Admin';
}

// Redirect to dashboard
header("Location: dashboard.php");
exit;
?>
