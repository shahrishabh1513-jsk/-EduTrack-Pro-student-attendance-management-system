<?php
require_once '../../includes/config.php';

if (!isLoggedIn() || !hasRole('student')) {
    redirect('login.php');
}

$user = getUserData($conn, $_SESSION['user_id']);

// Get student id
$student_query = "SELECT id FROM students WHERE user_id = {$_SESSION['user_id']}";
$student_result = mysqli_query($conn, $student_query);
$student = mysqli_fetch_assoc($student_result);
$student_id = $student['id'];

// Fetch leave history
$query = "SELECT * FROM leave_applications WHERE student_id = $student_id ORDER BY applied_at DESC";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leave Status - EduTrack Pro</title>
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
        .leave-item { background: white; border-radius: 15px; padding: 20px; margin-bottom: 15px; border-left: 4px solid; }
        .status-pending { border-left-color: #ffc107; }
        .status-approved { border-left-color: #4cc9f0; }
        .status-rejected { border-left-color: #f72585; }
        .leave-header { display: flex; justify-content: space-between; margin-bottom: 10px; }
        .leave-type { font-weight: 600; }
        .leave-dates { color: #6c757d; margin-bottom: 10px; }
        .leave-reason { margin-bottom: 10px; }
        @media (max-width: 768px) { .sidebar { transform: translateX(-100%); } .sidebar.active { transform: translateX(0); } .main-content { margin-left: 0; } }
        .mobile-menu-btn { display: none; background: none; border: none; font-size: 1.5rem; cursor: pointer; }
        @media (max-width: 768px) { .mobile-menu-btn { display: block; } }
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
                <a href="../attendance/view-attendance.php"><i class="fas fa-calendar-check"></i><span> Attendance</span></a>
                <a href="leave-apply.php"><i class="fas fa-calendar-plus"></i><span> Apply Leave</span></a>
                <a href="leave-status.php" class="active"><i class="fas fa-calendar-check"></i><span> Leave Status</span></a>
                <a href="grievance.php"><i class="fas fa-exclamation-circle"></i><span> Grievance</span></a>
                <a href="../logout.php"><i class="fas fa-sign-out-alt"></i><span> Logout</span></a>
            </nav>
        </aside>

        <main class="main-content" id="mainContent">
            <header class="top-header">
                <button class="mobile-menu-btn" id="mobileMenuBtn"><i class="fas fa-bars"></i></button>
                <div><h2>Leave Status</h2></div>
                <div class="user-profile">
                    <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($user['full_name']); ?>&background=4361ee&color=fff" alt="Profile">
                    <div><h4><?php echo htmlspecialchars($user['full_name']); ?></h4><p><?php echo htmlspecialchars($user['id_number']); ?></p></div>
                </div>
            </header>

            <div>
                <?php if(mysqli_num_rows($result) > 0): ?>
                    <?php while($leave = mysqli_fetch_assoc($result)): ?>
                        <div class="leave-item status-<?php echo $leave['status']; ?>">
                            <div class="leave-header">
                                <span class="leave-type"><?php echo ucfirst($leave['leave_type']); ?> Leave</span>
                                <span style="font-weight: 600;"><?php echo strtoupper($leave['status']); ?></span>
                            </div>
                            <div class="leave-dates">📅 <?php echo date('d M Y', strtotime($leave['from_date'])); ?> - <?php echo date('d M Y', strtotime($leave['to_date'])); ?></div>
                            <div class="leave-reason">📝 <?php echo htmlspecialchars($leave['reason']); ?></div>
                            <div class="leave-dates">📅 Applied: <?php echo date('d M Y', strtotime($leave['applied_at'])); ?></div>
                            <?php if($leave['remarks']): ?>
                                <div class="leave-dates">💬 Remarks: <?php echo htmlspecialchars($leave['remarks']); ?></div>
                            <?php endif; ?>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div style="background: white; border-radius: 15px; padding: 40px; text-align: center;">
                        <i class="fas fa-calendar-times" style="font-size: 3rem; color: #6c757d;"></i>
                        <p style="margin-top: 15px;">No leave applications found.</p>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <script>
        const sidebar = document.getElementById('sidebar'), mainContent = document.getElementById('mainContent'), toggleBtn = document.getElementById('toggleSidebar'), mobileMenuBtn = document.getElementById('mobileMenuBtn');
        if(toggleBtn) toggleBtn.addEventListener('click', function() { sidebar.classList.toggle('collapsed'); mainContent.classList.toggle('expanded'); });
        if(mobileMenuBtn) mobileMenuBtn.addEventListener('click', function() { sidebar.classList.toggle('active'); });
        function handleResponsive() { if(window.innerWidth <= 768) { sidebar.classList.add('collapsed'); mainContent.classList.add('expanded'); } else { sidebar.classList.remove('collapsed', 'active'); mainContent.classList.remove('expanded'); } }
        window.addEventListener('resize', handleResponsive); handleResponsive();
    </script>
</body>
</html>