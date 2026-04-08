<?php
require_once '../includes/config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, PUT, DELETE, OPTIONS');
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
    case 'list':
        $semester = isset($_GET['semester']) ? (int)$_GET['semester'] : 0;
        $department = isset($_GET['department']) ? sanitize($_GET['department']) : '';
        
        $where = [];
        if ($semester > 0) {
            $where[] = "semester = $semester";
        }
        if ($department) {
            $where[] = "department = '$department'";
        }
        
        $where_clause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";
        
        $query = "SELECT c.*, 
                  (SELECT COUNT(*) FROM course_assignments WHERE course_id = c.id) as faculty_count,
                  (SELECT COUNT(*) FROM student_courses WHERE course_id = c.id AND status = 'enrolled') as student_count
                  FROM courses c 
                  $where_clause 
                  ORDER BY c.semester, c.course_code";
        $result = mysqli_query($conn, $query);
        
        $courses = [];
        while($row = mysqli_fetch_assoc($result)) {
            $courses[] = $row;
        }
        
        echo json_encode(['success' => true, 'data' => $courses]);
        break;
        
    case 'add':
        if (!hasRole('admin')) {
            echo json_encode(['success' => false, 'message' => 'Permission denied']);
            exit();
        }
        
        $data = json_decode(file_get_contents('php://input'), true);
        
        $course_code = sanitize($data['course_code']);
        $course_name = sanitize($data['course_name']);
        $credits = (int)$data['credits'];
        $semester = (int)$data['semester'];
        $department = sanitize($data['department']);
        $description = sanitize($data['description'] ?? '');
        $status = sanitize($data['status'] ?? 'active');
        
        $query = "INSERT INTO courses (course_code, course_name, credits, semester, department, description, status) 
                  VALUES ('$course_code', '$course_name', $credits, $semester, '$department', '$description', '$status')";
        
        if(mysqli_query($conn, $query)) {
            echo json_encode(['success' => true, 'message' => 'Course added successfully', 'course_id' => mysqli_insert_id($conn)]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error adding course']);
        }
        break;
        
    case 'get':
        $id = (int)$_GET['id'];
        $query = "SELECT * FROM courses WHERE id = $id";
        $result = mysqli_query($conn, $query);
        $course = mysqli_fetch_assoc($result);
        
        if ($course) {
            // Get assigned faculty
            $faculty_query = "SELECT f.id, f.employee_id, u.full_name 
                              FROM course_assignments ca 
                              JOIN faculty f ON ca.faculty_id = f.id 
                              JOIN users u ON f.user_id = u.id 
                              WHERE ca.course_id = $id";
            $faculty_result = mysqli_query($conn, $faculty_query);
            $faculty = [];
            while($f = mysqli_fetch_assoc($faculty_result)) {
                $faculty[] = $f;
            }
            $course['faculty'] = $faculty;
            
            echo json_encode(['success' => true, 'data' => $course]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Course not found']);
        }
        break;
        
    case 'update':
        if (!hasRole('admin')) {
            echo json_encode(['success' => false, 'message' => 'Permission denied']);
            exit();
        }
        
        $data = json_decode(file_get_contents('php://input'), true);
        $id = (int)$data['id'];
        
        $query = "UPDATE courses SET 
                  course_code = '{$data['course_code']}',
                  course_name = '{$data['course_name']}',
                  credits = {$data['credits']},
                  semester = {$data['semester']},
                  department = '{$data['department']}',
                  description = '{$data['description']}',
                  status = '{$data['status']}'
                  WHERE id = $id";
        
        if(mysqli_query($conn, $query)) {
            echo json_encode(['success' => true, 'message' => 'Course updated successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error updating course']);
        }
        break;
        
    case 'delete':
        if (!hasRole('admin')) {
            echo json_encode(['success' => false, 'message' => 'Permission denied']);
            exit();
        }
        
        $id = (int)$_GET['id'];
        
        $query = "DELETE FROM courses WHERE id = $id";
        if(mysqli_query($conn, $query)) {
            echo json_encode(['success' => true, 'message' => 'Course deleted successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error deleting course']);
        }
        break;
        
    case 'assign_faculty':
        if (!hasRole('admin')) {
            echo json_encode(['success' => false, 'message' => 'Permission denied']);
            exit();
        }
        
        $data = json_decode(file_get_contents('php://input'), true);
        $course_id = (int)$data['course_id'];
        $faculty_id = (int)$data['faculty_id'];
        $semester = (int)$data['semester'];
        $academic_year = sanitize($data['academic_year']);
        
        $query = "INSERT INTO course_assignments (course_id, faculty_id, semester, academic_year) 
                  VALUES ($course_id, $faculty_id, $semester, '$academic_year')";
        
        if(mysqli_query($conn, $query)) {
            echo json_encode(['success' => true, 'message' => 'Faculty assigned successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error assigning faculty']);
        }
        break;
        
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
}
?>