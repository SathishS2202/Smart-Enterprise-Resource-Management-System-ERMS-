<?php
require_once '../includes/middleware.php';
allowOnly('Agent');

include '../includes/db.php';

if(!isset($_GET['id'])){
    header("Location: projects.php"); exit;
}

$project_id = (int)$_GET['id'];
$agent_id = $_SESSION['user_id'];

/* Fetch project details */
$project = mysqli_query($conn, "
    SELECT p.*, 
           u.name AS client_name,
           (SELECT COUNT(*) FROM tasks t WHERE t.project_id=p.id) AS total_tasks,
           (SELECT COUNT(*) FROM tasks t WHERE t.project_id=p.id AND t.status='Done') AS completed_tasks
    FROM projects p
    JOIN users u ON p.created_by=u.id
    WHERE p.id=$project_id AND p.agent_id=$agent_id
");

if(mysqli_num_rows($project)==0){
    $_SESSION['msg'] = "Project not found or not assigned to you.";
    header("Location: projects.php"); exit;
}

$p = mysqli_fetch_assoc($project);

/* Fetch tasks for this project */
$tasks = mysqli_query($conn, "SELECT * FROM tasks WHERE project_id=$project_id ORDER BY id ASC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Project Details</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
<style>
body{margin:0;font-family:'Segoe UI';background:#f3f4f6}

/* SIDEBAR */
.sidebar{
    position:fixed;left:0;top:0;height:100vh;width:70px;
    background:#111827;transition:.3s;overflow:hidden;z-index:1000;
}
.sidebar:hover{width:220px}
.sidebar a{
    display:flex;align-items:center;
    padding:15px;margin:5px 8px;
    gap:15px;color:#cbd5e1;text-decoration:none;border-radius:8px
}
.sidebar a:hover{background:#2563eb;color:#fff}
.sidebar i{font-size:20px;min-width:30px;text-align:center}
.sidebar span{opacity:0;transition:.3s}
.sidebar:hover span{opacity:1}

/* MAIN */
.main{margin-left:70px;padding:20px;transition:.3s}
.sidebar:hover ~ .main{margin-left:230px}

/* TOPBAR */
.topbar{
    background:#fff;padding:12px 20px;
    display:flex;justify-content:space-between;align-items:center;
    border-bottom:1px solid #ddd;margin-bottom:20px;
    border-radius:0 0 6px 6px;
}

/* CARDS */
.card{
    background:#fff;padding:20px;border-radius:8px;
    box-shadow:0 2px 8px rgba(0,0,0,.08);
    margin-bottom:20px;
}

/* BADGES */
.badge{padding:4px 10px;border-radius:20px;font-weight:600}
.bg-primary{background:#2563eb;color:#fff}
.bg-success{background:#16a34a;color:#fff}
.bg-warning{background:#fbbf24;color:#fff}

/* TABLE */
.table-container{overflow-x:auto;}
table{width:100%;border-collapse:collapse}
th,td{padding:12px;border:1px solid #e5e7eb;font-size:14px;text-align:left}
th{background:#f8fafc}
tr:hover{background:#f1f5f9}

/* PROGRESS BAR */
.progress{height:15px;border-radius:5px;margin-top:5px}

/* ICON BUTTON */
.icon-btn{
    display:inline-flex;align-items:center;justify-content:center;
    border:1px solid #cbd5e1;background:#f9fafb;
    padding:6px 10px;border-radius:4px;
    cursor:pointer;color:#111827;text-decoration:none
}
.icon-btn:hover{background:#e5e7eb}
</style>
</head>
<body>

<!-- SIDEBAR -->
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

<!-- MAIN -->
<div class="main">
<div class="topbar">
    <h4>Project Details</h4>
    <div><i class="bi bi-person"></i> <?= $_SESSION['user_name'] ?></div>
</div>

<!-- PROJECT INFO -->
<div class="card">
    <h5 class="mb-2"><?= htmlspecialchars($p['project_name']) ?></h5>
    <p><strong>Description:</strong> <?= nl2br(htmlspecialchars($p['description'])) ?></p>
    <p><strong>Client:</strong> <?= htmlspecialchars($p['client_name']) ?></p>
    <p><strong>Status:</strong> 
        <span class="badge <?= $p['status']=='Active'?'bg-primary':($p['status']=='Completed'?'bg-success':'bg-warning') ?>">
            <?= $p['status'] ?>
        </span>
    </p>
    <p><strong>Start Date:</strong> <?= $p['start_date'] ?> | <strong>End Date:</strong> <?= $p['end_date'] ?></p>
    <p><strong>Tasks Completed:</strong> <?= $p['completed_tasks'] ?>/<?= $p['total_tasks'] ?></p>
    <div class="progress mb-2">
        <div class="progress-bar bg-success" role="progressbar" style="width: <?= $p['total_tasks']>0?($p['completed_tasks']/$p['total_tasks']*100):0 ?>%"></div>
    </div>
</div>

<!-- TASK LIST -->
<div class="card table-container">
    <h5 class="mb-3">Tasks</h5>
    <table class="table table-hover align-middle">
        <thead>
            <tr>
                <th>#</th>
                <th>Task Name</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
        <?php $i=1; while($t=mysqli_fetch_assoc($tasks)): ?>
            <tr>
                <td><?= $i++ ?></td>
                <td><?= htmlspecialchars($t['task_name']) ?></td>
                <td>
                    <span class="badge <?= $t['status']=='Done'?'bg-success':'bg-warning' ?>">
                        <?= $t['status'] ?>
                    </span>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>

<a href="projects.php" class="btn btn-secondary mt-2"><i class="bi bi-arrow-left"></i> Back to Projects</a>
</div>

</body>
</html>
