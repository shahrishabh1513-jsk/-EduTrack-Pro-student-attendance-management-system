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
    <title>Feedback Management - EduTrack Pro</title>
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
        .btn-export { background: #28a745; color: white; border: none; padding: 10px 20px; border-radius: 8px; cursor: pointer; }
        .feedback-table { background: white; border-radius: 20px; padding: 25px; overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #e9ecef; }
        th { background: #f8f9fa; font-weight: 600; }
        .stars { color: #ffc107; font-size: 0.9rem; }
        .rating-badge { display: inline-block; padding: 4px 8px; border-radius: 20px; font-size: 0.75rem; font-weight: 600; }
        .rating-high { background: rgba(76,201,240,0.1); color: #4cc9f0; }
        .rating-medium { background: rgba(255,193,7,0.1); color: #ffc107; }
        .rating-low { background: rgba(247,37,133,0.1); color: #f72585; }
        .view-btn { background: #4361ee; color: white; border: none; padding: 5px 12px; border-radius: 5px; cursor: pointer; }
        .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); justify-content: center; align-items: center; z-index: 1000; }
        .modal-content { background: white; border-radius: 20px; padding: 30px; max-width: 600px; width: 90%; max-height: 80vh; overflow-y: auto; }
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
                <a href="leave-management.php"><i class="fas fa-calendar-alt"></i><span> Leave Management</span></a>
                <a href="grievance-management.php"><i class="fas fa-exclamation-circle"></i><span> Grievance</span></a>
                <a href="feedback-management.php" class="active"><i class="fas fa-star"></i><span> Feedback</span></a>
                <a href="../../logout.php"><i class="fas fa-sign-out-alt"></i><span> Logout</span></a>
            </nav>
        </aside>

        <main class="main-content" id="mainContent">
            <header class="top-header">
                <button class="mobile-menu-btn" id="mobileMenuBtn"><i class="fas fa-bars"></i></button>
                <div><h2>Feedback Management</h2></div>
                <div class="user-profile">
                    <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($user['full_name']); ?>&background=4361ee&color=fff" alt="Profile">
                    <div><h4><?php echo htmlspecialchars($user['full_name']); ?></h4><p>Administrator</p></div>
                </div>
            </header>

            <div class="stats-grid">
                <div class="stat-card"><div class="stat-value" id="totalFeedbacks">0</div><div>Total Feedbacks</div></div>
                <div class="stat-card"><div class="stat-value" id="avgRating">0</div><div>Average Rating</div><div class="stars" id="avgStars"></div></div>
                <div class="stat-card"><div class="stat-value" id="positiveCount">0</div><div>Positive (4-5★)</div></div>
                <div class="stat-card"><div class="stat-value" id="responseRate">0%</div><div>Response Rate</div></div>
            </div>

            <div class="filter-bar">
                <div class="search-box"><i class="fas fa-search"></i><input type="text" id="searchInput" placeholder="Search by student or target..."></div>
                <select class="filter-select" id="categoryFilter"><option value="all">All Categories</option><option value="faculty">Faculty Feedback</option><option value="course">Course Feedback</option><option value="general">General Feedback</option></select>
                <select class="filter-select" id="ratingFilter"><option value="all">All Ratings</option><option value="5">5 Stars</option><option value="4">4 Stars</option><option value="3">3 Stars</option><option value="2">2 Stars</option><option value="1">1 Star</option></select>
                <button class="btn-export" onclick="exportFeedback()"><i class="fas fa-download"></i> Export Report</button>
            </div>

            <div class="feedback-table">
                <h3 style="margin-bottom:20px;"><i class="fas fa-star"></i> Student Feedback</h3>
                <div id="feedbackTable"></div>
            </div>
        </main>
    </div>

    <!-- Feedback Detail Modal -->
    <div id="feedbackModal" class="modal">
        <div class="modal-content">
            <h3>Feedback Details</h3>
            <div id="modalBody"></div>
            <button class="view-btn" onclick="closeModal()" style="margin-top:15px;">Close</button>
        </div>
    </div>

    <script>
        let feedbacks = [
            { id: 1, student: 'Rishabh Sharma', roll: 'IT181', category: 'faculty', target: 'Dr. Aakash Gupta', rating: 5, comment: 'Excellent teaching style. Very clear explanations and great practical examples. Always available for doubts.', date: '2024-03-15' },
            { id: 2, student: 'Jenish Patel', roll: 'IT095', category: 'course', target: 'Data Structures', rating: 4, comment: 'Good course content. Well structured curriculum. Could include more programming exercises.', date: '2024-03-14' },
            { id: 3, student: 'Vasu Mehta', roll: 'IT124', category: 'faculty', target: 'Prof. Neha Shah', rating: 5, comment: 'Very helpful faculty. Explains complex topics easily. Great mentor.', date: '2024-03-13' },
            { id: 4, student: 'Hetvi Shah', roll: 'IT131', category: 'general', target: 'Library', rating: 4, comment: 'Good library facilities. Need more reference books for Data Structures.', date: '2024-03-12' },
            { id: 5, student: 'Aarav Desai', roll: 'CSIT001', category: 'faculty', target: 'Prof. Niraj Mehta', rating: 3, comment: 'Good knowledge but pace is too fast sometimes.', date: '2024-03-11' }
        ];
        
        function loadFeedback() {
            const category = document.getElementById('categoryFilter').value;
            const rating = document.getElementById('ratingFilter').value;
            const search = document.getElementById('searchInput').value.toLowerCase();
            
            let filtered = [...feedbacks];
            if(category !== 'all') filtered = filtered.filter(f => f.category === category);
            if(rating !== 'all') filtered = filtered.filter(f => f.rating == rating);
            if(search) filtered = filtered.filter(f => f.student.toLowerCase().includes(search) || f.target.toLowerCase().includes(search));
            
            const total = feedbacks.length;
            const avg = feedbacks.reduce((sum, f) => sum + f.rating, 0) / total;
            const positive = feedbacks.filter(f => f.rating >= 4).length;
            document.getElementById('totalFeedbacks').textContent = total;
            document.getElementById('avgRating').textContent = avg.toFixed(1);
            document.getElementById('positiveCount').textContent = positive;
            document.getElementById('responseRate').textContent = '92%';
            document.getElementById('avgStars').innerHTML = generateStars(avg);
            
            const categoryNames = { faculty: 'Faculty', course: 'Course', general: 'General' };
            const tableHTML = `<table>
                <thead><tr><th>Student</th><th>Category</th><th>Target</th><th>Rating</th><th>Comment</th><th>Date</th><th>Action</th></tr></thead>
                <tbody>${filtered.map(f => `<tr>
                    <td><strong>${f.student}</strong><br><small>${f.roll}</small></td>
                    <td>${categoryNames[f.category]}</td>
                    <td>${f.target}</td>
                    <td><div class="stars">${generateStars(f.rating)}</div>${getRatingBadge(f.rating)}</div></td>
                    <td>${f.comment.substring(0, 60)}${f.comment.length > 60 ? '...' : ''}</div></td>
                    <td>${new Date(f.date).toLocaleDateString()}</td>
                    <td><button class="view-btn" onclick="viewFeedback(${f.id})">View Details</button></td>
                </tr>`).join('')}
                </tbody>
            </table>`;
            document.getElementById('feedbackTable').innerHTML = tableHTML;
        }
        
        function generateStars(rating) {
            let stars = '';
            for(let i = 1; i <= 5; i++) stars += `<i class="fas fa-star" style="color:${i <= Math.round(rating) ? '#ffc107' : '#e9ecef'}"></i>`;
            return stars;
        }
        
        function getRatingBadge(rating) {
            let className = 'rating-high';
            if(rating <= 2) className = 'rating-low';
            else if(rating <= 3) className = 'rating-medium';
            return `<div class="rating-badge ${className}" style="display:inline-block; margin-left:8px;">${rating}★</div>`;
        }
        
        function viewFeedback(id) {
            const f = feedbacks.find(f => f.id === id);
            if(f) {
                const categoryNames = { faculty: 'Faculty', course: 'Course', general: 'General' };
                document.getElementById('modalBody').innerHTML = `
                    <div style="margin-top:15px;">
                        <p><strong>Student:</strong> ${f.student} (${f.roll})</p>
                        <p><strong>Category:</strong> ${categoryNames[f.category]}</p>
                        <p><strong>Target:</strong> ${f.target}</p>
                        <p><strong>Rating:</strong> <span class="stars">${generateStars(f.rating)}</span> (${f.rating}/5)</p>
                        <p><strong>Date:</strong> ${new Date(f.date).toLocaleDateString()}</p>
                        <hr>
                        <h4>Detailed Feedback:</h4>
                        <p style="margin-top:10px; line-height:1.6;">${f.comment}</p>
                        <hr>
                        <h4>Admin Response:</h4>
                        <textarea id="adminResponse" style="width:100%; padding:10px; border:2px solid #e9ecef; border-radius:8px; margin-top:10px; font-family:'Poppins',sans-serif;" rows="3" placeholder="Add your response to this feedback..."></textarea>
                        <button class="view-btn" onclick="submitResponse(${f.id})" style="margin-top:10px; width:100%;">Submit Response</button>
                    </div>
                `;
                document.getElementById('feedbackModal').style.display = 'flex';
            }
        }
        
        function submitResponse(id) {
            const response = document.getElementById('adminResponse').value;
            if(response) {
                showNotification('Response submitted successfully!', 'success');
                closeModal();
            } else {
                showNotification('Please enter a response', 'error');
            }
        }
        
        function exportFeedback() {
            let csvContent = "Student,Roll Number,Category,Target,Rating,Comment,Date\n";
            feedbacks.forEach(f => {
                csvContent += `${f.student},${f.roll},${f.category},${f.target},${f.rating},${f.comment.replace(/,/g, ';')},${f.date}\n`;
            });
            const blob = new Blob([csvContent], { type: 'text/csv' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'feedback_report.csv';
            a.click();
            URL.revokeObjectURL(url);
            showNotification('Feedback exported!', 'success');
        }
        
        function closeModal() { document.getElementById('feedbackModal').style.display = 'none'; }
        function showNotification(message, type) { const n = document.createElement('div'); n.className = 'notification'; n.style.background = type === 'success' ? '#4cc9f0' : '#f72585'; n.innerHTML = `<i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i> ${message}`; document.body.appendChild(n); setTimeout(() => n.remove(), 3000); }
        
        document.getElementById('categoryFilter').addEventListener('change', loadFeedback);
        document.getElementById('ratingFilter').addEventListener('change', loadFeedback);
        document.getElementById('searchInput').addEventListener('keyup', loadFeedback);
        
        const sidebar = document.getElementById('sidebar'), mainContent = document.getElementById('mainContent'), toggleBtn = document.getElementById('toggleSidebar'), mobileMenuBtn = document.getElementById('mobileMenuBtn');
        if(toggleBtn) toggleBtn.addEventListener('click', function() { sidebar.classList.toggle('collapsed'); mainContent.classList.toggle('expanded'); });
        if(mobileMenuBtn) mobileMenuBtn.addEventListener('click', function() { sidebar.classList.toggle('active'); });
        function handleResponsive() { if(window.innerWidth <= 768) { sidebar.classList.add('collapsed'); mainContent.classList.add('expanded'); } else { sidebar.classList.remove('collapsed', 'active'); mainContent.classList.remove('expanded'); } }
        window.addEventListener('resize', handleResponsive); handleResponsive();
        loadFeedback();
        window.onclick = function(event) { if(event.target == document.getElementById('feedbackModal')) closeModal(); }
    </script>
</body>
</html>