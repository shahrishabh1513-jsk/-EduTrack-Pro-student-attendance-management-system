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
    <title>Leave Report - EduTrack Pro</title>
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
        .filter-bar { display: flex; gap: 15px; margin-bottom: 20px; flex-wrap: wrap; align-items: center; }
        .filter-select, .date-input { padding: 10px 15px; border: 2px solid #e9ecef; border-radius: 10px; font-family: 'Poppins', sans-serif; }
        .btn-generate { background: #4361ee; color: white; border: none; padding: 10px 20px; border-radius: 8px; cursor: pointer; }
        .btn-export { background: #28a745; color: white; border: none; padding: 10px 20px; border-radius: 8px; cursor: pointer; }
        .report-summary { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .summary-card { background: white; padding: 20px; border-radius: 15px; text-align: center; }
        .summary-value { font-size: 2rem; font-weight: 700; color: #4361ee; }
        .report-table { background: white; border-radius: 20px; padding: 25px; overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #e9ecef; }
        th { background: #f8f9fa; font-weight: 600; }
        .progress-bar { height: 6px; background: #e9ecef; border-radius: 10px; width: 100px; display: inline-block; margin-left: 10px; }
        .progress-fill { height: 100%; background: #4361ee; border-radius: 10px; }
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
                <a href="leave-management.php"><i class="fas fa-calendar-alt"></i><span> Leave Management</span></a>
                <a href="leave-approval.php"><i class="fas fa-check-circle"></i><span> Leave Approval</span></a>
                <a href="leave-report.php" class="active"><i class="fas fa-chart-bar"></i><span> Leave Report</span></a>
                <a href="../../logout.php"><i class="fas fa-sign-out-alt"></i><span> Logout</span></a>
            </nav>
        </aside>

        <main class="main-content" id="mainContent">
            <header class="top-header">
                <button class="mobile-menu-btn" id="mobileMenuBtn"><i class="fas fa-bars"></i></button>
                <div><h2>Leave Report</h2></div>
                <div class="user-profile">
                    <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($user['full_name']); ?>&background=4361ee&color=fff" alt="Profile">
                    <div><h4><?php echo htmlspecialchars($user['full_name']); ?></h4><p>Administrator</p></div>
                </div>
            </header>

            <div class="filter-bar">
                <select class="filter-select" id="deptFilter"><option value="all">All Departments</option><option>Computer Science</option><option>Information Technology</option><option>Electronics</option></select>
                <select class="filter-select" id="yearFilter"><option value="2024">2024</option><option value="2023">2023</option></select>
                <button class="btn-generate" onclick="generateReport()"><i class="fas fa-sync-alt"></i> Generate Report</button>
                <button class="btn-export" onclick="exportReport()"><i class="fas fa-download"></i> Export PDF</button>
            </div>

            <div class="report-summary">
                <div class="summary-card"><div class="summary-value" id="totalLeaves">0</div><div>Total Leaves</div></div>
                <div class="summary-card"><div class="summary-value" id="approvedLeaves">0</div><div>Approved</div></div>
                <div class="summary-card"><div class="summary-value" id="rejectedLeaves">0</div><div>Rejected</div></div>
                <div class="summary-card"><div class="summary-value" id="pendingLeaves">0</div><div>Pending</div></div>
            </div>

            <div class="report-table"><h3 style="margin-bottom:20px;"><i class="fas fa-chart-bar"></i> Department-wise Leave Summary</h3><div id="deptTable"></div></div>
            <div class="report-table" style="margin-top:20px;"><h3 style="margin-bottom:20px;"><i class="fas fa-list"></i> Leave Type Distribution</h3><div id="typeTable"></div></div>
        </main>
    </div>

    <script>
        const departments = [{ name: 'Computer Science', total: 25, approved: 18, rejected: 3, pending: 4 }, { name: 'Information Technology', total: 18, approved: 14, rejected: 2, pending: 2 }, { name: 'Electronics', total: 14, approved: 10, rejected: 2, pending: 2 }];
        const leaveTypes = [{ type: 'Medical', count: 35 }, { type: 'Personal', count: 25 }, { type: 'Emergency', count: 15 }, { type: 'Vacation', count: 10 }];
        
        function generateReport() {
            const total = departments.reduce((s, d) => s + d.total, 0);
            const approved = departments.reduce((s, d) => s + d.approved, 0);
            const rejected = departments.reduce((s, d) => s + d.rejected, 0);
            const pending = departments.reduce((s, d) => s + d.pending, 0);
            document.getElementById('totalLeaves').textContent = total;
            document.getElementById('approvedLeaves').textContent = approved;
            document.getElementById('rejectedLeaves').textContent = rejected;
            document.getElementById('pendingLeaves').textContent = pending;
            
            const deptHTML = `<table><thead><tr><th>Department</th><th>Total Leaves</th><th>Approved</th><th>Rejected</th><th>Pending</th><th>Approval Rate</th></tr></thead><tbody>${departments.map(d => `<tr><td><strong>${d.name}</strong></td><td>${d.total}</td><td>${d.approved}</td><td>${d.rejected}</td><td>${d.pending}</td><td>${Math.round((d.approved / d.total) * 100)}%<div class="progress-bar"><div class="progress-fill" style="width:${(d.approved / d.total) * 100}%"></div></div></td></tr>`).join('')}</tbody></tr>`;
            document.getElementById('deptTable').innerHTML = deptHTML;
            
            const typeHTML = `<table><thead><tr><th>Leave Type</th><th>Count</th><th>Percentage</th></tr></thead><tbody>${leaveTypes.map(t => `<tr><td><strong>${t.type}</strong></td><td>${t.count}</td><td>${Math.round((t.count / leaveTypes.reduce((s, t) => s + t.count, 0)) * 100)}%<div class="progress-bar"><div class="progress-fill" style="width:${(t.count / leaveTypes.reduce((s, t) => s + t.count, 0)) * 100}%"></div></div></td></tr>`).join('')}</tbody></table>`;
            document.getElementById('typeTable').innerHTML = typeHTML;
        }
        
        function exportReport() { showNotification('Report exported!', 'success'); }
        function showNotification(message, type) { const n = document.createElement('div'); n.style.cssText = `position:fixed;top:20px;right:20px;padding:15px 25px;background:${type === 'success' ? '#4cc9f0' : '#f72585'};color:white;border-radius:10px;z-index:9999;animation:slideIn 0.3s ease;`; n.innerHTML = `<i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i> ${message}`; document.body.appendChild(n); setTimeout(() => n.remove(), 3000); }
        
        const sidebar = document.getElementById('sidebar'), mainContent = document.getElementById('mainContent'), toggleBtn = document.getElementById('toggleSidebar'), mobileMenuBtn = document.getElementById('mobileMenuBtn');
        if(toggleBtn) toggleBtn.addEventListener('click', function() { sidebar.classList.toggle('collapsed'); mainContent.classList.toggle('expanded'); });
        if(mobileMenuBtn) mobileMenuBtn.addEventListener('click', function() { sidebar.classList.toggle('active'); });
        function handleResponsive() { if(window.innerWidth <= 768) { sidebar.classList.add('collapsed'); mainContent.classList.add('expanded'); } else { sidebar.classList.remove('collapsed', 'active'); mainContent.classList.remove('expanded'); } }
        window.addEventListener('resize', handleResponsive); handleResponsive();
        generateReport();
    </script>
</body>
</html>