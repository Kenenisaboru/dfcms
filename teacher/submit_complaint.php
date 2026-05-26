<?php
// teacher/submit_complaint.php - Teacher Complaint Submission
require_once '../config/config.php';

// Check if user is logged in as teacher
check_login('teacher');

$error = '';
$success = '';

// Fetch allowed receivers for Teachers based on permissions
$allowedRoles = ['student', 'teacher', 'cr', 'hod', 'lab_assistant'];
$placeholders = implode(',', array_fill(0, count($allowedRoles), '?'));
$stmt = $pdo->prepare("SELECT id, full_name, role FROM users WHERE role IN ($placeholders)");
$stmt->execute($allowedRoles);
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
                <!-- SUBMIT COMPLAINT FORM -->
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
                                            <span class="x-small" style="color: var(--premium-text-muted);">Teachers can route to Students, Teachers, CRs, HODs, or Lab Assistants</span>
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

                <!-- RIGHT SIDEBAR - Tips & Stats -->
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

            <!-- MY RECENT COMPLAINTS TABLE -->
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
                                                    <span class="badge bg-light text-dark"><?php echo htmlspecialchars($c['category']); ?></span>
                                                </div>
                                            </td>
                                            <td>
                                                <?php echo htmlspecialchars($c['handler_name'] ?? 'Unassigned'); ?>
                                            </td>
                                            <td>
                                                <?php 
                                                $priorityClass = match($c['priority']) {
                                                    'High' => 'bg-danger',
                                                    'Medium' => 'bg-warning',
                                                    'Low' => 'bg-success',
                                                    default => 'bg-secondary'
                                                };
                                                ?>
                                                <span class="badge <?php echo $priorityClass; ?>"><?php echo htmlspecialchars($c['priority']); ?></span>
                                            </td>
                                            <td>
                                                <?php 
                                                $statusClass = match($c['status']) {
                                                    'Resolved' => 'bg-success',
                                                    'Pending' => 'bg-warning',
                                                    'In-Progress' => 'bg-info',
                                                    'Forwarded' => 'bg-primary',
                                                    'Assigned' => 'bg-secondary',
                                                    default => 'bg-secondary'
                                                };
                                                ?>
                                                <span class="badge <?php echo $statusClass; ?>"><?php echo htmlspecialchars($c['status']); ?></span>
                                            </td>
                                            <td>
                                                <span class="small"><?php echo date('M j, Y', strtotime($c['created_at'])); ?></span>
                                            </td>
                                            <td class="text-end pe-4">
                                                <a href="tracker.php" class="btn btn-sm btn-light rounded-pill">View</a>
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

<!-- Modern UI Framework -->
<script src="../assets/js/dfcms-ui.js"></script>
<script src="../assets/js/next-gen-ui.js"></script>

<script>
(function() {
    const form = document.getElementById('complaintForm');
    const submitBtn = document.getElementById('submitBtn');
    const messageInput = document.getElementById('message');
    const charCounter = document.getElementById('char-counter');
    const progressBar = document.getElementById('progress-bar');
    const progressPct = document.getElementById('progress-pct');
    const checklist = document.getElementById('checklist');
    const fileDropZone = document.getElementById('fileDropZone');
    const fileInput = document.getElementById('attachment');
    const filePreviewBar = document.getElementById('filePreviewBar');
    const removeFileBtn = document.getElementById('removeFileBtn');
    const priorityPills = document.querySelectorAll('.priority-pill');

    // Character counter
    messageInput.addEventListener('input', function() {
        const count = this.value.length;
        charCounter.textContent = count + ' / 20 min';
        if (count >= 20) {
            charCounter.style.color = 'var(--premium-teal)';
        } else {
            charCounter.style.color = 'var(--premium-text-muted)';
        }
        updateProgress();
    });

    // Priority pill selection
    priorityPills.forEach(pill => {
        pill.addEventListener('click', function() {
            priorityPills.forEach(p => p.classList.remove('active'));
            this.classList.add('active');
            this.querySelector('input').checked = true;
            updateProgress();
        });
    });

    // File upload handling
    fileDropZone.addEventListener('click', () => fileInput.click());
    fileDropZone.addEventListener('dragover', (e) => {
        e.preventDefault();
        fileDropZone.style.borderColor = 'var(--premium-primary)';
    });
    fileDropZone.addEventListener('dragleave', () => {
        fileDropZone.style.borderColor = 'var(--premium-border)';
    });
    fileDropZone.addEventListener('drop', (e) => {
        e.preventDefault();
        fileDropZone.style.borderColor = 'var(--premium-border)';
        if (e.dataTransfer.files.length) {
            fileInput.files = e.dataTransfer.files;
            handleFileSelect(e.dataTransfer.files[0]);
        }
    });
    fileInput.addEventListener('change', () => {
        if (fileInput.files.length) {
            handleFileSelect(fileInput.files[0]);
        }
    });

    function handleFileSelect(file) {
        const fileName = file.name;
        const fileSize = (file.size / 1024).toFixed(2) + ' KB';
        const ext = fileName.split('.').pop().toLowerCase();
        
        document.getElementById('previewFileName').textContent = fileName;
        document.getElementById('previewFileSize').textContent = fileSize;
        
        const iconMap = {
            'jpg': 'bi-file-earmark-image',
            'jpeg': 'bi-file-earmark-image',
            'png': 'bi-file-earmark-image',
            'pdf': 'bi-file-earmark-pdf'
        };
        document.getElementById('fileTypeIcon').innerHTML = `<i class="bi ${iconMap[ext] || 'bi-file-earmark'}"></i>`;
        
        fileDropZone.style.display = 'none';
        filePreviewBar.style.display = 'flex';
    }

    removeFileBtn.addEventListener('click', () => {
        fileInput.value = '';
        fileDropZone.style.display = 'block';
        filePreviewBar.style.display = 'none';
    });

    // Progress tracking
    function updateProgress() {
        const fields = ['category', 'receiver_id', 'message'];
        let completed = 0;
        
        fields.forEach(field => {
            const el = document.getElementById(field);
            const checkItem = document.querySelector(`.check-item[data-field="${field}"]`);
            const checkIcon = checkItem?.querySelector('.check-icon');
            
            if (el && el.value) {
                completed++;
                if (checkIcon) {
                    checkIcon.classList.remove('bi-circle');
                    checkIcon.classList.add('bi-check-circle-fill');
                    checkIcon.style.color = 'var(--premium-teal)';
                }
            } else {
                if (checkIcon) {
                    checkIcon.classList.remove('bi-check-circle-fill');
                    checkIcon.classList.add('bi-circle');
                    checkIcon.style.color = '';
                }
            }
        });
        
        const pct = Math.round((completed / fields.length) * 100);
        progressBar.style.width = pct + '%';
        progressPct.textContent = pct + '%';
    }

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
            requiredMessage: 'Please select a handler'
        },
        message: {
            required: true,
            minLength: 20,
            requiredMessage: 'Please provide a description',
            minLengthMessage: 'Description must be at least 20 characters'
        }
    });

    // Form submission
    form.addEventListener('submit', function(e) {
        if (!validator.validate()) {
            e.preventDefault();
            DFCMS.toast.error('Please correct the errors before submitting.');
            return;
        }

        DFCMS.LoadingManager.button(submitBtn, 'Submitting...');
    });

    // Initial progress update
    updateProgress();
})();
</script>
</body>
</html>
