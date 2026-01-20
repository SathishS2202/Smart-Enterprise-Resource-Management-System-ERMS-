<?php
include '../includes/auth_check.php';
checkRole('Admin');  // Only allow Admin
?>

<?php 
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../auth/login.php"); exit;
}
include '../includes/db.php';

/* FETCH PROJECTS WITH CLIENT AND AGENT */
$projects = mysqli_query($conn, "
    SELECT 
        p.id,
        p.project_name,
        p.status,
        p.admin_verified,
        p.start_date,
        p.end_date,
        c.name AS client_name,
        a.name AS agent_name
    FROM projects p
    LEFT JOIN users c ON p.created_by = c.id
    LEFT JOIN users a ON p.agent_id = a.id
    ORDER BY p.created_at DESC
");

/* FETCH ALL AGENTS FOR ASSIGNMENT */
$agents = mysqli_query($conn, "SELECT id,name FROM users WHERE role_id=(SELECT id FROM roles WHERE role_name='Agent')");
$agents_arr = mysqli_fetch_all($agents, MYSQLI_ASSOC); // Fetch once outside the loop

/* ASSIGN PROJECT TO AGENT */
if (isset($_POST['assign_agent'])) {
    $project_id = (int)$_POST['project_id'];
    $agent_id = (int)$_POST['agent_id'];
    mysqli_query($conn,"UPDATE projects SET agent_id=$agent_id, status='Active' WHERE id=$project_id");
    $_SESSION['msg'] = "Project approved & assigned to agent successfully!";
    header("Location: projects.php"); exit;
}

/* VERIFY PROJECT COMPLETION */
if (isset($_POST['verify_project'])) {
    $project_id = (int)$_POST['project_id'];
    mysqli_query($conn,"UPDATE projects SET admin_verified=1 WHERE id=$project_id");
    $_SESSION['msg'] = "Project verified & marked completed for client!";
    header("Location: projects.php"); exit;
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Admin Projects</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
<style>
body{font-family:'Segoe UI';background:#f3f4f6;margin:0;}
.sidebar{position:fixed;left:0;top:0;width:70px;height:100vh;background:#111827;transition:.3s;overflow:hidden;}
.sidebar:hover{width:220px;}
.sidebar a{display:flex;align-items:center;padding:15px;margin:5px 8px;gap:15px;color:#cbd5e1;text-decoration:none;border-radius:8px;}
.sidebar:hover a span{opacity:1;}
.sidebar a:hover{background:#2563eb;color:#fff;}
.sidebar i{min-width:30px;text-align:center;font-size:20px;}
.sidebar span{opacity:0;transition:.3s;}
.main{margin-left:70px;padding:15px;transition:.3s;}
.sidebar:hover ~ .main{margin-left:230px;}
.badge{padding:4px 8px;border-radius:5px;font-weight:600;}
.pending{background:#fbbf24;color:#fff;} /* yellow */
.active{background:#2563eb;color:#fff;} /* blue */
.completed{background:#16a34a;color:#fff;} /* green */
.verify{background:#065f46;color:#fff;} /* dark green */
.form-select{height:32px;font-size:14px;}
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
    <a href="logout.php"><i class="bi bi-box-arrow-right"></i><span>Logout</span></a>
</div>

<div class="main">
<h3>All Projects</h3>

<?php if(isset($_SESSION['msg'])){ echo "<div class='alert alert-success'>".$_SESSION['msg']."</div>"; unset($_SESSION['msg']); } ?>

<table class="table table-bordered table-hover">
<thead class="table-dark">
<tr>
<th>#</th>
<th>Project</th>
<th>Client</th>
<th>Agent</th>
<th>Status</th>
<th>Actions</th>
</tr>
</thead>
<tbody>
<?php $sl=1; while($p=mysqli_fetch_assoc($projects)): ?>
<tr>
<td><?= $sl++ ?></td>
<td><?= htmlspecialchars($p['project_name']) ?></td>
<td><?= htmlspecialchars($p['client_name']) ?></td>
<td><?= $p['agent_name'] ?? 'Not Assigned' ?></td>
<td>
<?php
if($p['status']=='Pending') echo "<span class='badge pending'>Pending</span>";
elseif($p['status']=='Active') echo "<span class='badge active'>Active</span>";
elseif($p['status']=='Completed' && !$p['admin_verified']) echo "<span class='badge completed'>Completed (Agent)</span>";
elseif($p['status']=='Completed' && $p['admin_verified']) echo "<span class='badge verify'>Completed & Verified</span>";
?>
</td>
<td>
<?php if($p['status']=='Pending'): ?>
<form method="post" class="d-flex gap-2">
<input type="hidden" name="project_id" value="<?= $p['id'] ?>">
<select name="agent_id" class="form-select" required>
<option value="">Assign Agent</option>
<?php foreach($agents_arr as $a): ?>
<option value="<?= $a['id'] ?>"><?= $a['name'] ?></option>
<?php endforeach; ?>
</select>
<button name="assign_agent" class="btn btn-success btn-sm">Approve & Assign</button>
</form>

<?php elseif($p['status']=='Completed' && !$p['admin_verified']): ?>
<form method="post">
<input type="hidden" name="project_id" value="<?= $p['id'] ?>">
<button name="verify_project" class="btn btn-primary btn-sm">Verify & Complete</button>
</form>
<?php else: ?>
<i>--</i>
<?php endif; ?>
</td>
</tr>
<?php endwhile; ?>
</tbody>
</table>
</div>
</body>
</html>
