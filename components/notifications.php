<?php
// components/notifications.php - Premium Notification Component v4.0
require_once __DIR__ . '/../lib/NotificationService.php';
require_once __DIR__ . '/../config/database.php';

if (!function_exists('timeAgo')) {
    function timeAgo($datetime) {
        $time = strtotime($datetime);
        $now = time();
        $diff = $now - $time;
        
        if ($diff < 60) return 'Just now';
        if ($diff < 3600) return floor($diff / 60) . ' min ago';
        if ($diff < 86400) return floor($diff / 3600) . ' hours ago';
        return date('M j, Y', $time);
    }
}

$userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
$notifications = [];
$unreadCount = 0;

if ($userId) {
    $notificationService = new NotificationService();
    $notifications = $notificationService->getUserNotifications($userId, 5);
    $unreadCount = $notificationService->getUnreadCount($userId);
} else {
    // If not logged in, don't render anything for notifications
    return;
}
?>

<!-- Premium Notification Bell -->
<div class="dropdown">
    <button class="notification-trigger" type="button" id="notificationDropdown" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Notifications">
        <i class="bi bi-bell-fill"></i>
        <?php if ($unreadCount > 0): ?>
            <span class="notification-badge" id="notificationBadge">
                <?php echo $unreadCount > 99 ? '99+' : (int) $unreadCount; ?>
            </span>
        <?php else: ?>
            <span class="notification-badge" id="notificationBadge" style="display:none;">0</span>
        <?php endif; ?>
    </button>
    
    <ul class="dropdown-menu dropdown-menu-end notification-dropdown" aria-labelledby="notificationDropdown">
        <li class="dropdown-header d-flex justify-content-between align-items-center">
            <span class="fw-700" style="color: var(--premium-text-heading); font-size: 0.9375rem;">
                <i class="bi bi-bell-fill me-2" style="color: var(--premium-primary);"></i>Notifications
            </span>
            <button class="btn btn-sm rounded-pill px-3 fw-600" 
                    style="background: var(--premium-primary-soft); color: var(--premium-primary); font-size: 0.6875rem; border: none;"
                    onclick="markAllNotificationsRead()">
                <i class="bi bi-check2-all me-1"></i> Mark all read
            </button>
        </li>
        <li><hr class="dropdown-divider"></li>
        
        <?php if (empty($notifications)): ?>
            <li class="text-center py-4 px-3">
                <div class="d-inline-flex align-items-center justify-content-center mb-2 rounded-xl" 
                     style="width: 56px; height: 56px; background: var(--premium-bg);">
                    <i class="bi bi-bell-slash fs-4" style="color: var(--premium-text-muted);"></i>
                </div>
                <p class="mb-0 fw-600 small" style="color: var(--premium-text-heading);">All caught up!</p>
                <p class="mb-0 x-small text-muted-color">No new notifications</p>
            </li>
        <?php else: ?>
            <?php foreach ($notifications as $notification): ?>
                <?php
                $notificationData = !empty($notification['data']) ? $notification['data'] : '{}';
                if (is_string($notificationData)) {
                    $notificationData = json_decode($notificationData, true) ?: [];
                }
                ?>
                <li class="dropdown-item notification-item <?php echo ($notification['is_read'] ?? true) ? 'read' : 'unread'; ?>"
                    onclick="handleNotificationClick(<?php echo (int)($notification['id'] ?? 0); ?>, '<?php echo addslashes($notification['type'] ?? 'info'); ?>', <?php echo json_encode($notificationData); ?>)"
                    style="cursor: pointer;">
                    <div class="d-flex gap-3 align-items-start">
                        <div class="flex-shrink-0">
                            <?php
                            $iconClass = 'bi-info-circle-fill';
                            $iconBg = 'var(--premium-info-soft)';
                            $iconColor = 'var(--premium-info)';
                            switch($notification['type']) {
                                case 'complaint_assigned':
                                    $iconClass = 'bi-clipboard2-check-fill';
                                    $iconBg = 'var(--premium-amber-soft)';
                                    $iconColor = 'var(--premium-amber)';
                                    break;
                                case 'cr_response':
                                    $iconClass = 'bi-reply-fill';
                                    $iconBg = 'var(--premium-teal-soft)';
                                    $iconColor = 'var(--premium-teal)';
                                    break;
                                case 'cr_message':
                                case 'new_message':
                                    $iconClass = 'bi-envelope-fill';
                                    $iconBg = 'var(--premium-primary-soft)';
                                    $iconColor = 'var(--premium-primary)';
                                    break;
                                case 'complaint_resolved':
                                    $iconClass = 'bi-check-circle-fill';
                                    $iconBg = 'var(--premium-teal-soft)';
                                    $iconColor = 'var(--premium-teal)';
                                    break;
                            }
                            ?>
                            <div class="d-flex align-items-center justify-content-center rounded-lg" 
                                 style="width: 38px; height: 38px; background: <?php echo $iconBg; ?>; border-radius: var(--radius-sm);">
                                <i class="bi <?php echo $iconClass; ?>" style="color: <?php echo $iconColor; ?>; font-size: 1rem;"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 min-width-0">
                            <div class="fw-600 small" style="color: var(--premium-text-heading);"><?php echo htmlspecialchars($notification['title'] ?? 'Notification'); ?></div>
                            <div class="x-small text-muted-color text-truncate" style="max-width: 220px;"><?php echo htmlspecialchars($notification['message'] ?? ''); ?></div>
                            <div class="x-small mt-1" style="color: var(--premium-text-muted);">
                                <i class="bi bi-clock me-1"></i><?php echo timeAgo($notification['created_at'] ?? date('Y-m-d H:i:s')); ?>
                            </div>
                        </div>
                        <?php if (!($notification['is_read'] ?? true)): ?>
                            <div class="flex-shrink-0">
                                <span class="d-inline-block rounded-circle" 
                                      style="width: 8px; height: 8px; background: var(--premium-primary); margin-top: 6px;"></span>
                            </div>
                        <?php endif; ?>
                    </div>
                </li>
                <li><hr class="dropdown-divider"></li>
            <?php endforeach; ?>
        <?php endif; ?>
        
        <li class="text-center py-2">
            <a href="/dfcms/student/notifications.php" class="text-decoration-none fw-600 small" style="color: var(--premium-primary);">
                View All Notifications <i class="bi bi-arrow-right ms-1"></i>
            </a>
        </li>
    </ul>
