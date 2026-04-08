-- ======================================================
-- Attendance Management Queries
-- ======================================================

-- Get attendance summary by student
SELECT 
    s.roll_number,
    u.full_name,
    COUNT(CASE WHEN a.status = 'present' THEN 1 END) as present_days,
    COUNT(CASE WHEN a.status = 'absent' THEN 1 END) as absent_days,
    COUNT(CASE WHEN a.status = 'late' THEN 1 END) as late_days,
    COUNT(*) as total_days,
    ROUND((COUNT(CASE WHEN a.status = 'present' THEN 1 END) / COUNT(*)) * 100, 2) as attendance_percentage
FROM attendance a
JOIN students s ON a.student_id = s.id
JOIN users u ON s.user_id = u.id
WHERE a.student_id = ?
GROUP BY a.student_id;

-- Get attendance by course
SELECT 
    c.course_code,
    c.course_name,
    COUNT(CASE WHEN a.status = 'present' THEN 1 END) as present,
    COUNT(CASE WHEN a.status = 'absent' THEN 1 END) as absent,
    COUNT(*) as total,
    ROUND((COUNT(CASE WHEN a.status = 'present' THEN 1 END) / COUNT(*)) * 100, 2) as percentage
FROM attendance a
JOIN courses c ON a.course_id = c.id
WHERE c.id = ?
GROUP BY a.course_id;

-- Get daily attendance summary
SELECT 
    a.date,
    COUNT(DISTINCT a.student_id) as total_students,
    COUNT(CASE WHEN a.status = 'present' THEN 1 END) as present_count,
    COUNT(CASE WHEN a.status = 'absent' THEN 1 END) as absent_count,
    ROUND((COUNT(CASE WHEN a.status = 'present' THEN 1 END) / COUNT(*)) * 100, 2) as attendance_rate
FROM attendance a
WHERE a.date BETWEEN ? AND ?
GROUP BY a.date
ORDER BY a.date DESC;

-- Get department-wise attendance
SELECT 
    s.course as department,
    COUNT(DISTINCT a.student_id) as total_students,
    COUNT(CASE WHEN a.status = 'present' THEN 1 END) as present_count,
    ROUND((COUNT(CASE WHEN a.status = 'present' THEN 1 END) / COUNT(*)) * 100, 2) as attendance_rate
FROM attendance a
JOIN students s ON a.student_id = s.id
WHERE a.date = CURDATE()
GROUP BY s.course;

-- Get students with attendance below threshold
SELECT 
    s.roll_number,
    u.full_name,
    s.course,
    ROUND((COUNT(CASE WHEN a.status = 'present' THEN 1 END) / COUNT(*)) * 100, 2) as attendance_percentage
FROM attendance a
JOIN students s ON a.student_id = s.id
JOIN users u ON s.user_id = u.id
GROUP BY a.student_id
HAVING attendance_percentage < 75
ORDER BY attendance_percentage ASC;

-- Get monthly attendance trend
SELECT 
    YEAR(a.date) as year,
    MONTH(a.date) as month,
    COUNT(DISTINCT a.student_id) as total_students,
    COUNT(CASE WHEN a.status = 'present' THEN 1 END) as present_count,
    ROUND((COUNT(CASE WHEN a.status = 'present' THEN 1 END) / COUNT(*)) * 100, 2) as attendance_rate
FROM attendance a
WHERE a.date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
GROUP BY YEAR(a.date), MONTH(a.date)
ORDER BY year DESC, month DESC;

-- Get faculty attendance marking statistics
SELECT 
    u.full_name as faculty_name,
    COUNT(*) as total_marks,
    COUNT(DISTINCT a.date) as days_marked,
    COUNT(DISTINCT a.course_id) as courses_covered
FROM attendance a
JOIN faculty f ON a.marked_by = f.id
JOIN users u ON f.user_id = u.id
WHERE a.date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
GROUP BY a.marked_by
ORDER BY total_marks DESC;

-- Get course-wise attendance for a student
SELECT 
    c.course_code,
    c.course_name,
    COUNT(CASE WHEN a.status = 'present' THEN 1 END) as present,
    COUNT(*) as total,
    ROUND((COUNT(CASE WHEN a.status = 'present' THEN 1 END) / COUNT(*)) * 100, 2) as percentage
FROM attendance a
JOIN courses c ON a.course_id = c.id
WHERE a.student_id = ?
GROUP BY a.course_id;

-- Get attendance for date range
SELECT 
    a.date,
    COUNT(CASE WHEN a.status = 'present' THEN 1 END) as present,
    COUNT(CASE WHEN a.status = 'absent' THEN 1 END) as absent,
    COUNT(CASE WHEN a.status = 'late' THEN 1 END) as late
FROM attendance a
WHERE a.course_id = ?
AND a.date BETWEEN ? AND ?
GROUP BY a.date
ORDER BY a.date;