<?php
require_once '../includes/config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, PUT, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$action = $_GET['action'] ?? '';

switch($action) {
    case 'get_my':
        if (!hasRole('student')) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit();
        }
        
        $student_query = "SELECT id FROM students WHERE user_id = {$_SESSION['user_id']}";
        $student_result = mysqli_query($conn, $student_query);
        $student = mysqli_fetch_assoc($student_result);
        $student_id = $student['id'];
        
        $semester = isset($_GET['semester']) ? (int)$_GET['semester'] : 0;
        
        $where = "er.student_id = $student_id";
        if ($semester > 0) {
            $where .= " AND er.semester = $semester";
        }
        
        $query = "SELECT er.*, c.course_code, c.course_name, c.credits
                  FROM exam_results er
                  JOIN courses c ON er.course_id = c.id
                  WHERE $where
                  ORDER BY er.semester, c.course_code";
        
        $result = mysqli_query($conn, $query);
        $results = [];
        while($row = mysqli_fetch_assoc($result)) {
            $results[] = $row;
        }
        
        // Calculate SGPA and CGPA
        $sgpa_query = "SELECT semester, 
                       SUM(credits * (CASE 
                           WHEN grade = 'A+' THEN 10
                           WHEN grade = 'A' THEN 9
                           WHEN grade = 'B+' THEN 8
                           WHEN grade = 'B' THEN 7
                           WHEN grade = 'C+' THEN 6
                           WHEN grade = 'C' THEN 5
                           WHEN grade = 'D' THEN 4
                           ELSE 0 END)) as total_points,
                       SUM(credits) as total_credits
                       FROM exam_results er
                       JOIN courses c ON er.course_id = c.id
                       WHERE er.student_id = $student_id
                       GROUP BY er.semester";
        
        $sgpa_result = mysqli_query($conn, $sgpa_query);
        $sgpa_data = [];
        while($row = mysqli_fetch_assoc($sgpa_result)) {
            $row['sgpa'] = $row['total_credits'] > 0 ? round($row['total_points'] / $row['total_credits'], 2) : 0;
            $sgpa_data[] = $row;
        }
        
        echo json_encode(['success' => true, 'data' => $results, 'sgpa' => $sgpa_data]);
        break;
        
    case 'get_student':
        if (!hasRole('faculty') && !hasRole('admin')) {
            echo json_encode(['success' => false, 'message' => 'Permission denied']);
            exit();
        }
        
        $student_id = (int)$_GET['student_id'];
        $semester = isset($_GET['semester']) ? (int)$_GET['semester'] : 0;
        
        $where = "er.student_id = $student_id";
        if ($semester > 0) {
            $where .= " AND er.semester = $semester";
        }
        
        $query = "SELECT er.*, c.course_code, c.course_name, c.credits,
                  s.roll_number, u.full_name as student_name
                  FROM exam_results er
                  JOIN courses c ON er.course_id = c.id
                  JOIN students s ON er.student_id = s.id
                  JOIN users u ON s.user_id = u.id
                  WHERE $where
                  ORDER BY er.semester, c.course_code";
        
        $result = mysqli_query($conn, $query);
        $results = [];
        while($row = mysqli_fetch_assoc($result)) {
            $results[] = $row;
        }
        
        echo json_encode(['success' => true, 'data' => $results]);
        break;
        
    case 'enter_marks':
        if (!hasRole('faculty') && !hasRole('admin')) {
            echo json_encode(['success' => false, 'message' => 'Permission denied']);
            exit();
        }
        
        $data = json_decode(file_get_contents('php://input'), true);
        $student_id = (int)$data['student_id'];
        $course_id = (int)$data['course_id'];
        $semester = (int)$data['semester'];
        $internal_marks = (int)$data['internal_marks'];
        $external_marks = (int)$data['external_marks'];
        $total_marks = $internal_marks + $external_marks;
        
        // Calculate grade
        if ($total_marks >= 90) $grade = 'A+';
        elseif ($total_marks >= 80) $grade = 'A';
        elseif ($total_marks >= 70) $grade = 'B+';
        elseif ($total_marks >= 60) $grade = 'B';
        elseif ($total_marks >= 50) $grade = 'C+';
        elseif ($total_marks >= 40) $grade = 'C';
        else $grade = 'F';
        
        $check_query = "SELECT id FROM exam_results WHERE student_id = $student_id AND course_id = $course_id AND semester = $semester";
        $check_result = mysqli_query($conn, $check_query);
        
        if (mysqli_num_rows($check_result) > 0) {
            $query = "UPDATE exam_results 
                      SET internal_marks = $internal_marks, external_marks = $external_marks, total_marks = $total_marks, grade = '$grade', result_date = CURDATE()
                      WHERE student_id = $student_id AND course_id = $course_id AND semester = $semester";
        } else {
            $query = "INSERT INTO exam_results (student_id, course_id, semester, internal_marks, external_marks, total_marks, grade, result_date) 
                      VALUES ($student_id, $course_id, $semester, $internal_marks, $external_marks, $total_marks, '$grade', CURDATE())";
        }
        
        if(mysqli_query($conn, $query)) {
            // Update student CGPA
            $cgpa_query = "SELECT AVG(CASE 
                               WHEN grade = 'A+' THEN 10
                               WHEN grade = 'A' THEN 9
                               WHEN grade = 'B+' THEN 8
                               WHEN grade = 'B' THEN 7
                               WHEN grade = 'C+' THEN 6
                               WHEN grade = 'C' THEN 5
                               WHEN grade = 'D' THEN 4
                               ELSE 0 END) as cgpa
                           FROM exam_results
                           WHERE student_id = $student_id";
            $cgpa_result = mysqli_query($conn, $cgpa_query);
            $cgpa_data = mysqli_fetch_assoc($cgpa_result);
            $cgpa = round($cgpa_data['cgpa'], 2);
            
            $update_cgpa = "UPDATE students SET cgpa = $cgpa WHERE id = $student_id";
            mysqli_query($conn, $update_cgpa);
            
            echo json_encode(['success' => true, 'message' => 'Marks entered successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error entering marks']);
        }
        break;
        
    case 'declare':
        if (!hasRole('admin')) {
            echo json_encode(['success' => false, 'message' => 'Permission denied']);
            exit();
        }
        
        $data = json_decode(file_get_contents('php://input'), true);
        $semester = (int)$data['semester'];
        $result_date = sanitize($data['result_date']);
        
        // Update results status
        $query = "UPDATE exam_results SET is_published = 1, published_date = '$result_date' WHERE semester = $semester";
        
        if(mysqli_query($conn, $query)) {
            echo json_encode(['success' => true, 'message' => 'Results declared successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error declaring results']);
        }
        break;
        
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
}
?>