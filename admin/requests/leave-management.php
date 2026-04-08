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
    <title>Leave Management - EduTrack Pro</title>
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
        .search-box { flex: 1; min-width: 250px; display: flex; align-items: center; gap: 10px; }
        .search-box input { border: none; outline: none; flex: 1; }
        .leave-table { background: white; border-radius: 20px; padding: 25px; overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #e9ecef; }
        th { background: #f8f9fa; font-weight: 600; }
        .status-pending { background: rgba(255,193,7,0.1); color: #ffc107; padding: 4px 10px; border-radius: 20px; font-size: 0.75rem; display: inline-block; }
        .status-approved { background: rgba(76,201,240,0.1); color: #4cc9f0; padding: 4px 10px; border-radius: 20px; font-size: 0.75rem; display: inline-block; }
        .status-rejected { background: rgba(247,37,133,0.1); color: #f72585; padding: 4px 10px; border-radius: 20px; font-size: 0.75rem; display: inline-block; }
        .view-btn, .approve-btn, .reject-btn { background: none; border: none; padding: 5px 12px; border-radius: 5px; cursor: pointer; margin: 0 5px; }
        .view-btn { background: #4361ee; color: white; }
        .approve-btn { background: #4cc9f0; color: white; }
        .reject-btn { background: #f72585; color: white; }
        .batch-actions { display: flex; gap: 10px; margin-bottom: 20px; }
        .batch-btn { background: #e9ecef; border: none; padding: 8px 20px; border-radius: 8px; cursor: pointer; }
        .batch-btn:hover { background: #4361ee; color: white; }
        .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); justify-content: center; align-items: center; z-index: 1000; }
        .modal-content { background: white; border-radius: 20px; padding: 30px; max-width: 500px; width: 90%; }
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
                <a href="leave-management.php" class="active"><i class="fas fa-calendar-alt"></i><span> Leave Management</span></a>
                <a href="leave-approval.php"><i class="fas fa-check-circle"></i><span> Leave Approval</span></a>
                <a href="leave-report.php"><i class="fas fa-chart-bar"></i><span> Leave Report</span></a>
                <a href="grievance-management.php"><i class="fas fa-exclamation-circle"></i><span> Grievance</span></a>
                <a href="feedback-management.php"><i class="fas fa-star"></i><span> Feedback</span></a>
                <a href="../../logout.php"><i class="fas fa-sign-out-alt"></i><span> Logout</span></a>
            </nav>
        </aside>

        <main class="main-content" id="mainContent">
            <header class="top-header">
                <button class="mobile-menu-btn" id="mobileMenuBtn"><i class="fas fa-bars"></i></button>
                <div><h2>Leave Management</h2></div>
                <div class="user-profile">
                    <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($user['full_name']); ?>&background=4361ee&color=fff" alt="Profile">
                    <div><h4><?php echo htmlspecialchars($user['full_name']); ?></h4><p>Administrator</p></div>
                </div>
            </header>

            <div class="stats-grid">
                <div class="stat-card"><div class="stat-value" id="pendingCount">0</div><div>Pending</div></div>
                <div class="stat-card"><div class="stat-value" id="approvedCount">0</div><div>Approved</div></div>
                <div class="stat-card"><div class="stat-value" id="rejectedCount">0</div><div>Rejected</div></div>
                <div class="stat-card"><div class="stat-value" id="totalCount">0</div><div>Total</div></div>
            </div>

            <div class="filter-bar">
                <div class="search-box"><i class="fas fa-search"></i><input type="text" id="searchInput" placeholder="Search by student name or roll..."></div>
                <select class="filter-select" id="statusFilter"><option value="all">All Status</option><option value="pending">Pending</option><option value="approved">Approved</option><option value="rejected">Rejected</option></select>
                <select class="filter-select" id="typeFilter"><option value="all">All Types</option><option value="medical">Medical</option><option value="emergency">Emergency</option><option value="personal">Personal</option><option value="vacation">Vacation</option></select>
                <button class="batch-btn" onclick="exportData()"><i class="fas fa-download"></i> Export</button>
            </div>

            <div class="batch-actions">
                <button class="batch-btn" onclick="approveSelected()"><i class="fas fa-check-double"></i> Approve Selected</button>
                <button class="batch-btn" onclick="rejectSelected()"><i class="fas fa-times"></i> Reject Selected</button>
            </div>

            <div class="leave-table">
                <h3 style="margin-bottom:20px;"><i class="fas fa-calendar-alt"></i> Leave Applications</h3>
                <div id="leaveTable"></div>
            </div>
        </main>
    </div>

    <!-- Leave Detail Modal -->
    <div id="leaveModal" class="modal">
        <div class="modal-content">
            <h3>Leave Application Details</h3>
            <div id="modalBody"></div>
            <div style="margin-top:20px;"><button class="approve-btn" id="modalApproveBtn">Approve</button><button class="reject-btn" id="modalRejectBtn">Reject</button><button class="view-btn" onclick="closeModal()">Close</button></div>
        </div>
    </div>

    <script>
        let leaves = [
            { id: 1, student: 'Rishabh Sharma', roll: 'IT181', type: 'medical', fromDate: '2024-03-20', toDate: '2024-03-22', days: 3, reason: 'Viral fever', status: 'pending', appliedDate: '2024-03-19', contact: '9876543210' },
            { id: 2, student: 'Jenish Patel', roll: 'IT095', type: 'personal', fromDate: '2024-03-25', toDate: '2024-03-26', days: 2, reason: 'Family function', status: 'pending', appliedDate: '2024-03-18', contact: '9876543211' },
            { id: 3, student: 'Vasu Mehta', roll: 'IT124', type: 'emergency', fromDate: '2024-03-18', toDate: '2024-03-19', days: 2, reason: 'Urgent matter', status: 'approved', appliedDate: '2024-03-17', contact: '9876543212', approvedBy: 'Dr. Aakash Gupta' },
            { id: 4, student: 'Hetvi Shah', roll: 'IT131', type: 'medical', fromDate: '2024-03-15', toDate: '2024-03-16', days: 2, reason: 'Doctor appointment', status: 'rejected', appliedDate: '2024-03-14', contact: '9876543213', remarks: 'Insufficient documentation' }
        ];
        
        let currentLeaveId = null;
        
        function loadLeaves() {
            const status = document.getElementById('statusFilter').value;
            const type = document.getElementById('typeFilter').value;
            const search = document.getElementById('searchInput').value.toLowerCase();
            
            let filtered = [...leaves];
            if(status !== 'all') filtered = filtered.filter(l => l.status === status);
            if(type !== 'all') filtered = filtered.filter(l => l.type === type);
            if(search) filtered = filtered.filter(l => l.student.toLowerCase().includes(search) || l.roll.toLowerCase().includes(search));
            
            const pending = leaves.filter(l => l.status === 'pending').length;
            const approved = leaves.filter(l => l.status === 'approved').length;
            const rejected = leaves.filter(l => l.status === 'rejected').length;
            document.getElementById('pendingCount').textContent = pending;
            document.getElementById('approvedCount').textContent = approved;
            document.getElementById('rejectedCount').textContent = rejected;
            document.getElementById('totalCount').textContent = leaves.length;
            
            const typeNames = { medical: 'Medical', personal: 'Personal', emergency: 'Emergency', vacation: 'Vacation' };
            const tableHTML = `<table><thead><tr><th><input type="checkbox" id="selectAll"></th><th>Student</th><th>Roll No</th><th>Type</th><th>Duration</th><th>From Date</th><th>To Date</th><th>Status</th><th>Actions</th></tr></thead><tbody>
                ${filtered.map(l => `<tr><td><input type="checkbox" class="leaveCheckbox" data-id="${l.id}"></td><td><strong>${l.student}</strong></td><td>${l.roll}</td><td>${typeNames[l.type]}</td><td>${l.days} days</td><td>${new Date(l.fromDate).toLocaleDateString()}</td><td>${new Date(l.toDate).toLocaleDateString()}</td><td><span class="status-${l.status}">${l.status.toUpperCase()}</span></td><td><button class="view-btn" onclick="viewLeave(${l.id})">View</button>${l.status === 'pending' ? `<button class="approve-btn" onclick="approveLeave(${l.id})">Approve</button><button class="reject-btn" onclick="rejectLeave(${l.id})">Reject</button>` : ''}</td></tr>`).join('')}
            </tbody></table>`;
            document.getElementById('leaveTable').innerHTML = tableHTML;
            document.getElementById('selectAll').onclick = function() { document.querySelectorAll('.leaveCheckbox').forEach(cb => cb.checked = this.checked); };
        }
        
        function viewLeave(id) {
            const l = leaves.find(l => l.id === id);
            if(l) {
                currentLeaveId = id;
                const typeNames = { medical: 'Medical Leave', personal: 'Personal Leave', emergency: 'Emergency', vacation: 'Vacation' };
                document.getElementById('modalBody').innerHTML = `<div><p><strong>Student:</strong> ${l.student} (${l.roll})</p><p><strong>Type:</strong> ${typeNames[l.type]}</p><p><strong>Duration:</strong> ${l.days} days (${new Date(l.fromDate).toLocaleDateString()} - ${new Date(l.toDate).toLocaleDateString()})</p><p><strong>Reason:</strong> ${l.reason}</p><p><strong>Contact:</strong> ${l.contact}</p><p><strong>Applied:</strong> ${new Date(l.appliedDate).toLocaleDateString()}</p>${l.approvedBy ? `<p><strong>Approved By:</strong> ${l.approvedBy}</p>` : ''}${l.remarks ? `<p><strong>Remarks:</strong> ${l.remarks}</p>` : ''}</div>`;
                document.getElementById('leaveModal').style.display = 'flex';
                document.getElementById('modalApproveBtn').onclick = () => approveLeave(id);
                document.getElementById('modalRejectBtn').onclick = () => rejectLeave(id);
            }
        }
        
        function approveLeave(id) { const index = leaves.findIndex(l => l.id === id); if(index !== -1) { leaves[index].status = 'approved'; leaves[index].approvedBy = '<?php echo $user['full_name']; ?>'; loadLeaves(); if(currentLeaveId === id) closeModal(); showNotification('Leave approved!', 'success'); } }
        function rejectLeave(id) { const remarks = prompt('Enter reason for rejection:'); if(remarks) { const index = leaves.findIndex(l => l.id === id); if(index !== -1) { leaves[index].status = 'rejected'; leaves[index].remarks = remarks; loadLeaves(); if(currentLeaveId === id) closeModal(); showNotification('Leave rejected!', 'info'); } } }
        function approveSelected() { document.querySelectorAll('.leaveCheckbox:checked').forEach(cb => approveLeave(parseInt(cb.dataset.id))); }
        function rejectSelected() { document.querySelectorAll('.leaveCheckbox:checked').forEach(cb => rejectLeave(parseInt(cb.dataset.id))); }
        function exportData() { showNotification('Data exported!', 'success'); }
        function closeModal() { document.getElementById('leaveModal').style.display = 'none'; currentLeaveId = null; }
        function showNotification(message, type) { const n = document.createElement('div'); n.className = 'notification'; n.style.background = type === 'success' ? '#4cc9f0' : '#f72585'; n.innerHTML = `<i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i> ${message}`; document.body.appendChild(n); setTimeout(() => n.remove(), 3000); }
        
        document.getElementById('statusFilter').addEventListener('change', loadLeaves);
        document.getElementById('typeFilter').addEventListener('change', loadLeaves);
        document.getElementById('searchInput').addEventListener('keyup', loadLeaves);
        
        const sidebar = document.getElementById('sidebar'), mainContent = document.getElementById('mainContent'), toggleBtn = document.getElementById('toggleSidebar'), mobileMenuBtn = document.getElementById('mobileMenuBtn');
        if(toggleBtn) toggleBtn.addEventListener('click', function() { sidebar.classList.toggle('collapsed'); mainContent.classList.toggle('expanded'); });
        if(mobileMenuBtn) mobileMenuBtn.addEventListener('click', function() { sidebar.classList.toggle('active'); });
        function handleResponsive() { if(window.innerWidth <= 768) { sidebar.classList.add('collapsed'); mainContent.classList.add('expanded'); } else { sidebar.classList.remove('collapsed', 'active'); mainContent.classList.remove('expanded'); } }
        window.addEventListener('resize', handleResponsive); handleResponsive();
        loadLeaves();
        window.onclick = function(event) { if(event.target == document.getElementById('leaveModal')) closeModal(); }
    </script>
</body>
</html>