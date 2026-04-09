<?php
require_once '../includes/config.php';

if (!isLoggedIn() || !hasRole('student')) {
    redirect('login.php');
}

$user = getUserData($conn, $_SESSION['user_id']);

// Get student details
$student_query = "SELECT * FROM students WHERE user_id = {$_SESSION['user_id']}";
$student_result = mysqli_query($conn, $student_query);
$student = mysqli_fetch_assoc($student_result);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Profile - EduTrack Pro</title>
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
        }
        
        .user-profile { display: flex; align-items: center; gap: 10px; cursor: pointer; }
        .user-profile img { width: 40px; height: 40px; border-radius: 50%; }
        
        .profile-container { max-width: 1200px; margin: 0 auto; }
        
        .profile-header {
            background: linear-gradient(135deg, #4361ee, #3f37c9);
            border-radius: 20px;
            padding: 40px;
            margin-bottom: 30px;
            color: white;
        }
        
        .profile-avatar {
            display: flex;
            align-items: center;
            gap: 30px;
        }
        
        .avatar-large {
            width: 120px;
            height: 120px;
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3.5rem;
            color: #4361ee;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        
        .profile-title h1 { font-size: 2rem; margin-bottom: 5px; }
        .profile-title p { opacity: 0.9; }
        
        .profile-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 30px;
            margin-bottom: 30px;
        }
        
        .profile-card {
            background: white;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.03);
        }
        
        .card-title {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 10px;
            border-bottom: 2px solid #e9ecef;
            padding-bottom: 15px;
        }
        .card-title i { color: #4361ee; }
        
        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }
        
        .info-item { margin-bottom: 15px; }
        .info-label {
            font-size: 0.85rem;
            color: #6c757d;
            margin-bottom: 5px;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        .info-value {
            font-size: 1rem;
            font-weight: 500;
            color: #1e293b;
            padding: 8px 12px;
            background: #f8f9fa;
            border-radius: 10px;
        }
        
        @media (max-width: 768px) {
            .profile-grid { grid-template-columns: 1fr; }
            .profile-avatar { flex-direction: column; text-align: center; }
            .sidebar { transform: translateX(-100%); }
            .sidebar.active { transform: translateX(0); }
            .main-content { margin-left: 0; }
            .info-grid { grid-template-columns: 1fr; }
        }
        
        .mobile-menu-btn {
            display: none;
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
        }
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
                <a href="dashboard.php"><i class="fas fa-home"></i><span> Dashboard</span></a>
                <a href="profile.php" class="active"><i class="fas fa-user"></i><span> Profile</span></a>
                <a href="attendance/view-attendance.php"><i class="fas fa-calendar-check"></i><span> Attendance</span></a>
                <a href="academics/academic-timeline.php"><i class="fas fa-timeline"></i><span> Academic Timeline</span></a>
                <a href="requests/leave-apply.php"><i class="fas fa-calendar-plus"></i><span> Apply Leave</span></a>
                <a href="requests/grievance.php"><i class="fas fa-exclamation-circle"></i><span> Grievance</span></a>
                <a href="resources/notices.php"><i class="fas fa-bell"></i><span> Notices</span></a>
                <a href="../logout.php"><i class="fas fa-sign-out-alt"></i><span> Logout</span></a>
            </nav>
        </aside>

        <main class="main-content" id="mainContent">
            <header class="top-header">
                <button class="mobile-menu-btn" id="mobileMenuBtn"><i class="fas fa-bars"></i></button>
                <div><h2>My Profile</h2></div>
                <div class="user-profile">
                    <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($user['full_name']); ?>&background=4361ee&color=fff" alt="Profile">
                    <div><h4><?php echo htmlspecialchars($user['full_name']); ?></h4><p><?php echo htmlspecialchars($user['id_number']); ?></p></div>
                </div>
            </header>

            <div class="profile-container">
                <div class="profile-header">
                    <div class="profile-avatar">
                        <div class="avatar-large"><i class="fas fa-user-graduate"></i></div>
                        <div class="profile-title">
                            <h1><?php echo htmlspecialchars($user['full_name']); ?></h1>
                            <p><?php echo htmlspecialchars($student['course']); ?> - Semester <?php echo $student['semester']; ?> | Roll No: <?php echo htmlspecialchars($student['roll_number']); ?></p>
                        </div>
                    </div>
                </div>

                <div class="profile-grid">
                    <div class="profile-card">
                        <div class="card-title"><i class="fas fa-user"></i> Personal Information</div>
                        <div class="info-grid">
                            <div class="info-item"><div class="info-label"><i class="fas fa-user"></i> Full Name</div><div class="info-value"><?php echo htmlspecialchars($user['full_name']); ?></div></div>
                            <div class="info-item"><div class="info-label"><i class="fas fa-id-card"></i> Roll Number</div><div class="info-value"><?php echo htmlspecialchars($student['roll_number']); ?></div></div>
                            <div class="info-item"><div class="info-label"><i class="fas fa-envelope"></i> Email</div><div class="info-value"><?php echo htmlspecialchars($user['email']); ?></div></div>
                            <div class="info-item"><div class="info-label"><i class="fas fa-phone"></i> Phone</div><div class="info-value"><?php echo htmlspecialchars($user['phone']); ?></div></div>
                            <div class="info-item"><div class="info-label"><i class="fas fa-calendar"></i> Date of Birth</div><div class="info-value"><?php echo $student['date_of_birth'] ? date('d M Y', strtotime($student['date_of_birth'])) : 'N/A'; ?></div></div>
                            <div class="info-item"><div class="info-label"><i class="fas fa-venus-mars"></i> Gender</div><div class="info-value"><?php echo $student['gender']; ?></div></div>
                            <div class="info-item"><div class="info-label"><i class="fas fa-map-marker-alt"></i> Address</div><div class="info-value"><?php echo htmlspecialchars($user['address']); ?></div></div>
                        </div>
                    </div>

                    <div class="profile-card">
                        <div class="card-title"><i class="fas fa-graduation-cap"></i> Academic Information</div>
                        <div class="info-item"><div class="info-label">Course</div><div class="info-value"><?php echo htmlspecialchars($student['course']); ?></div></div>
                        <div class="info-item"><div class="info-label">Current Semester</div><div class="info-value">Semester <?php echo $student['semester']; ?></div></div>
                        <div class="info-item"><div class="info-label">Enrollment Number</div><div class="info-value"><?php echo htmlspecialchars($student['enrollment_number']); ?></div></div>
                        <div class="info-item"><div class="info-label">Batch</div><div class="info-value"><?php echo htmlspecialchars($student['batch']); ?></div></div>
                        <div class="info-item"><div class="info-label">CGPA</div><div class="info-value"><?php echo $student['cgpa']; ?></div></div>
                        <div class="info-item"><div class="info-label">Admission Date</div><div class="info-value"><?php echo date('d M Y', strtotime($student['admission_date'])); ?></div></div>
                    </div>
                </div>
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