</div>

<!-- Toast Container -->
<div id="notificationToastContainer" class="position-fixed bottom-0 end-0 p-3" style="z-index: 9999;"></div>

<script>
const csrfToken = '<?php echo CSRF::generate(); ?>';
let lastSeenNotificationId = <?php echo !empty($notifications) ? (int)$notifications[0]['id'] : 0; ?>;

function showTelegramToast(n) {
    const container = document.getElementById('notificationToastContainer');
    const toastId = 'toast_' + n.id;
    
    // Don't show if already exists
    if (document.getElementById(toastId)) return;

    const toast = document.createElement('div');
    toast.id = toastId;
    toast.className = 'toast tg-toast show';
    toast.setAttribute('role', 'alert');
    toast.setAttribute('aria-live', 'assertive');
    toast.setAttribute('aria-atomic', 'true');
    
    toast.innerHTML = `
        <div class="toast-header bg-transparent border-bottom pb-1" style="border-color: var(--premium-border-light) !important;">
            <i class="bi bi-bell-fill me-2" style="color: var(--premium-primary);"></i>
            <strong class="me-auto" style="color: var(--premium-text-heading);">${n.title}</strong>
            <small style="color: var(--premium-text-muted);">Just now</small>
            <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        <div class="toast-body py-2" style="color: var(--premium-text-body);">
            ${n.message}
            <div class="mt-2 text-end">
                <button class="btn btn-sm btn-primary py-0 px-3 rounded-pill fw-600" style="font-size: 0.75rem" onclick="handleNotificationClick(${n.id}, '${n.type}', ${JSON.stringify(n.data)})">View</button>
            </div>
        </div>
    `;
    
    container.appendChild(toast);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 500);
    }, 5000);
}

function handleNotificationClick(notificationId, type, data) {
    // Mark as read
    markNotificationRead(notificationId);

    // Handle different notification types
    const userRole = '<?php echo isset($_SESSION['role']) ? addslashes($_SESSION['role']) : 'student'; ?>';
    let baseUrl = '/dfcms/student/';

    if (userRole === 'teacher' || userRole === 'admin' || userRole === 'hod') {
        baseUrl = '/dfcms/';
    }

    switch(type) {
        case 'complaint_assigned':
        case 'cr_response':
            if (data && data.complaint_id) {
                window.location.href = baseUrl + 'tracker.php?id=' + data.complaint_id;
            }
            break;
        case 'cr_message':
        case 'new_message':
            const receiverId = data && data.sender_id ? data.sender_id : '';
            window.location.href = baseUrl + 'messages.php' + (receiverId ? '?receiver_id=' + receiverId : '');
            break;
        default:
            window.location.href = baseUrl + 'notifications.php';
    }
}

function markNotificationRead(notificationId) {
    fetch('/dfcms/api/mark_notification_read.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-Token': csrfToken
        },
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
    fetch('/dfcms/api/mark_all_notifications_read.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-Token': csrfToken
        }
    }).then(response => response.json())
      .then(data => {
          if (data.success) {
              const badge = document.getElementById('notificationBadge');
              if (badge) badge.style.display = 'none';
              location.reload();
          }
      });
}

// Auto-refresh notifications every 4 seconds for Telegram-style updates.
setInterval(() => {
    fetch('/dfcms/api/get_latest_notifications.php?limit=5&unread_only=1')
        .then(response => response.json())
        .then(data => {
            if (!data.success) return;

            const badge = document.getElementById('notificationBadge');
            if (badge) {
                if (data.unread_count > 0) {
                    badge.textContent = data.unread_count > 99 ? '99+' : data.unread_count;
                    badge.style.display = 'inline-flex';
                } else {
                    badge.style.display = 'none';
                }
            }

            // Check for new notifications to show as Toast
            if (data.notifications && data.notifications.length > 0) {
                const newest = data.notifications[0];
                if (newest.id > lastSeenNotificationId) {
                    showTelegramToast(newest);
                    lastSeenNotificationId = newest.id;
                }
            }
        });
}, 4000);
</script>
