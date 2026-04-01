<?php
// teacher/assign_lab.php
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

// Strict Role Check: Only Teacher or HOD can assign tasks
if (!in_array($role, ['teacher', 'hod'])) {
    die("Access Denied: You do not have permission to assign laboratory technical tasks.");
}

$error = '';
$success = '';

// Handle Lab Assignment
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['assign_task'])) {
    $complaintId = $_POST['complaint_id'];
    $labAssistantId = $_POST['lab_assistant_id'];
    $instruction = trim($_POST['instruction']);

    try {
        $pdo->beginTransaction();
        
        // Ensure Target is a Lab Assistant
        $stmtCheck = $pdo->prepare("SELECT role, full_name FROM users WHERE id = ?");
        $stmtCheck->execute([$labAssistantId]);
        $targetUser = $stmtCheck->fetch();

        if (!$targetUser || $targetUser['role'] !== 'lab_assistant') {
            $error = "Error: Assign only to Lab Assistants.";
        } else {
            // Update complaint
            $stmt = $pdo->prepare("UPDATE complaints SET current_handler_role = 'lab_assistant', assigned_to = ?, status = 'Assigned' WHERE id = ?");
            $stmt->execute([$labAssistantId, $complaintId]);

            // Add to history
            $stmtHist = $pdo->prepare("INSERT INTO complaint_history (complaint_id, action_by, action, comments) VALUES (?, ?, 'Assigned to Lab', ?)");
            $stmtHist->execute([$complaintId, $userId, "Technical assignment for Lab: " . $instruction]);

            // Send Real-Time Notification to Lab Assistant
            NotificationManager::send($pdo, $labAssistantId, "New technical assignment #$complaintId from " . strtoupper($role) . " ($userId)", "lab/dashboard.php");

            $pdo->commit();
            $success = "Task #$complaintId assigned successfully to " . htmlspecialchars($targetUser['full_name']);
        }
    } catch (Exception $e) {
        $pdo->rollBack();
        $error = "Assignment failed: " . $e->getMessage();
    }
}

// Fetch complaints currently handled by Teacher or HOD
$stmt = $pdo->prepare("SELECT c.*, u.full_name as student_name 
                      FROM complaints c 
                      JOIN users u ON c.student_id = u.id 
                      WHERE c.current_handler_role = ? AND c.status != 'Resolved'
                      ORDER BY c.priority DESC, c.created_at ASC");
$stmt->execute([$role]);
$complaints = $stmt->fetchAll();

// Fetch all Lab Assistants for the dropdown
$stmtLab = $pdo->query("SELECT id, full_name FROM users WHERE role = 'lab_assistant'");
$labAssistants = $stmtLab->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Lab Assignment Hub - DFCMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background-color: #0c0d0e; color: #fff; font-family: 'Inter', sans-serif; }
        .navbar-custom { background-color: #121212; border-bottom: 1px solid #333; }
        .card-custom { background-color: #121212; border: 1px solid #333; border-radius: 12px; margin-top: 20px; padding: 40px; }
        .form-control, .form-select { background-color: #eef2f7 !important; border: 1px solid #444 !important; color: #000 !important; padding: 12px; }
        .badge-high { background-color: #ef4444; }
        .badge-medium { background-color: #f59e0b; }
        .badge-low { background-color: #3b82f6; }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark navbar-custom px-4">
        <a class="navbar-brand text-accent fw-bold" href="../dashboard.php">DFCMS</a>
        <div class="ms-auto"><a href="../dashboard.php" class="btn btn-outline-light btn-sm"><i class="fas fa-arrow-left"></i> Back to Dashboard</a></div>
    </nav>

    <div class="container my-5">
        <h2 class="mb-4 text-white fw-bold"><i class="fas fa-microscope me-2 text-success"></i> Technical Lab Routing</h2>
        
        <?php if ($error): ?><div class="alert alert-danger py-2"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
        <?php if ($success): ?><div class="alert alert-success py-2"><?php echo htmlspecialchars($success); ?></div><?php endif; ?>

        <?php if (count($complaints) > 0): ?>
            <?php foreach ($complaints as $c): ?>
                <div class="card card-custom shadow mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h4 class="mb-0 text-white">Issue #<?php echo $c['id']; ?></h4>
                        <span class="badge badge-<?php echo strtolower($c['priority']); ?> px-4 py-2"><?php echo $c['priority']; ?> Importance</span>
                    </div>
                    <p class="text-muted small"><strong>Source:</strong> <?php echo htmlspecialchars($c['student_name']); ?> | <strong>Category:</strong> <?php echo $c['category']; ?></p>
                    <div class="p-3 bg-dark rounded mb-3" style="border-left: 5px solid #10b981;">
                        <?php echo nl2br(htmlspecialchars($c['message'])); ?>
                    </div>
                    
                    <form method="POST" class="border-top pt-4 border-secondary">
                        <input type="hidden" name="complaint_id" value="<?php echo $c['id']; ?>">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label text-muted small fw-bold">Select Lab Technician:</label>
                                <select name="lab_assistant_id" class="form-select" required>
                                    <option value="">Choose Staff Member...</option>
                                    <?php foreach($labAssistants as $staff): ?>
                                        <option value="<?php echo $staff['id']; ?>"><?php echo htmlspecialchars($staff['full_name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted small fw-bold">Technical Instruction:</label>
                                <input type="text" name="instruction" class="form-control" placeholder="Describe the maintenance required..." required>
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="submit" name="assign_task" class="btn btn-success w-100 py-3 fw-bold"><i class="fas fa-check-circle me-1"></i> ASSIGN</button>
                            </div>
                        </div>
                    </form>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12 text-center py-5">
                <i class="fas fa-flask fa-4x text-muted mb-3 opacity-25"></i>
                <p class="text-muted">No pending laboratory assignments found.</p>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
