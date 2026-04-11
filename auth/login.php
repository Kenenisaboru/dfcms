<?php
require_once '../config/config.php';

if (isset($_SESSION['user_id'])) {
    header("Location: ../dashboard.php");
    exit;
}

$error = '';
$emailValue = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    CSRF::validate($_POST['csrf_token']);
    
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $emailValue = $email;
    
    DebugLogger::log('baseline', 'H5', 'auth/login.php:POST', 'login_attempt', array('emailHash' => substr(sha1(strtolower($email)), 0, 12), 'passwordLen' => strlen((string)$password)));

    if (empty($email) || empty($password)) {
        $error = "Please fill in all fields.";
    } elseif (!$pdo) {
        $error = "Service temporarily unavailable. Please try again later.";
    } else {
        $stmt = $pdo->prepare("SELECT id, full_name, password, role FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            DebugLogger::log('baseline', 'H5', 'auth/login.php:POST', 'login_success', array('userId' => (int)$user['id'], 'role' => (string)$user['role']));
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['role'] = $user['role'];
            header("Location: ../dashboard.php");
            exit;
        } else {
            DebugLogger::log('baseline', 'H5', 'auth/login.php:POST', 'login_failed', array('emailHash' => substr(sha1(strtolower($email)), 0, 12)));
            $error = "Invalid email or password.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="DFCMS - Digital Feedback & Complaint Management System Login">
    <title>Sign In | DFCMS</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <!-- Modern Design System -->
    <link href="../assets/css/dfcms-modern.css" rel="stylesheet">
    
    <style>
        .login-page {
            min-height: 100vh;
            display: flex;
            background: var(--bg-primary);
            align-items: flex-start;
        }
        
        .login-visual {
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
        
        .login-visual::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: radial-gradient(circle at 70% 30%, rgba(16, 185, 129, 0.2) 0%, transparent 60%);
            pointer-events: none;
        }
        
        .login-visual-content {
            position: relative;
            z-index: 1;
            max-width: 540px;
        }
        
        .login-visual-badge {
            display: inline-flex;
            align-items: center;
            gap: var(--space-2);
            padding: var(--space-2) var(--space-4);
            background: rgba(16, 185, 129, 0.15);
            border: 1px solid rgba(16, 185, 129, 0.3);
            border-radius: var(--radius-full);
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: var(--primary-400);
            margin-bottom: var(--space-6);
        }
        
        .login-visual h1 {
            font-size: 3.5rem;
            font-weight: 800;
            line-height: 1.1;
            letter-spacing: -0.03em;
            margin-bottom: var(--space-6);
        }
        
        .login-visual p {
            font-size: 1.125rem;
            color: var(--text-secondary);
            line-height: 1.7;
            max-width: 480px;
        }
        
        .login-visual-features {
            display: flex;
            flex-direction: column;
            gap: var(--space-4);
            margin-top: var(--space-10);
        }
        
        .feature-item {
            display: flex;
            align-items: center;
            gap: var(--space-4);
        }
        
        .feature-icon {
            width: 48px;
            height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(16, 185, 129, 0.1);
            border: 1px solid rgba(16, 185, 129, 0.2);
            border-radius: var(--radius-lg);
            color: var(--primary-400);
            font-size: 1.25rem;
        }
        
        .feature-text {
            font-size: 0.9375rem;
            color: var(--text-secondary);
        }
        
        .feature-text strong {
            color: var(--text-primary);
            display: block;
            margin-bottom: var(--space-1);
        }
        
        .login-form-section {
            flex: 0.7;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: var(--space-8);
            background: radial-gradient(ellipse at center, rgba(16, 185, 129, 0.03) 0%, transparent 70%);
            min-height: 100vh;
            position: relative;
        }
        
        .login-card {
            width: 100%;
            max-width: 420px;
            padding: var(--space-10);
        }
        
        .login-header {
            text-align: center;
            margin-bottom: var(--space-8);
        }
        
        .login-logo {
            display: inline-flex;
            align-items: center;
            gap: var(--space-3);
            margin-bottom: var(--space-6);
        }
        
        .login-logo-icon {
            width: 48px;
            height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, var(--primary-500) 0%, var(--primary-600) 100%);
            border-radius: var(--radius-lg);
            color: white;
            font-size: 1.5rem;
        }
        
        .login-logo-text {
            font-size: 1.5rem;
            font-weight: 800;
            letter-spacing: -0.02em;
            color: var(--text-primary);
        }
        
        .login-header h2 {
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: var(--space-2);
        }
        
        .login-header p {
            font-size: 0.9375rem;
            color: var(--text-secondary);
        }
        
        .form-options {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: var(--space-6);
        }
        
        .remember-me {
            display: flex;
            align-items: center;
            gap: var(--space-2);
            font-size: 0.875rem;
            color: var(--text-secondary);
            cursor: pointer;
        }
        
        .forgot-password {
            font-size: 0.875rem;
            color: var(--primary-400);
            text-decoration: none;
            font-weight: 500;
            transition: color var(--transition-fast);
        }
        
        .forgot-password:hover {
            color: var(--primary-300);
        }
        
        .login-footer {
            text-align: center;
            margin-top: var(--space-8);
            padding-top: var(--space-6);
            border-top: 1px solid var(--glass-border);
        }
        
        .login-footer p {
            font-size: 0.9375rem;
            color: var(--text-secondary);
        }
        
        .login-footer a {
            color: var(--primary-400);
            text-decoration: none;
            font-weight: 600;
            transition: color var(--transition-fast);
        }
        
        .login-footer a:hover {
            color: var(--primary-300);
        }
        
        .back-link {
            display: inline-flex;
            align-items: center;
            gap: var(--space-2);
            margin-top: var(--space-4);
            font-size: 0.875rem;
            color: var(--text-tertiary);
            text-decoration: none;
            transition: color var(--transition-fast);
        }
        
        .back-link:hover {
            color: var(--text-secondary);
        }
        
        /* Error shake animation */
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            10%, 30%, 50%, 70%, 90% { transform: translateX(-4px); }
            20%, 40%, 60%, 80% { transform: translateX(4px); }
        }
        
        .shake {
            animation: shake 0.5s ease-in-out;
        }
        
        /* Responsive */
        @media (max-width: 1024px) {
            .login-visual {
                flex: 1;
                padding: var(--space-10);
            }
            
            .login-visual h1 {
                font-size: 2.5rem;
            }
        }
        
        @media (max-width: 768px) {
            .login-visual {
                display: none;
            }
            
            .login-form-section {
                flex: 1;
                padding: var(--space-6);
            }
            
            .login-card {
                padding: var(--space-6);
            }
        }
    </style>
