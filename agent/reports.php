<?php
session_start();
if(!isset($_SESSION['user_id']) || $_SESSION['role']!=='Agent'){
    header("Location: ../auth/login.php"); exit;
}
include '../includes/db.php';

$agent_id = $_SESSION['user_id'];

// Get agent name
$agent_name = mysqli_fetch_assoc(mysqli_query($conn,"SELECT name FROM users WHERE id=$agent_id"))['name'];
$_SESSION['user_name'] = $agent_name;

/* ===== AGENT DATA ===== */

// TASKS
$task_counts = mysqli_fetch_assoc(mysqli_query($conn,"
    SELECT 
    SUM(status='To Do') AS todo,
    SUM(status='In Progress') AS progress,
    SUM(status='Done') AS done
    FROM tasks 
    WHERE assigned_to=$agent_id
"));

// ATTENDANCE
$attendance = mysqli_fetch_assoc(mysqli_query($conn,"
    SELECT 
    SUM(check_in IS NOT NULL) AS present,
    SUM(check_in IS NULL) AS absent
    FROM attendance 
    WHERE user_id=$agent_id
"));

// PROJECTS
$projects = mysqli_query($conn,"
    SELECT p.project_name, COUNT(t.id) total_tasks
    FROM projects p
    LEFT JOIN tasks t ON p.id=t.project_id
    WHERE p.agent_id=$agent_id
    GROUP BY p.id
");

// RECENT 7 DAYS TASKS (for weekly trend chart)
$task_week_labels = $task_week_data = [];
for($i=6;$i>=0;$i--){
    $day = date('Y-m-d', strtotime("-$i days"));
    $task_week_labels[] = date('d M', strtotime($day));
    $count = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) AS total FROM tasks WHERE assigned_to=$agent_id AND DATE(created_at)='$day'"))['total'];
    $task_week_data[] = $count;
}

// RECENT 7 DAYS ATTENDANCE (for weekly trend chart)
$attendance_week_labels = $attendance_week_data = [];
for($i=6;$i>=0;$i--){
    $day = date('Y-m-d', strtotime("-$i days"));
    $attendance_week_labels[] = date('d M', strtotime($day));
    $present = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) AS total FROM attendance WHERE user_id=$agent_id AND DATE(attendance_date)='$day' AND check_in IS NOT NULL"))['total'];
    $attendance_week_data[] = $present;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Reports - Agent</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

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

/* TOPBAR */
.topbar{background:#fff;padding:12px 20px;display:flex;justify-content:space-between;align-items:center;border-bottom:1px solid #ddd;margin-bottom:20px}
.page-title{font-size:22px;font-weight:600}

/* ROWS AND CARDS */
.row-charts{display:flex;gap:20px;margin-bottom:25px;flex-wrap:wrap}
.chart-card{flex:1;background:#fff;padding:15px;border-radius:12px;box-shadow:0 5px 15px rgba(0,0,0,0.05);min-width:250px}
.chart-card h6{text-align:center;margin-bottom:15px;font-weight:600;font-size:14px}

/* TABLE */
.card-table{background:#fff;padding:15px;border-radius:12px;box-shadow:0 5px 15px rgba(0,0,0,0.05)}
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
    <div class="page-title">Reports</div>
    <div><i class="bi bi-person"></i> <?= htmlspecialchars($_SESSION['user_name']) ?></div>
</div>

<!-- ROW 1: TASK STATUS -->
<h5 class="mb-2">Tasks</h5>
<div class="row-charts">
    <div class="chart-card"><h6>Status Distribution</h6><canvas id="taskStatusChart"></canvas></div>
    <div class="chart-card"><h6>Weekly Tasks</h6><canvas id="taskWeekChart"></canvas></div>
</div>

<!-- ROW 2: ATTENDANCE -->
<h5 class="mb-2">Attendance</h5>
<div class="row-charts">
    <div class="chart-card"><h6>Present vs Absent</h6><canvas id="attendanceChart"></canvas></div>
    <div class="chart-card"><h6>Weekly Attendance</h6><canvas id="attendanceWeekChart"></canvas></div>
</div>

<!-- ROW 3: PROJECTS -->
<h5 class="mb-2">Projects</h5>
<div class="card-table">
<table class="table table-bordered mt-3">
<thead class="table-light">
<tr><th>Project</th><th>Total Tasks</th></tr>
</thead>
<tbody>
<?php while($p=mysqli_fetch_assoc($projects)){ ?>
<tr>
<td><?= htmlspecialchars($p['project_name']) ?></td>
<td><?= $p['total_tasks'] ?></td>
</tr>
<?php } ?>
</tbody>
</table>
</div>
</div>

<script>
/* TASKS */
new Chart(document.getElementById('taskStatusChart'),{
    type:'doughnut',
    data:{labels:['To Do','In Progress','Done'],datasets:[{data:[<?= $task_counts['todo'] ?>,<?= $task_counts['progress'] ?>,<?= $task_counts['done'] ?>],backgroundColor:['#2563eb','#f59e0b','#16a34a']}]}
});
new Chart(document.getElementById('taskWeekChart'),{
    type:'line',
    data:{labels: <?= json_encode($task_week_labels) ?>, datasets:[{label:'Tasks Created',data: <?= json_encode($task_week_data) ?>, borderColor:'#2563eb',backgroundColor:'rgba(37,99,235,0.2)', fill:true, tension:0.4}]}
});

/* ATTENDANCE */
new Chart(document.getElementById('attendanceChart'),{
    type:'doughnut',
    data:{labels:['Present','Absent'],datasets:[{data:[<?= $attendance['present'] ?>,<?= $attendance['absent'] ?>],backgroundColor:['#16a34a','#ef4444']}]}
});
new Chart(document.getElementById('attendanceWeekChart'),{
    type:'line',
    data:{labels: <?= json_encode($attendance_week_labels) ?>, datasets:[{label:'Present', data: <?= json_encode($attendance_week_data) ?>, borderColor:'#16a34a', backgroundColor:'rgba(22,163,52,0.2)', fill:true, tension:0.4}]}
});
</script>

</body>
</html>
