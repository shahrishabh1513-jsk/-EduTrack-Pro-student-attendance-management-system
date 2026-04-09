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
        .section-title { font-size: 1.1rem; font-weight: 600; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; border-bottom: 2px solid #e9ecef; padding-bottom: 15px; }
        .leave-card { border: 1px solid #e9ecef; border-radius: 15px; padding: 15px; margin-bottom: 15px; transition: all 0.3s; }
        .leave-card:hover { border-color: #4361ee; box-shadow: 0 5px 15px rgba(0,0,0,0.05); }
        .leave-card.pending { border-left: 4px solid #ffc107; }
        .student-name { font-weight: 600; }
        .leave-dates { font-size: 0.85rem; color: #6c757d; margin: 5px 0; }
        .action-buttons { display: flex; gap: 10px; margin-top: 10px; }
        .btn-approve { background: #4cc9f0; color: white; border: none; padding: 6px 15px; border-radius: 5px; cursor: pointer; }
        .btn-reject { background: #f72585; color: white; border: none; padding: 6px 15px; border-radius: 5px; cursor: pointer; }
        .btn-view { background: #4361ee; color: white; border: none; padding: 6px 15px; border-radius: 5px; cursor: pointer; }
        @media (max-width: 768px) { .approval-container { grid-template-columns: 1fr; } .sidebar { transform: translateX(-100%); } .sidebar.active { transform: translateX(0); } .main-content { margin-left: 0; } }
        .mobile-menu-btn { display: none; background: none; border: none; font-size: 1.5rem; cursor: pointer; }
        @media (max-width: 768px) { .mobile-menu-btn { display: block; } }
        .notification { position: fixed; top: 20px; right: 20px; padding: 15px 25px; border-radius: 10px; z-index: 9999; animation: slideIn 0.3s ease; color: white; }
        @keyframes slideIn { from { transform: translateX(100%); opacity: 0; } to { transform: translateX(0); opacity: 1; } }
        .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); justify-content: center; align-items: center; z-index: 1000; }
        .modal-content { background: white; border-radius: 20px; padding: 30px; max-width: 500px; width: 90%; }
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
                <a href="leave-approval.php" class="active"><i class="fas fa-check-circle"></i><span> Leave Approval</span></a>
                <a href="leave-report.php"><i class="fas fa-chart-bar"></i><span> Leave Report</span></a>
                <a href="../../logout.php"><i class="fas fa-sign-out-alt"></i><span> Logout</span></a>
            </nav>
        </aside>

        <main class="main-content" id="mainContent">
            <header class="top-header">
                <button class="mobile-menu-btn" id="mobileMenuBtn"><i class="fas fa-bars"></i></button>
                <div><h2>Leave Approval Dashboard</h2></div>
                <div class="user-profile">
                    <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($user['full_name']); ?>&background=4361ee&color=fff" alt="Profile">
                    <div><h4><?php echo htmlspecialchars($user['full_name']); ?></h4><p>Administrator</p></div>
                </div>
            </header>

            <div class="approval-container">
                <div class="pending-list"><div class="section-title"><i class="fas fa-clock"></i> Pending Approvals (<span id="pendingCount">0</span>)</div><div id="pendingList"></div></div>
                <div class="approved-list"><div class="section-title"><i class="fas fa-check-circle"></i> Recently Approved</div><div id="approvedList"></div></div>
            </div>
        </main>
    </div>

    <div id="leaveModal" class="modal"><div class="modal-content"><h3>Leave Details</h3><div id="modalBody"></div><div style="margin-top:20px;"><button class="btn-approve" id="modalApproveBtn">Approve</button><button class="btn-reject" id="modalRejectBtn">Reject</button><button class="btn-view" onclick="closeModal()">Close</button></div></div></div>

    <script>
        let leaves = [
            { id: 1, student: 'Rishabh Shah', roll: 'IT181', type: 'medical', fromDate: '2024-03-20', toDate: '2024-03-22', days: 3, reason: 'Viral fever', status: 'pending', appliedDate: '2024-03-19' },
            { id: 2, student: 'Jenish khunt', roll: 'IT095', type: 'personal', fromDate: '2024-03-25', toDate: '2024-03-26', days: 2, reason: 'Family function', status: 'pending', appliedDate: '2024-03-18' },
            { id: 3, student: 'Vasu Motisarya', roll: 'IT124', type: 'emergency', fromDate: '2024-03-18', toDate: '2024-03-19', days: 2, reason: 'Urgent matter', status: 'approved', appliedDate: '2024-03-17', approvedBy: 'Prof.Aakash Gupta' }
        ];
        
        let currentLeaveId = null;
        const typeNames = { medical: 'Medical Leave', personal: 'Personal Leave', emergency: 'Emergency', vacation: 'Vacation' };
        
        function loadApprovals() {
            const pending = leaves.filter(l => l.status === 'pending');
            const approved = leaves.filter(l => l.status === 'approved').slice(0, 5);
            document.getElementById('pendingCount').textContent = pending.length;
            document.getElementById('pendingList').innerHTML = pending.length ? pending.map(l => `<div class="leave-card pending"><div class="student-name">${l.student} (${l.roll})</div><div class="leave-dates">${typeNames[l.type]} • ${l.days} days</div><div class="leave-dates">${new Date(l.fromDate).toLocaleDateString()} - ${new Date(l.toDate).toLocaleDateString()}</div><div class="action-buttons"><button class="btn-view" onclick="viewLeave(${l.id})">View</button><button class="btn-approve" onclick="approveLeave(${l.id})">Approve</button><button class="btn-reject" onclick="rejectLeave(${l.id})">Reject</button></div></div>`).join('') : '<div style="text-align:center;padding:20px;">No pending approvals</div>';
            document.getElementById('approvedList').innerHTML = approved.length ? approved.map(l => `<div class="leave-card"><div class="student-name">${l.student} (${l.roll})</div><div class="leave-dates">${typeNames[l.type]} • ${l.days} days</div><div class="leave-dates">Approved by: ${l.approvedBy}</div></div>`).join('') : '<div style="text-align:center;padding:20px;">No approved leaves</div>';
        }
        
        function viewLeave(id) { const l = leaves.find(l => l.id === id); if(l) { currentLeaveId = id; document.getElementById('modalBody').innerHTML = `<p><strong>Student:</strong> ${l.student} (${l.roll})</p><p><strong>Type:</strong> ${typeNames[l.type]}</p><p><strong>Duration:</strong> ${l.days} days</p><p><strong>Dates:</strong> ${new Date(l.fromDate).toLocaleDateString()} - ${new Date(l.toDate).toLocaleDateString()}</p><p><strong>Reason:</strong> ${l.reason}</p><p><strong>Applied:</strong> ${new Date(l.appliedDate).toLocaleDateString()}</p>`; document.getElementById('leaveModal').style.display = 'flex'; document.getElementById('modalApproveBtn').onclick = () => approveLeave(id); document.getElementById('modalRejectBtn').onclick = () => rejectLeave(id); } }
        function approveLeave(id) { const index = leaves.findIndex(l => l.id === id); if(index !== -1) { leaves[index].status = 'approved'; leaves[index].approvedBy = '<?php echo $user['full_name']; ?>'; loadApprovals(); if(currentLeaveId === id) closeModal(); showNotification('Leave approved!', 'success'); } }
        function rejectLeave(id) { const remarks = prompt('Enter reason:'); if(remarks) { const index = leaves.findIndex(l => l.id === id); if(index !== -1) { leaves[index].status = 'rejected'; leaves[index].remarks = remarks; loadApprovals(); if(currentLeaveId === id) closeModal(); showNotification('Leave rejected!', 'info'); } } }
        function closeModal() { document.getElementById('leaveModal').style.display = 'none'; currentLeaveId = null; }
        function showNotification(message, type) { const n = document.createElement('div'); n.className = 'notification'; n.style.background = type === 'success' ? '#4cc9f0' : '#f72585'; n.innerHTML = `<i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i> ${message}`; document.body.appendChild(n); setTimeout(() => n.remove(), 3000); }
        
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