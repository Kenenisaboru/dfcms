<?php
// components/notifications.php
require_once '../lib/NotificationService.php';
require_once '../config/database.php';

$notificationService = new NotificationService();
$userId = $_SESSION['user_id'];
$notifications = $notificationService->getUserNotifications($userId, 5);
$unreadCount = $notificationService->getUnreadCount($userId);
?>

<!-- Notification Dropdown -->
<div class="dropdown">
    <button class="btn btn-outline-light position-relative" type="button" id="notificationDropdown" data-bs-toggle="dropdown" aria-expanded="false">
        <i class="fas fa-bell"></i>
        <span
            class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"
            id="notificationBadge"
            style="<?php echo $unreadCount > 0 ? '' : 'display:none;'; ?>"
        >
            <?php echo $unreadCount > 99 ? '99+' : (int) $unreadCount; ?>
        </span>
    </button>
    
    <ul class="dropdown-menu dropdown-menu-end dropdown-menu-dark notification-dropdown" style="min-width: 350px; max-height: 400px; overflow-y: auto;" aria-labelledby="notificationDropdown">
        <li class="dropdown-header d-flex justify-content-between align-items-center">
            <span><i class="fas fa-bell me-2"></i>Notifications</span>
            <button class="btn btn-sm btn-outline-secondary" onclick="markAllNotificationsRead()">
                <i class="fas fa-check-double"></i> Mark all read
            </button>
        </li>
        <li><hr class="dropdown-divider"></li>
        
        <?php if (empty($notifications)): ?>
            <li class="dropdown-item text-center text-muted py-3">
                <i class="fas fa-bell-slash fa-2x mb-2"></i>
                <p class="mb-0">No notifications yet</p>
            </li>
        <?php else: ?>
            <?php foreach ($notifications as $notification): ?>
                <li class="dropdown-item notification-item <?php echo $notification['is_read'] ? 'read' : 'unread'; ?>" 
                    onclick="handleNotificationClick(<?php echo $notification['id']; ?>, '<?php echo $notification['type']; ?>', <?php echo $notification['data']; ?>)">
                    <div class="d-flex">
                        <div class="flex-shrink-0">
                            <?php
                            $iconClass = 'fa-info-circle';
                            $iconColor = 'text-info';
                            switch($notification['type']) {
                                case 'complaint_assigned':
                                    $iconClass = 'fa-clipboard-list';
                                    $iconColor = 'text-warning';
                                    break;
                                case 'cr_response':
                                    $iconClass = 'fa-reply';
                                    $iconColor = 'text-success';
                                    break;
                                case 'cr_message':
                                case 'new_message':
                                    $iconClass = 'fa-envelope';
                                    $iconColor = 'text-primary';
                                    break;
                                case 'complaint_resolved':
                                    $iconClass = 'fa-check-circle';
                                    $iconColor = 'text-success';
                                    break;
                            }
                            ?>
                            <i class="fas <?php echo $iconClass; ?> <?php echo $iconColor; ?> me-2"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="fw-bold small"><?php echo htmlspecialchars($notification['title']); ?></div>
                            <div class="small text-muted"><?php echo htmlspecialchars($notification['message']); ?></div>
                            <div class="text-muted x-small mt-1">
                                <?php echo timeAgo($notification['created_at']); ?>
                            </div>
                        </div>
                        <?php if (!$notification['is_read']): ?>
                            <div class="flex-shrink-0">
                                <span class="badge bg-primary rounded-pill">New</span>
                            </div>
                        <?php endif; ?>
                    </div>
                </li>
                <li><hr class="dropdown-divider"></li>
            <?php endforeach; ?>
        <?php endif; ?>
        
        <li class="dropdown-item text-center">
            <a href="notifications.php" class="text-decoration-none">
                <i class="fas fa-list me-2"></i>View All Notifications
            </a>
        </li>
    </ul>
</div>

<style>
.notification-dropdown .dropdown-item {
    padding: 12px 16px;
    border-left: 3px solid transparent;
    transition: all 0.2s ease;
}

.notification-dropdown .dropdown-item.unread {
    background-color: rgba(59, 130, 246, 0.1);
    border-left-color: var(--bs-primary);
}

.notification-dropdown .dropdown-item:hover {
    background-color: rgba(255, 255, 255, 0.1);
}

.notification-dropdown .dropdown-header {
    background-color: rgba(255, 255, 255, 0.05);
    padding: 10px 16px;
}

#notificationBadge {
    font-size: 0.6em;
    padding: 0.25em 0.4em;
    min-width: 1.5em;
    text-align: center;
}
</style>

<script>
function handleNotificationClick(notificationId, type, data) {
    // Mark as read
    markNotificationRead(notificationId);
    
    // Handle different notification types
    switch(type) {
        case 'complaint_assigned':
        case 'cr_response':
            if (data && data.complaint_id) {
                window.location.href = 'tracker.php?id=' + data.complaint_id;
            }
            break;
        case 'cr_message':
        case 'new_message':
            if (data && data.message_id) {
                if (data.sender_id) {
                    window.location.href = 'messages.php?receiver_id=' + data.sender_id;
                } else {
                    window.location.href = 'messages.php';
                }
            }
            break;
        default:
            window.location.href = 'notifications.php';
    }
}

function markNotificationRead(notificationId) {
    fetch('../api/mark_notification_read.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ notification_id: notificationId })
    }).then(response => response.json())
      .then(data => {
          if (data.success) {
              const badge = document.getElementById('notificationBadge');
              if (badge) {
                  const currentCount = parseInt(badge.textContent);
                  if (currentCount <= 1) {
                      badge.style.display = 'none';
                  } else {
                      badge.textContent = currentCount - 1;
                  }
              }
          }
      });
}

function markAllNotificationsRead() {
    fetch('../api/mark_all_notifications_read.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' }
    }).then(response => response.json())
      .then(data => {
          if (data.success) {
              const badge = document.getElementById('notificationBadge');
              if (badge) badge.style.display = 'none';
              location.reload();
          }
      });
}

// Auto-refresh notifications every 5 seconds for near real-time updates.
setInterval(() => {
    fetch('../api/get_unread_count.php')
        .then(response => response.json())
        .then(data => {
            const badge = document.getElementById('notificationBadge');
            if (badge) {
                if (data.success && data.count > 0) {
                    badge.textContent = data.count > 99 ? '99+' : data.count;
                    badge.style.display = 'inline-block';
                } else {
                    badge.style.display = 'none';
                }
            }
        });
}, 5000);
</script>

<?php
function timeAgo($datetime) {
    $time = strtotime($datetime);
    $now = time();
    $diff = $now - $time;
    
    if ($diff < 60) return 'Just now';
    if ($diff < 3600) return floor($diff / 60) . ' min ago';
    if ($diff < 86400) return floor($diff / 3600) . ' hours ago';
    return date('M j, Y', $time);
}
?>
