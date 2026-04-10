<?php
// lib/NotificationService.php
require_once __DIR__ . '/DebugLogger.php';
class NotificationService {
    private $pdo;
    private $lastError = '';
    private $columnExistsCache = array();
    private $allowedRolePairs = array(
        array('student', 'teacher'),
        array('teacher', 'cr'),
        array('cr', 'student'),
        array('hod', 'cr'),
        array('hod', 'student'),
        array('hod', 'teacher'),
        array('hod', 'lab_assistant'),
        array('lab_assistant', 'cr'),
        array('lab_assistant', 'teacher')
    );

    public function __construct() {
        global $pdo;
        $this->pdo = $pdo;
    }

    public function getLastError() {
        return $this->lastError;
    }

    private function setLastError($message) {
        $this->lastError = (string) $message;
    }

    private function tableHasColumn($tableName, $columnName) {
        $key = $tableName . '.' . $columnName;
        if (isset($this->columnExistsCache[$key])) {
            return $this->columnExistsCache[$key];
        }

        $safeColumnName = $this->pdo->quote($columnName);
        $stmt = $this->pdo->query("SHOW COLUMNS FROM `$tableName` LIKE $safeColumnName");
        $exists = (bool) $stmt->fetch();
        $this->columnExistsCache[$key] = $exists;
        return $exists;
    }

    /**
     * Ensure required table exists (no runtime schema mutations in production).
     */
    private function ensureMessagesTableExists() {
        if (!isset($this->pdo) || !$this->pdo) {
            return false;
        }

        try {
            $stmt = $this->pdo->query("SHOW TABLES LIKE 'messages'");
            $exists = (bool) $stmt->fetch();
            if (!$exists) {
                $this->setLastError('Messages table is missing. Run database migrations.');
            }
            return $exists;
        } catch (Throwable $e) {
            $this->setLastError('Failed to verify messages table.');
            return false;
        }
    }

    /**
     * Ensure required table exists (no runtime schema mutations in production).
     */
    private function ensureNotificationsTableExists() {
        if (!isset($this->pdo) || !$this->pdo) {
            return false;
        }

        try {
            $stmt = $this->pdo->query("SHOW TABLES LIKE 'notifications'");
            $exists = (bool) $stmt->fetch();
            if (!$exists) {
                $this->setLastError('Notifications table is missing. Run database migrations.');
            }
            return $exists;
        } catch (Throwable $e) {
            $this->setLastError('Failed to verify notifications table.');
            return false;
        }
    }

    /**
     * Normalize role labels from DB/session to canonical role keys.
     */
    private function normalizeRole($role) {
        $role = strtolower(trim((string) $role));
        $aliases = array(
            'class_representative' => 'cr',
            'class representative' => 'cr',
            'representative' => 'cr',
            'department_head' => 'hod',
            'department head' => 'hod',
            'head_of_department' => 'hod',
            'lab assistant' => 'lab_assistant'
        );

        return isset($aliases[$role]) ? $aliases[$role] : $role;
    }

    /**
     * Return true if two roles are allowed to chat.
     */
    public function canRolesChat($roleA, $roleB) {
        $roleA = $this->normalizeRole($roleA);
        $roleB = $this->normalizeRole($roleB);

        foreach ($this->allowedRolePairs as $pair) {
            if (
                ($pair[0] === $roleA && $pair[1] === $roleB) ||
                ($pair[0] === $roleB && $pair[1] === $roleA)
            ) {
                return true;
            }
        }
        return false;
    }

    /**
     * Return true if two users are allowed to chat.
     */
    public function canUsersChat($userIdA, $userIdB) {
        $stmt = $this->pdo->prepare("SELECT id, role FROM users WHERE id IN (?, ?)");
        $stmt->execute(array($userIdA, $userIdB));
        $users = $stmt->fetchAll();

        if (count($users) !== 2) {
            return false;
        }

        return $this->canRolesChat(
            $this->normalizeRole($users[0]['role']),
            $this->normalizeRole($users[1]['role'])
        );
    }

