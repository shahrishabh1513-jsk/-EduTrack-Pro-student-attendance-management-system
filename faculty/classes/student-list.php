<?php
require_once '../../includes/config.php';

if (!isLoggedIn() || !hasRole('faculty')) {
    redirect('login.php');
}

$user = getUserData($conn, $_SESSION['user_id']);
$class_code = $_GET['class'] ?? 'all';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student List - EduTrack Pro</title>
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
        .filter-select { padding: 10px 15px; border: 2px solid #e9ecef; border-radius: 10px; font-family: 'Poppins', sans-serif; }
        .search-box { padding: 10px 15px; border: 2px solid #e9ecef; border-radius: 10px; width: 300px; font-family: 'Poppins', sans-serif; }
        .student-table { background: white; border-radius: 20px; padding: 25px; overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #e9ecef; }
        th { background: #f8f9fa; font-weight: 600; }
        .view-btn { background: #4361ee; color: white; border: none; padding: 5px 12px; border-radius: 5px; cursor: pointer; }
        .view-btn:hover { background: #3f37c9; }
        .progress-bar { width: 80px; height: 6px; background: #e9ecef; border-radius: 3px; overflow: hidden; display: inline-block; margin-right: 8px; }
        .progress-fill { height: 100%; background: #4361ee; border-radius: 3px; }
        .export-btn { background: #28a745; color: white; padding: 10px 20px; border: none; border-radius: 8px; cursor: pointer; display: flex; align-items: center; gap: 8px; }
        .stats-badge { background: #e9ecef; padding: 8px 15px; border-radius: 20px; font-size: 0.85rem; }
        @media (max-width: 768px) { .sidebar { transform: translateX(-100%); } .sidebar.active { transform: translateX(0); } .main-content { margin-left: 0; } .filter-bar { flex-direction: column; } .search-box { width: 100%; } }
        .mobile-menu-btn { display: none; background: none; border: none; font-size: 1.5rem; cursor: pointer; }
        @media (max-width: 768px) { .mobile-menu-btn { display: block; } }
        .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); justify-content: center; align-items: center; z-index: 1000; }
        .modal-content { background: white; border-radius: 20px; padding: 30px; max-width: 500px; width: 90%; }
        .close-modal { float: right; cursor: pointer; font-size: 1.5rem; }
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
                <a href="my-classes.php"><i class="fas fa-chalkboard"></i><span> My Classes</span></a>
                <a href="student-list.php" class="active"><i class="fas fa-users"></i><span> Student List</span></a>
                <a href="timetable.php"><i class="fas fa-clock"></i><span> Timetable</span></a>
                <a href="../logout.php"><i class="fas fa-sign-out-alt"></i><span> Logout</span></a>
            </nav>
        </aside>

        <main class="main-content" id="mainContent">
            <header class="top-header">
                <button class="mobile-menu-btn" id="mobileMenuBtn"><i class="fas fa-bars"></i></button>
                <div><h2>Student List</h2></div>
                <div class="user-profile">
                    <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($user['full_name']); ?>&background=4361ee&color=fff" alt="Profile">
                    <div><h4><?php echo htmlspecialchars($user['full_name']); ?></h4><p><?php echo htmlspecialchars($user['id_number']); ?></p></div>
                </div>
            </header>

            <div class="filter-bar">
                <select class="filter-select" id="classFilter">
                    <option value="all" <?php echo $class_code == 'all' ? 'selected' : ''; ?>>All Classes</option>
                    <option value="CS301" <?php echo $class_code == 'CS301' ? 'selected' : ''; ?>>Data Structures (CS301)</option>
                    <option value="CS302" <?php echo $class_code == 'CS302' ? 'selected' : ''; ?>>Database Management (CS302)</option>
                    <option value="CS303" <?php echo $class_code == 'CS303' ? 'selected' : ''; ?>>Web Development (CS303)</option>
                </select>
                <input type="text" class="search-box" id="searchInput" placeholder="Search by name or roll number...">
                <button class="export-btn" onclick="exportToCSV()"><i class="fas fa-download"></i> Export CSV</button>
            </div>

            <div class="student-table">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 10px;">
                    <h3><i class="fas fa-users"></i> Enrolled Students</h3>
                    <div class="stats-badge" id="studentCount">Total: 0 students</div>
                </div>
                <div id="studentTable"></div>
            </div>
        </main>
    </div>

    <!-- Student Detail Modal -->
    <div id="studentModal" class="modal">
        <div class="modal-content">
            <span class="close-modal" onclick="closeModal()">&times;</span>
            <h3 id="modalTitle">Student Details</h3>
            <div id="modalBody"></div>
        </div>
    </div>

    <script>
        const students = [
            { roll: 'IT181', name: 'Rishabh Sharma', email: 'rishabh@edutrack.com', phone: '9876543210', class: 'CS301', className: 'Data Structures', attendance: 85, parentPhone: '9876543201', address: '123 Student Hostel, Mumbai', dob: '2004-05-15' },
            { roll: 'IT095', name: 'Jenish Patel', email: 'jenish@edutrack.com', phone: '9876543211', class: 'CS301', className: 'Data Structures', attendance: 92, parentPhone: '9876543202', address: '45 College Road, Ahmedabad', dob: '2004-08-20' },
            { roll: 'IT124', name: 'Vasu Mehta', email: 'vasu@edutrack.com', phone: '9876543212', class: 'CS301', className: 'Data Structures', attendance: 78, parentPhone: '9876543203', address: '78 University Area, Surat', dob: '2004-03-10' },
            { roll: 'IT131', name: 'Hetvi Shah', email: 'hetvi@edutrack.com', phone: '9876543213', class: 'CS301', className: 'Data Structures', attendance: 88, parentPhone: '9876543204', address: '12 Gandhi Nagar, Vadodara', dob: '2004-06-25' },
            { roll: 'CSIT001', name: 'Aarav Desai', email: 'aarav@edutrack.com', phone: '9876543214', class: 'CS302', className: 'Database Management', attendance: 90, parentPhone: '9876543205', address: '22 Lake View, Mumbai', dob: '2004-02-10' },
            { roll: 'CSIT002', name: 'Kiara Mehta', email: 'kiara@edutrack.com', phone: '9876543215', class: 'CS302', className: 'Database Management', attendance: 85, parentPhone: '9876543206', address: '56 Park Street, Pune', dob: '2004-07-18' },
            { roll: 'CSIT003', name: 'Dev Patel', email: 'dev@edutrack.com', phone: '9876543216', class: 'CS303', className: 'Web Development', attendance: 75, parentPhone: '9876543207', address: '89 IT Park, Bangalore', dob: '2004-01-30' }
        ];
        
        function loadStudents() {
            const classFilter = document.getElementById('classFilter').value;
            const searchTerm = document.getElementById('searchInput').value.toLowerCase();
            
            let filtered = [...students];
            if(classFilter !== 'all') filtered = filtered.filter(s => s.class === classFilter);
            if(searchTerm) filtered = filtered.filter(s => s.name.toLowerCase().includes(searchTerm) || s.roll.toLowerCase().includes(searchTerm));
            
            document.getElementById('studentCount').innerHTML = `Total: ${filtered.length} students`;
            
            if(filtered.length === 0) {
                document.getElementById('studentTable').innerHTML = '<div style="text-align:center;padding:40px;"><i class="fas fa-users-slash" style="font-size:3rem;color:#6c757d;"></i><p>No students found</p></div>';
                return;
            }
            
            const tableHTML = `
                <table>
                    <thead>
                        <tr>
                            <th>Roll Number</th>
                            <th>Student Name</th>
                            <th>Class</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Attendance</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${filtered.map(s => `
                            <tr>
                                <td><strong>${s.roll}</strong></td>
                                <td>${s.name}</td>
                                <td>${s.className}</td>
                                <td>${s.email}</td>
                                <td>${s.phone}</td>
                                <td>
                                    <div class="progress-bar">
                                        <div class="progress-fill" style="width: ${s.attendance}%"></div>
                                    </div>
                                    ${s.attendance}%
                                </td>
                                <td><button class="view-btn" onclick="viewStudent('${s.roll}')">View Details</button></td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            `;
            document.getElementById('studentTable').innerHTML = tableHTML;
        }
        
        function viewStudent(roll) {
            const student = students.find(s => s.roll === roll);
            if(student) {
                document.getElementById('modalTitle').innerHTML = `Student Details - ${student.name}`;
                document.getElementById('modalBody').innerHTML = `
                    <div style="margin-top:20px;">
                        <p><strong><i class="fas fa-id-card"></i> Roll Number:</strong> ${student.roll}</p>
                        <p><strong><i class="fas fa-user"></i> Full Name:</strong> ${student.name}</p>
                        <p><strong><i class="fas fa-envelope"></i> Email:</strong> ${student.email}</p>
                        <p><strong><i class="fas fa-phone"></i> Phone:</strong> ${student.phone}</p>
                        <p><strong><i class="fas fa-phone-alt"></i> Parent Phone:</strong> ${student.parentPhone}</p>
                        <p><strong><i class="fas fa-map-marker-alt"></i> Address:</strong> ${student.address}</p>
                        <p><strong><i class="fas fa-calendar"></i> Date of Birth:</strong> ${new Date(student.dob).toLocaleDateString()}</p>
                        <p><strong><i class="fas fa-chalkboard"></i> Class:</strong> ${student.className}</p>
                        <p><strong><i class="fas fa-chart-line"></i> Attendance:</strong> ${student.attendance}%</p>
                        <hr style="margin:15px 0;">
                        <h4>Academic Performance</h4>
                        <p><strong>CGPA:</strong> ${(student.attendance / 10 + 5).toFixed(1)}</p>
                        <p><strong>Status:</strong> <span style="color:${student.attendance >= 75 ? '#4cc9f0' : '#f72585'}">${student.attendance >= 75 ? 'Good Standing' : 'Need Improvement'}</span></p>
                    </div>
                `;
                document.getElementById('studentModal').style.display = 'flex';
            }
        }
        
        function closeModal() {
            document.getElementById('studentModal').style.display = 'none';
        }
        
        function exportToCSV() {
            const classFilter = document.getElementById('classFilter').value;
            const searchTerm = document.getElementById('searchInput').value.toLowerCase();
            
            let filtered = [...students];
            if(classFilter !== 'all') filtered = filtered.filter(s => s.class === classFilter);
            if(searchTerm) filtered = filtered.filter(s => s.name.toLowerCase().includes(searchTerm) || s.roll.toLowerCase().includes(searchTerm));
            
            let csvContent = "Roll Number,Name,Class,Email,Phone,Attendance\n";
            filtered.forEach(s => {
                csvContent += `${s.roll},${s.name},${s.className},${s.email},${s.phone},${s.attendance}\n`;
            });
            
            const blob = new Blob([csvContent], { type: 'text/csv' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'student_list.csv';
            a.click();
            URL.revokeObjectURL(url);
        }
        
        document.getElementById('classFilter').addEventListener('change', loadStudents);
        document.getElementById('searchInput').addEventListener('keyup', loadStudents);
        
        const sidebar = document.getElementById('sidebar'), mainContent = document.getElementById('mainContent'), toggleBtn = document.getElementById('toggleSidebar'), mobileMenuBtn = document.getElementById('mobileMenuBtn');
        if(toggleBtn) toggleBtn.addEventListener('click', function() { sidebar.classList.toggle('collapsed'); mainContent.classList.toggle('expanded'); });
        if(mobileMenuBtn) mobileMenuBtn.addEventListener('click', function() { sidebar.classList.toggle('active'); });
        function handleResponsive() { if(window.innerWidth <= 768) { sidebar.classList.add('collapsed'); mainContent.classList.add('expanded'); } else { sidebar.classList.remove('collapsed', 'active'); mainContent.classList.remove('expanded'); } }
        window.addEventListener('resize', handleResponsive); handleResponsive();
        
        loadStudents();
        
        window.onclick = function(event) { if(event.target == document.getElementById('studentModal')) closeModal(); }
    </script>
</body>
</html>