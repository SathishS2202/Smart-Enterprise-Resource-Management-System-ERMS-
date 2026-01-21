

<?php
require_once '../includes/middleware.php';
allowOnly('Admin');

include '../includes/db.php';

/* FETCH PENDING CLIENT PROJECTS */
$projects = mysqli_query($conn, "
    SELECT p.id, p.project_name, p.description, p.start_date, p.end_date, p.status, p.admin_verified, u.name AS client_name
    FROM projects p
    JOIN users u ON p.created_by = u.id
    ORDER BY p.created_at DESC
");

/* FETCH ALL AGENTS */
$agents = mysqli_query($conn, "SELECT id,name FROM users WHERE role_id = (SELECT id FROM roles WHERE role_name='Agent')");

/* APPROVE & ASSIGN */
if (isset($_POST['approve_project'])) {
    $project_id = (int)$_POST['project_id'];
    $agent_id = (int)$_POST['agent_id'];
    mysqli_query($conn,"UPDATE projects SET status='Active', agent_id=$agent_id WHERE id=$project_id");
    $_SESSION['msg'] = "Project approved & assigned successfully!";
    header("Location: client_requests.php"); exit;
}

/* ASSIGN PROJECT (without approving) */
if (isset($_POST['assign_project'])) {
    $project_id = (int)$_POST['project_id'];
    $agent_id = (int)$_POST['agent_id'];
    mysqli_query($conn,"UPDATE projects SET agent_id=$agent_id WHERE id=$project_id");
    $_SESSION['msg'] = "Project assigned to agent successfully!";
    header("Location: client_requests.php"); exit;
}

/* VERIFY COMPLETED PROJECT */
if (isset($_POST['verify_project'])) {
    $project_id = (int)$_POST['project_id'];
    mysqli_query($conn,"UPDATE projects SET admin_verified=1, status='Completed' WHERE id=$project_id");
    $_SESSION['msg'] = "Project verified & marked Completed for client!";
    header("Location: client_requests.php"); exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Client Requests</title>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
body{margin:0;font-family:Segoe UI;background:#f3f4f6}

/* SIDEBAR */
.sidebar{
    position:fixed;left:0;top:0;height:100vh;width:70px;background:#111827;transition:0.3s;overflow:hidden;z-index:1000;
}
.sidebar:hover{width:220px}
.sidebar a{
    display:flex;align-items:center;color:#cbd5e1;text-decoration:none;padding:15px;gap:15px;border-radius:8px;margin:5px 8px;transition:0.2s;
}
.sidebar a i{font-size:20px;min-width:30px;text-align:center;}
.sidebar a span{opacity:0;transition:opacity 0.3s;}
.sidebar:hover a span{opacity:1;}
.sidebar a:hover{background:#2563eb;color:#fff;}

/* MAIN */
.main{margin-left:70px;transition:.3s;padding:15px}
.sidebar:hover ~ .main{margin-left:230px}

/* TOP BAR */
.topbar{background:#fff;padding:12px 20px;display:flex;justify-content:space-between;align-items:center;border-bottom:1px solid #ddd}
.page-title{font-size:22px}

/* BOX */
.box{background:#fff;border-radius:6px;box-shadow:0 2px 6px rgba(0,0,0,.05)}
.box-header{display:flex;justify-content:space-between;align-items:center;padding:12px 15px;border-bottom:1px solid #e5e7eb}

/* ALERT MESSAGE */
.alert-msg{margin:10px 0;padding:10px;border-radius:5px;font-weight:600;}

/* SEARCH */
.search{padding:7px 10px;border:1px solid #ccc;border-radius:4px;width:220px}

/* TABLE */
table{width:100%;border-collapse:collapse;border:1px solid #d1d5db}
th,td{padding:12px;border:1px solid #e5e7eb;font-size:14px;text-align:center}
th{background:#f8fafc;font-weight:600}
tr:hover{background:#f1f5f9}

/* STATUS */
.ok{color:#065f46;background:#d1fae5;padding:4px 10px;border-radius:20px;font-size:12px;border:1px solid #6ee7b7}
.off{color:#7f1d1d;background:#fee2e2;padding:4px 10px;border-radius:20px;font-size:12px;border:1px solid #fca5a5}

/* ICON BUTTON */
.icon-btn{display:inline-flex;align-items:center;justify-content:center;border:1px solid #cbd5e1;background:#f9fafb;padding:6px 10px;border-radius:4px;cursor:pointer;color:#111827;text-decoration:none}
.icon-btn:hover{background:#e5e7eb}
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
    <a href="leave_approvals.php"><i class="bi bi-file-earmark-text"></i><span>Leave Approvals</span></a>
    <a href="reports.php"><i class="bi bi-bar-chart"></i><span>Reports</span></a>
    <a href="logout.php"><i class="bi bi-box-arrow-right"></i><span>Logout</span></a>
</div>

<!-- MAIN -->
<div class="main">

<!-- TOP BAR -->
<div class="topbar">
    <div class="page-title">Client Project Requests</div>
    <div><?= $_SESSION['user_name'] ?></div>
</div>

<br>

<!-- ALERT -->
<?php if(isset($_SESSION['msg'])): ?>
<div class="alert-msg" style="background:#d1fae5;color:#065f46;border:1px solid #6ee7b7;">
    <?= $_SESSION['msg'] ?>
</div>
<?php unset($_SESSION['msg']); endif; ?>

<!-- TABLE BOX -->
<div class="box">
<div class="box-header">
<strong>Requests</strong>
<div>
    <input type="text" id="searchInput" class="search" placeholder="Search...">
</div>
</div>

<table id="dataTable">
<thead>
<tr>
<th>#</th>
<th>Project Name</th>
<th>Client</th>
<th>Status</th>
<th>Action</th>
</tr>
</thead>
<tbody>
<?php $sl=1; while($p=mysqli_fetch_assoc($projects)): ?>
<tr>
<td><?= $sl++ ?></td>
<td><?= htmlspecialchars($p['project_name']) ?></td>
<td><?= htmlspecialchars($p['client_name']) ?></td>
<td>
<?php 
if($p['status']=='Pending') echo "<span class='off'>Pending</span>";
elseif($p['status']=='Active' && !$p['admin_verified']) echo "<span class='ok'>Active</span>";
else echo "<span class='ok'>Completed</span>";
?>
</td>
<td>
<?php if($p['status']=='Pending'): ?>
<!-- Approve & Assign -->
<form method="post" class="d-flex gap-2">
<input type="hidden" name="project_id" value="<?= $p['id'] ?>">
<select name="agent_id" class="form-select" required>
<option value="">Select Agent</option>
<?php foreach(mysqli_fetch_all($agents, MYSQLI_ASSOC) as $a): ?>
<option value="<?= $a['id'] ?>"><?= $a['name'] ?></option>
<?php endforeach; ?>
</select>
<button name="approve_project" class="btn btn-success btn-sm">Approve</button>
</form>

<!-- Assign Only (Modal) -->
<button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#assignModal<?= $p['id'] ?>">
Assign Agent
</button>
<div class="modal fade" id="assignModal<?= $p['id'] ?>" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="post">
        <div class="modal-header">
          <h5 class="modal-title">Assign Project: <?= htmlspecialchars($p['project_name']) ?></h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="project_id" value="<?= $p['id'] ?>">
          <select name="agent_id" class="form-select" required>
              <option value="">Select Agent</option>
              <?php foreach(mysqli_fetch_all($agents, MYSQLI_ASSOC) as $a): ?>
              <option value="<?= $a['id'] ?>"><?= $a['name'] ?></option>
              <?php endforeach; ?>
          </select>
        </div>
        <div class="modal-footer">
          <button type="submit" name="assign_project" class="btn btn-primary">Assign</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php elseif($p['status']=='Active' && !$p['admin_verified']): ?>
<form method="post">
<input type="hidden" name="project_id" value="<?= $p['id'] ?>">
<button name="verify_project" class="btn btn-primary btn-sm">Verify Completion</button>
</form>
<?php else: ?>
Completed & Verified
<?php endif; ?>
</td>
</tr>
<?php endwhile; ?>
</tbody>
</table>
</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
function searchTable() {
    let input = document.getElementById("searchInput").value.toLowerCase();
    document.querySelectorAll("#dataTable tbody tr").forEach(row=>{
        row.style.display = row.innerText.toLowerCase().includes(input) ? "" : "none";
    });
}
document.getElementById("searchInput").addEventListener('keyup', searchTable);
</script>

</body>
</html>
