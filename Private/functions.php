<?php


require 'config.php'; // Database configuration

/**
 * Database connection
 */
function db_connect() {
    static $pdo = null;
    
    if ($pdo === null) {
        try {
            $pdo = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]
            );
        } catch (PDOException $e) {
            error_log("Database connection failed: " . $e->getMessage());
            throw new Exception("Database connection error");
        }
    }
    
    return $pdo;
}

/**
 * Register a new user
 */
function register_user($name, $email, $password, $country, $age, $gender) {
    $pdo = db_connect();
    
    try {
        // Check if email already exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        
        if ($stmt->fetch()) {
            return ['success' => false, 'error' => 'Email already registered'];
        }
        
        // Generate verification token
        $verification_token = bin2hex(random_bytes(32));
        $verification_expires = date('Y-m-d H:i:s', strtotime('+24 hours'));
        
        // Insert new user
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password, country, age, gender, verification_token, verification_expires, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 0)");
        $stmt->execute([
            $name,
            $email,
            password_hash($password, PASSWORD_DEFAULT),
            $country,
            $age,
            $gender,
            $verification_token,
            $verification_expires
        ]);
        
        $user_id = $pdo->lastInsertId();
        
        return [
            'success' => true,
            'user_id' => $user_id,
            'verification_token' => $verification_token
        ];
    } catch (PDOException $e) {
        error_log("Registration error: " . $e->getMessage());
        return ['success' => false, 'error' => 'Registration failed'];
    }
}

/**
 * Send verification email
 */

function send_verification_email($email, $name, $token) {
    // Include PHPMailer files
    require_once 'PHPMailer/src/Exception.php';
    require_once 'PHPMailer/src/PHPMailer.php';
    require_once 'PHPMailer/src/SMTP.php';
    
    $verification_url = /*BASE_URL .*/ "http://localhost/worldrank/auth/verify.php?token=" . urlencode($token);
    $subject = "Verify Your Email Address - WorldRank";
    
    // HTML Email Template
    $message = <<<HTML
    <html>
    <head>
        <title>Email Verification</title>
        <style>
            body { 
                font-family: 'Bricolage Grotesque', Arial, sans-serif; 
                line-height: 1.6;
                color: #333;
                background-color: #f5f5f5;
                margin: 0;
                padding: 0;
            }
            .container { 
                max-width: 600px; 
                margin: 20px auto; 
                padding: 30px;
                background: white;
                border-radius: 8px;
                box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            }
            .button { 
                display: inline-block; 
                padding: 12px 25px; 
                background-color: #0d9488; 
                color: white; 
                text-decoration: none; 
                border-radius: 5px;
                font-weight: bold;
                margin: 15px 0;
            }
            .footer {
                margin-top: 30px;
                padding-top: 20px;
                border-top: 1px solid #eee;
                font-size: 12px;
                color: #777;
            }
        </style>
    </head>
    <body>
        <div class='container'>
            <h2>Welcome to WorldRank, $name!</h2>
            <p>Thank you for registering. Please verify your email address to activate your account and start competing on the leaderboard.</p>
            <p><a href='$verification_url' class='button'>Verify Email</a></p>
            <p>Or copy this link to your browser:<br>
            <code style="word-break:break-all;">$verification_url</code></p>
            <p>This link will expire in 24 hours.</p>
            
            <div class="footer">
                <p>If you didn't request this email, please ignore it.</p>
                <p>&copy; " . date('Y') . " WorldRank. All rights reserved.</p>
            </div>
        </div>
    </body>
    </html>
    HTML;
    
    // Plain text fallback
    $plain_message = "Welcome to WorldRank, $name!\n\n"
                   . "Thank you for registering. Please verify your email address by visiting this link:\n"
                   . "$verification_url\n\n"
                   . "This link will expire in 24 hours.\n\n"
                   . "If you didn't request this email, please ignore it.";
    
    $mail = new PHPMailer\PHPMailer\PHPMailer(true);
    
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = 'mail.emmanrl.xyz';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'info@emmanrl.xyz';
        $mail->Password   = '@TestPass12';
        $mail->SMTPSecure = 'ssl';  // Use 'tls' if using port 587
        $mail->Port       = 465;     // Use 587 for TLS
        
        // Enable debugging if needed (0 = off, 1 = client messages, 2 = client and server messages)
        $mail->SMTPDebug = 0;
        
        // Recipients
        $mail->setFrom('info@emmanrl.xyz', 'WorldRank');
        $mail->addAddress($email, $name);
        $mail->addReplyTo('support@emmanrl.xyz', 'WorldRank Support');
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $message;
        $mail->AltBody = $plain_message;
        
        // Additional headers for email clients
        $mail->addCustomHeader('X-Mailer', 'PHPMailer');
        $mail->addCustomHeader('Precedence', 'bulk');
        
        $mail->send();
        return true;
    } catch (PHPMailer\PHPMailer\Exception $e) {
        // Log the full error for debugging
        error_log("Email sending failed to $email. Error: " . $e->getMessage());
        error_log("PHPMailer Debug: " . $mail->ErrorInfo);
        return false;
    }
}

