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

/* DELETE TASK */
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);

    $check = mysqli_query($conn, "SELECT id FROM tasks WHERE id=$delete_id");
    if (mysqli_num_rows($check) > 0) {
        mysqli_query($conn, "DELETE FROM tasks WHERE id=$delete_id");
        $_SESSION['task_msg'] = ['type'=>'success','text'=>'Task deleted successfully'];
    } else {
        $_SESSION['task_msg'] = ['type'=>'error','text'=>'Task not found'];
    }
    header("Location: tasks.php");
    exit;
}

/* FETCH TASKS */
$tasks = mysqli_query($conn, "
    SELECT 
        t.id,
        t.title,
        t.priority,
        t.status,
        t.deadline,
        p.project_name,
        u.name AS agent_name
    FROM tasks t
    JOIN projects p ON t.project_id = p.id
    JOIN users u ON t.assigned_to = u.id
    ORDER BY t.created_at DESC
");
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Tasks</title>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">

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

/* TOP BAR */
.topbar{
    background:#fff;padding:8px 8px;
    display:flex;justify-content:space-between;
    align-items:center;border-bottom:1px solid #ddd
}

/* BOX */
.box{
    background:#fff;border-radius:6px;
    box-shadow:0 2px 6px rgba(0,0,0,.05)
}
.box-header{
    display:flex;justify-content:space-between;
    align-items:center;padding:12px 15px;
    border-bottom:1px solid #e5e7eb
}

/* ALERT */
.alert-inline{
    margin:10px 15px;padding:10px;
    border-radius:5px;font-weight:500
}
.success{background:#d1fae5;color:#065f46}
.error{background:#fee2e2;color:#7f1d1d}

/* SEARCH */
.search{
    padding:7px 10px;border:1px solid #ccc;
    border-radius:4px;width:220px
}

/* TABLE */
table{width:100%;border-collapse:collapse}
th,td{
    padding:12px;border:1px solid #e5e7eb;
    font-size:14px;text-align:center
}
th{background:#f8fafc}
tr:hover{background:#f1f5f9}

/* BADGES */
.badge{
    padding:4px 10px;border-radius:20px;
    font-size:12px;font-weight:600
}
.low{background:#e0f2fe;color:#0369a1}
.medium{background:#fef3c7;color:#92400e}
.high{background:#fee2e2;color:#991b1b}

.todo{background:#e5e7eb}
.progress{background:#dbeafe;color:#1e40af}
.done{background:#d1fae5;color:#065f46}

/* ICON BUTTON */
.icon-btn{
    padding:6px 10px;border:1px solid #cbd5e1;
    background:#f9fafb;border-radius:4px;
    color:#111827;text-decoration:none
}
.icon-btn:hover{background:#e5e7eb}
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
    <h2>Tasks</h2>
    <div><?= $_SESSION['user_name'] ?></div>
</div>

<br>

<div class="box">
<div class="box-header">
<strong>Task List</strong>
<div>
    <input class="search" placeholder="Search tasks...">
    <a href="add_task.php" class="icon-btn"><i class="fa fa-plus"></i></a>
</div>
</div>

<?php
if(isset($_SESSION['task_msg'])){
    $m = $_SESSION['task_msg'];
    echo "<div class='alert-inline {$m['type']}'>{$m['text']}</div>";
    unset($_SESSION['task_msg']);
}
?>

<table>
<thead>
<tr>
<th>#</th>
<th>Task</th>
<th>Project</th>
<th>Agent</th>
<th>Priority</th>
<th>Status</th>
<th>Deadline</th>
<th>Actions</th>
</tr>
</thead>
<tbody>

<?php
$sl=1;
while($t = mysqli_fetch_assoc($tasks)){
?>
<tr>
<td><?= $sl++ ?></td>
<td><?= htmlspecialchars($t['title']) ?></td>
<td><?= $t['project_name'] ?></td>
<td><?= $t['agent_name'] ?></td>
<td>
<span class="badge <?= strtolower($t['priority']) ?>">
<?= $t['priority'] ?>
</span>
</td>
<td>
<span class="badge 
<?= $t['status']=='To Do'?'todo':($t['status']=='In Progress'?'progress':'done') ?>">
<?= $t['status'] ?>
</span>
</td>
<td><?= $t['deadline'] ?></td>
<td>
<a href="view_task.php?id=<?= $t['id'] ?>" class="icon-btn"><i class="fa fa-eye"></i></a>
<a href="edit_task.php?id=<?= $t['id'] ?>" class="icon-btn"><i class="fa fa-edit"></i></a>
<a href="tasks.php?delete_id=<?= $t['id'] ?>" class="icon-btn">
<i class="fa fa-trash"></i>
</a>
</td>
</tr>
<?php } ?>

</tbody>
</table>
</div>
</div>

<script>
document.querySelector('.search').addEventListener('keyup', function(){
    let v = this.value.toLowerCase();
    document.querySelectorAll('tbody tr').forEach(r=>{
        r.style.display = r.innerText.toLowerCase().includes(v) ? '' : 'none';
    });
});
</script>

</body>
</html>
