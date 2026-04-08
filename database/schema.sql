-- ======================================================
-- EduTrack Pro Database Schema
-- Version: 2.0.0
-- Date: 2024-03-20
-- ======================================================

-- Create database if not exists
CREATE DATABASE IF NOT EXISTS `edutrack_db` 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE `edutrack_db`;

-- ======================================================
-- Users Table
-- ======================================================
CREATE TABLE IF NOT EXISTS `users` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `username` VARCHAR(100) UNIQUE NOT NULL,
    `email` VARCHAR(100) UNIQUE NOT NULL,
    `password` VARCHAR(255) NOT NULL,
    `role` ENUM('student', 'faculty', 'admin') DEFAULT 'student',
    `full_name` VARCHAR(100) NOT NULL,
    `phone` VARCHAR(20),
    `address` TEXT,
    `profile_pic` VARCHAR(255) DEFAULT 'default.png',
    `status` ENUM('active', 'inactive') DEFAULT 'active',
    `last_login` TIMESTAMP NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_email` (`email`),
    INDEX `idx_role` (`role`),
    INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ======================================================
-- Students Table
-- ======================================================
CREATE TABLE IF NOT EXISTS `students` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `user_id` INT NOT NULL,
    `roll_number` VARCHAR(50) UNIQUE NOT NULL,
    `enrollment_number` VARCHAR(50) UNIQUE NOT NULL,
    `course` VARCHAR(100) NOT NULL,
    `semester` INT NOT NULL,
    `batch` VARCHAR(20) NOT NULL,
    `father_name` VARCHAR(100),
    `mother_name` VARCHAR(100),
    `date_of_birth` DATE,
    `gender` ENUM('Male', 'Female', 'Other'),
    `nationality` VARCHAR(50) DEFAULT 'Indian',
    `admission_date` DATE,
    `cgpa` DECIMAL(3,2) DEFAULT 0.00,
    `total_attendance` INT DEFAULT 0,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    INDEX `idx_roll_number` (`roll_number`),
    INDEX `idx_course` (`course`),
    INDEX `idx_semester` (`semester`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ======================================================
-- Faculty Table
-- ======================================================
CREATE TABLE IF NOT EXISTS `faculty` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `user_id` INT NOT NULL,
    `employee_id` VARCHAR(50) UNIQUE NOT NULL,
    `department` VARCHAR(100) NOT NULL,
    `designation` VARCHAR(100) NOT NULL,
    `qualification` VARCHAR(200),
    `specialization` VARCHAR(200),
    `experience` INT DEFAULT 0,
    `joining_date` DATE,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    INDEX `idx_employee_id` (`employee_id`),
    INDEX `idx_department` (`department`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ======================================================
-- Departments Table
-- ======================================================
CREATE TABLE IF NOT EXISTS `departments` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `name` VARCHAR(100) UNIQUE NOT NULL,
    `code` VARCHAR(10) UNIQUE NOT NULL,
    `hod` VARCHAR(100),
    `established_year` YEAR,
    `email` VARCHAR(100),
    `phone` VARCHAR(20),
    `description` TEXT,
    `status` ENUM('active', 'inactive') DEFAULT 'active',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ======================================================
-- Courses Table
-- ======================================================
CREATE TABLE IF NOT EXISTS `courses` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `course_code` VARCHAR(20) UNIQUE NOT NULL,
    `course_name` VARCHAR(100) NOT NULL,
    `credits` INT NOT NULL,
    `semester` INT NOT NULL,
    `department` VARCHAR(100),
    `description` TEXT,
    `status` ENUM('active', 'inactive') DEFAULT 'active',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_course_code` (`course_code`),
    INDEX `idx_semester` (`semester`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ======================================================
-- Course Assignments Table
-- ======================================================
CREATE TABLE IF NOT EXISTS `course_assignments` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `course_id` INT NOT NULL,
    `faculty_id` INT NOT NULL,
    `semester` INT NOT NULL,
    `academic_year` VARCHAR(20),
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`course_id`) REFERENCES `courses`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`faculty_id`) REFERENCES `faculty`(`id`) ON DELETE CASCADE,
    UNIQUE KEY `unique_assignment` (`course_id`, `faculty_id`, `semester`),
    INDEX `idx_semester` (`semester`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ======================================================
-- Student Courses Enrollment Table
-- ======================================================
CREATE TABLE IF NOT EXISTS `student_courses` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `student_id` INT NOT NULL,
    `course_id` INT NOT NULL,
    `semester` INT NOT NULL,
    `academic_year` VARCHAR(20),
    `grade` VARCHAR(5),
    `marks_obtained` INT,
    `status` ENUM('enrolled', 'completed', 'dropped') DEFAULT 'enrolled',
    `enrolled_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`student_id`) REFERENCES `students`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`course_id`) REFERENCES `courses`(`id`) ON DELETE CASCADE,
    UNIQUE KEY `unique_enrollment` (`student_id`, `course_id`, `semester`),
    INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ======================================================
-- Attendance Table
-- ======================================================
CREATE TABLE IF NOT EXISTS `attendance` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `student_id` INT NOT NULL,
    `course_id` INT NOT NULL,
    `date` DATE NOT NULL,
    `status` ENUM('present', 'absent', 'late') DEFAULT 'absent',
    `marked_by` INT NOT NULL,
    `remarks` TEXT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`student_id`) REFERENCES `students`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`course_id`) REFERENCES `courses`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`marked_by`) REFERENCES `faculty`(`id`) ON DELETE CASCADE,
    UNIQUE KEY `unique_attendance` (`student_id`, `course_id`, `date`),
    INDEX `idx_date` (`date`),
    INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ======================================================
-- Leave Applications Table
-- ======================================================
CREATE TABLE IF NOT EXISTS `leave_applications` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `student_id` INT NOT NULL,
    `from_date` DATE NOT NULL,
    `to_date` DATE NOT NULL,
    `reason` TEXT NOT NULL,
    `leave_type` ENUM('medical', 'emergency', 'personal', 'vacation') DEFAULT 'personal',
    `document_path` VARCHAR(255),
    `status` ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    `approved_by` INT,
    `remarks` TEXT,
    `applied_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`student_id`) REFERENCES `students`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`approved_by`) REFERENCES `faculty`(`id`) ON DELETE SET NULL,
    INDEX `idx_status` (`status`),
    INDEX `idx_dates` (`from_date`, `to_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ======================================================
-- Grievances Table
-- ======================================================
CREATE TABLE IF NOT EXISTS `grievances` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `student_id` INT NOT NULL,
    `category` ENUM('academic', 'administrative', 'technical', 'other') DEFAULT 'other',
    `title` VARCHAR(200) NOT NULL,
    `description` TEXT NOT NULL,
    `attachment` VARCHAR(255),
    `status` ENUM('pending', 'in_progress', 'resolved', 'rejected') DEFAULT 'pending',
    `assigned_to` INT,
    `resolution` TEXT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`student_id`) REFERENCES `students`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`assigned_to`) REFERENCES `faculty`(`id`) ON DELETE SET NULL,
    INDEX `idx_status` (`status`),
    INDEX `idx_category` (`category`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ======================================================
-- Notices Table
-- ======================================================
CREATE TABLE IF NOT EXISTS `notices` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `title` VARCHAR(200) NOT NULL,
    `content` TEXT NOT NULL,
    `category` ENUM('important', 'exam', 'event', 'holiday', 'general') DEFAULT 'general',
    `target_role` ENUM('all', 'student', 'faculty', 'admin') DEFAULT 'all',
    `attachment` VARCHAR(255),
    `created_by` INT NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `expiry_date` DATE,
    `is_active` BOOLEAN DEFAULT TRUE,
    INDEX `idx_category` (`category`),
    INDEX `idx_active` (`is_active`),
    INDEX `idx_expiry` (`expiry_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ======================================================
-- Exam Forms Table
-- ======================================================
CREATE TABLE IF NOT EXISTS `exam_forms` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `student_id` INT NOT NULL,
    `semester` INT NOT NULL,
    `academic_year` VARCHAR(20),
    `total_fee` DECIMAL(10,2),
    `payment_status` ENUM('pending', 'paid', 'verified') DEFAULT 'pending',
    `payment_id` VARCHAR(100),
    `payment_date` TIMESTAMP NULL,
    `form_status` ENUM('draft', 'submitted', 'verified', 'rejected') DEFAULT 'draft',
    `submitted_at` TIMESTAMP NULL,
    `verified_by` INT,
    `verification_date` TIMESTAMP NULL,
    FOREIGN KEY (`student_id`) REFERENCES `students`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`verified_by`) REFERENCES `faculty`(`id`) ON DELETE SET NULL,
    INDEX `idx_status` (`form_status`),
    INDEX `idx_semester` (`semester`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ======================================================
-- Exam Form Subjects Table
-- ======================================================
CREATE TABLE IF NOT EXISTS `exam_form_subjects` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `exam_form_id` INT NOT NULL,
    `course_id` INT NOT NULL,
    `fee` DECIMAL(10,2),
    FOREIGN KEY (`exam_form_id`) REFERENCES `exam_forms`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`course_id`) REFERENCES `courses`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ======================================================
