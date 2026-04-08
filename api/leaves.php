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
    case 'apply':
        if (!hasRole('student')) {
            echo json_encode(['success' => false, 'message' => 'Only students can apply for leave']);
            exit();
        }
        
        $data = json_decode(file_get_contents('php://input'), true);
        
        $student_query = "SELECT id FROM students WHERE user_id = {$_SESSION['user_id']}";
        $student_result = mysqli_query($conn, $student_query);
        $student = mysqli_fetch_assoc($student_result);
        $student_id = $student['id'];
        
        $from_date = sanitize($data['from_date']);
        $to_date = sanitize($data['to_date']);
        $reason = sanitize($data['reason']);
        $leave_type = sanitize($data['leave_type']);
        $remarks = sanitize($data['emergency_contact'] ?? '');
        
        $query = "INSERT INTO leave_applications (student_id, from_date, to_date, reason, leave_type, remarks) 
                  VALUES ($student_id, '$from_date', '$to_date', '$reason', '$leave_type', '$remarks')";
        
        if(mysqli_query($conn, $query)) {
            echo json_encode(['success' => true, 'message' => 'Leave application submitted successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error submitting application']);
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
        
        $query = "SELECT l.*, u.full_name as approved_by_name 
                  FROM leave_applications l
                  LEFT JOIN faculty f ON l.approved_by = f.id
                  LEFT JOIN users u ON f.user_id = u.id
                  WHERE l.student_id = $student_id 
                  ORDER BY l.applied_at DESC";
        
        $result = mysqli_query($conn, $query);
        $leaves = [];
        while($row = mysqli_fetch_assoc($result)) {
            $leaves[] = $row;
        }
        
        echo json_encode(['success' => true, 'data' => $leaves]);
        break;
        
    case 'get_pending':
        if (!hasRole('faculty') && !hasRole('admin')) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit();
        }
        
        $query = "SELECT l.*, s.roll_number, u.full_name as student_name, u.email 
                  FROM leave_applications l
                  JOIN students s ON l.student_id = s.id
                  JOIN users u ON s.user_id = u.id
                  WHERE l.status = 'pending'
                  ORDER BY l.applied_at DESC";
        
        $result = mysqli_query($conn, $query);
        $leaves = [];
        while($row = mysqli_fetch_assoc($result)) {
            $leaves[] = $row;
        }
        
        echo json_encode(['success' => true, 'data' => $leaves]);
        break;
        
    case 'get_all':
        if (!hasRole('admin')) {
            echo json_encode(['success' => false, 'message' => 'Permission denied']);
            exit();
        }
        
        $status = isset($_GET['status']) ? sanitize($_GET['status']) : '';
        $from_date = isset($_GET['from_date']) ? sanitize($_GET['from_date']) : '';
        $to_date = isset($_GET['to_date']) ? sanitize($_GET['to_date']) : '';
        
        $where = [];
        if ($status) {
            $where[] = "l.status = '$status'";
        }
        if ($from_date) {
            $where[] = "l.from_date >= '$from_date'";
        }
        if ($to_date) {
            $where[] = "l.to_date <= '$to_date'";
        }
        
        $where_clause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";
        
        $query = "SELECT l.*, s.roll_number, u.full_name as student_name, u.email, 
                  fu.full_name as approved_by_name
                  FROM leave_applications l
                  JOIN students s ON l.student_id = s.id
                  JOIN users u ON s.user_id = u.id
                  LEFT JOIN faculty f ON l.approved_by = f.id
                  LEFT JOIN users fu ON f.user_id = fu.id
                  $where_clause
                  ORDER BY l.applied_at DESC";
        
        $result = mysqli_query($conn, $query);
        $leaves = [];
        while($row = mysqli_fetch_assoc($result)) {
            $leaves[] = $row;
        }
        
        echo json_encode(['success' => true, 'data' => $leaves]);
        break;
        
    case 'approve':
        if (!hasRole('faculty') && !hasRole('admin')) {
            echo json_encode(['success' => false, 'message' => 'Permission denied']);
            exit();
        }
        
        $data = json_decode(file_get_contents('php://input'), true);
        $leave_id = (int)$data['leave_id'];
        $status = sanitize($data['status']);
        $remarks = sanitize($data['remarks'] ?? '');
        
        $faculty_query = "SELECT id FROM faculty WHERE user_id = {$_SESSION['user_id']}";
        $faculty_result = mysqli_query($conn, $faculty_query);
        $faculty = mysqli_fetch_assoc($faculty_result);
        $faculty_id = $faculty['id'] ?? null;
        
        $query = "UPDATE leave_applications 
                  SET status = '$status', approved_by = " . ($faculty_id ?: 'NULL') . ", remarks = '$remarks' 
                  WHERE id = $leave_id";
        
        if(mysqli_query($conn, $query)) {
            echo json_encode(['success' => true, 'message' => 'Leave ' . $status . ' successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error updating leave']);
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
                  SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved,
                  SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected,
                  SUM(CASE WHEN leave_type = 'medical' THEN 1 ELSE 0 END) as medical,
                  SUM(CASE WHEN leave_type = 'emergency' THEN 1 ELSE 0 END) as emergency,
                  SUM(CASE WHEN leave_type = 'personal' THEN 1 ELSE 0 END) as personal,
                  SUM(CASE WHEN leave_type = 'vacation' THEN 1 ELSE 0 END) as vacation
                  FROM leave_applications
                  WHERE YEAR(applied_at) = YEAR(CURDATE())";
        
        $result = mysqli_query($conn, $query);
        $stats = mysqli_fetch_assoc($result);
        
        echo json_encode(['success' => true, 'data' => $stats]);
        break;
        
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
}
?>