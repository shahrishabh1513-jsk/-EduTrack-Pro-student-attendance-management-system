<?php
/**
 * EduTrack Pro - Validation Functions
 * Input validation and sanitization
 */

/**
 * Validate required fields
 */
function validateRequired($data, $fields) {
    $errors = [];
    
    foreach ($fields as $field) {
        if (empty($data[$field])) {
            $errors[] = ucfirst(str_replace('_', ' ', $field)) . ' is required';
        }
    }
    
    return $errors;
}

/**
 * Validate email format
 */
function validateEmailFormat($email) {
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return 'Invalid email format';
    }
    return null;
}

/**
 * Validate phone number (Indian)
 */
function validatePhoneNumber($phone) {
    if (!preg_match('/^[6-9]\d{9}$/', $phone)) {
        return 'Invalid phone number. Must be 10 digits starting with 6,7,8,9';
    }
    return null;
}

/**
 * Validate roll number format
 */
function validateRollNumber($roll) {
    if (!preg_match('/^[A-Z0-9]{4,10}$/i', $roll)) {
        return 'Invalid roll number format';
    }
    return null;
}

/**
 * Validate employee ID format
 */
function validateEmployeeId($emp_id) {
    if (!preg_match('/^[A-Z0-9]{3,10}$/i', $emp_id)) {
        return 'Invalid employee ID format';
    }
    return null;
}

/**
 * Validate password strength
 */
function validatePassword($password) {
    if (strlen($password) < 6) {
        return 'Password must be at least 6 characters long';
    }
    
    if (!preg_match('/[A-Z]/', $password)) {
        return 'Password must contain at least one uppercase letter';
    }
    
    if (!preg_match('/[a-z]/', $password)) {
        return 'Password must contain at least one lowercase letter';
    }
    
    if (!preg_match('/[0-9]/', $password)) {
        return 'Password must contain at least one number';
    }
    
    return null;
}

/**
 * Validate date format
 */
function validateDate($date, $format = 'Y-m-d') {
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) === $date;
}

/**
 * Validate date range (from <= to)
 */
function validateDateRange($from_date, $to_date) {
    if (strtotime($from_date) > strtotime($to_date)) {
        return 'From date must be less than or equal to To date';
    }
    return null;
}

/**
 * Validate age (minimum age)
 */
function validateAge($dob, $min_age = 16) {
    $age = calculateAge($dob);
    if ($age < $min_age) {
        return "Age must be at least $min_age years";
    }
    return null;
}

/**
 * Validate numeric range
 */
function validateNumericRange($value, $min, $max, $field_name = 'Value') {
    if (!is_numeric($value)) {
        return "$field_name must be a number";
    }
    
    if ($value < $min) {
        return "$field_name must be at least $min";
    }
    
    if ($value > $max) {
        return "$field_name must not exceed $max";
    }
    
    return null;
}

/**
 * Validate file upload
 */
function validateFileUpload($file, $allowed_types = null, $max_size = null) {
    $errors = [];
    
    if (!isset($file) || $file['error'] === UPLOAD_ERR_NO_FILE) {
        $errors[] = 'No file uploaded';
        return $errors;
    }
    
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errors[] = 'File upload error';
        return $errors;
    }
    
    if ($max_size && $file['size'] > $max_size) {
        $errors[] = 'File too large. Max size: ' . ($max_size / 1024 / 1024) . 'MB';
    }
    
    if ($allowed_types) {
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, $allowed_types)) {
            $errors[] = 'Invalid file type. Allowed: ' . implode(', ', $allowed_types);
        }
    }
    
    return $errors;
}

/**
 * Validate student registration data
 */
