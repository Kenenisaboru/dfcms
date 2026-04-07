<?php
header('Content-Type: application/json');
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(array('success' => false, 'message' => 'Unauthorized'));
    exit;
}

require_once '../config/database.php';
require_once '../lib/NotificationService.php';

$currentUserId = (int) $_SESSION['user_id'];
$receiverId = isset($_GET['receiver_id']) ? (int) $_GET['receiver_id'] : 0;
$afterId = isset($_GET['after_id']) ? (int) $_GET['after_id'] : 0;

if ($receiverId <= 0) {
    echo json_encode(array('success' => false, 'message' => 'Missing receiver_id'));
    exit;
}

$notificationService = new NotificationService();
if (!$notificationService->canUsersChat($currentUserId, $receiverId)) {
    echo json_encode(array('success' => false, 'message' => 'Forbidden'));
    exit;
}

$stmt = $pdo->prepare("
    SELECT m.id, m.sender_id, m.receiver_id, m.message, m.created_at, u.full_name AS sender_name
    FROM messages m
    JOIN users u ON u.id = m.sender_id
    WHERE ((m.sender_id = ? AND m.receiver_id = ?) OR (m.sender_id = ? AND m.receiver_id = ?))
      AND m.id > ?
    ORDER BY m.id ASC
    LIMIT 50
");
$stmt->execute(array($currentUserId, $receiverId, $receiverId, $currentUserId, $afterId));
$rows = $stmt->fetchAll();

foreach ($rows as &$row) {
    $row['created_time'] = date('h:i A', strtotime($row['created_at']));
}

echo json_encode(array(
    'success' => true,
    'messages' => $rows
));
?>
