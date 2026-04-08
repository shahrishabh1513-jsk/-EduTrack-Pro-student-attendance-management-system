<?php
require_once '../../includes/config.php';

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
    <title>Announcements - EduTrack Pro</title>
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
        .btn-add { background: #4361ee; color: white; border: none; padding: 12px 25px; border-radius: 10px; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; margin-bottom: 20px; }
        .announcement-card { background: white; border-radius: 20px; padding: 25px; margin-bottom: 20px; border-left: 4px solid #4361ee; transition: all 0.3s; }
        .announcement-card:hover { transform: translateX(5px); box-shadow: 0 5px 15px rgba(0,0,0,0.05); }
        .announcement-header { display: flex; justify-content: space-between; margin-bottom: 15px; flex-wrap: wrap; }
        .announcement-title { font-size: 1.2rem; font-weight: 600; }
        .announcement-badge { padding: 4px 12px; border-radius: 20px; font-size: 0.7rem; background: rgba(76,201,240,0.1); color: #4cc9f0; }
        .announcement-content { color: #6c757d; line-height: 1.6; margin-bottom: 15px; }
        .announcement-footer { display: flex; justify-content: space-between; align-items: center; font-size: 0.8rem; color: #6c757d; padding-top: 15px; border-top: 1px solid #e9ecef; }
        .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); justify-content: center; align-items: center; z-index: 1000; }
        .modal-content { background: white; border-radius: 20px; padding: 30px; max-width: 500px; width: 90%; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: 500; }
        .form-control { width: 100%; padding: 10px; border: 2px solid #e9ecef; border-radius: 8px; font-family: 'Poppins', sans-serif; }
        textarea.form-control { min-height: 100px; resize: vertical; }
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
                <a href="notices.php"><i class="fas fa-bell"></i><span> Notices</span></a>
                <a href="add-notice.php"><i class="fas fa-plus"></i><span> Add Notice</span></a>
                <a href="announcements.php" class="active"><i class="fas fa-bullhorn"></i><span> Announcements</span></a>
                <a href="email-broadcast.php"><i class="fas fa-envelope"></i><span> Email Broadcast</span></a>
                <a href="../../logout.php"><i class="fas fa-sign-out-alt"></i><span> Logout</span></a>
            </nav>
        </aside>

        <main class="main-content" id="mainContent">
            <header class="top-header">
                <button class="mobile-menu-btn" id="mobileMenuBtn"><i class="fas fa-bars"></i></button>
                <div><h2>Live Announcements</h2></div>
                <div class="user-profile">
                    <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($user['full_name']); ?>&background=4361ee&color=fff" alt="Profile">
                    <div><h4><?php echo htmlspecialchars($user['full_name']); ?></h4><p>Administrator</p></div>
                </div>
            </header>

            <button class="btn-add" onclick="openAddModal()"><i class="fas fa-plus"></i> Make Announcement</button>
            <div id="announcementsList"></div>
        </main>
    </div>

    <!-- Add Announcement Modal -->
    <div id="announcementModal" class="modal">
        <div class="modal-content">
            <h3><i class="fas fa-bullhorn"></i> New Announcement</h3>
            <form id="announcementForm">
                <div class="form-group"><label>Title</label><input type="text" class="form-control" id="announcementTitle" required></div>
                <div class="form-group"><label>Message</label><textarea class="form-control" id="announcementMessage" required></textarea></div>
                <div class="form-group"><label>Priority</label><select class="form-control" id="announcementPriority"><option value="normal">Normal</option><option value="high">High Priority</option><option value="urgent">Urgent</option></select></div>
                <button type="submit" class="btn-add" style="width:100%; margin-top:10px;">Post Announcement</button>
                <button type="button" class="btn-add" onclick="closeModal()" style="width:100%; margin-top:10px; background:#6c757d;">Cancel</button>
            </form>
        </div>
    </div>

    <script>
        let announcements = [
            { id: 1, title: 'Exam Schedule Released', message: 'End semester examination schedule has been published. Please check the exam timetable section.', priority: 'high', date: '2024-03-20 10:30 AM' },
            { id: 2, title: 'Library Timing Extended', message: 'Library will remain open 24/7 during examination week.', priority: 'normal', date: '2024-03-19 09:00 AM' }
        ];
        
        function loadAnnouncements() {
            const priorityColors = { high: '#ffc107', urgent: '#f72585', normal: '#4cc9f0' };
            const html = announcements.map(a => `
                <div class="announcement-card" style="border-left-color: ${priorityColors[a.priority]}">
                    <div class="announcement-header"><span class="announcement-title">${a.title}</span><span class="announcement-badge" style="background:${priorityColors[a.priority]}20; color:${priorityColors[a.priority]}">${a.priority.toUpperCase()}</span></div>
                    <div class="announcement-content">${a.message}</div>
                    <div class="announcement-footer"><span><i class="fas fa-user"></i> Admin</span><span><i class="fas fa-clock"></i> ${a.date}</span></div>
                </div>
            `).join('');
            document.getElementById('announcementsList').innerHTML = html || '<p>No announcements yet.</p>';
        }
        
        function openAddModal() { document.getElementById('announcementModal').style.display = 'flex'; }
        function closeModal() { document.getElementById('announcementModal').style.display = 'none'; }
        
        document.getElementById('announcementForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const newAnnouncement = { id: announcements.length + 1, title: document.getElementById('announcementTitle').value, message: document.getElementById('announcementMessage').value, priority: document.getElementById('announcementPriority').value, date: new Date().toLocaleString() };
            announcements.unshift(newAnnouncement);
            loadAnnouncements();
            closeModal();
            showNotification('Announcement posted!', 'success');
            this.reset();
        });
        
        function showNotification(message, type) { const n = document.createElement('div'); n.className = 'notification'; n.style.background = type === 'success' ? '#4cc9f0' : '#f72585'; n.innerHTML = `<i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i> ${message}`; document.body.appendChild(n); setTimeout(() => n.remove(), 3000); }
        
        const sidebar = document.getElementById('sidebar'), mainContent = document.getElementById('mainContent'), toggleBtn = document.getElementById('toggleSidebar'), mobileMenuBtn = document.getElementById('mobileMenuBtn');
        if(toggleBtn) toggleBtn.addEventListener('click', function() { sidebar.classList.toggle('collapsed'); mainContent.classList.toggle('expanded'); });
        if(mobileMenuBtn) mobileMenuBtn.addEventListener('click', function() { sidebar.classList.toggle('active'); });
        function handleResponsive() { if(window.innerWidth <= 768) { sidebar.classList.add('collapsed'); mainContent.classList.add('expanded'); } else { sidebar.classList.remove('collapsed', 'active'); mainContent.classList.remove('expanded'); } }
        window.addEventListener('resize', handleResponsive); handleResponsive();
        loadAnnouncements();
        window.onclick = function(event) { if(event.target == document.getElementById('announcementModal')) closeModal(); }
    </script>
</body>
</html>