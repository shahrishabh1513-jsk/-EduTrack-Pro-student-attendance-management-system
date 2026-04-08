<?php
require_once '../includes/config.php';

if (!isLoggedIn() || !hasRole('admin')) {
    redirect('login.php');
}

$user = getUserData($conn, $_SESSION['user_id']);

// Get statistics
$student_result = mysqli_query($conn, "SELECT COUNT(*) as count FROM students");
$student_count = mysqli_fetch_assoc($student_result)['count'];

$faculty_result = mysqli_query($conn, "SELECT COUNT(*) as count FROM faculty");
$faculty_count = mysqli_fetch_assoc($faculty_result)['count'];

$course_result = mysqli_query($conn, "SELECT COUNT(*) as count FROM courses");
$course_count = mysqli_fetch_assoc($course_result)['count'];

$pending_leaves = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM leave_applications WHERE status='pending'"))['count'];
$pending_grievances = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM grievances WHERE status='pending'"))['count'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - EduTrack Pro</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Poppins', sans-serif; background: #f8f9fa; }
        .dashboard-container { display: flex; min-height: 100vh; }
        .sidebar { width: 280px; background: white; position: fixed; height: 100vh; overflow-y: auto; box-shadow: 2px 0 10px rgba(0,0,0,0.05); transition: all 0.3s; z-index: 100; }
        .sidebar.collapsed { width: 80px; }
        .sidebar.collapsed .sidebar-nav a span, .sidebar.collapsed .sidebar-header h3 { display: none; }
        .sidebar-header { padding: 25px 20px; border-bottom: 1px solid #e9ecef; display: flex; align-items: center; justify-content: space-between; }
        .sidebar-header .logo { display: flex; align-items: center; gap: 10px; }
        .sidebar-header i { font-size: 2rem; color: #4361ee; }
        .sidebar-header h3 { font-size: 1.2rem; color: #1e293b; }
        .toggle-sidebar { background: none; border: none; cursor: pointer; font-size: 1.2rem; color: #6c757d; }
        .sidebar-nav { padding: 20px 0; }
        .sidebar-nav a { display: flex; align-items: center; gap: 15px; padding: 12px 20px; color: #6c757d; text-decoration: none; transition: all 0.3s; }
        .sidebar-nav a:hover, .sidebar-nav a.active { background: rgba(67,97,238,0.05); color: #4361ee; border-left: 4px solid #4361ee; }
        .main-content { flex: 1; margin-left: 280px; padding: 20px 30px; transition: all 0.3s; }
        .main-content.expanded { margin-left: 80px; }
        .top-header { background: white; padding: 15px 25px; border-radius: 15px; display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        .user-profile { display: flex; align-items: center; gap: 10px; cursor: pointer; }
        .user-profile img { width: 40px; height: 40px; border-radius: 50%; }
        .welcome-banner { background: linear-gradient(135deg, #4361ee, #3f37c9); padding: 30px; border-radius: 20px; color: white; margin-bottom: 30px; display: flex; justify-content: space-between; align-items: center; }
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: white; padding: 20px; border-radius: 15px; display: flex; justify-content: space-between; align-items: center; transition: all 0.3s; cursor: pointer; }
        .stat-card:hover { transform: translateY(-5px); box-shadow: 0 10px 25px rgba(0,0,0,0.07); }
        .stat-number { font-size: 1.8rem; font-weight: 700; color: #1e293b; }
        .stat-icon { width: 50px; height: 50px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; }
        .stat-icon.blue { background: rgba(67,97,238,0.1); color: #4361ee; }
        .stat-icon.green { background: rgba(76,201,240,0.1); color: #4cc9f0; }
        .stat-icon.orange { background: rgba(247,37,133,0.1); color: #f72585; }
        .stat-icon.purple { background: rgba(63,55,201,0.1); color: #3f37c9; }
        .content-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: 25px; }
        .content-card { background: white; border-radius: 20px; padding: 25px; }
        .card-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; padding-bottom: 15px; border-bottom: 1px solid #e9ecef; }
        .quick-actions { display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px; }
        .action-btn { text-align: center; padding: 15px; background: #f8f9fa; border-radius: 12px; text-decoration: none; color: #1e293b; transition: all 0.3s; display: block; }
        .action-btn:hover { background: #4361ee; color: white; transform: translateY(-3px); }
        .action-btn i { font-size: 1.5rem; margin-bottom: 8px; display: block; }
        .activity-item { display: flex; align-items: center; gap: 15px; padding: 12px 0; border-bottom: 1px solid #e9ecef; }
        .activity-icon { width: 40px; height: 40px; background: rgba(67,97,238,0.1); border-radius: 10px; display: flex; align-items: center; justify-content: center; color: #4361ee; }
        @media (max-width: 768px) { .sidebar { transform: translateX(-100%); } .sidebar.active { transform: translateX(0); } .main-content { margin-left: 0; } .stats-grid { grid-template-columns: 1fr; } }
        .mobile-menu-btn { display: none; background: none; border: none; font-size: 1.5rem; cursor: pointer; }
        @media (max-width: 768px) { .mobile-menu-btn { display: block; } }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <div class="logo"><i class="fas fa-graduation-cap"></i><h3>EduTrack Pro</h3></div>
                <button class="toggle-sidebar" id="toggleSidebar"><i class="fas fa-chevron-left"></i></button>
            </div>
            <nav class="sidebar-nav">
                <a href="dashboard.php" class="active"><i class="fas fa-home"></i><span> Dashboard</span></a>
                <a href="profile.php"><i class="fas fa-user"></i><span> Profile</span></a>
                <a href="management/student-management/students-list.php"><i class="fas fa-user-graduate"></i><span> Student Management</span></a>
                <a href="management/faculty-management/faculty-list.php"><i class="fas fa-chalkboard-teacher"></i><span> Faculty Management</span></a>
                <a href="management/course-management/courses-list.php"><i class="fas fa-book"></i><span> Course Management</span></a>
                <a href="attendance/attendance-overview.php"><i class="fas fa-calendar-check"></i><span> Attendance</span></a>
                <a href="examinations/exam-schedule.php"><i class="fas fa-file-alt"></i><span> Examinations</span></a>
                <a href="requests/leave-management.php"><i class="fas fa-calendar-plus"></i><span> Leave Management</span></a>
                <a href="requests/grievance-management.php"><i class="fas fa-exclamation-circle"></i><span> Grievances</span></a>
                <a href="communications/notices.php"><i class="fas fa-bell"></i><span> Notices</span></a>
                <a href="reports/attendance-report.php"><i class="fas fa-chart-bar"></i><span> Reports</span></a>
                <a href="system/system-settings.php"><i class="fas fa-cog"></i><span> Settings</span></a>
                <a href="../logout.php"><i class="fas fa-sign-out-alt"></i><span> Logout</span></a>
            </nav>
        </aside>

        <main class="main-content" id="mainContent">
            <header class="top-header">
                <button class="mobile-menu-btn" id="mobileMenuBtn"><i class="fas fa-bars"></i></button>
                <div><h2>Admin Dashboard</h2></div>
                <div class="user-profile">
                    <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($user['full_name']); ?>&background=4361ee&color=fff" alt="Profile">
                    <div><h4><?php echo htmlspecialchars($user['full_name']); ?></h4><p>Administrator</p></div>
                </div>
            </header>

            <div class="welcome-banner">
                <div><h1>Welcome, Admin! 👋</h1><p>Here's your system overview</p></div>
                <div><i class="fas fa-user-tie" style="font-size: 5rem; opacity: 0.3;"></i></div>
            </div>

            <div class="stats-grid">
                <div class="stat-card" onclick="window.location.href='management/student-management/students-list.php'">
                    <div><h3>Total Students</h3><div class="stat-number"><?php echo $student_count; ?></div><small>Registered</small></div>
                    <div class="stat-icon blue"><i class="fas fa-user-graduate"></i></div>
                </div>
                <div class="stat-card" onclick="window.location.href='management/faculty-management/faculty-list.php'">
                    <div><h3>Total Faculty</h3><div class="stat-number"><?php echo $faculty_count; ?></div><small>Teaching Staff</small></div>
                    <div class="stat-icon green"><i class="fas fa-chalkboard-teacher"></i></div>
                </div>
                <div class="stat-card" onclick="window.location.href='management/course-management/courses-list.php'">
                    <div><h3>Active Courses</h3><div class="stat-number"><?php echo $course_count; ?></div><small>This Semester</small></div>
                    <div class="stat-icon orange"><i class="fas fa-book"></i></div>
                </div>
                <div class="stat-card" onclick="window.location.href='requests/leave-management.php'">
                    <div><h3>Pending Leaves</h3><div class="stat-number"><?php echo $pending_leaves; ?></div><small>Awaiting Approval</small></div>
                    <div class="stat-icon purple"><i class="fas fa-calendar-times"></i></div>
                </div>
            </div>

            <div class="content-grid">
                <div class="content-card">
                    <div class="card-header"><h3><i class="fas fa-bolt"></i> Quick Actions</h3></div>
                    <div class="quick-actions">
                        <a href="management/student-management/add-student.php" class="action-btn"><i class="fas fa-user-plus"></i><span>Add Student</span></a>
                        <a href="management/faculty-management/add-faculty.php" class="action-btn"><i class="fas fa-chalkboard-teacher"></i><span>Add Faculty</span></a>
                        <a href="management/course-management/add-course.php" class="action-btn"><i class="fas fa-book"></i><span>Add Course</span></a>
                        <a href="communications/add-notice.php" class="action-btn"><i class="fas fa-bullhorn"></i><span>Post Notice</span></a>
                        <a href="examinations/exam-schedule.php" class="action-btn"><i class="fas fa-calendar-alt"></i><span>Exam Schedule</span></a>
                        <a href="reports/attendance-report.php" class="action-btn"><i class="fas fa-chart-line"></i><span>View Reports</span></a>
                    </div>
                </div>

                <div class="content-card">
                    <div class="card-header"><h3><i class="fas fa-history"></i> Recent Activities</h3><a href="#">View All</a></div>
                    <div class="activity-item"><div class="activity-icon"><i class="fas fa-user-plus"></i></div><div><strong>New Student Registration</strong><br><small>25 students enrolled in BSC IT - 2 hours ago</small></div></div>
                    <div class="activity-item"><div class="activity-icon"><i class="fas fa-file-alt"></i></div><div><strong>Exam Form Submissions</strong><br><small>156 forms pending verification - 5 hours ago</small></div></div>
                    <div class="activity-item"><div class="activity-icon"><i class="fas fa-calendar-check"></i></div><div><strong>Attendance Marked</strong><br><small>12 classes completed today - 8 hours ago</small></div></div>
                </div>
            </div>
        </main>
    </div>

    <script>
        const sidebar = document.getElementById('sidebar'), mainContent = document.getElementById('mainContent'), toggleBtn = document.getElementById('toggleSidebar'), mobileMenuBtn = document.getElementById('mobileMenuBtn');
        if(toggleBtn) toggleBtn.addEventListener('click', function() { sidebar.classList.toggle('collapsed'); mainContent.classList.toggle('expanded'); });
        if(mobileMenuBtn) mobileMenuBtn.addEventListener('click', function() { sidebar.classList.toggle('active'); });
        function handleResponsive() { if(window.innerWidth <= 768) { sidebar.classList.add('collapsed'); mainContent.classList.add('expanded'); } else { sidebar.classList.remove('collapsed', 'active'); mainContent.classList.remove('expanded'); } }
        window.addEventListener('resize', handleResponsive); handleResponsive();
    </script>
</body>
</html>