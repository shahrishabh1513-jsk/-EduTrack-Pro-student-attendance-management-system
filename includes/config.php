<?php
// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Error reporting - REMOVE THIS IN PRODUCTION
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'edutrack_db');

// Site configuration
define('SITE_NAME', 'EduTrack Pro');
define('SITE_URL', 'http://localhost/edutrack-pro/');

// Create connection
$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Set charset
mysqli_set_charset($conn, "utf8mb4");

// Simple user data function
function getUserData($conn, $user_id) {
    $query = "SELECT u.*, 
              CASE 
                  WHEN u.role = 'student' THEN s.roll_number
                  WHEN u.role = 'faculty' THEN f.employee_id
                  ELSE NULL
              END as id_number
              FROM users u
              LEFT JOIN students s ON u.id = s.user_id
              LEFT JOIN faculty f ON u.id = f.user_id
              WHERE u.id = $user_id";
    
    $result = mysqli_query($conn, $query);
    return mysqli_fetch_assoc($result);
}

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Check role
function hasRole($role) {
    return isset($_SESSION['role']) && $_SESSION['role'] == $role;
}

// Redirect function
function redirect($url) {
    header("Location: " . SITE_URL . $url);
    exit();
}

// Sanitize input
function sanitize($input) {
    global $conn;
    return mysqli_real_escape_string($conn, trim(htmlspecialchars($input)));
}
?>