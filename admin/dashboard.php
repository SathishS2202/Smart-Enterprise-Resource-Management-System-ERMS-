<?php
require '../includes/auth_check.php';
requireRole('Admin');
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
</head>
<body>
    <h1>Welcome Admin</h1>
    <p>You have full system access.</p>

    <ul>
        <li><a href="users.php">Manage Users</a></li>
        <li><a href="roles.php">Manage Roles</a></li>
        <li><a href="../auth/logout.php">Logout</a></li>
    </ul>
</body>
</html>
