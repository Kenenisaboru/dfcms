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
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Submit a new complaint or feedback - DFCMS">
    <title>Submit Complaint | DFCMS</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <link href="../assets/css/dfcms-modern.css" rel="stylesheet">
    
    <style>
        .complaint-page {
            min-height: 100vh;
            padding: var(--space-8);
            background: var(--bg-primary);
        }
        
        .complaint-container {
            max-width: 720px;
            margin: 0 auto;
        }
        
        .complaint-header {
            text-align: center;
            margin-bottom: var(--space-8);
        }
        
        .complaint-header h1 {
            font-size: 2rem;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: var(--space-2);
        }
        
        .complaint-header p {
            font-size: 1rem;
            color: var(--text-secondary);
        }
        
        .complaint-card {
            padding: var(--space-8);
        }
        
        .form-section-title {
            font-size: 0.875rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--text-tertiary);
            margin-bottom: var(--space-4);
            padding-bottom: var(--space-3);
            border-bottom: 1px solid var(--glass-border);
        }
        
        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: var(--space-5);
            margin-bottom: var(--space-6);
        }
        
        @media (max-width: 640px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
        }
        
        .receiver-select {
            position: relative;
        }
        
        .receiver-hint {
            display: flex;
            align-items: center;
            gap: var(--space-2);
            margin-top: var(--space-2);
            padding: var(--space-3);
            background: rgba(59, 130, 246, 0.05);
            border: 1px solid rgba(59, 130, 246, 0.2);
            border-radius: var(--radius-lg);
            font-size: 0.8125rem;
            color: var(--info);
        }
        
        .receiver-hint i {
            font-size: 1rem;
        }
        
        .file-upload-area {
            border: 2px dashed var(--glass-border);
            border-radius: var(--radius-xl);
            padding: var(--space-8);
            text-align: center;
            transition: all var(--transition-fast);
            cursor: pointer;
        }
        
        .file-upload-area:hover {
            border-color: var(--primary-500);
            background: rgba(16, 185, 129, 0.03);
        }
        
        .file-upload-area.drag-over {
            border-color: var(--primary-500);
            background: rgba(16, 185, 129, 0.1);
        }
        
        .file-upload-icon {
            width: 64px;
            height: 64px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--glass-highlight);
            border-radius: var(--radius-2xl);
            margin: 0 auto var(--space-4);
            color: var(--primary-400);
            font-size: 1.5rem;
        }
        
        .file-upload-text {
            font-size: 0.9375rem;
            color: var(--text-secondary);
            margin-bottom: var(--space-2);
        }
        
        .file-upload-hint {
            font-size: 0.8125rem;
            color: var(--text-tertiary);
        }
        
        .file-input {
            position: absolute;
            opacity: 0;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            cursor: pointer;
        }
        
        .file-preview {
            display: flex;
            align-items: center;
            gap: var(--space-3);
            padding: var(--space-3) var(--space-4);
            background: rgba(16, 185, 129, 0.05);
            border: 1px solid rgba(16, 185, 129, 0.2);
            border-radius: var(--radius-lg);
            margin-top: var(--space-4);
        }
        
        .file-preview i {
            color: var(--primary-400);
        }
        
        .file-preview span {
            flex: 1;
            font-size: 0.875rem;
            color: var(--text-secondary);
        }
        
        .file-preview button {
            background: none;
            border: none;
            color: var(--text-tertiary);
            cursor: pointer;
            padding: var(--space-1);
            border-radius: var(--radius-md);
            transition: all var(--transition-fast);
        }
        
        .file-preview button:hover {
            background: var(--glass-highlight);
            color: var(--danger);
        }
        
        .submit-section {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: var(--space-4);
            margin-top: var(--space-8);
            padding-top: var(--space-6);
            border-top: 1px solid var(--glass-border);
        }
        
        .submit-hint {
            font-size: 0.875rem;
            color: var(--text-tertiary);
        }
        
        .submit-hint i {
            color: var(--primary-400);
            margin-right: var(--space-2);
        }
        
        @media (max-width: 640px) {
            .submit-section {
                flex-direction: column;
                align-items: stretch;
            }
        }
        
        .back-nav {
            display: inline-flex;
            align-items: center;
            gap: var(--space-2);
            margin-bottom: var(--space-6);
            font-size: 0.875rem;
            color: var(--text-secondary);
            text-decoration: none;
            transition: color var(--transition-fast);
        }
        
        .back-nav:hover {
            color: var(--text-primary);
        }
    </style>
