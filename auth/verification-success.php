<?php
session_start();

// Check if verification was successful
if (!isset($_SESSION['verification_success']) || !$_SESSION['verification_success']) {
    header('Location: login.php');
    exit();
}
// Get user data from session
$name = htmlspecialchars($_SESSION['user_name'] ?? '');
$email = htmlspecialchars($_SESSION['user_email'] ?? '');

// Clear the verification flag to prevent reload exploits
unset($_SESSION['verification_success']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verified Successfully - WorldRank</title>
    <style>
        body {
            font-family: 'Bricolage Grotesque', Arial, sans-serif;
            background-color: #f5f7fa;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            color: #333;
        }
        .verification-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            padding: 40px;
            max-width: 600px;
            width: 90%;
            text-align: center;
        }
        .success-icon {
            color: #10b981;
            font-size: 72px;
            margin-bottom: 20px;
        }
        h1 {
            color: #0d9488;
            margin-bottom: 20px;
        }
        .user-info {
            background: #f0fdf4;
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
            border-left: 4px solid #10b981;
        }
        .btn {
            display: inline-block;
            background-color: #0d9488;
            color: white;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 6px;
            font-weight: bold;
            margin-top: 20px;
            transition: background-color 0.3s;
        }
        .btn:hover {
            background-color: #0f766e;
        }
        .additional-options {
            margin-top: 30px;
            font-size: 14px;
            color: #64748b;
        }
    </style>
</head>
<body>
    <div class="verification-container">
        <div class="success-icon">✓</div>
        <h1>Email Verified Successfully!</h1>
        
        <?php if (!empty($name)): ?>
            <p>Welcome to WorldRank, <strong><?= $name ?></strong>!</p>
        <?php endif; ?>
        
        <div class="user-info">
            <p>Your email <strong><?= $email ?></strong> has been successfully verified.</p>
            <p>You can now access all features of your account.</p>
        </div>
        
        <a href="../index" class="btn">Go to Your Dashboard</a>
        
        <div class="additional-options">
            <p>Need help? <a href="../support">Contact our support team</a></p>
        </div>
    </div>
</body>
</html>