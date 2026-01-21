<?php
require_once '../includes/middleware.php';
allowOnly('Admin');


include('../includes/db.php');

$user_id = $_SESSION['user_id'];

// Fetch user data
$user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM users WHERE id=$user_id"));

// Dashboard counts
$totalProjects = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM projects"))['total'];
$pendingTasks = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM tasks WHERE status='todo'"))['total'];
$pendingLeaves = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM leave_requests WHERE status='Pending'"))['total'];

// Handle profile update
$success = $error = '';
if (isset($_POST['update_profile'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);

    // Optional password change
    if (!empty($_POST['password'])) {
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $query = "UPDATE users SET name='$name', email='$email', phone='$phone', password='$password' WHERE id=$user_id";
    } else {
        $query = "UPDATE users SET name='$name', email='$email', phone='$phone' WHERE id=$user_id";
    }

    if (mysqli_query($conn, $query)) {
        $success = "Profile updated successfully!";
        $_SESSION['username'] = $name; // Update session username
        $user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM users WHERE id=$user_id")); // Refresh data
    } else {
        $error = "Failed to update profile. Please try again.";
    }
}
?>




<!DOCTYPE html>
<html>
<head>
    <title>My Profile</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body{margin:0;font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif;background:#f3f4f6;color:#111827;}
        
        /* Sidebar */
        .sidebar{position:fixed;left:0;top:0;height:100vh;width:70px;background:#111827;transition:width 0.3s;overflow:hidden;z-index:1000;}
        .sidebar:hover{width:220px;}
        .sidebar a{display:flex;align-items:center;color:#cbd5e1;text-decoration:none;padding:15px;gap:15px;border-radius:8px;margin:5px 8px;transition:0.2s;}
        .sidebar a i{font-size:20px;min-width:30px;text-align:center;}
        .sidebar a span{opacity:0;transition:opacity 0.3s;}
        .sidebar:hover a span{opacity:1;}
        .sidebar a:hover{background:#2563eb;color:#fff;}
        
        /* Main */
        .main{margin-left:70px;transition:margin-left 0.3s;}
        .sidebar:hover ~ .main{margin-left:220px;}
        .main-content{padding:30px;}
        
        /* Header */
        .header{display:flex;justify-content:space-between;align-items:center;background:#fff;padding:15px 25px;border-radius:12px;box-shadow:0 2px 10px rgba(0,0,0,0.05);margin-bottom:25px;}
        .header-left h3{margin:0;font-size:24px;font-weight:700;}
        .header-right{display:flex;align-items:center;gap:15px;font-weight:600;}
        
        /* Cards */
        .cards{display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:20px;margin-bottom:30px;}
        .card{background:#fff;border-radius:12px;padding:20px;display:flex;flex-direction:column;align-items:center;box-shadow:0 5px 15px rgba(0,0,0,0.08);transition:0.3s;}
        .card:hover{transform:translateY(-5px);box-shadow:0 8px 20px rgba(0,0,0,0.12);}
        .card h5{margin:0;font-size:14px;color:#6b7280;}
        .card span{font-size:24px;font-weight:700;margin-top:5px;}
        
        /* Form */
        .profile-form .form-control{border-radius:8px;}
        .profile-form button{border-radius:8px;}
        
        /* Table */
        .table-box{background:#fff;padding:20px;border-radius:12px;box-shadow:0 5px 15px rgba(0,0,0,0.08);margin-bottom:30px;}
        .table-box h5{margin-bottom:15px;}
        .table-hover tbody tr:hover{background:#e0f2fe;}
        .status-completed{color:#16a34a;font-weight:600;}
        .status-inprogress{color:#2563eb;font-weight:600;}
        .status-pending{color:#f59e0b;font-weight:600;}
    </style>
</head>
<body>

<div class="sidebar">
    <a href="dashboard.php"><i class="bi bi-speedometer2"></i><span>Dashboard</span></a>
    <a href="users.php"><i class="bi bi-people"></i><span>Users</span></a>
    <a href="projects.php"><i class="bi bi-folder"></i><span>Projects</span></a>
    <a href="tasks.php"><i class="bi bi-list-task"></i><span>Tasks</span></a>
    <a href="attendance.php"><i class="bi bi-calendar-check"></i><span>Attendance</span></a>
    <a href="documents.php"><i class="bi bi-file-earmark-text"></i><span>Documents</span></a>
    <a href="client_requests.php">
    <i class="bi bi-inbox"></i>
    <span>Client Requests</span>
</a>

    <a href="leave_approvals.php"><i class="bi bi-file-earmark-text"></i><span>Leave Approvals</span></a>
    <a href="reports.php"><i class="bi bi-bar-chart"></i><span>Reports</span></a>
    <a href="profile.php"><i class="bi bi-person-circle"></i><span>My Profile</span></a>
    <a href="../logout.php"><i class="bi bi-box-arrow-right"></i><span>Logout</span></a>
</div>


<div class="main">
    <div class="header">
        <div class="header-left"><h3>My Profile</h3></div>
        <div class="header-right"><?= $_SESSION['username'] ?></div>

    </div>

    <div class="main-content">

        <!-- Dashboard cards -->
        <div class="cards">
            <div class="card"><h5>Total Projects</h5><span><?= $totalProjects ?></span></div>
            <div class="card"><h5>Pending Tasks</h5><span><?= $pendingTasks ?></span></div>
            <div class="card"><h5>Pending Leaves</h5><span><?= $pendingLeaves ?></span></div>
        </div>

        <!-- Update Profile Form -->
        <div class="table-box">
            <h5>Update Profile</h5>
            <?php if($success): ?>
                <div class="alert alert-success"><?= $success ?></div>
            <?php elseif($error): ?>
                <div class="alert alert-danger"><?= $error ?></div>
            <?php endif; ?>
            <form method="post" class="profile-form">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label>Name</label>
                        <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($user['name']) ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label>Email</label>
                        <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label>Phone</label>
                        <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($user['phone']) ?>">
                    </div>
                    <div class="col-md-4">
                        <label>Change Password</label>
                        <input type="password" name="password" class="form-control" placeholder="Leave blank to keep current">
                    </div>
                    <div class="col-12">
                        <button type="submit" name="update_profile" class="btn btn-primary mt-2">Update Profile</button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Recent Projects Table -->
        <div class="table-box">
            <h5>Recent Projects</h5>
            <table class="table table-bordered table-hover">
                <thead><tr><th>Project</th><th>Status</th></tr></thead>
                <tbody>
                    <?php
                    $recentProjects = mysqli_query($conn,"SELECT * FROM projects ORDER BY id DESC LIMIT 5");
                    while($p = mysqli_fetch_assoc($recentProjects)){
                        $status_class = $p['status']=='Completed'?'status-completed':($p['status']=='In Progress'?'status-inprogress':'status-pending');
                        echo "<tr><td>".htmlspecialchars($p['project_name'])."</td><td class='$status_class'>".$p['status']."</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>

    </div>
</div>
</body>
</html>
