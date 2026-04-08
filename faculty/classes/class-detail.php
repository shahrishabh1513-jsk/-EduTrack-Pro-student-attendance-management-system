<?php
require_once '../../includes/config.php';

if (!isLoggedIn() || !hasRole('faculty')) {
    redirect('login.php');
}

$user = getUserData($conn, $_SESSION['user_id']);
$class_code = $_GET['class'] ?? 'CS301';

$class_names = [
    'CS301' => 'Data Structures',
    'CS302' => 'Database Management',
    'CS303' => 'Web Development'
];
$class_name = $class_names[$class_code] ?? 'Data Structures';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $class_name; ?> - Class Detail</title>
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
        .toggle-sidebar { background: none; border: none; cursor: pointer; font-size: 1.2rem; color: #6c757d; }
        .sidebar-nav { padding: 20px 0; }
        .sidebar-nav a { display: flex; align-items: center; gap: 15px; padding: 12px 20px; color: #6c757d; text-decoration: none; transition: all 0.3s; }
        .sidebar-nav a:hover, .sidebar-nav a.active { background: rgba(67,97,238,0.05); color: #4361ee; border-left: 4px solid #4361ee; }
        .main-content { flex: 1; margin-left: 280px; padding: 20px 30px; transition: all 0.3s; }
        .top-header { background: white; padding: 15px 25px; border-radius: 15px; display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        .user-profile { display: flex; align-items: center; gap: 10px; cursor: pointer; }
        .user-profile img { width: 40px; height: 40px; border-radius: 50%; }
        .class-header { background: linear-gradient(135deg, #4361ee, #3f37c9); border-radius: 20px; padding: 30px; color: white; margin-bottom: 30px; }
        .info-cards { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .info-card { background: white; padding: 20px; border-radius: 15px; text-align: center; }
        .info-value { font-size: 1.8rem; font-weight: 700; color: #4361ee; }
        .action-buttons { display: flex; gap: 15px; margin-bottom: 30px; flex-wrap: wrap; }
        .action-btn { background: white; padding: 12px 25px; border: none; border-radius: 10px; cursor: pointer; display: flex; align-items: center; gap: 10px; }
        .student-table { background: white; border-radius: 20px; padding: 25px; overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #e9ecef; }
        th { background: #f8f9fa; font-weight: 600; }
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
                <a href="my-classes.php" class="active"><i class="fas fa-chalkboard"></i><span> My Classes</span></a>
                <a href="student-list.php"><i class="fas fa-users"></i><span> Student List</span></a>
                <a href="../logout.php"><i class="fas fa-sign-out-alt"></i><span> Logout</span></a>
            </nav>
        </aside>

        <main class="main-content" id="mainContent">
            <header class="top-header">
                <button class="mobile-menu-btn" id="mobileMenuBtn"><i class="fas fa-bars"></i></button>
                <div><h2><?php echo $class_name; ?></h2></div>
                <div class="user-profile">
                    <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($user['full_name']); ?>&background=4361ee&color=fff" alt="Profile">
                    <div><h4><?php echo htmlspecialchars($user['full_name']); ?></h4><p><?php echo htmlspecialchars($user['id_number']); ?></p></div>
                </div>
            </header>

            <div class="class-header">
                <h1><?php echo $class_name; ?> (<?php echo $class_code; ?>)</h1>
                <p>BSC IT - Semester 3 | Room 101 | Monday, Wednesday 9:00 AM</p>
            </div>

            <div class="info-cards">
                <div class="info-card"><div class="info-value">45</div><div>Total Students</div></div>
                <div class="info-card"><div class="info-value">85%</div><div>Average Attendance</div></div>
                <div class="info-card"><div class="info-value">24</div><div>Classes Held</div></div>
            </div>

            <div class="action-buttons">
                <button class="action-btn" onclick="window.location.href='../attendance/mark-attendance.php?subject=<?php echo urlencode($class_name); ?>'"><i class="fas fa-calendar-check"></i> Mark Attendance</button>
                <button class="action-btn" onclick="window.location.href='student-list.php?class=<?php echo $class_code; ?>'"><i class="fas fa-users"></i> View Students</button>
            </div>

            <div class="student-table">
                <h3 style="margin-bottom:20px;">Recent Activity</h3>
                <table>
                    <thead><tr><th>Date</th><th>Activity</th><th>Details</th></tr></thead>
                    <tbody>
                        <tr><td>Mar 20, 2024</td><td>Attendance Marked</td><td>42 out of 45 students present</td></tr>
                        <tr><td>Mar 18, 2024</td><td>Assignment Posted</td><td>Data Structures Assignment 3</td></tr>
                        <tr><td>Mar 15, 2024</td><td>Mid-Term Exam</td><td>Mid-term examinations conducted</td></tr>
                    </tbody>
                </table>
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