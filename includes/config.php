<?php
/**
 * EduTrack Pro - Configuration File
 * Main configuration and initialization
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', dirname(__DIR__) . '/logs/error.log');

// Timezone
date_default_timezone_set('Asia/Kolkata');

// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'edutrack_db');

// Site configuration
define('SITE_NAME', 'EduTrack Pro');
define('SITE_URL', 'http://localhost/edutrack-pro/');
define('SITE_EMAIL', 'admin@edutrack.com');
define('ADMIN_EMAIL', 'admin@edutrack.com');

// Paths
define('BASE_PATH', dirname(__DIR__) . '/');
define('INCLUDES_PATH', BASE_PATH . 'includes/');
define('ASSETS_PATH', SITE_URL . 'assets/');
define('UPLOAD_PATH', BASE_PATH . 'uploads/');
define('LOG_PATH', BASE_PATH . 'logs/');

// Upload directories
define('PROFILE_PICS_PATH', UPLOAD_PATH . 'profile-pictures/');
define('DOCUMENTS_PATH', UPLOAD_PATH . 'documents/');
define('ASSIGNMENTS_PATH', UPLOAD_PATH . 'assignments/');
define('NOTICES_PATH', UPLOAD_PATH . 'notices/');
define('EXAM_MATERIALS_PATH', UPLOAD_PATH . 'exam-materials/');

// Security
define('SALT', 'edutrack_pro_salt_2024');
define('TOKEN_EXPIRY', 3600); // 1 hour
define('SESSION_TIMEOUT', 1800); // 30 minutes

// Pagination
define('ITEMS_PER_PAGE', 25);
define('ADMIN_ITEMS_PER_PAGE', 50);

// File upload limits
define('MAX_FILE_SIZE', 5242880); // 5MB
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'zip']);

// Email configuration
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USER', '');
define('SMTP_PASS', '');
define('SMTP_ENCRYPTION', 'tls');

// API keys (for future use)
define('RECAPTCHA_SITE_KEY', '');
define('RECAPTCHA_SECRET_KEY', '');
define('GOOGLE_API_KEY', '');
define('RAZORPAY_KEY_ID', '');
define('RAZORPAY_KEY_SECRET', '');

// Create database connection
$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Set charset
mysqli_set_charset($conn, "utf8mb4");

// Include required files
require_once INCLUDES_PATH . 'constants.php';
require_once INCLUDES_PATH . 'functions.php';
require_once INCLUDES_PATH . 'session.php';
require_once INCLUDES_PATH . 'validation.php';

// Initialize session management
initSession();

// Check if user is logged in for protected pages
$public_pages = ['login.php', 'register.php', 'forgot-password.php', 'reset-password.php'];
$current_page = basename($_SERVER['PHP_SELF']);

if (!in_array($current_page, $public_pages) && !isLoggedIn()) {
    redirect('login.php');
}

// Set timezone for database connection
mysqli_query($conn, "SET time_zone = '+05:30'");
?>