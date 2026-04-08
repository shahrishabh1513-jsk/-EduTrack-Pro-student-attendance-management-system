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
    case 'mark':
        if (!hasRole('faculty') && !hasRole('admin')) {
            echo json_encode(['success' => false, 'message' => 'Only faculty can mark attendance']);
            exit();
        }
        
        $data = json_decode(file_get_contents('php://input'), true);
        $course_id = (int)$data['course_id'];
        $date = sanitize($data['date']);
        $attendances = $data['attendances'];
        
        // Get faculty id
        $faculty_query = "SELECT id FROM faculty WHERE user_id = {$_SESSION['user_id']}";
        $faculty_result = mysqli_query($conn, $faculty_query);
        $faculty = mysqli_fetch_assoc($faculty_result);
        $faculty_id = $faculty['id'];
        
        mysqli_begin_transaction($conn);
        
        try {
            foreach($attendances as $att) {
                $student_id = (int)$att['student_id'];
                $status = sanitize($att['status']);
                $remarks = sanitize($att['remarks'] ?? '');
                
                $check_query = "SELECT id FROM attendance WHERE student_id = $student_id AND course_id = $course_id AND date = '$date'";
                $check_result = mysqli_query($conn, $check_query);
                
                if(mysqli_num_rows($check_result) > 0) {
                    $update_query = "UPDATE attendance SET status = '$status', marked_by = $faculty_id, remarks = '$remarks' WHERE student_id = $student_id AND course_id = $course_id AND date = '$date'";
                    mysqli_query($conn, $update_query);
                } else {
                    $insert_query = "INSERT INTO attendance (student_id, course_id, date, status, marked_by, remarks) VALUES ($student_id, $course_id, '$date', '$status', $faculty_id, '$remarks')";
                    mysqli_query($conn, $insert_query);
                }
            }
            
            mysqli_commit($conn);
            echo json_encode(['success' => true, 'message' => 'Attendance marked successfully']);
        } catch(Exception $e) {
            mysqli_rollback($conn);
            echo json_encode(['success' => false, 'message' => 'Error marking attendance']);
        }
        break;
        
    case 'get_student':
        $student_id = (int)$_GET['student_id'];
        $course_id = isset($_GET['course_id']) ? (int)$_GET['course_id'] : 0;
        
        $query = "SELECT a.*, c.course_name, c.course_code, u.full_name as marked_by_name
                  FROM attendance a 
                  JOIN courses c ON a.course_id = c.id 
                  LEFT JOIN faculty f ON a.marked_by = f.id
                  LEFT JOIN users u ON f.user_id = u.id
                  WHERE a.student_id = $student_id";
        
        if($course_id > 0) {
            $query .= " AND a.course_id = $course_id";
        }
        
        $query .= " ORDER BY a.date DESC LIMIT 100";
        
        $result = mysqli_query($conn, $query);
        $attendance = [];
        while($row = mysqli_fetch_assoc($result)) {
            $attendance[] = $row;
        }
        
        echo json_encode(['success' => true, 'data' => $attendance]);
        break;
        
    case 'get_course':
        $course_id = (int)$_GET['course_id'];
        $date = isset($_GET['date']) ? sanitize($_GET['date']) : date('Y-m-d');
        
        $query = "SELECT a.*, s.roll_number, u.full_name as student_name
                  FROM attendance a
                  JOIN students s ON a.student_id = s.id
                  JOIN users u ON s.user_id = u.id
                  WHERE a.course_id = $course_id AND a.date = '$date'";
        
        $result = mysqli_query($conn, $query);
        $attendance = [];
        while($row = mysqli_fetch_assoc($result)) {
            $attendance[] = $row;
        }
        
        echo json_encode(['success' => true, 'data' => $attendance]);
        break;
        
    case 'get_statistics':
        $student_id = (int)$_GET['student_id'];
        
        $query = "SELECT c.id, c.course_name, c.course_code,
                  COUNT(CASE WHEN a.status = 'present' THEN 1 END) as present,
                  COUNT(CASE WHEN a.status = 'absent' THEN 1 END) as absent,
                  COUNT(CASE WHEN a.status = 'late' THEN 1 END) as late,
                  COUNT(*) as total
                  FROM attendance a
                  JOIN courses c ON a.course_id = c.id
                  WHERE a.student_id = $student_id
                  GROUP BY a.course_id";
        
        $result = mysqli_query($conn, $query);
        $stats = [];
        while($row = mysqli_fetch_assoc($result)) {
            $stats[] = [
                'course_id' => $row['id'],
                'course_name' => $row['course_name'],
                'present' => $row['present'],
                'absent' => $row['absent'],
                'late' => $row['late'],
                'total' => $row['total'],
                'percentage' => $row['total'] > 0 ? round(($row['present'] / $row['total']) * 100) : 0
            ];
        }
        
        echo json_encode(['success' => true, 'data' => $stats]);
        break;
        
    case 'get_overview':
        if (!hasRole('admin')) {
            echo json_encode(['success' => false, 'message' => 'Permission denied']);
            exit();
        }
        
        $dept = isset($_GET['department']) ? sanitize($_GET['department']) : '';
        
        $query = "SELECT d.name as department, 
                  COUNT(DISTINCT s.id) as total_students,
                  SUM(CASE WHEN a.status = 'present' THEN 1 ELSE 0 END) as total_present,
                  COUNT(a.id) as total_records
                  FROM departments d
                  LEFT JOIN students s ON s.course = d.name
                  LEFT JOIN attendance a ON a.student_id = s.id
                  WHERE a.date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
                  GROUP BY d.name";
        
        $result = mysqli_query($conn, $query);
        $overview = [];
        while($row = mysqli_fetch_assoc($result)) {
            $row['percentage'] = $row['total_records'] > 0 ? round(($row['total_present'] / $row['total_records']) * 100) : 0;
            $overview[] = $row;
        }
        
        echo json_encode(['success' => true, 'data' => $overview]);
        break;
        
    case 'get_students_by_course':
        $course_id = (int)$_GET['course_id'];
        
        $query = "SELECT s.id, s.roll_number, u.full_name 
                  FROM students s 
                  JOIN users u ON s.user_id = u.id 
                  JOIN student_courses sc ON s.id = sc.student_id 
                  WHERE sc.course_id = $course_id AND sc.status = 'enrolled'
                  ORDER BY s.roll_number";
        
        $result = mysqli_query($conn, $query);
        $students = [];
        while($row = mysqli_fetch_assoc($result)) {
            $students[] = $row;
        }
        
        echo json_encode(['success' => true, 'data' => $students]);
        break;
        
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
}
?>