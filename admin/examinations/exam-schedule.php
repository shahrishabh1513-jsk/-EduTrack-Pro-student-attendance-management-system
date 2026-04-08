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
        .filter-bar { display: flex; gap: 15px; margin-bottom: 20px; flex-wrap: wrap; align-items: center; }
        .filter-select, .date-input { padding: 10px 15px; border: 2px solid #e9ecef; border-radius: 10px; font-family: 'Poppins', sans-serif; }
        .schedule-table { background: white; border-radius: 20px; padding: 25px; overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #e9ecef; }
        th { background: #f8f9fa; font-weight: 600; }
        .edit-btn, .delete-btn { background: none; border: none; cursor: pointer; margin: 0 5px; font-size: 1rem; }
        .edit-btn { color: #4cc9f0; }
        .delete-btn { color: #f72585; }
        .status-upcoming { background: rgba(255,193,7,0.1); color: #ffc107; padding: 4px 10px; border-radius: 20px; font-size: 0.75rem; display: inline-block; }
        .status-completed { background: rgba(76,201,240,0.1); color: #4cc9f0; padding: 4px 10px; border-radius: 20px; font-size: 0.75rem; display: inline-block; }
        .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); justify-content: center; align-items: center; z-index: 1000; }
        .modal-content { background: white; border-radius: 20px; padding: 30px; max-width: 600px; width: 90%; max-height: 80vh; overflow-y: auto; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: 500; }
        .form-control { width: 100%; padding: 10px; border: 2px solid #e9ecef; border-radius: 8px; }
        .btn-submit { background: #4361ee; color: white; border: none; padding: 12px 25px; border-radius: 8px; cursor: pointer; width: 100%; }
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
                <a href="../dashboard.php"><i class="fas fa-home</i><span> Dashboard</span></a>
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

            <div class="header-actions">
                <button class="btn-add" onclick="openAddModal()"><i class="fas fa-plus"></i> Add Exam Schedule</button>
                <button class="btn-add" onclick="exportSchedule()" style="background:#28a745;"><i class="fas fa-download"></i> Export Schedule</button>
            </div>

            <div class="filter-bar">
                <select class="filter-select" id="semesterFilter"><option value="all">All Semesters</option><option>Semester 1</option><option>Semester 2</option><option>Semester 3</option><option>Semester 4</option><option>Semester 5</option><option>Semester 6</option></select>
                <select class="filter-select" id="examTypeFilter"><option value="all">All Exam Types</option><option>Mid-Term</option><option>End Semester</option><option>Practical</option></select>
                <input type="date" class="date-input" id="startDateFilter" placeholder="From Date">
                <input type="date" class="date-input" id="endDateFilter" placeholder="To Date">
                <button class="btn-add" onclick="filterSchedule()" style="background:#6c757d;"><i class="fas fa-filter"></i> Filter</button>
            </div>

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
                <button type="button" class="btn-submit" onclick="closeModal()" style="background:#6c757d; margin-top:10px;">Cancel</button>
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
            
            const tableHTML = `<table><thead><tr><th>Exam Name</th><th>Course</th><th>Semester</th><th>Date</th><th>Time</th><th>Venue</th><th>Marks</th><th>Status</th><th>Actions</th></tr></thead><tbody>
                ${filtered.map(e => `<tr><td><strong>${e.name}</strong></td><td>${e.courseName}</td><td>Semester ${e.semester}</td><td>${new Date(e.date).toLocaleDateString()}</td><td>${e.startTime} - ${e.endTime}</td><td>${e.venue}</td><td>${e.totalMarks}</td><td><span class="status-${e.status}">${e.status.toUpperCase()}</span></td><td><button class="edit-btn" onclick="editExam(${e.id})"><i class="fas fa-edit"></i></button><button class="delete-btn" onclick="deleteExam(${e.id})"><i class="fas fa-trash"></i></button></td></tr>`).join('')}
            </tbody></table>`;
            document.getElementById('scheduleTable').innerHTML = tableHTML;
        }
        
        function openAddModal() { document.getElementById('modalTitle').innerHTML = '<i class="fas fa-plus"></i> Add Exam Schedule'; document.getElementById('examForm').reset(); document.getElementById('examId').value = ''; document.getElementById('examModal').style.display = 'flex'; }
        function editExam(id) { const exam = exams.find(e => e.id === id); if(exam) { document.getElementById('modalTitle').innerHTML = '<i class="fas fa-edit"></i> Edit Exam Schedule'; document.getElementById('examId').value = exam.id; document.getElementById('examName').value = exam.name; document.getElementById('examType').value = exam.type; document.getElementById('courseId').value = exam.course; document.getElementById('semester').value = exam.semester; document.getElementById('examDate').value = exam.date; document.getElementById('startTime').value = exam.startTime; document.getElementById('endTime').value = exam.endTime; document.getElementById('venue').value = exam.venue; document.getElementById('totalMarks').value = exam.totalMarks; document.getElementById('examModal').style.display = 'flex'; } }
        function deleteExam(id) { if(confirm('Delete this exam schedule?')) { exams = exams.filter(e => e.id !== id); loadSchedule(); showNotification('Exam deleted!', 'success'); } }
        function closeModal() { document.getElementById('examModal').style.display = 'none'; }
        function filterSchedule() { loadSchedule(); }
        function exportSchedule() { showNotification('Schedule exported!', 'success'); }
        
        document.getElementById('examForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const id = document.getElementById('examId').value;
            const examData = { id: id || exams.length + 1, name: document.getElementById('examName').value, type: document.getElementById('examType').value, course: document.getElementById('courseId').value, courseName: document.getElementById('courseId').options[document.getElementById('courseId').selectedIndex]?.text.split(' (')[0] || '', semester: parseInt(document.getElementById('semester').value), date: document.getElementById('examDate').value, startTime: document.getElementById('startTime').value, endTime: document.getElementById('endTime').value, venue: document.getElementById('venue').value, totalMarks: parseInt(document.getElementById('totalMarks').value), status: new Date(document.getElementById('examDate').value) > new Date() ? 'upcoming' : 'completed' };
            if(id) { const index = exams.findIndex(e => e.id == id); if(index !== -1) exams[index] = examData; } else { exams.push(examData); }
            closeModal(); loadSchedule(); showNotification('Schedule saved!', 'success');
        });
        
        function showNotification(message, type) { const n = document.createElement('div'); n.className = 'notification'; n.style.background = type === 'success' ? '#4cc9f0' : '#f72585'; n.innerHTML = `<i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i> ${message}`; document.body.appendChild(n); setTimeout(() => n.remove(), 3000); }
        
        const sidebar = document.getElementById('sidebar'), mainContent = document.getElementById('mainContent'), toggleBtn = document.getElementById('toggleSidebar'), mobileMenuBtn = document.getElementById('mobileMenuBtn');
        if(toggleBtn) toggleBtn.addEventListener('click', function() { sidebar.classList.toggle('collapsed'); mainContent.classList.toggle('expanded'); });
        if(mobileMenuBtn) mobileMenuBtn.addEventListener('click', function() { sidebar.classList.toggle('active'); });
        function handleResponsive() { if(window.innerWidth <= 768) { sidebar.classList.add('collapsed'); mainContent.classList.add('expanded'); } else { sidebar.classList.remove('collapsed', 'active'); mainContent.classList.remove('expanded'); } }
        window.addEventListener('resize', handleResponsive); handleResponsive();
        loadSchedule();
        window.onclick = function(event) { if(event.target == document.getElementById('examModal')) closeModal(); }
    </script>
</body>
</html>