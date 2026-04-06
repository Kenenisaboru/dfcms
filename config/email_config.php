<?php
// config/email_config.php
class EmailConfig {
    // SMTP Configuration
    private static $smtp_config = array(
        'host' => getenv('SMTP_HOST') ?: 'smtp.gmail.com',
        'port' => getenv('SMTP_PORT') ?: 587,
        'username' => getenv('SMTP_USERNAME') ?: '',
        'password' => getenv('SMTP_PASSWORD') ?: '',
        'encryption' => getenv('SMTP_ENCRYPTION') ?: 'tls',
        'from_email' => getenv('FROM_EMAIL') ?: 'noreply@dfcms.university.edu',
        'from_name' => getenv('FROM_NAME') ?: 'DFCMS System'
    );

    // Email Templates
    private static $templates = array(
        'complaint_submitted' => array(
            'subject' => 'New Complaint Submitted - #{complaint_id}',
            'html' => 'templates/complaint_submitted.html',
            'text' => 'templates/complaint_submitted.txt'
        ),
        'complaint_updated' => array(
            'subject' => 'Complaint Status Update - #{complaint_id}',
            'html' => 'templates/complaint_updated.html',
            'text' => 'templates/complaint_updated.txt'
        ),
        'complaint_resolved' => array(
            'subject' => 'Complaint Resolved - #{complaint_id}',
            'html' => 'templates/complaint_resolved.html',
            'text' => 'templates/complaint_resolved.txt'
        ),
        'password_reset' => array(
            'subject' => 'Password Reset Request - DFCMS',
            'html' => 'templates/password_reset.html',
            'text' => 'templates/password_reset.txt'
        ),
        'account_locked' => array(
            'subject' => 'Account Security Alert - DFCMS',
            'html' => 'templates/account_locked.html',
            'text' => 'templates/account_locked.txt'
        )
    );

    public static function getSmtpConfig() {
        return self::$smtp_config;
    }

    public static function getTemplate($type) {
        return isset(self::$templates[$type]) ? self::$templates[$type] : null;
    }

    public static function getNotificationPreferences($role) {
        $preferences = array(
            'student' => array(
                'complaint_submitted' => true,
                'complaint_updated' => true,
                'complaint_resolved' => true,
                'password_reset' => true,
                'account_locked' => true
            ),
            'cr' => array(
                'complaint_submitted' => true,
                'complaint_updated' => true,
                'complaint_resolved' => true,
                'password_reset' => true,
                'account_locked' => true
            ),
            'teacher' => array(
                'complaint_submitted' => true,
                'complaint_updated' => true,
                'complaint_resolved' => true,
                'password_reset' => true,
                'account_locked' => true
            ),
            'lab_assistant' => array(
                'complaint_updated' => true,
                'complaint_resolved' => true,
                'password_reset' => true,
                'account_locked' => true
            ),
            'hod' => array(
                'complaint_updated' => true,
                'complaint_resolved' => true,
                'password_reset' => true,
                'account_locked' => true
            )
        );

        return isset($preferences[$role]) ? $preferences[$role] : array();
    }
}
?>
