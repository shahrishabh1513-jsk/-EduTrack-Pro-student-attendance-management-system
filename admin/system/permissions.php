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
    <title>Permissions - EduTrack Pro</title>
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
        .btn-save { background: #4361ee; color: white; border: none; padding: 12px 25px; border-radius: 10px; cursor: pointer; margin-bottom: 20px; }
        .permissions-table { background: white; border-radius: 20px; padding: 25px; overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #e9ecef; }
        th { background: #f8f9fa; font-weight: 600; }
        .permission-checkbox { width: 40px; text-align: center; }
        .role-badge { display: inline-block; padding: 4px 10px; border-radius: 20px; font-size: 0.7rem; font-weight: 600; }
        .role-admin { background: rgba(67,97,238,0.1); color: #4361ee; }
        .role-faculty { background: rgba(76,201,240,0.1); color: #4cc9f0; }
        .role-student { background: rgba(247,37,133,0.1); color: #f72585; }
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
                <a href="permissions.php" class="active"><i class="fas fa-lock"></i><span> Permissions</span></a>
                <a href="system-settings.php"><i class="fas fa-cog"></i><span> System Settings</span></a>
                <a href="academic-settings.php"><i class="fas fa-school"></i><span> Academic Settings</span></a>
                <a href="backup-restore.php"><i class="fas fa-database"></i><span> Backup & Restore</span></a>
                <a href="logs.php"><i class="fas fa-history"></i><span> System Logs</span></a>
                <a href="../../logout.php"><i class="fas fa-sign-out-alt"></i><span> Logout</span></a>
            </nav>
        </aside>

        <main class="main-content" id="mainContent">
            <header class="top-header">
                <button class="mobile-menu-btn" id="mobileMenuBtn"><i class="fas fa-bars"></i></button>
                <div><h2>Role Permissions</h2></div>
                <div class="user-profile">
                    <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($user['full_name']); ?>&background=4361ee&color=fff" alt="Profile">
                    <div><h4><?php echo htmlspecialchars($user['full_name']); ?></h4><p>Administrator</p></div>
                </div>
            </header>

            <button class="btn-save" onclick="savePermissions()"><i class="fas fa-save"></i> Save Changes</button>

            <div class="permissions-table">
                <h3 style="margin-bottom:20px;"><i class="fas fa-lock"></i> Permission Matrix</h3>
                <div id="permissionsTable"></div>
            </div>
        </main>
    </div>

    <script>
        const permissions = [
            { name: 'Dashboard Access', key: 'dashboard', admin: true, faculty: true, student: true },
            { name: 'View Students', key: 'view_students', admin: true, faculty: true, student: false },
            { name: 'Manage Students (Add/Edit/Delete)', key: 'manage_students', admin: true, faculty: false, student: false },
            { name: 'View Faculty', key: 'view_faculty', admin: true, faculty: true, student: false },
            { name: 'Manage Faculty', key: 'manage_faculty', admin: true, faculty: false, student: false },
            { name: 'View Courses', key: 'view_courses', admin: true, faculty: true, student: true },
            { name: 'Manage Courses', key: 'manage_courses', admin: true, faculty: false, student: false },
            { name: 'Mark Attendance', key: 'mark_attendance', admin: true, faculty: true, student: false },
            { name: 'View Attendance', key: 'view_attendance', admin: true, faculty: true, student: true },
            { name: 'Manage Exams', key: 'manage_exams', admin: true, faculty: true, student: false },
            { name: 'View Results', key: 'view_results', admin: true, faculty: true, student: true },
            { name: 'Manage Results', key: 'manage_results', admin: true, faculty: true, student: false },
            { name: 'View Reports', key: 'view_reports', admin: true, faculty: true, student: false },
            { name: 'System Settings', key: 'system_settings', admin: true, faculty: false, student: false }
        ];
        
        function loadPermissions() {
            const tableHTML = `
                <table>
                    <thead><tr><th>Permission</th><th><span class="role-badge role-admin">Administrator</span></th><th><span class="role-badge role-faculty">Faculty</span></th><th><span class="role-badge role-student">Student</span></th></tr></thead>
                    <tbody>
                        ${permissions.map(p => `
                            <tr>
                                <td><strong>${p.name}</strong></td>
                                <td class="permission-checkbox"><input type="checkbox" class="perm-admin" data-perm="${p.key}" ${p.admin ? 'checked' : ''} ${p.key === 'dashboard' ? 'disabled' : ''}></td>
                                <td class="permission-checkbox"><input type="checkbox" class="perm-faculty" data-perm="${p.key}" ${p.faculty ? 'checked' : ''}></td>
                                <td class="permission-checkbox"><input type="checkbox" class="perm-student" data-perm="${p.key}" ${p.student ? 'checked' : ''}></td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            `;
            document.getElementById('permissionsTable').innerHTML = tableHTML;
        }
        
        function savePermissions() {
            showNotification('Permissions saved successfully!', 'success');
        }
        
        function showNotification(message, type) { const n = document.createElement('div'); n.className = 'notification'; n.style.background = type === 'success' ? '#4cc9f0' : '#f72585'; n.innerHTML = `<i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i> ${message}`; document.body.appendChild(n); setTimeout(() => n.remove(), 3000); }
        
        const sidebar = document.getElementById('sidebar'), mainContent = document.getElementById('mainContent'), toggleBtn = document.getElementById('toggleSidebar'), mobileMenuBtn = document.getElementById('mobileMenuBtn');
        if(toggleBtn) toggleBtn.addEventListener('click', function() { sidebar.classList.toggle('collapsed'); mainContent.classList.toggle('expanded'); });
        if(mobileMenuBtn) mobileMenuBtn.addEventListener('click', function() { sidebar.classList.toggle('active'); });
        function handleResponsive() { if(window.innerWidth <= 768) { sidebar.classList.add('collapsed'); mainContent.classList.add('expanded'); } else { sidebar.classList.remove('collapsed', 'active'); mainContent.classList.remove('expanded'); } }
        window.addEventListener('resize', handleResponsive); handleResponsive();
        loadPermissions();
    </script>
</body>
</html>