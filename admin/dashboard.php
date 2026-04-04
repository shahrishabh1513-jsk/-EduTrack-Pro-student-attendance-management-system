<?php
// admin/dashboard.php
require_once '../includes/config.php';
redirectIfNotAdmin();

// Get statistics
$stats = [];

// Total students
$query = "SELECT COUNT(*) as total FROM students";
$stmt = $db->query($query);
$stats['students'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Total faculty
$query = "SELECT COUNT(*) as total FROM faculty";
$stmt = $db->query($query);
$stats['faculty'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Total courses
$query = "SELECT COUNT(*) as total FROM courses";
$stmt = $db->query($query);
$stats['courses'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Total departments
$query = "SELECT COUNT(*) as total FROM departments";
$stmt = $db->query($query);
$stats['departments'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Today's attendance
$query = "SELECT COUNT(DISTINCT student_id) as total FROM attendance WHERE date = CURDATE()";
$stmt = $db->query($query);
$stats['today_attendance'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Pending leaves
$query = "SELECT COUNT(*) as total FROM leave_applications WHERE status = 'pending'";
$stmt = $db->query($query);
$stats['pending_leaves'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Recent activities
$activity_query = "SELECT l.*, u.full_name, u.role 
                   FROM system_logs l
                   JOIN users u ON l.user_id = u.id
                   ORDER BY l.created_at DESC LIMIT 10";
$activities = $db->query($activity_query)->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - EduTrack Pro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
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
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
            padding: 30px;
            border-radius: 15px;
            margin-bottom: 30px;
        }
        .activity-item {
            border-left: 3px solid #f5576c;
            margin-bottom: 15px;
            padding: 10px 15px;
            background: #f8f9fa;
            border-radius: 8px;
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
                        <i class="fas fa-school" style="font-size: 40px; color: white;"></i>
                        <h5 class="text-white mt-2">EduTrack Pro</h5>
                        <small class="text-white-50">Admin Portal</small>
                    </div>
                    <ul class="nav flex-column px-3">
                        <li class="nav-item">
                            <a class="nav-link active" href="dashboard.php">
                                <i class="fas fa-tachometer-alt"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#">
                                <i class="fas fa-users"></i> Student Management
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#">
                                <i class="fas fa-chalkboard-teacher"></i> Faculty Management
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#">
                                <i class="fas fa-book"></i> Course Management
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#">
                                <i class="fas fa-building"></i> Department Management
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#">
                                <i class="fas fa-calendar-check"></i> Attendance Overview
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#">
                                <i class="fas fa-file-alt"></i> Reports
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#">
                                <i class="fas fa-cog"></i> System Settings
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
                            <p class="mb-0">System Administrator | Manage and monitor the entire education system</p>
                        </div>
                        <div class="col-md-4 text-end">
                            <i class="fas fa-user-shield" style="font-size: 60px;"></i>
                        </div>
                    </div>
                </div>

                <!-- Stats Cards -->
                <div class="row mb-4">
                    <div class="col-md-3 mb-3">
                        <div class="card stat-card bg-primary text-white" onclick="window.location.href='#'">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="card-title">Total Students</h6>
                                        <h2><?php echo $stats['students']; ?></h2>
                                    </div>
                                    <i class="fas fa-users" style="font-size: 2rem; opacity: 0.5;"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card stat-card bg-success text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="card-title">Total Faculty</h6>
                                        <h2><?php echo $stats['faculty']; ?></h2>
                                    </div>
                                    <i class="fas fa-chalkboard-teacher" style="font-size: 2rem; opacity: 0.5;"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card stat-card bg-info text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="card-title">Total Courses</h6>
                                        <h2><?php echo $stats['courses']; ?></h2>
                                    </div>
                                    <i class="fas fa-book" style="font-size: 2rem; opacity: 0.5;"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card stat-card bg-warning text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="card-title">Departments</h6>
                                        <h2><?php echo $stats['departments']; ?></h2>
                                    </div>
                                    <i class="fas fa-building" style="font-size: 2rem; opacity: 0.5;"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <!-- Attendance Overview -->
                    <div class="col-md-6 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h5><i class="fas fa-chart-line"></i> Today's Overview</h5>
                            </div>
                            <div class="card-body">
                                <div class="row text-center">
                                    <div class="col-6">
                                        <div class="border rounded p-3">
                                            <i class="fas fa-calendar-check" style="font-size: 36px; color: #28a745;"></i>
                                            <h3 class="mt-2"><?php echo $stats['today_attendance']; ?></h3>
                                            <p class="text-muted">Students Present Today</p>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="border rounded p-3">
                                            <i class="fas fa-envelope" style="font-size: 36px; color: #ffc107;"></i>
                                            <h3 class="mt-2"><?php echo $stats['pending_leaves']; ?></h3>
                                            <p class="text-muted">Pending Leave Requests</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Stats Chart -->
                    <div class="col-md-6 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h5><i class="fas fa-chart-pie"></i> System Distribution</h5>
                            </div>
                            <div class="card-body">
                                <canvas id="statsChart" height="200"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <!-- Recent Activities -->
                    <div class="col-md-12 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h5><i class="fas fa-history"></i> Recent System Activities</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>User</th>
                                                <th>Role</th>
                                                <th>Action</th>
                                                <th>Time</th>
                                                <th>IP Address</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach($activities as $activity): ?>
                                            <tr>
                                                <td><?php echo $activity['full_name']; ?></td>
                                                <td>
                                                    <span class="badge bg-<?php 
                                                        echo $activity['role'] == 'admin' ? 'danger' : ($activity['role'] == 'faculty' ? 'info' : 'success'); 
                                                    ?>">
                                                        <?php echo ucfirst($activity['role']); ?>
                                                    </span>
                                                </td>
                                                <td><?php echo $activity['action']; ?></td>
                                                <td><?php echo date('d M Y h:i A', strtotime($activity['created_at'])); ?></td>
                                                <td><?php echo $activity['ip_address']; ?></td>
                                            </tr>
                                            <?php endforeach; ?>
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
        // Statistics Chart
        const ctx = document.getElementById('statsChart').getContext('2d');
        const statsChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Students', 'Faculty', 'Courses', 'Departments'],
                datasets: [{
                    data: [<?php echo $stats['students']; ?>, <?php echo $stats['faculty']; ?>, <?php echo $stats['courses']; ?>, <?php echo $stats['departments']; ?>],
                    backgroundColor: ['#007bff', '#28a745', '#17a2b8', '#ffc107'],
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