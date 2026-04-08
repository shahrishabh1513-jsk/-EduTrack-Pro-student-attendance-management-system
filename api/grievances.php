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
    case 'submit':
        if (!hasRole('student')) {
            echo json_encode(['success' => false, 'message' => 'Only students can submit grievances']);
            exit();
        }
        
        $data = json_decode(file_get_contents('php://input'), true);
        
        $student_query = "SELECT id FROM students WHERE user_id = {$_SESSION['user_id']}";
        $student_result = mysqli_query($conn, $student_query);
        $student = mysqli_fetch_assoc($student_result);
        $student_id = $student['id'];
        
        $category = sanitize($data['category']);
        $title = sanitize($data['title']);
        $description = sanitize($data['description']);
        
        $query = "INSERT INTO grievances (student_id, category, title, description, status) 
                  VALUES ($student_id, '$category', '$title', '$description', 'pending')";
        
        if(mysqli_query($conn, $query)) {
            echo json_encode(['success' => true, 'message' => 'Grievance submitted successfully', 'grievance_id' => mysqli_insert_id($conn)]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error submitting grievance']);
        }
        break;
        
    case 'get_my':
        if (!hasRole('student')) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit();
        }
        
        $student_query = "SELECT id FROM students WHERE user_id = {$_SESSION['user_id']}";
        $student_result = mysqli_query($conn, $student_query);
        $student = mysqli_fetch_assoc($student_result);
        $student_id = $student['id'];
        
        $query = "SELECT * FROM grievances WHERE student_id = $student_id ORDER BY created_at DESC";
        $result = mysqli_query($conn, $query);
        $grievances = [];
        while($row = mysqli_fetch_assoc($result)) {
            $grievances[] = $row;
        }
        
        echo json_encode(['success' => true, 'data' => $grievances]);
        break;
        
    case 'get_all':
        if (!hasRole('admin') && !hasRole('faculty')) {
            echo json_encode(['success' => false, 'message' => 'Permission denied']);
            exit();
        }
        
        $status = isset($_GET['status']) ? sanitize($_GET['status']) : '';
        $category = isset($_GET['category']) ? sanitize($_GET['category']) : '';
        
        $where = [];
        if ($status) {
            $where[] = "g.status = '$status'";
        }
        if ($category) {
            $where[] = "g.category = '$category'";
        }
        
        $where_clause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";
        
        $query = "SELECT g.*, s.roll_number, u.full_name as student_name, u.email,
                  fu.full_name as assigned_to_name
                  FROM grievances g
                  JOIN students s ON g.student_id = s.id
                  JOIN users u ON s.user_id = u.id
                  LEFT JOIN faculty f ON g.assigned_to = f.id
                  LEFT JOIN users fu ON f.user_id = fu.id
                  $where_clause
                  ORDER BY g.created_at DESC";
        
        $result = mysqli_query($conn, $query);
        $grievances = [];
        while($row = mysqli_fetch_assoc($result)) {
            $grievances[] = $row;
        }
        
        echo json_encode(['success' => true, 'data' => $grievances]);
        break;
        
    case 'resolve':
        if (!hasRole('admin') && !hasRole('faculty')) {
            echo json_encode(['success' => false, 'message' => 'Permission denied']);
            exit();
        }
        
        $data = json_decode(file_get_contents('php://input'), true);
        $grievance_id = (int)$data['grievance_id'];
        $resolution = sanitize($data['resolution']);
        
        $faculty_query = "SELECT id FROM faculty WHERE user_id = {$_SESSION['user_id']}";
        $faculty_result = mysqli_query($conn, $faculty_query);
        $faculty = mysqli_fetch_assoc($faculty_result);
        $faculty_id = $faculty['id'] ?? null;
        
        $query = "UPDATE grievances 
                  SET status = 'resolved', resolution = '$resolution', assigned_to = " . ($faculty_id ?: 'NULL') . " 
                  WHERE id = $grievance_id";
        
        if(mysqli_query($conn, $query)) {
            echo json_encode(['success' => true, 'message' => 'Grievance resolved successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error resolving grievance']);
        }
        break;
        
    case 'assign':
        if (!hasRole('admin')) {
            echo json_encode(['success' => false, 'message' => 'Permission denied']);
            exit();
        }
        
        $data = json_decode(file_get_contents('php://input'), true);
        $grievance_id = (int)$data['grievance_id'];
        $assigned_to = (int)$data['assigned_to'];
        
        $query = "UPDATE grievances SET assigned_to = $assigned_to, status = 'in_progress' WHERE id = $grievance_id";
        
        if(mysqli_query($conn, $query)) {
            echo json_encode(['success' => true, 'message' => 'Grievance assigned successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error assigning grievance']);
        }
        break;
        
    case 'statistics':
        if (!hasRole('admin')) {
            echo json_encode(['success' => false, 'message' => 'Permission denied']);
            exit();
        }
        
        $query = "SELECT 
                  COUNT(*) as total,
                  SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                  SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END) as in_progress,
                  SUM(CASE WHEN status = 'resolved' THEN 1 ELSE 0 END) as resolved,
                  SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected,
                  SUM(CASE WHEN category = 'academic' THEN 1 ELSE 0 END) as academic,
                  SUM(CASE WHEN category = 'administrative' THEN 1 ELSE 0 END) as administrative,
                  SUM(CASE WHEN category = 'technical' THEN 1 ELSE 0 END) as technical,
                  SUM(CASE WHEN category = 'other' THEN 1 ELSE 0 END) as other
                  FROM grievances
                  WHERE YEAR(created_at) = YEAR(CURDATE())";
        
        $result = mysqli_query($conn, $query);
        $stats = mysqli_fetch_assoc($result);
        
        echo json_encode(['success' => true, 'data' => $stats]);
        break;
        
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
}
?>