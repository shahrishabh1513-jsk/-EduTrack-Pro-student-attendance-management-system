<?php
// faculty/dashboard.php
require_once '../includes/config.php';
redirectIfNotFaculty();

$user_id = $_SESSION['user_id'];

// Get faculty details
$query = "SELECT f.*, d.dept_name 
          FROM faculty f 
          LEFT JOIN departments d ON f.department_id = d.id 
          WHERE f.user_id = :user_id";
$stmt = $db->prepare($query);
$stmt->bindParam(':user_id', $user_id);
$stmt->execute();
$faculty = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$faculty) {
    die("Faculty record not found");
}

$faculty_id = $faculty['id'];

// Get assigned subjects
$subjects_query = "SELECT s.*, c.course_name 
                   FROM subjects s
                   JOIN courses c ON s.course_id = c.id
                   WHERE s.faculty_id = :faculty_id";
$subj_stmt = $db->prepare($subjects_query);
$subj_stmt->bindParam(':faculty_id', $faculty_id);
$subj_stmt->execute();
$subjects = $subj_stmt->fetchAll(PDO::FETCH_ASSOC);

// Get today's classes - FIXED: removed c.semester
$today = strtolower(date('l'));
$classes_query = "SELECT t.*, s.subject_name, c.course_name 
                  FROM timetable t
                  JOIN subjects s ON t.subject_id = s.id
                  JOIN courses c ON t.course_id = c.id
                  WHERE t.faculty_id = :faculty_id 
                  AND t.day_of_week = :day
                  ORDER BY t.start_time";
$class_stmt = $db->prepare($classes_query);
$class_stmt->bindParam(':faculty_id', $faculty_id);
$class_stmt->bindParam(':day', $today);
$class_stmt->execute();
$today_classes = $class_stmt->fetchAll(PDO::FETCH_ASSOC);

// Get total students count
$students_query = "SELECT COUNT(DISTINCT s.id) as total 
                   FROM students s
                   JOIN subjects sub ON sub.course_id = s.course_id
                   WHERE sub.faculty_id = :faculty_id";
