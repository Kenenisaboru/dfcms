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
    <style>
        body { background-color: #121212; color: #fff; display: flex; align-items: center; justify-content: center; height: 100vh; }
        .card { background-color: #1e1e1e; border: 1px solid #333; border-radius: 10px; width: 100%; max-width: 400px; }
        .form-control { background-color: #2c2c2c; border: 1px solid #444; color: #fff; }
        .form-control:focus { background-color: #333; color: #fff; border-color: #10b981; box-shadow: none; }
        .btn-primary { background-color: #10b981; border: none; }
        .btn-primary:hover { background-color: #059669; }
    </style>
</head>
<body>
    <div class="card p-4 shadow-lg">
        <h3 class="text-center mb-4">System Login</h3>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <form method="POST" action="">
            <div class="mb-3">
                <label class="form-label">Email Address</label>
                <input type="email" name="email" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Login</button>
            <div class="text-center mt-3">
                <a href="register.php" class="text-decoration-none" style="color: #10b981;">Create an Account</a>
            </div>
        </form>
    </div>
</body>
</html>
