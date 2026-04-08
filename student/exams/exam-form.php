<?php
require_once '../../includes/config.php';

if (!isLoggedIn() || !hasRole('student')) {
    redirect('login.php');
}

$user = getUserData($conn, $_SESSION['user_id']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exam Form - EduTrack Pro</title>
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
        .form-container { background: white; border-radius: 20px; padding: 30px; max-width: 800px; margin: 0 auto; }
        .info-box { background: #f8f9fa; padding: 15px; border-radius: 10px; margin-bottom: 20px; }
        .subject-item { display: flex; justify-content: space-between; align-items: center; padding: 15px; border: 1px solid #e9ecef; border-radius: 10px; margin-bottom: 10px; }
        .fee-summary { background: linear-gradient(135deg, #f8f9fa, #e9ecef); border-radius: 15px; padding: 20px; margin: 20px 0; }
        .fee-row { display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #dee2e6; }
        .fee-row.total { font-weight: 700; font-size: 1.1rem; border-bottom: none; }
        .btn-submit { background: linear-gradient(135deg, #4361ee, #3f37c9); color: white; padding: 14px 30px; border: none; border-radius: 10px; font-size: 1rem; font-weight: 600; cursor: pointer; width: 100%; }
        .declaration { background: rgba(67,97,238,0.05); border-radius: 15px; padding: 20px; margin: 20px 0; }
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
                <a href="../attendance/view-attendance.php"><i class="fas fa-calendar-check"></i><span> Attendance</span></a>
                <a href="../academics/academic-timeline.php"><i class="fas fa-timeline"></i><span> Academic Timeline</span></a>
                <a href="exam-form.php" class="active"><i class="fas fa-file-alt"></i><span> Exam Form</span></a>
                <a href="../logout.php"><i class="fas fa-sign-out-alt"></i><span> Logout</span></a>
            </nav>
        </aside>

        <main class="main-content" id="mainContent">
            <header class="top-header">
                <button class="mobile-menu-btn" id="mobileMenuBtn"><i class="fas fa-bars"></i></button>
                <div><h2>Examination Form</h2></div>
                <div class="user-profile">
                    <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($user['full_name']); ?>&background=4361ee&color=fff" alt="Profile">
                    <div><h4><?php echo htmlspecialchars($user['full_name']); ?></h4><p><?php echo htmlspecialchars($user['id_number']); ?></p></div>
                </div>
            </header>

            <div class="form-container">
                <div class="info-box"><h4>Student Information</h4><p><strong>Name:</strong> <?php echo htmlspecialchars($user['full_name']); ?></p><p><strong>Roll Number:</strong> <?php echo htmlspecialchars($user['id_number']); ?></p><p><strong>Course:</strong> <?php echo htmlspecialchars($user['course']); ?></p><p><strong>Semester:</strong> Semester 3</p></div>
                
                <h3>Select Subjects</h3>
                <div class="subject-item"><div><strong>CS301</strong> - Data Structures</div><div>₹500</div></div>
                <div class="subject-item"><div><strong>CS302</strong> - Database Management</div><div>₹500</div></div>
                <div class="subject-item"><div><strong>CS303</strong> - Web Development</div><div>₹400</div></div>
                <div class="subject-item"><div><strong>CS304</strong> - Operating Systems</div><div>₹500</div></div>
                <div class="subject-item"><div><strong>CS305</strong> - Computer Networks</div><div>₹500</div></div>
                
                <div class="fee-summary"><div class="fee-row"><span>Registration Fee</span><span>₹500</span></div><div class="fee-row"><span>Examination Fee (5 Subjects)</span><span>₹2,400</span></div><div class="fee-row"><span>Practical Fee</span><span>₹1,000</span></div><div class="fee-row total"><span>Total Amount</span><span>₹3,900</span></div></div>
                
                <div class="declaration"><input type="checkbox" id="declaration"> <label for="declaration">I hereby declare that the information provided is true and correct. I have read and understood the examination rules and regulations.</label></div>
                
                <button class="btn-submit" onclick="submitForm()">Submit Application</button>
            </div>
        </main>
    </div>

    <script>
        function submitForm() { if(document.getElementById('declaration').checked) { showNotification('Exam form submitted successfully!', 'success'); } else { showNotification('Please accept the declaration', 'error'); } }
        function showNotification(message, type) { const notification = document.createElement('div'); notification.className = 'notification'; notification.style.background = type === 'success' ? '#4cc9f0' : '#f72585'; notification.innerHTML = `<i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i> ${message}`; document.body.appendChild(notification); setTimeout(() => notification.remove(), 3000); }
        
        const sidebar = document.getElementById('sidebar'), mainContent = document.getElementById('mainContent'), toggleBtn = document.getElementById('toggleSidebar'), mobileMenuBtn = document.getElementById('mobileMenuBtn');
        if(toggleBtn) toggleBtn.addEventListener('click', function() { sidebar.classList.toggle('collapsed'); mainContent.classList.toggle('expanded'); });
        if(mobileMenuBtn) mobileMenuBtn.addEventListener('click', function() { sidebar.classList.toggle('active'); });
        function handleResponsive() { if(window.innerWidth <= 768) { sidebar.classList.add('collapsed'); mainContent.classList.add('expanded'); } else { sidebar.classList.remove('collapsed', 'active'); mainContent.classList.remove('expanded'); } }
        window.addEventListener('resize', handleResponsive); handleResponsive();
    </script>
</body>
</html>