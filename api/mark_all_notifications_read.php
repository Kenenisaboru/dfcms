<?php
// api/mark_all_notifications_read.php
header('Content-Type: application/json');
require_once '../config/config.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

require_once '../lib/NotificationService.php';
CSRF::validateRequest(true);

$notificationService = new NotificationService();
$result = $notificationService->markAllAsRead($_SESSION['user_id']);

echo json_encode(['success' => $result]);
?>
