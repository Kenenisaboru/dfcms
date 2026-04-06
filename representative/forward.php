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
        :root {
            --primary: #10b981;
            --primary-glow: rgba(16, 185, 129, 0.4);
            --bg-dark: #0c0d0e;
            --card-bg: rgba(18, 18, 18, 0.7);
            --glass-border: rgba(255, 255, 255, 0.1);
            --text-light: #f8fafc;
            --text-dim: #94a3b8;
            --input-bg: #eef2f7;
        }

        body { 
            background-color: var(--bg-dark); 
            background-image: 
                radial-gradient(circle at 20% 20%, rgba(16, 185, 129, 0.05) 0%, transparent 40%),
                radial-gradient(circle at 80% 80%, rgba(16, 185, 129, 0.05) 0%, transparent 40%);
            color: var(--text-light); 
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
        }

        .navbar-custom { 
            background: rgba(18, 18, 18, 0.8);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid var(--glass-border); 
            padding: 1rem 2rem;
        }

        .card-custom { 
            background: var(--card-bg); 
            backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border); 
            border-radius: 20px; 
            margin-top: 20px; 
            padding: 30px; 
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            animation: fadeIn 0.8s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .form-label { 
            color: var(--text-light) !important; 
            font-size: 0.875rem;
            margin-bottom: 0.5rem;
            letter-spacing: 0.5px;
            font-weight: 600;
        }

        .text-dim { color: var(--text-dim) !important; }
        .text-accent { color: var(--primary); }

        .form-control, .form-select { 
            background-color: var(--input-bg) !important; 
            border: 1px solid var(--glass-border) !important; 
            color: #1e293b !important; 
            padding: 12px 16px; 
            border-radius: 10px;
            transition: all 0.3s ease;
        }

        .form-control:focus, .form-select:focus {
            box-shadow: 0 0 0 4px var(--primary-glow) !important;
            border-color: var(--primary) !important;
        }

        .badge-status {
            padding: 8px 16px;
            border-radius: 8px;
            font-weight: 700;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .badge-high { background-color: rgba(239, 68, 68, 0.15); color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.3); }
        .badge-medium { background-color: rgba(245, 158, 11, 0.15); color: #f59e0b; border: 1px solid rgba(245, 158, 11, 0.3); }
        .badge-low { background-color: rgba(59, 130, 246, 0.15); color: #3b82f6; border: 1px solid rgba(59, 130, 246, 0.3); }

        .message-box {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid var(--glass-border);
            border-radius: 12px;
            padding: 20px;
            font-size: 1rem;
            line-height: 1.6;
            color: var(--text-light);
            margin-bottom: 25px;
        }

        .btn-submit { 
            background: linear-gradient(135deg, var(--primary) 0%, #059669 100%);
            border: none; 
            padding: 14px; 
            font-weight: 700; 
            color: #fff; 
            letter-spacing: 1px; 
            border-radius: 10px;
            transition: all 0.3s ease;
        }
        
        .btn-submit:hover { 
            transform: translateY(-2px); 
            box-shadow: 0 10px 20px -5px var(--primary-glow);
            color: #fff;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark navbar-custom">
        <a class="navbar-brand text-accent fw-bold" href="../dashboard.php">DFCMS</a>
        <div class="ms-auto"><a href="../dashboard.php" class="btn btn-outline-light btn-sm"><i class="fas fa-arrow-left"></i> Back to Dashboard</a></div>
    </nav>

    <div class="container my-5">
        <h2 class="mb-4 text-white fw-bold"><i class="fas fa-inbox me-2 text-accent"></i> <?php echo strtoupper($role); ?> Action Hub</h2>
        
        <?php if ($error): ?><div class="alert alert-danger py-2 border-0 bg-danger bg-opacity-25 text-danger"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
        <?php if ($success): ?><div class="alert alert-success py-2 border-0 bg-success bg-opacity-25 text-success"><?php echo htmlspecialchars($success); ?></div><?php endif; ?>

        <div class="row mt-4">
            <?php if (count($inbox) > 0): ?>
                <?php foreach ($inbox as $item): ?>
                    <div class="col-md-12 mb-4">
                        <div class="card card-custom">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h4 class="mb-0 text-white fw-bold">Issue #<?php echo $item['id']; ?>: <?php echo htmlspecialchars($item['category']); ?></h4>
                                <span class="badge-status badge-<?php echo strtolower($item['priority']); ?>"><?php echo $item['priority']; ?> Priority</span>
                            </div>
                            
                            <div class="d-flex align-items-center mb-4 text-dim small">
                                <div class="me-4"><i class="fas fa-user-graduate me-1"></i> <strong>Source:</strong> <span class="text-light"><?php echo htmlspecialchars($item['student_name']); ?></span></div>
                                <div><i class="fas fa-folder me-1"></i> <strong>Category:</strong> <span class="text-light"><?php echo htmlspecialchars($item['category']); ?></span></div>
                            </div>
                            
                            <div class="message-box">
                                <?php echo nl2br(htmlspecialchars($item['message'])); ?>
                            </div>
                            
                            <form method="POST" class="pt-3 border-top" style="border-color: var(--glass-border) !important;">
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
