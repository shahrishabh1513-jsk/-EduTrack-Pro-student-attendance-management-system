-- ======================================================
-- EduTrack Pro - Seed Data
-- Initial data for testing and demonstration
-- ======================================================

USE `edutrack_db`;

-- ======================================================
-- Insert Departments
-- ======================================================
INSERT INTO `departments` (`name`, `code`, `hod`, `established_year`, `email`, `phone`, `description`) VALUES
('Computer Science', 'CS', 'Prof.Aakash Gupta', 2010, 'cs@edutrack.com', '+91 1234567890', 'Department of Computer Science offers B.Tech, M.Tech and PhD programs in various specializations.'),
('Information Technology', 'IT', 'Prof. Neha Shah', 2012, 'it@edutrack.com', '+91 1234567891', 'Department of Information Technology focuses on software development and IT infrastructure.'),
('Electronics', 'EC', 'Dr. Niraj Shah', 2011, 'ec@edutrack.com', '+91 1234567892', 'Department of Electronics and Communication Engineering.'),
('Mechanical', 'ME', 'Dr. Rajesh Kumar', 2010, 'me@edutrack.com', '+91 1234567893', 'Department of Mechanical Engineering.'),
('Civil', 'CE', 'Prof. Sunil Verma', 2013, 'ce@edutrack.com', '+91 1234567894', 'Department of Civil Engineering.');

-- ======================================================
-- Insert Users
-- ======================================================
INSERT INTO `users` (`username`, `email`, `password`, `role`, `full_name`, `phone`, `address`) VALUES
-- Students
('rishabh', 'rishabh@edutrack.com', MD5('password123'), 'student', 'Rishabh Shah', '9876543210', '123 Student Hostel, Mumbai'),
('jenish', 'jenish@edutrack.com', MD5('password123'), 'student', 'Jenish khunt', '9876543211', '45 College Road, Ahmedabad'),
('vasu', 'vasu@edutrack.com', MD5('password123'), 'student', 'Vasu Motisarya', '9876543212', '78 University Area, Surat'),
('hetvi', 'hetvi@edutrack.com', MD5('password123'), 'student', 'Hetvi Savani', '9876543213', '12 Gandhi Nagar, Vadodara'),
('aarav', 'aarav@edutrack.com', MD5('password123'), 'student', 'Aarav Desai', '9876543214', '22 Lake View, Mumbai'),
('kiara', 'kiara@edutrack.com', MD5('password123'), 'student', 'Kiara Mehta', '9876543215', '56 Park Street, Pune'),
('dev', 'dev@edutrack.com', MD5('password123'), 'student', 'Dev Patel', '9876543216', '89 IT Park, Bangalore'),

-- Faculty
('aakash', 'aakash@edutrack.com', MD5('password123'), 'faculty', 'Prof.Aakash Gupta', '9876543217', '45 Faculty Quarters, Mumbai'),
('neha', 'neha@edutrack.com', MD5('password123'), 'faculty', 'Prof. Neha Shah', '9876543218', '67 Teacher Colony, Mumbai'),
('niraj', 'niraj@edutrack.com', MD5('password123'), 'faculty', 'Dr. Niraj Shah', '9876543219', '89 Staff Housing, Mumbai'),
('rajesh', 'rajesh@edutrack.com', MD5('password123'), 'faculty', 'Prof. Rajesh Kumar', '9876543220', '34 Staff Quarters, Mumbai'),

-- Admin
('admin', 'admin@edutrack.com', MD5('admin123'), 'admin', 'System Administrator', '9876543221', 'Admin Office, Campus');

-- ======================================================
-- Insert Students
-- ======================================================
INSERT INTO `students` (`user_id`, `roll_number`, `enrollment_number`, `course`, `semester`, `batch`, `father_name`, `mother_name`, `date_of_birth`, `gender`, `admission_date`, `cgpa`) VALUES
(1, 'IT181', 'ENR2023001', 'BSC IT', 3, '2023-2026', 'Rajesh Sharma', 'Sunita Sharma', '2004-05-15', 'Male', '2023-07-01', 8.5),
(2, 'IT095', 'ENR2023002', 'BSC IT', 3, '2023-2026', 'Raj Patel', 'Neha Patel', '2004-08-20', 'Male', '2023-07-01', 8.7),
(3, 'IT124', 'ENR2023003', 'BSC IT', 3, '2023-2026', 'Rajesh Mehta', 'Priya Mehta', '2004-03-10', 'Male', '2023-07-01', 8.2),
(4, 'IT131', 'ENR2023004', 'BSC IT', 3, '2023-2026', 'Rajesh Shah', 'Anita Shah', '2004-06-25', 'Female', '2023-07-01', 8.9),
(5, 'CSIT001', 'ENR2023005', 'BSC IT', 3, '2023-2026', 'Raj Desai', 'Meena Desai', '2004-01-15', 'Male', '2023-07-01', 8.6),
(6, 'CSIT002', 'ENR2023006', 'BSC IT', 3, '2023-2026', 'Raj Mehta', 'Nita Mehta', '2004-07-18', 'Female', '2023-07-01', 8.4),
(7, 'CSIT003', 'ENR2023007', 'BSC IT', 3, '2023-2026', 'Raj Patel', 'Sita Patel', '2004-01-30', 'Male', '2023-07-01', 8.1);

