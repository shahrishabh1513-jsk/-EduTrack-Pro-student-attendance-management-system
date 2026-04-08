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
    <title>Marks Entry - EduTrack Pro</title>
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
        .selection-bar { display: flex; gap: 15px; margin-bottom: 30px; flex-wrap: wrap; align-items: flex-end; background: white; padding: 20px; border-radius: 15px; }
        .form-group { display: flex; flex-direction: column; gap: 5px; }
        .form-group label { font-weight: 500; font-size: 0.9rem; }
        .form-control { padding: 10px 15px; border: 2px solid #e9ecef; border-radius: 10px; font-family: 'Poppins', sans-serif; min-width: 180px; }
        .btn-load { background: #4361ee; color: white; border: none; padding: 10px 25px; border-radius: 10px; cursor: pointer; }
        .marks-table { background: white; border-radius: 20px; padding: 25px; overflow-x: auto; margin-top: 20px; display: none; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #e9ecef; }
        th { background: #f8f9fa; font-weight: 600; }
        .marks-input { width: 80px; padding: 8px; border: 2px solid #e9ecef; border-radius: 8px; text-align: center; }
        .btn-save { background: #4cc9f0; color: white; border: none; padding: 12px 30px; border-radius: 10px; cursor: pointer; margin-top: 20px; }
        .btn-submit { background: #28a745; color: white; border: none; padding: 12px 30px; border-radius: 10px; cursor: pointer; margin-left: 10px; }
        .grade-aplus { color: #4cc9f0; font-weight: 600; }
        .grade-a { color: #4361ee; font-weight: 600; }
        .grade-bplus { color: #3f37c9; font-weight: 600; }
        .grade-b { color: #4895ef; font-weight: 600; }
        @media (max-width: 768px) { .sidebar { transform: translateX(-100%); } .sidebar.active { transform: translateX(0); } .main-content { margin-left: 0; } .selection-bar { flex-direction: column; } .form-group { width: 100%; } .form-control { width: 100%; } }
        .mobile-menu-btn { display: none; background: none; border: none; font-size: 1.5rem; cursor: pointer; }
        @media (max-width: 768px) { .mobile-menu-btn { display: block; } }
        .notification { position: fixed; top: 20px; right: 20px; padding: 15px 25px; border-radius: 10px; z-index: 9999; animation: slideIn 0.3s ease; color: white; }
        @keyframes slideIn { from { transform: translateX(100%); opacity: 0; } to { transform: translateX(0); opacity: 1; } }
        .total-cell { font-weight: 700; background: #f8f9fa; }
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
                <a href="marks-entry.php" class="active"><i class="fas fa-pen"></i><span> Marks Entry</span></a>
                <a href="results-declare.php"><i class="fas fa-chart-line"></i><span> Results Declare</span></a>
                <a href="../logout.php"><i class="fas fa-sign-out-alt"></i><span> Logout</span></a>
            </nav>
        </aside>

        <main class="main-content" id="mainContent">
            <header class="top-header">
                <button class="mobile-menu-btn" id="mobileMenuBtn"><i class="fas fa-bars"></i></button>
                <div><h2>Marks Entry</h2></div>
                <div class="user-profile">
                    <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($user['full_name']); ?>&background=4361ee&color=fff" alt="Profile">
                    <div><h4><?php echo htmlspecialchars($user['full_name']); ?></h4><p><?php echo htmlspecialchars($user['id_number']); ?></p></div>
                </div>
            </header>

            <div class="selection-bar">
                <div class="form-group">
                    <label><i class="fas fa-book"></i> Select Subject</label>
                    <select class="form-control" id="subjectSelect">
                        <option value="">-- Select Subject --</option>
                        <option value="CS301">Data Structures (CS301)</option>
                        <option value="CS302">Database Management (CS302)</option>
                        <option value="CS303">Web Development (CS303)</option>
                        <option value="CS304">Operating Systems (CS304)</option>
                        <option value="CS305">Computer Networks (CS305)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label><i class="fas fa-calendar"></i> Exam Type</label>
                    <select class="form-control" id="examTypeSelect">
                        <option value="mid">Mid-Term Examination</option>
                        <option value="end">End Semester Examination</option>
                        <option value="practical">Practical Examination</option>
                    </select>
                </div>
                <div class="form-group">
                    <label><i class="fas fa-chalkboard"></i> Maximum Marks</label>
                    <input type="number" class="form-control" id="maxMarks" value="100" style="width:100px;">
                </div>
                <button class="btn-load" onclick="loadStudents()"><i class="fas fa-download"></i> Load Students</button>
            </div>

            <div id="marksTableContainer" class="marks-table">
                <h3 style="margin-bottom:20px;"><i class="fas fa-edit"></i> Enter Marks</h3>
                <div id="marksTable"></div>
                <div style="text-align:right; margin-top:20px;">
                    <button class="btn-save" onclick="saveMarks()"><i class="fas fa-save"></i> Save Draft</button>
                    <button class="btn-submit" onclick="submitMarks()"><i class="fas fa-paper-plane"></i> Submit & Publish</button>
                </div>
            </div>
        </main>
    </div>

    <script>
        let studentsData = [
            { id: 1, roll: 'IT181', name: 'Rishabh Sharma', internal: 0, external: 0, total: 0, grade: '' },
            { id: 2, roll: 'IT095', name: 'Jenish Patel', internal: 0, external: 0, total: 0, grade: '' },
            { id: 3, roll: 'IT124', name: 'Vasu Mehta', internal: 0, external: 0, total: 0, grade: '' },
            { id: 4, roll: 'IT131', name: 'Hetvi Shah', internal: 0, external: 0, total: 0, grade: '' }
        ];
        
        function loadStudents() {
            const subject = document.getElementById('subjectSelect').value;
            if(!subject) {
                showNotification('Please select a subject', 'error');
                return;
            }
            
            document.getElementById('marksTableContainer').style.display = 'block';
            
            const tableHTML = `
                <table>
                    <thead>
                        <tr>
                            <th>Roll Number</th>
                            <th>Student Name</th>
                            <th>Internal Marks (30)</th>
                            <th>External Marks (70)</th>
                            <th>Total (100)</th>
                            <th>Grade</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${studentsData.map(s => `
                            <tr>
                                <td><strong>${s.roll}</strong></td>
                                <td>${s.name}</td>
                                <td><input type="number" class="marks-input" id="internal_${s.id}" value="${s.internal}" min="0" max="30" onchange="calculateTotal(${s.id})"></td>
                                <td><input type="number" class="marks-input" id="external_${s.id}" value="${s.external}" min="0" max="70" onchange="calculateTotal(${s.id})"></td>
                                <td><span id="total_${s.id}">${s.total}</span></td>
                                <td><span id="grade_${s.id}" class="${getGradeClass(s.grade)}">${s.grade || '-'}</span></td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            `;
            document.getElementById('marksTable').innerHTML = tableHTML;
        }
        
        function calculateTotal(id) {
            const internal = parseInt(document.getElementById(`internal_${id}`).value) || 0;
            const external = parseInt(document.getElementById(`external_${id}`).value) || 0;
            const total = internal + external;
            const grade = calculateGrade(total);
            
            document.getElementById(`total_${id}`).innerHTML = total;
            document.getElementById(`grade_${id}`).innerHTML = grade;
            document.getElementById(`grade_${id}`).className = getGradeClass(grade);
            
            const student = studentsData.find(s => s.id === id);
            if(student) {
                student.internal = internal;
                student.external = external;
                student.total = total;
                student.grade = grade;
            }
        }
        
        function calculateGrade(total) {
            if(total >= 90) return 'A+';
            if(total >= 80) return 'A';
            if(total >= 70) return 'B+';
            if(total >= 60) return 'B';
            if(total >= 50) return 'C';
            if(total >= 40) return 'D';
            return 'F';
        }
        
        function getGradeClass(grade) {
            const classes = {
                'A+': 'grade-aplus',
                'A': 'grade-a',
                'B+': 'grade-bplus',
                'B': 'grade-b'
            };
            return classes[grade] || '';
        }
        
        function saveMarks() {
            // Save to localStorage
            localStorage.setItem('marks_draft', JSON.stringify(studentsData));
            showNotification('Marks saved as draft!', 'success');
        }
        
        function submitMarks() {
            if(confirm('Are you sure you want to submit and publish these marks? This action cannot be undone.')) {
                const subject = document.getElementById('subjectSelect').value;
                const examType = document.getElementById('examTypeSelect').value;
                
                // Save to localStorage as published
                const publishedData = {
                    subject: subject,
                    examType: examType,
                    date: new Date().toISOString(),
                    marks: studentsData
                };
                localStorage.setItem('published_marks', JSON.stringify(publishedData));
                showNotification('Marks published successfully!', 'success');
            }
        }
        
        function showNotification(message, type) {
            const notification = document.createElement('div');
            notification.className = 'notification';
            notification.style.background = type === 'success' ? '#4cc9f0' : type === 'error' ? '#f72585' : '#4361ee';
            notification.innerHTML = `<i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i> ${message}`;
            document.body.appendChild(notification);
            setTimeout(() => notification.remove(), 3000);
        }
        
        // Load draft if exists
        const savedDraft = localStorage.getItem('marks_draft');
        if(savedDraft) {
            studentsData = JSON.parse(savedDraft);
        }
        
        const sidebar = document.getElementById('sidebar'), mainContent = document.getElementById('mainContent'), toggleBtn = document.getElementById('toggleSidebar'), mobileMenuBtn = document.getElementById('mobileMenuBtn');
        if(toggleBtn) toggleBtn.addEventListener('click', function() { sidebar.classList.toggle('collapsed'); mainContent.classList.toggle('expanded'); });
        if(mobileMenuBtn) mobileMenuBtn.addEventListener('click', function() { sidebar.classList.toggle('active'); });
        function handleResponsive() { if(window.innerWidth <= 768) { sidebar.classList.add('collapsed'); mainContent.classList.add('expanded'); } else { sidebar.classList.remove('collapsed', 'active'); mainContent.classList.remove('expanded'); } }
        window.addEventListener('resize', handleResponsive); handleResponsive();
    </script>
</body>
</html>