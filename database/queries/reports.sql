-- ======================================================
-- Reports and Analytics Queries
-- ======================================================

-- Overall institution statistics
SELECT 
    (SELECT COUNT(*) FROM students WHERE status = 'active') as total_students,
    (SELECT COUNT(*) FROM faculty WHERE status = 'active') as total_faculty,
    (SELECT COUNT(*) FROM courses WHERE status = 'active') as total_courses,
    (SELECT COUNT(*) FROM departments WHERE status = 'active') as total_departments,
    (SELECT AVG(cgpa) FROM students) as avg_cgpa,
    (SELECT AVG(total_attendance) FROM students) as avg_attendance;

-- Department-wise statistics
SELECT 
    s.course as department,
    COUNT(DISTINCT s.id) as total_students,
    COUNT(DISTINCT f.id) as total_faculty,
    COUNT(DISTINCT c.id) as total_courses,
    AVG(s.cgpa) as avg_cgpa,
    AVG(s.total_attendance) as avg_attendance
FROM students s
LEFT JOIN faculty f ON f.department = s.course
LEFT JOIN courses c ON c.department = s.course
WHERE s.status = 'active'
GROUP BY s.course
ORDER BY total_students DESC;

-- Semester-wise performance report
SELECT 
    er.semester,
    COUNT(DISTINCT er.student_id) as total_students,
    AVG(er.total_marks) as avg_marks,
    AVG(CASE 
        WHEN er.grade = 'A+' THEN 10
        WHEN er.grade = 'A' THEN 9
        WHEN er.grade = 'B+' THEN 8
        WHEN er.grade = 'B' THEN 7
        WHEN er.grade = 'C+' THEN 6
        WHEN er.grade = 'C' THEN 5
        WHEN er.grade = 'D' THEN 4
        ELSE 0 END) as avg_sgpa,
    SUM(CASE WHEN er.grade IN ('A+', 'A', 'B+', 'B', 'C+', 'C', 'D') THEN 1 ELSE 0 END) as passed,
    COUNT(*) as total_exams,
    ROUND((SUM(CASE WHEN er.grade IN ('A+', 'A', 'B+', 'B', 'C+', 'C', 'D') THEN 1 ELSE 0 END) / COUNT(*)) * 100, 2) as pass_percentage
FROM exam_results er
GROUP BY er.semester
ORDER BY er.semester;

-- Course-wise performance report
SELECT 
    c.course_code,
    c.course_name,
    COUNT(DISTINCT er.student_id) as total_students,
    AVG(er.total_marks) as avg_marks,
    MAX(er.total_marks) as highest_marks,
    MIN(er.total_marks) as lowest_marks,
    SUM(CASE WHEN er.grade = 'A+' THEN 1 ELSE 0 END) as a_plus_count,
    SUM(CASE WHEN er.grade = 'A' THEN 1 ELSE 0 END) as a_count,
    SUM(CASE WHEN er.grade = 'F' THEN 1 ELSE 0 END) as failed_count
FROM exam_results er
JOIN courses c ON er.course_id = c.id
GROUP BY er.course_id
ORDER BY avg_marks DESC;

-- Gender distribution report
SELECT 
    gender,
    COUNT(*) as count,
    ROUND((COUNT(*) / (SELECT COUNT(*) FROM students)) * 100, 2) as percentage,
    AVG(cgpa) as avg_cgpa
FROM students
GROUP BY gender;

-- Admission trend report
SELECT 
    YEAR(admission_date) as year,
    MONTH(admission_date) as month,
    COUNT(*) as admissions,
    AVG(cgpa) as avg_cgpa
FROM students
GROUP BY YEAR(admission_date), MONTH(admission_date)
ORDER BY year DESC, month DESC;

-- Faculty workload report
SELECT 
    u.full_name as faculty_name,
    f.department,
    COUNT(DISTINCT ca.course_id) as courses_taught,
    COUNT(DISTINCT sc.student_id) as total_students,
    COUNT(DISTINCT a.id) as attendance_marks
FROM faculty f
JOIN users u ON f.user_id = u.id
LEFT JOIN course_assignments ca ON f.id = ca.faculty_id
LEFT JOIN student_courses sc ON ca.course_id = sc.course_id
LEFT JOIN attendance a ON f.id = a.marked_by
WHERE ca.semester = 3
GROUP BY f.id
ORDER BY courses_taught DESC;

-- Leave analysis report
SELECT 
    leave_type,
    COUNT(*) as total_applications,
    SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved,
    SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected,
    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
    ROUND((SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) / COUNT(*)) * 100, 2) as approval_rate
FROM leave_applications
WHERE YEAR(applied_at) = YEAR(CURDATE())
GROUP BY leave_type;

-- Grievance resolution report
SELECT 
    category,
    COUNT(*) as total,
    AVG(DATEDIFF(updated_at, created_at)) as avg_resolution_days,
    SUM(CASE WHEN status = 'resolved' THEN 1 ELSE 0 END) as resolved_count,
    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_count
FROM grievances
GROUP BY category;

-- Feedback analysis report
SELECT 
    category,
    AVG(rating) as avg_rating,
    COUNT(*) as total_feedback,
    SUM(CASE WHEN rating >= 4 THEN 1 ELSE 0 END) as positive,
    SUM(CASE WHEN rating <= 2 THEN 1 ELSE 0 END) as negative
FROM feedback
GROUP BY category;