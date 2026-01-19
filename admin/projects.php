<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../login.php");
    exit;
}

include '../includes/db.php';

/* Fetch projects with creator and assigned agent name */
$projects = mysqli_query($conn, "
    SELECT 
        p.id,
        p.project_name,
        p.start_date,
        p.end_date,
        p.status,
        p.created_at,
        creator.name AS created_by_name,
        agent.name AS agent_name
    FROM projects p
    LEFT JOIN users creator ON p.created_by = creator.id
    LEFT JOIN users agent ON p.agent_id = agent.id
    ORDER BY p.created_at DESC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Projects</title>

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

/* BOX */
.box{background:#fff;border-radius:6px;box-shadow:0 2px 6px rgba(0,0,0,.05)}
.box-header{display:flex;justify-content:space-between;align-items:center;padding:12px 15px;border-bottom:1px solid #e5e7eb}

/* ALERT MESSAGE */
.alert-msg{margin:10px 0;padding:10px;border-radius:5px;font-weight:600;}

/* SEARCH */
.search{padding:7px 10px;border:1px solid #ccc;border-radius:4px;width:220px}

/* TABLE */
table{width:100%;border-collapse:collapse;border:1px solid #d1d5db}
th,td{padding:12px;border:1px solid #e5e7eb;font-size:14px;text-align:center}
th{background:#f8fafc;font-weight:600}
tr:hover{background:#f1f5f9}

/* STATUS */
.ok{color:#065f46;background:#d1fae5;padding:4px 10px;border-radius:20px;font-size:12px;border:1px solid #6ee7b7}
.off{color:#7f1d1d;background:#fee2e2;padding:4px 10px;border-radius:20px;font-size:12px;border:1px solid #fca5a5}

/* ICON BUTTON */
.icon-btn{display:inline-flex;align-items:center;justify-content:center;border:1px solid #cbd5e1;background:#f9fafb;padding:6px 10px;border-radius:4px;cursor:pointer;color:#111827;text-decoration:none}
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
    <a href="reports.php"><i class="bi bi-bar-chart"></i><span>Reports</span></a>
    <a href="../logout.php"><i class="bi bi-box-arrow-right"></i><span>Logout</span></a>
</div>
<!-- MAIN -->
<div class="main">

<!-- TOP BAR -->
<div class="topbar">
    <div class="page-title">Projects</div>
    <div>
        <i class="bi bi-person"></i>
        <?= $_SESSION['user_name'] ?>
    </div>
</div>

<br>

<!-- TABLE BOX -->
<div class="box">
<div class="box-header">
<strong>List of Projects</strong>
<div>
    <input type="text" id="searchInput" class="search" placeholder="Search..." onkeyup="searchTable()">
    <a href="add_project.php" class="icon-btn"><i class="fa fa-plus"></i></a>
</div>
</div>

<!-- ALERT MESSAGE -->
<?php if(isset($_SESSION['project_msg'])): ?>
    <div class="alert-msg" style="
        background: <?= $_SESSION['project_msg']['type']=='success' ? '#d1fae5' : '#fee2e2' ?>;
        color: <?= $_SESSION['project_msg']['type']=='success' ? '#065f46' : '#7f1d1d' ?>;
        border: 1px solid <?= $_SESSION['project_msg']['type']=='success' ? '#6ee7b7' : '#fca5a5' ?>;
    ">
        <?= $_SESSION['project_msg']['text'] ?>
    </div>
<?php unset($_SESSION['project_msg']); endif; ?>

<table id="dataTable">
<thead>
<tr>
<th>#</th>
<th>Project Name</th>
<th>Assigned Agent</th>
<th>Created By</th>
<th>Start Date</th>
<th>End Date</th>
<th>Status</th>
<th>Actions</th>
</tr>
</thead>
<tbody>
<?php if(mysqli_num_rows($projects) > 0):
    $sl = 1;
    while($p = mysqli_fetch_assoc($projects)):
        $active = ($p['status'] == 'Active');
?>
<tr>
<td><?= $sl++ ?></td>
<td><?= htmlspecialchars($p['project_name']) ?></td>
<td><?= $p['agent_name'] ?? 'Not Assigned' ?></td>
<td><?= $p['created_by_name'] ?? 'Unknown' ?></td>
<td><?= $p['start_date'] ?? '-' ?></td>
<td><?= $p['end_date'] ?? '-' ?></td>
<td><span class="<?= $active ? 'ok' : 'off' ?>"><?= $p['status'] ?></span></td>
<td>
    <a href="view_project.php?id=<?= $p['id'] ?>" class="icon-btn"><i class="fa fa-eye"></i></a>
    <a href="edit_project.php?id=<?= $p['id'] ?>" class="icon-btn"><i class="fa fa-edit"></i></a>
    <a href="delete_project.php?id=<?= $p['id'] ?>" class="icon-btn"><i class="fa fa-trash"></i></a>
</td>
</tr>
<?php endwhile; else: ?>
<tr><td colspan="8">No projects found</td></tr>
<?php endif; ?>
</tbody>
</table>

</div>
</div>

<script>
function searchTable() {
    let input = document.getElementById("searchInput");
    let filter = input.value.toLowerCase();
    let table = document.getElementById("dataTable");
    let rows = table.getElementsByTagName("tr");

    for (let i = 1; i < rows.length; i++) { // skip header
        let rowText = rows[i].innerText.toLowerCase();
        rows[i].style.display = rowText.includes(filter) ? "" : "none";
    }
}
</script>

</body>
</html>
