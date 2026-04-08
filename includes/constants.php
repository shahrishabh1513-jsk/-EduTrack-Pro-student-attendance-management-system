<?php
/**
 * EduTrack Pro - Constants File
 * Application-wide constants
 */

// User roles
define('ROLE_ADMIN', 'admin');
define('ROLE_FACULTY', 'faculty');
define('ROLE_STUDENT', 'student');

// User status
define('STATUS_ACTIVE', 'active');
define('STATUS_INACTIVE', 'inactive');
define('STATUS_SUSPENDED', 'suspended');
define('STATUS_BANNED', 'banned');

// Attendance status
define('ATTENDANCE_PRESENT', 'present');
define('ATTENDANCE_ABSENT', 'absent');
define('ATTENDANCE_LATE', 'late');

// Leave types
define('LEAVE_MEDICAL', 'medical');
define('LEAVE_EMERGENCY', 'emergency');
define('LEAVE_PERSONAL', 'personal');
define('LEAVE_VACATION', 'vacation');

// Leave status
define('LEAVE_PENDING', 'pending');
define('LEAVE_APPROVED', 'approved');
define('LEAVE_REJECTED', 'rejected');

// Grievance categories
define('GRIEVANCE_ACADEMIC', 'academic');
define('GRIEVANCE_ADMINISTRATIVE', 'administrative');
define('GRIEVANCE_TECHNICAL', 'technical');
define('GRIEVANCE_OTHER', 'other');

// Grievance status
define('GRIEVANCE_PENDING', 'pending');
define('GRIEVANCE_IN_PROGRESS', 'in_progress');
define('GRIEVANCE_RESOLVED', 'resolved');
define('GRIEVANCE_REJECTED', 'rejected');

// Notice categories
define('NOTICE_IMPORTANT', 'important');
define('NOTICE_EXAM', 'exam');
define('NOTICE_EVENT', 'event');
define('NOTICE_HOLIDAY', 'holiday');
define('NOTICE_GENERAL', 'general');

// Notice targets
define('TARGET_ALL', 'all');
define('TARGET_STUDENT', 'student');
define('TARGET_FACULTY', 'faculty');
define('TARGET_ADMIN', 'admin');

// Exam form status
define('EXAM_FORM_DRAFT', 'draft');
define('EXAM_FORM_SUBMITTED', 'submitted');
define('EXAM_FORM_VERIFIED', 'verified');
define('EXAM_FORM_REJECTED', 'rejected');

// Payment status
define('PAYMENT_PENDING', 'pending');
define('PAYMENT_PAID', 'paid');
define('PAYMENT_VERIFIED', 'verified');
define('PAYMENT_FAILED', 'failed');

// Feedback categories
define('FEEDBACK_FACULTY', 'faculty');
define('FEEDBACK_COURSE', 'course');
define('FEEDBACK_GENERAL', 'general');

// Grade points mapping
$GRADE_POINTS = [
    'A+' => 10,
    'A' => 9,
    'B+' => 8,
    'B' => 7,
    'C+' => 6,
    'C' => 5,
    'D' => 4,
    'F' => 0
];

// Grade descriptions
$GRADE_DESCRIPTIONS = [
    'A+' => 'Outstanding',
    'A' => 'Excellent',
    'B+' => 'Very Good',
    'B' => 'Good',
    'C+' => 'Satisfactory',
    'C' => 'Average',
    'D' => 'Pass',
    'F' => 'Fail'
];

// Gender options
$GENDER_OPTIONS = ['Male', 'Female', 'Other'];

// Blood groups
$BLOOD_GROUPS = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];

// Departments
$DEPARTMENTS = [
    'Computer Science',
    'Information Technology',
    'Electronics',
    'Mechanical',
    'Civil',
    'Electrical'
];

// Courses
$COURSES = [
    'BSC IT' => 'Bachelor of Science in Information Technology',
    'BCA' => 'Bachelor of Computer Applications',
    'MSC IT' => 'Master of Science in Information Technology',
    'MCA' => 'Master of Computer Applications'
];

// Semesters
$SEMESTERS = [1, 2, 3, 4, 5, 6];

// Academic years
$ACADEMIC_YEARS = [
    '2020-2021',
    '2021-2022',
    '2022-2023',
    '2023-2024',
    '2024-2025',
    '2025-2026'
];

// File upload types
$UPLOAD_TYPES = [
    'profile_picture' => [
        'path' => PROFILE_PICS_PATH,
        'max_size' => 2097152, // 2MB
        'allowed' => ['jpg', 'jpeg', 'png', 'gif']
    ],
    'document' => [
        'path' => DOCUMENTS_PATH,
        'max_size' => 5242880, // 5MB
        'allowed' => ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png']
    ],
    'assignment' => [
        'path' => ASSIGNMENTS_PATH,
        'max_size' => 10485760, // 10MB
        'allowed' => ['pdf', 'doc', 'docx', 'zip', 'rar']
    ],
    'notice' => [
        'path' => NOTICES_PATH,
        'max_size' => 5242880, // 5MB
        'allowed' => ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png']
    ]
];

// Cache settings
define('CACHE_ENABLED', false);
define('CACHE_PATH', BASE_PATH . 'cache/');
define('CACHE_DURATION', 3600); // 1 hour

// Log levels
define('LOG_INFO', 'info');
define('LOG_WARNING', 'warning');
define('LOG_ERROR', 'error');
define('LOG_SUCCESS', 'success');

// API response codes
define('API_SUCCESS', 200);
define('API_CREATED', 201);
define('API_BAD_REQUEST', 400);
define('API_UNAUTHORIZED', 401);
define('API_FORBIDDEN', 403);
define('API_NOT_FOUND', 404);
define('API_VALIDATION_ERROR', 422);
define('API_SERVER_ERROR', 500);
?>