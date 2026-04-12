<?php
require_once '../config/config.php';

// Check if user is logged in
check_login();

$role = $_SESSION['role'];
$userId = $_SESSION['user_id'];

// Check permission: CR, Teacher, HOD, or Lab Assistant
if (!in_array($role, ['cr', 'teacher', 'hod', 'lab_assistant'])) {
    die("Access Denied: Your role does not have permission to access the Action Hub.");
}

$error = '';
$success = '';

// BASE ACTION HANDLER
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    CSRF::validateRequest();
    $complaintId = isset($_POST['complaint_id']) ? (int)$_POST['complaint_id'] : 0;
    
    // 1. FORWARD ACTION
    if (isset($_POST['forward_action'])) {
        $targetRole = isset($_POST['target_role']) ? trim($_POST['target_role']) : '';
        $comment = isset($_POST['action_comment']) ? trim($_POST['action_comment']) : '';

        if ($complaintId === 0 || empty($targetRole) || empty($comment)) {
            $error = "Please fill out all fields to route this complaint.";
        } elseif (!AccessManager::canForward($role, $targetRole)) {
            $error = "UNAUTHORIZED ROUTING: " . strtoupper($role) . " cannot forward to " . strtoupper($targetRole);
        } else {
            try {
                $pdo->beginTransaction();
                $stmt = $pdo->prepare("UPDATE complaints SET current_handler_role = ?, status = 'Forwarded' WHERE id = ?");
                $stmt->execute([$targetRole, $complaintId]);

                $stmtHist = $pdo->prepare("INSERT INTO complaint_history (complaint_id, action_by, action, comments) VALUES (?, ?, 'Forwarded', ?)");
                $stmtHist->execute([$complaintId, $userId, "Forwarded to " . strtoupper($targetRole) . ": " . $comment]);

                NotificationManager::sendToRole($pdo, $targetRole, "Complaint #$complaintId forwarded to you.", "representative/forward.php", 'complaint_assigned', 'Complaint Received');

                $pdo->commit();
                $success = "Complaint #$complaintId forwarded successfully.";
            } catch (Exception $e) { $pdo->rollBack(); $error = "Action failed."; }
        }
    }

    // 2. RESOLVE ACTION
    if (isset($_POST['resolve_action'])) {
        $comment = isset($_POST['action_comment']) ? trim($_POST['action_comment']) : '';
        if ($complaintId === 0 || empty($comment)) {
            $error = "Please provide a completion comment.";
        } else {
            try {
                $pdo->beginTransaction();
                $pdo->prepare("UPDATE complaints SET status = 'Resolved', assigned_to = ? WHERE id = ?")->execute([$userId, $complaintId]);
                $pdo->prepare("INSERT INTO complaint_history (complaint_id, action_by, action, comments) VALUES (?, ?, 'Resolved', ?)")->execute([$complaintId, $userId, $comment]);
                
                $stmtStudent = $pdo->prepare("SELECT student_id FROM complaints WHERE id = ?");
                $stmtStudent->execute([$complaintId]);
                NotificationManager::send($pdo, $stmtStudent->fetchColumn(), "Your complaint #$complaintId has been RESOLVED.", "student/tracker.php", 'complaint_resolved', 'Issue Resolved');

                $pdo->commit();
                $success = "Complaint resolved successfully.";
            } catch (Exception $e) { $pdo->rollBack(); $error = "Action failed."; }
        }
    }

    // 3. REJECT ACTION
    if (isset($_POST['reject_action'])) {
        $comment = isset($_POST['action_comment']) ? trim($_POST['action_comment']) : '';
        if ($complaintId === 0 || empty($comment)) {
            $error = "Please provide a rejection reason.";
        } else {
            try {
                $pdo->beginTransaction();
                $pdo->prepare("UPDATE complaints SET status = 'Rejected', assigned_to = ? WHERE id = ?")->execute([$userId, $complaintId]);
                $pdo->prepare("INSERT INTO complaint_history (complaint_id, action_by, action, comments) VALUES (?, ?, 'Rejected', ?)")->execute([$complaintId, $userId, $comment]);
                
                $stmtStudent = $pdo->prepare("SELECT student_id FROM complaints WHERE id = ?");
                $stmtStudent->execute([$complaintId]);
                NotificationManager::send($pdo, $stmtStudent->fetchColumn(), "Your complaint #$complaintId was rejected.", "student/tracker.php", 'complaint_rejected', 'Issue Closed');

                $pdo->commit();
                $success = "Complaint rejected.";
            } catch (Exception $e) { $pdo->rollBack(); $error = "Action failed."; }
        }
    }
}

