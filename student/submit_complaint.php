<?php
// student/submit_complaint.php - Pro Level Complaint Submission v4.0
require_once '../config/config.php';

// Check if user is logged in as student
check_login('student');

$error = '';
$success = '';

// Fetch only allowed receivers for Students (CR or Teacher)
$stmt = $pdo->query("SELECT id, full_name, role FROM users WHERE role IN ('cr', 'teacher')");
$receivers = $stmt->fetchAll();

// Fetch recent complaints for "My Recent Complaints" section
$stmtRecent = $pdo->prepare("SELECT c.*, u.full_name as handler_name 
                              FROM complaints c 
                              LEFT JOIN users u ON c.assigned_to = u.id 
                              WHERE c.student_id = ? 
                              ORDER BY c.created_at DESC LIMIT 8");
$stmtRecent->execute([$_SESSION['user_id']]);
$recentComplaints = $stmtRecent->fetchAll();

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
                $success = "Complaint #$complaintId submitted successfully! Your handler has been notified.";
            } catch (Exception $e) {
                $pdo->rollBack();
                error_log('Complaint submit failed: ' . $e->getMessage());
                $error = "Unable to submit complaint right now. Please try again.";
            }
        }
    }
}

$page_title = "Submit Complaint";
$base_path = '../';
include '../components/head.php';
?>

