require_once '../config/config.php';

// Check if user is logged in
check_login();

$role = $_SESSION['role'];
$userId = $_SESSION['user_id'];

// Check permission: CR, Teacher, or HOD only
if (!in_array($role, ['cr', 'teacher', 'hod'])) {
    die("Access Denied: Your role does not have permission to access the Forwarding tool.");
}

$error = '';
$success = '';

// Handle Forwarding Action
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['forward_action'])) {
    // Validate CSRF
    CSRF::validate($_POST['csrf_token']);
    
    $complaintId = $_POST['complaint_id'];
    $targetRole = $_POST['target_role'];
    $comment = trim($_POST['forward_comment']);

    // ARCHITECTURAL PERMISSION CHECK
    if (!AccessManager::canForward($role, $targetRole)) {
        $error = "UNAUTHORIZED ROUTING: " . strtoupper($role) . " cannot forward to " . strtoupper($targetRole);
    } else {
        try {
            $pdo->beginTransaction();
            
            // Update complaint
            $stmt = $pdo->prepare("UPDATE complaints SET current_handler_role = ?, status = 'Forwarded' WHERE id = ?");
            $stmt->execute([$targetRole, $complaintId]);

            // Add to history
            $stmtHist = $pdo->prepare("INSERT INTO complaint_history (complaint_id, action_by, action, comments) VALUES (?, ?, 'Forwarded', ?)");
            $stmtHist->execute([$complaintId, $userId, "Forwarded to " . strtoupper($targetRole) . ": " . $comment]);

            // Notify Target Role (Role-based broadcast notification logic)
            // Note: In a larger system, this would notify all users with $targetRole or a specific assigned person
            $pdo->commit();
            $success = "Complaint #$complaintId forwarded successfully to " . strtoupper($targetRole);
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = "Operation failed: " . $e->getMessage();
        }
    }
}

// Fetch complaints currently routed to this role
$stmt = $pdo->prepare("SELECT c.*, u.full_name as student_name 
                      FROM complaints c 
                      JOIN users u ON c.student_id = u.id 
                      WHERE c.current_handler_role = ? AND c.status != 'Resolved'
                      ORDER BY c.priority DESC, c.created_at ASC");
$stmt->execute([$role]);
$inbox = $stmt->fetchAll();
?>
<?php
$page_title = "Action Hub";
include '../components/head.php';
?>
<body>
    <?php include '../components/navbar.php'; ?>

    <div class="container my-5">
        <h2 class="mb-4 text-white fw-bold"><i class="fas fa-inbox me-2 text-accent"></i> <?php echo strtoupper($role); ?> Action Hub</h2>
        
        <?php if ($error): ?><div class="alert alert-danger py-2 border-0 bg-danger bg-opacity-25 text-danger"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
        <?php if ($success): ?><div class="alert alert-success py-2 border-0 bg-success bg-opacity-25 text-success"><?php echo htmlspecialchars($success); ?></div><?php endif; ?>

        <div class="row mt-4">
            <?php if (count($inbox) > 0): ?>
                <?php foreach ($inbox as $item): ?>
                    <div class="col-md-12 mb-4">
                        <div class="card card-custom bg-glass border-0 shadow rounded-4 p-4">
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
                                    <div class="col-md-4">
                                        <label class="form-label">Forward To Role:</label>
                                        <select name="target_role" class="form-select" required>
                                            <option value="">Select Target...</option>
                                            <?php if($role == 'cr'): ?>
                                                <option value="teacher">Teacher</option>
                                                <option value="lab_assistant">Lab Assistant</option>
                                                <option value="hod">Department Head (HOD)</option>
                                            <?php elseif($role == 'teacher'): ?>
                                                <option value="cr">Forward to CR</option>
                                                <option value="hod">Forward to HOD</option>
                                            <?php elseif($role == 'hod'): ?>
                                                <option value="teacher">Forward to Teacher</option>
                                            <?php endif; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Routing Comment:</label>
                                        <input type="text" name="forward_comment" class="form-control" placeholder="Explain the reason for routing..." required>
                                    </div>
                                    <div class="col-md-2 d-flex align-items-end gap-2">
                                        <button type="submit" name="forward_action" class="btn btn-submit w-100"><i class="fas fa-route me-1"></i> ROUTE</button>
                                        <a href="../student/messages.php?receiver_id=<?php echo $item['student_id']; ?>" class="btn btn-outline-success border-2 rounded-pill px-3 shadow-sm d-flex align-items-center justify-content-center" title="Message Student">
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
                    <p class="text-dim">Workflow queue is internal. All items processed.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
