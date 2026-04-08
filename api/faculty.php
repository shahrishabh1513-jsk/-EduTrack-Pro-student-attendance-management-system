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
        if (!hasRole('admin')) {
            echo json_encode(['success' => false, 'message' => 'Permission denied']);
            exit();
        }
        
        $search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
        $department = isset($_GET['department']) ? sanitize($_GET['department']) : '';
        
        $where = [];
        if ($search) {
            $where[] = "(u.full_name LIKE '%$search%' OR f.employee_id LIKE '%$search%')";
        }
        if ($department) {
            $where[] = "f.department = '$department'";
        }
        
        $where_clause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";
        
        $query = "SELECT f.*, u.full_name, u.email, u.phone, u.username, u.address 
                  FROM faculty f 
                  JOIN users u ON f.user_id = u.id 
                  $where_clause 
                  ORDER BY f.employee_id";
        $result = mysqli_query($conn, $query);
        
        $faculty = [];
        while($row = mysqli_fetch_assoc($result)) {
            $faculty[] = $row;
        }
        
        echo json_encode(['success' => true, 'data' => $faculty]);
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
                           VALUES ('$username', '$email', '$password', 'faculty', '$full_name', '$phone', '$address')";
            mysqli_query($conn, $user_query);
            $user_id = mysqli_insert_id($conn);
            
            $employee_id = sanitize($data['employee_id']);
            $department = sanitize($data['department']);
            $designation = sanitize($data['designation']);
            $qualification = sanitize($data['qualification']);
            $specialization = sanitize($data['specialization'] ?? '');
            $experience = (int)$data['experience'];
            $joining_date = sanitize($data['joining_date'] ?? date('Y-m-d'));
            
            $faculty_query = "INSERT INTO faculty (user_id, employee_id, department, designation, qualification, specialization, experience, joining_date) 
                              VALUES ($user_id, '$employee_id', '$department', '$designation', '$qualification', '$specialization', $experience, '$joining_date')";
            mysqli_query($conn, $faculty_query);
            
            mysqli_commit($conn);
            echo json_encode(['success' => true, 'message' => 'Faculty added successfully']);
        } catch(Exception $e) {
            mysqli_rollback($conn);
            echo json_encode(['success' => false, 'message' => 'Error adding faculty']);
        }
        break;
        
    case 'get':
        $id = (int)$_GET['id'];
        $query = "SELECT f.*, u.full_name, u.email, u.phone, u.username, u.address 
                  FROM faculty f 
                  JOIN users u ON f.user_id = u.id 
                  WHERE f.id = $id";
        $result = mysqli_query($conn, $query);
        $faculty = mysqli_fetch_assoc($result);
        
        if ($faculty) {
            // Get assigned courses
            $courses_query = "SELECT c.*, ca.semester, ca.academic_year 
                              FROM course_assignments ca 
                              JOIN courses c ON ca.course_id = c.id 
                              WHERE ca.faculty_id = $id";
            $courses_result = mysqli_query($conn, $courses_query);
            $courses = [];
            while($course = mysqli_fetch_assoc($courses_result)) {
                $courses[] = $course;
            }
            $faculty['courses'] = $courses;
            
            echo json_encode(['success' => true, 'data' => $faculty]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Faculty not found']);
        }
        break;
        
    case 'update':
        if (!hasRole('admin')) {
            echo json_encode(['success' => false, 'message' => 'Permission denied']);
            exit();
        }
        
        $data = json_decode(file_get_contents('php://input'), true);
        $id = (int)$data['id'];
        
        $faculty_query = "SELECT user_id FROM faculty WHERE id = $id";
        $faculty_result = mysqli_query($conn, $faculty_query);
        $faculty = mysqli_fetch_assoc($faculty_result);
        
        if (!$faculty) {
            echo json_encode(['success' => false, 'message' => 'Faculty not found']);
            exit();
        }
        
        $user_id = $faculty['user_id'];
        
        $update_user = "UPDATE users SET 
                        full_name = '{$data['full_name']}',
                        email = '{$data['email']}',
                        phone = '{$data['phone']}',
                        address = '{$data['address']}'
                        WHERE id = $user_id";
        mysqli_query($conn, $update_user);
        
        $update_faculty = "UPDATE faculty SET 
                           employee_id = '{$data['employee_id']}',
                           department = '{$data['department']}',
                           designation = '{$data['designation']}',
                           qualification = '{$data['qualification']}',
                           specialization = '{$data['specialization']}',
                           experience = {$data['experience']},
                           joining_date = '{$data['joining_date']}'
                           WHERE id = $id";
        
        if(mysqli_query($conn, $update_faculty)) {
            echo json_encode(['success' => true, 'message' => 'Faculty updated successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error updating faculty']);
        }
        break;
        
    case 'delete':
        if (!hasRole('admin')) {
            echo json_encode(['success' => false, 'message' => 'Permission denied']);
            exit();
        }
        
        $id = (int)$_GET['id'];
        
        $faculty_query = "SELECT user_id FROM faculty WHERE id = $id";
        $faculty_result = mysqli_query($conn, $faculty_query);
        $faculty = mysqli_fetch_assoc($faculty_result);
        
        if($faculty) {
            $delete_query = "DELETE FROM users WHERE id = {$faculty['user_id']}";
            if(mysqli_query($conn, $delete_query)) {
                echo json_encode(['success' => true, 'message' => 'Faculty deleted successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error deleting faculty']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Faculty not found']);
        }
        break;
        
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
}
?>