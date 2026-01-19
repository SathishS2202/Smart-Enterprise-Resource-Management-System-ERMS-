<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../login.php");
    exit;
}

include '../includes/db.php';

// Validate project ID from GET
$project_id = intval($_GET['id'] ?? 0);
if ($project_id <= 0) {
    $_SESSION['project_msg'] = ['type' => 'error', 'text' => 'Invalid Project ID.'];
    header("Location: projects.php");
    exit;
}

// Check if project exists
$check = mysqli_query($conn, "SELECT id FROM projects WHERE id = $project_id LIMIT 1");
if(mysqli_num_rows($check) == 0){
    $_SESSION['project_msg'] = ['type' => 'error', 'text' => 'Project not found.'];
    header("Location: projects.php");
    exit;
}

// Delete the project
$delete = mysqli_query($conn, "DELETE FROM projects WHERE id = $project_id");

if($delete){
    $_SESSION['project_msg'] = ['type' => 'success', 'text' => 'Project deleted successfully.'];
} else {
    $_SESSION['project_msg'] = ['type' => 'error', 'text' => 'Error deleting project: ' . mysqli_error($conn)];
}

header("Location: projects.php");
exit;
?>
