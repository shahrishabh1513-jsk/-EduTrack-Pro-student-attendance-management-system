<?php
require_once '../../../includes/config.php';

if (!isLoggedIn() || !hasRole('admin')) {
    redirect('login.php');
}

$user = getUserData($conn, $_SESSION['user_id']);
$faculty_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch faculty data
$query = "SELECT f.*, u.full_name, u.email, u.phone, u.username, u.address 
          FROM faculty f 
          JOIN users u ON f.user_id = u.id 
          WHERE f.id = $faculty_id";
$result = mysqli_query($conn, $query);
$faculty = mysqli_fetch_assoc($result);

if (!$faculty) {
    redirect('faculty-list.php');
}

// Fetch assigned courses
$courses_query = "SELECT c.course_code, c.course_name, c.credits, ca.semester, ca.academic_year 
                  FROM course_assignments ca 
                  JOIN courses c ON ca.course_id = c.id 
                  WHERE ca.faculty_id = $faculty_id";
$courses_result = mysqli_query($conn, $courses_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Faculty Detail - EduTrack Pro</title>
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
        .profile-header { background: linear-gradient(135deg, #4361ee, #3f37c9); border-radius: 20px; padding: 30px; margin-bottom: 30px; color: white; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 20px; }
        .faculty-info h1 { font-size: 1.8rem; margin-bottom: 5px; }
        .action-buttons { display: flex; gap: 10px; }
        .action-btn { background: rgba(255,255,255,0.2); border: 1px solid white; color: white; padding: 10px 20px; border-radius: 8px; cursor: pointer; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; }
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: white; padding: 20px; border-radius: 15px; text-align: center; }
        .stat-value { font-size: 2rem; font-weight: 700; color: #4361ee; }
        .info-card { background: white; border-radius: 20px; padding: 25px; margin-bottom: 30px; }
        .card-title { font-size: 1.2rem; font-weight: 600; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; border-bottom: 2px solid #e9ecef; padding-bottom: 15px; }
        .info-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px; }
        .info-item .label { font-size: 0.8rem; color: #6c757d; }
        .info-item .value { font-weight: 500; }
        .courses-table { width: 100%; border-collapse: collapse; }
        .courses-table th, .courses-table td { padding: 12px; text-align: left; border-bottom: 1px solid #e9ecef; }
        .courses-table th { background: #f8f9fa; font-weight: 600; }
        @media (max-width: 768px) { .sidebar { transform: translateX(-100%); } .sidebar.active { transform: translateX(0); } .main-content { margin-left: 0; } .info-grid { grid-template-columns: 1fr; } }
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
                <a href="../../dashboard.php"><i class="fas fa-home"></i><span> Dashboard</span></a>
                <a href="../../profile.php"><i class="fas fa-user"></i><span> Profile</span></a>
                <a href="faculty-list.php" class="active"><i class="fas fa-chalkboard-teacher"></i><span> Faculty Management</span></a>
                <a href="../../../logout.php"><i class="fas fa-sign-out-alt"></i><span> Logout</span></a>
            </nav>
        </aside>

        <main class="main-content" id="mainContent">
            <header class="top-header">
                <button class="mobile-menu-btn" id="mobileMenuBtn"><i class="fas fa-bars"></i></button>
                <div><h2>Faculty Details</h2></div>
                <div class="user-profile">
                    <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($user['full_name']); ?>&background=4361ee&color=fff" alt="Profile">
                    <div><h4><?php echo htmlspecialchars($user['full_name']); ?></h4><p>Administrator</p></div>
                </div>
            </header>

            <div class="profile-header">
                <div class="faculty-info"><h1><?php echo htmlspecialchars($faculty['full_name']); ?></h1><p><i class="fas fa-id-card"></i> <?php echo htmlspecialchars($faculty['employee_id']); ?> • <?php echo htmlspecialchars($faculty['designation']); ?> • <?php echo htmlspecialchars($faculty['department']); ?></p></div>
                <div class="action-buttons"><a href="edit-faculty.php?id=<?php echo $faculty['id']; ?>" class="action-btn"><i class="fas fa-edit"></i> Edit</a></div>
            </div>

            <div class="stats-grid">
                <div class="stat-card"><div class="stat-value"><?php echo $faculty['experience']; ?></div><div>Years Experience</div></div>
                <div class="stat-card"><div class="stat-value"><?php echo mysqli_num_rows($courses_result); ?></div><div>Courses Assigned</div></div>
            </div>

            <div class="info-card"><div class="card-title"><i class="fas fa-user"></i> Personal Information</div><div class="info-grid"><div class="info-item"><div class="label">Full Name</div><div class="value"><?php echo htmlspecialchars($faculty['full_name']); ?></div></div><div class="info-item"><div class="label">Employee ID</div><div class="value"><?php echo htmlspecialchars($faculty['employee_id']); ?></div></div><div class="info-item"><div class="label">Email</div><div class="value"><?php echo htmlspecialchars($faculty['email']); ?></div></div><div class="info-item"><div class="label">Phone</div><div class="value"><?php echo htmlspecialchars($faculty['phone']); ?></div></div><div class="info-item"><div class="label">Date of Birth</div><div class="value"><?php echo $faculty['date_of_birth'] ? date('F j, Y', strtotime($faculty['date_of_birth'])) : 'N/A'; ?></div></div><div class="info-item"><div class="label">Gender</div><div class="value"><?php echo $faculty['gender']; ?></div></div><div class="info-item"><div class="label">Address</div><div class="value"><?php echo htmlspecialchars($faculty['address']); ?></div></div></div></div>

            <div class="info-card"><div class="card-title"><i class="fas fa-briefcase"></i> Professional Information</div><div class="info-grid"><div class="info-item"><div class="label">Department</div><div class="value"><?php echo htmlspecialchars($faculty['department']); ?></div></div><div class="info-item"><div class="label">Designation</div><div class="value"><?php echo htmlspecialchars($faculty['designation']); ?></div></div><div class="info-item"><div class="label">Qualification</div><div class="value"><?php echo htmlspecialchars($faculty['qualification']); ?></div></div><div class="info-item"><div class="label">Specialization</div><div class="value"><?php echo htmlspecialchars($faculty['specialization']); ?></div></div><div class="info-item"><div class="label">Experience</div><div class="value"><?php echo $faculty['experience']; ?> Years</div></div><div class="info-item"><div class="label">Joining Date</div><div class="value"><?php echo $faculty['joining_date'] ? date('F j, Y', strtotime($faculty['joining_date'])) : 'N/A'; ?></div></div></div></div>

            <div class="info-card"><div class="card-title"><i class="fas fa-book"></i> Assigned Courses</div><table class="courses-table"><thead><tr><th>Course Code</th><th>Course Name</th><th>Credits</th><th>Semester</th><th>Academic Year</th></tr></thead><tbody><?php while($course = mysqli_fetch_assoc($courses_result)): ?><tr><td><strong><?php echo $course['course_code']; ?></strong></td><td><?php echo $course['course_name']; ?></td><td><?php echo $course['credits']; ?></td><td><?php echo $course['semester']; ?></td><td><?php echo $course['academic_year']; ?></td></tr><?php endwhile; ?></tbody></table></div>
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