<?php
// auth/register.php
session_start();
require_once '../config/database.php';

if (isset($_SESSION['user_id'])) {
    header("Location: ../dashboard.php");
    exit;
}

$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fullName = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $role = $_POST['role'];

    $allowedRoles = ['student', 'cr', 'teacher', 'lab_assistant', 'hod'];

    if (empty($fullName) || empty($email) || empty($password) || empty($role)) {
        $error = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } elseif (!in_array($role, $allowedRoles)) {
        $error = "Invalid role selected.";
    } elseif (!$pdo) {
        $error = "DATABASE CONNECTION FAILED. Please check credentials in config/database.php.";
    } else {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = "Email is already registered.";
        } else {
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $pdo->prepare("INSERT INTO users (full_name, email, password, role) VALUES (?, ?, ?, ?)");
            if ($stmt->execute([$fullName, $email, $hashedPassword, $role])) {
                $success = "Registration successful! You can now login.";
            } else {
                $error = "Something went wrong. Please try again.";
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
    <title>Register - DFCMS</title>
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
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.4), rgba(0, 0, 0, 0.9)), url('https://images.unsplash.com/photo-1517245386807-bb43f82c33c4?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80');
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
        .card {
            background-color: transparent;
            border: none;
            width: 100%;
            max-width: 420px;
        }
        .form-control, .form-select { 
            background-color: #1e1e1e; 
            border: 1px solid #444; 
            color: #fff; 
            padding: 12px;
            border-radius: 8px;
        }
        .form-control:focus, .form-select:focus { 
            background-color: #252525; 
            color: #fff; 
            border-color: #10b981; 
            box-shadow: 0 0 0 0.25rem rgba(16, 185, 129, 0.25); 
        }
        .btn-primary { 
            background-color: #10b981; 
            border: none; 
            padding: 14px;
            font-weight: bold;
            border-radius: 8px;
            transition: 0.3s;
        }
        .btn-primary:hover { 
            background-color: #059669; 
            transform: translateY(-2px);
        }
        .text-accent { color: #10b981; }
        .quote-icon { font-size: 3rem; color: #10b981; margin-bottom: 20px; opacity: 0.8; }
        .motivational-speech h1 { 
            font-size: 3.5rem; 
            font-weight: 800; 
            margin-bottom: 20px; 
            line-height: 1.1; 
            text-shadow: 2px 2px 15px rgba(0,0,0,0.9), 0 0 20px rgba(0,0,0,0.5);
        }
        .motivational-speech p { 
            font-size: 1.25rem; 
            color: #ffffff; 
            line-height: 1.6; 
            text-shadow: 1px 1px 10px rgba(0,0,0,0.9);
        }
        .motivational-speech .text-accent { 
            color: #34d399 !important; /* Brighter emerald for visibility */
            text-shadow: 0 0 15px rgba(16, 185, 129, 0.4);
        }

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
                <h1 class="text-white">Your Voice <br><span class="text-accent">Drives Excellence.</span></h1>
                <p>Digital Feedback & Complaint Management System isn't just about reporting problems—it's about creating solutions. Every feedback you provide is a step toward building a stronger, more transparent academic environment.</p>
                <div class="mt-4 d-flex align-items-center">
                    <div style="width: 50px; height: 2px; background: #10b981; margin-right: 15px;"></div>
                    <span class="text-uppercase fw-bold text-accent" style="letter-spacing: 2px;">University Of Information Science</span>
                </div>
            </div>
        </div>

        <!-- Registration Right Side -->
        <div class="content-right">
            <div class="card p-2">
                <h2 class="mb-2 fw-bold">Join the System</h2>
                <p class="text-muted mb-4">Empower Change at <span class="text-accent">DFCMS</span>.</p>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger py-2"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="alert alert-success py-2"><?php echo htmlspecialchars($success); ?> <a href="login.php" class="alert-link">Login here</a></div>
                <?php else: ?>
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label class="form-label text-muted small">Full Name</label>
                            <input type="text" name="full_name" class="form-control" placeholder="Enter your full name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-muted small">Email Address</label>
                            <input type="email" name="email" class="form-control" placeholder="example@university.edu" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-muted small">System Role</label>
                            <select name="role" class="form-select" required>
                                <option value="">Identify your role...</option>
                                <option value="student">Student</option>
                                <option value="cr">Class Representative (CR)</option>
                                <option value="teacher">Teacher</option>
                                <option value="lab_assistant">Lab Assistant</option>
                                <option value="hod">Department Head</option>
                            </select>
                        </div>
                        <div class="mb-4">
                            <label class="form-label text-muted small">Secure Password</label>
                            <input type="password" name="password" class="form-control" placeholder="••••••••••••" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 shadow"><i class="fas fa-user-plus me-2"></i> Create My Account</button>
                        <div class="text-center mt-3">
                            <span class="text-muted small">Already have an account? </span><a href="login.php" class="text-decoration-none text-accent fw-bold small">Login Here</a>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