    /**
     * Contacts the user is allowed to message.
     */
    public function getChatContacts($userId) {
        $stmt = $this->pdo->prepare("SELECT role FROM users WHERE id = ?");
        $stmt->execute(array($userId));
        $currentUser = $stmt->fetch();

        if (!$currentUser || !isset($currentUser['role'])) {
            return array();
        }

        $currentRole = $this->normalizeRole($currentUser['role']);

        $allowedRoles = array();
        foreach ($this->allowedRolePairs as $pair) {
            if ($pair[0] === $currentRole) {
                $allowedRoles[] = $pair[1];
            } elseif ($pair[1] === $currentRole) {
                $allowedRoles[] = $pair[0];
            }
        }

        $allowedRoles = array_values(array_unique($allowedRoles));
        if (empty($allowedRoles)) {
            return array();
        }

        $stmt = $this->pdo->prepare("
            SELECT id, full_name, role
            FROM users
            WHERE id != ?
            ORDER BY full_name ASC
        ");
        $stmt->execute(array($userId));
        $allUsers = $stmt->fetchAll();

        $filtered = array();
        foreach ($allUsers as $user) {
            if (in_array($this->normalizeRole($user['role']), $allowedRoles, true)) {
                $filtered[] = $user;
            }
        }

        return $filtered;
    }

    /**
     * Broadcast a message as HOD to all other users.
     * Returns number of successfully sent direct messages.
     */
    public function broadcastAsHOD($hodSenderId, $subject, $message) {
        $this->setLastError('');

        if (!isset($this->pdo) || !$this->pdo) {
            $this->setLastError('Database connection not available.');
            return 0;
        }

        $stmt = $this->pdo->prepare("SELECT role FROM users WHERE id = ?");
        $stmt->execute(array($hodSenderId));
        $sender = $stmt->fetch();

        if (!$sender || $this->normalizeRole($sender['role']) !== 'hod') {
            $this->setLastError('Only Department Head (HOD) can broadcast messages.');
            return 0;
        }

        $stmt = $this->pdo->prepare("SELECT id FROM users WHERE id != ?");
        $stmt->execute(array($hodSenderId));
        $receivers = $stmt->fetchAll();

        $sentCount = 0;
        foreach ($receivers as $receiver) {
            $receiverId = (int) $receiver['id'];
            if ($this->createMessage($hodSenderId, $receiverId, $subject, $message)) {
                $sentCount++;
            }
        }

        return $sentCount;
    }

    /**
     * Create notification for user
     */
    public function createNotification($userId, $type, $title, $message, $data = array()) {
        $this->setLastError('');
        // #region agent log
        DebugLogger::log('baseline', 'H1', 'lib/NotificationService.php:createNotification', 'notification_create_attempt', array('userId' => (int)$userId, 'type' => (string)$type));
        // #endregion
        if (!isset($this->pdo) || !$this->pdo) {
            $this->setLastError('Database connection not available.');
            return false;
        }
        if (!$this->ensureNotificationsTableExists()) {
            if ($this->lastError === '') {
                $this->setLastError('Notifications table is missing and could not be created.');
            }
            return false;
        }

        $columns = array('user_id', 'message');
        $values = array($userId, $message);

        if ($this->tableHasColumn('notifications', 'type')) {
            $columns[] = 'type';
            $values[] = $type;
        }
        if ($this->tableHasColumn('notifications', 'title')) {
            $columns[] = 'title';
            $values[] = $title;
        }
        if ($this->tableHasColumn('notifications', 'data')) {
            $columns[] = 'data';
            $values[] = empty($data) ? null : json_encode($data);
        }
        if ($this->tableHasColumn('notifications', 'link')) {
            $columns[] = 'link';
            $values[] = isset($data['link']) ? $data['link'] : (isset($data['message_id']) ? 'messages.php' : '');
        }

        $placeholders = array_fill(0, count($values), '?');
        if ($this->tableHasColumn('notifications', 'created_at')) {
            $columns[] = 'created_at';
            $placeholders[] = 'NOW()';
        }

        $sql = "INSERT INTO notifications (" . implode(', ', $columns) . ") VALUES (" . implode(', ', $placeholders) . ")";
        $stmt = $this->pdo->prepare($sql);
        $result = $stmt->execute($values);
        if (!$result) {
            $err = $stmt->errorInfo();
            $this->setLastError(isset($err[2]) ? $err[2] : 'Unknown notification insert error.');
        }
        // #region agent log
        DebugLogger::log('baseline', 'H1', 'lib/NotificationService.php:createNotification', 'notification_create_result', array('ok' => (bool)$result, 'error' => $this->lastError));
        // #endregion
        return $result;
    }

    /**
     * Get user notifications
     */
    public function getUserNotifications($userId, $limit = 10, $unreadOnly = false) {
        if (!$this->ensureNotificationsTableExists()) {
            return array();
        }

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
        if (!$this->ensureNotificationsTableExists()) {
            return false;
        }

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
        if (!$this->ensureNotificationsTableExists()) {
            return false;
        }

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
        if (!$this->ensureNotificationsTableExists()) {
            return 0;
        }

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
        $this->setLastError('');
        // #region agent log
        DebugLogger::log('baseline', 'H2', 'lib/NotificationService.php:createMessage', 'message_create_attempt', array('senderId' => (int)$senderId, 'receiverId' => (int)$receiverId, 'subjectLen' => strlen((string)$subject)));
        // #endregion

        if (!isset($this->pdo) || !$this->pdo) {
            $this->setLastError('Database connection not available.');
            return false;
        }

        if (!$this->ensureMessagesTableExists()) {
            if ($this->lastError === '') {
                $this->setLastError('Messages table is missing and could not be created.');
            }
            return false;
        }

        if (!$this->canUsersChat($senderId, $receiverId)) {
            $this->setLastError('Role policy blocked this chat.');
            // #region agent log
            DebugLogger::log('baseline', 'H2', 'lib/NotificationService.php:createMessage', 'message_blocked_role_policy', array('senderId' => (int)$senderId, 'receiverId' => (int)$receiverId));
            // #endregion
            return false;
        }

        try {
            $columns = array('sender_id', 'receiver_id', 'message');
            $values = array($senderId, $receiverId, $message);

            if ($this->tableHasColumn('messages', 'subject')) {
                $columns[] = 'subject';
                $values[] = $subject;
            }
            if ($this->tableHasColumn('messages', 'complaint_id')) {
                $columns[] = 'complaint_id';
                $values[] = $complaintId;
            }
            if ($this->tableHasColumn('messages', 'created_at')) {
                $columns[] = 'created_at';
            }

            $placeholders = array_fill(0, count($values), '?');
            if ($this->tableHasColumn('messages', 'created_at')) {
                $placeholders[] = 'NOW()';
            }

            $sql = "INSERT INTO messages (" . implode(', ', $columns) . ") VALUES (" . implode(', ', $placeholders) . ")";
            $stmt = $this->pdo->prepare($sql);
            $result = $stmt->execute($values);

            if (!$result) {
                $err = $stmt->errorInfo();
                $this->setLastError(isset($err[2]) ? $err[2] : 'Unknown database error during insert.');
            }
        } catch (Throwable $e) {
            $this->setLastError($e->getMessage());
            $result = false;
        }
        // #region agent log
        DebugLogger::log('baseline', 'H2', 'lib/NotificationService.php:createMessage', 'message_insert_result', array('ok' => (bool)$result, 'error' => $this->lastError));
        // #endregion

        if ($result) {
            // Notify receiver about new message
            $stmt = $this->pdo->prepare("SELECT full_name FROM users WHERE id = ?");
            $stmt->execute(array($senderId));
            $sender = $stmt->fetch();

            $notificationCreated = $this->createNotification(
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
            if (!$notificationCreated && $this->lastError === '') {
                $this->setLastError('Message saved, but notification could not be created.');
            }
        }

        return $result;
    }

    /**
     * Get conversation between two users
     */
    public function getConversation($userId1, $userId2, $limit = 50) {
        if (!$this->ensureMessagesTableExists()) {
            return array();
        }

        if (!$this->canUsersChat($userId1, $userId2)) {
            return array();
        }

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
     * Get unread direct-message count for a user.
     */
    public function getUnreadMessageCount($userId) {
        if (!$this->ensureMessagesTableExists()) {
            return 0;
        }

        $stmt = $this->pdo->prepare("
            SELECT COUNT(*) FROM messages
            WHERE receiver_id = ? AND is_read = 0
        ");
        $stmt->execute(array($userId));
        return (int) $stmt->fetchColumn();
    }

    /**
     * Mark conversation messages as read for current user.
     */
    public function markConversationAsRead($currentUserId, $otherUserId) {
        if (!$this->ensureMessagesTableExists()) {
            return false;
        }

        $stmt = $this->pdo->prepare("
            UPDATE messages
            SET is_read = 1
            WHERE sender_id = ? AND receiver_id = ? AND is_read = 0
        ");
        return $stmt->execute(array($otherUserId, $currentUserId));
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
