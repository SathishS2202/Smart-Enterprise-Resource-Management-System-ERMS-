<?php
session_start();
include '../includes/db.php';

if ($_SESSION['role'] !== 'Admin') {
    header("Location: ../login.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add User</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">

    <style>
        body {
            background: #f4f6f9;
            font-family: Arial, sans-serif;
        }

        .form-container {
            max-width: 550px;
            margin: 60px auto;
            background: #fff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.08);
        }

        .form-container h3 {
            text-align: center;
            margin-bottom: 25px;
            color: #111827;
        }

        .form-group {
            margin-bottom: 18px;
        }

        .form-group label {
            font-weight: 600;
            margin-bottom: 6px;
            display: block;
        }

        .form-group input,
        .form-group select {
            width: 80%;
            padding: 10px 12px;
            border-radius: 8px;
            border: 1px solid #d1d5db;
            outline: none;
        }

        .form-group input:focus,
        .form-group select:focus {
            border-color: #2563eb;
        }

        .btn-submit {
            width: 100%;
            background: #2563eb;
            border: none;
            padding: 12px;
            color: #fff;
            font-size: 16px;
            border-radius: 8px;
            cursor: pointer;
        }

        .btn-submit:hover {
            background: #1e40af;
        }

        .back-link {
            display: block;
            text-align: center;
            margin-top: 15px;
            text-decoration: none;
            color: #2563eb;
        }
    </style>
</head>

<body>

<div class="form-container">
    <h3><i class="bi bi-person-plus"></i> Add New User</h3>

    <form method="POST" action="add_user_process.php">

        <div class="form-group">
            <label>Full Name</label>
            <input type="text" name="name" required>
        </div>

        <div class="form-group">
            <label>Username</label>
            <input type="text" name="username" required>
        </div>

        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" required>
        </div>

        <div class="form-group">
            <label>Phone Number</label>
            <input type="text" name="phone" required>
        </div>

        <div class="form-group">
            <label>Role</label>
            <select name="role_id" required>
                <option value="">Select Role</option>
                <option value="1">Admin</option>
                <option value="2">Agent</option>
                <option value="3">Client</option>
            </select>
        </div>

        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" required>
        </div>

        <button class="btn-submit">Create User</button>

        <a href="users.php" class="back-link">← Back to Users</a>
    </form>
</div>

</body>
</html>
 