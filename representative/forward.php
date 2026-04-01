<?php
// representative/forward.php
session_start();
require_once '../config/database.php';
require_once '../config/permissions.php';
require_once '../config/notifications.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

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
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Inbox & Routing - DFCMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background-color: #0c0d0e; color: #fff; font-family: 'Inter', sans-serif; }
        .navbar-custom { background-color: #121212; border-bottom: 1px solid #333; }
        .card-custom { background-color: #121212; border: 1px solid #333; border-radius: 12px; margin-top: 20px; padding: 30px; }
        .form-control, .form-select { background-color: #eef2f7 !important; border: 1px solid #444 !important; color: #000 !important; padding: 12px; }
        .badge-high { background-color: #ef4444; }
        .badge-medium { background-color: #f59e0b; }
        .badge-low { background-color: #3b82f6; }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark navbar-custom px-4">
        <a class="navbar-brand text-success fw-bold" href="../dashboard.php">DFCMS</a>
        <div class="ms-auto"><a href="../dashboard.php" class="btn btn-outline-light btn-sm"><i class="fas fa-arrow-left"></i> Back to Dashboard</a></div>
    </nav>

    <div class="container my-5">
        <h2 class="mb-4 text-white fw-bold"><i class="fas fa-inbox me-2 text-success"></i> <?php echo strtoupper($role); ?> Action Hub</h2>
        
        <?php if ($error): ?><div class="alert alert-danger py-2"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
        <?php if ($success): ?><div class="alert alert-success py-2"><?php echo htmlspecialchars($success); ?></div><?php endif; ?>

        <div class="row mt-4">
            <?php if (count($inbox) > 0): ?>
                <?php foreach ($inbox as $item): ?>
                    <div class="col-md-12 mb-4">
                        <div class="card card-custom shadow">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h4 class="mb-0 text-white">Issue #<?php echo $item['id']; ?>: <?php echo $item['category']; ?></h4>
                                <span class="badge badge-<?php echo strtolower($item['priority']); ?> px-3 py-2"><?php echo $item['priority']; ?> Priority</span>
                            </div>
                            <p class="text-muted small"><strong>Source:</strong> <?php echo htmlspecialchars($item['student_name']); ?> | <strong>Category:</strong> <?php echo $item['category']; ?></p>
                            <div class="p-3 bg-dark rounded mb-4" style="border: 1px solid #333;">
                                <?php echo nl2br(htmlspecialchars($item['message'])); ?>
                            </div>
                            
                            <form method="POST" class="pt-3 border-top border-secondary">
                                <input type="hidden" name="complaint_id" value="<?php echo $item['id']; ?>">
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label text-muted small fw-bold">Forward To Role:</label>
                                        <select name="target_role" class="form-select" required>
                                            <option value="">Select Target...</option>
                                            <?php if($role == 'cr'): ?>
                                                <option value="teacher">Teacher</option>
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
                                        <label class="form-label text-muted small fw-bold">Routing Comment:</label>
                                        <input type="text" name="forward_comment" class="form-control" placeholder="Explain the reason for routing..." required>
                                    </div>
                                    <div class="col-md-2 d-flex align-items-end">
                                        <button type="submit" name="forward_action" class="btn btn-success w-100 py-3 fw-bold"><i class="fas fa-route me-1"></i> ROUTE</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12 text-center py-5">
                    <i class="fas fa-check-circle fa-4x text-muted mb-3 opacity-25"></i>
                    <p class="text-muted">Workflow queue is internal. All items processed.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
