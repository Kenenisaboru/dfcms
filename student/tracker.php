<?php
// student/tracker.php - Premium Complaint Tracker v4.0
require_once '../config/config.php';

check_login('student');

$userId = $_SESSION['user_id'];

// Get all complaints for the student
$stmt = $pdo->prepare("SELECT c.*, u.full_name as handler_name 
                      FROM complaints c 
                      LEFT JOIN users u ON c.assigned_to = u.id 
                      WHERE c.student_id = ? 
                      ORDER BY c.created_at DESC");
$stmt->execute([$userId]);
$complaints = $stmt->fetchAll();

// Stats
$totalCount = count($complaints);
$pendingCount = count(array_filter($complaints, fn($c) => $c['status'] === 'Pending'));
$progressCount = count(array_filter($complaints, fn($c) => $c['status'] === 'In-Progress'));
$resolvedCount = count(array_filter($complaints, fn($c) => $c['status'] === 'Resolved'));

$page_title = "My Complaints";
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
                        <i class="bi bi-list-task me-2" style="color: var(--premium-primary);"></i>My Complaints
                    </h1>
                    <p class="text-muted-color mb-0" style="font-size: 0.9375rem;">Track and manage all your submitted complaints</p>
                </div>
                <div class="mt-3 mt-md-0 d-flex gap-3">
                    <a href="submit_complaint.php" class="btn btn-primary rounded-pill px-4 py-2 fw-600" id="btn-new-complaint">
                        <i class="bi bi-plus-circle-fill me-2"></i> New Complaint
                    </a>
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

            <!-- Filter Bar -->
            <div class="card border-0 mb-4" id="card-filter">
                <div class="card-body py-3 px-4">
                    <div class="d-flex flex-wrap align-items-center gap-3">
                        <span class="fw-700 small" style="color: var(--premium-text-heading);">
                            <i class="bi bi-funnel me-1"></i>Filter:
                        </span>
                        <button class="btn btn-sm rounded-pill px-3 fw-600 filter-btn active" data-filter="all"
                                style="font-size: 0.75rem;">
                            All <span class="ms-1 badge rounded-pill" style="background: var(--premium-primary); font-size: 0.6rem;"><?php echo $totalCount; ?></span>
                        </button>
                        <button class="btn btn-sm btn-light rounded-pill px-3 fw-600 filter-btn" data-filter="Pending"
                                style="font-size: 0.75rem;">
                            Pending <span class="ms-1 badge rounded-pill bg-warning" style="font-size: 0.6rem;"><?php echo $pendingCount; ?></span>
                        </button>
                        <button class="btn btn-sm btn-light rounded-pill px-3 fw-600 filter-btn" data-filter="In-Progress"
                                style="font-size: 0.75rem;">
                            In Progress <span class="ms-1 badge rounded-pill bg-info" style="font-size: 0.6rem;"><?php echo $progressCount; ?></span>
                        </button>
                        <button class="btn btn-sm btn-light rounded-pill px-3 fw-600 filter-btn" data-filter="Resolved"
                                style="font-size: 0.75rem;">
                            Resolved <span class="ms-1 badge rounded-pill bg-success" style="font-size: 0.6rem;"><?php echo $resolvedCount; ?></span>
                        </button>
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
                            <p class="fw-600 mb-1" style="color: var(--premium-text-heading);">No complaints yet</p>
                            <p class="text-muted-color small mb-3">Submit your first complaint to get started.</p>
                            <a href="submit_complaint.php" class="btn btn-primary rounded-pill px-4 py-2 fw-600 btn-sm">
                                <i class="bi bi-plus-circle me-1"></i> Submit Complaint
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0" id="complaints-table">
                                <thead>
                                    <tr>
                                        <th class="ps-4">ID</th>
                                        <th>Category</th>
                                        <th>Handler</th>
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
                                                        <?php echo $c['handler_name'] ? strtoupper(substr($c['handler_name'], 0, 1)) : '?'; ?>
                                                    </div>
                                                    <span class="small"><?php echo $c['handler_name'] ? htmlspecialchars($c['handler_name']) : '<em class="text-muted-color">Unassigned</em>'; ?></span>
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
                                                    <?php if ($c['assigned_to']): ?>
                                                        <a href="messages.php?receiver_id=<?php echo $c['assigned_to']; ?>" 
                                                           class="btn btn-sm btn-light rounded-pill px-3 fw-600" style="font-size: 0.75rem;">
                                                            <i class="bi bi-chat-dots me-1"></i>Chat
                                                        </a>
                                                    <?php endif; ?>
                                                    <button class="btn btn-sm rounded-pill px-3 fw-600" 
                                                            style="font-size: 0.75rem; background: var(--premium-primary-soft); color: var(--premium-primary); border: none;"
                                                            onclick="viewHistory(<?php echo $c['id']; ?>)">
                                                        <i class="bi bi-clock-history me-1"></i>History
                                                    </button>
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

<!-- History Modal -->
<div class="modal fade" id="historyModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 rounded-xl overflow-hidden" style="box-shadow: var(--premium-shadow-xl);">
            <div class="modal-header py-3 px-4" style="border-bottom: 1px solid var(--premium-border-light); background: var(--premium-bg);">
                <h5 class="modal-title fw-700" style="color: var(--premium-text-heading);">
                    <i class="bi bi-clock-history me-2" style="color: var(--premium-primary);"></i>Complaint #<span id="modalCompId"></span> History
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div id="historyTimeline" class="history-timeline"></div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Filter Buttons */
    .filter-btn.active {
        background: var(--premium-primary) !important;
        color: #fff !important;
        border: none;
    }
    .filter-btn:not(.active) {
        background: var(--premium-bg);
        color: var(--premium-text-body);
        border: 1px solid var(--premium-border);
    }
    .filter-btn:not(.active):hover {
        background: var(--premium-white);
        border-color: var(--premium-primary);
        color: var(--premium-primary);
    }

    /* History Timeline */
    .history-timeline { position: relative; padding-left: 30px; }
    .history-timeline::before { content: ''; position: absolute; left: 10px; top: 0; bottom: 0; width: 2px; background: var(--premium-border); }
    .history-item { position: relative; margin-bottom: 25px; }
    .history-item::before { content: ''; position: absolute; left: -25px; top: 5px; width: 12px; height: 12px; border-radius: 50%; background: var(--premium-primary); border: 3px solid var(--premium-white); box-shadow: 0 0 0 2px var(--premium-border); z-index: 1; }
    .history-actor { font-weight: 700; color: var(--premium-primary); font-size: 0.875rem; }
    .history-date { font-size: 0.75rem; color: var(--premium-text-muted); margin-left: 10px; }
    .history-comment { margin-top: 5px; color: var(--premium-text-body); font-size: 0.875rem; background: var(--premium-bg); padding: 12px; border-radius: var(--radius-md); border: 1px solid var(--premium-border-light); }
</style>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
(function() {
    'use strict';

    // ═══════════════════════════════════════
    // STATUS FILTER
    // ═══════════════════════════════════════
    const filterBtns = document.querySelectorAll('.filter-btn');
    const rows = document.querySelectorAll('#complaints-table tbody tr');

    filterBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            filterBtns.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');

            const filter = btn.dataset.filter;
            rows.forEach(row => {
                if (filter === 'all' || row.dataset.status === filter) {
                    row.style.display = '';
                    row.style.animation = 'fadeInUp 0.3s ease forwards';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    });

    // ═══════════════════════════════════════
    // HISTORY MODAL
    // ═══════════════════════════════════════
    window.viewHistory = async function(id) {
        document.getElementById('modalCompId').innerText = id;
        document.getElementById('historyTimeline').innerHTML = `
            <div class="text-center py-4">
                <div class="spinner-border" style="color: var(--premium-primary);" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
        `;
        
        const modal = new bootstrap.Modal(document.getElementById('historyModal'));
        modal.show();

        try {
            const response = await fetch(`../api/get_complaint_history.php?id=${id}`);
            const data = await response.json();
            
            if (data.success) {
                let html = '';
                if (data.history.length === 0) {
                    html = '<p class="text-center py-4" style="color: var(--premium-text-muted);">No history found.</p>';
                } else {
                    data.history.forEach(item => {
                        html += `
                            <div class="history-item">
                                <div class="d-flex align-items-center flex-wrap">
                                    <span class="history-actor">${item.actor_name} (${item.actor_role.toUpperCase()})</span>
                                    <span class="history-date">${new Date(item.created_at).toLocaleString()}</span>
                                </div>
                                <div class="fw-700 small mt-1" style="color: var(--premium-text-heading);">${item.action}</div>
                                <div class="history-comment">${item.comments || 'No specific comments provided.'}</div>
                            </div>
                        `;
                    });
                }
                document.getElementById('historyTimeline').innerHTML = html;
            } else {
                document.getElementById('historyTimeline').innerHTML = `<p style="color: var(--premium-coral);">${data.message}</p>`;
            }
        } catch (err) {
            document.getElementById('historyTimeline').innerHTML = `<p style="color: var(--premium-coral);">Failed to load history.</p>`;
        }
    };
})();
</script>

<?php include '../components/footer.php'; ?>
</body>
</html>
