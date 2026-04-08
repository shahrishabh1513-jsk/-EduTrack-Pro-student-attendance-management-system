<?php
require_once '../includes/config.php';

if (!isLoggedIn() || !hasRole('faculty')) {
    redirect('login.php');
}

$user = getUserData($conn, $_SESSION['user_id']);

// Get faculty statistics
$faculty_query = "SELECT id FROM faculty WHERE user_id = {$_SESSION['user_id']}";
$faculty_result = mysqli_query($conn, $faculty_query);
$faculty = mysqli_fetch_assoc($faculty_result);
$faculty_id = $faculty['id'];

// Get total students count
$students_query = "SELECT COUNT(DISTINCT sc.student_id) as total 
                   FROM student_courses sc 
                   JOIN course_assignments ca ON sc.course_id = ca.course_id 
                   WHERE ca.faculty_id = $faculty_id AND sc.semester = 3";
$students_result = mysqli_query($conn, $students_query);
$total_students = mysqli_fetch_assoc($students_result)['total'];

// Get today's classes count
$today_classes_query = "SELECT COUNT(*) as total FROM course_assignments 
                        WHERE faculty_id = $faculty_id AND semester = 3";
$classes_result = mysqli_query($conn, $today_classes_query);
$today_classes = mysqli_fetch_assoc($classes_result)['total'];

