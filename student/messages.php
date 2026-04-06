<?php
// student/messages.php
session_start();
require_once '../config/database.php';
require_once '../lib/NotificationService.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

$userId = $_SESSION['user_id'];
$role = $_SESSION['role'];

// Only allow students and CRs
if (!in_array($role, ['student', 'cr'])) {
    die("Access Denied");
}

$notificationService = new NotificationService();

// Handle message sending
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'send_message') {
        $studentId = $_POST['student_id'];
        $crId = $_POST['cr_id'];
        $subject = $_POST['subject'];
        $message = $_POST['message'];
        
        if ($role === 'cr') {
            $notificationService->createCRStudentMessage($crId, $studentId, $subject, $message);
        }
        
        header("Location: messages.php?success=1");
        exit;
    }
}

// Get messages
$messages = array();
$students = array();
$crs = array();

if ($role === 'cr') {
    $students = $notificationService->getCRAssignedStudents($userId);
    
    if (isset($_GET['student_id'])) {
        $studentId = $_GET['student_id'];
        $messages = $notificationService->getCRStudentConversation($userId, $studentId);
    }
} elseif ($role === 'student') {
    // Get CR for this student (simplified - in real app, you'd have proper assignment)
    $stmt = $pdo->prepare("SELECT id, full_name FROM users WHERE role = 'cr' LIMIT 1");
    $stmt->execute();
    $crs = $stmt->fetchAll();
    
    if (!empty($crs) && isset($_GET['cr_id'])) {
        $crId = $_GET['cr_id'];
        $messages = $notificationService->getCRStudentConversation($crId, $userId);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages - DFCMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/next-gen-ui.css" rel="stylesheet">
    <style>
        .conversation-list {
            max-height: 400px;
            overflow-y: auto;
        }
        .message-bubble {
            max-width: 70%;
            padding: 12px 16px;
            border-radius: 18px;
            margin-bottom: 8px;
        }
        .message-sent {
            background: var(--primary);
            color: white;
            margin-left: auto;
        }
        .message-received {
            background: var(--card-bg-dark);
            border: 1px solid var(--border-dark);
        }
        .message-form {
            position: sticky;
            bottom: 0;
            background: var(--card-bg-dark);
            border-top: 1px solid var(--border-dark);
            padding: 16px;
        }
    </style>
</head>
<body class="dark-mode">
    <nav class="main-header py-3">
        <div class="container d-flex justify-content-between align-items-center">
            <a href="../dashboard.php" class="text-decoration-none text-white h4 mb-0">
                <i class="fas fa-envelope text-success me-2"></i>Messages
            </a>
            <div class="d-flex gap-3 align-items-center">
                <?php include '../components/notifications.php'; ?>
                <a href="../dashboard.php" class="btn btn-outline-light btn-sm">Dashboard</a>
            </div>
        </div>
    </nav>

    <div class="container" style="margin-top: 100px;">
        <div class="row">
            <!-- Contact List -->
            <div class="col-md-4">
                <div class="card p-4">
                    <h5 class="mb-3">
                        <?php echo $role === 'cr' ? 'Students' : 'Class Representatives'; ?>
                    </h5>
                    <div class="conversation-list">
                        <?php if ($role === 'cr'): ?>
                            <?php foreach ($students as $student): ?>
                                <a href="?student_id=<?php echo $student['id']; ?>" 
                                   class="list-group-item list-group-item-action bg-transparent text-light border-secondary mb-2 <?php echo isset($_GET['student_id']) && $_GET['student_id'] == $student['id'] ? 'active' : ''; ?>">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-1"><?php echo htmlspecialchars($student['full_name']); ?></h6>
                                            <small class="text-muted"><?php echo $student['complaint_count']; ?> complaints</small>
                                        </div>
                                        <i class="fas fa-chevron-right text-secondary"></i>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <?php foreach ($crs as $cr): ?>
                                <a href="?cr_id=<?php echo $cr['id']; ?>" 
                                   class="list-group-item list-group-item-action bg-transparent text-light border-secondary mb-2 <?php echo isset($_GET['cr_id']) && $_GET['cr_id'] == $cr['id'] ? 'active' : ''; ?>">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-1"><?php echo htmlspecialchars($cr['full_name']); ?></h6>
                                            <small class="text-muted">Class Representative</small>
                                        </div>
                                        <i class="fas fa-chevron-right text-secondary"></i>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Conversation -->
            <div class="col-md-8">
                <?php if (($role === 'cr' && isset($_GET['student_id'])) || ($role === 'student' && isset($_GET['cr_id']))): ?>
                    <div class="card h-100">
                        <div class="card-header border-bottom border-secondary">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">
                                    <?php 
                                    if ($role === 'cr') {
                                        $student = array_filter($students, fn($s) => $s['id'] == $_GET['student_id']);
                                        $student = reset($student);
                                        echo htmlspecialchars($student['full_name']);
                                    } else {
                                        $cr = array_filter($crs, fn($c) => $c['id'] == $_GET['cr_id']);
                                        $cr = reset($cr);
                                        echo htmlspecialchars($cr['full_name']);
                                    }
                                    ?>
                                </h5>
                                <small class="text-muted">Direct Message</small>
                            </div>
                        </div>
                        
                        <div class="card-body p-4" style="min-height: 400px; max-height: 500px; overflow-y: auto;">
                            <?php if (empty($messages)): ?>
                                <div class="text-center text-muted py-5">
                                    <i class="fas fa-comments fa-3x mb-3"></i>
                                    <p>No messages yet. Start the conversation!</p>
                                </div>
                            <?php else: ?>
                                <?php foreach (array_reverse($messages) as $message): ?>
                                    <div class="message-bubble <?php echo $message['sender_id'] == $userId ? 'message-sent' : 'message-received'; ?>">
                                        <div class="small fw-bold mb-1"><?php echo htmlspecialchars($message['sender_name']); ?></div>
                                        <div><?php echo nl2br(htmlspecialchars($message['message'])); ?></div>
                                        <div class="small text-muted mt-1"><?php echo date('h:i A', strtotime($message['created_at'])); ?></div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>

                        <?php if ($role === 'cr'): ?>
                            <div class="message-form">
                                <form method="POST">
                                    <input type="hidden" name="action" value="send_message">
                                    <input type="hidden" name="cr_id" value="<?php echo $userId; ?>">
                                    <input type="hidden" name="student_id" value="<?php echo $_GET['student_id']; ?>">
                                    <div class="input-group">
                                        <input type="text" name="subject" class="form-control" placeholder="Subject" required>
                                        <input type="text" name="message" class="form-control" placeholder="Type your message..." required>
                                        <button class="btn btn-success" type="submit">
                                            <i class="fas fa-paper-plane"></i> Send
                                        </button>
                                    </div>
                                </form>
                            </div>
                        <?php else: ?>
                            <div class="message-form">
                                <div class="text-center text-muted">
                                    <i class="fas fa-info-circle me-2"></i>
                                    Only Class Representatives can initiate messages. Wait for your CR to contact you.
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="card h-100">
                        <div class="card-body text-center text-muted py-5">
                            <i class="fas fa-comments fa-3x mb-3"></i>
                            <h5>Select a conversation</h5>
                            <p>Choose a <?php echo $role === 'cr' ? 'student' : 'Class Representative'; ?> from the list to start messaging.</p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <button class="theme-toggle" aria-label="Toggle dark/light mode">
        <i class="fas fa-sun"></i>
    </button>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/next-gen-ui.js"></script>
</body>
</html>
