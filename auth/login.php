<?php
// auth/login.php
session_start();
require_once '../config/database.php';

if (isset($_SESSION['user_id'])) {
    header("Location: ../dashboard.php");
    exit;
}

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $error = "Please fill in all fields.";
    } elseif (!$pdo) {
        $error = "DATABASE CONNECTION FAILED. Please check credentials in config/database.php.";
    } else {
        $stmt = $pdo->prepare("SELECT id, full_name, password, role FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['role'] = $user['role'];
            header("Location: ../dashboard.php");
            exit;
        } else {
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
        body { 
            background-color: #0f1012; 
            color: #fff; 
            margin: 0; 
            padding: 0; 
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', sans-serif;
        }
        .main-wrapper {
            display: flex;
            width: 100%;
            height: 100vh;
            overflow: hidden;
        }
        .content-left {
            flex: 1.2;
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.2), rgba(0, 0, 0, 0.9)), url('https://images.unsplash.com/photo-1434030216411-0b793f4b4173?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80');
            background-size: cover;
            background-position: center;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 80px;
            color: #fff;
        }
        .content-right {
            flex: 0.8;
            background-color: #121212;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px;
            border-left: 1px solid #333;
        }
        .card h2 { color: #ffffff !important; }
        .text-muted { color: #cfcfcf !important; }
        .form-control { 
            background-color: #eef2f7 !important; 
            border: 1px solid #444; 
            color: #000 !important; 
            padding: 12px;
            border-radius: 8px;
        }
        .form-control::placeholder { color: #555 !important; }
        .form-control:focus { 
            background-color: #ffffff !important; 
            color: #000; 
            border-color: #3b82f6; 
            box-shadow: 0 0 0 0.25rem rgba(59, 130, 246, 0.25); 
        }
        .btn-primary { 
            background-color: #3b82f6; 
            border: none; 
            padding: 14px;
            font-weight: bold;
            border-radius: 8px;
            transition: 0.3s;
        }
        .btn-primary:hover { 
            background-color: #2563eb; 
            transform: translateY(-2px);
        }
        .text-accent { color: #3b82f6; } /* Blue for Login */
        .quote-icon { font-size: 3rem; color: #3b82f6; margin-bottom: 20px; opacity: 0.5; }
        .motivational-speech h1 { font-size: 3.5rem; font-weight: 800; margin-bottom: 20px; line-height: 1.1; }
        .motivational-speech p { font-size: 1.25rem; color: #ccc; line-height: 1.6; }

        @media (max-width: 992px) {
            .content-left { display: none; }
            .content-right { flex: 1; }
        }
    </style>
</head>
<body>
    <div class="main-wrapper">
        <!-- Motivational Left Side -->
        <div class="content-left">
            <div class="quote-icon"><i class="fas fa-quote-left"></i></div>
            <div class="motivational-speech">
                <h1 class="text-white">Action Begins <br><span class="text-accent">With You.</span></h1>
                <p>Welcome back to the University Digital Feedback & Complaint Management System. Success isn't about avoiding obstacles; it's about resolving them together with transparent communication and decisive action.</p>
                <div class="mt-4 d-flex align-items-center">
                    <div style="width: 50px; height: 2px; background: #3b82f6; margin-right: 15px;"></div>
                    <span class="text-uppercase fw-bold text-accent" style="letter-spacing: 2px;">Excellence Through Feedback</span>
                </div>
            </div>
        </div>

        <!-- Login Right Side -->
        <div class="content-right">
            <div class="card p-2">
                <h2 class="mb-2 fw-bold">Sign In</h2>
                <p class="text-muted mb-4">Access Your Secure <span class="text-accent">DFCMS</span> Hub.</p>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger py-2"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <div class="mb-3">
                        <label class="form-label text-muted small">Registered Email Address</label>
                        <input type="email" name="email" class="form-control" placeholder="example@university.edu" required>
                    </div>
                    <div class="mb-4">
                        <label class="form-label text-muted small">Password</label>
                        <input type="password" name="password" class="form-control" placeholder="••••••••••••" required>
                    </div>
                    
                    <button type="submit" class="btn btn-primary w-100 shadow"><i class="fas fa-sign-in-alt me-2"></i> Enter Dashboard</button>
                    
                    <div class="text-center mt-3">
                        <span class="text-muted small">New to the system? </span><a href="register.php" class="text-decoration-none text-accent fw-bold small">Create Account</a>
                    </div>
                    <div class="text-center mt-2">
                        <a href="../index.php" class="text-decoration-none text-muted small"><i class="fas fa-arrow-left me-1"></i> Back to Landing</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
