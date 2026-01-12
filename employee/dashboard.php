<?php
require '../includes/auth_check.php';
requireRole('Employee');
?>

<!DOCTYPE html>
<html>
<head>
    <title>Employee Dashboard</title>
</head>
<body>
    <h1>Welcome Employee</h1>
    <p>View your tasks.</p>

    <ul>
        <li><a href="tasks.php">My Tasks</a></li>
        <li><a href="../auth/logout.php">Logout</a></li>
    </ul>
</body>
</html>
