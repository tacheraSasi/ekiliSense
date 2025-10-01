-- Migration: Security Improvements
-- Description: Add login logs, session tracking, and security tables
-- Date: 2025-01-01

-- Create login logs table
CREATE TABLE IF NOT EXISTS `login_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `school_uid` varchar(100) DEFAULT NULL,
  `teacher_id` int(11) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `user_agent` text,
  `status` enum('success','failed','blocked') DEFAULT 'failed',
  `failure_reason` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `school_uid` (`school_uid`),
  KEY `teacher_id` (`teacher_id`),
  KEY `email` (`email`),
  KEY `status` (`status`),
  KEY `created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create session tracking table
CREATE TABLE IF NOT EXISTS `active_sessions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `session_id` varchar(128) NOT NULL,
  `school_uid` varchar(100) DEFAULT NULL,
  `teacher_id` int(11) DEFAULT NULL,
  `user_type` enum('school','teacher','parent') NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `user_agent` text,
  `last_activity` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `session_id` (`session_id`),
  KEY `school_uid` (`school_uid`),
  KEY `teacher_id` (`teacher_id`),
  KEY `last_activity` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create password reset tokens table
CREATE TABLE IF NOT EXISTS `password_reset_tokens` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `token` varchar(64) NOT NULL,
  `user_type` enum('school','teacher') NOT NULL,
  `expires_at` datetime NOT NULL,
  `used` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `token` (`token`),
  KEY `email` (`email`),
  KEY `expires_at` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create two-factor authentication table
CREATE TABLE IF NOT EXISTS `two_factor_auth` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `school_uid` varchar(100) DEFAULT NULL,
  `teacher_id` int(11) DEFAULT NULL,
  `secret` varchar(255) NOT NULL,
  `backup_codes` text COMMENT 'JSON array of backup codes',
  `enabled` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `school_uid` (`school_uid`),
  KEY `teacher_id` (`teacher_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create security settings table
CREATE TABLE IF NOT EXISTS `security_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `school_uid` varchar(100) NOT NULL,
  `require_2fa` tinyint(1) DEFAULT 0,
  `session_timeout` int(11) DEFAULT 3600 COMMENT 'Session timeout in seconds',
  `max_login_attempts` int(11) DEFAULT 5,
  `lockout_duration` int(11) DEFAULT 900 COMMENT 'Lockout duration in seconds',
  `password_expiry_days` int(11) DEFAULT 90,
  `require_strong_password` tinyint(1) DEFAULT 1,
  `allow_remember_me` tinyint(1) DEFAULT 1,
  `ip_whitelist` text COMMENT 'JSON array of whitelisted IPs',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `school_uid` (`school_uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add indexes for performance
CREATE INDEX idx_login_logs_lookup ON login_logs(email, status, created_at);
CREATE INDEX idx_sessions_cleanup ON active_sessions(last_activity);

-- Clean up expired sessions and tokens (run this periodically via cron)
-- DELETE FROM active_sessions WHERE last_activity < DATE_SUB(NOW(), INTERVAL 24 HOUR);
-- DELETE FROM password_reset_tokens WHERE expires_at < NOW() OR used = 1;