-- ======================================================
-- Insert Faculty
-- ======================================================
INSERT INTO `faculty` (`user_id`, `employee_id`, `department`, `designation`, `qualification`, `specialization`, `experience`, `joining_date`) VALUES
(8, 'FAC001', 'Computer Science', 'Professor', 'Ph.D. in Computer Science', 'Data Structures, Algorithms', 12, '2015-08-01'),
(9, 'FAC002', 'Computer Science', 'Associate Professor', 'M.Tech in IT', 'Database Management', 8, '2018-07-15'),
(10, 'FAC003', 'Computer Science', 'Assistant Professor', 'M.Sc. Computer Science', 'Web Development', 5, '2020-06-10'),
(11, 'FAC004', 'Computer Science', 'Assistant Professor', 'M.Tech in CS', 'Operating Systems', 6, '2019-08-01');

-- ======================================================
-- Insert Courses
-- ======================================================
INSERT INTO `courses` (`course_code`, `course_name`, `credits`, `semester`, `department`, `description`) VALUES
('CS301', 'Data Structures', 4, 3, 'Computer Science', 'Study of fundamental data structures and algorithms including arrays, linked lists, stacks, queues, trees, and graphs.'),
('CS302', 'Database Management Systems', 4, 3, 'Computer Science', 'Introduction to database concepts, relational model, SQL, normalization, transaction management, and database design.'),
('CS303', 'Web Development', 3, 3, 'Computer Science', 'Front-end and back-end web development using HTML5, CSS3, JavaScript, PHP, and modern frameworks.'),
('CS304', 'Operating Systems', 4, 3, 'Computer Science', 'Concepts of operating systems including processes, memory management, file systems, and concurrency.'),
('CS305', 'Computer Networks', 4, 3, 'Computer Science', 'Fundamentals of computer networks, OSI model, TCP/IP, routing, and network security.');

-- ======================================================
-- Assign Courses to Faculty
-- ======================================================
INSERT INTO `course_assignments` (`course_id`, `faculty_id`, `semester`, `academic_year`) VALUES
(1, 1, 3, '2024-2025'),
(2, 2, 3, '2024-2025'),
(3, 3, 3, '2024-2025'),
(4, 1, 3, '2024-2025'),
(5, 2, 3, '2024-2025');

-- ======================================================
-- Enroll Students in Courses
-- ======================================================
INSERT IGNORE INTO `student_courses` (`student_id`, `course_id`, `semester`, `academic_year`, `status`)
SELECT s.id, c.id, 3, '2024-2025', 'enrolled'
FROM students s, courses c
WHERE s.course = 'BSC IT' AND c.semester = 3;

-- ======================================================
-- Insert Sample Attendance
-- ======================================================
INSERT INTO `attendance` (`student_id`, `course_id`, `date`, `status`, `marked_by`) 
SELECT s.id, c.id, CURDATE(), 'present', 1
FROM students s, courses c
WHERE s.course = 'BSC IT' AND c.semester = 3
LIMIT 10;

-- ======================================================
-- Insert Sample Notices
-- ======================================================
INSERT INTO `notices` (`title`, `content`, `category`, `target_role`, `created_by`) VALUES
('End Semester Examination Schedule Released', 'The schedule for end semester examinations has been released. Please check the exam timetable section for detailed schedule. All students must report 15 minutes before exam time.', 'exam', 'all', 12),
('College Holiday on March 25th', 'The college will remain closed on March 25th on account of Ram Navami. All classes and examinations scheduled for the day will be rescheduled.', 'holiday', 'all', 12),
('Annual Tech Fest 2024', 'Registrations open for annual technical festival "TechFusion 2024". Events include coding competition, hackathon, and technical quiz. Last date for registration: March 30.', 'event', 'student', 12),
('Library Timing Extended', 'Library timings have been extended during examination week. Library will remain open from 8 AM to 10 PM.', 'important', 'all', 12);

-- ======================================================
-- Insert System Settings
-- ======================================================
INSERT INTO `system_settings` (`setting_key`, `setting_value`, `setting_type`, `description`) VALUES
('system_name', 'EduTrack Pro', 'text', 'Name of the system'),
('system_version', '2.0.0', 'text', 'Current system version'),
('academic_year', '2024-2025', 'text', 'Current academic year'),
('current_semester', '3', 'number', 'Current semester number'),
('min_attendance', '75', 'number', 'Minimum attendance percentage required'),
('exam_fee', '500', 'number', 'Exam fee per subject'),
('practical_fee', '1000', 'number', 'Practical exam fee'),
('enable_registration', 'true', 'boolean', 'Enable new student registration'),
('maintenance_mode', 'false', 'boolean', 'System maintenance mode status');

-- ======================================================
-- Insert Sample Feedback
-- ======================================================
INSERT INTO `feedback` (`student_id`, `faculty_id`, `rating`, `comments`, `category`) VALUES
(1, 1, 5, 'Excellent teaching style. Very clear explanations.', 'faculty'),
(2, 1, 4, 'Good course content. Could include more examples.', 'faculty'),
(3, 2, 5, 'Very helpful faculty. Always available for doubts.', 'faculty');

-- ======================================================
-- Insert Sample System Logs
-- ======================================================
INSERT INTO `system_logs` (`user_id`, `action`, `details`, `ip_address`, `level`) VALUES
(12, 'System Installation', 'Initial system setup completed', '127.0.0.1', 'success'),
(12, 'Database Seeded', 'Sample data inserted successfully', '127.0.0.1', 'info');