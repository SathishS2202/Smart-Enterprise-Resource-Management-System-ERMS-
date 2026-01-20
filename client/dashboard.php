<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Client') {
    header("Location: ../auth/login.php");
    exit;
}

include '../includes/db.php';

$client_id = $_SESSION['user_id'];

/* Total Projects (Client) */

$totalProjects = mysqli_fetch_assoc(
    mysqli_query($conn,"
        SELECT COUNT(*) AS total
        FROM projects
        WHERE created_by = $client_id
    ")
)['total'];

$recentProjects = mysqli_query($conn,"
    SELECT * FROM projects
    WHERE created_by = $client_id
    ORDER BY id DESC
    LIMIT 5
");


/* Pending Tasks */
$pendingTasks = mysqli_fetch_assoc(
    mysqli_query($conn,"
        SELECT COUNT(*) AS total
        FROM tasks t
        JOIN projects p ON t.project_id = p.id
        WHERE p.created_by = $client_id
        AND t.status IN ('todo','pending')
    ")
)['total'];

/* Completed Tasks */
$completedTasks = mysqli_fetch_assoc(
    mysqli_query($conn,"
        SELECT COUNT(*) AS total
        FROM tasks t
        JOIN projects p ON t.project_id = p.id
        WHERE p.created_by = $client_id
        AND t.status = 'completed'
    ")
)['total'];

$recentTasks = mysqli_query($conn,"
    SELECT t.*, p.project_name
    FROM tasks t
    JOIN projects p ON t.project_id = p.id
    WHERE p.created_by = $client_id
    ORDER BY t.id DESC
    LIMIT 5
");

?>


<!DOCTYPE html>
<html>
<head>
    <title>Client Dashboard</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body{margin:0;font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif;background:#f3f4f6;color:#111827;}

        /* SIDEBAR */
        .sidebar{position:fixed;left:0;top:0;height:100vh;width:70px;background:#111827;transition:width 0.3s;overflow:hidden;z-index:1000;}
        .sidebar:hover{width:220px;}
        .sidebar a{display:flex;align-items:center;color:#cbd5e1;text-decoration:none;padding:15px;gap:15px;white-space:nowrap;border-radius:8px;margin:5px 8px;transition:0.2s;}
        .sidebar a i{font-size:20px;min-width:30px;text-align:center;}
        .sidebar a span{opacity:0;transition:opacity 0.3s;}
        .sidebar:hover a span{opacity:1;}
        .sidebar a:hover{background:#2563eb;color:#fff;}

        /* MAIN */
        .main{margin-left:70px;transition:margin-left 0.3s;}
        .sidebar:hover ~ .main{margin-left:220px;}
        .main-content{padding:30px;}

        /* HEADER */
        .header{display:flex;justify-content:space-between;align-items:center;background:#fff;padding:15px 25px;border-radius:12px;box-shadow:0 2px 10px rgba(0,0,0,0.05);margin-bottom:25px;}
        .header-left h3{margin:0;font-size:24px;font-weight:700;}
        .header-right{display:flex;align-items:center;gap:15px;font-weight:600;}
        .header-right i{font-size:20px;}

        /* CARDS */
        .cards{display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:20px;margin-bottom:30px;}
        .card{background:#fff;border-radius:12px;padding:20px;display:flex;flex-direction:column;align-items:center;box-shadow:0 5px 15px rgba(0,0,0,0.08);}
        .card h5{margin:0;font-size:14px;color:#6b7280;}
        .card span{font-size:24px;font-weight:700;margin-top:5px;}

        /* ICONS */
        .dashboard-icons{display:grid;grid-template-columns:repeat(4,1fr);gap:20px;margin-bottom:30px;}
        .icon-box{background:#fff;border-radius:12px;padding:25px 0;text-align:center;transition:0.3s;box-shadow:0 5px 15px rgba(0,0,0,0.08);text-decoration:none;color:#111827;}
        .icon-box i{font-size:40px;margin-bottom:12px;color:#2563eb;transition:0.3s;}
        .icon-box span{font-weight:600;display:block;}
        .icon-box:hover{background:#2563eb;color:#fff;transform:translateY(-5px);box-shadow:0 8px 20px rgba(0,0,0,0.12);}
        .icon-box:hover i{color:#fff;}

        /* TABLE BOX */
        .table-box{background:#fff;padding:20px;border-radius:12px;box-shadow:0 5px 15px rgba(0,0,0,0.08);margin-bottom:30px;}
        .table-box h5{margin-bottom:15px;}
        .table-box table td, .table-box table th{vertical-align:middle;}
        .table-box table tr:nth-child(even){background:#f1f5f9;}
        .table-box table tr:nth-child(odd){background:#ffffff;}
        .table-box table th{background:#2563eb;color:#fff;}

        @media(max-width:768px){.cards{grid-template-columns:repeat(auto-fit,minmax(150px,1fr));}}
    </style>
</head>
<body>

<div class="sidebar">
    <a href="client_dashboard.php"><i class="bi bi-speedometer2"></i><span>Dashboard</span></a>
    <a href="projects.php"><i class="bi bi-folder"></i><span>Projects</span></a>
    <a href="tasks.php"><i class="bi bi-list-task"></i><span>Tasks</span></a>
    <a href="documents.php"><i class="bi bi-file-earmark-text"></i><span>Documents</span></a>
    <a href="profile.php"><i class="bi bi-person-circle"></i><span>My Profile</span></a>
    <a href="../logout.php"><i class="bi bi-box-arrow-right"></i><span>Logout</span></a>
</div>

<div class="main">
    <div class="header">
        <div class="header-left"><h3>Client Dashboard</h3></div>
        <div class="header-right"><?= $_SESSION['username'] ?? 'Client'; ?></div>
    </div>

    <div class="main-content">
        <!-- Top Cards -->
        <div class="cards">
            <div class="card"><h5>Total Projects</h5><span><?= $totalProjects ?></span></div>
            <div class="card"><h5>Pending Tasks</h5><span><?= $pendingTasks ?></span></div>
            <div class="card"><h5>Completed Tasks</h5><span><?= $completedTasks ?></span></div>
        </div>

        <!-- Dashboard Icons -->
        <div class="dashboard-icons">
            <a href="projects.php" class="icon-box"><i class="bi bi-folder"></i><span>Projects</span></a>
            <a href="tasks.php" class="icon-box"><i class="bi bi-list-task"></i><span>Tasks</span></a>
            <a href="documents.php" class="icon-box"><i class="bi bi-file-earmark-text"></i><span>Documents</span></a>
            <a href="profile.php" class="icon-box"><i class="bi bi-person-circle"></i><span>My Profile</span></a>
        </div>

        <!-- Recent Projects -->
        <div class="table-box">
            <h5>Recent Projects</h5>
            <table class="table table-bordered table-hover">
                <thead>
                    <tr><th>Project</th><th>Status</th><th>Start Date</th><th>End Date</th></tr>
                </thead>
                <tbody>
                <?php
               $recentProjects = mysqli_query($conn,"
    SELECT * FROM projects
    WHERE created_by = $client_id
    ORDER BY id DESC
    LIMIT 5
");

                while($p = mysqli_fetch_assoc($recentProjects)){
                    $color = $p['status']=='Completed'?'#16a34a':($p['status']=='In Progress'?'#2563eb':'#f59e0b');
                    echo "<tr>
                            <td>".htmlspecialchars($p['project_name'])."</td>
                            <td style='color:$color;font-weight:600'>".$p['status']."</td>
                            <td>".$p['start_date']."</td>
                            <td>".$p['end_date']."</td>
                          </tr>";
                }
                ?>
                </tbody>
            </table>
        </div>

        <!-- Recent Tasks -->
        <div class="table-box">
            <h5>Recent Tasks</h5>
            <table class="table table-bordered table-hover">
                <thead>
                    <tr><th>Task</th><th>Project</th><th>Status</th><th>Due Date</th></tr>
                </thead>
                <tbody>
                <?php
                $recentTasks = mysqli_query($conn,"
    SELECT t.*, p.project_name
    FROM tasks t
    JOIN projects p ON t.project_id = p.id
    WHERE p.created_by = $client_id
    ORDER BY t.id DESC
    LIMIT 5
");

                while($t = mysqli_fetch_assoc($recentTasks)){
                    $color = $t['status']=='Done'?'#16a34a':($t['status']=='In Progress'?'#2563eb':'#f59e0b');
                    echo "<tr>
                            <td>".htmlspecialchars($t['task_name'])."</td>
                            <td>".htmlspecialchars($t['project_name'])."</td>
                            <td style='color:$color;font-weight:600'>".$t['status']."</td>
                            <td>".$t['due_date']."</td>
                          </tr>";
                }
                ?>
                </tbody>
            </table>
        </div>

    </div>
</div>

</body>
</html>
