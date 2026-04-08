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
    <title>Attendance Report - EduTrack Pro</title>
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
        .filter-bar { display: flex; gap: 15px; margin-bottom: 20px; flex-wrap: wrap; align-items: center; }
        .filter-select, .date-input { padding: 10px 15px; border: 2px solid #e9ecef; border-radius: 10px; font-family: 'Poppins', sans-serif; }
        .btn-generate { background: #4361ee; color: white; border: none; padding: 10px 25px; border-radius: 10px; cursor: pointer; }
        .btn-export { background: #28a745; color: white; border: none; padding: 10px 25px; border-radius: 10px; cursor: pointer; }
        .report-table { background: white; border-radius: 20px; padding: 25px; overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #e9ecef; }
        th { background: #f8f9fa; font-weight: 600; }
        .present { color: #4cc9f0; font-weight: 600; }
        .absent { color: #f72585; font-weight: 600; }
        .progress-bar { height: 6px; background: #e9ecef; border-radius: 10px; width: 80px; display: inline-block; margin-left: 8px; }
        .progress-fill { height: 100%; background: #4361ee; border-radius: 10px; }
        @media (max-width: 768px) { .sidebar { transform: translateX(-100%); } .sidebar.active { transform: translateX(0); } .main-content { margin-left: 0; } .filter-bar { flex-direction: column; } }
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
                <a href="attendance-overview.php"><i class="fas fa-chart-line"></i><span> Attendance Overview</span></a>
                <a href="attendance-report.php" class="active"><i class="fas fa-file-alt"></i><span> Attendance Report</span></a>
                <a href="student-attendance.php"><i class="fas fa-user-graduate"></i><span> Student Attendance</span></a>
                <a href="faculty-attendance.php"><i class="fas fa-chalkboard-teacher"></i><span> Faculty Attendance</span></a>
                <a href="../../logout.php"><i class="fas fa-sign-out-alt"></i><span> Logout</span></a>
            </nav>
        </aside>

        <main class="main-content" id="mainContent">
            <header class="top-header">
                <button class="mobile-menu-btn" id="mobileMenuBtn"><i class="fas fa-bars"></i></button>
                <div><h2>Attendance Report</h2></div>
                <div class="user-profile">
                    <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($user['full_name']); ?>&background=4361ee&color=fff" alt="Profile">
                    <div><h4><?php echo htmlspecialchars($user['full_name']); ?></h4><p>Administrator</p></div>
                </div>
            </header>

            <div class="filter-bar">
                <select class="filter-select" id="deptFilter"><option value="all">All Departments</option><option>Computer Science</option><option>Information Technology</option><option>Electronics</option></select>
                <select class="filter-select" id="semesterFilter"><option value="all">All Semesters</option><option>Semester 1</option><option>Semester 2</option><option>Semester 3</option><option>Semester 4</option><option>Semester 5</option><option>Semester 6</option></select>
                <input type="date" class="date-input" id="fromDate" value="2024-03-01">
                <input type="date" class="date-input" id="toDate" value="2024-03-31">
                <button class="btn-generate" onclick="generateReport()"><i class="fas fa-sync-alt"></i> Generate</button>
                <button class="btn-export" onclick="exportReport()"><i class="fas fa-download"></i> Export CSV</button>
            </div>

            <div class="report-table">
                <h3 style="margin-bottom:20px;"><i class="fas fa-chart-bar"></i> Student Attendance Summary</h3>
                <div id="reportTable"></div>
            </div>
        </main>
    </div>

    <script>
        const students = [
            { roll: 'IT181', name: 'Rishabh Sharma', dept: 'Computer Science', semester: 3, present: 42, total: 48, percent: 88 },
            { roll: 'IT095', name: 'Jenish Patel', dept: 'Computer Science', semester: 3, present: 44, total: 48, percent: 92 },
            { roll: 'IT124', name: 'Vasu Mehta', dept: 'Computer Science', semester: 3, present: 37, total: 48, percent: 77 },
            { roll: 'IT131', name: 'Hetvi Shah', dept: 'Computer Science', semester: 3, present: 43, total: 48, percent: 90 }
        ];
        
        function generateReport() {
            const dept = document.getElementById('deptFilter').value;
            let filtered = [...students];
            if(dept !== 'all') filtered = filtered.filter(s => s.dept === dept);
            
            const tableHTML = `<table><thead><tr><th>Roll No</th><th>Student Name</th><th>Department</th><th>Semester</th><th>Present</th><th>Total Classes</th><th>Attendance %</th></tr></thead><tbody>
                ${filtered.map(s => `<tr><td><strong>${s.roll}</strong></td><td>${s.name}</td><td>${s.dept}</td><td>${s.semester}</td><td>${s.present}</td><td>${s.total}</td><td>${s.percent}%<div class="progress-bar"><div class="progress-fill" style="width:${s.percent}%"></div></div></td></tr>`).join('')}
            </tbody></table>`;
            document.getElementById('reportTable').innerHTML = tableHTML;
        }
        
        function exportReport() { showNotification('Report exported successfully!', 'success'); }
        function showNotification(message, type) { const n = document.createElement('div'); n.style.cssText = `position:fixed;top:20px;right:20px;padding:15px 25px;background:${type === 'success' ? '#4cc9f0' : '#f72585'};color:white;border-radius:10px;z-index:9999;animation:slideIn 0.3s ease;`; n.innerHTML = `<i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i> ${message}`; document.body.appendChild(n); setTimeout(() => n.remove(), 3000); }
        
        const sidebar = document.getElementById('sidebar'), mainContent = document.getElementById('mainContent'), toggleBtn = document.getElementById('toggleSidebar'), mobileMenuBtn = document.getElementById('mobileMenuBtn');
        if(toggleBtn) toggleBtn.addEventListener('click', function() { sidebar.classList.toggle('collapsed'); mainContent.classList.toggle('expanded'); });
        if(mobileMenuBtn) mobileMenuBtn.addEventListener('click', function() { sidebar.classList.toggle('active'); });
        function handleResponsive() { if(window.innerWidth <= 768) { sidebar.classList.add('collapsed'); mainContent.classList.add('expanded'); } else { sidebar.classList.remove('collapsed', 'active'); mainContent.classList.remove('expanded'); } }
        window.addEventListener('resize', handleResponsive); handleResponsive();
        generateReport();
    </script>
</body>
</html>