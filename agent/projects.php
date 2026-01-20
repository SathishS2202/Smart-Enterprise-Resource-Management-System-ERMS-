<?php
session_start();
if(!isset($_SESSION['user_id']) || $_SESSION['role']!=='Agent'){
    header("Location: ../auth/login.php"); exit;
}

include '../includes/db.php';
$agent_id = $_SESSION['user_id'];

/* Fetch all projects assigned to agent */
$projects = mysqli_query($conn, "
    SELECT p.*, 
           (SELECT COUNT(*) FROM tasks t WHERE t.project_id=p.id) AS total_tasks,
           (SELECT COUNT(*) FROM tasks t WHERE t.project_id=p.id AND t.status='Done') AS completed_tasks
    FROM projects p
    WHERE p.agent_id=$agent_id
    ORDER BY p.start_date DESC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>My Projects</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<style>
body{margin:0;font-family:Segoe UI;background:#f3f4f6}
.sidebar{position:fixed;left:0;top:0;height:100vh;width:70px;background:#111827;transition:.3s;overflow:hidden;z-index:1000}
.sidebar:hover{width:220px}
.sidebar a{display:flex;align-items:center;padding:15px;margin:5px 8px;gap:15px;color:#cbd5e1;text-decoration:none;border-radius:8px}
.sidebar a:hover{background:#2563eb;color:#fff}
.sidebar i{font-size:20px;min-width:30px;text-align:center}
.sidebar span{opacity:0;transition:.3s}
.sidebar:hover span{opacity:1}
.main{margin-left:70px;padding:20px;transition:.3s}
.sidebar:hover ~ .main{margin-left:230px}
.topbar{background:#fff;padding:12px 20px;display:flex;justify-content:space-between;align-items:center;border-bottom:1px solid #ddd;margin-bottom:20px}
.page-title{font-size:22px}
.card{background:#fff;padding:20px;border-radius:8px;box-shadow:0 2px 8px rgba(0,0,0,.08);margin-bottom:20px}
.table-container{overflow-x:auto;}
.progress{height:10px;border-radius:5px;margin-top:5px}
.icon-btn{display:inline-flex;align-items:center;justify-content:center;border:1px solid #cbd5e1;background:#f9fafb;padding:6px 10px;border-radius:4px;cursor:pointer;color:#111827;text-decoration:none}
.icon-btn:hover{background:#e5e7eb}
.search-box{padding:6px 10px;border-radius:4px;border:1px solid #ccc;width:200px;margin-right:10px;margin-bottom:10px}
.badge{padding:4px 8px;border-radius:5px;font-weight:600;}
.bg-primary{background:#2563eb;color:#fff}
.bg-success{background:#16a34a;color:#fff}
.bg-warning{background:#fbbf24;color:#fff}
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
    <div class="page-title">My Projects</div>
    <div><i class="bi bi-person"></i> <?= $_SESSION['user_name'] ?></div>
</div>

<div class="card table-container">
    <table class="table table-bordered table-hover align-middle" id="projects-table">
        <thead class="table-light">
        <tr>
            <th>#</th>
            <th>Project Name</th>
            <th>Description</th>
            <th>Status</th>
            <th>Tasks Completed</th>
            <th>Start Date</th>
            <th>End Date</th>
            <th>Actions</th>
        </tr>
        </thead>
        <tbody>
        <?php if(mysqli_num_rows($projects)>0):
            $i=1;
            while($p=mysqli_fetch_assoc($projects)): ?>
            <tr>
                <td><?= $i++ ?></td>
                <td><?= htmlspecialchars($p['project_name']) ?></td>
                <td><?= htmlspecialchars(substr($p['description'],0,50)) ?>...</td>
                <td>
                    <span class="badge <?= $p['status']=='Active'?'bg-primary':($p['status']=='Completed'?'bg-success':'bg-warning') ?>">
                        <?= $p['status'] ?>
                    </span>
                </td>
                <td>
                    <div class="progress">
                        <div class="progress-bar bg-success" role="progressbar" style="width: <?= $p['total_tasks']>0 ? ($p['completed_tasks']/$p['total_tasks']*100) : 0 ?>%" aria-valuenow="<?= $p['completed_tasks'] ?>" aria-valuemin="0" aria-valuemax="<?= $p['total_tasks'] ?>"></div>
                    </div>
                    <small><?= $p['completed_tasks'] ?>/<?= $p['total_tasks'] ?></small>
                </td>
                <td><?= $p['start_date'] ?></td>
                <td><?= $p['end_date'] ?></td>
                <td>
                    <a href="view_project.php?id=<?= $p['id'] ?>" class="icon-btn"><i class="fa fa-eye"></i></a>
                    <a href="tasks.php?project_id=<?= $p['id'] ?>" class="icon-btn"><i class="fa fa-tasks"></i></a>
                    <?php if($p['status']=='Active'): ?>
                        <a href="../includes/update_project_status.php?id=<?= $p['id'] ?>&status=Completed" class="btn btn-success btn-sm">Mark Completed</a>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endwhile; else: ?>
        <tr><td colspan="8" class="text-center text-muted">No projects assigned yet</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>
</div>

</body>
</html>
