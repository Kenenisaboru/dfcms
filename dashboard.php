<?php
// dashboard.php - Premium Dashboard v4.0
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

$page_title = "Dashboard";
include 'components/head.php';
?>

<body>
<div class="admin-layout">
    <!-- Sidebar -->
    <?php include 'components/sidebar.php'; ?>

    <div class="main-container">
        <!-- Top Navbar -->
        <?php 
        $current_role = $role;
        include 'components/navbar.php'; 
        ?>

        <!-- Page Content -->
        <main class="p-4 p-lg-5" style="max-width: 1600px;">
            <!-- Welcome Header -->
            <div class="d-md-flex align-items-center justify-content-between mb-5 page-header">
                <div>
                    <p class="text-secondary-color mb-1 fw-600" style="font-size: 0.875rem;">Good <?php echo date('H') < 12 ? 'morning' : (date('H') < 17 ? 'afternoon' : 'evening'); ?>,</p>
                    <h1 class="fw-800 mb-1" style="color: var(--premium-text-heading);">
                        <?php echo explode(' ', $userName)[0]; ?> 👋
                    </h1>
                    <p class="text-muted-color mb-0" style="font-size: 0.9375rem;">Here's what's happening with your complaints today.</p>
                </div>
                <div class="mt-4 mt-md-0 d-flex gap-3">
                    <?php if($role === 'admin'): ?>
                        <a href="admin/dashboard.php" class="btn btn-primary px-4 py-2 rounded-pill fw-600">
                            <i class="bi bi-shield-lock-fill me-2"></i> Admin Hub
                        </a>
                    <?php endif; ?>
                    <?php if($role === 'student'): ?>
                        <a href="student/submit_complaint.php" class="btn btn-primary px-4 py-2 rounded-pill fw-600" id="btn-file-complaint">
                            <i class="bi bi-plus-circle-fill me-2"></i> File Complaint
                        </a>
                    <?php endif; ?>
                    <button class="btn btn-white px-4 py-2 rounded-pill fw-600" id="btn-export-data">
                        <i class="bi bi-cloud-arrow-down me-2"></i> Export
                    </button>
                </div>
            </div>

            <!-- Stats Grid -->
            <div class="row g-4 mb-5">
                <!-- Total Submissions -->
                <div class="col-12 col-md-6 col-lg-3">
                    <div class="card h-100 stat-card border-0" id="stat-total-submissions">
                        <div class="card-body p-4">
                            <div class="stat-icon" style="color: var(--premium-primary);">
                                <i class="bi bi-journal-text"></i>
                            </div>
                            <div class="stat-icon-badge bg-primary-soft">
                                <i class="bi bi-journal-text"></i>
                            </div>
                            <div class="stat-label mb-2">Total Submissions</div>
                            <div class="stat-value"><?php echo $totalComplaints; ?></div>
                            <div class="d-flex align-items-center gap-1 mt-3" style="font-size: 0.8125rem;">
                                <span class="badge-soft badge-soft-success">
                                    <i class="bi bi-graph-up-arrow"></i> Active
                                </span>
                                <span class="text-muted-color ms-1">Consistent</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Unread Alerts -->
                <div class="col-12 col-md-6 col-lg-3">
                    <div class="card h-100 stat-card border-0" id="stat-unread-alerts">
                        <div class="card-body p-4">
                            <div class="stat-icon" style="color: var(--premium-info);">
                                <i class="bi bi-bell-fill"></i>
                            </div>
                            <div class="stat-icon-badge bg-primary-soft" style="background: var(--premium-info-soft) !important; color: var(--premium-info) !important;">
                                <i class="bi bi-bell-fill"></i>
                            </div>
                            <div class="stat-label mb-2">Unread Alerts</div>
                            <div class="stat-value">
                                <?php
                                    $notificationService = new NotificationService();
                                    echo $notificationService->getUnreadCount($userId);
                                ?>
                            </div>
                            <div class="d-flex align-items-center gap-1 mt-3" style="font-size: 0.8125rem;">
                                <span class="badge-soft badge-soft-info">
                                    <i class="bi bi-envelope"></i> New
                                </span>
                                <span class="text-muted-color ms-1">Check alerts</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pending Resolution -->
                <div class="col-12 col-md-6 col-lg-3">
                    <div class="card h-100 stat-card border-0" id="stat-pending-resolution">
                        <div class="card-body p-4">
                            <div class="stat-icon" style="color: var(--premium-amber);">
                                <i class="bi bi-hourglass-split"></i>
                            </div>
                            <div class="stat-icon-badge bg-amber-soft">
                                <i class="bi bi-hourglass-split"></i>
                            </div>
                            <div class="stat-label mb-2">Pending</div>
                            <div class="stat-value"><?php echo count($activities); ?></div>
                            <div class="d-flex align-items-center gap-1 mt-3" style="font-size: 0.8125rem;">
                                <span class="badge-soft badge-soft-warning">
                                    <i class="bi bi-clock-history"></i> Waiting
                                </span>
                                <span class="text-muted-color ms-1">In review</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Resolution Rate -->
                <div class="col-12 col-md-6 col-lg-3">
                    <div class="card h-100 stat-card border-0" id="stat-resolution-rate">
                        <div class="card-body p-4">
                            <div class="stat-icon" style="color: var(--premium-teal);">
                                <i class="bi bi-check-all"></i>
                            </div>
                            <div class="stat-icon-badge bg-teal-soft">
                                <i class="bi bi-check-circle-fill"></i>
                            </div>
                            <div class="stat-label mb-2">Resolution Rate</div>
                            <div class="stat-value">94<span style="font-size: 1rem; font-weight: 600;">%</span></div>
                            <div class="progress mt-3" style="height: 6px;">
                                <div class="progress-bar rounded-pill" role="progressbar" style="width: 94%; background: linear-gradient(90deg, var(--premium-teal), #00e68a);" aria-valuenow="94" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content Grid -->
            <div class="row g-4">
                <!-- Recent Complaints Table -->
                <div class="col-12 col-xl-8">
                    <div class="card border-0" id="card-recent-complaints">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="fw-700 mb-0" style="color: var(--premium-text-heading);">Recent Complaints</h5>
                                <p class="text-muted-color mb-0 x-small mt-1">Latest submissions and their status</p>
                            </div>
                            <a href="<?php echo $role === 'student' ? 'student/tracker.php' : 'representative/forward.php'; ?>" 
                               class="btn btn-light btn-sm rounded-pill px-3 fw-600" id="btn-see-all-complaints">
                                See All <i class="bi bi-arrow-right ms-1"></i>
                            </a>
                        </div>
                        <div class="card-body p-0">
                            <?php if (empty($activities)): ?>
                                <div class="text-center py-5 px-4">
                                    <div class="d-inline-flex align-items-center justify-content-center mb-3 rounded-xl" 
                                         style="width: 80px; height: 80px; background: var(--premium-bg);">
                                        <i class="bi bi-journal-x fs-1" style="color: var(--premium-text-muted);"></i>
                                    </div>
                                    <p class="fw-600 mb-1" style="color: var(--premium-text-heading);">No complaints yet</p>
                                    <p class="text-muted-color small mb-0">Your complaint history will appear here.</p>
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0">
                                        <thead>
                                            <tr>
                                                <th class="ps-4">Reference</th>
                                                <th>Category</th>
                                                <th>Priority</th>
                                                <th>Status</th>
                                                <th class="text-end pe-4">Date</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($activities as $act): ?>
                                                <tr>
                                                    <td class="ps-4">
                                                        <span class="fw-700" style="color: var(--premium-primary);">#<?php echo $act['id']; ?></span>
                                                    </td>
                                                    <td class="fw-500"><?php echo htmlspecialchars($act['category']); ?></td>
                                                    <td>
                                                        <?php 
                                                        $pClass = 'badge-soft-info';
                                                        if (strtolower($act['priority']) == 'high') $pClass = 'badge-soft-danger';
                                                        if (strtolower($act['priority']) == 'medium') $pClass = 'badge-soft-warning';
                                                        ?>
                                                        <span class="badge-soft <?php echo $pClass; ?>"><?php echo $act['priority']; ?></span>
                                                    </td>
                                                    <td>
                                                        <?php 
                                                        $sClass = 'badge-soft-info';
                                                        if (strtolower($act['status']) == 'resolved') $sClass = 'badge-soft-success';
                                                        if (strtolower($act['status']) == 'pending') $sClass = 'badge-soft-warning';
                                                        ?>
                                                        <span class="badge-soft <?php echo $sClass; ?>"><?php echo $act['status']; ?></span>
                                                    </td>
                                                    <td class="text-end pe-4 text-muted-color fw-500">
                                                        <?php echo date('M j, Y', strtotime($act['created_at'])); ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Right Sidebar -->
                <div class="col-12 col-xl-4">
                    <!-- Achievements Card -->
                    <div class="card border-0 mb-4" id="card-achievements">
                        <div class="card-header">
                            <h5 class="fw-700 mb-0" style="color: var(--premium-text-heading);">Recent Badges</h5>
                        </div>
                        <div class="card-body">
                            <div class="d-flex gap-4 overflow-auto pb-2">
                                <div class="text-center" style="min-width: 80px;">
                                    <div class="d-flex align-items-center justify-content-center mb-2 mx-auto rounded-xl" 
                                         style="width: 64px; height: 64px; background: var(--premium-primary-soft);">
                                        <i class="bi bi-star-fill fs-3" style="color: var(--premium-primary);"></i>
                                    </div>
                                    <span class="x-small fw-700" style="color: var(--premium-text-heading);">Early Bird</span>
                                </div>
                                <div class="text-center" style="min-width: 80px; opacity: 0.5;">
                                    <div class="d-flex align-items-center justify-content-center mb-2 mx-auto rounded-xl" 
                                         style="width: 64px; height: 64px; background: var(--premium-bg);">
                                        <i class="bi bi-shield-check fs-3" style="color: var(--premium-text-muted);"></i>
                                    </div>
                                    <span class="x-small fw-700" style="color: var(--premium-text-muted);">Verified</span>
                                </div>
                                <div class="text-center" style="min-width: 80px; opacity: 0.5;">
                                    <div class="d-flex align-items-center justify-content-center mb-2 mx-auto rounded-xl" 
                                         style="width: 64px; height: 64px; background: var(--premium-bg);">
                                        <i class="bi bi-trophy fs-3" style="color: var(--premium-text-muted);"></i>
                                    </div>
                                    <span class="x-small fw-700" style="color: var(--premium-text-muted);">Winner</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions Card -->
                    <div class="card border-0 mb-4" id="card-quick-actions">
                        <div class="card-header">
                            <h5 class="fw-700 mb-0" style="color: var(--premium-text-heading);">Quick Actions</h5>
                        </div>
                        <div class="card-body">
                            <div class="d-flex flex-column gap-2">
                                <a href="student/submit_complaint.php" class="d-flex align-items-center gap-3 p-3 rounded-xl text-decoration-none" 
                                   style="background: var(--premium-bg); transition: all 0.25s; border: 1px solid transparent;"
                                   onmouseover="this.style.borderColor='var(--premium-primary)'; this.style.transform='translateX(4px)'"
                                   onmouseout="this.style.borderColor='transparent'; this.style.transform='none'">
                                    <div class="stat-icon-badge bg-primary-soft" style="width: 42px; height: 42px; margin: 0; flex-shrink: 0;">
                                        <i class="bi bi-plus-lg" style="font-size: 1rem;"></i>
                                    </div>
                                    <div>
                                        <div class="fw-600" style="color: var(--premium-text-heading); font-size: 0.875rem;">New Complaint</div>
                                        <div class="x-small text-muted-color">Submit a new feedback</div>
                                    </div>
                                    <i class="bi bi-chevron-right ms-auto" style="color: var(--premium-text-muted);"></i>
                                </a>
                                <a href="student/knowledge_base.php" class="d-flex align-items-center gap-3 p-3 rounded-xl text-decoration-none" 
                                   style="background: var(--premium-bg); transition: all 0.25s; border: 1px solid transparent;"
                                   onmouseover="this.style.borderColor='var(--premium-teal)'; this.style.transform='translateX(4px)'"
                                   onmouseout="this.style.borderColor='transparent'; this.style.transform='none'">
                                    <div class="stat-icon-badge bg-teal-soft" style="width: 42px; height: 42px; margin: 0; flex-shrink: 0;">
                                        <i class="bi bi-book" style="font-size: 1rem;"></i>
                                    </div>
                                    <div>
                                        <div class="fw-600" style="color: var(--premium-text-heading); font-size: 0.875rem;">Knowledge Base</div>
                                        <div class="x-small text-muted-color">Browse guides & FAQ</div>
                                    </div>
                                    <i class="bi bi-chevron-right ms-auto" style="color: var(--premium-text-muted);"></i>
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Help CTA Card -->
                    <div class="card border-0 overflow-hidden position-relative" id="card-help-cta" 
                         style="background: linear-gradient(135deg, var(--premium-primary) 0%, #7551ff 100%); border: none !important;">
                        <div class="card-body p-4 position-relative" style="z-index: 1;">
                            <h4 class="fw-800 text-white mb-2">Need Help?</h4>
                            <p class="small text-white mb-4" style="opacity: 0.85;">Our comprehensive guides are here to help you navigate through any issues you might face.</p>
                            <a href="student/knowledge_base.php" class="btn btn-white rounded-pill px-4 py-2 fw-600 small" id="btn-explore-faq">
                                Explore FAQ <i class="bi bi-arrow-right ms-1"></i>
                            </a>
                        </div>
                        <!-- Decorative Icon -->
                        <i class="bi bi-question-diamond position-absolute text-white" 
                           style="font-size: 10rem; right: -1.5rem; bottom: -2.5rem; opacity: 0.12; pointer-events: none;"></i>
                        <!-- Decorative circles -->
                        <div class="position-absolute" style="width: 120px; height: 120px; border-radius: 50%; background: rgba(255,255,255,0.06); top: -30px; left: -30px; pointer-events: none;"></div>
                        <div class="position-absolute" style="width: 80px; height: 80px; border-radius: 50%; background: rgba(255,255,255,0.04); bottom: 20px; left: 40%; pointer-events: none;"></div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<?php include 'components/footer.php'; ?>
</body>
</html>
