<?php
session_start();
if(!isset($_SESSION['user_id']) || $_SESSION['role']!=='Agent'){
    header("Location: ../auth/login.php"); exit;
}

include '../includes/db.php';
$agent_id = $_SESSION['user_id'];

// Fetch agent name (ensure topbar shows correctly)
$result = mysqli_query($conn,"SELECT name FROM users WHERE id=$agent_id");
$agent = mysqli_fetch_assoc($result);
$_SESSION['user_name'] = $agent['name'];

// KEY STATS
$total_projects = mysqli_num_rows(mysqli_query($conn,"SELECT id FROM projects WHERE agent_id=$agent_id"));
$task_counts = mysqli_fetch_assoc(mysqli_query($conn,"SELECT 
    SUM(status='To Do') AS todo, 
    SUM(status='In Progress') AS in_progress, 
    SUM(status='Done') AS done 
    FROM tasks WHERE assigned_to=$agent_id"));
$attendance_counts = mysqli_fetch_assoc(mysqli_query($conn,"SELECT 
    SUM(CASE WHEN check_in IS NOT NULL THEN 1 ELSE 0 END) AS present,
    SUM(CASE WHEN check_in IS NULL THEN 1 ELSE 0 END) AS absent
    FROM attendance WHERE user_id=$agent_id"));

// RECENT TASKS
$recent_tasks = mysqli_query($conn,"SELECT t.*, p.project_name 
    FROM tasks t 
    JOIN projects p ON t.project_id=p.id 
    WHERE t.assigned_to=$agent_id 
    ORDER BY t.id DESC LIMIT 5");

// RECENT PROJECTS
$recent_projects = mysqli_query($conn,"SELECT * FROM projects WHERE agent_id=$agent_id ORDER BY id DESC LIMIT 5");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Agent Dashboard</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
body{margin:0;font-family:Segoe UI;background:#f3f4f6;}
.sidebar{position:fixed;left:0;top:0;height:100vh;width:70px;background:#111827;transition:.3s;overflow:hidden;z-index:1000}
.sidebar:hover{width:220px}
.sidebar a{display:flex;align-items:center;padding:15px;margin:5px 8px;gap:15px;color:#cbd5e1;text-decoration:none;border-radius:8px}
.sidebar a:hover{background:#2563eb;color:#fff}
.sidebar i{font-size:20px;min-width:30px;text-align:center}
.sidebar span{opacity:0;transition:.3s}
.sidebar:hover span{opacity:1}
.main{margin-left:70px;padding:20px;transition:.1s}
.sidebar:hover ~ .main{margin-left:230px}
.topbar{background:#fff;padding:12px 20px;display:flex;justify-content:space-between;align-items:center;border-bottom:1px solid #ddd;margin-bottom:20px}
.page-title{font-size:22px;font-weight:600;}
.card{background:#fff;padding:20px;border-radius:8px;box-shadow:0 2px 8px rgba(0,0,0,.08);margin-bottom:20px;text-align:center}
.card h5{margin-bottom:10px;color:#374151;font-weight:600;}
.card span{font-size:24px;font-weight:600;color:#111827}
.table-container{overflow-x:auto;}
.progress{height:10px;border-radius:5px;margin-top:5px}
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
    <div class="page-title">Agent Dashboard</div>
    <div><i class="bi bi-person"></i> <?= htmlspecialchars($_SESSION['user_name']) ?></div>
</div>

<!-- KEY STATS -->
<div class="row g-3 mb-3">
    <div class="col-md-3"><div class="card"><h5>Total Projects</h5><span><?= $total_projects ?></span></div></div>
    <div class="col-md-3"><div class="card"><h5>Tasks To Do</h5><span><?= $task_counts['todo'] ?? 0 ?></span></div></div>
    <div class="col-md-3"><div class="card"><h5>Tasks In Progress</h5><span><?= $task_counts['in_progress'] ?? 0 ?></span></div></div>
    <div class="col-md-3"><div class="card"><h5>Tasks Done</h5><span><?= $task_counts['done'] ?? 0 ?></span></div></div>
</div>

<!-- CHARTS -->
<div class="row g-3 mb-3">
    <div class="col-md-4"><canvas id="taskPie"></canvas></div>
    <div class="col-md-4"><canvas id="projectBar"></canvas></div>
    <div class="col-md-4"><canvas id="attendanceLine"></canvas></div>
</div>

<!-- RECENT TASKS & PROJECTS -->
<div class="row g-3">
    <div class="col-md-6">
        <div class="card table-container">
            <h5>Recent Tasks</h5>
            <table class="table table-bordered table-hover align-middle">
                <thead class="table-light"><tr><th>Title</th><th>Project</th><th>Status</th></tr></thead>
                <tbody>
                <?php while($t=mysqli_fetch_assoc($recent_tasks)): ?>
                    <tr>
                        <td><?= htmlspecialchars($t['title']) ?></td>
                        <td><?= htmlspecialchars($t['project_name']) ?></td>
                        <td><?= $t['status'] ?></td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card table-container">
            <h5>Recent Projects</h5>
            <table class="table table-bordered table-hover align-middle">
                <thead class="table-light"><tr><th>Project Name</th><th>Status</th><th>Start</th></tr></thead>
                <tbody>
                <?php while($p=mysqli_fetch_assoc($recent_projects)): ?>
                    <tr>
                        <td><?= htmlspecialchars($p['project_name']) ?></td>
                        <td><?= $p['status'] ?></td>
                        <td><?= $p['start_date'] ?></td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- CHARTS SCRIPT -->
<script>
new Chart(document.getElementById('taskPie'), {
    type:'pie',
    data:{
        labels:['To Do','In Progress','Done'],
        datasets:[{data:[<?= $task_counts['todo'] ?? 0 ?>,<?= $task_counts['in_progress'] ?? 0 ?>,<?= $task_counts['done'] ?? 0 ?>],backgroundColor:['#2563eb','#f59e0b','#16a34a'] }]
    },
    options:{plugins:{title:{display:true,text:'Task Status'}}}
});

new Chart(document.getElementById('projectBar'), {
    type:'bar',
    data:{
        labels:['Projects'],
        datasets:[{label:'Total Projects',data:[<?= $total_projects ?>],backgroundColor:'#2563eb'}]
    },
    options:{plugins:{title:{display:true,text:'Projects Overview'}},responsive:true}
});

new Chart(document.getElementById('attendanceLine'), {
    type:'line',
    data:{
        labels:['Present','Absent'],
        datasets:[{label:'Attendance',data:[<?= $attendance_counts['present'] ?? 0 ?>,<?= $attendance_counts['absent'] ?? 0 ?>],backgroundColor:'rgba(37,99,235,0.2)',borderColor:'#2563eb',fill:true}]
    },
    options:{plugins:{title:{display:true,text:'Attendance Overview'}},responsive:true}
});
</script>

</div>
</body>
</html>
