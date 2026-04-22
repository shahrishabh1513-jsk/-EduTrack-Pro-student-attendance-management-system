<?php
require_once '../../../includes/config.php';

if (!isLoggedIn() || !hasRole('admin')) {
    redirect('login.php');
}

$user = getUserData($conn, $_SESSION['user_id']);

// Handle delete request
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $student_id = (int)$_GET['delete'];
    
    // Get user_id first
    $user_query = "SELECT user_id FROM students WHERE id = $student_id";
    $user_result = mysqli_query($conn, $user_query);
    if ($user_row = mysqli_fetch_assoc($user_result)) {
        $user_id = $user_row['user_id'];
        
        // Delete from users table (cascade will delete from students)
        $delete_query = "DELETE FROM users WHERE id = $user_id";
        if (mysqli_query($conn, $delete_query)) {
            $success = "Student deleted successfully!";
        } else {
            $error = "Error deleting student";
        }
    }
}

// Fetch all students with updated names
$query = "SELECT s.*, u.full_name, u.email, u.phone, u.username 
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
        
        .sidebar {
            width: 280px;
            background: white;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            box-shadow: 2px 0 10px rgba(0,0,0,0.05);
            transition: all 0.3s;
            z-index: 100;
        }
        
        .sidebar.collapsed { width: 80px; }
        .sidebar.collapsed .sidebar-nav a span,
        .sidebar.collapsed .sidebar-header h3 { display: none; }
        .sidebar.collapsed .sidebar-nav a { justify-content: center; padding: 15px; }
        
        .sidebar-header {
            padding: 25px 20px;
            border-bottom: 1px solid #e9ecef;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .sidebar-header .logo { display: flex; align-items: center; gap: 10px; }
        .sidebar-header i { font-size: 2rem; color: #4361ee; }
        .sidebar-header h3 { font-size: 1.2rem; color: #1e293b; }
        .toggle-sidebar {
            background: none;
            border: none;
            cursor: pointer;
            font-size: 1.2rem;
            color: #6c757d;
        }
        
        .sidebar-nav { padding: 20px 0; }
        .sidebar-nav a {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 12px 20px;
            color: #6c757d;
            text-decoration: none;
            transition: all 0.3s;
        }
        .sidebar-nav a:hover, .sidebar-nav a.active { background: rgba(67,97,238,0.05); color: #4361ee; border-left: 4px solid #4361ee; }
        
        .main-content {
            flex: 1;
            margin-left: 280px;
            padding: 20px 30px;
            transition: all 0.3s;
        }
        .main-content.expanded { margin-left: 80px; }
        
        .top-header {
            background: white;
            padding: 15px 25px;
            border-radius: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.03);
        }
        
        .user-profile { display: flex; align-items: center; gap: 10px; cursor: pointer; }
        .user-profile img { width: 40px; height: 40px; border-radius: 50%; object-fit: cover; }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: all 0.3s;
            box-shadow: 0 2px 10px rgba(0,0,0,0.03);
        }
        
        .stat-card:hover { transform: translateY(-5px); box-shadow: 0 10px 25px rgba(0,0,0,0.07); }
        .stat-info h3 { font-size: 0.85rem; color: #6c757d; margin-bottom: 5px; }
        .stat-number { font-size: 1.8rem; font-weight: 700; color: #1e293b; }
        .stat-icon { width: 50px; height: 50px; border-radius: 15px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; }
        .stat-icon.blue { background: rgba(67,97,238,0.1); color: #4361ee; }
        .stat-icon.green { background: rgba(76,201,240,0.1); color: #4cc9f0; }
        .stat-icon.orange { background: rgba(247,37,133,0.1); color: #f72585; }
        .stat-icon.purple { background: rgba(63,55,201,0.1); color: #3f37c9; }
        
        .header-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .btn-add {
            background: linear-gradient(135deg, #4361ee, #3f37c9);
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 10px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-weight: 500;
            transition: all 0.3s;
        }
        
        .btn-add:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(67,97,238,0.3); }
        
        .search-filter {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            align-items: center;
        }
        
        .search-box {
            display: flex;
            align-items: center;
            background: white;
            border: 2px solid #e9ecef;
            border-radius: 10px;
            padding: 0 15px;
            min-width: 250px;
        }
        
        .search-box i { color: #6c757d; }
        .search-box input { padding: 12px; border: none; outline: none; width: 100%; font-family: 'Poppins', sans-serif; }
        
        .filter-select {
            padding: 10px 15px;
            border: 2px solid #e9ecef;
            border-radius: 10px;
            font-family: 'Poppins', sans-serif;
            background: white;
            cursor: pointer;
        }
        
        .student-table {
            background: white;
            border-radius: 20px;
            padding: 25px;
            overflow-x: auto;
            box-shadow: 0 2px 10px rgba(0,0,0,0.03);
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #e9ecef;
        }
        
        th {
            background: #f8f9fa;
            font-weight: 600;
            color: #1e293b;
        }
        
        tr:hover { background: #f8f9fa; }
        
        .action-btn {
            background: none;
            border: none;
            cursor: pointer;
            margin: 0 5px;
            font-size: 1rem;
            transition: all 0.3s;
        }
        
        .action-btn:hover { transform: scale(1.1); }
        .view-btn { color: #4361ee; }
        .edit-btn { color: #4cc9f0; }
        .delete-btn { color: #f72585; }
        
        .status-active {
            background: rgba(76,201,240,0.1);
            color: #4cc9f0;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.75rem;
            display: inline-block;
        }
        
        .alert-success {
            background: rgba(76,201,240,0.1);
            color: #4cc9f0;
            padding: 12px 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            border-left: 4px solid #4cc9f0;
        }
        
        .alert-error {
            background: rgba(247,37,133,0.1);
            color: #f72585;
            padding: 12px 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            border-left: 4px solid #f72585;
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
        }
        
        .empty-state i {
            font-size: 4rem;
            color: #cbd5e1;
            margin-bottom: 15px;
        }
        
        .empty-state h3 {
            font-size: 1.2rem;
            color: #1e293b;
            margin-bottom: 10px;
        }
        
        .empty-state p {
            color: #6c757d;
        }
        
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }
        
        .modal-content {
            background: white;
            border-radius: 20px;
            padding: 30px;
            max-width: 400px;
            width: 90%;
            text-align: center;
        }
        
        .modal-buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin-top: 20px;
        }
        
        .modal-buttons button {
            padding: 10px 25px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-family: 'Poppins', sans-serif;
            font-weight: 500;
        }
        
        .confirm-delete { background: #f72585; color: white; }
        .cancel-delete { background: #e9ecef; color: #1e293b; }
        
        @media (max-width: 1024px) {
            .stats-grid { grid-template-columns: repeat(2, 1fr); }
        }
        
        @media (max-width: 768px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.active { transform: translateX(0); }
            .main-content { margin-left: 0; }
            .stats-grid { grid-template-columns: 1fr; }
            .header-actions { flex-direction: column; align-items: stretch; }
            .search-filter { flex-direction: column; }
            .search-box { width: 100%; }
            .filter-select { width: 100%; }
        }
        
        .mobile-menu-btn {
            display: none;
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
        }
        
        @media (max-width: 768px) { .mobile-menu-btn { display: block; } }
        
        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 25px;
            border-radius: 10px;
            z-index: 9999;
            animation: slideIn 0.3s ease;
            color: white;
        }
        
        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
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
                <div><h2><i class="fas fa-user-graduate" style="color: #4361ee;"></i> Student Management</h2></div>
                <div class="user-profile">
                    <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($user['full_name']); ?>&background=4361ee&color=fff&length=2" alt="Profile">
                    <div><h4><?php echo htmlspecialchars($user['full_name']); ?></h4><p>Administrator</p></div>
                </div>
            </header>

            <!-- Statistics Cards -->
            <?php
            $total_students = mysqli_num_rows($result);
            $male_count = 0;
            $female_count = 0;
            $active_count = 0;
            
            // Reset result pointer for counting
            mysqli_data_seek($result, 0);
            while($row = mysqli_fetch_assoc($result)) {
                if($row['gender'] == 'Male') $male_count++;
                if($row['gender'] == 'Female') $female_count++;
                $active_count++;
            }
            mysqli_data_seek($result, 0);
            ?>
            
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-info"><h3>Total Students</h3><div class="stat-number"><?php echo $total_students; ?></div><small>Registered</small></div>
                    <div class="stat-icon blue"><i class="fas fa-users"></i></div>
                </div>
                <div class="stat-card">
                    <div class="stat-info"><h3>Male Students</h3><div class="stat-number"><?php echo $male_count; ?></div><small>Enrolled</small></div>
                    <div class="stat-icon green"><i class="fas fa-mars"></i></div>
                </div>
                <div class="stat-card">
                    <div class="stat-info"><h3>Female Students</h3><div class="stat-number"><?php echo $female_count; ?></div><small>Enrolled</small></div>
                    <div class="stat-icon orange"><i class="fas fa-venus"></i></div>
                </div>
                <div class="stat-card">
                    <div class="stat-info"><h3>Active</h3><div class="stat-number"><?php echo $active_count; ?></div><small>Active Students</small></div>
                    <div class="stat-icon purple"><i class="fas fa-check-circle"></i></div>
                </div>
            </div>

            <?php if(isset($success)): ?>
                <div class="alert-success"><i class="fas fa-check-circle"></i> <?php echo $success; ?></div>
            <?php endif; ?>
            
            <?php if(isset($error)): ?>
                <div class="alert-error"><i class="fas fa-exclamation-circle"></i> <?php echo $error; ?></div>
            <?php endif; ?>

            <!-- Action Buttons -->
            <div class="header-actions">
                <button class="btn-add" onclick="window.location.href='add-student.php'"><i class="fas fa-plus"></i> Add New Student</button>
                <div class="search-filter">
                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" id="searchInput" placeholder="Search by name, roll number or email...">
                    </div>
                    <select class="filter-select" id="courseFilter">
                        <option value="all">All Courses</option>
                        <option value="BSC IT">BSC IT</option>
                        <option value="BCA">BCA</option>
                        <option value="MSC IT">MSC IT</option>
                    </select>
                    <select class="filter-select" id="semesterFilter">
                        <option value="all">All Semesters</option>
                        <option value="1">Semester 1</option>
                        <option value="2">Semester 2</option>
                        <option value="3">Semester 3</option>
                        <option value="4">Semester 4</option>
                        <option value="5">Semester 5</option>
                        <option value="6">Semester 6</option>
                    </select>
                    <button class="btn-add" onclick="exportToCSV()" style="background:#28a745; padding: 10px 20px;"><i class="fas fa-download"></i> Export</button>
                </div>
            </div>

            <!-- Students Table -->
            <div class="student-table">
                <h3 style="margin-bottom:20px;"><i class="fas fa-list"></i> All Students List</h3>
                <div id="studentsTable">
                    <?php if(mysqli_num_rows($result) > 0): ?>
                        <table>
                            <thead>
                                <tr>
                                    <th>Roll No</th>
                                    <th>Full Name</th>
                                    <th>Course</th>
                                    <th>Semester</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($student = mysqli_fetch_assoc($result)): ?>
                                <tr data-course="<?php echo htmlspecialchars($student['course']); ?>" data-semester="<?php echo $student['semester']; ?>">
                                    <td><strong><?php echo htmlspecialchars($student['roll_number']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($student['full_name']); ?></td>
                                    <td><?php echo htmlspecialchars($student['course']); ?></td>
                                    <td><?php echo $student['semester']; ?></td>
                                    <td><?php echo htmlspecialchars($student['email']); ?></td>
                                    <td><?php echo htmlspecialchars($student['phone']); ?></td>
                                    <td><span class="status-active">Active</span></td>
                                    <td>
                                        <button class="action-btn view-btn" onclick="viewStudent(<?php echo $student['id']; ?>)" title="View"><i class="fas fa-eye"></i></button>
                                        <button class="action-btn edit-btn" onclick="editStudent(<?php echo $student['id']; ?>)" title="Edit"><i class="fas fa-edit"></i></button>
                                        <button class="action-btn delete-btn" onclick="openDeleteModal(<?php echo $student['id']; ?>, '<?php echo htmlspecialchars($student['full_name']); ?>')" title="Delete"><i class="fas fa-trash-alt"></i></button>
                                    </td>
                                 </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-user-graduate"></i>
                            <h3>No Students Found</h3>
                            <p>Click "Add New Student" to register a student.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="modal">
        <div class="modal-content">
            <i class="fas fa-exclamation-triangle" style="font-size: 3rem; color: #f72585; margin-bottom: 15px;"></i>
            <h3>Confirm Delete</h3>
            <p id="deleteMessage" style="margin: 15px 0;">Are you sure you want to delete this student?</p>
            <div class="modal-buttons">
                <button class="cancel-delete" onclick="closeDeleteModal()">Cancel</button>
                <button class="confirm-delete" id="confirmDeleteBtn">Delete</button>
            </div>
        </div>
    </div>

    <script>
        let deleteId = null;
        
        function viewStudent(id) {
            window.location.href = `student-detail.php?id=${id}`;
        }
        
        function editStudent(id) {
            window.location.href = `edit-student.php?id=${id}`;
        }
        
        function openDeleteModal(id, name) {
            deleteId = id;
            document.getElementById('deleteMessage').innerHTML = `Are you sure you want to delete <strong>${name}</strong>? This action cannot be undone.`;
            document.getElementById('deleteModal').style.display = 'flex';
        }
        
        function closeDeleteModal() {
            deleteId = null;
            document.getElementById('deleteModal').style.display = 'none';
        }
        
        document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
            if(deleteId) {
                window.location.href = `students-list.php?delete=${deleteId}`;
            }
        });
        
        // Search and Filter functionality
        function filterTable() {
            const searchTerm = document.getElementById('searchInput').value.toLowerCase();
            const courseFilter = document.getElementById('courseFilter').value;
            const semesterFilter = document.getElementById('semesterFilter').value;
            
            const rows = document.querySelectorAll('#studentsTable tbody tr');
            let visibleCount = 0;
            
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                const course = row.getAttribute('data-course');
                const semester = row.getAttribute('data-semester');
                
                let show = true;
                
                if(searchTerm && !text.includes(searchTerm)) show = false;
                if(courseFilter !== 'all' && course !== courseFilter) show = false;
                if(semesterFilter !== 'all' && semester != semesterFilter) show = false;
                
                if(show) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });
            
            // Show empty state if no rows visible
            const tableBody = document.querySelector('#studentsTable tbody');
            const emptyMessage = document.getElementById('emptyTableMessage');
            
            if(visibleCount === 0 && rows.length > 0) {
                if(!emptyMessage) {
                    const msg = document.createElement('tr');
                    msg.id = 'emptyTableMessage';
                    msg.innerHTML = '<td colspan="8" style="text-align: center; padding: 40px;"><i class="fas fa-search" style="font-size: 2rem; color: #cbd5e1;"></i><p style="margin-top: 10px;">No students match your search criteria</p></td>';
                    tableBody.appendChild(msg);
                }
            } else if(emptyMessage) {
                emptyMessage.remove();
            }
        }
        
        function exportToCSV() {
            const rows = document.querySelectorAll('#studentsTable tbody tr:visible');
            let csvContent = "Roll Number,Full Name,Course,Semester,Email,Phone,Status\n";
            
            rows.forEach(row => {
                const cells = row.querySelectorAll('td');
                if(cells.length > 0) {
                    const rowData = [
                        cells[0].innerText.replace(/[^A-Z0-9]/g, ''),
                        cells[1].innerText,
                        cells[2].innerText,
                        cells[3].innerText,
                        cells[4].innerText,
                        cells[5].innerText,
                        'Active'
                    ];
                    csvContent += rowData.join(',') + '\n';
                }
            });
            
            const blob = new Blob([csvContent], { type: 'text/csv' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'students_list.csv';
            a.click();
            URL.revokeObjectURL(url);
            
            showNotification('Students list exported successfully!', 'success');
        }
        
        function showNotification(message, type) {
            const notification = document.createElement('div');
            notification.className = 'notification';
            notification.style.background = type === 'success' ? '#4cc9f0' : '#f72585';
            notification.innerHTML = `<i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i> ${message}`;
            document.body.appendChild(notification);
            setTimeout(() => notification.remove(), 3000);
        }
        
        // Event listeners for filters
        document.getElementById('searchInput').addEventListener('keyup', filterTable);
        document.getElementById('courseFilter').addEventListener('change', filterTable);
        document.getElementById('semesterFilter').addEventListener('change', filterTable);
        
        const sidebar = document.getElementById('sidebar'), mainContent = document.getElementById('mainContent'), toggleBtn = document.getElementById('toggleSidebar'), mobileMenuBtn = document.getElementById('mobileMenuBtn');
        if(toggleBtn) toggleBtn.addEventListener('click', function() { sidebar.classList.toggle('collapsed'); mainContent.classList.toggle('expanded'); });
        if(mobileMenuBtn) mobileMenuBtn.addEventListener('click', function() { sidebar.classList.toggle('active'); });
        
        function handleResponsive() {
            if(window.innerWidth <= 768) {
                sidebar.classList.add('collapsed');
                mainContent.classList.add('expanded');
            } else {
                sidebar.classList.remove('collapsed', 'active');
                mainContent.classList.remove('expanded');
            }
        }
        
        window.addEventListener('resize', handleResponsive);
        handleResponsive();
        
        window.onclick = function(event) {
            if(event.target == document.getElementById('deleteModal')) {
                closeDeleteModal();
            }
        }
    </script>
</body>
</html>