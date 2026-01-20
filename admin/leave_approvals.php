<?php
include '../includes/auth_check.php';
checkRole('Admin');  // Only allow Admin
?>
<?php
session_start();
if(!isset($_SESSION['user_id']) || $_SESSION['role']!=='Admin'){
    header("Location: ../auth/login.php"); exit;
}
include '../includes/db.php';

// Handle approval/rejection
if(isset($_POST['action'])){
    $id = $_POST['id'];
    $status = $_POST['action'];
    $remark = mysqli_real_escape_string($conn,$_POST['remark']);

    mysqli_query($conn,"
        UPDATE leave_requests 
        SET status='$status', admin_remark='$remark', updated_at=NOW() 
        WHERE id=$id
    ");
}

// Fetch leave requests
$requests = mysqli_query($conn,"
    SELECT lr.*, u.name 
    FROM leave_requests lr 
    JOIN users u ON lr.user_id=u.id 
    ORDER BY lr.requested_at DESC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Leave Approvals</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
<style>
body{margin:0;font-family:'Segoe UI',sans-serif;background:#f4f6f9}
/* SIDEBAR */
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
/* MAIN */
.main{margin-left:70px;padding:20px; transition:.3s}
.sidebar:hover ~ .main{margin-left:230px}
/* TOPBAR */
.topbar{
    background:#fff; padding:12px 20px; display:flex; justify-content:space-between;
    border-bottom:1px solid #ddd; margin-bottom:20px; border-radius:0 0 8px 8px;
}
/* TABLE CARD */
.card{background:#fff; border-radius:12px; padding:20px; box-shadow:0 4px 15px rgba(0,0,0,.08)}
.table th, .table td{vertical-align:middle;}
.form-control{border-radius:6px;}
.btn-sm{border-radius:6px;}
</style>
</head>
<body>

<!-- SIDEBAR -->
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
<div class="topbar">
    <div class="page-title"><i class="bi bi-calendar-check me-2"></i>Leave Approvals</div>
    <div><i class="bi bi-person"></i> <?= $_SESSION['user_name'] ?></div>
</div>

<div class="container-fluid">

<!-- Filters -->
<div class="row mb-3">
<div class="col-md-4">
<form method="get" class="d-flex gap-2">
    <select name="status" class="form-select">
        <option value="">All Status</option>
        <option value="Pending" <?= (isset($_GET['status']) && $_GET['status']=='Pending')?'selected':'' ?>>Pending</option>
        <option value="Approved" <?= (isset($_GET['status']) && $_GET['status']=='Approved')?'selected':'' ?>>Approved</option>
        <option value="Rejected" <?= (isset($_GET['status']) && $_GET['status']=='Rejected')?'selected':'' ?>>Rejected</option>
    </select>
    <button class="btn btn-primary">Filter</button>
</form>
</div>
</div>

<!-- Leave Requests Table -->
<div class="card">
<table class="table table-bordered table-hover align-middle">
<thead class="table-light">
<tr>
<th>Agent</th>
<th>Type</th>
<th>Details</th>
<th>Reason</th>
<th>Status</th>
<th>Admin Remark / Action</th>
</tr>
</thead>
<tbody>
<?php
$filter = '';
if(isset($_GET['status']) && $_GET['status']!=''){
    $filter = "WHERE lr.status='".mysqli_real_escape_string($conn,$_GET['status'])."'";
    $requests = mysqli_query($conn,"
        SELECT lr.*, u.name 
        FROM leave_requests lr 
        JOIN users u ON lr.user_id=u.id 
        $filter 
        ORDER BY lr.requested_at DESC
    ");
}

while($r=mysqli_fetch_assoc($requests)):
?>
<tr>
<td><?= htmlspecialchars($r['name']) ?></td>
<td><?= $r['request_type'] ?></td>
<td>
<?php if($r['request_type']=='Leave'): ?>
<?= $r['from_date'] ?> → <?= $r['to_date'] ?>
<?php else: ?>
<?= $r['permission_date'] ?> (<?= $r['from_time'] ?> - <?= $r['to_time'] ?>)
<?php endif; ?>
</td>
<td><?= htmlspecialchars($r['reason']) ?></td>
<td><?= $r['status'] ?></td>
<td>
<?php if($r['status']=='Pending'): ?>
<form method="post" class="d-flex gap-1">
<input type="hidden" name="id" value="<?= $r['id'] ?>">
<input type="text" name="remark" placeholder="Remark" class="form-control" required>
<button name="action" value="Approved" class="btn btn-success btn-sm">Approve</button>
<button name="action" value="Rejected" class="btn btn-danger btn-sm">Reject</button>
</form>
<?php else: ?>
<span class="badge <?= $r['status']=='Approved'?'bg-success':'bg-danger' ?>"><?= $r['status'] ?></span>
<br>
<small><?= htmlspecialchars($r['admin_remark']) ?></small>
<?php endif; ?>
</td>
</tr>
<?php endwhile; ?>
</tbody>
</table>
</div>

</div>
</div>

</body>
</html>
