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
    <title>View Attendance - EduTrack Pro</title>
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
        .filter-bar { display: flex; gap: 15px; margin-bottom: 20px; flex-wrap: wrap; }
        .filter-select { padding: 10px 15px; border: 2px solid #e9ecef; border-radius: 10px; font-family: 'Poppins', sans-serif; }
        .attendance-table { background: white; border-radius: 20px; padding: 25px; overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #e9ecef; }
        th { background: #f8f9fa; font-weight: 600; }
        .present { color: #4cc9f0; font-weight: 600; }
        .absent { color: #f72585; font-weight: 600; }
        .late { color: #ffc107; font-weight: 600; }
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
                <a href="mark-attendance.php"><i class="fas fa-calendar-check"></i><span> Mark Attendance</span></a>
                <a href="view-attendance.php" class="active"><i class="fas fa-eye"></i><span> View Attendance</span></a>
                <a href="attendance-report.php"><i class="fas fa-chart-bar"></i><span> Attendance Report</span></a>
                <a href="../logout.php"><i class="fas fa-sign-out-alt"></i><span> Logout</span></a>
            </nav>
        </aside>

        <main class="main-content" id="mainContent">
            <header class="top-header">
                <button class="mobile-menu-btn" id="mobileMenuBtn"><i class="fas fa-bars"></i></button>
                <div><h2>View Attendance</h2></div>
                <div class="user-profile">
                    <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($user['full_name']); ?>&background=4361ee&color=fff" alt="Profile">
                    <div><h4><?php echo htmlspecialchars($user['full_name']); ?></h4><p><?php echo htmlspecialchars($user['id_number']); ?></p></div>
                </div>
            </header>

            <div class="filter-bar">
                <select class="filter-select" id="subjectFilter">
                    <option value="all">All Subjects</option>
                    <option value="Data Structures">Data Structures</option>
                    <option value="Database Management">Database Management</option>
                    <option value="Web Development">Web Development</option>
                </select>
                <select class="filter-select" id="studentFilter">
                    <option value="all">All Students</option>
                </select>
            </div>

            <div class="attendance-table">
                <h3 style="margin-bottom: 20px;">Attendance Records</h3>
                <div id="attendanceRecords"></div>
            </div>
        </main>
    </div>

    <script>
        const attendanceData = [
            { date: '2024-03-20', student: 'Rishabh Sharma', roll: 'IT181', subject: 'Data Structures', status: 'present' },
            { date: '2024-03-20', student: 'Jenish Patel', roll: 'IT095', subject: 'Data Structures', status: 'present' },
            { date: '2024-03-20', student: 'Vasu Mehta', roll: 'IT124', subject: 'Data Structures', status: 'absent' },
            { date: '2024-03-20', student: 'Hetvi Shah', roll: 'IT131', subject: 'Data Structures', status: 'present' }
        ];
        
        function loadAttendance() {
            const subjectFilter = document.getElementById('subjectFilter').value;
            let filtered = [...attendanceData];
            if(subjectFilter !== 'all') filtered = filtered.filter(a => a.subject === subjectFilter);
            
            const tableHTML = `<table><thead><tr><th>Date</th><th>Roll Number</th><th>Student Name</th><th>Subject</th><th>Status</th></tr></thead><tbody>
                ${filtered.map(record => `<tr><td>${new Date(record.date).toLocaleDateString()}</td><td>${record.roll}</td><td>${record.student}</td><td>${record.subject}</td><td class="${record.status}">${record.status.toUpperCase()}</td>`).join('')}
            </tbody></table>`;
            document.getElementById('attendanceRecords').innerHTML = tableHTML;
        }
        
        document.getElementById('subjectFilter').addEventListener('change', loadAttendance);
        
        const sidebar = document.getElementById('sidebar'), mainContent = document.getElementById('mainContent'), toggleBtn = document.getElementById('toggleSidebar'), mobileMenuBtn = document.getElementById('mobileMenuBtn');
        if(toggleBtn) toggleBtn.addEventListener('click', function() { sidebar.classList.toggle('collapsed'); mainContent.classList.toggle('expanded'); });
        if(mobileMenuBtn) mobileMenuBtn.addEventListener('click', function() { sidebar.classList.toggle('active'); });
        function handleResponsive() { if(window.innerWidth <= 768) { sidebar.classList.add('collapsed'); mainContent.classList.add('expanded'); } else { sidebar.classList.remove('collapsed', 'active'); mainContent.classList.remove('expanded'); } }
        window.addEventListener('resize', handleResponsive); handleResponsive();
        loadAttendance();
    </script>
</body>
</html>