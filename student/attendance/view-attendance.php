<?php
// student/attendance/view-attendance.php
require_once '../../includes/config.php';
redirectIfNotStudent();

$user_id = $_SESSION['user_id'];

// Get student ID
$student_query = "SELECT id FROM students WHERE user_id = :user_id";
$stmt = $db->prepare($student_query);
$stmt->bindParam(':user_id', $user_id);
$stmt->execute();
$student = $stmt->fetch(PDO::FETCH_ASSOC);
$student_id = $student['id'];

// Get filter parameters
$month = isset($_GET['month']) ? $_GET['month'] : date('m');
$year = isset($_GET['year']) ? $_GET['year'] : date('Y');
$subject_id = isset($_GET['subject_id']) ? $_GET['subject_id'] : '';

// Get subjects
$subjects_query = "SELECT s.*, sub.subject_name 
                   FROM subjects sub
                   JOIN students s ON s.course_id = sub.course_id
                   WHERE s.id = :student_id AND sub.semester = (SELECT semester FROM students WHERE id = :student_id)";
$subj_stmt = $db->prepare($subjects_query);
$subj_stmt->bindParam(':student_id', $student_id);
$subj_stmt->execute();
$subjects = $subj_stmt->fetchAll(PDO::FETCH_ASSOC);

// Build attendance query
$attendance_query = "SELECT a.*, sub.subject_name, a.date, a.status 
                     FROM attendance a
                     JOIN subjects sub ON a.subject_id = sub.id
                     WHERE a.student_id = :student_id 
                     AND MONTH(a.date) = :month 
                     AND YEAR(a.date) = :year";
if ($subject_id) {
    $attendance_query .= " AND a.subject_id = :subject_id";
}
$attendance_query .= " ORDER BY a.date DESC";

$att_stmt = $db->prepare($attendance_query);
$att_stmt->bindParam(':student_id', $student_id);
$att_stmt->bindParam(':month', $month);
$att_stmt->bindParam(':year', $year);
if ($subject_id) {
    $att_stmt->bindParam(':subject_id', $subject_id);
}
$att_stmt->execute();
$attendance_records = $att_stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate statistics
$total_days = count($attendance_records);
$present_days = 0;
$absent_days = 0;
$late_days = 0;
foreach ($attendance_records as $record) {
    if ($record['status'] == 'present') $present_days++;
    elseif ($record['status'] == 'absent') $absent_days++;
    elseif ($record['status'] == 'late') $late_days++;
}
$attendance_percentage = $total_days > 0 ? round(($present_days / $total_days) * 100, 2) : 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Attendance - EduTrack Pro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .stats-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            text-align: center;
            padding: 20px;
            margin-bottom: 20px;
        }
        .stats-card i {
            font-size: 2rem;
            margin-bottom: 10px;
        }
        .present-card { background: linear-gradient(135deg, #28a745, #20c997); color: white; }
        .absent-card { background: linear-gradient(135deg, #dc3545, #fd7e14); color: white; }
        .percentage-card { background: linear-gradient(135deg, #667eea, #764ba2); color: white; }
        .status-present { color: #28a745; font-weight: bold; }
        .status-absent { color: #dc3545; font-weight: bold; }
        .status-late { color: #ffc107; font-weight: bold; }
    </style>
</head>
<body>
    <?php include '../includes/student-nav.php'; ?>
    
    <div class="container-fluid">
        <div class="row">
            <?php include '../includes/student-sidebar.php'; ?>
            
            <main class="col-md-10 ms-sm-auto px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">My Attendance</h1>
                </div>
                
                <!-- Statistics Cards -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="stats-card present-card">
                            <i class="fas fa-check-circle"></i>
                            <h3><?php echo $present_days; ?></h3>
                            <p>Present Days</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="stats-card absent-card">
                            <i class="fas fa-times-circle"></i>
                            <h3><?php echo $absent_days + $late_days; ?></h3>
                            <p>Absent/Late Days</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="stats-card percentage-card">
                            <i class="fas fa-chart-line"></i>
                            <h3><?php echo $attendance_percentage; ?>%</h3>
                            <p>Overall Attendance</p>
                        </div>
                    </div>
                </div>
                
                <!-- Filter Form -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5><i class="fas fa-filter"></i> Filter Attendance</h5>
                    </div>
                    <div class="card-body">
                        <form method="GET" action="" class="row">
                            <div class="col-md-4">
                                <label class="form-label">Subject</label>
                                <select name="subject_id" class="form-select">
                                    <option value="">All Subjects</option>
                                    <?php foreach($subjects as $subject): ?>
                                    <option value="<?php echo $subject['subject_id']; ?>" <?php echo $subject_id == $subject['subject_id'] ? 'selected' : ''; ?>>
                                        <?php echo $subject['subject_name']; ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Month</label>
                                <select name="month" class="form-select">
                                    <?php for($m = 1; $m <= 12; $m++): ?>
                                    <option value="<?php echo $m; ?>" <?php echo $month == $m ? 'selected' : ''; ?>>
                                        <?php echo date('F', mktime(0,0,0,$m,1)); ?>
                                    </option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Year</label>
                                <select name="year" class="form-select">
                                    <?php for($y = date('Y')-2; $y <= date('Y')+1; $y++): ?>
                                    <option value="<?php echo $y; ?>" <?php echo $year == $y ? 'selected' : ''; ?>>
                                        <?php echo $y; ?>
                                    </option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary w-100">Apply Filter</button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Attendance Table -->
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-calendar-alt"></i> Attendance Records</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Date</th>
                                        <th>Subject</th>
                                        <th>Status</th>
                                        <th>Remarks</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if(count($attendance_records) > 0): ?>
                                        <?php foreach($attendance_records as $record): ?>
                                        <tr>
                                            <td><?php echo date('d M Y', strtotime($record['date'])); ?></td>
                                            <td><?php echo $record['subject_name']; ?></td>
                                            <td>
                                                <span class="status-<?php echo $record['status']; ?>">
                                                    <i class="fas fa-<?php echo $record['status'] == 'present' ? 'check-circle' : ($record['status'] == 'absent' ? 'times-circle' : 'clock'); ?>"></i>
                                                    <?php echo ucfirst($record['status']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo $record['remarks'] ?? '-'; ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="4" class="text-center">No attendance records found</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
                <!-- Attendance Chart -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h5><i class="fas fa-chart-pie"></i> Attendance Chart</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="attendanceChart" height="100"></canvas>
                    </div>
                </div>
            </main>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const ctx = document.getElementById('attendanceChart').getContext('2d');
        const attendanceChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Present', 'Absent', 'Late'],
                datasets: [{
                    data: [<?php echo $present_days; ?>, <?php echo $absent_days; ?>, <?php echo $late_days; ?>],
                    backgroundColor: ['#28a745', '#dc3545', '#ffc107'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    </script>
</body>
</html>