<?php
header('Content-Type: application/json');
require_once '../config/config.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    echo json_encode(array('success' => false, 'message' => 'Unauthorized'));
    exit;
}

require_once '../lib/NotificationService.php';
CSRF::validateRequest(true);

$role = strtolower(trim((string) $_SESSION['role']));
if (in_array($role, array('department_head', 'department head', 'head_of_department'), true)) {
    $role = 'hod';
}
if ($role !== 'hod') {
    echo json_encode(array('success' => false, 'message' => 'Only HOD can broadcast.'));
    exit;
}

$subject = isset($_POST['subject']) ? trim((string) $_POST['subject']) : '';
$message = isset($_POST['message']) ? trim((string) $_POST['message']) : '';
if ($subject === '' || $message === '') {
    echo json_encode(array('success' => false, 'message' => 'Subject and message are required.'));
    exit;
}

$notificationService = new NotificationService();
$sentCount = $notificationService->broadcastAsHOD((int) $_SESSION['user_id'], $subject, $message);

if ($sentCount > 0) {
    echo json_encode(array('success' => true, 'sent_count' => $sentCount));
    exit;
}

$error = trim($notificationService->getLastError());
echo json_encode(array(
    'success' => false,
    'message' => 'Broadcast failed.'
));
?>
