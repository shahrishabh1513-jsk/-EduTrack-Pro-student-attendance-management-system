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
    <title>Grievance - EduTrack Pro</title>
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
        .grievance-container { display: grid; grid-template-columns: 1fr 1fr; gap: 30px; }
        .grievance-form { background: white; border-radius: 20px; padding: 30px; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 500; }
        .form-group input, .form-group select, .form-group textarea { width: 100%; padding: 12px; border: 2px solid #e9ecef; border-radius: 10px; font-family: 'Poppins', sans-serif; }
        .btn-submit { background: linear-gradient(135deg, #4361ee, #3f37c9); color: white; padding: 14px 30px; border: none; border-radius: 10px; font-size: 1rem; font-weight: 600; cursor: pointer; width: 100%; }
        .grievance-list { background: white; border-radius: 20px; padding: 30px; }
        .grievance-item { border: 1px solid #e9ecef; border-radius: 15px; padding: 20px; margin-bottom: 15px; }
        .status-pending { color: #ffc107; }
        .status-resolved { color: #4cc9f0; }
        .notification { position: fixed; top: 20px; right: 20px; padding: 15px 25px; border-radius: 10px; z-index: 9999; animation: slideIn 0.3s ease; color: white; }
        @keyframes slideIn { from { transform: translateX(100%); opacity: 0; } to { transform: translateX(0); opacity: 1; } }
        @media (max-width: 768px) { .grievance-container { grid-template-columns: 1fr; } .sidebar { transform: translateX(-100%); } .sidebar.active { transform: translateX(0); } .main-content { margin-left: 0; } }
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
                <a href="../dashboard.php"><i class="fas fa-home"></i><span> Dashboard</span></a>
                <a href="../profile.php"><i class="fas fa-user"></i><span> Profile</span></a>
                <a href="../attendance/view-attendance.php"><i class="fas fa-calendar-check"></i><span> Attendance</span></a>
                <a href="leave-apply.php"><i class="fas fa-calendar-plus"></i><span> Apply Leave</span></a>
                <a href="leave-status.php"><i class="fas fa-calendar-check"></i><span> Leave Status</span></a>
                <a href="grievance.php" class="active"><i class="fas fa-exclamation-circle"></i><span> Grievance</span></a>
                <a href="../logout.php"><i class="fas fa-sign-out-alt"></i><span> Logout</span></a>
            </nav>
        </aside>

        <main class="main-content" id="mainContent">
            <header class="top-header">
                <button class="mobile-menu-btn" id="mobileMenuBtn"><i class="fas fa-bars"></i></button>
                <div><h2>Grievance Portal</h2></div>
                <div class="user-profile">
                    <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($user['full_name']); ?>&background=4361ee&color=fff" alt="Profile">
                    <div><h4><?php echo htmlspecialchars($user['full_name']); ?></h4><p><?php echo htmlspecialchars($user['id_number']); ?></p></div>
                </div>
            </header>

            <div class="grievance-container">
                <div class="grievance-form"><h3 style="margin-bottom:20px;">Submit New Grievance</h3>
                    <form id="grievanceForm"><div class="form-group"><label>Category</label><select id="category" required><option value="academic">Academic</option><option value="administrative">Administrative</option><option value="technical">Technical</option><option value="other">Other</option></select></div>
                    <div class="form-group"><label>Title</label><input type="text" id="title" placeholder="Brief title of your grievance" required></div>
                    <div class="form-group"><label>Description</label><textarea id="description" rows="4" placeholder="Describe your grievance in detail" required></textarea></div>
                    <button type="submit" class="btn-submit">Submit Grievance</button></form>
                </div>
                <div class="grievance-list"><h3 style="margin-bottom:20px;">My Grievances</h3><div id="grievanceItems">Loading...</div></div>
            </div>
        </main>
    </div>

    <script>
        async function loadGrievances() {
            try {
                const response = await fetch('../../api/grievances.php?action=get_my');
                const data = await response.json();
                if(data.success && data.data.length > 0) {
                    const itemsHTML = data.data.map(g => `<div class="grievance-item"><div style="display:flex;justify-content:space-between;margin-bottom:10px;"><strong>${g.title}</strong><span class="status-${g.status}">${g.status.toUpperCase()}</span></div><p style="color:#6c757d;">${g.description}</p><p style="font-size:0.8rem;margin-top:10px;">Submitted: ${new Date(g.created_at).toLocaleDateString()}</p></div>`).join('');
                    document.getElementById('grievanceItems').innerHTML = itemsHTML;
                } else { document.getElementById('grievanceItems').innerHTML = '<p>No grievances found.</p>'; }
            } catch(error) { console.error('Error:', error); }
        }
        
        document.getElementById('grievanceForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            const grievanceData = { category: document.getElementById('category').value, title: document.getElementById('title').value, description: document.getElementById('description').value };
            try {
                const response = await fetch('../../api/grievances.php?action=submit', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(grievanceData) });
                const data = await response.json();
                if(data.success) { showNotification('Grievance submitted successfully!', 'success'); this.reset(); loadGrievances(); } else { showNotification(data.message, 'error'); }
            } catch(error) { showNotification('Error submitting grievance', 'error'); }
        });
        
        function showNotification(message, type) { const notification = document.createElement('div'); notification.className = 'notification'; notification.style.background = type === 'success' ? '#4cc9f0' : '#f72585'; notification.innerHTML = `<i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i> ${message}`; document.body.appendChild(notification); setTimeout(() => notification.remove(), 3000); }
        
        const sidebar = document.getElementById('sidebar'), mainContent = document.getElementById('mainContent'), toggleBtn = document.getElementById('toggleSidebar'), mobileMenuBtn = document.getElementById('mobileMenuBtn');
        if(toggleBtn) toggleBtn.addEventListener('click', function() { sidebar.classList.toggle('collapsed'); mainContent.classList.toggle('expanded'); });
        if(mobileMenuBtn) mobileMenuBtn.addEventListener('click', function() { sidebar.classList.toggle('active'); });
        function handleResponsive() { if(window.innerWidth <= 768) { sidebar.classList.add('collapsed'); mainContent.classList.add('expanded'); } else { sidebar.classList.remove('collapsed', 'active'); mainContent.classList.remove('expanded'); } }
        window.addEventListener('resize', handleResponsive); handleResponsive();
        loadGrievances();
    </script>
</body>
</html>