<?php
session_start();
include '../includes/db.php';

$id = $_GET['id'];

mysqli_query($conn, "DELETE FROM users WHERE id=$id");

header("Location: users.php");
exit;
