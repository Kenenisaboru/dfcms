require_once '../config/config.php';

// Check if user is logged in as HOD
check_login('hod');

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
<?php
$page_title = "Global Audit Monitor";
include '../components/head.php';
?>
<body>
    <?php include '../components/navbar.php'; ?>

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
