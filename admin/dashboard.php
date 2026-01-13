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
        body{margin:0;font-family:Segoe UI;background:#f3f4f6}

        /* ===== SIDEBAR ===== */
        .sidebar {
            position: fixed;
            width: 70px;
            height: 100vh;
            background: #111827;
            padding-top: 20px;
            transition: 0.3s;
            overflow: hidden;
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
        }

        .sidebar a i {
            font-size: 20px;
            min-width: 30px;
            text-align: center;
        }

        .sidebar a span {
            opacity: 0;
            transition: 0.2s;
        }

        .sidebar:hover a span {
            opacity: 1;
        }

        .sidebar a:hover {
            background: #2563eb;
            color: #fff;
        }

        /* ===== MAIN ===== */
        .main {
            margin-left: 70px;
            transition: 0.3s;
        }
        .main-content {
    padding: 30px;
    background: #fff;
    border-radius: 15px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.05);
    margin: 20px; /* spacing from main edges */
}


        .sidebar:hover ~ .main {
            margin-left: 220px;
        }
        .top-icon {
    margin-bottom: 40px;
    display: flex;
    justify-content: center;
}

        /* ===== HEADER ===== */.header {
    background: #fff;
    padding: 15px 25px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    box-shadow: 0 1px 5px rgba(0,0,0,0.1);
}

/* Left side: switch + title */
.header-left {
    display: flex;
    align-items: center;
    gap: 20px;
}

.switch-agent {
    display: flex;
    align-items: center;
    gap: 8px;
    background: #151515; /* green */
    color: #fff;
    padding: 8px 12px;
    border-radius: 8px;
    font-weight: 600;
    text-decoration: none;
    transition: background 0.2s, transform 0.2s;
}

.switch-agent i {
    font-size: 18px;
}

.switch-agent:hover {
    background: #059669;
    transform: translateY(-2px);
}

/* Right side: notifications & username */
.header-right {
    display: flex;
    align-items: center;
    gap: 10px;
}


        /* ===== DASHBOARD CARDS ===== */
        .cards {
            padding: 25px;
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
        }

        .card {
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            display: flex;
            justify-content: space-between;
            align-items: center;
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
            font-size: 35px;
            color: #2563eb;
        }

        /* ===== TABLE ===== */
        .table-box {
            margin: 0 25px 25px;
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        }

        .dashboard-icons {
    display: grid;
    grid-template-columns: repeat(3, 1fr); /* 3 columns */
    gap: 30px;
    padding: 30px;
}

.icon-box {
    background: #fff;
    border-radius: 15px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    text-align: center;
    padding: 30px 0;
    color: #111827;
    text-decoration: none;
    transition: transform 0.2s, background 0.2s;
}

.icon-box i {
    font-size: 40px;
    color: #455353;
    margin-bottom: 10px;
}

.icon-box span {
    display: block;
    font-weight: 600;
}

.icon-box:hover {
    background: #bee0f6;
    color: #fff;
    transform: translateY(-5px);
}

.icon-box:hover i {
    color: #fff;
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
        </div>
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
