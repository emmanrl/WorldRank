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
    $verification_url = BASE_URL . "/verify.php?token=" . urlencode($token);
    $subject = "Verify Your Email Address";
    
    $message = "
    <html>
    <head>
        <title>Email Verification</title>
        <style>
            body { font-family: 'Bricolage Grotesque', sans-serif; line-height: 1.6; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .button { display: inline-block; padding: 10px 20px; background-color: #0d9488; color: white; text-decoration: none; border-radius: 5px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <h2>Welcome to WorldRank, $name!</h2>
            <p>Thank you for registering. Please verify your email address to activate your account and start competing on the leaderboard.</p>
            <p><a href='$verification_url' class='button'>Verify Email</a></p>
            <p>Or copy this link to your browser:<br>$verification_url</p>
            <p>This link will expire in 24 hours.</p>
        </div>
    </body>
    </html>
    ";
    
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: WorldRank <no-reply@" . parse_url(BASE_URL, PHP_URL_HOST) . ">" . "\r\n";
    
    return mail($email, $subject, $message, $headers);
}

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