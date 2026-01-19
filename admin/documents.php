<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../login.php");
    exit;
}

include '../includes/db.php';

// Handle file upload
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['document'])) {
    $file = $_FILES['document'];
    $file_name_input = mysqli_real_escape_string($conn, $_POST['file_name']);
    $uploaded_by = $_SESSION['user_id'];

    if (empty($file_name_input)) $errors[] = "Document name is required.";
    if ($file['error'] !== 0) $errors[] = "Error uploading file.";

    if (empty($errors)) {
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid().'_'.time().'.'.$ext;
        $target = '../uploads/'.$filename;

        if (move_uploaded_file($file['tmp_name'], $target)) {
            $file_size = $file['size'];
            $file_type = $file['type'];
            mysqli_query($conn, "INSERT INTO documents (user_id, file_name, file_path, file_type, file_size, version, uploaded_at) 
                                 VALUES ('$uploaded_by','$file_name_input','$filename','$file_type','$file_size',1,NOW())");
            $success = "Document uploaded successfully.";
        } else {
            $errors[] = "Failed to move uploaded file.";
        }
    }
}

// Handle deletion
if (isset($_GET['delete_id'])) {
    $id = intval($_GET['delete_id']);
    $doc = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM documents WHERE id=$id"));
    if ($doc) {
        unlink('../uploads/'.$doc['file_path']); // delete file
        mysqli_query($conn, "DELETE FROM documents WHERE id=$id");
        $success = "Document deleted successfully.";
    } else {
        $errors[] = "Document not found.";
    }
}

// Fetch documents with uploader name
$documents = mysqli_query($conn, "
    SELECT d.*, u.name AS uploader 
    FROM documents d
    JOIN users u ON d.user_id = u.id
    ORDER BY d.uploaded_at DESC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Documents - Admin</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
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
.main{margin-left:70px;padding:15px;transition:.3s}
.sidebar:hover ~ .main{margin-left:230px}

/* TOPBAR */
.topbar{
    background:#fff;
    padding:12px 20px;
    display:flex;
    justify-content:space-between;
    align-items:center;
    border-bottom:1px solid #ddd;
}
.page-title{font-size:22px}

/* CARD */
.card{border-radius:12px;box-shadow:0 5px 15px rgba(0,0,0,0.05);}
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
    <a href="../logout.php"><i class="bi bi-box-arrow-right"></i><span>Logout</span></a>
</div>

<div class="main">
    <div class="topbar mb-4">
        <div class="page-title">Documents</div>
        <div><i class="bi bi-person"></i> <?= $_SESSION['user_name'] ?></div>
    </div>

    <!-- UPLOAD FORM -->
    <div class="card mb-4">
        <div class="card-body">
            <?php if(!empty($errors)): ?>
                <div class="alert alert-danger"><?php foreach($errors as $e) echo $e.'<br>'; ?></div>
            <?php endif; ?>
            <?php if(!empty($success)): ?>
                <div class="alert alert-success"><?= $success ?></div>
            <?php endif; ?>

            <form method="post" enctype="multipart/form-data" class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Document Name</label>
                    <input type="text" name="file_name" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Choose File</label>
                    <input type="file" name="document" class="form-control" required>
                </div>
                <div class="col-12 text-end">
                    <button type="submit" class="btn btn-primary">Upload</button>
                </div>
            </form>
        </div>
    </div>

    <!-- DOCUMENTS TABLE -->
    <div class="card">
        <div class="card-body">
            <table class="table table-bordered table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Uploaded By</th>
                        <th>File</th>
                        <th>Uploaded At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if(mysqli_num_rows($documents) > 0): $i=1; while($doc=mysqli_fetch_assoc($documents)): ?>
                    <tr>
                        <td><?= $i++ ?></td>
                        <td><?= htmlspecialchars($doc['file_name']) ?></td>
                        <td><?= htmlspecialchars($doc['uploader']) ?></td>
                        <td><a href="../uploads/<?= $doc['file_path'] ?>" target="_blank"><i class="bi bi-file-earmark-text"></i> View</a></td>
                        <td><?= $doc['uploaded_at'] ?></td>
                        <td>
                            <a href="../uploads/<?= $doc['file_path'] ?>" class="btn btn-sm btn-success" download>Download</a>
                            <a href="documents.php?delete_id=<?= $doc['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this document?')">Delete</a>
                        </td>
                    </tr>
                <?php endwhile; else: ?>
                    <tr><td colspan="6" class="text-center text-muted">No documents found.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

</body>
</html>
