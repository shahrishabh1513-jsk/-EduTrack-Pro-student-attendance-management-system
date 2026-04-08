-- ======================================================
-- Student Management Queries
-- ======================================================

-- Get all students with details
SELECT 
    s.id,
    s.roll_number,
    s.enrollment_number,
    u.full_name,
    u.email,
    u.phone,
    s.course,
    s.semester,
    s.batch,
    s.cgpa,
    s.total_attendance,
    u.status
FROM students s
JOIN users u ON s.user_id = u.id
ORDER BY s.roll_number;

-- Get student by roll number
SELECT 
    s.*,
    u.full_name,
    u.email,
    u.phone,
    u.address
FROM students s
JOIN users u ON s.user_id = u.id
WHERE s.roll_number = ?;

-- Get students by course and semester
SELECT 
    s.roll_number,
    u.full_name,
    s.cgpa
FROM students s
JOIN users u ON s.user_id = u.id
WHERE s.course = ? AND s.semester = ?
ORDER BY s.cgpa DESC;

-- Get student enrollment statistics by course
SELECT 
    course,
    COUNT(*) as total_students,
    SUM(CASE WHEN gender = 'Male' THEN 1 ELSE 0 END) as male,
    SUM(CASE WHEN gender = 'Female' THEN 1 ELSE 0 END) as female,
    AVG(cgpa) as avg_cgpa
FROM students
GROUP BY course;

-- Get student semester-wise performance
SELECT 
    s.roll_number,
    u.full_name,
    er.semester,
    AVG(er.total_marks) as avg_marks,
    AVG(CASE 
        WHEN er.grade = 'A+' THEN 10
        WHEN er.grade = 'A' THEN 9
        WHEN er.grade = 'B+' THEN 8
        WHEN er.grade = 'B' THEN 7
        WHEN er.grade = 'C+' THEN 6
        WHEN er.grade = 'C' THEN 5
        WHEN er.grade = 'D' THEN 4
        ELSE 0 END) as sgpa
FROM students s
JOIN users u ON s.user_id = u.id
JOIN exam_results er ON s.id = er.student_id
GROUP BY s.id, er.semester
ORDER BY s.roll_number, er.semester;

-- Get top performing students
SELECT 
    s.roll_number,
    u.full_name,
    s.course,
    s.cgpa,
    RANK() OVER (ORDER BY s.cgpa DESC) as rank_position
FROM students s
JOIN users u ON s.user_id = u.id
WHERE s.status = 'active'
ORDER BY s.cgpa DESC
LIMIT 10;

-- Get students with low attendance
SELECT 
    s.roll_number,
    u.full_name,
    s.course,
    s.semester,
    s.total_attendance as attendance_percentage
FROM students s
JOIN users u ON s.user_id = u.id
WHERE s.total_attendance < 75
ORDER BY s.total_attendance ASC;

-- Get students eligible for promotion
SELECT 
    s.id,
    s.roll_number,
    u.full_name,
    s.course,
    s.semester as current_semester,
    s.semester + 1 as next_semester,
    s.cgpa
FROM students s
JOIN users u ON s.user_id = u.id
WHERE s.semester < 6 
AND s.status = 'active'
AND s.cgpa >= 5.0
ORDER BY s.semester, s.cgpa DESC;

-- Get student count by batch
SELECT 
    batch,
    COUNT(*) as student_count,
    AVG(cgpa) as avg_cgpa
FROM students
GROUP BY batch
ORDER BY batch;

-- Search students
SELECT 
    s.roll_number,
    u.full_name,
    u.email,
    s.course,
    s.semester
FROM students s
JOIN users u ON s.user_id = u.id
WHERE u.full_name LIKE CONCAT('%', ?, '%')
   OR s.roll_number LIKE CONCAT('%', ?, '%')
   OR u.email LIKE CONCAT('%', ?, '%')
ORDER BY u.full_name;