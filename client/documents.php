<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

include '../includes/db.php';

$user_id = $_SESSION['user_id'];
$errors = [];
$success = "";

/* Handle file upload */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['document'])) {
    $file      = $_FILES['document'];
    $projectId = intval($_POST['project_id']); // if you want to link file to a project

    if ($file['error'] === 0) {
        $fileName = basename($file['name']);
        $fileType = pathinfo($fileName, PATHINFO_EXTENSION);
        $fileSize = $file['size'];
        $uploadDir = "../uploads/";

        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

        // Create unique file path
        $filePath = time() . "_" . preg_replace('/[^a-zA-Z0-9_\-\.]/', '_', $fileName);
        $fullPath = $uploadDir . $filePath;

        if (move_uploaded_file($file['tmp_name'], $fullPath)) {
            // Insert into database
            $version = 1;
            mysqli_query($conn, "INSERT INTO documents 
                (user_id, file_name, file_path, file_type, file_size, version) 
                VALUES ('$user_id','$fileName','$filePath','$fileType','$fileSize','$version')");

            $success = "File uploaded successfully.";
        } else {
            $errors[] = "Failed to move uploaded file.";
        }
    } else {
        $errors[] = "Error uploading file. Code: ".$file['error'];
    }
}

/* Fetch documents uploaded by this user */
$documents = mysqli_query($conn, "
    SELECT * FROM documents
    WHERE user_id = $user_id
    ORDER BY id DESC
");

/* Fetch projects for linking (optional) */
$projects = mysqli_query($conn, "SELECT id, project_name FROM projects WHERE created_by = $user_id");
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Documents</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body{margin:0;font-family:'Segoe UI';background:#f3f4f6;color:#111827;}
.sidebar{position:fixed;left:0;top:0;height:100vh;width:70px;background:#111827;transition:.3s;overflow:hidden;z-index:1000;}
.sidebar:hover{width:220px;}
.sidebar a{display:flex;align-items:center;color:#cbd5e1;text-decoration:none;padding:15px;gap:15px;white-space:nowrap;border-radius:8px;margin:5px 8px;}
.sidebar a i{font-size:20px;min-width:30px;text-align:center;}
.sidebar a span{opacity:0;transition:0.3s;}
.sidebar:hover a span{opacity:1;}
.sidebar a:hover{background:#2563eb;color:#fff;}
.main{margin-left:70px;transition:.3s;}
.sidebar:hover ~ .main{margin-left:220px;}
.main-content{padding:30px;}
.header{display:flex;justify-content:space-between;align-items:center;background:#fff;padding:15px 25px;border-radius:12px;box-shadow:0 2px 10px rgba(0,0,0,0.05);margin-bottom:25px;}
.header-left h3{margin:0;font-size:24px;font-weight:700;}
.header-right{display:flex;align-items:center;gap:15px;font-weight:600;}
.table-box{background:#fff;padding:20px;border-radius:12px;box-shadow:0 5px 15px rgba(0,0,0,0.08);margin-bottom:30px;}
.table-box h5{margin-bottom:15px;}
.table-box table td, .table-box table th{vertical-align:middle;}
.table-box table tr:nth-child(even){background:#f1f5f9;}
.table-box table tr:nth-child(odd){background:#ffffff;}
.table-box table th{background:#2563eb;color:#fff;}
</style>
</head>
<body>

<div class="sidebar">
    <a href="client_dashboard.php"><i class="bi bi-speedometer2"></i><span>Dashboard</span></a>
    <a href="projects.php"><i class="bi bi-folder"></i><span>Projects</span></a>
    <a href="tasks.php"><i class="bi bi-list-task"></i><span>Tasks</span></a>
    <a href="documents.php"><i class="bi bi-file-earmark-text"></i><span>Documents</span></a>
    <a href="profile.php"><i class="bi bi-person-circle"></i><span>My Profile</span></a>
    <a href="logout.php"><i class="bi bi-box-arrow-right"></i><span>Logout</span></a>
</div>

<div class="main">
<div class="header">
    <div class="header-left"><h3>Documents</h3></div>
    <div class="header-right"><?= $_SESSION['username'] ?? 'User'; ?></div>
</div>

<div class="main-content">

<?php if(!empty($errors)): ?>
<div class="alert alert-danger"><?= implode('<br>',$errors) ?></div>
<?php endif; ?>

<?php if($success): ?>
<div class="alert alert-success"><?= $success ?></div>
<?php endif; ?>

<!-- Upload Form -->
<div class="table-box mb-4">
    <h5>Upload Document</h5>
    <form method="POST" enctype="multipart/form-data" class="row g-3">
        <div class="col-md-6">
            <label class="form-label">Select File</label>
            <input type="file" name="document" class="form-control" required>
        </div>
        <div class="col-md-6">
            <label class="form-label">Link to Project (optional)</label>
            <select name="project_id" class="form-select">
                <option value="">-- None --</option>
                <?php while($p=mysqli_fetch_assoc($projects)): ?>
                    <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['project_name']) ?></option>
                <?php endwhile; ?>
            </select>
        </div>
        
        <div class="col-12 text-end">
            <button class="btn btn-primary">Upload</button>
        </div>
    </form>
</div>

<!-- Documents Table -->
<div class="table-box">
    <h5>My Documents</h5>
    <table class="table table-bordered table-hover">
        <thead>
            <tr>
                <th>#</th>
                <th>File Name</th>
                <th>Type</th>
                <th>Size</th>
                <th>Version</th>
                <th>Uploaded At</th>
                <th>Download</th>
            </tr>
        </thead>
        <tbody>
        <?php
        $i=1;
        mysqli_data_seek($documents,0); // reset pointer
        while($doc=mysqli_fetch_assoc($documents)):
            $size = number_format($doc['file_size']/1024,2).' KB';
        ?>
            <tr>
                <td><?= $i++ ?></td>
                <td><?= htmlspecialchars($doc['file_name']) ?></td>
                <td><?= htmlspecialchars($doc['file_type']) ?></td>
                <td><?= $size ?></td>
                <td><?= $doc['version'] ?></td>
                <td><?= $doc['uploaded_at'] ?></td>
                <td>
                    <a href="../uploads/<?= htmlspecialchars($doc['file_path']) ?>" class="btn btn-sm btn-primary" download>
                        <i class="bi bi-download"></i> Download
                    </a>
                </td>
            </tr>
        <?php endwhile; ?>
        <?php if(mysqli_num_rows($documents) == 0) echo "<tr><td colspan='7' class='text-center'>No documents uploaded.</td></tr>"; ?>
        </tbody>
    </table>
</div>

</div>
</div>

</body>
</html>
