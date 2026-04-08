<?php
require_once '../../../includes/config.php';

if (!isLoggedIn() || !hasRole('admin')) {
    redirect('login.php');
}

$user = getUserData($conn, $_SESSION['user_id']);
$course_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$view_mode = isset($_GET['view']) ? true : false;

// Fetch course data
$query = "SELECT * FROM courses WHERE id = $course_id";
$result = mysqli_query($conn, $query);
$course = mysqli_fetch_assoc($result);

if (!$course) {
    redirect('courses-list.php');
}

// Fetch assigned faculty
$faculty_query = "SELECT f.id, u.full_name, f.employee_id 
                  FROM course_assignments ca 
                  JOIN faculty f ON ca.faculty_id = f.id 
                  JOIN users u ON f.user_id = u.id 
                  WHERE ca.course_id = $course_id";
$faculty_result = mysqli_query($conn, $faculty_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $view_mode ? 'View Course' : 'Edit Course'; ?> - EduTrack Pro</title>
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
        .form-container { background: white; border-radius: 20px; padding: 30px; max-width: 800px; margin: 0 auto; }
        .form-title { font-size: 1.3rem; font-weight: 600; margin-bottom: 25px; display: flex; align-items: center; gap: 10px; padding-bottom: 15px; border-bottom: 2px solid #e9ecef; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 500; }
        .form-control { width: 100%; padding: 12px; border: 2px solid #e9ecef; border-radius: 8px; font-family: 'Poppins', sans-serif; }
        .form-control:focus { outline: none; border-color: #4361ee; }
        .form-control[readonly] { background: #f8f9fa; cursor: not-allowed; }
        textarea.form-control { min-height: 100px; resize: vertical; }
        .form-row { display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; }
        .btn-submit { background: linear-gradient(135deg, #4361ee, #3f37c9); color: white; border: none; padding: 14px 30px; border-radius: 10px; font-size: 1rem; font-weight: 600; cursor: pointer; width: 100%; margin-top: 20px; }
        .btn-cancel { background: #e9ecef; color: #1e293b; border: none; padding: 14px 30px; border-radius: 10px; font-size: 1rem; font-weight: 600; cursor: pointer; width: 100%; margin-top: 10px; }
        .faculty-list { margin-top: 20px; padding-top: 20px; border-top: 1px solid #e9ecef; }
        .faculty-item { display: flex; justify-content: space-between; align-items: center; padding: 10px; background: #f8f9fa; border-radius: 8px; margin-bottom: 10px; }
        @media (max-width: 768px) { .sidebar { transform: translateX(-100%); } .sidebar.active { transform: translateX(0); } .main-content { margin-left: 0; } .form-row { grid-template-columns: 1fr; } }
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
                <a href="../../dashboard.php"><i class="fas fa-home"></i><span> Dashboard</span></a>
                <a href="../../profile.php"><i class="fas fa-user"></i><span> Profile</span></a>
                <a href="courses-list.php" class="active"><i class="fas fa-book"></i><span> Course Management</span></a>
                <a href="../../../logout.php"><i class="fas fa-sign-out-alt"></i><span> Logout</span></a>
            </nav>
        </aside>

        <main class="main-content" id="mainContent">
            <header class="top-header">
                <button class="mobile-menu-btn" id="mobileMenuBtn"><i class="fas fa-bars"></i></button>
                <div><h2><?php echo $view_mode ? 'Course Details' : 'Edit Course'; ?></h2></div>
                <div class="user-profile">
                    <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($user['full_name']); ?>&background=4361ee&color=fff" alt="Profile">
                    <div><h4><?php echo htmlspecialchars($user['full_name']); ?></h4><p>Administrator</p></div>
                </div>
            </header>

            <div class="form-container">
                <div class="form-title"><i class="fas fa-book"></i> <?php echo $view_mode ? 'Course Information' : 'Edit Course Information'; ?></div>
                <form id="editCourseForm">
                    <input type="hidden" name="id" value="<?php echo $course['id']; ?>">
                    <div class="form-row">
                        <div class="form-group"><label>Course Code</label><input type="text" class="form-control" name="course_code" value="<?php echo htmlspecialchars($course['course_code']); ?>" <?php echo $view_mode ? 'readonly' : ''; ?> required></div>
                        <div class="form-group"><label>Course Name</label><input type="text" class="form-control" name="course_name" value="<?php echo htmlspecialchars($course['course_name']); ?>" <?php echo $view_mode ? 'readonly' : ''; ?> required></div>
                    </div>
                    <div class="form-row">
                        <div class="form-group"><label>Credits</label><input type="number" class="form-control" name="credits" value="<?php echo $course['credits']; ?>" <?php echo $view_mode ? 'readonly' : ''; ?> required></div>
                        <div class="form-group"><label>Semester</label><select class="form-control" name="semester" <?php echo $view_mode ? 'disabled' : ''; ?> required><option <?php echo $course['semester'] == 1 ? 'selected' : ''; ?>>1</option><option <?php echo $course['semester'] == 2 ? 'selected' : ''; ?>>2</option><option <?php echo $course['semester'] == 3 ? 'selected' : ''; ?>>3</option><option <?php echo $course['semester'] == 4 ? 'selected' : ''; ?>>4</option><option <?php echo $course['semester'] == 5 ? 'selected' : ''; ?>>5</option><option <?php echo $course['semester'] == 6 ? 'selected' : ''; ?>>6</option></select></div>
                    </div>
                    <div class="form-row">
                        <div class="form-group"><label>Department</label><select class="form-control" name="department" <?php echo $view_mode ? 'disabled' : ''; ?> required><option <?php echo $course['department'] == 'Computer Science' ? 'selected' : ''; ?>>Computer Science</option><option <?php echo $course['department'] == 'Information Technology' ? 'selected' : ''; ?>>Information Technology</option><option <?php echo $course['department'] == 'Electronics' ? 'selected' : ''; ?>>Electronics</option></select></div>
                        <div class="form-group"><label>Status</label><select class="form-control" name="status" <?php echo $view_mode ? 'disabled' : ''; ?>><option <?php echo $course['status'] == 'active' ? 'selected' : ''; ?>>active</option><option <?php echo $course['status'] == 'inactive' ? 'selected' : ''; ?>>inactive</option></select></div>
                    </div>
                    <div class="form-group"><label>Description</label><textarea class="form-control" name="description" <?php echo $view_mode ? 'readonly' : ''; ?>><?php echo htmlspecialchars($course['description']); ?></textarea></div>
                    
                    <?php if(!$view_mode): ?>
                    <button type="submit" class="btn-submit"><i class="fas fa-save"></i> Update Course</button>
                    <?php endif; ?>
                    <button type="button" class="btn-cancel" onclick="window.location.href='courses-list.php'"><i class="fas fa-arrow-left"></i> Back to Courses</button>
                </form>

                <?php if(mysqli_num_rows($faculty_result) > 0): ?>
                <div class="faculty-list">
                    <h4><i class="fas fa-chalkboard-teacher"></i> Assigned Faculty</h4>
                    <?php while($faculty = mysqli_fetch_assoc($faculty_result)): ?>
                    <div class="faculty-item"><span><strong><?php echo htmlspecialchars($faculty['employee_id']); ?></strong> - <?php echo htmlspecialchars($faculty['full_name']); ?></span></div>
                    <?php endwhile; ?>
                </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <script>
        <?php if(!$view_mode): ?>
        document.getElementById('editCourseForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const data = Object.fromEntries(formData);
            try {
                const response = await fetch('../../../api/courses.php?action=update', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(data) });
                const result = await response.json();
                if(result.success) { showNotification('Course updated successfully!', 'success'); setTimeout(() => window.location.href = 'courses-list.php', 1500); } 
                else { showNotification(result.message, 'error'); }
            } catch(error) { showNotification('Error updating course', 'error'); }
        });
        <?php endif; ?>
        
        function showNotification(message, type) { const n = document.createElement('div'); n.className = 'notification'; n.style.background = type === 'success' ? '#4cc9f0' : '#f72585'; n.innerHTML = `<i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i> ${message}`; document.body.appendChild(n); setTimeout(() => n.remove(), 3000); }
        
        const sidebar = document.getElementById('sidebar'), mainContent = document.getElementById('mainContent'), toggleBtn = document.getElementById('toggleSidebar'), mobileMenuBtn = document.getElementById('mobileMenuBtn');
        if(toggleBtn) toggleBtn.addEventListener('click', function() { sidebar.classList.toggle('collapsed'); mainContent.classList.toggle('expanded'); });
        if(mobileMenuBtn) mobileMenuBtn.addEventListener('click', function() { sidebar.classList.toggle('active'); });
        function handleResponsive() { if(window.innerWidth <= 768) { sidebar.classList.add('collapsed'); mainContent.classList.add('expanded'); } else { sidebar.classList.remove('collapsed', 'active'); mainContent.classList.remove('expanded'); } }
        window.addEventListener('resize', handleResponsive); handleResponsive();
    </script>
</body>
</html>