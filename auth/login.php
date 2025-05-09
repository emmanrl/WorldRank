<?php
session_start();
require '../private/functions.php';

$error = '';
$email = $_COOKIE['remember_email'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize_input($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember_me = isset($_POST['remember-me']);

    // Validate inputs
    if (empty($email) || empty($password)) {
        $error = 'Please enter both email and password';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address';
    } else {
        $login_result = login_user($email, $password, $remember_me);
        
        if ($login_result['success']) {
            // Regenerate session ID to prevent fixation
            session_regenerate_id(true);
            
            // Store user in session
            $_SESSION['user'] = [
                'id' => $login_result['user_id'],
                'name' => $login_result['name'],
                'email' => $login_result['email'],
                'session_token' => $login_result['session_token']
            ];
            
            redirect('dashboard.php');
        } else {
            $error = $login_result['error'];
        }
    }
}

// Check for remember token
if (empty($_SESSION['user']) && isset($_COOKIE['remember_token'])) {
    $remember_result = check_remember_token($_COOKIE['remember_token']);
    
    if ($remember_result['success']) {
        session_regenerate_id(true);
        
        $_SESSION['user'] = [
            'id' => $remember_result['user_id'],
            'name' => $remember_result['name'],
            'email' => $remember_result['email'],
            'session_token' => $remember_result['session_token']
        ];
        
        redirect('dashboard.php');
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sleek Login</title>
    <!-- Bricolage Grotesque Font -->
    <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,200..800&display=swap" rel="stylesheet">
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <script>
        tailwind.config = {
        theme: {
            extend: {
            fontFamily: {
                sans: ['Bricolage Grotesque', 'sans-serif'],
            }
            }
        }
        }
    </script>
    <style>
        .password-toggle {
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
        }

        input:focus ~ .input-highlight {
            width: 100%;
        }

        @keyframes float1 {
            0%, 100% { transform: translate(0, 0) rotate(0deg); }
            50% { transform: translate(-10px, -20px) rotate(5deg); }
        }
        @keyframes float2 {
            0%, 100% { transform: translate(0, 0) rotate(0deg); }
            50% { transform: translate(15px, 10px) rotate(-5deg); }
        }
        @keyframes float3 {
            0%, 100% { transform: translate(0, 0); }
            50% { transform: translate(5px, -15px); }
        }
        @keyframes bounce-slow {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }
        @keyframes fade-in-up {
            0% { opacity: 0; transform: translateY(20px); }
            100% { opacity: 1; transform: translateY(0); }
        }
        @keyframes progress {
            0% { width: 0%; }
            100% { width: 75%; }
        }
        @keyframes pulse-slow {
            0%, 100% { opacity: 0.8; }
            50% { opacity: 1; }
        }
        .animate-float1 { animation: float1 8s ease-in-out infinite; }
        .animate-float2 { animation: float2 10s ease-in-out infinite; }
        .animate-float3 { animation: float3 12s ease-in-out infinite; }
        .animate-bounce-slow { animation: bounce-slow 3s ease-in-out infinite; }
        .animate-fade-in-up { animation: fade-in-up 1s ease-out forwards; }
        .animate-progress { animation: progress 2s ease-out forwards; }
        .animate-pulse-slow { animation: pulse-slow 4s ease-in-out infinite; }
        .delay-100 { animation-delay: 100ms; }
    </style>
</head>
<body class="bg-stone-50 font-sans">
    <div class="min-h-screen flex flex-col md:flex-row">
        <!-- Image Section -->
        <div class="md:w-1/2 bg-gradient-to-br from-stone-800 via-stone-700 to-stone-600 hidden md:flex items-center justify-center p-12 relative overflow-hidden">
            <!-- Textured background -->
            <div class="absolute inset-0 bg-[url('https://www.transparenttextures.com/patterns/concrete-wall.png')] opacity-10"></div>
            
            <!-- Animated background elements -->
            <div class="absolute inset-0 overflow-hidden">
                <div class="absolute -top-20 -left-20 w-64 h-64 bg-teal-400/5 rounded-full filter blur-xl animate-float1"></div>
                <div class="absolute bottom-10 right-32 w-48 h-48 bg-teal-300/10 rounded-full filter blur-lg animate-float2"></div>
                <div class="absolute top-1/3 right-20 w-32 h-32 bg-teal-200/15 rounded-full filter blur-md animate-float3"></div>
            </div>
            
            <!-- Content with animations -->
            <div class="max-w-md text-white text-center relative z-10 space-y-6 transform transition-all duration-500 hover:scale-105">
                <div class="inline-block animate-bounce-slow">
                    <i class="fas fa-trophy text-6xl mb-2 transform transition-all hover:rotate-12 hover:text-teal-300"></i>
                </div>
                <h2 class="text-4xl font-bold mb-4 animate-fade-in-up">
                    <span class="bg-clip-text text-transparent bg-gradient-to-r from-teal-300 to-teal-500">
                        Welcome to WorldRank
                    </span>
                </h2>
                <p class="text-teal-100 text-lg mb-8 animate-fade-in-up delay-100">
                    Join millions competing for the top spot
                </p>
                
                <!-- Animated rank indicator -->
                <div class="relative h-3 bg-stone-500/30 rounded-full overflow-hidden mt-8">
                    <div class="absolute inset-0 bg-gradient-to-r from-teal-400 to-teal-600 rounded-full w-3/4 animate-progress"></div>
                </div>
                <p class="text-sm text-teal-200 animate-pulse-slow">
                    Current top rank: 99.9 percentile
                </p>
            </div>
        </div>
        
        <!-- Login Form Section -->
        <div class="w-full md:w-1/2 flex items-center justify-center p-8">
            <div class="w-full max-w-md bg-white rounded-xl shadow-lg p-5 sm:p-10 border border-stone-200">
                <div class="text-center mb-8">
                    <div class="w-16 h-16 bg-teal-500/10 rounded-full flex items-center justify-center mx-auto mb-4 border border-teal-500/20">
                        <i class="fas fa-user text-teal-600 text-2xl"></i>
                    </div>
                    <h1 class="text-3xl font-bold text-stone-800 mb-2 text-teal-600">WorldRank</h1>
                    <h3 class="text-2xl font-bold text-stone-800 mb-2">Sign in</h3>
                    <p class="text-stone-500">Enter your credentials to continue</p>
                </div>
                
                <?php if ($error): ?>
                    <div class="mb-4 p-4 bg-red-50 text-red-700 rounded-lg border border-red-200">
                        <i class="fas fa-exclamation-circle mr-2"></i>
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" class="space-y-6">
                    <div class="relative">
                        <label for="email" class="block text-sm font-medium text-stone-700 mb-1">Email address</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-envelope text-stone-400"></i>
                            </div>
                            <input type="email" id="email" name="email" 
                                   class="w-full pl-10 pr-4 py-3 rounded-lg border border-stone-300 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 outline-none transition duration-200 bg-stone-50" 
                                   placeholder="your@email.com" 
                                   value="<?php echo htmlspecialchars($email); ?>" 
                                   required>
                            <div class="input-highlight"></div>
                        </div>
                    </div>
                    
                    <div class="relative">
                        <div class="flex justify-between items-center mb-1">
                            <label for="password" class="block text-sm font-medium text-stone-700">Password</label>
                            <a href="forgot_password.php" class="text-sm text-teal-600 hover:text-teal-500 font-medium">Forgot password?</a>
                        </div>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-lock text-stone-400"></i>
                            </div>
                            <input type="password" id="password" name="password" 
                                   class="w-full pl-10 pr-10 py-3 rounded-lg border border-stone-300 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 outline-none transition duration-200 bg-stone-50 text-stone-900" 
                                   placeholder="• • • • • • • •" required>
                            <div class="password-toggle absolute right-3 top-1/2 transform -translate-y-1/2 cursor-pointer" onclick="togglePassword()">
                                <i class="fas fa-eye text-stone-400 hover:text-stone-600"></i>
                            </div>
                            <div class="input-highlight"></div>
                        </div>
                    </div>
                    
                    <div class="flex items-center">
                        <input id="remember-me" name="remember-me" type="checkbox" 
                               class="h-4 w-4 text-teal-600 focus:ring-teal-500 border-stone-300 rounded"
                               <?php echo isset($_COOKIE['remember_token']) ? 'checked' : ''; ?>>
                        <label for="remember-me" class="ml-2 block text-sm text-stone-700">Remember me</label>
                    </div>
                    
                    <button type="submit" 
                            class="w-full flex justify-center items-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-teal-800 hover:bg-teal-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-800 transition duration-200 group relative overflow-hidden">
                        <span class="relative z-10 group-hover:mr-2 transition-all duration-300">Sign in</span>
                        <i class="fas fa-arrow-right opacity-0 group-hover:opacity-100 transition-all duration-300 relative z-10"></i>
                        <span class="absolute inset-0 bg-gradient-to-r from-teal-800 to-teal-950 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></span>
                    </button>
                </form>
                
                <div class="mt-6">
                    <div class="relative">
                        <div class="absolute inset-0 flex items-center">
                            <div class="w-full border-t border-stone-300"></div>
                        </div>
                        <div class="relative flex justify-center text-sm">
                            <span class="px-2 bg-white text-stone-800">Don't have an account?</span>
                        </div>
                    </div>
                    
                    <div class="mt-6 text-center">
                        <a href="register.php" class="w-full inline-flex justify-center py-2 px-4 border border-stone-300 rounded-lg shadow-sm bg-white text-sm font-medium text-stone-700 hover:bg-stone-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-800 transition duration-200 hover:border-teal-300">
                            Create new account
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.querySelector('.password-toggle i');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.replace('fa-eye', 'fa-eye-slash');
                toggleIcon.classList.replace('text-stone-400', 'text-teal-500');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.replace('fa-eye-slash', 'fa-eye');
                toggleIcon.classList.replace('text-teal-500', 'text-stone-400');
            }
        }
    </script>
</body>
</html>