/**
 * send emails
 */



/**
 * Verify user email
 */
function verify_email($token) {
    $pdo = db_connect();
    
    try {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE verification_token = ? AND verification_expires > NOW()");
        $stmt->execute([$token]);
        $user = $stmt->fetch();
        
        if ($user) {
            $stmt = $pdo->prepare("UPDATE users SET is_active = 1, verification_token = NULL, verification_expires = NULL WHERE id = ?");
            $stmt->execute([$user['id']]);
            return ['success' => true, 'user_id' => $user['id']];
        }
        
        return ['success' => false, 'error' => 'Invalid or expired token'];
    } catch (PDOException $e) {
        error_log("Verification error: " . $e->getMessage());
        return ['success' => false, 'error' => 'Verification failed'];
    }
}


function get_user_by_email($email) {
    // Example implementation - replace with your actual database query
    $pdo = db_connect();
    
    $stmt = $pdo->prepare("SELECT id, name, email, verified_at FROM users WHERE email = ?");
    $stmt->execute([$email]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function generate_verification_token() {
    // Generate a secure random token
    return bin2hex(random_bytes(32));
}

function update_verification_token($user_id, $token) {
    // Update the user's verification token in database
    $pdo = db_connect();
        $stmt = $pdo->prepare("UPDATE users SET verification_token = ? WHERE id = ?");
    return $stmt->execute([$token, $user_id]);
}


/**
 * Login user with credentials
 */
function login_user($email, $password, $remember = false) {
    $pdo = db_connect();
    
    try {
        $stmt = $pdo->prepare("SELECT id, name, email, password, is_active, login_attempts, last_login FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if (!$user) {
            return ['success' => false, 'error' => 'Invalid credentials'];
        }
        
        // Check if account is locked
        if ($user['login_attempts'] >= MAX_LOGIN_ATTEMPTS) {
            return ['success' => false, 'error' => 'Account locked. Please contact support.'];
        }
        
        // Check if account is active
        if (!$user['is_active']) {
            return ['success' => false, 'error' => 'Account inactive. Please contact support.'];
        }
        
        if (password_verify($password, $user['password'])) {
            // Reset login attempts on successful login
            $stmt = $pdo->prepare("UPDATE users SET login_attempts = 0, last_login = NOW() WHERE id = ?");
            $stmt->execute([$user['id']]);
            
            // Generate session token
            $session_token = bin2hex(random_bytes(32));
            
            // Store session in database
            $stmt = $pdo->prepare("INSERT INTO user_sessions (user_id, session_token, ip_address, user_agent, expires_at) VALUES (?, ?, ?, ?, DATE_ADD(NOW(), INTERVAL 2 HOUR))");
            $stmt->execute([
                $user['id'],
                $session_token,
                $_SERVER['REMOTE_ADDR'],
                $_SERVER['HTTP_USER_AGENT']
            ]);
            
            // Set remember token if requested
            if ($remember) {
                $remember_token = bin2hex(random_bytes(32));
                $expires = time() + (30 * 24 * 60 * 60); // 30 days
                
                $stmt = $pdo->prepare("UPDATE users SET remember_token = ?, remember_token_expires = FROM_UNIXTIME(?) WHERE id = ?");
                $stmt->execute([$remember_token, $expires, $user['id']]);
                
                setcookie('remember_token', $remember_token, $expires, '/', '', true, true);
            }
            
            return [
                'success' => true,
                'user_id' => $user['id'],
                'name' => $user['name'],
                'email' => $user['email'],
                'session_token' => $session_token
            ];
        } else {
            // Increment failed login attempts
            $stmt = $pdo->prepare("UPDATE users SET login_attempts = login_attempts + 1 WHERE id = ?");
            $stmt->execute([$user['id']]);
            
            return ['success' => false, 'error' => 'Invalid credentials'];
        }
    } catch (PDOException $e) {
        error_log("Login error: " . $e->getMessage());
        return ['success' => false, 'error' => 'Database error'];
    }
}

/**
 * Check remember token and login user
 */
function check_remember_token($token) {
    $pdo = db_connect();
    
    try {
        $stmt = $pdo->prepare("SELECT u.id, u.name, u.email FROM users u WHERE u.remember_token = ? AND u.remember_token_expires > NOW()");
        $stmt->execute([$token]);
        $user = $stmt->fetch();
        
        if ($user) {
            // Generate new session
            $session_token = bin2hex(random_bytes(32));
            
            $stmt = $pdo->prepare("INSERT INTO user_sessions (user_id, session_token, ip_address, user_agent, expires_at) VALUES (?, ?, ?, ?, DATE_ADD(NOW(), INTERVAL 2 HOUR))");
            $stmt->execute([
                $user['id'],
                $session_token,
                $_SERVER['REMOTE_ADDR'],
                $_SERVER['HTTP_USER_AGENT']
            ]);
            
            return [
                'success' => true,
                'user_id' => $user['id'],
                'name' => $user['name'],
                'email' => $user['email'],
                'session_token' => $session_token
            ];
        }
    } catch (PDOException $e) {
        error_log("Remember token check error: " . $e->getMessage());
    }
    
    return ['success' => false];
}

/**
 * Validate user session
 */
function validate_session($user_id, $session_token) {
    $pdo = db_connect();
    
    try {
        $stmt = $pdo->prepare("SELECT id FROM user_sessions WHERE user_id = ? AND session_token = ? AND expires_at > NOW() AND ip_address = ? AND user_agent = ?");
        $stmt->execute([
            $user_id,
            $session_token,
            $_SERVER['REMOTE_ADDR'],
            $_SERVER['HTTP_USER_AGENT']
        ]);
        
        return $stmt->fetch() !== false;
    } catch (PDOException $e) {
        error_log("Session validation error: " . $e->getMessage());
        return false;
    }
}

/**
 * Logout user
 */
function logout_user($user_id, $session_token) {
    $pdo = db_connect();
    
    try {
        // Delete current session
        $stmt = $pdo->prepare("DELETE FROM user_sessions WHERE user_id = ? AND session_token = ?");
        $stmt->execute([$user_id, $session_token]);
        
        // Clear remember token
        $stmt = $pdo->prepare("UPDATE users SET remember_token = NULL, remember_token_expires = NULL WHERE id = ?");
        $stmt->execute([$user_id]);
        
        // Clear remember cookie
        setcookie('remember_token', '', time() - 3600, '/', '', true, true);
        
        return true;
    } catch (PDOException $e) {
        error_log("Logout error: " . $e->getMessage());
        return false;
    }
}

/**
 * Secure input sanitization
 */
function sanitize_input($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

/**
 * Redirect helper
 */
function redirect($url) {
    header("Location: $url");
    exit();
}

function generate_password_reset_token() {
    return bin2hex(random_bytes(32));
}

function store_password_reset_token($user_id, $token) {
    $pdo = db_connect();
    $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
    $stmt = $pdo->prepare("INSERT INTO password_resets (user_id, token, expires_at) VALUES (?, ?, ?)");
    return $stmt->execute([$user_id, $token, $expires]);
}

function send_password_reset_email($email, $name, $token) {
    $reset_url = /*BASE_URL .*/ "http://localhost/worldrank/auth/reset_password.php?token=" . urlencode($token);
    
    $subject = "Password Reset Request - WorldRank";
    
    $message = <<<HTML
    <html>
    <head>
        <title>Password Reset</title>
        <style>
            body { font-family: 'Bricolage Grotesque', sans-serif; line-height: 1.6; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .button { display: inline-block; padding: 10px 20px; background-color: #0d9488; color: white; text-decoration: none; border-radius: 5px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <h2>Password Reset Request</h2>
            <p>Hello $name,</p>
            <p>We received a request to reset your password. Click the button below to reset it:</p>
            <p><a href='$reset_url' class='button'>Reset Password</a></p>
            <p>Or copy this link to your browser:<br>$reset_url</p>
            <p>This link will expire in 1 hour.</p>
            <p>If you didn't request this, please ignore this email.</p>
        </div>
    </body>
    </html>
    HTML;
    
    $plain_message = "Hello $name,\n\n"
                   . "We received a request to reset your password. Visit this link to reset it:\n"
                   . "$reset_url\n\n"
                   . "This link will expire in 1 hour.\n\n"
                   . "If you didn't request this, please ignore this email.";
    
    // Use your existing PHPMailer implementation to send the email
    // Include PHPMailer files
    require_once 'PHPMailer/src/Exception.php';
    require_once 'PHPMailer/src/PHPMailer.php';
    require_once 'PHPMailer/src/SMTP.php';

    $mail = new PHPMailer\PHPMailer\PHPMailer(true);
    
    try {
        $mail->isSMTP();
        $mail->Host       = 'mail.emmanrl.xyz';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'info@emmanrl.xyz';
        $mail->Password   = '@TestPass12';
        $mail->SMTPSecure = 'ssl';
        $mail->Port       = 465;
        
        $mail->setFrom('info@emmanrl.xyz', 'WorldRank');
        $mail->addAddress($email, $name);
        
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $message;
        $mail->AltBody = $plain_message;
        
        return $mail->send();
    } catch (Exception $e) {
        error_log("Password reset email failed: " . $mail->ErrorInfo);
        return false;
    }
}


function verify_password_reset_token($token) {
    $pdo = db_connect();
    
    // Check if token exists and isn't expired
    $stmt = $pdo->prepare("SELECT user_id FROM password_resets WHERE token = ? AND expires_at > NOW() AND used = 0");
    $stmt->execute([$token]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    return $result ? $result['user_id'] : false;
}

function update_user_password($user_id, $password) {
    $pdo = db_connect();
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
    return $stmt->execute([$hashed_password, $user_id]);
}

function invalidate_password_reset_token($token) {
    $pdo = db_connect();
    $stmt = $pdo->prepare("UPDATE password_resets SET used = 1 WHERE token = ?");
    return $stmt->execute([$token]);
}