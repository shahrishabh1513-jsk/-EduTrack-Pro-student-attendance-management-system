<?php
/**
 * EduTrack Pro - Functions File
 * Helper functions for the application
 */

// ==================== USER FUNCTIONS ====================

/**
 * Get user data by ID
 */
function getUserData($user_id) {
    global $conn;
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
              END as cgpa,
              CASE 
                  WHEN u.role = 'student' THEN s.semester
                  ELSE NULL
              END as semester
              FROM users u
              LEFT JOIN students s ON u.id = s.user_id
              LEFT JOIN faculty f ON u.id = f.user_id
              WHERE u.id = $user_id";
    
    $result = mysqli_query($conn, $query);
    return mysqli_fetch_assoc($result);
}

/**
 * Get user by email
 */
function getUserByEmail($email) {
    global $conn;
    $email = sanitize($email);
    $query = "SELECT * FROM users WHERE email = '$email'";
    $result = mysqli_query($conn, $query);
    return mysqli_fetch_assoc($result);
}

/**
 * Get user by username
 */
function getUserByUsername($username) {
    global $conn;
    $username = sanitize($username);
    $query = "SELECT * FROM users WHERE username = '$username'";
    $result = mysqli_query($conn, $query);
    return mysqli_fetch_assoc($result);
}

/**
 * Update user last login
 */
function updateLastLogin($user_id) {
    global $conn;
    $query = "UPDATE users SET last_login = NOW() WHERE id = $user_id";
    return mysqli_query($conn, $query);
}

// ==================== SANITIZATION FUNCTIONS ====================

/**
 * Sanitize input string
 */
function sanitize($input) {
    global $conn;
    return mysqli_real_escape_string($conn, trim(htmlspecialchars($input)));
}

/**
 * Sanitize array
 */
function sanitizeArray($array) {
    return array_map('sanitize', $array);
}

/**
 * Validate email
 */
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

/**
 * Validate phone number (Indian)
 */
function validatePhone($phone) {
    return preg_match('/^[6-9]\d{9}$/', $phone);
}

/**
 * Validate roll number
 */
function validateRollNumber($roll) {
    return preg_match('/^[A-Z0-9]{4,10}$/i', $roll);
}

/**
 * Generate random string
 */
