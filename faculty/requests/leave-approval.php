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
    <title>Leave Approval - EduTrack Pro</title>
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
        .approval-container { display: grid; grid-template-columns: 1fr 1.5fr; gap: 30px; }
        .pending-list, .approved-list { background: white; border-radius: 20px; padding: 25px; }
        .section-title { font-size: 1.2rem; font-weight: 600; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; border-bottom: 2px solid #e9ecef; padding-bottom: 15px; }
        .leave-card { border: 1px solid #e9ecef; border-radius: 15px; padding: 15px; margin-bottom: 15px; transition: all 0.3s; cursor: pointer; }
        .leave-card:hover { border-color: #4361ee; box-shadow: 0 5px 15px rgba(0,0,0,0.05); }
        .leave-card.pending { border-left: 4px solid #ffc107; }
        .leave-card.approved { border-left: 4px solid #4cc9f0; }
        .student-name { font-weight: 600; font-size: 1rem; }
        .leave-dates { font-size: 0.85rem; color: #6c757d; margin: 5px 0; }
        .action-buttons { display: flex; gap: 10px; margin-top: 10px; }
        .btn-approve { background: #4cc9f0; color: white; border: none; padding: 6px 15px; border-radius: 5px; cursor: pointer; }
        .btn-reject { background: #f72585; color: white; border: none; padding: 6px 15px; border-radius: 5px; cursor: pointer; }
        .btn-view { background: #4361ee; color: white; border: none; padding: 6px 15px; border-radius: 5px; cursor: pointer; }
        .status-badge { padding: 3px 10px; border-radius: 15px; font-size: 0.7rem; font-weight: 600; display: inline-block; }
        .badge-pending { background: rgba(255,193,7,0.1); color: #ffc107; }
        .badge-approved { background: rgba(76,201,240,0.1); color: #4cc9f0; }
        @media (max-width: 768px) { .approval-container { grid-template-columns: 1fr; } .sidebar { transform: translateX(-100%); } .sidebar.active { transform: translateX(0); } .main-content { margin-left: 0; } }
        .mobile-menu-btn { display: none; background: none; border: none; font-size: 1.5rem; cursor: pointer; }
        @media (max-width: 768px) { .mobile-menu-btn { display: block; } }
        .notification { position: fixed; top: 20px; right: 20px; padding: 15px 25px; border-radius: 10px; z-index: 9999; animation: slideIn 0.3s ease; color: white; }
        @keyframes slideIn { from { transform: translateX(100%); opacity: 0; } to { transform: translateX(0); opacity: 1; } }
        .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); justify-content: center; align-items: center; z-index: 1000; }
        .modal-content { background: white; border-radius: 20px; padding: 30px; max-width: 500px; width: 90%; }
        .close-modal { float: right; cursor: pointer; font-size: 1.5rem; color: #6c757d; }
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
                <a href="leave-requests.php"><i class="fas fa-calendar-plus"></i><span> Leave Requests</span></a>
                <a href="leave-approval.php" class="active"><i class="fas fa-check-circle"></i><span> Leave Approval</span></a>
                <a href="grievance-review.php"><i class="fas fa-exclamation-circle"></i><span> Grievance Review</span></a>
                <a href="../logout.php"><i class="fas fa-sign-out-alt"></i><span> Logout</span></a>
            </nav>
        </aside>

        <main class="main-content" id="mainContent">
            <header class="top-header">
                <button class="mobile-menu-btn" id="mobileMenuBtn"><i class="fas fa-bars"></i></button>
                <div><h2>Leave Approval Dashboard</h2></div>
                <div class="user-profile">
                    <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($user['full_name']); ?>&background=4361ee&color=fff" alt="Profile">
                    <div><h4><?php echo htmlspecialchars($user['full_name']); ?></h4><p><?php echo htmlspecialchars($user['id_number']); ?></p></div>
                </div>
            </header>

            <div class="approval-container">
                <div class="pending-list">
                    <div class="section-title"><i class="fas fa-clock"></i> Pending Approvals</div>
                    <div id="pendingList"></div>
                </div>
                <div class="approved-list">
                    <div class="section-title"><i class="fas fa-check-circle"></i> Recently Approved</div>
                    <div id="approvedList"></div>
                </div>
            </div>
        </main>
    </div>

    <!-- Leave Detail Modal -->
    <div id="leaveModal" class="modal">
        <div class="modal-content">
            <span class="close-modal" onclick="closeModal()">&times;</span>
            <h3 id="modalTitle">Leave Application Details</h3>
            <div id="modalBody"></div>
            <div style="margin-top:20px; display:flex; gap:10px; justify-content:flex-end;">
                <button class="btn-approve" id="modalApproveBtn" onclick="approveCurrent()">Approve</button>
                <button class="btn-reject" id="modalRejectBtn" onclick="rejectCurrent()">Reject</button>
            </div>
        </div>
    </div>

    <script>
        let leaveData = [
            { id: 1, student: 'Rishabh Shah', roll: 'IT181', class: 'CS301', type: 'medical', fromDate: '2024-03-20', toDate: '2024-03-22', reason: 'Suffering from viral fever', status: 'pending', appliedDate: '2024-03-19', contact: '9876543210' },
            { id: 2, student: 'Jenish khunt', roll: 'IT095', class: 'CS301', type: 'personal', fromDate: '2024-03-25', toDate: '2024-03-26', reason: 'Family function', status: 'pending', appliedDate: '2024-03-18', contact: '9876543211' },
            { id: 3, student: 'Aarav Desai', roll: 'CSIT001', class: 'CS302', type: 'vacation', fromDate: '2024-04-01', toDate: '2024-04-05', reason: 'Family vacation', status: 'pending', appliedDate: '2024-03-20', contact: '9876543214' },
            { id: 4, student: 'Vasu Motisarya', roll: 'IT124', class: 'CS301', type: 'emergency', fromDate: '2024-03-18', toDate: '2024-03-19', reason: 'Urgent family matter', status: 'approved', appliedDate: '2024-03-17', approvedBy: 'Prof.Aakash Gupta' },
            { id: 5, student: 'Hetvi Savani', roll: 'IT131', class: 'CS301', type: 'medical', fromDate: '2024-03-15', toDate: '2024-03-16', reason: 'Doctor appointment', status: 'approved', appliedDate: '2024-03-14', approvedBy: 'Prof. Neha Shah' }
        ];
        
        let currentLeaveId = null;
        const typeNames = { medical: 'Medical Leave', personal: 'Personal Leave', emergency: 'Emergency', vacation: 'Vacation' };
        
        function loadApprovals() {
            const pending = leaveData.filter(l => l.status === 'pending');
            const approved = leaveData.filter(l => l.status === 'approved').slice(0, 5);
            
            if(pending.length === 0) {
                document.getElementById('pendingList').innerHTML = '<div style="text-align:center;padding:40px;"><i class="fas fa-check-circle" style="font-size:3rem;color:#4cc9f0;"></i><p>No pending approvals</p></div>';
            } else {
                document.getElementById('pendingList').innerHTML = pending.map(l => `
                    <div class="leave-card pending" onclick="viewLeave(${l.id})">
                        <div class="student-name">${l.student} <span style="font-size:0.8rem; color:#6c757d;">(${l.roll})</span></div>
                        <div class="leave-dates"><i class="fas fa-calendar"></i> ${new Date(l.fromDate).toLocaleDateString()} - ${new Date(l.toDate).toLocaleDateString()}</div>
                        <div class="leave-dates"><i class="fas fa-tag"></i> ${typeNames[l.type]} • ${l.class}</div>
                        <div class="action-buttons">
                            <button class="btn-view" onclick="event.stopPropagation(); viewLeave(${l.id})"><i class="fas fa-eye"></i> View</button>
                            <button class="btn-approve" onclick="event.stopPropagation(); approveLeave(${l.id})"><i class="fas fa-check"></i> Approve</button>
                            <button class="btn-reject" onclick="event.stopPropagation(); rejectLeave(${l.id})"><i class="fas fa-times"></i> Reject</button>
                        </div>
                    </div>
                `).join('');
            }
            
            if(approved.length === 0) {
                document.getElementById('approvedList').innerHTML = '<div style="text-align:center;padding:40px;"><i class="fas fa-inbox" style="font-size:3rem;color:#6c757d;"></i><p>No approved leaves</p></div>';
            } else {
                document.getElementById('approvedList').innerHTML = approved.map(l => `
                    <div class="leave-card approved">
                        <div class="student-name">${l.student} <span style="font-size:0.8rem; color:#6c757d;">(${l.roll})</span></div>
                        <div class="leave-dates"><i class="fas fa-calendar"></i> ${new Date(l.fromDate).toLocaleDateString()} - ${new Date(l.toDate).toLocaleDateString()}</div>
                        <div class="leave-dates"><i class="fas fa-tag"></i> ${typeNames[l.type]}</div>
                        <div class="leave-dates"><i class="fas fa-check-circle"></i> Approved by: ${l.approvedBy}</div>
                    </div>
                `).join('');
            }
        }
        
        function viewLeave(id) {
            const leave = leaveData.find(l => l.id === id);
            if(leave) {
                currentLeaveId = id;
                document.getElementById('modalTitle').innerHTML = `Leave Application - ${leave.student}`;
                document.getElementById('modalBody').innerHTML = `
                    <div style="margin-top:20px;">
                        <div style="background:#f8f9fa; padding:15px; border-radius:10px; margin-bottom:15px;">
                            <h4>Student Information</h4>
                            <p><strong>Name:</strong> ${leave.student}</p>
                            <p><strong>Roll Number:</strong> ${leave.roll}</p>
                            <p><strong>Class:</strong> ${leave.class}</p>
                            <p><strong>Contact:</strong> ${leave.contact}</p>
                        </div>
                        <div style="background:#f8f9fa; padding:15px; border-radius:10px;">
                            <h4>Leave Details</h4>
                            <p><strong>Type:</strong> ${typeNames[leave.type]}</p>
                            <p><strong>From Date:</strong> ${new Date(leave.fromDate).toLocaleDateString()}</p>
                            <p><strong>To Date:</strong> ${new Date(leave.toDate).toLocaleDateString()}</p>
                            <p><strong>Reason:</strong> ${leave.reason}</p>
                            <p><strong>Applied On:</strong> ${new Date(leave.appliedDate).toLocaleDateString()}</p>
                        </div>
                    </div>
                `;
                document.getElementById('leaveModal').style.display = 'flex';
            }
        }
        
        function approveLeave(id) {
            const index = leaveData.findIndex(l => l.id === id);
            if(index !== -1 && leaveData[index].status === 'pending') {
                leaveData[index].status = 'approved';
                leaveData[index].approvedBy = '<?php echo $user['full_name']; ?>';
                showNotification(`Leave request for ${leaveData[index].student} approved`, 'success');
                loadApprovals();
                if(currentLeaveId === id) closeModal();
            }
        }
        
        function rejectLeave(id) {
            const remarks = prompt('Please enter reason for rejection:');
            if(remarks) {
                const index = leaveData.findIndex(l => l.id === id);
                if(index !== -1 && leaveData[index].status === 'pending') {
                    leaveData[index].status = 'rejected';
                    leaveData[index].remarks = remarks;
                    showNotification(`Leave request for ${leaveData[index].student} rejected`, 'info');
                    loadApprovals();
                    if(currentLeaveId === id) closeModal();
                }
            }
        }
        
        function approveCurrent() { if(currentLeaveId) approveLeave(currentLeaveId); }
        function rejectCurrent() { if(currentLeaveId) rejectLeave(currentLeaveId); }
        function closeModal() { document.getElementById('leaveModal').style.display = 'none'; currentLeaveId = null; }
        
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
        
        loadApprovals();
        window.onclick = function(event) { if(event.target == document.getElementById('leaveModal')) closeModal(); }
    </script>
</body>
</html>