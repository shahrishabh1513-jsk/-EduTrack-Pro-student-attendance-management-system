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
    <title>Leave Requests - EduTrack Pro</title>
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
        .leave-table { background: white; border-radius: 20px; padding: 25px; overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #e9ecef; }
        th { background: #f8f9fa; font-weight: 600; }
        .status-pending { background: rgba(255,193,7,0.1); color: #ffc107; padding: 4px 10px; border-radius: 20px; font-size: 0.8rem; display: inline-block; }
        .status-approved { background: rgba(76,201,240,0.1); color: #4cc9f0; padding: 4px 10px; border-radius: 20px; font-size: 0.8rem; display: inline-block; }
        .status-rejected { background: rgba(247,37,133,0.1); color: #f72585; padding: 4px 10px; border-radius: 20px; font-size: 0.8rem; display: inline-block; }
        .approve-btn { background: #4cc9f0; color: white; border: none; padding: 5px 12px; border-radius: 5px; cursor: pointer; margin: 0 5px; }
        .reject-btn { background: #f72585; color: white; border: none; padding: 5px 12px; border-radius: 5px; cursor: pointer; margin: 0 5px; }
        .view-btn { background: #4361ee; color: white; border: none; padding: 5px 12px; border-radius: 5px; cursor: pointer; }
        .batch-actions { display: flex; gap: 10px; margin-bottom: 20px; }
        .batch-btn { background: #e9ecef; border: none; padding: 8px 20px; border-radius: 8px; cursor: pointer; transition: all 0.3s; }
        .batch-btn:hover { background: #4361ee; color: white; }
        @media (max-width: 768px) { .sidebar { transform: translateX(-100%); } .sidebar.active { transform: translateX(0); } .main-content { margin-left: 0; } .filter-bar { flex-direction: column; } .search-box { width: 100%; } }
        .mobile-menu-btn { display: none; background: none; border: none; font-size: 1.5rem; cursor: pointer; }
        @media (max-width: 768px) { .mobile-menu-btn { display: block; } }
        .notification { position: fixed; top: 20px; right: 20px; padding: 15px 25px; border-radius: 10px; z-index: 9999; animation: slideIn 0.3s ease; color: white; }
        @keyframes slideIn { from { transform: translateX(100%); opacity: 0; } to { transform: translateX(0); opacity: 1; } }
        .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); justify-content: center; align-items: center; z-index: 1000; }
        .modal-content { background: white; border-radius: 20px; padding: 30px; max-width: 500px; width: 90%; }
        .close-modal { float: right; cursor: pointer; font-size: 1.5rem; color: #6c757d; }
        .close-modal:hover { color: #1e293b; }
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
                <a href="leave-requests.php" class="active"><i class="fas fa-calendar-plus"></i><span> Leave Requests</span></a>
                <a href="leave-approval.php"><i class="fas fa-check-circle"></i><span> Leave Approval</span></a>
                <a href="grievance-review.php"><i class="fas fa-exclamation-circle"></i><span> Grievance Review</span></a>
                <a href="../logout.php"><i class="fas fa-sign-out-alt"></i><span> Logout</span></a>
            </nav>
        </aside>

        <main class="main-content" id="mainContent">
            <header class="top-header">
                <button class="mobile-menu-btn" id="mobileMenuBtn"><i class="fas fa-bars"></i></button>
                <div><h2>Student Leave Requests</h2></div>
                <div class="user-profile">
                    <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($user['full_name']); ?>&background=4361ee&color=fff" alt="Profile">
                    <div><h4><?php echo htmlspecialchars($user['full_name']); ?></h4><p><?php echo htmlspecialchars($user['id_number']); ?></p></div>
                </div>
            </header>

            <div class="stats-grid">
                <div class="stat-card"><h3><i class="fas fa-clock"></i> Pending</h3><div class="stat-value" id="pendingCount">0</div><small>Awaiting Approval</small></div>
                <div class="stat-card"><h3><i class="fas fa-check-circle"></i> Approved</h3><div class="stat-value" id="approvedCount">0</div><small>This Month</small></div>
                <div class="stat-card"><h3><i class="fas fa-calendar-alt"></i> Total Requests</h3><div class="stat-value" id="totalCount">0</div><small>All Time</small></div>
            </div>

            <div class="filter-bar">
                <select class="filter-select" id="statusFilter">
                    <option value="pending">Pending</option>
                    <option value="all">All Requests</option>
                    <option value="approved">Approved</option>
                    <option value="rejected">Rejected</option>
                </select>
                <select class="filter-select" id="leaveTypeFilter">
                    <option value="all">All Types</option>
                    <option value="medical">Medical Leave</option>
                    <option value="emergency">Emergency</option>
                    <option value="personal">Personal Leave</option>
                    <option value="vacation">Vacation</option>
                </select>
                <input type="text" class="search-box" id="searchInput" placeholder="Search by student name or roll...">
            </div>

            <div class="batch-actions">
                <button class="batch-btn" onclick="approveAll()"><i class="fas fa-check-double"></i> Approve Selected</button>
                <button class="batch-btn" onclick="rejectAll()"><i class="fas fa-times"></i> Reject Selected</button>
                <button class="batch-btn" onclick="exportToCSV()"><i class="fas fa-download"></i> Export CSV</button>
            </div>

            <div class="leave-table">
                <h3 style="margin-bottom:20px;"><i class="fas fa-list"></i> Leave Applications</h3>
                <div id="leaveTable"></div>
            </div>
        </main>
    </div>

    <!-- Leave Detail Modal -->
    <div id="leaveModal" class="modal">
        <div class="modal-content">
            <span class="close-modal" onclick="closeModal()">&times;</span>
            <h3 id="modalTitle"><i class="fas fa-file-alt"></i> Leave Application Details</h3>
            <div id="modalBody"></div>
            <div style="margin-top:20px; display:flex; gap:10px; justify-content:flex-end;">
                <button class="approve-btn" id="modalApproveBtn" onclick="approveCurrent()">Approve</button>
                <button class="reject-btn" id="modalRejectBtn" onclick="rejectCurrent()">Reject</button>
            </div>
        </div>
    </div>

    <script>
        let leaveRequests = [
            { id: 1, student: 'Rishabh Shah', roll: 'IT181', class: 'CS301', type: 'medical', fromDate: '2024-03-20', toDate: '2024-03-22', reason: 'Suffering from viral fever. Need rest.', status: 'pending', appliedDate: '2024-03-19', contact: '9876543210', days: 3 },
            { id: 2, student: 'Jenish khunt', roll: 'IT095', class: 'CS301', type: 'personal', fromDate: '2024-03-25', toDate: '2024-03-26', reason: 'Family function to attend. Will complete pending assignments.', status: 'pending', appliedDate: '2024-03-18', contact: '9876543211', days: 2 },
            { id: 3, student: 'Vasu Motisarya', roll: 'IT124', class: 'CS301', type: 'emergency', fromDate: '2024-03-18', toDate: '2024-03-19', reason: 'Urgent family matter at hometown.', status: 'approved', appliedDate: '2024-03-17', contact: '9876543212', days: 2, approvedBy: 'Prof.Aakash Gupta', approvedDate: '2024-03-17' },
            { id: 4, student: 'Hetvi Savani', roll: 'IT131', class: 'CS301', type: 'medical', fromDate: '2024-03-15', toDate: '2024-03-16', reason: 'Doctor appointment for regular checkup.', status: 'rejected', appliedDate: '2024-03-14', contact: '9876543213', days: 2, remarks: 'Insufficient documentation. Please provide medical certificate.' },
            { id: 5, student: 'Aarav Desai', roll: 'CSIT001', class: 'CS302', type: 'vacation', fromDate: '2024-04-01', toDate: '2024-04-05', reason: 'Family vacation planned.', status: 'pending', appliedDate: '2024-03-20', contact: '9876543214', days: 5 }
        ];
        
        let currentLeaveId = null;
        
        function loadLeaveRequests() {
            const statusFilter = document.getElementById('statusFilter').value;
            const typeFilter = document.getElementById('leaveTypeFilter').value;
            const searchTerm = document.getElementById('searchInput').value.toLowerCase();
            
            let filtered = [...leaveRequests];
            if(statusFilter !== 'all') filtered = filtered.filter(l => l.status === statusFilter);
            if(typeFilter !== 'all') filtered = filtered.filter(l => l.type === typeFilter);
            if(searchTerm) filtered = filtered.filter(l => l.student.toLowerCase().includes(searchTerm) || l.roll.toLowerCase().includes(searchTerm));
            
            const pendingCount = leaveRequests.filter(l => l.status === 'pending').length;
            const approvedCount = leaveRequests.filter(l => l.status === 'approved').length;
            document.getElementById('pendingCount').textContent = pendingCount;
            document.getElementById('approvedCount').textContent = approvedCount;
            document.getElementById('totalCount').textContent = leaveRequests.length;
            
            if(filtered.length === 0) {
                document.getElementById('leaveTable').innerHTML = '<div style="text-align:center;padding:40px;"><i class="fas fa-inbox" style="font-size:3rem;color:#6c757d;"></i><p>No leave requests found</p></div>';
                return;
            }
            
            const tableHTML = `
                <table>
                    <thead>
                        <tr>
                            <th><input type="checkbox" id="selectAll" onchange="toggleSelectAll()"></th>
                            <th>Student Name</th>
                            <th>Roll No</th>
                            <th>Leave Type</th>
                            <th>Duration</th>
                            <th>Applied On</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${filtered.map(l => `
                            <tr>
                                <td><input type="checkbox" class="leaveCheckbox" data-id="${l.id}"></td>
                                <td><strong>${l.student}</strong><br><small>${l.class}</small></td>
                                <td>${l.roll}</td>
                                <td><span style="text-transform:capitalize;">${l.type}</span></td>
                                <td>${l.days} days<br><small>${new Date(l.fromDate).toLocaleDateString()} - ${new Date(l.toDate).toLocaleDateString()}</small></td>
                                <td>${new Date(l.appliedDate).toLocaleDateString()}</td>
                                <td><span class="status-${l.status}">${l.status.toUpperCase()}</span></td>
                                <td>
                                    <button class="view-btn" onclick="viewLeave(${l.id})"><i class="fas fa-eye"></i> View</button>
                                    ${l.status === 'pending' ? `<button class="approve-btn" onclick="approveLeave(${l.id})"><i class="fas fa-check"></i> Approve</button>
                                    <button class="reject-btn" onclick="rejectLeave(${l.id})"><i class="fas fa-times"></i> Reject</button>` : ''}
                                </td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            `;
            document.getElementById('leaveTable').innerHTML = tableHTML;
        }
        
        function toggleSelectAll() {
            const selectAll = document.getElementById('selectAll');
            const checkboxes = document.querySelectorAll('.leaveCheckbox');
            checkboxes.forEach(cb => cb.checked = selectAll.checked);
        }
        
        function getSelectedIds() {
            const checkboxes = document.querySelectorAll('.leaveCheckbox:checked');
            return Array.from(checkboxes).map(cb => parseInt(cb.dataset.id));
        }
        
        function approveAll() {
            const selectedIds = getSelectedIds();
            if(selectedIds.length === 0) {
                showNotification('Please select leave requests to approve', 'error');
                return;
            }
            if(confirm(`Approve ${selectedIds.length} leave request(s)?`)) {
                selectedIds.forEach(id => approveLeave(id));
            }
        }
        
        function rejectAll() {
            const selectedIds = getSelectedIds();
            if(selectedIds.length === 0) {
                showNotification('Please select leave requests to reject', 'error');
                return;
            }
            if(confirm(`Reject ${selectedIds.length} leave request(s)?`)) {
                selectedIds.forEach(id => rejectLeave(id));
            }
        }
        
        function viewLeave(id) {
            const leave = leaveRequests.find(l => l.id === id);
            if(leave) {
                currentLeaveId = id;
                const typeNames = { medical: 'Medical Leave', personal: 'Personal Leave', emergency: 'Emergency', vacation: 'Vacation' };
                document.getElementById('modalTitle').innerHTML = `<i class="fas fa-file-alt"></i> Leave Application - ${leave.student}`;
                document.getElementById('modalBody').innerHTML = `
                    <div style="margin-top:20px;">
                        <div style="background:#f8f9fa; padding:15px; border-radius:10px; margin-bottom:15px;">
                            <h4>Student Information</h4>
                            <p><strong>Name:</strong> ${leave.student}</p>
                            <p><strong>Roll Number:</strong> ${leave.roll}</p>
                            <p><strong>Class:</strong> ${leave.class}</p>
                            <p><strong>Contact:</strong> ${leave.contact}</p>
                        </div>
                        <div style="background:#f8f9fa; padding:15px; border-radius:10px; margin-bottom:15px;">
                            <h4>Leave Details</h4>
                            <p><strong>Type:</strong> ${typeNames[leave.type]}</p>
                            <p><strong>From Date:</strong> ${new Date(leave.fromDate).toLocaleDateString()}</p>
                            <p><strong>To Date:</strong> ${new Date(leave.toDate).toLocaleDateString()}</p>
                            <p><strong>Duration:</strong> ${leave.days} days</p>
                            <p><strong>Reason:</strong> ${leave.reason}</p>
                        </div>
                        <div style="background:#f8f9fa; padding:15px; border-radius:10px;">
                            <h4>Application Details</h4>
                            <p><strong>Applied On:</strong> ${new Date(leave.appliedDate).toLocaleDateString()}</p>
                            ${leave.approvedBy ? `<p><strong>Approved By:</strong> ${leave.approvedBy}</p>` : ''}
                            ${leave.approvedDate ? `<p><strong>Approved Date:</strong> ${new Date(leave.approvedDate).toLocaleDateString()}</p>` : ''}
                            ${leave.remarks ? `<p><strong>Remarks:</strong> ${leave.remarks}</p>` : ''}
                        </div>
                    </div>
                `;
                document.getElementById('leaveModal').style.display = 'flex';
                
                const approveBtn = document.getElementById('modalApproveBtn');
                const rejectBtn = document.getElementById('modalRejectBtn');
                if(leave.status === 'pending') {
                    approveBtn.style.display = 'inline-block';
                    rejectBtn.style.display = 'inline-block';
                } else {
                    approveBtn.style.display = 'none';
                    rejectBtn.style.display = 'none';
                }
            }
        }
        
        function approveLeave(id) {
            const index = leaveRequests.findIndex(l => l.id === id);
            if(index !== -1 && leaveRequests[index].status === 'pending') {
                leaveRequests[index].status = 'approved';
                leaveRequests[index].approvedBy = '<?php echo $user['full_name']; ?>';
                leaveRequests[index].approvedDate = new Date().toISOString().split('T')[0];
                showNotification(`Leave request for ${leaveRequests[index].student} approved`, 'success');
                loadLeaveRequests();
                if(currentLeaveId === id) closeModal();
            }
        }
        
        function rejectLeave(id) {
            const remarks = prompt('Please enter reason for rejection:');
            if(remarks) {
                const index = leaveRequests.findIndex(l => l.id === id);
                if(index !== -1 && leaveRequests[index].status === 'pending') {
                    leaveRequests[index].status = 'rejected';
                    leaveRequests[index].remarks = remarks;
                    showNotification(`Leave request for ${leaveRequests[index].student} rejected`, 'info');
                    loadLeaveRequests();
                    if(currentLeaveId === id) closeModal();
                }
            }
        }
        
        function approveCurrent() {
            if(currentLeaveId) approveLeave(currentLeaveId);
        }
        
        function rejectCurrent() {
            if(currentLeaveId) rejectLeave(currentLeaveId);
        }
        
        function closeModal() {
            document.getElementById('leaveModal').style.display = 'none';
            currentLeaveId = null;
        }
        
        function exportToCSV() {
            const statusFilter = document.getElementById('statusFilter').value;
            let filtered = [...leaveRequests];
            if(statusFilter !== 'all') filtered = filtered.filter(l => l.status === statusFilter);
            
            let csvContent = "Student Name,Roll Number,Class,Leave Type,From Date,To Date,Days,Reason,Status,Applied Date\n";
            filtered.forEach(l => {
                csvContent += `${l.student},${l.roll},${l.class},${l.type},${l.fromDate},${l.toDate},${l.days},${l.reason},${l.status},${l.appliedDate}\n`;
            });
            
            const blob = new Blob([csvContent], { type: 'text/csv' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'leave_requests.csv';
            a.click();
            URL.revokeObjectURL(url);
            showNotification('Exported successfully!', 'success');
        }
        
        function showNotification(message, type) {
            const notification = document.createElement('div');
            notification.className = 'notification';
            notification.style.background = type === 'success' ? '#4cc9f0' : type === 'error' ? '#f72585' : '#4361ee';
            notification.innerHTML = `<i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i> ${message}`;
            document.body.appendChild(notification);
            setTimeout(() => notification.remove(), 3000);
        }
        
        document.getElementById('statusFilter').addEventListener('change', loadLeaveRequests);
        document.getElementById('leaveTypeFilter').addEventListener('change', loadLeaveRequests);
        document.getElementById('searchInput').addEventListener('keyup', loadLeaveRequests);
        
        const sidebar = document.getElementById('sidebar'), mainContent = document.getElementById('mainContent'), toggleBtn = document.getElementById('toggleSidebar'), mobileMenuBtn = document.getElementById('mobileMenuBtn');
        if(toggleBtn) toggleBtn.addEventListener('click', function() { sidebar.classList.toggle('collapsed'); mainContent.classList.toggle('expanded'); });
        if(mobileMenuBtn) mobileMenuBtn.addEventListener('click', function() { sidebar.classList.toggle('active'); });
        function handleResponsive() { if(window.innerWidth <= 768) { sidebar.classList.add('collapsed'); mainContent.classList.add('expanded'); } else { sidebar.classList.remove('collapsed', 'active'); mainContent.classList.remove('expanded'); } }
        window.addEventListener('resize', handleResponsive); handleResponsive();
        
        loadLeaveRequests();
        
        window.onclick = function(event) { if(event.target == document.getElementById('leaveModal')) closeModal(); }
    </script>
</body>
</html>