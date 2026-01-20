<?php
session_start();

// Clear all session variables
$_SESSION = [];

// Destroy the session
session_destroy();

// Redirect to main login page
header("Location: ../auth/login.php"); // "../" because logout.php is inside admin/agent/client folders
exit;
?>
