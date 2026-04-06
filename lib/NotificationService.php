<?php
// lib/NotificationService.php
class NotificationService {
    private $pdo;

    public function __construct() {
        global $pdo;
        $this->pdo = $pdo;
    }

    /**
     * Create notification for user
     */
    public function createNotification($userId, $type, $title, $message, $data = array()) {
        $stmt = $this->pdo->prepare("
            INSERT INTO notifications (user_id, type, title, message, data, created_at) 
            VALUES (?, ?, ?, ?, ?, NOW())
        ");
        return $stmt->execute(array($userId, $type, $title, $message, json_encode($data)));
    }

    /**
     * Get user notifications
     */
    public function getUserNotifications($userId, $limit = 10, $unreadOnly = false) {
        $sql = "SELECT * FROM notifications WHERE user_id = ?";
        $params = array($userId);

        if ($unreadOnly) {
            $sql .= " AND is_read = 0";
        }

        $sql .= " ORDER BY created_at DESC LIMIT ?";
        $params[] = $limit;

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Mark notification as read
     */
    public function markAsRead($notificationId, $userId) {
        $stmt = $this->pdo->prepare("
            UPDATE notifications SET is_read = 1, read_at = NOW() 
            WHERE id = ? AND user_id = ?
        ");
        return $stmt->execute(array($notificationId, $userId));
    }

    /**
     * Mark all notifications as read for user
     */
    public function markAllAsRead($userId) {
        $stmt = $this->pdo->prepare("
            UPDATE notifications SET is_read = 1, read_at = NOW() 
            WHERE user_id = ? AND is_read = 0
        ");
        return $stmt->execute(array($userId));
    }

    /**
     * Get unread count
     */
    public function getUnreadCount($userId) {
        $stmt = $this->pdo->prepare("
            SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0
        ");
        $stmt->execute(array($userId));
        return $stmt->fetchColumn();
    }

    /**
     * Send notification to all users of a specific role
     */
    public function notifyRole($role, $type, $title, $message, $data = array()) {
        $stmt = $this->pdo->prepare("SELECT id FROM users WHERE role = ?");
        $stmt->execute(array($role));
        $users = $stmt->fetchAll();

        foreach ($users as $user) {
            $this->createNotification($user['id'], $type, $title, $message, $data);
        }
        return count($users);
    }

    /**
     * Notify Class Representative about student complaint
     */
    public function notifyCRAboutComplaint($complaintId, $studentId, $crId) {
        $stmt = $this->pdo->prepare("
            SELECT c.*, u.full_name, u.email 
            FROM complaints c 
            JOIN users u ON c.student_id = u.id 
            WHERE c.id = ?
        ");
        $stmt->execute(array($complaintId));
        $complaint = $stmt->fetch();

        return $this->createNotification(
            $crId,
            'complaint_assigned',
            'New Complaint Assigned',
            "A new complaint from {$complaint['full_name']} needs your attention.",
            array(
                'complaint_id' => $complaintId,
                'student_id' => $studentId,
                'category' => $complaint['category'],
                'priority' => $complaint['priority']
            )
        );
    }

    /**
     * Notify student about CR response
     */
    public function notifyStudentAboutCRResponse($studentId, $crId, $complaintId, $message) {
        $stmt = $this->pdo->prepare("SELECT full_name FROM users WHERE id = ?");
        $stmt->execute(array($crId));
        $cr = $stmt->fetch();

        return $this->createNotification(
            $studentId,
            'cr_response',
            'Response from Class Representative',
            "{$cr['full_name']} has responded to your complaint.",
            array(
                'complaint_id' => $complaintId,
                'cr_id' => $crId,
                'message' => $message
            )
        );
    }

    /**
     * Create unified message between any roles
     */
    public function createMessage($senderId, $receiverId, $subject, $message, $complaintId = null) {
        $stmt = $this->pdo->prepare("
            INSERT INTO messages (sender_id, receiver_id, subject, message, complaint_id, created_at) 
            VALUES (?, ?, ?, ?, ?, NOW())
        ");
        $result = $stmt->execute(array($senderId, $receiverId, $subject, $message, $complaintId));

        if ($result) {
            // Notify receiver about new message
            $stmt = $this->pdo->prepare("SELECT full_name FROM users WHERE id = ?");
            $stmt->execute(array($senderId));
            $sender = $stmt->fetch();

            $this->createNotification(
                $receiverId,
                'new_message',
                'New Message Received',
                "{$sender['full_name']} sent you a message: {$subject}",
                array(
                    'message_id' => $this->pdo->lastInsertId(),
                    'sender_id' => $senderId,
                    'subject' => $subject
                )
            );
        }

        return $result;
    }

    /**
     * Get conversation between two users
     */
    public function getConversation($userId1, $userId2, $limit = 50) {
        $stmt = $this->pdo->prepare("
            SELECT m.*, 
                   u1.full_name as sender_name,
                   u2.full_name as receiver_name
            FROM messages m
            JOIN users u1 ON m.sender_id = u1.id
            JOIN users u2 ON m.receiver_id = u2.id
            WHERE (m.sender_id = ? AND m.receiver_id = ?) 
               OR (m.sender_id = ? AND m.receiver_id = ?)
            ORDER BY m.created_at DESC
            LIMIT ?
        ");
        $stmt->execute(array($userId1, $userId2, $userId2, $userId1, $limit));
        return $stmt->fetchAll();
    }

    /**
     * Get CR's assigned students
     */
    public function getCRAssignedStudents($crId) {
        $stmt = $this->pdo->prepare("
            SELECT DISTINCT u.id, u.full_name, u.email, COUNT(c.id) as complaint_count
            FROM users u
            LEFT JOIN complaints c ON u.id = c.student_id
            WHERE u.role = 'student'
            GROUP BY u.id, u.full_name, u.email
            ORDER BY u.full_name
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Delete old notifications (cleanup)
     */
    public function cleanupOldNotifications($days = 30) {
        $stmt = $this->pdo->prepare("
            DELETE FROM notifications 
            WHERE created_at < DATE_SUB(NOW(), INTERVAL ? DAY) AND is_read = 1
        ");
        return $stmt->execute(array($days));
    }
}
?>
