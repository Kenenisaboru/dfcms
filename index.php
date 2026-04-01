<?php
// index.php
session_start();
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DFCMS - Digital Feedback & Complaint Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #121212; color: #ffffff; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .hero { min-height: 100vh; display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, #0f2027, #203a43, #2c5364); }
        .hero-content { text-align: center; }
        .btn-custom { background-color: #10b981; color: white; border: none; padding: 12px 30px; border-radius: 5px; font-weight: bold; transition: 0.3s; }
        .btn-custom:hover { background-color: #059669; color: white; }
    </style>
</head>
<body>
    <div class="hero">
        <div class="hero-content">
            <h1 class="display-4 fw-bold mb-4">University DFCMS</h1>
            <p class="lead mb-5">Digital Feedback & Complaint Management System<br>A secure, role-based workflow for academic excellence.</p>
            <a href="auth/login.php" class="btn btn-custom me-3">Login to System</a>
            <a href="auth/register.php" class="btn btn-outline-light">Register Account</a>
        </div>
    </div>
</body>
</html>
