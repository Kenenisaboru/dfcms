<?php
// teacher/assign_lab.php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

$role = $_SESSION['role'];
$userId = $_SESSION['user_id'];

// Check permission: Only Teacher or HOD can assign tasks to Lab Assistant
if (!in_array($role, ['teacher', 'hod'])) {
    die("Access Denied: You do not have permission to assign lab tasks.");
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
        
        // Ensure the selected user is actually a lab assistant
        $stmtCheck = $pdo->prepare("SELECT id FROM users WHERE id = ? AND role = 'lab_assistant'");
        $stmtCheck->execute([$labAssistantId]);
        
        if (!$stmtCheck->fetch()) {
            throw new Exception("Invalid target user: Select a Lab Assistant.");
        }

        // Update complaint
        $stmt = $pdo->prepare("UPDATE complaints SET current_handler_role = 'lab_assistant', assigned_to = ?, status = 'Assigned' WHERE id = ?");
        $stmt->execute([$labAssistantId, $complaintId]);

        // Add to history
        $stmtHist = $pdo->prepare("INSERT INTO complaint_history (complaint_id, action_by, action, comments) VALUES (?, ?, 'Assigned to Lab', ?)");
        $stmtHist->execute([$complaintId, $userId, "Assigned to Lab Assistant for technical resolution: " . $instruction]);

        $pdo->commit();
        $success = "Task #$complaintId assigned to Lab Assistant successfully.";
    } catch (Exception $e) {
        $pdo->rollBack();
        $error = "Assignment failed: " . $e->getMessage();
    }
}

// Fetch complaints currently handled by Teacher or HOD that are not yet resolved
$stmt = $pdo->prepare("SELECT c.*, u.full_name as student_name 
                      FROM complaints c 
                      JOIN users u ON c.student_id = u.id 
                      WHERE c.current_handler_role = ? AND c.status != 'Resolved'
                      ORDER BY c.priority DESC, c.created_at ASC");
$stmt->execute([$role]);
$complaints = $stmt->fetchAll();

// Fetch all available Lab Assistants for the dropdown
$stmtLab = $pdo->query("SELECT id, full_name FROM users WHERE role = 'lab_assistant'");
$labAssistants = $stmtLab->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Assign Lab Tasks - DFCMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background-color: #121212; color: #fff; }
        .navbar-custom { background-color: #1e1e1e; border-bottom: 1px solid #333; }
        .card-custom { background-color: #1e1e1e; border: 1px solid #333; border-radius: 10px; margin-top: 20px; padding: 25px; }
        .form-control, .form-select { background-color: #2c2c2c; border: 1px solid #444; color: #fff; }
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
        <h3 class="mb-4"><i class="fas fa-microscope me-2 text-success"></i> Lab Task Assignment</h3>
        
        <?php if ($error): ?><div class="alert alert-danger mt-3"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
        <?php if ($success): ?><div class="alert alert-success mt-3"><?php echo htmlspecialchars($success); ?></div><?php endif; ?>

        <div class="row">
            <?php if (count($complaints) > 0): ?>
                <?php foreach ($complaints as $c): ?>
                    <div class="col-md-12 mb-4">
                        <div class="card card-custom">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="mb-0">Complaint #<?php echo $c['id']; ?>: <?php echo htmlspecialchars($c['category']); ?></h5>
                                <span class="badge badge-<?php echo strtolower($c['priority']); ?>"><?php echo $c['priority']; ?> Priority</span>
                            </div>
                            <p class="text-muted small">Student: <?php echo htmlspecialchars($c['student_name']); ?> | Submitted: <?php echo date('M d, Y', strtotime($c['created_at'])); ?></p>
                            <p class="mb-4"><?php echo nl2br(htmlspecialchars($c['message'])); ?></p>
                            
                            <form method="POST" action="" class="border-top pt-3 mt-3 border-secondary">
                                <input type="hidden" name="complaint_id" value="<?php echo $c['id']; ?>">
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label text-muted small">Assign To Lab Assistant:</label>
                                        <select name="lab_assistant_id" class="form-select" required>
                                            <option value="">Select Lab Staff...</option>
                                            <?php foreach($labAssistants as $staff): ?>
                                                <option value="<?php echo $staff['id']; ?>"><?php echo htmlspecialchars($staff['full_name']); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label text-muted small">Instruction for Lab Staff:</label>
                                        <input type="text" name="instruction" class="form-control" placeholder="Describe what technical fix is needed..." required>
                                    </div>
                                    <div class="col-md-2 d-flex align-items-end">
                                        <button type="submit" name="assign_task" class="btn btn-success w-100"><i class="fas fa-check me-1"></i> Assign</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12 text-center py-5">
                    <i class="fas fa-laptop-code fa-3x text-muted mb-3"></i>
                    <p class="text-muted">No complaints waiting for laboratory technical assignment.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
