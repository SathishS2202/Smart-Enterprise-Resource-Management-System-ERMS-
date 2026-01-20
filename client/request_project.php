<?php
if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Client') {
    header("Location: ../auth/login.php");
    exit;
}

include '../includes/db.php';

$client_id = $_SESSION['user_id'];
$msg = '';

if (isset($_POST['send_request'])) {
    $name  = mysqli_real_escape_string($conn, $_POST['project_name']);
    $desc  = mysqli_real_escape_string($conn, $_POST['description']);

    if ($name != '') {
        mysqli_query($conn, "
            INSERT INTO projects (project_name, description, status, created_by)
            VALUES ('$name', '$desc', 'Pending', $client_id)
        ");
        $msg = "Project request sent to Admin.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Request Project</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4">

<h3>Request New Project</h3>

<?php if($msg): ?><div class="alert alert-success"><?= $msg ?></div><?php endif; ?>

<form method="post">
    <div class="mb-3">
        <label>Project Name</label>
        <input type="text" name="project_name" class="form-control" required>
    </div>

    <div class="mb-3">
        <label>Description</label>
        <textarea name="description" class="form-control"></textarea>
    </div>

    <button name="send_request" class="btn btn-primary">Send Request</button>
</form>

</body>
</html>
