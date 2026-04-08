<?php
// student/submit_complaint.php
session_start();
require_once '../config/database.php';
require_once '../config/permissions.php';
require_once '../config/notifications.php';
require_once '../lib/DebugLogger.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    die("Access Denied: Only students can submit complaints.");
}

$error = '';
$success = '';

// Fetch only allowed receivers for Students (CR or Teacher)
$stmt = $pdo->query("SELECT id, full_name, role FROM users WHERE role IN ('cr', 'teacher')");
$receivers = $stmt->fetchAll();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // #region agent log
    DebugLogger::log('baseline', 'H3', 'student/submit_complaint.php:POST', 'complaint_submit_attempt', array('hasAttachment' => isset($_FILES['attachment']) ? 1 : 0, 'sessionRole' => isset($_SESSION['role']) ? $_SESSION['role'] : 'none'));
    // #endregion
    $category = trim($_POST['category']);
    $priority = $_POST['priority'];
    $receiver_id = $_POST['receiver_id'];
    $message = trim($_POST['message']);
    $file_path = null;

    // Strict Architectural Validation using the AccessManager
    $stmtCheck = $pdo->prepare("SELECT role FROM users WHERE id = ?");
    $stmtCheck->execute([$receiver_id]);
    $receiverFound = $stmtCheck->fetch();

    if (!$receiverFound || !AccessManager::canCommunicate($_SESSION['role'], $receiverFound['role'])) {
        $error = "UNAUTHORIZED ROUTING: You cannot send complaints to this role.";
    } elseif (empty($category) || empty($priority) || empty($message)) {
        $error = "All mandatory fields must be filled.";
    } else {
        $receiver_role = $receiverFound['role'];

        // Handle File Upload
        if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] == 0) {
            $allowed = ['jpg', 'jpeg', 'png', 'pdf'];
            $filename = $_FILES['attachment']['name'];
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            $size = $_FILES['attachment']['size'];
            $mime = function_exists('mime_content_type') ? @mime_content_type($_FILES['attachment']['tmp_name']) : 'unknown';
            // #region agent log
            DebugLogger::log('baseline', 'H4', 'student/submit_complaint.php:file-upload', 'complaint_attachment_metadata', array('ext' => $ext, 'size' => (int)$size, 'mime' => (string)$mime));
            // #endregion

            if (!in_array($ext, $allowed)) {
                $error = "Invalid file type. Only JPG, PNG, and PDF allowed.";
            } elseif ($size > 5 * 1024 * 1024) {
                $error = "File size must be under 5MB.";
            } else {
                $uploadDir = '../assets/uploads/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
                $newFilename = uniqid('comp_') . '.' . $ext;
                $dest = $uploadDir . $newFilename;
                if (move_uploaded_file($_FILES['attachment']['tmp_name'], $dest)) {
                    $file_path = 'assets/uploads/' . $newFilename;
                } else {
                    $error = "Upload failed.";
                }
            }
        }

        if (empty($error)) {
            try {
                $pdo->beginTransaction();

                $stmt = $pdo->prepare("INSERT INTO complaints (student_id, assigned_to, current_handler_role, category, priority, message, file_path, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'Pending')");
                $stmt->execute([$_SESSION['user_id'], $receiver_id, $receiver_role, $category, $priority, $message, $file_path]);
                $complaintId = $pdo->lastInsertId();

                $stmtHist = $pdo->prepare("INSERT INTO complaint_history (complaint_id, action_by, action, comments) VALUES (?, ?, 'Submitted', 'Initial registration via portal.')");
                $stmtHist->execute([$complaintId, $_SESSION['user_id']]);

                // Integrated Notification System
                NotificationManager::send($pdo, $receiver_id, "New complaint #$complaintId from " . $_SESSION['full_name'], "representative/forward.php");

                $pdo->commit();
                $success = "Complaint submitted and receiver notified!";
            } catch (Exception $e) {
                $pdo->rollBack();
                $error = "Database Error: " . $e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Submit Complaint - DFCMS</title>
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
            margin-top: 30px; 
            padding: 40px; 
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
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .text-muted-custom {
            color: var(--text-dim) !important;
        }

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

        .text-accent { color: var(--primary); }
        
        .btn-submit { 
            background: linear-gradient(135deg, var(--primary) 0%, #059669 100%);
            border: none; 
            padding: 14px; 
            font-weight: 700; 
            color: #fff; 
            letter-spacing: 1px; 
            border-radius: 12px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            text-transform: uppercase;
            font-size: 0.9rem;
        }
        
        .btn-submit:hover { 
            transform: translateY(-3px); 
            box-shadow: 0 10px 20px -5px var(--primary-glow);
            color: #fff;
        }

        .btn-submit:active {
            transform: translateY(-1px);
        }

        .info-box {
            background: rgba(16, 185, 129, 0.1);
            border-left: 4px solid var(--primary);
            padding: 12px;
            border-radius: 6px;
            font-size: 0.8rem;
            color: var(--text-dim);
            margin-top: 1rem;
        }

        .section-header {
            border-bottom: 2px solid rgba(255,255,255,0.05);
            padding-bottom: 10px;
            margin-bottom: 25px;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark navbar-custom px-4">
        <a class="navbar-brand text-accent fw-bold" href="../dashboard.php">DFCMS</a>
        <div class="ms-auto"><a href="../dashboard.php" class="btn btn-outline-light btn-sm me-3"><i class="fas fa-arrow-left"></i> Back to Dashboard</a></div>
    </nav>

    <div class="container my-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card card-custom">
                    <div class="section-header">
                        <h2 class="text-white fw-bold mb-1">Submit Complaint</h2>
                        <p class="text-muted-custom small">Your complaint will be securely routed according to strict University transparency protocols.</p>
                    </div>
                    
                    <?php if ($error): ?><div class="alert alert-danger py-2 border-0 bg-danger bg-opacity-25 text-danger"><?php echo $error; ?></div><?php endif; ?>
                    <?php if ($success): ?><div class="alert alert-success py-2 border-0 bg-success bg-opacity-25 text-success"><?php echo $success; ?></div><?php endif; ?>

                    <form method="POST" enctype="multipart/form-data">
                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <label class="form-label"><i class="fas fa-list text-accent"></i> Complaint Category</label>
                                <select name="category" class="form-select" required>
                                    <option value="">Select Category...</option>
                                    <option value="Academic">Academic</option>
                                    <option value="Facilities">Facilities/Infrastructure</option>
                                    <option value="Administration">Administration</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-4">
                                <label class="form-label"><i class="fas fa-flag text-accent"></i> Priority Level</label>
                                <select name="priority" class="form-select" required>
                                    <option value="Low">Low Priority</option>
                                    <option value="Medium">Medium Priority</option>
                                    <option value="High">High Priority</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label"><i class="fas fa-user-tie text-accent"></i> Route Complaint To:</label>
                            <select name="receiver_id" class="form-select" required>
                                <option value="">Select Target Handler (CR or Teacher)...</option>
                                <?php foreach ($receivers as $r): ?>
                                    <option value="<?php echo $r['id']; ?>">
                                         <?php echo htmlspecialchars($r['full_name']); ?> (<?php echo strtoupper($r['role']); ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="info-box">
                                <i class="fas fa-info-circle me-1"></i> Students are strictly limited to Class Representatives and Teaching Staff.
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label"><i class="fas fa-pen-nib text-accent"></i> Description of the Issue</label>
                            <textarea name="message" class="form-control" rows="5" placeholder="Please provide specific details..." required></textarea>
                        </div>

                        <div class="mb-4">
                            <label class="form-label"><i class="fas fa-paperclip text-accent"></i> Evidence Attachment <span class="text-muted-custom ms-1">(Optional)</span></label>
                            <input type="file" name="attachment" class="form-control" accept=".jpg,.jpeg,.png,.pdf">
                            <small class="text-muted-custom mt-1 d-block">Accepted: JPG, PNG, PDF (Max 5MB)</small>
                        </div>

                        <button type="submit" class="btn btn-submit w-100 mt-2"><i class="fas fa-paper-plane me-2"></i> Initialize Workflow</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
