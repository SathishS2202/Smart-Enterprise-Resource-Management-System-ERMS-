<?php
// Start session at the top before any HTML
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>ERMS Register</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="../assets/css/auth.css">
</head>
<body>

<div class="container-fluid vh-100">
    <div class="row h-100">

        <!-- LEFT SIDE (Info panel, only on md+) -->
        <div class="col-md-6 d-none d-md-flex left-panel">
            <div class="m-auto text-white px-5 text-center">
                <h1 class="fw-bold">ERMS</h1>
                <p class="fs-5 mt-3">
                    Smart Enterprise Resource Management System
                </p>
            </div>
        </div>

        <!-- RIGHT SIDE (Register Card) -->
        <div class="col-md-6 d-flex align-items-center justify-content-center">
            <div class="login-card p-4 shadow">

                <h4 class="mb-4 text-center">Create a New Account</h4>

                <!-- Display Errors / Success Messages -->
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

                <form method="POST" action="register_process.php">
                    <div class="mb-3">
                        <label class="form-label">Full Name</label>
                        <input type="text" name="name" class="form-control" placeholder="Enter full name" >
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" placeholder="Enter email" >
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" placeholder="Enter password" >
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Confirm Password</label>
                        <input type="password" name="confirm_password" class="form-control" placeholder="Confirm password" >
                    </div>

                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="terms" name="terms" >
                        <label class="form-check-label" for="terms">I agree to the Terms</label>
                    </div>

                    <button class="btn btn-primary w-100">Sign Up</button>

                    <p class="text-center mt-3">
                        Already have an account? <a href="login.php">Login</a>
                    </p>
                </form>

            </div>
        </div>

    </div>
</div>

</body>
</html>
