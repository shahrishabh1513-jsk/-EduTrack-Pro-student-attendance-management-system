<?php
// student/dashboard.php
require_once '../includes/config.php';
redirectIfNotStudent();

$user_id = $_SESSION['user_id'];

// Get student details
$query = "SELECT s.*, c.course_name, d.dept_name 
          FROM students s 
          LEFT JOIN courses c ON s.course_id = c.id 
          LEFT JOIN departments d ON c.department_id = d.id 
          WHERE s.user_id = :user_id";
$stmt = $db->prepare($query);
$stmt->bindParam(':user_id', $user_id);
$stmt->execute();
$student = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$student) {
    die("Student record not found");
}

$student_id = $student['id'];

// Get attendance summary
$attendance_query = "SELECT 
                        COUNT(CASE WHEN status = 'present' THEN 1 END) as present,
                        COUNT(CASE WHEN status = 'absent' THEN 1 END) as absent,
                        COUNT(CASE WHEN status = 'late' THEN 1 END) as late,
                        COUNT(*) as total
                     FROM attendance 
                     WHERE student_id = :student_id";
$att_stmt = $db->prepare($attendance_query);
$att_stmt->bindParam(':student_id', $student_id);
$att_stmt->execute();
$attendance = $att_stmt->fetch(PDO::FETCH_ASSOC);

$attendance_percentage = $attendance['total'] > 0 ? round(($attendance['present'] / $attendance['total']) * 100, 2) : 0;

// Get recent notices
$notice_query = "SELECT * FROM notices 
                 WHERE is_active = 1 
                 AND (expiry_date IS NULL OR expiry_date >= CURDATE())
                 ORDER BY created_at DESC LIMIT 5";
$notices = $db->query($notice_query)->fetchAll(PDO::FETCH_ASSOC);

// Get upcoming exams
$exam_query = "SELECT es.*, s.subject_name 
               FROM exam_schedule es
               JOIN subjects s ON es.subject_id = s.id
               WHERE es.course_id = :course_id 
               AND es.semester = :semester
               AND es.exam_date >= CURDATE()
               ORDER BY es.exam_date ASC LIMIT 5";
$exam_stmt = $db->prepare($exam_query);
$exam_stmt->bindParam(':course_id', $student['course_id']);
$exam_stmt->bindParam(':semester', $student['semester']);
$exam_stmt->execute();
$exams = $exam_stmt->fetchAll(PDO::FETCH_ASSOC);

// Get subjects for current semester
$subjects_query = "SELECT * FROM subjects 
                   WHERE course_id = :course_id AND semester = :semester";