function validateStudentRegistration($data) {
    $errors = [];
    
    // Required fields
    $required = ['username', 'email', 'password', 'full_name', 'phone', 'roll_number', 'course', 'semester'];
    $errors = array_merge($errors, validateRequired($data, $required));
    
    // Email validation
    if (empty($errors) && !empty($data['email'])) {
        $email_error = validateEmailFormat($data['email']);
        if ($email_error) $errors[] = $email_error;
    }
    
    // Phone validation
    if (empty($errors) && !empty($data['phone'])) {
        $phone_error = validatePhoneNumber($data['phone']);
        if ($phone_error) $errors[] = $phone_error;
    }
    
    // Password validation
    if (empty($errors) && !empty($data['password'])) {
        $pass_error = validatePassword($data['password']);
        if ($pass_error) $errors[] = $pass_error;
    }
    
    // Roll number validation
    if (empty($errors) && !empty($data['roll_number'])) {
        $roll_error = validateRollNumber($data['roll_number']);
        if ($roll_error) $errors[] = $roll_error;
    }
    
    // Semester validation
    if (empty($errors) && isset($data['semester'])) {
        $sem_error = validateNumericRange($data['semester'], 1, 8, 'Semester');
        if ($sem_error) $errors[] = $sem_error;
    }
    
    // CGPA validation (if provided)
    if (empty($errors) && !empty($data['cgpa'])) {
        $cgpa_error = validateNumericRange($data['cgpa'], 0, 10, 'CGPA');
        if ($cgpa_error) $errors[] = $cgpa_error;
    }
    
    return $errors;
}

/**
 * Validate faculty registration data
 */
function validateFacultyRegistration($data) {
    $errors = [];
    
    // Required fields
    $required = ['username', 'email', 'password', 'full_name', 'phone', 'employee_id', 'department', 'designation'];
    $errors = array_merge($errors, validateRequired($data, $required));
    
    // Email validation
    if (empty($errors) && !empty($data['email'])) {
        $email_error = validateEmailFormat($data['email']);
        if ($email_error) $errors[] = $email_error;
    }
    
    // Phone validation
    if (empty($errors) && !empty($data['phone'])) {
        $phone_error = validatePhoneNumber($data['phone']);
        if ($phone_error) $errors[] = $phone_error;
    }
    
    // Password validation
    if (empty($errors) && !empty($data['password'])) {
        $pass_error = validatePassword($data['password']);
        if ($pass_error) $errors[] = $pass_error;
    }
    
    // Employee ID validation
    if (empty($errors) && !empty($data['employee_id'])) {
        $emp_error = validateEmployeeId($data['employee_id']);
        if ($emp_error) $errors[] = $emp_error;
    }
    
    // Experience validation
    if (empty($errors) && isset($data['experience'])) {
        $exp_error = validateNumericRange($data['experience'], 0, 50, 'Experience');
        if ($exp_error) $errors[] = $exp_error;
    }
    
    return $errors;
}

/**
 * Validate course data
 */
function validateCourse($data) {
    $errors = [];
    
    // Required fields
    $required = ['course_code', 'course_name', 'credits', 'semester'];
    $errors = array_merge($errors, validateRequired($data, $required));
    
    // Credits validation
    if (empty($errors) && isset($data['credits'])) {
        $credits_error = validateNumericRange($data['credits'], 1, 6, 'Credits');
        if ($credits_error) $errors[] = $credits_error;
    }
    
    // Semester validation
    if (empty($errors) && isset($data['semester'])) {
        $sem_error = validateNumericRange($data['semester'], 1, 8, 'Semester');
        if ($sem_error) $errors[] = $sem_error;
    }
    
    return $errors;
}

/**
 * Validate leave application
 */
function validateLeaveApplication($data) {
    $errors = [];
    
    // Required fields
    $required = ['from_date', 'to_date', 'reason', 'leave_type'];
    $errors = array_merge($errors, validateRequired($data, $required));
    
    // Date validation
    if (empty($errors)) {
        if (!validateDate($data['from_date'])) {
            $errors[] = 'Invalid from date';
        }
        if (!validateDate($data['to_date'])) {
            $errors[] = 'Invalid to date';
        }
    }
    
    // Date range validation
    if (empty($errors)) {
        $range_error = validateDateRange($data['from_date'], $data['to_date']);
        if ($range_error) $errors[] = $range_error;
    }
    
    // Leave type validation
    $valid_types = ['medical', 'emergency', 'personal', 'vacation'];
    if (empty($errors) && !in_array($data['leave_type'], $valid_types)) {
        $errors[] = 'Invalid leave type';
    }
    
    return $errors;
}

/**
 * Validate grievance submission
 */
