<?php
// faculty/classes/my-classes.php
require_once '../../includes/config.php';
redirectIfNotFaculty();

$user_id = $_SESSION['user_id'];
$faculty_id = getFacultyIdByUserId($user_id);

// Get faculty timetable
$query = "SELECT t.*, s.subject_name, c.course_name, c.semester 
          FROM timetable t
          JOIN subjects s ON t.subject_id = s.id
          JOIN courses c ON t.course_id = c.id
          WHERE t.faculty_id = :faculty_id
          ORDER BY FIELD(t.day_of_week, 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'), t.start_time";
$stmt = $db->prepare($query);
$stmt->bindParam(':faculty_id', $faculty_id);
$stmt->execute();
$classes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Group by day
$schedule = [];
$days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
foreach($days as $day) {
    $schedule[$day] = [];
}
foreach($classes as $class) {
    $schedule[$class['day_of_week']][] = $class;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Classes - EduTrack Pro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .schedule-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .day-header {
            background: linear-gradient(135deg, #11998e, #38ef7d);
            color: white;
            padding: 10px 15px;
            border-radius: 15px 15px 0 0;
        }
        .class-item {
            border-left: 4px solid #11998e;
            margin: 10px;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 8px;
            transition: transform 0.3s;
        }
        .class-item:hover {
            transform: translateX(5px);
            background: #e9ecef;
        }
        .today-class {
            background: #d4edda;
            border-left-color: #28a745;
        }
    </style>
</head>
<body>
    <?php include '../includes/faculty-nav.php'; ?>
    
    <div class="container-fluid">
        <div class="row">
            <?php include '../includes/faculty-sidebar.php'; ?>
            
            <main class="col-md-10 ms-sm-auto px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">My Class Schedule</h1>
                </div>
                
                <div class="row">
                    <?php foreach($schedule as $day => $day_classes): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card schedule-card">
                            <div class="day-header">
                                <h5 class="mb-0">
                                    <?php echo ucfirst($day); ?>
                                    <?php if(strtolower(date('l')) == $day): ?>
                                        <span class="badge bg-warning float-end">Today</span>
                                    <?php endif; ?>
                                </h5>
                            </div>
                            <div class="card-body">
                                <?php if(count($day_classes) > 0): ?>
                                    <?php foreach($day_classes as $class): ?>
                                    <div class="class-item <?php echo strtolower(date('l')) == $day ? 'today-class' : ''; ?>">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h6 class="mb-1"><?php echo $class['subject_name']; ?></h6>
                                                <small class="text-muted">
                                                    <i class="fas fa-graduation-cap"></i> <?php echo $class['course_name']; ?> (Sem <?php echo $class['semester']; ?>)
                                                </small>
                                            </div>
                                            <div class="text-end">
                                                <span class="badge bg-primary">
                                                    <?php echo date('h:i A', strtotime($class['start_time'])); ?>
                                                </span>
                                                <br>
                                                <small class="text-muted">Room: <?php echo $class['room_no']; ?></small>
                                            </div>
                                        </div>
                                        <div class="mt-2">
                                            <a href="class-detail.php?id=<?php echo $class['id']; ?>" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-eye"></i> View Details
                                            </a>
                                            <a href="../attendance/mark-attendance.php?subject_id=<?php echo $class['subject_id']; ?>" class="btn btn-sm btn-outline-success">
                                                <i class="fas fa-check"></i> Mark Attendance
                                            </a>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <p class="text-muted text-center mb-0">No classes scheduled</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </main>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>