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
    <title>Repository - EduTrack Pro</title>
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
        .repo-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; flex-wrap: wrap; gap: 15px; }
        .search-box { display: flex; align-items: center; background: white; border: 2px solid #e9ecef; border-radius: 10px; padding: 0 15px; }
        .search-box input { padding: 12px; border: none; outline: none; width: 250px; font-family: 'Poppins', sans-serif; }
        .category-buttons { display: flex; gap: 10px; margin-bottom: 25px; flex-wrap: wrap; }
        .category-btn { padding: 8px 20px; background: white; border: 2px solid #e9ecef; border-radius: 25px; cursor: pointer; transition: all 0.3s; }
        .category-btn.active, .category-btn:hover { background: #4361ee; color: white; border-color: #4361ee; }
        .repo-stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: white; padding: 20px; border-radius: 15px; text-align: center; }
        .stat-value { font-size: 1.8rem; font-weight: 700; color: #4361ee; }
        .repo-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 20px; }
        .repo-item { background: white; border-radius: 15px; padding: 20px; transition: all 0.3s; border: 1px solid #e9ecef; }
        .repo-item:hover { transform: translateY(-5px); box-shadow: 0 10px 25px rgba(0,0,0,0.1); border-color: #4361ee; }
        .repo-icon { width: 50px; height: 50px; border-radius: 12px; display: flex; align-items: center; justify-content: center; margin-bottom: 15px; font-size: 1.5rem; }
        .icon-pdf { background: rgba(247,37,133,0.1); color: #f72585; }
        .icon-doc { background: rgba(67,97,238,0.1); color: #4361ee; }
        .icon-video { background: rgba(76,201,240,0.1); color: #4cc9f0; }
        .repo-name { font-weight: 600; margin-bottom: 5px; }
        .repo-meta { font-size: 0.75rem; color: #6c757d; margin-bottom: 10px; }
        .repo-actions { display: flex; gap: 15px; margin-top: 15px; padding-top: 15px; border-top: 1px solid #e9ecef; }
        .repo-actions i { cursor: pointer; color: #6c757d; transition: color 0.3s; }
        .repo-actions i:hover { color: #4361ee; }
        @media (max-width: 768px) { .sidebar { transform: translateX(-100%); } .sidebar.active { transform: translateX(0); } .main-content { margin-left: 0; } }
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
                <a href="upload-material.php"><i class="fas fa-upload"></i><span> Upload Material</span></a>
                <a href="repository.php" class="active"><i class="fas fa-folder"></i><span> Repository</span></a>
                <a href="notices.php"><i class="fas fa-bell"></i><span> Notices</span></a>
                <a href="feedback-view.php"><i class="fas fa-star"></i><span> Feedback</span></a>
                <a href="../../logout.php"><i class="fas fa-sign-out-alt"></i><span> Logout</span></a>
            </nav>
        </aside>

        <main class="main-content" id="mainContent">
            <header class="top-header">
                <button class="mobile-menu-btn" id="mobileMenuBtn"><i class="fas fa-bars"></i></button>
                <div><h2>Study Repository</h2></div>
                <div class="user-profile">
                    <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($user['full_name']); ?>&background=4361ee&color=fff" alt="Profile">
                    <div><h4><?php echo htmlspecialchars($user['full_name']); ?></h4><p><?php echo htmlspecialchars($user['id_number']); ?></p></div>
                </div>
            </header>

            <div class="repo-header">
                <div class="search-box"><i class="fas fa-search"></i><input type="text" id="searchInput" placeholder="Search files..."></div>
                <button class="category-btn" onclick="window.location.href='upload-material.php'" style="background:#4361ee; color:white;"><i class="fas fa-plus"></i> Upload New</button>
            </div>

            <div class="repo-stats">
                <div class="stat-card"><div class="stat-value">156</div><div>Total Files</div></div>
                <div class="stat-card"><div class="stat-value">48</div><div>PDFs</div></div>
                <div class="stat-card"><div class="stat-value">2.4K</div><div>Downloads</div></div>
                <div class="stat-card"><div class="stat-value">3.2GB</div><div>Storage</div></div>
            </div>

            <div class="category-buttons">
                <button class="category-btn active" data-cat="all">All Files</button>
                <button class="category-btn" data-cat="pdf">PDFs</button>
                <button class="category-btn" data-cat="doc">Documents</button>
                <button class="category-btn" data-cat="video">Videos</button>
                <button class="category-btn" data-cat="assignment">Assignments</button>
                <button class="category-btn" data-cat="notes">Lecture Notes</button>
            </div>

            <div class="repo-grid" id="repoGrid"></div>
        </main>
    </div>

    <script>
        const materials = [
            { id: 1, name: 'Data Structures Lecture Notes.pdf', type: 'pdf', category: 'notes', size: '2.4 MB', date: '2024-03-20', subject: 'CS301', downloads: 45 },
            { id: 2, name: 'DBMS Assignment 2.docx', type: 'doc', category: 'assignment', size: '1.1 MB', date: '2024-03-19', subject: 'CS302', downloads: 32 },
            { id: 3, name: 'Web Development Tutorial.mp4', type: 'video', category: 'video', size: '45.2 MB', date: '2024-03-18', subject: 'CS303', downloads: 28 },
            { id: 4, name: 'Operating Systems Chapter 1.pdf', type: 'pdf', category: 'notes', size: '3.2 MB', date: '2024-03-17', subject: 'CS304', downloads: 56 },
            { id: 5, name: 'Computer Networks Reference.pdf', type: 'pdf', category: 'reference', size: '4.5 MB', date: '2024-03-16', subject: 'CS305', downloads: 34 }
        ];
        
        function loadMaterials() {
            const searchTerm = document.getElementById('searchInput').value.toLowerCase();
            let filtered = [...materials];
            if(searchTerm) filtered = filtered.filter(m => m.name.toLowerCase().includes(searchTerm));
            
            const repoGrid = document.getElementById('repoGrid');
            if(filtered.length === 0) {
                repoGrid.innerHTML = '<div style="text-align:center;padding:40px;grid-column:1/-1;"><i class="fas fa-folder-open" style="font-size:3rem;color:#6c757d;"></i><p>No files found</p></div>';
                return;
            }
            
            repoGrid.innerHTML = filtered.map(m => `
                <div class="repo-item">
                    <div class="repo-icon icon-${m.type}"><i class="fas ${m.type === 'pdf' ? 'fa-file-pdf' : m.type === 'doc' ? 'fa-file-word' : 'fa-file-video'}"></i></div>
                    <div class="repo-name">${m.name}</div>
                    <div class="repo-meta">${m.subject} • ${m.size} • ${new Date(m.date).toLocaleDateString()}</div>
                    <div class="repo-meta"><i class="fas fa-download"></i> ${m.downloads} downloads</div>
                    <div class="repo-actions">
                        <i class="fas fa-download" title="Download" onclick="downloadFile(${m.id})"></i>
                        <i class="fas fa-share-alt" title="Share" onclick="shareFile(${m.id})"></i>
                        <i class="fas fa-trash" title="Delete" onclick="deleteFile(${m.id})"></i>
                    </div>
                </div>
            `).join('');
        }
        
        function downloadFile(id) { showNotification('Download started...', 'success'); }
        function shareFile(id) { showNotification('Share link copied to clipboard!', 'success'); }
        function deleteFile(id) { if(confirm('Delete this file?')) { showNotification('File deleted', 'info'); loadMaterials(); } }
        
        function showNotification(message, type) {
            const notification = document.createElement('div');
            notification.style.cssText = `position:fixed;top:20px;right:20px;padding:15px 25px;background:${type === 'success' ? '#4cc9f0' : '#4361ee'};color:white;border-radius:10px;z-index:9999;animation:slideIn 0.3s ease;`;
            notification.innerHTML = `<i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-info-circle'}"></i> ${message}`;
            document.body.appendChild(notification);
            setTimeout(() => notification.remove(), 3000);
        }
        
        document.getElementById('searchInput').addEventListener('keyup', loadMaterials);
        document.querySelectorAll('.category-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                document.querySelectorAll('.category-btn').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                loadMaterials();
            });
        });
        
        const sidebar = document.getElementById('sidebar'), mainContent = document.getElementById('mainContent'), toggleBtn = document.getElementById('toggleSidebar'), mobileMenuBtn = document.getElementById('mobileMenuBtn');
        if(toggleBtn) toggleBtn.addEventListener('click', function() { sidebar.classList.toggle('collapsed'); mainContent.classList.toggle('expanded'); });
        if(mobileMenuBtn) mobileMenuBtn.addEventListener('click', function() { sidebar.classList.toggle('active'); });
        function handleResponsive() { if(window.innerWidth <= 768) { sidebar.classList.add('collapsed'); mainContent.classList.add('expanded'); } else { sidebar.classList.remove('collapsed', 'active'); mainContent.classList.remove('expanded'); } }
        window.addEventListener('resize', handleResponsive); handleResponsive();
        loadMaterials();
    </script>
</body>
</html>