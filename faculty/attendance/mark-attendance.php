<?php
require_once '../../includes/config.php';

if (!isLoggedIn() || !hasRole('faculty')) {
    redirect('login.php');
}

$user = getUserData($conn, $_SESSION['user_id']);
$subject = $_GET['subject'] ?? 'Data Structures';

// Get course ID
$course_query = "SELECT id FROM courses WHERE course_name LIKE '%$subject%' LIMIT 1";
$course_result = mysqli_query($conn, $course_query);
$course = mysqli_fetch_assoc($course_result);
$course_id = $course['id'] ?? 1;

// Get students for this course
$students_query = "SELECT s.id, s.roll_number, u.full_name 
                   FROM students s 
                   JOIN users u ON s.user_id = u.id 
                   JOIN student_courses sc ON s.id = sc.student_id 
                   WHERE sc.course_id = $course_id AND sc.status = 'enrolled'";
$students_result = mysqli_query($conn, $students_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mark Attendance - EduTrack Pro</title>
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
        .toggle-sidebar { background: none; border: none; cursor: pointer; font-size: 1.2rem; color: #6c757d; }
        .sidebar-nav { padding: 20px 0; }
        .sidebar-nav a { display: flex; align-items: center; gap: 15px; padding: 12px 20px; color: #6c757d; text-decoration: none; transition: all 0.3s; }
        .sidebar-nav a:hover, .sidebar-nav a.active { background: rgba(67,97,238,0.05); color: #4361ee; border-left: 4px solid #4361ee; }
        .main-content { flex: 1; margin-left: 280px; padding: 20px 30px; transition: all 0.3s; }
        .top-header { background: white; padding: 15px 25px; border-radius: 15px; display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        .user-profile { display: flex; align-items: center; gap: 10px; cursor: pointer; }
        .user-profile img { width: 40px; height: 40px; border-radius: 50%; }
        .attendance-form { background: white; border-radius: 20px; padding: 25px; overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #e9ecef; }
        th { background: #f8f9fa; font-weight: 600; }
        .status-select { padding: 8px; border-radius: 8px; border: 2px solid #e9ecef; font-family: 'Poppins', sans-serif; }
        .btn-submit { background: linear-gradient(135deg, #4361ee, #3f37c9); color: white; padding: 14px 30px; border: none; border-radius: 10px; font-size: 1rem; font-weight: 600; cursor: pointer; margin-top: 20px; }
        .present { color: #4cc9f0; }
        .absent { color: #f72585; }
        .late { color: #ffc107; }
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
                <a href="mark-attendance.php" class="active"><i class="fas fa-calendar-check"></i><span> Mark Attendance</span></a>
                <a href="view-attendance.php"><i class="fas fa-eye"></i><span> View Attendance</span></a>
                <a href="../logout.php"><i class="fas fa-sign-out-alt"></i><span> Logout</span></a>
            </nav>
        </aside>

        <main class="main-content" id="mainContent">
            <header class="top-header">
                <button class="mobile-menu-btn" id="mobileMenuBtn"><i class="fas fa-bars"></i></button>
                <div><h2>Mark Attendance - <?php echo htmlspecialchars($subject); ?></h2></div>
                <div class="user-profile">
                    <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($user['full_name']); ?>&background=4361ee&color=fff" alt="Profile">
                    <div><h4><?php echo htmlspecialchars($user['full_name']); ?></h4><p><?php echo htmlspecialchars($user['id_number']); ?></p></div>
                </div>
            </header>

            <div class="attendance-form">
                <div style="margin-bottom: 20px;">
                    <label>Date: </label>
                    <input type="date" id="attendanceDate" value="<?php echo date('Y-m-d'); ?>" style="padding: 8px; border-radius: 8px; border: 2px solid #e9ecef;">
                </div>
                
                <form id="attendanceForm">
                    <table>
                        <thead>
                            <tr><th>Roll Number</th><th>Student Name</th><th>Status</th><th>Remarks</th></tr>
                        </thead>
                        <tbody>
                            <?php while($student = mysqli_fetch_assoc($students_result)): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($student['roll_number']); ?></td>
                                <td><?php echo htmlspecialchars($student['full_name']); ?></td>
                                <td>
                                    <select name="status[<?php echo $student['id']; ?>]" class="status-select">
                                        <option value="present">Present</option>
                                        <option value="absent">Absent</option>
                                        <option value="late">Late</option>
                                    </select>
                                </td>
                                <td><input type="text" name="remarks[<?php echo $student['id']; ?>]" placeholder="Optional remarks" style="padding: 8px; border-radius: 8px; border: 2px solid #e9ecef; width: 100%;"></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                    <button type="submit" class="btn-submit">Submit Attendance</button>
                </form>
            </div>
        </main>
    </div>

    <script>
        document.getElementById('attendanceForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            const date = document.getElementById('attendanceDate').value;
            const formData = new FormData(this);
            const attendances = [];
            
            for(let [key, value] of formData.entries()) {
                if(key.startsWith('status[')) {
                    const studentId = key.match(/\d+/)[0];
                    attendances.push({ student_id: studentId, status: value });
                }
            }
            
            try {
                const response = await fetch('../../api/attendance.php?action=mark', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ course_id: <?php echo $course_id; ?>, date: date, attendances: attendances })
                });
                const data = await response.json();
                if(data.success) { showNotification('Attendance marked successfully!', 'success'); } 
                else { showNotification(data.message, 'error'); }
            } catch(error) { showNotification('Error marking attendance', 'error'); }
        });
        
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
    </script>
</body>
</html>