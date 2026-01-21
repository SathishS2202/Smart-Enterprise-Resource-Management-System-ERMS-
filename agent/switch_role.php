<?php
session_start();

// Must be logged in
if (!isset($_SESSION['user_id'], $_SESSION['role'])) {
    header("Location: auth/login.php");
    exit;
}

$role = ucfirst($_GET['role'] ?? '');

// Only allow switching if original role is Admin
if ($_SESSION['role'] !== 'Admin') {
    die("Unauthorized role switch");
}

$allowed = ['Admin','Agent','Client'];
if (!in_array($role, $allowed)) {
    die("Invalid role");
}

// Set the active role
$_SESSION['active_role'] = $role;

// Redirect to respective dashboard
switch ($role) {
    case 'Admin': header("Location: ../admin/dashboard.php"); break;
    case 'Agent': header("Location: ../agent/dashboard.php"); break;
    case 'Client': header("Location: ../client/dashboard.php"); break;
}
exit;
