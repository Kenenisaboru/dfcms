<?php
// dashboard.php
require_once 'config/config.php';
require_once 'lib/NotificationService.php';

check_login();

$role = $_SESSION['role'];
$userId = $_SESSION['user_id'];
$userName = $_SESSION['full_name'];

// Fetch stats based on role
$totalComplaints = 0;
if ($role == 'student') {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM complaints WHERE student_id = ?");
    $stmt->execute([$userId]);
    $totalComplaints = $stmt->fetchColumn();
    
    // Fetch recent complaints for activity log
    $stmtActivity = $pdo->prepare("SELECT id, category, priority, status, created_at FROM complaints WHERE student_id = ? ORDER BY created_at DESC LIMIT 5");
    $stmtActivity->execute([$userId]);
    $activities = $stmtActivity->fetchAll();
} else {
    // For admins/receivers, show complaints routed to them
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM complaints WHERE current_handler_role = ? OR assigned_to = ?");
    $stmt->execute([$role, $userId]);
    $totalComplaints = $stmt->fetchColumn();
    
    // Fetch recent complaints for activity log
    $stmtActivity = $pdo->prepare("SELECT id, category, priority, status, created_at FROM complaints WHERE current_handler_role = ? OR assigned_to = ? ORDER BY created_at DESC LIMIT 5");
    $stmtActivity->execute([$role, $userId]);
    $activities = $stmtActivity->fetchAll();
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
    <link href="assets/css/next-gen-ui.css" rel="stylesheet">
    <style>
        :root {
            --primary: #10b981;
            --primary-glow: rgba(16, 185, 129, 0.4);
            --bg-dark: #0c0d0e;
            --card-bg: rgba(18, 18, 18, 0.7);
            --glass-border: rgba(255, 255, 255, 0.1);
            --text-light: #f8fafc;
            --text-dim: #94a3b8;
        }

        body { 
            background-color: var(--bg-dark); 
            background-image: 
                radial-gradient(circle at 20% 20%, rgba(16, 185, 129, 0.05) 0%, transparent 40%),
                radial-gradient(circle at 80% 80%, rgba(16, 185, 129, 0.05) 0%, transparent 40%);
            color: var(--text-light); 
            font-family: 'Inter', sans-serif;
            display: flex;
            min-height: 100vh;
        }

        .sidebar { 
            width: 280px; 
            background: rgba(18, 18, 18, 0.8);
            backdrop-filter: blur(20px);
            height: 100vh; 
            padding: 30px 20px; 
            border-right: 1px solid var(--glass-border); 
            position: fixed; 
            display: flex;
            flex-direction: column;
            z-index: 1000;
        }

        .sidebar h4 {
            font-weight: 800;
            letter-spacing: -0.5px;
            margin-bottom: 40px;
            padding-left: 10px;
        }

        .sidebar a { 
            color: var(--text-dim); 
            text-decoration: none; 
            display: flex; 
            align-items: center;
            padding: 12px 15px; 
            border-radius: 12px; 
            margin-bottom: 8px; 
            transition: all 0.3s ease;
            font-weight: 500;
        }

        .sidebar a i {
            width: 20px;
            margin-right: 12px;
            font-size: 1.1rem;
        }

        .sidebar a:hover, .sidebar a.active { 
            background: rgba(16, 185, 129, 0.1);
            color: var(--primary); 
            transform: translateX(5px);
        }

        .main-content { 
            margin-left: 280px; 
            padding: 40px; 
            width: calc(100% - 280px); 
            animation: fadeIn 0.8s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .card-custom { 
            background: var(--card-bg); 
            backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border); 
            border-radius: 20px; 
            padding: 30px;
            box-shadow: 0 10px 30px -10px rgba(0,0,0,0.5);
            transition: all 0.3s ease;
        }

        .card-custom:hover {
            border-color: rgba(16, 185, 129, 0.3);
            transform: translateY(-5px);
        }

        .text-accent { color: var(--primary); }
        .text-dim { color: var(--text-dim) !important; }
        
        .stat-label {
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 600;
            color: var(--text-dim);
            margin-bottom: 10px;
        }

        .stat-value {
            font-size: 2.5rem;
            font-weight: 800;
            margin: 0;
        }

        .table-dark {
            --bs-table-bg: transparent;
            --bs-table-border-color: var(--glass-border);
            color: var(--text-light);
        }

        .logout-link {
            margin-top: auto;
            color: #ef4444 !important;
        }
        .logout-link:hover {
            background: rgba(239, 68, 68, 0.1) !important;
        }
    </style>
</head>
<body>
    <!-- Mobile Navigation Toggle -->
    <button class="menu-toggle" id="sidebarToggle">
        <i class="fas fa-bars"></i>
    </button>

    <!-- Sidebar Overlay for Mobile -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <div class="sidebar" id="sidebar">
        <h4 class="text-accent d-flex align-items-center justify-content-between">
            <span><i class="fas fa-university"></i> DFCMS</span>
            <div class="ms-2" style="transform: scale(0.8)">
                <?php include 'components/notifications.php'; ?>
            </div>
        </h4>
        <a href="dashboard.php" class="active"><i class="fas fa-home"></i> Dashboard</a>
        
        <?php if($role === 'student'): ?>
            <a href="student/submit_complaint.php"><i class="fas fa-plus-circle"></i> Submit Complaint</a>
            <a href="student/tracker.php"><i class="fas fa-search"></i> Track Status</a>
        <?php endif; ?>

        <?php if($role === 'cr' || $role === 'teacher' || $role === 'hod'): ?>
            <a href="representative/forward.php"><i class="fas fa-share"></i> Inbox / Forward</a>
        <?php endif; ?>

        <?php if($role === 'teacher' || $role === 'hod'): ?>
            <a href="teacher/assign_lab.php"><i class="fas fa-tasks"></i> Assign Tasks</a>
        <?php endif; ?>
        
        <!-- Quick Links -->
        <hr class="border-secondary my-3">
        <h6 class="text-dim text-uppercase small mb-3">Quick Access</h6>
        <a href="student/notifications.php"><i class="fas fa-bell"></i> All Alerts</a>
        <a href="student/messages.php"><i class="fas fa-envelope"></i> Secure Chat</a>
        <a href="student/badges.php"><i class="fas fa-medal"></i> Achievements</a>
        <a href="student/knowledge_base.php"><i class="fas fa-book"></i> Help Guides</a>
        
        <a href="auth/logout.php" class="logout-link"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>

    <div class="main-content">
        <div class="mb-5">
            <h2 class="fw-bold">Welcome, <?php echo htmlspecialchars($userName); ?></h2>
            <p class="text-dim">You are logged in as <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3"><?php echo strtoupper($role); ?></span></p>
        </div>

        <div class="row g-4">
            <div class="col-md-4">
                <div class="card card-custom">
                    <p class="stat-label">Active Complaints</p>
                    <h2 class="stat-value text-accent"><?php echo $totalComplaints; ?></h2>
                    <p class="text-dim small mt-2">Actionable requests within your scope</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card card-custom">
                    <p class="stat-label">Notifications</p>
                    <h2 class="stat-value text-white"><?php
                        $notificationService = new NotificationService();
                        echo $notificationService->getUnreadCount($userId);
                    ?></h2>
                    <p class="text-dim small mt-2">Unread messages and alerts</p>
                </div>
            </div>
        </div>

        <!-- Engagement Section: Badges & Knowledge Base -->
        <div class="row g-4 mb-4 mt-4">
            <div class="col-md-6">
                <div class="card card-custom h-100">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0"><i class="fas fa-medal text-warning me-2"></i>My Achievements</h5>
                        <a href="student/badges.php" class="btn btn-sm btn-outline-success">View All</a>
                    </div>
                    <div class="badge-container d-flex gap-3 overflow-auto pb-2">
                        <div class="badge-item earned text-center" style="min-width: 80px;">
                            <i class="fas fa-bullhorn fa-2x text-success mb-1"></i>
                            <div class="small">First Voice</div>
                        </div>
                        <div class="badge-item text-center opacity-50" style="min-width: 80px;">
                            <i class="fas fa-check-circle fa-2x text-secondary mb-1"></i>
                            <div class="small">Solver</div>
                        </div>
                        <div class="badge-item text-center opacity-50" style="min-width: 80px;">
                            <i class="fas fa-bolt fa-2x text-secondary mb-1"></i>
                            <div class="small">Fast Track</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card card-custom h-100">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0"><i class="fas fa-book-open text-info me-2"></i>Knowledge Base</h5>
                        <a href="student/knowledge_base.php" class="btn btn-sm btn-outline-info">Browse</a>
                    </div>
                    <p class="text-dim small mb-3">Quick answers to common questions and system guides.</p>
                    <div class="list-group list-group-flush bg-transparent">
                        <a href="student/knowledge_base.php?category=general" class="list-group-item list-group-item-action bg-transparent text-light border-secondary small py-2">
                            <i class="fas fa-chevron-right me-2 text-info"></i>How to submit a complaint
                        </a>
                        <a href="student/knowledge_base.php?category=technical" class="list-group-item list-group-item-action bg-transparent text-light border-secondary small py-2">
                            <i class="fas fa-chevron-right me-2 text-info"></i>Password Reset Guide
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="card card-custom mt-5">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="fw-bold m-0">Recent Activity Overview</h4>
                <a href="<?php echo $role === 'student' ? 'student/tracker.php' : 'representative/forward.php'; ?>" class="btn btn-sm btn-outline-success rounded-pill px-3">View Full List</a>
            </div>
            <p class="text-dim">Displaying operations scoped to your access level.</p>
            <div class="table-responsive">
                <table class="table table-dark table-hover mt-3">
                    <thead>
                        <tr>
                            <th class="text-dim small text-uppercase">ID</th>
                            <th class="text-dim small text-uppercase">Category</th>
                            <th class="text-dim small text-uppercase">Priority</th>
                            <th class="text-dim small text-uppercase">Status</th>
                            <th class="text-dim small text-uppercase text-end">Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($activities)): ?>
                            <tr>
                                <td colspan="5" class="text-center text-dim py-5">
                                    <i class="fas fa-history fa-2x mb-3 opacity-25"></i>
                                    <p>No recent activity found in your workflow.</p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($activities as $act): ?>
                                <tr>
                                    <td class="fw-bold">#<?php echo $act['id']; ?></td>
                                    <td><?php echo htmlspecialchars($act['category']); ?></td>
                                    <td>
                                        <span class="badge rounded-pill bg-<?php echo strtolower($act['priority']) === 'high' ? 'danger' : (strtolower($act['priority']) === 'medium' ? 'warning text-dark' : 'info'); ?> px-2">
                                            <?php echo $act['priority']; ?>
                                        </span>
                                    </td>
                                    <td><span class="text-accent"><?php echo $act['status']; ?></span></td>
                                    <td class="text-end text-dim small"><?php echo date('M j, Y', strtotime($act['created_at'])); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/next-gen-ui.js"></script>
    <script>
        const sidebar = document.getElementById('sidebar');
        const toggleBtn = document.getElementById('sidebarToggle');
        const overlay = document.getElementById('sidebarOverlay');

        if (toggleBtn) {
            toggleBtn.addEventListener('click', () => {
                sidebar.classList.toggle('active');
                overlay.classList.toggle('show');
            });
        }
        if (overlay) {
            overlay.addEventListener('click', () => {
                sidebar.classList.remove('active');
                overlay.classList.remove('show');
            });
        }
    </script>
</body>
</html>
