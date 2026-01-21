<?php
session_start();

function allowOnly($role){
    if (!isset($_SESSION['user_id'], $_SESSION['active_role'])) {
        header("Location: ../auth/login.php");
        exit;
    }

    // Check the current active role
    if ($_SESSION['active_role'] !== $role) {
        header("Location: ../auth/login.php");
        exit;
    }
}
?>
