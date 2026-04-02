<?php
// config/permissions.php

class AccessManager {
    /**
     * Defines strict communication rules: [Sender Role] => [Allowed Receiver Roles]
     */
    private static $rules = [
        'student'        => ['cr', 'teacher'],
        'cr'             => ['teacher', 'hod', 'lab_assistant'],
        'teacher'        => ['student', 'teacher', 'cr', 'hod', 'lab_assistant'],
        'hod'            => ['teacher', 'lab_assistant', 'cr'],
        'lab_assistant'  => ['teacher', 'hod']
    ];

    /**
     * Checks if a communication link is allowed according to system rules
     */
    public static function canCommunicate($senderRole, $receiverRole) {
        if (!isset(self::$rules[$senderRole])) return false;
        return in_array($receiverRole, self::$rules[$senderRole]);
    }

    /**
     * Validates if a user can forward a complaint to a specific role
     */
    public static function canForward($senderRole, $targetRole) {
        // Forwarding follows communication rules, but typically upward for CR/Students
        return self::canCommunicate($senderRole, $targetRole);
    }
}
?>
