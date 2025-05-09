<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ./auth/login');
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Dashboard</title>
</head>
<body>
    <h1>Welcome, <?php echo htmlspecialchars($_SESSION['user']['name']); ?></h1>
    <a href="logout.php">Logout</a>
</body>
</html>


<?

/*
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sleek Login</title>
    
    <!-- Core CSS Libraries -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Animation Libraries -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <link rel="stylesheet" href="https://unpkg.com/aos@2.3.1/dist/aos.css" />
    
    <!-- Icon Libraries -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    
    <!-- Form Validation -->
    <script src="https://unpkg.com/validator@latest/validator.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/vee-validate@4.7.3/dist/vee-validate.min.js"></script>
    
    <!-- Micro-interactions -->
    <script src="https://unpkg.com/@lottiefiles/lottie-player@latest/dist/lottie-player.js"></script>
    
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                    colors: {
                        primary: {
                            50: '#f0f9ff',
                            100: '#e0f2fe',
                            500: '#3b82f6',
                            600: '#2563eb',
                        },
                        dark: {
                            800: '#1e293b',
                            900: '#0f172a'
                        }
                    },
                    animation: {
                        'float': 'float 6s ease-in-out infinite',
                        'pulse-slow': 'pulse 4s cubic-bezier(0.4, 0, 0.6, 1) infinite'
                    }
                }
            }
        }
    </script>
    
    <style>
        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-20px); }
        }
        
        .password-toggle {
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
        }
        
        .input-highlight {
            transition: all 0.3s ease;
            position: absolute;
            bottom: 0;
            left: 0;
            height: 2px;
            width: 0;
            background: linear-gradient(90deg, #3b82f6, #8b5cf6);
        }
        
        input:focus ~ .input-highlight {
            width: 100%;
        }
    </style>
</head>
<body class="bg-gray-50 font-sans dark:bg-dark-900">
    <div class="min-h-screen flex flex-col md:flex-row">
        <!-- Image Section -->
        <div class="md:w-1/2 bg-gradient-to-br from-primary-500 to-primary-600 hidden md:flex items-center justify-center p-12 relative overflow-hidden">
            <div class="absolute inset-0 opacity-10">
                <div class="absolute top-10 left-20 w-32 h-32 bg-white rounded-full animate-float"></div>
                <div class="absolute top-1/3 right-20 w-40 h-40 bg-white rounded-full animate-float" style="animation-delay: 1s"></div>
                <div class="absolute bottom-20 left-1/4 w-24 h-24 bg-white rounded-full animate-float" style="animation-delay: 2s"></div>
            </div>
            
            <div class="max-w-md text-white text-center relative z-10" data-aos="fade-up" data-aos-duration="1000">
                <lottie-player 
                    src="https://assets10.lottiefiles.com/packages/lf20_ktwnwv5m.json" 
                    background="transparent" 
                    speed="1" 
                    style="width: 300px; height: 300px; margin: 0 auto;" 
                    loop 
                    autoplay>
                </lottie-player>
                
                <h2 class="text-3xl font-bold mb-4">Welcome Back!</h2>
                <p class="text-primary-100 mb-6">Access your account and discover great products and services tailored just for you.</p>
                
                <div class="flex justify-center space-x-4">
                    <div class="w-10 h-10 rounded-full bg-white/10 flex items-center justify-center hover:bg-white/20 transition cursor-pointer">
                        <i class="ri-facebook-fill text-lg"></i>
                    </div>
                    <div class="w-10 h-10 rounded-full bg-white/10 flex items-center justify-center hover:bg-white/20 transition cursor-pointer">
                        <i class="ri-twitter-fill text-lg"></i>
                    </div>
                    <div class="w-10 h-10 rounded-full bg-white/10 flex items-center justify-center hover:bg-white/20 transition cursor-pointer">
                        <i class="ri-linkedin-fill text-lg"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Login Form Section -->
        <div class="w-full md:w-1/2 flex items-center justify-center p-8">
            <div class="w-full max-w-md bg-white dark:bg-dark-800 rounded-xl shadow-lg p-8 sm:p-10 transition-all duration-300 hover:shadow-xl" data-aos="fade-left" data-aos-duration="800">
                <div class="text-center mb-8">
                    <div class="w-16 h-16 bg-primary-500/10 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="ri-user-3-fill text-primary-500 text-2xl"></i>
                    </div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">Sign in</h1>
                    <p class="text-gray-500 dark:text-gray-400">Enter your details to access your account</p>
                </div>
                
                <form class="space-y-6" id="loginForm">
                    <div class="relative">
                        <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Email address</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="ri-mail-line text-gray-400"></i>
                            </div>
                            <input type="email" id="email" name="email" 
                                   class="w-full pl-10 pr-4 py-3 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-dark-700 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none transition duration-200" 
                                   placeholder="your@email.com" required>
                            <div class="input-highlight"></div>
                        </div>
                        <p class="mt-1 text-xs text-red-500 hidden" id="email-error">Please enter a valid email address</p>
                    </div>
                    
                    <div class="relative">
                        <div class="flex justify-between items-center mb-1">
                            <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Password</label>
                            <a href="#" class="text-sm text-primary-600 hover:text-primary-500 font-medium">Forgot password?</a>
                        </div>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="ri-lock-line text-gray-400"></i>
                            </div>
                            <input type="password" id="password" name="password" 
                                   class="w-full pl-10 pr-10 py-3 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-dark-700 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none transition duration-200" 
                                   placeholder="••••••••" required>
                            <div class="password-toggle absolute" onclick="togglePassword()">
                                <i class="ri-eye-line text-gray-400 hover:text-gray-600"></i>
                            </div>
                            <div class="input-highlight"></div>
                        </div>
                        <p class="mt-1 text-xs text-red-500 hidden" id="password-error">Password must be at least 8 characters</p>
                    </div>
                    
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <input id="remember-me" name="remember-me" type="checkbox" 
                                   class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 dark:border-gray-600 rounded">
                            <label for="remember-me" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">Remember me</label>
                        </div>
                        <div class="flex items-center">
                            <span class="text-sm text-gray-500 dark:text-gray-400 mr-2">Dark mode</span>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" id="dark-mode-toggle" class="sr-only peer">
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary-300 dark:peer-focus:ring-primary-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-primary-600"></div>
                            </label>
                        </div>
                    </div>
                    
                    <button type="submit" 
                            class="w-full flex justify-center items-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition duration-200 group">
                        <span class="group-hover:mr-2 transition-all duration-300">Sign in</span>
                        <i class="ri-arrow-right-line opacity-0 group-hover:opacity-100 transition-all duration-300"></i>
                    </button>
                </form>
                
                <div class="mt-6">
                    <div class="relative">
                        <div class="absolute inset-0 flex items-center">
                            <div class="w-full border-t border-gray-300 dark:border-gray-600"></div>
                        </div>
                        <div class="relative flex justify-center text-sm">
                            <span class="px-2 bg-white dark:bg-dark-800 text-gray-500 dark:text-gray-400">Or continue with</span>
                        </div>
                    </div>
                    
                    <div class="mt-6 grid grid-cols-3 gap-3">
                        <a href="#" class="w-full inline-flex justify-center py-2 px-4 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm bg-white dark:bg-dark-700 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-dark-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition duration-200">
                            <i class="ri-google-fill text-lg"></i>
                        </a>
                        <a href="#" class="w-full inline-flex justify-center py-2 px-4 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm bg-white dark:bg-dark-700 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-dark-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition duration-200">
                            <i class="ri-github-fill text-lg"></i>
                        </a>
                        <a href="#" class="w-full inline-flex justify-center py-2 px-4 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm bg-white dark:bg-dark-700 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-dark-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition duration-200">
                            <i class="ri-twitter-fill text-lg"></i>
                        </a>
                    </div>
                </div>
                
                <div class="mt-6 text-center text-sm text-gray-500 dark:text-gray-400">
                    Don't have an account? 
                    <a href="#" class="font-medium text-primary-600 hover:text-primary-500">Sign up</a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Floating particles background -->
    <canvas id="particles" class="fixed top-0 left-0 w-full h-full pointer-events-none z-0"></canvas>
    
    <!-- Added Libraries JS -->
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/particles.js@2.0.0/particles.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script>
        // Initialize AOS (Animate On Scroll)
        AOS.init({
            once: true
        });
        
        // Initialize particles.js
        particlesJS('particles', {
            particles: {
                number: { value: 80, density: { enable: true, value_area: 800 } },
                color: { value: "#3b82f6" },
                shape: { type: "circle" },
                opacity: { value: 0.3, random: true },
                size: { value: 3, random: true },
                line_linked: { enable: false },
                move: { enable: true, speed: 2, direction: "none", random: true, straight: false, out_mode: "out" }
            },
            interactivity: { detect_on: "canvas", events: { onhover: { enable: false }, onclick: { enable: false } } }
        });
        
        // Toggle password visibility
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.querySelector('.password-toggle i');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.replace('ri-eye-line', 'ri-eye-off-line');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.replace('ri-eye-off-line', 'ri-eye-line');
            }
        }
        
        // Dark mode toggle
        const darkModeToggle = document.getElementById('dark-mode-toggle');
        const html = document.documentElement;
        
        // Check for saved user preference or system preference
        if (localStorage.getItem('darkMode') === 'true' || 
            (!localStorage.getItem('darkMode') && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            html.classList.add('dark');
            darkModeToggle.checked = true;
        }
        
        darkModeToggle.addEventListener('change', function() {
            if (this.checked) {
                html.classList.add('dark');
                localStorage.setItem('darkMode', 'true');
            } else {
                html.classList.remove('dark');
                localStorage.setItem('darkMode', 'false');
            }
        });
        
        // Form validation
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            let isValid = true;
            
            // Email validation
            if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                document.getElementById('email-error').classList.remove('hidden');
                isValid = false;
            } else {
                document.getElementById('email-error').classList.add('hidden');
            }
            
            // Password validation
            if (password.length < 8) {
                document.getElementById('password-error').classList.remove('hidden');
                isValid = false;
            } else {
                document.getElementById('password-error').classList.add('hidden');
            }
            
            if (isValid) {
                Swal.fire({
                    title: 'Success!',
                    text: 'You have successfully logged in!',
                    icon: 'success',
                    confirmButtonText: 'Continue',
                    confirmButtonColor: '#3b82f6',
                    background: html.classList.contains('dark') ? '#1e293b' : '#fff',
                    color: html.classList.contains('dark') ? '#fff' : '#1e293b'
                });
            }
        });
    </script>
</body>
</html>
*/
?>