<?php
session_start();

if (isset($_SESSION['user_id'], $_SESSION['active_role'])) {
    switch ($_SESSION['active_role']) {
        case 'Admin':
            header("Location: admin/dashboard.php");
            exit;
        case 'Agent':
            header("Location: agent/dashboard.php");
            exit;
        case 'Client':
            header("Location: client/dashboard.php");
            exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>ERMS | Smart Enterprise Resource Management System</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/landing.css">
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg navbar-dark fixed-top">
    <div class="container">
        <a class="navbar-brand fw-bold" href="#">ERMS</a>
        <div class="ms-auto">
            <a href="auth/login.php" class="btn btn-outline-light btn-sm">
                Login
            </a>
        </div>
    </div>
</nav>

<!-- HERO SECTION -->
<section class="hero-section">
    <div class="container text-center text-white">
        <h1 class="fw-bold">Enterprise Resource Management System</h1>
        <p class="lead mt-3">
            A centralized platform to manage users, projects, tasks,
            attendance, documents, and reports efficiently.
        </p>

        <div class="mt-4">
            <a href="auth/login.php" class="btn btn-primary btn-lg px-5">
                Get Started
            </a>
        </div>
    </div>
</section>

<!-- FEATURES SECTION -->
<section class="features-section">
    <div class="container">
        <div class="row text-center">

            <div class="col-md-4 mb-4">
                <div class="feature-card">
                    <h5>User & Role Management</h5>
                    <p>Secure authentication and role-based access control.</p>
                </div>
            </div>

            <div class="col-md-4 mb-4">
                <div class="feature-card">
                    <h5>Project & Task Tracking</h5>
                    <p>Manage projects, assign tasks, and track progress.</p>
                </div>
            </div>

            <div class="col-md-4 mb-4">
                <div class="feature-card">
                    <h5>Reports & Analytics</h5>
                    <p>Generate insights with real-time reporting tools.</p>
                </div>
            </div>

        </div>
    </div>
</section>

<!-- SECOND FEATURES ROW -->
<section class="features-section bg-light">
    <div class="container">
        <div class="row text-center">

            <div class="col-md-4 mb-4">
                <div class="feature-card">
                    <h5>Attendance & Leave</h5>
                    <p>Track employee attendance and manage leave requests.</p>
                </div>
            </div>

            <div class="col-md-4 mb-4">
                <div class="feature-card">
                    <h5>Document Management</h5>
                    <p>Upload, secure, and manage enterprise documents.</p>
                </div>
            </div>

            <div class="col-md-4 mb-4">
                <div class="feature-card">
                    <h5>Notifications</h5>
                    <p>Get real-time updates on tasks and approvals.</p>
                </div>
            </div>

        </div>
    </div>
</section>

<!-- FOOTER -->
<footer class="footer">
    <div class="container text-center">
        <small>© 2026 ERMS. All rights reserved.</small>
    </div>
</footer>

</body>
</html>
