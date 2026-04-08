<?php
require_once '../includes/config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
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
            echo json_encode(['success' => false, 'message' => 'Only students can submit feedback']);
            exit();
        }
        
        $data = json_decode(file_get_contents('php://input'), true);
        
        $student_query = "SELECT id FROM students WHERE user_id = {$_SESSION['user_id']}";
        $student_result = mysqli_query($conn, $student_query);
        $student = mysqli_fetch_assoc($student_result);
        $student_id = $student['id'];
        
        $category = sanitize($data['category']);
        $faculty_id = isset($data['faculty_id']) ? (int)$data['faculty_id'] : null;
        $course_id = isset($data['course_id']) ? (int)$data['course_id'] : null;
        $rating = (int)$data['rating'];
        $comments = sanitize($data['comments']);
        
        $query = "INSERT INTO feedback (student_id, faculty_id, course_id, rating, comments, category) 
                  VALUES ($student_id, " . ($faculty_id ?: 'NULL') . ", " . ($course_id ?: 'NULL') . ", $rating, '$comments', '$category')";
        
        if(mysqli_query($conn, $query)) {
            echo json_encode(['success' => true, 'message' => 'Feedback submitted successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error submitting feedback']);
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
        
        $query = "SELECT f.*, 
                  fu.full_name as faculty_name,
                  c.course_name
                  FROM feedback f
                  LEFT JOIN faculty fa ON f.faculty_id = fa.id
                  LEFT JOIN users fu ON fa.user_id = fu.id
                  LEFT JOIN courses c ON f.course_id = c.id
                  WHERE f.student_id = $student_id
                  ORDER BY f.created_at DESC";
        
        $result = mysqli_query($conn, $query);
        $feedback = [];
        while($row = mysqli_fetch_assoc($result)) {
            $feedback[] = $row;
        }
        
        echo json_encode(['success' => true, 'data' => $feedback]);
        break;
        
    case 'get_all':
        if (!hasRole('admin') && !hasRole('faculty')) {
            echo json_encode(['success' => false, 'message' => 'Permission denied']);
            exit();
        }
        
        $category = isset($_GET['category']) ? sanitize($_GET['category']) : '';
        $rating = isset($_GET['rating']) ? (int)$_GET['rating'] : 0;
        
        $where = [];
        if ($category) {
            $where[] = "f.category = '$category'";
        }
        if ($rating > 0) {
            $where[] = "f.rating = $rating";
        }
        
        $where_clause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";
        
        $query = "SELECT f.*, s.roll_number, u.full_name as student_name,
                  fu.full_name as faculty_name,
                  c.course_name
                  FROM feedback f
                  JOIN students s ON f.student_id = s.id
                  JOIN users u ON s.user_id = u.id
                  LEFT JOIN faculty fa ON f.faculty_id = fa.id
                  LEFT JOIN users fu ON fa.user_id = fu.id
                  LEFT JOIN courses c ON f.course_id = c.id
                  $where_clause
                  ORDER BY f.created_at DESC";
        
        $result = mysqli_query($conn, $query);
        $feedback = [];
        while($row = mysqli_fetch_assoc($result)) {
            $feedback[] = $row;
        }
        
        echo json_encode(['success' => true, 'data' => $feedback]);
        break;
        
    case 'get_for_faculty':
        if (!hasRole('faculty')) {
            echo json_encode(['success' => false, 'message' => 'Permission denied']);
            exit();
        }
        
        $faculty_query = "SELECT id FROM faculty WHERE user_id = {$_SESSION['user_id']}";
        $faculty_result = mysqli_query($conn, $faculty_query);
        $faculty = mysqli_fetch_assoc($faculty_result);
        $faculty_id = $faculty['id'];
        
        $query = "SELECT f.*, s.roll_number, u.full_name as student_name
                  FROM feedback f
                  JOIN students s ON f.student_id = s.id
                  JOIN users u ON s.user_id = u.id
                  WHERE f.faculty_id = $faculty_id
                  ORDER BY f.created_at DESC";
        
        $result = mysqli_query($conn, $query);
        $feedback = [];
        while($row = mysqli_fetch_assoc($result)) {
            $feedback[] = $row;
        }
        
        echo json_encode(['success' => true, 'data' => $feedback]);
        break;
        
    case 'statistics':
        if (!hasRole('admin')) {
            echo json_encode(['success' => false, 'message' => 'Permission denied']);
            exit();
        }
        
        $query = "SELECT 
                  COUNT(*) as total,
                  AVG(rating) as avg_rating,
                  SUM(CASE WHEN rating >= 4 THEN 1 ELSE 0 END) as positive,
                  SUM(CASE WHEN rating <= 2 THEN 1 ELSE 0 END) as negative,
                  SUM(CASE WHEN category = 'faculty' THEN 1 ELSE 0 END) as faculty_feedback,
                  SUM(CASE WHEN category = 'course' THEN 1 ELSE 0 END) as course_feedback,
                  SUM(CASE WHEN category = 'general' THEN 1 ELSE 0 END) as general_feedback
                  FROM feedback
                  WHERE YEAR(created_at) = YEAR(CURDATE())";
        
        $result = mysqli_query($conn, $query);
        $stats = mysqli_fetch_assoc($result);
        $stats['avg_rating'] = round($stats['avg_rating'], 1);
        
        echo json_encode(['success' => true, 'data' => $stats]);
        break;
        
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
}
?>