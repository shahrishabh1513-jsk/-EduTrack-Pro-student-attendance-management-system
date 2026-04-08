<?php
require_once '../includes/config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch($action) {
    case 'login':
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $username = sanitize($_POST['username']);
            $password = md5($_POST['password']);
            
            $query = "SELECT u.*, 
                      CASE 
                          WHEN u.role = 'student' THEN s.roll_number
                          WHEN u.role = 'faculty' THEN f.employee_id
                          ELSE NULL
                      END as id_number,
                      CASE 
                          WHEN u.role = 'student' THEN s.course
                          WHEN u.role = 'faculty' THEN f.department
                          ELSE NULL
                      END as department,
                      CASE 
                          WHEN u.role = 'student' THEN s.cgpa
                          ELSE NULL
                      END as cgpa
                      FROM users u
                      LEFT JOIN students s ON u.id = s.user_id
                      LEFT JOIN faculty f ON u.id = f.user_id
                      WHERE (u.username = '$username' OR u.email = '$username') 
                      AND u.password = '$password' 
                      AND u.status = 'active'";
            
            $result = mysqli_query($conn, $query);
            
            if (mysqli_num_rows($result) > 0) {
                $user = mysqli_fetch_assoc($result);
                
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['id_number'] = $user['id_number'];
                $_SESSION['department'] = $user['department'];
                
                echo json_encode([
                    'success' => true,
                    'role' => $user['role'],
                    'user' => [
                        'id' => $user['id'],
                        'name' => $user['full_name'],
                        'email' => $user['email'],
                        'role' => $user['role'],
                        'id_number' => $user['id_number'],
                        'department' => $user['department'],
                        'cgpa' => $user['cgpa']
                    ],
                    'message' => 'Login successful'
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Invalid username or password'
                ]);
            }
        }
        break;
        
    case 'logout':
        session_destroy();
        echo json_encode(['success' => true, 'message' => 'Logged out successfully']);
        break;
        
    case 'check_session':
        if (isLoggedIn()) {
            echo json_encode([
                'success' => true,
                'logged_in' => true,
                'user' => [
                    'id' => $_SESSION['user_id'],
                    'name' => $_SESSION['full_name'],
                    'role' => $_SESSION['role'],
                    'id_number' => $_SESSION['id_number'] ?? ''
                ]
            ]);
        } else {
            echo json_encode(['success' => true, 'logged_in' => false]);
        }
        break;
        
    case 'change_password':
        if (!isLoggedIn()) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit();
        }
        
        $data = json_decode(file_get_contents('php://input'), true);
        $old_password = md5($data['old_password']);
        $new_password = md5($data['new_password']);
        $user_id = $_SESSION['user_id'];
        
        $check_query = "SELECT id FROM users WHERE id = $user_id AND password = '$old_password'";
        $check_result = mysqli_query($conn, $check_query);
        
        if (mysqli_num_rows($check_result) > 0) {
            $update_query = "UPDATE users SET password = '$new_password' WHERE id = $user_id";
            if (mysqli_query($conn, $update_query)) {
                echo json_encode(['success' => true, 'message' => 'Password changed successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error changing password']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Current password is incorrect']);
        }
        break;
        
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
}
?>