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
    <title>Semester Management - EduTrack Pro</title>
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
        .semester-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: 25px; }
        .semester-card { background: white; border-radius: 20px; overflow: hidden; transition: all 0.3s; }
        .semester-card:hover { transform: translateY(-5px); box-shadow: 0 10px 25px rgba(0,0,0,0.1); }
        .semester-header { background: linear-gradient(135deg, #4361ee, #3f37c9); padding: 20px; color: white; display: flex; justify-content: space-between; align-items: center; }
        .semester-number { font-size: 1.5rem; font-weight: 700; }
        .semester-status { padding: 4px 12px; border-radius: 20px; font-size: 0.75rem; background: rgba(255,255,255,0.2); }
        .semester-body { padding: 20px; }
        .course-item { display: flex; justify-content: space-between; align-items: center; padding: 10px 0; border-bottom: 1px solid #e9ecef; }
        .course-code { font-weight: 600; color: #4361ee; }
        .course-name { font-size: 0.9rem; }
        .btn-manage { background: #e9ecef; border: none; padding: 8px 15px; border-radius: 8px; cursor: pointer; margin-top: 15px; width: 100%; }
        @media (max-width: 768px) { .sidebar { transform: translateX(-100%); } .sidebar.active { transform: translateX(0); } .main-content { margin-left: 0; } }
        .mobile-menu-btn { display: none; background: none; border: none; font-size: 1.5rem; cursor: pointer; }
        @media (max-width: 768px) { .mobile-menu-btn { display: block; } }
        .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); justify-content: center; align-items: center; z-index: 1000; }
        .modal-content { background: white; border-radius: 20px; padding: 30px; max-width: 500px; width: 90%; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: 500; }
        .form-control { width: 100%; padding: 10px; border: 2px solid #e9ecef; border-radius: 8px; }
        .btn-save { background: #4361ee; color: white; border: none; padding: 10px 20px; border-radius: 8px; cursor: pointer; width: 100%; }
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
                <a href="courses-list.php"><i class="fas fa-book"></i><span> Courses</span></a>
                <a href="subject-management.php"><i class="fas fa-tags"></i><span> Subjects</span></a>
                <a href="semester-management.php" class="active"><i class="fas fa-layer-group"></i><span> Semesters</span></a>
                <a href="../../../logout.php"><i class="fas fa-sign-out-alt"></i><span> Logout</span></a>
            </nav>
        </aside>

        <main class="main-content" id="mainContent">
            <header class="top-header">
                <button class="mobile-menu-btn" id="mobileMenuBtn"><i class="fas fa-bars"></i></button>
                <div><h2>Semester Management</h2></div>
                <div class="user-profile">
                    <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($user['full_name']); ?>&background=4361ee&color=fff" alt="Profile">
                    <div><h4><?php echo htmlspecialchars($user['full_name']); ?></h4><p>Administrator</p></div>
                </div>
            </header>

            <div class="semester-grid" id="semesterGrid"></div>
        </main>
    </div>

    <!-- Edit Semester Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <h3><i class="fas fa-edit"></i> Edit Semester</h3>
            <form id="editForm">
                <input type="hidden" id="editSemesterId">
                <div class="form-group"><label>Semester Name</label><input type="text" class="form-control" id="editSemesterName" required></div>
                <div class="form-group"><label>Academic Year</label><input type="text" class="form-control" id="editAcademicYear" required></div>
                <div class="form-group"><label>Start Date</label><input type="date" class="form-control" id="editStartDate" required></div>
                <div class="form-group"><label>End Date</label><input type="date" class="form-control" id="editEndDate" required></div>
                <div class="form-group"><label>Status</label><select class="form-control" id="editStatus"><option value="active">Active</option><option value="completed">Completed</option><option value="upcoming">Upcoming</option></select></div>
                <button type="submit" class="btn-save">Save Changes</button>
                <button type="button" class="btn-save" onclick="closeModal()" style="background:#6c757d; margin-top:10px;">Cancel</button>
            </form>
        </div>
    </div>

    <script>
        const semesters = [
            { id: 1, name: 'Semester 1', year: '2023-2024', startDate: '2023-07-01', endDate: '2023-12-15', status: 'completed', courses: 6 },
            { id: 2, name: 'Semester 2', year: '2023-2024', startDate: '2024-01-01', endDate: '2024-05-30', status: 'completed', courses: 6 },
            { id: 3, name: 'Semester 3', year: '2024-2025', startDate: '2024-07-01', endDate: '2024-12-15', status: 'active', courses: 5 },
            { id: 4, name: 'Semester 4', year: '2024-2025', startDate: '2025-01-01', endDate: '2025-05-30', status: 'upcoming', courses: 0 },
            { id: 5, name: 'Semester 5', year: '2025-2026', startDate: '2025-07-01', endDate: '2025-12-15', status: 'upcoming', courses: 0 },
            { id: 6, name: 'Semester 6', year: '2025-2026', startDate: '2026-01-01', endDate: '2026-05-30', status: 'upcoming', courses: 0 }
        ];
        
        const coursesBySemester = {
            1: [{ code: 'CS101', name: 'Programming Fundamentals' }, { code: 'CS102', name: 'Mathematics I' }],
            2: [{ code: 'CS201', name: 'Object Oriented Programming' }, { code: 'CS202', name: 'Discrete Mathematics' }],
            3: [{ code: 'CS301', name: 'Data Structures' }, { code: 'CS302', name: 'Database Management' }, { code: 'CS303', name: 'Web Development' }, { code: 'CS304', name: 'Operating Systems' }, { code: 'CS305', name: 'Computer Networks' }]
        };
        
        function loadSemesters() {
            const grid = document.getElementById('semesterGrid');
            grid.innerHTML = semesters.map(sem => `
                <div class="semester-card">
                    <div class="semester-header">
                        <div><div class="semester-number">${sem.name}</div><div>${sem.year}</div></div>
                        <div class="semester-status ${sem.status === 'active' ? 'status-active' : sem.status === 'completed' ? 'status-completed' : 'status-upcoming'}">${sem.status.toUpperCase()}</div>
                    </div>
                    <div class="semester-body">
                        <p><strong>Duration:</strong> ${new Date(sem.startDate).toLocaleDateString()} - ${new Date(sem.endDate).toLocaleDateString()}</p>
                        <p><strong>Courses:</strong> ${sem.courses} subjects</p>
                        <div style="margin-top:15px;">
                            <strong>Subjects:</strong>
                            ${(coursesBySemester[sem.id] || []).map(c => `<div class="course-item"><span class="course-code">${c.code}</span><span class="course-name">${c.name}</span></div>`).join('')}
                            ${(!coursesBySemester[sem.id] || coursesBySemester[sem.id].length === 0) ? '<p style="color:#6c757d; font-size:0.8rem;">No subjects assigned yet</p>' : ''}
                        </div>
                        <button class="btn-manage" onclick="editSemester(${sem.id})"><i class="fas fa-edit"></i> Edit Semester</button>
                        <button class="btn-manage" onclick="manageSubjects(${sem.id})" style="margin-top:10px; background:#4361ee; color:white;"><i class="fas fa-book"></i> Manage Subjects</button>
                    </div>
                </div>
            `).join('');
        }
        
        function editSemester(id) {
            const sem = semesters.find(s => s.id === id);
            if(sem) {
                document.getElementById('editSemesterId').value = sem.id;
                document.getElementById('editSemesterName').value = sem.name;
                document.getElementById('editAcademicYear').value = sem.year;
                document.getElementById('editStartDate').value = sem.startDate;
                document.getElementById('editEndDate').value = sem.endDate;
                document.getElementById('editStatus').value = sem.status;
                document.getElementById('editModal').style.display = 'flex';
            }
        }
        
        function manageSubjects(semesterId) {
            window.location.href = `subject-management.php?semester=${semesterId}`;
        }
        
        document.getElementById('editForm').addEventListener('submit', function(e) {
            e.preventDefault();
            showNotification('Semester updated successfully!', 'success');
            closeModal();
            loadSemesters();
        });
        
        function closeModal() { document.getElementById('editModal').style.display = 'none'; }
        function showNotification(message, type) { const n = document.createElement('div'); n.className = 'notification'; n.style.background = type === 'success' ? '#4cc9f0' : '#f72585'; n.innerHTML = `<i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i> ${message}`; document.body.appendChild(n); setTimeout(() => n.remove(), 3000); }
        
        const sidebar = document.getElementById('sidebar'), mainContent = document.getElementById('mainContent'), toggleBtn = document.getElementById('toggleSidebar'), mobileMenuBtn = document.getElementById('mobileMenuBtn');
        if(toggleBtn) toggleBtn.addEventListener('click', function() { sidebar.classList.toggle('collapsed'); mainContent.classList.toggle('expanded'); });
        if(mobileMenuBtn) mobileMenuBtn.addEventListener('click', function() { sidebar.classList.toggle('active'); });
        function handleResponsive() { if(window.innerWidth <= 768) { sidebar.classList.add('collapsed'); mainContent.classList.add('expanded'); } else { sidebar.classList.remove('collapsed', 'active'); mainContent.classList.remove('expanded'); } }
        window.addEventListener('resize', handleResponsive); handleResponsive();
        
        loadSemesters();
        window.onclick = function(event) { if(event.target == document.getElementById('editModal')) closeModal(); }
    </script>
</body>
</html>