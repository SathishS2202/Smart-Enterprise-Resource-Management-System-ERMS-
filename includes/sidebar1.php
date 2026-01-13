<style>
/* ===== SIDEBAR ===== */
.sidebar {
    position: fixed;
    left: 0;
    top: 0;
    width: 70px;
    height: 100vh;
    background: #111827;
    transition: width 0.3s ease;
    overflow: hidden;
    z-index: 1000;
}

.sidebar:hover {
    width: 220px;
}

.sidebar .logo {
    text-align: center;
    padding: 20px 0;
    color: #fff;
    font-size: 22px;
    font-weight: bold;
}

.sidebar a {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 14px 20px;
    color: #cbd5e1;
    text-decoration: none;
    white-space: nowrap;
    transition: background 0.2s;
}

.sidebar a i {
    font-size: 20px;
    min-width: 30px;
    text-align: center;
}

.sidebar a span {
    opacity: 0;
    transition: opacity 0.2s;
}

.sidebar:hover a span {
    opacity: 1;
}

.sidebar a:hover {
    background: #2563eb;
    color: #fff;
}
</style>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">

<div class="sidebar">
    <div class="logo">ERMS</div>

    <a href="dashboard.php">
        <i class="bi bi-speedometer2"></i>
        <span>Dashboard</span>
    </a>

    <a href="users.php">
        <i class="bi bi-people"></i>
        <span>Users</span>
    </a>

    <a href="projects.php">
        <i class="bi bi-folder"></i>
        <span>Projects</span>
    </a>

    <a href="tasks.php">
        <i class="bi bi-list-task"></i>
        <span>Tasks</span>
    </a>

    <a href="attendance.php">
        <i class="bi bi-calendar-check"></i>
        <span>Attendance</span>
    </a>

    <a href="documents.php">
        <i class="bi bi-file-earmark-text"></i>
        <span>Documents</span>
    </a>

    <a href="reports.php">
        <i class="bi bi-bar-chart"></i>
        <span>Reports</span>
    </a>

    <a href="../logout.php">
        <i class="bi bi-box-arrow-right"></i>
        <span>Logout</span>
    </a>
</div>
