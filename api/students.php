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
        if (!hasRole('admin') && !hasRole('faculty')) {
            echo json_encode(['success' => false, 'message' => 'Permission denied']);
            exit();
        }
        
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 100;
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $offset = ($page - 1) * $limit;
        
        $search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
        $course = isset($_GET['course']) ? sanitize($_GET['course']) : '';
        $semester = isset($_GET['semester']) ? (int)$_GET['semester'] : 0;
        
        $where = [];
        if ($search) {
            $where[] = "(u.full_name LIKE '%$search%' OR s.roll_number LIKE '%$search%' OR u.email LIKE '%$search%')";
        }
        if ($course) {
            $where[] = "s.course = '$course'";
        }
        if ($semester > 0) {
            $where[] = "s.semester = $semester";
        }
        
        $where_clause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";
        
        $count_query = "SELECT COUNT(*) as total FROM students s JOIN users u ON s.user_id = u.id $where_clause";
        $count_result = mysqli_query($conn, $count_query);
        $total = mysqli_fetch_assoc($count_result)['total'];
        
        $query = "SELECT s.*, u.full_name, u.email, u.phone, u.username, u.address, u.status as user_status 
                  FROM students s 
                  JOIN users u ON s.user_id = u.id 
                  $where_clause 
                  ORDER BY s.roll_number 
                  LIMIT $offset, $limit";
        $result = mysqli_query($conn, $query);
        
        $students = [];
        while($row = mysqli_fetch_assoc($result)) {
            $students[] = $row;
        }
        
        echo json_encode([
            'success' => true, 
            'data' => $students,
            'pagination' => [
                'total' => $total,
                'page' => $page,
                'limit' => $limit,
                'total_pages' => ceil($total / $limit)
            ]
        ]);
        break;
        
    case 'add':
        if (!hasRole('admin')) {
            echo json_encode(['success' => false, 'message' => 'Permission denied']);
            exit();
        }
        
        $data = json_decode(file_get_contents('php://input'), true);
        
        mysqli_begin_transaction($conn);
        
        try {
            $username = sanitize($data['username']);
            $email = sanitize($data['email']);
            $password = md5($data['password']);
            $full_name = sanitize($data['full_name']);
            $phone = sanitize($data['phone']);
            $address = sanitize($data['address'] ?? '');
            
            $user_query = "INSERT INTO users (username, email, password, role, full_name, phone, address) 
                           VALUES ('$username', '$email', '$password', 'student', '$full_name', '$phone', '$address')";
            mysqli_query($conn, $user_query);
            $user_id = mysqli_insert_id($conn);
            
            $roll_number = sanitize($data['roll_number']);
            $enrollment_number = sanitize($data['enrollment_number']);
            $course = sanitize($data['course']);
            $semester = (int)$data['semester'];
            $batch = sanitize($data['batch']);
            $father_name = sanitize($data['father_name'] ?? '');
            $mother_name = sanitize($data['mother_name'] ?? '');
            $date_of_birth = sanitize($data['date_of_birth'] ?? '');
            $gender = sanitize($data['gender'] ?? 'Male');
            $admission_date = sanitize($data['admission_date'] ?? date('Y-m-d'));
            
            $student_query = "INSERT INTO students (user_id, roll_number, enrollment_number, course, semester, batch, 
                              father_name, mother_name, date_of_birth, gender, admission_date) 
                              VALUES ($user_id, '$roll_number', '$enrollment_number', '$course', $semester, '$batch', 
                              '$father_name', '$mother_name', '$date_of_birth', '$gender', '$admission_date')";
            mysqli_query($conn, $student_query);
            
            mysqli_commit($conn);
            echo json_encode(['success' => true, 'message' => 'Student added successfully', 'student_id' => mysqli_insert_id($conn)]);
        } catch(Exception $e) {
            mysqli_rollback($conn);
            echo json_encode(['success' => false, 'message' => 'Error adding student: ' . $e->getMessage()]);
        }
        break;
        
    case 'get':
        $id = (int)$_GET['id'];
        $query = "SELECT s.*, u.full_name, u.email, u.phone, u.username, u.address 
                  FROM students s 
                  JOIN users u ON s.user_id = u.id 
                  WHERE s.id = $id";
        $result = mysqli_query($conn, $query);
        $student = mysqli_fetch_assoc($result);
        
        if ($student) {
            // Get enrolled courses
            $courses_query = "SELECT c.*, sc.grade, sc.marks_obtained, sc.status as enrollment_status 
                              FROM student_courses sc 
                              JOIN courses c ON sc.course_id = c.id 
                              WHERE sc.student_id = $id";
            $courses_result = mysqli_query($conn, $courses_query);
            $courses = [];
            while($course = mysqli_fetch_assoc($courses_result)) {
                $courses[] = $course;
            }
            $student['courses'] = $courses;
            
            echo json_encode(['success' => true, 'data' => $student]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Student not found']);
        }
        break;
        
    case 'update':
        if (!hasRole('admin')) {
            echo json_encode(['success' => false, 'message' => 'Permission denied']);
            exit();
        }
        
        $data = json_decode(file_get_contents('php://input'), true);
        $id = (int)$data['id'];
        
        $student_query = "SELECT user_id FROM students WHERE id = $id";
        $student_result = mysqli_query($conn, $student_query);
        $student = mysqli_fetch_assoc($student_result);
        
        if (!$student) {
            echo json_encode(['success' => false, 'message' => 'Student not found']);
            exit();
        }
        
        $user_id = $student['user_id'];
        
        $update_user = "UPDATE users SET 
                        full_name = '{$data['full_name']}',
                        email = '{$data['email']}',
                        phone = '{$data['phone']}',
                        address = '{$data['address']}'
                        WHERE id = $user_id";
        mysqli_query($conn, $update_user);
        
        $update_student = "UPDATE students SET 
                           roll_number = '{$data['roll_number']}',
                           enrollment_number = '{$data['enrollment_number']}',
                           course = '{$data['course']}',
                           semester = {$data['semester']},
                           batch = '{$data['batch']}',
                           father_name = '{$data['father_name']}',
                           mother_name = '{$data['mother_name']}',
                           date_of_birth = '{$data['date_of_birth']}',
                           gender = '{$data['gender']}'
                           WHERE id = $id";
        
        if(mysqli_query($conn, $update_student)) {
            echo json_encode(['success' => true, 'message' => 'Student updated successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error updating student']);
        }
        break;
        
    case 'delete':
        if (!hasRole('admin')) {
            echo json_encode(['success' => false, 'message' => 'Permission denied']);
            exit();
        }
        
        $id = (int)$_GET['id'];
        
        $student_query = "SELECT user_id FROM students WHERE id = $id";
        $student_result = mysqli_query($conn, $student_query);
        $student = mysqli_fetch_assoc($student_result);
        
        if($student) {
            $delete_query = "DELETE FROM users WHERE id = {$student['user_id']}";
            if(mysqli_query($conn, $delete_query)) {
                echo json_encode(['success' => true, 'message' => 'Student deleted successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error deleting student']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Student not found']);
        }
        break;
        
    case 'promote':
        if (!hasRole('admin')) {
            echo json_encode(['success' => false, 'message' => 'Permission denied']);
            exit();
        }
        
        $data = json_decode(file_get_contents('php://input'), true);
        $student_ids = $data['student_ids'];
        $new_semester = (int)$data['new_semester'];
        
        $ids = implode(',', array_map('intval', $student_ids));
        $query = "UPDATE students SET semester = $new_semester WHERE id IN ($ids)";
        
        if(mysqli_query($conn, $query)) {
            echo json_encode(['success' => true, 'message' => 'Students promoted successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error promoting students']);
        }
        break;
        
    case 'statistics':
        $query = "SELECT 
                  COUNT(*) as total,
                  SUM(CASE WHEN gender = 'Male' THEN 1 ELSE 0 END) as male,
                  SUM(CASE WHEN gender = 'Female' THEN 1 ELSE 0 END) as female,
                  AVG(cgpa) as avg_cgpa
                  FROM students";
        $result = mysqli_query($conn, $query);
        $stats = mysqli_fetch_assoc($result);
        
        $dept_query = "SELECT course, COUNT(*) as count FROM students GROUP BY course";
        $dept_result = mysqli_query($conn, $dept_query);
        $department_stats = [];
        while($row = mysqli_fetch_assoc($dept_result)) {
            $department_stats[] = $row;
        }
        
        echo json_encode([
            'success' => true,
            'data' => [
                'overall' => $stats,
                'by_department' => $department_stats
            ]
        ]);
        break;
        
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
}
?>