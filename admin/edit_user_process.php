<?php
session_start();
include '../includes/db.php';

$id = $_POST['id'];
$name = trim($_POST['name']);
$email = trim($_POST['email']);
$role_id = $_POST['role_id'];

mysqli_query($conn, "
    UPDATE users 
    SET name='$name', email='$email', role_id='$role_id'
    WHERE id=$id
");

header("Location: users.php");
exit;
