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
        $error = "DATABASE CONNECTION FAILED. Check credentials.";
    } else {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = "Email is already registered.";
        } else {
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $pdo->prepare("INSERT INTO users (full_name, email, password, role) VALUES (?, ?, ?, ?)");
            if ($stmt->execute([$fullName, $email, $hashedPassword, $role])) {
                $success = "Success! Login now.";
            } else {
                $error = "Registration failed.";
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
    <title>Join DFCMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background-color: #0c0d0e; color: #fff; margin:0; font-family: 'Segoe UI', sans-serif; overflow: hidden; height: 100vh; }
        .wrapper { display: flex; height: 100vh; width: 100%; }
        .left-side {
            flex: 1.2;
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.4), rgba(0, 0, 0, 0.9)), url('https://images.unsplash.com/photo-1517245386807-bb43f82c33c4?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80');
            background-size: cover; background-position: center;
            display: flex; flex-direction: column; justify-content: center; padding: 80px;
        }
        .right-side {
            flex: 0.8; background-color: #121212;
            display: flex; align-items: center; justify-content: center; padding: 40px;
            border-left: 1px solid #333;
        }
        .form-container { width: 100%; max-width: 400px; }
        .form-label { color: #cfcfcf !important; font-weight: 600; font-size: 0.9rem; margin-bottom: 8px; display: block; }
        .form-control, .form-select { 
            background-color: #eef2f7 !important; /* Light-blue backdrop for contrast */
            border: 1px solid #444 !important; 
            color: #000 !important; /* Dark text on light background inside */
            padding: 12px !important; border-radius: 8px !important; margin-bottom: 20px;
        }
        .form-control::placeholder { color: #555 !important; }
        .form-control:focus { border-color: #10b981 !important; box-shadow: 0 0 0 0.2rem rgba(16,185,129,0.25) !important; }
        .btn-submit { 
            background-color: #10b981; border: none; padding: 14px; width: 100%; color: #000; font-weight: 800;
            border-radius: 8px; text-transform: uppercase; letter-spacing: 1px; transition: 0.3s;
        }
        .btn-submit:hover { background-color: #059669; transform: translateY(-2px); }
        .accent { color: #10b981; }
        h1 { font-size: 4rem; font-weight: 800; text-shadow: 2px 2px 10px rgba(0,0,0,0.5); margin-bottom: 20px; }
        p.subtitle { font-size: 1.2rem; color: #ccc; line-height: 1.6; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="left-side">
            <h1>Your Voice <br><span class="accent">Drives Excellence.</span></h1>
            <p class="subtitle">Join the University Digital Feedback System. Help us build a more transparent and efficient academic environment together.</p>
        </div>
        <div class="right-side">
            <div class="form-container">
                <h2 class="mb-4 fw-bold">Create Account</h2>
                
                <?php if($error): ?><div class="alert alert-danger py-2"><?php echo $error; ?></div><?php endif; ?>
                <?php if($success): ?><div class="alert alert-success py-2"><?php echo $success; ?></div><?php endif; ?>

                <form method="POST">
                    <label class="form-label">Full Name</label>
                    <input type="text" name="full_name" class="form-control" placeholder="Enter Full Name" required>

                    <label class="form-label">Email Address</label>
                    <input type="email" name="email" class="form-control" placeholder="university@email.com" required>

                    <label class="form-label">System Role</label>
                    <select name="role" class="form-select" required>
                        <option value="">Select Role...</option>
                        <option value="student">Student</option>
                        <option value="cr">Class Representative (CR)</option>
                        <option value="teacher">Teacher</option>
                        <option value="lab_assistant">Lab Assistant</option>
                        <option value="hod">Department Head</option>
                    </select>

                    <label class="form-label">Set Password</label>
                    <input type="password" name="password" class="form-control" placeholder="••••••••" required>

                    <button type="submit" class="btn btn-submit">Register Now</button>
                    <p class="text-center mt-3 small text-muted">Already have an account? <a href="login.php" class="accent fw-bold text-decoration-none">Login</a></p>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
