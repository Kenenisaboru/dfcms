<?php
// teacher/tracker.php - Teacher Complaint Tracker
require_once '../config/config.php';

check_login('teacher');

$userId = $_SESSION['user_id'];

// Get complaints assigned to this teacher
$stmt = $pdo->prepare("SELECT c.*, u.full_name as student_name 
                      FROM complaints c 
                      LEFT JOIN users u ON c.student_id = u.id 
                      WHERE c.assigned_to = ? 
                      ORDER BY c.created_at DESC");
$stmt->execute([$userId]);
$complaints = $stmt->fetchAll();

// Stats
$totalCount = count($complaints);
$pendingCount = count(array_filter($complaints, fn($c) => $c['status'] === 'Pending'));
$progressCount = count(array_filter($complaints, fn($c) => $c['status'] === 'In-Progress'));
$resolvedCount = count(array_filter($complaints, fn($c) => $c['status'] === 'Resolved'));

$page_title = "Assigned Complaints";
$base_path = '../';
include '../components/head.php';
?>

<body>
<div class="admin-layout">
    <?php include '../components/sidebar.php'; ?>

    <div class="main-container">
        <?php include '../components/navbar.php'; ?>

        <main class="p-4 p-lg-5" style="max-width: 1600px;">
            <!-- Page Header -->
            <div class="d-md-flex align-items-center justify-content-between mb-5 page-header">
                <div>
                    <h1 class="fw-800 mb-1" style="color: var(--premium-text-heading); font-size: 1.75rem;">
                        <i class="bi bi-list-task me-2" style="color: var(--premium-primary);"></i>Assigned Complaints
                    </h1>
                    <p class="text-muted-color mb-0" style="font-size: 0.9375rem;">Manage complaints assigned to you</p>
                </div>
            </div>

            <!-- Stats Row -->
            <div class="row g-4 mb-5">
                <div class="col-6 col-lg-3">
                    <div class="card border-0 stat-card h-100">
                        <div class="card-body p-4 text-center">
                            <div class="stat-icon-badge bg-primary-soft mx-auto" style="width: 48px; height: 48px;">
                                <i class="bi bi-journal-text" style="font-size: 1.1rem;"></i>
                            </div>
                            <div class="stat-value mt-2" style="font-size: 1.5rem;"><?php echo $totalCount; ?></div>
                            <div class="stat-label">Total</div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-lg-3">
                    <div class="card border-0 stat-card h-100">
                        <div class="card-body p-4 text-center">
                            <div class="stat-icon-badge bg-amber-soft mx-auto" style="width: 48px; height: 48px;">
                                <i class="bi bi-clock" style="font-size: 1.1rem;"></i>
                            </div>
                            <div class="stat-value mt-2" style="font-size: 1.5rem;"><?php echo $pendingCount; ?></div>
                            <div class="stat-label">Pending</div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-lg-3">
                    <div class="card border-0 stat-card h-100">
                        <div class="card-body p-4 text-center">
                            <div class="stat-icon-badge mx-auto" style="width: 48px; height: 48px; background: var(--premium-info-soft); color: var(--premium-info);">
                                <i class="bi bi-arrow-repeat" style="font-size: 1.1rem;"></i>
                            </div>
                            <div class="stat-value mt-2" style="font-size: 1.5rem;"><?php echo $progressCount; ?></div>
                            <div class="stat-label">In Progress</div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-lg-3">
                    <div class="card border-0 stat-card h-100">
                        <div class="card-body p-4 text-center">
                            <div class="stat-icon-badge bg-teal-soft mx-auto" style="width: 48px; height: 48px;">
                                <i class="bi bi-check-circle" style="font-size: 1.1rem;"></i>
                            </div>
                            <div class="stat-value mt-2" style="font-size: 1.5rem;"><?php echo $resolvedCount; ?></div>
                            <div class="stat-label">Resolved</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Complaints Table -->
            <div class="card border-0" id="card-complaints-table">
                <div class="card-body p-0">
                    <?php if (empty($complaints)): ?>
                        <div class="text-center py-5 px-4">
                            <div class="d-inline-flex align-items-center justify-content-center mb-3 rounded-xl" 
                                 style="width: 80px; height: 80px; background: var(--premium-bg);">
                                <i class="bi bi-clipboard-x fs-1" style="color: var(--premium-text-muted);"></i>
                            </div>
                            <p class="fw-600 mb-1" style="color: var(--premium-text-heading);">No complaints assigned</p>
                            <p class="text-muted-color small">You don't have any complaints assigned to you yet.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0" id="complaints-table">
                                <thead>
                                    <tr>
                                        <th class="ps-4">ID</th>
                                        <th>Category</th>
                                        <th>Student</th>
                                        <th>Priority</th>
                                        <th>Status</th>
                                        <th>Last Updated</th>
                                        <th class="text-end pe-4">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($complaints as $c): ?>
                                        <tr data-status="<?php echo $c['status']; ?>">
                                            <td class="ps-4">
                                                <span class="fw-700" style="color: var(--premium-primary);">#<?php echo $c['id']; ?></span>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center gap-2">
                                                    <i class="bi bi-folder2-open" style="color: var(--premium-text-muted);"></i>
                                                    <span class="fw-500"><?php echo htmlspecialchars($c['category']); ?></span>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center gap-2">
                                                    <div class="d-flex align-items-center justify-content-center rounded-circle" 
                                                         style="width: 28px; height: 28px; background: var(--premium-bg); font-size: 0.6875rem; font-weight: 700; color: var(--premium-text-secondary);">
                                                        <?php echo $c['student_name'] ? strtoupper(substr($c['student_name'], 0, 1)) : '?'; ?>
                                                    </div>
                                                    <span class="small"><?php echo $c['student_name'] ? htmlspecialchars($c['student_name']) : '<em class="text-muted-color">Unknown</em>'; ?></span>
                                                </div>
                                            </td>
                                            <td>
                                                <?php 
                                                $pClass = 'badge-soft-info';
                                                if (strtolower($c['priority']) == 'high') $pClass = 'badge-soft-danger';
                                                if (strtolower($c['priority']) == 'medium') $pClass = 'badge-soft-warning';
                                                ?>
                                                <span class="badge-soft <?php echo $pClass; ?>"><?php echo $c['priority']; ?></span>
                                            </td>
                                            <td>
                                                <?php
                                                $statusMap = [
                                                    'Pending' => 'badge-soft-warning',
                                                    'In-Progress' => 'badge-soft-info',
                                                    'Resolved' => 'badge-soft-success',
                                                    'Rejected' => 'badge-soft-danger',
                                                ];
                                                $sClass = $statusMap[$c['status']] ?? 'badge-soft-info';
                                                $statusIcon = [
                                                    'Pending' => 'bi-clock',
                                                    'In-Progress' => 'bi-arrow-repeat',
                                                    'Resolved' => 'bi-check-circle',
                                                    'Rejected' => 'bi-x-circle',
                                                ];
                                                $sIcon = $statusIcon[$c['status']] ?? 'bi-info-circle';
                                                ?>
                                                <span class="badge-soft <?php echo $sClass; ?>">
                                                    <i class="bi <?php echo $sIcon; ?>"></i> <?php echo $c['status']; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="small text-muted-color">
                                                    <i class="bi bi-calendar3 me-1"></i><?php echo date('M j, Y', strtotime($c['updated_at'])); ?>
                                                </span>
                                            </td>
                                            <td class="text-end pe-4">
                                                <div class="d-flex justify-content-end gap-2">
                                                    <?php if ($c['student_id']): ?>
                                                        <a href="messages.php?receiver_id=<?php echo $c['student_id']; ?>" 
                                                           class="btn btn-sm btn-light rounded-pill px-3 fw-600" style="font-size: 0.75rem;">
                                                            <i class="bi bi-chat-dots me-1"></i>Chat
                                                        </a>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<?php include '../components/footer.php'; ?>
</body>
</html>
