<?php
require_once '../config/config.php';

if (isset($_SESSION['user_id'])) {
    header("Location: ../dashboard.php");
    exit;
}

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate CSRF
    CSRF::validate($_POST['csrf_token']);
    
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    // #region agent log
    DebugLogger::log('baseline', 'H5', 'auth/login.php:POST', 'login_attempt', array('emailHash' => substr(sha1(strtolower($email)), 0, 12), 'passwordLen' => strlen((string)$password)));
    // #endregion

    if (empty($email) || empty($password)) {
        $error = "Please fill in all fields.";
    } elseif (!$pdo) {
        $error = "Service temporarily unavailable. Please try again later.";
    } else {
        $stmt = $pdo->prepare("SELECT id, full_name, password, role FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            // #region agent log
            DebugLogger::log('baseline', 'H5', 'auth/login.php:POST', 'login_success', array('userId' => (int)$user['id'], 'role' => (string)$user['role']));
            // #endregion
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['role'] = $user['role'];
            header("Location: ../dashboard.php");
            exit;
        } else {
            // #region agent log
            DebugLogger::log('baseline', 'H5', 'auth/login.php:POST', 'login_failed', array('emailHash' => substr(sha1(strtolower($email)), 0, 12)));
            // #endregion
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
    <title>Login - DFCMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #10b981;
            --primary-glow: rgba(16, 185, 129, 0.4);
            --bg-dark: #0c0d0e;
            --card-bg: rgba(18, 18, 18, 0.7);
            --glass-border: rgba(255, 255, 255, 0.1);
            --text-light: #f8fafc;
            --text-dim: #94a3b8;
        }

        body { 
            background-color: var(--bg-dark); 
            color: var(--text-light); 
            font-family: 'Inter', sans-serif;
            margin: 0; 
            padding: 0; 
            height: 100vh;
            overflow: hidden;
        }

        .main-wrapper {
            display: flex;
            width: 100%;
            height: 100vh;
        }

        .content-left {
            flex: 1.2;
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.2), rgba(0, 0, 0, 0.9)), url('https://images.unsplash.com/photo-1434030216411-0b793f4b4173?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80');
            background-size: cover;
            background-position: center;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 80px;
            color: #fff;
            animation: fadeIn 1s ease-out;
        }

        .content-right {
            flex: 0.8;
            background-color: var(--bg-dark);
            background-image: radial-gradient(circle at center, rgba(16, 185, 129, 0.05) 0%, transparent 70%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px;
            border-left: 1px solid var(--glass-border);
            animation: slideIn 0.8s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes slideIn {
            from { opacity: 0; transform: translateX(50px); }
            to { opacity: 1; transform: translateX(0); }
        }

        .glass-card {
            background: var(--card-bg);
            backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: 24px;
            padding: 40px;
            width: 100%;
            max-width: 450px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
        }

        .form-label { 
            color: var(--text-light) !important; 
            font-size: 0.85rem;
            font-weight: 500;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
        }

        .form-control { 
            background-color: #eef2f7 !important; 
            border: 1px solid var(--glass-border) !important; 
            color: #1e293b !important; 
            padding: 14px 18px;
            border-radius: 12px;
            transition: all 0.3s ease;
        }

        .form-control:focus { 
            box-shadow: 0 0 0 4px var(--primary-glow) !important;
            border-color: var(--primary) !important;
        }

        .btn-primary { 
            background: linear-gradient(135deg, var(--primary) 0%, #059669 100%);
            border: none; 
            padding: 16px;
            font-weight: 700;
            border-radius: 12px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            text-transform: uppercase;
            letter-spacing: 1px;
            font-size: 0.9rem;
        }

        .btn-primary:hover { 
            transform: translateY(-3px); 
            box-shadow: 0 10px 20px -5px var(--primary-glow);
            color: #fff;
        }

        .text-accent { color: var(--primary) !important; }
        .text-dim { color: var(--text-dim) !important; }

        .quote-icon { font-size: 3rem; color: var(--primary); margin-bottom: 20px; opacity: 0.5; }
        .motivational-speech h1 { font-size: 3.5rem; font-weight: 800; margin-bottom: 20px; line-height: 1.1; }
        .motivational-speech p { font-size: 1.2rem; color: #cbd5e1; line-height: 1.6; max-width: 600px; }

        @media (max-width: 992px) {
            .content-left { display: none; }
            .content-right { flex: 1; border-left: none; }
        }
    </style>
</head>
<body>
    <div class="main-wrapper">
        <div class="content-left">
            <div class="quote-icon"><i class="fas fa-quote-left"></i></div>
            <div class="motivational-speech">
                <h1 class="text-white">Action Begins <br><span class="text-accent">With You.</span></h1>
                <p>Welcome back to the Digital Feedback & Complaint Management System. Success isn't about avoiding obstacles; it's about resolving them together with transparent communication.</p>
                <div class="mt-4 d-flex align-items-center">
                    <div style="width: 50px; height: 3px; background: var(--primary); margin-right: 15px; border-radius: 2px;"></div>
                    <span class="text-uppercase fw-bold text-accent" style="letter-spacing: 2px;">Excellence Through Feedback</span>
                </div>
            </div>
        </div>

        <div class="content-right">
            <div class="glass-card">
                <div class="text-center mb-4">
                    <h2 class="fw-bold text-white mb-2">Sign In</h2>
                    <p class="text-dim">Access Your Secure <span class="text-accent fw-bold">DFCMS</span> Hub.</p>
                </div>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger py-2 border-0 bg-danger bg-opacity-10 text-danger small"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <?php echo CSRF::input(); ?>
                    <div class="mb-3">
                        <label class="form-label"><i class="fas fa-envelope text-accent me-1"></i> Registered Email Address</label>
                        <input type="email" name="email" class="form-control" placeholder="example@university.edu" required>
                    </div>
                    <div class="mb-4">
                        <label class="form-label"><i class="fas fa-lock text-accent me-1"></i> Password</label>
                        <input type="password" name="password" class="form-control" placeholder="••••••••••••" required>
                    </div>
                    
                    <button type="submit" class="btn btn-primary w-100 mb-4"><i class="fas fa-sign-in-alt me-2"></i> Enter Dashboard</button>
                    
                    <div class="text-center mt-3">
                        <span class="text-dim small">New to the system? </span><a href="register.php" class="text-decoration-none text-accent fw-bold small">Create Account</a>
                    </div>
                    <div class="text-center mt-3">
                        <a href="../index.php" class="text-decoration-none text-dim small hover-accent"><i class="fas fa-arrow-left me-1"></i> Back to Landing</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