function generateRandomString($length = 10) {
    return substr(str_shuffle(str_repeat($x='0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil($length/strlen($x)))), 1, $length);
}

/**
 * Generate unique ID
 */
function generateUniqueId($prefix = '') {
    return $prefix . uniqid() . '_' . bin2hex(random_bytes(8));
}

// ==================== REDIRECTION FUNCTIONS ====================

/**
 * Redirect to URL
 */
function redirect($url) {
    header("Location: " . SITE_URL . $url);
    exit();
}

/**
 * Redirect back
 */
function redirectBack() {
    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit();
}

/**
 * Redirect with message
 */
function redirectWithMessage($url, $message, $type = 'success') {
    $_SESSION['flash_message'] = $message;
    $_SESSION['flash_type'] = $type;
    redirect($url);
}

// ==================== FILE UPLOAD FUNCTIONS ====================

/**
 * Upload file
 */
function uploadFile($file, $type = 'document') {
    global $UPLOAD_TYPES;
    
    if (!isset($UPLOAD_TYPES[$type])) {
        return ['success' => false, 'message' => 'Invalid upload type'];
    }
    
    $config = $UPLOAD_TYPES[$type];
    $filename = $file['name'];
    $file_tmp = $file['tmp_name'];
    $file_size = $file['size'];
    $file_error = $file['error'];
    
    // Check for errors
    if ($file_error !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'File upload error'];
    }
    
    // Check file size
    if ($file_size > $config['max_size']) {
        return ['success' => false, 'message' => 'File too large. Max size: ' . ($config['max_size'] / 1024 / 1024) . 'MB'];
    }
    
    // Check file extension
    $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    if (!in_array($extension, $config['allowed'])) {
        return ['success' => false, 'message' => 'Invalid file type. Allowed: ' . implode(', ', $config['allowed'])];
    }
    
    // Generate unique filename
    $new_filename = generateUniqueId() . '.' . $extension;
    $upload_path = $config['path'] . $new_filename;
    
    // Create directory if not exists
    if (!is_dir($config['path'])) {
        mkdir($config['path'], 0777, true);
    }
    
    // Move uploaded file
    if (move_uploaded_file($file_tmp, $upload_path)) {
        return ['success' => true, 'filename' => $new_filename, 'path' => $upload_path];
    }
    
    return ['success' => false, 'message' => 'Failed to upload file'];
}

/**
 * Delete file
 */
function deleteFile($filename, $type = 'document') {
    global $UPLOAD_TYPES;
    
    if (!isset($UPLOAD_TYPES[$type])) {
        return false;
    }
    
    $file_path = $UPLOAD_TYPES[$type]['path'] . $filename;
    
    if (file_exists($file_path)) {
        return unlink($file_path);
    }
    
    return false;
}

// ==================== DATE FUNCTIONS ====================

/**
 * Format date
 */
function formatDate($date, $format = 'd/m/Y') {
    if (!$date || $date == '0000-00-00') {
        return 'N/A';
    }
    return date($format, strtotime($date));
}

/**
 * Format datetime
 */
function formatDateTime($datetime, $format = 'd/m/Y h:i A') {
    if (!$datetime) {
        return 'N/A';
    }
    return date($format, strtotime($datetime));
}

/**
 * Calculate age from date of birth
 */
function calculateAge($dob) {
    if (!$dob) return 0;
    $birthDate = new DateTime($dob);
    $today = new DateTime('today');
    $age = $birthDate->diff($today)->y;
    return $age;
}

/**
 * Get days difference
 */
function daysDifference($date1, $date2) {
    $d1 = new DateTime($date1);
    $d2 = new DateTime($date2);
    $diff = $d1->diff($d2);
    return $diff->days;
}

// ==================== NUMBER FUNCTIONS ====================

/**
 * Format currency
 */
function formatCurrency($amount) {
    return '₹' . number_format($amount, 2);
}

/**
 * Format percentage
 */
function formatPercentage($value) {
    return round($value, 2) . '%';
}

/**
 * Calculate CGPA from grades
 */
function calculateCGPA($grades) {
    global $GRADE_POINTS;
    $total_points = 0;
    $total_credits = 0;
    
    foreach ($grades as $grade) {
        $total_points += $GRADE_POINTS[$grade['grade']] * $grade['credits'];
        $total_credits += $grade['credits'];
    }
    
    if ($total_credits > 0) {
        return round($total_points / $total_credits, 2);
    }
    
    return 0;
}

/**
 * Calculate grade from marks
 */
function calculateGrade($marks, $max_marks = 100) {
    global $GRADE_POINTS, $GRADE_DESCRIPTIONS;
    
    $percentage = ($marks / $max_marks) * 100;
    
    if ($percentage >= 90) $grade = 'A+';
    elseif ($percentage >= 80) $grade = 'A';
    elseif ($percentage >= 70) $grade = 'B+';
    elseif ($percentage >= 60) $grade = 'B';
    elseif ($percentage >= 50) $grade = 'C+';
    elseif ($percentage >= 40) $grade = 'C';
    elseif ($percentage >= 35) $grade = 'D';
    else $grade = 'F';
    
    return [
        'grade' => $grade,
        'points' => $GRADE_POINTS[$grade],
        'description' => $GRADE_DESCRIPTIONS[$grade]
    ];
}

// ==================== LOGGING FUNCTIONS ====================

/**
 * Log system activity
 */
function logActivity($user_id, $action, $details = '', $level = 'info') {
    global $conn;
    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    
    $user_id = $user_id ?: 'NULL';
    $details = sanitize($details);
    
    $query = "INSERT INTO system_logs (user_id, action, details, ip_address, user_agent, level) 
              VALUES ($user_id, '$action', '$details', '$ip', '$user_agent', '$level')";
    
    return mysqli_query($conn, $query);
}

/**
 * Get flash message
 */
function getFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        $type = $_SESSION['flash_type'] ?? 'info';
        unset($_SESSION['flash_message']);
        unset($_SESSION['flash_type']);
        return ['message' => $message, 'type' => $type];
    }
    return null;
}

// ==================== EMAIL FUNCTIONS ====================

/**
 * Send email
 */
function sendEmail($to, $subject, $message, $is_html = true) {
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/" . ($is_html ? "html" : "plain") . ";charset=UTF-8" . "\r\n";
    $headers .= "From: " . SITE_NAME . " <" . SITE_EMAIL . ">" . "\r\n";
    
    return mail($to, $subject, $message, $headers);
}

/**
 * Send welcome email to new user
 */
function sendWelcomeEmail($email, $name, $role, $password = null) {
    $subject = "Welcome to " . SITE_NAME;
    $message = "
        <html>
        <head>
            <title>Welcome to " . SITE_NAME . "</title>
        </head>
        <body>
            <h2>Welcome to " . SITE_NAME . ", $name!</h2>
            <p>Your account has been created successfully.</p>
            <p><strong>Role:</strong> " . ucfirst($role) . "</p>
            <p><strong>Email:</strong> $email</p>";
    
    if ($password) {
        $message .= "<p><strong>Temporary Password:</strong> $password</p>
                     <p>Please change your password after first login.</p>";
    }
    
    $message .= "<p>Login here: <a href='" . SITE_URL . "login.php'>" . SITE_URL . "login.php</a></p>
                 <p>Best regards,<br>" . SITE_NAME . " Team</p>
                </body>
                </html>";
    
    return sendEmail($email, $subject, $message);
}

/**
 * Send password reset email
 */
function sendPasswordResetEmail($email, $token) {
    $reset_link = SITE_URL . "reset-password.php?token=" . $token;
    $subject = "Password Reset Request - " . SITE_NAME;
    $message = "
        <html>
        <head>
            <title>Password Reset Request</title>
        </head>
        <body>
            <h2>Password Reset Request</h2>
            <p>You requested to reset your password. Click the link below to reset it:</p>
            <p><a href='$reset_link'>$reset_link</a></p>
            <p>This link will expire in 1 hour.</p>
            <p>If you did not request this, please ignore this email.</p>
            <p>Best regards,<br>" . SITE_NAME . " Team</p>
        </body>
        </html>";
    
    return sendEmail($email, $subject, $message);
}

