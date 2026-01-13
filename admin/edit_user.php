<?php
session_start();
include '../includes/db.php';

$id = $_GET['id'];

$user = mysqli_fetch_assoc(
    mysqli_query($conn, "SELECT * FROM users WHERE id=$id")
);
?>

<h3>Edit User</h3>

<form method="POST" action="edit_user_process.php">
    <input type="hidden" name="id" value="<?= $user['id'] ?>">

    <input type="text" name="name" value="<?= $user['name'] ?>" required><br><br>
    <input type="email" name="email" value="<?= $user['email'] ?>" required><br><br>

    <select name="role_id" required>
        <option value="1" <?= $user['role_id']==1?'selected':'' ?>>Admin</option>
        <option value="2" <?= $user['role_id']==2?'selected':'' ?>>Agent</option>
        <option value="3" <?= $user['role_id']==3?'selected':'' ?>>Client</option>
    </select><br><br>

    <button type="submit">Update</button>
</form>
