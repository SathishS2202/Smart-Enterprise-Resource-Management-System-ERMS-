<?php
require_once '../includes/middleware.php';
allowOnly('Admin');


include '../includes/db.php';

// Handle deletion if `delete_id` is set in GET
if(isset($_GET['delete_id'])){
    $delete_id = intval($_GET['delete_id']);
    $check = mysqli_query($conn, "SELECT id FROM users WHERE id=$delete_id LIMIT 1");

    if(mysqli_num_rows($check) > 0){
        if(mysqli_query($conn, "DELETE FROM users WHERE id=$delete_id")){
            $_SESSION['user_msg'] = ['type'=>'success','text'=>'User deleted successfully.'];
        } else {
            $_SESSION['user_msg'] = ['type'=>'error','text'=>'Database error: '.mysqli_error($conn)];
        }
    } else {
        $_SESSION['user_msg'] = ['type'=>'error','text'=>'User not found.'];
    }
    header("Location: users.php");
    exit;
}

/* Fetch users with roles */
$users = mysqli_query($conn, "
    SELECT 
        users.id,
        users.name,
        users.username,
        users.email,
        users.phone,
        users.status,
        roles.role_name
    FROM users
    JOIN roles ON users.role_id = roles.id
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Users</title>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">

<style>
body{margin:0;font-family:Segoe UI;background:#f3f4f6}

/* SIDEBAR */
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
.topbar{
background:#fff;padding:12px 20px;
display:flex;justify-content:space-between;
align-items:center;border-bottom:1px solid #ddd
}

.page-title{font-size:22px}

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
margin:10px 15px;
padding:10px;
border-radius:5px;
font-weight:500;
}
.alert-inline.success{background:#d1fae5;color:#065f46;border:1px solid #6ee7b7;}
.alert-inline.error{background:#fee2e2;color:#7f1d1d;border:1px solid #fca5a5;}

/* SEARCH */
.search{
padding:7px 10px;border:1px solid #ccc;
border-radius:4px;width:220px
}

/* TABLE */
table{
width:100%;
border-collapse:collapse;
border:1px solid #d1d5db
}
th,td{
padding:12px;
border:1px solid #e5e7eb;
font-size:14px;text-align:center
}
th{background:#f8fafc;font-weight:600}
tr:hover{background:#f1f5f9}

/* STATUS */
.ok{
color:#065f46;background:#d1fae5;
padding:4px 10px;border-radius:20px;
font-size:12px;border:1px solid #6ee7b7
}
.off{
color:#7f1d1d;background:#fee2e2;
padding:4px 10px;border-radius:20px;
font-size:12px;border:1px solid #fca5a5
}

/* ICON BUTTON */
.icon-btn{
display:inline-flex;
align-items:center;
justify-content:center;
border:1px solid #cbd5e1;
background:#f9fafb;
padding:6px 10px;
border-radius:4px;
cursor:pointer;
color:#111827;
text-decoration:none
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

<!-- TOP BAR -->
<div class="topbar">
    <div class="page-title">Users</div>
    <div>
        <i class="bi bi-person"></i>
       <?= $_SESSION['username'] ?>

    </div>
</div>

<br>

<!-- TABLE BOX -->
<div class="box">
<div class="box-header">
<strong>List of Users</strong>
<div>
    <input class="search" placeholder="Search users...">
    <a href="add_user.php" class="icon-btn">
        <i class="fa fa-plus"></i>
    </a>
</div>
</div>

<?php
// Show inline message if exists
if(isset($_SESSION['user_msg'])){
    $msg = $_SESSION['user_msg'];
    echo "<div class='alert-inline {$msg['type']}'>{$msg['text']}</div>";
    unset($_SESSION['user_msg']);
}
?>

<table>
<thead>
<tr>
<th>#</th>
<th>Name</th>
<th>Username</th>
<th>Email</th>
<th>Phone</th>
<th>Role</th>
<th>Status</th>
<th>Actions</th>
</tr>
</thead>
<tbody>

<?php if(mysqli_num_rows($users) > 0){ 
$sl = 1;
while($u = mysqli_fetch_assoc($users)){ 

$active = ($u['status'] == 1 || $u['status'] == 'Active');
?>
<tr>
<td><?= $sl++ ?></td>
<td><?= htmlspecialchars($u['name']) ?></td>
<td><?= htmlspecialchars($u['username']) ?></td>
<td><?= htmlspecialchars($u['email']) ?></td>
<td><?= htmlspecialchars($u['phone']) ?></td>
<td><?= $u['role_name'] ?></td>
<td>
<span class="<?= $active ? 'ok' : 'off' ?>">
<?= $active ? 'Active' : 'Inactive' ?>
</span>
</td>
<td>
    <a href="view_user.php?id=<?= $u['id'] ?>" class="icon-btn">
        <i class="fa fa-eye"></i>
    </a>

    <a href="edit_user.php?id=<?= $u['id'] ?>" class="icon-btn">
        <i class="fa fa-edit"></i>
    </a>

    <a href="users.php?delete_id=<?= $u['id'] ?>" class="icon-btn">
        <i class="fa fa-trash"></i>
    </a>
</td>
</tr>
<?php } } else { ?>
<tr>
<td colspan="8">No users found</td>
</tr>
<?php } ?>

</tbody>
</table>

</div>
</div>

<script>
const searchInput = document.querySelector('.search');

searchInput.addEventListener('input', () => {
    const filter = searchInput.value.trim().toLowerCase();
    const rows = document.querySelectorAll('table tbody tr');

    rows.forEach(row => {
        const cells = row.querySelectorAll('td');
        let match = false;

        cells.forEach(cell => {
            // Skip the last cell (Actions)
            if(cell.cellIndex === cells.length - 1) return;
            if(cell.textContent.toLowerCase().includes(filter)){
                match = true;
            }
        });

        row.style.display = match ? '' : 'none';
    });
});
</script>


</body>
</html>
