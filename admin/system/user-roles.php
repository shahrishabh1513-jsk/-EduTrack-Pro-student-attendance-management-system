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
    <title>User Roles - EduTrack Pro</title>
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
        .header-actions { display: flex; gap: 15px; margin-bottom: 20px; flex-wrap: wrap; }
        .btn-add { background: #4361ee; color: white; border: none; padding: 12px 25px; border-radius: 10px; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; }
        .roles-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); gap: 25px; }
        .role-card { background: white; border-radius: 20px; overflow: hidden; transition: all 0.3s; }
        .role-card:hover { transform: translateY(-5px); box-shadow: 0 10px 25px rgba(0,0,0,0.1); }
        .role-header { background: linear-gradient(135deg, #4361ee, #3f37c9); padding: 20px; color: white; display: flex; justify-content: space-between; align-items: center; }
        .role-name { font-size: 1.2rem; font-weight: 600; }
        .role-badge { padding: 4px 12px; border-radius: 20px; font-size: 0.7rem; background: rgba(255,255,255,0.2); }
        .role-body { padding: 20px; }
        .user-count { display: flex; justify-content: space-between; margin-bottom: 15px; padding-bottom: 15px; border-bottom: 1px solid #e9ecef; }
        .permission-list { list-style: none; margin: 15px 0; }
        .permission-list li { padding: 8px 0; display: flex; align-items: center; gap: 10px; font-size: 0.9rem; }
        .permission-list i.fa-check { color: #4cc9f0; }
        .permission-list i.fa-times { color: #f72585; }
        .role-actions { display: flex; gap: 10px; margin-top: 15px; padding-top: 15px; border-top: 1px solid #e9ecef; }
        .edit-role, .delete-role { background: none; border: none; padding: 8px 15px; border-radius: 8px; cursor: pointer; }
        .edit-role { background: #4cc9f0; color: white; }
        .delete-role { background: #f72585; color: white; }
        .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); justify-content: center; align-items: center; z-index: 1000; }
        .modal-content { background: white; border-radius: 20px; padding: 30px; max-width: 500px; width: 90%; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: 500; }
        .form-control { width: 100%; padding: 10px; border: 2px solid #e9ecef; border-radius: 8px; font-family: 'Poppins', sans-serif; }
        .permission-group { margin: 15px 0; }
        .permission-checkbox { display: flex; align-items: center; gap: 10px; margin: 8px 0; }
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
                <a href="user-roles.php" class="active"><i class="fas fa-users-cog"></i><span> User Roles</span></a>
                <a href="permissions.php"><i class="fas fa-lock"></i><span> Permissions</span></a>
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
                <div><h2>User Roles Management</h2></div>
                <div class="user-profile">
                    <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($user['full_name']); ?>&background=4361ee&color=fff" alt="Profile">
                    <div><h4><?php echo htmlspecialchars($user['full_name']); ?></h4><p>Administrator</p></div>
                </div>
            </header>

            <div class="header-actions">
                <button class="btn-add" onclick="openAddRoleModal()"><i class="fas fa-plus"></i> Add New Role</button>
            </div>

            <div class="roles-grid" id="rolesGrid"></div>
        </main>
    </div>

    <!-- Add/Edit Role Modal -->
    <div id="roleModal" class="modal">
        <div class="modal-content">
            <h3 id="modalTitle"><i class="fas fa-plus"></i> Add New Role</h3>
            <form id="roleForm">
                <input type="hidden" id="roleId">
                <div class="form-group"><label>Role Name</label><input type="text" class="form-control" id="roleName" required></div>
                <div class="form-group"><label>Description</label><textarea class="form-control" id="roleDesc" rows="2"></textarea></div>
                <div class="permission-group"><label>Permissions</label>
                    <div class="permission-checkbox"><input type="checkbox" id="perm_dashboard"> <label>Dashboard Access</label></div>
                    <div class="permission-checkbox"><input type="checkbox" id="perm_students"> <label>Manage Students</label></div>
                    <div class="permission-checkbox"><input type="checkbox" id="perm_faculty"> <label>Manage Faculty</label></div>
                    <div class="permission-checkbox"><input type="checkbox" id="perm_courses"> <label>Manage Courses</label></div>
                    <div class="permission-checkbox"><input type="checkbox" id="perm_attendance"> <label>Manage Attendance</label></div>
                    <div class="permission-checkbox"><input type="checkbox" id="perm_exams"> <label>Manage Exams</label></div>
                    <div class="permission-checkbox"><input type="checkbox" id="perm_reports"> <label>View Reports</label></div>
                    <div class="permission-checkbox"><input type="checkbox" id="perm_settings"> <label>System Settings</label></div>
                </div>
                <button type="submit" class="btn-add" style="width:100%; margin-top:10px;">Save Role</button>
                <button type="button" class="btn-add" onclick="closeModal()" style="width:100%; margin-top:10px; background:#6c757d;">Cancel</button>
            </form>
        </div>
    </div>

    <script>
        let roles = [
            { id: 1, name: 'Administrator', description: 'Full system access with all permissions', users: 1, permissions: ['dashboard', 'students', 'faculty', 'courses', 'attendance', 'exams', 'reports', 'settings'] },
            { id: 2, name: 'Faculty', description: 'Can manage classes, attendance, and results', users: 7, permissions: ['dashboard', 'students', 'attendance', 'exams', 'reports'] },
            { id: 3, name: 'Student', description: 'View-only access to personal information', users: 1250, permissions: ['dashboard', 'reports'] }
        ];
        
        function loadRoles() {
            const grid = document.getElementById('rolesGrid');
            grid.innerHTML = roles.map(role => `
                <div class="role-card">
                    <div class="role-header"><div class="role-name">${role.name}</div><div class="role-badge">${role.users} Users</div></div>
                    <div class="role-body">
                        <div class="user-count"><span>Description</span><span>${role.description}</span></div>
                        <strong>Permissions:</strong>
                        <ul class="permission-list">
                            ${role.permissions.includes('dashboard') ? '<li><i class="fas fa-check-circle"></i> Dashboard Access</li>' : ''}
                            ${role.permissions.includes('students') ? '<li><i class="fas fa-check-circle"></i> Manage Students</li>' : ''}
                            ${role.permissions.includes('faculty') ? '<li><i class="fas fa-check-circle"></i> Manage Faculty</li>' : ''}
                            ${role.permissions.includes('courses') ? '<li><i class="fas fa-check-circle"></i> Manage Courses</li>' : ''}
                            ${role.permissions.includes('attendance') ? '<li><i class="fas fa-check-circle"></i> Manage Attendance</li>' : ''}
                            ${role.permissions.includes('exams') ? '<li><i class="fas fa-check-circle"></i> Manage Exams</li>' : ''}
                            ${role.permissions.includes('reports') ? '<li><i class="fas fa-check-circle"></i> View Reports</li>' : ''}
                            ${role.permissions.includes('settings') ? '<li><i class="fas fa-check-circle"></i> System Settings</li>' : ''}
                        </ul>
                        <div class="role-actions"><button class="edit-role" onclick="editRole(${role.id})"><i class="fas fa-edit"></i> Edit</button><button class="delete-role" onclick="deleteRole(${role.id})"><i class="fas fa-trash"></i> Delete</button></div>
                    </div>
                </div>
            `).join('');
        }
        
        function openAddRoleModal() { document.getElementById('modalTitle').innerHTML = '<i class="fas fa-plus"></i> Add New Role'; document.getElementById('roleForm').reset(); document.getElementById('roleId').value = ''; document.getElementById('roleModal').style.display = 'flex'; }
        
        function editRole(id) { const role = roles.find(r => r.id === id); if(role) { document.getElementById('modalTitle').innerHTML = '<i class="fas fa-edit"></i> Edit Role'; document.getElementById('roleId').value = role.id; document.getElementById('roleName').value = role.name; document.getElementById('roleDesc').value = role.description; document.getElementById('perm_dashboard').checked = role.permissions.includes('dashboard'); document.getElementById('perm_students').checked = role.permissions.includes('students'); document.getElementById('perm_faculty').checked = role.permissions.includes('faculty'); document.getElementById('perm_courses').checked = role.permissions.includes('courses'); document.getElementById('perm_attendance').checked = role.permissions.includes('attendance'); document.getElementById('perm_exams').checked = role.permissions.includes('exams'); document.getElementById('perm_reports').checked = role.permissions.includes('reports'); document.getElementById('perm_settings').checked = role.permissions.includes('settings'); document.getElementById('roleModal').style.display = 'flex'; } }
        
        function deleteRole(id) { if(confirm('Delete this role?')) { roles = roles.filter(r => r.id !== id); loadRoles(); showNotification('Role deleted!', 'success'); } }
        
        document.getElementById('roleForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const id = document.getElementById('roleId').value;
            const permissions = [];
            if(document.getElementById('perm_dashboard').checked) permissions.push('dashboard');
            if(document.getElementById('perm_students').checked) permissions.push('students');
            if(document.getElementById('perm_faculty').checked) permissions.push('faculty');
            if(document.getElementById('perm_courses').checked) permissions.push('courses');
            if(document.getElementById('perm_attendance').checked) permissions.push('attendance');
            if(document.getElementById('perm_exams').checked) permissions.push('exams');
            if(document.getElementById('perm_reports').checked) permissions.push('reports');
            if(document.getElementById('perm_settings').checked) permissions.push('settings');
            
            const roleData = { id: id || roles.length + 1, name: document.getElementById('roleName').value, description: document.getElementById('roleDesc').value, users: 0, permissions: permissions };
            if(id) { const index = roles.findIndex(r => r.id == id); if(index !== -1) roles[index] = roleData; } else { roles.push(roleData); }
            closeModal(); loadRoles(); showNotification('Role saved!', 'success');
        });
        
        function closeModal() { document.getElementById('roleModal').style.display = 'none'; }
        function showNotification(message, type) { const n = document.createElement('div'); n.className = 'notification'; n.style.background = type === 'success' ? '#4cc9f0' : '#f72585'; n.innerHTML = `<i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i> ${message}`; document.body.appendChild(n); setTimeout(() => n.remove(), 3000); }
        
        const sidebar = document.getElementById('sidebar'), mainContent = document.getElementById('mainContent'), toggleBtn = document.getElementById('toggleSidebar'), mobileMenuBtn = document.getElementById('mobileMenuBtn');
        if(toggleBtn) toggleBtn.addEventListener('click', function() { sidebar.classList.toggle('collapsed'); mainContent.classList.toggle('expanded'); });
        if(mobileMenuBtn) mobileMenuBtn.addEventListener('click', function() { sidebar.classList.toggle('active'); });
        function handleResponsive() { if(window.innerWidth <= 768) { sidebar.classList.add('collapsed'); mainContent.classList.add('expanded'); } else { sidebar.classList.remove('collapsed', 'active'); mainContent.classList.remove('expanded'); } }
        window.addEventListener('resize', handleResponsive); handleResponsive();
        loadRoles();
        window.onclick = function(event) { if(event.target == document.getElementById('roleModal')) closeModal(); }
    </script>
</body>
</html>