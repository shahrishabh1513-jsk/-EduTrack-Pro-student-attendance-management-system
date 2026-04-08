<?php
require_once '../../../includes/config.php';

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
    <title>Departments - EduTrack Pro</title>
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
        .search-box { display: flex; align-items: center; background: white; border: 2px solid #e9ecef; border-radius: 10px; padding: 0 15px; flex: 1; }
        .search-box input { padding: 12px; border: none; outline: none; width: 100%; font-family: 'Poppins', sans-serif; }
        .departments-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); gap: 25px; }
        .dept-card { background: white; border-radius: 20px; overflow: hidden; transition: all 0.3s; cursor: pointer; }
        .dept-card:hover { transform: translateY(-5px); box-shadow: 0 10px 25px rgba(0,0,0,0.1); }
        .dept-header { background: linear-gradient(135deg, #4361ee, #3f37c9); padding: 20px; color: white; }
        .dept-name { font-size: 1.3rem; font-weight: 600; }
        .dept-code { font-size: 0.8rem; opacity: 0.8; margin-top: 5px; }
        .dept-body { padding: 20px; }
        .dept-stats { display: flex; gap: 20px; margin-bottom: 15px; }
        .stat { text-align: center; flex: 1; }
        .stat-value { font-size: 1.5rem; font-weight: 700; color: #4361ee; }
        .stat-label { font-size: 0.75rem; color: #6c757d; }
        .dept-actions { display: flex; gap: 10px; margin-top: 15px; padding-top: 15px; border-top: 1px solid #e9ecef; }
        .edit-btn, .delete-btn { background: none; border: none; padding: 8px 15px; border-radius: 8px; cursor: pointer; }
        .edit-btn { background: #4cc9f0; color: white; }
        .delete-btn { background: #f72585; color: white; }
        @media (max-width: 768px) { .sidebar { transform: translateX(-100%); } .sidebar.active { transform: translateX(0); } .main-content { margin-left: 0; } .departments-grid { grid-template-columns: 1fr; } }
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
                <a href="../../dashboard.php"><i class="fas fa-home"></i><span> Dashboard</span></a>
                <a href="../../profile.php"><i class="fas fa-user"></i><span> Profile</span></a>
                <a href="../student-management/students-list.php"><i class="fas fa-user-graduate"></i><span> Student Management</span></a>
                <a href="../faculty-management/faculty-list.php"><i class="fas fa-chalkboard-teacher"></i><span> Faculty Management</span></a>
                <a href="../course-management/courses-list.php"><i class="fas fa-book"></i><span> Course Management</span></a>
                <a href="departments.php" class="active"><i class="fas fa-building"></i><span> Departments</span></a>
                <a href="../../../logout.php"><i class="fas fa-sign-out-alt"></i><span> Logout</span></a>
            </nav>
        </aside>

        <main class="main-content" id="mainContent">
            <header class="top-header">
                <button class="mobile-menu-btn" id="mobileMenuBtn"><i class="fas fa-bars"></i></button>
                <div><h2>Department Management</h2></div>
                <div class="user-profile">
                    <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($user['full_name']); ?>&background=4361ee&color=fff" alt="Profile">
                    <div><h4><?php echo htmlspecialchars($user['full_name']); ?></h4><p>Administrator</p></div>
                </div>
            </header>

            <div class="header-actions">
                <button class="btn-add" onclick="window.location.href='add-department.php'"><i class="fas fa-plus"></i> Add Department</button>
                <div class="search-box"><i class="fas fa-search"></i><input type="text" id="searchInput" placeholder="Search departments..."></div>
            </div>

            <div class="departments-grid" id="departmentsGrid"></div>
        </main>
    </div>

    <script>
        let departments = [
            { id: 1, name: 'Computer Science', code: 'CS', hod: 'Dr. Aakash Gupta', students: 850, faculty: 25, courses: 12, established: '2010', email: 'cs@edutrack.com', phone: '+91 1234567890' },
            { id: 2, name: 'Information Technology', code: 'IT', hod: 'Prof. Neha Shah', students: 620, faculty: 18, courses: 10, established: '2012', email: 'it@edutrack.com', phone: '+91 1234567891' },
            { id: 3, name: 'Electronics & Communication', code: 'EC', hod: 'Prof. Niraj Mehta', students: 450, faculty: 14, courses: 10, established: '2011', email: 'ec@edutrack.com', phone: '+91 1234567892' },
            { id: 4, name: 'Mechanical Engineering', code: 'ME', hod: 'Dr. Rajesh Kumar', students: 380, faculty: 12, courses: 10, established: '2010', email: 'me@edutrack.com', phone: '+91 1234567893' },
            { id: 5, name: 'Civil Engineering', code: 'CE', hod: 'Prof. Sunil Verma', students: 247, faculty: 10, courses: 10, established: '2013', email: 'ce@edutrack.com', phone: '+91 1234567894' }
        ];
        
        function loadDepartments() {
            const searchTerm = document.getElementById('searchInput').value.toLowerCase();
            let filtered = departments;
            if(searchTerm) filtered = departments.filter(d => d.name.toLowerCase().includes(searchTerm) || d.code.toLowerCase().includes(searchTerm));
            
            const grid = document.getElementById('departmentsGrid');
            if(filtered.length === 0) {
                grid.innerHTML = '<div style="text-align:center;padding:40px;grid-column:1/-1;"><i class="fas fa-building" style="font-size:3rem;color:#6c757d;"></i><p>No departments found</p></div>';
                return;
            }
            
            grid.innerHTML = filtered.map(dept => `
                <div class="dept-card">
                    <div class="dept-header">
                        <div class="dept-name">${dept.name}</div>
                        <div class="dept-code">${dept.code}</div>
                    </div>
                    <div class="dept-body">
                        <div class="dept-stats">
                            <div class="stat"><div class="stat-value">${dept.students}</div><div class="stat-label">Students</div></div>
                            <div class="stat"><div class="stat-value">${dept.faculty}</div><div class="stat-label">Faculty</div></div>
                            <div class="stat"><div class="stat-value">${dept.courses}</div><div class="stat-label">Courses</div></div>
                        </div>
                        <p><strong>HOD:</strong> ${dept.hod}</p>
                        <p><strong>Established:</strong> ${dept.established}</p>
                        <div class="dept-actions">
                            <button class="edit-btn" onclick="editDepartment(${dept.id})"><i class="fas fa-edit"></i> Edit</button>
                            <button class="delete-btn" onclick="deleteDepartment(${dept.id})"><i class="fas fa-trash"></i> Delete</button>
                            <button class="edit-btn" onclick="viewDepartment(${dept.id})" style="background:#4361ee;"><i class="fas fa-eye"></i> View</button>
                        </div>
                    </div>
                </div>
            `).join('');
        }
        
        function viewDepartment(id) {
            const dept = departments.find(d => d.id === id);
            if(dept) {
                alert(`Department Details:\n\nName: ${dept.name}\nCode: ${dept.code}\nHOD: ${dept.hod}\nStudents: ${dept.students}\nFaculty: ${dept.faculty}\nCourses: ${dept.courses}\nEstablished: ${dept.established}\nEmail: ${dept.email}\nPhone: ${dept.phone}`);
            }
        }
        
        function editDepartment(id) { window.location.href = `edit-department.php?id=${id}`; }
        function deleteDepartment(id) { if(confirm('Delete this department?')) { departments = departments.filter(d => d.id !== id); loadDepartments(); showNotification('Department deleted!', 'success'); } }
        function showNotification(message, type) { const n = document.createElement('div'); n.className = 'notification'; n.style.background = type === 'success' ? '#4cc9f0' : '#f72585'; n.innerHTML = `<i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i> ${message}`; document.body.appendChild(n); setTimeout(() => n.remove(), 3000); }
        
        document.getElementById('searchInput').addEventListener('keyup', loadDepartments);
        
        const sidebar = document.getElementById('sidebar'), mainContent = document.getElementById('mainContent'), toggleBtn = document.getElementById('toggleSidebar'), mobileMenuBtn = document.getElementById('mobileMenuBtn');
        if(toggleBtn) toggleBtn.addEventListener('click', function() { sidebar.classList.toggle('collapsed'); mainContent.classList.toggle('expanded'); });
        if(mobileMenuBtn) mobileMenuBtn.addEventListener('click', function() { sidebar.classList.toggle('active'); });
        function handleResponsive() { if(window.innerWidth <= 768) { sidebar.classList.add('collapsed'); mainContent.classList.add('expanded'); } else { sidebar.classList.remove('collapsed', 'active'); mainContent.classList.remove('expanded'); } }
        window.addEventListener('resize', handleResponsive); handleResponsive();
        loadDepartments();
    </script>
</body>
</html>