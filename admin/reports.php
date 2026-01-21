
<?php
require_once '../includes/middleware.php';
allowOnly('Admin');


include '../includes/db.php';

/* ===== FETCH DATA FOR CHARTS ===== */

/* USERS */
$totalUsers = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM users"))['total'];
$rolesData = mysqli_query($conn, "SELECT roles.role_name, COUNT(*) AS count FROM users JOIN roles ON users.role_id = roles.id GROUP BY role_id");
$roleLabels = [];
$roleCounts = [];
while($r = mysqli_fetch_assoc($rolesData)){
    $roleLabels[] = $r['role_name'];
    $roleCounts[] = $r['count'];
}

/* Weekly users growth (last 7 days) */
$userGrowthData = [];
$userGrowthLabels = [];
for($i=6;$i>=0;$i--){
    $day = date('Y-m-d', strtotime("-$i days"));
    $userGrowthLabels[] = date('d M', strtotime($day));
    $count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM users WHERE DATE(created_at)='$day'"))['total'];
    $userGrowthData[] = $count;
}

/* PROJECTS */
$totalProjects = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM projects"))['total'];
$projectStatusData = [];
$statusLabels = ['Active','Completed','On Hold'];
foreach($statusLabels as $status){
    $count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM projects WHERE status='$status'"))['total'];
    $projectStatusData[] = $count;
}

/* Projects per agent */
$projectsAgentsData = mysqli_query($conn, "SELECT u.name, COUNT(p.id) AS total FROM projects p JOIN users u ON p.agent_id = u.id GROUP BY u.id");
$agentLabels = [];
$agentCounts = [];
while($pa = mysqli_fetch_assoc($projectsAgentsData)){
    $agentLabels[] = $pa['name'];
    $agentCounts[] = $pa['total'];
}

/* TASKS */
$totalTasks = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM tasks"))['total'];

/* Task status distribution */
$statusData = [];
$taskStatusLabels = ['To Do','In Progress','Done'];
foreach($taskStatusLabels as $ts){
    $count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM tasks WHERE status='$ts'"))['total'];
    $statusData[] = $count;
}

/* Task priority distribution */
$priorityData = [];
$priorityLabels = ['Low','Medium','High'];
foreach($priorityLabels as $p){
    $count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM tasks WHERE priority='$p'"))['total'];
    $priorityData[] = $count;
}

/* ATTENDANCE */
$attendancePresent = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM attendance WHERE check_in IS NOT NULL"))['total'];
$attendanceAbsent  = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM users"))['total'] - $attendancePresent;

