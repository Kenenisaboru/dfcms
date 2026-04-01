<?php
// config/notifications.php

class NotificationManager {
    /**
     * Creates a real-time notification record in the database
     */
    public static function send($pdo, $receiverId, $message, $link = null) {
        try {
            $stmt = $pdo->prepare("INSERT INTO notifications (user_id, message, link, is_read, created_at) VALUES (?, ?, ?, 0, NOW())");
            return $stmt->execute([$receiverId, $message, $link]);
        } catch (Exception $e) {
            // Log error but don't crash the main process
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
