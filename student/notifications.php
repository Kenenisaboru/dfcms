<?php
// student/notifications.php
require_once '../config/config.php';
require_once '../lib/NotificationService.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

$userId = $_SESSION['user_id'];
$notificationService = new NotificationService();

// Handle marking notifications as read
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_read'])) {
    CSRF::validateRequest();
    $notificationId = $_POST['notification_id'];
    $notificationService->markAsRead($notificationId, $userId);
    header("Location: notifications.php");
    exit;
}

// Handle marking all as read
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_all_read'])) {
    CSRF::validateRequest();
    $notificationService->markAllAsRead($userId);
    header("Location: notifications.php");
    exit;
}

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 20;
$offset = ($page - 1) * $perPage;

$notifications = $notificationService->getUserNotifications($userId, $perPage, false);
$totalNotifications = count($notificationService->getUserNotifications($userId, 1000));
$totalPages = ceil($totalNotifications / $perPage);
$unreadCount = $notificationService->getUnreadCount($userId);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications - DFCMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/next-gen-ui.css" rel="stylesheet">
    <style>
        .notification-card {
            transition: var(--transition);
            border-left: 4px solid transparent;
        }
        .notification-card.unread {
            border-left-color: var(--primary);
            background: rgba(16, 185, 129, 0.05);
        }
        .notification-card:hover {
            transform: translateX(5px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        .notification-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
        }
    </style>
</head>
<body class="dark-mode">
    <?php include '../components/navbar.php'; ?>

    <div class="container py-5">
        <!-- Actions Bar -->
        <div class="card p-3 mb-4">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="mb-0">All Notifications</h4>
                    <small class="text-muted"><?php echo $totalNotifications; ?> total notifications</small>
                </div>
                <div>
                    <?php if ($unreadCount > 0): ?>
                        <form method="POST" class="d-inline">
                            <?php echo CSRF::input(); ?>
                            <input type="hidden" name="mark_all_read" value="1">
                            <button type="submit" class="btn btn-sm btn-outline-success">
                                <i class="fas fa-check-double me-2"></i>Mark All Read
                            </button>
                        </form>
                    <?php endif; ?>
                    <button class="btn btn-sm btn-outline-primary ms-2" onclick="refreshNotifications()">
                        <i class="fas fa-sync-alt me-2"></i>Refresh
                    </button>
                </div>
            </div>
        </div>

        <!-- Notifications List -->
        <div class="row">
            <div class="col-12">
                <?php if (empty($notifications)): ?>
                    <div class="card text-center py-5">
                        <i class="fas fa-bell-slash fa-3x text-muted mb-3"></i>
                        <h5>No Notifications</h5>
                        <p class="text-muted">You're all caught up! No new notifications to show.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($notifications as $notification): ?>
                        <div class="card notification-card mb-3 <?php echo $notification['is_read'] ? '' : 'unread'; ?>">
                            <div class="card-body p-4">
                                <div class="row align-items-center">
                                    <div class="col-auto">
                                        <?php
                                        $iconClass = 'fa-info-circle';
                                        $iconBg = 'bg-info';
                                        switch($notification['type']) {
                                            case 'complaint_assigned':
                                                $iconClass = 'fa-clipboard-list';
                                                $iconBg = 'bg-warning';
                                                break;
                                            case 'cr_response':
                                                $iconClass = 'fa-reply';
                                                $iconBg = 'bg-success';
                                                break;
                                            case 'cr_message':
                                                $iconClass = 'fa-envelope';
                                                $iconBg = 'bg-primary';
                                                break;
                                            case 'complaint_resolved':
                                                $iconClass = 'fa-check-circle';
                                                $iconBg = 'bg-success';
                                                break;
                                            case 'system':
                                                $iconClass = 'fa-cog';
                                                $iconBg = 'bg-secondary';
                                                break;
                                        }
                                        ?>
                                        <div class="notification-icon <?php echo $iconBg; ?>">
                                            <i class="fas <?php echo $iconClass; ?> text-white"></i>
                                        </div>
                                    </div>
                                    <div class="col">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <h6 class="mb-1"><?php echo htmlspecialchars($notification['title']); ?></h6>
                                                <p class="text-muted mb-2"><?php echo htmlspecialchars($notification['message']); ?></p>
                                                <small class="text-muted">
                                                    <i class="far fa-clock me-1"></i>
                                                    <?php echo date('M j, Y, g:i A', strtotime($notification['created_at'])); ?>
                                                    <?php if ($notification['is_read']): ?>
                                                        <span class="ms-2"><i class="fas fa-check-circle text-success me-1"></i>Read</span>
                                                    <?php else: ?>
                                                        <span class="ms-2 badge bg-primary">New</span>
                                                    <?php endif; ?>
                                                </small>
                                            </div>
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="dropdown">
                                                    <i class="fas fa-ellipsis-v"></i>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-dark">
                                                    <?php if (!$notification['is_read']): ?>
                                                        <li>
                                                            <form method="POST" class="mb-0">
                                                                <?php echo CSRF::input(); ?>
                                                                <input type="hidden" name="mark_read" value="1">
                                                                <input type="hidden" name="notification_id" value="<?php echo $notification['id']; ?>">
                                                                <button type="submit" class="dropdown-item">
                                                                    <i class="fas fa-check me-2"></i>Mark as Read
                                                                </button>
                                                            </form>
                                                        </li>
                                                    <?php endif; ?>
                                                    <li><a class="dropdown-item" href="#"><i class="fas fa-trash me-2"></i>Delete</a></li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <!-- Pagination -->
                    <?php if ($totalPages > 1): ?>
                        <nav aria-label="Notifications pagination">
                            <ul class="pagination justify-content-center">
                                <?php if ($page > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?php echo $page - 1; ?>">Previous</a>
                                    </li>
                                <?php endif; ?>
                                
                                <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                                    <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                    </li>
                                <?php endfor; ?>
                                
                                <?php if ($page < $totalPages): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?php echo $page + 1; ?>">Next</a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    <?php endif; ?>
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
        function refreshNotifications() {
            location.reload();
        }

        // Auto-refresh every 30 seconds
        setInterval(() => {
            console.log('Checking for new notifications...');
            // In a real implementation, you'd use AJAX to fetch new notifications
        }, 30000);
    </script>
</body>
</html>