-- Exam Schedule Table
-- ======================================================
CREATE TABLE IF NOT EXISTS `exam_schedule` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `exam_name` VARCHAR(200) NOT NULL,
    `exam_type` ENUM('mid', 'end', 'practical') DEFAULT 'end',
    `course_id` INT NOT NULL,
    `semester` INT NOT NULL,
    `exam_date` DATE NOT NULL,
    `start_time` TIME NOT NULL,
    `end_time` TIME NOT NULL,
    `venue` VARCHAR(100),
    `total_marks` INT DEFAULT 100,
    `instructions` TEXT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`course_id`) REFERENCES `courses`(`id`) ON DELETE CASCADE,
    INDEX `idx_date` (`exam_date`),
    INDEX `idx_semester` (`semester`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ======================================================
-- Feedback Table
-- ======================================================
CREATE TABLE IF NOT EXISTS `feedback` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `student_id` INT NOT NULL,
    `faculty_id` INT,
    `course_id` INT,
    `rating` INT CHECK (rating >= 1 AND rating <= 5),
    `comments` TEXT,
    `category` ENUM('faculty', 'course', 'general') DEFAULT 'general',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`student_id`) REFERENCES `students`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`faculty_id`) REFERENCES `faculty`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`course_id`) REFERENCES `courses`(`id`) ON DELETE SET NULL,
    INDEX `idx_rating` (`rating`),
    INDEX `idx_category` (`category`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ======================================================
-- Exam Results Table
-- ======================================================
CREATE TABLE IF NOT EXISTS `exam_results` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `student_id` INT NOT NULL,
    `course_id` INT NOT NULL,
    `semester` INT NOT NULL,
    `internal_marks` INT,
    `external_marks` INT,
    `total_marks` INT,
    `grade` VARCHAR(5),
    `result_date` DATE,
    `is_published` BOOLEAN DEFAULT FALSE,
    `published_date` DATE,
    FOREIGN KEY (`student_id`) REFERENCES `students`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`course_id`) REFERENCES `courses`(`id`) ON DELETE CASCADE,
    UNIQUE KEY `unique_result` (`student_id`, `course_id`, `semester`),
    INDEX `idx_semester` (`semester`),
    INDEX `idx_grade` (`grade`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ======================================================
-- System Logs Table
-- ======================================================
CREATE TABLE IF NOT EXISTS `system_logs` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `user_id` INT,
    `action` VARCHAR(100) NOT NULL,
    `details` TEXT,
    `ip_address` VARCHAR(45),
    `user_agent` TEXT,
    `level` ENUM('info', 'warning', 'error', 'success') DEFAULT 'info',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
    INDEX `idx_action` (`action`),
    INDEX `idx_level` (`level`),
    INDEX `idx_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ======================================================
-- System Settings Table
-- ======================================================
CREATE TABLE IF NOT EXISTS `system_settings` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `setting_key` VARCHAR(100) UNIQUE NOT NULL,
    `setting_value` TEXT,
    `setting_type` ENUM('text', 'number', 'boolean', 'json', 'array') DEFAULT 'text',
    `description` TEXT,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ======================================================
-- Indexes for Performance Optimization
-- ======================================================

-- Additional indexes for frequently queried columns
CREATE INDEX idx_users_username ON users(username);
CREATE INDEX idx_users_created ON users(created_at);
CREATE INDEX idx_students_course_semester ON students(course, semester);
CREATE INDEX idx_attendance_student_date ON attendance(student_id, date);
CREATE INDEX idx_leave_student_status ON leave_applications(student_id, status);
CREATE INDEX idx_grievance_student_status ON grievances(student_id, status);
CREATE INDEX idx_feedback_student ON feedback(student_id);
CREATE INDEX idx_results_student ON exam_results(student_id);
CREATE INDEX idx_notices_created ON notices(created_at);