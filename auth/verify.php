<?php
session_start();
require '../private/functions.php';

$token = $_GET['token'] ?? '';

if (empty($token)) {
    $_SESSION['verification_error'] = 'Invalid verification link. Please check your email for the correct verification URL.';
    header('Location: login.php');
    exit();
}

$verification = verify_email($token);

if ($verification['success']) {
    // Store verification success and user data
    $_SESSION['verification_success'] = true;
    $_SESSION['user_id'] = $verification['user_id'];
    $_SESSION['user_email'] = $verification['email'] ?? '';
    $_SESSION['user_name'] = $verification['name'] ?? '';
    
    // Redirect to a dedicated success page instead of login.php
    header('Location: verification-success.php');
} else {
    $_SESSION['verification_error'] = $verification['error'] ?? 'Email verification failed. Please try again or contact support.';
    header('Location: login.php');
}
exit();
?>