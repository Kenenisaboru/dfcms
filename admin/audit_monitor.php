<?php
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
                        ORDER BY h.created_at DESC LIMIT 30");
$auditLogs = $stmtHist->fetchAll();

// Category Breakdown for Analytics
$categoryStats = $pdo->query("SELECT category, COUNT(*) as count FROM complaints GROUP BY category ORDER BY count DESC")->fetchAll();

$chartLabels = []; $chartData = [];
foreach ($categoryStats as $row) { $chartLabels[] = $row['category']; $chartData[] = $row['count']; }
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

        <div class="row g-4 mb-4">
            <!-- Stats -->
            <div class="col-md-8">
                <div class="row g-4">
                    <div class="col-md-3"><div class="card-stat shadow"><h6 class="text-muted small fw-bold">TOTAL</h6><h2 class="mb-0 fw-bold"><?php echo $stats['total']; ?></h2></div></div>
                    <div class="col-md-3"><div class="card-stat shadow text-warning"><h6 class="small fw-bold">PENDING</h6><h2 class="mb-0 fw-bold"><?php echo $stats['pending']; ?></h2></div></div>
                    <div class="col-md-3"><div class="card-stat shadow text-info"><h6 class="small fw-bold">ROUTING</h6><h2 class="mb-0 fw-bold"><?php echo $stats['assigned']; ?></h2></div></div>
                    <div class="col-md-3"><div class="card-stat shadow text-success"><h6 class="small fw-bold">RESOLVED</h6><h2 class="mb-0 fw-bold"><?php echo $stats['resolved']; ?></h2></div></div>
                </div>
                
                <div class="card card-custom bg-glass border-0 shadow mt-4 p-4">
                    <h5 class="fw-bold text-white mb-3">Live Complaint Stream</h5>
                    <div class="table-responsive">
                        <table class="table table-dark table-hover align-middle">
                            <thead class="text-muted small text-uppercase">
                                <tr><th>ID</th><th>Source</th><th>Category</th><th>Priority</th><th>Handler</th><th>Status</th></tr>
                            </thead>
                            <tbody>
                                <?php foreach($complaints as $c): ?>
                                    <tr>
                                        <td>#<?php echo $c['id']; ?></td>
                                        <td><?php echo htmlspecialchars($c['student_name']); ?></td>
                                        <td><span class="small text-muted"><?php echo $c['category']; ?></span></td>
                                        <td><span class="text-<?php echo ($c['priority'] == 'High') ? 'danger' : (($c['priority'] == 'Medium') ? 'warning' : 'primary'); ?> fw-bold small"><?php echo $c['priority']; ?></span></td>
                                        <td><?php echo $c['handler_name'] ? htmlspecialchars($c['handler_name']) : '<span class="text-muted">System Queue</span>'; ?></td>
                                        <td><span class="badge bg-<?php echo ($c['status'] == 'Resolved') ? 'success' : (($c['status'] == 'Pending') ? 'warning' : 'info'); ?>"><?php echo $c['status']; ?></span></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Side Cards -->
            <div class="col-md-4">
                <div class="card card-custom bg-glass border-0 shadow p-4 mb-4">
                    <h5 class="fw-bold text-white mb-3">Issue Breakdown</h5>
                    <canvas id="categoryChart" height="200"></canvas>
                </div>

                <div class="card card-custom bg-glass border-0 shadow p-4" style="max-height: 400px; overflow-y: auto;">
                    <h5 class="fw-bold text-white mb-3">Activity Log</h5>
                    <?php foreach($auditLogs as $log): ?>
                        <div class="audit-log-item mb-3 pb-2 border-bottom border-secondary border-opacity-10">
                            <div class="d-flex justify-content-between"><span class="text-accent fw-bold small"><?php echo $log['actor_name']; ?></span><span class="text-muted" style="font-size: 10px;"><?php echo date('H:i', strtotime($log['created_at'])); ?></span></div>
                            <div class="small text-light mt-1"><?php echo htmlspecialchars($log['comments']); ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <?php include '../components/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const ctx = document.getElementById('categoryChart').getContext('2d');
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode($chartLabels); ?>,
                datasets: [{
                    data: <?php echo json_encode($chartData); ?>,
                    backgroundColor: ['#10b981', '#3b82f6', '#f59e0b', '#ef4444', '#8b5cf6', '#ec4899'],
                    borderWidth: 0
                }]
            },
            options: {
                plugins: { legend: { position: 'bottom', labels: { color: '#fff', font: { size: 10 } } } },
                cutout: '70%'
            }
        });
    </script>
</body>
</html>
