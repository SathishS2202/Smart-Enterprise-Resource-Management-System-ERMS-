<?php
session_start();

if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Agent'){
    header("Location: ../auth/login.php");
    exit;
}

include '../includes/db.php';

// Handle status update
if(isset($_GET['id'], $_GET['status'])){
    $task_id = (int)$_GET['id'];
    $status  = $_GET['status'];

    // Only allow the logged-in agent to update their tasks
    mysqli_query($conn, "UPDATE tasks SET status='$status' WHERE id=$task_id AND assigned_to={$_SESSION['user_id']}");
    header("Location: tasks.php");
    exit;
}

// Fetch tasks assigned to this agent
$tasks = mysqli_query($conn, "
    SELECT t.*, p.project_name 
    FROM tasks t
    JOIN projects p ON t.project_id = p.id
    WHERE t.assigned_to = {$_SESSION['user_id']}
    ORDER BY t.deadline ASC
");
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>My Tasks</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
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
.topbar{background:#fff;padding:12px 20px;display:flex;justify-content:space-between;align-items:center;border-bottom:1px solid #ddd}
.page-title{font-size:22px}

/* TABLE */
table{width:100%;border-collapse:collapse;border:1px solid #d1d5db;background:#fff;border-radius:6px}
th,td{padding:12px;border:1px solid #e5e7eb;text-align:center;font-size:14px}
th{background:#f8fafc;font-weight:600}
tr:hover{background:#f1f5f9}

/* STATUS BADGES */
.badge-low{background:#dbeafe;color:#1e3a8a}
.badge-medium{background:#fef3c7;color:#92400e}
.badge-high{background:#fee2e2;color:#991b1b}
.badge-todo{background:#f3f4f6;color:#374151}
.badge-inprogress{background:#bfdbfe;color:#1e40af}
.badge-done{background:#d1fae5;color:#065f46}

/* BUTTONS */
.btn-status{padding:4px 8px;font-size:12px}

/* SEARCH */
.search-box{
    width:250px;
    padding:6px 10px;
    border-radius:4px;
    border:1px solid #ccc;
    margin-bottom:10px;
}
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
    <div class="page-title">My Tasks</div>
    <div><i class="bi bi-person"></i> <?= $_SESSION['user_name'] ?></div>
</div>

<div class="card mt-4 p-3">

    <!-- Search Box -->
    <input type="text" class="search-box" placeholder="Search tasks...">

    <table class="table table-bordered table-hover align-middle">
    <thead>
    <tr>
        <th>#</th>
        <th>Project</th>
        <th>Task Title</th>
        <th>Description</th>
        <th>Priority</th>
        <th>Deadline</th>
        <th>Status</th>
        <th>Action</th>
    </tr>
    </thead>
    <tbody>
    <?php if(mysqli_num_rows($tasks) > 0):
        $i=1;
        while($t = mysqli_fetch_assoc($tasks)):
            $priorityClass = strtolower($t['priority']);
            $statusClass = strtolower(str_replace(' ','',$t['status']));
    ?>
    <tr>
        <td><?= $i++ ?></td>
        <td><?= htmlspecialchars($t['project_name']) ?></td>
        <td><?= htmlspecialchars($t['title']) ?></td>
        <td><?= htmlspecialchars($t['description']) ?></td>
        <td><span class="badge badge-<?= $priorityClass ?>"><?= $t['priority'] ?></span></td>
        <td><?= $t['deadline'] ?></td>
        <td><span class="badge badge-<?= $statusClass ?>"><?= $t['status'] ?></span></td>
        <td>
            <?php if($t['status'] !== 'Done'): ?>
                <a href="tasks.php?id=<?= $t['id'] ?>&status=In Progress" class="btn btn-primary btn-status mb-1">In Progress</a>
                <a href="tasks.php?id=<?= $t['id'] ?>&status=Done" class="btn btn-success btn-status">Mark Done</a>
            <?php else: ?>
                <span class="text-success fw-bold">Completed</span>
            <?php endif; ?>
        </td>
    </tr>
    <?php endwhile; else: ?>
    <tr>
    <td colspan="8" class="text-center text-muted">No tasks assigned</td>
    </tr>
    <?php endif; ?>
    </tbody>
    </table>
</div>

</div>

<!-- Search Script -->
<script>
const searchInput = document.querySelector('.search-box');
searchInput.addEventListener('keyup', () => {
    const filter = searchInput.value.toLowerCase();
    const rows = document.querySelectorAll('table tbody tr');
    rows.forEach(row => {
        row.style.display = row.innerText.toLowerCase().includes(filter) ? '' : 'none';
    });
});
</script>

</body>
</html>
