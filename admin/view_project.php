<?php
include '../includes/auth_check.php';
checkRole('Admin');  // Only allow Admin
?>


<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../auth/login.php");
    exit;
}

include '../includes/db.php';

// Get project ID from URL
$project_id = intval($_GET['id'] ?? 0);
if ($project_id <= 0) {
    die("Invalid Project ID.");
}

// Fetch project details with assigned agent and creator
$result = mysqli_query($conn, "
    SELECT 
        p.*,
        creator.name AS created_by_name,
        agent.name AS agent_name
    FROM projects p
    LEFT JOIN users creator ON p.created_by = creator.id
    LEFT JOIN users agent ON p.agent_id = agent.id
    WHERE p.id = $project_id
    LIMIT 1
");

if(mysqli_num_rows($result) == 0){
    die("Project not found.");
}

$project = mysqli_fetch_assoc($result);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>View Project</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
<style>
body{margin:0;font-family:Segoe UI;background:#f3f4f6}

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

/* MAIN */
.main{margin-left:60px;transition:.3s;padding:15px}
.sidebar:hover ~ .main{margin-left:230px}

/* TOP BAR */
.topbar{background:#fff;padding:12px 20px;display:flex;justify-content:space-between;align-items:center;border-bottom:1px solid #ddd}
.page-title{font-size:22px}

/* CARD */
.card{background:#fff;padding:20px;border-radius:8px;box-shadow:0 2px 8px rgba(0,0,0,.08);margin-bottom:20px}
.card h3{margin-top:0}
.label{font-weight:600;color:#374151;margin-right:8px}
.value{color:#111827}
.ok{color:#065f46;background:#d1fae5;padding:4px 10px;border-radius:20px;font-size:12px;border:1px solid #6ee7b7}
.off{color:#7f1d1d;background:#fee2e2;padding:4px 10px;border-radius:20px;font-size:12px;border:1px solid #fca5a5}
.back-btn{display:inline-flex;align-items:center;gap:6px;background:#2563eb;color:#fff;padding:8px 12px;border-radius:6px;text-decoration:none}
.back-btn:hover{background:#1e40af}
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

<!-- TOP BAR -->
<div class="topbar">
    <div class="page-title">View Project</div>
    <div><i class="bi bi-person"></i> <?= $_SESSION['user_name'] ?></div>
</div>

<br>

<div class="card">
    <h3><?= htmlspecialchars($project['project_name']) ?></h3>
    <p><span class="label">Assigned Agent:</span> <span class="value"><?= $project['agent_name'] ?? 'Not Assigned' ?></span></p>
    <p><span class="label">Created By:</span> <span class="value"><?= $project['created_by_name'] ?? 'Unknown' ?></span></p>
    <p><span class="label">Start Date:</span> <span class="value"><?= $project['start_date'] ?? '-' ?></span></p>
    <p><span class="label">End Date:</span> <span class="value"><?= $project['end_date'] ?? '-' ?></span></p>
    <p><span class="label">Status:</span> 
        <span class="<?= $project['status']=='Active'?'ok':'off' ?>"><?= $project['status'] ?></span>
    </p>
    <p><span class="label">Created At:</span> <span class="value"><?= $project['created_at'] ?></span></p>
    <p><span class="label">Description:</span></p>
    <p><?= nl2br(htmlspecialchars($project['description'] ?? 'No description')) ?></p>
</div>

<a href="projects.php" class="back-btn"><i class="fa fa-arrow-left"></i> Back to Projects</a>

</div>
</body>
</html>
