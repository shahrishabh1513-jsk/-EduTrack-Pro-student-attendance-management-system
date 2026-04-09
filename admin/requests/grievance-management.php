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
    <title>Grievance Management - EduTrack Pro</title>
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
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: white; padding: 20px; border-radius: 15px; text-align: center; }
        .stat-value { font-size: 2rem; font-weight: 700; color: #4361ee; }
        .filter-bar { display: flex; gap: 15px; margin-bottom: 20px; flex-wrap: wrap; }
        .filter-select, .search-box { padding: 10px 15px; border: 2px solid #e9ecef; border-radius: 10px; font-family: 'Poppins', sans-serif; }
        .grievance-table { background: white; border-radius: 20px; padding: 25px; overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #e9ecef; }
        th { background: #f8f9fa; font-weight: 600; }
        .status-pending { background: rgba(255,193,7,0.1); color: #ffc107; padding: 4px 10px; border-radius: 20px; font-size: 0.75rem; display: inline-block; }
        .status-resolved { background: rgba(76,201,240,0.1); color: #4cc9f0; padding: 4px 10px; border-radius: 20px; font-size: 0.75rem; display: inline-block; }
        .status-progress { background: rgba(67,97,238,0.1); color: #4361ee; padding: 4px 10px; border-radius: 20px; font-size: 0.75rem; display: inline-block; }
        .resolve-btn { background: #4cc9f0; color: white; border: none; padding: 5px 12px; border-radius: 5px; cursor: pointer; }
        .view-btn { background: #4361ee; color: white; border: none; padding: 5px 12px; border-radius: 5px; cursor: pointer; }
        .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); justify-content: center; align-items: center; z-index: 1000; }
        .modal-content { background: white; border-radius: 20px; padding: 30px; max-width: 600px; width: 90%; }
        .form-group { margin-bottom: 15px; }
        .form-group textarea { width: 100%; padding: 10px; border: 2px solid #e9ecef; border-radius: 8px; font-family: 'Poppins', sans-serif; min-height: 100px; }
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
                <a href="leave-management.php"><i class="fas fa-calendar-alt"></i><span> Leave Management</span></a>
                <a href="grievance-management.php" class="active"><i class="fas fa-exclamation-circle"></i><span> Grievance</span></a>
                <a href="feedback-management.php"><i class="fas fa-star"></i><span> Feedback</span></a>
                <a href="../../logout.php"><i class="fas fa-sign-out-alt"></i><span> Logout</span></a>
            </nav>
        </aside>

        <main class="main-content" id="mainContent">
            <header class="top-header">
                <button class="mobile-menu-btn" id="mobileMenuBtn"><i class="fas fa-bars"></i></button>
                <div><h2>Grievance Management</h2></div>
                <div class="user-profile">
                    <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($user['full_name']); ?>&background=4361ee&color=fff" alt="Profile">
                    <div><h4><?php echo htmlspecialchars($user['full_name']); ?></h4><p>Administrator</p></div>
                </div>
            </header>

            <div class="stats-grid">
                <div class="stat-card"><div class="stat-value" id="pendingCount">0</div><div>Pending</div></div>
                <div class="stat-card"><div class="stat-value" id="progressCount">0</div><div>In Progress</div></div>
                <div class="stat-card"><div class="stat-value" id="resolvedCount">0</div><div>Resolved</div></div>
                <div class="stat-card"><div class="stat-value" id="totalCount">0</div><div>Total</div></div>
            </div>

            <div class="filter-bar">
                <div class="search-box"><i class="fas fa-search"></i><input type="text" id="searchInput" placeholder="Search by student or title..."></div>
                <select class="filter-select" id="statusFilter"><option value="all">All Status</option><option value="pending">Pending</option><option value="in_progress">In Progress</option><option value="resolved">Resolved</option></select>
                <select class="filter-select" id="categoryFilter"><option value="all">All Categories</option><option value="academic">Academic</option><option value="administrative">Administrative</option><option value="technical">Technical</option></select>
            </div>

            <div class="grievance-table"><h3 style="margin-bottom:20px;"><i class="fas fa-list"></i> Student Grievances</h3><div id="grievanceTable"></div></div>
        </main>
    </div>

    <div id="resolveModal" class="modal"><div class="modal-content"><h3>Resolve Grievance</h3><div id="resolveBody"></div><div class="form-group"><label>Resolution / Action Taken</label><textarea id="resolutionText" placeholder="Describe the resolution..."></textarea></div><button class="resolve-btn" onclick="submitResolution()">Submit Resolution</button><button class="view-btn" onclick="closeModal()" style="margin-top:10px;">Cancel</button></div></div>

    <script>
        let grievances = [
            { id: 1, student: 'Rishabh Shah', roll: 'IT181', category: 'academic', title: 'Assignment Grading Issue', description: 'Requesting re-evaluation of Assignment 3', status: 'pending', date: '2024-03-10' },
            { id: 2, student: 'Jenish khunt', roll: 'IT095', category: 'technical', title: 'LMS Access Problem', description: 'Unable to access student portal', status: 'in_progress', date: '2024-03-12' },
            { id: 3, student: 'Vasu Motisarya', roll: 'IT124', category: 'administrative', title: 'Scholarship Delay', description: 'Scholarship status pending', status: 'resolved', date: '2024-03-14', resolution: 'Scholarship approved' }
        ];
        
        let currentGrievanceId = null;
        const categoryNames = { academic: 'Academic', administrative: 'Administrative', technical: 'Technical', other: 'Other' };
        
        function loadGrievances() {
            const status = document.getElementById('statusFilter').value;
            const category = document.getElementById('categoryFilter').value;
            const search = document.getElementById('searchInput').value.toLowerCase();
            let filtered = [...grievances];
            if(status !== 'all') filtered = filtered.filter(g => g.status === status);
            if(category !== 'all') filtered = filtered.filter(g => g.category === category);
            if(search) filtered = filtered.filter(g => g.student.toLowerCase().includes(search) || g.title.toLowerCase().includes(search));
            
            const pending = grievances.filter(g => g.status === 'pending').length;
            const progress = grievances.filter(g => g.status === 'in_progress').length;
            const resolved = grievances.filter(g => g.status === 'resolved').length;
            document.getElementById('pendingCount').textContent = pending;
            document.getElementById('progressCount').textContent = progress;
            document.getElementById('resolvedCount').textContent = resolved;
            document.getElementById('totalCount').textContent = grievances.length;
            
            const tableHTML = `<table><thead><tr><th>Student</th><th>Roll</th><th>Category</th><th>Title</th><th>Date</th><th>Status</th><th>Actions</th></tr></thead><tbody>${filtered.map(g => `<tr><td><strong>${g.student}</strong></td><td>${g.roll}</td><td>${categoryNames[g.category]}</td><td>${g.title}</td><td>${new Date(g.date).toLocaleDateString()}</td><td><span class="status-${g.status === 'in_progress' ? 'progress' : g.status}">${g.status === 'in_progress' ? 'IN PROGRESS' : g.status.toUpperCase()}</span></td><td><button class="view-btn" onclick="viewGrievance(${g.id})">View</button>${g.status !== 'resolved' ? `<button class="resolve-btn" onclick="openResolveModal(${g.id})">Resolve</button>` : ''}</td></tr>`).join('')}</tbody></tr>`;
            document.getElementById('grievanceTable').innerHTML = tableHTML;
        }
        
        function viewGrievance(id) { const g = grievances.find(g => g.id === id); if(g) { alert(`Grievance Details:\n\nStudent: ${g.student} (${g.roll})\nCategory: ${categoryNames[g.category]}\nTitle: ${g.title}\nDescription: ${g.description}\nStatus: ${g.status}\nDate: ${new Date(g.date).toLocaleDateString()}\n${g.resolution ? `\nResolution: ${g.resolution}` : ''}`); } }
        function openResolveModal(id) { const g = grievances.find(g => g.id === id); if(g) { currentGrievanceId = id; document.getElementById('resolveBody').innerHTML = `<div style="margin-bottom:15px;"><p><strong>Student:</strong> ${g.student} (${g.roll})</p><p><strong>Title:</strong> ${g.title}</p><p><strong>Description:</strong> ${g.description}</p></div>`; document.getElementById('resolveModal').style.display = 'flex'; } }
        function submitResolution() { const resolution = document.getElementById('resolutionText').value; if(!resolution) { showNotification('Please enter a resolution', 'error'); return; } const index = grievances.findIndex(g => g.id === currentGrievanceId); if(index !== -1) { grievances[index].status = 'resolved'; grievances[index].resolution = resolution; grievances[index].resolvedBy = '<?php echo $user['full_name']; ?>'; loadGrievances(); closeModal(); showNotification('Grievance resolved!', 'success'); } }
        function closeModal() { document.getElementById('resolveModal').style.display = 'none'; currentGrievanceId = null; document.getElementById('resolutionText').value = ''; }
        function showNotification(message, type) { const n = document.createElement('div'); n.className = 'notification'; n.style.background = type === 'success' ? '#4cc9f0' : '#f72585'; n.innerHTML = `<i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i> ${message}`; document.body.appendChild(n); setTimeout(() => n.remove(), 3000); }
        
        document.getElementById('statusFilter').addEventListener('change', loadGrievances);
        document.getElementById('categoryFilter').addEventListener('change', loadGrievances);
        document.getElementById('searchInput').addEventListener('keyup', loadGrievances);
        
        const sidebar = document.getElementById('sidebar'), mainContent = document.getElementById('mainContent'), toggleBtn = document.getElementById('toggleSidebar'), mobileMenuBtn = document.getElementById('mobileMenuBtn');
        if(toggleBtn) toggleBtn.addEventListener('click', function() { sidebar.classList.toggle('collapsed'); mainContent.classList.toggle('expanded'); });
        if(mobileMenuBtn) mobileMenuBtn.addEventListener('click', function() { sidebar.classList.toggle('active'); });
        function handleResponsive() { if(window.innerWidth <= 768) { sidebar.classList.add('collapsed'); mainContent.classList.add('expanded'); } else { sidebar.classList.remove('collapsed', 'active'); mainContent.classList.remove('expanded'); } }
        window.addEventListener('resize', handleResponsive); handleResponsive();
        loadGrievances();
        window.onclick = function(event) { if(event.target == document.getElementById('resolveModal')) closeModal(); }
    </script>
</body>
</html>