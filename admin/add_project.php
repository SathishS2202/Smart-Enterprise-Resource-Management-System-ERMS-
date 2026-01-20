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
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require '../vendor/autoload.php';

// Fetch all agents
$agents = mysqli_query($conn, "SELECT id, name, email FROM users WHERE role_id = 2");

// Initialize errors
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $project_name = trim(mysqli_real_escape_string($conn, $_POST['project_name']));
    $description  = trim(mysqli_real_escape_string($conn, $_POST['description']));
    $start_date   = $_POST['start_date'];
    $end_date     = $_POST['end_date'];
    $status       = $_POST['status'];
    $agent_id     = $_POST['agent_id'];
    $created_by   = $_SESSION['user_id'];

    // Validation
    if(empty($project_name)) $errors[] = "Project Name is required.";
    if(empty($start_date)) $errors[] = "Start Date is required.";
    if(empty($status)) $errors[] = "Project Status is required.";
    if(empty($agent_id)) $errors[] = "Please assign an agent.";

    if(empty($errors)){
        $sql = "INSERT INTO projects 
                (project_name, description, start_date, end_date, status, agent_id, created_by)
                VALUES ('$project_name', '$description', '$start_date', '$end_date', '$status', '$agent_id', '$created_by')";
        
        if(mysqli_query($conn, $sql)){
            // Send email
            $agentData = mysqli_fetch_assoc(mysqli_query($conn, "SELECT name, email FROM users WHERE id = $agent_id"));
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
                $mail->Subject = "New Project Assigned: $project_name";

                $loginLink = "http://localhost/erms/login.php";

                $mail->Body = "
                    <p>Hello {$agentData['name']},</p>
                    <p>You have been assigned a new project.</p>
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
            } catch (Exception $e) {
                error_log("Email error: ".$mail->ErrorInfo);
            }

            header("Location: projects.php?success=Project added successfully");
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
<title>Add Project</title>
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
.main{margin-left:60px;transition:.3s;padding:20px}
.sidebar:hover ~ .main{margin-left:230px}

/* TOPBAR */
.topbar{background:#fff;padding:12px 20px;display:flex;justify-content:space-between;align-items:center;border-bottom:1px solid #ddd}
.page-title{font-size:22px}

/* CARD */
.card{background:#fff;padding:20px;border-radius:8px;box-shadow:0 2px 8px rgba(0,0,0,.08);margin-bottom:20px}

/* LABELS */
.form-label{font-weight:600;color:#374151}

/* BUTTONS */
.btn-primary{background:#2563eb;border:none}
.btn-primary:hover{background:#1e40af}
.btn-secondary{background:#6b7280;border:none}
.btn-secondary:hover{background:#4b5563}
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

<div class="main">
<div class="topbar">
    <div class="page-title">Add New Project</div>
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
        <input type="text" name="project_name" class="form-control" value="<?= htmlspecialchars($_POST['project_name'] ?? '') ?>">
    </div>

    <div class="col-md-6">
        <label class="form-label">Assign to Agent</label>
        <select name="agent_id" class="form-select">
            <option value="">-- Select Agent --</option>
            <?php while($a = mysqli_fetch_assoc($agents)) { ?>
                <option value="<?= $a['id'] ?>" <?= (isset($_POST['agent_id']) && $_POST['agent_id']==$a['id'])?'selected':'' ?>>
                    <?= htmlspecialchars($a['name']) ?>
                </option>
            <?php } ?>
        </select>
    </div>

    <div class="col-12">
        <label class="form-label">Description</label>
        <textarea name="description" class="form-control" rows="3"><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
    </div>

    <div class="col-md-4">
        <label class="form-label">Start Date</label>
        <input type="date" name="start_date" class="form-control" value="<?= $_POST['start_date'] ?? '' ?>">
    </div>

    <div class="col-md-4">
        <label class="form-label">End Date</label>
        <input type="date" name="end_date" class="form-control" value="<?= $_POST['end_date'] ?? '' ?>">
    </div>

    <div class="col-md-4">
        <label class="form-label">Status</label>
        <select name="status" class="form-select">
            <option value="">-- Select Status --</option>
            <option value="Active" <?= (isset($_POST['status']) && $_POST['status']=='Active')?'selected':'' ?>>Active</option>
            <option value="Completed" <?= (isset($_POST['status']) && $_POST['status']=='Completed')?'selected':'' ?>>Completed</option>
            <option value="On Hold" <?= (isset($_POST['status']) && $_POST['status']=='On Hold')?'selected':'' ?>>On Hold</option>
        </select>
    </div>

    <div class="col-12 text-end">
        <a href="projects.php" class="btn btn-secondary me-2">Cancel</a>
        <button type="submit" class="btn btn-primary">Add Project</button>
    </div>

</form>
</div>
</div>

</body>
</html>
