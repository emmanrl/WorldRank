<?php
session_start();
require '../private/functions.php';

$token = $_GET['token'] ?? '';

if (empty($token)) {
    $_SESSION['verification_error'] = 'Invalid verification link';
    header('Location: login.php');
    exit();
}

$verification = verify_email($token);

if ($verification['success']) {
    $_SESSION['verification_success'] = true;
    $_SESSION['user_id'] = $verification['user_id'];
    header('Location: login.php');
} else {
    $_SESSION['verification_error'] = $verification['error'];
    header('Location: login.php');
}
exit();
?>