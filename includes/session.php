<?php
/**
 * EduTrack Pro - Session Management
 * Handles user session operations
 */

/**
 * Initialize session
 */
function initSession() {
    // Check session timeout
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > SESSION_TIMEOUT)) {
        destroySession();
    }
    
    // Update last activity time
    $_SESSION['last_activity'] = time();
    
    // Regenerate session ID periodically
    if (!isset($_SESSION['session_regenerated'])) {
        session_regenerate_id(true);
        $_SESSION['session_regenerated'] = true;
    }
}

/**
 * Set session variables for logged in user
 */
function setUserSession($user) {
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['full_name'] = $user['full_name'];
    $_SESSION['role'] = $user['role'];
    $_SESSION['id_number'] = $user['id_number'] ?? '';
    $_SESSION['department'] = $user['department'] ?? '';
    $_SESSION['logged_in'] = true;
    $_SESSION['login_time'] = time();
    
    // Update last login in database
    updateLastLogin($user['id']);
}

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
}

/**
 * Check if user has specific role
 */
function hasRole($role) {
    if (!isLoggedIn()) return false;
    
    if (is_array($role)) {
        return in_array($_SESSION['role'], $role);
    }
    
    return $_SESSION['role'] === $role;
}

/**
 * Check if user is admin
 */
function isAdmin() {
    return hasRole('admin');
}

/**
 * Check if user is faculty
 */
function isFaculty() {
    return hasRole('faculty');
}

/**
 * Check if user is student
 */
function isStudent() {
    return hasRole('student');
}

/**
 * Get current user ID
 */
function getCurrentUserId() {
    return $_SESSION['user_id'] ?? null;
}

/**
 * Get current user role
 */
function getCurrentUserRole() {
    return $_SESSION['role'] ?? null;
}

/**
 * Get current user name
 */
function getCurrentUserName() {
    return $_SESSION['full_name'] ?? 'Guest';
}

/**
 * Get current user email
 */
function getCurrentUserEmail() {
    return $_SESSION['user_email'] ?? '';
}

/**
 * Get current user ID number (roll/employee)
 */
function getCurrentUserIdNumber() {
    return $_SESSION['id_number'] ?? '';
}

/**
 * Destroy session (logout)
 */
function destroySession() {
    $_SESSION = array();
    
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    
    session_destroy();
}

/**
 * Require login - redirect if not logged in
 */
function requireLogin() {
    if (!isLoggedIn()) {
        redirect('login.php');
    }
}

/**
 * Require specific role - redirect if not authorized
 */
function requireRole($role) {
    requireLogin();
    
    if (!hasRole($role)) {
        redirect('unauthorized.php');
    }
}

/**
 * Require admin access
 */
function requireAdmin() {
    requireRole('admin');
}

/**
 * Require faculty access
 */
function requireFaculty() {
    requireRole('faculty');
}

/**
 * Require student access
 */
function requireStudent() {
    requireRole('student');
}

/**
 * Set flash message
 */
function setFlashMessage($message, $type = 'success') {
    $_SESSION['flash_message'] = $message;
    $_SESSION['flash_type'] = $type;
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

/**
 * Display flash message
 */
function displayFlashMessage() {
    $flash = getFlashMessage();
    if ($flash) {
        $type = $flash['type'];
        $message = $flash['message'];
        echo "<div class='alert alert-$type'>$message</div>";
    }
}

/**
 * Check if user has permission for specific action
 */
function hasPermission($permission) {
    if (!isLoggedIn()) return false;
    
    // Admin has all permissions
    if (isAdmin()) return true;
    
    // Define role-based permissions
    $permissions = [
        'faculty' => [
            'view_students', 'mark_attendance', 'view_attendance', 'upload_material',
            'create_assignment', 'grade_assignment', 'enter_marks', 'view_results',
            'approve_leave', 'review_grievance'
        ],
        'student' => [
            'view_profile', 'edit_profile', 'view_attendance', 'view_results',
            'apply_leave', 'submit_grievance', 'submit_feedback', 'view_notices',
            'download_material', 'submit_assignment', 'fill_exam_form'
        ]
    ];
    
    $role = getCurrentUserRole();
    return isset($permissions[$role]) && in_array($permission, $permissions[$role]);
}

/**
 * Generate session token for API
 */
function generateApiToken($user_id) {
    $token = bin2hex(random_bytes(32));
    $expiry = time() + TOKEN_EXPIRY;
    
    $_SESSION['api_token'] = $token;
    $_SESSION['api_token_expiry'] = $expiry;
    
    return $token;
}

/**
 * Verify API token
 */
function verifyApiToken($token) {
    if (!isset($_SESSION['api_token']) || !isset($_SESSION['api_token_expiry'])) {
        return false;
    }
    
    if ($_SESSION['api_token_expiry'] < time()) {
        unset($_SESSION['api_token']);
        unset($_SESSION['api_token_expiry']);
        return false;
    }
    
    return hash_equals($_SESSION['api_token'], $token);
}

/**
 * Clear user session data
 */
function clearUserSession() {
    unset($_SESSION['user_id']);
    unset($_SESSION['username']);
    unset($_SESSION['user_email']);
    unset($_SESSION['full_name']);
    unset($_SESSION['role']);
    unset($_SESSION['id_number']);
    unset($_SESSION['logged_in']);
    unset($_SESSION['login_time']);
    unset($_SESSION['api_token']);
    unset($_SESSION['api_token_expiry']);
}
?>