// Get pending leave requests count
$pending_leaves_query = "SELECT COUNT(*) as total FROM leave_applications WHERE status = 'pending'";
$pending_result = mysqli_query($conn, $pending_leaves_query);
$pending_leaves = mysqli_fetch_assoc($pending_result)['total'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Faculty Dashboard - EduTrack Pro</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Poppins', sans-serif; background: #f8f9fa; }
        .dashboard-container { display: flex; min-height: 100vh; }
        .sidebar { width: 280px; background: white; position: fixed; height: 100vh; overflow-y: auto; box-shadow: 2px 0 10px rgba(0,0,0,0.05); transition: all 0.3s; z-index: 100; }
        .sidebar.collapsed { width: 80px; }
        .sidebar.collapsed .sidebar-nav a span, .sidebar.collapsed .sidebar-header h3 { display: none; }
        .sidebar.collapsed .sidebar-nav a { justify-content: center; padding: 15px; }
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
        .welcome-banner { background: linear-gradient(135deg, #4361ee, #3f37c9); padding: 30px; border-radius: 20px; color: white; margin-bottom: 30px; display: flex; justify-content: space-between; align-items: center; }
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 25px; margin-bottom: 30px; }
        .stat-card { background: white; padding: 25px; border-radius: 20px; display: flex; justify-content: space-between; align-items: center; transition: all 0.3s; cursor: pointer; }
        .stat-card:hover { transform: translateY(-5px); box-shadow: 0 10px 25px rgba(0,0,0,0.07); }
        .stat-number { font-size: 2rem; font-weight: 700; color: #1e293b; }
        .stat-icon { width: 60px; height: 60px; border-radius: 15px; display: flex; align-items: center; justify-content: center; font-size: 2rem; }
        .stat-icon.blue { background: rgba(67,97,238,0.1); color: #4361ee; }
        .stat-icon.green { background: rgba(76,201,240,0.1); color: #4cc9f0; }
        .stat-icon.orange { background: rgba(247,37,133,0.1); color: #f72585; }
        .content-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 25px; }
        .content-card { background: white; border-radius: 20px; padding: 25px; }
        .card-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; padding-bottom: 15px; border-bottom: 1px solid #e9ecef; }
        .card-header a { color: #4361ee; text-decoration: none; }
        .schedule-item { display: flex; align-items: center; justify-content: space-between; padding: 15px; border: 1px solid #e9ecef; border-radius: 15px; margin-bottom: 10px; }
        .schedule-time { font-weight: 600; color: #4361ee; min-width: 100px; }
        .take-attendance-btn { background: #4361ee; color: white; border: none; padding: 8px 15px; border-radius: 8px; cursor: pointer; }
        .request-item { display: flex; justify-content: space-between; align-items: center; padding: 15px 0; border-bottom: 1px solid #e9ecef; }
        .approve-btn, .reject-btn { padding: 5px 15px; border: none; border-radius: 5px; cursor: pointer; margin: 0 5px; }
        .approve-btn { background: rgba(76,201,240,0.1); color: #4cc9f0; }
        .reject-btn { background: rgba(247,37,133,0.1); color: #f72585; }
        @media (max-width: 768px) { .sidebar { transform: translateX(-100%); } .sidebar.active { transform: translateX(0); } .main-content { margin-left: 0; } .content-grid { grid-template-columns: 1fr; } }
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
                <a href="dashboard.php" class="active"><i class="fas fa-home"></i><span> Dashboard</span></a>
                <a href="profile.php"><i class="fas fa-user"></i><span> Profile</span></a>
                <a href="attendance/mark-attendance.php"><i class="fas fa-calendar-check"></i><span> Mark Attendance</span></a>
                <a href="attendance/view-attendance.php"><i class="fas fa-eye"></i><span> View Attendance</span></a>
                <a href="attendance/attendance-report.php"><i class="fas fa-chart-bar"></i><span> Attendance Report</span></a>
                <a href="attendance/qr-attendance.php"><i class="fas fa-qrcode"></i><span> QR Attendance</span></a>
                <a href="classes/my-classes.php"><i class="fas fa-chalkboard"></i><span> My Classes</span></a>
                <a href="classes/student-list.php"><i class="fas fa-users"></i><span> Student List</span></a>
                <a href="classes/timetable.php"><i class="fas fa-clock"></i><span> Timetable</span></a>
                <a href="exams/exam-verification.php"><i class="fas fa-file-alt"></i><span> Exam Verification</span></a>
                <a href="exams/marks-entry.php"><i class="fas fa-pen"></i><span> Marks Entry</span></a>
                <a href="requests/leave-requests.php"><i class="fas fa-calendar-plus"></i><span> Leave Requests</span></a>
                <a href="requests/grievance-review.php"><i class="fas fa-exclamation-circle"></i><span> Grievance Review</span></a>
                <a href="resources/upload-material.php"><i class="fas fa-upload"></i><span> Upload Material</span></a>
                <a href="resources/repository.php"><i class="fas fa-folder"></i><span> Repository</span></a>
                <a href="resources/notices.php"><i class="fas fa-bell"></i><span> Notices</span></a>
                <a href="resources/feedback-view.php"><i class="fas fa-star"></i><span> Feedback</span></a>
                <a href="../logout.php"><i class="fas fa-sign-out-alt"></i><span> Logout</span></a>
            </nav>
        </aside>

        <main class="main-content" id="mainContent">
            <header class="top-header">
                <button class="mobile-menu-btn" id="mobileMenuBtn"><i class="fas fa-bars"></i></button>
                <div><h2>Faculty Dashboard</h2></div>
                <div class="user-profile">
                    <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($user['full_name']); ?>&background=4361ee&color=fff" alt="Profile">
                    <div><h4><?php echo htmlspecialchars($user['full_name']); ?></h4><p><?php echo htmlspecialchars($user['id_number']); ?></p></div>
                </div>
            </header>

            <div class="welcome-banner">
                <div><h1>Welcome, <?php echo explode(' ', $user['full_name'])[0]; ?>! 👋</h1><p>Here's your teaching overview for today</p></div>
                <div><i class="fas fa-chalkboard-teacher" style="font-size: 5rem; opacity: 0.3;"></i></div>
            </div>

            <div class="stats-grid">
                <div class="stat-card" onclick="window.location.href='classes/student-list.php'">
                    <div><h3>Total Students</h3><div class="stat-number"><?php echo $total_students ?: 156; ?></div><small>Across all classes</small></div>
                    <div class="stat-icon blue"><i class="fas fa-users"></i></div>
                </div>
                <div class="stat-card" onclick="window.location.href='classes/timetable.php'">
                    <div><h3>Today's Classes</h3><div class="stat-number"><?php echo $today_classes ?: 4; ?></div><small>Schedule for today</small></div>
                    <div class="stat-icon green"><i class="fas fa-clock"></i></div>
                </div>
                <div class="stat-card" onclick="window.location.href='requests/leave-requests.php'">
                    <div><h3>Pending Leave</h3><div class="stat-number" id="pendingCount"><?php echo $pending_leaves; ?></div><small>Require approval</small></div>
                    <div class="stat-icon orange"><i class="fas fa-calendar-times"></i></div>
                </div>
            </div>

            <div class="content-grid">
                <div class="content-card">
                    <div class="card-header"><h3><i class="fas fa-clock"></i> Today's Schedule</h3><a href="classes/timetable.php">View Full Schedule</a></div>
                    <div id="todaySchedule"></div>
                </div>
                <div class="content-card">
                    <div class="card-header"><h3><i class="fas fa-calendar-alt"></i> Pending Leave Requests</h3><a href="requests/leave-requests.php">View All</a></div>
                    <div id="pendingLeaves"></div>
                </div>
            </div>
        </main>
    </div>

    <script>
        const todaySchedule = [
            { time: '09:00 - 10:00', subject: 'Data Structures', class: 'BSC IT Sem 3', students: 45 },
            { time: '11:00 - 12:00', subject: 'Database Management', class: 'BSC IT Sem 5', students: 38 },
            { time: '14:00 - 15:00', subject: 'Web Development', class: 'BSC IT Sem 3', students: 45 }
        ];
        
        function loadSchedule() {
            const scheduleHTML = todaySchedule.map(item => `
                <div class="schedule-item">
                    <div class="schedule-time">${item.time}</div>
                    <div><strong>${item.subject}</strong><br><small>${item.class} • ${item.students} students</small></div>
                    <button class="take-attendance-btn" onclick="window.location.href='attendance/mark-attendance.php?subject=${encodeURIComponent(item.subject)}'">Take Attendance</button>
                </div>
            `).join('');
            document.getElementById('todaySchedule').innerHTML = scheduleHTML;
        }
        
        async function loadPendingLeaves() {
            try {
                const response = await fetch('../api/leave.php?action=get_pending');
                const data = await response.json();
                if(data.success && data.data.length > 0) {
                    const leavesHTML = data.data.slice(0, 3).map(leave => `
                        <div class="request-item">
                            <div><strong>${leave.student_name}</strong><br><small>${leave.leave_type} • ${new Date(leave.from_date).toLocaleDateString()}</small></div>
                            <div><button class="approve-btn" onclick="approveLeave(${leave.id})">Approve</button><button class="reject-btn" onclick="rejectLeave(${leave.id})">Reject</button></div>
                        </div>
                    `).join('');
                    document.getElementById('pendingLeaves').innerHTML = leavesHTML;
                } else {
                    document.getElementById('pendingLeaves').innerHTML = '<p>No pending leave requests.</p>';
                }
            } catch(error) { console.error('Error:', error); }
        }
        
        async function approveLeave(id) {
            try {
                const response = await fetch('../api/leave.php?action=approve', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ leave_id: id, status: 'approved' })
                });
                const data = await response.json();
                if(data.success) { showNotification('Leave approved successfully', 'success'); loadPendingLeaves(); }
            } catch(error) { showNotification('Error approving leave', 'error'); }
        }
        
        async function rejectLeave(id) {
            try {
                const response = await fetch('../api/leave.php?action=approve', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ leave_id: id, status: 'rejected', remarks: 'Not approved' })
                });
                const data = await response.json();
                if(data.success) { showNotification('Leave rejected', 'success'); loadPendingLeaves(); }
            } catch(error) { showNotification('Error rejecting leave', 'error'); }
        }
        
        function showNotification(message, type) {
            const notification = document.createElement('div');
            notification.className = 'notification';
            notification.style.background = type === 'success' ? '#4cc9f0' : '#f72585';
            notification.innerHTML = `<i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i> ${message}`;
            document.body.appendChild(notification);
            setTimeout(() => notification.remove(), 3000);
        }
        
        const sidebar = document.getElementById('sidebar'), mainContent = document.getElementById('mainContent'), toggleBtn = document.getElementById('toggleSidebar'), mobileMenuBtn = document.getElementById('mobileMenuBtn');
        if(toggleBtn) toggleBtn.addEventListener('click', function() { sidebar.classList.toggle('collapsed'); mainContent.classList.toggle('expanded'); });
        if(mobileMenuBtn) mobileMenuBtn.addEventListener('click', function() { sidebar.classList.toggle('active'); });
        function handleResponsive() { if(window.innerWidth <= 768) { sidebar.classList.add('collapsed'); mainContent.classList.add('expanded'); } else { sidebar.classList.remove('collapsed', 'active'); mainContent.classList.remove('expanded'); } }
        window.addEventListener('resize', handleResponsive); handleResponsive();
        loadSchedule();
        loadPendingLeaves();
    </script>
</body>
</html>