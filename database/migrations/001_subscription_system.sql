-- Migration: Subscription System
-- Description: Create tables for subscription management
-- Date: 2025-01-01

-- Create subscription plans table
CREATE TABLE IF NOT EXISTS `subscription_plans` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `display_name` varchar(100) NOT NULL,
  `description` text,
  `price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `currency` varchar(3) DEFAULT 'USD',
  `billing_period` enum('monthly','yearly') DEFAULT 'monthly',
  `max_students` int(11) DEFAULT -1 COMMENT '-1 means unlimited',
  `max_teachers` int(11) DEFAULT -1,
  `max_classes` int(11) DEFAULT -1,
  `features` text COMMENT 'JSON array of features',
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create school subscriptions table
CREATE TABLE IF NOT EXISTS `school_subscriptions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `school_uid` varchar(100) NOT NULL,
  `plan_id` int(11) NOT NULL,
  `status` enum('active','expired','cancelled','trial') DEFAULT 'trial',
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `trial_end_date` date DEFAULT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `last_payment_date` date DEFAULT NULL,
  `next_billing_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `school_uid` (`school_uid`),
  KEY `plan_id` (`plan_id`),
  KEY `status` (`status`),
  CONSTRAINT `fk_subscription_plan` FOREIGN KEY (`plan_id`) REFERENCES `subscription_plans` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create payment transactions table
CREATE TABLE IF NOT EXISTS `payment_transactions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `subscription_id` int(11) NOT NULL,
  `school_uid` varchar(100) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `currency` varchar(3) DEFAULT 'USD',
  `payment_method` varchar(50) DEFAULT NULL,
  `transaction_id` varchar(255) DEFAULT NULL,
  `merchant_reference` varchar(255) DEFAULT NULL,
  `status` enum('pending','completed','failed','refunded') DEFAULT 'pending',
  `payment_date` datetime DEFAULT NULL,
  `metadata` text COMMENT 'JSON metadata',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `subscription_id` (`subscription_id`),
  KEY `school_uid` (`school_uid`),
  KEY `status` (`status`),
  KEY `transaction_id` (`transaction_id`),
  CONSTRAINT `fk_transaction_subscription` FOREIGN KEY (`subscription_id`) REFERENCES `school_subscriptions` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default subscription plans
INSERT INTO `subscription_plans` (`name`, `display_name`, `description`, `price`, `billing_period`, `max_students`, `max_teachers`, `max_classes`, `features`, `is_active`) VALUES
('free', 'Free Plan', 'Perfect for small schools getting started', 0.00, 'monthly', 50, 5, 5, '["basic_dashboard","student_management","teacher_management","class_management"]', 1),
('basic', 'Basic Plan', 'Essential features for growing schools', 150.00, 'monthly', 200, 20, 20, '["basic_dashboard","student_management","teacher_management","class_management","homework_assignments","basic_analytics","email_support"]', 1),
('professional', 'Professional Plan', 'Advanced features for established schools', 400.00, 'monthly', 1000, 100, 50, '["all_basic_features","parent_portal","advanced_analytics","real_time_notifications","attendance_tracking","exam_management","priority_support"]', 1),
('enterprise', 'Enterprise Plan', 'Complete solution for large institutions', 800.00, 'monthly', -1, -1, -1, '["all_professional_features","multi_campus","custom_integrations","api_access","dedicated_support","custom_reports","sla_guarantee"]', 1);

-- Create indexes for better performance
CREATE INDEX idx_school_subscription_active ON school_subscriptions(school_uid, status, end_date);
CREATE INDEX idx_payment_date ON payment_transactions(payment_date);

-- Add subscription_id to existing schools table (if not exists)
-- This assumes schools table exists, adjust if needed
ALTER TABLE `schools` 
ADD COLUMN IF NOT EXISTS `current_subscription_id` int(11) DEFAULT NULL,
ADD COLUMN IF NOT EXISTS `trial_ends_at` date DEFAULT NULL,
ADD KEY `current_subscription_id` (`current_subscription_id`);
