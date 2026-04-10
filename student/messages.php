<?php
// student/messages.php
require_once '../config/config.php';
require_once '../lib/NotificationService.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

$userId = $_SESSION['user_id'];
$role = $_SESSION['role'];
$roleKey = strtolower(trim((string) $role));
if (in_array($roleKey, array('department_head', 'department head', 'head_of_department'), true)) {
    $roleKey = 'hod';
}

// Allow any logged-in user
if (!isset($role)) {
    die("Access Denied");
}


$notificationService = new NotificationService();
$sendError = '';
$sendSuccess = isset($_GET['success']) && $_GET['success'] == '1';
$broadcastError = '';
$broadcastSuccess = '';
$activeReceiverId = isset($_GET['receiver_id']) ? (int) $_GET['receiver_id'] : 0;

// Handle message sending
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    CSRF::validateRequest();
    if ($_POST['action'] === 'send_message') {
        $receiverId = isset($_POST['receiver_id']) ? (int) $_POST['receiver_id'] : 0;
        $subject = isset($_POST['subject']) ? trim($_POST['subject']) : '';
        $message = isset($_POST['message']) ? trim($_POST['message']) : '';

        if ($receiverId <= 0 || $subject === '' || $message === '') {
            $sendError = 'Please fill all message fields.';
        } elseif (!$notificationService->canUsersChat($userId, $receiverId)) {
            $sendError = 'You are not allowed to send a message to this user.';
        } else {
            $sent = $notificationService->createMessage($userId, $receiverId, $subject, $message);
            if ($sent) {
                header("Location: messages.php?receiver_id=" . $receiverId . "&success=1");
                exit;
            }
            $details = trim($notificationService->getLastError());
            $sendError = 'Message send failed. ' . ($details !== '' ? $details : 'Please try again.');
        }

        $activeReceiverId = $receiverId;
        $messages = $notificationService->getConversation($userId, $receiverId);
    } elseif ($_POST['action'] === 'broadcast_hod') {
        if ($roleKey !== 'hod') {
            $broadcastError = 'Only HOD can broadcast messages.';
        } else {
            $subject = isset($_POST['subject']) ? trim($_POST['subject']) : '';
            $message = isset($_POST['message']) ? trim($_POST['message']) : '';

            if ($subject === '' || $message === '') {
                $broadcastError = 'Please provide subject and message for broadcast.';
            } else {
                $sentCount = $notificationService->broadcastAsHOD($userId, $subject, $message);
                if ($sentCount > 0) {
                    $broadcastSuccess = "Broadcast sent to {$sentCount} users.";
                } else {
                    $details = trim($notificationService->getLastError());
                    $broadcastError = 'Broadcast failed. ' . ($details !== '' ? $details : 'Please try again.');
                }
            }
        }
    }
}

$contacts = $notificationService->getChatContacts($userId);

