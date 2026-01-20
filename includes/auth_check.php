<?php
session_start();

// Check if user is logged in
if(!isset($_SESSION['user_id']) || !isset($_SESSION['role'])){
    header("Location: ../auth/login.php");
    exit;
}

// Function to check if user has correct role for this panel
function checkRole($requiredRole){
    if(!isset($_SESSION['role']) || $_SESSION['role'] !== $requiredRole){
        // Destroy session if wrong role
        session_unset();
        session_destroy();
        header("Location: ../auth/login.php");
        exit;
    }
}
?>
