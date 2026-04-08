-- ======================================================
-- Migration: Version 1.0.0
-- Initial database setup
-- Date: 2024-01-01
-- ======================================================

-- Create database
CREATE DATABASE IF NOT EXISTS `edutrack_db` 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE `edutrack_db`;

-- Core tables creation
SOURCE ../schema.sql;

-- Insert initial data
SOURCE ../seed-data.sql;

-- Create migration tracking table
CREATE TABLE IF NOT EXISTS `migrations` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `version` VARCHAR(20) NOT NULL,
    `description` TEXT,
    `executed_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `unique_version` (`version`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Record this migration
INSERT INTO `migrations` (`version`, `description`) VALUES 
('1.0.0', 'Initial database setup with core tables and sample data');

-- Create initial indexes for performance
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_users_role ON users(role);
CREATE INDEX idx_students_roll ON students(roll_number);
CREATE INDEX idx_attendance_date ON attendance(date);
CREATE INDEX idx_results_student ON exam_results(student_id);
CREATE INDEX idx_logs_created ON system_logs(created_at);

-- Set initial system settings
INSERT INTO `system_settings` (`setting_key`, `setting_value`, `setting_type`, `description`) VALUES
('db_version', '1.0.0', 'text', 'Database schema version'),
('initialized_at', NOW(), 'text', 'System initialization timestamp');