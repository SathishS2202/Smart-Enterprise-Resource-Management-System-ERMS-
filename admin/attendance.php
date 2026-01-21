
<?php
require_once '../includes/middleware.php';
allowOnly('Admin');
include '../includes/db.php';

$date = $_GET['date'] ?? date('Y-m-d');

$query = "
    SELECT 
        a.*,
        u.name,
        TIMEDIFF(a.check_out, a.check_in) AS total_hours
    FROM attendance a
    JOIN users u ON a.user_id = u.id
    WHERE a.attendance_date = '$date'
    ORDER BY u.name ASC
";

$result = mysqli_query($conn, $query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Attendance</title>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<style>
body{margin:0;font-family:Segoe UI;background:#f3f4f6}

/* SIDEBAR */
.sidebar{
    position:fixed;left:0;top:0;height:100vh;width:70px;
    background:#111827;transition:.3s;overflow:hidden;z-index:1000
}
.sidebar:hover{width:220px}
.sidebar a{
    display:flex;align-items:center;
    padding:15px;margin:5px 8px;
    gap:15px;color:#cbd5e1;text-decoration:none;
    border-radius:8px;white-space:nowrap
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
    border-bottom:1px solid #ddd
}
.page-title{font-size:22px}

/* BOX */
.box{
    background:#fff;
    border-radius:6px;
    box-shadow:0 2px 6px rgba(0,0,0,.05)
}
.box-header{
    display:flex;
    justify-content:space-between;
    align-items:center;
    padding:12px 15px;
    border-bottom:1px solid #e5e7eb
}

/* TABLE */
table{
    width:100%;
    border-collapse:collapse
}
th,td{
    padding:12px;
    border:1px solid #e5e7eb;
    font-size:14px;
    text-align:center
}
th{
    background:#f8fafc;
    font-weight:600
}
tr:hover{background:#f1f5f9}

/* STATUS */
.badge-present{
    background:#d1fae5;
    color:#065f46;
    padding:4px 10px;
    border-radius:20px;
    font-size:12px;
    border:1px solid #6ee7b7
}
.badge-absent{
    background:#fee2e2;
    color:#7f1d1d;
    padding:4px 10px;
    border-radius:20px;
    font-size:12px;
    border:1px solid #fca5a5
}

/* SEARCH */
.search{
    padding:7px 10px;
    border:1px solid #ccc;
    border-radius:4px;
    width:220px
}
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

<!-- TOPBAR -->
<div class="topbar mb-3">
    <div class="page-title">Attendance</div>
    <div>
        <i class="bi bi-person"></i>
        <?= $_SESSION['username'] ?? 'Admin' ?>
    </div>
</div>
<br>
<br>

<!-- BOX -->
<div class="box">

<div class="box-header">
    <strong>Attendance Records</strong>
    <div class="d-flex gap-2">
        <input type="text" class="search" placeholder="Search name...">
        <form method="get">
            <input type="date" name="date" value="<?= $date ?>" class="search">
        </form>
    </div>
</div>

<table>
<thead>
<tr>
    <th>#</th>
    <th>Name</th>
    <th>Date</th>
    <th>Check In</th>
    <th>Check Out</th>
    <th>Total Hours</th>
    <th>Status</th>
</tr>
</thead>
<tbody>

<?php
if(mysqli_num_rows($result)>0){
    $i=1;
    while($row=mysqli_fetch_assoc($result)){
        $present = !empty($row['check_in']);
?>
<tr>
    <td><?= $i++ ?></td>
    <td><?= htmlspecialchars($row['name']) ?></td>
    <td><?= $row['attendance_date'] ?></td>
    <td><?= $row['check_in'] ?? '-' ?></td>
    <td><?= $row['check_out'] ?? '-' ?></td>
    <td><?= $row['total_hours'] ?? '-' ?></td>
    <td>
        <?php if($present){ ?>
            <span class="badge-present">Present</span>
        <?php } else { ?>
            <span class="badge-absent">Absent</span>
        <?php } ?>
    </td>
</tr>
<?php } } else { ?>
<tr>
    <td colspan="7" class="text-muted">No records found</td>
</tr>
<?php } ?>

</tbody>
</table>

</div>
</div>

<script>
const searchInput = document.querySelector('.search');
searchInput.addEventListener('keyup', () => {
    const filter = searchInput.value.toLowerCase();
    document.querySelectorAll('tbody tr').forEach(row => {
        row.style.display = row.innerText.toLowerCase().includes(filter) ? '' : 'none';
    });
});
</script>

</body>
</html>
