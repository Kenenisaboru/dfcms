<?php
require_once '../config/config.php';

if (isset($_SESSION['user_id'])) {
    header("Location: ../dashboard.php");
    exit;
}

$error = '';
$success = '';
$formData = ['full_name' => '', 'email' => '', 'role' => ''];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    CSRF::validate($_POST['csrf_token']);
    
    $fullName = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $role = $_POST['role'];
    
    $formData = ['full_name' => $fullName, 'email' => $email, 'role' => $role];

    $allowedRoles = ['student', 'cr', 'teacher', 'lab_assistant', 'hod'];

    if (empty($fullName) || empty($email) || empty($password) || empty($role)) {
        $error = "All fields are required.";
    } elseif (strlen($fullName) < 3) {
        $error = "Full name must be at least 3 characters long.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } elseif (!in_array($role, $allowedRoles)) {
        $error = "Invalid role selected.";
    } elseif (strlen($password) < 8) {
        $error = "Password must be at least 8 characters long.";
    } elseif (!$pdo) {
        $error = "Service temporarily unavailable. Please try again later.";
    } else {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = "This email is already registered. Please use a different email or sign in.";
        } else {
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $pdo->prepare("INSERT INTO users (full_name, email, password, role) VALUES (?, ?, ?, ?)");
            if ($stmt->execute([$fullName, $email, $hashedPassword, $role])) {
                $success = "Account created successfully! Please sign in to continue.";
                $formData = ['full_name' => '', 'email' => '', 'role' => ''];
            } else {
                $error = "Registration failed. Please try again.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Create your DFCMS account - Digital Feedback & Complaint Management System">
    <title>Create Account | DFCMS</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <link href="../assets/css/dfcms-modern.css" rel="stylesheet">
    
    <style>
        .register-page {
            min-height: 100vh;
            display: flex;
            background: var(--bg-primary);
            align-items: flex-start;
        }
        
        .register-visual {
            flex: 1.3;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: var(--space-16);
            background: 
                linear-gradient(135deg, rgba(16, 185, 129, 0.25) 0%, var(--bg-primary) 100%),
                var(--gradient-mesh);
            background-size: cover;
            background-position: center;
            position: sticky;
            top: 0;
            height: 100vh;
            overflow: hidden;
        }
        
        .register-visual::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: radial-gradient(circle at 30% 70%, rgba(16, 185, 129, 0.15) 0%, transparent 50%);
            pointer-events: none;
        }
        
        .register-visual-content {
            position: relative;
            z-index: 1;
            max-width: 520px;
        }
        
        .register-visual h1 {
            font-size: 3.25rem;
            font-weight: 800;
            line-height: 1.1;
            letter-spacing: -0.03em;
            margin-bottom: var(--space-6);
        }
        
        .register-visual p {
            font-size: 1.125rem;
            color: var(--text-secondary);
            line-height: 1.7;
        }
        
        .stats-row {
            display: flex;
            gap: var(--space-8);
            margin-top: var(--space-10);
        }
        
        .stat-item h3 {
            font-size: 2.5rem;
            font-weight: 800;
            color: var(--primary-400);
            line-height: 1;
        }
        
        .stat-item p {
            font-size: 0.875rem;
            color: var(--text-tertiary);
            margin-top: var(--space-1);
        }
        
        .register-form-section {
            flex: 0.7;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: var(--space-8);
            background: radial-gradient(ellipse at center, rgba(16, 185, 129, 0.03) 0%, transparent 70%);
            min-height: 100vh;
        }
        
        .register-card {
            width: 100%;
            max-width: 440px;
            padding: var(--space-10);
        }
        
        .register-header {
            text-align: center;
            margin-bottom: var(--space-8);
        }
        
        .register-header h2 {
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: var(--space-2);
        }
        
        .register-header p {
            font-size: 0.9375rem;
            color: var(--text-secondary);
        }
        
        .progress-steps {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: var(--space-2);
            margin-bottom: var(--space-8);
        }
        
        .step {
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: var(--radius-full);
            background: var(--bg-tertiary);
            border: 2px solid var(--glass-border);
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--text-tertiary);
        }
        
        .step.active {
            background: var(--primary-500);
            border-color: var(--primary-500);
            color: white;
        }
        
        .step-line {
            width: 40px;
            height: 2px;
            background: var(--glass-border);
        }
        
        .password-strength {
            margin-top: var(--space-2);
        }
        
        .strength-bar {
            height: 4px;
            background: var(--bg-tertiary);
            border-radius: var(--radius-full);
            overflow: hidden;
        }
        
        .strength-fill {
            height: 100%;
            width: 0;
            border-radius: var(--radius-full);
            transition: all var(--transition-base);
        }
        
        .strength-fill.weak { width: 33%; background: var(--danger); }
        .strength-fill.fair { width: 66%; background: var(--warning); }
        .strength-fill.strong { width: 100%; background: var(--success); }
        
        .strength-text {
            font-size: 0.75rem;
            margin-top: var(--space-1);
            color: var(--text-tertiary);
        }
        
        .role-cards {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: var(--space-3);
            margin-top: var(--space-2);
        }
        
        .role-card {
            padding: var(--space-4);
            background: var(--bg-tertiary);
            border: 2px solid transparent;
            border-radius: var(--radius-lg);
            cursor: pointer;
            text-align: center;
            transition: all var(--transition-fast);
        }
        
        .role-card:hover {
            border-color: var(--glass-border);
            background: var(--glass-highlight);
        }
        
        .role-card.selected {
            border-color: var(--primary-500);
            background: rgba(16, 185, 129, 0.1);
        }
        
        .role-card i {
            font-size: 1.5rem;
            color: var(--text-tertiary);
            margin-bottom: var(--space-2);
            transition: color var(--transition-fast);
        }
        
        .role-card.selected i {
            color: var(--primary-400);
        }
        
        .role-card span {
            display: block;
            font-size: 0.8125rem;
            font-weight: 500;
            color: var(--text-secondary);
        }
        
        .role-card.selected span {
            color: var(--text-primary);
        }
        
        .role-input {
            position: absolute;
            opacity: 0;
        }
        
        .register-footer {
            text-align: center;
            margin-top: var(--space-8);
            padding-top: var(--space-6);
            border-top: 1px solid var(--glass-border);
        }
        
        .register-footer p {
            font-size: 0.9375rem;
            color: var(--text-secondary);
        }
        
        .register-footer a {
            color: var(--primary-400);
            text-decoration: none;
            font-weight: 600;
            transition: color var(--transition-fast);
        }
        
        .register-footer a:hover {
            color: var(--primary-300);
        }
        
        @media (max-width: 1024px) {
            .register-visual {
                flex: 1;
                padding: var(--space-10);
            }
            
            .register-visual h1 {
                font-size: 2.5rem;
            }
        }
        
        @media (max-width: 768px) {
            .register-visual {
                display: none;
            }
            
            .register-form-section {
                flex: 1;
                padding: var(--space-6);
            }
            
            .register-card {
                padding: var(--space-6);
            }
            
            .role-cards {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <main class="register-page">
        <section class="register-visual animate-fade-in">
            <div class="register-visual-content">
                <h1>Your Voice<br><span class="text-accent">Drives Excellence.</span></h1>
                <p>Join the University Digital Feedback System. Help us build a more transparent and efficient academic environment through collaborative feedback management.</p>
                
                <div class="stats-row">
                    <div class="stat-item">
                        <h3>5K+</h3>
                        <p>Active Users</p>
                    </div>
                    <div class="stat-item">
                        <h3>98%</h3>
                        <p>Resolution Rate</p>
                    </div>
                    <div class="stat-item">
                        <h3>24h</h3>
                        <p>Avg Response</p>
                    </div>
                </div>
            </div>
        </section>
        
        <section class="register-form-section">
            <div class="card glass-card register-card animate-slide-in-right">
                <div class="register-header">
                    <h2>Create Account</h2>
                    <p>Fill in your details to get started</p>
                </div>
                
                <?php if ($success): ?>
                    <div class="alert alert-success mb-6" role="alert">
                        <div class="alert-icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="alert-content">
                            <div class="alert-title">Success!</div>
                            <?php echo htmlspecialchars($success); ?>
                        </div>
                    </div>
                    <div class="text-center mb-6">
                        <a href="login.php" class="btn btn-primary">
                            <i class="fas fa-sign-in-alt"></i>
                            Sign In Now
                        </a>
                    </div>
                <?php else: ?>
                    <?php if ($error): ?>
                        <div class="alert alert-danger mb-6" role="alert">
                            <div class="alert-icon">
                                <i class="fas fa-exclamation-circle"></i>
                            </div>
                            <div class="alert-content">
                                <div class="alert-title">Registration Error</div>
                                <?php echo htmlspecialchars($error); ?>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="" id="registerForm" novalidate>
                        <?php echo CSRF::input(); ?>
                        
                        <div class="form-group">
                            <label class="form-label" for="full_name">
                                <i class="fas fa-user text-accent me-2"></i>Full Name
                            </label>
                            <div class="input-group">
                                <i class="fas fa-user input-icon"></i>
                                <input 
                                    type="text" 
                                    id="full_name"
                                    name="full_name" 
                                    class="form-input" 
                                    placeholder="Enter your full name"
                                    value="<?php echo htmlspecialchars($formData['full_name']); ?>"
                                    required
                                    autocomplete="name"
                                >
                            </div>
                            <div class="form-feedback" id="full_name-feedback"></div>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label" for="email">
                                <i class="fas fa-envelope text-accent me-2"></i>Email Address
                            </label>
                            <div class="input-group">
                                <i class="fas fa-envelope input-icon"></i>
                                <input 
                                    type="email" 
                                    id="email"
                                    name="email" 
                                    class="form-input" 
                                    placeholder="you@university.edu"
                                    value="<?php echo htmlspecialchars($formData['email']); ?>"
                                    required
                                    autocomplete="email"
                                >
                            </div>
                            <div class="form-feedback" id="email-feedback"></div>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-user-tag text-accent me-2"></i>Select Your Role
                            </label>
                            <div class="role-cards">
                                <label class="role-card <?php echo $formData['role'] === 'student' ? 'selected' : ''; ?>">
                                    <input type="radio" name="role" value="student" class="role-input" <?php echo $formData['role'] === 'student' ? 'checked' : ''; ?> required>
                                    <i class="fas fa-graduation-cap"></i>
                                    <span>Student</span>
                                </label>
                                <label class="role-card <?php echo $formData['role'] === 'cr' ? 'selected' : ''; ?>">
                                    <input type="radio" name="role" value="cr" class="role-input" <?php echo $formData['role'] === 'cr' ? 'checked' : ''; ?>>
                                    <i class="fas fa-users"></i>
                                    <span>Class Rep</span>
                                </label>
                                <label class="role-card <?php echo $formData['role'] === 'teacher' ? 'selected' : ''; ?>">
                                    <input type="radio" name="role" value="teacher" class="role-input" <?php echo $formData['role'] === 'teacher' ? 'checked' : ''; ?>>
                                    <i class="fas fa-chalkboard-teacher"></i>
                                    <span>Teacher</span>
                                </label>
                                <label class="role-card <?php echo $formData['role'] === 'hod' ? 'selected' : ''; ?>">
                                    <input type="radio" name="role" value="hod" class="role-input" <?php echo $formData['role'] === 'hod' ? 'checked' : ''; ?>>
                                    <i class="fas fa-user-tie"></i>
                                    <span>HOD</span>
                                </label>
                            </div>
                            <div class="form-feedback" id="role-feedback"></div>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label" for="password">
                                <i class="fas fa-lock text-accent me-2"></i>Create Password
                            </label>
                            <div class="input-group">
                                <i class="fas fa-lock input-icon"></i>
                                <input 
                                    type="password" 
                                    id="password"
                                    name="password" 
                                    class="form-input" 
                                    placeholder="Min. 8 characters"
                                    required
                                    autocomplete="new-password"
                                >
                                <button type="button" class="btn btn-ghost btn-icon" id="togglePassword" style="position: absolute; right: var(--space-3); top: 50%; transform: translateY(-50%);" aria-label="Toggle password visibility">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <div class="password-strength">
                                <div class="strength-bar">
                                    <div class="strength-fill" id="strengthFill"></div>
                                </div>
                                <div class="strength-text" id="strengthText">Enter a password</div>
                            </div>
                            <div class="form-feedback" id="password-feedback"></div>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-check" style="cursor: pointer;">
                                <input type="checkbox" name="terms" class="form-check-input" required>
                                <span>I agree to the <a href="#" class="text-accent">Terms of Service</a> and <a href="#" class="text-accent">Privacy Policy</a></span>
                            </label>
                            <div class="form-feedback" id="terms-feedback"></div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary btn-lg w-100" id="submitBtn">
                            <i class="fas fa-user-plus"></i>
                            Create Account
                        </button>
                    </form>
                <?php endif; ?>
                
                <div class="register-footer">
                    <p>Already have an account? <a href="login.php">Sign in</a></p>
                </div>
            </div>
        </section>
    </main>
    
    <script src="../assets/js/dfcms-ui.js"></script>
    
    <script>
        (function() {
            const form = document.getElementById('registerForm');
            if (!form) return;
            
            const submitBtn = document.getElementById('submitBtn');
            const togglePassword = document.getElementById('togglePassword');
            const passwordInput = document.getElementById('password');
            const strengthFill = document.getElementById('strengthFill');
            const strengthText = document.getElementById('strengthText');
            const roleCards = document.querySelectorAll('.role-card');
            
            // Password visibility toggle
            togglePassword.addEventListener('click', function() {
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                this.querySelector('i').classList.toggle('fa-eye');
                this.querySelector('i').classList.toggle('fa-eye-slash');
            });
            
            // Role card selection
            roleCards.forEach(card => {
                card.addEventListener('click', function() {
                    roleCards.forEach(c => c.classList.remove('selected'));
                    this.classList.add('selected');
                    this.querySelector('input').checked = true;
                });
            });
            
            // Password strength checker
            passwordInput.addEventListener('input', function() {
                const password = this.value;
                let strength = 0;
                
                if (password.length >= 8) strength++;
                if (password.match(/[a-z]/) && password.match(/[A-Z]/)) strength++;
                if (password.match(/[0-9]/)) strength++;
                if (password.match(/[^a-zA-Z0-9]/)) strength++;
                
                strengthFill.className = 'strength-fill';
                
                if (password.length === 0) {
                    strengthText.textContent = 'Enter a password';
                } else if (strength <= 1) {
                    strengthFill.classList.add('weak');
                    strengthText.textContent = 'Weak password - Add more complexity';
                    strengthText.style.color = 'var(--danger)';
                } else if (strength === 2) {
                    strengthFill.classList.add('fair');
                    strengthText.textContent = 'Fair password - Could be stronger';
                    strengthText.style.color = 'var(--warning)';
                } else {
                    strengthFill.classList.add('strong');
                    strengthText.textContent = 'Strong password - Great job!';
                    strengthText.style.color = 'var(--success)';
                }
            });
            
            // Form validation
            const validator = new DFCMS.FormValidator(form, {
                validateOnBlur: true,
                showInlineErrors: true
            });
            
            validator.rules({
                full_name: {
                    required: true,
                    minLength: 3,
                    requiredMessage: 'Please enter your full name',
                    minLengthMessage: 'Name must be at least 3 characters'
                },
                email: {
                    required: true,
                    email: true,
                    requiredMessage: 'Please enter your email address',
                    emailMessage: 'Please enter a valid email address'
                },
                role: {
                    required: true,
                    requiredMessage: 'Please select your role'
                },
                password: {
                    required: true,
                    minLength: 8,
                    requiredMessage: 'Please create a password',
                    minLengthMessage: 'Password must be at least 8 characters'
                },
                terms: {
                    custom: function(value) {
                        const checkbox = form.querySelector('[name="terms"]');
                        return checkbox.checked ? null : 'You must agree to the terms';
                    }
                }
            });
            
            // Form submission
            form.addEventListener('submit', function(e) {
                if (!validator.validate()) {
                    e.preventDefault();
                    
                    DFCMS.toast.error('Please correct the errors before submitting.');
                    return;
                }
                
                DFCMS.LoadingManager.button(submitBtn, 'Creating account...');
            });
        })();
    </script>
</body>
</html>
