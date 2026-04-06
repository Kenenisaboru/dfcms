-- Database Updates for Notifications and CR-Student Communication
-- Run these SQL commands to add the necessary tables

-- Notifications Table
CREATE TABLE IF NOT EXISTS `notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `type` varchar(50) NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `data` json DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `read_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_user_notifications` (`user_id`, `is_read`),
  KEY `idx_created_at` (`created_at`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- CR-Student Messages Table
CREATE TABLE IF NOT EXISTS `cr_student_messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cr_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `receiver_id` int(11) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `complaint_id` int(11) DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `read_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_cr_student` (`cr_id`, `student_id`),
  KEY `idx_created_at` (`created_at`),
  FOREIGN KEY (`cr_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`student_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`sender_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`receiver_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`complaint_id`) REFERENCES `complaints`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Notification Types
INSERT IGNORE INTO `notifications` (`user_id`, `type`, `title`, `message`, `data`) VALUES
(1, 'system', 'Welcome to DFCMS', 'Your account has been successfully created.', '{"welcome": true}');

-- Add indexes for better performance
CREATE INDEX IF NOT EXISTS `idx_notifications_type` ON `notifications`(`type`);
CREATE INDEX IF NOT EXISTS `idx_messages_sender` ON `cr_student_messages`(`sender_id`);
CREATE INDEX IF NOT EXISTS `idx_messages_receiver` ON `cr_student_messages`(`receiver_id`);
