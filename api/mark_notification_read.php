<?php
// api/mark_notification_read.php
header('Content-Type: application/json');
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

require_once '../lib/NotificationService.php';
require_once '../config/database.php';

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['notification_id'])) {
    echo json_encode(['success' => false, 'message' => 'Missing notification ID']);
    exit;
}

$notificationService = new NotificationService();
$result = $notificationService->markAsRead($data['notification_id'], $_SESSION['user_id']);

echo json_encode(['success' => $result]);
?>
