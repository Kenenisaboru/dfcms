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
    <style>
        body { background-color: #121212; color: #fff; display: flex; align-items: center; justify-content: center; height: 100vh; }
        .card { background-color: #1e1e1e; border: 1px solid #333; border-radius: 10px; width: 100%; max-width: 450px; }
        .form-control, .form-select { background-color: #2c2c2c; border: 1px solid #444; color: #fff; }
        .form-control:focus, .form-select:focus { background-color: #333; color: #fff; border-color: #10b981; box-shadow: none; }
        .btn-primary { background-color: #10b981; border: none; }
        .btn-primary:hover { background-color: #059669; }
    </style>
</head>
<body>
    <div class="card p-4 shadow-lg">
        <h3 class="text-center mb-4">Register Account</h3>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?> <a href="login.php" class="alert-link">Login here</a></div>
        <?php else: ?>
        <form method="POST" action="">
            <div class="mb-3">
                <label class="form-label">Full Name</label>
                <input type="text" name="full_name" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Email Address</label>
                <input type="email" name="email" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">System Role</label>
                <select name="role" class="form-select" required>
                    <option value="">Select Role...</option>
                    <option value="student">Student</option>
                    <option value="cr">Class Representative (CR)</option>
                    <option value="teacher">Teacher</option>
                    <option value="lab_assistant">Lab Assistant</option>
                    <option value="hod">Department Head</option>
                </select>
            </div>
            <div class="mb-4">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Register</button>
            <div class="text-center mt-3">
                <a href="login.php" class="text-decoration-none" style="color: #10b981;">Already have an account? Login</a>
            </div>
        </form>
        <?php endif; ?>
    </div>
</body>
</html>
