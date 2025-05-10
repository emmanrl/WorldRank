<?php
session_start();
require '../private/functions.php';

$errors = [];
$form_data = [
    'name' => '',
    'email' => '',
    'country' => '',
    'age' => '',
    'gender' => ''
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize inputs
    $form_data = [
        'name' => sanitize_input($_POST['name'] ?? ''),
        'email' => sanitize_input($_POST['email'] ?? ''),
        'password' => $_POST['password'] ?? '',
        'confirm_password' => $_POST['confirm_password'] ?? '',
        'country' => sanitize_input($_POST['country'] ?? ''),
        'age' => (int)($_POST['age'] ?? 0),
        'gender' => sanitize_input($_POST['gender'] ?? '')
    ];

    // Validate inputs
    if (empty($form_data['name'])) {
        $errors['name'] = 'Name is required';
    }

    if (empty($form_data['email'])) {
        $errors['email'] = 'Email is required';
    } elseif (!filter_var($form_data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Invalid email format';
    }

    if (empty($form_data['password'])) {
        $errors['password'] = 'Password is required';
    } elseif (strlen($form_data['password']) < 8) {
        $errors['password'] = 'Password must be at least 8 characters';
    } elseif ($form_data['password'] !== $form_data['confirm_password']) {
        $errors['confirm_password'] = 'Passwords do not match';
    }

    if (empty($form_data['country'])) {
        $errors['country'] = 'Country is required';
    }

    if ($form_data['age'] < 13 || $form_data['age'] > 120) {
        $errors['age'] = 'Age must be between 13 and 120';
    }
    
    if (empty($errors)) {
        // Register user
        $registration = register_user(
            $form_data['name'],
            $form_data['email'],
            $form_data['password'],
            $form_data['country'],
            $form_data['age'],
            $form_data['gender']
        );

        if ($registration['success']) {
            // Send verification email
            $email_sent = send_verification_email(
                $form_data['email'],
                $form_data['name'],
                $registration['verification_token']
            );
            
            
            if ($email_sent) {
                // Store user data in session
                $_SESSION['registration_success'] = true;
                $_SESSION['registration_email'] = $form_data['email'];
                redirect('register_success.php');
            } else {
                // Handle email sending failure
                $errors['general'] = 'Failed to send verification email. Please contact support.';
                header('Location: register.php');
                exit();
            }
        } else {
            // Handle registration failure
            $errors['general'] = $registration['error'];
        }
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register for WorldRank</title>
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
                    },
                    colors: {
                        teal: {
                            500: '#0d9488',
                            600: '#0f766e',
                            700: '#115e59',
                            800: '#134e4a',
                        }
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

        input:focus ~ .input-highlight, select:focus ~ .input-highlight {
            width: 100%;
        }
        .animate-fade-in-up {
            animation: fade-in-up 0.5s ease-out forwards;
        }
        @keyframes fade-in-up {
            0% { opacity: 0; transform: translateY(20px); }
            100% { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body class="bg-stone-50 font-sans">
    <div class="min-h-screen flex flex-col md:flex-row">
        <!-- Image Section -->
        <div class="md:w-1/2 bg-gradient-to-br from-stone-800 via-stone-700 to-stone-600 hidden md:flex items-center justify-center p-12 relative overflow-hidden">
            <!-- Textured background -->
            <div class="absolute inset-0 bg-[url('https://www.transparenttextures.com/patterns/concrete-wall.png')] opacity-10"></div>
            
            <!-- Content -->
            <div class="max-w-md text-white text-center relative z-10 space-y-6">
                <div class="inline-block">
                    <i class="fas fa-trophy text-6xl mb-2 text-teal-300"></i>
                </div>
                <h2 class="text-4xl font-bold mb-4">
                    <span class="bg-clip-text text-transparent bg-gradient-to-r from-teal-300 to-teal-500">
                        Join the Competition
                    </span>
                </h2>
                <p class="text-teal-100 text-lg mb-8">
                    Create your account and start climbing the leaderboard
                </p>
                
                <!-- Features list -->
                <div class="text-left space-y-3">
                    <div class="flex items-start">
                        <i class="fas fa-check-circle text-teal-300 mt-1 mr-2"></i>
                        <span>Track your progress against others</span>
                    </div>
                    <div class="flex items-start">
                        <i class="fas fa-check-circle text-teal-300 mt-1 mr-2"></i>
                        <span>Compete in global rankings</span>
                    </div>
                    <div class="flex items-start">
                        <i class="fas fa-check-circle text-teal-300 mt-1 mr-2"></i>
                        <span>Unlock achievements</span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Registration Form Section -->
        <div class="w-full md:w-1/2 flex items-center justify-center p-8">
            <div class="w-full max-w-md bg-white rounded-xl shadow-lg p-8 sm:p-10 border border-stone-200 animate-fade-in-up">
                <div class="text-center mb-8">
                    <div class="w-16 h-16 bg-teal-500/10 rounded-full flex items-center justify-center mx-auto mb-4 border border-teal-500/20">
                        <i class="fas fa-user-plus text-teal-600 text-2xl"></i>
                    </div>
                    <h1 class="text-3xl font-bold text-stone-800 mb-2">Create Account</h1>
                    <p class="text-stone-500">Join WorldRank and start competing</p>
                </div>
                
                <?php if (!empty($errors['general'])): ?>
                    <div class="mb-4 p-4 bg-red-50 text-red-700 rounded-lg border border-red-200">
                        <i class="fas fa-exclamation-circle mr-2"></i>
                        <?php echo htmlspecialchars($errors['general']); ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" class="space-y-4">
                    <!-- Name -->
                    <div class="relative">
                        <label for="name" class="block text-sm font-medium text-stone-700 mb-1">Full Name</label>
                        <div class="relative">
                            <input type="text" id="name" name="name" 
                                   class="w-full px-4 py-3 rounded-lg border border-stone-300 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 outline-none transition duration-200 bg-stone-50" 
                                   placeholder="Your name" 
                                   value="<?php echo htmlspecialchars($form_data['name']); ?>" 
                                   required>
                            <div class="input-highlight"></div>
                        </div>
                        <?php if (!empty($errors['name'])): ?>
                            <p class="mt-1 text-sm text-red-600"><?php echo htmlspecialchars($errors['name']); ?></p>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Email -->
                    <div class="relative">
                        <label for="email" class="block text-sm font-medium text-stone-700 mb-1">Email Address</label>
                        <div class="relative">
                            <input type="email" id="email" name="email" 
                                   class="w-full px-4 py-3 rounded-lg border border-stone-300 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 outline-none transition duration-200 bg-stone-50" 
                                   placeholder="your@email.com" 
                                   value="<?php echo htmlspecialchars($form_data['email']); ?>" 
                                   required>
                            <div class="input-highlight"></div>
                        </div>
                        <?php if (!empty($errors['email'])): ?>
                            <p class="mt-1 text-sm text-red-600"><?php echo htmlspecialchars($errors['email']); ?></p>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Password -->
                    <div class="relative">
                        <label for="password" class="block text-sm font-medium text-stone-700 mb-1">Password</label>
                        <div class="relative">
                            <input type="password" id="password" name="password" 
                                   class="w-full px-4 py-3 rounded-lg border border-stone-300 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 outline-none transition duration-200 bg-stone-50" 
                                   placeholder="••••••••" 
                                   required>
                            <div class="input-highlight"></div>
                        </div>
                        <p class="mt-1 text-xs text-stone-500">Minimum 8 characters</p>
                        <?php if (!empty($errors['password'])): ?>
                            <p class="mt-1 text-sm text-red-600"><?php echo htmlspecialchars($errors['password']); ?></p>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Confirm Password -->
                    <div class="relative">
                        <label for="confirm_password" class="block text-sm font-medium text-stone-700 mb-1">Confirm Password</label>
                        <div class="relative">
                            <input type="password" id="confirm_password" name="confirm_password" 
                                   class="w-full px-4 py-3 rounded-lg border border-stone-300 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 outline-none transition duration-200 bg-stone-50" 
                                   placeholder="••••••••" 
                                   required>
                            <div class="input-highlight"></div>
                        </div>
                        <?php if (!empty($errors['confirm_password'])): ?>
                            <p class="mt-1 text-sm text-red-600"><?php echo htmlspecialchars($errors['confirm_password']); ?></p>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Country -->
                    <div class="relative">
                        <label for="country" class="block text-sm font-medium text-stone-700 mb-1">Country</label>
                        <div class="relative">
                            <select id="country" name="country" 
                                    class="w-full px-4 py-3 rounded-lg border border-stone-300 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 outline-none transition duration-200 bg-stone-50 appearance-none"
                                    required>
                                <option value="">Select your country</option>
                                <?php
                                $countries = [
                                    'US' => 'United States',
                                    'GB' => 'United Kingdom',
                                    'CA' => 'Canada',
                                    'AU' => 'Australia',
                                    // Add more countries as needed
                                ];
                                
                                foreach ($countries as $code => $name) {
                                    $selected = $form_data['country'] === $code ? 'selected' : '';
                                    echo "<option value=\"$code\" $selected>$name</option>";
                                }
                                ?>
                            </select>
                                                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                                <i class="fas fa-chevron-down text-stone-400"></i>
                            </div>
                            <div class="input-highlight"></div>
                        </div>
                        <?php if (!empty($errors['country'])): ?>
                            <p class="mt-1 text-sm text-red-600"><?php echo htmlspecialchars($errors['country']); ?></p>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Age -->
                    <div class="relative">
                        <label for="age" class="block text-sm font-medium text-stone-700 mb-1">Age</label>
                        <div class="relative">
                            <input type="number" id="age" name="age" min="13" max="120"
                                   class="w-full px-4 py-3 rounded-lg border border-stone-300 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 outline-none transition duration-200 bg-stone-50" 
                                   placeholder="Your age"
                                   value="<?php echo htmlspecialchars($form_data['age']); ?>"
                                   required>
                            <div class="input-highlight"></div>
                        </div>
                        <?php if (!empty($errors['age'])): ?>
                            <p class="mt-1 text-sm text-red-600"><?php echo htmlspecialchars($errors['age']); ?></p>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Gender -->
                    <div class="relative">
                        <label class="block text-sm font-medium text-stone-700 mb-2">Gender</label>
                        <div class="space-y-2">
                            <div class="flex items-center">
                                <input id="gender-male" name="gender" type="radio" value="male"
                                       class="h-4 w-4 text-teal-600 focus:ring-teal-500 border-stone-300"
                                       <?php echo ($form_data['gender'] === 'male') ? 'checked' : ''; ?>>
                                <label for="gender-male" class="ml-2 block text-sm text-stone-700">Male</label>
                            </div>
                            <div class="flex items-center">
                                <input id="gender-female" name="gender" type="radio" value="female"
                                       class="h-4 w-4 text-teal-600 focus:ring-teal-500 border-stone-300"
                                       <?php echo ($form_data['gender'] === 'female') ? 'checked' : ''; ?>>
                                <label for="gender-female" class="ml-2 block text-sm text-stone-700">Female</label>
                            </div>
                            <div class="flex items-center">
                                <input id="gender-other" name="gender" type="radio" value="other"
                                       class="h-4 w-4 text-teal-600 focus:ring-teal-500 border-stone-300"
                                       <?php echo ($form_data['gender'] === 'other') ? 'checked' : ''; ?>>
                                <label for="gender-other" class="ml-2 block text-sm text-stone-700">Other</label>
                            </div>
                            <div class="flex items-center">
                                <input id="gender-prefer-not-to-say" name="gender" type="radio" value="prefer_not_to_say"
                                       class="h-4 w-4 text-teal-600 focus:ring-teal-500 border-stone-300"
                                       <?php echo ($form_data['gender'] === 'prefer_not_to_say') ? 'checked' : ''; ?>>
                                <label for="gender-prefer-not-to-say" class="ml-2 block text-sm text-stone-700">Prefer not to say</label>
                            </div>
                        </div>
                        <?php if (!empty($errors['gender'])): ?>
                            <p class="mt-1 text-sm text-red-600"><?php echo htmlspecialchars($errors['gender']); ?></p>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Terms and Conditions -->
                    <div class="flex items-start">
                        <div class="flex items-center h-5">
                            <input id="terms" name="terms" type="checkbox"
                                   class="focus:ring-teal-500 h-4 w-4 text-teal-600 border-stone-300 rounded"
                                   required>
                        </div>
                        <div class="ml-3 text-sm">
                            <label for="terms" class="font-medium text-stone-700">I agree to the <a href="#" class="text-teal-600 hover:text-teal-500">Terms of Service</a> and <a href="#" class="text-teal-600 hover:text-teal-500">Privacy Policy</a></label>
                        </div>
                    </div>
                    
                    <!-- Submit Button -->
                    <button type="submit"
                            class="w-full flex justify-center items-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-teal-600 hover:bg-teal-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-500 transition duration-200 group relative overflow-hidden">
                        <span class="relative z-10 group-hover:mr-2 transition-all duration-300">Create Account</span>
                        <i class="fas fa-arrow-right opacity-0 group-hover:opacity-100 transition-all duration-300 relative z-10"></i>
                        <span class="absolute inset-0 bg-gradient-to-r from-teal-600 to-teal-800 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></span>
                    </button>
                </form>
                
                <div class="mt-6 text-center text-sm text-stone-500">
                    Already have an account? <a href="login.php" class="font-medium text-teal-600 hover:text-teal-500">Sign in</a>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Password strength indicator
        const passwordInput = document.getElementById('password');
        const passwordStrength = document.createElement('div');
        passwordStrength.className = 'h-1 mt-1 bg-stone-200 rounded-full overflow-hidden';
        passwordInput.parentNode.appendChild(passwordStrength);
        
        const strengthBar = document.createElement('div');
        strengthBar.className = 'h-full bg-teal-500 w-0 transition-all duration-300';
        passwordStrength.appendChild(strengthBar);
        
        passwordInput.addEventListener('input', function() {
            const strength = calculatePasswordStrength(this.value);
            strengthBar.style.width = strength + '%';
            strengthBar.className = `h-full transition-all duration-300 ${getStrengthColor(strength)}`;
        });
        
        function calculatePasswordStrength(password) {
            let strength = 0;
            if (password.length > 7) strength += 25;
            if (password.match(/[a-z]/) && password.match(/[A-Z]/)) strength += 25;
            if (password.match(/\d/)) strength += 25;
            if (password.match(/[^a-zA-Z\d]/)) strength += 25;
            return Math.min(strength, 100);
        }
        
        function getStrengthColor(strength) {
            if (strength < 40) return 'bg-red-500';
            if (strength < 70) return 'bg-yellow-500';
            return 'bg-teal-500';
        }
        
        // Toggle password visibility
        function togglePasswordVisibility(inputId) {
            const input = document.getElementById(inputId);
            const icon = input.nextElementSibling.querySelector('i');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        }
    </script>
</body>
</html>