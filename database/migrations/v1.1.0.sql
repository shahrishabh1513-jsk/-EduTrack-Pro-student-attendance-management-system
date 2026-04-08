-- ======================================================
-- Migration: Version 1.1.0
-- Add new features and improvements
-- Date: 2024-02-15
-- ======================================================

USE `edutrack_db`;

-- Add new columns to students table
ALTER TABLE `students` 
ADD COLUMN `emergency_contact` VARCHAR(20) AFTER `phone`,
ADD COLUMN `blood_group` VARCHAR(5) AFTER `gender`,
ADD COLUMN `aadhar_number` VARCHAR(12) AFTER `enrollment_number`,
ADD INDEX `idx_blood_group` (`blood_group`);

-- Add new columns to faculty table
ALTER TABLE `faculty` 
ADD COLUMN `emergency_contact` VARCHAR(20) AFTER `phone`,
ADD COLUMN `blood_group` VARCHAR(5) AFTER `designation`,
ADD COLUMN `pan_number` VARCHAR(10) AFTER `employee_id`;

-- Create new table for assignments
CREATE TABLE IF NOT EXISTS `assignments` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `course_id` INT NOT NULL,
    `title` VARCHAR(200) NOT NULL,
    `description` TEXT,
    `due_date` DATE NOT NULL,
    `max_marks` INT DEFAULT 100,
    `created_by` INT NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`course_id`) REFERENCES `courses`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`created_by`) REFERENCES `faculty`(`id`) ON DELETE CASCADE,
    INDEX `idx_due_date` (`due_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create table for assignment submissions
CREATE TABLE IF NOT EXISTS `assignment_submissions` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `assignment_id` INT NOT NULL,
    `student_id` INT NOT NULL,
    `submission_date` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `file_path` VARCHAR(255),
    `marks_obtained` INT,
    `feedback` TEXT,
    `status` ENUM('submitted', 'graded', 'late') DEFAULT 'submitted',
    FOREIGN KEY (`assignment_id`) REFERENCES `assignments`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`student_id`) REFERENCES `students`(`id`) ON DELETE CASCADE,
    UNIQUE KEY `unique_submission` (`assignment_id`, `student_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create table for library books
CREATE TABLE IF NOT EXISTS `library_books` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `book_code` VARCHAR(50) UNIQUE NOT NULL,
    `title` VARCHAR(200) NOT NULL,
    `author` VARCHAR(100),
    `publisher` VARCHAR(100),
    `edition` VARCHAR(20),
    `isbn` VARCHAR(20),
    `category` VARCHAR(50),
    `quantity` INT DEFAULT 1,
    `available` INT DEFAULT 1,
    `location` VARCHAR(50),
    `status` ENUM('available', 'issued', 'damaged', 'lost') DEFAULT 'available',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_book_code` (`book_code`),
    INDEX `idx_title` (`title`),
    INDEX `idx_category` (`category`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create table for book issues
CREATE TABLE IF NOT EXISTS `book_issues` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `book_id` INT NOT NULL,
    `student_id` INT NOT NULL,
    `issue_date` DATE NOT NULL,
    `due_date` DATE NOT NULL,
    `return_date` DATE,
    `fine_amount` DECIMAL(10,2) DEFAULT 0,
    `status` ENUM('issued', 'returned', 'overdue') DEFAULT 'issued',
    FOREIGN KEY (`book_id`) REFERENCES `library_books`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`student_id`) REFERENCES `students`(`id`) ON DELETE CASCADE,
    INDEX `idx_issue_date` (`issue_date`),
    INDEX `idx_due_date` (`due_date`),
    INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add new columns to exam_results table
ALTER TABLE `exam_results` 
ADD COLUMN `practical_marks` INT AFTER `external_marks`,
ADD COLUMN `oral_marks` INT AFTER `practical_marks`,
ADD COLUMN `remarks` TEXT AFTER `grade`;

-- Create table for fee structure
CREATE TABLE IF NOT EXISTS `fee_structure` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `course` VARCHAR(100) NOT NULL,
    `semester` INT NOT NULL,
    `tuition_fee` DECIMAL(10,2) NOT NULL,
    `exam_fee` DECIMAL(10,2),
    `library_fee` DECIMAL(10,2),
    `sports_fee` DECIMAL(10,2),
    `other_fees` DECIMAL(10,2),
    `total_fee` DECIMAL(10,2),
    `academic_year` VARCHAR(20),
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `unique_fee` (`course`, `semester`, `academic_year`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create table for fee payments
CREATE TABLE IF NOT EXISTS `fee_payments` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `student_id` INT NOT NULL,
    `receipt_number` VARCHAR(50) UNIQUE NOT NULL,
    `amount` DECIMAL(10,2) NOT NULL,
    `payment_date` DATE NOT NULL,
    `payment_mode` ENUM('cash', 'card', 'netbanking', 'upi') DEFAULT 'cash',
    `transaction_id` VARCHAR(100),
    `payment_for` VARCHAR(100),
    `remarks` TEXT,
    `status` ENUM('paid', 'pending', 'cancelled') DEFAULT 'paid',
    FOREIGN KEY (`student_id`) REFERENCES `students`(`id`) ON DELETE CASCADE,
    INDEX `idx_receipt` (`receipt_number`),
    INDEX `idx_payment_date` (`payment_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add new system settings
INSERT INTO `system_settings` (`setting_key`, `setting_value`, `setting_type`, `description`) VALUES
('library_enabled', 'true', 'boolean', 'Enable library management module'),
('assignment_module', 'true', 'boolean', 'Enable assignments module'),
('fee_module', 'true', 'boolean', 'Enable fee management module'),
('fine_per_day', '10', 'number', 'Library fine per day for overdue books'),
('max_books_issue', '5', 'number', 'Maximum books a student can issue');

-- Record this migration
INSERT INTO `migrations` (`version`, `description`) VALUES 
('1.1.0', 'Added assignments, library management, fee structure modules');

-- Update system version
UPDATE `system_settings` 
SET `setting_value` = '1.1.0' 
WHERE `setting_key` = 'db_version';