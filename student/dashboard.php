<?php
require_once '../includes/config.php';

if (!isLoggedIn() || !hasRole('student')) {
    redirect('login.php');
}

$user = getUserData($conn, $_SESSION['user_id']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard - EduTrack Pro</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Poppins', sans-serif; background: #f8f9fa; }
        .dashboard-container { display: flex; min-height: 100vh; }
        .sidebar { width: 280px; background: white; position: fixed; height: 100vh; overflow-y: auto; box-shadow: 2px 0 10px rgba(0,0,0,0.05); transition: all 0.3s; z-index: 100; }
        .sidebar.collapsed { width: 80px; }
        .sidebar.collapsed .sidebar-nav a span, .sidebar.collapsed .sidebar-header h3 { display: none; }
        .sidebar.collapsed .sidebar-nav a { justify-content: center; padding: 15px; }
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
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 25px; margin-bottom: 30px; }
        .stat-card { background: white; padding: 25px; border-radius: 20px; display: flex; justify-content: space-between; align-items: center; transition: all 0.3s; cursor: pointer; }
        .stat-card:hover { transform: translateY(-5px); box-shadow: 0 10px 25px rgba(0,0,0,0.07); }
        .stat-number { font-size: 2rem; font-weight: 700; color: #1e293b; }
        .stat-icon { width: 60px; height: 60px; border-radius: 15px; display: flex; align-items: center; justify-content: center; font-size: 2rem; }
        .stat-icon.blue { background: rgba(67,97,238,0.1); color: #4361ee; }
        .stat-icon.green { background: rgba(76,201,240,0.1); color: #4cc9f0; }
        .stat-icon.orange { background: rgba(247,37,133,0.1); color: #f72585; }
        .content-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: 25px; }
        .content-card { background: white; border-radius: 20px; padding: 25px; }
        .card-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; padding-bottom: 15px; border-bottom: 1px solid #e9ecef; }
        .card-header a { color: #4361ee; text-decoration: none; }
        .notice-item, .timetable-item { display: flex; align-items: center; gap: 15px; padding: 12px 0; border-bottom: 1px solid #e9ecef; }
        .notice-icon { width: 40px; height: 40px; background: rgba(67,97,238,0.1); border-radius: 10px; display: flex; align-items: center; justify-content: center; color: #4361ee; }
        .progress-item { margin-bottom: 15px; }
        .progress-info { display: flex; justify-content: space-between; margin-bottom: 5px; font-size: 0.9rem; }
        .progress-bar { height: 8px; background: #e9ecef; border-radius: 10px; overflow: hidden; }
        .progress-fill { height: 100%; background: linear-gradient(90deg, #4361ee, #4cc9f0); border-radius: 10px; transition: width 0.3s; }
        @media (max-width: 768px) { .sidebar { transform: translateX(-100%); } .sidebar.active { transform: translateX(0); } .main-content { margin-left: 0; } }
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
                <a href="attendance/view-attendance.php"><i class="fas fa-calendar-check"></i><span> Attendance</span></a>
                <a href="academics/academic-timeline.php"><i class="fas fa-timeline"></i><span> Academic Timeline</span></a>
                <a href="academics/course-outline.php"><i class="fas fa-book"></i><span> Course Outline</span></a>
                <a href="academics/exam-timetable.php"><i class="fas fa-clock"></i><span> Exam Timetable</span></a>
                <a href="academics/results.php"><i class="fas fa-chart-line"></i><span> Results</span></a>
                <a href="requests/leave-apply.php"><i class="fas fa-calendar-plus"></i><span> Apply Leave</span></a>
                <a href="requests/leave-status.php"><i class="fas fa-calendar-check"></i><span> Leave Status</span></a>
                <a href="requests/grievance.php"><i class="fas fa-exclamation-circle"></i><span> Grievance</span></a>
                <a href="exams/exam-form.php"><i class="fas fa-file-alt"></i><span> Exam Form</span></a>
                <a href="resources/notices.php"><i class="fas fa-bell"></i><span> Notices</span></a>
                <a href="resources/feedback.php"><i class="fas fa-star"></i><span> Feedback</span></a>
                <a href="../logout.php"><i class="fas fa-sign-out-alt"></i><span> Logout</span></a>
            </nav>
        </aside>

        <main class="main-content" id="mainContent">
            <header class="top-header">
                <button class="mobile-menu-btn" id="mobileMenuBtn"><i class="fas fa-bars"></i></button>
                <div><h2>Dashboard</h2></div>
                <div class="user-profile">
                    <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($user['full_name']); ?>&background=4361ee&color=fff" alt="Profile">
                    <div><h4><?php echo htmlspecialchars($user['full_name']); ?></h4><p><?php echo htmlspecialchars($user['id_number']); ?></p></div>
                </div>
            </header>

            <div class="welcome-banner">
                <div><h1>Welcome back, <?php echo explode(' ', $user['full_name'])[0]; ?>! 👋</h1><p>Here's your academic overview for today</p></div>
                <div><i class="fas fa-user-graduate" style="font-size: 5rem; opacity: 0.3;"></i></div>
            </div>

            <div class="stats-grid">
                <div class="stat-card" onclick="window.location.href='attendance/view-attendance.php'">
                    <div><h3>Attendance</h3><div class="stat-number" id="attendancePercent">--%</div><small>This Semester</small></div>
                    <div class="stat-icon blue"><i class="fas fa-calendar-check"></i></div>
                </div>
                <div class="stat-card" onclick="window.location.href='academics/results.php'">
                    <div><h3>CGPA</h3><div class="stat-number"><?php echo $user['cgpa'] ?? '8.5'; ?></div><small>Out of 10</small></div>
                    <div class="stat-icon green"><i class="fas fa-star"></i></div>
                </div>
                <div class="stat-card" onclick="window.location.href='academics/exam-timetable.php'">
                    <div><h3>Upcoming Exams</h3><div class="stat-number">3</div><small>Next in 5 days</small></div>
                    <div class="stat-icon orange"><i class="fas fa-file-alt"></i></div>
                </div>
            </div>

            <div class="content-grid">
                <div class="content-card">
                    <div class="card-header"><h3><i class="fas fa-bell"></i> Recent Notices</h3><a href="resources/notices.php">View All</a></div>
                    <div id="recentNotices">Loading...</div>
                </div>
                <div class="content-card">
                    <div class="card-header"><h3><i class="fas fa-clock"></i> Today's Schedule</h3><a href="academics/exam-timetable.php">Full Schedule</a></div>
                    <div id="todayTimetable"></div>
                </div>
                <div class="content-card">
                    <div class="card-header"><h3><i class="fas fa-chart-pie"></i> Attendance Overview</h3><a href="attendance/view-attendance.php">Details</a></div>
                    <div id="attendanceOverview">Loading...</div>
                </div>
            </div>
        </main>
    </div>

    <script>
        async function loadDashboard() {
            try {
                const response = await fetch('../api/attendance.php?action=get_statistics&student_id=<?php echo $user['id']; ?>');
                const data = await response.json();
                if(data.success && data.data.length > 0) {
                    const totalPercent = data.data.reduce((sum, item) => sum + item.percentage, 0) / data.data.length;
                    document.getElementById('attendancePercent').textContent = Math.round(totalPercent) + '%';
                    const attendanceHTML = data.data.map(item => `<div class="progress-item"><div class="progress-info"><span>${item.course_name}</span><span>${item.percentage}%</span></div><div class="progress-bar"><div class="progress-fill" style="width: ${item.percentage}%"></div></div></div>`).join('');
                    document.getElementById('attendanceOverview').innerHTML = attendanceHTML;
                } else {
                    document.getElementById('attendanceOverview').innerHTML = '<p>No attendance data available</p>';
                }
                
                const noticesResponse = await fetch('../api/notices.php?action=get_recent');
                const noticesData = await noticesResponse.json();
                if(noticesData.success && noticesData.data.length > 0) {
                    const noticesHTML = noticesData.data.slice(0, 3).map(notice => `<div class="notice-item"><div class="notice-icon"><i class="fas fa-bullhorn"></i></div><div><h4>${notice.title}</h4><p style="font-size:0.8rem;color:#6c757d;">${new Date(notice.created_at).toLocaleDateString()}</p></div></div>`).join('');
                    document.getElementById('recentNotices').innerHTML = noticesHTML;
                } else {
                    document.getElementById('recentNotices').innerHTML = '<p>No notices available</p>';
                }
                
                const timetable = [
                    { time: '09:00 AM', subject: 'Data Structures', teacher: 'Dr. Aakash Gupta' },
                    { time: '11:00 AM', subject: 'Database Management', teacher: 'Prof. Neha Shah' },
                    { time: '02:00 PM', subject: 'Web Development', teacher: 'Prof. Niraj Mehta' }
                ];
                const timetableHTML = timetable.map(item => `<div class="timetable-item"><div class="notice-icon"><i class="fas fa-book"></i></div><div><h4>${item.subject}</h4><p style="font-size:0.8rem;color:#6c757d;">${item.time} • ${item.teacher}</p></div></div>`).join('');
                document.getElementById('todayTimetable').innerHTML = timetableHTML;
            } catch(error) { console.error('Error:', error); }
        }
        
        const sidebar = document.getElementById('sidebar'), mainContent = document.getElementById('mainContent'), toggleBtn = document.getElementById('toggleSidebar'), mobileMenuBtn = document.getElementById('mobileMenuBtn');
        if(toggleBtn) toggleBtn.addEventListener('click', function() { sidebar.classList.toggle('collapsed'); mainContent.classList.toggle('expanded'); });
        if(mobileMenuBtn) mobileMenuBtn.addEventListener('click', function() { sidebar.classList.toggle('active'); });
        function handleResponsive() { if(window.innerWidth <= 768) { sidebar.classList.add('collapsed'); mainContent.classList.add('expanded'); } else { sidebar.classList.remove('collapsed', 'active'); mainContent.classList.remove('expanded'); } }
        window.addEventListener('resize', handleResponsive); handleResponsive();
        loadDashboard();
    </script>
</body>
</html>