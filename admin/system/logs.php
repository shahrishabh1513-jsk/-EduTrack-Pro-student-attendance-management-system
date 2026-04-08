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
    <title>System Logs - EduTrack Pro</title>
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
        .filter-select, .date-input, .search-box { padding: 10px 15px; border: 2px solid #e9ecef; border-radius: 10px; font-family: 'Poppins', sans-serif; }
        .search-box { flex: 1; min-width: 250px; display: flex; align-items: center; gap: 10px; }
        .search-box input { border: none; outline: none; flex: 1; }
        .btn-clear { background: #f72585; color: white; border: none; padding: 10px 20px; border-radius: 8px; cursor: pointer; }
        .btn-export { background: #28a745; color: white; border: none; padding: 10px 20px; border-radius: 8px; cursor: pointer; }
        .logs-table { background: white; border-radius: 20px; padding: 25px; overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #e9ecef; }
        th { background: #f8f9fa; font-weight: 600; }
        .log-info { color: #4cc9f0; }
        .log-warning { color: #ffc107; }
        .log-error { color: #f72585; }
        .log-success { color: #28a745; }
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
                <a href="backup-restore.php"><i class="fas fa-database"></i><span> Backup & Restore</span></a>
                <a href="logs.php" class="active"><i class="fas fa-history"></i><span> System Logs</span></a>
                <a href="../../logout.php"><i class="fas fa-sign-out-alt"></i><span> Logout</span></a>
            </nav>
        </aside>

        <main class="main-content" id="mainContent">
            <header class="top-header">
                <button class="mobile-menu-btn" id="mobileMenuBtn"><i class="fas fa-bars"></i></button>
                <div><h2>System Logs</h2></div>
                <div class="user-profile">
                    <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($user['full_name']); ?>&background=4361ee&color=fff" alt="Profile">
                    <div><h4><?php echo htmlspecialchars($user['full_name']); ?></h4><p>Administrator</p></div>
                </div>
            </header>

            <div class="filter-bar">
                <div class="search-box"><i class="fas fa-search"></i><input type="text" id="searchInput" placeholder="Search logs..."></div>
                <select class="filter-select" id="logLevel"><option value="all">All Levels</option><option value="info">Info</option><option value="warning">Warning</option><option value="error">Error</option><option value="success">Success</option></select>
                <input type="date" class="date-input" id="fromDate" value="2024-03-01">
                <input type="date" class="date-input" id="toDate" value="2024-03-31">
                <button class="btn-clear" onclick="clearLogs()"><i class="fas fa-trash"></i> Clear Logs</button>
                <button class="btn-export" onclick="exportLogs()"><i class="fas fa-download"></i> Export Logs</button>
            </div>

            <div class="logs-table">
                <h3 style="margin-bottom:20px;"><i class="fas fa-list"></i> System Activity Log</h3>
                <div id="logsTable"></div>
            </div>
        </main>
    </div>

    <script>
        let logs = [
            { id: 1, timestamp: '2024-03-20 09:15:23', user: 'admin', action: 'User Login', details: 'Admin logged in successfully', level: 'success', ip: '127.0.0.1' },
            { id: 2, timestamp: '2024-03-20 10:30:45', user: 'admin', action: 'Student Added', details: 'New student Rishabh Sharma added', level: 'info', ip: '127.0.0.1' },
            { id: 3, timestamp: '2024-03-19 14:20:10', user: 'aakash', action: 'Attendance Marked', details: 'Attendance marked for Data Structures class', level: 'info', ip: '127.0.0.1' },
            { id: 4, timestamp: '2024-03-19 11:05:32', user: 'admin', action: 'System Setting Changed', details: 'Academic year updated to 2024-2025', level: 'warning', ip: '127.0.0.1' },
            { id: 5, timestamp: '2024-03-18 16:45:22', user: 'jenish', action: 'Login Failed', details: 'Incorrect password attempt', level: 'error', ip: '127.0.0.1' }
        ];
        
        function loadLogs() {
            const level = document.getElementById('logLevel').value;
            const search = document.getElementById('searchInput').value.toLowerCase();
            
            let filtered = [...logs];
            if(level !== 'all') filtered = filtered.filter(l => l.level === level);
            if(search) filtered = filtered.filter(l => l.user.toLowerCase().includes(search) || l.action.toLowerCase().includes(search) || l.details.toLowerCase().includes(search));
            
            const tableHTML = `<table><thead><tr><th>Timestamp</th><th>User</th><th>Action</th><th>Details</th><th>Level</th><th>IP Address</th></tr></thead><tbody>
                ${filtered.map(l => `<tr><td>${l.timestamp}</td><td><strong>${l.user}</strong></td><td>${l.action}</td><td>${l.details}</td><td class="log-${l.level}">${l.level.toUpperCase()}</td><td>${l.ip}</td></tr>`).join('')}
            </tbody></table>`;
            document.getElementById('logsTable').innerHTML = tableHTML;
        }
        
        function clearLogs() { if(confirm('Clear all system logs?')) { logs = []; loadLogs(); showNotification('Logs cleared!', 'success'); } }
        function exportLogs() { showNotification('Logs exported!', 'success'); }
        function showNotification(message, type) { const n = document.createElement('div'); n.className = 'notification'; n.style.background = type === 'success' ? '#4cc9f0' : '#f72585'; n.innerHTML = `<i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i> ${message}`; document.body.appendChild(n); setTimeout(() => n.remove(), 3000); }
        
        document.getElementById('logLevel').addEventListener('change', loadLogs);
        document.getElementById('searchInput').addEventListener('keyup', loadLogs);
        
        const sidebar = document.getElementById('sidebar'), mainContent = document.getElementById('mainContent'), toggleBtn = document.getElementById('toggleSidebar'), mobileMenuBtn = document.getElementById('mobileMenuBtn');
        if(toggleBtn) toggleBtn.addEventListener('click', function() { sidebar.classList.toggle('collapsed'); mainContent.classList.toggle('expanded'); });
        if(mobileMenuBtn) mobileMenuBtn.addEventListener('click', function() { sidebar.classList.toggle('active'); });
        function handleResponsive() { if(window.innerWidth <= 768) { sidebar.classList.add('collapsed'); mainContent.classList.add('expanded'); } else { sidebar.classList.remove('collapsed', 'active'); mainContent.classList.remove('expanded'); } }
        window.addEventListener('resize', handleResponsive); handleResponsive();
        loadLogs();
    </script>
</body>
</html>