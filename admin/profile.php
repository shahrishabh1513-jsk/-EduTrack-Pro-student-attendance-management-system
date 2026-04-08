<?php
require_once '../includes/config.php';

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
    <title>Admin Profile - EduTrack Pro</title>
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
        .profile-container { max-width: 1000px; margin: 0 auto; }
        .profile-header { background: linear-gradient(135deg, #4361ee, #3f37c9); border-radius: 20px; padding: 40px; margin-bottom: 30px; color: white; display: flex; align-items: center; gap: 30px; }
        .avatar-large { width: 100px; height: 100px; background: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 3rem; color: #4361ee; }
        .profile-card { background: white; border-radius: 20px; padding: 30px; margin-bottom: 30px; }
        .card-title { font-size: 1.2rem; font-weight: 600; margin-bottom: 25px; display: flex; align-items: center; gap: 10px; border-bottom: 2px solid #e9ecef; padding-bottom: 15px; }
        .info-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; }
        .info-item { margin-bottom: 15px; }
        .info-label { font-size: 0.85rem; color: #6c757d; margin-bottom: 5px; }
        .info-value { font-size: 1rem; font-weight: 500; padding: 8px 12px; background: #f8f9fa; border-radius: 10px; }
        .edit-mode .info-value { display: none; }
        .edit-input { display: none; width: 100%; padding: 8px 12px; border: 2px solid #4361ee; border-radius: 8px; }
        .edit-mode .edit-input { display: block; }
        .edit-actions { display: none; gap: 10px; margin-top: 20px; justify-content: flex-end; }
        .edit-mode .edit-actions { display: flex; }
        .save-btn { background: #4361ee; color: white; border: none; padding: 10px 25px; border-radius: 8px; cursor: pointer; }
        .cancel-btn { background: #e9ecef; color: #1e293b; border: none; padding: 10px 25px; border-radius: 8px; cursor: pointer; }
        @media (max-width: 768px) { .sidebar { transform: translateX(-100%); } .sidebar.active { transform: translateX(0); } .main-content { margin-left: 0; } .profile-header { flex-direction: column; text-align: center; } .info-grid { grid-template-columns: 1fr; } }
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
                <a href="dashboard.php"><i class="fas fa-home"></i><span> Dashboard</span></a>
                <a href="profile.php" class="active"><i class="fas fa-user"></i><span> Profile</span></a>
                <a href="management/student-management/students-list.php"><i class="fas fa-user-graduate"></i><span> Student Management</span></a>
                <a href="management/faculty-management/faculty-list.php"><i class="fas fa-chalkboard-teacher"></i><span> Faculty Management</span></a>
                <a href="../logout.php"><i class="fas fa-sign-out-alt"></i><span> Logout</span></a>
            </nav>
        </aside>

        <main class="main-content" id="mainContent">
            <header class="top-header">
                <button class="mobile-menu-btn" id="mobileMenuBtn"><i class="fas fa-bars"></i></button>
                <div><h2>My Profile</h2></div>
                <div class="user-profile">
                    <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($user['full_name']); ?>&background=4361ee&color=fff" alt="Profile">
                    <div><h4><?php echo htmlspecialchars($user['full_name']); ?></h4><p>Administrator</p></div>
                </div>
            </header>

            <div class="profile-container">
                <div class="profile-header">
                    <div class="avatar-large"><i class="fas fa-user-tie"></i></div>
                    <div><h1><?php echo htmlspecialchars($user['full_name']); ?></h1><p>System Administrator • <?php echo htmlspecialchars($user['email']); ?></p></div>
                    <button class="edit-profile-btn" id="editProfileBtn" style="margin-left:auto; background:rgba(255,255,255,0.2); border:2px solid white; color:white; padding:10px 25px; border-radius:25px; cursor:pointer;"><i class="fas fa-edit"></i> Edit Profile</button>
                </div>

                <div class="profile-card" id="personalInfoCard">
                    <div class="card-title"><i class="fas fa-user"></i> Personal Information</div>
                    <div class="info-grid">
                        <div class="info-item"><div class="info-label">Full Name</div><div class="info-value" id="displayName"><?php echo htmlspecialchars($user['full_name']); ?></div><input type="text" class="edit-input" id="editName" value="<?php echo htmlspecialchars($user['full_name']); ?>"></div>
                        <div class="info-item"><div class="info-label">Email Address</div><div class="info-value" id="displayEmail"><?php echo htmlspecialchars($user['email']); ?></div><input type="email" class="edit-input" id="editEmail" value="<?php echo htmlspecialchars($user['email']); ?>"></div>
                        <div class="info-item"><div class="info-label">Phone Number</div><div class="info-value" id="displayPhone"><?php echo htmlspecialchars($user['phone']); ?></div><input type="tel" class="edit-input" id="editPhone" value="<?php echo htmlspecialchars($user['phone']); ?>"></div>
                        <div class="info-item"><div class="info-label">Username</div><div class="info-value" id="displayUsername"><?php echo htmlspecialchars($user['username']); ?></div><input type="text" class="edit-input" id="editUsername" value="<?php echo htmlspecialchars($user['username']); ?>"></div>
                        <div class="info-item"><div class="info-label">Address</div><div class="info-value" id="displayAddress"><?php echo htmlspecialchars($user['address']); ?></div><textarea class="edit-input" id="editAddress" rows="2"><?php echo htmlspecialchars($user['address']); ?></textarea></div>
                    </div>
                    <div class="edit-actions"><button class="cancel-btn" onclick="cancelEdit()">Cancel</button><button class="save-btn" onclick="saveProfile()">Save Changes</button></div>
                </div>

                <div class="profile-card"><div class="card-title"><i class="fas fa-shield-alt"></i> Security Settings</div><div class="info-grid"><div class="info-item"><div class="info-label">Role</div><div class="info-value">System Administrator</div></div><div class="info-item"><div class="info-label">Last Login</div><div class="info-value"><?php echo date('F j, Y g:i A'); ?></div></div></div><button class="save-btn" onclick="showNotification('Password change feature coming soon', 'info')" style="margin-top:15px;">Change Password</button></div>
            </div>
        </main>
    </div>

    <script>
        let editMode = false, originalData = {};
        document.getElementById('editProfileBtn').addEventListener('click', function() { if(!editMode) enableEditMode(); else cancelEdit(); });
        function enableEditMode() { editMode = true; document.getElementById('personalInfoCard').classList.add('edit-mode'); document.getElementById('editProfileBtn').innerHTML = '<i class="fas fa-times"></i> Cancel Edit'; originalData = { name: document.getElementById('displayName').textContent, email: document.getElementById('displayEmail').textContent, phone: document.getElementById('displayPhone').textContent, username: document.getElementById('displayUsername').textContent, address: document.getElementById('displayAddress').textContent }; }
        function cancelEdit() { editMode = false; document.getElementById('personalInfoCard').classList.remove('edit-mode'); document.getElementById('editProfileBtn').innerHTML = '<i class="fas fa-edit"></i> Edit Profile'; document.getElementById('displayName').textContent = originalData.name; document.getElementById('displayEmail').textContent = originalData.email; document.getElementById('displayPhone').textContent = originalData.phone; document.getElementById('displayUsername').textContent = originalData.username; document.getElementById('displayAddress').textContent = originalData.address; }
        function saveProfile() { const newName = document.getElementById('editName').value; const newEmail = document.getElementById('editEmail').value; const newPhone = document.getElementById('editPhone').value; const newUsername = document.getElementById('editUsername').value; const newAddress = document.getElementById('editAddress').value; document.getElementById('displayName').textContent = newName; document.getElementById('displayEmail').textContent = newEmail; document.getElementById('displayPhone').textContent = newPhone; document.getElementById('displayUsername').textContent = newUsername; document.getElementById('displayAddress').textContent = newAddress; editMode = false; document.getElementById('personalInfoCard').classList.remove('edit-mode'); document.getElementById('editProfileBtn').innerHTML = '<i class="fas fa-edit"></i> Edit Profile'; showNotification('Profile updated successfully!', 'success'); }
        function showNotification(message, type) { const n = document.createElement('div'); n.className = 'notification'; n.style.background = type === 'success' ? '#4cc9f0' : '#4361ee'; n.innerHTML = `<i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-info-circle'}"></i> ${message}`; document.body.appendChild(n); setTimeout(() => n.remove(), 3000); }
        const sidebar = document.getElementById('sidebar'), mainContent = document.getElementById('mainContent'), toggleBtn = document.getElementById('toggleSidebar'), mobileMenuBtn = document.getElementById('mobileMenuBtn');
        if(toggleBtn) toggleBtn.addEventListener('click', function() { sidebar.classList.toggle('collapsed'); mainContent.classList.toggle('expanded'); });
        if(mobileMenuBtn) mobileMenuBtn.addEventListener('click', function() { sidebar.classList.toggle('active'); });
        function handleResponsive() { if(window.innerWidth <= 768) { sidebar.classList.add('collapsed'); mainContent.classList.add('expanded'); } else { sidebar.classList.remove('collapsed', 'active'); mainContent.classList.remove('expanded'); } }
        window.addEventListener('resize', handleResponsive); handleResponsive();
    </script>
</body>
</html> 