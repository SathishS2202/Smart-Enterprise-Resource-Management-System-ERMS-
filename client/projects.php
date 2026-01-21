<?php
if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Client') {
    header("Location: ../auth/login.php");
    exit;
}

include '../includes/db.php';

$client_id = $_SESSION['user_id'];
$success = '';
$error = '';

/* ADD PROJECT */
if (isset($_POST['add_project'])) {
    $name  = mysqli_real_escape_string($conn, $_POST['project_name']);
    $desc  = mysqli_real_escape_string($conn, $_POST['description']);
    $start = $_POST['start_date'] ?: null;
    $end   = $_POST['end_date'] ?: null;

    if ($name == '') {
        $error = "Project name is required.";
    } else {
        mysqli_query($conn, "
    INSERT INTO projects 
    (project_name, description, client_id, status, start_date, end_date)
    VALUES (
        '$name',
        '$desc',
        $client_id,
        'Pending',
        ".($start ? "'$start'" : "NULL").",
        ".($end ? "'$end'" : "NULL")."
    )
");
/* ===== ADD NOTIFICATION FOR ADMIN ===== */
mysqli_query($conn, "
    INSERT INTO notifications (user_id, title, message, type)
    VALUES (
        1,
        'New Project Created',
        'Client requested a new project: $name',
        'project'
    )
");


        if(mysqli_affected_rows($conn)>0){
            $success = "Project request sent to Admin!";
        } else {
            $error = "Database error: ".mysqli_error($conn);
        }
    }
}

/* FETCH CLIENT PROJECTS */
$projects = mysqli_query($conn, "
    SELECT * FROM projects 
    WHERE created_by = $client_id 
    ORDER BY id DESC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Client Projects</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<style>
body{margin:0;font-family:Segoe UI,sans-serif;background:#f3f4f6;}
/* SIDEBAR */
.sidebar{position:fixed;left:0;top:0;height:100vh;width:70px;background:#111827;transition:.3s;overflow:hidden;z-index:1000}
.sidebar:hover{width:220px}
.sidebar a{display:flex;align-items:center;padding:15px;margin:5px 8px;gap:15px;color:#cbd5e1;text-decoration:none;border-radius:8px}
.sidebar a:hover{background:#2563eb;color:#fff}
.sidebar i{font-size:20px;min-width:30px;text-align:center}
.sidebar span{opacity:0;transition:.3s}
.sidebar:hover span{opacity:1}
/* MAIN */
.main{margin-left:70px;padding:20px;transition:.3s}
.sidebar:hover ~ .main{margin-left:230px}
/* TOP BAR */
.topbar{background:#fff;padding:12px 20px;display:flex;justify-content:space-between;align-items:center;border-bottom:1px solid #ddd;margin-bottom:20px}
/* CARD */
.card{background:#fff;padding:20px;border-radius:8px;box-shadow:0 2px 8px rgba(0,0,0,.08);margin-bottom:20px}
/* TABLE */
.table thead th{background:#1e293b;color:#fff}
.badge-pending{background:#facc15;color:#1e293b;}
.badge-active{background:#2563eb;color:#fff;}
.badge-completed{background:#16a34a;color:#fff;}
</style>
</head>
<body>

<div class="sidebar">
    <a href="dashboard.php"><i class="bi bi-speedometer2"></i><span>Dashboard</span></a>
    <a href="projects.php"><i class="bi bi-folder"></i><span>Projects</span></a>
    <a href="tasks.php"><i class="bi bi-list-task"></i><span>Tasks</span></a>
    <a href="documents.php"><i class="bi bi-file-earmark-text"></i><span>Documents</span></a>
    <a href="profile.php"><i class="bi bi-person-circle"></i><span>My Profile</span></a>
    <a href="logout.php"><i class="bi bi-box-arrow-right"></i><span>Logout</span></a>
</div>

<div class="main">
<div class="topbar">
    <div class="page-title">My Projects</div>
    <div><i class="bi bi-person"></i> <?= $_SESSION['user_name'] ?></div>
</div>

<?php if($success): ?>
    <div class="alert alert-success"><?= $success ?></div>
<?php endif; ?>
<?php if($error): ?>
    <div class="alert alert-danger"><?= $error ?></div>
<?php endif; ?>

<!-- CREATE PROJECT -->
<div class="card">
    <div class="card-header fw-bold">Request New Project</div>
    <div class="card-body">
        <form method="post">
            <div class="row g-3">
                <div class="col-md-4">
                    <input type="text" name="project_name" class="form-control" placeholder="Project Name" required>
                </div>
                <div class="col-md-4">
                    <input type="text" name="description" class="form-control" placeholder="Description">
                </div>
                <div class="col-md-2">
                    <input type="date" name="start_date" class="form-control">
                </div>
                <div class="col-md-2">
                    <input type="date" name="end_date" class="form-control">
                </div>
                <div class="col-md-2 mt-2">
                    <button class="btn btn-primary w-100" name="add_project">Request</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- PROJECT LIST -->
<div class="card">
    <div class="card-header fw-bold">Project List</div>
    <div class="card-body table-responsive p-0">
        <table class="table table-bordered table-hover mb-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Project</th>
                    <th>Status</th>
                    <th>Start Date</th>
                    <th>End Date</th>
                </tr>
            </thead>
            <tbody>
            <?php $sl=1; while($p=mysqli_fetch_assoc($projects)): ?>
                <tr>
                    <td><?= $sl++ ?></td>
                    <td><?= htmlspecialchars($p['project_name']) ?></td>
                    <td>
                        <?php 
                            $status = $p['status'];
                            if($status=='Pending') echo '<span class="badge badge-pending">Pending</span>';
                            elseif($status=='Active') echo '<span class="badge badge-active">Approved</span>';
                            elseif($status=='Completed') echo '<span class="badge badge-completed">Completed</span>';
                            else echo $status;
                        ?>
                    </td>
                    <td><?= $p['start_date'] ?: '-' ?></td>
                    <td><?= $p['end_date'] ?: '-' ?></td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>
</div>

</body>
</html>
