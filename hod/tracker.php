<?php
// hod/tracker.php - HOD Complaint Distribution Dashboard
require_once '../config/config.php';

check_login('hod');

$userId = $_SESSION['user_id'];
$error   = '';
$success = '';

// Fetch all teachers for the assign dropdown
$stmtTeachers = $pdo->query("SELECT id, full_name FROM users WHERE role = 'teacher' ORDER BY full_name ASC");
$teachers = $stmtTeachers->fetchAll();

// Fetch all lab assistants for routing
$stmtLabs = $pdo->query("SELECT id, full_name FROM users WHERE role = 'lab_assistant' ORDER BY full_name ASC");
$labAssistants = $stmtLabs->fetchAll();

// ── POST ACTIONS ──────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    CSRF::validateRequest();

    $complaintId = isset($_POST['complaint_id']) ? (int)$_POST['complaint_id'] : 0;
    $comment     = isset($_POST['action_comment']) ? trim($_POST['action_comment']) : '';

    // 1. ASSIGN to a specific teacher
    if (isset($_POST['assign_action'])) {
        $assignTo = isset($_POST['assign_to']) ? (int)$_POST['assign_to'] : 0;

        if ($complaintId === 0 || $assignTo === 0 || empty($comment)) {
            $error = "Please select a teacher and provide an assignment note.";
        } else {
            // Verify the target is actually a teacher
            $stmtCheck = $pdo->prepare("SELECT id, full_name FROM users WHERE id = ? AND role = 'teacher'");
            $stmtCheck->execute([$assignTo]);
            $targetTeacher = $stmtCheck->fetch();

            if (!$targetTeacher) {
                $error = "Invalid teacher selected.";
            } else {
                try {
                    $pdo->beginTransaction();
                    $pdo->prepare("UPDATE complaints SET assigned_to = ?, current_handler_role = 'teacher', status = 'Assigned' WHERE id = ?")
                        ->execute([$assignTo, $complaintId]);
                    $pdo->prepare("INSERT INTO complaint_history (complaint_id, action_by, action, comments) VALUES (?, ?, 'Assigned', ?)")
                        ->execute([$complaintId, $userId, "Assigned to " . $targetTeacher['full_name'] . ": " . $comment]);
                    NotificationManager::send($pdo, $assignTo, "Complaint #$complaintId has been assigned to you by HOD.", "representative/forward.php", 'complaint_assigned', 'New Assignment');
                    $pdo->commit();
                    $success = "Complaint #$complaintId assigned to {$targetTeacher['full_name']} successfully.";
                } catch (Exception $e) {
                    $pdo->rollBack();
                    $error = "Assignment failed. Please try again.";
                }
            }
        }
    }

    // 2. FORWARD to a role (lab_assistant or cr)
    if (isset($_POST['forward_action'])) {
        $targetRole = isset($_POST['target_role']) ? trim($_POST['target_role']) : '';

        if ($complaintId === 0 || empty($targetRole) || empty($comment)) {
            $error = "Please fill out all fields to route this complaint.";
        } elseif (!AccessManager::canForward('hod', $targetRole)) {
            $error = "HOD cannot forward to " . strtoupper($targetRole);
        } else {
            try {
                $pdo->beginTransaction();
                $pdo->prepare("UPDATE complaints SET current_handler_role = ?, assigned_to = NULL, status = 'Forwarded' WHERE id = ?")
                    ->execute([$targetRole, $complaintId]);
                $pdo->prepare("INSERT INTO complaint_history (complaint_id, action_by, action, comments) VALUES (?, ?, 'Forwarded', ?)")
                    ->execute([$complaintId, $userId, "Forwarded to " . strtoupper($targetRole) . ": " . $comment]);
                NotificationManager::sendToRole($pdo, $targetRole, "Complaint #$complaintId forwarded to you by HOD.", "representative/forward.php", 'complaint_assigned', 'Complaint Received');
                $pdo->commit();
                $success = "Complaint #$complaintId forwarded to " . strtoupper($targetRole) . ".";
            } catch (Exception $e) {
                $pdo->rollBack();
                $error = "Forward failed. Please try again.";
            }
        }
    }

    // 3. RESOLVE
    if (isset($_POST['resolve_action'])) {
        if ($complaintId === 0 || empty($comment)) {
            $error = "Please provide a resolution note.";
        } else {
            try {
                $pdo->beginTransaction();
                $pdo->prepare("UPDATE complaints SET status = 'Resolved', assigned_to = ? WHERE id = ?")
                    ->execute([$userId, $complaintId]);
                $pdo->prepare("INSERT INTO complaint_history (complaint_id, action_by, action, comments) VALUES (?, ?, 'Resolved', ?)")
                    ->execute([$complaintId, $userId, $comment]);
                $stmtSid = $pdo->prepare("SELECT student_id FROM complaints WHERE id = ?");
                $stmtSid->execute([$complaintId]);
                $studentId = $stmtSid->fetchColumn();
                if ($studentId) {
                    NotificationManager::send($pdo, $studentId, "Your complaint #$complaintId has been RESOLVED by HOD.", "student/tracker.php", 'complaint_resolved', 'Issue Resolved');
                }
                $pdo->commit();
                $success = "Complaint #$complaintId resolved.";
            } catch (Exception $e) {
                $pdo->rollBack();
                $error = "Resolve failed. Please try again.";
            }
        }
    }

    // 4. REJECT
    if (isset($_POST['reject_action'])) {
        if ($complaintId === 0 || empty($comment)) {
            $error = "Please provide a rejection reason.";
        } else {
            try {
                $pdo->beginTransaction();
                $pdo->prepare("UPDATE complaints SET status = 'Rejected', assigned_to = ? WHERE id = ?")
                    ->execute([$userId, $complaintId]);
                $pdo->prepare("INSERT INTO complaint_history (complaint_id, action_by, action, comments) VALUES (?, ?, 'Rejected', ?)")
                    ->execute([$complaintId, $userId, $comment]);
                $stmtSid = $pdo->prepare("SELECT student_id FROM complaints WHERE id = ?");
                $stmtSid->execute([$complaintId]);
                $studentId = $stmtSid->fetchColumn();
                if ($studentId) {
                    NotificationManager::send($pdo, $studentId, "Your complaint #$complaintId was rejected by HOD.", "student/tracker.php", 'complaint_rejected', 'Issue Closed');
                }
                $pdo->commit();
                $success = "Complaint #$complaintId rejected.";
            } catch (Exception $e) {
                $pdo->rollBack();
                $error = "Reject failed. Please try again.";
            }
        }
    }
}

