<?php
// api/get_unread_count.php
header('Content-Type: application/json');
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

require_once '../lib/NotificationService.php';

$notificationService = new NotificationService();
$count = $notificationService->getUnreadCount($_SESSION['user_id']);

echo json_encode(['success' => true, 'count' => $count]);
?>
