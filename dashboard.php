<?php
// dashboard.php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: auth/login.php");
    exit;
}

$role = $_SESSION['role'];
$userId = $_SESSION['user_id'];
$userName = $_SESSION['full_name'];

// Global Error Handler for DB
if (!$pdo) {
    die("<div style='background:#121212;color:#ff4d4d;padding:50px;text-align:center;font-family:sans-serif;'>
            <h2>DATABASE CONNECTION ERROR</h2>
            <p>The dashboard cannot load because the database is not connecting.</p>
            <p>Please check <b>config/database.php</b> and ensure your password is correct.</p>
            <a href='auth/logout.php' style='color:#fff;text-decoration:underline;'>Logout and try again</a>
         </div>");
}

// Fetch stats based on role
$totalComplaints = 0;
if ($role == 'student') {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM complaints WHERE student_id = ?");
    $stmt->execute([$userId]);
    $totalComplaints = $stmt->fetchColumn();
} else {
    // For admins/receivers, show complaints routed to them
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM complaints WHERE current_handler_role = ? OR assigned_to = ?");
    $stmt->execute([$role, $userId]);
    $totalComplaints = $stmt->fetchColumn();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - DFCMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background-color: #121212; color: #fff; display: flex; }
        .sidebar { width: 250px; background-color: #1e1e1e; height: 100vh; padding: 20px; border-right: 1px solid #333; position: fixed; }
        .sidebar a { color: #aaa; text-decoration: none; display: block; padding: 10px; border-radius: 5px; margin-bottom: 5px; transition: 0.3s; }
        .sidebar a:hover, .sidebar a.active { background-color: #2c2c2c; color: #10b981; }
        .main-content { margin-left: 250px; padding: 30px; width: calc(100% - 250px); }
        .card-custom { background-color: #1e1e1e; border: 1px solid #333; border-radius: 10px; }
        .text-accent { color: #10b981; }
        .text-muted { color: #cfcfcf !important; } /* brighter muted for dark mode */
        h4, h5 { color: #fff; }
    </style>
</head>
<body>
    <div class="sidebar">
        <h4 class="mb-4 text-accent"><i class="fas fa-university"></i> DFCMS</h4>
        <a href="dashboard.php" class="active"><i class="fas fa-home me-2"></i> Dashboard</a>
        
        <?php if($role === 'student'): ?>
            <a href="student/submit_complaint.php"><i class="fas fa-plus-circle me-2"></i> Submit Complaint</a>
            <a href="student/tracker.php"><i class="fas fa-search me-2"></i> Track Status</a>
        <?php endif; ?>

        <?php if($role === 'cr' || $role === 'teacher' || $role === 'hod'): ?>
            <a href="representative/forward.php"><i class="fas fa-share me-2"></i> Inbox / Forward</a>
        <?php endif; ?>

        <?php if($role === 'teacher' || $role === 'hod'): ?>
            <a href="teacher/assign_lab.php"><i class="fas fa-tasks me-2"></i> Assign Tasks</a>
        <?php endif; ?>
        
        <a href="auth/logout.php" class="mt-5 text-danger"><i class="fas fa-sign-out-alt me-2"></i> Logout</a>
    </div>

    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Welcome, <?php echo htmlspecialchars($userName); ?> (<?php echo strtoupper($role); ?>)</h2>
        </div>

        <div class="row">
            <div class="col-md-4">
                <div class="card card-custom p-4 mb-3">
                    <h5 class="text-muted">Total Relevant Complaints</h5>
                    <h2 class="text-accent"><?php echo $totalComplaints; ?></h2>
                </div>
            </div>
        </div>

        <div class="card card-custom p-4 mt-4">
            <h4>Recent Activity Overview</h4>
            <p class="text-muted">Displaying operations scoped to your access level.</p>
            <table class="table table-dark table-hover mt-3">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Category</th>
                        <th>Priority</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td colspan="5" class="text-center text-muted">Functional tabular data will appear here</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