// ── FETCH INBOX ───────────────────────────────────────────────────────────────
// HOD sees ALL complaints currently at HOD level (by role or directly assigned to this HOD)
$inbox = [];
$allComplaints = [];
if ($pdo) {
    $stmt = $pdo->prepare("
        SELECT c.*, 
               u.full_name  AS student_name,
               u2.full_name AS assigned_name
        FROM complaints c
        JOIN users u  ON c.student_id  = u.id
        LEFT JOIN users u2 ON c.assigned_to = u2.id
        WHERE (c.current_handler_role = 'hod' OR c.assigned_to = ?)
          AND c.status NOT IN ('Resolved', 'Rejected')
        ORDER BY 
            FIELD(c.priority, 'High', 'Medium', 'Low'),
            c.created_at ASC
    ");
    $stmt->execute([$userId]);
    $inbox = $stmt->fetchAll();

    // All complaints (for stats — including resolved/rejected)
    $stmtAll = $pdo->prepare("
        SELECT c.status, c.priority
        FROM complaints c
        WHERE c.current_handler_role = 'hod' OR c.assigned_to = ?
    ");
    $stmtAll->execute([$userId]);
    $allComplaints = $stmtAll->fetchAll();
}

// ── STATS ─────────────────────────────────────────────────────────────────────
$totalCount    = count($allComplaints);
$pendingCount  = count(array_filter($allComplaints, fn($c) => in_array($c['status'], ['Pending', 'Forwarded'])));
$assignedCount = count(array_filter($allComplaints, fn($c) => $c['status'] === 'Assigned'));
$resolvedCount = count(array_filter($allComplaints, fn($c) => $c['status'] === 'Resolved'));
$highCount     = count(array_filter($allComplaints, fn($c) => $c['priority'] === 'High'));

$page_title = "HOD Distribution Hub";
$base_path  = '../';
include '../components/head.php';
?>

<body>
<div class="admin-layout">
    <?php include '../components/sidebar.php'; ?>

    <div class="main-container">
        <?php
        $current_role = 'hod';
        include '../components/navbar.php';
        ?>

        <main class="p-4 p-lg-5" style="max-width: 1600px;">

            <!-- Page Header -->
            <div class="d-md-flex align-items-center justify-content-between mb-5 page-header">
                <div>
                    <h1 class="fw-800 mb-1" style="color: var(--premium-text-heading); font-size: 1.75rem;">
                        <i class="bi bi-diagram-3-fill me-2" style="color: var(--premium-primary);"></i>HOD Distribution Hub
                    </h1>
                    <p class="text-muted-color mb-0" style="font-size: 0.9375rem;">
                        Review, assign, and manage all complaints escalated to the Head of Department.
                    </p>
                </div>
                <div class="mt-3 mt-md-0 d-flex gap-2 flex-wrap">
                    <span class="badge-soft badge-soft-primary px-3 py-2 fw-700" style="font-size: 0.875rem;">
                        <i class="bi bi-inbox-fill me-1"></i><?php echo count($inbox); ?> Active
                    </span>
                    <?php if ($highCount > 0): ?>
                    <span class="badge-soft badge-soft-danger px-3 py-2 fw-700" style="font-size: 0.875rem;">
                        <i class="bi bi-exclamation-triangle-fill me-1"></i><?php echo $highCount; ?> High Priority
                    </span>
                    <?php endif; ?>
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
                            <div class="stat-label">Total Received</div>
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
                            <div class="stat-label">Awaiting Action</div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-lg-3">
                    <div class="card border-0 stat-card h-100">
                        <div class="card-body p-4 text-center">
                            <div class="stat-icon-badge mx-auto" style="width: 48px; height: 48px; background: var(--premium-info-soft); color: var(--premium-info);">
                                <i class="bi bi-person-check" style="font-size: 1.1rem;"></i>
                            </div>
                            <div class="stat-value mt-2" style="font-size: 1.5rem;"><?php echo $assignedCount; ?></div>
                            <div class="stat-label">Assigned</div>
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

            <!-- Alerts -->
            <?php if ($error): ?>
                <div class="alert alert-danger border-0 rounded-4 p-3 mb-4 small" role="alert">
                    <i class="bi bi-exclamation-circle-fill me-2"></i><?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="alert alert-success border-0 rounded-4 p-3 mb-4 small" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i><?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>

            <!-- Complaint Cards -->
            <?php if (empty($inbox)): ?>
                <div class="card border-0 text-center py-5 px-4">
                    <div class="d-inline-flex align-items-center justify-content-center mb-3 rounded-xl mx-auto"
                         style="width: 80px; height: 80px; background: var(--premium-bg);">
                        <i class="bi bi-check2-all fs-1" style="color: var(--premium-text-muted);"></i>
                    </div>
                    <p class="fw-600 mb-1" style="color: var(--premium-text-heading);">All caught up!</p>
                    <p class="text-muted-color small">No active complaints at HOD level right now.</p>
                    <a href="../dashboard.php" class="btn btn-primary rounded-pill px-4 mt-3 fw-600">Return to Dashboard</a>
                </div>
            <?php else: ?>
                <div class="row g-4">
                    <?php foreach ($inbox as $item): ?>
                        <?php
                        $pClass = 'badge-soft-info';
                        if (strtolower($item['priority']) === 'high')   $pClass = 'badge-soft-danger';
                        if (strtolower($item['priority']) === 'medium') $pClass = 'badge-soft-warning';

                        $sClass = 'badge-soft-warning';
                        if ($item['status'] === 'Assigned')   $sClass = 'badge-soft-info';
                        if ($item['status'] === 'Forwarded')  $sClass = 'badge-soft-primary';
                        if ($item['status'] === 'In-Progress') $sClass = 'badge-soft-info';
                        ?>
                        <div class="col-12">
                            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                                <!-- Card top accent bar by priority -->
                                <div style="height: 4px; background: <?php echo strtolower($item['priority']) === 'high' ? 'var(--premium-danger)' : (strtolower($item['priority']) === 'medium' ? 'var(--premium-amber)' : 'var(--premium-info)'); ?>;"></div>

                                <div class="card-body p-4 p-md-5">
                                    <!-- Header row -->
                                    <div class="d-flex flex-wrap justify-content-between align-items-start mb-4 gap-3">
                                        <div>
                                            <div class="d-flex align-items-center gap-2 mb-2">
                                                <span class="badge-soft badge-soft-primary">Issue #<?php echo $item['id']; ?></span>
                                                <span class="badge-soft <?php echo $pClass; ?>"><?php echo $item['priority']; ?> Priority</span>
                                                <span class="badge-soft <?php echo $sClass; ?>"><?php echo $item['status']; ?></span>
                                            </div>
                                            <h3 class="fw-700 mb-0" style="color: var(--premium-text-heading);">
                                                <?php echo htmlspecialchars($item['category']); ?>
                                            </h3>
                                        </div>
                                        <div class="text-end">
                                            <div class="small text-muted-color">
                                                <i class="bi bi-calendar3 me-1"></i><?php echo date('M j, Y', strtotime($item['created_at'])); ?>
                                            </div>
                                            <?php if ($item['assigned_name']): ?>
                                            <div class="small mt-1" style="color: var(--premium-info);">
                                                <i class="bi bi-person-check me-1"></i>Assigned to: <strong><?php echo htmlspecialchars($item['assigned_name']); ?></strong>
                                            </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <!-- Meta row -->
                                    <div class="row g-3 mb-4">
                                        <div class="col-md-4">
                                            <div class="small text-muted-color mb-1 text-uppercase fw-700" style="font-size: 0.65rem; letter-spacing: 0.05em;">From Student</div>
                                            <div class="d-flex align-items-center gap-2 fw-600" style="color: var(--premium-text-heading);">
                                                <div class="d-flex align-items-center justify-content-center rounded-circle"
                                                     style="width: 30px; height: 30px; background: var(--premium-primary-soft); font-size: 0.75rem; font-weight: 700; color: var(--premium-primary);">
                                                    <?php echo strtoupper(substr($item['student_name'], 0, 1)); ?>
                                                </div>
                                                <?php echo htmlspecialchars($item['student_name']); ?>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="small text-muted-color mb-1 text-uppercase fw-700" style="font-size: 0.65rem; letter-spacing: 0.05em;">Current Handler</div>
                                            <span class="badge bg-light text-dark rounded-pill px-3 py-1 border" style="font-size: 0.8rem;">
                                                <?php echo strtoupper($item['current_handler_role']); ?>
                                            </span>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="small text-muted-color mb-1 text-uppercase fw-700" style="font-size: 0.65rem; letter-spacing: 0.05em;">Last Updated</div>
                                            <div class="small fw-500" style="color: var(--premium-text-body);">
                                                <i class="bi bi-clock me-1"></i><?php echo date('M j, Y g:i A', strtotime($item['updated_at'])); ?>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Description -->
                                    <div class="p-4 rounded-4 mb-5" style="background: var(--premium-bg); border-left: 4px solid var(--premium-primary);">
                                        <div class="small text-muted-color mb-2 fw-700 text-uppercase" style="font-size: 0.65rem;">Complaint Description</div>
                                        <p class="mb-0" style="color: var(--premium-text-body); line-height: 1.7;">
                                            <?php echo nl2br(htmlspecialchars($item['message'])); ?>
                                        </p>
                                    </div>

                                    <!-- Action Form -->
                                    <form method="POST" action="">
                                        <?php echo CSRF::input(); ?>
                                        <input type="hidden" name="complaint_id" value="<?php echo $item['id']; ?>">

                                        <div class="row g-3">
                                            <!-- Action Note -->
                                            <div class="col-12">
                                                <label class="form-label fw-700 small text-uppercase" style="letter-spacing: 0.05em; font-size: 0.75rem;">
                                                    Action Note <span class="text-danger">*</span>
                                                </label>
                                                <textarea name="action_comment" class="form-control" rows="2"
                                                          placeholder="Describe your action, assignment reason, or resolution note..."
                                                          required></textarea>
                                            </div>

                                            <!-- Assign to Teacher -->
                                            <div class="col-md-5">
                                                <label class="form-label fw-700 small text-uppercase" style="letter-spacing: 0.05em; font-size: 0.75rem;">
                                                    Assign to Teacher
                                                </label>
                                                <div class="d-flex gap-2">
                                                    <select name="assign_to" class="form-select">
                                                        <option value="">Select teacher...</option>
                                                        <?php foreach ($teachers as $t): ?>
                                                            <option value="<?php echo $t['id']; ?>"><?php echo htmlspecialchars($t['full_name']); ?></option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                    <button type="submit" name="assign_action"
                                                            class="btn btn-primary rounded-pill px-4 fw-700 text-nowrap">
                                                        <i class="bi bi-person-check me-1"></i>Assign
                                                    </button>
                                                </div>
                                            </div>

                                            <!-- Forward to Role -->
                                            <div class="col-md-4">
                                                <label class="form-label fw-700 small text-uppercase" style="letter-spacing: 0.05em; font-size: 0.75rem;">
                                                    Forward to Role
                                                </label>
                                                <div class="d-flex gap-2">
                                                    <select name="target_role" class="form-select">
                                                        <option value="">Choose role...</option>
                                                        <option value="teacher">Teacher</option>
                                                        <option value="lab_assistant">Lab Assistant</option>
                                                        <option value="cr">Class Rep</option>
                                                    </select>
                                                    <button type="submit" name="forward_action"
                                                            class="btn btn-outline-primary rounded-pill px-3 fw-700 text-nowrap">
                                                        <i class="bi bi-send me-1"></i>Route
                                                    </button>
                                                </div>
                                            </div>

                                            <!-- Resolve / Reject / Chat -->
                                            <div class="col-md-3 d-flex align-items-end gap-2 flex-wrap">
                                                <button type="submit" name="resolve_action"
                                                        class="btn btn-success rounded-pill px-3 fw-700 flex-grow-1">
                                                    <i class="bi bi-check-circle me-1"></i>Resolve
                                                </button>
                                                <button type="submit" name="reject_action"
                                                        class="btn btn-danger rounded-pill px-3 fw-700 flex-grow-1">
                                                    <i class="bi bi-x-circle me-1"></i>Reject
                                                </button>
                                                <a href="../teacher/messages.php?receiver_id=<?php echo $item['student_id']; ?>"
                                                   class="btn btn-light rounded-circle p-2 d-flex align-items-center justify-content-center"
                                                   style="width: 40px; height: 40px;" title="Message Student">
                                                    <i class="bi bi-chat-text"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<?php include '../components/footer.php'; ?>
</body>
</html>
