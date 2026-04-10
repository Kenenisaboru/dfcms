<?php
require_once '../config/config.php';
require_once '../lib/NotificationService.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$notificationService = new NotificationService();
$userId = $_SESSION['user_id'];
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 5;
$unreadOnly = isset($_GET['unread_only']) && $_GET['unread_only'] == '1';

$notifications = $notificationService->getUserNotifications($userId, $limit, $unreadOnly);

// Convert data JSON string to array for easier frontend handling
foreach ($notifications as &$n) {
    if (isset($n['data']) && is_string($n['data'])) {
        $n['data'] = json_decode($n['data'], true);
    }
}

echo json_encode([
    'success' => true,
    'notifications' => $notifications,
    'unread_count' => $notificationService->getUnreadCount($userId)
]);
?>
