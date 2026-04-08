<?php
require_once '../../includes/config.php';

if (!isLoggedIn() || !hasRole('faculty')) {
    redirect('login.php');
}

$user = getUserData($conn, $_SESSION['user_id']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Classes - EduTrack Pro</title>
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
        .classes-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: 25px; }
        .class-card { background: white; border-radius: 20px; overflow: hidden; transition: all 0.3s; cursor: pointer; }
        .class-card:hover { transform: translateY(-5px); box-shadow: 0 10px 25px rgba(0,0,0,0.1); }
        .class-header { background: linear-gradient(135deg, #4361ee, #3f37c9); padding: 20px; color: white; }
        .class-body { padding: 20px; }
        .class-stats { display: flex; gap: 20px; margin-top: 15px; }
        .class-stat { text-align: center; flex: 1; }
        .class-stat-value { font-size: 1.5rem; font-weight: 700; color: #4361ee; }
        .btn-view { background: #4361ee; color: white; padding: 10px 20px; border: none; border-radius: 8px; cursor: pointer; width: 100%; margin-top: 15px; }
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
                <a href="timetable.php"><i class="fas fa-clock"></i><span> Timetable</span></a>
                <a href="../logout.php"><i class="fas fa-sign-out-alt"></i><span> Logout</span></a>
            </nav>
        </aside>

        <main class="main-content" id="mainContent">
            <header class="top-header">
                <button class="mobile-menu-btn" id="mobileMenuBtn"><i class="fas fa-bars"></i></button>
                <div><h2>My Classes</h2></div>
                <div class="user-profile">
                    <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($user['full_name']); ?>&background=4361ee&color=fff" alt="Profile">
                    <div><h4><?php echo htmlspecialchars($user['full_name']); ?></h4><p><?php echo htmlspecialchars($user['id_number']); ?></p></div>
                </div>
            </header>

            <div class="classes-grid">
                <div class="class-card" onclick="window.location.href='class-detail.php?class=CS301'">
                    <div class="class-header"><h3>Data Structures</h3><p>CS301</p></div>
                    <div class="class-body">
                        <p><strong>Schedule:</strong> Monday, Wednesday 9:00 AM</p>
                        <p><strong>Room:</strong> Room 101</p>
                        <div class="class-stats">
                            <div class="class-stat"><div class="class-stat-value">45</div><small>Students</small></div>
                            <div class="class-stat"><div class="class-stat-value">85%</div><small>Attendance</small></div>
                        </div>
                        <button class="btn-view">View Class</button>
                    </div>
                </div>

                <div class="class-card" onclick="window.location.href='class-detail.php?class=CS302'">
                    <div class="class-header"><h3>Database Management</h3><p>CS302</p></div>
                    <div class="class-body">
                        <p><strong>Schedule:</strong> Tuesday, Thursday 11:00 AM</p>
                        <p><strong>Room:</strong> Room 203</p>
                        <div class="class-stats">
                            <div class="class-stat"><div class="class-stat-value">38</div><small>Students</small></div>
                            <div class="class-stat"><div class="class-stat-value">92%</div><small>Attendance</small></div>
                        </div>
                        <button class="btn-view">View Class</button>
                    </div>
                </div>

                <div class="class-card" onclick="window.location.href='class-detail.php?class=CS303'">
                    <div class="class-header"><h3>Web Development</h3><p>CS303</p></div>
                    <div class="class-body">
                        <p><strong>Schedule:</strong> Monday, Wednesday 2:00 PM</p>
                        <p><strong>Room:</strong> Lab 3</p>
                        <div class="class-stats">
                            <div class="class-stat"><div class="class-stat-value">45</div><small>Students</small></div>
                            <div class="class-stat"><div class="class-stat-value">78%</div><small>Attendance</small></div>
                        </div>
                        <button class="btn-view">View Class</button>
                    </div>
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