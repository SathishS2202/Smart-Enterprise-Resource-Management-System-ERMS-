<?php
require_once '../includes/middleware.php';
allowOnly('Admin');
include '../includes/db.php';

/* MARK AS READ */
if (isset($_GET['read'])) {
    $nid = (int)$_GET['read'];
    mysqli_query($conn, "UPDATE notifications SET is_read=1 WHERE id=$nid");
    header("Location: notifications.php");
    exit;
}

/* FETCH NOTIFICATIONS */
$notifications = mysqli_query($conn, "
    SELECT * FROM notifications
    WHERE user_id = {$_SESSION['user_id']}
    ORDER BY created_at DESC
");
?>
<!DOCTYPE html>
<html>
<head>
<title>Notifications</title>

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
display:flex;align-items:center;padding:15px;margin:5px 8px;
gap:15px;color:#cbd5e1;text-decoration:none;border-radius:8px
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

/* CARD */
.card{
background:#fff;border-radius:6px;
padding:15px;margin-bottom:10px;
box-shadow:0 2px 6px rgba(0,0,0,.05)
}
.card.unread{border-left:5px solid #2563eb}
.card.read{opacity:.7}

/* BADGES */
.badge{
padding:4px 10px;border-radius:20px;
font-size:12px;font-weight:600
}
.project{background:#dbeafe;color:#1e40af}
.task{background:#dcfce7;color:#065f46}
.system{background:#fef3c7;color:#92400e}

/* BUTTON */
.btn{
padding:5px 10px;font-size:12px;
border:none;border-radius:4px;
background:#2563eb;color:#fff;
text-decoration:none
}
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
    <a href="client_requests.php">
    <i class="bi bi-inbox"></i>
    <span>Client Requests</span>
</a>

    <a href="leave_approvals.php"><i class="bi bi-file-earmark-text"></i><span>Leave Approvals</span></a>
    <a href="reports.php"><i class="bi bi-bar-chart"></i><span>Reports</span></a>
    <a href="profile.php"><i class="bi bi-person-circle"></i><span>My Profile</span></a>
    <a href="logout.php"><i class="bi bi-box-arrow-right"></i><span>Logout</span></a>
</div>

<div class="main">

<div class="topbar">
<h2>Notifications</h2>
<div><?= htmlspecialchars($_SESSION['username'] ?? 'Admin') ?></div>
</div>

<br>

<?php while($n = mysqli_fetch_assoc($notifications)) { ?>
<div class="card <?= $n['is_read'] ? 'read' : 'unread' ?>">
    
    <span class="badge <?= $n['type'] ?>">
        <?= ucfirst($n['type']) ?>
    </span>

    <h4><?= htmlspecialchars($n['title']) ?></h4>
    <p><?= htmlspecialchars($n['message']) ?></p>

    <small><?= date('d M Y h:i A', strtotime($n['created_at'])) ?></small>

    <?php if(!$n['is_read']){ ?>
        <br><br>
        <a class="btn" href="?read=<?= $n['id'] ?>">Mark as Read</a>
    <?php } ?>

</div>
<?php } ?>

</div>
</body>
</html>
