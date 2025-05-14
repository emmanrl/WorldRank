<?php
session_start();
require '../private/functions.php';

$token = $_GET['token'] ?? '';

if (empty($token)) {
    $_SESSION['reset_error'] = 'Invalid reset link';
    header('Location: forgot_password.php');
    exit();
}

// Verify token
$user_id = verify_password_reset_token($token);
if (!$user_id) {
    $_SESSION['reset_error'] = 'Invalid or expired reset link';
    header('Location: forgot_password.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    if (empty($password) || empty($confirm_password)) {
        $_SESSION['reset_error'] = 'Please fill in all fields';
    } elseif ($password !== $confirm_password) {
        $_SESSION['reset_error'] = 'Passwords do not match';
    } elseif (strlen($password) < 8) {
        $_SESSION['reset_error'] = 'Password must be at least 8 characters';
    } else {
        // Update password
        if (update_user_password($user_id, $password)) {
            // Invalidate the token after use
            invalidate_password_reset_token($token);
            $_SESSION['reset_success'] = 'Your password has been updated successfully';
            header('Location: login.php');
            exit();
        } else {
            $_SESSION['reset_error'] = 'Failed to update password';
        }
    }
    
    header('Location: reset_password.php?token=' . urlencode($token));
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - WorldRank</title>
    <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,200..800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-stone-50 font-sans">
    <div class="min-h-screen flex items-center justify-center p-8">
        <div class="max-w-md w-full bg-white rounded-xl shadow-lg p-8 sm:p-10">
            <div class="w-20 h-20 bg-teal-100 rounded-full flex items-center justify-center mx-auto mb-6">
                <i class="fas fa-key text-teal-600 text-3xl"></i>
            </div>
            <h1 class="text-2xl font-bold text-stone-800 mb-2 text-center">Reset Your Password</h1>
            <p class="text-stone-600 mb-6 text-center">Create a new password for your account</p>
            
            <?php if (isset($_SESSION['reset_error'])): ?>
                <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-exclamation-circle text-red-500"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-red-700"><?= htmlspecialchars($_SESSION['reset_error']) ?></p>
                        </div>
                    </div>
                </div>
                <?php unset($_SESSION['reset_error']); ?>
            <?php endif; ?>
            
            <form method="POST" class="space-y-6">
                <div>
                    <label for="password" class="block text-sm font-medium text-stone-700 mb-1">New Password</label>
                    <input type="password" id="password" name="password" required minlength="8"
                           class="w-full px-4 py-2 border border-stone-300 rounded-lg focus:ring-teal-500 focus:border-teal-500"
                           placeholder="At least 8 characters">
                </div>
                
                <div>
                    <label for="confirm_password" class="block text-sm font-medium text-stone-700 mb-1">Confirm Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" required minlength="8"
                           class="w-full px-4 py-2 border border-stone-300 rounded-lg focus:ring-teal-500 focus:border-teal-500"
                           placeholder="Confirm your password">
                </div>
                
                <button type="submit" class="w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-teal-600 hover:bg-teal-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-500">
                    Reset Password
                </button>
            </form>
        </div>
    </div>
</body>
</html>