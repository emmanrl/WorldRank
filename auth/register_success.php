<?php
session_start();

if (!isset($_SESSION['registration_success'])) {
    header('Location: register.php');
    exit();
}

$email = $_SESSION['registration_email'];
// Don't unset the session yet - we might need it for resending
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <!-- [Keep your existing head content] -->
</head>
<body class="bg-stone-50 font-sans">
    <div class="min-h-screen flex items-center justify-center p-8">
        <div class="max-w-md w-full bg-white rounded-xl shadow-lg p-8 sm:p-10 text-center">
            <!-- [Keep your existing success markup] -->
            <div class="bg-stone-50 p-4 rounded-lg border border-stone-200 mb-6 text-left">
                <p class="text-sm text-stone-600">
                    <i class="fas fa-info-circle text-teal-500 mr-2"></i> 
                    Can't find the email? Check your spam folder or 
                    <a href="resend_verification.php" class="text-teal-600 hover:text-teal-500">
                        resend verification email
                    </a>.
                </p>
            </div>
            <!-- [Rest of your existing code] -->
        </div>
    </div>
</body>
</html>