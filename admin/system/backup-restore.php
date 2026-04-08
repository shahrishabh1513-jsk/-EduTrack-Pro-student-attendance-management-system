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
    <title>Backup & Restore - EduTrack Pro</title>
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
        .backup-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: 25px; }
        .backup-card { background: white; border-radius: 20px; padding: 30px; text-align: center; transition: all 0.3s; }
        .backup-card:hover { transform: translateY(-5px); box-shadow: 0 10px 25px rgba(0,0,0,0.1); }
        .backup-icon { width: 80px; height: 80px; background: rgba(67,97,238,0.1); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; font-size: 2.5rem; color: #4361ee; }
        .backup-title { font-size: 1.3rem; font-weight: 600; margin-bottom: 10px; }
        .backup-desc { color: #6c757d; margin-bottom: 20px; }
        .btn-backup { background: #4361ee; color: white; border: none; padding: 12px 25px; border-radius: 10px; cursor: pointer; width: 100%; display: flex; align-items: center; justify-content: center; gap: 8px; }
        .btn-restore { background: #4cc9f0; color: white; border: none; padding: 12px 25px; border-radius: 10px; cursor: pointer; width: 100%; display: flex; align-items: center; justify-content: center; gap: 8px; margin-top: 10px; }
        .backup-list { background: white; border-radius: 20px; padding: 25px; margin-top: 30px; }
        .backup-item { display: flex; justify-content: space-between; align-items: center; padding: 15px; border-bottom: 1px solid #e9ecef; }
        .backup-item:last-child { border-bottom: none; }
        .backup-info .name { font-weight: 600; }
        .backup-info .details { font-size: 0.8rem; color: #6c757d; }
        .backup-actions button { background: none; border: none; cursor: pointer; margin: 0 5px; font-size: 1rem; }
        .download-btn { color: #4cc9f0; }
        .restore-btn { color: #4361ee; }
        .delete-btn { color: #f72585; }
        .upload-area { border: 2px dashed #e9ecef; border-radius: 15px; padding: 30px; text-align: center; margin-top: 20px; cursor: pointer; }
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
                <a href="user-roles.php"><i class="fas fa-users-cog"></i><span> User Roles</span></a>
                <a href="permissions.php"><i class="fas fa-lock"></i><span> Permissions</span></a>
                <a href="system-settings.php"><i class="fas fa-cog"></i><span> System Settings</span></a>
                <a href="academic-settings.php"><i class="fas fa-school"></i><span> Academic Settings</span></a>
                <a href="backup-restore.php" class="active"><i class="fas fa-database"></i><span> Backup & Restore</span></a>
                <a href="logs.php"><i class="fas fa-history"></i><span> System Logs</span></a>
                <a href="../../logout.php"><i class="fas fa-sign-out-alt"></i><span> Logout</span></a>
            </nav>
        </aside>

        <main class="main-content" id="mainContent">
            <header class="top-header">
                <button class="mobile-menu-btn" id="mobileMenuBtn"><i class="fas fa-bars"></i></button>
                <div><h2>Backup & Restore</h2></div>
                <div class="user-profile">
                    <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($user['full_name']); ?>&background=4361ee&color=fff" alt="Profile">
                    <div><h4><?php echo htmlspecialchars($user['full_name']); ?></h4><p>Administrator</p></div>
                </div>
            </header>

            <div class="backup-grid">
                <div class="backup-card"><div class="backup-icon"><i class="fas fa-database"></i></div><div class="backup-title">Full Database Backup</div><div class="backup-desc">Backup all data including students, faculty, courses, attendance, and results</div><button class="btn-backup" onclick="createBackup('full')"><i class="fas fa-download"></i> Create Full Backup</button></div>
                <div class="backup-card"><div class="backup-icon"><i class="fas fa-user-graduate"></i></div><div class="backup-title">Student Data Only</div><div class="backup-desc">Backup only student records, enrollment, and academic information</div><button class="btn-backup" onclick="createBackup('students')"><i class="fas fa-download"></i> Backup Students</button></div>
                <div class="backup-card"><div class="backup-icon"><i class="fas fa-chart-line"></i></div><div class="backup-title">Reports & Analytics</div><div class="backup-desc">Backup all generated reports, analytics data, and logs</div><button class="btn-backup" onclick="createBackup('reports')"><i class="fas fa-download"></i> Backup Reports</button></div>
            </div>

            <div class="backup-list"><h3 style="margin-bottom:20px;"><i class="fas fa-history"></i> Available Backups</h3><div id="backupList"></div>
                <div class="upload-area" onclick="document.getElementById('restoreFile').click()"><i class="fas fa-cloud-upload-alt" style="font-size:2rem; color:#4361ee;"></i><p>Click to upload backup file for restoration</p><input type="file" id="restoreFile" style="display:none;" accept=".sql,.zip"></div>
            </div>
        </main>
    </div>

    <script>
        let backups = [
            { name: 'full_backup_2024_03_20.sql', size: '45.2 MB', date: '2024-03-20 10:30 AM', type: 'Full Database' },
            { name: 'students_backup_2024_03_19.sql', size: '12.5 MB', date: '2024-03-19 02:15 PM', type: 'Student Data' },
            { name: 'reports_backup_2024_03_18.zip', size: '8.3 MB', date: '2024-03-18 11:45 AM', type: 'Reports' }
        ];
        
        function loadBackups() {
            const html = backups.map(b => `
                <div class="backup-item">
                    <div class="backup-info"><div class="name">${b.name}</div><div class="details">${b.type} • ${b.size} • ${b.date}</div></div>
                    <div class="backup-actions"><button class="download-btn" onclick="downloadBackup('${b.name}')"><i class="fas fa-download"></i></button><button class="restore-btn" onclick="restoreBackup('${b.name}')"><i class="fas fa-upload"></i></button><button class="delete-btn" onclick="deleteBackup('${b.name}')"><i class="fas fa-trash"></i></button></div>
                </div>
            `).join('');
            document.getElementById('backupList').innerHTML = html || '<p>No backups available</p>';
        }
        
        function createBackup(type) { showNotification(`Creating ${type} backup...`, 'success'); setTimeout(() => showNotification('Backup created successfully!', 'success'), 2000); }
        function downloadBackup(name) { showNotification(`Downloading ${name}...`, 'success'); }
        function restoreBackup(name) { if(confirm(`Restore from ${name}? This will overwrite current data.`)) showNotification('Restore initiated!', 'success'); }
        function deleteBackup(name) { if(confirm(`Delete ${name}?`)) { backups = backups.filter(b => b.name !== name); loadBackups(); showNotification('Backup deleted!', 'success'); } }
        document.getElementById('restoreFile').addEventListener('change', function(e) { if(e.target.files[0]) showNotification(`Restoring ${e.target.files[0].name}...`, 'success'); });
        function showNotification(message, type) { const n = document.createElement('div'); n.className = 'notification'; n.style.background = type === 'success' ? '#4cc9f0' : '#f72585'; n.innerHTML = `<i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i> ${message}`; document.body.appendChild(n); setTimeout(() => n.remove(), 3000); }
        
        const sidebar = document.getElementById('sidebar'), mainContent = document.getElementById('mainContent'), toggleBtn = document.getElementById('toggleSidebar'), mobileMenuBtn = document.getElementById('mobileMenuBtn');
        if(toggleBtn) toggleBtn.addEventListener('click', function() { sidebar.classList.toggle('collapsed'); mainContent.classList.toggle('expanded'); });
        if(mobileMenuBtn) mobileMenuBtn.addEventListener('click', function() { sidebar.classList.toggle('active'); });
        function handleResponsive() { if(window.innerWidth <= 768) { sidebar.classList.add('collapsed'); mainContent.classList.add('expanded'); } else { sidebar.classList.remove('collapsed', 'active'); mainContent.classList.remove('expanded'); } }
        window.addEventListener('resize', handleResponsive); handleResponsive();
        loadBackups();
    </script>
</body>
</html>