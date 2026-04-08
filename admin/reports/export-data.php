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
    <title>Export Data - EduTrack Pro</title>
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
        .export-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 25px; }
        .export-card { background: white; border-radius: 20px; padding: 25px; transition: all 0.3s; }
        .export-card:hover { transform: translateY(-5px); box-shadow: 0 10px 25px rgba(0,0,0,0.1); }
        .export-icon { width: 60px; height: 60px; background: rgba(67,97,238,0.1); border-radius: 15px; display: flex; align-items: center; justify-content: center; font-size: 1.8rem; color: #4361ee; margin-bottom: 15px; }
        .export-title { font-size: 1.2rem; font-weight: 600; margin-bottom: 10px; }
        .export-desc { color: #6c757d; font-size: 0.85rem; margin-bottom: 20px; }
        .export-formats { display: flex; gap: 10px; margin-bottom: 20px; }
        .format-btn { background: #e9ecef; border: none; padding: 8px 15px; border-radius: 8px; cursor: pointer; transition: all 0.3s; }
        .format-btn:hover { background: #4361ee; color: white; }
        .btn-export { background: #4361ee; color: white; border: none; padding: 10px 20px; border-radius: 8px; cursor: pointer; width: 100%; display: flex; align-items: center; justify-content: center; gap: 8px; }
        .filter-bar { background: white; border-radius: 15px; padding: 20px; margin-bottom: 25px; display: flex; gap: 15px; flex-wrap: wrap; align-items: flex-end; }
        .filter-group { display: flex; flex-direction: column; gap: 5px; }
        .filter-group label { font-size: 0.8rem; font-weight: 500; }
        .filter-select { padding: 8px 12px; border: 2px solid #e9ecef; border-radius: 8px; font-family: 'Poppins', sans-serif; }
        @media (max-width: 768px) { .sidebar { transform: translateX(-100%); } .sidebar.active { transform: translateX(0); } .main-content { margin-left: 0; } }
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
                <a href="academic-report.php"><i class="fas fa-graduation-cap"></i><span> Academic Report</span></a>
                <a href="financial-report.php"><i class="fas fa-rupee-sign"></i><span> Financial Report</span></a>
                <a href="analytics-dashboard.php"><i class="fas fa-chart-pie"></i><span> Analytics</span></a>
                <a href="export-data.php" class="active"><i class="fas fa-download"></i><span> Export Data</span></a>
                <a href="../../logout.php"><i class="fas fa-sign-out-alt"></i><span> Logout</span></a>
            </nav>
        </aside>

        <main class="main-content" id="mainContent">
            <header class="top-header">
                <button class="mobile-menu-btn" id="mobileMenuBtn"><i class="fas fa-bars"></i></button>
                <div><h2>Export Data</h2></div>
                <div class="user-profile">
                    <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($user['full_name']); ?>&background=4361ee&color=fff" alt="Profile">
                    <div><h4><?php echo htmlspecialchars($user['full_name']); ?></h4><p>Administrator</p></div>
                </div>
            </header>

            <div class="filter-bar">
                <div class="filter-group"><label>Academic Year</label><select class="filter-select" id="academicYear"><option>2023-2024</option><option>2022-2023</option><option>2021-2022</option></select></div>
                <div class="filter-group"><label>Semester</label><select class="filter-select" id="semester"><option>All</option><option>1</option><option>2</option><option>3</option><option>4</option><option>5</option><option>6</option></select></div>
                <div class="filter-group"><label>Department</label><select class="filter-select" id="department"><option>All</option><option>Computer Science</option><option>Information Technology</option><option>Electronics</option></select></div>
                <div class="filter-group"><label>Date Range</label><div style="display:flex; gap:10px;"><input type="date" class="filter-select" id="fromDate"><input type="date" class="filter-select" id="toDate"></div></div>
            </div>

            <div class="export-grid">
                <div class="export-card"><div class="export-icon"><i class="fas fa-user-graduate"></i></div><div class="export-title">Student Data</div><div class="export-desc">Export complete student database including personal and academic information</div><div class="export-formats"><button class="format-btn">CSV</button><button class="format-btn">Excel</button><button class="format-btn">PDF</button></div><button class="btn-export" onclick="exportData('students')"><i class="fas fa-download"></i> Export Students</button></div>
                <div class="export-card"><div class="export-icon"><i class="fas fa-chalkboard-teacher"></i></div><div class="export-title">Faculty Data</div><div class="export-desc">Export faculty records including qualifications and assignments</div><div class="export-formats"><button class="format-btn">CSV</button><button class="format-btn">Excel</button><button class="format-btn">PDF</button></div><button class="btn-export" onclick="exportData('faculty')"><i class="fas fa-download"></i> Export Faculty</button></div>
                <div class="export-card"><div class="export-icon"><i class="fas fa-book"></i></div><div class="export-title">Course Data</div><div class="export-desc">Export course catalog, syllabus, and curriculum information</div><div class="export-formats"><button class="format-btn">CSV</button><button class="format-btn">Excel</button><button class="format-btn">PDF</button></div><button class="btn-export" onclick="exportData('courses')"><i class="fas fa-download"></i> Export Courses</button></div>
                <div class="export-card"><div class="export-icon"><i class="fas fa-calendar-check"></i></div><div class="export-title">Attendance Records</div><div class="export-desc">Export attendance data with date-wise and student-wise details</div><div class="export-formats"><button class="format-btn">CSV</button><button class="format-btn">Excel</button><button class="format-btn">PDF</button></div><button class="btn-export" onclick="exportData('attendance')"><i class="fas fa-download"></i> Export Attendance</button></div>
                <div class="export-card"><div class="export-icon"><i class="fas fa-chart-line"></i></div><div class="export-title">Results & Grades</div><div class="export-desc">Export examination results, grades, and academic performance</div><div class="export-formats"><button class="format-btn">CSV</button><button class="format-btn">Excel</button><button class="format-btn">PDF</button></div><button class="btn-export" onclick="exportData('results')"><i class="fas fa-download"></i> Export Results</button></div>
                <div class="export-card"><div class="export-icon"><i class="fas fa-file-alt"></i></div><div class="export-title">Complete Report</div><div class="export-desc">Generate comprehensive report with all data combined</div><div class="export-formats"><button class="format-btn">PDF</button><button class="format-btn">Excel</button></div><button class="btn-export" onclick="exportData('complete')"><i class="fas fa-file-pdf"></i> Generate Full Report</button></div>
            </div>
        </main>
    </div>

    <script>
        function exportData(type) {
            showNotification(`Exporting ${type} data...`, 'success');
            setTimeout(() => showNotification(`${type} data exported successfully!`, 'success'), 1500);
        }
        function showNotification(message, type) { const n = document.createElement('div'); n.className = 'notification'; n.style.background = type === 'success' ? '#4cc9f0' : '#f72585'; n.innerHTML = `<i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i> ${message}`; document.body.appendChild(n); setTimeout(() => n.remove(), 3000); }
        
        const sidebar = document.getElementById('sidebar'), mainContent = document.getElementById('mainContent'), toggleBtn = document.getElementById('toggleSidebar'), mobileMenuBtn = document.getElementById('mobileMenuBtn');
        if(toggleBtn) toggleBtn.addEventListener('click', function() { sidebar.classList.toggle('collapsed'); mainContent.classList.toggle('expanded'); });
        if(mobileMenuBtn) mobileMenuBtn.addEventListener('click', function() { sidebar.classList.toggle('active'); });
        function handleResponsive() { if(window.innerWidth <= 768) { sidebar.classList.add('collapsed'); mainContent.classList.add('expanded'); } else { sidebar.classList.remove('collapsed', 'active'); mainContent.classList.remove('expanded'); } }
        window.addEventListener('resize', handleResponsive); handleResponsive();
    </script>
</body>
</html>