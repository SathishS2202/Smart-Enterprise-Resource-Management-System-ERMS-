

<?php
require_once '../includes/middleware.php';
allowOnly('Admin');

include '../includes/db.php';

$id = intval($_GET['id'] ?? 0);

// Fetch user
$userRes = mysqli_query($conn, "SELECT * FROM users WHERE id=$id");
if(mysqli_num_rows($userRes) == 0){
    die("User not found.");
}
$user = mysqli_fetch_assoc($userRes);

// Fetch roles dynamically
$roles = mysqli_query($conn, "SELECT * FROM roles ORDER BY role_name ASC");

// Initialize error array
$errors = [];

// Handle POST submission
if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $role_id = intval($_POST['role_id'] ?? 0);
    $status = $_POST['status'] ?? '';

    // Server-side validation
    if($name === '') $errors[] = "Name cannot be empty.";
    if($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Enter a valid email.";
    if($role_id <= 0) $errors[] = "Select a role.";
    if($status !== '1' && $status !== '0') $errors[] = "Select a valid status.";

    if(empty($errors)){
        // Update user in database
        $updateSQL = "UPDATE users SET name='".mysqli_real_escape_string($conn,$name)."',
                      email='".mysqli_real_escape_string($conn,$email)."',
                      role_id=$role_id,
                      status=$status
                      WHERE id=$id";

        if(mysqli_query($conn, $updateSQL)){
            header("Location: users.php?success=User updated successfully");
            exit;
        } else {
            $errors[] = "Database error: " . mysqli_error($conn);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Edit User</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
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
/* CARD FORM */
.card{background:#fff;padding:20px;border-radius:8px;box-shadow:0 2px 8px rgba(0,0,0,.08);margin-bottom:20px}
.card h3{margin-top:0}
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
<div class="topbar">
    <div class="page-title">Edit User</div>
    <div><i class="bi bi-person"></i> <?= $_SESSION['user_name'] ?></div>
</div>

<br>

<div class="card">
    <h3>Edit User</h3>

    <?php if(!empty($errors)): ?>
        <div class="alert alert-danger">
            <ul class="mb-0">
                <?php foreach($errors as $err) echo "<li>$err</li>"; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="POST" class="row g-3">

        <div class="col-md-6">
            <label class="form-label">Name</label>
            <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($_POST['name'] ?? $user['name']) ?>">
        </div>

        <div class="col-md-6">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($_POST['email'] ?? $user['email']) ?>">
        </div>

        <div class="col-md-6">
            <label class="form-label">Role</label>
            <select name="role_id" class="form-select">
                <?php while($r = mysqli_fetch_assoc($roles)) { ?>
                    <option value="<?= $r['id'] ?>" <?= (($user['role_id']==$r['id']) ? 'selected' : '') ?>><?= htmlspecialchars($r['role_name']) ?></option>
                <?php } ?>
            </select>
        </div>

        <div class="col-md-6">
            <label class="form-label">Status</label>
            <select name="status" class="form-select">
                <option value="1" <?= (($user['status']==1)?'selected':'') ?>>Active</option>
                <option value="0" <?= (($user['status']==0)?'selected':'') ?>>Inactive</option>
            </select>
        </div>

        <div class="col-12 text-end">
            <a href="users.php" class="btn btn-secondary me-2">Cancel</a>
            <button type="submit" class="btn btn-primary">Update User</button>
        </div>

    </form>
</div>

</div>
</body>
</html>
