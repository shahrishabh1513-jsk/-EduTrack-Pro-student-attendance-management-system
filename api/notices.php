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
    case 'get_all':
        $category = isset($_GET['category']) ? sanitize($_GET['category']) : '';
        $target_role = isset($_GET['role']) ? sanitize($_GET['role']) : '';
        
        $where = ["is_active = 1", "(expiry_date IS NULL OR expiry_date >= CURDATE())"];
        if ($category) {
            $where[] = "category = '$category'";
        }
        if ($target_role && $target_role !== 'all') {
            $where[] = "(target_role = 'all' OR target_role = '$target_role')";
        }
        
        $where_clause = "WHERE " . implode(" AND ", $where);
        
        $query = "SELECT n.*, u.full_name as created_by_name 
                  FROM notices n 
                  JOIN users u ON n.created_by = u.id 
                  $where_clause 
                  ORDER BY n.created_at DESC";
        
        $result = mysqli_query($conn, $query);
        $notices = [];
        while($row = mysqli_fetch_assoc($result)) {
            $notices[] = $row;
        }
        
        echo json_encode(['success' => true, 'data' => $notices]);
        break;
        
    case 'get_recent':
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 5;
        
        $query = "SELECT n.*, u.full_name as created_by_name 
                  FROM notices n 
                  JOIN users u ON n.created_by = u.id 
                  WHERE n.is_active = 1 AND (expiry_date IS NULL OR expiry_date >= CURDATE())
                  ORDER BY n.created_at DESC 
                  LIMIT $limit";
        
        $result = mysqli_query($conn, $query);
        $notices = [];
        while($row = mysqli_fetch_assoc($result)) {
            $notices[] = $row;
        }
        
        echo json_encode(['success' => true, 'data' => $notices]);
        break;
        
    case 'add':
        if (!hasRole('admin')) {
            echo json_encode(['success' => false, 'message' => 'Permission denied']);
            exit();
        }
        
        $data = json_decode(file_get_contents('php://input'), true);
        
        $title = sanitize($data['title']);
        $content = sanitize($data['content']);
        $category = sanitize($data['category']);
        $target_role = sanitize($data['target_role']);
        $expiry_date = isset($data['expiry_date']) ? sanitize($data['expiry_date']) : null;
        $created_by = $_SESSION['user_id'];
        
        $query = "INSERT INTO notices (title, content, category, target_role, created_by, expiry_date) 
                  VALUES ('$title', '$content', '$category', '$target_role', $created_by, " . ($expiry_date ? "'$expiry_date'" : "NULL") . ")";
        
        if(mysqli_query($conn, $query)) {
            echo json_encode(['success' => true, 'message' => 'Notice added successfully', 'notice_id' => mysqli_insert_id($conn)]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error adding notice']);
        }
        break;
        
    case 'update':
        if (!hasRole('admin')) {
            echo json_encode(['success' => false, 'message' => 'Permission denied']);
            exit();
        }
        
        $data = json_decode(file_get_contents('php://input'), true);
        $id = (int)$data['id'];
        
        $title = sanitize($data['title']);
        $content = sanitize($data['content']);
        $category = sanitize($data['category']);
        $target_role = sanitize($data['target_role']);
        $expiry_date = isset($data['expiry_date']) ? sanitize($data['expiry_date']) : null;
        $is_active = isset($data['is_active']) ? (int)$data['is_active'] : 1;
        
        $query = "UPDATE notices SET 
                  title = '$title',
                  content = '$content',
                  category = '$category',
                  target_role = '$target_role',
                  expiry_date = " . ($expiry_date ? "'$expiry_date'" : "NULL") . ",
                  is_active = $is_active
                  WHERE id = $id";
        
        if(mysqli_query($conn, $query)) {
            echo json_encode(['success' => true, 'message' => 'Notice updated successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error updating notice']);
        }
        break;
        
    case 'delete':
        if (!hasRole('admin')) {
            echo json_encode(['success' => false, 'message' => 'Permission denied']);
            exit();
        }
        
        $id = (int)$_GET['id'];
        
        $query = "DELETE FROM notices WHERE id = $id";
        if(mysqli_query($conn, $query)) {
            echo json_encode(['success' => true, 'message' => 'Notice deleted successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error deleting notice']);
        }
        break;
        
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
}
?>