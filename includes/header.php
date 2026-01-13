<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>ERMS Dashboard</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<div class="topbar">
    <div class="logo">ERMS</div>
    <div class="user-info">
        <?= htmlspecialchars($_SESSION['user_name']) ?>
    </div>
</div>

<div class="container">
