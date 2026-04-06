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

// Allow any logged-in user
if (!isset($role)) {
    die("Access Denied");
}


$notificationService = new NotificationService();

// Handle message sending
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'send_message') {
        $receiverId = $_POST['receiver_id'];
        $subject = $_POST['subject'];
        $message = $_POST['message'];
        
        $notificationService->createMessage($userId, $receiverId, $subject, $message);
        
        header("Location: messages.php?receiver_id=" . $receiverId . "&success=1");
        exit;
    }
}


// Get contacts based on role
$contacts = array();
if ($role === 'student') {
    // Students see CRs and Teachers they've interacted with (or just all for simplicity here)
    $stmt = $pdo->prepare("SELECT id, full_name, role FROM users WHERE role IN ('cr', 'teacher')");
    $stmt->execute();
    $contacts = $stmt->fetchAll();
} else {
    // Others (CR, Teacher, HOD) see all students (or assigned ones)
    $stmt = $pdo->prepare("SELECT id, full_name, 'student' as role FROM users WHERE role = 'student'");
    $stmt->execute();
    $contacts = $stmt->fetchAll();
}

if (isset($_GET['receiver_id'])) {
    $receiverId = $_GET['receiver_id'];
    $messages = $notificationService->getConversation($userId, $receiverId);
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
                    <h5 class="mb-3">Recent Contacts</h5>
                    <div class="conversation-list">
                        <?php foreach ($contacts as $contact): ?>
                            <a href="?receiver_id=<?php echo $contact['id']; ?>" 
                               class="list-group-item list-group-item-action bg-transparent text-light border-secondary mb-2 <?php echo isset($_GET['receiver_id']) && $_GET['receiver_id'] == $contact['id'] ? 'active' : ''; ?>">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1"><?php echo htmlspecialchars($contact['full_name']); ?></h6>
                                        <small class="text-muted opacity-75"><?php echo strtoupper($contact['role']); ?></small>
                                    </div>
                                    <i class="fas fa-chevron-right text-secondary small"></i>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>

            </div>

            <!-- Conversation -->
            <div class="col-md-8">
                <?php if (isset($_GET['receiver_id'])): ?>
                    <div class="card h-100">
                        <div class="card-header border-bottom border-secondary">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">
                                    <?php 
                                        $target = array_filter($contacts, fn($c) => $c['id'] == $_GET['receiver_id']);
                                        $target = reset($target);
                                        echo htmlspecialchars($target['full_name'] ?? 'Conversation');
                                    ?>
                                </h5>
                                <small class="text-muted"><i class="fas fa-circle text-success me-1 small"></i> Secure Channel</small>
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

                        <div class="message-form">
                            <form method="POST">
                                <input type="hidden" name="action" value="send_message">
                                <input type="hidden" name="receiver_id" value="<?php echo $_GET['receiver_id']; ?>">
                                <div class="row g-2">
                                    <div class="col-12 mb-2">
                                        <input type="text" name="subject" class="form-control" placeholder="Subject" required>
                                    </div>
                                    <div class="col">
                                        <textarea name="message" class="form-control" rows="1" placeholder="Type your message..." required></textarea>
                                    </div>
                                    <div class="col-auto">
                                        <button class="btn btn-success h-100 px-4" type="submit">
                                            <i class="fas fa-paper-plane"></i>
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="card h-100">
                        <div class="card-body text-center text-muted py-5">
                            <i class="fas fa-comments fa-3x mb-3 opacity-25"></i>
                            <h5>Select a conversation</h5>
                            <p>Choose a contact from the list on the left to start messaging.</p>
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
