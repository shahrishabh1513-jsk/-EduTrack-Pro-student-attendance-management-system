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
    <title>Financial Report - EduTrack Pro</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
        .filter-bar { display: flex; gap: 15px; margin-bottom: 25px; flex-wrap: wrap; align-items: center; }
        .filter-select, .date-input { padding: 10px 15px; border: 2px solid #e9ecef; border-radius: 10px; font-family: 'Poppins', sans-serif; }
        .btn-generate { background: #4361ee; color: white; border: none; padding: 10px 25px; border-radius: 10px; cursor: pointer; }
        .btn-export { background: #28a745; color: white; border: none; padding: 10px 25px; border-radius: 10px; cursor: pointer; }
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: white; padding: 20px; border-radius: 15px; text-align: center; }
        .stat-value { font-size: 2rem; font-weight: 700; color: #4361ee; }
        .chart-container { background: white; border-radius: 20px; padding: 25px; margin-bottom: 30px; display: grid; grid-template-columns: 1fr 1fr; gap: 25px; }
        .report-table { background: white; border-radius: 20px; padding: 25px; overflow-x: auto; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #e9ecef; }
        th { background: #f8f9fa; font-weight: 600; }
        .amount-positive { color: #4cc9f0; font-weight: 600; }
        .amount-negative { color: #f72585; font-weight: 600; }
        @media (max-width: 768px) { .sidebar { transform: translateX(-100%); } .sidebar.active { transform: translateX(0); } .main-content { margin-left: 0; } .chart-container { grid-template-columns: 1fr; } }
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
                <a href="attendance-report.php"><i class="fas fa-chart-line"></i><span> Attendance Report</span></a>
                <a href="academic-report.php"><i class="fas fa-graduation-cap"></i><span> Academic Report</span></a>
                <a href="financial-report.php" class="active"><i class="fas fa-rupee-sign"></i><span> Financial Report</span></a>
                <a href="analytics-dashboard.php"><i class="fas fa-chart-pie"></i><span> Analytics</span></a>
                <a href="export-data.php"><i class="fas fa-download"></i><span> Export Data</span></a>
                <a href="../../logout.php"><i class="fas fa-sign-out-alt"></i><span> Logout</span></a>
            </nav>
        </aside>

        <main class="main-content" id="mainContent">
            <header class="top-header">
                <button class="mobile-menu-btn" id="mobileMenuBtn"><i class="fas fa-bars"></i></button>
                <div><h2>Financial Report</h2></div>
                <div class="user-profile">
                    <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($user['full_name']); ?>&background=4361ee&color=fff" alt="Profile">
                    <div><h4><?php echo htmlspecialchars($user['full_name']); ?></h4><p>Administrator</p></div>
                </div>
            </header>

            <div class="filter-bar">
                <select class="filter-select" id="yearFilter"><option value="2024">2024</option><option value="2023">2023</option></select>
                <button class="btn-generate" onclick="generateReport()"><i class="fas fa-sync-alt"></i> Generate</button>
                <button class="btn-export" onclick="exportReport()"><i class="fas fa-download"></i> Export PDF</button>
            </div>

            <div class="stats-grid">
                <div class="stat-card"><div class="stat-value" id="totalRevenue">₹0</div><div>Total Revenue</div></div>
                <div class="stat-card"><div class="stat-value" id="totalExpenses">₹0</div><div>Total Expenses</div></div>
                <div class="stat-card"><div class="stat-value" id="netProfit">₹0</div><div>Net Profit</div></div>
                <div class="stat-card"><div class="stat-value" id="pendingPayments">₹0</div><div>Pending Payments</div></div>
            </div>

            <div class="chart-container">
                <canvas id="revenueChart"></canvas>
                <canvas id="expenseChart"></canvas>
            </div>

            <div class="report-table"><h3 style="margin-bottom:20px;">Revenue Breakdown</h3><div id="revenueTable"></div></div>
            <div class="report-table"><h3 style="margin-bottom:20px;">Monthly Financial Summary</h3><div id="monthlyTable"></div></div>
        </main>
    </div>

    <script>
        const revenueData = [
            { source: 'Tuition Fees', amount: 2500000, percentage: 65 },
            { source: 'Exam Fees', amount: 500000, percentage: 13 },
            { source: 'Library Fees', amount: 300000, percentage: 8 },
            { source: 'Hostel Fees', amount: 400000, percentage: 10 },
            { source: 'Other Income', amount: 150000, percentage: 4 }
        ];
        
        const monthlyData = [
            { month: 'Jan', revenue: 350000, expense: 280000, profit: 70000 },
            { month: 'Feb', revenue: 320000, expense: 260000, profit: 60000 },
            { month: 'Mar', revenue: 380000, expense: 290000, profit: 90000 },
            { month: 'Apr', revenue: 420000, expense: 310000, profit: 110000 },
            { month: 'May', revenue: 450000, expense: 330000, profit: 120000 },
            { month: 'Jun', revenue: 300000, expense: 250000, profit: 50000 }
        ];
        
        let revenueChart = null, expenseChart = null;
        
        function generateReport() {
            const totalRevenue = revenueData.reduce((s, r) => s + r.amount, 0);
            const totalExpenses = 3200000;
            const netProfit = totalRevenue - totalExpenses;
            const pendingPayments = 450000;
            
            document.getElementById('totalRevenue').textContent = '₹' + (totalRevenue / 100000).toFixed(1) + 'L';
            document.getElementById('totalExpenses').textContent = '₹' + (totalExpenses / 100000).toFixed(1) + 'L';
            document.getElementById('netProfit').textContent = '₹' + (netProfit / 100000).toFixed(1) + 'L';
            document.getElementById('pendingPayments').textContent = '₹' + (pendingPayments / 1000).toFixed(0) + 'K';
            
            if(revenueChart) revenueChart.destroy();
            const revenueCtx = document.getElementById('revenueChart').getContext('2d');
            revenueChart = new Chart(revenueCtx, {
                type: 'pie',
                data: { labels: revenueData.map(r => r.source), datasets: [{ data: revenueData.map(r => r.amount), backgroundColor: ['#4cc9f0', '#4361ee', '#3f37c9', '#4895ef', '#f72585'], borderWidth: 0 }] },
                options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
            });
            
            if(expenseChart) expenseChart.destroy();
            const expenseCtx = document.getElementById('expenseChart').getContext('2d');
            expenseChart = new Chart(expenseCtx, {
                type: 'line',
                data: { labels: monthlyData.map(m => m.month), datasets: [{ label: 'Revenue', data: monthlyData.map(m => m.revenue), borderColor: '#4cc9f0', backgroundColor: 'rgba(76,201,240,0.1)', fill: true }, { label: 'Expenses', data: monthlyData.map(m => m.expense), borderColor: '#f72585', backgroundColor: 'rgba(247,37,133,0.1)', fill: true }] },
                options: { responsive: true, plugins: { legend: { position: 'top' } } }
            });
            
            const revenueHTML = `<table><thead><tr><th>Source</th><th>Amount (₹)</th><th>Percentage</th></tr></thead><tbody>${revenueData.map(r => `<tr><td><strong>${r.source}</strong></td><td class="amount-positive">₹${(r.amount / 100000).toFixed(1)}L</td><td>${r.percentage}%<div class="progress-bar" style="width:80px;"><div class="progress-fill" style="width:${r.percentage}%"></div></div></td></tr>`).join('')}</tbody></table>`;
            document.getElementById('revenueTable').innerHTML = revenueHTML;
            
            const monthlyHTML = `<table><thead><tr><th>Month</th><th>Revenue (₹)</th><th>Expenses (₹)</th><th>Profit (₹)</th></tr></thead><tbody>${monthlyData.map(m => `<tr><td><strong>${m.month}</strong></td><td class="amount-positive">₹${(m.revenue / 1000).toFixed(0)}K</td><td class="amount-negative">₹${(m.expense / 1000).toFixed(0)}K</td><td class="amount-positive">₹${(m.profit / 1000).toFixed(0)}K</td></tr>`).join('')}</tbody></table>`;
            document.getElementById('monthlyTable').innerHTML = monthlyHTML;
        }
        
        function exportReport() { showNotification('Report exported!', 'success'); }
        function showNotification(message, type) { const n = document.createElement('div'); n.className = 'notification'; n.style.background = type === 'success' ? '#4cc9f0' : '#f72585'; n.innerHTML = `<i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i> ${message}`; document.body.appendChild(n); setTimeout(() => n.remove(), 3000); }
        
        const sidebar = document.getElementById('sidebar'), mainContent = document.getElementById('mainContent'), toggleBtn = document.getElementById('toggleSidebar'), mobileMenuBtn = document.getElementById('mobileMenuBtn');
        if(toggleBtn) toggleBtn.addEventListener('click', function() { sidebar.classList.toggle('collapsed'); mainContent.classList.toggle('expanded'); });
        if(mobileMenuBtn) mobileMenuBtn.addEventListener('click', function() { sidebar.classList.toggle('active'); });
        function handleResponsive() { if(window.innerWidth <= 768) { sidebar.classList.add('collapsed'); mainContent.classList.add('expanded'); } else { sidebar.classList.remove('collapsed', 'active'); mainContent.classList.remove('expanded'); } }
        window.addEventListener('resize', handleResponsive); handleResponsive();
        generateReport();
    </script>
</body>
</html>