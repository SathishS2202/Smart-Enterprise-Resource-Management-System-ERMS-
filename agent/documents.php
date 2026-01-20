<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Agent') {
   header("Location: ../auth/login.php");
    exit;
}

include '../includes/db.php';

$agent_id = $_SESSION['user_id'];
$msg = "";

/* ================= UPLOAD DOCUMENT ================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!empty($_FILES['document']['name'])) {

        $fileName = basename($_FILES['document']['name']);
        $fileType = pathinfo($fileName, PATHINFO_EXTENSION);
        $fileSize = round($_FILES['document']['size'] / 1024); // KB

        $newName = time() . "_" . $fileName;
        $uploadDir = "../uploads/documents/";
        $filePath = $uploadDir . $newName;

        if (move_uploaded_file($_FILES['document']['tmp_name'], $filePath)) {
            mysqli_query($conn, "
                INSERT INTO documents (user_id, file_name, file_path, file_type, file_size, version)
                VALUES ($agent_id, '$fileName', '$filePath', '$fileType', '$fileSize', 1)
            ");
            $msg = "Document uploaded successfully";
        } else {
            $msg = "Upload failed";
        }
    }
}

/* ================= FETCH DOCUMENTS ================= */
$documents = mysqli_query($conn, "
    SELECT * FROM documents 
    WHERE user_id = $agent_id 
    ORDER BY uploaded_at DESC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>My Documents</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
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
.main{margin-left:70px;padding:20px;transition:.3s}
.sidebar:hover ~ .main{margin-left:230px}

.topbar{
    background:#fff;padding:12px 20px;
    display:flex;justify-content:space-between;
    align-items:center;border-bottom:1px solid #ddd;
    margin-bottom:20px
}

.card{
    border-radius:12px;
    box-shadow:0 4px 12px rgba(0,0,0,.08)
}
.table thead th{background:#f8fafc}
</style>
</head>

<body>

<!-- SIDEBAR -->
<div class="sidebar">
    <a href="dashboard.php"><i class="bi bi-speedometer2"></i><span>Dashboard</span></a>
    <a href="projects.php"><i class="bi bi-folder"></i><span>Projects</span></a>
    <a href="tasks.php"><i class="bi bi-list-task"></i><span>Tasks</span></a>
    <a href="attendance.php"><i class="bi bi-calendar-check"></i><span>Attendance</span></a>
    <a href="documents.php"><i class="bi bi-file-earmark-text"></i><span>Documents</span></a>
    <a href="reports.php"><i class="bi bi-bar-chart"></i><span>Reports</span></a>
    <a href="profile.php"><i class="bi bi-person-circle"></i><span>Profile</span></a>
    <a href="logout.php"><i class="bi bi-box-arrow-right"></i><span>Logout</span></a>
</div>

<div class="main">

<div class="topbar">
    <div class="page-title fs-4">My Documents</div>
    <div><i class="bi bi-person"></i> <?= $_SESSION['user_name'] ?></div>
</div>

<?php if($msg): ?>
<div class="alert alert-success"><?= $msg ?></div>
<?php endif; ?>

<!-- UPLOAD CARD -->
<div class="card p-4 mb-4">
    <h5 class="mb-3">Upload Document</h5>
    <form method="post" enctype="multipart/form-data" class="row g-3">
        <div class="col-md-8">
            <input type="file" name="document" class="form-control" required>
        </div>
        <div class="col-md-4">
            <button class="btn btn-primary w-100">
                <i class="bi bi-upload"></i> Upload
            </button>
        </div>
    </form>
</div>

<!-- DOCUMENT LIST -->
<div class="card p-4">
<h5 class="mb-3">Uploaded Documents</h5>

<div class="table-responsive">
<table class="table table-bordered table-hover align-middle">
<thead>
<tr>
    <th>#</th>
    <th>File Name</th>
    <th>Type</th>
    <th>Size (KB)</th>
    <th>Version</th>
    <th>Uploaded At</th>
    <th>Action</th>
</tr>
</thead>
<tbody>
<?php
if(mysqli_num_rows($documents)>0):
$i=1;
while($d=mysqli_fetch_assoc($documents)):
?>
<tr>
    <td><?= $i++ ?></td>
    <td><?= htmlspecialchars($d['file_name']) ?></td>
    <td><?= strtoupper($d['file_type']) ?></td>
    <td><?= $d['file_size'] ?></td>
    <td>v<?= $d['version'] ?></td>
    <td><?= $d['uploaded_at'] ?></td>
    <td>
        <a href="<?= $d['file_path'] ?>" target="_blank" class="btn btn-sm btn-outline-primary">
            <i class="bi bi-download"></i> Download
        </a>
    </td>
</tr>
<?php endwhile; else: ?>
<tr>
<td colspan="7" class="text-center text-muted">No documents uploaded</td>
</tr>
<?php endif; ?>
</tbody>
</table>
</div>
</div>

</div>
</body>
</html>
