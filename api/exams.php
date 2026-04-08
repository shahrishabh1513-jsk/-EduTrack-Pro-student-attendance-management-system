<?php
require_once '../includes/config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, PUT, OPTIONS');
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
    case 'submit_form':
        if (!hasRole('student')) {
            echo json_encode(['success' => false, 'message' => 'Only students can submit exam forms']);
            exit();
        }
        
        $data = json_decode(file_get_contents('php://input'), true);
        
        $student_query = "SELECT id FROM students WHERE user_id = {$_SESSION['user_id']}";
        $student_result = mysqli_query($conn, $student_query);
        $student = mysqli_fetch_assoc($student_result);
        $student_id = $student['id'];
        
        $semester = (int)$data['semester'];
        $academic_year = sanitize($data['academic_year']);
        $total_fee = (float)$data['total_fee'];
        $course_ids = $data['course_ids'];
        
        mysqli_begin_transaction($conn);
        
        try {
            $query = "INSERT INTO exam_forms (student_id, semester, academic_year, total_fee, payment_status, form_status, submitted_at) 
                      VALUES ($student_id, $semester, '$academic_year', $total_fee, 'paid', 'submitted', NOW())";
            mysqli_query($conn, $query);
            $form_id = mysqli_insert_id($conn);
            
            foreach($course_ids as $course_id) {
                $subject_query = "INSERT INTO exam_form_subjects (exam_form_id, course_id, fee) 
                                  VALUES ($form_id, $course_id, 500)";
                mysqli_query($conn, $subject_query);
            }
            
            mysqli_commit($conn);
            echo json_encode(['success' => true, 'message' => 'Exam form submitted successfully', 'form_id' => $form_id]);
        } catch(Exception $e) {
            mysqli_rollback($conn);
            echo json_encode(['success' => false, 'message' => 'Error submitting exam form']);
        }
        break;
        
    case 'get_my_forms':
        if (!hasRole('student')) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit();
        }
        
        $student_query = "SELECT id FROM students WHERE user_id = {$_SESSION['user_id']}";
        $student_result = mysqli_query($conn, $student_query);
        $student = mysqli_fetch_assoc($student_result);
        $student_id = $student['id'];
        
        $query = "SELECT ef.*, u.full_name as verified_by_name
                  FROM exam_forms ef
                  LEFT JOIN faculty f ON ef.verified_by = f.id
                  LEFT JOIN users u ON f.user_id = u.id
                  WHERE ef.student_id = $student_id
                  ORDER BY ef.submitted_at DESC";
        
        $result = mysqli_query($conn, $query);
        $forms = [];
        while($row = mysqli_fetch_assoc($result)) {
            $forms[] = $row;
        }
        
        echo json_encode(['success' => true, 'data' => $forms]);
        break;
        
    case 'get_pending_forms':
        if (!hasRole('faculty') && !hasRole('admin')) {
            echo json_encode(['success' => false, 'message' => 'Permission denied']);
            exit();
        }
        
        $query = "SELECT ef.*, s.roll_number, u.full_name as student_name, u.email
                  FROM exam_forms ef
                  JOIN students s ON ef.student_id = s.id
                  JOIN users u ON s.user_id = u.id
                  WHERE ef.form_status = 'submitted'
                  ORDER BY ef.submitted_at ASC";
        
        $result = mysqli_query($conn, $query);
        $forms = [];
        while($row = mysqli_fetch_assoc($result)) {
            // Get subjects for each form
            $subjects_query = "SELECT c.course_code, c.course_name, efs.fee 
                               FROM exam_form_subjects efs 
                               JOIN courses c ON efs.course_id = c.id 
                               WHERE efs.exam_form_id = {$row['id']}";
            $subjects_result = mysqli_query($conn, $subjects_query);
            $subjects = [];
            while($subject = mysqli_fetch_assoc($subjects_result)) {
                $subjects[] = $subject;
            }
            $row['subjects'] = $subjects;
            $forms[] = $row;
        }
        
        echo json_encode(['success' => true, 'data' => $forms]);
        break;
        
    case 'verify_form':
        if (!hasRole('faculty') && !hasRole('admin')) {
            echo json_encode(['success' => false, 'message' => 'Permission denied']);
            exit();
        }
        
        $data = json_decode(file_get_contents('php://input'), true);
        $form_id = (int)$data['form_id'];
        $status = sanitize($data['status']);
        
        $faculty_query = "SELECT id FROM faculty WHERE user_id = {$_SESSION['user_id']}";
        $faculty_result = mysqli_query($conn, $faculty_query);
        $faculty = mysqli_fetch_assoc($faculty_result);
        $faculty_id = $faculty['id'] ?? null;
        
        $query = "UPDATE exam_forms 
                  SET form_status = '$status', verified_by = " . ($faculty_id ?: 'NULL') . ", verification_date = NOW() 
                  WHERE id = $form_id";
        
        if(mysqli_query($conn, $query)) {
            echo json_encode(['success' => true, 'message' => 'Exam form ' . $status . ' successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error updating exam form']);
        }
        break;
        
    case 'get_schedule':
        $semester = isset($_GET['semester']) ? (int)$_GET['semester'] : 0;
        
        $where = "";
        if ($semester > 0) {
            $where = "WHERE semester = $semester";
        }
        
        $query = "SELECT * FROM exam_schedule $where ORDER BY exam_date ASC";
        $result = mysqli_query($conn, $query);
        $schedule = [];
        while($row = mysqli_fetch_assoc($result)) {
            $schedule[] = $row;
        }
        
        echo json_encode(['success' => true, 'data' => $schedule]);
        break;
        
    case 'add_schedule':
        if (!hasRole('admin')) {
            echo json_encode(['success' => false, 'message' => 'Permission denied']);
            exit();
        }
        
        $data = json_decode(file_get_contents('php://input'), true);
        
        $exam_name = sanitize($data['exam_name']);
        $exam_type = sanitize($data['exam_type']);
        $course_id = (int)$data['course_id'];
        $semester = (int)$data['semester'];
        $exam_date = sanitize($data['exam_date']);
        $start_time = sanitize($data['start_time']);
        $end_time = sanitize($data['end_time']);
        $venue = sanitize($data['venue']);
        $total_marks = (int)$data['total_marks'];
        $instructions = sanitize($data['instructions']);
        
        $query = "INSERT INTO exam_schedule (exam_name, exam_type, course_id, semester, exam_date, start_time, end_time, venue, total_marks, instructions) 
                  VALUES ('$exam_name', '$exam_type', $course_id, $semester, '$exam_date', '$start_time', '$end_time', '$venue', $total_marks, '$instructions')";
        
        if(mysqli_query($conn, $query)) {
            echo json_encode(['success' => true, 'message' => 'Exam schedule added successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error adding exam schedule']);
        }
        break;
        
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
}
?>