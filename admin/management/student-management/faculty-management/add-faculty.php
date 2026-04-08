<?php
require_once '../../../includes/config.php';

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
    <title>Add Faculty - EduTrack Pro</title>
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
        .form-container { background: white; border-radius: 20px; padding: 30px; max-width: 900px; margin: 0 auto; }
        .form-title { font-size: 1.3rem; font-weight: 600; margin-bottom: 25px; display: flex; align-items: center; gap: 10px; padding-bottom: 15px; border-bottom: 2px solid #e9ecef; }
        .form-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: 500; font-size: 0.9rem; }
        .form-control { width: 100%; padding: 10px 12px; border: 2px solid #e9ecef; border-radius: 8px; font-family: 'Poppins', sans-serif; }
        .form-control:focus { outline: none; border-color: #4361ee; }
        .btn-submit { background: linear-gradient(135deg, #4361ee, #3f37c9); color: white; border: none; padding: 14px 30px; border-radius: 10px; font-size: 1rem; font-weight: 600; cursor: pointer; width: 100%; margin-top: 20px; }
        .btn-cancel { background: #e9ecef; color: #1e293b; border: none; padding: 14px 30px; border-radius: 10px; font-size: 1rem; font-weight: 600; cursor: pointer; width: 100%; margin-top: 10px; }
        .section-title { font-size: 1rem; font-weight: 600; margin: 20px 0 15px; color: #4361ee; grid-column: span 2; }
        @media (max-width: 768px) { .sidebar { transform: translateX(-100%); } .sidebar.active { transform: translateX(0); } .main-content { margin-left: 0; } .form-grid { grid-template-columns: 1fr; } .section-title { grid-column: span 1; } }
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
                <a href="faculty-list.php" class="active"><i class="fas fa-chalkboard-teacher"></i><span> Faculty Management</span></a>
                <a href="../../../logout.php"><i class="fas fa-sign-out-alt"></i><span> Logout</span></a>
            </nav>
        </aside>

        <main class="main-content" id="mainContent">
            <header class="top-header">
                <button class="mobile-menu-btn" id="mobileMenuBtn"><i class="fas fa-bars"></i></button>
                <div><h2>Add New Faculty</h2></div>
                <div class="user-profile">
                    <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($user['full_name']); ?>&background=4361ee&color=fff" alt="Profile">
                    <div><h4><?php echo htmlspecialchars($user['full_name']); ?></h4><p>Administrator</p></div>
                </div>
            </header>

            <div class="form-container">
                <div class="form-title"><i class="fas fa-chalkboard-teacher"></i> Faculty Registration Form</div>
                <form id="addFacultyForm">
                    <div class="form-grid">
                        <div class="section-title"><i class="fas fa-user"></i> Personal Information</div>
                        <div class="form-group"><label>Username *</label><input type="text" class="form-control" name="username" required></div>
                        <div class="form-group"><label>Email *</label><input type="email" class="form-control" name="email" required></div>
                        <div class="form-group"><label>Password *</label><input type="password" class="form-control" name="password" required></div>
                        <div class="form-group"><label>Full Name *</label><input type="text" class="form-control" name="full_name" required></div>
                        <div class="form-group"><label>Phone *</label><input type="tel" class="form-control" name="phone" required></div>
                        <div class="form-group"><label>Date of Birth</label><input type="date" class="form-control" name="date_of_birth"></div>
                        <div class="form-group"><label>Gender</label><select class="form-control" name="gender"><option>Male</option><option>Female</option><option>Other</option></select></div>
                        <div class="form-group"><label>Address</label><textarea class="form-control" name="address" rows="2"></textarea></div>
                        
                        <div class="section-title"><i class="fas fa-briefcase"></i> Professional Information</div>
                        <div class="form-group"><label>Employee ID *</label><input type="text" class="form-control" name="employee_id" required></div>
                        <div class="form-group"><label>Department *</label><select class="form-control" name="department" required><option>Computer Science</option><option>Information Technology</option><option>Electronics</option><option>Mechanical</option><option>Civil</option></select></div>
                        <div class="form-group"><label>Designation *</label><select class="form-control" name="designation" required><option>Professor</option><option>Associate Professor</option><option>Assistant Professor</option><option>Lecturer</option></select></div>
                        <div class="form-group"><label>Qualification *</label><input type="text" class="form-control" name="qualification" placeholder="Ph.D., M.Tech, etc." required></div>
                        <div class="form-group"><label>Specialization</label><input type="text" class="form-control" name="specialization" placeholder="e.g., Data Structures, AI"></div>
                        <div class="form-group"><label>Experience (Years)</label><input type="number" class="form-control" name="experience" value="0"></div>
                        <div class="form-group"><label>Joining Date</label><input type="date" class="form-control" name="joining_date"></div>
                    </div>
                    <button type="submit" class="btn-submit"><i class="fas fa-save"></i> Register Faculty</button>
                    <button type="button" class="btn-cancel" onclick="window.location.href='faculty-list.php'"><i class="fas fa-times"></i> Cancel</button>
                </form>
            </div>
        </main>
    </div>

    <script>
        document.getElementById('addFacultyForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const data = Object.fromEntries(formData);
            try {
                const response = await fetch('../../../api/faculty.php?action=add', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(data) });
                const result = await response.json();
                if(result.success) { showNotification('Faculty added successfully!', 'success'); setTimeout(() => window.location.href = 'faculty-list.php', 1500); } 
                else { showNotification(result.message, 'error'); }
            } catch(error) { showNotification('Error adding faculty', 'error'); }
        });
        function showNotification(message, type) { const n = document.createElement('div'); n.className = 'notification'; n.style.background = type === 'success' ? '#4cc9f0' : '#f72585'; n.innerHTML = `<i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i> ${message}`; document.body.appendChild(n); setTimeout(() => n.remove(), 3000); }
        
        const sidebar = document.getElementById('sidebar'), mainContent = document.getElementById('mainContent'), toggleBtn = document.getElementById('toggleSidebar'), mobileMenuBtn = document.getElementById('mobileMenuBtn');
        if(toggleBtn) toggleBtn.addEventListener('click', function() { sidebar.classList.toggle('collapsed'); mainContent.classList.toggle('expanded'); });
        if(mobileMenuBtn) mobileMenuBtn.addEventListener('click', function() { sidebar.classList.toggle('active'); });
        function handleResponsive() { if(window.innerWidth <= 768) { sidebar.classList.add('collapsed'); mainContent.classList.add('expanded'); } else { sidebar.classList.remove('collapsed', 'active'); mainContent.classList.remove('expanded'); } }
        window.addEventListener('resize', handleResponsive); handleResponsive();
    </script>
</body>
</html>