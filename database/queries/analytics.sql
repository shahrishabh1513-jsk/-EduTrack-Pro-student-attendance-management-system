-- ======================================================
-- Analytics and Dashboard Queries
-- ======================================================

-- Real-time dashboard statistics
SELECT 
    (SELECT COUNT(*) FROM students WHERE status = 'active') as total_students,
    (SELECT COUNT(*) FROM faculty WHERE status = 'active') as total_faculty,
    (SELECT COUNT(*) FROM courses WHERE status = 'active') as total_courses,
    (SELECT COUNT(*) FROM leave_applications WHERE status = 'pending') as pending_leaves,
    (SELECT COUNT(*) FROM grievances WHERE status = 'pending') as pending_grievances,
    (SELECT COUNT(*) FROM exam_forms WHERE form_status = 'submitted') as pending_exam_forms;

-- Daily active users
SELECT 
    DATE(created_at) as date,
    COUNT(DISTINCT user_id) as active_users,
    COUNT(*) as total_actions
FROM system_logs
WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
GROUP BY DATE(created_at)
ORDER BY date DESC;

-- Student retention rate
SELECT 
    s1.batch,
    COUNT(DISTINCT s1.id) as initial_enrollment,
    COUNT(DISTINCT s2.id) as current_enrollment,
    ROUND((COUNT(DISTINCT s2.id) / COUNT(DISTINCT s1.id)) * 100, 2) as retention_rate
FROM students s1
LEFT JOIN students s2 ON s1.id = s2.id AND s2.status = 'active'
GROUP BY s1.batch;

-- Performance trends over time
SELECT 
    YEAR(er.result_date) as year,
    QUARTER(er.result_date) as quarter,
    AVG(er.total_marks) as avg_marks,
    AVG(CASE 
        WHEN er.grade = 'A+' THEN 10
        WHEN er.grade = 'A' THEN 9
        WHEN er.grade = 'B+' THEN 8
        WHEN er.grade = 'B' THEN 7
        WHEN er.grade = 'C+' THEN 6
        WHEN er.grade = 'C' THEN 5
        WHEN er.grade = 'D' THEN 4
        ELSE 0 END) as avg_grade_points,
    SUM(CASE WHEN er.grade IN ('A+', 'A', 'B+', 'B') THEN 1 ELSE 0 END) as distinction_count
FROM exam_results er
GROUP BY YEAR(er.result_date), QUARTER(er.result_date)
ORDER BY year DESC, quarter DESC;

-- Predictive analytics - student success indicators
SELECT 
    s.roll_number,
    u.full_name,
    s.cgpa,
    s.total_attendance as attendance,
    (SELECT COUNT(*) FROM exam_results WHERE student_id = s.id AND grade = 'F') as failed_subjects,
    CASE 
        WHEN s.cgpa >= 8.5 AND s.total_attendance >= 85 THEN 'Excellent'
        WHEN s.cgpa >= 7.0 AND s.total_attendance >= 75 THEN 'Good'
        WHEN s.cgpa >= 5.0 AND s.total_attendance >= 65 THEN 'Average'
        ELSE 'At Risk'
    END as performance_category
FROM students s
JOIN users u ON s.user_id = u.id
WHERE s.status = 'active'
ORDER BY s.cgpa DESC;

-- Course popularity analytics
SELECT 
    c.course_code,
    c.course_name,
    COUNT(DISTINCT sc.student_id) as enrolled_students,
    COUNT(DISTINCT f.id) as faculty_count,
    AVG(fb.rating) as avg_feedback_rating
FROM courses c
LEFT JOIN student_courses sc ON c.id = sc.course_id
LEFT JOIN course_assignments ca ON c.id = ca.course_id
LEFT JOIN faculty f ON ca.faculty_id = f.id
LEFT JOIN feedback fb ON c.id = fb.course_id
GROUP BY c.id
ORDER BY enrolled_students DESC;

-- Faculty performance metrics
SELECT 
    u.full_name as faculty_name,
    COUNT(DISTINCT ca.course_id) as courses_taught,
    COUNT(DISTINCT sc.student_id) as students_taught,
    AVG(fb.rating) as avg_feedback,
    COUNT(DISTINCT a.id) as attendance_marked
FROM faculty f
JOIN users u ON f.user_id = u.id
LEFT JOIN course_assignments ca ON f.id = ca.faculty_id
LEFT JOIN student_courses sc ON ca.course_id = sc.course_id
LEFT JOIN feedback fb ON f.id = fb.faculty_id
LEFT JOIN attendance a ON f.id = a.marked_by
WHERE ca.semester = 3
GROUP BY f.id
ORDER BY avg_feedback DESC;

-- Department comparison analytics
SELECT 
    s.course as department,
    COUNT(DISTINCT s.id) as student_count,
    AVG(s.cgpa) as avg_cgpa,
    AVG(s.total_attendance) as avg_attendance,
    (SELECT COUNT(*) FROM faculty WHERE department = s.course) as faculty_count,
    (SELECT AVG(rating) FROM feedback fb JOIN faculty f ON fb.faculty_id = f.id WHERE f.department = s.course) as avg_feedback
FROM students s
GROUP BY s.course
ORDER BY avg_cgpa DESC;

-- Time-based activity pattern
SELECT 
    HOUR(created_at) as hour,
    COUNT(*) as activity_count,
    COUNT(DISTINCT user_id) as unique_users
FROM system_logs
WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
GROUP BY HOUR(created_at)
ORDER BY hour;

-- Student progress tracking
SELECT 
    s.roll_number,
    u.full_name,
    s.semester as current_semester,
    s.cgpa as current_cgpa,
    (SELECT AVG(total_marks) FROM exam_results WHERE student_id = s.id AND semester = s.semester - 1) as prev_semester_avg,
    (SELECT AVG(total_marks) FROM exam_results WHERE student_id = s.id AND semester = s.semester) as current_semester_avg,
    CASE 
        WHEN (SELECT AVG(total_marks) FROM exam_results WHERE student_id = s.id AND semester = s.semester) > 
             (SELECT AVG(total_marks) FROM exam_results WHERE student_id = s.id AND semester = s.semester - 1) THEN 'Improving'
        WHEN (SELECT AVG(total_marks) FROM exam_results WHERE student_id = s.id AND semester = s.semester) < 
             (SELECT AVG(total_marks) FROM exam_results WHERE student_id = s.id AND semester = s.semester - 1) THEN 'Declining'
        ELSE 'Stable'
    END as trend
FROM students s
JOIN users u ON s.user_id = u.id
WHERE s.semester > 1
ORDER BY s.cgpa DESC;