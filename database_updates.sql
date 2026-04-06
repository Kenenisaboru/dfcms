-- Database Updates for Enterprise Features
-- Run these SQL commands to add the necessary tables for the new features

-- Email Queue Table
CREATE TABLE IF NOT EXISTS `email_queue` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `to_email` varchar(255) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `html_body` text NOT NULL,
  `text_body` text NOT NULL,
  `template_data` text DEFAULT NULL,
  `priority` enum('low','normal','high') DEFAULT 'normal',
  `status` enum('queued','sent','failed') DEFAULT 'queued',
  `attempts` int(11) DEFAULT 0,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `processed_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_status_priority` (`status`,`priority`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Email Logs Table
CREATE TABLE IF NOT EXISTS `email_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `to_email` varchar(255) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `status` enum('sent','failed','pending') NOT NULL,
  `error_message` text DEFAULT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_status` (`status`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- User Activity Table
CREATE TABLE IF NOT EXISTS `user_activity` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `action` varchar(100) NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `details` text DEFAULT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_created_at` (`created_at`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- User Sessions Table
CREATE TABLE IF NOT EXISTS `user_sessions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `session_id` varchar(255) NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `expires_at` timestamp NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_session` (`session_id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_expires_at` (`expires_at`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Security Audit Log Table
CREATE TABLE IF NOT EXISTS `security_audit_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `event_type` varchar(100) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `details` text DEFAULT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_event_type` (`event_type`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_created_at` (`created_at`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Rate Limits Table
CREATE TABLE IF NOT EXISTS `rate_limits` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `action` varchar(50) NOT NULL,
  `identifier` varchar(255) NOT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_action_identifier` (`action`,`identifier`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Add Two-Factor Authentication to Users Table
ALTER TABLE `users` 
ADD COLUMN `two_factor_enabled` tinyint(1) DEFAULT 0,
ADD COLUMN `two_factor_secret` varchar(255) DEFAULT NULL,
ADD COLUMN `backup_codes` text DEFAULT NULL,
ADD COLUMN `last_password_change` timestamp NULL DEFAULT NULL,
ADD COLUMN `login_attempts` int(11) DEFAULT 0,
ADD COLUMN `locked_until` timestamp NULL DEFAULT NULL;

-- Add Email Preferences to Users Table
ALTER TABLE `users`
ADD COLUMN `email_notifications` tinyint(1) DEFAULT 1,
ADD COLUMN `notification_preferences` text DEFAULT NULL;

-- Add Security Columns to Complaints Table
ALTER TABLE `complaints`
ADD COLUMN `ip_address` varchar(45) DEFAULT NULL,
ADD COLUMN `user_agent` text DEFAULT NULL,
ADD COLUMN `encrypted_data` text DEFAULT NULL;

-- Create Indexes for Performance
CREATE INDEX idx_complaints_status_created ON complaints(status, created_at);
CREATE INDEX idx_complaints_student_created ON complaints(student_id, created_at);
CREATE INDEX idx_notifications_user_read ON notifications(user_id, is_read);

-- Insert Default Email Templates
INSERT IGNORE INTO `email_templates` (`name`, `subject`, `html_template`, `text_template`) VALUES
('complaint_submitted', 'New Complaint Submitted - #{complaint_id}', 
 '<h3>New Complaint Received</h3><p>Complaint #{complaint_id} has been submitted by {student_name}.</p><p><strong>Category:</strong> {category}</p><p><strong>Priority:</strong> {priority}</p><p><strong>Message:</strong> {message}</p>',
 'New Complaint #{complaint_id} submitted by {student_name}. Category: {category}, Priority: {priority}'),

('complaint_updated', 'Complaint Status Update - #{complaint_id}',
 '<h3>Complaint Status Update</h3><p>Your complaint #{complaint_id} status has been updated to: {status}</p><p><strong>Updated by:</strong> {updated_by}</p><p><strong>Comments:</strong> {comments}</p>',
 'Complaint #{complaint_id} status updated to: {status}. Updated by: {updated_by}'),

('password_reset', 'Password Reset Request - DFCMS',
 '<h3>Password Reset Request</h3><p>A password reset has been requested for your account.</p><p>Click the link below to reset your password:</p><p><a href="{reset_link}">Reset Password</a></p><p>This link expires in 1 hour.</p>',
 'Password reset requested. Click here: {reset_link}. Expires in 1 hour.');

-- Create Triggers for Audit Logging
DELIMITER //

CREATE TRIGGER log_complaint_insert 
AFTER INSERT ON complaints
FOR EACH ROW
BEGIN
    INSERT INTO security_audit_log (event_type, user_id, details)
    VALUES ('complaint_created', NEW.student_id, JSON_OBJECT('complaint_id', NEW.id, 'category', NEW.category));
END//

CREATE TRIGGER log_complaint_update
AFTER UPDATE ON complaints
FOR EACH ROW
BEGIN
    IF OLD.status != NEW.status THEN
        INSERT INTO security_audit_log (event_type, details)
        VALUES ('complaint_status_changed', JSON_OBJECT('complaint_id', NEW.id, 'old_status', OLD.status, 'new_status', NEW.status));
    END IF;
END//

DELIMITER ;

-- Create Views for Monitoring
CREATE OR REPLACE VIEW `system_stats` AS
SELECT 
    'active_users' as metric,
    COUNT(DISTINCT ua.user_id) as value,
    NOW() as calculated_at
FROM user_activity ua 
WHERE ua.created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)

UNION ALL

SELECT 
    'pending_complaints' as metric,
    COUNT(*) as value,
    NOW() as calculated_at
FROM complaints 
WHERE status = 'Pending'

UNION ALL

SELECT 
    'failed_logins_24h' as metric,
    COUNT(*) as value,
    NOW() as calculated_at
FROM security_audit_log 
WHERE event_type = 'login_failed' 
AND created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR);

-- Create Stored Procedures for Maintenance
DELIMITER //

CREATE PROCEDURE CleanupOldSessions()
BEGIN
    DELETE FROM user_sessions WHERE expires_at < NOW();
    SELECT ROW_COUNT() as sessions_cleaned;
END//

CREATE PROCEDURE CleanupOldLogs()
BEGIN
    -- Delete audit logs older than 1 year
    DELETE FROM security_audit_log WHERE created_at < DATE_SUB(NOW(), INTERVAL 1 YEAR);
    
    -- Delete rate limit entries older than 24 hours
    DELETE FROM rate_limits WHERE created_at < DATE_SUB(NOW(), INTERVAL 24 HOUR);
    
    -- Delete old email logs (keep last 90 days)
    DELETE FROM email_logs WHERE created_at < DATE_SUB(NOW(), INTERVAL 90 DAY);
    
    SELECT 'Cleanup completed' as result;
END//

DELIMITER ;