// ==================== PAGINATION FUNCTIONS ====================

/**
 * Generate pagination links
 */
function paginate($total_items, $current_page, $items_per_page = ITEMS_PER_PAGE) {
    $total_pages = ceil($total_items / $items_per_page);
    $offset = ($current_page - 1) * $items_per_page;
    
    return [
        'total_pages' => $total_pages,
        'current_page' => $current_page,
        'offset' => $offset,
        'items_per_page' => $items_per_page,
        'has_previous' => $current_page > 1,
        'has_next' => $current_page < $total_pages,
        'previous_page' => $current_page - 1,
        'next_page' => $current_page + 1
    ];
}

/**
 * Display pagination HTML
 */
function displayPagination($total_pages, $current_page, $url) {
    if ($total_pages <= 1) return '';
    
    $html = '<div class="pagination"><ul class="pagination-list">';
    
    // Previous button
    if ($current_page > 1) {
        $html .= '<li><a href="' . $url . '?page=' . ($current_page - 1) . '" class="page-link">&laquo; Previous</a></li>';
    }
    
    // Page numbers
    $start = max(1, $current_page - 2);
    $end = min($total_pages, $current_page + 2);
    
    if ($start > 1) {
        $html .= '<li><a href="' . $url . '?page=1" class="page-link">1</a></li>';
        if ($start > 2) $html .= '<li><span class="page-dots">...</span></li>';
    }
    
    for ($i = $start; $i <= $end; $i++) {
        $active = ($i == $current_page) ? 'active' : '';
        $html .= '<li><a href="' . $url . '?page=' . $i . '" class="page-link ' . $active . '">' . $i . '</a></li>';
    }
    
    if ($end < $total_pages) {
        if ($end < $total_pages - 1) $html .= '<li><span class="page-dots">...</span></li>';
        $html .= '<li><a href="' . $url . '?page=' . $total_pages . '" class="page-link">' . $total_pages . '</a></li>';
    }
    
    // Next button
    if ($current_page < $total_pages) {
        $html .= '<li><a href="' . $url . '?page=' . ($current_page + 1) . '" class="page-link">Next &raquo;</a></li>';
    }
    
    $html .= '</ul></div>';
    return $html;
}

// ==================== MISCELLANEOUS FUNCTIONS ====================

/**
 * Generate CSRF token
 */
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 */
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Get user role display name
 */
function getRoleDisplay($role) {
    $roles = [
        'student' => 'Student',
        'faculty' => 'Faculty Member',
        'admin' => 'Administrator'
    ];
    return $roles[$role] ?? $role;
}

/**
 * Get status badge HTML
 */
function getStatusBadge($status) {
    $badges = [
        'active' => '<span class="badge badge-success">Active</span>',
        'inactive' => '<span class="badge badge-warning">Inactive</span>',
        'pending' => '<span class="badge badge-warning">Pending</span>',
        'approved' => '<span class="badge badge-success">Approved</span>',
        'rejected' => '<span class="badge badge-danger">Rejected</span>',
        'completed' => '<span class="badge badge-info">Completed</span>',
        'verified' => '<span class="badge badge-success">Verified</span>'
    ];
    
    return $badges[$status] ?? '<span class="badge badge-secondary">' . ucfirst($status) . '</span>';
}

/**
 * Generate breadcrumb
 */
function generateBreadcrumb() {
    $path = trim($_SERVER['REQUEST_URI'], '/');
    $segments = explode('/', $path);
    $breadcrumb = '<div class="breadcrumb"><a href="' . SITE_URL . '">Home</a>';
    
    $url = '';
    foreach ($segments as $segment) {
        if ($segment == 'edutrack-pro') continue;
        $url .= '/' . $segment;
        $name = ucwords(str_replace(['.php', '-', '_'], ['', ' ', ' '], $segment));
        $breadcrumb .= ' / <a href="' . $url . '">' . $name . '</a>';
    }
    
    $breadcrumb .= '</div>';
    return $breadcrumb;
}

/**
 * Get current academic year
 */
function getCurrentAcademicYear() {
    $year = date('Y');
    $month = date('m');
    
    if ($month >= 6) {
        return $year . '-' . ($year + 1);
    } else {
        return ($year - 1) . '-' . $year;
    }
}

/**
 * Get current semester
 */
function getCurrentSemester() {
    $month = date('m');
    
    if ($month >= 1 && $month <= 5) {
        return 2; // Even semester (Jan-May)
    } else {
        return 1; // Odd semester (Jun-Dec)
    }
}

/**
 * Format file size
 */
function formatFileSize($bytes) {
    if ($bytes >= 1073741824) {
        return number_format($bytes / 1073741824, 2) . ' GB';
    } elseif ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        return number_format($bytes / 1024, 2) . ' KB';
    } else {
        return $bytes . ' bytes';
    }
}
?>