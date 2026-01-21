<?php
require_once '../includes/middleware.php';
allowOnly('Agent');

include '../includes/db.php';

$agent_id = $_SESSION['user_id'];

$user = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT name,email,username,phone FROM users WHERE id=$agent_id"
));

if(isset($_POST['update'])){
    $name  = mysqli_real_escape_string($conn,$_POST['name']);
    $phone = mysqli_real_escape_string($conn,$_POST['phone']);

    mysqli_query($conn,
        "UPDATE users SET name='$name', phone='$phone' WHERE id=$agent_id"
    );

    $_SESSION['user_name'] = $name;
    header("Location: profile.php?success=1");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>My Profile</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">

<style>
body{
    margin:0;
    font-family:'Segoe UI', sans-serif;
    background:#f4f6f9;
}
.sidebar{
    position:fixed; left:0; top:0; height:100vh; width:70px;
    background:#111827; transition:.3s; overflow:hidden;
}
.sidebar:hover{width:220px}
.sidebar a{
    display:flex; align-items:center; padding:15px; margin:5px 8px;
    gap:15px; color:#cbd5e1; text-decoration:none; border-radius:8px;
}
.sidebar a:hover{background:#2563eb;color:#fff}
.sidebar i{font-size:20px; min-width:30px; text-align:center}
.sidebar span{opacity:0; transition:.3s}
.sidebar:hover span{opacity:1}
.main{margin-left:70px; padding:20px; transition:.3s}
.sidebar:hover ~ .main{margin-left:230px}
.topbar{
    background:#fff; padding:12px 20px; display:flex; justify-content:space-between;
    border-bottom:1px solid #ddd; margin-bottom:20px; border-radius:0 0 8px 8px;
}
.profile-card{
    background:#fff; border-radius:12px; padding:30px;
    box-shadow:0 4px 15px rgba(0,0,0,.08);
}
.profile-header{
    display:flex; align-items:center; margin-bottom:25px;
}
.profile-header img{
    width:80px; height:80px; border-radius:50%; object-fit:cover; margin-right:20px;
    border:2px solid #2563eb;
}
.profile-header h3{margin:0; font-weight:600; color:#111827;}
.profile-header p{margin:0; color:#6b7280;}
.profile-section h5{
    font-weight:600; color:#374151; border-bottom:1px solid #e5e7eb; padding-bottom:5px;
    margin-bottom:15px;
}
.form-control{border-radius:8px;}
.btn-primary{border-radius:8px;}
.alert{border-radius:8px;}
</style>
</head>
<body>

<div class="sidebar">
    <a href="dashboard.php"><i class="bi bi-speedometer2"></i><span>Dashboard</span></a>
    <a href="projects.php"><i class="bi bi-folder"></i><span>Projects</span></a>
    <a href="tasks.php"><i class="bi bi-list-task"></i><span>Tasks</span></a>
    <a href="attendance.php"><i class="bi bi-calendar-check"></i><span>Attendance</span></a>
    <a href="documents.php"><i class="bi bi-file-earmark-text"></i><span>Documents</span></a>
    <a href="reports.php"><i class="bi bi-bar-chart"></i><span>Reports</span></a>
    <a href="profile.php"><i class="bi bi-person-circle"></i><span>Profile</span></a>
    <a href="logout.php"><i class="bi bi-box-arrow-right"></i><span>Logout</span></a>
</div>

<div class="main">
<div class="topbar">
    <div class="page-title">My Profile</div>
    <div><i class="bi bi-person"></i> <?= $_SESSION['user_name'] ?></div>
</div>

<div class="row justify-content-center">
<div class="col-lg-8 col-md-10">
<div class="profile-card">

<!-- Profile Header -->
<div class="profile-header">
    <img src="https://ui-avatars.com/api/?name=<?= urlencode($user['name']) ?>&background=2563eb&color=fff&size=128" alt="Avatar">
    <div>
        <h3><?= htmlspecialchars($user['name']) ?></h3>
        <p>Agent</p>
    </div>
</div>

<!-- Success Message -->
<?php if(isset($_GET['success'])){ ?>
<div class="alert alert-success">Profile updated successfully</div>
<?php } ?>

<!-- Personal Info Form -->
<div class="profile-section">
<h5>Personal Information</h5>
<form method="post">
    <div class="mb-3">
        <label>Name</label>
        <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($user['name']) ?>" required>
    </div>
    <div class="mb-3">
        <label>Phone</label>
        <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($user['phone']) ?>">
    </div>
    <button class="btn btn-primary w-100" name="update">Update Information</button>
</form>
</div>

<!-- Account Info (Read-only) -->
<div class="profile-section mt-4">
<h5>Account Information</h5>
<div class="mb-3">
    <label>Email</label>
    <input type="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" readonly>
</div>
<div class="mb-3">
    <label>Username</label>
    <input type="text" class="form-control" value="<?= htmlspecialchars($user['username']) ?>" readonly>
</div>
</div>

</div>
</div>
</div>

</div>
</body>
</html>
