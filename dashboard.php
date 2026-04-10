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
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/dfcms-modern.css" rel="stylesheet">
    
    <style>
        /* Dashboard Specific Styles */
        .dashboard-page {
            display: flex;
            min-height: 100vh;
        }
        
        .page-header {
            margin-bottom: var(--space-8);
        }
        
        .page-header h1 {
            font-size: 2rem;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: var(--space-2);
        }
        
        .page-header p {
            font-size: 1rem;
            color: var(--text-secondary);
        }
        
        .role-badge {
            display: inline-flex;
            align-items: center;
            gap: var(--space-2);
            padding: var(--space-1) var(--space-4);
            background: rgba(16, 185, 129, 0.1);
            border: 1px solid rgba(16, 185, 129, 0.3);
            border-radius: var(--radius-full);
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--primary-400);
        }
        
        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: var(--space-6);
            margin-bottom: var(--space-8);
        }
        
        .stat-card-modern {
            padding: var(--space-6);
            display: flex;
            align-items: center;
            gap: var(--space-5);
        }
        
        .stat-icon-wrapper {
            width: 60px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: var(--radius-xl);
            flex-shrink: 0;
        }
        
        .stat-icon-wrapper.primary {
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.2) 0%, rgba(16, 185, 129, 0.05) 100%);
            color: var(--primary-400);
        }
        
        .stat-icon-wrapper.info {
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.2) 0%, rgba(59, 130, 246, 0.05) 100%);
            color: var(--info);
        }
        
        .stat-icon-wrapper.warning {
            background: linear-gradient(135deg, rgba(245, 158, 11, 0.2) 0%, rgba(245, 158, 11, 0.05) 100%);
            color: var(--warning);
        }
        
        .stat-icon-wrapper i {
            font-size: 1.5rem;
        }
        
        .stat-info h3 {
            font-size: 2rem;
            font-weight: 800;
            color: var(--text-primary);
            line-height: 1;
            margin-bottom: var(--space-1);
        }
        
        .stat-info p {
            font-size: 0.875rem;
            color: var(--text-secondary);
        }
        
        /* Quick Actions */
        .quick-actions {
            display: flex;
            gap: var(--space-3);
            margin-bottom: var(--space-8);
            flex-wrap: wrap;
        }
        
        .quick-action-btn {
            display: flex;
            align-items: center;
            gap: var(--space-2);
            padding: var(--space-3) var(--space-5);
            background: var(--glass-highlight);
            border: 1px solid var(--glass-border);
            border-radius: var(--radius-lg);
            color: var(--text-secondary);
            font-size: 0.875rem;
            font-weight: 500;
            text-decoration: none;
            transition: all var(--transition-fast);
        }
        
        .quick-action-btn:hover {
            background: rgba(16, 185, 129, 0.1);
            border-color: rgba(16, 185, 129, 0.3);
            color: var(--primary-400);
            transform: translateY(-2px);
        }
        
        .quick-action-btn i {
            font-size: 1rem;
        }
        
        /* Activity Table */
        .activity-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: var(--space-5);
        }
        
        .activity-header h3 {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--text-primary);
        }
        
        .priority-badge {
            display: inline-flex;
            align-items: center;
            gap: var(--space-1);
            padding: var(--space-1) var(--space-3);
            font-size: 0.75rem;
            font-weight: 600;
            border-radius: var(--radius-full);
        }
        
        .priority-badge.high {
            background: rgba(239, 68, 68, 0.15);
            color: var(--danger);
        }
        
        .priority-badge.medium {
            background: rgba(245, 158, 11, 0.15);
            color: var(--warning);
        }
        
        .priority-badge.low {
            background: rgba(59, 130, 246, 0.15);
            color: var(--info);
        }
        
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: var(--space-2);
            padding: var(--space-2) var(--space-3);
            background: var(--glass-highlight);
            border: 1px solid var(--glass-border);
            border-radius: var(--radius-full);
            font-size: 0.8125rem;
            font-weight: 500;
            color: var(--text-secondary);
        }
        
        .status-badge::before {
            content: '';
            width: 6px;
            height: 6px;
            border-radius: 50%;
        }
        
        .status-badge.pending::before {
            background: var(--warning);
            box-shadow: 0 0 6px var(--warning);
        }
        
        .status-badge.in-progress::before {
            background: var(--info);
            box-shadow: 0 0 6px var(--info);
        }
        
        .status-badge.resolved::before {
            background: var(--success);
            box-shadow: 0 0 6px var(--success);
        }
        
        /* Achievement Cards */
        .achievement-card {
            padding: var(--space-5);
        }
        
        .achievement-list {
            display: flex;
            gap: var(--space-4);
            overflow-x: auto;
            padding-bottom: var(--space-2);
        }
        
        .achievement-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            min-width: 80px;
            padding: var(--space-4);
            background: var(--glass-highlight);
            border: 1px solid var(--glass-border);
            border-radius: var(--radius-lg);
            transition: all var(--transition-fast);
        }
        
        .achievement-item.earned {
            background: rgba(16, 185, 129, 0.1);
            border-color: rgba(16, 185, 129, 0.3);
        }
        
        .achievement-item.earned i {
            color: var(--primary-400);
        }
        
        .achievement-item:not(.earned) {
            opacity: 0.5;
        }
        
        .achievement-item:not(.earned) i {
            color: var(--text-muted);
        }
        
        .achievement-item i {
            font-size: 1.75rem;
            margin-bottom: var(--space-2);
        }
        
        .achievement-item span {
            font-size: 0.75rem;
            font-weight: 500;
            color: var(--text-secondary);
        }
        
        /* Knowledge Base Preview */
        .kb-list {
            display: flex;
            flex-direction: column;
            gap: var(--space-3);
        }
        
        .kb-item {
            display: flex;
            align-items: center;
            gap: var(--space-3);
            padding: var(--space-3) var(--space-4);
            background: var(--glass-highlight);
            border: 1px solid var(--glass-border);
            border-radius: var(--radius-lg);
            text-decoration: none;
            transition: all var(--transition-fast);
        }
        
        .kb-item:hover {
            background: rgba(16, 185, 129, 0.05);
            border-color: rgba(16, 185, 129, 0.3);
            transform: translateX(4px);
        }
        
        .kb-item i {
            color: var(--primary-400);
            font-size: 0.875rem;
        }
        
        .kb-item span {
            font-size: 0.875rem;
            color: var(--text-secondary);
        }
        
        .kb-item:hover span {
            color: var(--text-primary);
        }
        
        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .activity-header {
                flex-direction: column;
                gap: var(--space-3);
                align-items: flex-start;
            }
        }
    </style>
