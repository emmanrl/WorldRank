<?php
session_start();
if (empty($_SESSION['registered_email'])) {
    header('Location: register.php');
    exit();
}

$email = $_SESSION['registered_email'];
unset($_SESSION['registered_email']); // Clear after use
?>

<!DOCTYPE html>
<html>
<head>
    <title>Verification Email Resent</title>
    <style>
        body { font-family: Arial, sans-serif; text-align: center; padding: 50px; }
        .success { color: green; font-size: 1.2em; margin: 20px 0; }
        .email { font-weight: bold; }
    </style>
</head>
<body>
    <h1>Verification Email Resent</h1>
    <div class="success">
        We've resent the verification email to <span class="email"><?= htmlspecialchars($email) ?></span>.
    </div>
    <p>Please check your inbox and spam folder.</p>
    <p>Didn't receive it? <a href="contact.php">Contact support</a></p>
    <p><a href="login.php">Return to login</a></p>
</body>
</html>