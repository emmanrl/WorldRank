<?php
session_start();
require '../private/functions.php';

// Check if we have a registered email
if (empty($_SESSION['registration_email'])) {
    $_SESSION['error'] = 'No email found to resend verification';
    header('Location: register.php');
    exit();
}

$email = $_SESSION['registration_email'];
$user = get_user_by_email($email);

if (!$user) {
    $_SESSION['error'] = 'Account not found';
    header('Location: register.php');
    exit();
}

if ($user['verified_at']) {
    $_SESSION['error'] = 'Email already verified';
    header('Location: login.php');
    exit();
}

// Generate new token and resend
$new_token = generate_verification_token();
update_verification_token($user['id'], $new_token);

if (send_verification_email($email, $user['name'], $new_token)) {
    $_SESSION['resend_success'] = true;
    header('Location: register_success.php');
} else {
    $_SESSION['error'] = 'Failed to resend verification email';
    header('Location: register_success.php');
}
exit();