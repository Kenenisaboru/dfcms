-- Additional Database Updates for Workflow and Engagement
-- Run these SQL commands to add the missing tables

-- Workflow Steps Table
CREATE TABLE IF NOT EXISTS `workflow_steps` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `role_key` varchar(50) NOT NULL,
  `step_order` int(11) NOT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_step_order` (`step_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Ensure standard roles are in place if not exists
-- (This assumes roles are managed via enum in users table as per database.sql)

-- Add missing columns to knowledge_base if needed
-- (Already included in previous database_engagement.sql but as a safety check)
ALTER TABLE `knowledge_base` ADD COLUMN IF NOT EXISTS `is_published` tinyint(1) DEFAULT 1;

-- Seed initial workflow steps
INSERT IGNORE INTO `workflow_steps` (`role_key`, `step_order`) VALUES
('cr', 1),
('teacher', 2),
('hod', 3);
