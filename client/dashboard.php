<?php
include '../includes/auth_check.php';
if ($_SESSION['role'] != 'Client') {
    header("Location: ../auth/login.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Client Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container-fluid">
    <div class="row">
        <?php include '../includes/sidebar.php'; ?>

        <div class="col-md-10 p-4">
            <?php include '../includes/header.php'; ?>

            <div class="row g-4">
                <div class="col-md-4">
                    <div class="card p-3 text-center shadow">
                        <h6>My Projects</h6>
                        <h3>2</h3>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card p-3 text-center shadow">
                        <h6>Active Tasks</h6>
                        <h3>5</h3>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

</body>
</html>
