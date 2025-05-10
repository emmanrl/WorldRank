
<?php
session_start();
require_once('../includes/db_connection.php');

if(isset($_POST['submit'])) {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    
    // Check if email exists
    $query = "SELECT * FROM users WHERE email = '$email'";
    $result = mysqli_query($conn, $query);
    
    if(mysqli_num_rows($result) > 0) {
        // Generate verification code
        $verification_code = rand(100000, 999999);
        
        // Store code in session
        $_SESSION['reset_code'] = $verification_code;
        $_SESSION['reset_email'] = $email;
        
        // Email configuration
        $to = $email;
        $subject = "Password Reset Verification";
        $message = "Your verification code is: " . $verification_code;
        $message .= "\n\nClick here to reset your password: ";
        $message .= "http://" . $_SERVER['HTTP_HOST'] . "/auth/reset_password.php?code=" . $verification_code;
        $headers = "From: noreply@yourwebsite.com";
        
        // Send email
        if(mail($to, $subject, $message, $headers)) {
            $_SESSION['message'] = "Verification code sent to your email";
            header("Location: verify_reset_code.php");
            exit();
        } else {
            $error = "Failed to send verification code";
        }
    } else {
        $error = "Email address not found";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="container">
        <div class="form-container">
            <h2>Forgot Password</h2>
            <?php if(isset($error)): ?>
                <div class="error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" name="email" id="email" required>
                </div>
                <button type="submit" name="submit" class="btn">Send Reset Link</button>
            </form>
            
            <div class="links">
                <a href="login.php">Back to Login</a>
            </div>
        </div>
    </div>
</body>
</html>
