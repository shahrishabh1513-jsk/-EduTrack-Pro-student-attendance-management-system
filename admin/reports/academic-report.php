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
    <title>Academic Report - EduTrack Pro</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
        .report-table { background: white; border-radius: 20px; padding: 25px; overflow-x: auto; }
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
                <a href="attendance-report.php"><i class="fas fa-chart-line"></i><span> Attendance Report</span></a>
                <a href="academic-report.php" class="active"><i class="fas fa-graduation-cap"></i><span> Academic Report</span></a>
                <a href="financial-report.php"><i class="fas fa-rupee-sign"></i><span> Financial Report</span></a>
                <a href="analytics-dashboard.php"><i class="fas fa-chart-pie"></i><span> Analytics</span></a>
                <a href="export-data.php"><i class="fas fa-download"></i><span> Export Data</span></a>
                <a href="../../logout.php"><i class="fas fa-sign-out-alt"></i><span> Logout</span></a>
            </nav>
        </aside>

        <main class="main-content" id="mainContent">
            <header class="top-header">
                <button class="mobile-menu-btn" id="mobileMenuBtn"><i class="fas fa-bars"></i></button>
                <div><h2>Academic Report</h2></div>
                <div class="user-profile">
                    <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($user['full_name']); ?>&background=4361ee&color=fff" alt="Profile">
                    <div><h4><?php echo htmlspecialchars($user['full_name']); ?></h4><p>Administrator</p></div>
                </div>
            </header>

            <div class="stats-grid">
                <div class="stat-card"><div class="stat-value" id="avgCgpa">8.5</div><div>Average CGPA</div></div>
                <div class="stat-card"><div class="stat-value" id="passPercent">92%</div><div>Pass Percentage</div></div>
                <div class="stat-card"><div class="stat-value" id="distinctionCount">1250</div><div>Distinction (75%+)</div></div>
                <div class="stat-card"><div class="stat-value" id="firstClassCount">980</div><div>First Class (60-74%)</div></div>
            </div>

            <div class="chart-container">
                <canvas id="performanceChart" style="max-height: 300px; width: 100%;"></canvas>
            </div>

            <div class="report-table">
                <h3 style="margin-bottom:20px;">Department-wise Academic Performance</h3>
                <table>
                    <thead><tr><th>Department</th><th>Students</th><th>Avg CGPA</th><th>Pass %</th><th>Distinction</th><th>First Class</th></tr></thead>
                    <tbody>
                        <tr><td>Computer Science</td><td>850</td><td>8.5</td><td>92%</td><td>320</td><td>380</td></tr>
                        <tr><td>Information Technology</td><td>620</td><td>8.3</td><td>90%</td><td>220</td><td>280</td></tr>
                        <tr><td>Electronics</td><td>450</td><td>7.8</td><td>85%</td><td>120</td><td>200</td></tr>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <script>
        // Performance Chart
        const perfCtx = document.getElementById('performanceChart').getContext('2d');
        new Chart(perfCtx, {
            type: 'bar',
            data: {
                labels: ['Sem 1', 'Sem 2', 'Sem 3', 'Sem 4', 'Sem 5', 'Sem 6'],
                datasets: [{
                    label: 'Average CGPA',
                    data: [7.8, 8.0, 8.5, 8.3, 8.2, 8.4],
                    backgroundColor: '#4361ee',
                    borderRadius: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                scales: { y: { beginAtZero: true, max: 10, title: { display: true, text: 'CGPA' } } },
                plugins: { legend: { position: 'top' } }
            }
        });

        const sidebar = document.getElementById('sidebar'), mainContent = document.getElementById('mainContent'), toggleBtn = document.getElementById('toggleSidebar'), mobileMenuBtn = document.getElementById('mobileMenuBtn');
        if(toggleBtn) toggleBtn.addEventListener('click', function() { sidebar.classList.toggle('collapsed'); mainContent.classList.toggle('expanded'); });
        if(mobileMenuBtn) mobileMenuBtn.addEventListener('click', function() { sidebar.classList.toggle('active'); });
        function handleResponsive() { if(window.innerWidth <= 768) { sidebar.classList.add('collapsed'); mainContent.classList.add('expanded'); } else { sidebar.classList.remove('collapsed', 'active'); mainContent.classList.remove('expanded'); } }
        window.addEventListener('resize', handleResponsive); handleResponsive();
    </script>
</body>
</html>