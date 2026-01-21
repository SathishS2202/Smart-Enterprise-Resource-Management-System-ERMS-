<?php
require_once '../includes/middleware.php';
allowOnly('Admin');

include '../includes/db.php';
?>
<?php
// Dashboard counts
$totalUsers = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM users"))['total'];
$totalAgents = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM users WHERE role_id = 2"))['total'];
$totalProjects = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM projects"))['total'];
$pendingTasks = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM tasks WHERE status='todo'"))['total'];
$pendingLeaves = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM leave_requests WHERE status='Pending'"))['total'];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">

    <style>
        body{margin:0;font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif;background:#f3f4f6;color:#111827;}
        .sidebar{position:fixed;left:0;top:0;height:100vh;width:70px;background:#111827;transition:0.3s;overflow:hidden;z-index:1000;}
        .sidebar:hover{width:220px;}
        .sidebar a{display:flex;align-items:center;color:#cbd5e1;text-decoration:none;padding:15px;gap:15px;white-space:nowrap;border-radius:8px;margin:5px 8px;transition:0.2s;}
        .sidebar a i{font-size:20px;min-width:30px;text-align:center;}
        .sidebar a span{opacity:0;transition:opacity 0.3s;}
        .sidebar:hover a span{opacity:1;}
        .sidebar a:hover{background:#2563eb;color:#fff;}

        .main{margin-left:70px;transition:0.3s;}
        .sidebar:hover ~ .main{margin-left:220px;}
        .main-content{padding:30px;}

        .header{display:flex;justify-content:space-between;align-items:center;background:#fff;padding:15px 25px;border-radius:12px;box-shadow:0 2px 10px rgba(0,0,0,0.05);margin-bottom:25px;}
        .header-left h3{margin:0;font-size:24px;font-weight:700;}
        .header-left .role-switch{display:flex;align-items:center;gap:8px;background:#151515;padding:6px 12px;border-radius:8px;color:#fff;font-weight:600;}
        .header-left .role-switch select{background:transparent;border:none;color:#fff;outline:none;cursor:pointer;font-weight:600;}
        .header-left .role-switch select option{color:#000;}
        .header-right{display:flex;align-items:center;gap:15px;font-weight:600;}
        .header-right i{font-size:20px;cursor:pointer;transition:transform 0.2s;}
        .header-right i:hover{transform:scale(1.2);}

        .dashboard-icons{display:grid;grid-template-columns:repeat(4,1fr);gap:20px;margin-bottom:30px;}
        .icon-box{background:#fff;border-radius:12px;padding:25px 0;text-align:center;transition:0.3s;box-shadow:0 5px 15px rgba(0,0,0,0.08);text-decoration:none;color:#111827;}
        .icon-box i{font-size:40px;margin-bottom:12px;color:#2563eb;transition:0.3s;}
        .icon-box span{font-weight:600;display:block;}
        .icon-box:hover{background:#2563eb;color:#fff;transform:translateY(-5px);box-shadow:0 8px 20px rgba(0,0,0,0.12);}
        .icon-box:hover i{color:#fff;}

        .cards{display:grid;grid-template-columns:repeat(5,1fr);gap:15px;margin-bottom:30px;}
        .card{background:#fff;border-radius:12px;padding:20px;box-shadow:0 5px 15px rgba(0,0,0,0.08);display:flex;flex-direction:column;align-items:center;transition:0.2s;}
        .card:hover{transform:translateY(-5px);box-shadow:0 10px 25px rgba(0,0,0,0.12);}
        .card h5{margin:0;font-size:14px;color:#6b7280;}
        .card span{font-size:24px;font-weight:700;margin-top:5px;}

        .table-box{background:#fff;padding:15px;border-radius:12px;box-shadow:0 5px 15px rgba(0,0,0,0.08);margin-bottom:30px;}
        .table-box h5{margin-bottom:15px;}

        .chart-container{max-width:400px;margin:auto;}
        @media screen and (max-width:1200px){.dashboard-icons{grid-template-columns:repeat(2,1fr);}.cards{grid-template-columns:repeat(3,1fr);}}
        @media screen and (max-width:768px){.dashboard-icons{grid-template-columns:repeat(1,1fr);}.cards{grid-template-columns:repeat(2,1fr);}.chart-container{max-width:100%;}}
        @media screen and (max-width:480px){.cards{grid-template-columns:repeat(1,1fr);}}
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
    <div class="header">
        <div class="header-left">
            <h3>Admin Dashboard</h3>
            <div class="role-switch">
                <i class="bi bi-person-lines-fill"></i>
                <select onchange="location.href=this.value">
                    <option selected disabled>Switch Role</option>
                    <option value="switch_role.php?role=agent">Agent View</option>
                </select>
            </div>
        </div>
        <div class="header-right">
            <!-- <a href="switch_role.php?role=agent"><i class="bi bi-arrow-repeat"></i></a> -->
            <i class="bi bi-bell"></i>
            <?= $_SESSION['username'] ?? 'Admin'; ?>
        </div>
    </div>

    <div class="main-content">

        <!-- First row of icons -->
          <div class="cards">
            <div class="card"><h5>Total Users</h5><span><?= $totalUsers ?></span></div>
            <div class="card"><h5>Total Agents</h5><span><?= $totalAgents ?></span></div>
            <div class="card"><h5>Total Projects</h5><span><?= $totalProjects ?></span></div>
            <div class="card"><h5>Pending Tasks</h5><span><?= $pendingTasks ?></span></div>
            <div class="card"><h5>Pending Leaves</h5><span><?= $pendingLeaves ?></span></div>
        </div>
        <div class="dashboard-icons">
            <a href="users.php" class="icon-box"><i class="bi bi-people"></i><span>Users</span></a>
            <a href="projects.php" class="icon-box"><i class="bi bi-folder"></i><span>Projects</span></a>
            <a href="tasks.php" class="icon-box"><i class="bi bi-list-task"></i><span>Tasks</span></a>
            <a href="attendance.php" class="icon-box"><i class="bi bi-calendar-check"></i><span>Attendance</span></a>
        </div>

        <!-- Second row of icons -->
        <div class="dashboard-icons">
            <a href="documents.php" class="icon-box"><i class="bi bi-file-earmark-text"></i><span>Documents</span></a>
            <a href="leave_approvals.php" class="icon-box"><i class="bi bi-file-earmark-text"></i><span>Leave Approvals</span></a>
            <a href="reports.php" class="icon-box"><i class="bi bi-bar-chart"></i><span>Reports</span></a>
            <a href="notifications.php" class="icon-box"><i class="bi bi-bell"></i><span>Notifications</span></a>
        </div>

        <!-- Top Cards -->
       

        <!-- Pie Chart -->
        
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const ctx = document.getElementById('taskPieChart');
new Chart(ctx,{
    type:'pie',
    data:{
        labels:['To Do','In Progress','Done'],
        datasets:[{
            data:[
                <?= mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) FROM tasks WHERE status='To Do'"))['COUNT(*)'] ?>,
                <?= mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) FROM tasks WHERE status='In Progress'"))['COUNT(*)'] ?>,
                <?= mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) FROM tasks WHERE status='Done'"))['COUNT(*)'] ?>
            ],
            backgroundColor:['#f59e0b','#2563eb','#16a34a']
        }]
    },
    options:{plugins:{legend:{position:'bottom'},title:{display:false,text:''}}}
});
</script>

</body>
</html>
