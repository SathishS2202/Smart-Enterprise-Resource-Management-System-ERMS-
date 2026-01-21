


<?php
require_once '../includes/middleware.php';
allowOnly('Admin');
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Add User</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">

<style>
/* --- BODY & MAIN LAYOUT --- */
body {
    margin: 0;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background: #f3f4f6;
}

/* --- SIDEBAR (reuse from dashboard) --- */
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

/* --- MAIN AREA --- */
.main {
    margin-left: 60px;
    transition: .3s;
    padding: 30px;
}
.sidebar:hover ~ .main { margin-left: 230px; }

/* --- TOPBAR --- */
.topbar {
    background: #fff;
    padding: 12px 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid #ddd;
    border-radius: 8px;
    margin-bottom: 20px;
}
.page-title {
    font-size: 22px;
    font-weight: 600;
}

/* --- FORM CONTAINER --- */
.form-container {
    max-width: 600px;
    margin: 0 auto;
    background: #fff;
    padding: 30px;
    border-radius: 12px;
    box-shadow: 0 10px 25px rgba(0,0,0,0.08);
}

/* --- FORM HEAD --- */
.form-container h3 {
    text-align: center;
    margin-bottom: 25px;
    font-size: 26px;
    font-weight: 700;
    color: #111827;
}

/* --- FORM GROUPS --- */
.form-group {
    margin-bottom: 20px;
}
.form-group label {
    display: block;
    font-weight: 600;
    margin-bottom: 6px;
    color: #374151;
}
.form-group input,
.form-group select,
.form-group textarea  {
    width: 90%;
    padding: 12px 15px;
    border-radius: 8px;
    border: 1px solid #d1d5db;
    outline: none;
    font-size: 15px;
    transition: 0.3s;
    background: #f9fafb;
}
.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
    border-color: #2563eb;
    background: #fff;
}

/* --- TEXTAREA --- */
textarea {
    resize: vertical;
    min-height: 100px;
}

/* --- BUTTONS --- */
.btn-submit {
    width: 100%;
    background: #2563eb;
    border: none;
    padding: 14px;
    color: #fff;
    font-size: 16px;
    font-weight: 600;
    border-radius: 8px;
    cursor: pointer;
    transition: 0.3s;
}
.btn-submit:hover {
    background: #1e40af;
}

/* --- BACK LINK --- */
.back-link {
    display: inline-block;
    margin-top: 15px;
    text-decoration: none;
    color: #2563eb;
    font-weight: 600;
}
.back-link:hover { text-decoration: underline; }

/* --- ALERTS --- */
.alert-inline {
    margin-bottom: 15px;
    padding: 12px;
    border-radius: 8px;
    font-weight: 600;
    text-align: center;
}
.alert-inline.success {
    background: #dcfce7;
    color: #166534;
    border: 1px solid #4ade80;
}
.alert-inline.error {
    background: #fee2e2;
    color: #991b1b;
    border: 1px solid #fca5a5;
}

/* --- RESPONSIVE --- */
@media screen and (max-width: 768px){
    .main{padding:20px;}
    .form-container{padding:20px;}
}


/* ALERT */
.alert-inline{
margin-bottom:15px;padding:12px;border-radius:8px;font-weight:600;text-align:center;
}
.alert-inline.success{background:#dcfce7;color:#166534;border:1px solid #4ade80;}
.alert-inline.error{background:#fee2e2;color:#991b1b;border:1px solid #fca5a5;}
</style>
</head>
<body>

<div class="sidebar">
    <a href="dashboard.php"><i class="bi bi-speedometer2"></i><span>Dashboard</span></a>
    <a href="users.php"><i class="bi bi-people"></i><span>Users</span></a>
    <a href="projects.php"><i class="bi bi-folder"></i><span>Projects</span></a>
    <a href="tasks.php"><i class="bi bi-list-task"></i><span>Tasks</span></a>
    <a href="attendance.php"><i class="bi bi-calendar-check"></i><span>Attendance</span></a>
    <a href="documents.php"><i class="bi bi-file-earmark-text"></i><span>Documents</span></a>
    <a href="leave_approvals.php"><i class="bi bi-file-earmark-text"></i><span>Leave Approvals</span></a>
    <a href="reports.php"><i class="bi bi-bar-chart"></i><span>Reports</span></a>
    <a href="logout.php"><i class="bi bi-box-arrow-right"></i><span>Logout</span></a>
</div>

<div class="main">

<div class="topbar">
<div class="page-title">Add New User</div>
<div><i class="bi bi-person"></i> <?= $_SESSION['user_name'] ?></div>
</div>

<br>

<div class="form-container">

<?php if(isset($_SESSION['error'])): ?>
    <div class="alert-inline error"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
<?php endif; ?>
<?php if(isset($_SESSION['success'])): ?>
    <div class="alert-inline success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
<?php endif; ?>

<form method="POST" action="add_user_process.php" id="addUserForm">

<div class="form-group">
    <label>Full Name</label>
    <input type="text" name="name">
</div>

<div class="form-group">
    <label>Username</label>
    <input type="text" name="username">
</div>

<div class="form-group">
    <label>Email</label>
    <input type="email" name="email">
</div>

<div class="form-group">
    <label>Phone Number</label>
    <input type="text" name="phone">
</div>

<div class="form-group">
    <label>Role</label>
    <select name="role_id">
        <option value="">Select Role</option>
        <option value="1">Admin</option>
        <option value="2">Agent</option>
        <option value="3">Client</option>
    </select>
</div>

<div class="form-group">
    <label>Password</label>
    <input type="password" name="password">
</div>

<button class="btn-submit" type="submit">Create User</button>

<a href="users.php" class="back-link">← Back to Users</a>
</form>
</div>
</div>

<script>
// Client-side validation example (optional)
document.getElementById('addUserForm').addEventListener('submit', function(e){
    let errors = [];
    const name = this.name.value.trim();
    const username = this.username.value.trim();
    const email = this.email.value.trim();
    const phone = this.phone.value.trim();
    const role = this.role_id.value;
    const pass = this.password.value;

    if(name === '') errors.push("Full Name is required");
    if(username === '') errors.push("Username is required");
    if(email === '' || !email.includes('@')) errors.push("Valid Email is required");
    if(phone === '') errors.push("Phone is required");
    if(role === '') errors.push("Select a Role");
    if(pass === '') errors.push("Password is required");

    if(errors.length > 0){
        alert(errors.join("\n"));
        e.preventDefault();
    }
});
</script>

</body>
</html>