<body>
<div class="admin-layout">
    <!-- Sidebar -->
    <?php include '../components/sidebar.php'; ?>

    <div class="main-container">
        <!-- Top Navbar -->
        <?php include '../components/navbar.php'; ?>

        <!-- Page Content -->
        <main class="p-4 p-lg-5" style="max-width: 1600px;">
            
            <!-- Welcome Banner -->
            <div class="card border-0 mb-5 overflow-hidden position-relative" id="welcome-banner"
                 style="background: linear-gradient(135deg, var(--premium-primary) 0%, #7551ff 60%, #b983ff 100%); border: none !important;">
                <div class="card-body p-4 p-lg-5 position-relative" style="z-index: 1;">
                    <div class="row align-items-center">
                        <div class="col-lg-8">
                            <p class="text-white mb-1 fw-600 small" style="opacity: 0.8;">
                                <i class="bi bi-lightning-charge-fill me-1"></i> Complaint Hub
                            </p>
                            <h2 class="text-white fw-800 mb-2" style="font-size: 1.75rem;">
                                Submit New Complaint
                            </h2>
                            <p class="text-white mb-0" style="opacity: 0.8; max-width: 520px; font-size: 0.9375rem;">
                                Your voice matters. Submit your complaint securely — it will be encrypted and routed according to University transparency protocols.
                            </p>
                        </div>
                        <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
                            <div class="d-inline-flex align-items-center gap-2 px-3 py-2 rounded-pill" 
                                 style="background: rgba(255,255,255,0.15); backdrop-filter: blur(10px);">
                                <i class="bi bi-shield-lock-fill text-white"></i>
                                <span class="text-white small fw-600">End-to-End Encrypted</span>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Decorative elements -->
                <div class="position-absolute" style="width: 200px; height: 200px; border-radius: 50%; background: rgba(255,255,255,0.04); top: -60px; right: -40px; pointer-events: none;"></div>
                <div class="position-absolute" style="width: 120px; height: 120px; border-radius: 50%; background: rgba(255,255,255,0.06); bottom: -30px; left: 20%; pointer-events: none;"></div>
                <i class="bi bi-chat-square-text-fill position-absolute text-white" 
                   style="font-size: 8rem; right: 2rem; bottom: -1rem; opacity: 0.06; pointer-events: none;"></i>
            </div>

            <!-- Alerts -->
            <?php if ($error): ?>
                <div class="alert d-flex align-items-start gap-3 mb-4 rounded-xl border-0 animate-fade-up" 
                     style="background: var(--premium-coral-soft); padding: 1rem 1.25rem;" role="alert">
                    <div class="d-flex align-items-center justify-content-center rounded-lg flex-shrink-0" 
                         style="width: 36px; height: 36px; background: rgba(238, 93, 80, 0.15); border-radius: var(--radius-sm);">
                        <i class="bi bi-exclamation-triangle-fill" style="color: var(--premium-coral);"></i>
                    </div>
                    <div>
                        <div class="fw-700 small" style="color: var(--premium-coral);">Submission Failed</div>
                        <div class="small" style="color: var(--premium-text-body);"><?php echo htmlspecialchars($error); ?></div>
                    </div>
                    <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close" style="font-size: 0.7rem;"></button>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert d-flex align-items-start gap-3 mb-4 rounded-xl border-0 animate-fade-up" 
                     style="background: var(--premium-teal-soft); padding: 1rem 1.25rem;" role="alert">
                    <div class="d-flex align-items-center justify-content-center rounded-lg flex-shrink-0" 
                         style="width: 36px; height: 36px; background: rgba(1, 181, 116, 0.15); border-radius: var(--radius-sm);">
                        <i class="bi bi-check-circle-fill" style="color: var(--premium-teal);"></i>
                    </div>
                    <div>
                        <div class="fw-700 small" style="color: var(--premium-teal);">Success!</div>
                        <div class="small" style="color: var(--premium-text-body);"><?php echo htmlspecialchars($success); ?></div>
                    </div>
                    <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close" style="font-size: 0.7rem;"></button>
                </div>
            <?php endif; ?>

            <div class="row g-4">
                <!-- ═══════════════════════════════════════
                     SUBMIT COMPLAINT FORM 
                     ═══════════════════════════════════════ -->
                <div class="col-12 col-xl-8">
                    <div class="card border-0" id="card-submit-complaint">
                        <div class="card-header d-flex align-items-center gap-3">
                            <div class="stat-icon-badge bg-primary-soft" style="width: 42px; height: 42px; margin: 0;">
                                <i class="bi bi-pencil-square" style="font-size: 1rem;"></i>
                            </div>
                            <div>
                                <h5 class="fw-700 mb-0" style="color: var(--premium-text-heading);">New Complaint / Feedback</h5>
                                <p class="text-muted-color mb-0 x-small">Fill in the details below to submit your complaint</p>
                            </div>
                        </div>
                        <div class="card-body p-4 p-lg-5">
                            <form method="POST" action="" id="complaintForm" enctype="multipart/form-data" novalidate>
                                <?php echo CSRF::input(); ?>
                                
                                <!-- Section: Classification -->
                                <div class="d-flex align-items-center gap-2 mb-4">
                                    <div style="width: 3px; height: 18px; background: var(--premium-primary); border-radius: 3px;"></div>
                                    <h6 class="fw-700 mb-0 text-heading" style="font-size: 0.8125rem; text-transform: uppercase; letter-spacing: 0.08em;">Classification</h6>
                                </div>

                                <div class="row g-4 mb-4">
                                    <!-- Category (Floating Label) -->
                                    <div class="col-12 col-md-6">
                                        <div class="form-floating" id="field-category">
                                            <select name="category" id="category" class="form-select" required style="border-radius: var(--radius-md); border-color: var(--premium-border); height: 58px;">
                                                <option value="" selected disabled>Select...</option>
                                                <option value="Academic">Academic</option>
                                                <option value="Facilities">Facilities / Infrastructure</option>
                                                <option value="Administration">Administration</option>
                                                <option value="Other">Other</option>
                                            </select>
                                            <label for="category"><i class="bi bi-tag-fill me-2" style="color: var(--premium-primary);"></i>Category</label>
                                            <div class="invalid-feedback" id="category-feedback">Please select a category</div>
                                        </div>
                                    </div>

                                    <!-- Route To (Floating Label) -->
                                    <div class="col-12 col-md-6">
                                        <div class="form-floating" id="field-receiver">
                                            <select name="receiver_id" id="receiver_id" class="form-select" required style="border-radius: var(--radius-md); border-color: var(--premium-border); height: 58px;">
                                                <option value="" selected disabled>Select handler...</option>
                                                <?php foreach ($receivers as $r): ?>
                                                    <option value="<?php echo $r['id']; ?>">
                                                        <?php echo htmlspecialchars($r['full_name']); ?> (<?php echo ucfirst($r['role']); ?>)
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <label for="receiver_id"><i class="bi bi-person-fill me-2" style="color: var(--premium-primary);"></i>Route To</label>
                                            <div class="invalid-feedback" id="receiver-feedback">Please select a handler</div>
                                        </div>
                                        <div class="d-flex align-items-center gap-2 mt-2 px-1">
                                            <i class="bi bi-info-circle-fill" style="color: var(--premium-info); font-size: 0.75rem;"></i>
                                            <span class="x-small" style="color: var(--premium-text-muted);">Students can route to Class Reps or Teaching Staff</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Priority Pills -->
                                <div class="d-flex align-items-center gap-2 mb-3">
                                    <div style="width: 3px; height: 18px; background: var(--premium-amber); border-radius: 3px;"></div>
                                    <h6 class="fw-700 mb-0 text-heading" style="font-size: 0.8125rem; text-transform: uppercase; letter-spacing: 0.08em;">Priority Level</h6>
                                </div>
                                <div class="d-flex flex-wrap gap-3 mb-4" id="priority-pills">
                                    <label class="priority-pill" id="pill-low">
                                        <input type="radio" name="priority" value="Low" class="d-none">
                                        <div class="pill-content">
                                            <i class="bi bi-arrow-down-circle"></i>
                                            <span>Low</span>
                                        </div>
                                    </label>
                                    <label class="priority-pill active" id="pill-medium">
                                        <input type="radio" name="priority" value="Medium" class="d-none" checked>
                                        <div class="pill-content">
                                            <i class="bi bi-dash-circle"></i>
                                            <span>Medium</span>
                                        </div>
                                    </label>
                                    <label class="priority-pill" id="pill-high">
                                        <input type="radio" name="priority" value="High" class="d-none">
                                        <div class="pill-content">
                                            <i class="bi bi-exclamation-circle"></i>
                                            <span>High</span>
                                        </div>
                                    </label>
                                </div>

                                <!-- Section: Description -->
                                <div class="d-flex align-items-center gap-2 mb-4">
                                    <div style="width: 3px; height: 18px; background: var(--premium-teal); border-radius: 3px;"></div>
                                    <h6 class="fw-700 mb-0 text-heading" style="font-size: 0.8125rem; text-transform: uppercase; letter-spacing: 0.08em;">Description</h6>
                                </div>

                                <!-- Subject (Floating Label) -->
                                <div class="form-floating mb-4" id="field-subject">
                                    <input type="text" class="form-control" id="subject" name="subject" placeholder="Brief subject line" 
                                           style="border-radius: var(--radius-md); border-color: var(--premium-border);">
                                    <label for="subject"><i class="bi bi-type-h1 me-2" style="color: var(--premium-primary);"></i>Subject</label>
                                    <div class="invalid-feedback" id="subject-feedback">Please enter a subject</div>
                                </div>

                                <!-- Message (Floating Label) -->
                                <div class="form-floating mb-4" id="field-message">
                                    <textarea class="form-control" id="message" name="message" placeholder="Describe your issue..." 
                                              style="height: 160px; border-radius: var(--radius-md); border-color: var(--premium-border); resize: none;" 
                                              required></textarea>
                                    <label for="message"><i class="bi bi-pencil me-2" style="color: var(--premium-primary);"></i>Describe your issue in detail</label>
                                    <div class="invalid-feedback" id="message-feedback">Please provide at least 20 characters</div>
                                    <div class="d-flex justify-content-between mt-2 px-1">
                                        <span class="x-small" style="color: var(--premium-text-muted);">Include dates, locations, and people involved</span>
                                        <span class="x-small fw-600" id="char-counter" style="color: var(--premium-text-muted);">0 / 20 min</span>
                                    </div>
                                </div>

                                <!-- Section: Attachments -->
                                <div class="d-flex align-items-center gap-2 mb-4">
                                    <div style="width: 3px; height: 18px; background: var(--premium-info); border-radius: 3px;"></div>
                                    <h6 class="fw-700 mb-0 text-heading" style="font-size: 0.8125rem; text-transform: uppercase; letter-spacing: 0.08em;">Attachment <span class="fw-500 text-muted-color">(Optional)</span></h6>
                                </div>

                                <!-- Drag & Drop Upload -->
                                <div class="file-drop-zone" id="fileDropZone">
                                    <input type="file" name="attachment" id="attachment" accept=".jpg,.jpeg,.png,.pdf" class="d-none">
                                    <div class="drop-zone-content">
                                        <div class="drop-icon">
                                            <i class="bi bi-cloud-arrow-up-fill"></i>
                                        </div>
                                        <p class="drop-title">Drop your file here or <span class="drop-browse">browse</span></p>
                                        <p class="drop-hint">JPG, PNG, or PDF — Max 5MB</p>
                                    </div>
                                </div>
                                <div class="file-preview-bar" id="filePreviewBar" style="display: none;">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="file-type-icon" id="fileTypeIcon">
                                            <i class="bi bi-file-earmark-image"></i>
                                        </div>
                                        <div>
                                            <div class="fw-600 small" style="color: var(--premium-text-heading);" id="previewFileName">file.jpg</div>
                                            <div class="x-small" style="color: var(--premium-text-muted);" id="previewFileSize">0 KB</div>
                                        </div>
                                    </div>
                                    <button type="button" class="btn-remove-file" id="removeFileBtn" aria-label="Remove file">
                                        <i class="bi bi-x-lg"></i>
                                    </button>
                                </div>

                                <!-- Submit Footer -->
                                <div class="d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-3 mt-5 pt-4" 
                                     style="border-top: 1px solid var(--premium-border-light);">
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="bi bi-shield-lock-fill" style="color: var(--premium-teal);"></i>
                                        <span class="small" style="color: var(--premium-text-muted);">Encrypted & secure submission</span>
                                    </div>
                                    <button type="submit" class="btn btn-primary btn-lg rounded-pill px-5 py-3 fw-700" id="submitBtn">
                                        <i class="bi bi-send-fill me-2"></i> Submit Complaint
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- ═══════════════════════════════════════
                     RIGHT SIDEBAR - Tips & Stats
                     ═══════════════════════════════════════ -->
                <div class="col-12 col-xl-4">
                    <!-- Tips Card -->
                    <div class="card border-0 mb-4" id="card-tips">
                        <div class="card-header">
                            <h6 class="fw-700 mb-0" style="color: var(--premium-text-heading);">
                                <i class="bi bi-lightbulb-fill me-2" style="color: var(--premium-amber);"></i>Pro Tips
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="d-flex flex-column gap-3">
                                <div class="d-flex gap-3 align-items-start">
                                    <div class="d-flex align-items-center justify-content-center flex-shrink-0 rounded-lg" 
                                         style="width: 32px; height: 32px; background: var(--premium-teal-soft); border-radius: var(--radius-sm);">
                                        <i class="bi bi-check2" style="color: var(--premium-teal); font-size: 0.875rem;"></i>
                                    </div>
                                    <p class="mb-0 small" style="color: var(--premium-text-body);">Be specific — include dates, times, and locations for faster resolution.</p>
                                </div>
                                <div class="d-flex gap-3 align-items-start">
                                    <div class="d-flex align-items-center justify-content-center flex-shrink-0 rounded-lg" 
                                         style="width: 32px; height: 32px; background: var(--premium-primary-soft); border-radius: var(--radius-sm);">
                                        <i class="bi bi-check2" style="color: var(--premium-primary); font-size: 0.875rem;"></i>
                                    </div>
                                    <p class="mb-0 small" style="color: var(--premium-text-body);">Attach evidence (photos, PDFs) if available — it strengthens your case.</p>
                                </div>
                                <div class="d-flex gap-3 align-items-start">
                                    <div class="d-flex align-items-center justify-content-center flex-shrink-0 rounded-lg" 
                                         style="width: 32px; height: 32px; background: var(--premium-amber-soft); border-radius: var(--radius-sm);">
                                        <i class="bi bi-check2" style="color: var(--premium-amber); font-size: 0.875rem;"></i>
                                    </div>
                                    <p class="mb-0 small" style="color: var(--premium-text-body);">Use "High" priority only for urgent, time-sensitive issues.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Submission Progress -->
                    <div class="card border-0 mb-4" id="card-form-progress">
                        <div class="card-header">
                            <h6 class="fw-700 mb-0" style="color: var(--premium-text-heading);">
                                <i class="bi bi-bar-chart-fill me-2" style="color: var(--premium-primary);"></i>Form Progress
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="small fw-600" style="color: var(--premium-text-body);">Completion</span>
                                <span class="small fw-700" style="color: var(--premium-primary);" id="progress-pct">0%</span>
                            </div>
                            <div class="progress mb-4" style="height: 8px;">
                                <div class="progress-bar rounded-pill" role="progressbar" id="progress-bar"
                                     style="width: 0%; background: linear-gradient(90deg, var(--premium-primary), #7551ff); transition: width 0.5s var(--ease-smooth);"
                                     aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                            <div class="d-flex flex-column gap-2" id="checklist">
                                <div class="d-flex align-items-center gap-2 check-item" data-field="category">
                                    <i class="bi bi-circle check-icon" style="font-size: 0.75rem;"></i>
                                    <span class="small" style="color: var(--premium-text-secondary);">Category selected</span>
                                </div>
                                <div class="d-flex align-items-center gap-2 check-item" data-field="receiver_id">
                                    <i class="bi bi-circle check-icon" style="font-size: 0.75rem;"></i>
                                    <span class="small" style="color: var(--premium-text-secondary);">Handler selected</span>
                                </div>
                                <div class="d-flex align-items-center gap-2 check-item" data-field="message">
                                    <i class="bi bi-circle check-icon" style="font-size: 0.75rem;"></i>
                                    <span class="small" style="color: var(--premium-text-secondary);">Description filled</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Stats -->
                    <div class="card border-0" id="card-quick-stats" style="background: linear-gradient(135deg, var(--premium-navy-900) 0%, var(--premium-navy-800) 100%); border: none !important;">
                        <div class="card-body p-4">
                            <h6 class="fw-700 text-white mb-3"><i class="bi bi-graph-up-arrow me-2"></i>Your Stats</h6>
                            <div class="d-flex flex-column gap-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="small" style="color: var(--premium-navy-300);">Total Submitted</span>
                                    <span class="fw-700 text-white"><?php echo count($recentComplaints); ?></span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="small" style="color: var(--premium-navy-300);">Pending</span>
                                    <span class="fw-700" style="color: var(--premium-amber);">
                                        <?php echo count(array_filter($recentComplaints, fn($c) => $c['status'] === 'Pending')); ?>
                                    </span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="small" style="color: var(--premium-navy-300);">Resolved</span>
                                    <span class="fw-700" style="color: var(--premium-teal);">
                                        <?php echo count(array_filter($recentComplaints, fn($c) => $c['status'] === 'Resolved')); ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ═══════════════════════════════════════
                 MY RECENT COMPLAINTS TABLE
                 ═══════════════════════════════════════ -->
            <div class="card border-0 mt-5" id="card-recent-complaints">
                <div class="card-header d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="stat-icon-badge bg-teal-soft" style="width: 42px; height: 42px; margin: 0;">
                            <i class="bi bi-list-task" style="font-size: 1rem;"></i>
                        </div>
                        <div>
                            <h5 class="fw-700 mb-0" style="color: var(--premium-text-heading);">My Recent Complaints</h5>
                            <p class="text-muted-color mb-0 x-small"><?php echo count($recentComplaints); ?> total submissions</p>
                        </div>
                    </div>
                    <a href="tracker.php" class="btn btn-light btn-sm rounded-pill px-4 fw-600" id="btn-view-all">
                        View All <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($recentComplaints)): ?>
                        <div class="text-center py-5 px-4">
                            <div class="d-inline-flex align-items-center justify-content-center mb-3 rounded-xl" 
                                 style="width: 80px; height: 80px; background: var(--premium-bg);">
                                <i class="bi bi-journal-x fs-1" style="color: var(--premium-text-muted);"></i>
                            </div>
                            <p class="fw-600 mb-1" style="color: var(--premium-text-heading);">No complaints submitted yet</p>
                            <p class="text-muted-color small mb-3">Use the form above to submit your first complaint.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0" id="complaints-table">
                                <thead>
                                    <tr>
                                        <th class="ps-4">ID</th>
                                        <th>Category</th>
                                        <th>Handler</th>
                                        <th>Priority</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                        <th class="text-end pe-4">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recentComplaints as $c): ?>
                                        <tr>
                                            <td class="ps-4">
                                                <span class="fw-700" style="color: var(--premium-primary);">#<?php echo $c['id']; ?></span>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center gap-2">
                                                    <i class="bi bi-folder2-open" style="color: var(--premium-text-muted);"></i>
                                                    <span class="fw-500"><?php echo htmlspecialchars($c['category']); ?></span>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center gap-2">
                                                    <div class="d-flex align-items-center justify-content-center rounded-circle" 
                                                         style="width: 28px; height: 28px; background: var(--premium-bg); font-size: 0.6875rem; font-weight: 700; color: var(--premium-text-secondary);">
                                                        <?php echo $c['handler_name'] ? strtoupper(substr($c['handler_name'], 0, 1)) : '?'; ?>
                                                    </div>
                                                    <span class="small"><?php echo $c['handler_name'] ? htmlspecialchars($c['handler_name']) : '<em class="text-muted-color">Unassigned</em>'; ?></span>
                                                </div>
                                            </td>
                                            <td>
                                                <?php 
                                                $pClass = 'badge-soft-info';
                                                if (strtolower($c['priority']) == 'high') $pClass = 'badge-soft-danger';
                                                if (strtolower($c['priority']) == 'medium') $pClass = 'badge-soft-warning';
                                                ?>
                                                <span class="badge-soft <?php echo $pClass; ?>"><?php echo $c['priority']; ?></span>
                                            </td>
                                            <td>
                                                <?php
                                                $statusMap = [
                                                    'Pending' => 'badge-soft-warning',
                                                    'In-Progress' => 'badge-soft-info',
                                                    'Resolved' => 'badge-soft-success',
                                                    'Rejected' => 'badge-soft-danger',
                                                ];
                                                $sClass = $statusMap[$c['status']] ?? 'badge-soft-info';
                                                $statusIcon = [
                                                    'Pending' => 'bi-clock',
                                                    'In-Progress' => 'bi-arrow-repeat',
                                                    'Resolved' => 'bi-check-circle',
                                                    'Rejected' => 'bi-x-circle',
                                                ];
                                                $sIcon = $statusIcon[$c['status']] ?? 'bi-info-circle';
                                                ?>
                                                <span class="badge-soft <?php echo $sClass; ?>">
                                                    <i class="bi <?php echo $sIcon; ?>"></i> <?php echo $c['status']; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="small text-muted-color"><?php echo date('M j, Y', strtotime($c['created_at'])); ?></span>
                                            </td>
                                            <td class="text-end pe-4">
                                                <div class="d-flex justify-content-end gap-2">
                                                    <?php if ($c['assigned_to']): ?>
                                                        <a href="messages.php?receiver_id=<?php echo $c['assigned_to']; ?>" 
                                                           class="btn btn-sm btn-light rounded-pill px-3 fw-600" style="font-size: 0.75rem;">
                                                            <i class="bi bi-chat-dots me-1"></i>Chat
                                                        </a>
                                                    <?php endif; ?>
                                                    <button class="btn btn-sm rounded-pill px-3 fw-600" 
                                                            style="font-size: 0.75rem; background: var(--premium-primary-soft); color: var(--premium-primary); border: none;"
                                                            onclick="viewHistory(<?php echo $c['id']; ?>)">
                                                        <i class="bi bi-clock-history me-1"></i>History
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

        </main>
    </div>
