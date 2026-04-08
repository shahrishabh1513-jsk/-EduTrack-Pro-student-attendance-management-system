<?php
require_once '../../includes/config.php';

if (!isLoggedIn() || !hasRole('admin')) {
    redirect('login.php');
}

$user = getUserData($conn, $_SESSION['user_id']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance Overview - EduTrack Pro</title>
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
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: white; padding: 20px; border-radius: 15px; text-align: center; }
        .stat-value { font-size: 2rem; font-weight: 700; color: #4361ee; }
        .chart-container { background: white; border-radius: 20px; padding: 25px; margin-bottom: 30px; }
        .dept-table { background: white; border-radius: 20px; padding: 25px; overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #e9ecef; }
        th { background: #f8f9fa; font-weight: 600; }
        .progress-bar { height: 8px; background: #e9ecef; border-radius: 10px; overflow: hidden; width: 150px; display: inline-block; margin-left: 10px; }
        .progress-fill { height: 100%; background: linear-gradient(90deg, #4361ee, #4cc9f0); border-radius: 10px; }
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
                <a href="attendance-overview.php" class="active"><i class="fas fa-chart-line"></i><span> Attendance Overview</span></a>
                <a href="attendance-report.php"><i class="fas fa-file-alt"></i><span> Attendance Report</span></a>
                <a href="student-attendance.php"><i class="fas fa-user-graduate"></i><span> Student Attendance</span></a>
                <a href="faculty-attendance.php"><i class="fas fa-chalkboard-teacher"></i><span> Faculty Attendance</span></a>
                <a href="../../logout.php"><i class="fas fa-sign-out-alt"></i><span> Logout</span></a>
            </nav>
        </aside>

        <main class="main-content" id="mainContent">
            <header class="top-header">
                <button class="mobile-menu-btn" id="mobileMenuBtn"><i class="fas fa-bars"></i></button>
                <div><h2>Attendance Overview</h2></div>
                <div class="user-profile">
                    <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($user['full_name']); ?>&background=4361ee&color=fff" alt="Profile">
                    <div><h4><?php echo htmlspecialchars($user['full_name']); ?></h4><p>Administrator</p></div>
                </div>
            </header>

            <div class="stats-grid">
                <div class="stat-card"><div class="stat-value" id="totalStudents">0</div><div>Total Students</div></div>
                <div class="stat-card"><div class="stat-value" id="avgAttendance">0%</div><div>Average Attendance</div></div>
                <div class="stat-card"><div class="stat-value" id="todayPresent">0</div><div>Present Today</div></div>
                <div class="stat-card"><div class="stat-value" id="todayAbsent">0</div><div>Absent Today</div></div>
            </div>

            <div class="chart-container">
                <h3><i class="fas fa-chart-line"></i> Monthly Attendance Trend</h3>
                <canvas id="attendanceChart" style="max-height: 300px; width: 100%;"></canvas>
            </div>

            <div class="dept-table">
                <h3 style="margin-bottom:20px;"><i class="fas fa-building"></i> Department-wise Attendance</h3>
                <div id="deptTable"></div>
            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const departments = [
            { name: 'Computer Science', students: 850, present: 765, percent: 90 },
            { name: 'Information Technology', students: 620, present: 558, percent: 90 },
            { name: 'Electronics', students: 450, present: 378, percent: 84 },
            { name: 'Mechanical', students: 380, present: 304, percent: 80 },
            { name: 'Civil', students: 247, present: 185, percent: 75 }
        ];
        
        const total = departments.reduce((s, d) => s + d.students, 0);
        const totalPresent = departments.reduce((s, d) => s + d.present, 0);
        document.getElementById('totalStudents').textContent = total;
        document.getElementById('avgAttendance').textContent = Math.round((totalPresent / total) * 100) + '%';
        document.getElementById('todayPresent').textContent = totalPresent;
        document.getElementById('todayAbsent').textContent = total - totalPresent;
        
        // Chart
        const ctx = document.getElementById('attendanceChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: { labels: ['Week 1', 'Week 2', 'Week 3', 'Week 4'], datasets: [{ label: 'Attendance %', data: [85, 87, 86, 89], borderColor: '#4361ee', backgroundColor: 'rgba(67,97,238,0.1)', fill: true, tension: 0.4 }] },
            options: { responsive: true, maintainAspectRatio: true, plugins: { legend: { position: 'top' } } }
        });
        
        const tableHTML = `<table><thead><tr><th>Department</th><th>Total Students</th><th>Present Today</th><th>Attendance %</th></tr></thead><tbody>
            ${departments.map(d => `<tr><td><strong>${d.name}</strong></td><td>${d.students}</td><td>${d.present}</td><td>${d.percent}%<div class="progress-bar"><div class="progress-fill" style="width:${d.percent}%"></div></div></td></tr>`).join('')}
        </tbody></table>`;
        document.getElementById('deptTable').innerHTML = tableHTML;
        
        const sidebar = document.getElementById('sidebar'), mainContent = document.getElementById('mainContent'), toggleBtn = document.getElementById('toggleSidebar'), mobileMenuBtn = document.getElementById('mobileMenuBtn');
        if(toggleBtn) toggleBtn.addEventListener('click', function() { sidebar.classList.toggle('collapsed'); mainContent.classList.toggle('expanded'); });
        if(mobileMenuBtn) mobileMenuBtn.addEventListener('click', function() { sidebar.classList.toggle('active'); });
        function handleResponsive() { if(window.innerWidth <= 768) { sidebar.classList.add('collapsed'); mainContent.classList.add('expanded'); } else { sidebar.classList.remove('collapsed', 'active'); mainContent.classList.remove('expanded'); } }
        window.addEventListener('resize', handleResponsive); handleResponsive();
    </script>
</body>
</html>