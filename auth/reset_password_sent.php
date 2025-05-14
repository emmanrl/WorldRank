<?php
session_start();

if (!isset($_SESSION['reset_success'])) {
    header('Location: forgot_password.php');
    exit();
}

$email = $_SESSION['reset_email'];
unset($_SESSION['reset_success']);
unset($_SESSION['reset_email']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Email Sent - WorldRank</title>
    <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,200..800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-stone-50 font-sans">
    <div class="min-h-screen flex items-center justify-center p-8">
        <div class="max-w-md w-full bg-white rounded-xl shadow-lg p-8 sm:p-10 text-center">
            <div class="w-20 h-20 bg-teal-100 rounded-full flex items-center justify-center mx-auto mb-6">
                <i class="fas fa-envelope text-teal-600 text-3xl"></i>
            </div>
            <h1 class="text-2xl font-bold text-stone-800 mb-4">Password Reset Email Sent!</h1>
            <p class="text-stone-600 mb-6">We've sent a password reset link to <span class="font-medium"><?= htmlspecialchars($email) ?></span>. Please check your inbox and follow the instructions.</p>
            <div class="bg-stone-50 p-4 rounded-lg border border-stone-200 mb-6 text-left">
                <p class="text-sm text-stone-600">
                    <i class="fas fa-info-circle text-teal-500 mr-2"></i> 
                    Can't find the email? Check your spam folder or 
                    <a href="forgot_password.php" class="text-teal-600 hover:text-teal-500">
                        try again
                    </a>.
                </p>
            </div>
            <a href="login.php" class="inline-flex items-center px-4 py-2 bg-teal-600 text-white rounded-lg hover:bg-teal-700 transition">
                <i class="fas fa-sign-in-alt mr-2"></i> Return to Login
            </a>
        </div>
    </div>
</body>
</html>