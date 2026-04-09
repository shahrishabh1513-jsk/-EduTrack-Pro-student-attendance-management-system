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
    <title>Hall Ticket Generate - EduTrack Pro</title>
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
        .filter-select, .search-box { padding: 10px 15px; border: 2px solid #e9ecef; border-radius: 10px; font-family: 'Poppins', sans-serif; }
        .search-box { flex: 1; min-width: 250px; display: flex; align-items: center; gap: 10px; }
        .search-box input { border: none; outline: none; flex: 1; }
        .btn-generate { background: #4361ee; color: white; border: none; padding: 10px 20px; border-radius: 8px; cursor: pointer; }
        .hall-ticket-card { background: white; border-radius: 20px; padding: 25px; margin-bottom: 20px; border: 1px solid #e9ecef; }
        .hall-ticket-header { background: linear-gradient(135deg, #4361ee, #3f37c9); color: white; padding: 20px; border-radius: 15px; text-align: center; margin-bottom: 20px; }
        .hall-ticket-body { display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px; }
        .info-row { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #e9ecef; }
        .exam-table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        .exam-table th, .exam-table td { padding: 8px; border: 1px solid #e9ecef; text-align: left; font-size: 0.85rem; }
        .btn-print { background: #28a745; color: white; border: none; padding: 10px 20px; border-radius: 8px; cursor: pointer; margin-top: 15px; }
        .btn-download { background: #4361ee; color: white; border: none; padding: 10px 20px; border-radius: 8px; cursor: pointer; margin-right: 10px; }
        .hall-ticket-container { max-width: 800px; margin: 0 auto; }
        @media (max-width: 768px) { .sidebar { transform: translateX(-100%); } .sidebar.active { transform: translateX(0); } .main-content { margin-left: 0; } .hall-ticket-body { grid-template-columns: 1fr; } }
        .mobile-menu-btn { display: none; background: none; border: none; font-size: 1.5rem; cursor: pointer; }
        @media (max-width: 768px) { .mobile-menu-btn { display: block; } }
        .notification { position: fixed; top: 20px; right: 20px; padding: 15px 25px; border-radius: 10px; z-index: 9999; animation: slideIn 0.3s ease; color: white; }
        @keyframes slideIn { from { transform: translateX(100%); opacity: 0; } to { transform: translateX(0); opacity: 1; } }
        @media print { .sidebar, .top-header, .filter-bar, .btn-print, .btn-download, .btn-generate, .notification { display: none; } .main-content { margin: 0; padding: 0; } .hall-ticket-card { box-shadow: none; border: 1px solid #000; } }
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
                <a href="hall-ticket-generate.php" class="active"><i class="fas fa-ticket-alt"></i><span> Hall Tickets</span></a>
                <a href="seat-allocation.php"><i class="fas fa-chair"></i><span> Seat Allocation</span></a>
                <a href="results-management.php"><i class="fas fa-chart-line"></i><span> Results</span></a>
                <a href="../../logout.php"><i class="fas fa-sign-out-alt"></i><span> Logout</span></a>
            </nav>
        </aside>

        <main class="main-content" id="mainContent">
            <header class="top-header">
                <button class="mobile-menu-btn" id="mobileMenuBtn"><i class="fas fa-bars"></i></button>
                <div><h2>Hall Ticket Generator</h2></div>
                <div class="user-profile">
                    <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($user['full_name']); ?>&background=4361ee&color=fff" alt="Profile">
                    <div><h4><?php echo htmlspecialchars($user['full_name']); ?></h4><p>Administrator</p></div>
                </div>
            </header>

            <div class="filter-bar">
                <div class="search-box"><i class="fas fa-search"></i><input type="text" id="searchInput" placeholder="Enter Roll Number or Student Name..."></div>
                <select class="filter-select" id="semesterSelect"><option value="3">Semester 3</option><option value="1">Semester 1</option><option value="2">Semester 2</option><option value="4">Semester 4</option><option value="5">Semester 5</option><option value="6">Semester 6</option></select>
                <button class="btn-generate" onclick="generateHallTicket()"><i class="fas fa-search"></i> Generate Hall Ticket</button>
            </div>

            <div id="hallTicketResult" class="hall-ticket-container"></div>
        </main>
    </div>

    <script>
        const students = [
            { roll: 'IT181', name: 'Rishabh Shah', fatherName: 'Rajesh Sharma', course: 'BSC IT', semester: 3, enrollment: 'ENR2023001', dob: '2004-05-15' },
            { roll: 'IT095', name: 'Jenish khunt', fatherName: 'Raj Patel', course: 'BSC IT', semester: 3, enrollment: 'ENR2023002', dob: '2004-08-20' }
        ];
        
        const exams = [
            { name: 'Data Structures', code: 'CS301', date: '2024-03-25', time: '10:00 AM - 1:00 PM', venue: 'Room 101' },
            { name: 'Database Management', code: 'CS302', date: '2024-03-26', time: '10:00 AM - 1:00 PM', venue: 'Room 203' },
            { name: 'Web Development', code: 'CS303', date: '2024-03-27', time: '10:00 AM - 1:00 PM', venue: 'Lab 3' }
        ];
        
        function generateHallTicket() {
            const searchTerm = document.getElementById('searchInput').value.toLowerCase();
            const student = students.find(s => s.roll.toLowerCase() === searchTerm || s.name.toLowerCase().includes(searchTerm));
            
            if(!student) {
                document.getElementById('hallTicketResult').innerHTML = '<div style="background:white; border-radius:20px; padding:40px; text-align:center;"><i class="fas fa-user-slash" style="font-size:3rem;color:#f72585;"></i><p style="margin-top:15px;">Student not found. Please check roll number or name.</p></div>';
                return;
            }
            
            const html = `
                <div class="hall-ticket-card" id="hallTicketCard">
                    <div class="hall-ticket-header"><h2><i class="fas fa-ticket-alt"></i> HALL TICKET</h2><p>End Semester Examination - ${new Date().getFullYear()}</p></div>
                    <div class="hall-ticket-body">
                        <div><div class="info-row"><strong>Student Name:</strong><span>${student.name}</span></div><div class="info-row"><strong>Roll Number:</strong><span>${student.roll}</span></div><div class="info-row"><strong>Father's Name:</strong><span>${student.fatherName}</span></div><div class="info-row"><strong>Course:</strong><span>${student.course}</span></div></div>
                        <div><div class="info-row"><strong>Enrollment No:</strong><span>${student.enrollment}</span></div><div class="info-row"><strong>Semester:</strong><span>${student.semester}</span></div><div class="info-row"><strong>Date of Birth:</strong><span>${new Date(student.dob).toLocaleDateString()}</span></div><div class="info-row"><strong>Center:</strong><span>Main Campus, Mumbai</span></div></div>
                    </div>
                    <h4 style="margin: 20px 0 10px;">Exam Schedule</h4>
                    <table class="exam-table"><thead><tr><th>Date</th><th>Subject</th><th>Code</th><th>Time</th><th>Venue</th></tr></thead><tbody>
                        ${exams.map(e => `<tr><td>${new Date(e.date).toLocaleDateString()}</td><td>${e.name}</td><td>${e.code}</td><td>${e.time}</td><td>${e.venue}</td></tr>`).join('')}
                    </tbody></table>
                    <div style="margin-top:20px;"><p><strong>Instructions:</strong></p><ul style="margin-left:20px;"><li>Report to examination hall 30 minutes before scheduled time</li><li>Bring this hall ticket and valid ID card</li><li>Electronic devices are strictly prohibited</li><li>Use only black/blue pen for writing</li></ul></div>
                    <div style="margin-top:20px; text-align:center; padding-top:20px; border-top:1px solid #e9ecef;"><p>Controller of Examinations<br>EduTrack Pro University</p></div>
                    <div style="margin-top:15px; text-align:center;"><button class="btn-download" onclick="downloadHallTicket()"><i class="fas fa-download"></i> Download</button><button class="btn-print" onclick="printHallTicket()"><i class="fas fa-print"></i> Print</button></div>
                </div>
            `;
            document.getElementById('hallTicketResult').innerHTML = html;
        }
        
        function printHallTicket() { window.print(); }
        function downloadHallTicket() { showNotification('Hall ticket downloaded!', 'success'); }
        function showNotification(message, type) { const n = document.createElement('div'); n.className = 'notification'; n.style.background = type === 'success' ? '#4cc9f0' : '#f72585'; n.innerHTML = `<i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i> ${message}`; document.body.appendChild(n); setTimeout(() => n.remove(), 3000); }
        
        const sidebar = document.getElementById('sidebar'), mainContent = document.getElementById('mainContent'), toggleBtn = document.getElementById('toggleSidebar'), mobileMenuBtn = document.getElementById('mobileMenuBtn');
        if(toggleBtn) toggleBtn.addEventListener('click', function() { sidebar.classList.toggle('collapsed'); mainContent.classList.toggle('expanded'); });
        if(mobileMenuBtn) mobileMenuBtn.addEventListener('click', function() { sidebar.classList.toggle('active'); });
        function handleResponsive() { if(window.innerWidth <= 768) { sidebar.classList.add('collapsed'); mainContent.classList.add('expanded'); } else { sidebar.classList.remove('collapsed', 'active'); mainContent.classList.remove('expanded'); } }
        window.addEventListener('resize', handleResponsive); handleResponsive();
    </script>
</body>
</html>