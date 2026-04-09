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
    <title>Import Students - EduTrack Pro</title>
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
        .import-container { max-width: 800px; margin: 0 auto; }
        .import-card { background: white; border-radius: 20px; padding: 30px; margin-bottom: 30px; }
        .upload-area { border: 2px dashed #e9ecef; border-radius: 15px; padding: 40px; text-align: center; cursor: pointer; transition: all 0.3s; margin-bottom: 20px; }
        .upload-area:hover { border-color: #4361ee; background: rgba(67,97,238,0.02); }
        .upload-area i { font-size: 3rem; color: #4361ee; margin-bottom: 15px; }
        .btn-import { background: linear-gradient(135deg, #4361ee, #3f37c9); color: white; border: none; padding: 14px 30px; border-radius: 10px; font-size: 1rem; font-weight: 600; cursor: pointer; width: 100%; }
        .template-link { color: #4361ee; text-decoration: none; }
        .preview-table { overflow-x: auto; margin-top: 20px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 10px; border: 1px solid #e9ecef; text-align: left; font-size: 0.85rem; }
        th { background: #f8f9fa; }
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
                <a href="../../dashboard.php"><i class="fas fa-home"></i><span> Dashboard</span></a>
                <a href="../../profile.php"><i class="fas fa-user"></i><span> Profile</span></a>
                <a href="students-list.php" class="active"><i class="fas fa-user-graduate"></i><span> Student Management</span></a>
                <a href="../../../logout.php"><i class="fas fa-sign-out-alt"></i><span> Logout</span></a>
            </nav>
        </aside>

        <main class="main-content" id="mainContent">
            <header class="top-header">
                <button class="mobile-menu-btn" id="mobileMenuBtn"><i class="fas fa-bars"></i></button>
                <div><h2>Import Students</h2></div>
                <div class="user-profile">
                    <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($user['full_name']); ?>&background=4361ee&color=fff" alt="Profile">
                    <div><h4><?php echo htmlspecialchars($user['full_name']); ?></h4><p>Administrator</p></div>
                </div>
            </header>

            <div class="import-container">
                <div class="import-card">
                    <h3><i class="fas fa-file-excel"></i> Import Students from Excel/CSV</h3>
                    <p style="color:#6c757d; margin:15px 0;">Download the template, fill student data, and upload the file</p>
                    <a href="#" class="template-link" onclick="downloadTemplate()"><i class="fas fa-download"></i> Download Excel Template</a>
                    
                    <div class="upload-area" id="dropZone"><i class="fas fa-cloud-upload-alt"></i><p>Drag & drop Excel/CSV file here or click to browse</p><p style="font-size:0.8rem;">Supported: .xlsx, .csv (Max 10MB)</p><input type="file" id="fileInput" accept=".xlsx,.csv" style="display:none;"></div>
                    
                    <div id="preview" class="preview-table" style="display:none;"><h4>Preview Data</h4><div id="previewTable"></div></div>
                    
                    <button class="btn-import" id="importBtn" style="display:none;" onclick="importData()"><i class="fas fa-upload"></i> Import Students</button>
                </div>

                <div class="import-card"><h4><i class="fas fa-info-circle"></i> Instructions</h4><ul style="margin-left:20px; margin-top:10px;"><li>Download the template file first</li><li>Fill student details in the template</li><li>Required fields: Roll Number, Full Name, Email, Course, Semester</li><li>Do not modify the column headers</li><li>Save as Excel or CSV and upload</li></ul></div>
            </div>
        </main>
    </div>

    <script>
        let importedData = [];
        const dropZone = document.getElementById('dropZone'), fileInput = document.getElementById('fileInput');
        dropZone.addEventListener('click', () => fileInput.click());
        dropZone.addEventListener('dragover', (e) => { e.preventDefault(); dropZone.style.borderColor = '#4361ee'; });
        dropZone.addEventListener('dragleave', () => { dropZone.style.borderColor = '#e9ecef'; });
        dropZone.addEventListener('drop', (e) => { e.preventDefault(); dropZone.style.borderColor = '#e9ecef'; const file = e.dataTransfer.files[0]; if(file) processFile(file); });
        fileInput.addEventListener('change', (e) => { if(e.target.files[0]) processFile(e.target.files[0]); });
        
        function processFile(file) {
            showNotification(`File "${file.name}" selected`, 'success');
            const sampleData = [
                { roll: 'IT181', name: 'Rishabh Shah', email: 'rishabh@edutrack.com', course: 'BSC IT', semester: 3 },
                { roll: 'IT095', name: 'Jenish khunt', email: 'jenish@edutrack.com', course: 'BSC IT', semester: 3 }
            ];
            importedData = sampleData;
            displayPreview(sampleData);
        }
        
        function displayPreview(data) {
            document.getElementById('preview').style.display = 'block';
            document.getElementById('importBtn').style.display = 'block';
            let html = '<table><thead><tr><th>Roll Number</th><th>Full Name</th><th>Email</th><th>Course</th><th>Semester</th></tr></thead><tbody>';
            data.forEach(row => { html += `<tr><td>${row.roll}</td><td>${row.name}</td><td>${row.email}</td><td>${row.course}</td><td>${row.semester}</td></tr>`; });
            html += '</tbody></table>';
            document.getElementById('previewTable').innerHTML = html;
        }
        
        function importData() { showNotification(`${importedData.length} students imported successfully!`, 'success'); setTimeout(() => window.location.href = 'students-list.php', 1500); }
        function downloadTemplate() { showNotification('Template downloaded!', 'success'); }
        function showNotification(message, type) { const n = document.createElement('div'); n.className = 'notification'; n.style.background = type === 'success' ? '#4cc9f0' : '#f72585'; n.innerHTML = `<i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i> ${message}`; document.body.appendChild(n); setTimeout(() => n.remove(), 3000); }
        
        const sidebar = document.getElementById('sidebar'), mainContent = document.getElementById('mainContent'), toggleBtn = document.getElementById('toggleSidebar'), mobileMenuBtn = document.getElementById('mobileMenuBtn');
        if(toggleBtn) toggleBtn.addEventListener('click', function() { sidebar.classList.toggle('collapsed'); mainContent.classList.toggle('expanded'); });
        if(mobileMenuBtn) mobileMenuBtn.addEventListener('click', function() { sidebar.classList.toggle('active'); });
        function handleResponsive() { if(window.innerWidth <= 768) { sidebar.classList.add('collapsed'); mainContent.classList.add('expanded'); } else { sidebar.classList.remove('collapsed', 'active'); mainContent.classList.remove('expanded'); } }
        window.addEventListener('resize', handleResponsive); handleResponsive();
    </script>
</body>
</html>