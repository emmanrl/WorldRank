<?php
session_start();
require '../private/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    
    if (empty($email)) {
        $_SESSION['reset_error'] = 'Please enter your email address';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['reset_error'] = 'Please enter a valid email address';
    } else {
        $user = get_user_by_email($email);
        
        if (!$user) {
            $_SESSION['reset_error'] = 'No account found with this email address';
        } else {
            $token = generate_password_reset_token();
            store_password_reset_token($user['id'], $token);
            
            if (send_password_reset_email($email, $user['name'], $token)) {
                $_SESSION['reset_success'] = true;
                $_SESSION['reset_email'] = $email;
                header('Location: reset_password_sent.php');
                exit();
            } else {
                $_SESSION['reset_error'] = 'Failed to send password reset email';
            }
        }
    }
    
    header('Location: forgot_password.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - WorldRank</title>
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
            <h1 class="text-2xl font-bold text-stone-800 mb-2 text-center">Forgot Password?</h1>
            <p class="text-stone-600 mb-6 text-center">Enter your email to receive a password reset link</p>
            
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
                    <label for="email" class="block text-sm font-medium text-stone-700 mb-1">Email Address</label>
                    <input type="email" id="email" name="email" required
                           class="w-full px-4 py-2 border border-stone-300 rounded-lg focus:ring-teal-500 focus:border-teal-500"
                           placeholder="your@email.com">
                </div>
                
                <button type="submit" class="w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-teal-600 hover:bg-teal-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-500">
                    Send Reset Link
                </button>
            </form>
            
            <div class="mt-6 text-center">
                <a href="login.php" class="text-sm font-medium text-teal-600 hover:text-teal-500">
                    <i class="fas fa-arrow-left mr-1"></i> Back to Login
                </a>
            </div>
        </div>
    </div>
</body>
</html>