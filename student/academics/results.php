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
    <title>Exam Results - EduTrack Pro</title>
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
        .result-summary { background: linear-gradient(135deg, #4361ee, #3f37c9); border-radius: 20px; padding: 30px; color: white; margin-bottom: 30px; display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; }
        .result-stat { text-align: center; padding: 20px; background: rgba(255,255,255,0.1); border-radius: 15px; }
        .result-stat .value { font-size: 2rem; font-weight: 700; }
        .result-table { background: white; border-radius: 20px; padding: 25px; overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #e9ecef; }
        th { background: #f8f9fa; font-weight: 600; }
        .grade-a-plus { color: #4cc9f0; font-weight: 600; }
        .grade-a { color: #4361ee; font-weight: 600; }
        .progress-bar-container { height: 8px; background: #e9ecef; border-radius: 10px; overflow: hidden; margin-top: 5px; }
        .progress-bar-fill { height: 100%; background: linear-gradient(90deg, #4361ee, #4cc9f0); border-radius: 10px; }
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
                <a href="../dashboard.php"><i class="fas fa-home"></i><span> Dashboard</span></a>
                <a href="../profile.php"><i class="fas fa-user"></i><span> Profile</span></a>
                <a href="../attendance/view-attendance.php"><i class="fas fa-calendar-check"></i><span> Attendance</span></a>
                <a href="academic-timeline.php"><i class="fas fa-timeline"></i><span> Academic Timeline</span></a>
                <a href="course-outline.php"><i class="fas fa-book"></i><span> Course Outline</span></a>
                <a href="exam-timetable.php"><i class="fas fa-clock"></i><span> Exam Timetable</span></a>
                <a href="results.php" class="active"><i class="fas fa-chart-line"></i><span> Results</span></a>
                <a href="../logout.php"><i class="fas fa-sign-out-alt"></i><span> Logout</span></a>
            </nav>
        </aside>

        <main class="main-content" id="mainContent">
            <header class="top-header">
                <button class="mobile-menu-btn" id="mobileMenuBtn"><i class="fas fa-bars"></i></button>
                <div><h2>Exam Results</h2></div>
                <div class="user-profile">
                    <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($user['full_name']); ?>&background=4361ee&color=fff" alt="Profile">
                    <div><h4><?php echo htmlspecialchars($user['full_name']); ?></h4><p><?php echo htmlspecialchars($user['id_number']); ?></p></div>
                </div>
            </header>

            <div class="result-summary">
                <div class="result-stat"><div class="label">Overall CGPA</div><div class="value">8.5</div><div class="sub">Out of 10</div></div>
                <div class="result-stat"><div class="label">Current SGPA</div><div class="value">8.7</div><div class="sub">Semester 3</div></div>
                <div class="result-stat"><div class="label">Total Credits</div><div class="value">72</div><div class="sub">Earned: 68</div></div>
            </div>

            <div class="result-table"><h3 style="margin-bottom: 20px;">Semester 3 Results</h3>
                <table><thead><tr><th>Subject Code</th><th>Subject Name</th><th>Credits</th><th>Total</th><th>Grade</th><th>Status</th></tr></thead>
                <tbody><tr><td>CS301</td><td>Data Structures</td><td>4</td><td>85</td><td class="grade-a">A</td><td>Pass</td></tr>
                <tr><td>CS302</td><td>Database Management</td><td>4</td><td>92</td><td class="grade-a-plus">A+</td><td>Pass</td></tr>
                <tr><td>CS303</td><td>Web Development</td><td>3</td><td>78</td><td class="grade-a">B+</td><td>Pass</td></tr>
                <tr><td>CS304</td><td>Operating Systems</td><td>4</td><td>88</td><td class="grade-a">A</td><td>Pass</td></tr>
                <tr><td>CS305</td><td>Computer Networks</td><td>4</td><td>84</td><td class="grade-a">A</td><td>Pass</td></tr></tbody>
                <tfoot><tr><td colspan="6"><strong>SGPA: 8.7 | Percentage: 85.4%</strong></td></tr></tfoot></table>
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