// Fetch complaints
$inbox = [];
if ($pdo) {
    $stmt = $pdo->prepare("SELECT c.*, u.full_name as student_name FROM complaints c JOIN users u ON c.student_id = u.id WHERE (c.current_handler_role = ? OR c.assigned_to = ?) AND c.status NOT IN ('Resolved', 'Rejected') ORDER BY c.priority DESC, c.created_at ASC");
    $stmt->execute([$role, $userId]);
    $inbox = $stmt->fetchAll();
}

$page_title = "Action Hub";
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
            <!-- Header -->
            <div class="d-md-flex align-items-center justify-content-between mb-5">
                <div>
                    <h1 class="display-6 fw-bold mb-1">Action Hub</h1>
                    <p class="text-muted mb-0">Manage and route incoming complaints for the <?php echo strtoupper($role); ?> role.</p>
                </div>
                <div class="mt-3 mt-md-0">
                    <span class="badge bg-primary-soft text-primary px-3 py-2 rounded-pill fw-bold">
                        <i class="bi bi-inbox-fill me-1"></i> <?php echo count($inbox); ?> Pending Actions
                    </span>
                </div>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-danger border-0 shadow-sm rounded-4 p-3 mb-4 small" role="alert">
                    <i class="bi bi-exclamation-circle-fill me-2"></i> <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success border-0 shadow-sm rounded-4 p-3 mb-4 small" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i> <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>

            <div class="row g-4">
                <?php if (count($inbox) > 0): ?>
                    <?php foreach ($inbox as $item): ?>
                        <div class="col-12">
                            <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
                                <div class="card-body p-4 p-md-5">
                                    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
                                        <div>
                                            <span class="badge-soft badge-soft-primary mb-2 d-inline-block">Issue #<?php echo $item['id']; ?></span>
                                            <h3 class="fw-bold mb-0"><?php echo htmlspecialchars($item['category']); ?></h3>
                                        </div>
                                        <div class="d-flex gap-2">
                                            <?php 
                                            $pClass = 'badge-soft-info';
                                            if (strtolower($item['priority']) == 'high') $pClass = 'badge-soft-danger';
                                            if (strtolower($item['priority']) == 'medium') $pClass = 'badge-soft-warning';
                                            ?>
                                            <span class="badge-soft <?php echo $pClass; ?> px-3 py-2 fw-bold"><?php echo $item['priority']; ?> Priority</span>
                                        </div>
                                    </div>

                                    <div class="row g-4 mb-4">
                                        <div class="col-md-6 col-lg-3">
                                            <div class="small text-muted mb-1 text-uppercase fw-bold tracking-wider" style="font-size: 0.65rem;">From Student</div>
                                            <div class="fw-bold d-flex align-items-center">
                                                <div class="bg-light rounded-circle p-2 me-2 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                                                    <i class="bi bi-person text-primary"></i>
                                                </div>
                                                <?php echo htmlspecialchars($item['student_name']); ?>
                                            </div>
                                        </div>
                                        <div class="col-md-6 col-lg-3">
                                            <div class="small text-muted mb-1 text-uppercase fw-bold tracking-wider" style="font-size: 0.65rem;">Date Submitted</div>
                                            <div class="fw-bold"><i class="bi bi-calendar-event me-2 text-muted"></i><?php echo date('M j, Y', strtotime($item['created_at'])); ?></div>
                                        </div>
                                    </div>

                                    <div class="bg-light p-4 rounded-4 mb-5 border-start border-primary border-4">
                                        <div class="small text-muted mb-2 fw-bold text-uppercase" style="font-size: 0.65rem;">Description</div>
                                        <p class="mb-0 text-dark lh-base"><?php echo nl2br(htmlspecialchars($item['message'])); ?></p>
                                    </div>

                                    <form method="POST" action="">
                                        <?php echo CSRF::input(); ?>
                                        <input type="hidden" name="complaint_id" value="<?php echo $item['id']; ?>">
                                        
                                        <div class="row g-4">
                                            <div class="col-12">
                                                <label class="form-label fw-bold small text-uppercase tracking-wider">Internal Action Note</label>
                                                <textarea name="action_comment" class="form-control border-light bg-light" rows="3" placeholder="Describe your action or reason for routing/closing..." required></textarea>
                                            </div>
                                            
                                            <div class="col-md-4">
                                                <label class="form-label fw-bold small text-uppercase tracking-wider">Route to Role</label>
                                                <select name="target_role" class="form-select border-light bg-light">
                                                    <option value="">Choose target...</option>
                                                    <?php if($role == 'cr'): ?>
                                                        <option value="teacher">Teacher</option>
                                                        <option value="lab_assistant">Lab Assistant</option>
                                                        <option value="hod">HOD</option>
                                                    <?php elseif($role == 'teacher'): ?>
                                                        <option value="cr">Forward to CR</option>
                                                        <option value="hod">Forward to HOD</option>
                                                        <option value="lab_assistant">Lab Assistant</option>
                                                    <?php elseif($role == 'hod'): ?>
                                                        <option value="teacher">Forward to Teacher</option>
                                                    <?php elseif($role == 'lab_assistant'): ?>
                                                        <option value="teacher">Report back to Teacher</option>
                                                        <option value="cr">Update CR</option>
                                                    <?php endif; ?>
                                                </select>
                                            </div>
                                            
                                            <div class="col-md-8 d-flex align-items-end gap-2 flex-wrap flex-md-nowrap">
                                                <button type="submit" name="forward_action" class="btn btn-primary shadow-sm rounded-pill flex-grow-1 py-2 fw-bold px-4 mt-2 mt-md-0"><i class="bi bi-send-check me-2"></i> Route</button>
                                                <button type="submit" name="resolve_action" class="btn btn-success shadow-sm rounded-pill flex-grow-1 py-2 fw-bold px-4 mt-2 mt-md-0 text-white"><i class="bi bi-check-circle me-2"></i> Resolve</button>
                                                <button type="submit" name="reject_action" class="btn btn-danger shadow-sm rounded-pill flex-grow-1 py-2 fw-bold px-4 mt-2 mt-md-0"><i class="bi bi-x-circle me-2"></i> Reject</button>
                                                <a href="../student/messages.php?receiver_id=<?php echo $item['student_id']; ?>" class="btn btn-white shadow-sm rounded-circle p-2 mt-2 mt-md-0 d-flex align-items-center justify-content-center" style="width: 42px; height: 42px;">
                                                    <i class="bi bi-chat-text fs-5"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12 text-center py-5">
                        <div class="bg-primary-soft text-primary rounded-circle d-inline-flex align-items-center justify-content-center mb-4 shadow-sm" style="width: 100px; height: 100px;">
                            <i class="bi bi-check2-all fs-1"></i>
                        </div>
                        <h3 class="fw-bold">All caught up!</h3>
                        <p class="text-muted">Your inbox is clear. All complaints have been routed or resolved.</p>
                        <a href="../dashboard.php" class="btn btn-primary rounded-pill px-4 mt-3">Return to Dashboard</a>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
</div>

<style>
    .bg-primary-soft { background-color: var(--brand-primary-soft); }
    .btn-white { background-color: #fff; color: #0f172a; border: 1px solid #e2e8f0; transition: all 0.2s; }
    .btn-white:hover { background-color: #f1f5f9; transform: translateY(-1px); }
    .tracking-wider { letter-spacing: 0.05em; }
</style>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<?php include '../components/footer.php'; ?>
</body>
</html>
