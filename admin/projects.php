
<?php
require_once '../includes/middleware.php';
allowOnly('Admin');
include '../includes/db.php';

/* FETCH PROJECTS */
$projects = mysqli_query($conn, "
    SELECT 
        p.id,
        p.project_name,
        p.status,
        p.admin_verified,
        c.name AS client_name,
        a.name AS agent_name
    FROM projects p
    LEFT JOIN users c ON p.created_by = c.id
    LEFT JOIN users a ON p.agent_id = a.id
    ORDER BY p.created_at DESC
");




/* FETCH AGENTS */
$agents = mysqli_query($conn,"
    SELECT id,name FROM users 
    WHERE role_id=(SELECT id FROM roles WHERE role_name='Agent')
");
$agents_arr = mysqli_fetch_all($agents, MYSQLI_ASSOC);

/* ASSIGN AGENT */
if(isset($_POST['assign_agent'])){
    $pid = (int)$_POST['project_id'];
    $aid = (int)$_POST['agent_id'];
    mysqli_query($conn,"UPDATE projects SET agent_id=$aid,status='Active' WHERE id=$pid");
    $_SESSION['msg']=['type'=>'success','text'=>'Project approved & assigned'];
    header("Location: projects.php"); exit;
}

/* VERIFY PROJECT */
if(isset($_POST['verify_project'])){
    $pid = (int)$_POST['project_id'];
    mysqli_query($conn,"UPDATE projects SET admin_verified=1 WHERE id=$pid");
    $_SESSION['msg']=['type'=>'success','text'=>'Project verified successfully'];
    header("Location: projects.php"); exit;
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Projects</title>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">

<style>
body{margin:0;font-family:Segoe UI;background:#f3f4f6}

/* SIDEBAR */
.sidebar{position:fixed;left:0;top:0;height:100vh;width:70px;background:#111827;transition:.3s;overflow:hidden}
.sidebar:hover{width:220px}
.sidebar a{display:flex;align-items:center;padding:15px;margin:5px 8px;gap:15px;color:#cbd5e1;text-decoration:none;border-radius:8px}
.sidebar a:hover{background:#2563eb;color:#fff}
.sidebar i{font-size:20px;min-width:30px;text-align:center}
.sidebar span{opacity:0}
.sidebar:hover span{opacity:1}

/* MAIN */
.main{margin-left:70px;padding:15px;transition:.3s}
.sidebar:hover ~ .main{margin-left:230px}

.topbar{
    background:#fff;
    padding:12px 20px;
    display:flex;
    justify-content:space-between;
    align-items:center;
    border-bottom:1px solid #ddd
}

/* BOX */
.box{background:#fff;border-radius:6px;box-shadow:0 2px 6px rgba(0,0,0,.05)}
.box-header{display:flex;justify-content:space-between;align-items:center;padding:12px 15px;border-bottom:1px solid #e5e7eb}

/* ALERT */
.alert-inline{margin:10px 15px;padding:10px;border-radius:5px;font-weight:500}
.success{background:#d1fae5;color:#065f46}

/* TABLE */
table{width:100%;border-collapse:collapse}
th,td{padding:12px;border:1px solid #e5e7eb;font-size:14px;text-align:center}
th{background:#f8fafc}
tr:hover{background:#f1f5f9}

/* BADGES */
.badge{padding:4px 10px;border-radius:20px;font-size:12px;font-weight:600}
.pending{background:#fef3c7;color:#92400e}
.active{background:#dbeafe;color:#1e40af}
.completed{background:#d1fae5;color:#065f46}
.verified{background:#065f46;color:#fff}

/* BUTTON */
.btn-small{padding:5px 8px;font-size:12px;border-radius:4px;border:none}
.assign{background:#2563eb;color:#fff}
.verify{background:#16a34a;color:#fff}
select{padding:5px}
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

<!-- MAIN -->
<div class="main">

<div class="topbar">
<h2>Projects</h2>
 <div><?= htmlspecialchars($_SESSION['username'] ?? 'Admin') ?></div>


</div>
<br>
<br>

<div class="box">
<div class="box-header">
<strong>Project List</strong>
</div>

<?php
if(isset($_SESSION['msg'])){
$m=$_SESSION['msg'];
echo "<div class='alert-inline {$m['type']}'>{$m['text']}</div>";
unset($_SESSION['msg']);
}
?>

<table>
<thead>
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

<?php $i=1; while($p=mysqli_fetch_assoc($projects)){ ?>
<tr>
<td><?= $i++ ?></td>
<td><?= htmlspecialchars($p['project_name']) ?></td>
<td><?= $p['client_name'] ?></td>
<td><?= $p['agent_name'] ?? 'Not Assigned' ?></td>

<td>
<?php
if($p['status']=='Pending') echo "<span class='badge pending'>Pending</span>";
elseif($p['status']=='Active') echo "<span class='badge active'>Active</span>";
elseif($p['status']=='Completed' && !$p['admin_verified']) echo "<span class='badge completed'>Completed</span>";
else echo "<span class='badge verified'>Verified</span>";
?>
</td>

<td>
<?php if($p['status']=='Pending'){ ?>
<form method="post" style="display:flex;gap:6px;justify-content:center">
<input type="hidden" name="project_id" value="<?= $p['id'] ?>">
<select name="agent_id" required>
<option value="">Assign</option>
<?php foreach($agents_arr as $a){ ?>
<option value="<?= $a['id'] ?>"><?= $a['name'] ?></option>
<?php } ?>
</select>
<button class="btn-small assign" name="assign_agent">Approve</button>
</form>

<?php } elseif($p['status']=='Completed' && !$p['admin_verified']){ ?>
<form method="post">
<input type="hidden" name="project_id" value="<?= $p['id'] ?>">
<button class="btn-small verify" name="verify_project">Verify</button>
</form>
<?php } else { echo "--"; } ?>
</td>

</tr>
<?php } ?>

</tbody>
</table>
</div>

</div>
</body>
</html>
