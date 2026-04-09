<?php
require_once '../../../includes/config.php';

if (!isLoggedIn() || !hasRole('admin')) {
    redirect('login.php');
}

$user = getUserData($conn, $_SESSION['user_id']);

// Fetch all subjects (courses)
$query = "SELECT c.*, 
          (SELECT COUNT(*) FROM course_assignments WHERE course_id = c.id) as faculty_count
          FROM courses c 
          ORDER BY c.semester, c.course_code";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subject Management - EduTrack Pro</title>
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
        .semester-tabs { display: flex; gap: 10px; margin-bottom: 20px; flex-wrap: wrap; }
        .semester-tab { padding: 10px 20px; background: white; border: 2px solid #e9ecef; border-radius: 25px; cursor: pointer; transition: all 0.3s; }
        .semester-tab.active { background: #4361ee; color: white; border-color: #4361ee; }
        .subjects-table { background: white; border-radius: 20px; padding: 25px; overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #e9ecef; }
        th { background: #f8f9fa; font-weight: 600; }
        .assign-btn { background: #4cc9f0; color: white; border: none; padding: 5px 12px; border-radius: 5px; cursor: pointer; }
        .edit-btn { color: #4361ee; background: none; border: none; cursor: pointer; margin: 0 5px; }
        @media (max-width: 768px) { .sidebar { transform: translateX(-100%); } .sidebar.active { transform: translateX(0); } .main-content { margin-left: 0; } }
        .mobile-menu-btn { display: none; background: none; border: none; font-size: 1.5rem; cursor: pointer; }
        @media (max-width: 768px) { .mobile-menu-btn { display: block; } }
        .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); justify-content: center; align-items: center; z-index: 1000; }
        .modal-content { background: white; border-radius: 20px; padding: 30px; max-width: 500px; width: 90%; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: 500; }
        .form-control { width: 100%; padding: 10px; border: 2px solid #e9ecef; border-radius: 8px; }
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
                <a href="subject-management.php" class="active"><i class="fas fa-tags"></i><span> Subjects</span></a>
                <a href="semester-management.php"><i class="fas fa-layer-group"></i><span> Semesters</span></a>
                <a href="../../../logout.php"><i class="fas fa-sign-out-alt"></i><span> Logout</span></a>
            </nav>
        </aside>

        <main class="main-content" id="mainContent">
            <header class="top-header">
                <button class="mobile-menu-btn" id="mobileMenuBtn"><i class="fas fa-bars"></i></button>
                <div><h2>Subject Management</h2></div>
                <div class="user-profile">
                    <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($user['full_name']); ?>&background=4361ee&color=fff" alt="Profile">
                    <div><h4><?php echo htmlspecialchars($user['full_name']); ?></h4><p>Administrator</p></div>
                </div>
            </header>

            <div class="header-actions">
                <button class="btn-add" onclick="window.location.href='add-course.php'"><i class="fas fa-plus"></i> Add New Subject</button>
                <div class="search-box"><i class="fas fa-search"></i><input type="text" id="searchInput" placeholder="Search subjects..."></div>
            </div>

            <div class="semester-tabs" id="semesterTabs">
                <button class="semester-tab active" data-semester="all">All Semesters</button>
                <button class="semester-tab" data-semester="1">Semester 1</button>
                <button class="semester-tab" data-semester="2">Semester 2</button>
                <button class="semester-tab" data-semester="3">Semester 3</button>
                <button class="semester-tab" data-semester="4">Semester 4</button>
                <button class="semester-tab" data-semester="5">Semester 5</button>
                <button class="semester-tab" data-semester="6">Semester 6</button>
            </div>

            <div class="subjects-table">
                <h3 style="margin-bottom:20px;"><i class="fas fa-tags"></i> All Subjects</h3>
                <div id="subjectsTable">
                    <table>
                        <thead>
                            <tr><th>Code</th><th>Subject Name</th><th>Credits</th><th>Semester</th><th>Department</th><th>Faculty Assigned</th><th>Actions</th></tr>
                        </thead>
                        <tbody>
                            <?php while($course = mysqli_fetch_assoc($result)): ?>
                            <tr data-semester="<?php echo $course['semester']; ?>">
                                <td><strong><?php echo htmlspecialchars($course['course_code']); ?></strong></td>
                                <td><?php echo htmlspecialchars($course['course_name']); ?></td>
                                <td><?php echo $course['credits']; ?></td>
                                <td>Semester <?php echo $course['semester']; ?></td>
                                <td><?php echo htmlspecialchars($course['department']); ?></td>
                                <td><?php echo $course['faculty_count']; ?> faculty</td>
                                <td>
                                    <button class="edit-btn" onclick="assignFaculty(<?php echo $course['id']; ?>, '<?php echo htmlspecialchars($course['course_name']); ?>')"><i class="fas fa-user-plus"></i> Assign</button>
                                    <button class="edit-btn" onclick="editSubject(<?php echo $course['id']; ?>)"><i class="fas fa-edit"></i></button>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <!-- Assign Faculty Modal -->
    <div id="assignModal" class="modal">
        <div class="modal-content">
            <h3><i class="fas fa-user-plus"></i> Assign Faculty to <span id="assignSubjectName"></span></h3>
            <form id="assignForm">
                <input type="hidden" id="assignCourseId">
                <div class="form-group"><label>Select Faculty</label><select class="form-control" id="facultySelect" required><option value="">-- Select Faculty --</option><option value="1">Prof.Aakash Gupta</option><option value="2">Prof. Neha Shah</option><option value="3">Dr. Niraj Shah</option></select></div>
                <div class="form-group"><label>Semester</label><select class="form-control" id="assignSemester" required><option value="1">Semester 1</option><option value="2">Semester 2</option><option value="3">Semester 3</option><option value="4">Semester 4</option><option value="5">Semester 5</option><option value="6">Semester 6</option></select></div>
                <div class="form-group"><label>Academic Year</label><input type="text" class="form-control" id="assignYear" value="2024-2025"></div>
                <button type="submit" class="btn-add" style="width:100%; margin-top:10px;">Assign Faculty</button>
                <button type="button" class="btn-add" onclick="closeModal()" style="width:100%; margin-top:10px; background:#6c757d;">Cancel</button>
            </form>
        </div>
    </div>

    <script>
        function assignFaculty(courseId, courseName) {
            document.getElementById('assignSubjectName').textContent = courseName;
            document.getElementById('assignCourseId').value = courseId;
            document.getElementById('assignModal').style.display = 'flex';
        }
        
        function editSubject(id) { window.location.href = `edit-course.php?id=${id}`; }
        function closeModal() { document.getElementById('assignModal').style.display = 'none'; }
        
        document.getElementById('assignForm').addEventListener('submit', function(e) {
            e.preventDefault();
            showNotification('Faculty assigned successfully!', 'success');
            closeModal();
        });
        
        // Semester filter
        document.querySelectorAll('.semester-tab').forEach(tab => {
            tab.addEventListener('click', function() {
                document.querySelectorAll('.semester-tab').forEach(t => t.classList.remove('active'));
                this.classList.add('active');
                const semester = this.dataset.semester;
                const rows = document.querySelectorAll('#subjectsTable tbody tr');
                rows.forEach(row => {
                    if(semester === 'all' || row.dataset.semester === semester) row.style.display = '';
                    else row.style.display = 'none';
                });
            });
        });
        
        document.getElementById('searchInput').addEventListener('keyup', function() {
            const filter = this.value.toLowerCase();
            const rows = document.querySelectorAll('#subjectsTable tbody tr');
            rows.forEach(row => { row.style.display = row.textContent.toLowerCase().includes(filter) ? '' : 'none'; });
        });
        
        function showNotification(message, type) { const n = document.createElement('div'); n.className = 'notification'; n.style.background = type === 'success' ? '#4cc9f0' : '#f72585'; n.innerHTML = `<i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i> ${message}`; document.body.appendChild(n); setTimeout(() => n.remove(), 3000); }
        
        const sidebar = document.getElementById('sidebar'), mainContent = document.getElementById('mainContent'), toggleBtn = document.getElementById('toggleSidebar'), mobileMenuBtn = document.getElementById('mobileMenuBtn');
        if(toggleBtn) toggleBtn.addEventListener('click', function() { sidebar.classList.toggle('collapsed'); mainContent.classList.toggle('expanded'); });
        if(mobileMenuBtn) mobileMenuBtn.addEventListener('click', function() { sidebar.classList.toggle('active'); });
        function handleResponsive() { if(window.innerWidth <= 768) { sidebar.classList.add('collapsed'); mainContent.classList.add('expanded'); } else { sidebar.classList.remove('collapsed', 'active'); mainContent.classList.remove('expanded'); } }
        window.addEventListener('resize', handleResponsive); handleResponsive();
        
        window.onclick = function(event) { if(event.target == document.getElementById('assignModal')) closeModal(); }
    </script>
</body>
</html>