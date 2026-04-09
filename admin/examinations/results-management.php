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
    <title>Results Management - EduTrack Pro</title>
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
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: white; padding: 20px; border-radius: 15px; text-align: center; }
        .stat-value { font-size: 2rem; font-weight: 700; color: #4361ee; }
        .filter-bar { display: flex; gap: 15px; margin-bottom: 20px; flex-wrap: wrap; align-items: center; }
        .filter-select, .search-box { padding: 10px 15px; border: 2px solid #e9ecef; border-radius: 10px; font-family: 'Poppins', sans-serif; }
        .btn-declare { background: #4361ee; color: white; border: none; padding: 10px 20px; border-radius: 8px; cursor: pointer; }
        .btn-export { background: #28a745; color: white; border: none; padding: 10px 20px; border-radius: 8px; cursor: pointer; }
        .results-table { background: white; border-radius: 20px; padding: 25px; overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #e9ecef; }
        th { background: #f8f9fa; font-weight: 600; }
        .grade-aplus { color: #4cc9f0; font-weight: 600; }
        .grade-a { color: #4361ee; font-weight: 600; }
        .edit-btn { background: #4cc9f0; color: white; border: none; padding: 5px 12px; border-radius: 5px; cursor: pointer; margin: 0 5px; }
        .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); justify-content: center; align-items: center; z-index: 1000; }
        .modal-content { background: white; border-radius: 20px; padding: 30px; max-width: 500px; width: 90%; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: 500; }
        .form-control { width: 100%; padding: 10px; border: 2px solid #e9ecef; border-radius: 8px; }
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
                <a href="exam-schedule.php"><i class="fas fa-calendar-alt"></i><span> Exam Schedule</span></a>
                <a href="exam-forms-verification.php"><i class="fas fa-file-alt"></i><span> Exam Forms</span></a>
                <a href="hall-ticket-generate.php"><i class="fas fa-ticket-alt"></i><span> Hall Tickets</span></a>
                <a href="seat-allocation.php"><i class="fas fa-chair"></i><span> Seat Allocation</span></a>
                <a href="results-management.php" class="active"><i class="fas fa-chart-line"></i><span> Results</span></a>
                <a href="../../logout.php"><i class="fas fa-sign-out-alt"></i><span> Logout</span></a>
            </nav>
        </aside>

        <main class="main-content" id="mainContent">
            <header class="top-header">
                <button class="mobile-menu-btn" id="mobileMenuBtn"><i class="fas fa-bars"></i></button>
                <div><h2>Results Management</h2></div>
                <div class="user-profile">
                    <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($user['full_name']); ?>&background=4361ee&color=fff" alt="Profile">
                    <div><h4><?php echo htmlspecialchars($user['full_name']); ?></h4><p>Administrator</p></div>
                </div>
            </header>

            <div class="stats-grid">
                <div class="stat-card"><div class="stat-value" id="totalStudents">0</div><div>Total Students</div></div>
                <div class="stat-card"><div class="stat-value" id="passedCount">0</div><div>Passed</div></div>
                <div class="stat-card"><div class="stat-value" id="failedCount">0</div><div>Failed</div></div>
                <div class="stat-card"><div class="stat-value" id="passPercent">0%</div><div>Pass Percentage</div></div>
            </div>

            <div class="filter-bar">
                <select class="filter-select" id="semesterFilter"><option value="all">All Semesters</option><option>1</option><option>2</option><option>3</option><option>4</option><option>5</option><option>6</option></select>
                <select class="filter-select" id="examFilter"><option value="all">All Exams</option><option>Mid-Term</option><option>End Semester</option></select>
                <div class="search-box"><i class="fas fa-search"></i><input type="text" id="searchInput" placeholder="Search by name or roll..."></div>
                <button class="btn-declare" onclick="declareResults()"><i class="fas fa-file-alt"></i> Declare Results</button>
                <button class="btn-export" onclick="exportResults()"><i class="fas fa-download"></i> Export Results</button>
            </div>

            <div class="results-table">
                <h3 style="margin-bottom:20px;"><i class="fas fa-chart-line"></i> Student Results</h3>
                <div id="resultsTable"></div>
            </div>
        </main>
    </div>

    <!-- Edit Marks Modal -->
    <div id="marksModal" class="modal">
        <div class="modal-content">
            <h3>Edit Student Marks</h3>
            <form id="marksForm">
                <input type="hidden" id="editStudentId">
                <input type="hidden" id="editCourseId">
                <div class="form-group"><label>Student Name</label><input type="text" id="editStudentName" class="form-control" readonly></div>
                <div class="form-group"><label>Course</label><input type="text" id="editCourseName" class="form-control" readonly></div>
                <div class="form-group"><label>Internal Marks (Max 30)</label><input type="number" id="editInternal" class="form-control" min="0" max="30"></div>
                <div class="form-group"><label>External Marks (Max 70)</label><input type="number" id="editExternal" class="form-control" min="0" max="70"></div>
                <div class="form-group"><label>Total Marks</label><input type="text" id="editTotal" class="form-control" readonly></div>
                <button type="submit" class="btn-declare" style="width:100%;">Save Changes</button>
                <button type="button" class="btn-declare" onclick="closeModal()" style="background:#6c757d; margin-top:10px;">Cancel</button>
            </form>
        </div>
    </div>

    <script>
        let results = [
            { id: 1, roll: 'IT181', name: 'Rishabh Shah', course: 'CS301', courseName: 'Data Structures', internal: 28, external: 57, total: 85, grade: 'A', semester: 3 },
            { id: 2, roll: 'IT095', name: 'Jenish khunt', course: 'CS301', courseName: 'Data Structures', internal: 29, external: 63, total: 92, grade: 'A+', semester: 3 },
            { id: 3, roll: 'IT124', name: 'Vasu Motisarya', course: 'CS301', courseName: 'Data Structures', internal: 25, external: 53, total: 78, grade: 'B+', semester: 3 },
            { id: 4, roll: 'IT131', name: 'Hetvi Savani', course: 'CS301', courseName: 'Data Structures', internal: 27, external: 61, total: 88, grade: 'A', semester: 3 }
        ];
        
        function loadResults() {
            const semester = document.getElementById('semesterFilter').value;
            const exam = document.getElementById('examFilter').value;
            const searchTerm = document.getElementById('searchInput').value.toLowerCase();
            
            let filtered = [...results];
            if(semester !== 'all') filtered = filtered.filter(r => r.semester == semester);
            if(searchTerm) filtered = filtered.filter(r => r.name.toLowerCase().includes(searchTerm) || r.roll.toLowerCase().includes(searchTerm));
            
            const passed = filtered.filter(r => r.total >= 40).length;
            const failed = filtered.length - passed;
            document.getElementById('totalStudents').textContent = filtered.length;
            document.getElementById('passedCount').textContent = passed;
            document.getElementById('failedCount').textContent = failed;
            document.getElementById('passPercent').textContent = filtered.length > 0 ? Math.round((passed / filtered.length) * 100) + '%' : '0%';
            
            const tableHTML = `<table><thead><tr><th>Roll No</th><th>Student Name</th><th>Course</th><th>Internal</th><th>External</th><th>Total</th><th>Grade</th><th>Result</th><th>Action</th></tr></thead><tbody>
                ${filtered.map(r => `<tr><td><strong>${r.roll}</strong></td><td>${r.name}</td><td>${r.courseName}</td><td>${r.internal}/30</td><td>${r.external}/70</td><td>${r.total}/100</td><td class="grade-${r.grade === 'A+' ? 'aplus' : 'a'}">${r.grade}</td><td><span style="color:${r.total >= 40 ? '#4cc9f0' : '#f72585'}">${r.total >= 40 ? 'PASS' : 'FAIL'}</span></td><td><button class="edit-btn" onclick="editMarks(${r.id})"><i class="fas fa-edit"></i> Edit</button></td>`).join('')}
            </tbody></table>`;
            document.getElementById('resultsTable').innerHTML = tableHTML;
        }
        
        function editMarks(id) {
            const result = results.find(r => r.id === id);
            if(result) {
                document.getElementById('editStudentId').value = result.id;
                document.getElementById('editStudentName').value = result.name;
                document.getElementById('editCourseName').value = result.courseName;
                document.getElementById('editInternal').value = result.internal;
                document.getElementById('editExternal').value = result.external;
                document.getElementById('editTotal').value = result.total;
                document.getElementById('marksModal').style.display = 'flex';
            }
        }
        
        document.getElementById('editInternal').addEventListener('input', calculateTotal);
        document.getElementById('editExternal').addEventListener('input', calculateTotal);
        
        function calculateTotal() {
            const internal = parseInt(document.getElementById('editInternal').value) || 0;
            const external = parseInt(document.getElementById('editExternal').value) || 0;
            document.getElementById('editTotal').value = internal + external;
        }
        
        document.getElementById('marksForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const id = parseInt(document.getElementById('editStudentId').value);
            const internal = parseInt(document.getElementById('editInternal').value);
            const external = parseInt(document.getElementById('editExternal').value);
            const total = internal + external;
            let grade = 'F';
            if(total >= 90) grade = 'A+';
            else if(total >= 80) grade = 'A';
            else if(total >= 70) grade = 'B+';
            else if(total >= 60) grade = 'B';
            else if(total >= 50) grade = 'C';
            else if(total >= 40) grade = 'D';
            
            const index = results.findIndex(r => r.id === id);
            if(index !== -1) {
                results[index].internal = internal;
                results[index].external = external;
                results[index].total = total;
                results[index].grade = grade;
                closeModal();
                loadResults();
                showNotification('Marks updated successfully!', 'success');
            }
        });
        
        function declareResults() { showNotification('Results declared and published!', 'success'); }
        function exportResults() { showNotification('Results exported!', 'success'); }
        function closeModal() { document.getElementById('marksModal').style.display = 'none'; }
        function showNotification(message, type) { const n = document.createElement('div'); n.className = 'notification'; n.style.background = type === 'success' ? '#4cc9f0' : '#f72585'; n.innerHTML = `<i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i> ${message}`; document.body.appendChild(n); setTimeout(() => n.remove(), 3000); }
        
        document.getElementById('semesterFilter').addEventListener('change', loadResults);
        document.getElementById('searchInput').addEventListener('keyup', loadResults);
        
        const sidebar = document.getElementById('sidebar'), mainContent = document.getElementById('mainContent'), toggleBtn = document.getElementById('toggleSidebar'), mobileMenuBtn = document.getElementById('mobileMenuBtn');
        if(toggleBtn) toggleBtn.addEventListener('click', function() { sidebar.classList.toggle('collapsed'); mainContent.classList.toggle('expanded'); });
        if(mobileMenuBtn) mobileMenuBtn.addEventListener('click', function() { sidebar.classList.toggle('active'); });
        function handleResponsive() { if(window.innerWidth <= 768) { sidebar.classList.add('collapsed'); mainContent.classList.add('expanded'); } else { sidebar.classList.remove('collapsed', 'active'); mainContent.classList.remove('expanded'); } }
        window.addEventListener('resize', handleResponsive); handleResponsive();
        loadResults();
        window.onclick = function(event) { if(event.target == document.getElementById('marksModal')) closeModal(); }
    </script>
</body>
</html>