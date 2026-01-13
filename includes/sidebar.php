<div class="col-md-2 bg-dark text-white vh-100 p-3">
    <h5 class="text-center mb-4">ERMS</h5>

    <ul class="nav flex-column">
        <li class="nav-item mb-2">
            <a class="nav-link text-white" href="dashboard.php">Dashboard</a>
        </li>

        <?php if ($_SESSION['role'] == 'Admin'): ?>
            <li class="nav-item mb-2">
                <a class="nav-link text-white" href="../admin/users.php">Users</a>
            </li>
        <?php endif; ?>

        <?php if ($_SESSION['role'] == 'Agent'): ?>
            <li class="nav-item mb-2">
                <a class="nav-link text-white" href="../tasks/tasks.php">Tasks</a>
            </li>
        <?php endif; ?>

        <?php if ($_SESSION['role'] == 'Client'): ?>
            <li class="nav-item mb-2">
                <a class="nav-link text-white" href="../projects/projects.php">Projects</a>
            </li>
        <?php endif; ?>

        <li class="nav-item mt-4">
            <a class="nav-link text-danger" href="../auth/logout.php">Logout</a>
        </li>
    </ul>
</div>
