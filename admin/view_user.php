<?php
session_start();
include '../includes/db.php';

$id = $_GET['id'];

$user = mysqli_fetch_assoc(
    mysqli_query($conn, "
        SELECT users.*, roles.role_name 
        FROM users 
        JOIN roles ON users.role_id = roles.id 
        WHERE users.id = $id
    ")
);
?>

<h3>User Details</h3>

<p><b>Name:</b> <?= $user['name'] ?></p>
<p><b>Email:</b> <?= $user['email'] ?></p>
<p><b>Role:</b> <?= $user['role_name'] ?></p>
<p><b>Status:</b> <?= $user['status'] ? 'Active' : 'Inactive' ?></p>

<a href="users.php">Back</a>
