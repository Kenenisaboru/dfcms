<?php
// lib/EmailService.php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/email_config.php';

class EmailService {
    private $pdo;
    private $smtp_config;
    
    public function __construct() {
        global $pdo;
        $this->pdo = $pdo;
        $this->smtp_config = EmailConfig::getSmtpConfig();
    }
    
    /**
     * Send email using PHPMailer
     */
    public function sendEmail($to, $subject, $htmlBody, $textBody, $templateData = array()) {
        try {
            // Create email log entry
            $logId = $this->logEmail($to, $subject, 'pending');
            
            // Process template variables
            $processedSubject = $this->processTemplate($subject, $templateData);
            $processedHtml = $this->processTemplate($htmlBody, $templateData);
            $processedText = $this->processTemplate($textBody, $templateData);
            
            // Send email using PHPMailer
            $result = $this->sendWithPHPMailer($to, $processedSubject, $processedHtml, $processedText);
            
            // Update log
            $this->updateEmailLog($logId, $result ? 'sent' : 'failed', $result ? null : 'PHPMailer error');
            
            return $result;
            
        } catch (Exception $e) {
            $this->updateEmailLog($logId, 'failed', $e->getMessage());
            return false;
        }
    }
    
    /**
     * Queue email for bulk processing
     */
    public function queueEmail($to, $subject, $htmlBody, $textBody, $templateData = array(), $priority = 'normal') {
        $stmt = $this->pdo->prepare("
            INSERT INTO email_queue (to_email, subject, html_body, text_body, template_data, priority, status, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, 'queued', NOW())
        ");
        
        $serializedData = json_encode($templateData);
        return $stmt->execute(array($to, $subject, $htmlBody, $textBody, $serializedData, $priority));
    }
    
    /**
     * Process email queue
     */
    public function processQueue($limit = 10) {
        $stmt = $this->pdo->prepare("
            SELECT * FROM email_queue 
            WHERE status = 'queued' 
            ORDER BY priority DESC, created_at ASC 
            LIMIT ?
        ");
        $stmt->execute(array($limit));
        $emails = $stmt->fetchAll();
        
        $processed = 0;
        foreach ($emails as $email) {
            $templateData = json_decode($email['template_data'], true);
            $result = $this->sendEmail($email['to_email'], $email['subject'], $email['html_body'], $email['text_body'], $templateData);
            
            // Update queue status
            $updateStmt = $this->pdo->prepare("
                UPDATE email_queue SET status = ?, processed_at = NOW() WHERE id = ?
            ");
            $updateStmt->execute(array($result ? 'sent' : 'failed', $email['id']));
            
            if ($result) $processed++;
        }
        
        return $processed;
    }
    
    /**
     * Send notification based on user preferences
     */
    public function sendNotification($userId, $templateType, $templateData = array()) {
        // Get user info and preferences
        $stmt = $this->pdo->prepare("SELECT email, role FROM users WHERE id = ?");
        $stmt->execute(array($userId));
        $user = $stmt->fetch();
        
        if (!$user) return false;
        
        // Check notification preferences
        $preferences = EmailConfig::getNotificationPreferences($user['role']);
        if (!isset($preferences[$templateType]) || !$preferences[$templateType]) {
            return false; // User has opted out of this notification type
        }
        
        // Get template
        $template = EmailConfig::getTemplate($templateType);
        if (!$template) return false;
        
        // Read template files
        $htmlBody = file_get_contents($template['html']);
        $textBody = file_get_contents($template['text']);
        
        // Add user data to template
        $templateData['user_email'] = $user['email'];
        $templateData['user_role'] = $user['role'];
        
        return $this->sendEmail($user['email'], $template['subject'], $htmlBody, $textBody, $templateData);
    }
    
    /**
     * Process template variables
     */
    private function processTemplate($template, $data) {
        foreach ($data as $key => $value) {
            $template = str_replace('{' . $key . '}', $value, $template);
        }
        return $template;
    }
    
    /**
     * Send email using PHPMailer
     */
    private function sendWithPHPMailer($to, $subject, $htmlBody, $textBody) {
        // Include PHPMailer (you'll need to install it via composer)
        if (!class_exists('PHPMailer\PHPMailer\PHPMailer')) {
            throw new Exception('PHPMailer not installed. Please run: composer require phpmailer/phpmailer');
        }
        
        $mail = new PHPMailer\PHPMailer\PHPMailer(true);
        
        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host = $this->smtp_config['host'];
            $mail->SMTPAuth = true;
            $mail->Username = $this->smtp_config['username'];
            $mail->Password = $this->smtp_config['password'];
            $mail->SMTPSecure = $this->smtp_config['encryption'];
            $mail->Port = $this->smtp_config['port'];
            
            // Recipients
            $mail->setFrom($this->smtp_config['from_email'], $this->smtp_config['from_name']);
            $mail->addAddress($to);
            
            // Content
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $htmlBody;
            $mail->AltBody = $textBody;
            
            return $mail->send();
            
        } catch (Exception $e) {
            throw new Exception('Email sending failed: ' . $mail->ErrorInfo);
        }
    }
    
    /**
     * Log email for tracking
     */
    private function logEmail($to, $subject, $status) {
        $stmt = $this->pdo->prepare("
            INSERT INTO email_logs (to_email, subject, status, created_at) 
            VALUES (?, ?, ?, NOW())
        ");
        $stmt->execute(array($to, $subject, $status));
        return $this->pdo->lastInsertId();
    }
    
    /**
     * Update email log
     */
    private function updateEmailLog($logId, $status, $errorMessage = null) {
        $stmt = $this->pdo->prepare("
            UPDATE email_logs SET status = ?, error_message = ?, updated_at = NOW() 
            WHERE id = ?
        ");
        $stmt->execute(array($status, $errorMessage, $logId));
    }
    
    /**
     * Get email statistics
     */
    public function getEmailStats($startDate = null, $endDate = null) {
        $sql = "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN status = 'sent' THEN 1 ELSE 0 END) as sent,
                    SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as failed
                FROM email_logs 
                WHERE created_at BETWEEN COALESCE(?, DATE_SUB(NOW(), INTERVAL 30 DAY)) AND COALESCE(?, NOW())";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array($startDate, $endDate));
        return $stmt->fetch();
    }
}

// Auto-process queue (call this periodically via cron)
function processEmailQueue() {
    $emailService = new EmailService();
    return $emailService->processQueue(20);
}
?>
