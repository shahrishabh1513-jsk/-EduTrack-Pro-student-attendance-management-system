<?php
require_once '../../includes/config.php';

if (!isLoggedIn() || !hasRole('faculty')) {
    redirect('login.php');
}

$user = getUserData($conn, $_SESSION['user_id']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notices - EduTrack Pro</title>
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
        .notice-card { background: white; border-radius: 20px; padding: 25px; margin-bottom: 20px; border-left: 4px solid; transition: all 0.3s; cursor: pointer; }
        .notice-card:hover { transform: translateX(5px); box-shadow: 0 5px 15px rgba(0,0,0,0.05); }
        .notice-card.important { border-left-color: #f72585; }
        .notice-card.exam { border-left-color: #4361ee; }
        .notice-card.event { border-left-color: #4cc9f0; }
        .notice-card.holiday { border-left-color: #ffc107; }
        .notice-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; }
        .notice-badge { padding: 4px 12px; border-radius: 20px; font-size: 0.7rem; font-weight: 600; }
        .badge-important { background: rgba(247,37,133,0.1); color: #f72585; }
        .badge-exam { background: rgba(67,97,238,0.1); color: #4361ee; }
        .badge-event { background: rgba(76,201,240,0.1); color: #4cc9f0; }
        .badge-holiday { background: rgba(255,193,7,0.1); color: #ffc107; }
        .notice-title { font-size: 1.2rem; font-weight: 600; margin-bottom: 10px; }
        .notice-content { color: #6c757d; line-height: 1.6; margin-bottom: 15px; }
        .add-notice-btn { background: #4361ee; color: white; border: none; padding: 12px 25px; border-radius: 10px; cursor: pointer; margin-bottom: 20px; display: inline-flex; align-items: center; gap: 8px; }
        .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); justify-content: center; align-items: center; z-index: 1000; }
        .modal-content { background: white; border-radius: 20px; padding: 30px; max-width: 600px; width: 90%; }
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
                <a href="upload-material.php"><i class="fas fa-upload"></i><span> Upload Material</span></a>
                <a href="repository.php"><i class="fas fa-folder"></i><span> Repository</span></a>
                <a href="notices.php" class="active"><i class="fas fa-bell"></i><span> Notices</span></a>
                <a href="feedback-view.php"><i class="fas fa-star"></i><span> Feedback</span></a>
                <a href="../../logout.php"><i class="fas fa-sign-out-alt"></i><span> Logout</span></a>
            </nav>
        </aside>

        <main class="main-content" id="mainContent">
            <header class="top-header">
                <button class="mobile-menu-btn" id="mobileMenuBtn"><i class="fas fa-bars"></i></button>
                <div><h2>Academic Notices</h2></div>
                <div class="user-profile">
                    <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($user['full_name']); ?>&background=4361ee&color=fff" alt="Profile">
                    <div><h4><?php echo htmlspecialchars($user['full_name']); ?></h4><p><?php echo htmlspecialchars($user['id_number']); ?></p></div>
                </div>
            </header>

            <button class="add-notice-btn" onclick="openAddNoticeModal()"><i class="fas fa-plus"></i> Post New Notice</button>

            <div id="noticesList"></div>
        </main>
    </div>

    <!-- Add Notice Modal -->
    <div id="noticeModal" class="modal">
        <div class="modal-content">
            <h3><i class="fas fa-bullhorn"></i> Post New Notice</h3>
            <form id="noticeForm">
                <div class="form-group"><label>Title</label><input type="text" class="form-control" id="noticeTitle" required></div>
                <div class="form-group"><label>Category</label><select class="form-control" id="noticeCategory"><option value="important">Important</option><option value="exam">Examination</option><option value="event">Event</option><option value="holiday">Holiday</option></select></div>
                <div class="form-group"><label>Content</label><textarea class="form-control" id="noticeContent" required></textarea></div>
                <div style="display:flex; gap:10px; margin-top:20px;"><button type="submit" class="add-notice-btn" style="margin:0;">Post Notice</button><button type="button" class="add-notice-btn" onclick="closeModal()" style="background:#6c757d;">Cancel</button></div>
            </form>
        </div>
    </div>

    <script>
        let notices = [
            { id: 1, title: 'End Semester Examination Schedule', category: 'exam', content: 'The schedule for end semester examinations has been released. Please check the exam timetable section.', date: '2024-03-20', postedBy: 'Admin' },
            { id: 2, title: 'College Holiday on March 25th', category: 'holiday', content: 'The college will remain closed on March 25th on account of Ram Navami.', date: '2024-03-18', postedBy: 'Admin' }
        ];
        
        function loadNotices() {
            const noticesHTML = notices.map(notice => `
                <div class="notice-card ${notice.category}" onclick="viewNotice(${notice.id})">
                    <div class="notice-header"><span class="notice-badge badge-${notice.category}">${notice.category.toUpperCase()}</span><span>${new Date(notice.date).toLocaleDateString()}</span></div>
                    <div class="notice-title">${notice.title}</div>
                    <div class="notice-content">${notice.content.substring(0, 150)}${notice.content.length > 150 ? '...' : ''}</div>
                    <div style="font-size:0.8rem; color:#6c757d;">Posted by: ${notice.postedBy}</div>
                </div>
            `).join('');
            document.getElementById('noticesList').innerHTML = noticesHTML;
        }
        
        function viewNotice(id) { alert('Notice Details:\n\n' + notices.find(n => n.id === id).content); }
        
        document.getElementById('noticeForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const newNotice = { id: notices.length + 1, title: document.getElementById('noticeTitle').value, category: document.getElementById('noticeCategory').value, content: document.getElementById('noticeContent').value, date: new Date().toISOString().split('T')[0], postedBy: '<?php echo $user['full_name']; ?>' };
            notices.unshift(newNotice);
            loadNotices();
            closeModal();
            showNotification('Notice posted successfully!', 'success');
            this.reset();
        });
        
        function openAddNoticeModal() { document.getElementById('noticeModal').style.display = 'flex'; }
        function closeModal() { document.getElementById('noticeModal').style.display = 'none'; }
        function showNotification(message, type) { const n = document.createElement('div'); n.className = 'notification'; n.style.background = type === 'success' ? '#4cc9f0' : '#4361ee'; n.innerHTML = `<i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-info-circle'}"></i> ${message}`; document.body.appendChild(n); setTimeout(() => n.remove(), 3000); }
        
        const sidebar = document.getElementById('sidebar'), mainContent = document.getElementById('mainContent'), toggleBtn = document.getElementById('toggleSidebar'), mobileMenuBtn = document.getElementById('mobileMenuBtn');
        if(toggleBtn) toggleBtn.addEventListener('click', function() { sidebar.classList.toggle('collapsed'); mainContent.classList.toggle('expanded'); });
        if(mobileMenuBtn) mobileMenuBtn.addEventListener('click', function() { sidebar.classList.toggle('active'); });
        function handleResponsive() { if(window.innerWidth <= 768) { sidebar.classList.add('collapsed'); mainContent.classList.add('expanded'); } else { sidebar.classList.remove('collapsed', 'active'); mainContent.classList.remove('expanded'); } }
        window.addEventListener('resize', handleResponsive); handleResponsive();
        loadNotices();
        window.onclick = function(event) { if(event.target == document.getElementById('noticeModal')) closeModal(); }
    </script>
</body>
</html>