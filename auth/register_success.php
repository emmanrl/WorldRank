<?php
session_start();

if (!isset($_SESSION['registration_success'])) {
    header('Location: register.php');
    exit();
}

$email = $_SESSION['registration_email'];
unset($_SESSION['registration_success']);
unset($_SESSION['registration_email']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Successful</title>
    <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,200..800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-stone-50 font-sans">
    <div class="min-h-screen flex items-center justify-center p-8">
        <div class="max-w-md w-full bg-white rounded-xl shadow-lg p-8 sm:p-10 text-center">
            <div class="w-20 h-20 bg-teal-100 rounded-full flex items-center justify-center mx-auto mb-6">
                <i class="fas fa-envelope-open-text text-teal-600 text-3xl"></i>
            </div>
            <h1 class="text-2xl font-bold text-stone-800 mb-4">Registration Successful!</h1>
            <p class="text-stone-600 mb-6">We've sent a verification email to <span class="font-medium"><?php echo htmlspecialchars($email); ?></span>. Please check your inbox and click the verification link to activate your account.</p>
            <div class="bg-stone-50 p-4 rounded-lg border border-stone-200 mb-6 text-left">
                <p class="text-sm text-stone-600"><i class="fas fa-info-circle text-teal-500 mr-2"></i> Can't find the email? Check your spam folder or <a href="resend_verification.php?email=<?php echo urlencode($email); ?>" class="text-teal-600 hover:text-teal-500">resend verification email</a>.</p>
            </div>
            <a href="login.php" class="inline-flex items-center px-4 py-2 bg-teal-600 text-white rounded-lg hover:bg-teal-700 transition">
                <i class="fas fa-sign-in-alt mr-2"></i> Go to Login
            </a>
        </div>
    </div>
</body>
</html>