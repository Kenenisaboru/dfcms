<?php
// student/tracker.php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../auth/login.php");
    exit;
}

$userId = $_SESSION['user_id'];

// Get all complaints for the student
$stmt = $pdo->prepare("SELECT c.*, u.full_name as handler_name 
                      FROM complaints c 
                      LEFT JOIN users u ON c.assigned_to = u.id 
                      WHERE c.student_id = ? 
                      ORDER BY c.created_at DESC");
$stmt->execute([$userId]);
$complaints = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Track Complaints - DFCMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background-color: #121212; color: #fff; }
        .navbar-custom { background-color: #1e1e1e; border-bottom: 1px solid #333; }
        .card-custom { background-color: #1e1e1e; border: 1px solid #333; border-radius: 10px; margin-top: 30px; padding: 30px; }
        .table-dark { --bs-table-bg: #1e1e1e; }
        .badge-pending { background-color: #f59e0b; }
        .badge-resolved { background-color: #10b981; }
        .badge-rejected { background-color: #ef4444; }
        .badge-progress { background-color: #3b82f6; }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark navbar-custom px-4">
        <a class="navbar-brand text-success fw-bold" href="../dashboard.php">DFCMS</a>
        <div class="ms-auto">
            <a href="../dashboard.php" class="btn btn-outline-light btn-sm me-3"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
        </div>
    </nav>

    <div class="container my-5">
        <h3 class="mb-4"><i class="fas fa-search me-2 text-success"></i> Track Your Status</h3>
        
        <div class="card card-custom">
            <div class="table-responsive">
                <table class="table table-dark table-hover">
                    <thead>
                        <tr>
                            <th>Complaint ID</th>
                            <th>Category</th>
                            <th>Current Handler</th>
                            <th>Status</th>
                            <th>Last Update</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($complaints) > 0): ?>
                            <?php foreach ($complaints as $c): ?>
                                <tr>
                                    <td>#<?php echo $c['id']; ?></td>
                                    <td><?php echo htmlspecialchars($c['category']); ?></td>
                                    <td><?php echo $c['handler_name'] ? htmlspecialchars($c['handler_name']) : '<span class="text-muted">Unassigned</span>'; ?></td>
                                    <td>
                                        <span class="badge <?php 
                                            echo match($c['status']) {
                                                'Pending' => 'badge-pending',
                                                'In-Progress' => 'badge-progress',
                                                'Resolved' => 'badge-resolved',
                                                'Rejected' => 'badge-rejected',
                                                default => 'bg-secondary'
                                            };
                                        ?>"><?php echo $c['status']; ?></span>
                                    </td>
                                    <td><?php echo date('M d, Y', strtotime($c['updated_at'])); ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-success" onclick="alert('Viewing historical details is being finalized.')">View Log</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted p-4">You haven't submitted any complaints yet.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
