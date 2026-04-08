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
    <title>Course Outline - EduTrack Pro</title>
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
        .course-card { background: white; border-radius: 20px; margin-bottom: 25px; overflow: hidden; }
        .course-header { background: #f8f9fa; padding: 20px 25px; display: flex; justify-content: space-between; align-items: center; cursor: pointer; }
        .course-header-left { display: flex; align-items: center; gap: 15px; }
        .course-icon { width: 50px; height: 50px; background: #4361ee; border-radius: 12px; display: flex; align-items: center; justify-content: center; color: white; font-size: 1.5rem; }
        .course-body { padding: 0; max-height: 0; overflow: hidden; transition: all 0.3s; }
        .course-card.expanded .course-body { padding: 25px; max-height: 800px; }
        .course-section { margin-bottom: 25px; }
        .section-title { font-size: 1rem; font-weight: 600; margin-bottom: 15px; display: flex; align-items: center; gap: 8px; }
        .section-title i { color: #4361ee; }
        .objectives-list { list-style: none; }
        .objectives-list li { padding: 8px 0; display: flex; align-items: center; gap: 10px; color: #6c757d; }
        .topics-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px; }
        .topic-item { background: #f8f9fa; padding: 15px; border-radius: 12px; }
        .topic-week { font-size: 0.85rem; color: #4361ee; font-weight: 600; margin-bottom: 5px; }
        @media (max-width: 768px) { .topics-grid { grid-template-columns: 1fr; } .sidebar { transform: translateX(-100%); } .sidebar.active { transform: translateX(0); } .main-content { margin-left: 0; } }
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
                <a href="course-outline.php" class="active"><i class="fas fa-book"></i><span> Course Outline</span></a>
                <a href="exam-timetable.php"><i class="fas fa-clock"></i><span> Exam Timetable</span></a>
                <a href="results.php"><i class="fas fa-chart-line"></i><span> Results</span></a>
                <a href="../logout.php"><i class="fas fa-sign-out-alt"></i><span> Logout</span></a>
            </nav>
        </aside>

        <main class="main-content" id="mainContent">
            <header class="top-header">
                <button class="mobile-menu-btn" id="mobileMenuBtn"><i class="fas fa-bars"></i></button>
                <div><h2>Course Outline</h2></div>
                <div class="user-profile">
                    <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($user['full_name']); ?>&background=4361ee&color=fff" alt="Profile">
                    <div><h4><?php echo htmlspecialchars($user['full_name']); ?></h4><p><?php echo htmlspecialchars($user['id_number']); ?></p></div>
                </div>
            </header>

            <div class="course-card expanded" id="course1">
                <div class="course-header" onclick="toggleCourse('course1')">
                    <div class="course-header-left"><div class="course-icon"><i class="fas fa-code"></i></div><div><h3>Data Structures (CS301)</h3><p>4 Credits • Dr. Aakash Gupta</p></div></div>
                    <div><i class="fas fa-chevron-down"></i></div>
                </div>
                <div class="course-body">
                    <div class="course-section"><div class="section-title"><i class="fas fa-bullseye"></i> Course Objectives</div><ul class="objectives-list"><li><i class="fas fa-check-circle"></i> Understand fundamental data structures and their implementations</li><li><i class="fas fa-check-circle"></i> Analyze time and space complexity of algorithms</li><li><i class="fas fa-check-circle"></i> Apply appropriate data structures for problem solving</li></ul></div>
                    <div class="course-section"><div class="section-title"><i class="fas fa-list-ul"></i> Course Topics</div><div class="topics-grid"><div class="topic-item"><div class="topic-week">Week 1-2</div><div>Introduction to Data Structures & Arrays</div></div><div class="topic-item"><div class="topic-week">Week 3-4</div><div>Linked Lists</div></div><div class="topic-item"><div class="topic-week">Week 5-6</div><div>Stacks and Queues</div></div><div class="topic-item"><div class="topic-week">Week 7-8</div><div>Trees and Binary Search Trees</div></div></div></div>
                </div>
            </div>

            <div class="course-card" id="course2">
                <div class="course-header" onclick="toggleCourse('course2')">
                    <div class="course-header-left"><div class="course-icon"><i class="fas fa-database"></i></div><div><h3>Database Management (CS302)</h3><p>4 Credits • Prof. Neha Shah</p></div></div>
                    <div><i class="fas fa-chevron-down"></i></div>
                </div>
                <div class="course-body">
                    <div class="course-section"><div class="section-title"><i class="fas fa-bullseye"></i> Course Objectives</div><ul class="objectives-list"><li><i class="fas fa-check-circle"></i> Understand database concepts and architecture</li><li><i class="fas fa-check-circle"></i> Design and implement relational databases</li><li><i class="fas fa-check-circle"></i> Master SQL queries and optimization</li></ul></div>
                </div>
            </div>

            <div class="course-card" id="course3">
                <div class="course-header" onclick="toggleCourse('course3')">
                    <div class="course-header-left"><div class="course-icon"><i class="fas fa-globe"></i></div><div><h3>Web Development (CS303)</h3><p>3 Credits • Prof. Niraj Mehta</p></div></div>
                    <div><i class="fas fa-chevron-down"></i></div>
                </div>
                <div class="course-body">
                    <div class="course-section"><div class="section-title"><i class="fas fa-bullseye"></i> Course Objectives</div><ul class="objectives-list"><li><i class="fas fa-check-circle"></i> Understand web development fundamentals</li><li><i class="fas fa-check-circle"></i> Build responsive websites using HTML5, CSS3</li><li><i class="fas fa-check-circle"></i> Implement client-side functionality with JavaScript</li></ul></div>
                </div>
            </div>
        </main>
    </div>

    <script>
        function toggleCourse(courseId) { document.getElementById(courseId).classList.toggle('expanded'); }
        const sidebar = document.getElementById('sidebar'), mainContent = document.getElementById('mainContent'), toggleBtn = document.getElementById('toggleSidebar'), mobileMenuBtn = document.getElementById('mobileMenuBtn');
        if(toggleBtn) toggleBtn.addEventListener('click', function() { sidebar.classList.toggle('collapsed'); mainContent.classList.toggle('expanded'); });
        if(mobileMenuBtn) mobileMenuBtn.addEventListener('click', function() { sidebar.classList.toggle('active'); });
        function handleResponsive() { if(window.innerWidth <= 768) { sidebar.classList.add('collapsed'); mainContent.classList.add('expanded'); } else { sidebar.classList.remove('collapsed', 'active'); mainContent.classList.remove('expanded'); } }
        window.addEventListener('resize', handleResponsive); handleResponsive();
    </script>
</body>
</html>