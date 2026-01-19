<?php
session_start();

if (!isset($_GET['role'])) {
    header("Location: dashboard.php");
    exit;
}

if ($_GET['role'] === 'agent') {
    $_SESSION['role'] = 'Agent';
    header("Location: ../agent/dashboard.php");
    exit;
}

if ($_GET['role'] === 'client') {
    $_SESSION['role'] = 'Client';
    header("Location: ../client/dashboard.php");
    exit;
}

header("Location: dashboard.php");
exit;
