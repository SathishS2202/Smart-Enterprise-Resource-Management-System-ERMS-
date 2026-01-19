<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../login.php");
    exit;
}

include '../includes/db.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require '../vendor/autoload.php';

// Get project ID
if(!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: projects.php");
    exit;
}

$project_id = intval($_GET['id']);

// Fetch project info
$projectRes = mysqli_query($conn, "SELECT * FROM projects WHERE id = $project_id");
if(mysqli_num_rows($projectRes) == 0){
    header("Location: projects.php");
    exit;
}
$project = mysqli_fetch_assoc($projectRes);

// Fetch all agents
$agents = mysqli_query($conn, "SELECT id, name, email FROM users WHERE role_id = 2");

$errors = [];

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $project_name = trim(mysqli_real_escape_string($conn, $_POST['project_name']));
    $description  = trim(mysqli_real_escape_string($conn, $_POST['description']));
    $start_date   = $_POST['start_date'];
    $end_date     = $_POST['end_date'];
    $status       = $_POST['status'];
    $agent_id     = $_POST['agent_id'];

    // Server-side validation
    if(empty($project_name)) $errors[] = "Project Name is required.";
    if(empty($start_date)) $errors[] = "Start Date is required.";
    if(empty($status)) $errors[] = "Project Status is required.";
    if(empty($agent_id)) $errors[] = "Please assign an agent.";

    if(empty($errors)) {
        // Check if agent changed
        $previous_agent_id = $project['agent_id'];
        $sql = "UPDATE projects SET 
                project_name='$project_name',
                description='$description',
                start_date='$start_date',
                end_date='$end_date',
                status='$status',
                agent_id='$agent_id'
                WHERE id=$project_id";

        if(mysqli_query($conn, $sql)){
            // Send email if agent changed
            if($previous_agent_id != $agent_id) {
                $agentData = mysqli_fetch_assoc(mysqli_query($conn, "SELECT name, email FROM users WHERE id=$agent_id"));

                $mail = new PHPMailer(true);
                try {
                    $mail->isSMTP();
                    $mail->Host       = 'smtp.gmail.com';
                    $mail->SMTPAuth   = true;
                    $mail->Username   = 'sathishs2202@gmail.com';
                    $mail->Password   = 'iegaktnuladdhjsm';
                    $mail->SMTPSecure = 'tls';
                    $mail->Port       = 587;

                    $mail->setFrom('youremail@gmail.com', 'ERMS Admin');
                    $mail->addAddress($agentData['email'], $agentData['name']);

                    $mail->isHTML(true);
                    $mail->Subject = "Project Assigned/Updated: $project_name";

                    $loginLink = "http://localhost/erms/login.php"; // Change to live URL
                    $mail->Body = "
                        <p>Hello {$agentData['name']},</p>
                        <p>You have been assigned/updated to the following project:</p>
                        <ul>
                            <li><strong>Project Name:</strong> $project_name</li>
                            <li><strong>Start Date:</strong> $start_date</li>
                            <li><strong>End Date:</strong> $end_date</li>
                            <li><strong>Status:</strong> $status</li>
                        </ul>
                        <p>Please <a href='$loginLink'>log in</a> to ERMS to view project details.</p>
                        <p>Regards,<br>Admin</p>
                    ";
                    $mail->send();
                } catch(Exception $e) {
                    error_log("Email error: ".$mail->ErrorInfo);
                }
            }

            header("Location: projects.php?success=Project updated successfully");
            exit;
        } else {
            $errors[] = "Database error: ".mysqli_error($conn);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Edit Project</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
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

/* CARD */
.card{background:#fff;padding:20px;border-radius:8px;box-shadow:0 2px 8px rgba(0,0,0,.08);margin-bottom:20px}
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
    <div class="page-title">Edit Project</div>
    <div><i class="bi bi-person"></i> <?= $_SESSION['user_name'] ?></div>
</div>

<br>

<div class="card">
    <?php if(!empty($errors)): ?>
        <div class="alert alert-danger">
            <ul class="mb-0">
                <?php foreach($errors as $err) echo "<li>$err</li>"; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="post" class="row g-3">

        <div class="col-md-6">
            <label class="form-label">Project Name</label>
            <input type="text" name="project_name" class="form-control" value="<?= htmlspecialchars($_POST['project_name'] ?? $project['project_name']) ?>">
        </div>

        <div class="col-md-6">
            <label class="form-label">Assign to Agent</label>
            <select name="agent_id" class="form-select">
                <option value="">-- Select Agent --</option>
                <?php while($a = mysqli_fetch_assoc($agents)) { ?>
                    <option value="<?= $a['id'] ?>" <?= ((isset($_POST['agent_id']) && $_POST['agent_id']==$a['id']) || ($project['agent_id']==$a['id'] && !isset($_POST['agent_id'])))?'selected':'' ?>>
                        <?= htmlspecialchars($a['name']) ?>
                    </option>
                <?php } ?>
            </select>
        </div>

        <div class="col-12">
            <label class="form-label">Description</label>
            <textarea name="description" class="form-control" rows="3"><?= htmlspecialchars($_POST['description'] ?? $project['description']) ?></textarea>
        </div>

        <div class="col-md-4">
            <label class="form-label">Start Date</label>
            <input type="date" name="start_date" class="form-control" value="<?= $_POST['start_date'] ?? $project['start_date'] ?>">
        </div>

        <div class="col-md-4">
            <label class="form-label">End Date</label>
            <input type="date" name="end_date" class="form-control" value="<?= $_POST['end_date'] ?? $project['end_date'] ?>">
        </div>

        <div class="col-md-4">
            <label class="form-label">Status</label>
            <select name="status" class="form-select">
                <option value="">-- Select Status --</option>
                <?php foreach(['Active','Completed','On Hold'] as $st){ ?>
                    <option value="<?= $st ?>" <?= ((isset($_POST['status']) && $_POST['status']==$st) || ($project['status']==$st && !isset($_POST['status'])))?'selected':'' ?>><?= $st ?></option>
                <?php } ?>
            </select>
        </div>

        <div class="col-12 text-end">
            <a href="projects.php" class="btn btn-secondary me-2">Cancel</a>
            <button type="submit" class="btn btn-primary">Update Project</button>
        </div>

    </form>
</div>

</div>
</body>
</html>
