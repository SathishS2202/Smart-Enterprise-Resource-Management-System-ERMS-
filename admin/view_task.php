<?php
require_once '../includes/middleware.php';
allowOnly('Admin');

include '../includes/db.php';

if (!isset($_GET['id'])) {
    header("Location: tasks.php");
    exit;
}

$id = intval($_GET['id']);

$sql = "
    SELECT 
        t.id,
        t.title,
        t.description,
        t.priority,
        t.status,
        t.deadline,
        t.created_at,
        p.project_name,
        u.name AS agent_name
    FROM tasks t
    LEFT JOIN projects p ON t.project_id = p.id
    LEFT JOIN users u ON t.assigned_to = u.id
    WHERE t.id = $id
";

$result = mysqli_query($conn, $sql);

if (!$result || mysqli_num_rows($result) == 0) {
    header("Location: tasks.php");
    exit;
}

$task = mysqli_fetch_assoc($result);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>View Task</title>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">

<style>
body{margin:0;font-family:Segoe UI;background:#f3f4f6}

/* ===== SIDEBAR ===== */
.sidebar{
    position:fixed;
    left:0;top:0;
    height:100vh;
    width:70px;
    background:#111827;
    transition:.3s;
    overflow:hidden;
}
.sidebar:hover{width:220px}
.sidebar a{
    display:flex;
    align-items:center;
    gap:15px;
    padding:15px;
    color:#cbd5e1;
    text-decoration:none;
    white-space:nowrap;
}
.sidebar a i{
    font-size:20px;
    min-width:30px;
    text-align:center;
}
.sidebar a span{opacity:0}
.sidebar:hover a span{opacity:1}
.sidebar a:hover{background:#2563eb;color:#fff}

/* ===== MAIN ===== */
.main{
    margin-left:70px;
    transition:.3s;
    padding:20px;
}
.sidebar:hover ~ .main{margin-left:220px}

/* ===== TOPBAR ===== */
.topbar{
    background:#fff;
    padding:15px 20px;
    display:flex;
    justify-content:space-between;
    align-items:center;
    border-radius:10px;
    box-shadow:0 2px 6px rgba(0,0,0,.05);
}
.page-title{font-size:22px;font-weight:600}

/* ===== CARD ===== */
.card{
    margin-top:20px;
    background:#fff;
    border-radius:14px;
    box-shadow:0 5px 15px rgba(0,0,0,.08);
}
.card-header{
    padding:16px 20px;
    background:#2563eb;
    color:#fff;
    border-radius:14px 14px 0 0;
    font-size:18px;
}
.card-body{
    padding:25px;
    display:grid;
    grid-template-columns:repeat(2,1fr);
    gap:20px;
}
.label{
    font-size:13px;
    font-weight:600;
    color:#6b7280;
}
.value{
    font-size:15px;
    color:#111827;
    margin-top:4px;
}
.full{grid-column:1/3}

.status{
    padding:5px 12px;
    border-radius:20px;
    font-size:12px;
    font-weight:600;
}
.todo{background:#fee2e2;color:#991b1b}
.progress{background:#fef3c7;color:#92400e}
.done{background:#dcfce7;color:#166534}

.priority{
    padding:5px 12px;
    border-radius:20px;
    font-size:12px;
    font-weight:600;
}
.low{background:#e0f2fe;color:#0369a1}
.medium{background:#fef3c7;color:#92400e}
.high{background:#fee2e2;color:#991b1b}

/* ===== ACTIONS ===== */
.actions{
    margin-top:25px;
    text-align:right;
}
.btn{
    padding:10px 18px;
    border-radius:8px;
    text-decoration:none;
    font-weight:600;
}
.btn-back{background:#e5e7eb;color:#111827}
.btn-edit{background:#2563eb;color:#fff}
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

<div class="topbar">
    <div class="page-title">Task Details</div>
    <div>
        <i class="bi bi-person-circle"></i>
        <?= $_SESSION['username'] ?? 'Admin' ?>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <?= htmlspecialchars($task['title']) ?>
    </div>

    <div class="card-body">

        <div>
            <div class="label">Project</div>
            <div class="value"><?= $task['project_name'] ?? 'N/A' ?></div>
        </div>

        <div>
            <div class="label">Assigned To</div>
            <div class="value"><?= $task['agent_name'] ?? 'N/A' ?></div>
        </div>

        <div>
            <div class="label">Priority</div>
            <div class="value">
                <span class="priority <?= strtolower($task['priority']) ?>">
                    <?= $task['priority'] ?>
                </span>
            </div>
        </div>

        <div>
            <div class="label">Status</div>
            <div class="value">
                <span class="status <?= str_replace(' ','',strtolower($task['status'])) ?>">
                    <?= $task['status'] ?>
                </span>
            </div>
        </div>

        <div>
            <div class="label">Deadline</div>
            <div class="value"><?= $task['deadline'] ?: '—' ?></div>
        </div>

        <div>
            <div class="label">Created At</div>
            <div class="value"><?= date('d M Y', strtotime($task['created_at'])) ?></div>
        </div>

        <div class="full">
            <div class="label">Description</div>
            <div class="value">
                <?= nl2br(htmlspecialchars($task['description'] ?: 'No description provided')) ?>
            </div>
        </div>

    </div>
</div>

<div class="actions">
    <a href="tasks.php" class="btn btn-back">← Back</a>
    <a href="edit_task.php?id=<?= $task['id'] ?>" class="btn btn-edit">Edit Task</a>
</div>

</div>
</body>
</html>
