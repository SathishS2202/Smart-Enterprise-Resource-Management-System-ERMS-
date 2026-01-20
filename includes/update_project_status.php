<?php
session_start();
include '../includes/db.php';

// Only allow logged-in agents
if (!isset($_SESSION['user_id']) || $_SESSION['role']!=='Agent') {
    header("Location: ../auth/login.php"); exit;
}

// Check required GET parameters
if (!isset($_GET['id']) || !isset($_GET['status'])) {
    header("Location: projects.php"); exit;
}

$project_id = (int) $_GET['id'];
$status = mysqli_real_escape_string($conn, $_GET['status']);

// Update project status only for this agent
$update = mysqli_query($conn, "
    UPDATE projects 
    SET status='$status' 
    WHERE id=$project_id AND agent_id=".$_SESSION['user_id']."
");

if ($update) {
    $_SESSION['project_msg'] = [
        'type'=>'success',
        'text'=>"Project status updated to '$status' successfully!"
    ];
} else {
    $_SESSION['project_msg'] = [
        'type'=>'error',
        'text'=>"Database error: ".mysqli_error($conn)
    ];
}

header("Location: projects.php");
exit;
?>