</head>
<body class="dashboard-page">
    <!-- Mobile Navigation Toggle -->
    <button class="sidebar-toggle" id="sidebarToggle" aria-label="Toggle sidebar">
        <i class="fas fa-bars"></i>
    </button>

    <!-- Sidebar Overlay for Mobile -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <!-- Modern Sidebar -->
    <aside class="sidebar-modern" id="sidebar">
        <div class="sidebar-header">
            <div class="nav-brand-icon">
                <i class="fas fa-university"></i>
            </div>
            <span class="login-logo-text">DFCMS</span>
        </div>
        
        <nav class="sidebar-nav">
            <a href="dashboard.php" class="sidebar-link active">
                <i class="fas fa-home"></i> Dashboard
            </a>
            
            <?php if($role === 'student'): ?>
                <a href="student/submit_complaint.php" class="sidebar-link">
                    <i class="fas fa-plus-circle"></i> Submit Complaint
                </a>
                <a href="student/tracker.php" class="sidebar-link">
                    <i class="fas fa-search"></i> Track Status
                </a>
            <?php endif; ?>

            <?php if($role === 'cr' || $role === 'teacher' || $role === 'hod'): ?>
                <a href="representative/forward.php" class="sidebar-link">
                    <i class="fas fa-inbox"></i> Inbox / Forward
                </a>
            <?php endif; ?>

            <?php if($role === 'teacher' || $role === 'hod'): ?>
                <a href="teacher/assign_lab.php" class="sidebar-link">
                    <i class="fas fa-tasks"></i> Assign Tasks
                </a>
            <?php endif; ?>
            
            <div class="divider" style="margin: var(--space-4) 0;"></div>
            
            <a href="student/notifications.php" class="sidebar-link">
                <i class="fas fa-bell"></i> Notifications
            </a>
            <a href="student/messages.php" class="sidebar-link">
                <i class="fas fa-envelope"></i> Messages
            </a>
            <a href="student/badges.php" class="sidebar-link">
                <i class="fas fa-medal"></i> Achievements
            </a>
            <a href="student/knowledge_base.php" class="sidebar-link">
                <i class="fas fa-book"></i> Knowledge Base
            </a>
        </nav>
        
        <div class="sidebar-footer">
            <a href="auth/logout.php" class="sidebar-link" style="color: var(--danger);">
                <i class="fas fa-sign-out-alt"></i> Sign Out
            </a>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="main-with-sidebar">
        <!-- Page Header -->
        <header class="page-header animate-fade-in-down">
            <h1>Welcome back, <?php echo htmlspecialchars($userName); ?></h1>
            <p>
                You're signed in as 
                <span class="role-badge">
                    <i class="fas fa-shield-alt"></i>
                    <?php echo ucfirst($role); ?>
                </span>
            </p>
        </header>

        <!-- Quick Actions -->
        <div class="quick-actions animate-fade-in-up">
            <?php if($role === 'student'): ?>
                <a href="student/submit_complaint.php" class="quick-action-btn">
                    <i class="fas fa-plus-circle"></i>
                    Submit Complaint
                </a>
            <?php endif; ?>
            <a href="student/tracker.php" class="quick-action-btn">
                <i class="fas fa-search"></i>
                Track Status
            </a>
            <a href="student/notifications.php" class="quick-action-btn">
                <i class="fas fa-bell"></i>
                View Notifications
            </a>
            <a href="student/knowledge_base.php" class="quick-action-btn">
                <i class="fas fa-question-circle"></i>
                Get Help
            </a>
        </div>

        <!-- Stats Grid -->
        <div class="stats-grid stagger-children">
            <div class="card glass-card stat-card-modern">
                <div class="stat-icon-wrapper primary">
                    <i class="fas fa-clipboard-list"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $totalComplaints; ?></h3>
                    <p>Active Complaints</p>
                </div>
            </div>
            
            <div class="card glass-card stat-card-modern">
                <div class="stat-icon-wrapper info">
                    <i class="fas fa-bell"></i>
                </div>
                <div class="stat-info">
                    <h3><?php
                        $notificationService = new NotificationService();
                        echo $notificationService->getUnreadCount($userId);
                    ?></h3>
                    <p>Unread Notifications</p>
                </div>
            </div>
            
            <div class="card glass-card stat-card-modern">
                <div class="stat-icon-wrapper warning">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo count($activities); ?></h3>
                    <p>Recent Activity</p>
                </div>
            </div>
        </div>

        <!-- Secondary Content Grid -->
        <div class="row g-4" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: var(--space-6); margin-bottom: var(--space-8);">
            <!-- Achievements Card -->
            <div class="animate-fade-in-up" style="animation-delay: 0.1s;">
                <div class="card achievement-card">
                    <div class="activity-header">
                        <h3><i class="fas fa-medal text-warning me-2"></i>My Achievements</h3>
                        <a href="student/badges.php" class="btn btn-sm btn-secondary">View All</a>
                    </div>
                    <div class="achievement-list">
                        <div class="achievement-item earned">
                            <i class="fas fa-bullhorn"></i>
                            <span>First Voice</span>
                        </div>
                        <div class="achievement-item">
                            <i class="fas fa-check-circle"></i>
                            <span>Solver</span>
                        </div>
                        <div class="achievement-item">
                            <i class="fas fa-bolt"></i>
                            <span>Fast Track</span>
                        </div>
                        <div class="achievement-item">
                            <i class="fas fa-star"></i>
                            <span>Top Contributor</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Knowledge Base Card -->
            <div class="animate-fade-in-up" style="animation-delay: 0.2s;">
                <div class="card achievement-card">
                    <div class="activity-header">
                        <h3><i class="fas fa-book-open text-info me-2"></i>Knowledge Base</h3>
                        <a href="student/knowledge_base.php" class="btn btn-sm btn-secondary">Browse</a>
                    </div>
                    <p class="text-secondary mb-4">Quick answers to common questions and system guides.</p>
                    <div class="kb-list">
                        <a href="student/knowledge_base.php?category=general" class="kb-item">
                            <i class="fas fa-chevron-right"></i>
                            <span>How to submit a complaint</span>
                        </a>
                        <a href="student/knowledge_base.php?category=technical" class="kb-item">
                            <i class="fas fa-chevron-right"></i>
                            <span>Password Reset Guide</span>
                        </a>
                        <a href="student/knowledge_base.php?category=general" class="kb-item">
                            <i class="fas fa-chevron-right"></i>
                            <span>Understanding complaint statuses</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="card glass-card animate-fade-in-up" style="animation-delay: 0.3s;">
            <div class="activity-header">
                <h3><i class="fas fa-history text-accent me-2"></i>Recent Activity</h3>
                <a href="<?php echo $role === 'student' ? 'student/tracker.php' : 'representative/forward.php'; ?>" class="btn btn-sm btn-secondary">
                    View All
                </a>
            </div>
            
            <?php if (empty($activities)): ?>
                <div class="empty-state" style="padding: var(--space-12) var(--space-8);">
                    <div class="empty-state-icon">
                        <i class="fas fa-clipboard-list"></i>
                    </div>
                    <h4 class="empty-state-title">No Recent Activity</h4>
                    <p class="empty-state-description">Your recent complaints and actions will appear here once you start using the system.</p>
                </div>
            <?php else: ?>
                <div class="table-container">
                    <table class="table-modern">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Category</th>
                                <th>Priority</th>
                                <th>Status</th>
                                <th class="text-end">Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($activities as $act): ?>
                                <tr>
                                    <td class="fw-bold">#<?php echo $act['id']; ?></td>
                                    <td><?php echo htmlspecialchars($act['category']); ?></td>
                                    <td>
                                        <span class="priority-badge <?php echo strtolower($act['priority']); ?>">
                                            <?php echo $act['priority']; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="status-badge <?php echo strtolower(str_replace(' ', '-', $act['status'])); ?>">
                                            <?php echo $act['status']; ?>
                                        </span>
                                    </td>
                                    <td class="text-end text-tertiary">
                                        <?php echo date('M j, Y', strtotime($act['created_at'])); ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <!-- Modern UI Framework -->
    <script src="assets/js/dfcms-ui.js"></script>
    
    <script>
        // Initialize sidebar
        const sidebar = document.getElementById('sidebar');
        const toggleBtn = document.getElementById('sidebarToggle');
        const overlay = document.getElementById('sidebarOverlay');

        if (toggleBtn) {
            toggleBtn.addEventListener('click', () => {
                sidebar.classList.toggle('open');
                overlay.classList.toggle('show');
                document.body.style.overflow = sidebar.classList.contains('open') ? 'hidden' : '';
            });
        }
        
        if (overlay) {
            overlay.addEventListener('click', () => {
                sidebar.classList.remove('open');
                overlay.classList.remove('show');
                document.body.style.overflow = '';
            });
        }

        // Welcome toast for returning users
        document.addEventListener('DOMContentLoaded', () => {
            setTimeout(() => {
                DFCMS.toast.success('Welcome back!', 'You have successfully signed in.', { duration: 3000 });
            }, 1000);
        });
    </script>
</body>
</html>
