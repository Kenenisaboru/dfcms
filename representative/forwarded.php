<?php
require_once '../config/config.php';

// Check if user is logged in
check_login();

$role = $_SESSION['role'];
$userId = $_SESSION['user_id'];

// Check permission: CR, Teacher, HOD, or Lab Assistant
if (!in_array($role, ['cr', 'teacher', 'hod', 'lab_assistant'])) {
    die("Access Denied.");
}

// Fetch complaints that THIS user has forwarded
// We look at complaint_history where action_by = userId AND action = 'Forwarded'
$stmt = $pdo->prepare("
    SELECT c.*, u.full_name as student_name, MAX(ch.created_at) as forwarded_at
    FROM complaints c 
    JOIN users u ON c.student_id = u.id 
    JOIN complaint_history ch ON c.id = ch.complaint_id 
    WHERE ch.action_by = ? AND ch.action = 'Forwarded'
    GROUP BY c.id
    ORDER BY forwarded_at DESC
");
$stmt->execute([$userId]);
$forwardedComplaints = $stmt->fetchAll();

$page_title = "Forwarded History";
$base_path = "../";
include '../components/head.php';
?>

<div class="admin-layout">
    <?php include '../components/sidebar.php'; ?>

    <div class="main-container">
        <?php 
        $current_role = $role;
        include '../components/navbar.php'; 
        ?>

        <main class="p-4 p-lg-5">
        <main class="p-4 p-lg-5" style="max-width: 1600px;">
            <!-- Header -->
            <div class="d-md-flex align-items-center justify-content-between mb-5 page-header pt-3">
                <div>
                    <h1 class="fw-800 mb-1 d-flex align-items-center gap-2" style="color: var(--premium-text-heading);">
                        <i class="bi bi-clock-history text-primary"></i> Forwarding History
                    </h1>
                    <p class="text-muted-color mb-0" style="font-size: 0.9375rem;">Review complaints you have routed to other departments.</p>
                </div>
                <div class="mt-4 mt-md-0 d-flex gap-3">
                    <span class="badge bg-info-soft text-info px-4 py-2 rounded-pill fw-700" style="font-size: 0.875rem;">
                        <i class="bi bi-send-check-fill me-1"></i> <?php echo count($forwardedComplaints); ?> Sent
                    </span>
                </div>
            </div>

            <div class="card border-0" style="border-radius: var(--radius-xl); box-shadow: var(--premium-shadow-sm); overflow: hidden;">
                <div class="card-body p-0">
                    <?php if (empty($forwardedComplaints)): ?>
                        <div class="text-center py-5">
                            <div class="d-inline-flex align-items-center justify-content-center mb-3 rounded-xl" 
                                 style="width: 80px; height: 80px; background: var(--premium-bg);">
                                <i class="bi bi-send-slash fs-1" style="color: var(--premium-text-muted);"></i>
                            </div>
                            <p class="fw-600 mb-1" style="color: var(--premium-text-heading);">You haven't forwarded any complaints yet.</p>
                            <p class="text-muted-color small mb-4">When you route complaints to other departments, they will appear here.</p>
                            <a href="forward.php" class="btn btn-primary rounded-pill px-4 fw-600">Go to Inbox</a>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover table-borderless align-middle mb-0">
                                <thead style="background: var(--premium-bg);">
                                    <tr>
                                        <th class="ps-4 py-3 fw-600" style="color: var(--premium-text-secondary); font-size: 0.85rem;">ID</th>
                                        <th class="py-3 fw-600" style="color: var(--premium-text-secondary); font-size: 0.85rem;">Student</th>
                                        <th class="py-3 fw-600" style="color: var(--premium-text-secondary); font-size: 0.85rem;">Category</th>
                                        <th class="py-3 fw-600" style="color: var(--premium-text-secondary); font-size: 0.85rem;">Current Handler</th>
                                        <th class="py-3 fw-600" style="color: var(--premium-text-secondary); font-size: 0.85rem;">Status</th>
                                        <th class="text-end pe-4 py-3 fw-600" style="color: var(--premium-text-secondary); font-size: 0.85rem;">Forwarded Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($forwardedComplaints as $item): ?>
                                        <tr style="border-bottom: 1px solid var(--premium-border-light);">
                                            <td class="ps-4 py-3">
                                                <span class="fw-700" style="color: var(--premium-primary);">#<?php echo $item['id']; ?></span>
                                            </td>
                                            <td class="py-3 fw-600" style="color: var(--premium-text-heading);">
                                                <?php echo htmlspecialchars($item['student_name']); ?>
                                            </td>
                                            <td class="py-3">
                                                <div class="d-flex align-items-center gap-2">
                                                    <i class="bi bi-folder2-open" style="color: var(--premium-text-muted);"></i>
                                                    <span class="fw-500" style="color: var(--premium-text-body);"><?php echo htmlspecialchars($item['category']); ?></span>
                                                </div>
                                            </td>
                                            <td class="py-3">
                                                <span class="badge bg-light text-dark rounded-pill px-3" style="border: 1px solid var(--premium-border);">
                                                    <?php echo strtoupper($item['current_handler_role']); ?>
                                                </span>
                                            </td>
                                            <td class="py-3">
                                                <?php 
                                                $sClass = 'badge-soft-info';
                                                if (strtolower($item['status']) == 'resolved') $sClass = 'badge-soft-success';
                                                if (strtolower($item['status']) == 'pending') $sClass = 'badge-soft-warning';
                                                if (strtolower($item['status']) == 'forwarded') $sClass = 'badge-soft-primary';
                                                ?>
                                                <span class="badge-soft <?php echo $sClass; ?>"><?php echo $item['status']; ?></span>
                                            </td>
                                            <td class="text-end pe-4 py-3 text-muted-color small fw-500">
                                                <i class="bi bi-calendar3 me-1"></i><?php echo date('M j, Y - g:i A', strtotime($item['forwarded_at'])); ?>
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

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<?php include '../components/footer.php'; ?>
</body>
</html>
