<?php
session_start();
include '../includes/db.php';

$name     = trim($_POST['name']);
$username = trim($_POST['username']);
$email    = trim($_POST['email']);
$phone    = trim($_POST['phone']);
$role_id  = $_POST['role_id'];
$password = password_hash($_POST['password'], PASSWORD_DEFAULT);

/* Insert user */
mysqli_query($conn, "
    INSERT INTO users (name, username, email, phone, role_id, password)
    VALUES ('$name', '$username', '$email', '$phone', '$role_id', '$password')
");

header("Location: users.php");
exit;
