<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../login.php");
    exit;
}

include '../includes/db.php';

$id = intval($_GET['id']);

$q = mysqli_query($conn, "
    SELECT 
        t.*,
        p.project_name,
        u.name AS agent_name
    FROM tasks t
    JOIN projects p ON t.project_id = p.id
    JOIN users u ON t.assigned_to = u.id
    WHERE t.id = $id
");

if(mysqli_num_rows($q) == 0){
    header("Location: tasks.php");
    exit;
}

$task = mysqli_fetch_assoc($q);
?>

<!DOCTYPE html>
<html>
<head>
<title>View Task</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body{background:#f3f4f6;font-family:Segoe UI}
.card{border-radius:10px}
.label{font-weight:600;color:#6b7280}
.value{font-size:15px}
</style>
</head>
<body>

<div class="container my-5">
<div class="card shadow-sm">
<div class="card-header bg-primary text-white">
View Task Details
</div>

<div class="card-body row g-3">

<div class="col-md-6">
<div class="label">Project</div>
<div class="value"><?= htmlspecialchars($task['project_name']) ?></div>
</div>

<div class="col-md-6">
<div class="label">Assigned Agent</div>
<div class="value"><?= htmlspecialchars($task['agent_name']) ?></div>
</div>

<div class="col-md-6">
<div class="label">Task Title</div>
<div class="value"><?= htmlspecialchars($task['title']) ?></div>
</div>

<div class="col-md-6">
<div class="label">Deadline</div>
<div class="value"><?= $task['deadline'] ?: '—' ?></div>
</div>

<div class="col-md-6">
<div class="label">Priority</div>
<div class="value"><?= $task['priority'] ?></div>
</div>

<div class="col-md-6">
<div class="label">Status</div>
<div class="value"><?= $task['status'] ?></div>
</div>

<div class="col-12">
<div class="label">Description</div>
<div class="value"><?= nl2br(htmlspecialchars($task['description'])) ?></div>
</div>

<div class="col-12 text-end mt-3">
<a href="tasks.php" class="btn btn-secondary">Back</a>
<a href="edit_task.php?id=<?= $task['id'] ?>" class="btn btn-primary">Edit</a>
</div>

</div>
</div>
</div>

</body>
</html>
