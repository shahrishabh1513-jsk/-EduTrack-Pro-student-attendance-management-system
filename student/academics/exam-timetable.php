<?php
require_once '../../includes/config.php';

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
    <title>Exam Timetable - EduTrack Pro</title>
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
        .timetable-header { background: linear-gradient(135deg, #4361ee, #3f37c9); border-radius: 20px; padding: 30px; color: white; margin-bottom: 30px; }
        .timetable-grid { display: grid; grid-template-columns: repeat(5, 1fr); gap: 15px; }
        .exam-card { background: white; border-radius: 15px; padding: 20px; text-align: center; transition: all 0.3s; }
        .exam-card:hover { transform: translateY(-5px); box-shadow: 0 10px 25px rgba(0,0,0,0.1); }
        .exam-date { font-size: 1.1rem; font-weight: 600; color: #4361ee; margin-bottom: 10px; }
        .exam-subject { font-weight: 600; margin-bottom: 5px; }
        .exam-time { font-size: 0.85rem; color: #6c757d; }
        @media (max-width: 768px) { .timetable-grid { grid-template-columns: 1fr; } .sidebar { transform: translateX(-100%); } .sidebar.active { transform: translateX(0); } .main-content { margin-left: 0; } }
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
                <a href="../dashboard.php"><i class="fas fa-home"></i><span> Dashboard</span></a>
                <a href="../profile.php"><i class="fas fa-user"></i><span> Profile</span></a>
                <a href="../attendance/view-attendance.php"><i class="fas fa-calendar-check"></i><span> Attendance</span></a>
                <a href="academic-timeline.php"><i class="fas fa-timeline"></i><span> Academic Timeline</span></a>
                <a href="course-outline.php"><i class="fas fa-book"></i><span> Course Outline</span></a>
                <a href="exam-timetable.php" class="active"><i class="fas fa-clock"></i><span> Exam Timetable</span></a>
                <a href="results.php"><i class="fas fa-chart-line"></i><span> Results</span></a>
                <a href="../logout.php"><i class="fas fa-sign-out-alt"></i><span> Logout</span></a>
            </nav>
        </aside>

        <main class="main-content" id="mainContent">
            <header class="top-header">
                <button class="mobile-menu-btn" id="mobileMenuBtn"><i class="fas fa-bars"></i></button>
                <div><h2>Exam Timetable</h2></div>
                <div class="user-profile">
                    <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($user['full_name']); ?>&background=4361ee&color=fff" alt="Profile">
                    <div><h4><?php echo htmlspecialchars($user['full_name']); ?></h4><p><?php echo htmlspecialchars($user['id_number']); ?></p></div>
                </div>
            </header>

            <div class="timetable-header"><h2>End Semester Examination 2024</h2><p>March 25 - April 15, 2024</p></div>

            <div class="timetable-grid">
                <div class="exam-card"><div class="exam-date">March 25</div><div class="exam-subject">Data Structures</div><div class="exam-time">10:00 AM - 1:00 PM</div></div>
                <div class="exam-card"><div class="exam-date">March 26</div><div class="exam-subject">Database Management</div><div class="exam-time">10:00 AM - 1:00 PM</div></div>
                <div class="exam-card"><div class="exam-date">March 27</div><div class="exam-subject">Web Development</div><div class="exam-time">10:00 AM - 1:00 PM</div></div>
                <div class="exam-card"><div class="exam-date">March 28</div><div class="exam-subject">Operating Systems</div><div class="exam-time">10:00 AM - 1:00 PM</div></div>
                <div class="exam-card"><div class="exam-date">March 29</div><div class="exam-subject">Computer Networks</div><div class="exam-time">10:00 AM - 1:00 PM</div></div>
            </div>

            <div style="background: #fff3cd; border-radius: 15px; padding: 20px; margin-top: 30px;"><h4 style="color: #856404;"><i class="fas fa-exclamation-triangle"></i> Important Instructions</h4><ul style="color: #856404; padding-left: 20px;"><li>Report to examination hall 15 minutes before the scheduled time</li><li>Bring your ID card and hall ticket</li><li>Electronic devices are not allowed inside the examination hall</li></ul></div>
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