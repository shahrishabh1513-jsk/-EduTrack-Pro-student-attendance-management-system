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
    <title>Exam Verification - EduTrack Pro</title>
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
        .stat-card { background: white; padding: 20px; border-radius: 15px; text-align: center; box-shadow: 0 2px 10px rgba(0,0,0,0.03); }
        .stat-value { font-size: 2rem; font-weight: 700; color: #4361ee; }
        .filter-bar { display: flex; gap: 15px; margin-bottom: 20px; flex-wrap: wrap; align-items: center; }
        .filter-select { padding: 10px 15px; border: 2px solid #e9ecef; border-radius: 10px; font-family: 'Poppins', sans-serif; }
        .search-box { padding: 10px 15px; border: 2px solid #e9ecef; border-radius: 10px; width: 250px; font-family: 'Poppins', sans-serif; }
        .verification-table { background: white; border-radius: 20px; padding: 25px; overflow-x: auto; box-shadow: 0 2px 10px rgba(0,0,0,0.03); }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #e9ecef; }
        th { background: #f8f9fa; font-weight: 600; }
        .verify-btn { background: #4cc9f0; color: white; border: none; padding: 6px 15px; border-radius: 5px; cursor: pointer; margin: 0 5px; transition: all 0.3s; }
        .reject-btn { background: #f72585; color: white; border: none; padding: 6px 15px; border-radius: 5px; cursor: pointer; transition: all 0.3s; }
        .view-btn { background: #4361ee; color: white; border: none; padding: 6px 15px; border-radius: 5px; cursor: pointer; }
        .status-pending { background: rgba(255,193,7,0.1); color: #ffc107; padding: 4px 10px; border-radius: 20px; font-size: 0.8rem; font-weight: 600; display: inline-block; }
        .status-verified { background: rgba(76,201,240,0.1); color: #4cc9f0; padding: 4px 10px; border-radius: 20px; font-size: 0.8rem; font-weight: 600; display: inline-block; }
        .status-rejected { background: rgba(247,37,133,0.1); color: #f72585; padding: 4px 10px; border-radius: 20px; font-size: 0.8rem; font-weight: 600; display: inline-block; }
        @media (max-width: 768px) { .sidebar { transform: translateX(-100%); } .sidebar.active { transform: translateX(0); } .main-content { margin-left: 0; } .filter-bar { flex-direction: column; } .search-box { width: 100%; } }
        .mobile-menu-btn { display: none; background: none; border: none; font-size: 1.5rem; cursor: pointer; }
        @media (max-width: 768px) { .mobile-menu-btn { display: block; } }
        .notification { position: fixed; top: 20px; right: 20px; padding: 15px 25px; border-radius: 10px; z-index: 9999; animation: slideIn 0.3s ease; color: white; }
        @keyframes slideIn { from { transform: translateX(100%); opacity: 0; } to { transform: translateX(0); opacity: 1; } }
        .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); justify-content: center; align-items: center; z-index: 1000; }
        .modal-content { background: white; border-radius: 20px; padding: 30px; max-width: 600px; width: 90%; max-height: 80vh; overflow-y: auto; }
        .close-modal { float: right; cursor: pointer; font-size: 1.5rem; color: #6c757d; }
        .close-modal:hover { color: #1e293b; }
        .batch-actions { display: flex; gap: 10px; margin-bottom: 20px; }
        .batch-btn { background: #e9ecef; border: none; padding: 8px 20px; border-radius: 8px; cursor: pointer; transition: all 0.3s; }
        .batch-btn:hover { background: #4361ee; color: white; }
        .export-btn { background: #28a745; color: white; border: none; padding: 10px 20px; border-radius: 8px; cursor: pointer; display: flex; align-items: center; gap: 8px; }
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
                <a href="exam-verification.php" class="active"><i class="fas fa-file-alt"></i><span> Exam Verification</span></a>
                <a href="marks-entry.php"><i class="fas fa-pen"></i><span> Marks Entry</span></a>
                <a href="results-declare.php"><i class="fas fa-chart-line"></i><span> Results Declare</span></a>
                <a href="../logout.php"><i class="fas fa-sign-out-alt"></i><span> Logout</span></a>
            </nav>
        </aside>

        <main class="main-content" id="mainContent">
            <header class="top-header">
                <button class="mobile-menu-btn" id="mobileMenuBtn"><i class="fas fa-bars"></i></button>
                <div><h2>Exam Form Verification</h2></div>
                <div class="user-profile">
                    <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($user['full_name']); ?>&background=4361ee&color=fff" alt="Profile">
                    <div><h4><?php echo htmlspecialchars($user['full_name']); ?></h4><p><?php echo htmlspecialchars($user['id_number']); ?></p></div>
                </div>
            </header>

            <div class="stats-grid">
                <div class="stat-card"><h3><i class="fas fa-clock"></i> Pending</h3><div class="stat-value" id="pendingCount">0</div><small>Awaiting Verification</small></div>
                <div class="stat-card"><h3><i class="fas fa-check-circle"></i> Verified</h3><div class="stat-value" id="verifiedCount">0</div><small>Applications Verified</small></div>
                <div class="stat-card"><h3><i class="fas fa-file-alt"></i> Total</h3><div class="stat-value" id="totalCount">0</div><small>Total Applications</small></div>
            </div>

            <div class="filter-bar">
                <select class="filter-select" id="classFilter">
                    <option value="all">All Classes</option>
                    <option value="CS301">Data Structures (CS301)</option>
                    <option value="CS302">Database Management (CS302)</option>
                    <option value="CS303">Web Development (CS303)</option>
                </select>
                <select class="filter-select" id="statusFilter">
                    <option value="all">All Status</option>
                    <option value="pending">Pending</option>
                    <option value="verified">Verified</option>
                    <option value="rejected">Rejected</option>
                </select>
                <input type="text" class="search-box" id="searchInput" placeholder="Search by name or roll number...">
                <button class="export-btn" onclick="exportToCSV()"><i class="fas fa-download"></i> Export</button>
            </div>

            <div class="batch-actions">
                <button class="batch-btn" onclick="verifyAll()"><i class="fas fa-check-double"></i> Verify All Pending</button>
                <button class="batch-btn" onclick="rejectAll()"><i class="fas fa-times"></i> Reject All Pending</button>
            </div>

            <div class="verification-table">
                <h3 style="margin-bottom:20px;"><i class="fas fa-list"></i> Exam Form Applications</h3>
                <div id="applicationsTable"></div>
            </div>
        </main>
    </div>

    <!-- Application Detail Modal -->
    <div id="appModal" class="modal">
        <div class="modal-content">
            <span class="close-modal" onclick="closeModal()">&times;</span>
            <h3 id="modalTitle"><i class="fas fa-file-alt"></i> Application Details</h3>
            <div id="modalBody"></div>
            <div style="margin-top:20px; display:flex; gap:10px; justify-content:flex-end;">
                <button class="verify-btn" id="modalVerifyBtn" onclick="verifyCurrent()"><i class="fas fa-check"></i> Verify & Approve</button>
                <button class="reject-btn" id="modalRejectBtn" onclick="rejectCurrent()"><i class="fas fa-times"></i> Reject</button>
            </div>
        </div>
    </div>

    <script>
        let applications = [
            { id: 1, student: 'Rishabh Sharma', roll: 'IT181', class: 'CS301', className: 'Data Structures', subjects: ['CS301 - Data Structures', 'CS302 - Database Management', 'CS303 - Web Development', 'CS304 - Operating Systems', 'CS305 - Computer Networks'], fee: 3900, status: 'pending', appliedDate: '2024-03-15', paymentId: 'PAY123456', paymentStatus: 'Paid' },
            { id: 2, student: 'Jenish Patel', roll: 'IT095', class: 'CS301', className: 'Data Structures', subjects: ['CS301 - Data Structures', 'CS302 - Database Management', 'CS303 - Web Development', 'CS304 - Operating Systems', 'CS305 - Computer Networks'], fee: 3900, status: 'pending', appliedDate: '2024-03-16', paymentId: 'PAY123457', paymentStatus: 'Paid' },
            { id: 3, student: 'Vasu Mehta', roll: 'IT124', class: 'CS301', className: 'Data Structures', subjects: ['CS301 - Data Structures', 'CS302 - Database Management', 'CS303 - Web Development', 'CS304 - Operating Systems', 'CS305 - Computer Networks'], fee: 3900, status: 'pending', appliedDate: '2024-03-14', paymentId: 'PAY123458', paymentStatus: 'Paid' },
            { id: 4, student: 'Hetvi Shah', roll: 'IT131', class: 'CS301', className: 'Data Structures', subjects: ['CS301 - Data Structures', 'CS302 - Database Management', 'CS303 - Web Development', 'CS304 - Operating Systems', 'CS305 - Computer Networks'], fee: 3900, status: 'verified', appliedDate: '2024-03-13', paymentId: 'PAY123459', paymentStatus: 'Paid', verifiedBy: 'Dr. Aakash Gupta', verifiedDate: '2024-03-14' },
            { id: 5, student: 'Aarav Desai', roll: 'CSIT001', class: 'CS302', className: 'Database Management', subjects: ['CS301 - Data Structures', 'CS302 - Database Management', 'CS303 - Web Development', 'CS304 - Operating Systems', 'CS305 - Computer Networks'], fee: 3900, status: 'pending', appliedDate: '2024-03-16', paymentId: 'PAY123460', paymentStatus: 'Paid' },
            { id: 6, student: 'Kiara Mehta', roll: 'CSIT002', class: 'CS302', className: 'Database Management', subjects: ['CS301 - Data Structures', 'CS302 - Database Management', 'CS303 - Web Development', 'CS304 - Operating Systems', 'CS305 - Computer Networks'], fee: 3900, status: 'rejected', appliedDate: '2024-03-12', paymentId: 'PAY123461', paymentStatus: 'Paid', remarks: 'Incomplete documents' }
        ];
        
        let currentAppId = null;
        
        function loadApplications() {
            const classFilter = document.getElementById('classFilter').value;
            const statusFilter = document.getElementById('statusFilter').value;
            const searchTerm = document.getElementById('searchInput').value.toLowerCase();
            
            let filtered = [...applications];
            if(classFilter !== 'all') filtered = filtered.filter(a => a.class === classFilter);
            if(statusFilter !== 'all') filtered = filtered.filter(a => a.status === statusFilter);
            if(searchTerm) filtered = filtered.filter(a => a.student.toLowerCase().includes(searchTerm) || a.roll.toLowerCase().includes(searchTerm));
            
            const pendingCount = applications.filter(a => a.status === 'pending').length;
            const verifiedCount = applications.filter(a => a.status === 'verified').length;
            document.getElementById('pendingCount').textContent = pendingCount;
            document.getElementById('verifiedCount').textContent = verifiedCount;
            document.getElementById('totalCount').textContent = applications.length;
            
            if(filtered.length === 0) {
                document.getElementById('applicationsTable').innerHTML = '<div style="text-align:center;padding:40px;"><i class="fas fa-inbox" style="font-size:3rem;color:#6c757d;"></i><p>No applications found</p></div>';
                return;
            }
            
            const tableHTML = `
                <table>
                    <thead>
                        <tr>
                            <th><input type="checkbox" id="selectAll" onchange="toggleSelectAll()"></th>
                            <th>Roll Number</th>
                            <th>Student Name</th>
                            <th>Class</th>
                            <th>Fee</th>
                            <th>Applied Date</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${filtered.map(a => `
                            <tr>
                                <td><input type="checkbox" class="appCheckbox" data-id="${a.id}"></td>
                                <td><strong>${a.roll}</strong></td>
                                <td>${a.student}</td>
                                <td>${a.className}</td>
                                <td>₹${a.fee}</td>
                                <td>${new Date(a.appliedDate).toLocaleDateString()}</td>
                                <td><span class="status-${a.status}">${a.status.toUpperCase()}</span></td>
                                <td>
                                    <button class="view-btn" onclick="viewApplication(${a.id})"><i class="fas fa-eye"></i> View</button>
                                    ${a.status === 'pending' ? `<button class="verify-btn" onclick="verifyApplication(${a.id})"><i class="fas fa-check"></i> Verify</button>
                                    <button class="reject-btn" onclick="rejectApplication(${a.id})"><i class="fas fa-times"></i> Reject</button>` : ''}
                                </td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            `;
            document.getElementById('applicationsTable').innerHTML = tableHTML;
        }
        
        function toggleSelectAll() {
            const selectAll = document.getElementById('selectAll');
            const checkboxes = document.querySelectorAll('.appCheckbox');
            checkboxes.forEach(cb => cb.checked = selectAll.checked);
        }
        
        function getSelectedIds() {
            const checkboxes = document.querySelectorAll('.appCheckbox:checked');
            return Array.from(checkboxes).map(cb => parseInt(cb.dataset.id));
        }
        
        function verifyAll() {
            const selectedIds = getSelectedIds();
            if(selectedIds.length === 0) {
                showNotification('Please select applications to verify', 'error');
                return;
            }
            if(confirm(`Verify ${selectedIds.length} application(s)?`)) {
                selectedIds.forEach(id => verifyApplication(id));
            }
        }
        
        function rejectAll() {
            const selectedIds = getSelectedIds();
            if(selectedIds.length === 0) {
                showNotification('Please select applications to reject', 'error');
                return;
            }
            if(confirm(`Reject ${selectedIds.length} application(s)?`)) {
                selectedIds.forEach(id => rejectApplication(id));
            }
        }
        
        function viewApplication(id) {
            const app = applications.find(a => a.id === id);
            if(app) {
                currentAppId = id;
                document.getElementById('modalTitle').innerHTML = `<i class="fas fa-file-alt"></i> Application Details - ${app.student}`;
                document.getElementById('modalBody').innerHTML = `
                    <div style="margin-top:20px;">
                        <div style="background:#f8f9fa; padding:15px; border-radius:10px; margin-bottom:15px;">
                            <h4>Student Information</h4>
                            <p><strong>Name:</strong> ${app.student}</p>
                            <p><strong>Roll Number:</strong> ${app.roll}</p>
                            <p><strong>Class:</strong> ${app.className}</p>
                        </div>
                        <div style="background:#f8f9fa; padding:15px; border-radius:10px; margin-bottom:15px;">
                            <h4>Exam Details</h4>
                            <p><strong>Applied Date:</strong> ${new Date(app.appliedDate).toLocaleDateString()}</p>
                            <p><strong>Total Fee:</strong> ₹${app.fee}</p>
                            <p><strong>Payment Status:</strong> <span style="color:#4cc9f0;">${app.paymentStatus}</span></p>
                            <p><strong>Payment ID:</strong> ${app.paymentId}</p>
                        </div>
                        <div style="background:#f8f9fa; padding:15px; border-radius:10px;">
                            <h4>Subjects Selected</h4>
                            <ul style="margin-left:20px;">
                                ${app.subjects.map(s => `<li>${s}</li>`).join('')}
                            </ul>
                        </div>
                        ${app.verifiedBy ? `<div style="background:#e8f4fd; padding:15px; border-radius:10px; margin-top:15px;">
                            <h4>Verification Details</h4>
                            <p><strong>Verified By:</strong> ${app.verifiedBy}</p>
                            <p><strong>Verified Date:</strong> ${new Date(app.verifiedDate).toLocaleDateString()}</p>
                        </div>` : ''}
                        ${app.remarks ? `<div style="background:#fde8e8; padding:15px; border-radius:10px; margin-top:15px;">
                            <h4>Remarks</h4>
                            <p>${app.remarks}</p>
                        </div>` : ''}
                    </div>
                `;
                document.getElementById('appModal').style.display = 'flex';
                
                // Show/hide verify/reject buttons based on status
                const verifyBtn = document.getElementById('modalVerifyBtn');
                const rejectBtn = document.getElementById('modalRejectBtn');
                if(app.status === 'pending') {
                    verifyBtn.style.display = 'inline-block';
                    rejectBtn.style.display = 'inline-block';
                } else {
                    verifyBtn.style.display = 'none';
                    rejectBtn.style.display = 'none';
                }
            }
        }
        
        function verifyApplication(id) {
            const index = applications.findIndex(a => a.id === id);
            if(index !== -1 && applications[index].status === 'pending') {
                applications[index].status = 'verified';
                applications[index].verifiedBy = '<?php echo $user['full_name']; ?>';
                applications[index].verifiedDate = new Date().toISOString().split('T')[0];
                showNotification(`Application for ${applications[index].student} verified successfully!`, 'success');
                loadApplications();
                if(currentAppId === id) closeModal();
            }
        }
        
        function rejectApplication(id) {
            const remarks = prompt('Please enter reason for rejection:');
            if(remarks) {
                const index = applications.findIndex(a => a.id === id);
                if(index !== -1 && applications[index].status === 'pending') {
                    applications[index].status = 'rejected';
                    applications[index].remarks = remarks;
                    showNotification(`Application for ${applications[index].student} rejected`, 'info');
                    loadApplications();
                    if(currentAppId === id) closeModal();
                }
            }
        }
        
        function verifyCurrent() {
            if(currentAppId) verifyApplication(currentAppId);
        }
        
        function rejectCurrent() {
            if(currentAppId) rejectApplication(currentAppId);
        }
        
        function closeModal() {
            document.getElementById('appModal').style.display = 'none';
            currentAppId = null;
        }
        
        function exportToCSV() {
            const classFilter = document.getElementById('classFilter').value;
            const statusFilter = document.getElementById('statusFilter').value;
            
            let filtered = [...applications];
            if(classFilter !== 'all') filtered = filtered.filter(a => a.class === classFilter);
            if(statusFilter !== 'all') filtered = filtered.filter(a => a.status === statusFilter);
            
            let csvContent = "Roll Number,Student Name,Class,Applied Date,Fee,Status,Payment ID\n";
            filtered.forEach(a => {
                csvContent += `${a.roll},${a.student},${a.className},${a.appliedDate},${a.fee},${a.status},${a.paymentId}\n`;
            });
            
            const blob = new Blob([csvContent], { type: 'text/csv' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'exam_applications.csv';
            a.click();
            URL.revokeObjectURL(url);
            showNotification('Exported successfully!', 'success');
        }
        
        function showNotification(message, type) {
            const notification = document.createElement('div');
            notification.className = 'notification';
            notification.style.background = type === 'success' ? '#4cc9f0' : type === 'error' ? '#f72585' : '#4361ee';
            notification.innerHTML = `<i class="fas ${type === 'success' ? 'fa-check-circle' : type === 'error' ? 'fa-exclamation-circle' : 'fa-info-circle'}"></i> ${message}`;
            document.body.appendChild(notification);
            setTimeout(() => notification.remove(), 3000);
        }
        
        document.getElementById('classFilter').addEventListener('change', loadApplications);
        document.getElementById('statusFilter').addEventListener('change', loadApplications);
        document.getElementById('searchInput').addEventListener('keyup', loadApplications);
        
        const sidebar = document.getElementById('sidebar'), mainContent = document.getElementById('mainContent'), toggleBtn = document.getElementById('toggleSidebar'), mobileMenuBtn = document.getElementById('mobileMenuBtn');
        if(toggleBtn) toggleBtn.addEventListener('click', function() { sidebar.classList.toggle('collapsed'); mainContent.classList.toggle('expanded'); });
        if(mobileMenuBtn) mobileMenuBtn.addEventListener('click', function() { sidebar.classList.toggle('active'); });
        function handleResponsive() { if(window.innerWidth <= 768) { sidebar.classList.add('collapsed'); mainContent.classList.add('expanded'); } else { sidebar.classList.remove('collapsed', 'active'); mainContent.classList.remove('expanded'); } }
        window.addEventListener('resize', handleResponsive); handleResponsive();
        
        loadApplications();
        
        window.onclick = function(event) { if(event.target == document.getElementById('appModal')) closeModal(); }
    </script>
</body>
</html>