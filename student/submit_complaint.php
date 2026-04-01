<?php
// student/submit_complaint.php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    die("Access Denied: Only students can submit complaints.");
}

$error = '';
$success = '';

// Fetch available CRs and Teachers for routing
$stmt = $pdo->query("SELECT id, full_name, role FROM users WHERE role IN ('cr', 'teacher')");
$receivers = $stmt->fetchAll();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $category = trim($_POST['category']);
    $priority = $_POST['priority'];
    $receiver_id = $_POST['receiver_id'];
    $message = trim($_POST['message']);
    $file_path = null;

    // Validate Receiver
    $valid_receiver = false;
    $receiver_role = '';
    foreach ($receivers as $r) {
        if ($r['id'] == $receiver_id) {
            $valid_receiver = true;
            $receiver_role = $r['role'];
            break;
        }
    }

    if (!$valid_receiver) {
        $error = "Invalid recipient selected. You can only send to CR or Teacher.";
    } elseif (empty($category) || empty($priority) || empty($message)) {
        $error = "All mandatory fields must be filled.";
    } else {
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
                // Ensure upload directory exists
                $uploadDir = '../assets/uploads/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                $newFilename = uniqid('comp_') . '.' . $ext;
                $dest = $uploadDir . $newFilename;
                if (move_uploaded_file($_FILES['attachment']['tmp_name'], $dest)) {
                    $file_path = 'assets/uploads/' . $newFilename;
                } else {
                    $error = "Failed to upload file.";
                }
            }
        }

        if (empty($error)) {
            try {
                $pdo->beginTransaction();

                $stmt = $pdo->prepare("INSERT INTO complaints (student_id, assigned_to, current_handler_role, category, priority, message, file_path, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'Pending')");
                $stmt->execute([$_SESSION['user_id'], $receiver_id, $receiver_role, $category, $priority, $message, $file_path]);
                $complaintId = $pdo->lastInsertId();

                $stmtHist = $pdo->prepare("INSERT INTO complaint_history (complaint_id, action_by, action, comments) VALUES (?, ?, 'Submitted', 'Complaint initialized and sent to designated receiver.')");
                $stmtHist->execute([$complaintId, $_SESSION['user_id']]);

                $pdo->commit();
                $success = "Complaint submitted successfully!";
            } catch (Exception $e) {
                $pdo->rollBack();
                $error = "Error saving complaint: " . $e->getMessage();
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
    <title>Submit Complaint - DFCMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background-color: #121212; color: #fff; }
        .navbar-custom { background-color: #1e1e1e; border-bottom: 1px solid #333; }
        .card-custom { background-color: #1e1e1e; border: 1px solid #333; border-radius: 10px; margin-top: 30px; padding: 30px; }
        .form-control, .form-select { background-color: #2c2c2c; border: 1px solid #444; color: #fff; }
        .form-control:focus, .form-select:focus { background-color: #333; color: #fff; border-color: #10b981; box-shadow: none; }
        .btn-primary { background-color: #10b981; border: none; }
        .btn-primary:hover { background-color: #059669; }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark navbar-custom px-4">
        <a class="navbar-brand text-success fw-bold" href="../dashboard.php">DFCMS</a>
        <div class="ms-auto d-flex align-items-center">
            <a href="../dashboard.php" class="btn btn-outline-light btn-sm me-3"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
            <span class="text-light me-3"><i class="fas fa-user-circle"></i> <?php echo htmlspecialchars($_SESSION['full_name']); ?></span>
        </div>
    </nav>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card card-custom shadow-lg">
                    <h3 class="mb-4">Submit a New Complaint</h3>
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>
                    <?php if ($success): ?>
                        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                    <?php endif; ?>

                    <form method="POST" action="" enctype="multipart/form-data">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Category</label>
                                <select name="category" class="form-select" required>
                                    <option value="">Select Category...</option>
                                    <option value="Academic">Academic</option>
                                    <option value="Facilities">Facilities/Infrastructure</option>
                                    <option value="Administration">Administration</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Priority</label>
                                <select name="priority" class="form-select" required>
                                    <option value="Low">Low</option>
                                    <option value="Medium">Medium</option>
                                    <option value="High">High</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Send Complaint To:</label>
                            <select name="receiver_id" class="form-select" required>
                                <option value="">Select Recipient (CR or Teacher)...</option>
                                <?php foreach ($receivers as $r): ?>
                                    <option value="<?php echo $r['id']; ?>">
                                        <?php echo htmlspecialchars($r['full_name']); ?> (<?php echo strtoupper($r['role']); ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <small class="text-muted">Students can only submit complaints to Class Representatives or Teachers.</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Complaint Details</label>
                            <textarea name="message" class="form-control" rows="5" required></textarea>
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Attachment (Optional)</label>
                            <input type="file" name="attachment" class="form-control" accept=".jpg,.jpeg,.png,.pdf">
                            <small class="text-muted">Max 5MB. PDF, JPG, PNG only.</small>
                        </div>

                        <button type="submit" class="btn btn-primary w-100"><i class="fas fa-paper-plane me-2"></i> Submit Complaint</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
