<?php
// ===== AUTH & ROLE CHECK =====
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../login.php");
    exit;
}

// ===== DB CONNECTION =====
include('../includes/db.php');

// ===== DASHBOARD COUNTS =====
$totalUsers = mysqli_fetch_assoc(
    mysqli_query($conn, "SELECT COUNT(*) AS total FROM users")
)['total'];

$totalAgents = mysqli_fetch_assoc(
    mysqli_query($conn, "SELECT COUNT(*) AS total FROM users WHERE role_id = 2")
)['total'];

$totalProjects = mysqli_fetch_assoc(
    mysqli_query($conn, "SELECT COUNT(*) AS total FROM projects")
)['total'];

$pendingTasks = mysqli_fetch_assoc(
    mysqli_query($conn, "SELECT COUNT(*) AS total FROM tasks WHERE status = 'todo'")
)['total'];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">

    <style>
/* ===== GENERAL ===== */
body {
    margin: 0;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background: #f3f4f6;
    color: #111827;
}

/* ===== SIDEBAR ===== */
.sidebar {
    position: fixed;
    left: 0;
    top: 0;
    height: 100vh;
    width: 70px;
    background: #111827;
    transition: width 0.3s;
    overflow: hidden;
    z-index: 1000;
}
.sidebar:hover {
    width: 220px;
}
.sidebar a {
    display: flex;
    align-items: center;
    color: #cbd5e1;
    text-decoration: none;
    padding: 15px;
    gap: 15px;
    white-space: nowrap;
    transition: 0.2s;
    border-radius: 8px;
    margin: 5px 8px;
}
.sidebar a i {
    font-size: 20px;
    min-width: 30px;
    text-align: center;
}
.sidebar a span {
    opacity: 0;
    transition: opacity 0.3s;
}
.sidebar:hover a span { opacity: 1; }
.sidebar a:hover { background: #2563eb; color: #fff; }

/* ===== MAIN AREA ===== */
.main {
    margin-left: 70px;
    transition: margin-left 0.3s;
}
.sidebar:hover ~ .main {
    margin-left: 220px;
}
.main-content {
    padding: 30px;
}

/* ===== HEADER ===== */
.header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: #fff;
    padding: 15px 25px;
    border-radius: 12px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    margin-bottom: 25px;
}
.header-left h3 {
    margin: 0;
    font-size: 24px;
    font-weight: 700;
}
.header-left .role-switch {
    display: flex;
    align-items: center;
    gap: 8px;
    background: #151515;
    padding: 6px 12px;
    border-radius: 8px;
    color: #fff;
    font-weight: 600;
}
.header-left .role-switch select {
    background: transparent;
    border: none;
    color: #fff;
    font-weight: 600;
    outline: none;
    cursor: pointer;
}
.header-left .role-switch select option { color: #000; }

.header-right {
    display: flex;
    align-items: center;
    gap: 15px;
    font-weight: 600;
}
.header-right i {
    font-size: 20px;
    cursor: pointer;
    transition: transform 0.2s;
}
.header-right i:hover { transform: scale(1.2); }

/* ===== DASHBOARD ICON BOXES ===== */
/* ===== DASHBOARD ICON BOXES - 2 ROWS ===== */
.dashboard-icons {
    display: grid;
    grid-template-columns: repeat(3, 1fr); /* 3 icons per row */
    grid-template-rows: repeat(2, 1fr);    /* 2 rows */
    gap: 25px;
    padding: 0;
}
.icon-box {
    background: #fff;
    border-radius: 12px;
    padding: 30px 0;
    text-align: center;
    transition: 0.3s;
    box-shadow: 0 5px 15px rgba(0,0,0,0.08);
    text-decoration: none;
    color: #111827;
}
.icon-box i {
    font-size: 40px;
    margin-bottom: 12px;
    color: #2563eb;
    transition: 0.3s;
}
.icon-box span {
    font-weight: 600;
    display: block;
}
.icon-box:hover {
    background: #2563eb;
    color: #fff;
    transform: translateY(-5px);
    box-shadow: 0 8px 20px rgba(0,0,0,0.12);
}
.icon-box:hover i { color: #fff; }


/* ===== CARDS ===== */
.cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}
.card {
    background: #fff;
    border-radius: 12px;
    padding: 20px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.08);
    display: flex;
    justify-content: space-between;
    align-items: center;
    transition: transform 0.2s, box-shadow 0.2s;
}
.card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.12);
}
.card h2 {
    margin: 0;
    font-size: 28px;
}
.card p {
    margin: 0;
    color: #6b7280;
}
.card i {
    font-size: 36px;
    color: #2563eb;
}