</div>

<!-- History Modal -->
<div class="modal fade" id="historyModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 rounded-xl overflow-hidden" style="box-shadow: var(--premium-shadow-xl);">
            <div class="modal-header py-3 px-4" style="border-bottom: 1px solid var(--premium-border-light); background: var(--premium-bg);">
                <h5 class="modal-title fw-700" style="color: var(--premium-text-heading);">
                    <i class="bi bi-clock-history me-2" style="color: var(--premium-primary);"></i>Complaint #<span id="modalCompId"></span> History
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div id="historyTimeline" class="history-timeline">
                    <!-- History items injected here -->
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Premium Inline Styles -->
<style>
    /* Priority Pills */
    .priority-pill {
        cursor: pointer;
        flex: 1;
        min-width: 100px;
    }
    .priority-pill .pill-content {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        padding: 0.75rem 1.25rem;
        border-radius: var(--radius-md);
        border: 2px solid var(--premium-border);
        background: var(--premium-white);
        font-weight: 600;
        font-size: 0.875rem;
        color: var(--premium-text-secondary);
        transition: all 0.25s var(--ease-smooth);
    }
    .priority-pill:hover .pill-content {
        border-color: var(--premium-navy-200);
        background: var(--premium-bg);
    }
    .priority-pill.active .pill-content {
        border-color: var(--premium-primary);
        background: var(--premium-primary-soft);
        color: var(--premium-primary);
        box-shadow: 0 2px 8px rgba(67, 24, 255, 0.15);
    }
    .priority-pill[data-level="high"].active .pill-content {
        border-color: var(--premium-coral);
        background: var(--premium-coral-soft);
        color: var(--premium-coral);
        box-shadow: 0 2px 8px rgba(238, 93, 80, 0.15);
    }
    .priority-pill[data-level="low"].active .pill-content {
        border-color: var(--premium-teal);
        background: var(--premium-teal-soft);
        color: var(--premium-teal);
        box-shadow: 0 2px 8px rgba(1, 181, 116, 0.15);
    }
    .priority-pill i { font-size: 1.1rem; }

    /* File Drop Zone */
    .file-drop-zone {
        border: 2px dashed var(--premium-border);
        border-radius: var(--radius-xl);
        padding: 2.5rem 1.5rem;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s var(--ease-smooth);
        background: var(--premium-bg);
    }
    .file-drop-zone:hover {
        border-color: var(--premium-primary);
        background: var(--premium-primary-soft);
    }
    .file-drop-zone.drag-active {
        border-color: var(--premium-primary);
        background: var(--premium-primary-soft);
        transform: scale(1.01);
        box-shadow: 0 0 0 4px rgba(67, 24, 255, 0.08);
    }
    .drop-icon {
        width: 64px;
        height: 64px;
        margin: 0 auto 1rem;
        display: flex;
        align-items: center;
        justify-content: center;
        background: var(--premium-primary-soft);
        border-radius: var(--radius-lg);
        color: var(--premium-primary);
        font-size: 1.5rem;
        transition: all 0.3s var(--ease-smooth);
    }
    .file-drop-zone:hover .drop-icon { transform: translateY(-3px); }
    .drop-title { font-size: 0.9375rem; font-weight: 600; color: var(--premium-text-body); margin-bottom: 0.25rem; }
    .drop-browse { color: var(--premium-primary); text-decoration: underline; cursor: pointer; }
    .drop-hint { font-size: 0.8125rem; color: var(--premium-text-muted); margin: 0; }

    /* File Preview Bar */
    .file-preview-bar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0.75rem 1rem;
        background: var(--premium-teal-soft);
        border: 1px solid rgba(1, 181, 116, 0.15);
        border-radius: var(--radius-md);
        margin-top: 0.75rem;
    }
    .file-type-icon {
        width: 38px;
        height: 38px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(1, 181, 116, 0.12);
        border-radius: var(--radius-sm);
        color: var(--premium-teal);
        font-size: 1.1rem;
    }
    .btn-remove-file {
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: none;
        border: none;
        color: var(--premium-text-muted);
        cursor: pointer;
        border-radius: var(--radius-sm);
        transition: all 0.2s;
    }
    .btn-remove-file:hover { background: var(--premium-coral-soft); color: var(--premium-coral); }

    /* Validation Styles */
    .form-control.is-valid, .form-select.is-valid {
        border-color: var(--premium-teal) !important;
        box-shadow: 0 0 0 3px rgba(1, 181, 116, 0.1) !important;
        background-image: none;
    }
    .form-control.is-invalid, .form-select.is-invalid {
        border-color: var(--premium-coral) !important;
        box-shadow: 0 0 0 3px rgba(238, 93, 80, 0.1) !important;
        background-image: none;
    }
    .form-floating > .form-control:focus ~ label,
    .form-floating > .form-select ~ label {
        color: var(--premium-primary) !important;
    }

    /* Check items in progress card */
    .check-item.completed .check-icon {
        color: var(--premium-teal) !important;
    }
    .check-item.completed .check-icon::before {
        content: "\F26B"; /* bi-check-circle-fill */
    }
    .check-item.completed span {
        color: var(--premium-teal) !important;
        text-decoration: line-through;
    }

    /* History Timeline (Light Theme) */
    .history-timeline { position: relative; padding-left: 30px; }
    .history-timeline::before { content: ''; position: absolute; left: 10px; top: 0; bottom: 0; width: 2px; background: var(--premium-border); }
    .history-item { position: relative; margin-bottom: 25px; }
    .history-item::before { content: ''; position: absolute; left: -25px; top: 5px; width: 12px; height: 12px; border-radius: 50%; background: var(--premium-primary); border: 3px solid var(--premium-white); box-shadow: 0 0 0 2px var(--premium-border); z-index: 1; }
    .history-actor { font-weight: 700; color: var(--premium-primary); font-size: 0.875rem; }
    .history-date { font-size: 0.75rem; color: var(--premium-text-muted); margin-left: 10px; }
    .history-comment { margin-top: 5px; color: var(--premium-text-body); font-size: 0.875rem; background: var(--premium-bg); padding: 12px; border-radius: var(--radius-md); border: 1px solid var(--premium-border-light); }

    /* Form focus enhancement */
    .form-control:focus, .form-select:focus {
        border-color: var(--premium-primary) !important;
        box-shadow: 0 0 0 4px var(--premium-primary-soft) !important;
    }
