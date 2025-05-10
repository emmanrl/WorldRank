<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ./auth/login');
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Dashboard</title>
</head>
<body>
    <h1>Welcome, <?php echo htmlspecialchars($_SESSION['user']['name']); ?></h1>
    <a href="auth/logout">Logout</a>
</body>
</html>