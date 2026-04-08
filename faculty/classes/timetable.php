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
    <title>My Timetable - EduTrack Pro</title>
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
        .timetable-header { background: linear-gradient(135deg, #4361ee, #3f37c9); border-radius: 20px; padding: 30px; color: white; margin-bottom: 30px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; }
        .week-selector { display: flex; gap: 10px; margin-bottom: 20px; flex-wrap: wrap; }
        .week-btn { padding: 10px 20px; background: white; border: 2px solid #e9ecef; border-radius: 10px; cursor: pointer; transition: all 0.3s; }
        .week-btn.active { background: #4361ee; color: white; border-color: #4361ee; }
        .timetable-grid { display: grid; grid-template-columns: 100px repeat(5, 1fr); gap: 1px; background: #e9ecef; border-radius: 15px; overflow: hidden; }
        .grid-cell { background: white; padding: 15px; min-height: 100px; }
        .grid-header { background: #f8f9fa; font-weight: 600; text-align: center; padding: 15px; }
        .time-slot { font-weight: 600; background: #f8f9fa; }
        .class-item { background: rgba(67,97,238,0.1); padding: 8px; border-radius: 8px; margin-bottom: 5px; font-size: 0.85rem; }
        .class-code { font-weight: 600; color: #4361ee; }
        .room { font-size: 0.75rem; color: #6c757d; }
        @media (max-width: 768px) { .timetable-grid { overflow-x: auto; display: block; } .grid-cell { min-width: 150px; } .sidebar { transform: translateX(-100%); } .sidebar.active { transform: translateX(0); } .main-content { margin-left: 0; } }
        .mobile-menu-btn { display: none; background: none; border: none; font-size: 1.5rem; cursor: pointer; }
        @media (max-width: 768px) { .mobile-menu-btn { display: block; } }
        .print-btn { background: rgba(255,255,255,0.2); border: 1px solid white; color: white; padding: 10px 20px; border-radius: 8px; cursor: pointer; display: flex; align-items: center; gap: 8px; }
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
                <a href="student-list.php"><i class="fas fa-users"></i><span> Student List</span></a>
                <a href="timetable.php" class="active"><i class="fas fa-clock"></i><span> Timetable</span></a>
                <a href="../logout.php"><i class="fas fa-sign-out-alt"></i><span> Logout</span></a>
            </nav>
        </aside>

        <main class="main-content" id="mainContent">
            <header class="top-header">
                <button class="mobile-menu-btn" id="mobileMenuBtn"><i class="fas fa-bars"></i></button>
                <div><h2>My Timetable</h2></div>
                <div class="user-profile">
                    <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($user['full_name']); ?>&background=4361ee&color=fff" alt="Profile">
                    <div><h4><?php echo htmlspecialchars($user['full_name']); ?></h4><p><?php echo htmlspecialchars($user['id_number']); ?></p></div>
                </div>
            </header>

            <div class="timetable-header">
                <div><h2>Weekly Schedule</h2><p>Semester 3 - Academic Year 2024-2025</p></div>
                <button class="print-btn" onclick="window.print()"><i class="fas fa-print"></i> Print Timetable</button>
            </div>

            <div class="week-selector">
                <button class="week-btn active" data-week="1">Week 1</button>
                <button class="week-btn" data-week="2">Week 2</button>
                <button class="week-btn" data-week="3">Week 3</button>
                <button class="week-btn" data-week="4">Week 4</button>
            </div>

            <div class="timetable-grid" id="timetableGrid"></div>

            <div style="margin-top: 30px; background: white; border-radius: 15px; padding: 20px;">
                <h4><i class="fas fa-info-circle"></i> Notes:</h4>
                <ul style="margin-top: 10px; padding-left: 20px; color: #6c757d;">
                    <li>Please report to class 5 minutes before scheduled time</li>
                    <li>Bring your ID card and teaching materials</li>
                    <li>Contact HOD for any timetable changes</li>
                </ul>
            </div>
        </main>
    </div>

    <script>
        const timetableData = {
            week1: {
                Monday: { '09:00-10:00': { subject: 'Data Structures', code: 'CS301', room: 'Room 101' }, '11:00-12:00': { subject: 'Database Management', code: 'CS302', room: 'Room 203' } },
                Tuesday: { '10:00-11:00': { subject: 'Web Development', code: 'CS303', room: 'Lab 3' } },
                Wednesday: { '09:00-10:00': { subject: 'Data Structures', code: 'CS301', room: 'Room 101' }, '14:00-15:00': { subject: 'Operating Systems', code: 'CS304', room: 'Room 105' } },
                Thursday: { '11:00-12:00': { subject: 'Database Management', code: 'CS302', room: 'Room 203' } },
                Friday: { '10:00-11:00': { subject: 'Web Development', code: 'CS303', room: 'Lab 3' }, '15:00-16:00': { subject: 'Faculty Meeting', code: 'MEET', room: 'Conference Hall' } }
            },
            week2: {
                Monday: { '09:00-10:00': { subject: 'Data Structures', code: 'CS301', room: 'Room 101' }, '13:00-14:00': { subject: 'Project Review', code: 'PRJ', room: 'Lab 2' } },
                Tuesday: { '10:00-11:00': { subject: 'Web Development', code: 'CS303', room: 'Lab 3' } },
                Wednesday: { '09:00-10:00': { subject: 'Data Structures', code: 'CS301', room: 'Room 101' } },
                Thursday: { '11:00-12:00': { subject: 'Database Management', code: 'CS302', room: 'Room 203' }, '14:00-15:00': { subject: 'Research Meeting', code: 'RES', room: 'Conference Hall' } },
                Friday: { '10:00-11:00': { subject: 'Web Development', code: 'CS303', room: 'Lab 3' } }
            }
        };
        
        const timeSlots = ['09:00-10:00', '10:00-11:00', '11:00-12:00', '12:00-13:00', '13:00-14:00', '14:00-15:00', '15:00-16:00'];
        const days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
        
        function loadTimetable(week) {
            const data = week === '1' ? timetableData.week1 : timetableData.week2;
            
            let gridHTML = `<div class="grid-cell time-slot"><strong>Time / Day</strong></div>`;
            days.forEach(day => { gridHTML += `<div class="grid-header">${day}</div>`; });
            
            timeSlots.forEach(slot => {
                gridHTML += `<div class="grid-cell time-slot">${slot}</div>`;
                days.forEach(day => {
                    let cellContent = '';
                    if(data[day] && data[day][slot]) {
                        const cls = data[day][slot];
                        cellContent = `<div class="class-item"><div class="class-code">${cls.code}</div><div>${cls.subject}</div><div class="room"><i class="fas fa-door-open"></i> ${cls.room}</div></div>`;
                    }
                    gridHTML += `<div class="grid-cell">${cellContent || '-'}</div>`;
                });
            });
            
            document.getElementById('timetableGrid').innerHTML = gridHTML;
        }
        
        document.querySelectorAll('.week-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                document.querySelectorAll('.week-btn').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                loadTimetable(this.dataset.week);
            });
        });
        
        const sidebar = document.getElementById('sidebar'), mainContent = document.getElementById('mainContent'), toggleBtn = document.getElementById('toggleSidebar'), mobileMenuBtn = document.getElementById('mobileMenuBtn');
        if(toggleBtn) toggleBtn.addEventListener('click', function() { sidebar.classList.toggle('collapsed'); mainContent.classList.toggle('expanded'); });
        if(mobileMenuBtn) mobileMenuBtn.addEventListener('click', function() { sidebar.classList.toggle('active'); });
        function handleResponsive() { if(window.innerWidth <= 768) { sidebar.classList.add('collapsed'); mainContent.classList.add('expanded'); } else { sidebar.classList.remove('collapsed', 'active'); mainContent.classList.remove('expanded'); } }
        window.addEventListener('resize', handleResponsive); handleResponsive();
        
        loadTimetable('1');
    </script>
</body>
</html>