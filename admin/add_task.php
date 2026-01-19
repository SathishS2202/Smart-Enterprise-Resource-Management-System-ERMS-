<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../login.php");
    exit;
}

include '../includes/db.php';

/* Fetch projects */
$projects = mysqli_query($conn, "SELECT id, project_name FROM projects");

/* Fetch agents */
$agents = mysqli_query($conn, "
    SELECT id, name 
    FROM users 
    WHERE role_id = 2 AND status = 1
");

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $project_id  = intval($_POST['project_id']);
    $assigned_to = intval($_POST['assigned_to']);
    $title       = trim(mysqli_real_escape_string($conn, $_POST['title']));
    $description = trim(mysqli_real_escape_string($conn, $_POST['description']));
    $priority    = $_POST['priority'];
    $status      = $_POST['status'];
    $deadline    = $_POST['deadline'];

    /* VALIDATION */
    if (empty($project_id))  $errors[] = "Project is required.";
    if (empty($assigned_to)) $errors[] = "Agent is required.";
    if (empty($title))       $errors[] = "Task title is required.";
    if (empty($priority))    $errors[] = "Priority is required.";
    if (empty($status))      $errors[] = "Status is required.";

    if (empty($errors)) {
        $sql = "INSERT INTO tasks 
                (project_id, assigned_to, title, description, priority, status, deadline)
                VALUES 
                ('$project_id','$assigned_to','$title','$description','$priority','$status','$deadline')";

        if (mysqli_query($conn, $sql)) {
            $_SESSION['task_msg'] = ['type'=>'success','text'=>'Task added successfully'];
            header("Location: tasks.php");
            exit;
        } else {
            $errors[] = "Database error: " . mysqli_error($conn);
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Add Task</title>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
body{margin:0;font-family:Segoe UI;background:#f3f4f6}

/* SIDEBAR */
.sidebar{
    position:fixed;left:0;top:0;height:100vh;width:70px;
    background:#111827;transition:.3s;overflow:hidden
}
.sidebar:hover{width:220px}
.sidebar a{
    display:flex;align-items:center;
    padding:15px;margin:5px 8px;
    gap:15px;color:#cbd5e1;text-decoration:none;
    border-radius:8px
}
.sidebar a:hover{background:#2563eb;color:#fff}
.sidebar i{font-size:20px;min-width:30px;text-align:center}
.sidebar span{opacity:0}
.sidebar:hover span{opacity:1}

/* MAIN */
.main{margin-left:70px;padding:20px;transition:.3s}
.sidebar:hover ~ .main{margin-left:230px}

/* CARD */
.card{border:none;border-radius:8px}
.card-header{background:#2563eb;color:#fff;font-weight:600}
</style>
</head>

<body>

<!-- SIDEBAR -->
<div class="sidebar">
    <a href="dashboard.php"><i class="bi bi-speedometer2"></i><span>Dashboard</span></a>
    <a href="users.php"><i class="bi bi-people"></i><span>Users</span></a>
    <a href="projects.php"><i class="bi bi-folder"></i><span>Projects</span></a>
    <a href="tasks.php"><i class="bi bi-list-task"></i><span>Tasks</span></a>
    <a href="../logout.php"><i class="bi bi-box-arrow-right"></i><span>Logout</span></a>
</div>

<!-- MAIN -->
<div class="main">

<div class="card shadow-sm">
<div class="card-header">Add New Task</div>
<div class="card-body">

<?php if(!empty($errors)): ?>
<div class="alert alert-danger">
<ul class="mb-0">
<?php foreach($errors as $e) echo "<li>$e</li>"; ?>
</ul>
</div>
<?php endif; ?>

<form method="POST" class="row g-3">

<div class="col-md-6">
<label class="form-label">Project</label>
<select name="project_id" class="form-select">
<option value="">-- Select Project --</option>
<?php while($p = mysqli_fetch_assoc($projects)) { ?>
<option value="<?= $p['id'] ?>" <?= (($_POST['project_id'] ?? '')==$p['id'])?'selected':'' ?>>
<?= htmlspecialchars($p['project_name']) ?>
</option>
<?php } ?>
</select>
</div>

<div class="col-md-6">
<label class="form-label">Assign to Agent</label>
<select name="assigned_to" class="form-select">
<option value="">-- Select Agent --</option>
<?php while($a = mysqli_fetch_assoc($agents)) { ?>
<option value="<?= $a['id'] ?>" <?= (($_POST['assigned_to'] ?? '')==$a['id'])?'selected':'' ?>>
<?= htmlspecialchars($a['name']) ?>
</option>
<?php } ?>
</select>
</div>

<div class="col-md-6">
<label class="form-label">Task Title</label>
<input type="text" name="title" class="form-control"
value="<?= htmlspecialchars($_POST['title'] ?? '') ?>">
</div>

<div class="col-md-6">
<label class="form-label">Deadline</label>
<input type="date" name="deadline" class="form-control"
value="<?= $_POST['deadline'] ?? '' ?>">
</div>

<div class="col-12">
<label class="form-label">Description</label>
<textarea name="description" rows="3" class="form-control"><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
</div>

<div class="col-md-6">
<label class="form-label">Priority</label>
<select name="priority" class="form-select">
<option value="">-- Select Priority --</option>
<option value="Low" <?= (($_POST['priority'] ?? '')=='Low')?'selected':'' ?>>Low</option>
<option value="Medium" <?= (($_POST['priority'] ?? '')=='Medium')?'selected':'' ?>>Medium</option>
<option value="High" <?= (($_POST['priority'] ?? '')=='High')?'selected':'' ?>>High</option>
</select>
</div>

<div class="col-md-6">
<label class="form-label">Status</label>
<select name="status" class="form-select">
<option value="">-- Select Status --</option>
<option value="To Do" <?= (($_POST['status'] ?? '')=='To Do')?'selected':'' ?>>To Do</option>
<option value="In Progress" <?= (($_POST['status'] ?? '')=='In Progress')?'selected':'' ?>>In Progress</option>
<option value="Done" <?= (($_POST['status'] ?? '')=='Done')?'selected':'' ?>>Done</option>
</select>
</div>

<div class="col-12 text-end">
<a href="tasks.php" class="btn btn-secondary">Cancel</a>
<button class="btn btn-primary">Add Task</button>
</div>

</form>
</div>
</div>

</div>
</body>
</html>