</head>
<body class="complaint-page">
    <div class="complaint-container">
        <a href="../dashboard.php" class="back-nav">
            <i class="fas fa-arrow-left"></i>
            Back to Dashboard
        </a>
        
        <div class="complaint-header animate-fade-in-down">
            <h1>Submit Complaint</h1>
            <p>Your complaint will be securely routed according to University transparency protocols</p>
        </div>
        
        <?php if ($error): ?>
            <div class="alert alert-danger mb-6" role="alert">
                <div class="alert-icon"><i class="fas fa-exclamation-circle"></i></div>
                <div class="alert-content">
                    <div class="alert-title">Submission Failed</div>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success mb-6" role="alert">
                <div class="alert-icon"><i class="fas fa-check-circle"></i></div>
                <div class="alert-content">
                    <div class="alert-title">Success!</div>
                    <?php echo htmlspecialchars($success); ?>
                </div>
            </div>
        <?php endif; ?>
        
        <div class="card glass-card complaint-card animate-fade-in-up">
            <form method="POST" action="" id="complaintForm" enctype="multipart/form-data" novalidate>
                <?php echo CSRF::input(); ?>
                
                <h3 class="form-section-title">Complaint Details</h3>
                
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label" for="category">
                            <i class="fas fa-list text-accent me-2"></i>Category
                        </label>
                        <select name="category" id="category" class="form-select" required>
                            <option value="">Select category...</option>
                            <option value="Academic">Academic</option>
                            <option value="Facilities">Facilities/Infrastructure</option>
                            <option value="Administration">Administration</option>
                            <option value="Other">Other</option>
                        </select>
                        <div class="form-feedback" id="category-feedback"></div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="priority">
                            <i class="fas fa-flag text-accent me-2"></i>Priority Level
                        </label>
                        <select name="priority" id="priority" class="form-select" required>
                            <option value="Low">Low Priority</option>
                            <option value="Medium" selected>Medium Priority</option>
                            <option value="High">High Priority</option>
                        </select>
                        <div class="form-feedback" id="priority-feedback"></div>
                    </div>
                </div>
                
                <div class="form-group receiver-select">
                    <label class="form-label" for="receiver_id">
                        <i class="fas fa-user-tie text-accent me-2"></i>Route Complaint To
                    </label>
                    <select name="receiver_id" id="receiver_id" class="form-select" required>
                        <option value="">Select handler...</option>
                        <?php foreach ($receivers as $r): ?>
                            <option value="<?php echo $r['id']; ?>">
                                <?php echo htmlspecialchars($r['full_name']); ?> (<?php echo ucfirst($r['role']); ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="receiver-hint">
                        <i class="fas fa-info-circle"></i>
                        <span>Students can only send complaints to Class Representatives and Teaching Staff</span>
                    </div>
                    <div class="form-feedback" id="receiver_id-feedback"></div>
                </div>
                
                <h3 class="form-section-title" style="margin-top: var(--space-8);">Description</h3>
                
                <div class="form-group">
                    <label class="form-label" for="message">
                        <i class="fas fa-pen-nib text-accent me-2"></i>Issue Description
                    </label>
                    <textarea 
                        name="message" 
                        id="message"
                        class="form-textarea" 
                        rows="6" 
                        placeholder="Please provide specific details about your complaint, including any relevant dates, locations, and people involved..."
                        required
                    ></textarea>
                    <div class="form-feedback" id="message-feedback"></div>
                </div>
                
                <h3 class="form-section-title" style="margin-top: var(--space-8);">Attachments</h3>
                
                <div class="form-group">
                    <div class="file-upload-area" id="fileUploadArea">
                        <input type="file" name="attachment" id="attachment" class="file-input" accept=".jpg,.jpeg,.png,.pdf">
                        <div class="file-upload-icon">
                            <i class="fas fa-cloud-upload-alt"></i>
                        </div>
                        <p class="file-upload-text">Drop your file here or click to browse</p>
                        <p class="file-upload-hint">JPG, PNG, or PDF up to 5MB</p>
                    </div>
                    <div id="filePreview" class="file-preview" style="display: none;">
                        <i class="fas fa-file"></i>
                        <span id="fileName">filename.jpg</span>
                        <button type="button" id="removeFile" aria-label="Remove file">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
                
                <div class="submit-section">
                    <p class="submit-hint">
                        <i class="fas fa-shield-alt"></i>
                        Your complaint will be encrypted and securely transmitted
                    </p>
                    <button type="submit" class="btn btn-primary btn-lg" id="submitBtn">
                        <i class="fas fa-paper-plane"></i>
                        Submit Complaint
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <script src="../assets/js/dfcms-ui.js"></script>
    
    <script>
        (function() {
            const form = document.getElementById('complaintForm');
            const fileUploadArea = document.getElementById('fileUploadArea');
            const fileInput = document.getElementById('attachment');
            const filePreview = document.getElementById('filePreview');
            const fileName = document.getElementById('fileName');
            const removeFile = document.getElementById('removeFile');
            
            // File upload handling
            fileUploadArea.addEventListener('click', () => fileInput.click());
            
            fileInput.addEventListener('change', function() {
                if (this.files && this.files[0]) {
                    showFilePreview(this.files[0]);
                }
            });
            
            // Drag and drop
            fileUploadArea.addEventListener('dragover', (e) => {
                e.preventDefault();
                fileUploadArea.classList.add('drag-over');
            });
            
            fileUploadArea.addEventListener('dragleave', () => {
                fileUploadArea.classList.remove('drag-over');
            });
            
            fileUploadArea.addEventListener('drop', (e) => {
                e.preventDefault();
                fileUploadArea.classList.remove('drag-over');
                
                if (e.dataTransfer.files && e.dataTransfer.files[0]) {
                    fileInput.files = e.dataTransfer.files;
                    showFilePreview(e.dataTransfer.files[0]);
                }
            });
            
            function showFilePreview(file) {
                fileName.textContent = file.name;
                filePreview.style.display = 'flex';
                fileUploadArea.style.display = 'none';
            }
            
            removeFile.addEventListener('click', () => {
                fileInput.value = '';
                filePreview.style.display = 'none';
                fileUploadArea.style.display = 'block';
            });
            
            // Form validation
            const validator = new DFCMS.FormValidator(form, {
                validateOnBlur: true,
                showInlineErrors: true
            });
            
            validator.rules({
                category: {
                    required: true,
                    requiredMessage: 'Please select a category'
                },
                receiver_id: {
                    required: true,
                    requiredMessage: 'Please select who should receive this complaint'
                },
                message: {
                    required: true,
                    minLength: 20,
                    requiredMessage: 'Please describe the issue',
                    minLengthMessage: 'Please provide at least 20 characters of detail'
                }
            });
            
            // Form submission
            form.addEventListener('submit', function(e) {
                if (!validator.validate()) {
                    e.preventDefault();
                    DFCMS.toast.error('Please correct the errors before submitting.');
                    return;
                }
                
                DFCMS.LoadingManager.button(document.getElementById('submitBtn'), 'Submitting...');
            });
        })();
    </script>
</body>
</html>