if ($activeReceiverId > 0) {
    $receiverId = $activeReceiverId;
    $messages = $notificationService->getConversation($userId, $receiverId);
    $notificationService->markConversationAsRead($userId, $receiverId);
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
        :root {
            --primary: #10b981;
            --primary-glow: rgba(16, 185, 129, 0.4);
            --bg-dark: #0c0d0e;
            --card-bg: rgba(18, 18, 18, 0.7);
            --glass-border: rgba(255, 255, 255, 0.1);
            --text-light: #f8fafc;
            --text-dim: #94a3b8;
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

        .main-header {
            background: rgba(18, 18, 18, 0.8);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid var(--glass-border);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .card-custom { 
            background: var(--card-bg); 
            backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border) !important; 
            border-radius: 20px !important; 
            box-shadow: 0 10px 30px -10px rgba(0,0,0,0.5);
            overflow: hidden;
        }

        .text-dim { color: var(--text-dim) !important; }

        .conversation-list {
            max-height: 600px;
            overflow-y: auto;
        }
        
        .conversation-list .list-group-item {
            border: none;
            border-bottom: 1px solid var(--glass-border) !important;
            transition: all 0.3s ease;
            padding: 16px 20px;
        }
        
        .conversation-list .list-group-item:hover, 
        .conversation-list .list-group-item.active {
            background: rgba(16, 185, 129, 0.1) !important;
            border-left: 4px solid var(--primary) !important;
        }

        .conversation-list .list-group-item.active h6 {
            color: var(--primary) !important;
        }

        .message-bubble {
            max-width: 75%;
            padding: 14px 18px;
            border-radius: 20px;
            margin-bottom: 15px;
            position: relative;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .message-sent {
            background: var(--primary);
            color: #ffffff;
            margin-left: auto;
            border-bottom-right-radius: 4px;
        }
        
        .message-sent .text-muted {
            color: rgba(255,255,255,0.7) !important;
        }

        .message-received {
            background: rgba(255,255,255,0.05);
            border: 1px solid var(--glass-border);
            color: var(--text-light);
            border-bottom-left-radius: 4px;
        }
        
        .message-received .text-muted {
            color: var(--text-dim) !important;
        }

        .message-form {
            position: sticky;
            bottom: 0;
            background: rgba(18, 18, 18, 0.95);
            backdrop-filter: blur(10px);
            border-top: 1px solid var(--glass-border);
            padding: 20px;
        }

        /* Custom form controls for dark mode */
        .form-control-dark {
            background: rgba(255,255,255,0.05) !important;
            border: 1px solid var(--glass-border) !important;
            color: var(--text-light) !important;
        }
        
        .form-control-dark:focus {
            background: rgba(255,255,255,0.08) !important;
            border-color: var(--primary) !important;
            color: var(--text-light) !important;
            box-shadow: 0 0 10px rgba(16, 185, 129, 0.2) !important;
            outline: none;
        }
        
        .form-control-dark::placeholder {
            color: var(--text-dim) !important;
        }

        /* Custom scrollbar for webkit */
        ::-webkit-scrollbar {
            width: 6px;
        }
        ::-webkit-scrollbar-track {
            background: transparent;
        }
        ::-webkit-scrollbar-thumb {
            background: rgba(255,255,255,0.1);
            border-radius: 10px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: rgba(255,255,255,0.2);
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
            <div class="col-md-4 mb-4 mb-md-0">
                <?php if ($roleKey === 'hod'): ?>
                    <div class="card card-custom mb-3">
                        <div class="card-header border-bottom border-secondary pt-3 pb-2 px-3 bg-transparent">
                            <h6 class="fw-bold mb-0"><i class="fas fa-bullhorn text-warning me-2"></i>HOD Broadcast</h6>
                        </div>
                        <div class="card-body">
                            <?php if ($broadcastSuccess !== ''): ?>
                                <div class="alert alert-success py-2 mb-2"><?php echo htmlspecialchars($broadcastSuccess); ?></div>
                            <?php endif; ?>
                            <?php if ($broadcastError !== ''): ?>
                                <div class="alert alert-danger py-2 mb-2"><?php echo htmlspecialchars($broadcastError); ?></div>
                            <?php endif; ?>
                            <form method="POST">
                                <?php echo CSRF::input(); ?>
                                <input type="hidden" name="action" value="broadcast_hod">
                                <input type="text" name="subject" class="form-control form-control-dark mb-2" placeholder="Broadcast subject" required>
                                <textarea name="message" class="form-control form-control-dark mb-2" rows="3" placeholder="Broadcast message to all roles..." required></textarea>
                                <button type="submit" class="btn btn-warning w-100 fw-semibold">
                                    <i class="fas fa-paper-plane me-1"></i>Send to All Users
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endif; ?>
                <div class="card card-custom h-100">
                    <div class="card-header border-bottom border-secondary pt-4 pb-3 px-4 bg-transparent">
                        <h5 class="fw-bold mb-0"><i class="fas fa-users text-primary me-2"></i>Recent Contacts</h5>
                    </div>
                    <div class="card-body p-0 conversation-list">
                        <div class="list-group list-group-flush bg-transparent">
                            <?php foreach ($contacts as $contact): ?>
                                <a href="?receiver_id=<?php echo $contact['id']; ?>" 
                                   class="list-group-item list-group-item-action bg-transparent text-light mb-0 <?php echo $activeReceiverId == (int) $contact['id'] ? 'active' : ''; ?>">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-1 text-light fw-semibold fs-6"><?php echo htmlspecialchars($contact['full_name']); ?></h6>
                                            <small class="text-dim fw-bold" style="letter-spacing: 0.5px; font-size: 0.65rem;"><?php echo strtoupper($contact['role']); ?></small>
                                        </div>
                                        <i class="fas fa-chevron-right text-dim small opacity-50"></i>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Conversation -->
            <div class="col-md-8">
                <?php if ($sendSuccess): ?>
                    <div class="alert alert-success">Message sent successfully.</div>
                <?php endif; ?>
                <?php if ($sendError !== ''): ?>
                    <div class="alert alert-danger"><?php echo htmlspecialchars($sendError); ?></div>
                <?php endif; ?>
                <?php if ($activeReceiverId > 0): ?>
                    <div class="card card-custom h-100 d-flex flex-column">
                        <div class="card-header border-bottom border-secondary bg-transparent pt-4 pb-3 px-4">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="fw-bold mb-0">
                                    <?php 
                                        $target = array_filter($contacts, fn($c) => (int) $c['id'] === (int) $activeReceiverId);
                                        $target = reset($target);
                                        echo htmlspecialchars($target['full_name'] ?? 'Conversation');
                                    ?>
                                </h5>
                                <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3 py-2 border border-success border-opacity-25"><i class="fas fa-lock me-1"></i> Secure Channel</span>
                            </div>
                        </div>

                        
                        <div id="messageContainer" class="card-body p-4" style="min-height: 400px; max-height: 500px; overflow-y: auto;">
                            <?php if (empty($messages)): ?>
                                <div class="text-center text-muted py-5">
                                    <i class="fas fa-comments fa-3x mb-3"></i>
                                    <p>No messages yet. Start the conversation!</p>
                                </div>
                            <?php else: ?>
                                <?php foreach (array_reverse($messages) as $message): ?>
                                    <div class="message-bubble <?php echo $message['sender_id'] == $userId ? 'message-sent' : 'message-received'; ?>" data-message-id="<?php echo (int) $message['id']; ?>">
                                        <div class="small fw-bold mb-1"><?php echo htmlspecialchars($message['sender_name']); ?></div>
                                        <div><?php echo nl2br(htmlspecialchars($message['message'])); ?></div>
                                        <div class="small text-muted mt-1"><?php echo date('h:i A', strtotime($message['created_at'])); ?></div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>

                        <div class="message-form">
                            <form id="messageForm" method="POST">
                                <?php echo CSRF::input(); ?>
                                <input type="hidden" name="action" value="send_message">
                                <input type="hidden" name="receiver_id" value="<?php echo (int) $activeReceiverId; ?>">
                                <div class="row g-2 align-items-center">
                                    <div class="col-12 mb-1">
                                        <input type="text" name="subject" class="form-control form-control-dark rounded-pill px-4" placeholder="Message Subject..." required>
                                    </div>
                                    <div class="col">
                                        <textarea id="messageInput" name="message" class="form-control form-control-dark rounded-4 px-4 py-3" style="resize: none;" rows="2" placeholder="Type your secure message..." required></textarea>
                                    </div>
                                    <div class="col-auto h-100">
                                        <button class="btn btn-success rounded-circle shadow-lg d-flex align-items-center justify-content-center" style="width: 55px; height: 55px;" type="submit" title="Send Message">
                                            <i class="fas fa-paper-plane fs-5"></i>
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="card card-custom h-100 d-flex flex-column justify-content-center align-items-center" style="min-height: 600px;">
                        <div class="card-body text-center text-dim py-5 d-flex flex-column justify-content-center align-items-center">
                            <div class="bg-dark bg-opacity-50 rounded-circle p-4 mb-4 border border-secondary border-opacity-25">
                                <i class="fas fa-comments fa-3x text-primary opacity-75"></i>
                            </div>
                            <h4 class="fw-bold text-light">Select a Conversation</h4>
                            <p class="text-dim mt-2" style="max-width: 300px;">Choose a contact from the list on the left to start messaging securely across the network.</p>
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
    <script>
        (function () {
            const receiverId = <?php echo (int) $activeReceiverId; ?>;
            const currentUserId = <?php echo (int) $userId; ?>;
            const container = document.getElementById('messageContainer');

            if (!receiverId || !container) {
                return;
            }

            function renderMessage(message) {
                const emptyState = container.querySelector('.text-center.text-muted.py-5');
                if (emptyState) {
                    emptyState.remove();
                }

                const bubble = document.createElement('div');
                const senderClass = Number(message.sender_id) === currentUserId ? 'message-sent' : 'message-received';
                bubble.className = 'message-bubble ' + senderClass;
                bubble.setAttribute('data-message-id', message.id);

                const sender = document.createElement('div');
                sender.className = 'small fw-bold mb-1';
                sender.textContent = message.sender_name || 'User';

                const body = document.createElement('div');
                body.textContent = message.message;

                const time = document.createElement('div');
                time.className = 'small text-muted mt-1';
                time.textContent = message.created_time || '';

                bubble.appendChild(sender);
                bubble.appendChild(body);
                bubble.appendChild(time);
                container.appendChild(bubble);
            }

            async function pollMessages() {
                const nodes = container.querySelectorAll('[data-message-id]');
                const lastMessageId = nodes.length ? Number(nodes[nodes.length - 1].getAttribute('data-message-id')) : 0;

                try {
                    const response = await fetch('../api/chat_messages.php?receiver_id=' + receiverId + '&after_id=' + lastMessageId);
                    const payload = await response.json();
                    if (!payload.success || !Array.isArray(payload.messages)) {
                        return;
                    }

                    if (payload.messages.length > 0) {
                        payload.messages.forEach(renderMessage);
                        container.scrollTop = container.scrollHeight;
                    }
                } catch (error) {
                    // Keep polling; intermittent network errors are expected.
                }
            }

            container.scrollTop = container.scrollHeight;
            setInterval(pollMessages, 3000);
        })();
    </script>
</body>
</html>
