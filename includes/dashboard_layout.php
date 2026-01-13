<?php
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title><?= $pageTitle ?></title>
    <link rel="stylesheet" href="../assets/css/dashboard.css">
</head>
<body>

<div class="wrapper">

    <!-- SIDEBAR -->
    <aside class="sidebar">
        <h2 class="logo">ERMS</h2>
        <p class="user"><?= $_SESSION['user_name'] ?></p>

        <ul>
            <?= $sidebarItems ?>
            <li><a href="../auth/logout.php">Sign Out</a></li>
        </ul>
    </aside>

    <!-- MAIN CONTENT -->
    <main class="main">
        <header class="topbar">
            <h3><?= $pageTitle ?></h3>
        </header>

        <section class="content">
            <?= $content ?>
        </section>
    </main>

</div>

</body>
</html>
