<?php
// ================= AUTH =================
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Agent') {
     header("Location: ../auth/login.php");
    exit;
}

include '../includes/db.php';

$agent_id = $_SESSION['user_id'];
$today = date('Y-m-d');
$officeStart = "09:30:00";

// ================= HANDLE CHECK IN / OUT =================
if (isset($_POST['action'])) {

    if ($_POST['action'] === 'checkin') {
        $check = mysqli_query($conn,
            "SELECT id FROM attendance 
             WHERE user_id=$agent_id AND attendance_date='$today'"
        );

        if (mysqli_num_rows($check) == 0) {
            mysqli_query($conn,
                "INSERT INTO attendance (user_id, check_in, attendance_date)
                 VALUES ($agent_id, NOW(), '$today')"
            );
        }
    }

    if ($_POST['action'] === 'checkout') {
        mysqli_query($conn,
            "UPDATE attendance 
             SET check_out = NOW()
             WHERE user_id=$agent_id AND attendance_date='$today'"
        );
    }

    header("Location: attendance.php");
    exit;
}

// ================= FETCH TODAY =================
$todayAttendance = mysqli_fetch_assoc(
    mysqli_query($conn,
        "SELECT * FROM attendance 
         WHERE user_id=$agent_id AND attendance_date='$today'"
    )
);

// ================= WORK DURATION & LATE =================
$workDuration = "0h 0m";
$isLate = false;

if ($todayAttendance && $todayAttendance['check_in']) {
    $checkIn = strtotime($todayAttendance['check_in']);

    if (date("H:i:s", $checkIn) > $officeStart) {
        $isLate = true;
    }

    if ($todayAttendance['check_out']) {
        $checkOut = strtotime($todayAttendance['check_out']);
        $diff = $checkOut - $checkIn;

        $hours = floor($diff / 3600);
        $minutes = floor(($diff % 3600) / 60);
        $workDuration = "{$hours}h {$minutes}m";
    }
}

// ================= MONTHLY SUMMARY =================
$month = date('Y-m');

$monthlyTotal = mysqli_fetch_assoc(
    mysqli_query($conn,
        "SELECT COUNT(*) total FROM attendance 
         WHERE user_id=$agent_id AND attendance_date LIKE '$month%'"
    )
);

$monthlyPresent = mysqli_fetch_assoc(
    mysqli_query($conn,
        "SELECT COUNT(*) present FROM attendance 
         WHERE user_id=$agent_id AND attendance_date LIKE '$month%'
         AND check_in IS NOT NULL"
    )
);

$attendancePercent = ($monthlyTotal['total'] > 0)
    ? round(($monthlyPresent['present'] / $monthlyTotal['total']) * 100)
    : 0;

// ================= HISTORY =================
$history = mysqli_query($conn,
    "SELECT * FROM attendance 
     WHERE user_id=$agent_id 
     ORDER BY attendance_date DESC 
     LIMIT 10"
);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Attendance</title>

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

/* CARDS */
.card{border-radius:14px;box-shadow:0 6px 18px rgba(0,0,0,.08)}
.progress{height:10px;border-radius:10px}
.progress-bar{border-radius:10px}

.badge-present{background:#10b981}
.badge-absent{background:#ef4444}
.badge-late{background:#f59e0b;color:#000}
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
    <a href="leave_requests.php"><i class="bi bi-file-earmark-text"></i><span>Leave Requests</span></a>
    <a href="logout.php"><i class="bi bi-box-arrow-right"></i><span>Logout</span></a>
</div>

<!-- MAIN -->
<div class="main">

<div class="topbar">
    <h5>Attendance</h5>
    <div><i class="bi bi-person"></i> <?= $_SESSION['user_name'] ?></div>
</div>

<!-- SUMMARY ROW -->
<div class="row g-3 mb-4">

<div class="col-md-4">
<div class="card p-4 text-center">
    <h6>Today Status</h6>
    <p><?= $today ?></p>

    <?php if($todayAttendance): ?>
        <p>In: <?= $todayAttendance['check_in'] ?? '-' ?></p>
        <p>Out: <?= $todayAttendance['check_out'] ?? '-' ?></p>

        <?php if($isLate): ?>
            <span class="badge badge-late">Late Check-In</span>
        <?php endif; ?>

    <?php else: ?>
        <span class="badge badge-absent">Not Checked In</span>
    <?php endif; ?>

    <form method="post" class="mt-3">
        <?php if(!$todayAttendance || !$todayAttendance['check_in']): ?>
            <button name="action" value="checkin" class="btn btn-primary w-100">Check In</button>
        <?php elseif(!$todayAttendance['check_out']): ?>
            <button name="action" value="checkout" class="btn btn-success w-100">Check Out</button>
        <?php endif; ?>
    </form>
</div>
</div>

<div class="col-md-4">
<div class="card p-4 text-center">
    <h6>Work Duration</h6>
    <h2><?= $workDuration ?></h2>

    <?php
    $progress = (!$todayAttendance) ? 0 :
        ($todayAttendance['check_in'] && !$todayAttendance['check_out'] ? 50 : 100);
    ?>

    <div class="progress mt-3">
        <div class="progress-bar bg-success" style="width:<?= $progress ?>%"></div>
    </div>
</div>
</div>

<div class="col-md-4">
<div class="card p-4 text-center">
    <h6>Monthly Attendance</h6>
    <h2><?= $attendancePercent ?>%</h2>
    <p><?= $monthlyPresent['present'] ?> / <?= $monthlyTotal['total'] ?> Days</p>
</div>
</div>

</div>

<!-- HISTORY -->
<div class="card p-4">
<h6>Last 10 Days</h6>
<table class="table table-hover align-middle">
<thead>
<tr>
<th>#</th>
<th>Date</th>
<th>In</th>
<th>Out</th>
<th>Status</th>
</tr>
</thead>
<tbody>
<?php $i=1; while($h=mysqli_fetch_assoc($history)):
$status = ($h['check_in'] && $h['check_out']) ? 'Present' : 'Absent';
$badge = ($status=='Present')?'badge-present':'badge-absent';
?>
<tr>
<td><?= $i++ ?></td>
<td><?= $h['attendance_date'] ?></td>
<td><?= $h['check_in'] ?? '-' ?></td>
<td><?= $h['check_out'] ?? '-' ?></td>
<td><span class="badge <?= $badge ?>"><?= $status ?></span></td>
</tr>
<?php endwhile; ?>
</tbody>
</table>
</div>

</div>
</body>
</html>