/* Weekly attendance trends */
$attendanceWeekLabels = [];
$attendanceWeekData = [];
for($i=6;$i>=0;$i--){
    $day = date('Y-m-d', strtotime("-$i days"));
    $attendanceWeekLabels[] = date('d M', strtotime($day));
    $present = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM attendance WHERE DATE(attendance_date)='$day' AND check_in IS NOT NULL"))['total'];
    $attendanceWeekData[] = $present;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Reports - Admin</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
body{margin:0;font-family:Segoe UI;background:#f3f4f6}

/* SIDEBAR */
.sidebar{
    position:fixed;left:0;top:0;height:100vh;width:70px;
    background:#111827;transition:.3s;overflow:hidden
}
.sidebar:hover{width:220px}
.sidebar a{
    display:flex;align-items:center;
    padding:15px;margin:5px 8px;
    gap:15px;color:#cbd5e1;text-decoration:none;
    border-radius:8px
}
.sidebar a:hover{background:#2563eb;color:#fff}
.sidebar i{font-size:20px;min-width:30px;text-align:center}
.sidebar span{opacity:0}
.sidebar:hover span{opacity:1}

/* MAIN */
.main{margin-left:70px;padding:15px;transition:.3s}
.sidebar:hover ~ .main{margin-left:230px}

/* TOPBAR */
.topbar{
    background:#fff;
    padding:12px 20px;
    display:flex;
    justify-content:space-between;
    align-items:center;
    border-bottom:1px solid #ddd;
}
.page-title{font-size:22px}

/* ROWS AND CARDS */
.row-charts{display:flex;gap:20px;margin-bottom:25px;flex-wrap:wrap}
.chart-card{flex:1;background:#fff;padding:15px;border-radius:12px;box-shadow:0 5px 15px rgba(0,0,0,0.05);min-width:250px}
.chart-card h6{text-align:center;margin-bottom:15px;font-weight:600;font-size:14px}
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
    <div class="topbar mb-4">
        <div class="page-title">Reports</div>
        <div>
            <i class="bi bi-person"></i> <?= $_SESSION['username'] ?>
        </div>
    </div>

    <!-- ROW 1: USERS -->
    <h5 class="mb-2">Users (Total: <?= $totalUsers ?>)</h5>
    <div class="row-charts">
        <div class="chart-card"><h6>Role Distribution</h6><canvas id="userRolesChart"></canvas></div>
        <div class="chart-card"><h6>Weekly Growth</h6><canvas id="userGrowthChart"></canvas></div>
        <div class="chart-card"><h6>Users per Project</h6><canvas id="usersProjectsChart"></canvas></div>
    </div>

    <!-- ROW 2: PROJECTS -->
    <h5 class="mb-2">Projects (Total: <?= $totalProjects ?>)</h5>
    <div class="row-charts">
        <div class="chart-card"><h6>Status Distribution</h6><canvas id="projectStatusChart"></canvas></div>
        <div class="chart-card"><h6>Per Agent</h6><canvas id="projectAgentChart"></canvas></div>
        <div class="chart-card"><h6>Recent Creation</h6><canvas id="projectRecentChart"></canvas></div>
    </div>

    <!-- ROW 3: TASKS -->
    <h5 class="mb-2">Tasks (Total: <?= $totalTasks ?>)</h5>
    <div class="row-charts">
        <div class="chart-card"><h6>Status Distribution</h6><canvas id="taskStatusChart"></canvas></div>
        <div class="chart-card"><h6>Priority Distribution</h6><canvas id="taskPriorityChart"></canvas></div>
        <div class="chart-card"><h6>Recent Tasks</h6><canvas id="taskRecentChart"></canvas></div>
    </div>

    <!-- ROW 4: ATTENDANCE -->
    <h5 class="mb-2">Attendance</h5>
    <div class="row-charts">
        <div class="chart-card"><h6>Present vs Absent</h6><canvas id="attendancePieChart"></canvas></div>
        <div class="chart-card"><h6>Weekly Attendance</h6><canvas id="attendanceWeekChart"></canvas></div>
        <div class="chart-card"><h6>Recent Trends</h6><canvas id="attendanceRecentChart"></canvas></div>
    </div>
</div>

<script>
/* USERS */
const userRolesChart = new Chart(document.getElementById('userRolesChart'), {
    type:'doughnut',
    data:{labels: <?= json_encode($roleLabels) ?>, datasets:[{data: <?= json_encode($roleCounts) ?>,backgroundColor:['#2563eb','#10b981','#f59e0b','#ef4444']}]},
});
const userGrowthChart = new Chart(document.getElementById('userGrowthChart'),{
    type:'line',
    data:{labels: <?= json_encode($userGrowthLabels) ?>, datasets:[{label:'New Users', data: <?= json_encode($userGrowthData) ?>, borderColor:'#2563eb',backgroundColor:'rgba(37,99,235,0.2)', fill:true, tension:0.4}]},
});
const usersProjectsChart = new Chart(document.getElementById('usersProjectsChart'),{
    type:'bar',
    data:{labels: <?= json_encode($agentLabels) ?>, datasets:[{label:'Users per Agent', data: <?= json_encode($agentCounts) ?>, backgroundColor:'#10b981'}]},
});

/* PROJECTS */
const projectStatusChart = new Chart(document.getElementById('projectStatusChart'),{
    type:'doughnut',
    data:{labels: <?= json_encode($statusLabels) ?>, datasets:[{data: <?= json_encode($projectStatusData) ?>, backgroundColor:['#2563eb','#10b981','#f59e0b']}]}
});
const projectAgentChart = new Chart(document.getElementById('projectAgentChart'),{
    type:'bar',
    data:{labels: <?= json_encode($agentLabels) ?>, datasets:[{label:'Projects per Agent', data: <?= json_encode($agentCounts) ?>, backgroundColor:'#f59e0b'}]}
});
const projectRecentChart = new Chart(document.getElementById('projectRecentChart'),{
    type:'line',
    data:{labels: <?= json_encode($userGrowthLabels) ?>, datasets:[{label:'Projects Created', data: <?= json_encode($userGrowthData) ?>, borderColor:'#10b981', backgroundColor:'rgba(16,185,129,0.2)', fill:true, tension:0.4}]}
});

/* TASKS */
const taskStatusChart = new Chart(document.getElementById('taskStatusChart'),{
    type:'doughnut',
    data:{labels: <?= json_encode($taskStatusLabels) ?>, datasets:[{data: <?= json_encode($statusData) ?>, backgroundColor:['#2563eb','#10b981','#f59e0b']}]}
});
const taskPriorityChart = new Chart(document.getElementById('taskPriorityChart'),{
    type:'doughnut',
    data:{labels: <?= json_encode($priorityLabels) ?>, datasets:[{data: <?= json_encode($priorityData) ?>, backgroundColor:['#2563eb','#10b981','#f59e0b']}]}
});
const taskRecentChart = new Chart(document.getElementById('taskRecentChart'),{
    type:'line',
    data:{labels: <?= json_encode($userGrowthLabels) ?>, datasets:[{label:'Tasks Created', data: <?= json_encode($userGrowthData) ?>, borderColor:'#f59e0b',backgroundColor:'rgba(245,158,11,0.2)', fill:true, tension:0.4}]}
});

/* ATTENDANCE */
const attendancePieChart = new Chart(document.getElementById('attendancePieChart'),{
    type:'doughnut',
    data:{labels:['Present','Absent'], datasets:[{data:[<?= $attendancePresent ?>,<?= $attendanceAbsent ?>], backgroundColor:['#10b981','#ef4444']}]}
});
const attendanceWeekChart = new Chart(document.getElementById('attendanceWeekChart'),{
    type:'line',
    data:{labels: <?= json_encode($attendanceWeekLabels) ?>, datasets:[{label:'Present Users', data: <?= json_encode($attendanceWeekData) ?>, borderColor:'#2563eb',backgroundColor:'rgba(37,99,235,0.2)', fill:true, tension:0.4}]}
});
const attendanceRecentChart = new Chart(document.getElementById('attendanceRecentChart'),{
    type:'bar',
    data:{labels: <?= json_encode($attendanceWeekLabels) ?>, datasets:[{label:'Present Users', data: <?= json_encode($attendanceWeekData) ?>, backgroundColor:'#10b981'}]}
});
</script>

</body>
</html>
