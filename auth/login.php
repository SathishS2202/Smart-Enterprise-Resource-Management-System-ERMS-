<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>ERMS Login</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="../assets/css/auth.css">
</head>
<body>

<div class="container-fluid vh-100">
    <div class="row h-100">

        <!-- LEFT SIDE -->
        <div class="col-md-6 d-none d-md-flex left-panel">
            <div class="m-auto text-white px-5 text-center">
                <h1 class="fw-bold">ERMS</h1>
                <p class="fs-5 mt-3">
                    Smart Enterprise Resource Management System
                </p>
            </div>
        </div>

        <!-- RIGHT SIDE -->
        <div class="col-md-6 d-flex align-items-center justify-content-center">
            <div class="login-card p-4 shadow">

                <h4 class="mb-4 text-center">Sign In to Your Account</h4>

                <!-- Session Messages -->
                <?php
                if (isset($_SESSION['error'])) {
                    echo '<div class="alert alert-danger">'.$_SESSION['error'].'</div>';
                    unset($_SESSION['error']);
                }
                if (isset($_SESSION['success'])) {
                    echo '<div class="alert alert-success">'.$_SESSION['success'].'</div>';
                    unset($_SESSION['success']);
                }
                ?>

                <form method="POST" action="login_process.php">
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" placeholder="Enter email" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" placeholder="Enter password" required>
                    </div>

                    <div class="d-flex justify-content-between mb-3">
                        <div>
                            <input type="checkbox" id="remember" name="remember">
                            <label for="remember">Remember me</label>
                        </div>
                        <a href="forgot_password.php" class="text-decoration-none">Forgot password?</a>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Login</button>
                    <p class="text-center mt-3">
                        Don't have an account? <a href="register.php">Register</a>
                    </p>
                </form>
            </div>
        </div>
    </div>
</div>
</body>
</html>
