-- ======================================================
-- Migration: Version 2.0.0
-- Major update with advanced features
-- Date: 2024-03-20
-- ======================================================

USE `edutrack_db`;

-- Create table for online exams
CREATE TABLE IF NOT EXISTS `online_exams` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `course_id` INT NOT NULL,
    `title` VARCHAR(200) NOT NULL,
    `description` TEXT,
    `exam_date` DATETIME NOT NULL,
    `duration_minutes` INT NOT NULL,
    `total_marks` INT DEFAULT 100,
    `passing_marks` INT DEFAULT 40,
    `instructions` TEXT,
    `is_active` BOOLEAN DEFAULT TRUE,
    `created_by` INT NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`course_id`) REFERENCES `courses`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`created_by`) REFERENCES `faculty`(`id`) ON DELETE CASCADE,
    INDEX `idx_exam_date` (`exam_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create table for exam questions
CREATE TABLE IF NOT EXISTS `exam_questions` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `exam_id` INT NOT NULL,
    `question_text` TEXT NOT NULL,
    `question_type` ENUM('mcq', 'truefalse', 'fillblank', 'descriptive') DEFAULT 'mcq',
    `options` JSON,
    `correct_answer` TEXT,
    `marks` INT DEFAULT 1,
    `order_number` INT DEFAULT 0,
    FOREIGN KEY (`exam_id`) REFERENCES `online_exams`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create table for exam attempts
CREATE TABLE IF NOT EXISTS `exam_attempts` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `exam_id` INT NOT NULL,
    `student_id` INT NOT NULL,
    `start_time` DATETIME NOT NULL,
    `end_time` DATETIME,
    `total_marks_obtained` INT,
    `percentage` DECIMAL(5,2),
    `status` ENUM('in_progress', 'completed', 'expired') DEFAULT 'in_progress',
    FOREIGN KEY (`exam_id`) REFERENCES `online_exams`(`id`) ON DELETE CASCADE,
 FOREIGN KEY (`student_id`) REFERENCES `students`(`id`) ON DELETE CASCADE,
    UNIQUE KEY `unique_attempt` (`exam_id`, `student_id`),
    INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create table for student answers
CREATE TABLE IF NOT EXISTS `student_answers` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `attempt_id` INT NOT NULL,
    `question_id` INT NOT NULL,
    `answer` TEXT,
    `is_correct` BOOLEAN DEFAULT FALSE,
    `marks_obtained` INT DEFAULT 0,
    FOREIGN KEY (`attempt_id`) REFERENCES `exam_attempts`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`question_id`) REFERENCES `exam_questions`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create table for notifications
CREATE TABLE IF NOT EXISTS `notifications` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `user_id` INT NOT NULL,
    `title` VARCHAR(200) NOT NULL,
    `message` TEXT NOT NULL,
    `type` ENUM('info', 'success', 'warning', 'error') DEFAULT 'info',
    `is_read` BOOLEAN DEFAULT FALSE,
    `link` VARCHAR(255),
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    INDEX `idx_user_read` (`user_id`, `is_read`),
    INDEX `idx_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create table for chat messages
CREATE TABLE IF NOT EXISTS `chat_messages` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `sender_id` INT NOT NULL,
    `receiver_id` INT NOT NULL,
    `message` TEXT NOT NULL,
    `is_read` BOOLEAN DEFAULT FALSE,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`sender_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`receiver_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    INDEX `idx_conversation` (`sender_id`, `receiver_id`),
    INDEX `idx_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create table for event calendar
CREATE TABLE IF NOT EXISTS `events` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `title` VARCHAR(200) NOT NULL,
    `description` TEXT,
    `event_date` DATE NOT NULL,
    `start_time` TIME,
    `end_time` TIME,
    `venue` VARCHAR(200),
    `event_type` ENUM('academic', 'cultural', 'sports', 'holiday', 'other') DEFAULT 'academic',
    `target_audience` ENUM('all', 'students', 'faculty', 'admin') DEFAULT 'all',
    `created_by` INT NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    INDEX `idx_event_date` (`event_date`),
    INDEX `idx_type` (`event_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create table for student hostel management
CREATE TABLE IF NOT EXISTS `hostel_rooms` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `room_number` VARCHAR(20) NOT NULL,
    `room_type` ENUM('single', 'double', 'triple', 'dorm') DEFAULT 'double',
    `capacity` INT NOT NULL,
    `occupied` INT DEFAULT 0,
    `floor` INT,
    `block` VARCHAR(20),
    `facilities` TEXT,
    `rent_per_month` DECIMAL(10,2),
    `status` ENUM('available', 'occupied', 'maintenance') DEFAULT 'available',
    INDEX `idx_room_number` (`room_number`),
    INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create table for hostel allocations
CREATE TABLE IF NOT EXISTS `hostel_allocations` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `student_id` INT NOT NULL,
    `room_id` INT NOT NULL,
    `allocation_date` DATE NOT NULL,
    `release_date` DATE,
    `status` ENUM('active', 'released', 'pending') DEFAULT 'active',
    FOREIGN KEY (`student_id`) REFERENCES `students`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`room_id`) REFERENCES `hostel_rooms`(`id`) ON DELETE CASCADE,
    INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create table for placement drives
