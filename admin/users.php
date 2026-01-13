<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../login.php");
    exit;
}
include '../includes/db.php';

$users = mysqli_query($conn,"
    SELECT users.id, users.name, users.email, users.username, users.phone, users.status,
           roles.role_name
    FROM users
    JOIN roles ON users.role_id = roles.id
");
?>
<!DOCTYPE html>
<html>
<head>
<title>Agents</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">

<style>
body{margin:0;font-family:Segoe UI;background:#f3f4f6}

/* SIDEBAR */
.sidebar{
position:fixed;left:0;top:0;height:100vh;width:60px;
background: #111827;transition:.3s;overflow:hidden
}
.sidebar:hover{width:230px}
.sidebar .logo{
color:#fff;text-align:center;font-weight:700;
padding:18px 0;border-bottom:1px solid #444
}
.sidebar a{
display:flex;align-items:center;
padding:14px;color:#cbd5e1;text-decoration:none
}
.sidebar i{font-size:18px;min-width:45px;text-align:center}
.sidebar span{opacity:0}
.sidebar:hover span{opacity:1}
.sidebar a:hover{background:#2563eb}

/* MAIN */
.main{
margin-left:60px;transition:.3s;padding:15px
}
.sidebar:hover ~ .main{margin-left:230px}

/* HEADER */
.topbar{
background:#fff;padding:12px 20px;
display:flex;justify-content:space-between;
align-items:center;border-bottom:1px solid #ddd
}

/* ALERT */
.alert{
background:#fef9c3;color:#92400e;
padding:12px 15px;border-radius:5px;
margin:15px 0;font-size:14px
}

/* PAGE TITLE */
.page-title{font-size:22px;margin:10px 0}

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
.box-header strong{font-size:15px}
.box-header i{
cursor:pointer;margin-left:10px
}

/* SEARCH */
.search{
padding:7px 10px;border:1px solid #ccc;
border-radius:4px;width:220px
}

/* TABLE */
/* TABLE – ENTERPRISE BORDERED STYLE */
table{
    width:100%;
    border-collapse:collapse;
    background:#fff;
    border:1px solid #d1d5db;   /* outer border */
}

th, td{
    padding:12px;
    font-size:14px;
    border:1px solid #e5e7eb;   /* cell borders */
    vertical-align:middle;
   text-align: center;
}

th{
    background:#f8fafc;
    font-weight:600;
    color:#374151;
}

tr:hover{
    background:#f1f5f9;
}

/* Checkbox alignment */
th input, td input{
    transform:scale(1.1);
}

/* ACTION BUTTONS */
.actions i{
    border:1px solid #cbd5e1;
    background:#f9fafb;
    padding:6px 8px;
    border-radius:4px;
    margin-right:5px;
}

.actions i:hover{
    background:#e5e7eb;
}

/* STATUS */
.ok{
    color:#065f46;
    background:#d1fae5;
    padding:4px 10px;
    border-radius:20px;
    font-size:12px;
    border:1px solid #6ee7b7;
}

.off{
    color:#7f1d1d;
    background:#fee2e2;
    padding:4px 10px;
    border-radius:20px;
    font-size:12px;
    border:1px solid #fca5a5;
}
td{
    align-items: center;
}

.icon-btn{
    display:inline-flex;
    align-items:center;
    justify-content:center;
    border:1px solid #cbd5e1;
    background:#f9fafb;
    padding:6px 10px;
    border-radius:4px;
    cursor:pointer;
    text-decoration:none;
    color:#111827;
}

.icon-btn:hover{
    background:#e5e7eb;
}

.icon-btn i{
    pointer-events:none; /* VERY IMPORTANT */
}

/* ACTIONS */
.actions i{
background:#e5e7eb;padding:6px 8px;
border-radius:4px;margin-right:5px;
cursor:pointer
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
    <a href="reports.php"><i class="bi bi-bar-chart"></i><span>Reports</span></a>
    <a href="../logout.php"><i class="bi bi-box-arrow-right"></i><span>Logout</span></a>
</div>

<!-- MAIN -->
<div class="main">

<!-- TOP BAR -->
<div class="topbar">
<div class="page-title">Users</div>
<div>
<i class="bi bi-plus-square"></i>
<i class="bi bi-person"></i>
<i class="bi bi-bell"></i>
<?=$_SESSION['user_name']?>
</div>
</div>

<br> <br>

<!-- TABLE BOX -->
<div class="box">
<div class="box-header">
<strong>List of Users</strong>
<div>
<input class="search" placeholder="Type and press enter to search...">
<i class="bi bi-funnel"></i>
<a href="add_user.php" class="icon-btnn" style="color: black;">
    <i class="fa fa-plus"></i>
</a>

</div>
</div>

<table>
<thead>
<tr>
<th><input type="checkbox"></th>
<th>Name</th>
<th>Username</th>
<th>Email</th>
<th>Phone</th>
<th>Role</th>
<th>Account Info</th>
<th>Actions</th>
</tr>
</thead>
<tbody>
<?php while($u=mysqli_fetch_assoc($users)){ ?>
<tr>
<td><input type="checkbox"></td>
<td><?= $u['name'] ?></td>
<td><?= $u['username'] ?></td>
<td><?= $u['email'] ?></td>
<td><?= $u['phone'] ?></td>
<td><?= $u['role_name'] ?></td>
<td>
<span class="<?= $u['status']?'ok':'off' ?>">
<?= $u['status']?'Active':'Inactive' ?>
</span>
</td>
<td class="actions">
    <a href="edit_user.php?id=1" class="action-btn edit">
        <i class="fa fa-edit"></i>
    </a>

    <a href="view_user.php?id=1" class="action-btn view">
        <i class="fa fa-eye"></i>
    </a>

    <button class="action-btnn delete" onclick="deleteUser(1)">
        <i class="fa fa-trash"></i>
    </button>
</td>


</tr>
<?php } ?>
</tbody>
</table>
</div>

</div>
<script>
function deleteUser(id){
    if(confirm("Are you sure you want to delete this user?")){
        window.location.href = "delete_user.php?id=" + id;
    }
}
</script>

</body>
</html>