</head>
<body>
    <main class="login-page">
        <!-- Visual Side -->
        <section class="login-visual animate-fade-in">
            <div class="login-visual-content">
                <div class="login-visual-badge">
                    <i class="fas fa-shield-alt"></i>
                    Secure & Encrypted
                </div>
                <h1>Action Begins<br><span class="text-accent">With You.</span></h1>
                <p>Welcome back to the Digital Feedback & Complaint Management System. Transform your feedback into actionable solutions through transparent communication.</p>
                
                <div class="login-visual-features">
                    <div class="feature-item">
                        <div class="feature-icon">
                            <i class="fas fa-bolt"></i>
                        </div>
                        <div class="feature-text">
                            <strong>Lightning Fast</strong>
                            Instant complaint routing and real-time status updates
                        </div>
                    </div>
                    <div class="feature-item">
                        <div class="feature-icon">
                            <i class="fas fa-lock"></i>
                        </div>
                        <div class="feature-text">
                            <strong>End-to-End Encrypted</strong>
                            Your data is protected with enterprise-grade security
                        </div>
                    </div>
                    <div class="feature-item">
                        <div class="feature-icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <div class="feature-text">
                            <strong>Real-time Analytics</strong>
                            Track resolution progress and system performance
                        </div>
                    </div>
                </div>
            </div>
        </section>
        
        <!-- Form Side -->
        <section class="login-form-section">
            <div class="card glass-card login-card animate-slide-in-right">
                <div class="login-header">
                    <div class="login-logo">
                        <div class="login-logo-icon">
                            <i class="fas fa-university"></i>
                        </div>
                        <span class="login-logo-text">DFCMS</span>
                    </div>
                    <h2>Welcome Back</h2>
                    <p>Sign in to access your dashboard</p>
                </div>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger mb-6" role="alert">
                        <div class="alert-icon">
                            <i class="fas fa-exclamation-circle"></i>
                        </div>
                        <div class="alert-content">
                            <div class="alert-title">Authentication Failed</div>
                            <?php echo htmlspecialchars($error); ?>
                        </div>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="" id="loginForm" novalidate>
                    <?php echo CSRF::input(); ?>
                    
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
                                value="<?php echo htmlspecialchars($emailValue); ?>"
                                required
                                autocomplete="email"
                            >
                        </div>
                        <div class="form-feedback" id="email-feedback"></div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="password">
                            <i class="fas fa-lock text-accent me-2"></i>Password
                        </label>
                        <div class="input-group">
                            <i class="fas fa-lock input-icon"></i>
                            <input 
                                type="password" 
                                id="password"
                                name="password" 
                                class="form-input" 
                                placeholder="Enter your password"
                                required
                                autocomplete="current-password"
                            >
                            <button type="button" class="btn btn-ghost btn-icon" id="togglePassword" style="position: absolute; right: var(--space-3); top: 50%; transform: translateY(-50%);" aria-label="Toggle password visibility">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <div class="form-feedback" id="password-feedback"></div>
                    </div>
                    
                    <div class="form-options">
                        <label class="remember-me">
                            <input type="checkbox" name="remember" class="form-check-input">
                            <span>Remember me</span>
                        </label>
                        <a href="#" class="forgot-password">Forgot password?</a>
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-lg w-100" id="submitBtn">
                        <i class="fas fa-sign-in-alt"></i>
                        Sign In
                    </button>
                </form>
                
                <div class="login-footer">
                    <p>Don't have an account? <a href="register.php">Create one now</a></p>
                    <a href="../index.php" class="back-link">
                        <i class="fas fa-arrow-left"></i>
                        Back to Home
                    </a>
                </div>
            </div>
        </section>
    </main>
    
    <!-- Modern UI Framework -->
    <script src="../assets/js/dfcms-ui.js"></script>
    
    <script>
        (function() {
            const form = document.getElementById('loginForm');
            const submitBtn = document.getElementById('submitBtn');
            const togglePassword = document.getElementById('togglePassword');
            const passwordInput = document.getElementById('password');
            
            // Password visibility toggle
            togglePassword.addEventListener('click', function() {
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                this.querySelector('i').classList.toggle('fa-eye');
                this.querySelector('i').classList.toggle('fa-eye-slash');
            });
            
            // Form validation
            const validator = new DFCMS.FormValidator(form, {
                validateOnBlur: true,
                showInlineErrors: true
            });
            
            validator.rules({
                email: {
                    required: true,
                    email: true,
                    requiredMessage: 'Please enter your email address',
                    emailMessage: 'Please enter a valid email address'
                },
                password: {
                    required: true,
                    minLength: 1,
                    requiredMessage: 'Please enter your password'
                }
            });
            
            // Form submission
            form.addEventListener('submit', function(e) {
                if (!validator.validate()) {
                    e.preventDefault();
                    
                    // Shake animation on error
                    form.closest('.glass-card').classList.add('shake');
                    setTimeout(() => {
                        form.closest('.glass-card').classList.remove('shake');
                    }, 500);
                    
                    // Show toast notification
                    DFCMS.toast.error('Please correct the errors before signing in.');
                    return;
                }
                
                // Show loading state
                const restoreButton = DFCMS.LoadingManager.button(submitBtn, 'Signing in...');
                
                // Form will submit normally
            });
            
            // Focus email field on load
            document.getElementById('email').focus();
        })();
    </script>
</body>
</html>
