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
    <title>Exam Forms Verification - EduTrack Pro</title>
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
        .filter-bar { display: flex; gap: 15px; margin-bottom: 20px; flex-wrap: wrap; align-items: center; }
        .filter-select, .search-box { padding: 10px 15px; border: 2px solid #e9ecef; border-radius: 10px; font-family: 'Poppins', sans-serif; }
        .search-box { flex: 1; min-width: 250px; }
        .forms-table { background: white; border-radius: 20px; padding: 25px; overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #e9ecef; }
        th { background: #f8f9fa; font-weight: 600; }
        .verify-btn { background: #4cc9f0; color: white; border: none; padding: 5px 12px; border-radius: 5px; cursor: pointer; margin: 0 5px; }
        .reject-btn { background: #f72585; color: white; border: none; padding: 5px 12px; border-radius: 5px; cursor: pointer; }
        .view-btn { background: #4361ee; color: white; border: none; padding: 5px 12px; border-radius: 5px; cursor: pointer; }
        .status-pending { background: rgba(255,193,7,0.1); color: #ffc107; padding: 4px 10px; border-radius: 20px; font-size: 0.75rem; display: inline-block; }
        .status-verified { background: rgba(76,201,240,0.1); color: #4cc9f0; padding: 4px 10px; border-radius: 20px; font-size: 0.75rem; display: inline-block; }
        .batch-actions { display: flex; gap: 10px; margin-bottom: 20px; }
        .batch-btn { background: #e9ecef; border: none; padding: 8px 20px; border-radius: 8px; cursor: pointer; }
        .batch-btn:hover { background: #4361ee; color: white; }
        .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); justify-content: center; align-items: center; z-index: 1000; }
        .modal-content { background: white; border-radius: 20px; padding: 30px; max-width: 600px; width: 90%; }
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
                <a href="exam-schedule.php"><i class="fas fa-calendar-alt"></i><span> Exam Schedule</span></a>
                <a href="exam-forms-verification.php" class="active"><i class="fas fa-file-alt"></i><span> Exam Forms</span></a>
                <a href="hall-ticket-generate.php"><i class="fas fa-ticket-alt"></i><span> Hall Tickets</span></a>
                <a href="seat-allocation.php"><i class="fas fa-chair"></i><span> Seat Allocation</span></a>
                <a href="results-management.php"><i class="fas fa-chart-line"></i><span> Results</span></a>
                <a href="../../logout.php"><i class="fas fa-sign-out-alt"></i><span> Logout</span></a>
            </nav>
        </aside>

        <main class="main-content" id="mainContent">
            <header class="top-header">
                <button class="mobile-menu-btn" id="mobileMenuBtn"><i class="fas fa-bars"></i></button>
                <div><h2>Exam Forms Verification</h2></div>
                <div class="user-profile">
                    <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($user['full_name']); ?>&background=4361ee&color=fff" alt="Profile">
                    <div><h4><?php echo htmlspecialchars($user['full_name']); ?></h4><p>Administrator</p></div>
                </div>
            </header>

            <div class="stats-grid">
                <div class="stat-card"><div class="stat-value" id="pendingCount">0</div><div>Pending Forms</div></div>
                <div class="stat-card"><div class="stat-value" id="verifiedCount">0</div><div>Verified Forms</div></div>
                <div class="stat-card"><div class="stat-value" id="totalCount">0</div><div>Total Applications</div></div>
                <div class="stat-card"><div class="stat-value" id="totalFee">₹0</div><div>Total Collection</div></div>
            </div>

            <div class="filter-bar">
                <div class="search-box"><i class="fas fa-search"></i><input type="text" id="searchInput" placeholder="Search by name or roll number..."></div>
                <select class="filter-select" id="statusFilter"><option value="pending">Pending</option><option value="all">All Applications</option><option value="verified">Verified</option><option value="rejected">Rejected</option></select>
                <select class="filter-select" id="semesterFilter"><option value="all">All Semesters</option><option>1</option><option>2</option><option>3</option><option>4</option><option>5</option><option>6</option></select>
            </div>

            <div class="batch-actions">
                <button class="batch-btn" onclick="verifyAll()"><i class="fas fa-check-double"></i> Verify Selected</button>
                <button class="batch-btn" onclick="rejectAll()"><i class="fas fa-times"></i> Reject Selected</button>
                <button class="batch-btn" onclick="exportForms()"><i class="fas fa-download"></i> Export CSV</button>
            </div>

            <div class="forms-table">
                <h3 style="margin-bottom:20px;"><i class="fas fa-file-alt"></i> Exam Form Applications</h3>
                <div id="formsTable"></div>
            </div>
        </main>
    </div>

    <!-- Form Detail Modal -->
    <div id="formModal" class="modal">
        <div class="modal-content">
            <h3>Application Details</h3>
            <div id="modalBody"></div>
            <div style="margin-top:20px;"><button class="verify-btn" id="modalVerifyBtn">Verify & Approve</button><button class="reject-btn" id="modalRejectBtn">Reject</button><button class="view-btn" onclick="closeModal()">Close</button></div>
        </div>
    </div>

    <script>
        let applications = [
            { id: 1, student: 'Rishabh Shah', roll: 'IT181', semester: 3, fee: 3900, subjects: 5, status: 'pending', appliedDate: '2024-03-15', paymentId: 'PAY123456' },
            { id: 2, student: 'Jenish khunt', roll: 'IT095', semester: 3, fee: 3900, subjects: 5, status: 'pending', appliedDate: '2024-03-16', paymentId: 'PAY123457' },
            { id: 3, student: 'Vasu Motisarya', roll: 'IT124', semester: 3, fee: 3900, subjects: 5, status: 'verified', appliedDate: '2024-03-14', paymentId: 'PAY123458' },
            { id: 4, student: 'Hetvi Savani', roll: 'IT131', semester: 3, fee: 3900, subjects: 5, status: 'pending', appliedDate: '2024-03-17', paymentId: 'PAY123459' }
        ];
        
        let currentFormId = null;
        
        function loadForms() {
            const statusFilter = document.getElementById('statusFilter').value;
            const semesterFilter = document.getElementById('semesterFilter').value;
            const searchTerm = document.getElementById('searchInput').value.toLowerCase();
            
            let filtered = [...applications];
            if(statusFilter !== 'all') filtered = filtered.filter(f => f.status === statusFilter);
            if(semesterFilter !== 'all') filtered = filtered.filter(f => f.semester == semesterFilter);
            if(searchTerm) filtered = filtered.filter(f => f.student.toLowerCase().includes(searchTerm) || f.roll.toLowerCase().includes(searchTerm));
            
            const pending = applications.filter(f => f.status === 'pending').length;
            const verified = applications.filter(f => f.status === 'verified').length;
            const totalFee = applications.reduce((sum, f) => sum + f.fee, 0);
            document.getElementById('pendingCount').textContent = pending;
            document.getElementById('verifiedCount').textContent = verified;
            document.getElementById('totalCount').textContent = applications.length;
            document.getElementById('totalFee').textContent = '₹' + totalFee;
            
            const tableHTML = `<tr><thead><tr><th><input type="checkbox" id="selectAll"></th><th>Roll No</th><th>Student Name</th><th>Semester</th><th>Subjects</th><th>Fee</th><th>Applied Date</th><th>Status</th><th>Actions</th></tr></thead><tbody>
                ${filtered.map(f => `<tr><td><input type="checkbox" class="formCheckbox" data-id="${f.id}"></td><td><strong>${f.roll}</strong></td><td>${f.student}</td><td>Semester ${f.semester}</td><td>${f.subjects}</td><td>₹${f.fee}</td><td>${new Date(f.appliedDate).toLocaleDateString()}</td><td><span class="status-${f.status}">${f.status.toUpperCase()}</span></td><td><button class="view-btn" onclick="viewForm(${f.id})">View</button>${f.status === 'pending' ? `<button class="verify-btn" onclick="verifyForm(${f.id})">Verify</button><button class="reject-btn" onclick="rejectForm(${f.id})">Reject</button>` : ''}</td></tr>`).join('')}
            </tbody></table>`;
            document.getElementById('formsTable').innerHTML = tableHTML;
            
            document.getElementById('selectAll').onclick = function() { document.querySelectorAll('.formCheckbox').forEach(cb => cb.checked = this.checked); };
        }
        
        function viewForm(id) {
            const f = applications.find(a => a.id === id);
            if(f) {
                currentFormId = id;
                document.getElementById('modalBody').innerHTML = `<div style="margin-top:15px;"><p><strong>Student:</strong> ${f.student} (${f.roll})</p><p><strong>Semester:</strong> ${f.semester}</p><p><strong>Subjects Applied:</strong> ${f.subjects}</p><p><strong>Total Fee:</strong> ₹${f.fee}</p><p><strong>Payment ID:</strong> ${f.paymentId}</p><p><strong>Applied Date:</strong> ${new Date(f.appliedDate).toLocaleDateString()}</p><p><strong>Status:</strong> ${f.status.toUpperCase()}</p></div>`;
                document.getElementById('formModal').style.display = 'flex';
                document.getElementById('modalVerifyBtn').onclick = () => verifyForm(id);
                document.getElementById('modalRejectBtn').onclick = () => rejectForm(id);
            }
        }
        
        function verifyForm(id) { const index = applications.findIndex(a => a.id === id); if(index !== -1) { applications[index].status = 'verified'; loadForms(); if(currentFormId === id) closeModal(); showNotification('Form verified!', 'success'); } }
        function rejectForm(id) { const index = applications.findIndex(a => a.id === id); if(index !== -1) { applications[index].status = 'rejected'; loadForms(); if(currentFormId === id) closeModal(); showNotification('Form rejected!', 'info'); } }
        function verifyAll() { const selected = document.querySelectorAll('.formCheckbox:checked'); selected.forEach(cb => verifyForm(parseInt(cb.dataset.id))); }
        function rejectAll() { const selected = document.querySelectorAll('.formCheckbox:checked'); selected.forEach(cb => rejectForm(parseInt(cb.dataset.id))); }
        function exportForms() { showNotification('Exported!', 'success'); }
        function closeModal() { document.getElementById('formModal').style.display = 'none'; currentFormId = null; }
        function showNotification(message, type) { const n = document.createElement('div'); n.className = 'notification'; n.style.background = type === 'success' ? '#4cc9f0' : '#f72585'; n.innerHTML = `<i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i> ${message}`; document.body.appendChild(n); setTimeout(() => n.remove(), 3000); }
        
        document.getElementById('statusFilter').addEventListener('change', loadForms);
        document.getElementById('semesterFilter').addEventListener('change', loadForms);
        document.getElementById('searchInput').addEventListener('keyup', loadForms);
        
        const sidebar = document.getElementById('sidebar'), mainContent = document.getElementById('mainContent'), toggleBtn = document.getElementById('toggleSidebar'), mobileMenuBtn = document.getElementById('mobileMenuBtn');
        if(toggleBtn) toggleBtn.addEventListener('click', function() { sidebar.classList.toggle('collapsed'); mainContent.classList.toggle('expanded'); });
        if(mobileMenuBtn) mobileMenuBtn.addEventListener('click', function() { sidebar.classList.toggle('active'); });
        function handleResponsive() { if(window.innerWidth <= 768) { sidebar.classList.add('collapsed'); mainContent.classList.add('expanded'); } else { sidebar.classList.remove('collapsed', 'active'); mainContent.classList.remove('expanded'); } }
        window.addEventListener('resize', handleResponsive); handleResponsive();
        loadForms();
        window.onclick = function(event) { if(event.target == document.getElementById('formModal')) closeModal(); }
    </script>
</body>
</html>