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
        .header-actions { display: flex; gap: 15px; margin-bottom: 20px; flex-wrap: wrap; }
        .btn-add { background: #4361ee; color: white; border: none; padding: 12px 25px; border-radius: 10px; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; }
        .filter-bar { display: flex; gap: 15px; margin-bottom: 20px; flex-wrap: wrap; align-items: center; }
        .filter-select, .search-box { padding: 10px 15px; border: 2px solid #e9ecef; border-radius: 10px; font-family: 'Poppins', sans-serif; }
        .search-box { flex: 1; min-width: 250px; display: flex; align-items: center; gap: 10px; }
        .search-box input { border: none; outline: none; flex: 1; }
        .notices-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(400px, 1fr)); gap: 25px; }
        .notice-card { background: white; border-radius: 20px; overflow: hidden; transition: all 0.3s; border-left: 4px solid; }
        .notice-card.important { border-left-color: #f72585; }
        .notice-card.exam { border-left-color: #4361ee; }
        .notice-card.event { border-left-color: #4cc9f0; }
        .notice-card.holiday { border-left-color: #ffc107; }
        .notice-card:hover { transform: translateY(-5px); box-shadow: 0 10px 25px rgba(0,0,0,0.1); }
        .notice-header { padding: 20px 20px 0 20px; display: flex; justify-content: space-between; align-items: center; }
        .notice-badge { padding: 4px 12px; border-radius: 20px; font-size: 0.7rem; font-weight: 600; }
        .badge-important { background: rgba(247,37,133,0.1); color: #f72585; }
        .badge-exam { background: rgba(67,97,238,0.1); color: #4361ee; }
        .badge-event { background: rgba(76,201,240,0.1); color: #4cc9f0; }
        .badge-holiday { background: rgba(255,193,7,0.1); color: #ffc107; }
        .notice-title { font-size: 1.1rem; font-weight: 600; padding: 15px 20px 0 20px; }
        .notice-content { padding: 10px 20px; color: #6c757d; font-size: 0.9rem; line-height: 1.5; }
        .notice-footer { padding: 15px 20px 20px 20px; border-top: 1px solid #e9ecef; display: flex; justify-content: space-between; align-items: center; font-size: 0.8rem; color: #6c757d; }
        .notice-actions { display: flex; gap: 10px; }
        .edit-notice, .delete-notice { background: none; border: none; cursor: pointer; padding: 5px 10px; border-radius: 5px; }
        .edit-notice { color: #4cc9f0; }
        .delete-notice { color: #f72585; }
        @media (max-width: 768px) { .sidebar { transform: translateX(-100%); } .sidebar.active { transform: translateX(0); } .main-content { margin-left: 0; } .notices-grid { grid-template-columns: 1fr; } }
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
                <a href="notices.php" class="active"><i class="fas fa-bell"></i><span> Notices</span></a>
                <a href="add-notice.php"><i class="fas fa-plus"></i><span> Add Notice</span></a>
                <a href="announcements.php"><i class="fas fa-bullhorn"></i><span> Announcements</span></a>
                <a href="email-broadcast.php"><i class="fas fa-envelope"></i><span> Email Broadcast</span></a>
                <a href="../../logout.php"><i class="fas fa-sign-out-alt"></i><span> Logout</span></a>
            </nav>
        </aside>

        <main class="main-content" id="mainContent">
            <header class="top-header">
                <button class="mobile-menu-btn" id="mobileMenuBtn"><i class="fas fa-bars"></i></button>
                <div><h2>Academic Notices</h2></div>
                <div class="user-profile">
                    <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($user['full_name']); ?>&background=4361ee&color=fff" alt="Profile">
                    <div><h4><?php echo htmlspecialchars($user['full_name']); ?></h4><p>Administrator</p></div>
                </div>
            </header>

            <div class="header-actions">
                <button class="btn-add" onclick="window.location.href='add-notice.php'"><i class="fas fa-plus"></i> Post New Notice</button>
                <div class="search-box"><i class="fas fa-search"></i><input type="text" id="searchInput" placeholder="Search notices..."></div>
                <select class="filter-select" id="categoryFilter"><option value="all">All Categories</option><option value="important">Important</option><option value="exam">Examination</option><option value="event">Event</option><option value="holiday">Holiday</option></select>
            </div>

            <div class="notices-grid" id="noticesGrid"></div>
        </main>
    </div>

    <script>
        let notices = [
            { id: 1, title: 'End Semester Examination Schedule Released', category: 'exam', content: 'The schedule for end semester examinations has been released. Please check the exam timetable section for detailed schedule. All students must report 15 minutes before exam time.', date: '2024-03-20', postedBy: 'Admin', target: 'all' },
            { id: 2, title: 'College Holiday on March 25th', category: 'holiday', content: 'The college will remain closed on March 25th on account of Ram Navami. All classes and examinations scheduled for the day will be rescheduled.', date: '2024-03-18', postedBy: 'Admin', target: 'all' },
            { id: 3, title: 'Annual Tech Fest 2024', category: 'event', content: 'Registrations open for annual technical festival "TechFusion 2024". Events include coding competition, hackathon, and technical quiz. Last date for registration: March 30.', date: '2024-03-15', postedBy: 'Admin', target: 'student' },
            { id: 4, title: 'Library Timing Extended', category: 'important', content: 'Library timings have been extended during examination week. Library will remain open from 8 AM to 10 PM.', date: '2024-03-14', postedBy: 'Admin', target: 'all' }
        ];
        
        function loadNotices() {
            const category = document.getElementById('categoryFilter').value;
            const search = document.getElementById('searchInput').value.toLowerCase();
            
            let filtered = [...notices];
            if(category !== 'all') filtered = filtered.filter(n => n.category === category);
            if(search) filtered = filtered.filter(n => n.title.toLowerCase().includes(search) || n.content.toLowerCase().includes(search));
            
            const categoryNames = { important: 'Important', exam: 'Examination', event: 'Event', holiday: 'Holiday' };
            const grid = document.getElementById('noticesGrid');
            if(filtered.length === 0) {
                grid.innerHTML = '<div style="text-align:center;padding:40px;grid-column:1/-1;"><i class="fas fa-bell-slash" style="font-size:3rem;color:#6c757d;"></i><p>No notices found</p></div>';
                return;
            }
            grid.innerHTML = filtered.map(notice => `
                <div class="notice-card ${notice.category}">
                    <div class="notice-header"><span class="notice-badge badge-${notice.category}">${categoryNames[notice.category]}</span><span>${new Date(notice.date).toLocaleDateString()}</span></div>
                    <div class="notice-title">${notice.title}</div>
                    <div class="notice-content">${notice.content.substring(0, 120)}${notice.content.length > 120 ? '...' : ''}</div>
                    <div class="notice-footer"><span><i class="fas fa-user"></i> Posted by: ${notice.postedBy}</span><div class="notice-actions"><button class="edit-notice" onclick="editNotice(${notice.id})"><i class="fas fa-edit"></i></button><button class="delete-notice" onclick="deleteNotice(${notice.id})"><i class="fas fa-trash"></i></button></div></div>
                </div>
            `).join('');
        }
        
        function editNotice(id) { window.location.href = `add-notice.php?id=${id}`; }
        function deleteNotice(id) { if(confirm('Delete this notice?')) { notices = notices.filter(n => n.id !== id); loadNotices(); showNotification('Notice deleted!', 'success'); } }
        function showNotification(message, type) { const n = document.createElement('div'); n.className = 'notification'; n.style.background = type === 'success' ? '#4cc9f0' : '#f72585'; n.innerHTML = `<i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i> ${message}`; document.body.appendChild(n); setTimeout(() => n.remove(), 3000); }
        
        document.getElementById('categoryFilter').addEventListener('change', loadNotices);
        document.getElementById('searchInput').addEventListener('keyup', loadNotices);
        
        const sidebar = document.getElementById('sidebar'), mainContent = document.getElementById('mainContent'), toggleBtn = document.getElementById('toggleSidebar'), mobileMenuBtn = document.getElementById('mobileMenuBtn');
        if(toggleBtn) toggleBtn.addEventListener('click', function() { sidebar.classList.toggle('collapsed'); mainContent.classList.toggle('expanded'); });
        if(mobileMenuBtn) mobileMenuBtn.addEventListener('click', function() { sidebar.classList.toggle('active'); });
        function handleResponsive() { if(window.innerWidth <= 768) { sidebar.classList.add('collapsed'); mainContent.classList.add('expanded'); } else { sidebar.classList.remove('collapsed', 'active'); mainContent.classList.remove('expanded'); } }
        window.addEventListener('resize', handleResponsive); handleResponsive();
        loadNotices();
    </script>
</body>
</html>