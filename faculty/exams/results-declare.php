<?php
require_once '../../includes/config.php';

if (!isLoggedIn() || !hasRole('faculty')) {
    redirect('login.php');
}

$user = getUserData($conn, $_SESSION['user_id']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Declare Results - EduTrack Pro</title>
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
        .result-summary { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .summary-card { background: white; padding: 20px; border-radius: 15px; text-align: center; box-shadow: 0 2px 10px rgba(0,0,0,0.03); }
        .summary-value { font-size: 2rem; font-weight: 700; color: #4361ee; }
        .filter-bar { display: flex; gap: 15px; margin-bottom: 20px; flex-wrap: wrap; }
        .filter-select { padding: 10px 15px; border: 2px solid #e9ecef; border-radius: 10px; font-family: 'Poppins', sans-serif; }
        .results-table { background: white; border-radius: 20px; padding: 25px; overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #e9ecef; }
        th { background: #f8f9fa; font-weight: 600; }
        .btn-declare { background: #4361ee; color: white; border: none; padding: 10px 25px; border-radius: 8px; cursor: pointer; margin-bottom: 20px; }
        .btn-print { background: #28a745; color: white; border: none; padding: 10px 25px; border-radius: 8px; cursor: pointer; margin-left: 10px; }
        .status-published { background: rgba(76,201,240,0.1); color: #4cc9f0; padding: 4px 10px; border-radius: 20px; font-size: 0.8rem; display: inline-block; }
        .status-draft { background: rgba(255,193,7,0.1); color: #ffc107; padding: 4px 10px; border-radius: 20px; font-size: 0.8rem; display: inline-block; }
        .grade-aplus { color: #4cc9f0; font-weight: 600; }
        .grade-a { color: #4361ee; font-weight: 600; }
        .grade-bplus { color: #3f37c9; font-weight: 600; }
        @media (max-width: 768px) { .sidebar { transform: translateX(-100%); } .sidebar.active { transform: translateX(0); } .main-content { margin-left: 0; } }
        .mobile-menu-btn { display: none; background: none; border: none; font-size: 1.5rem; cursor: pointer; }
        @media (max-width: 768px) { .mobile-menu-btn { display: block; } }
        .notification { position: fixed; top: 20px; right: 20px; padding: 15px 25px; border-radius: 10px; z-index: 9999; animation: slideIn 0.3s ease; color: white; }
        @keyframes slideIn { from { transform: translateX(100%); opacity: 0; } to { transform: translateX(0); opacity: 1; } }
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
                <a href="exam-verification.php"><i class="fas fa-file-alt"></i><span> Exam Verification</span></a>
                <a href="marks-entry.php"><i class="fas fa-pen"></i><span> Marks Entry</span></a>
                <a href="results-declare.php" class="active"><i class="fas fa-chart-line"></i><span> Results Declare</span></a>
                <a href="../logout.php"><i class="fas fa-sign-out-alt"></i><span> Logout</span></a>
            </nav>
        </aside>

        <main class="main-content" id="mainContent">
            <header class="top-header">
                <button class="mobile-menu-btn" id="mobileMenuBtn"><i class="fas fa-bars"></i></button>
                <div><h2>Declare Results</h2></div>
                <div class="user-profile">
                    <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($user['full_name']); ?>&background=4361ee&color=fff" alt="Profile">
                    <div><h4><?php echo htmlspecialchars($user['full_name']); ?></h4><p><?php echo htmlspecialchars($user['id_number']); ?></p></div>
                </div>
            </header>

            <div class="result-summary">
                <div class="summary-card"><h3>Total Students</h3><div class="summary-value" id="totalStudents">0</div></div>
                <div class="summary-card"><h3>Passed</h3><div class="summary-value" id="passedCount">0</div></div>
                <div class="summary-card"><h3>Failed</h3><div class="summary-value" id="failedCount">0</div></div>
                <div class="summary-card"><h3>Pass Percentage</h3><div class="summary-value" id="passPercentage">0%</div></div>
            </div>

            <div class="filter-bar">
                <select class="filter-select" id="subjectFilter">
                    <option value="all">All Subjects</option>
                    <option value="CS301">Data Structures</option>
                    <option value="CS302">Database Management</option>
                    <option value="CS303">Web Development</option>
                </select>
                <select class="filter-select" id="statusFilter">
                    <option value="all">All Results</option>
                    <option value="published">Published</option>
                    <option value="draft">Draft</option>
                </select>
            </div>

            <div style="margin-bottom:20px;">
                <button class="btn-declare" onclick="declareResults()"><i class="fas fa-file-alt"></i> Declare New Results</button>
                <button class="btn-print" onclick="window.print()"><i class="fas fa-print"></i> Print Result Sheet</button>
            </div>

            <div class="results-table">
                <h3 style="margin-bottom:20px;"><i class="fas fa-chart-line"></i> Declared Results</h3>
                <div id="resultsTable"></div>
            </div>
        </main>
    </div>

    <!-- Declare Results Modal -->
    <div id="declareModal" class="modal">
        <div class="modal-content">
            <h3><i class="fas fa-file-alt"></i> Declare New Results</h3>
            <div style="margin-top:20px;">
                <div class="form-group" style="margin-bottom:15px;">
                    <label>Select Subject</label>
                    <select id="newSubject" style="width:100%; padding:10px; border:2px solid #e9ecef; border-radius:8px; margin-top:5px;">
                        <option value="CS301">Data Structures (CS301)</option>
                        <option value="CS302">Database Management (CS302)</option>
                        <option value="CS303">Web Development (CS303)</option>
                    </select>
                </div>
                <div class="form-group" style="margin-bottom:15px;">
                    <label>Exam Type</label>
                    <select id="newExamType" style="width:100%; padding:10px; border:2px solid #e9ecef; border-radius:8px; margin-top:5px;">
                        <option value="mid">Mid-Term Examination</option>
                        <option value="end">End Semester Examination</option>
                    </select>
                </div>
                <div class="form-group" style="margin-bottom:15px;">
                    <label>Result Date</label>
                    <input type="date" id="newResultDate" style="width:100%; padding:10px; border:2px solid #e9ecef; border-radius:8px; margin-top:5px;">
                </div>
                <div style="display:flex; gap:10px; margin-top:20px;">
                    <button class="btn-declare" onclick="publishResults()" style="flex:1;">Publish Results</button>
                    <button class="btn-print" onclick="closeModal()" style="flex:1;">Cancel</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        let results = [
            { id: 1, subject: 'CS301', subjectName: 'Data Structures', examType: 'Mid-Term', date: '2024-03-15', status: 'published', students: 45, passed: 38, avgMarks: 78 },
            { id: 2, subject: 'CS302', subjectName: 'Database Management', examType: 'Mid-Term', date: '2024-03-16', status: 'published', students: 38, passed: 35, avgMarks: 82 },
            { id: 3, subject: 'CS303', subjectName: 'Web Development', examType: 'Mid-Term', date: '2024-03-17', status: 'draft', students: 45, passed: 0, avgMarks: 0 }
        ];
        
        function loadResults() {
            const subjectFilter = document.getElementById('subjectFilter').value;
            const statusFilter = document.getElementById('statusFilter').value;
            
            let filtered = [...results];
            if(subjectFilter !== 'all') filtered = filtered.filter(r => r.subject === subjectFilter);
            if(statusFilter !== 'all') filtered = filtered.filter(r => r.status === statusFilter);
            
            // Calculate totals
            const totalStudents = filtered.reduce((sum, r) => sum + r.students, 0);
            const totalPassed = filtered.reduce((sum, r) => sum + r.passed, 0);
            document.getElementById('totalStudents').textContent = totalStudents;
            document.getElementById('passedCount').textContent = totalPassed;
            document.getElementById('failedCount').textContent = totalStudents - totalPassed;
            document.getElementById('passPercentage').textContent = totalStudents > 0 ? Math.round((totalPassed / totalStudents) * 100) + '%' : '0%';
            
            if(filtered.length === 0) {
                document.getElementById('resultsTable').innerHTML = '<div style="text-align:center;padding:40px;">No results declared</div>';
                return;
            }
            
            const tableHTML = `
                <table>
                    <thead>
                        <tr>
                            <th>Subject</th>
                            <th>Exam Type</th>
                            <th>Result Date</th>
                            <th>Total Students</th>
                            <th>Passed</th>
                            <th>Failed</th>
                            <th>Pass %</th>
                            <th>Avg Marks</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${filtered.map(r => `
                            <tr>
                                <td><strong>${r.subjectName}</strong></td>
                                <td>${r.examType}</td>
                                <td>${new Date(r.date).toLocaleDateString()}</td>
                                <td>${r.students}</td>
                                <td>${r.passed}</td>
                                <td>${r.students - r.passed}</td>
                                <td>${Math.round((r.passed / r.students) * 100)}%</td>
                                <td>${r.avgMarks}</td>
                                <td><span class="status-${r.status}">${r.status.toUpperCase()}</span></td>
                                <td><button class="btn-declare" onclick="viewResultDetails(${r.id})" style="padding:5px 15px; margin:0;"><i class="fas fa-eye"></i> View</button></td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            `;
            document.getElementById('resultsTable').innerHTML = tableHTML;
        }
        
        function declareResults() {
            document.getElementById('declareModal').style.display = 'flex';
            document.getElementById('newResultDate').value = new Date().toISOString().split('T')[0];
        }
        
        function publishResults() {
            const subject = document.getElementById('newSubject').value;
            const examType = document.getElementById('newExamType').value;
            const resultDate = document.getElementById('newResultDate').value;
            
            const subjectNames = {
                'CS301': 'Data Structures',
                'CS302': 'Database Management',
                'CS303': 'Web Development'
            };
            
            const newResult = {
                id: results.length + 1,
                subject: subject,
                subjectName: subjectNames[subject],
                examType: examType === 'mid' ? 'Mid-Term' : 'End Semester',
                date: resultDate,
                status: 'published',
                students: 45,
                passed: 42,
                avgMarks: 85
            };
            
            results.push(newResult);
            closeModal();
            loadResults();
            showNotification('Results declared successfully!', 'success');
        }
        
        function viewResultDetails(id) {
            const result = results.find(r => r.id === id);
            if(result) {
                showNotification(`Viewing results for ${result.subjectName}`, 'info');
                // Here you would show detailed results
            }
        }
        
        function closeModal() {
            document.getElementById('declareModal').style.display = 'none';
        }
        
        function showNotification(message, type) {
            const notification = document.createElement('div');
            notification.className = 'notification';
            notification.style.background = type === 'success' ? '#4cc9f0' : type === 'error' ? '#f72585' : '#4361ee';
            notification.innerHTML = `<i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-info-circle'}"></i> ${message}`;
            document.body.appendChild(notification);
            setTimeout(() => notification.remove(), 3000);
        }
        
        document.getElementById('subjectFilter').addEventListener('change', loadResults);
        document.getElementById('statusFilter').addEventListener('change', loadResults);
        
        const sidebar = document.getElementById('sidebar'), mainContent = document.getElementById('mainContent'), toggleBtn = document.getElementById('toggleSidebar'), mobileMenuBtn = document.getElementById('mobileMenuBtn');
        if(toggleBtn) toggleBtn.addEventListener('click', function() { sidebar.classList.toggle('collapsed'); mainContent.classList.toggle('expanded'); });
        if(mobileMenuBtn) mobileMenuBtn.addEventListener('click', function() { sidebar.classList.toggle('active'); });
        function handleResponsive() { if(window.innerWidth <= 768) { sidebar.classList.add('collapsed'); mainContent.classList.add('expanded'); } else { sidebar.classList.remove('collapsed', 'active'); mainContent.classList.remove('expanded'); } }
        window.addEventListener('resize', handleResponsive); handleResponsive();
        
        loadResults();
        
        window.onclick = function(event) { if(event.target == document.getElementById('declareModal')) closeModal(); }
    </script>
</body>
</html>