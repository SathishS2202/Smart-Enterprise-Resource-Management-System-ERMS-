`<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

include '../includes/db.php';

$user_id = $_SESSION['user_id'];

/* FETCH USER DATA */
$user = mysqli_fetch_assoc(mysqli_query($conn,"
    SELECT u.name, u.email, u.phone, r.role_name
    FROM users u
    JOIN roles r ON u.role_id = r.id
    WHERE u.id = $user_id
"));

$success = $error = "";

/* UPDATE PROFILE */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name  = trim(mysqli_real_escape_string($conn, $_POST['name']));
    $email = trim(mysqli_real_escape_string($conn, $_POST['email']));
    $phone = trim(mysqli_real_escape_string($conn, $_POST['phone']));

    if ($name == "" || $email == "") {
        $error = "Name and Email are required.";
    } else {
        mysqli_query($conn,"
            UPDATE users 
            SET name='$name', email='$email', phone='$phone'
            WHERE id=$user_id
        ");

        $_SESSION['username'] = $name;
        $success = "Profile updated successfully.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>My Profile</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">

<style>
body{margin:0;font-family:Segoe UI;background:#f3f4f6}

/* SIDEBAR */
.sidebar{
    position:fixed;left:0;top:0;height:100vh;width:70px;
    background:#111827;transition:.3s;overflow:hidden
}
.sidebar:hover{width:220px}
.sidebar a{
    display:flex;align-items:center;padding:15px;margin:5px 8px;
    gap:15px;color:#cbd5e1;text-decoration:none;border-radius:8px
}
.sidebar a:hover{background:#2563eb;color:#fff}
.sidebar i{font-size:20px;min-width:30px;text-align:center}
.sidebar span{opacity:0}
.sidebar:hover span{opacity:1}

/* MAIN */
.main{margin-left:70px;padding:30px;transition:.3s}
.sidebar:hover ~ .main{margin-left:230px}

/* PROFILE CARD */
.profile-card{
    max-width:600px;
    margin:auto;
    background:#fff;
    padding:30px;
    border-radius:12px;
    box-shadow:0 5px 15px rgba(0,0,0,.08)
}
.profile-avatar{
    width:100px;height:100px;
    background:#2563eb;
    color:#fff;
    font-size:36px;
    border-radius:50%;
    display:flex;
    align-items:center;
    justify-content:center;
    margin:auto;
}
</style>
</head>

<body>

<!-- SIDEBAR -->
<div class="sidebar">
    <a href="dashboard.php"><i class="bi bi-speedometer2"></i><span>Dashboard</span></a>
    <a href="projects.php"><i class="bi bi-folder"></i><span>Projects</span></a>
    <a href="tasks.php"><i class="bi bi-list-task"></i><span>Tasks</span></a>
    <a href="documents.php"><i class="bi bi-file-earmark-text"></i><span>Documents</span></a>
    <a href="profile.php"><i class="bi bi-person-circle"></i><span>My Profile</span></a>
    <a href="logout.php"><i class="bi bi-box-arrow-right"></i><span>Logout</span></a>
</div>

<!-- MAIN -->
<div class="main">

<div class="profile-card">

    <div class="profile-avatar mb-3">
        <?= strtoupper(substr($user['name'],0,1)) ?>
    </div>

    <h4 class="text-center mb-4">My Profile</h4>

    <?php if($success): ?>
        <div class="alert alert-success"><?= $success ?></div>
    <?php endif; ?>

    <?php if($error): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST">

        <div class="mb-3">
            <label class="form-label">Full Name</label>
            <input type="text" name="name" class="form-control"
                   value="<?= htmlspecialchars($user['name']) ?>">
        </div>

        <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control"
                   value="<?= htmlspecialchars($user['email']) ?>">
        </div>

        <div class="mb-3">
            <label class="form-label">Phone</label>
            <input type="text" name="phone" class="form-control"
                   value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
        </div>

        <div class="mb-3">
            <label class="form-label">Role</label>
            <input type="text" class="form-control" readonly
                   value="<?= $user['role_name'] ?>">
        </div>

        <div class="text-end">
            <button class="btn btn-primary">
                <i class="bi bi-save"></i> Update Profile
            </button>
        </div>

    </form>
</div>

</div>
</body>
</html>
`