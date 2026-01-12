<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/* User must be logged in */
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

/* Role checker function */
function requireRole($role)
{
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== $role) {
        header("Location: ../auth/login.php");
        exit();
    }
}
