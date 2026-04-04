<?php
// includes/config.php
session_start();

define('BASE_URL', 'http://localhost/edutrack-pro/');
define('BASE_PATH', 'F:/xampp/htdocs/edutrack-pro/');
define('UPLOAD_PATH', BASE_PATH . 'uploads/');

// Timezone
date_default_timezone_set('Asia/Kolkata');

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection
require_once BASE_PATH . 'database/connection.php';
$database = new Database();
$db = $database->getConnection();

// Include functions
require_once BASE_PATH . 'includes/functions.php';
require_once BASE_PATH . 'includes/session.php';
?>