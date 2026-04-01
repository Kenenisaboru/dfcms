<?php
// representative/forward.php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

$role = $_SESSION['role'];
$userId = $_SESSION['user_id'];

// Check permission
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

    // Core Routing Rule Check based on Phase 2 requirements:
    // CR can forward to Teacher or HOD.
    // Teacher can forward to CR or HOD.
    // HOD can forward to Teacher.
    $allowed_forward = false;
    if ($role == 'cr' && in_array($targetRole, ['teacher', 'hod'])) $allowed_forward = true;
    if ($role == 'teacher' && in_array($targetRole, ['cr', 'hod'])) $allowed_forward = true;
    if ($role == 'hod' && $targetRole == 'teacher') $allowed_forward = true;

    if (!$allowed_forward) {
        $error = "Unauthorized forwarding route: " . strtoupper($role) . " cannot forward to " . strtoupper($targetRole);
    } else {
        try {
            $pdo->beginTransaction();
            
            // Update complaint
            $stmt = $pdo->prepare("UPDATE complaints SET current_handler_role = ?, status = 'Forwarded' WHERE id = ?");
            $stmt->execute([$targetRole, $complaintId]);

            // Add to history
            $stmtHist = $pdo->prepare("INSERT INTO complaint_history (complaint_id, action_by, action, comments) VALUES (?, ?, 'Forwarded', ?)");
            $stmtHist->execute([$complaintId, $userId, "Forwarded to " . strtoupper($targetRole) . ": " . $comment]);

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
    <title>Inbox & Forwarding - DFCMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background-color: #121212; color: #fff; }
        .navbar-custom { background-color: #1e1e1e; border-bottom: 1px solid #333; }
        .card-custom { background-color: #1e1e1e; border: 1px solid #333; border-radius: 10px; margin-top: 20px; padding: 20px; }
        .badge-high { background-color: #ef4444; }
        .badge-medium { background-color: #f59e0b; }
        .badge-low { background-color: #3b82f6; }
        .form-control, .form-select { background-color: #2c2c2c; border: 1px solid #444; color: #fff; }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark navbar-custom px-4">
        <a class="navbar-brand text-success fw-bold" href="../dashboard.php">DFCMS</a>
        <div class="ms-auto"><a href="../dashboard.php" class="btn btn-outline-light btn-sm"><i class="fas fa-arrow-left"></i> Back to Dashboard</a></div>
    </nav>

    <div class="container my-5">
        <h3><i class="fas fa-inbox me-2 text-success"></i> <?php echo strtoupper($role); ?> Inbox & Routing</h3>
        
        <?php if ($error): ?><div class="alert alert-danger mt-3"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
        <?php if ($success): ?><div class="alert alert-success mt-3"><?php echo htmlspecialchars($success); ?></div><?php endif; ?>

        <div class="row mt-4">
            <?php if (count($inbox) > 0): ?>
                <?php foreach ($inbox as $item): ?>
                    <div class="col-md-6 mb-4">
                        <div class="card card-custom">
                            <div class="d-flex justify-content-between">
                                <h5>Complaint #<?php echo $item['id']; ?></h5>
                                <span class="badge badge-<?php echo strtolower($item['priority']); ?>"><?php echo $item['priority']; ?></span>
                            </div>
                            <p class="text-muted small">From: <?php echo htmlspecialchars($item['student_name']); ?> | Category: <?php echo $item['category']; ?></p>
                            <hr style="border-color: #333;">
                            <p><?php echo nl2br(htmlspecialchars($item['message'])); ?></p>
                            
                            <form method="POST" action="" class="mt-3">
                                <input type="hidden" name="complaint_id" value="<?php echo $item['id']; ?>">
                                <div class="row g-2">
                                    <div class="col-md-5">
                                        <select name="target_role" class="form-select form-select-sm" required>
                                            <option value="">Forward to...</option>
                                            <?php if($role == 'cr'): ?>
                                                <option value="teacher">Teacher</option>
                                                <option value="hod">Department Head</option>
                                            <?php elseif($role == 'teacher'): ?>
                                                <option value="cr">Forward to CR</option>
                                                <option value="hod">Forward to HOD</option>
                                            <?php elseif($role == 'hod'): ?>
                                                <option value="teacher">Forward to Teacher</option>
                                            <?php endif; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-7">
                                        <input type="text" name="forward_comment" class="form-control form-control-sm" placeholder="Add a note..." required>
                                    </div>
                                </div>
                                <button type="submit" name="forward_action" class="btn btn-success btn-sm w-100 mt-2"><i class="fas fa-paper-plane me-1"></i> Forward Complaint</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12 text-center py-5">
                    <i class="fas fa-check-circle fa-3x text-muted mb-3"></i>
                    <p class="text-muted">Your inbox is clean. No complaints routed to your role at this time.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
