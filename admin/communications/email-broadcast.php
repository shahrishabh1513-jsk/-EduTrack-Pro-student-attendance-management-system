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
    <title>Email Broadcast - EduTrack Pro</title>
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
        .broadcast-container { max-width: 800px; margin: 0 auto; }
        .broadcast-card { background: white; border-radius: 20px; padding: 30px; margin-bottom: 30px; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 500; }
        .form-control { width: 100%; padding: 12px; border: 2px solid #e9ecef; border-radius: 8px; font-family: 'Poppins', sans-serif; }
        .form-control:focus { outline: none; border-color: #4361ee; }
        textarea.form-control { min-height: 150px; resize: vertical; }
        .recipient-options { display: flex; gap: 20px; margin-bottom: 20px; flex-wrap: wrap; }
        .recipient-option { display: flex; align-items: center; gap: 8px; }
        .btn-send { background: linear-gradient(135deg, #4361ee, #3f37c9); color: white; border: none; padding: 14px 30px; border-radius: 10px; font-size: 1rem; font-weight: 600; cursor: pointer; width: 100%; }
        .preview-card { background: #f8f9fa; border-radius: 15px; padding: 20px; margin-top: 20px; }
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: white; padding: 20px; border-radius: 15px; text-align: center; }
        .stat-value { font-size: 2rem; font-weight: 700; color: #4361ee; }
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
                <a href="announcements.php"><i class="fas fa-bullhorn"></i><span> Announcements</span></a>
                <a href="email-broadcast.php" class="active"><i class="fas fa-envelope"></i><span> Email Broadcast</span></a>
                <a href="../../logout.php"><i class="fas fa-sign-out-alt"></i><span> Logout</span></a>
            </nav>
        </aside>

        <main class="main-content" id="mainContent">
            <header class="top-header">
                <button class="mobile-menu-btn" id="mobileMenuBtn"><i class="fas fa-bars"></i></button>
                <div><h2>Email Broadcast</h2></div>
                <div class="user-profile">
                    <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($user['full_name']); ?>&background=4361ee&color=fff" alt="Profile">
                    <div><h4><?php echo htmlspecialchars($user['full_name']); ?></h4><p>Administrator</p></div>
                </div>
            </header>

            <div class="stats-grid">
                <div class="stat-card"><div class="stat-value">2,547</div><div>Total Students</div></div>
                <div class="stat-card"><div class="stat-value">128</div><div>Faculty Members</div></div>
                <div class="stat-card"><div class="stat-value">2,675</div><div>Total Recipients</div></div>
                <div class="stat-card"><div class="stat-value">98%</div><div>Delivery Rate</div></div>
            </div>

            <div class="broadcast-container">
                <div class="broadcast-card">
                    <h3><i class="fas fa-envelope"></i> Compose Email Broadcast</h3>
                    <form id="emailForm">
                        <div class="form-group"><label>Subject *</label><input type="text" class="form-control" id="emailSubject" placeholder="Enter email subject" required></div>
                        <div class="form-group"><label>Recipients *</label><div class="recipient-options"><label class="recipient-option"><input type="radio" name="recipient" value="all" checked> All Users</label><label class="recipient-option"><input type="radio" name="recipient" value="students"> Students Only</label><label class="recipient-option"><input type="radio" name="recipient" value="faculty"> Faculty Only</label><label class="recipient-option"><input type="radio" name="recipient" value="custom"> Custom List</label></div></div>
                        <div class="form-group" id="customEmailGroup" style="display:none;"><label>Custom Email Addresses (comma separated)</label><input type="text" class="form-control" id="customEmails" placeholder="email1@example.com, email2@example.com"></div>
                        <div class="form-group"><label>Message *</label><textarea class="form-control" id="emailMessage" rows="8" placeholder="Write your email message here..."></textarea></div>
                        <div class="preview-card"><h4><i class="fas fa-eye"></i> Preview</h4><div id="previewContent"></div></div>
                        <button type="submit" class="btn-send"><i class="fas fa-paper-plane"></i> Send Broadcast</button>
                    </form>
                </div>
                <div class="broadcast-card"><h3><i class="fas fa-history"></i> Recent Broadcasts</h3><div id="recentBroadcasts"></div></div>
            </div>
        </main>
    </div>

    <script>
        let recentBroadcasts = [
            { subject: 'Examination Schedule', date: '2024-03-20', recipients: 'All Students', status: 'Sent' },
            { subject: 'Holiday Notice', date: '2024-03-18', recipients: 'All Users', status: 'Sent' }
        ];
        
        function updatePreview() {
            const subject = document.getElementById('emailSubject').value;
            const message = document.getElementById('emailMessage').value;
            document.getElementById('previewContent').innerHTML = `<div style="background:white; padding:15px; border-radius:10px;"><strong>Subject:</strong> ${subject || 'Your Subject Here'}<br><br><strong>Message:</strong><br>${message || 'Your message will appear here...'}</div>`;
        }
        
        document.getElementById('emailSubject').addEventListener('input', updatePreview);
        document.getElementById('emailMessage').addEventListener('input', updatePreview);
        
        document.querySelectorAll('input[name="recipient"]').forEach(radio => {
            radio.addEventListener('change', function() {
                document.getElementById('customEmailGroup').style.display = this.value === 'custom' ? 'block' : 'none';
            });
        });
        
        document.getElementById('emailForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const subject = document.getElementById('emailSubject').value;
            if(!subject) { showNotification('Please enter a subject', 'error'); return; }
            showNotification('Email broadcast sent successfully!', 'success');
            this.reset();
            updatePreview();
            document.getElementById('customEmailGroup').style.display = 'none';
            document.querySelector('input[value="all"]').checked = true;
        });
        
        function loadRecentBroadcasts() {
            const html = recentBroadcasts.map(b => `<div style="display:flex; justify-content:space-between; align-items:center; padding:12px 0; border-bottom:1px solid #e9ecef;"><div><strong>${b.subject}</strong><br><small>To: ${b.recipients}</small></div><div><span style="color:#4cc9f0;">${b.status}</span><br><small>${b.date}</small></div></div>`).join('');
            document.getElementById('recentBroadcasts').innerHTML = html || '<p>No recent broadcasts</p>';
        }
        
        function showNotification(message, type) { const n = document.createElement('div'); n.className = 'notification'; n.style.background = type === 'success' ? '#4cc9f0' : '#f72585'; n.innerHTML = `<i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i> ${message}`; document.body.appendChild(n); setTimeout(() => n.remove(), 3000); }
        
        const sidebar = document.getElementById('sidebar'), mainContent = document.getElementById('mainContent'), toggleBtn = document.getElementById('toggleSidebar'), mobileMenuBtn = document.getElementById('mobileMenuBtn');
        if(toggleBtn) toggleBtn.addEventListener('click', function() { sidebar.classList.toggle('collapsed'); mainContent.classList.toggle('expanded'); });
        if(mobileMenuBtn) mobileMenuBtn.addEventListener('click', function() { sidebar.classList.toggle('active'); });
        function handleResponsive() { if(window.innerWidth <= 768) { sidebar.classList.add('collapsed'); mainContent.classList.add('expanded'); } else { sidebar.classList.remove('collapsed', 'active'); mainContent.classList.remove('expanded'); } }
        window.addEventListener('resize', handleResponsive); handleResponsive();
        updatePreview();
        loadRecentBroadcasts();
    </script>
</body>
</html>