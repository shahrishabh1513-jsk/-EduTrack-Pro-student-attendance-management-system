<?php
require_once '../includes/config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

if (!isLoggedIn() || !hasRole('admin')) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$action = $_GET['action'] ?? '';

switch($action) {
    case 'attendance':
        $department = isset($_GET['department']) ? sanitize($_GET['department']) : '';
        $semester = isset($_GET['semester']) ? (int)$_GET['semester'] : 0;
        $from_date = isset($_GET['from_date']) ? sanitize($_GET['from_date']) : date('Y-m-d', strtotime('-30 days'));
        $to_date = isset($_GET['to_date']) ? sanitize($_GET['to_date']) : date('Y-m-d');
        
        $where = ["a.date BETWEEN '$from_date' AND '$to_date'"];
        if ($department) {
            $where[] = "s.course = '$department'";
        }
        if ($semester > 0) {
            $where[] = "s.semester = $semester";
        }
        
        $where_clause = "WHERE " . implode(" AND ", $where);
        
        $query = "SELECT s.roll_number, u.full_name as student_name, s.course, s.semester,
                  COUNT(CASE WHEN a.status = 'present' THEN 1 END) as present,
                  COUNT(CASE WHEN a.status = 'absent' THEN 1 END) as absent,
                  COUNT(CASE WHEN a.status = 'late' THEN 1 END) as late,
                  COUNT(*) as total_days
                  FROM attendance a
                  JOIN students s ON a.student_id = s.id
                  JOIN users u ON s.user_id = u.id
                  $where_clause
                  GROUP BY a.student_id
                  ORDER BY s.roll_number";
        
        $result = mysqli_query($conn, $query);
        $data = [];
        while($row = mysqli_fetch_assoc($result)) {
            $row['percentage'] = $row['total_days'] > 0 ? round(($row['present'] / $row['total_days']) * 100, 2) : 0;
            $data[] = $row;
        }
        
        echo json_encode(['success' => true, 'data' => $data]);
        break;
        
    case 'academic':
        $department = isset($_GET['department']) ? sanitize($_GET['department']) : '';
        $semester = isset($_GET['semester']) ? (int)$_GET['semester'] : 0;
        
        $where = [];
        if ($department) {
            $where[] = "s.course = '$department'";
        }
        if ($semester > 0) {
            $where[] = "s.semester = $semester";
        }
        
        $where_clause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";
        
        $query = "SELECT s.roll_number, u.full_name as student_name, s.course, s.semester, s.cgpa,
                  (SELECT COUNT(*) FROM exam_results er WHERE er.student_id = s.id AND er.total_marks >= 40) as passed_courses,
                  (SELECT COUNT(*) FROM exam_results er WHERE er.student_id = s.id) as total_courses
                  FROM students s
                  JOIN users u ON s.user_id = u.id
                  $where_clause
                  ORDER BY s.cgpa DESC";
        
        $result = mysqli_query($conn, $query);
        $data = [];
        while($row = mysqli_fetch_assoc($result)) {
            $row['pass_percentage'] = $row['total_courses'] > 0 ? round(($row['passed_courses'] / $row['total_courses']) * 100, 2) : 0;
            $data[] = $row;
        }
        
        echo json_encode(['success' => true, 'data' => $data]);
        break;
        
    case 'financial':
        $year = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');
        
        // Fee collection summary
        $fee_query = "SELECT 
                      SUM(total_fee) as total_collected,
                      COUNT(*) as total_forms
                      FROM exam_forms
                      WHERE YEAR(submitted_at) = $year AND payment_status = 'paid'";
        $fee_result = mysqli_query($conn, $fee_query);
        $fee_data = mysqli_fetch_assoc($fee_result);
        
        // Monthly breakdown
        $monthly_query = "SELECT 
                          MONTHNAME(submitted_at) as month,
                          SUM(total_fee) as amount,
                          COUNT(*) as forms
                          FROM exam_forms
                          WHERE YEAR(submitted_at) = $year AND payment_status = 'paid'
                          GROUP BY MONTH(submitted_at)
                          ORDER BY MONTH(submitted_at)";
        $monthly_result = mysqli_query($conn, $monthly_query);
        $monthly = [];
        while($row = mysqli_fetch_assoc($monthly_result)) {
            $monthly[] = $row;
        }
        
        echo json_encode([
            'success' => true,
            'data' => [
                'summary' => $fee_data,
                'monthly' => $monthly
            ]
        ]);
        break;
        
    case 'analytics':
        // Student enrollment trend
        $enrollment_query = "SELECT 
                             YEAR(admission_date) as year,
                             COUNT(*) as count
                             FROM students
                             GROUP BY YEAR(admission_date)
                             ORDER BY year";
        $enrollment_result = mysqli_query($conn, $enrollment_query);
        $enrollment = [];
        while($row = mysqli_fetch_assoc($enrollment_result)) {
            $enrollment[] = $row;
        }
        
        // Department distribution
        $dept_query = "SELECT course as department, COUNT(*) as count FROM students GROUP BY course";
        $dept_result = mysqli_query($conn, $dept_query);
        $departments = [];
        while($row = mysqli_fetch_assoc($dept_result)) {
            $departments[] = $row;
        }
        
        // Performance trend
        $performance_query = "SELECT 
                              semester,
                              AVG(cgpa) as avg_cgpa
                              FROM students
                              GROUP BY semester
                              ORDER BY semester";
        $performance_result = mysqli_query($conn, $performance_query);
        $performance = [];
        while($row = mysqli_fetch_assoc($performance_result)) {
            $performance[] = $row;
        }
        
        echo json_encode([
            'success' => true,
            'data' => [
                'enrollment_trend' => $enrollment,
                'department_distribution' => $departments,
                'performance_trend' => $performance
            ]
        ]);
        break;
        
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
}
?>