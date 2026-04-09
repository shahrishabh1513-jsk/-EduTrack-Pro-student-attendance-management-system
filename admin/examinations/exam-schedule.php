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
    <title>Exam Schedule - EduTrack Pro</title>
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
        .btn-export { background: #28a745; }
        .btn-filter { background: #6c757d; }
        
        .filter-bar {
            background: white;
            padding: 20px;
            border-radius: 15px;
            display: flex;
            gap: 15px;
            margin-bottom: 25px;
            flex-wrap: wrap;
            align-items: flex-end;
            box-shadow: 0 2px 10px rgba(0,0,0,0.03);
        }
        
        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        
        .filter-group label {
            font-size: 0.8rem;
            font-weight: 500;
            color: #495057;
        }
        
        .filter-select, .date-input {
            padding: 10px 15px;
            border: 2px solid #e9ecef;
            border-radius: 10px;
            font-family: 'Poppins', sans-serif;
            background: white;
            min-width: 160px;
        }
        
        .schedule-table {
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
        
        .status-upcoming {
            background: rgba(255,193,7,0.1);
            color: #ffc107;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            display: inline-block;
        }
        
        .status-completed {
            background: rgba(108,117,125,0.1);
            color: #6c757d;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            display: inline-block;
        }
        
        .status-ongoing {
            background: rgba(76,201,240,0.1);
            color: #4cc9f0;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            display: inline-block;
        }
        
        .edit-btn, .delete-btn {
            background: none;
            border: none;
            cursor: pointer;
            margin: 0 5px;
            font-size: 1rem;
        }
        .edit-btn { color: #4cc9f0; }
        .delete-btn { color: #f72585; }
        
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
            max-width: 600px;
            width: 90%;
            max-height: 80vh;
            overflow-y: auto;
        }
        
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: 500; color: #1e293b; }
        .form-control { width: 100%; padding: 10px; border: 2px solid #e9ecef; border-radius: 8px; font-family: 'Poppins', sans-serif; }
        .form-control:focus { outline: none; border-color: #4361ee; }
        .btn-submit { background: #4361ee; color: white; border: none; padding: 12px 25px; border-radius: 8px; cursor: pointer; width: 100%; }
        .btn-cancel { background: #6c757d; margin-top: 10px; }
        
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
        
        @media (max-width: 1024px) {
            .stats-grid { grid-template-columns: repeat(2, 1fr); }
        }
        
        @media (max-width: 768px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.active { transform: translateX(0); }
            .main-content { margin-left: 0; }
            .stats-grid { grid-template-columns: 1fr; }
            .filter-bar { flex-direction: column; }
            .filter-group { width: 100%; }
            .filter-select, .date-input { width: 100%; }
            .header-actions { flex-direction: column; align-items: stretch; }
        }
        
        .mobile-menu-btn {
            display: none;
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
        }
        
        @media (max-width: 768px) {
            .mobile-menu-btn { display: block; }
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
                <a href="exam-schedule.php" class="active"><i class="fas fa-calendar-alt"></i><span> Exam Schedule</span></a>
                <a href="exam-forms-verification.php"><i class="fas fa-file-alt"></i><span> Exam Forms</span></a>
                <a href="hall-ticket-generate.php"><i class="fas fa-ticket-alt"></i><span> Hall Tickets</span></a>
                <a href="seat-allocation.php"><i class="fas fa-chair"></i><span> Seat Allocation</span></a>
                <a href="results-management.php"><i class="fas fa-chart-line"></i><span> Results</span></a>
                <a href="../../logout.php"><i class="fas fa-sign-out-alt"></i><span> Logout</span></a>
            </nav>
        </aside>

        <main class="main-content" id="mainContent">
            <header class="top-header">
                <button class="mobile-menu-btn" id="mobileMenuBtn"><i class="fas fa-bars"></i></button>
                <div><h2>Exam Schedule Management</h2></div>
                <div class="user-profile">
                    <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($user['full_name']); ?>&background=4361ee&color=fff" alt="Profile">
                    <div><h4><?php echo htmlspecialchars($user['full_name']); ?></h4><p>Administrator</p></div>
                </div>
            </header>

            <!-- Statistics Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-info"><h3>Total Exams</h3><div class="stat-number">3</div><small>Scheduled</small></div>
                    <div class="stat-icon blue"><i class="fas fa-calendar-alt"></i></div>
                </div>
                <div class="stat-card">
                    <div class="stat-info"><h3>Upcoming</h3><div class="stat-number">2</div><small>Future Exams</small></div>
                    <div class="stat-icon green"><i class="fas fa-clock"></i></div>
                </div>
                <div class="stat-card">
                    <div class="stat-info"><h3>Ongoing</h3><div class="stat-number">0</div><small>Today</small></div>
                    <div class="stat-icon orange"><i class="fas fa-play-circle"></i></div>
                </div>
                <div class="stat-card">
                    <div class="stat-info"><h3>Completed</h3><div class="stat-number">1</div><small>Finished</small></div>
                    <div class="stat-icon purple"><i class="fas fa-check-circle"></i></div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="header-actions">
                <button class="btn-add" onclick="openAddModal()"><i class="fas fa-plus"></i> Add Exam Schedule</button>
                <button class="btn-add btn-export" onclick="exportSchedule()" style="background:#28a745;"><i class="fas fa-download"></i> Export Schedule</button>
            </div>

            <!-- Filter Bar -->
            <div class="filter-bar">
                <div class="filter-group"><label><i class="fas fa-layer-group"></i> Semester</label><select class="filter-select" id="semesterFilter"><option value="all">All Semesters</option><option>1</option><option>2</option><option>3</option><option>4</option><option>5</option><option>6</option></select></div>
                <div class="filter-group"><label><i class="fas fa-tag"></i> Exam Type</label><select class="filter-select" id="examTypeFilter"><option value="all">All Exam Types</option><option>Mid-Term</option><option>End Semester</option><option>Practical</option></select></div>
                <div class="filter-group"><label><i class="fas fa-calendar-week"></i> From Date</label><input type="date" class="date-input" id="startDateFilter"></div>
                <div class="filter-group"><label><i class="fas fa-calendar-week"></i> To Date</label><input type="date" class="date-input" id="endDateFilter"></div>
                <div class="filter-group"><label>&nbsp;</label><button class="btn-add btn-filter" onclick="filterSchedule()" style="background:#6c757d; padding: 10px 25px;"><i class="fas fa-filter"></i> Apply Filter</button></div>
            </div>

            <!-- Schedule Table -->
            <div class="schedule-table">
                <h3 style="margin-bottom:20px;"><i class="fas fa-calendar-alt"></i> Examination Schedule</h3>
                <div id="scheduleTable"></div>
            </div>
        </main>
    </div>

    <!-- Add/Edit Exam Modal -->
    <div id="examModal" class="modal">
        <div class="modal-content">
            <h3 id="modalTitle"><i class="fas fa-plus"></i> Add Exam Schedule</h3>
            <form id="examForm">
                <input type="hidden" id="examId">
                <div class="form-group"><label>Exam Name *</label><input type="text" class="form-control" id="examName" required></div>
                <div class="form-group"><label>Exam Type *</label><select class="form-control" id="examType" required><option>Mid-Term</option><option>End Semester</option><option>Practical</option></select></div>
                <div class="form-group"><label>Course / Subject *</label><select class="form-control" id="courseId" required><option value="CS301">Data Structures (CS301)</option><option value="CS302">Database Management (CS302)</option><option value="CS303">Web Development (CS303)</option></select></div>
                <div class="form-group"><label>Semester *</label><select class="form-control" id="semester" required><option>1</option><option>2</option><option>3</option><option>4</option><option>5</option><option>6</option></select></div>
                <div class="form-group"><label>Exam Date *</label><input type="date" class="form-control" id="examDate" required></div>
                <div class="form-group"><label>Start Time *</label><input type="time" class="form-control" id="startTime" required></div>
                <div class="form-group"><label>End Time *</label><input type="time" class="form-control" id="endTime" required></div>
                <div class="form-group"><label>Venue / Room *</label><input type="text" class="form-control" id="venue" placeholder="e.g., Room 101, Lab 3" required></div>
                <div class="form-group"><label>Total Marks</label><input type="number" class="form-control" id="totalMarks" value="100"></div>
                <div class="form-group"><label>Instructions</label><textarea class="form-control" id="instructions" rows="3" placeholder="Exam instructions..."></textarea></div>
                <button type="submit" class="btn-submit"><i class="fas fa-save"></i> Save Schedule</button>
                <button type="button" class="btn-submit btn-cancel" onclick="closeModal()"><i class="fas fa-times"></i> Cancel</button>
            </form>
        </div>
    </div>

    <script>
        let exams = [
            { id: 1, name: 'Data Structures Mid-Term', type: 'Mid-Term', course: 'CS301', courseName: 'Data Structures', semester: 3, date: '2024-03-25', startTime: '10:00', endTime: '13:00', venue: 'Room 101', totalMarks: 100, status: 'upcoming' },
            { id: 2, name: 'Database Management Mid-Term', type: 'Mid-Term', course: 'CS302', courseName: 'Database Management', semester: 3, date: '2024-03-26', startTime: '10:00', endTime: '13:00', venue: 'Room 203', totalMarks: 100, status: 'upcoming' },
            { id: 3, name: 'Web Development Practical', type: 'Practical', course: 'CS303', courseName: 'Web Development', semester: 3, date: '2024-03-27', startTime: '09:00', endTime: '12:00', venue: 'Lab 3', totalMarks: 50, status: 'completed' }
        ];
        
        function loadSchedule() {
            const semester = document.getElementById('semesterFilter').value;
            const examType = document.getElementById('examTypeFilter').value;
            let filtered = [...exams];
            if(semester !== 'all') filtered = filtered.filter(e => e.semester == semester);
            if(examType !== 'all') filtered = filtered.filter(e => e.type === examType);
            
            if(filtered.length === 0) {
                document.getElementById('scheduleTable').innerHTML = `
                    <div class="empty-state">
                        <i class="fas fa-calendar-times"></i>
                        <h3>No Exams Found</h3>
                        <p>No exam schedules match your filter criteria.</p>
                    </div>
                `;
                return;
            }
            
            const tableHTML = `<table>
                <thead>
                    <tr>
                        <th>Exam Name</th>
                        <th>Course</th>
                        <th>Semester</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Venue</th>
                        <th>Marks</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    ${filtered.map(e => {
                        let statusClass = e.status === 'upcoming' ? 'status-upcoming' : 'status-completed';
                        return `
                            <tr>
                                <td><strong>${e.name}</strong></td>
                                <td>${e.courseName}</td>
                                <td>Semester ${e.semester}</td>
                                <td><i class="fas fa-calendar-day"></i> ${new Date(e.date).toLocaleDateString()}</td>
                                <td><i class="fas fa-clock"></i> ${e.startTime} - ${e.endTime}</td>
                                <td><i class="fas fa-map-marker-alt"></i> ${e.venue}</td>
                                <td>${e.totalMarks}</td>
                                <td><span class="${statusClass}">${e.status.toUpperCase()}</span></td>
                                <td>
                                    <button class="edit-btn" onclick="editExam(${e.id})"><i class="fas fa-edit"></i></button>
                                    <button class="delete-btn" onclick="deleteExam(${e.id})"><i class="fas fa-trash-alt"></i></button>
                                </td>
                            </tr>
                        `;
                    }).join('')}
                </tbody>
            </table>`;
            document.getElementById('scheduleTable').innerHTML = tableHTML;
        }
        
        function openAddModal() { 
            document.getElementById('modalTitle').innerHTML = '<i class="fas fa-plus"></i> Add Exam Schedule'; 
            document.getElementById('examForm').reset(); 
            document.getElementById('examId').value = ''; 
            document.getElementById('examModal').style.display = 'flex'; 
        }
        
        function editExam(id) { 
            const exam = exams.find(e => e.id === id); 
            if(exam) { 
                document.getElementById('modalTitle').innerHTML = '<i class="fas fa-edit"></i> Edit Exam Schedule'; 
                document.getElementById('examId').value = exam.id; 
                document.getElementById('examName').value = exam.name; 
                document.getElementById('examType').value = exam.type; 
                document.getElementById('courseId').value = exam.course; 
                document.getElementById('semester').value = exam.semester; 
                document.getElementById('examDate').value = exam.date; 
                document.getElementById('startTime').value = exam.startTime; 
                document.getElementById('endTime').value = exam.endTime; 
                document.getElementById('venue').value = exam.venue; 
                document.getElementById('totalMarks').value = exam.totalMarks; 
                document.getElementById('examModal').style.display = 'flex'; 
            } 
        }
        
        function deleteExam(id) { 
            if(confirm('Are you sure you want to delete this exam schedule?')) { 
                exams = exams.filter(e => e.id !== id); 
                loadSchedule(); 
                showNotification('Exam deleted successfully!', 'success'); 
            } 
        }
        
        function closeModal() { 
            document.getElementById('examModal').style.display = 'none'; 
        }
        
        function filterSchedule() { 
            loadSchedule(); 
        }
        
        function exportSchedule() { 
            showNotification('Schedule exported successfully!', 'success'); 
        }
        
        document.getElementById('examForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const id = document.getElementById('examId').value;
            const examDate = document.getElementById('examDate').value;
            const today = new Date().toISOString().split('T')[0];
            const status = examDate >= today ? 'upcoming' : 'completed';
            
            const examData = { 
                id: id || exams.length + 1, 
                name: document.getElementById('examName').value, 
                type: document.getElementById('examType').value, 
                course: document.getElementById('courseId').value, 
                courseName: document.getElementById('courseId').options[document.getElementById('courseId').selectedIndex]?.text.split(' (')[0] || '', 
                semester: parseInt(document.getElementById('semester').value), 
                date: examDate, 
                startTime: document.getElementById('startTime').value, 
                endTime: document.getElementById('endTime').value, 
                venue: document.getElementById('venue').value, 
                totalMarks: parseInt(document.getElementById('totalMarks').value), 
                status: status 
            };
            
            if(id) { 
                const index = exams.findIndex(e => e.id == id); 
                if(index !== -1) exams[index] = examData; 
            } else { 
                exams.push(examData); 
            }
            closeModal(); 
            loadSchedule(); 
            showNotification('Schedule saved successfully!', 'success');
        });
        
        function showNotification(message, type) { 
            const n = document.createElement('div'); 
            n.className = 'notification'; 
            n.style.background = type === 'success' ? '#4cc9f0' : '#f72585'; 
            n.innerHTML = `<i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i> ${message}`; 
            document.body.appendChild(n); 
            setTimeout(() => n.remove(), 3000); 
        }
        
        // Set default filter dates
        const today = new Date();
        const nextMonth = new Date();
        nextMonth.setMonth(nextMonth.getMonth() + 1);
        document.getElementById('startDateFilter').value = today.toISOString().split('T')[0];
        document.getElementById('endDateFilter').value = nextMonth.toISOString().split('T')[0];
        
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
        loadSchedule();
        window.onclick = function(event) { if(event.target == document.getElementById('examModal')) closeModal(); }
    </script>
</body>
</html>