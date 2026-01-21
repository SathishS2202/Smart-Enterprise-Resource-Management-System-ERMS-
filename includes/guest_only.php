<?php
// guest.php — use this at the top of login.php, register.php, index.php etc.

session_start();

// If user is already logged in, redirect to their active dashboard
if (isset($_SESSION['user_id'], $_SESSION['active_role'])) {
    switch ($_SESSION['active_role']) {
        case 'Admin':
            header("Location: ../admin/dashboard.php");
            exit;
        case 'Agent':
            header("Location: ../agent/dashboard.php");
            exit;
        case 'Client':
            header("Location: ../client/dashboard.php");
            exit;
    }
}
