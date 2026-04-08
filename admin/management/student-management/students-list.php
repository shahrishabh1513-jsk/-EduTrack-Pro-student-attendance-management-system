<?php
require_once '../../../includes/config.php';

if (!isLoggedIn() || !hasRole('admin')) {
    redirect('login.php');
}

$user = getUserData($conn, $_SESSION['user_id']);

// Fetch all students
$query = "SELECT s.*, u.full_name, u.email, u.phone 
          FROM students s 
          JOIN users u ON s.user_id = u.id 
          ORDER BY s.roll_number";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Students List - EduTrack Pro</title>
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
        .filter-select { padding: 10px 15px; border: 2px solid #e9ecef; border-radius: 10px; font-family: 'Poppins', sans-serif; }
        .student-table { background: white; border-radius: 20px; padding: 25px; overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #e9ecef; }
        th { background: #f8f9fa; font-weight: 600; }
        .action-btn { background: none; border: none; cursor: pointer; margin: 0 5px; font-size: 1rem; }
        .edit-btn { color: #4cc9f0; }
        .delete-btn { color: #f72585; }
        .view-btn { color: #4361ee; }
        .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); justify-content: center; align-items: center; z-index: 1000; }
        .modal-content { background: white; border-radius: 20px; padding: 30px; max-width: 500px; width: 90%; }
        @media (max-width: 768px) { .sidebar { transform: translateX(-100%); } .sidebar.active { transform: translateX(0); } .main-content { margin-left: 0; } .header-actions { flex-direction: column; } }
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
                <a href="students-list.php" class="active"><i class="fas fa-user-graduate"></i><span> Student Management</span></a>
                <a href="../faculty-management/faculty-list.php"><i class="fas fa-chalkboard-teacher"></i><span> Faculty Management</span></a>
                <a href="../course-management/courses-list.php"><i class="fas fa-book"></i><span> Course Management</span></a>
                <a href="../../../logout.php"><i class="fas fa-sign-out-alt"></i><span> Logout</span></a>
            </nav>
        </aside>

        <main class="main-content" id="mainContent">
            <header class="top-header">
                <button class="mobile-menu-btn" id="mobileMenuBtn"><i class="fas fa-bars"></i></button>
                <div><h2>Student Management</h2></div>
                <div class="user-profile">
                    <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($user['full_name']); ?>&background=4361ee&color=fff" alt="Profile">
                    <div><h4><?php echo htmlspecialchars($user['full_name']); ?></h4><p>Administrator</p></div>
                </div>
            </header>

            <div class="header-actions">
                <button class="btn-add" onclick="window.location.href='add-student.php'"><i class="fas fa-plus"></i> Add New Student</button>
                <button class="btn-add" onclick="window.location.href='student-import.php'" style="background:#28a745;"><i class="fas fa-upload"></i> Import Students</button>
                <div class="search-box"><i class="fas fa-search"></i><input type="text" id="searchInput" placeholder="Search by name, roll number or email..."></div>
                <select class="filter-select" id="courseFilter"><option value="all">All Courses</option><option value="BSC IT">BSC IT</option><option value="BCA">BCA</option><option value="MSC IT">MSC IT</option></select>
            </div>

            <div class="student-table">
                <h3 style="margin-bottom:20px;"><i class="fas fa-users"></i> All Students</h3>
                <div id="studentsTable">
                    <table>
                        <thead><tr><th>Roll No</th><th>Full Name</th><th>Course</th><th>Semester</th><th>Email</th><th>Phone</th><th>Status</th><th>Actions</th></tr></thead>
                        <tbody>
                            <?php while($student = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($student['roll_number']); ?></strong></td>
                                <td><?php echo htmlspecialchars($student['full_name']); ?></td>
                                <td><?php echo htmlspecialchars($student['course']); ?></td>
                                <td><?php echo $student['semester']; ?></td>
                                <td><?php echo htmlspecialchars($student['email']); ?></td>
                                <td><?php echo htmlspecialchars($student['phone']); ?></td>
                                <td><span style="background:rgba(76,201,240,0.1); color:#4cc9f0; padding:4px 10px; border-radius:20px; font-size:0.75rem;">Active</span></td>
                                <td>
                                    <button class="action-btn view-btn" onclick="viewStudent(<?php echo $student['id']; ?>)" title="View"><i class="fas fa-eye"></i></button>
                                    <button class="action-btn edit-btn" onclick="editStudent(<?php echo $student['id']; ?>)" title="Edit"><i class="fas fa-edit"></i></button>
                                    <button class="action-btn delete-btn" onclick="deleteStudent(<?php echo $student['id']; ?>)" title="Delete"><i class="fas fa-trash"></i></button>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <script>
        function viewStudent(id) { window.location.href = `student-detail.php?id=${id}`; }
        function editStudent(id) { window.location.href = `edit-student.php?id=${id}`; }
        function deleteStudent(id) { if(confirm('Are you sure you want to delete this student?')) { showNotification('Student deleted successfully!', 'success'); setTimeout(() => location.reload(), 1500); } }
        function showNotification(message, type) { const n = document.createElement('div'); n.className = 'notification'; n.style.background = type === 'success' ? '#4cc9f0' : '#f72585'; n.innerHTML = `<i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i> ${message}`; document.body.appendChild(n); setTimeout(() => n.remove(), 3000); }
        document.getElementById('searchInput').addEventListener('keyup', function() { const filter = this.value.toLowerCase(); const rows = document.querySelectorAll('#studentsTable tbody tr'); rows.forEach(row => { row.style.display = row.textContent.toLowerCase().includes(filter) ? '' : 'none'; }); });
        const sidebar = document.getElementById('sidebar'), mainContent = document.getElementById('mainContent'), toggleBtn = document.getElementById('toggleSidebar'), mobileMenuBtn = document.getElementById('mobileMenuBtn');
        if(toggleBtn) toggleBtn.addEventListener('click', function() { sidebar.classList.toggle('collapsed'); mainContent.classList.toggle('expanded'); });
        if(mobileMenuBtn) mobileMenuBtn.addEventListener('click', function() { sidebar.classList.toggle('active'); });
        function handleResponsive() { if(window.innerWidth <= 768) { sidebar.classList.add('collapsed'); mainContent.classList.add('expanded'); } else { sidebar.classList.remove('collapsed', 'active'); mainContent.classList.remove('expanded'); } }
        window.addEventListener('resize', handleResponsive); handleResponsive();
    </script>
</body>
</html>