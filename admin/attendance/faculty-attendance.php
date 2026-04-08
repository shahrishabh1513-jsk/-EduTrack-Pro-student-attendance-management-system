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
    <title>Faculty Attendance - EduTrack Pro</title>
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
        .search-box { display: flex; align-items: center; background: white; border: 2px solid #e9ecef; border-radius: 10px; padding: 0 15px; margin-bottom: 20px; }
        .search-box input { padding: 12px; border: none; outline: none; width: 100%; font-family: 'Poppins', sans-serif; }
        .attendance-table { background: white; border-radius: 20px; padding: 25px; overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #e9ecef; }
        th { background: #f8f9fa; font-weight: 600; }
        .present { color: #4cc9f0; font-weight: 600; }
        .absent { color: #f72585; font-weight: 600; }
        .view-btn { background: #4361ee; color: white; border: none; padding: 5px 12px; border-radius: 5px; cursor: pointer; }
        @media (max-width: 768px) { .sidebar { transform: translateX(-100%); } .sidebar.active { transform: translateX(0); } .main-content { margin-left: 0; } }
        .mobile-menu-btn { display: none; background: none; border: none; font-size: 1.5rem; cursor: pointer; }
        @media (max-width: 768px) { .mobile-menu-btn { display: block; } }
        .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); justify-content: center; align-items: center; z-index: 1000; }
        .modal-content { background: white; border-radius: 20px; padding: 30px; max-width: 500px; width: 90%; }
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
                <a href="attendance-overview.php"><i class="fas fa-chart-line"></i><span> Attendance Overview</span></a>
                <a href="attendance-report.php"><i class="fas fa-file-alt"></i><span> Attendance Report</span></a>
                <a href="student-attendance.php"><i class="fas fa-user-graduate"></i><span> Student Attendance</span></a>
                <a href="faculty-attendance.php" class="active"><i class="fas fa-chalkboard-teacher"></i><span> Faculty Attendance</span></a>
                <a href="../../logout.php"><i class="fas fa-sign-out-alt"></i><span> Logout</span></a>
            </nav>
        </aside>

        <main class="main-content" id="mainContent">
            <header class="top-header">
                <button class="mobile-menu-btn" id="mobileMenuBtn"><i class="fas fa-bars"></i></button>
                <div><h2>Faculty Attendance</h2></div>
                <div class="user-profile">
                    <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($user['full_name']); ?>&background=4361ee&color=fff" alt="Profile">
                    <div><h4><?php echo htmlspecialchars($user['full_name']); ?></h4><p>Administrator</p></div>
                </div>
            </header>

            <div class="search-box"><i class="fas fa-search"></i><input type="text" id="searchInput" placeholder="Search by faculty name or employee ID..."></div>

            <div class="attendance-table">
                <h3 style="margin-bottom:20px;"><i class="fas fa-calendar-check"></i> Faculty Attendance Records</h3>
                <div id="attendanceTable"></div>
            </div>
        </main>
    </div>

    <div id="facultyModal" class="modal">
        <div class="modal-content">
            <h3 id="modalTitle">Faculty Attendance Details</h3>
            <div id="modalBody"></div>
            <button class="view-btn" onclick="closeModal()" style="margin-top:15px;">Close</button>
        </div>
    </div>

    <script>
        const faculty = [
            { id: 1, empId: 'FAC001', name: 'Dr. Aakash Gupta', dept: 'Computer Science', designation: 'Professor', attendance: 95, present: 38, total: 40 },
            { id: 2, empId: 'FAC002', name: 'Prof. Neha Shah', dept: 'Computer Science', designation: 'Associate Professor', attendance: 88, present: 35, total: 40 },
            { id: 3, empId: 'FAC003', name: 'Prof. Niraj Mehta', dept: 'Computer Science', designation: 'Assistant Professor', attendance: 92, present: 37, total: 40 }
        ];
        
        function loadAttendance() {
            const searchTerm = document.getElementById('searchInput').value.toLowerCase();
            let filtered = faculty;
            if(searchTerm) filtered = faculty.filter(f => f.name.toLowerCase().includes(searchTerm) || f.empId.toLowerCase().includes(searchTerm));
            
            const tableHTML = `<table><thead><tr><th>Employee ID</th><th>Faculty Name</th><th>Department</th><th>Designation</th><th>Attendance %</th><th>Present</th><th>Total Days</th><th>Action</th></tr></thead><tbody>
                ${filtered.map(f => `<tr><td><strong>${f.empId}</strong></td><td>${f.name}</td><td>${f.dept}</td><td>${f.designation}</td><td><div style="display:flex;align-items:center;gap:8px;"><div class="progress-bar" style="width:80px;"><div class="progress-fill" style="width:${f.attendance}%;background:#4361ee;"></div></div>${f.attendance}%</div></td><td>${f.present}</td><td>${f.total}</td><td><button class="view-btn" onclick="viewFaculty(${f.id})">View Details</button></td></tr>`).join('')}
            </tbody></table>`;
            document.getElementById('attendanceTable').innerHTML = tableHTML;
        }
        
        function viewFaculty(id) {
            const f = faculty.find(f => f.id === id);
            if(f) {
                document.getElementById('modalTitle').innerHTML = `Attendance Details - ${f.name}`;
                document.getElementById('modalBody').innerHTML = `<div style="margin-top:15px;"><p><strong>Employee ID:</strong> ${f.empId}</p><p><strong>Name:</strong> ${f.name}</p><p><strong>Department:</strong> ${f.dept}</p><p><strong>Designation:</strong> ${f.designation}</p><hr><h4>Attendance Summary</h4><p><strong>Total Working Days:</strong> ${f.total}</p><p><strong>Days Present:</strong> ${f.present}</p><p><strong>Days Absent:</strong> ${f.total - f.present}</p><p><strong>Attendance Percentage:</strong> ${f.attendance}%</p><div class="progress-bar" style="width:100%;"><div class="progress-fill" style="width:${f.attendance}%;background:#4361ee;"></div></div></div>`;
                document.getElementById('facultyModal').style.display = 'flex';
            }
        }
        
        function closeModal() { document.getElementById('facultyModal').style.display = 'none'; }
        document.getElementById('searchInput').addEventListener('keyup', loadAttendance);
        
        const sidebar = document.getElementById('sidebar'), mainContent = document.getElementById('mainContent'), toggleBtn = document.getElementById('toggleSidebar'), mobileMenuBtn = document.getElementById('mobileMenuBtn');
        if(toggleBtn) toggleBtn.addEventListener('click', function() { sidebar.classList.toggle('collapsed'); mainContent.classList.toggle('expanded'); });
        if(mobileMenuBtn) mobileMenuBtn.addEventListener('click', function() { sidebar.classList.toggle('active'); });
        function handleResponsive() { if(window.innerWidth <= 768) { sidebar.classList.add('collapsed'); mainContent.classList.add('expanded'); } else { sidebar.classList.remove('collapsed', 'active'); mainContent.classList.remove('expanded'); } }
        window.addEventListener('resize', handleResponsive); handleResponsive();
        loadAttendance();
        window.onclick = function(event) { if(event.target == document.getElementById('facultyModal')) closeModal(); }
    </script>
</body>
</html>