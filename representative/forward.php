<?php
$page_title = "Action Hub";
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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Action Hub - DFCMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/next-gen-ui.css" rel="stylesheet">
    <style>
        .btn-action-group { display: flex; gap: 10px; margin-top: 20px; }
        .btn-resolve { background: #10b981; color: #000; font-weight: bold; border: none; }
        .btn-reject { background: #ef4444; color: #fff; font-weight: bold; border: none; }
        .btn-route { background: #3b82f6; color: #fff; font-weight: bold; border: none; }
        .action-card { transition: 0.3s; }
        .action-card:hover { transform: scale(1.01); }
    </style>
</head>
<body class="dark-mode">
    <?php include '../components/navbar.php'; ?>

    <div class="container my-5">
        <h2 class="mb-4 text-white fw-bold"><i class="fas fa-inbox me-2 text-accent"></i> <?php echo strtoupper($role); ?> Action Hub</h2>
        
        <?php if ($error): ?><div class="alert alert-danger py-2 border-0 bg-danger bg-opacity-25 text-danger"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
        <?php if ($success): ?><div class="alert alert-success py-2 border-0 bg-success bg-opacity-25 text-success"><?php echo htmlspecialchars($success); ?></div><?php endif; ?>

        <div class="row mt-4">
            <?php if (count($inbox) > 0): ?>
                <?php foreach ($inbox as $item): ?>
                    <div class="col-md-12 mb-4">
                        <div class="card card-custom action-card bg-glass border-0 shadow rounded-4 p-4">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h4 class="mb-0 text-white fw-bold">Issue #<?php echo $item['id']; ?>: <?php echo htmlspecialchars($item['category']); ?></h4>
                                <span class="badge rounded-pill bg-<?php echo strtolower($item['priority']) === 'high' ? 'danger' : (strtolower($item['priority']) === 'medium' ? 'warning text-dark' : 'info'); ?> px-3 py-2 small fw-bold"><?php echo $item['priority']; ?> Priority</span>
                            </div>
                            
                            <div class="d-flex align-items-center mb-4 text-dim small">
                                <div class="me-4"><i class="fas fa-user-graduate me-1"></i> <strong>Source:</strong> <span class="text-light"><?php echo htmlspecialchars($item['student_name']); ?></span></div>
                                <div><i class="fas fa-folder me-1"></i> <strong>Category:</strong> <span class="text-light"><?php echo htmlspecialchars($item['category']); ?></span></div>
                            </div>
                            
                            <div class="message-box bg-dark bg-opacity-25 border border-secondary border-opacity-10 p-3 rounded-3 mb-4">
                                <?php echo nl2br(htmlspecialchars($item['message'])); ?>
                            </div>
                            
                            <form method="POST" class="pt-3 border-top border-secondary border-opacity-10">
                                <?php echo CSRF::input(); ?>
                                <input type="hidden" name="complaint_id" value="<?php echo $item['id']; ?>">
                                
                                <div class="row g-3">
                                    <div class="col-md-12">
                                        <label class="form-label">Action Log / Comments:</label>
                                        <textarea name="action_comment" class="form-control" rows="2" placeholder="Describe your action or reason for routing/closing..." required></textarea>
                                    </div>
                                    
                                    <div class="col-md-4">
                                        <label class="form-label">Forward To (Optional):</label>
                                        <select name="target_role" class="form-select">
                                            <option value="">Select Target...</option>
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
                                    
                                    <div class="col-md-8 d-flex align-items-end gap-2">
                                        <button type="submit" name="forward_action" class="btn btn-route flex-grow-1 py-2"><i class="fas fa-route me-1"></i> ROUTE</button>
                                        <button type="submit" name="resolve_action" class="btn btn-resolve flex-grow-1 py-2"><i class="fas fa-check-circle me-1"></i> RESOLVE</button>
                                        <button type="submit" name="reject_action" class="btn btn-reject flex-grow-1 py-2"><i class="fas fa-times-circle me-1"></i> REJECT</button>
                                        <a href="../student/messages.php?receiver_id=<?php echo $item['student_id']; ?>" class="btn btn-outline-light border-2 rounded-3 px-3">
                                            <i class="fas fa-comment-dots"></i>
                                        </a>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12 text-center py-5">
                    <i class="fas fa-check-circle fa-4x text-dim mb-3 opacity-25"></i>
                    <p class="text-dim">Queue is empty. Excellent work!</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
