<!DOCTYPE html>
<html>
<head>
    <title>Agent Dashboard</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">

    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f4f6f9;
        }

        /* ===== SIDEBAR ===== */
        .sidebar {
            position: fixed;
            width: 70px;
            height: 100vh;
            background: #111827;
            padding-top: 20px;
            transition: 0.3s;
            overflow: hidden;
        }

        .sidebar:hover {
            width: 220px;
        }

        .sidebar a {
            display: flex;
            align-items: center;
            color: #cbd5e1;
            text-decoration: none;
            padding: 15px;
            gap: 15px;
            white-space: nowrap;
        }

        .sidebar a i {
            font-size: 20px;
            min-width: 30px;
            text-align: center;
        }

        .sidebar a span {
            opacity: 0;
            transition: 0.2s;
        }

        .sidebar:hover a span {
            opacity: 1;
        }

        .sidebar a:hover {
            background: #2563eb;
            color: #fff;
        }

        /* ===== MAIN ===== */
        .main {
            margin-left: 70px;
            transition: 0.3s;
        }

        .sidebar:hover ~ .main {
            margin-left: 220px;
        }

        /* ===== HEADER ===== */
        .header {
            background: #fff;
            padding: 15px 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 1px 5px rgba(0,0,0,0.1);
        }

        /* ===== DASHBOARD CARDS ===== */
        .cards {
            padding: 25px;
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
        }

        .card {
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .card h2 {
            margin: 0;
            font-size: 28px;
        }

        .card p {
            margin: 0;
            color: #6b7280;
        }

        .card i {
            font-size: 35px;
            color: #2563eb;
        }

        /* ===== TABLE ===== */
        .table-box {
            margin: 0 25px 25px;
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        }
    </style>
</head>
<body>

<!-- SIDEBAR -->
<div class="sidebar">
    <a href="#"><i class="bi bi-speedometer2"></i><span>Dashboard</span></a>
    <a href="#"><i class="bi bi-list-task"></i><span>Tasks</span></a>
    <a href="#"><i class="bi bi-calendar-check"></i><span>Attendance</span></a>
    <a href="#"><i class="bi bi-folder"></i><span>Documents</span></a>
    <a href="#"><i class="bi bi-box-arrow-right"></i><span>Logout</span></a>
</div>

<!-- MAIN -->
<div class="main">

    <!-- HEADER -->
    <div class="header">
        <h3>Agent Dashboard</h3>
        <div>
            <i class="bi bi-bell"></i> Agent
        </div>
    </div>

    <!-- CARDS -->
    <div class="cards">
        <div class="card">
            <div>
                <p>Total Tasks</p>
                <h2>12</h2>
            </div>
            <i class="bi bi-list-check"></i>
        </div>

        <div class="card">
            <div>
                <p>Completed</p>
                <h2>7</h2>
            </div>
            <i class="bi bi-check-circle"></i>
        </div>

        <div class="card">
            <div>
                <p>Pending</p>
                <h2>5</h2>
            </div>
            <i class="bi bi-hourglass"></i>
        </div>

        <div class="card">
            <div>
                <p>Documents</p>
                <h2>9</h2>
            </div>
            <i class="bi bi-folder2-open"></i>
        </div>
    </div>

    <!-- TABLE -->
    <div class="table-box">
        <h4>Recent Tasks</h4>
        <table width="100%" cellpadding="10">
            <tr>
                <th>Task</th>
                <th>Status</th>
                <th>Deadline</th>
            </tr>
            <tr>
                <td>Client Report</td>
                <td>Pending</td>
                <td>15 Jan</td>
            </tr>
        </table>
    </div>

</div>

</body>
</html>
