<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../login.php");
    exit;
}

include '../includes/db.php';

$id = intval($_GET['id']);

$taskQ = mysqli_query($conn, "SELECT * FROM tasks WHERE id=$id");
if(mysqli_num_rows($taskQ) == 0){
    header("Location: tasks.php");
    exit;
}
$task = mysqli_fetch_assoc($taskQ);

/* Fetch projects & agents */
$projects = mysqli_query($conn, "SELECT id, project_name FROM projects");
$agents = mysqli_query($conn, "SELECT id, name FROM users WHERE role_id=2 AND status=1");

$errors = [];

if($_SERVER['REQUEST_METHOD'] === 'POST'){

    $project_id  = intval($_POST['project_id']);
    $assigned_to = intval($_POST['assigned_to']);
    $title       = trim(mysqli_real_escape_string($conn,$_POST['title']));
    $description = trim(mysqli_real_escape_string($conn,$_POST['description']));
    $priority    = $_POST['priority'];
    $status      = $_POST['status'];
    $deadline    = $_POST['deadline'];

    if(empty($project_id))  $errors[] = "Project is required";
    if(empty($assigned_to)) $errors[] = "Agent is required";
    if(empty($title))       $errors[] = "Task title is required";
    if(empty($priority))    $errors[] = "Priority is required";
    if(empty($status))      $errors[] = "Status is required";

    if(empty($errors)){
        $sql = "
            UPDATE tasks SET
            project_id='$project_id',
            assigned_to='$assigned_to',
            title='$title',
            description='$description',
            priority='$priority',
            status='$status',
            deadline='$deadline'
            WHERE id=$id
        ";

        if(mysqli_query($conn,$sql)){
            header("Location: tasks.php");
            exit;
        } else {
            $errors[] = "Database error";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Edit Task</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body{background:#f3f4f6;font-family:Segoe UI}
.card{border-radius:10px}
</style>
</head>
<body>

<div class="container my-5">
<div class="card shadow-sm">
<div class="card-header bg-primary text-white">
Edit Task
</div>

<div class="card-body">

<?php if($errors): ?>
<div class="alert alert-danger">
<ul class="mb-0">
<?php foreach($errors as $e) echo "<li>$e</li>"; ?>
</ul>
</div>
<?php endif; ?>

<form method="post" class="row g-3">

<div class="col-md-6">
<label class="form-label">Project</label>
<select name="project_id" class="form-select">
<?php while($p=mysqli_fetch_assoc($projects)){ ?>
<option value="<?= $p['id'] ?>" <?= $task['project_id']==$p['id']?'selected':'' ?>>
<?= htmlspecialchars($p['project_name']) ?>
</option>
<?php } ?>
</select>
</div>

<div class="col-md-6">
<label class="form-label">Agent</label>
<select name="assigned_to" class="form-select">
<?php while($a=mysqli_fetch_assoc($agents)){ ?>
<option value="<?= $a['id'] ?>" <?= $task['assigned_to']==$a['id']?'selected':'' ?>>
<?= htmlspecialchars($a['name']) ?>
</option>
<?php } ?>
</select>
</div>

<div class="col-md-6">
<label class="form-label">Task Title</label>
<input type="text" name="title" class="form-control" value="<?= htmlspecialchars($task['title']) ?>">
</div>

<div class="col-md-6">
<label class="form-label">Deadline</label>
<input type="date" name="deadline" class="form-control" value="<?= $task['deadline'] ?>">
</div>

<div class="col-12">
<label class="form-label">Description</label>
<textarea name="description" rows="3" class="form-control"><?= htmlspecialchars($task['description']) ?></textarea>
</div>

<div class="col-md-6">
<label class="form-label">Priority</label>
<select name="priority" class="form-select">
<option <?= $task['priority']=='Low'?'selected':'' ?>>Low</option>
<option <?= $task['priority']=='Medium'?'selected':'' ?>>Medium</option>
<option <?= $task['priority']=='High'?'selected':'' ?>>High</option>
</select>
</div>

<div class="col-md-6">
<label class="form-label">Status</label>
<select name="status" class="form-select">
<option <?= $task['status']=='To Do'?'selected':'' ?>>To Do</option>
<option <?= $task['status']=='In Progress'?'selected':'' ?>>In Progress</option>
<option <?= $task['status']=='Done'?'selected':'' ?>>Done</option>
</select>
</div>

<div class="col-12 text-end">
<a href="tasks.php" class="btn btn-secondary">Cancel</a>
<button class="btn btn-primary">Update Task</button>
</div>

</form>
</div>
</div>
</div>

</body>
</html>