$subj_stmt = $db->prepare($subjects_query);
$subj_stmt->bindParam(':course_id', $student['course_id']);
$subj_stmt->bindParam(':semester', $student['semester']);
$subj_stmt->execute();
$subjects = $subj_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard - EduTrack Pro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .sidebar .nav-link {
            color: white;
            padding: 12px 20px;
            margin: 5px 0;
            border-radius: 10px;
            transition: all 0.3s;
        }
        .sidebar .nav-link:hover {
            background: rgba(255,255,255,0.2);
            transform: translateX(5px);
        }
        .sidebar .nav-link.active {
            background: rgba(255,255,255,0.3);
        }
        .sidebar .nav-link i {
            margin-right: 10px;
        }
        .stat-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
        .stat-icon {
            font-size: 2.5rem;
            opacity: 0.5;
        }
        .welcome-banner {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            border-radius: 15px;
            margin-bottom: 30px;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-2 d-md-block sidebar p-0">
                <div class="position-sticky">
                    <div class="text-center py-4">
                        <i class="fas fa-graduation-cap" style="font-size: 40px; color: white;"></i>
                        <h5 class="text-white mt-2">EduTrack Pro</h5>
                        <small class="text-white-50">Student Portal</small>
                    </div>
                    <ul class="nav flex-column px-3">
                        <li class="nav-item">
                            <a class="nav-link active" href="dashboard.php">
                                <i class="fas fa-tachometer-alt"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#">
                                <i class="fas fa-calendar-check"></i> Attendance
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#">
                                <i class="fas fa-trophy"></i> Results
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#">
                                <i class="fas fa-clock"></i> Exam Schedule
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#">
                                <i class="fas fa-envelope"></i> Leave Application
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#">
                                <i class="fas fa-file-alt"></i> Exam Form
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#">
                                <i class="fas fa-bell"></i> Notices
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#">
                                <i class="fas fa-folder"></i> Study Materials
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="../logout.php">
                                <i class="fas fa-sign-out-alt"></i> Logout
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Main content -->
            <main class="col-md-10 ms-sm-auto px-md-4">
                <!-- Welcome Banner -->
                <div class="welcome-banner mt-3">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h2>Welcome back, <?php echo $_SESSION['full_name']; ?>!</h2>
                            <p class="mb-0">Student ID: <?php echo $student['student_id']; ?> | Course: <?php echo $student['course_name']; ?> | Semester: <?php echo $student['semester']; ?></p>
                        </div>
                        <div class="col-md-4 text-end">
                            <i class="fas fa-user-graduate" style="font-size: 60px;"></i>
                        </div>
                    </div>
                </div>

                <!-- Stats Cards -->
                <div class="row mb-4">
                    <div class="col-md-3 mb-3">
                        <div class="card stat-card bg-primary text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="card-title">Overall Attendance</h6>
                                        <h2><?php echo $attendance_percentage; ?>%</h2>
                                    </div>
                                    <i class="fas fa-calendar-check stat-icon"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card stat-card bg-success text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="card-title">Current CGPA</h6>
                                        <h2>8.5</h2>
                                    </div>
                                    <i class="fas fa-trophy stat-icon"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card stat-card bg-info text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="card-title">Semester</h6>
                                        <h2><?php echo $student['semester']; ?></h2>
                                    </div>
                                    <i class="fas fa-book stat-icon"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card stat-card bg-warning text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="card-title">Upcoming Exams</h6>
                                        <h2><?php echo count($exams); ?></h2>
                                    </div>
                                    <i class="fas fa-clock stat-icon"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <!-- Attendance Chart -->
                    <div class="col-md-6 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h5>Attendance Overview</h5>
                            </div>
                            <div class="card-body">
                                <canvas id="attendanceChart" height="200"></canvas>
                                <div class="mt-3">
                                    <div class="progress" style="height: 25px;">
                                        <div class="progress-bar bg-success" style="width: <?php echo $attendance_percentage; ?>%">
                                            Present: <?php echo $attendance['present'] ?? 0; ?>
                                        </div>
                                        <div class="progress-bar bg-danger" style="width: <?php echo 100 - $attendance_percentage; ?>%">
                                            Absent: <?php echo $attendance['absent'] ?? 0; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Subject-wise Attendance -->
                    <div class="col-md-6 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h5>Subject-wise Attendance</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Subject</th>
                                                <th>Attendance</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach($subjects as $subject): 
                                                $subject_attendance = getAttendancePercentage($student_id, $subject['id']);
                                            ?>
                                            <tr>
                                                <td><?php echo $subject['subject_name']; ?></td>
                                                <td><?php echo $subject_attendance; ?>%</td>
                                                <td>
                                                    <?php if($subject_attendance >= 75): ?>
                                                        <span class="badge bg-success">Good</span>
                                                    <?php elseif($subject_attendance >= 60): ?>
                                                        <span class="badge bg-warning">Average</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-danger">Low</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <!-- Recent Notices -->
                    <div class="col-md-6 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h5><i class="fas fa-bell"></i> Recent Notices</h5>
                            </div>
                            <div class="card-body">
                                <div class="list-group">
                                    <?php foreach($notices as $notice): ?>
                                    <div class="list-group-item list-group-item-action">
                                        <div class="d-flex w-100 justify-content-between">
                                            <h6 class="mb-1"><?php echo $notice['title']; ?></h6>
                                            <small><?php echo date('d M Y', strtotime($notice['created_at'])); ?></small>
                                        </div>
                                        <p class="mb-1"><?php echo substr($notice['content'], 0, 100); ?>...</p>
                                        <small class="text-muted">
                                            <i class="fas fa-tag"></i> <?php echo ucfirst($notice['notice_type']); ?>
                                        </small>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Upcoming Exams -->
                    <div class="col-md-6 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h5><i class="fas fa-calendar-alt"></i> Upcoming Examinations</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Subject</th>
                                                <th>Date</th>
                                                <th>Time</th>
                                                <th>Venue</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach($exams as $exam): ?>
                                            <tr>
                                                <td><?php echo $exam['subject_name']; ?></td>
                                                <td><?php echo date('d M Y', strtotime($exam['exam_date'])); ?></td>
                                                <td><?php echo date('h:i A', strtotime($exam['start_time'])); ?></td>
                                                <td><?php echo $exam['venue']; ?></td>
                                            </tr>
                                            <?php endforeach; ?>
                                            <?php if(count($exams) == 0): ?>
                                            <tr>
                                                <td colspan="4" class="text-center">No upcoming exams</td>
                                            </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Attendance Chart
        const ctx = document.getElementById('attendanceChart').getContext('2d');
        const attendanceChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Present', 'Absent', 'Late'],
                datasets: [{
                    data: [<?php echo $attendance['present'] ?? 0; ?>, <?php echo $attendance['absent'] ?? 0; ?>, <?php echo $attendance['late'] ?? 0; ?>],
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