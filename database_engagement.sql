-- Database Updates for Engagement Features
-- Run these SQL commands to add the necessary tables

-- User Badges Table
CREATE TABLE IF NOT EXISTS `user_badges` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `badge_type` varchar(50) NOT NULL,
  `awarded_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_user_badge` (`user_id`, `badge_type`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Knowledge Base Table
CREATE TABLE IF NOT EXISTS `knowledge_base` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category` varchar(50) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `views` int(11) DEFAULT 0,
  `is_published` tinyint(1) DEFAULT 1,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_category` (`category`),
  FULLTEXT KEY `ft_search` (`title`, `content`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Help Interactions Table
CREATE TABLE IF NOT EXISTS `help_interactions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `help_key` varchar(100) NOT NULL,
  `interacted_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert some initial Knowledge Base articles
INSERT IGNORE INTO `knowledge_base` (`category`, `title`, `content`) VALUES
('general', 'How to submit a complaint', 'To submit a complaint, go to the Dashboard and click the "New Complaint" button. Fill in the category, priority, and description of your issue.'),
('technical', 'Password Reset Guide', 'If you forget your password, use the "Forgot Password" link on the login page. You will receive an email with instructions.'),
('academic', 'Grading Concerns', 'For concerns related to grading, please first contact your course teacher through the platform.'),
('facilities', 'Laboratory Access', 'Laboratory issues should be reported under the "Facilities" category with high priority if it affects practical sessions.');
