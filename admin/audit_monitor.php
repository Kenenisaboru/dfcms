<?php
// admin/audit_monitor.php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'hod') {
    die("Access Denied: Super Admin / Department Head access only.");
}

// Fetch Comprehensive Global Complaint Statistics
$stats = [
    'total'    => $pdo->query("SELECT COUNT(*) FROM complaints")->fetchColumn(),
    'pending'  => $pdo->query("SELECT COUNT(*) FROM complaints WHERE status = 'Pending'")->fetchColumn(),
    'assigned' => $pdo->query("SELECT COUNT(*) FROM complaints WHERE status = 'Assigned' OR status = 'Forwarded'")->fetchColumn(),
    'resolved' => $pdo->query("SELECT COUNT(*) FROM complaints WHERE status = 'Resolved'")->fetchColumn(),
];

// Fetch Global Complaint Feed with Student & Current Handler info
$stmt = $pdo->query("SELECT c.*, s.full_name as student_name, h.full_name as handler_name 
                    FROM complaints c 
                    JOIN users s ON c.student_id = s.id 
                    LEFT JOIN users h ON c.assigned_to = h.id 
                    ORDER BY c.created_at DESC LIMIT 50");
$complaints = $stmt->fetchAll();
$stmtHist = $pdo->query("SELECT h.*, u.full_name as actor_name, u.role as actor_role 
                        FROM complaint_history h 
                        JOIN users u ON h.action_by = u.id 
                        ORDER BY h.action_date DESC LIMIT 30");
$auditLogs = $stmtHist->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Global Audit Monitor - DFCMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background-color: #0c0d0e; color: #fff; font-family: 'Inter', sans-serif; }
        .navbar-custom { background-color: #121212; border-bottom: 1px solid #333; }
        .card-stat { background-color: #121212; border: 1px solid #333; border-radius: 12px; padding: 25px; transition: 0.3s; }
        .card-stat:hover { transform: translateY(-5px); border-color: #10b981; }
        .card-table { background-color: #121212; border: 1px solid #333; border-radius: 12px; padding: 30px; margin-top: 30px; }
        .table-dark { --bs-table-bg: #121212; }
        .text-accent { color: #10b981; }
        .badge-status { font-size: 10px; padding: 5px 10px; border-radius: 20px; }
        .audit-log-item { border-left: 3px solid #333; padding-left: 15px; margin-bottom: 20px; position: relative; }
        .audit-log-item::before { content: ''; position: absolute; left: -7px; top: 0; width: 11px; height: 11px; background: #10b981; border-radius: 50%; }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark navbar-custom px-4">
        <a class="navbar-brand text-accent fw-bold" href="../dashboard.php">DFCMS ADMIN</a>
        <div class="ms-auto"><a href="../dashboard.php" class="btn btn-outline-light btn-sm"><i class="fas fa-arrow-left"></i> Back to Dashboard</a></div>
    </nav>

    <div class="container-fluid px-5 my-5">
        <div class="row align-items-end mb-4">
            <div class="col-md-8">
                <h1 class="fw-bold mb-0">System-Wide <span class="text-accent">Audit Monitor</span></h1>
                <p class="text-muted">Global oversight of all departmental grievances and laboratory technical workflows.</p>
            </div>
            <div class="col-md-4 text-end">
                <button class="btn btn-outline-success btn-sm" onclick="window.print()"><i class="fas fa-file-export me-1"></i> EXPORT REPORT</button>
            </div>
        </div>

        <!-- Quick Statistics -->
        <div class="row g-4">
            <div class="col-md-3">
                <div class="card-stat shadow">
                    <h6 class="text-muted text-uppercase small fw-bold">Grand Total</h6>
                    <h2 class="mb-0 fw-bold"><?php echo $stats['total']; ?></h2>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card-stat shadow">
                    <h6 class="text-muted text-uppercase small fw-bold text-warning">Pending Review</h6>
                    <h2 class="mb-0 fw-bold text-warning"><?php echo $stats['pending']; ?></h2>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card-stat shadow">
                    <h6 class="text-muted text-uppercase small fw-bold text-info">In Routing</h6>
                    <h2 class="mb-0 fw-bold text-info"><?php echo $stats['assigned']; ?></h2>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card-stat shadow">
                    <h6 class="text-muted text-uppercase small fw-bold text-success">Resolved</h6>
                    <h2 class="mb-0 fw-bold text-success"><?php echo $stats['resolved']; ?></h2>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Left: Live Complaint Feed -->
            <div class="col-lg-8">
                <div class="card-table shadow">
                    <h5 class="mb-4 fw-bold">Live Complaint Stream</h5>
                    <div class="table-responsive">
                        <table class="table table-dark table-hover align-middle">
                            <thead class="text-muted small text-uppercase">
                                <tr>
                                    <th>ID</th>
                                    <th>Source</th>
                                    <th>Category</th>
                                    <th>Priority</th>
                                    <th>Current Handler</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($complaints as $c): ?>
                                    <tr>
                                        <td class="fw-bold">#<?php echo $c['id']; ?></td>
                                        <td><?php echo htmlspecialchars($c['student_name']); ?></td>
                                        <td><span class="small text-muted"><?php echo $c['category']; ?></span></td>
                                        <td><span class="text-<?php echo ($c['priority'] == 'High') ? 'danger' : (($c['priority'] == 'Medium') ? 'warning' : 'primary'); ?> fw-bold small"><?php echo $c['priority']; ?></span></td>
                                        <td><?php echo $c['handler_name'] ? htmlspecialchars($c['handler_name']) : '<span class="text-muted">System Queue</span>'; ?></td>
                                        <td>
                                            <span class="badge badge-status bg-<?php echo ($c['status'] == 'Resolved') ? 'success' : (($c['status'] == 'Pending') ? 'warning' : 'info'); ?>">
                                                <?php echo $c['status']; ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Right: Real-Time Audit Log -->
            <div class="col-lg-4">
                <div class="card-table shadow overflow-auto" style="max-height: 600px;">
                    <h5 class="mb-4 fw-bold">Dept. Activity Log</h5>
                    <?php if (count($auditLogs) > 0): ?>
                        <?php foreach($auditLogs as $log): ?>
                            <div class="audit-log-item">
                                <span class="text-accent fw-bold small"><?php echo htmlspecialchars($log['actor_name']); ?></span> 
                                <span class="text-muted small">(<?php echo strtoupper($log['actor_role']); ?>)</span>
                                <div class="small mt-1"><?php echo htmlspecialchars($log['comments']); ?></div>
                                <div class="text-muted" style="font-size: 10px;"><?php echo date('M d, H:i', strtotime($log['action_date'])); ?></div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-muted small">No departmental activity logged yet.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
