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
        .form-step {
            display: none;
        }
        .form-step.active {
            display: block;
            animation: fade-in-up 0.5s ease-out forwards;
        }

        .step-indicator {
            display: flex;
            flex-direction: column;
            align-items: center;
            position: relative;
            z-index: 1;
            flex: 1;
            max-width: 80px;
        }

        .step-indicator.active .step-number {
            background-color: #0d9488;
            color: white;
            border-color: #0d9488;
            box-shadow: 0 0 0 4px rgba(13, 148, 136, 0.2);
        }

        .step-indicator.completed .step-number {
            background-color: #0d9488;
            color: white;
            border-color: #0d9488;
        }

        .step-indicator.completed .step-number::before {
            
            font-family: "Font Awesome 6 Free";
            font-weight: 900;
            font-size: 0.8rem;
        }

        .step-indicator.active .text-xs {
            color: #0d9488;
            font-weight: 600;
        }

        .step-indicator.completed .text-xs {
            color: #0d9488;
            font-weight: 500;
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
                
                <!-- Step Indicators -->
                <div class="flex justify-between mb-8 relative">
                    <!-- Progress line -->
                    <div class="absolute top-4 left-0 right-0 h-1 bg-stone-200 -z-10 mx-10">
                        <div id="progress-line" class="h-full bg-teal-500 transition-all duration-300" style="width: 0%"></div>
                    </div>
                    
                    <!-- Step 1 -->
                    <div class="step-indicator active" data-step="1">
                        <div class="step-number flex items-center justify-center w-8 h-8 rounded-full bg-teal-600 text-white font-medium border-2 border-teal-600 transition-all duration-300">
                            1
                        </div>
                        <div class="text-xs font-medium text-teal-600 mt-2 transition-all duration-300">Account</div>
                    </div>
                    
                    <!-- Step 2 -->
                    <div class="step-indicator" data-step="2">
                        <div class="step-number flex items-center justify-center w-8 h-8 rounded-full bg-white text-stone-500 font-medium border-2 border-stone-300 transition-all duration-300">
                            2
                        </div>
                        <div class="text-xs font-medium text-stone-500 mt-2 transition-all duration-300">Profile</div>
                    </div>
                    
                    <!-- Step 3 -->
                    <div class="step-indicator" data-step="3">
                        <div class="step-number flex items-center justify-center w-8 h-8 rounded-full bg-white text-stone-500 font-medium border-2 border-stone-300 transition-all duration-300">
                            3
                        </div>
                        <div class="text-xs font-medium text-stone-500 mt-2 transition-all duration-300">Complete</div>
                    </div>
                </div>
                
                <?php if (!empty($errors['general'])): ?>
                    <div class="mb-4 p-4 bg-red-50 text-red-700 rounded-lg border border-red-200">
                        <i class="fas fa-exclamation-circle mr-2"></i>
                        <?php echo htmlspecialchars($errors['general']); ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" class="space-y-4" id="registrationForm">
                    <!-- Step 1: Account Information -->
                    <div class="form-step active" id="step-1">
                        <!-- First & Last Name (Single Line) -->
                        <div class="grid grid-cols-2 gap-4">
                            <div class="relative">
                                <label for="first_name" class="block text-sm font-medium text-stone-700 mb-1">First Name</label>
                                <div class="relative">
                                    <input type="text" id="first_name" name="first_name" 
                                        class="w-full px-4 py-3 rounded-lg border border-stone-300 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 outline-none transition duration-200 bg-stone-50" 
                                        placeholder="First" 
                                        value="<?php echo htmlspecialchars($form_data['first_name'] ?? ''); ?>" 
                                        required>
                                    <div class="input-highlight"></div>
                                </div>
                                <?php if (!empty($errors['first_name'])): ?>
                                    <p class="mt-1 text-sm text-red-600"><?php echo htmlspecialchars($errors['first_name']); ?></p>
                                <?php endif; ?>
                            </div>
                            <div class="relative">
                                <label for="last_name" class="block text-sm font-medium text-stone-700 mb-1">Last Name</label>
                                <div class="relative">
                                    <input type="text" id="last_name" name="last_name" 
                                        class="w-full px-4 py-3 rounded-lg border border-stone-300 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 outline-none transition duration-200 bg-stone-50" 
                                        placeholder="Last" 
                                        value="<?php echo htmlspecialchars($form_data['last_name'] ?? ''); ?>" 
                                        required>
                                    <div class="input-highlight"></div>
                                </div>
                                <?php if (!empty($errors['last_name'])): ?>
                                    <p class="mt-1 text-sm text-red-600"><?php echo htmlspecialchars($errors['last_name']); ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <!-- Email -->
                        <div class="relative mt-4">
                            <label for="email" class="block text-sm font-medium text-stone-700 mb-1">Email Address</label>
                            <div class="relative">
                                <input type="email" id="email" name="email" 
                                    class="w-full px-4 py-3 rounded-lg border border-stone-300 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 outline-none transition duration-200 bg-stone-50" 
                                    placeholder="your@email.com" 
                                    value="<?php echo htmlspecialchars($form_data['email'] ?? ''); ?>" 
                                    required>
                                <div class="input-highlight"></div>
                            </div>
                            <?php if (!empty($errors['email'])): ?>
                                <p class="mt-1 text-sm text-red-600"><?php echo htmlspecialchars($errors['email']); ?></p>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Password -->
                        <div class="relative mt-4">
                            <label for="password" class="block text-sm font-medium text-stone-700 mb-1">Password</label>
                            <div class="relative">
                                <input type="password" id="password" name="password" 
                                    class="w-full px-4 py-3 rounded-lg border border-stone-300 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 outline-none transition duration-200 bg-stone-50" 
                                    placeholder="• • • • • • • •" 
                                    required>
                                <div class="input-highlight"></div>
                                <div class="absolute right-3 top-1/2 transform -translate-y-1/2 cursor-pointer" onclick="togglePasswordVisibility('password')">
                                    <i class="fas fa-eye text-stone-400"></i>
                                </div>
                            </div>
                            <div class="password-strength mt-1">
                                <div id="password-strength-bar" class="h-full bg-red-500 w-0 transition-all duration-300"></div>
                                <p class="text-xs text-stone-500 mt-1">Minimum 8 characters with uppercase, lowercase, number, and special character</p>
                            </div>
                            <?php if (!empty($errors['password'])): ?>
                                <p class="mt-1 text-sm text-red-600"><?php echo htmlspecialchars($errors['password']); ?></p>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Confirm Password -->
                        <div class="relative mt-4">
                            <label for="confirm_password" class="block text-sm font-medium text-stone-700 mb-1">Confirm Password</label>
                            <div class="relative">
                                <input type="password" id="confirm_password" name="confirm_password" 
                                    class="w-full px-4 py-3 rounded-lg border border-stone-300 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 outline-none transition duration-200 bg-stone-50" 
                                    placeholder="• • • • • • • •" 
                                    required>
                                <div class="input-highlight"></div>
                                <div class="absolute right-3 top-1/2 transform -translate-y-1/2 cursor-pointer" onclick="togglePasswordVisibility('confirm_password')">
                                    <i class="fas fa-eye text-stone-400"></i>
                                </div>
                            </div>
                            <?php if (!empty($errors['confirm_password'])): ?>
                                <p class="mt-1 text-sm text-red-600"><?php echo htmlspecialchars($errors['confirm_password']); ?></p>
                            <?php endif; ?>
                        </div>
                        
                        <div class="flex justify-between pt-4">
                            <div></div> <!-- Empty div for spacing -->
                            <button type="button" onclick="nextStep(1, 2)" class="flex items-center justify-center py-3 px-6 rounded-lg shadow-sm text-sm font-medium text-white bg-teal-600 hover:bg-teal-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-500 transition duration-200">
                                Next <i class="fas fa-arrow-right ml-2"></i>
                            </button>
                        </div>
                    </div>
                    
                    <!-- Step 2: Profile Information -->
                    <div class="form-step" id="step-2">
                        <!-- Username -->
                        <div class="grid grid-cols-2 gap-4">
                            <div class="relative">
                                <label for="username" class="block text-sm font-medium text-stone-700 mb-1">Username</label>
                                <div class="relative">
                                    <input type="text" id="username" name="username" 
                                        class="w-full px-4 py-3 rounded-lg border border-stone-300 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 outline-none transition duration-200 bg-stone-50" 
                                        placeholder="<?php echo ["CodeMaster", "TechNo", "GameOn", "ProPlayer", "DevDream"][rand(0, 4)] . rand(100, 999); ?>"
                                        value="<?php echo htmlspecialchars($form_data['username'] ?? ''); ?>"
                                        required>
                                    <div class="input-highlight"></div>
                                </div>
                                <p class="mt-1 text-xs text-stone-500">This will be your public display name</p>
                                <?php if (!empty($errors['username'])): ?>
                                    <p class="mt-1 text-sm text-red-600"><?php echo htmlspecialchars($errors['username']); ?></p>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Phone Number -->
                            <div class="relative">
                                <label for="phone" class="block text-sm font-medium text-stone-700 mb-1">Phone Number</label>
                                <div class="relative">
                                    <input type="tel" id="phone" name="phone" 
                                        class="w-full px-4 py-3 rounded-lg border border-stone-300 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 outline-none transition duration-200 bg-stone-50" 
                                        placeholder="+1 (555) 123-4567" 
                                        value="<?php echo htmlspecialchars($form_data['phone'] ?? ''); ?>">
                                    <div class="input-highlight"></div>
                                </div>
                                
                                <?php if (!empty($errors['phone'])): ?>
                                    <p class="mt-1 text-sm text-red-600"><?php echo htmlspecialchars($errors['phone']); ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <!-- Date of Birth -->
                        <div class="grid grid-cols-2 gap-4">
                            <div class="relative mt-4">
                                <label for="dob" class="block text-sm font-medium text-stone-700 mb-1">Date of Birth</label>
                                <div class="relative">
                                    <input type="date" id="dob" name="dob" 
                                        class="w-full px-4 py-3 rounded-lg border border-stone-300 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 outline-none transition duration-200 bg-stone-50" 
                                        value="<?php echo htmlspecialchars($form_data['dob'] ?? ''); ?>"
                                        required>
                                    <div class="input-highlight"></div>
                                </div>
                                <?php if (!empty($errors['dob'])): ?>
                                    <p class="mt-1 text-sm text-red-600"><?php echo htmlspecialchars($errors['dob']); ?></p>
                                <?php endif; ?>
                            </div>
                            
                            
                            <!-- Country -->
                            <div class="relative mt-4">
                                <label for="country" class="block text-sm font-medium text-stone-700 mb-1">Country</label>
                                <div class="relative">
                                    <?php

                                    $cacheFile = __DIR__ . '/countries_cache.json';

                                    // 2. Load countries from cache or generate fresh
                                    if (!file_exists($cacheFile)) {
                                        // Fetch countries from API (or use hardcoded array)
                                        $countries = [
                                            'US' => 'United States',
                                            'GB' => 'United Kingdom',
                                            'CA' => 'Canada',
                                            // ... (your existing entries)
                                        ];
                                        
                                        // Optional: Fetch from REST Countries API if you want live updates
                                        
                                        $apiData = file_get_contents('https://restcountries.com/v3.1/all');
                                        $allCountries = json_decode($apiData, true);
                                        foreach ($allCountries as $country) {
                                            $countries[$country['cca2']] = $country['name']['common'];
                                        }
                                        
                                        
                                        // Save to cache
                                        file_put_contents($cacheFile, json_encode($countries));
                                    } else {
                                        // Load from cache
                                        $countries = json_decode(file_get_contents($cacheFile), true);
                                    }

                                    // 3. Sort alphabetically
                                    asort($countries);
                                    ?>

                                    <select 
                                        id="country" 
                                        name="country"
                                        class="w-full px-4 py-3 rounded-lg border border-stone-300 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 outline-none transition duration-200 bg-stone-50 appearance-none"
                                        required
                                    >
                                        <option value="">Select your country</option>
                                        <?php foreach ($countries as $code => $name): ?>
                                            <option 
                                                value="<?= htmlspecialchars($code) ?>" 
                                                <?= ($_POST['country'] ?? '') === $code ? 'selected' : '' ?>
                                            >
                                                <?= htmlspecialchars($name) ?>
                                            </option>
                                        <?php endforeach; ?>
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
                        </div>
                        
                        <!-- Gender -->
                        <div class="relative">
                            <label class="block text-sm font-medium text-stone-700 mb-2">Gender</label>
                            <div class="grid grid-cols-2 gap-2">
                                <div class="flex items-center">
                                    <input id="gender-male" name="gender" type="radio" value="male"
                                        class="h-4 w-4 text-teal-600 focus:ring-teal-500 border-stone-300"
                                        <?php echo (($form_data['gender'] ?? '') === 'male') ? 'checked' : ''; ?>>
                                    <label for="gender-male" class="ml-2 block text-sm text-stone-700">Male</label>
                                </div>
                                <div class="flex items-center">
                                    <input id="gender-female" name="gender" type="radio" value="female"
                                        class="h-4 w-4 text-teal-600 focus:ring-teal-500 border-stone-300"
                                        <?php echo (($form_data['gender'] ?? '') === 'female') ? 'checked' : ''; ?>>
                                    <label for="gender-female" class="ml-2 block text-sm text-stone-700">Female</label>
                                </div>
                                <div class="flex items-center">
                                    <input id="gender-other" name="gender" type="radio" value="other"
                                        class="h-4 w-4 text-teal-600 focus:ring-teal-500 border-stone-300"
                                        <?php echo (($form_data['gender'] ?? '') === 'other') ? 'checked' : ''; ?>>
                                    <label for="gender-other" class="ml-2 block text-sm text-stone-700">Other</label>
                                </div>
                                <div class="flex items-center">
                                    <input id="gender-prefer-not-to-say" name="gender" type="radio" value="prefer_not_to_say"
                                        class="h-4 w-4 text-teal-600 focus:ring-teal-500 border-stone-300"
                                        <?php echo (($form_data['gender'] ?? '') === 'prefer_not_to_say') ? 'checked' : ''; ?>>
                                    <label for="gender-prefer-not-to-say" class="ml-2 block text-sm text-stone-700">Prefer not to say</label>
                                </div>
                            </div>
                            <?php if (!empty($errors['gender'])): ?>
                                <p class="mt-1 text-sm text-red-600"><?php echo htmlspecialchars($errors['gender']); ?></p>
                            <?php endif; ?>
                        </div>
                        
                        <div class="flex justify-between pt-4">
                            <button type="button" onclick="prevStep(2, 1)" class="flex items-center justify-center py-3 px-6 rounded-lg shadow-sm text-sm font-medium text-stone-700 bg-stone-100 hover:bg-stone-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-stone-500 transition duration-200">
                                <i class="fas fa-arrow-left mr-2"></i> Back
                            </button>
                            <button type="button" onclick="nextStep(2, 3)" class="flex items-center justify-center py-3 px-6 rounded-lg shadow-sm text-sm font-medium text-white bg-teal-600 hover:bg-teal-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-500 transition duration-200">
                                Next <i class="fas fa-arrow-right ml-2"></i>
                            </button>
                        </div>
                    </div>
                    
                    <!-- Step 3: Terms and Submit -->
                    <div class="form-step" id="step-3">
                        <!-- Profile Picture Upload -->
                        <div class="relative">
                            <label class="block text-sm font-medium text-stone-700 mb-2">Profile Picture</label>
                            <div class="flex items-center space-x-4">
                                <div class="shrink-0">
                                    <img id="profile-preview" class="h-16 w-16 rounded-full object-cover border-2 border-stone-200" src="https://ui-avatars.com/api/?name=<?php echo urlencode(($form_data['first_name'] ?? '').' '.($form_data['last_name'] ?? '')); ?>&background=0d9488&color=fff" alt="Profile preview">
                                </div>
                                <label class="block">
                                    <span class="sr-only">Choose profile photo</span>
                                    <input type="file" id="profile_picture" name="profile_picture" accept="image/*" class="block w-full text-sm text-stone-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-teal-50 file:text-teal-700 hover:file:bg-teal-100">
                                </label>
                            </div>
                            <?php if (!empty($errors['profile_picture'])): ?>
                                <p class="mt-1 text-sm text-red-600"><?php echo htmlspecialchars($errors['profile_picture']); ?></p>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Bio -->
                        <div class="relative">
                            <label for="bio" class="block text-sm font-medium text-stone-700 mb-1">Bio</label>
                            <div class="relative">
                                <textarea id="bio" name="bio" rows="3"
                                    class="w-full px-4 py-3 rounded-lg border border-stone-300 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 outline-none transition duration-200 bg-stone-50" 
                                    placeholder="Tell us about yourself"><?php echo htmlspecialchars($form_data['bio'] ?? ''); ?></textarea>
                                <div class="input-highlight"></div>
                            </div>
                            <p class="mt-1 text-xs text-stone-500">Maximum 200 characters</p>
                            <?php if (!empty($errors['bio'])): ?>
                                <p class="mt-1 text-sm text-red-600"><?php echo htmlspecialchars($errors['bio']); ?></p>
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
                        
                        <!-- Newsletter Subscription -->
                        <div class="flex items-start">
                            <div class="flex items-center h-5">
                                <input id="newsletter" name="newsletter" type="checkbox"
                                    class="focus:ring-teal-500 h-4 w-4 text-teal-600 border-stone-300 rounded">
                            </div>
                            <div class="ml-3 text-sm">
                                <label for="newsletter" class="font-medium text-stone-700">Subscribe to our newsletter</label>
                                <p class="text-stone-500">Get updates on new features and competitions</p>
                            </div>
                        </div>
                        
                        <div class="pt-6">
                            <h3 class="text-lg font-medium text-stone-800 mb-2">Review your information</h3>
                            <div class="bg-stone-50 rounded-lg p-4 space-y-3 text-sm text-stone-600">
                                <div class="flex">
                                    <span class="w-28 font-medium">Name:</span>
                                    <span id="review-name">
                                        <?php
                                            echo htmlspecialchars(
                                            (
                                                $form_data['first_name'] ?? '').' '.($form_data['last_name'] ?? '')
                                            );
                                        ?>
                                    </span>
                                </div>
                                <div class="flex">
                                    <span class="w-28 font-medium">Username:</span>
                                    <span id="review-username"><?php echo htmlspecialchars($form_data['username'] ?? ''); ?></span>
                                </div>
                                <div class="flex">
                                    <span class="w-28 font-medium">Email:</span>
                                    <span id="review-email"><?php echo htmlspecialchars($form_data['email'] ?? ''); ?></span>
                                </div>
                                <div class="flex">
                                    <span class="w-28 font-medium">Location:</span>
                                    <span id="review-location">
                                        <?php 
                                        echo htmlspecialchars(($form_data['city'] ?? '').', ');
                                        echo htmlspecialchars(($form_data['state'] ?? '').', ');
                                        echo htmlspecialchars($countries[$form_data['country'] ?? ''] ?? '');
                                        ?>
                                    </span>
                                </div>
                                <div class="flex">
                                    <span class="w-28 font-medium">Date of Birth:</span>
                                    <span id="review-dob"><?php echo htmlspecialchars($form_data['dob'] ?? ''); ?></span>
                                </div>
                                <div class="flex">
                                    <span class="w-28 font-medium">Gender:</span>
                                    <span id="review-gender">
                                        <?php 
                                        $gender = $form_data['gender'] ?? '';
                                        echo htmlspecialchars(ucfirst(str_replace('_', ' ', $gender)));
                                        ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="flex justify-between pt-4">
                            <button type="button" onclick="prevStep(3, 2)" class="flex items-center justify-center py-3 px-6 rounded-lg shadow-sm text-sm font-medium text-stone-700 bg-stone-100 hover:bg-stone-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-stone-500 transition duration-200">
                                <i class="fas fa-arrow-left mr-2"></i> Back
                            </button>
                            <!-- Submit Button -->
                            <button type="submit"
                                    class="flex items-center justify-center py-3 px-6 rounded-lg shadow-sm text-sm font-medium text-white bg-teal-600 hover:bg-teal-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-500 transition duration-200 group relative overflow-hidden">
                                <span class="relative z-10 group-hover:mr-2 transition-all duration-300">Create Account</span>
                                <i class="fas fa-arrow-right opacity-0 group-hover:opacity-100 transition-all duration-300 relative z-10"></i>
                                <span class="absolute inset-0 bg-gradient-to-r from-teal-600 to-teal-800 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></span>
                            </button>
                        </div>
                    </div>
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
        
        
        // Update the nextStep function to include progress line update
        function nextStep(currentStep, nextStep) {
            if (validateStep(currentStep)) {
                document.getElementById(`step-${currentStep}`).classList.remove('active');
                document.getElementById(`step-${nextStep}`).classList.add('active');
                
                // Update step indicators
                const currentIndicator = document.querySelector(`.step-indicator[data-step="${currentStep}"]`);
                currentIndicator.classList.remove('active');
                currentIndicator.classList.add('completed');
                
                const nextIndicator = document.querySelector(`.step-indicator[data-step="${nextStep}"]`);
                nextIndicator.classList.add('active');
                
                // Update progress line
                updateProgressLine(nextStep);
                
                // Update review information if going to step 3
                if (nextStep === 3) {
                    updateReviewInformation();
                }
            }
        }

        function updateProgressLine(currentStep) {
            const progressPercentage = ((currentStep - 1) / 2) * 100;
            document.getElementById('progress-line').style.width = `${progressPercentage}%`;
        }

        // Call this initially to set the correct progress
        updateProgressLine(1);

        // Form pagination functionality
        
        function prevStep(currentStep, prevStep) {
            // Hide current step
            document.getElementById(`step-${currentStep}`).classList.remove('active');
            // Show previous step
            document.getElementById(`step-${prevStep}`).classList.add('active');
            
            // Update step indicators
            document.querySelector(`.step-indicator[data-step="${currentStep}"]`).classList.remove('active');
            document.querySelector(`.step-indicator[data-step="${prevStep}"]`).classList.add('active');
        }
        
        function validateStep(step) {
            let isValid = true;
            const stepElement = document.getElementById(`step-${step}`);
            
            // Check all required fields in this step
            const inputs = stepElement.querySelectorAll('input[required], select[required]');
            inputs.forEach(input => {
                if (!input.value) {
                    input.classList.add('border-red-500');
                    isValid = false;
                } else {
                    input.classList.remove('border-red-500');
                }
                
                // Special validation for email
                if (input.type === 'email' && input.value) {
                    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    if (!emailRegex.test(input.value)) {
                        input.classList.add('border-red-500');
                        isValid = false;
                    }
                }
                
                // Special validation for password match
                if (input.id === 'confirm_password' && input.value) {
                    const password = document.getElementById('password').value;
                    if (password !== input.value) {
                        input.classList.add('border-red-500');
                        isValid = false;
                    }
                }
            });
            
            return isValid;
        }

        function updateReviewInformation() {
            document.getElementById('review-name').textContent = 
                document.getElementById('first_name').value + ' ' + document.getElementById('last_name').value;
            document.getElementById('review-username').textContent = document.getElementById('username').value;
            document.getElementById('review-email').textContent = document.getElementById('email').value;
            
            const countrySelect = document.getElementById('country');
            const countryName = countrySelect.options[countrySelect.selectedIndex].text;
            document.getElementById('review-location').textContent = 
                document.getElementById('city').value + ', ' + 
                document.getElementById('state').value + ', ' + 
                countryName;
            
            document.getElementById('review-dob').textContent = document.getElementById('dob').value;
            
            const genderRadios = document.querySelectorAll('input[name="gender"]');
            let selectedGender = '';
            genderRadios.forEach(radio => {
                if (radio.checked) {
                    selectedGender = radio.nextElementSibling.textContent;
                }
            });
            document.getElementById('review-gender').textContent = selectedGender;
        }

// Preview profile picture when selected
document.getElementById('profile_picture').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(event) {
            document.getElementById('profile-preview').src = event.target.result;
        };
        reader.readAsDataURL(file);
    }
});
        
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