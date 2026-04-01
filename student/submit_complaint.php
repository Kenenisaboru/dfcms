<?php
// student/submit_complaint.php
session_start();
require_once '../config/database.php';
require_once '../config/permissions.php';
require_once '../config/notifications.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    die("Access Denied: Only students can submit complaints.");
}

$error = '';
$success = '';

// Fetch only allowed receivers for Students (CR or Teacher)
$stmt = $pdo->query("SELECT id, full_name, role FROM users WHERE role IN ('cr', 'teacher')");
$receivers = $stmt->fetchAll();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
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
        body { background-color: #0c0d0e; color: #fff; font-family: 'Inter', sans-serif; }
        .navbar-custom { background-color: #121212; border-bottom: 1px solid #333; }
        .card-custom { background-color: #121212; border: 1px solid #333; border-radius: 12px; margin-top: 30px; padding: 40px; }
        .form-control, .form-select { background-color: #eef2f7 !important; border: 1px solid #444 !important; color: #000 !important; padding: 12px; }
        .text-accent { color: #10b981; }
        .btn-submit { background-color: #10b981; border: none; padding: 15px; font-weight: 800; color: #000; letter-spacing: 1px; }
        .btn-submit:hover { background-color: #059669; transform: translateY(-2px); }
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
                <div class="card card-custom shadow-lg">
                    <h2 class="mb-4 text-white fw-bold">Submit Complaint</h2>
                    <p class="text-muted mb-4 small">Your complaint will be securely routed according to strict University transparency protocols.</p>
                    
                    <?php if ($error): ?><div class="alert alert-danger py-2"><?php echo $error; ?></div><?php endif; ?>
                    <?php if ($success): ?><div class="alert alert-success py-2"><?php echo $success; ?></div><?php endif; ?>

                    <form method="POST" enctype="multipart/form-data">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold text-muted small">Complaint Category</label>
                                <select name="category" class="form-select" required>
                                    <option value="">Select Category...</option>
                                    <option value="Academic">Academic</option>
                                    <option value="Facilities">Facilities/Infrastructure</option>
                                    <option value="Administration">Administration</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold text-muted small">Priority Level</label>
                                <select name="priority" class="form-select" required>
                                    <option value="Low">Low Priority</option>
                                    <option value="Medium">Medium Priority</option>
                                    <option value="High">High Priority</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold text-muted small">Route Complaint To:</label>
                            <select name="receiver_id" class="form-select" required>
                                <option value="">Select Target Handler (CR or Teacher)...</option>
                                <?php foreach ($receivers as $r): ?>
                                    <option value="<?php echo $r['id']; ?>">
                                        <?php echo htmlspecialchars($r['full_name']); ?> (<?php echo strtoupper($r['role']); ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="mt-2" style="font-size: 11px; color:#888;">
                                <i class="fas fa-info-circle me-1"></i> Students are strictly limited to Class Representatives and Teaching Staff.
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold text-muted small">Description of the Issue</label>
                            <textarea name="message" class="form-control" rows="5" placeholder="Please provide specific details..." required></textarea>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold text-muted small">Evidence Attachment (Optional)</label>
                            <input type="file" name="attachment" class="form-control" accept=".jpg,.jpeg,.png,.pdf">
                        </div>

                        <button type="submit" class="btn btn-submit w-100 shadow"><i class="fas fa-paper-plane me-2"></i> Initialize Workflow</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
