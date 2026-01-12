<?php
session_start();
include '../includes/db.php';

if (!isset($_GET['token'])) {
    $_SESSION['error'] = "Invalid reset link.";
    header("Location: forgot_password.php");
    exit();
}

$token = $_GET['token'];

/* Check token */
$query = mysqli_query($conn, "
    SELECT email, expires_at 
    FROM password_resets 
    WHERE token='$token' 
    LIMIT 1
");

if (mysqli_num_rows($query) !== 1) {
    $_SESSION['error'] = "Invalid or expired link.";
    header("Location: forgot_password.php");
    exit();
}

$data = mysqli_fetch_assoc($query);

/* Check expiry */
if (strtotime($data['expires_at']) < time()) {
    $_SESSION['error'] = "Reset link expired.";
    header("Location: forgot_password.php");
    exit();
}

$email = $data['email'];
?>
<!DOCTYPE html>
<html>
<head>
    <title>Reset Password</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
    <div class="col-md-5 mx-auto">
        <div class="card p-4 shadow">
            <h4 class="text-center mb-3">Set New Password</h4>

            <?php
            if (isset($_SESSION['error'])) {
                echo '<div class="alert alert-danger">'.$_SESSION['error'].'</div>';
                unset($_SESSION['error']);
            }
            ?>

            <form method="POST" action="reset_password_process.php">
                <input type="hidden" name="email" value="<?= htmlspecialchars($email) ?>">
                <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">

                <div class="mb-3">
                    <label>New Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label>Confirm Password</label>
                    <input type="password" name="confirm_password" class="form-control" required>
                </div>

                <button class="btn btn-primary w-100">Update Password</button>
            </form>
        </div>
    </div>
</div>

</body>
</html>