/* ===== TABLE BOX ===== */
.table-box {
    background: #fff;
    padding: 20px;
    border-radius: 12px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.08);
}

/* ===== RESPONSIVE ===== */
@media screen and (max-width: 992px) {
    .header-left h3 { font-size: 20px; }
    .dashboard-icons { grid-template-columns: repeat(auto-fit, minmax(140px, 3fr)); }
    .cards { grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); }
}
@media screen and (max-width: 576px) {
    .main { margin-left: 0; padding: 15px; }
    .sidebar { width: 60px; }
    .sidebar:hover { width: 180px; }
}
</style>

</head>
<body>

<!-- ===== ADMIN SIDEBAR ===== -->
<div class="sidebar">
    <a href="dashboard.php"><i class="bi bi-speedometer2"></i><span>Dashboard</span></a>
    <a href="users.php"><i class="bi bi-people"></i><span>Users</span></a>
    <a href="projects.php"><i class="bi bi-folder"></i><span>Projects</span></a>
    <a href="tasks.php"><i class="bi bi-list-task"></i><span>Tasks</span></a>
    <a href="attendance.php"><i class="bi bi-calendar-check"></i><span>Attendance</span></a>
    <a href="documents.php"><i class="bi bi-file-earmark-text"></i><span>Documents</span></a>
    <a href="reports.php"><i class="bi bi-bar-chart"></i><span>Reports</span></a>
    <a href="../logout.php"><i class="bi bi-box-arrow-right"></i><span>Logout</span></a>
</div>

<!-- ===== MAIN CONTENT ===== -->
<!-- ===== MAIN CONTENT ===== -->
<div class="main">

   <div class="header">
        <div class="header-left">
    <h3>Admin Dashboard</h3>

    <div class="role-switch">
        <i class="bi bi-person-lines-fill"></i>
        <select onchange="location.href=this.value">
            <option selected disabled>Switch Role</option>
            <option value="switch_role.php?role=agent">Agent View</option>
            <option value="switch_role.php?role=client">Client View</option>
        </select>
    </div>
</div>
>
        <div class="header-right">
            <a href="switch_agent.php" class="switch-agent">
                <i class="bi bi-arrow-repeat"></i>
            </a>
            <i class="bi bi-bell"></i>
            <?php echo $_SESSION['username'] ?? 'Admin'; ?>
        </div>
    </div>

    <!-- CONTENT CONTAINER -->
    <div class="main-content">
        <!-- DASHBOARD ICONS -->
        <div class="dashboard-icons">
            <a href="users.php" class="icon-box">
                <i class="bi bi-people"></i>
                <span>Users</span>
            </a>
            <a href="projects.php" class="icon-box">
                <i class="bi bi-folder"></i>
                <span>Projects</span>
            </a>
            <a href="tasks.php" class="icon-box">
                <i class="bi bi-list-task"></i>
                <span>Tasks</span>
            </a>
            <a href="attendance.php" class="icon-box">
                <i class="bi bi-calendar-check"></i>
                <span>Attendance</span>
            </a>
            <a href="documents.php" class="icon-box">
                <i class="bi bi-file-earmark-text"></i>
                <span>Documents</span>
            </a>
            <a href="reports.php" class="icon-box">
                <i class="bi bi-bar-chart"></i>
                <span>Reports</span>
            </a>
        </div>
    </div>

</div>


</body>
</html>