$stud_stmt = $db->prepare($students_query);
$stud_stmt->bindParam(':faculty_id', $faculty_id);
$stud_stmt->execute();
$total_students = $stud_stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Get pending leave requests
$leave_query = "SELECT COUNT(*) as total FROM leave_applications WHERE status = 'pending'";
$leave_stmt = $db->prepare($leave_query);
$leave_stmt->execute();
$pending_leaves = $leave_stmt->fetch(PDO::FETCH_ASSOC)['total'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Faculty Dashboard - EduTrack Pro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
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
            cursor: pointer;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
        .welcome-banner {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            color: white;
            padding: 30px;
            border-radius: 15px;
            margin-bottom: 30px;
        }
        .schedule-item {
            border-left: 4px solid #11998e;
            margin-bottom: 15px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 10px;
            transition: all 0.3s;
        }
        .schedule-item:hover {
            transform: translateX(5px);
            background: #e9ecef;
        }
        .today-class {
            background: #d4edda;
            border-left-color: #28a745;
        }
        .btn-quick {
            margin: 5px;
            padding: 10px;
            border-radius: 10px;
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
                        <i class="fas fa-chalkboard-teacher" style="font-size: 40px; color: white;"></i>
                        <h5 class="text-white mt-2">EduTrack Pro</h5>
                        <small class="text-white-50">Faculty Portal</small>
                    </div>
                    <ul class="nav flex-column px-3">
                        <li class="nav-item">
                            <a class="nav-link active" href="dashboard.php">
                                <i class="fas fa-tachometer-alt"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="attendance/mark-attendance.php">
                                <i class="fas fa-user-check"></i> Mark Attendance
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="classes/my-classes.php">
                                <i class="fas fa-chalkboard"></i> My Classes
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="exams/marks-entry.php">
                                <i class="fas fa-edit"></i> Marks Entry
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="requests/leave-requests.php">
                                <i class="fas fa-envelope"></i> Leave Requests
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="resources/upload-material.php">
                                <i class="fas fa-upload"></i> Upload Material
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="resources/notices.php">
                                <i class="fas fa-bell"></i> Notices
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="profile.php">
                                <i class="fas fa-user"></i> My Profile
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
                            <h2>Welcome, <?php echo $_SESSION['full_name']; ?>!</h2>
                            <p class="mb-0">Faculty ID: <?php echo $faculty['faculty_id']; ?> | Department: <?php echo $faculty['dept_name']; ?> | Designation: <?php echo $faculty['designation']; ?></p>
                        </div>
                        <div class="col-md-4 text-end">
                            <i class="fas fa-chalkboard-teacher" style="font-size: 60px;"></i>
                        </div>
                    </div>
                </div>

                <!-- Stats Cards -->
                <div class="row mb-4">
                    <div class="col-md-3 mb-3">
                        <div class="card stat-card bg-primary text-white" onclick="window.location.href='classes/my-classes.php'">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="card-title">Subjects</h6>
                                        <h2><?php echo count($subjects); ?></h2>
                                    </div>
                                    <i class="fas fa-book" style="font-size: 2rem; opacity: 0.5;"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card stat-card bg-success text-white" onclick="window.location.href='attendance/mark-attendance.php'">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="card-title">Total Students</h6>
                                        <h2><?php echo $total_students; ?></h2>
                                    </div>
                                    <i class="fas fa-users" style="font-size: 2rem; opacity: 0.5;"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card stat-card bg-info text-white" onclick="window.location.href='classes/my-classes.php'">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="card-title">Today's Classes</h6>
                                        <h2><?php echo count($today_classes); ?></h2>
                                    </div>
                                    <i class="fas fa-clock" style="font-size: 2rem; opacity: 0.5;"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card stat-card bg-warning text-white" onclick="window.location.href='requests/leave-requests.php'">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="card-title">Pending Leaves</h6>
                                        <h2><?php echo $pending_leaves; ?></h2>
                                    </div>
                                    <i class="fas fa-envelope" style="font-size: 2rem; opacity: 0.5;"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <!-- Today's Schedule -->
                    <div class="col-md-6 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h5><i class="fas fa-calendar-day"></i> Today's Schedule - <?php echo ucfirst($today); ?></h5>
                            </div>
                            <div class="card-body">
                                <?php if(count($today_classes) > 0): ?>
                                    <?php foreach($today_classes as $class): ?>
                                    <div class="schedule-item">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h6 class="mb-1"><?php echo $class['subject_name']; ?></h6>
                                                <small class="text-muted">
                                                    <i class="fas fa-graduation-cap"></i> <?php echo $class['course_name']; ?>
                                                </small>
                                            </div>
                                            <div class="text-end">
                                                <span class="badge bg-primary">
                                                    <?php echo date('h:i A', strtotime($class['start_time'])); ?> - <?php echo date('h:i A', strtotime($class['end_time'])); ?>
                                                </span>
                                                <div><small class="text-muted">Room: <?php echo $class['room_no']; ?></small></div>
                                            </div>
                                        </div>
                                        <div class="mt-2">
                                            <a href="attendance/mark-attendance.php?subject_id=<?php echo $class['subject_id']; ?>" class="btn btn-sm btn-success">
                                                <i class="fas fa-check"></i> Mark Attendance
                                            </a>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="text-center py-4">
                                        <i class="fas fa-calendar-times" style="font-size: 48px; color: #ccc;"></i>
                                        <p class="mt-2">No classes scheduled for today</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- My Subjects -->
                    <div class="col-md-6 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h5><i class="fas fa-book-open"></i> My Subjects</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Subject Code</th>
                                                <th>Subject Name</th>
                                                <th>Course</th>
                                                <th>Credits</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach($subjects as $subject): ?>
                                            <tr>
                                                <td><?php echo $subject['subject_code']; ?></td>
                                                <td><?php echo $subject['subject_name']; ?></td>
                                                <td><?php echo $subject['course_name']; ?> (Sem <?php echo $subject['semester']; ?>)</td>
                                                <td><?php echo $subject['credits']; ?></td>
                                                <td>
                                                    <a href="attendance/mark-attendance.php?subject_id=<?php echo $subject['id']; ?>" class="btn btn-sm btn-primary">
                                                        <i class="fas fa-check"></i> Mark
                                                    </a>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                            <?php if(count($subjects) == 0): ?>
                                            <tr>
                                                <td colspan="5" class="text-center">No subjects assigned</td>
                                            </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5><i class="fas fa-bolt"></i> Quick Actions</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-3 col-sm-6 mb-2">
                                        <a href="attendance/mark-attendance.php" class="btn btn-primary w-100 btn-quick">
                                            <i class="fas fa-user-check"></i> Mark Attendance
                                        </a>
                                    </div>
                                    <div class="col-md-3 col-sm-6 mb-2">
                                        <a href="exams/marks-entry.php" class="btn btn-success w-100 btn-quick">
                                            <i class="fas fa-edit"></i> Enter Marks
                                        </a>
                                    </div>
                                    <div class="col-md-3 col-sm-6 mb-2">
                                        <a href="resources/upload-material.php" class="btn btn-info w-100 btn-quick">
                                            <i class="fas fa-upload"></i> Upload Material
                                        </a>
                                    </div>
                                    <div class="col-md-3 col-sm-6 mb-2">
                                        <a href="requests/leave-requests.php" class="btn btn-warning w-100 btn-quick">
                                            <i class="fas fa-envelope"></i> View Requests
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>