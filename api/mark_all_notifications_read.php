<?php
// api/mark_all_notifications_read.php
header('Content-Type: application/json');
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

require_once '../lib/NotificationService.php';

$notificationService = new NotificationService();
$result = $notificationService->markAllAsRead($_SESSION['user_id']);

echo json_encode(['success' => $result]);
?>
