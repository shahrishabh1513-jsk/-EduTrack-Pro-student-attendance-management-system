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
    <title>Upload Material - EduTrack Pro</title>
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
        .upload-container { max-width: 800px; margin: 0 auto; }
        .upload-card { background: white; border-radius: 20px; padding: 30px; margin-bottom: 30px; box-shadow: 0 2px 10px rgba(0,0,0,0.03); }
        .upload-area { border: 2px dashed #e9ecef; border-radius: 15px; padding: 40px; text-align: center; cursor: pointer; transition: all 0.3s; margin-bottom: 20px; }
        .upload-area:hover { border-color: #4361ee; background: rgba(67,97,238,0.02); }
        .upload-area i { font-size: 3rem; color: #4361ee; margin-bottom: 15px; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 500; }
        .form-control { width: 100%; padding: 12px; border: 2px solid #e9ecef; border-radius: 10px; font-family: 'Poppins', sans-serif; }
        .form-control:focus { outline: none; border-color: #4361ee; }
        .btn-upload { background: linear-gradient(135deg, #4361ee, #3f37c9); color: white; border: none; padding: 14px 30px; border-radius: 10px; font-size: 1rem; font-weight: 600; cursor: pointer; width: 100%; }
        .file-list { margin-top: 20px; }
        .file-item { display: flex; justify-content: space-between; align-items: center; padding: 12px; background: #f8f9fa; border-radius: 10px; margin-bottom: 10px; }
        .file-info { display: flex; align-items: center; gap: 10px; }
        .file-icon { width: 40px; height: 40px; background: rgba(67,97,238,0.1); border-radius: 10px; display: flex; align-items: center; justify-content: center; color: #4361ee; }
        .remove-file { color: #f72585; cursor: pointer; }
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
                <a href="upload-material.php" class="active"><i class="fas fa-upload"></i><span> Upload Material</span></a>
                <a href="repository.php"><i class="fas fa-folder"></i><span> Repository</span></a>
                <a href="notices.php"><i class="fas fa-bell"></i><span> Notices</span></a>
                <a href="feedback-view.php"><i class="fas fa-star"></i><span> Feedback</span></a>
                <a href="../../logout.php"><i class="fas fa-sign-out-alt"></i><span> Logout</span></a>
            </nav>
        </aside>

        <main class="main-content" id="mainContent">
            <header class="top-header">
                <button class="mobile-menu-btn" id="mobileMenuBtn"><i class="fas fa-bars"></i></button>
                <div><h2>Upload Study Material</h2></div>
                <div class="user-profile">
                    <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($user['full_name']); ?>&background=4361ee&color=fff" alt="Profile">
                    <div><h4><?php echo htmlspecialchars($user['full_name']); ?></h4><p><?php echo htmlspecialchars($user['id_number']); ?></p></div>
                </div>
            </header>

            <div class="upload-container">
                <div class="upload-card">
                    <h3><i class="fas fa-cloud-upload-alt"></i> Upload New Material</h3>
                    <p style="color:#6c757d; margin-bottom:20px;">Share study materials, notes, assignments with students</p>
                    
                    <div class="upload-area" id="dropZone">
                        <i class="fas fa-cloud-upload-alt"></i>
                        <p>Drag & drop files here or click to browse</p>
                        <p style="font-size:0.8rem; margin-top:5px;">Supported: PDF, DOC, PPT, MP4, ZIP (Max 50MB)</p>
                        <input type="file" id="fileInput" multiple style="display:none;">
                    </div>
                    
                    <div id="fileList" class="file-list"></div>
                    
                    <div class="form-group">
                        <label><i class="fas fa-book"></i> Select Subject</label>
                        <select class="form-control" id="subjectSelect">
                            <option value="">-- Select Subject --</option>
                            <option value="CS301">Data Structures (CS301)</option>
                            <option value="CS302">Database Management (CS302)</option>
                            <option value="CS303">Web Development (CS303)</option>
                            <option value="CS304">Operating Systems (CS304)</option>
                            <option value="CS305">Computer Networks (CS305)</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label><i class="fas fa-tag"></i> Material Type</label>
                        <select class="form-control" id="materialType">
                            <option value="notes">Lecture Notes</option>
                            <option value="assignment">Assignment</option>
                            <option value="reference">Reference Material</option>
                            <option value="video">Video Lecture</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label><i class="fas fa-heading"></i> Title</label>
                        <input type="text" class="form-control" id="materialTitle" placeholder="Enter title for this material">
                    </div>
                    
                    <div class="form-group">
                        <label><i class="fas fa-align-left"></i> Description (Optional)</label>
                        <textarea class="form-control" id="materialDesc" rows="3" placeholder="Brief description of the material"></textarea>
                    </div>
                    
                    <button class="btn-upload" onclick="uploadFiles()"><i class="fas fa-upload"></i> Upload to Repository</button>
                </div>
                
                <div class="upload-card">
                    <h3><i class="fas fa-history"></i> Recent Uploads</h3>
                    <div id="recentUploads"></div>
                </div>
            </div>
        </main>
    </div>

    <script>
        let selectedFiles = [];
        
        const dropZone = document.getElementById('dropZone');
        const fileInput = document.getElementById('fileInput');
        
        dropZone.addEventListener('click', () => fileInput.click());
        dropZone.addEventListener('dragover', (e) => { e.preventDefault(); dropZone.style.borderColor = '#4361ee'; });
        dropZone.addEventListener('dragleave', () => { dropZone.style.borderColor = '#e9ecef'; });
        dropZone.addEventListener('drop', (e) => {
            e.preventDefault();
            dropZone.style.borderColor = '#e9ecef';
            const files = Array.from(e.dataTransfer.files);
            addFiles(files);
        });
        
        fileInput.addEventListener('change', (e) => { addFiles(Array.from(e.target.files)); });
        
        function addFiles(files) {
            files.forEach(file => {
                if(!selectedFiles.find(f => f.name === file.name)) {
                    selectedFiles.push(file);
                }
            });
            displayFileList();
        }
        
        function displayFileList() {
            const fileListDiv = document.getElementById('fileList');
            if(selectedFiles.length === 0) {
                fileListDiv.innerHTML = '';
                return;
            }
            fileListDiv.innerHTML = '<h4>Selected Files:</h4>' + selectedFiles.map((file, index) => `
                <div class="file-item">
                    <div class="file-info">
                        <div class="file-icon"><i class="fas ${getFileIcon(file.name)}"></i></div>
                        <div><strong>${file.name}</strong><br><small>${(file.size / 1024).toFixed(2)} KB</small></div>
                    </div>
                    <div class="remove-file" onclick="removeFile(${index})"><i class="fas fa-trash"></i></div>
                </div>
            `).join('');
        }
        
        function getFileIcon(filename) {
            const ext = filename.split('.').pop().toLowerCase();
            if(ext === 'pdf') return 'fa-file-pdf';
            if(ext === 'doc' || ext === 'docx') return 'fa-file-word';
            if(ext === 'ppt' || ext === 'pptx') return 'fa-file-powerpoint';
            if(ext === 'mp4' || ext === 'webm') return 'fa-file-video';
            if(ext === 'zip' || ext === 'rar') return 'fa-file-archive';
            return 'fa-file';
        }
        
        function removeFile(index) {
            selectedFiles.splice(index, 1);
            displayFileList();
        }
        
        function uploadFiles() {
            const subject = document.getElementById('subjectSelect').value;
            const materialType = document.getElementById('materialType').value;
            const title = document.getElementById('materialTitle').value;
            
            if(!subject) { showNotification('Please select a subject', 'error'); return; }
            if(!title) { showNotification('Please enter a title', 'error'); return; }
            if(selectedFiles.length === 0) { showNotification('Please select files to upload', 'error'); return; }
            
            showNotification(`Uploading ${selectedFiles.length} file(s)...`, 'success');
            
            setTimeout(() => {
                showNotification('Files uploaded successfully!', 'success');
                selectedFiles = [];
                displayFileList();
                document.getElementById('materialTitle').value = '';
                loadRecentUploads();
            }, 2000);
        }
        
        function loadRecentUploads() {
            const recent = [
                { name: 'Data Structures Lecture 1.pdf', type: 'notes', date: '2024-03-20', subject: 'CS301' },
                { name: 'Assignment 2 - DBMS.docx', type: 'assignment', date: '2024-03-19', subject: 'CS302' },
                { name: 'Web Dev Tutorial Video.mp4', type: 'video', date: '2024-03-18', subject: 'CS303' }
            ];
            document.getElementById('recentUploads').innerHTML = recent.map(r => `
                <div class="file-item">
                    <div class="file-info"><div class="file-icon"><i class="fas ${getFileIcon(r.name)}"></i></div><div><strong>${r.name}</strong><br><small>${r.subject} • ${new Date(r.date).toLocaleDateString()}</small></div></div>
                    <i class="fas fa-download" style="color:#4361ee; cursor:pointer;"></i>
                </div>
            `).join('');
        }
        
        function showNotification(message, type) {
            const notification = document.createElement('div');
            notification.className = 'notification';
            notification.style.background = type === 'success' ? '#4cc9f0' : '#f72585';
            notification.innerHTML = `<i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i> ${message}`;
            document.body.appendChild(notification);
            setTimeout(() => notification.remove(), 3000);
        }
        
        const sidebar = document.getElementById('sidebar'), mainContent = document.getElementById('mainContent'), toggleBtn = document.getElementById('toggleSidebar'), mobileMenuBtn = document.getElementById('mobileMenuBtn');
        if(toggleBtn) toggleBtn.addEventListener('click', function() { sidebar.classList.toggle('collapsed'); mainContent.classList.toggle('expanded'); });
        if(mobileMenuBtn) mobileMenuBtn.addEventListener('click', function() { sidebar.classList.toggle('active'); });
        function handleResponsive() { if(window.innerWidth <= 768) { sidebar.classList.add('collapsed'); mainContent.classList.add('expanded'); } else { sidebar.classList.remove('collapsed', 'active'); mainContent.classList.remove('expanded'); } }
        window.addEventListener('resize', handleResponsive); handleResponsive();
        loadRecentUploads();
    </script>
</body>
</html>