function validateGrievance($data) {
    $errors = [];
    
    // Required fields
    $required = ['title', 'description', 'category'];
    $errors = array_merge($errors, validateRequired($data, $required));
    
    // Title length
    if (empty($errors) && strlen($data['title']) < 5) {
        $errors[] = 'Title must be at least 5 characters long';
    }
    
    if (empty($errors) && strlen($data['title']) > 200) {
        $errors[] = 'Title cannot exceed 200 characters';
    }
    
    // Description length
    if (empty($errors) && strlen($data['description']) < 10) {
        $errors[] = 'Description must be at least 10 characters long';
    }
    
    // Category validation
    $valid_categories = ['academic', 'administrative', 'technical', 'other'];
    if (empty($errors) && !in_array($data['category'], $valid_categories)) {
        $errors[] = 'Invalid category';
    }
    
    return $errors;
}

/**
 * Validate feedback submission
 */
function validateFeedback($data) {
    $errors = [];
    
    // Required fields
    $required = ['rating'];
    $errors = array_merge($errors, validateRequired($data, $required));
    
    // Rating validation
    if (empty($errors)) {
        $rating_error = validateNumericRange($data['rating'], 1, 5, 'Rating');
        if ($rating_error) $errors[] = $rating_error;
    }
    
    return $errors;
}

/**
 * Validate exam form submission
 */
function validateExamForm($data) {
    $errors = [];
    
    // Required fields
    $required = ['semester', 'course_ids'];
    $errors = array_merge($errors, validateRequired($data, $required));
    
    // Semester validation
    if (empty($errors)) {
        $sem_error = validateNumericRange($data['semester'], 1, 8, 'Semester');
        if ($sem_error) $errors[] = $sem_error;
    }
    
    // Course IDs validation
    if (empty($errors) && empty($data['course_ids'])) {
        $errors[] = 'At least one subject must be selected';
    }
    
    return $errors;
}

/**
 * Validate marks entry
 */
function validateMarksEntry($data) {
    $errors = [];
    
    if (isset($data['internal_marks'])) {
        $internal_error = validateNumericRange($data['internal_marks'], 0, 30, 'Internal marks');
        if ($internal_error) $errors[] = $internal_error;
    }
    
    if (isset($data['external_marks'])) {
        $external_error = validateNumericRange($data['external_marks'], 0, 70, 'External marks');
        if ($external_error) $errors[] = $external_error;
    }
    
    if (isset($data['practical_marks'])) {
        $practical_error = validateNumericRange($data['practical_marks'], 0, 50, 'Practical marks');
        if ($practical_error) $errors[] = $practical_error;
    }
    
    return $errors;
}

/**
 * Check if email already exists
 */
function emailExists($email, $exclude_id = null) {
    global $conn;
    $email = sanitize($email);
    $query = "SELECT id FROM users WHERE email = '$email'";
    
    if ($exclude_id) {
        $query .= " AND id != $exclude_id";
    }
    
    $result = mysqli_query($conn, $query);
    return mysqli_num_rows($result) > 0;
}

/**
 * Check if username already exists
 */
function usernameExists($username, $exclude_id = null) {
    global $conn;
    $username = sanitize($username);
    $query = "SELECT id FROM users WHERE username = '$username'";
    
    if ($exclude_id) {
        $query .= " AND id != $exclude_id";
    }
    
    $result = mysqli_query($conn, $query);
    return mysqli_num_rows($result) > 0;
}

/**
 * Check if roll number already exists
 */
function rollNumberExists($roll_number, $exclude_id = null) {
    global $conn;
    $roll_number = sanitize($roll_number);
    $query = "SELECT id FROM students WHERE roll_number = '$roll_number'";
    
    if ($exclude_id) {
        $query .= " AND id != $exclude_id";
    }
    
    $result = mysqli_query($conn, $query);
    return mysqli_num_rows($result) > 0;
}

/**
 * Check if employee ID already exists
 */
function employeeIdExists($employee_id, $exclude_id = null) {
    global $conn;
    $employee_id = sanitize($employee_id);
    $query = "SELECT id FROM faculty WHERE employee_id = '$employee_id'";
    
    if ($exclude_id) {
        $query .= " AND id != $exclude_id";
    }
    
    $result = mysqli_query($conn, $query);
    return mysqli_num_rows($result) > 0;
}

/**
 * Check if course code already exists
 */
function courseCodeExists($course_code, $exclude_id = null) {
    global $conn;
    $course_code = sanitize($course_code);
    $query = "SELECT id FROM courses WHERE course_code = '$course_code'";
    
    if ($exclude_id) {
        $query .= " AND id != $exclude_id";
    }
    
    $result = mysqli_query($conn, $query);
    return mysqli_num_rows($result) > 0;
}
?>