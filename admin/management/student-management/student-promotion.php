<?php
require_once '../../../includes/config.php';

if (!isLoggedIn() || !hasRole('admin')) {
    redirect('login.php');
}

$user = getUserData($conn, $_SESSION['user_id']);
$student_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch student data
$query = "SELECT s.*, u.full_name FROM students s JOIN users u ON s.user_id = u.id WHERE s.id = $student_id";
$result = mysqli_query($conn, $query);
$student = mysqli_fetch_assoc($result);

if (!$student) {
    redirect('students-list.php');
}

$current_semester = $student['semester'];
$next_semester = $current_semester + 1;
$max_semester = 6;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $new_semester = (int)$_POST['new_semester'];
    $update_query = "UPDATE students SET semester = $new_semester WHERE id = $student_id";
    if (mysqli_query($conn, $update_query)) {
        $success = "Student promoted to Semester $new_semester successfully!";
        $student['semester'] = $new_semester;
        $current_semester = $new_semester;
        $next_semester = $current_semester + 1;
    } else {
        $error = "Error promoting student";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Promotion - EduTrack Pro</title>
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
        .promotion-container { max-width: 600px; margin: 0 auto; }
        .promotion-card { background: white; border-radius: 20px; padding: 30px; text-align: center; }
        .student-info { background: linear-gradient(135deg, #4361ee, #3f37c9); color: white; padding: 25px; border-radius: 15px; margin-bottom: 25px; }
        .semester-display { display: flex; justify-content: center; align-items: center; gap: 30px; margin: 30px 0; }
        .current-semester, .next-semester { text-align: center; padding: 20px; border-radius: 15px; }
        .current-semester { background: #e9ecef; }
        .next-semester { background: rgba(67,97,238,0.1); border: 2px solid #4361ee; }
        .semester-number { font-size: 3rem; font-weight: 700; }
        .arrow-icon { font-size: 2rem; color: #4361ee; }
        .btn-promote { background: linear-gradient(135deg, #4361ee, #3f37c9); color: white; border: none; padding: 14px 30px; border-radius: 10px; font-size: 1rem; font-weight: 600; cursor: pointer; width: 100%; }
        .btn-back { background: #e9ecef; color: #1e293b; border: none; padding: 14px 30px; border-radius: 10px; font-size: 1rem; font-weight: 600; cursor: pointer; width: 100%; margin-top: 10px; text-decoration: none; display: inline-block; text-align: center; }
        .alert-success { background: rgba(76,201,240,0.1); color: #4cc9f0; padding: 12px; border-radius: 10px; margin-bottom: 20px; }
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
                <a href="../../dashboard.php"><i class="fas fa-home"></i><span> Dashboard</span></a>
                <a href="../../profile.php"><i class="fas fa-user"></i><span> Profile</span></a>
                <a href="students-list.php" class="active"><i class="fas fa-user-graduate"></i><span> Student Management</span></a>
                <a href="../../../logout.php"><i class="fas fa-sign-out-alt"></i><span> Logout</span></a>
            </nav>
        </aside>

        <main class="main-content" id="mainContent">
            <header class="top-header">
                <button class="mobile-menu-btn" id="mobileMenuBtn"><i class="fas fa-bars"></i></button>
                <div><h2>Student Promotion</h2></div>
                <div class="user-profile">
                    <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($user['full_name']); ?>&background=4361ee&color=fff" alt="Profile">
                    <div><h4><?php echo htmlspecialchars($user['full_name']); ?></h4><p>Administrator</p></div>
                </div>
            </header>

            <div class="promotion-container">
                <div class="promotion-card">
                    <div class="student-info"><h2><?php echo htmlspecialchars($student['full_name']); ?></h2><p><?php echo htmlspecialchars($student['roll_number']); ?> • <?php echo htmlspecialchars($student['course']); ?></p></div>
                    
                    <?php if(isset($success)): ?>
                        <div class="alert-success"><i class="fas fa-check-circle"></i> <?php echo $success; ?></div>
                    <?php endif; ?>
                    
                    <?php if($current_semester >= $max_semester): ?>
                        <div class="alert-success" style="background:rgba(247,37,133,0.1); color:#f72585;"><i class="fas fa-graduation-cap"></i> Student has completed all semesters! Eligible for graduation.</div>
                    <?php else: ?>
                        <div class="semester-display">
                            <div class="current-semester"><div class="semester-number"><?php echo $current_semester; ?></div><div>Current Semester</div></div>
                            <div class="arrow-icon"><i class="fas fa-arrow-right"></i></div>
                            <div class="next-semester"><div class="semester-number"><?php echo $next_semester; ?></div><div>Next Semester</div></div>
                        </div>
                        
                        <form method="POST">
                            <input type="hidden" name="new_semester" value="<?php echo $next_semester; ?>">
                            <button type="submit" class="btn-promote" onclick="return confirm('Promote student to Semester <?php echo $next_semester; ?>?')"><i class="fas fa-arrow-up"></i> Promote to Semester <?php echo $next_semester; ?></button>
                        </form>
                    <?php endif; ?>
                    
                    <a href="student-detail.php?id=<?php echo $student['id']; ?>" class="btn-back"><i class="fas fa-arrow-left"></i> Back to Student Details</a>
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