require_once '../config/config.php';

// Check if user is logged in as student
check_login('student');

$error = '';
$success = '';

// Fetch only allowed receivers for Students (CR or Teacher)
$stmt = $pdo->query("SELECT id, full_name, role FROM users WHERE role IN ('cr', 'teacher')");
$receivers = $stmt->fetchAll();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate CSRF
    CSRF::validate($_POST['csrf_token']);
    
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
            $allowedMime = array(
                'jpg' => array('image/jpeg'),
                'jpeg' => array('image/jpeg'),
                'png' => array('image/png'),
                'pdf' => array('application/pdf')
            );
            // #region agent log
            DebugLogger::log('baseline', 'H4', 'student/submit_complaint.php:file-upload', 'complaint_attachment_metadata', array('ext' => $ext, 'size' => (int)$size, 'mime' => (string)$mime));
            // #endregion

            if (!in_array($ext, $allowed)) {
                $error = "Invalid file type. Only JPG, PNG, and PDF allowed.";
            } elseif (!in_array($mime, $allowedMime[$ext], true)) {
                $error = "Invalid file content detected.";
            } elseif ($size > 5 * 1024 * 1024) {
                $error = "File size must be under 5MB.";
            } else {
                $uploadDir = dirname(__DIR__) . '/storage/uploads/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0750, true);
                }
                $newFilename = uniqid('comp_') . '.' . $ext;
                $dest = $uploadDir . $newFilename;
                if (move_uploaded_file($_FILES['attachment']['tmp_name'], $dest)) {
                    $file_path = 'storage/uploads/' . $newFilename;
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
                NotificationManager::send($pdo, $receiver_id, "New complaint #$complaintId from " . $_SESSION['full_name'], "representative/forward.php", 'complaint_assigned', 'New Complaint Received');

                $pdo->commit();
                $success = "Complaint submitted and receiver notified!";
            } catch (Exception $e) {
                $pdo->rollBack();
                error_log('Complaint submit failed: ' . $e->getMessage());
                $error = "Unable to submit complaint right now. Please try again.";
            }
        }
    }
}
?>
<?php
$page_title = "Submit Complaint";
include '../components/head.php';
?>
<body>
    <?php include '../components/navbar.php'; ?>

    <div class="container my-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card card-custom bg-glass p-4 border-0 shadow-lg rounded-4">
                    <div class="section-header">
                        <h2 class="text-white fw-bold mb-1">Submit Complaint</h2>
                        <p class="text-muted small">Your complaint will be securely routed according to strict University transparency protocols.</p>
                    </div>
                    
                    <?php if ($error): ?><div class="alert alert-danger py-2 border-0 bg-danger bg-opacity-25 text-danger"><?php echo $error; ?></div><?php endif; ?>
                    <?php if ($success): ?><div class="alert alert-success py-2 border-0 bg-success bg-opacity-25 text-success"><?php echo $success; ?></div><?php endif; ?>

                    <form method="POST" enctype="multipart/form-data">
                        <?php echo CSRF::input(); ?>
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
