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
        .filter-bar { display: flex; gap: 15px; margin-bottom: 25px; flex-wrap: wrap; align-items: center; }
        .filter-select { padding: 10px 15px; border: 2px solid #e9ecef; border-radius: 10px; font-family: 'Poppins', sans-serif; }
        .btn-generate { background: #4361ee; color: white; border: none; padding: 10px 25px; border-radius: 10px; cursor: pointer; }
        .btn-export { background: #28a745; color: white; border: none; padding: 10px 25px; border-radius: 10px; cursor: pointer; }
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: white; padding: 20px; border-radius: 15px; text-align: center; }
        .stat-value { font-size: 2rem; font-weight: 700; color: #4361ee; }
        .chart-container { background: white; border-radius: 20px; padding: 25px; margin-bottom: 30px; display: grid; grid-template-columns: 1fr 1fr; gap: 25px; }
        .report-table { background: white; border-radius: 20px; padding: 25px; overflow-x: auto; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #e9ecef; }
        th { background: #f8f9fa; font-weight: 600; }
        .grade-aplus { color: #4cc9f0; font-weight: 600; }
        .grade-a { color: #4361ee; font-weight: 600; }
        .progress-bar { height: 6px; background: #e9ecef; border-radius: 10px; width: 100px; display: inline-block; margin-left: 10px; }
        .progress-fill { height: 100%; background: linear-gradient(90deg, #4361ee, #4cc9f0); border-radius: 10px; }
        @media (max-width: 768px) { .sidebar { transform: translateX(-100%); } .sidebar.active { transform: translateX(0); } .main-content { margin-left: 0; } .chart-container { grid-template-columns: 1fr; } }
        .mobile-menu-btn { display: none; background: none; border: none; font-size: 1.5rem; cursor: pointer; }
        @media (max-width: 768px) { .mobile-menu-btn { display: block; } }
        .notification { position: fixed; top: 20px; right: 20px; padding: 15px 25px; border-radius: 10px; z-index: 9999; animation: slideIn 0.3s ease; color: white; }
        @keyframes slideIn { from { transform: translateX(100%); opacity: 0; } to { transform: translateX(0); opacity: 1; } }
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

            <div class="filter-bar">
                <select class="filter-select" id="semesterFilter"><option value="all">All Semesters</option><option>1</option><option>2</option><option>3</option><option>4</option><option>5</option><option>6</option></select>
                <select class="filter-select" id="deptFilter"><option value="all">All Departments</option><option>Computer Science</option><option>Information Technology</option><option>Electronics</option></select>
                <button class="btn-generate" onclick="generateReport()"><i class="fas fa-sync-alt"></i> Generate</button>
                <button class="btn-export" onclick="exportReport()"><i class="fas fa-download"></i> Export PDF</button>
            </div>

            <div class="stats-grid">
                <div class="stat-card"><div class="stat-value" id="avgCgpa">0</div><div>Average CGPA</div></div>
                <div class="stat-card"><div class="stat-value" id="passPercent">0%</div><div>Pass Percentage</div></div>
                <div class="stat-card"><div class="stat-value" id="distinctionCount">0</div><div>Distinction (75%+)</div></div>
                <div class="stat-card"><div class="stat-value" id="firstClassCount">0</div><div>First Class (60-74%)</div></div>
            </div>

            <div class="chart-container">
                <canvas id="gradeChart"></canvas>
                <canvas id="deptChart"></canvas>
            </div>

            <div class="report-table"><h3 style="margin-bottom:20px;">Department-wise Academic Performance</h3><div id="deptTable"></div></div>
            <div class="report-table"><h3 style="margin-bottom:20px;">Top Performing Students</h3><div id="topStudentsTable"></div></div>
        </main>
    </div>

    <script>
        const departments = [
            { name: 'Computer Science', students: 850, avgCgpa: 8.5, passPercent: 92, distinction: 320, firstClass: 380 },
            { name: 'Information Technology', students: 620, avgCgpa: 8.3, passPercent: 90, distinction: 220, firstClass: 280 },
            { name: 'Electronics', students: 450, avgCgpa: 7.8, passPercent: 85, distinction: 120, firstClass: 200 }
        ];
        
        const topStudents = [
            { rank: 1, name: 'Hetvi Shah', roll: 'IT131', cgpa: 9.2, dept: 'Computer Science' },
            { rank: 2, name: 'Jenish Patel', roll: 'IT095', cgpa: 9.0, dept: 'Computer Science' },
            { rank: 3, name: 'Rishabh Sharma', roll: 'IT181', cgpa: 8.8, dept: 'Computer Science' },
            { rank: 4, name: 'Aarav Desai', roll: 'CSIT001', cgpa: 8.6, dept: 'Information Technology' },
            { rank: 5, name: 'Vasu Mehta', roll: 'IT124', cgpa: 8.4, dept: 'Computer Science' }
        ];
        
        let gradeChart = null, deptChart = null;
        
        function generateReport() {
            const totalStudents = departments.reduce((s, d) => s + d.students, 0);
            const avgCgpa = (departments.reduce((s, d) => s + (d.avgCgpa * d.students), 0) / totalStudents).toFixed(1);
            const passPercent = Math.round(departments.reduce((s, d) => s + (d.passPercent * d.students), 0) / totalStudents);
            const distinction = departments.reduce((s, d) => s + d.distinction, 0);
            const firstClass = departments.reduce((s, d) => s + d.firstClass, 0);
            
            document.getElementById('avgCgpa').textContent = avgCgpa;
            document.getElementById('passPercent').textContent = passPercent + '%';
            document.getElementById('distinctionCount').textContent = distinction;
            document.getElementById('firstClassCount').textContent = firstClass;
            
            if(gradeChart) gradeChart.destroy();
            const gradeCtx = document.getElementById('gradeChart').getContext('2d');
            gradeChart = new Chart(gradeCtx, {
                type: 'doughnut',
                data: { labels: ['Distinction (75%+)', 'First Class (60-74%)', 'Second Class (50-59%)', 'Pass (40-49%)'], datasets: [{ data: [distinction, firstClass, 150, 80], backgroundColor: ['#4cc9f0', '#4361ee', '#3f37c9', '#4895ef'], borderWidth: 0 }] },
                options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
            });
            
            if(deptChart) deptChart.destroy();
            const deptCtx = document.getElementById('deptChart').getContext('2d');
            deptChart = new Chart(deptCtx, {
                type: 'bar',
                data: { labels: departments.map(d => d.name), datasets: [{ label: 'Average CGPA', data: departments.map(d => d.avgCgpa), backgroundColor: '#4361ee', borderRadius: 8 }] },
                options: { responsive: true, scales: { y: { beginAtZero: true, max: 10 } } }
            });
            
            const deptHTML = `<table><thead><tr><th>Department</th><th>Students</th><th>Avg CGPA</th><th>Pass %</th><th>Distinction</th><th>First Class</th></tr></thead><tbody>${departments.map(d => `<tr><td><strong>${d.name}</strong></td><td>${d.students}</td><td>${d.avgCgpa}</td><td>${d.passPercent}%<div class="progress-bar"><div class="progress-fill" style="width:${d.passPercent}%"></div></div></td><td>${d.distinction}</td><td>${d.firstClass}</td>`).join('')}</tbody></table>`;
            document.getElementById('deptTable').innerHTML = deptHTML;
            
            const topHTML = `<table><thead><td><th>Rank</th><th>Student Name</th><th>Roll No</th><th>Department</th><th>CGPA</th></tr></thead><tbody>${topStudents.map(s => `<tr><td><span style="background:#4361ee; color:white; padding:4px 10px; border-radius:20px;">#${s.rank}</span></td><td><strong>${s.name}</strong></td><td>${s.roll}</td><td>${s.dept}</td><td class="grade-aplus">${s.cgpa}</td>`).join('')}</tbody></table>`;
            document.getElementById('topStudentsTable').innerHTML = topHTML;
        }
        
        function exportReport() { showNotification('Report exported!', 'success'); }
        function showNotification(message, type) { const n = document.createElement('div'); n.className = 'notification'; n.style.background = type === 'success' ? '#4cc9f0' : '#f72585'; n.innerHTML = `<i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i> ${message}`; document.body.appendChild(n); setTimeout(() => n.remove(), 3000); }
        
        const sidebar = document.getElementById('sidebar'), mainContent = document.getElementById('mainContent'), toggleBtn = document.getElementById('toggleSidebar'), mobileMenuBtn = document.getElementById('mobileMenuBtn');
        if(toggleBtn) toggleBtn.addEventListener('click', function() { sidebar.classList.toggle('collapsed'); mainContent.classList.toggle('expanded'); });
        if(mobileMenuBtn) mobileMenuBtn.addEventListener('click', function() { sidebar.classList.toggle('active'); });
        function handleResponsive() { if(window.innerWidth <= 768) { sidebar.classList.add('collapsed'); mainContent.classList.add('expanded'); } else { sidebar.classList.remove('collapsed', 'active'); mainContent.classList.remove('expanded'); } }
        window.addEventListener('resize', handleResponsive); handleResponsive();
        generateReport();
    </script>
</body>
</html>