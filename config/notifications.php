<?php
// config/notifications.php

class NotificationManager {
    /**
     * Creates a real-time notification record in the database
     */
    public static function send($pdo, $receiverId, $message, $link = null, $type = 'general', $title = 'System Update') {
        try {
            require_once __DIR__ . '/../lib/NotificationService.php';
            $service = new NotificationService();
            return $service->createNotification($receiverId, $type, $title, $message, ['link' => $link]);
        } catch (Exception $e) {
            error_log("Notification Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Fetches unread notifications for a specific user
     */
    public static function getUnread($pdo, $userId) {
        $stmt = $pdo->prepare("SELECT * FROM notifications WHERE user_id = ? AND is_read = 0 ORDER BY created_at DESC");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }
}
?>
