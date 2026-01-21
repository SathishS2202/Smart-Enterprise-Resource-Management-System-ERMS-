<?php
require_once '../includes/middleware.php';
allowOnly('Agent');

include '../includes/db.php';
$agent_id = $_SESSION['user_id'];

/* SUBMIT REQUEST */
if (isset($_POST['submit'])) {
    $type = $_POST['request_type'];
    $reason = mysqli_real_escape_string($conn, $_POST['reason']);

    if ($type === 'Leave') {
        mysqli_query($conn, "
            INSERT INTO leave_requests
            (user_id, request_type, from_date, to_date, reason, status)
            VALUES
            ($agent_id, 'Leave', '{$_POST['from_date']}', '{$_POST['to_date']}', '$reason', 'Pending')
        ");
    } else {
        mysqli_query($conn, "
            INSERT INTO leave_requests
            (user_id, request_type, permission_date, from_time, to_time, reason, status)
            VALUES
            ($agent_id, 'Permission', '{$_POST['permission_date']}', '{$_POST['from_time']}', '{$_POST['to_time']}', '$reason', 'Pending')
        ");
    }
    header("Location: leave_requests.php");
    exit;
}

$requests = mysqli_query($conn,"
    SELECT * FROM leave_requests
    WHERE user_id=$agent_id
    ORDER BY requested_at DESC
");
?>
<!DOCTYPE html>
<html>
<head>
<title>Leave Requests</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

<style>
body{margin:0;font-family:Segoe UI;background:#f3f4f6}

/* SIDEBAR */
.sidebar{position:fixed;left:0;top:0;height:100vh;width:70px;background:#111827;transition:.3s;overflow:hidden}
.sidebar:hover{width:220px}
.sidebar a{display:flex;align-items:center;padding:15px;margin:5px 8px;gap:15px;color:#cbd5e1;text-decoration:none;border-radius:8px}
.sidebar a:hover{background:#2563eb;color:#fff}
.sidebar i{font-size:20px;min-width:30px;text-align:center}
.sidebar span{opacity:0;transition:.3s}
.sidebar:hover span{opacity:1}

/* MAIN */
.main{margin-left:70px;padding:20px;transition:.3s}
.sidebar:hover ~ .main{margin-left:220px}

/* TOPBAR */
.topbar{background:#fff;padding:12px 20px;border-radius:12px;
display:flex;justify-content:space-between;align-items:center;
box-shadow:0 4px 10px rgba(0,0,0,.08);margin-bottom:20px}

.card{border-radius:14px;box-shadow:0 6px 18px rgba(0,0,0,.08)}

.badge-pending{background:#facc15;color:#000}
.badge-approved{background:#22c55e}
.badge-rejected{background:#ef4444}
</style>
</head>

<body>

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
    <h5>Leave Requests</h5>
    <div><i class="bi bi-person"></i> <?= $_SESSION['user_name'] ?></div>
</div>

<div class="card p-4 mb-4">
<form method="post">
<div class="row g-3">
<div class="col-md-4">
<select name="request_type" id="type" class="form-select" required>
<option value="">Select Type</option>
<option value="Leave">Leave</option>
<option value="Permission">Permission</option>
</select>
</div>

<div id="leave" class="col-md-8 d-none">
<input type="date" name="from_date" class="form-control mb-2">
<input type="date" name="to_date" class="form-control">
</div>

<div id="permission" class="col-md-8 d-none">
<input type="date" name="permission_date" class="form-control mb-2">
<input type="time" name="from_time" class="form-control mb-2">
<input type="time" name="to_time" class="form-control">
</div>

<div class="col-md-12">
<textarea name="reason" class="form-control" placeholder="Reason" required></textarea>
</div>

<div class="col-md-12 text-end">
<button name="submit" class="btn btn-primary">Submit</button>
</div>
</div>
</form>
</div>

<div class="card p-4">
<table class="table table-hover">
<thead>
<tr>
<th>Type</th>
<th>Details</th>
<th>Status</th>
<th>Admin Remark</th>
</tr>
</thead>
<tbody>
<?php while($r=mysqli_fetch_assoc($requests)):
$badge = $r['status']=='Approved'?'badge-approved':($r['status']=='Rejected'?'badge-rejected':'badge-pending');
?>
<tr>
<td><?= $r['request_type'] ?></td>
<td><?= $r['request_type']=='Leave'
    ? $r['from_date']." → ".$r['to_date']
    : $r['permission_date']." (".$r['from_time']."-".$r['to_time'].")" ?></td>
<td><span class="badge <?= $badge ?>"><?= $r['status'] ?></span></td>
<td><?= $r['admin_remark'] ?: '-' ?></td>
</tr>
<?php endwhile; ?>
</tbody>
</table>
</div>

</div>

<script>
document.getElementById('type').addEventListener('change',function(){
document.getElementById('leave').classList.add('d-none');
document.getElementById('permission').classList.add('d-none');
if(this.value==='Leave') document.getElementById('leave').classList.remove('d-none');
if(this.value==='Permission') document.getElementById('permission').classList.remove('d-none');
});
</script>

</body>
</html>
