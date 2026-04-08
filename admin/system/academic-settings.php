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
    <title>Academic Settings - EduTrack Pro</title>
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
        .settings-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 25px; }
        .settings-card { background: white; border-radius: 20px; padding: 25px; }
        .card-title { font-size: 1.1rem; font-weight: 600; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; border-bottom: 2px solid #e9ecef; padding-bottom: 15px; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 500; }
        .form-control { width: 100%; padding: 10px 12px; border: 2px solid #e9ecef; border-radius: 8px; font-family: 'Poppins', sans-serif; }
        .grade-scale { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #e9ecef; }
        .btn-save { background: #4361ee; color: white; border: none; padding: 12px 25px; border-radius: 10px; cursor: pointer; margin-top: 20px; width: 100%; }
        @media (max-width: 768px) { .sidebar { transform: translateX(-100%); } .sidebar.active { transform: translateX(0); } .main-content { margin-left: 0; } .settings-grid { grid-template-columns: 1fr; } }
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
                <a href="user-roles.php"><i class="fas fa-users-cog"></i><span> User Roles</span></a>
                <a href="permissions.php"><i class="fas fa-lock"></i><span> Permissions</span></a>
                <a href="system-settings.php"><i class="fas fa-cog"></i><span> System Settings</span></a>
                <a href="academic-settings.php" class="active"><i class="fas fa-school"></i><span> Academic Settings</span></a>
                <a href="backup-restore.php"><i class="fas fa-database"></i><span> Backup & Restore</span></a>
                <a href="logs.php"><i class="fas fa-history"></i><span> System Logs</span></a>
                <a href="../../logout.php"><i class="fas fa-sign-out-alt"></i><span> Logout</span></a>
            </nav>
        </aside>

        <main class="main-content" id="mainContent">
            <header class="top-header">
                <button class="mobile-menu-btn" id="mobileMenuBtn"><i class="fas fa-bars"></i></button>
                <div><h2>Academic Settings</h2></div>
                <div class="user-profile">
                    <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($user['full_name']); ?>&background=4361ee&color=fff" alt="Profile">
                    <div><h4><?php echo htmlspecialchars($user['full_name']); ?></h4><p>Administrator</p></div>
                </div>
            </header>

            <div class="settings-grid">
                <div class="settings-card"><div class="card-title"><i class="fas fa-calendar-alt"></i> Academic Calendar</div>
                    <div class="form-group"><label>Current Academic Year</label><select class="form-control" id="academicYear"><option>2024-2025</option><option>2023-2024</option><option>2022-2023</option></select></div>
                    <div class="form-group"><label>Current Semester</label><select class="form-control" id="currentSemester"><option>Semester 3 (Odd)</option><option>Semester 4 (Even)</option><option>Semester 5 (Odd)</option></select></div>
                    <div class="form-group"><label>Semester Start Date</label><input type="date" class="form-control" id="semesterStart" value="2024-07-01"></div>
                    <div class="form-group"><label>Semester End Date</label><input type="date" class="form-control" id="semesterEnd" value="2024-12-15"></div>
                    <div class="form-group"><label>Examination Start Date</label><input type="date" class="form-control" id="examStart" value="2024-12-01"></div>
                    <div class="form-group"><label>Examination End Date</label><input type="date" class="form-control" id="examEnd" value="2024-12-20"></div>
                </div>

                <div class="settings-card"><div class="card-title"><i class="fas fa-chart-line"></i> Grading System</div>
                    <div class="grade-scale"><span>A+ (90-100)</span><span>Outstanding</span><span>10 Points</span></div>
                    <div class="grade-scale"><span>A (80-89)</span><span>Excellent</span><span>9 Points</span></div>
                    <div class="grade-scale"><span>B+ (70-79)</span><span>Very Good</span><span>8 Points</span></div>
                    <div class="grade-scale"><span>B (60-69)</span><span>Good</span><span>7 Points</span></div>
                    <div class="grade-scale"><span>C+ (55-59)</span><span>Satisfactory</span><span>6 Points</span></div>
                    <div class="grade-scale"><span>C (50-54)</span><span>Average</span><span>5 Points</span></div>
                    <div class="grade-scale"><span>D (40-49)</span><span>Pass</span><span>4 Points</span></div>
                    <div class="grade-scale"><span>F (Below 40)</span><span>Fail</span><span>0 Points</span></div>
                </div>

                <div class="settings-card"><div class="card-title"><i class="fas fa-percent"></i> Attendance Settings</div>
                    <div class="form-group"><label>Minimum Attendance Required (%)</label><input type="number" class="form-control" id="minAttendance" value="75"></div>
                    <div class="toggle-switch" style="display:flex; justify-content:space-between; margin-bottom:15px;"><span>Enable Auto-Notification for Low Attendance</span><input type="checkbox" id="autoNotify" checked style="width:50px;"></div>
                    <div class="form-group"><label>Classes per Week (per subject)</label><input type="number" class="form-control" id="classesPerWeek" value="3"></div>
                    <div class="form-group"><label>Attendance Marking Deadline (days)</label><input type="number" class="form-control" id="attendanceDeadline" value="7"></div>
                </div>

                <div class="settings-card"><div class="card-title"><i class="fas fa-file-alt"></i> Examination Settings</div>
                    <div class="form-group"><label>Exam Form Fee (per subject)</label><input type="text" class="form-control" id="examFee" value="₹500"></div>
                    <div class="form-group"><label>Practical Exam Fee</label><input type="text" class="form-control" id="practicalFee" value="₹1000"></div>
                    <div class="form-group"><label>Result Declaration Date</label><input type="date" class="form-control" id="resultDate" value="2024-12-25"></div>
                    <div class="toggle-switch" style="display:flex; justify-content:space-between;"><span>Allow Exam Form Re-submission</span><input type="checkbox" id="allowResubmit" checked style="width:50px;"></div>
                </div>
            </div>

            <button class="btn-save" onclick="saveSettings()"><i class="fas fa-save"></i> Save Academic Settings</button>
        </main>
    </div>

    <script>
        function saveSettings() { showNotification('Academic settings saved successfully!', 'success'); }
        function showNotification(message, type) { const n = document.createElement('div'); n.className = 'notification'; n.style.background = type === 'success' ? '#4cc9f0' : '#f72585'; n.innerHTML = `<i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i> ${message}`; document.body.appendChild(n); setTimeout(() => n.remove(), 3000); }
        
        const sidebar = document.getElementById('sidebar'), mainContent = document.getElementById('mainContent'), toggleBtn = document.getElementById('toggleSidebar'), mobileMenuBtn = document.getElementById('mobileMenuBtn');
        if(toggleBtn) toggleBtn.addEventListener('click', function() { sidebar.classList.toggle('collapsed'); mainContent.classList.toggle('expanded'); });
        if(mobileMenuBtn) mobileMenuBtn.addEventListener('click', function() { sidebar.classList.toggle('active'); });
        function handleResponsive() { if(window.innerWidth <= 768) { sidebar.classList.add('collapsed'); mainContent.classList.add('expanded'); } else { sidebar.classList.remove('collapsed', 'active'); mainContent.classList.remove('expanded'); } }
        window.addEventListener('resize', handleResponsive); handleResponsive();
    </script>
</body>
</html>