CREATE TABLE IF NOT EXISTS `placement_drives` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `company_name` VARCHAR(200) NOT NULL,
    `job_title` VARCHAR(200) NOT NULL,
    `job_description` TEXT,
    `package` VARCHAR(100),
    `eligibility_criteria` TEXT,
    `drive_date` DATE NOT NULL,
    `last_apply_date` DATE NOT NULL,
    `venue` VARCHAR(200),
    `status` ENUM('upcoming', 'ongoing', 'completed', 'cancelled') DEFAULT 'upcoming',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_drive_date` (`drive_date`),
    INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create table for placement applications
CREATE TABLE IF NOT EXISTS `placement_applications` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `drive_id` INT NOT NULL,
    `student_id` INT NOT NULL,
    `application_date` DATE NOT NULL,
    `resume_path` VARCHAR(255),
    `status` ENUM('applied', 'shortlisted', 'selected', 'rejected') DEFAULT 'applied',
    `interview_date` DATE,
    `remarks` TEXT,
    FOREIGN KEY (`drive_id`) REFERENCES `placement_drives`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`student_id`) REFERENCES `students`(`id`) ON DELETE CASCADE,
    UNIQUE KEY `unique_application` (`drive_id`, `student_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create table for alumni
CREATE TABLE IF NOT EXISTS `alumni` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `student_id` INT NOT NULL,
    `graduation_year` YEAR NOT NULL,
    `current_company` VARCHAR(200),
    `job_title` VARCHAR(200),
    `contact_email` VARCHAR(100),
    `contact_phone` VARCHAR(20),
    `linkedin_profile` VARCHAR(255),
    `achievements` TEXT,
    `verified` BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (`student_id`) REFERENCES `students`(`id`) ON DELETE CASCADE,
    INDEX `idx_graduation_year` (`graduation_year`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add new indexes for performance optimization
CREATE INDEX idx_attendance_student_status ON attendance(student_id, status);
CREATE INDEX idx_results_grade ON exam_results(grade);
CREATE INDEX idx_feedback_rating ON feedback(rating);
CREATE INDEX idx_notices_created_at ON notices(created_at);
CREATE INDEX idx_leave_dates ON leave_applications(from_date, to_date);

-- Add full-text search indexes
ALTER TABLE `courses` ADD FULLTEXT INDEX `ft_courses` (`course_name`, `description`);
ALTER TABLE `students` ADD FULLTEXT INDEX `ft_students` (`roll_number`);
ALTER TABLE `users` ADD FULLTEXT INDEX `ft_users` (`full_name`, `email`);

-- Create view for student dashboard
CREATE OR REPLACE VIEW `v_student_dashboard` AS
SELECT 
    s.id as student_id,
    s.roll_number,
    u.full_name,
    s.course,
    s.semester,
    s.cgpa,
    s.total_attendance as attendance,
    (SELECT COUNT(*) FROM exam_results WHERE student_id = s.id AND semester = s.semester) as exams_completed,
    (SELECT AVG(rating) FROM feedback WHERE student_id = s.id) as avg_feedback,
    (SELECT COUNT(*) FROM notifications WHERE user_id = u.id AND is_read = 0) as unread_notifications
FROM students s
JOIN users u ON s.user_id = u.id
WHERE s.status = 'active';

-- Create view for faculty dashboard
CREATE OR REPLACE VIEW `v_faculty_dashboard` AS
SELECT 
    f.id as faculty_id,
    u.full_name,
    f.department,
    f.designation,
    (SELECT COUNT(*) FROM course_assignments WHERE faculty_id = f.id AND semester = 3) as courses_teaching,
    (SELECT COUNT(DISTINCT student_id) FROM student_courses sc JOIN course_assignments ca ON sc.course_id = ca.course_id WHERE ca.faculty_id = f.id) as total_students,
    (SELECT COUNT(*) FROM leave_applications WHERE approved_by = f.id AND status = 'pending') as pending_leaves
FROM faculty f
JOIN users u ON f.user_id = u.id
WHERE u.status = 'active';

-- Create view for admin dashboard
CREATE OR REPLACE VIEW `v_admin_dashboard` AS
SELECT 
    (SELECT COUNT(*) FROM users WHERE role = 'student' AND status = 'active') as total_students,
    (SELECT COUNT(*) FROM users WHERE role = 'faculty' AND status = 'active') as total_faculty,
    (SELECT COUNT(*) FROM courses WHERE status = 'active') as total_courses,
    (SELECT COUNT(*) FROM leave_applications WHERE status = 'pending') as pending_leaves,
    (SELECT COUNT(*) FROM grievances WHERE status = 'pending') as pending_grievances,
    (SELECT COUNT(*) FROM exam_forms WHERE form_status = 'submitted') as pending_exam_forms,
    (SELECT COUNT(*) FROM feedback WHERE DATE(created_at) = CURDATE()) as today_feedback,
    (SELECT COUNT(*) FROM users WHERE DATE(created_at) = CURDATE()) as new_registrations;

-- Add sample data for new tables
INSERT INTO `notifications` (`user_id`, `title`, `message`, `type`) VALUES
(1, 'Welcome to EduTrack Pro', 'Your account has been created successfully. Please complete your profile.', 'success'),
(2, 'Assignment Due Soon', 'Your Data Structures assignment is due in 2 days.', 'warning');

-- Insert sample events
INSERT INTO `events` (`title`, `description`, `event_date`, `event_type`, `target_audience`, `created_by`) VALUES
('Annual Sports Day', 'Annual sports competition for all students', '2024-04-15', 'sports', 'all', 12),
('Tech Symposium 2024', 'Technical event showcasing student projects', '2024-04-20', 'academic', 'students', 12),
('Independence Day Celebration', 'Flag hoisting ceremony', '2024-08-15', 'cultural', 'all', 12);

-- Insert sample placement drives
INSERT INTO `placement_drives` (`company_name`, `job_title`, `package`, `drive_date`, `last_apply_date`, `venue`, `status`) VALUES
('Google India', 'Software Engineer', '25 LPA', '2024-04-10', '2024-03-30', 'Main Auditorium', 'upcoming'),
('Microsoft', 'Cloud Engineer', '28 LPA', '2024-04-15', '2024-04-05', 'Seminar Hall', 'upcoming'),
('Amazon', 'SDE Intern', '12 LPA', '2024-04-20', '2024-04-10', 'Online', 'upcoming');

-- Update system version
UPDATE `system_settings` 
SET `setting_value` = '2.0.0' 
WHERE `setting_key` = 'db_version';

-- Record this migration
INSERT INTO `migrations` (`version`, `description`) VALUES 
('2.0.0', 'Major update: Added online exams, notifications, chat, events, hostel management, placement cell, alumni module');

-- Add new system settings for v2.0.0
INSERT INTO `system_settings` (`setting_key`, `setting_value`, `setting_type`, `description`) VALUES
('online_exams_enabled', 'true', 'boolean', 'Enable online examination module'),
('chat_enabled', 'true', 'boolean', 'Enable chat messaging feature'),
('placement_cell_enabled', 'true', 'boolean', 'Enable placement management module'),
('alumni_module_enabled', 'true', 'boolean', 'Enable alumni management module'),
('hostel_enabled', 'true', 'boolean', 'Enable hostel management module'),
('notification_enabled', 'true', 'boolean', 'Enable push notifications'),
('max_online_exam_attempts', '1', 'number', 'Maximum attempts allowed for online exams');

-- Create stored procedure for attendance report
DELIMITER //

CREATE PROCEDURE `sp_attendance_report`(
    IN p_course_id INT,
    IN p_from_date DATE,
    IN p_to_date DATE
)
BEGIN
    SELECT 
        s.roll_number,
        u.full_name as student_name,
        COUNT(CASE WHEN a.status = 'present' THEN 1 END) as present_days,
        COUNT(CASE WHEN a.status = 'absent' THEN 1 END) as absent_days,
        COUNT(CASE WHEN a.status = 'late' THEN 1 END) as late_days,
        COUNT(*) as total_days,
        ROUND((COUNT(CASE WHEN a.status = 'present' THEN 1 END) / COUNT(*)) * 100, 2) as percentage
    FROM attendance a
    JOIN students s ON a.student_id = s.id
    JOIN users u ON s.user_id = u.id
    WHERE a.course_id = p_course_id
        AND a.date BETWEEN p_from_date AND p_to_date
    GROUP BY a.student_id
    ORDER BY percentage DESC;
END //

-- Create stored procedure for student performance analysis
CREATE PROCEDURE `sp_student_performance`(
    IN p_student_id INT,
    IN p_semester INT
)
BEGIN
    SELECT 
        c.course_code,
        c.course_name,
        er.internal_marks,
        er.external_marks,
        er.total_marks,
        er.grade,
        CASE 
            WHEN er.grade = 'A+' THEN 'Outstanding'
            WHEN er.grade = 'A' THEN 'Excellent'
            WHEN er.grade = 'B+' THEN 'Very Good'
            WHEN er.grade = 'B' THEN 'Good'
            WHEN er.grade = 'C+' THEN 'Satisfactory'
            WHEN er.grade = 'C' THEN 'Average'
            WHEN er.grade = 'D' THEN 'Pass'
            ELSE 'Fail'
        END as performance_status
    FROM exam_results er
    JOIN courses c ON er.course_id = c.id
    WHERE er.student_id = p_student_id
        AND er.semester = p_semester
    ORDER BY c.course_code;
END //

-- Create function to calculate SGPA
CREATE FUNCTION `fn_calculate_sgpa`(p_student_id INT, p_semester INT)
RETURNS DECIMAL(3,2)
DETERMINISTIC
BEGIN
    DECLARE total_points DECIMAL(10,2);
    DECLARE total_credits INT;
    
    SELECT 
        SUM(credits * CASE 
            WHEN grade = 'A+' THEN 10
            WHEN grade = 'A' THEN 9
            WHEN grade = 'B+' THEN 8
            WHEN grade = 'B' THEN 7
            WHEN grade = 'C+' THEN 6
            WHEN grade = 'C' THEN 5
            WHEN grade = 'D' THEN 4
            ELSE 0 END),
        SUM(credits)
    INTO total_points, total_credits
    FROM exam_results er
    JOIN courses c ON er.course_id = c.id
    WHERE er.student_id = p_student_id
        AND er.semester = p_semester;
    
    IF total_credits > 0 THEN
        RETURN ROUND(total_points / total_credits, 2);
    ELSE
        RETURN 0;
    END IF;
END //

DELIMITER ;

-- Create trigger for audit logging
DELIMITER //

CREATE TRIGGER `tr_student_update` 
AFTER UPDATE ON `students`
FOR EACH ROW
BEGIN
    INSERT INTO `system_logs` (`user_id`, `action`, `details`, `level`)
    VALUES (NULL, 'STUDENT_UPDATE', 
            CONCAT('Student ID ', OLD.id, ' updated: CGPA from ', OLD.cgpa, ' to ', NEW.cgpa),
            'info');
END //

CREATE TRIGGER `tr_attendance_insert`
AFTER INSERT ON `attendance`
FOR EACH ROW
BEGIN
    UPDATE students 
    SET total_attendance = (
        SELECT ROUND(AVG(CASE WHEN status = 'present' THEN 100 ELSE 0 END))
        FROM attendance 
        WHERE student_id = NEW.student_id
    )
    WHERE id = NEW.student_id;
END //

DELIMITER ;