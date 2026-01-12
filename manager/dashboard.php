<?php
require '../includes/auth_check.php';
requireRole('Manager');
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manager Dashboard</title>
</head>
<body>
    <h1>Welcome Manager</h1>
    <p>Manage teams and reports.</p>

    <ul>
        <li><a href="reports.php">View Reports</a></li>
        <li><a href="../auth/logout.php">Logout</a></li>
    </ul>
</body>
</html>