</style>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
(function() {
    'use strict';

    // ═══════════════════════════════════════
    // PRIORITY PILL SELECTOR
    // ═══════════════════════════════════════
    const pills = document.querySelectorAll('.priority-pill');
    pills[0].setAttribute('data-level', 'low');
    pills[1].setAttribute('data-level', 'medium');
    pills[2].setAttribute('data-level', 'high');

    pills.forEach(pill => {
        pill.addEventListener('click', () => {
            pills.forEach(p => p.classList.remove('active'));
            pill.classList.add('active');
            pill.querySelector('input').checked = true;
        });
    });


    // ═══════════════════════════════════════
    // FILE UPLOAD (DRAG & DROP)
    // ═══════════════════════════════════════
    const dropZone = document.getElementById('fileDropZone');
    const fileInput = document.getElementById('attachment');
    const previewBar = document.getElementById('filePreviewBar');
    const previewName = document.getElementById('previewFileName');
    const previewSize = document.getElementById('previewFileSize');
    const removeBtn = document.getElementById('removeFileBtn');
    const fileTypeIcon = document.getElementById('fileTypeIcon');

    dropZone.addEventListener('click', () => fileInput.click());

    fileInput.addEventListener('change', function() {
        if (this.files && this.files[0]) showFilePreview(this.files[0]);
    });

    ['dragenter', 'dragover'].forEach(evt => {
        dropZone.addEventListener(evt, (e) => { e.preventDefault(); dropZone.classList.add('drag-active'); });
    });

    ['dragleave', 'drop'].forEach(evt => {
        dropZone.addEventListener(evt, (e) => { e.preventDefault(); dropZone.classList.remove('drag-active'); });
    });

    dropZone.addEventListener('drop', (e) => {
        if (e.dataTransfer.files && e.dataTransfer.files[0]) {
            fileInput.files = e.dataTransfer.files;
            showFilePreview(e.dataTransfer.files[0]);
        }
    });

    function showFilePreview(file) {
        previewName.textContent = file.name;
        previewSize.textContent = formatFileSize(file.size);
        
        // Set icon based on file type
        const ext = file.name.split('.').pop().toLowerCase();
        const iconMap = { 'pdf': 'bi-file-earmark-pdf-fill', 'jpg': 'bi-file-earmark-image', 'jpeg': 'bi-file-earmark-image', 'png': 'bi-file-earmark-image' };
        fileTypeIcon.innerHTML = `<i class="bi ${iconMap[ext] || 'bi-file-earmark'}"></i>`;
        
        previewBar.style.display = 'flex';
        dropZone.style.display = 'none';
    }

    function formatFileSize(bytes) {
        if (bytes < 1024) return bytes + ' B';
        if (bytes < 1048576) return (bytes / 1024).toFixed(1) + ' KB';
        return (bytes / 1048576).toFixed(1) + ' MB';
    }

    removeBtn.addEventListener('click', () => {
        fileInput.value = '';
        previewBar.style.display = 'none';
        dropZone.style.display = 'block';
    });


    // ═══════════════════════════════════════
    // REAL-TIME FORM VALIDATION
    // ═══════════════════════════════════════
    const form = document.getElementById('complaintForm');
    const category = document.getElementById('category');
    const receiverId = document.getElementById('receiver_id');
    const message = document.getElementById('message');
    const subject = document.getElementById('subject');
    const charCounter = document.getElementById('char-counter');

    // Validation rules
    const validators = {
        category: {
            el: category,
            validate: () => category.value !== '',
            feedback: 'category-feedback',
            msg: 'Please select a category'
        },
        receiver_id: {
            el: receiverId,
            validate: () => receiverId.value !== '',
            feedback: 'receiver-feedback',
            msg: 'Please select a handler'
        },
        message: {
            el: message,
            validate: () => message.value.trim().length >= 20,
            feedback: 'message-feedback',
            msg: 'Please provide at least 20 characters'
        }
    };

    function validateField(key) {
        const v = validators[key];
        if (!v) return true;
        const isValid = v.validate();
        
        v.el.classList.remove('is-valid', 'is-invalid');
        if (v.el.value) {
            v.el.classList.add(isValid ? 'is-valid' : 'is-invalid');
        }

        const feedback = document.getElementById(v.feedback);
        if (feedback) {
            feedback.textContent = isValid ? '' : v.msg;
            feedback.style.display = isValid ? 'none' : 'block';
        }

        updateProgress();
        return isValid;
    }

    // Real-time validation listeners
    category.addEventListener('change', () => validateField('category'));
    receiverId.addEventListener('change', () => validateField('receiver_id'));
    
    message.addEventListener('input', () => {
        const len = message.value.trim().length;
        charCounter.textContent = `${len} / 20 min`;
        charCounter.style.color = len >= 20 ? 'var(--premium-teal)' : 'var(--premium-text-muted)';
        validateField('message');
    });

    // Subject validation (optional but shows green when filled)
    subject.addEventListener('input', () => {
        subject.classList.remove('is-valid', 'is-invalid');
        if (subject.value.trim().length > 0) {
            subject.classList.add('is-valid');
        }
        updateProgress();
    });


    // ═══════════════════════════════════════
    // FORM PROGRESS TRACKER
    // ═══════════════════════════════════════
    function updateProgress() {
        const checks = {
            category: category.value !== '',
            receiver_id: receiverId.value !== '',
            message: message.value.trim().length >= 20
        };

        let completed = 0;
        let total = Object.keys(checks).length;

        Object.entries(checks).forEach(([field, done]) => {
            const item = document.querySelector(`.check-item[data-field="${field}"]`);
            if (item) {
                if (done) {
                    item.classList.add('completed');
                    item.querySelector('.check-icon').className = 'bi bi-check-circle-fill check-icon';
                    completed++;
                } else {
                    item.classList.remove('completed');
                    item.querySelector('.check-icon').className = 'bi bi-circle check-icon';
                }
            }
        });

        const pct = Math.round((completed / total) * 100);
        document.getElementById('progress-pct').textContent = pct + '%';
        document.getElementById('progress-bar').style.width = pct + '%';
    }


    // ═══════════════════════════════════════
    // FORM SUBMIT
    // ═══════════════════════════════════════
    form.addEventListener('submit', function(e) {
        let hasError = false;
        
        Object.keys(validators).forEach(key => {
            if (!validateField(key)) hasError = true;
        });

        if (hasError) {
            e.preventDefault();
            
            // Scroll to first invalid field
            const firstInvalid = form.querySelector('.is-invalid');
            if (firstInvalid) {
                firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
                firstInvalid.focus();
            }
            return;
        }

        // Show loading state
        const btn = document.getElementById('submitBtn');
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Submitting...';
        btn.disabled = true;
    });


    // ═══════════════════════════════════════
    // HISTORY MODAL
    // ═══════════════════════════════════════
    window.viewHistory = async function(id) {
        document.getElementById('modalCompId').innerText = id;
        document.getElementById('historyTimeline').innerHTML = `
            <div class="text-center py-4">
                <div class="spinner-border" style="color: var(--premium-primary);" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
        `;
        
        const modal = new bootstrap.Modal(document.getElementById('historyModal'));
        modal.show();

        try {
            const response = await fetch(`../api/get_complaint_history.php?id=${id}`);
            const data = await response.json();
            
            if (data.success) {
                let html = '';
                if (data.history.length === 0) {
                    html = '<p class="text-center py-4" style="color: var(--premium-text-muted);">No history found.</p>';
                } else {
                    data.history.forEach(item => {
                        html += `
                            <div class="history-item">
                                <div class="d-flex align-items-center flex-wrap">
                                    <span class="history-actor">${item.actor_name} (${item.actor_role.toUpperCase()})</span>
                                    <span class="history-date">${new Date(item.created_at).toLocaleString()}</span>
                                </div>
                                <div class="fw-700 small mt-1" style="color: var(--premium-text-heading);">${item.action}</div>
                                <div class="history-comment">${item.comments || 'No specific comments provided.'}</div>
                            </div>
                        `;
                    });
                }
                document.getElementById('historyTimeline').innerHTML = html;
            } else {
                document.getElementById('historyTimeline').innerHTML = `<p style="color: var(--premium-coral);">${data.message}</p>`;
            }
        } catch (err) {
            document.getElementById('historyTimeline').innerHTML = `<p style="color: var(--premium-coral);">Failed to load history.</p>`;
        }
    };

    // Initialize progress
    updateProgress();

})();
</script>

<?php include '../components/footer.php'; ?>
</body>
</html>
