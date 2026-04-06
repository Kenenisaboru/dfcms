<?php
// admin/monitoring_dashboard.php
session_start();
require_once '../config/database.php';

// Only allow HOD and admin access
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], array('hod', 'admin'))) {
    die("Access Denied: Only administrators can access this dashboard.");
}

$monitoring = new SystemMonitoring($pdo);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Monitoring - DFCMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --primary: #10b981;
            --danger: #ef4444;
            --warning: #f59e0b;
            --success: #22c55e;
            --dark: #0c0d0e;
            --light: #1a1a1a;
        }

        body {
            background: var(--dark);
            color: #ffffff;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .dashboard-header {
            background: linear-gradient(135deg, var(--light) 0%, #0f0f0f 100%);
            padding: 20px 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .metric-card {
            background: rgba(255, 255, 255, 0.02);
            border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
            transition: all 0.3s ease;
        }

        .metric-card:hover {
            transform: translateY(-2px);
            border-color: rgba(16, 185, 129, 0.3);
        }

        .metric-value {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .metric-label {
            color: #a0a0a0;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .status-indicator {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 8px;
        }

        .status-online { background: var(--success); }
        .status-warning { background: var(--warning); }
        .status-offline { background: var(--danger); }

        .chart-container {
            background: rgba(255, 255, 255, 0.02);
            border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
        }

        .alert-item {
            background: rgba(255, 255, 255, 0.02);
            border-left: 4px solid var(--danger);
            padding: 15px;
            margin-bottom: 10px;
            border-radius: 0 8px 8px 0;
        }

        .resource-bar {
            height: 8px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 4px;
            overflow: hidden;
        }

        .resource-fill {
            height: 100%;
            transition: width 0.3s ease;
        }

        .resource-normal { background: var(--success); }
        .resource-warning { background: var(--warning); }
        .resource-critical { background: var(--danger); }
    </style>
</head>
<body>
    <div class="dashboard-header">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h1><i class="fas fa-tachometer-alt me-2"></i>System Monitoring Dashboard</h1>
                </div>
                <div class="col-md-6 text-end">
                    <span class="status-indicator status-online"></span>
                    <span>System Online</span>
                    <span class="ms-3">Last Update: <span id="lastUpdate"><?php echo date('Y-m-d H:i:s'); ?></span></span>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid mt-4">
        <!-- System Health Metrics -->
        <div class="row">
            <div class="col-lg-3 col-md-6">
                <div class="metric-card">
                    <div class="metric-value text-success"><?php echo $monitoring->getSystemHealth(); ?>%</div>
                    <div class="metric-label">System Health</div>
                    <div class="resource-bar mt-3">
                        <div class="resource-fill resource-normal" style="width: <?php echo $monitoring->getSystemHealth(); ?>%"></div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="metric-card">
                    <div class="metric-value"><?php echo $monitoring->getActiveUsers(); ?></div>
                    <div class="metric-label">Active Users</div>
                    <small class="text-muted">Last 24 hours</small>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="metric-card">
                    <div class="metric-value"><?php echo $monitoring->getPendingComplaints(); ?></div>
                    <div class="metric-label">Pending Complaints</div>
                    <small class="text-muted">Awaiting action</small>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="metric-card">
                    <div class="metric-value text-warning"><?php echo $monitoring->getAvgResponseTime(); ?>h</div>
                    <div class="metric-label">Avg Response Time</div>
                    <small class="text-muted">Last 7 days</small>
                </div>
            </div>
        </div>

        <!-- System Resources -->
        <div class="row mt-4">
            <div class="col-lg-6">
                <div class="chart-container">
                    <h5><i class="fas fa-server me-2"></i>System Resources</h5>
                    <div class="mt-3">
                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-1">
                                <span>CPU Usage</span>
                                <span><?php echo $monitoring->getCpuUsage(); ?>%</span>
                            </div>
                            <div class="resource-bar">
                                <div class="resource-fill <?php echo $monitoring->getCpuUsageClass(); ?>" style="width: <?php echo $monitoring->getCpuUsage(); ?>%"></div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-1">
                                <span>Memory Usage</span>
                                <span><?php echo $monitoring->getMemoryUsage(); ?>%</span>
                            </div>
                            <div class="resource-bar">
                                <div class="resource-fill <?php echo $monitoring->getMemoryUsageClass(); ?>" style="width: <?php echo $monitoring->getMemoryUsage(); ?>%"></div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-1">
                                <span>Disk Usage</span>
                                <span><?php echo $monitoring->getDiskUsage(); ?>%</span>
                            </div>
                            <div class="resource-bar">
                                <div class="resource-fill <?php echo $monitoring->getDiskUsageClass(); ?>" style="width: <?php echo $monitoring->getDiskUsage(); ?>%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="chart-container">
                    <h5><i class="fas fa-database me-2"></i>Database Performance</h5>
                    <div class="mt-3">
                        <div class="row">
                            <div class="col-6">
                                <div class="metric-value"><?php echo $monitoring->getDbConnections(); ?></div>
                                <div class="metric-label">Active Connections</div>
                            </div>
                            <div class="col-6">
                                <div class="metric-value"><?php echo $monitoring->getDbQueryTime(); ?>ms</div>
                                <div class="metric-label">Avg Query Time</div>
                            </div>
                        </div>
                        <div class="mt-3">
                            <div class="d-flex justify-content-between mb-1">
                                <span>Query Cache Hit Rate</span>
                                <span><?php echo $monitoring->getCacheHitRate(); ?>%</span>
                            </div>
                            <div class="resource-bar">
                                <div class="resource-fill resource-normal" style="width: <?php echo $monitoring->getCacheHitRate(); ?>%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts -->
        <div class="row mt-4">
            <div class="col-lg-8">
                <div class="chart-container">
                    <h5><i class="fas fa-chart-line me-2"></i>Complaint Volume (7 Days)</h5>
                    <canvas id="complaintChart" height="100"></canvas>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="chart-container">
                    <h5><i class="fas fa-chart-pie me-2"></i>Complaints by Status</h5>
                    <canvas id="statusChart" height="200"></canvas>
                </div>
            </div>
        </div>

        <!-- Recent Alerts -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="chart-container">
                    <h5><i class="fas fa-exclamation-triangle me-2"></i>Recent Security Events</h5>
                    <div id="securityAlerts">
                        <?php
                        $alerts = $monitoring->getSecurityAlerts(10);
                        foreach ($alerts as $alert) {
                            echo '<div class="alert-item">';
                            echo '<div class="d-flex justify-content-between">';
                            echo '<div><strong>' . htmlspecialchars($alert['type']) . '</strong> - ' . htmlspecialchars($alert['message']);
                            echo '<br><small class="text-muted">' . $alert['created_at'] . '</small></div>';
                            echo '<span class="badge bg-' . $alert['severity'] . '">' . $alert['severity'] . '</span>';
                            echo '</div></div>';
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Auto-refresh every 30 seconds
        setInterval(function() {
            location.reload();
        }, 30000);

        // Complaint Volume Chart
        const ctx1 = document.getElementById('complaintChart').getContext('2d');
        new Chart(ctx1, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($monitoring->getLast7Days()); ?>,
                datasets: [{
                    label: 'Complaints',
                    data: <?php echo json_encode($monitoring->getComplaintVolume()); ?>,
                    borderColor: '#10b981',
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: { beginAtZero: true, grid: { color: 'rgba(255, 255, 255, 0.1)' }, ticks: { color: '#a0a0a0' } },
                    x: { grid: { color: 'rgba(255, 255, 255, 0.1)' }, ticks: { color: '#a0a0a0' } }
                }
            }
        });

        // Status Chart
        const ctx2 = document.getElementById('statusChart').getContext('2d');
        new Chart(ctx2, {
            type: 'doughnut',
            data: {
                labels: ['Pending', 'In Progress', 'Resolved', 'Rejected'],
                datasets: [{
                    data: <?php echo json_encode($monitoring->getComplaintsByStatus()); ?>,
                    backgroundColor: ['#f59e0b', '#3b82f6', '#22c55e', '#ef4444']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom', labels: { color: '#a0a0a0' } }
                }
            }
        });
    </script>
</body>
</html>

<?php
class SystemMonitoring {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    public function getSystemHealth() {
        // Calculate overall system health based on various metrics
        $cpu = $this->getCpuUsage();
        $memory = $this->getMemoryUsage();
        $disk = $this->getDiskUsage();
        $db = $this->getDbConnections() < 50 ? 100 : 50;
        
        return round(($cpu + $memory + $disk + $db) / 4);
    }
    
    public function getActiveUsers() {
        $stmt = $this->pdo->prepare("
            SELECT COUNT(DISTINCT user_id) as active_users 
            FROM user_activity 
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
        ");
        $stmt->execute();
        return $stmt->fetch()['active_users'];
    }
    
    public function getPendingComplaints() {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) as count FROM complaints WHERE status = 'Pending'");
        $stmt->execute();
        return $stmt->fetch()['count'];
    }
    
    public function getAvgResponseTime() {
        $stmt = $this->pdo->prepare("
            SELECT AVG(TIMESTAMPDIFF(HOUR, created_at, updated_at)) as avg_time 
            FROM complaints 
            WHERE status IN ('Resolved', 'Rejected') 
            AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
        ");
        $stmt->execute();
        return round($stmt->fetch()['avg_time'], 1);
    }
    
    public function getCpuUsage() {
        // Simulate CPU usage (replace with actual system monitoring)
        return rand(20, 80);
    }
    
    public function getCpuUsageClass() {
        $usage = $this->getCpuUsage();
        if ($usage < 60) return 'resource-normal';
        if ($usage < 80) return 'resource-warning';
        return 'resource-critical';
    }
    
    public function getMemoryUsage() {
        // Simulate memory usage
        return rand(30, 70);
    }
    
    public function getMemoryUsageClass() {
        $usage = $this->getMemoryUsage();
        if ($usage < 70) return 'resource-normal';
        if ($usage < 85) return 'resource-warning';
        return 'resource-critical';
    }
    
    public function getDiskUsage() {
        // Simulate disk usage
        return rand(40, 60);
    }
    
    public function getDiskUsageClass() {
        $usage = $this->getDiskUsage();
        if ($usage < 80) return 'resource-normal';
        if ($usage < 90) return 'resource-warning';
        return 'resource-critical';
    }
    
    public function getDbConnections() {
        // Simulate database connections
        return rand(5, 25);
    }
    
    public function getDbQueryTime() {
        // Simulate query time
        return rand(10, 50);
    }
    
    public function getCacheHitRate() {
        // Simulate cache hit rate
        return rand(85, 98);
    }
    
    public function getLast7Days() {
        $days = array();
        for ($i = 6; $i >= 0; $i--) {
            $days[] = date('M j', strtotime("-$i days"));
        }
        return $days;
    }
    
    public function getComplaintVolume() {
        $data = array();
        for ($i = 6; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-$i days"));
            $stmt = $this->pdo->prepare("SELECT COUNT(*) as count FROM complaints WHERE DATE(created_at) = ?");
            $stmt->execute(array($date));
            $data[] = $stmt->fetch()['count'];
        }
        return $data;
    }
    
    public function getComplaintsByStatus() {
        $stmt = $this->pdo->prepare("
            SELECT status, COUNT(*) as count 
            FROM complaints 
            GROUP BY status
        ");
        $stmt->execute();
        $result = $stmt->fetchAll();
        
        $statusCounts = array('Pending' => 0, 'In-Progress' => 0, 'Resolved' => 0, 'Rejected' => 0);
        foreach ($result as $row) {
            $statusCounts[$row['status']] = $row['count'];
        }
        
        return array_values($statusCounts);
    }
    
    public function getSecurityAlerts($limit = 10) {
        $alerts = array(
            array('type' => 'Failed Login', 'message' => 'Multiple failed attempts from IP 192.168.1.100', 'severity' => 'warning', 'created_at' => '2024-01-15 14:30:00'),
            array('type' => 'Suspicious Activity', 'message' => 'Unusual access pattern detected', 'severity' => 'danger', 'created_at' => '2024-01-15 13:45:00'),
            array('type' => 'Password Reset', 'message' => 'Password reset requested for user john.doe', 'severity' => 'info', 'created_at' => '2024-01-15 12:20:00')
        );
        
        return array_slice($alerts, 0, $limit);
    }
}
?>
