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
    <title>Feedback View - EduTrack Pro</title>
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
        .rating-stars { color: #ffc107; font-size: 1.2rem; }
        .feedback-card { background: white; border-radius: 20px; padding: 25px; margin-bottom: 20px; transition: all 0.3s; }
        .feedback-card:hover { transform: translateX(5px); box-shadow: 0 5px 15px rgba(0,0,0,0.05); }
        .feedback-header { display: flex; justify-content: space-between; margin-bottom: 15px; flex-wrap: wrap; }
        .student-name { font-weight: 600; }
        .feedback-rating { margin: 10px 0; }
        .feedback-comment { color: #6c757d; line-height: 1.6; margin-top: 10px; padding-top: 10px; border-top: 1px solid #e9ecef; }
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
                <a href="upload-material.php"><i class="fas fa-upload"></i><span> Upload Material</span></a>
                <a href="repository.php"><i class="fas fa-folder"></i><span> Repository</span></a>
                <a href="notices.php"><i class="fas fa-bell"></i><span> Notices</span></a>
                <a href="feedback-view.php" class="active"><i class="fas fa-star"></i><span> Feedback</span></a>
                <a href="../../logout.php"><i class="fas fa-sign-out-alt"></i><span> Logout</span></a>
            </nav>
        </aside>

        <main class="main-content" id="mainContent">
            <header class="top-header">
                <button class="mobile-menu-btn" id="mobileMenuBtn"><i class="fas fa-bars"></i></button>
                <div><h2>Student Feedback</h2></div>
                <div class="user-profile">
                    <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($user['full_name']); ?>&background=4361ee&color=fff" alt="Profile">
                    <div><h4><?php echo htmlspecialchars($user['full_name']); ?></h4><p><?php echo htmlspecialchars($user['id_number']); ?></p></div>
                </div>
            </header>

            <div class="stats-grid">
                <div class="stat-card"><div class="stat-value" id="avgRating">0</div><div>Average Rating</div><div class="rating-stars" id="avgStars"></div></div>
                <div class="stat-card"><div class="stat-value" id="totalFeedback">0</div><div>Total Feedbacks</div></div>
                <div class="stat-card"><div class="stat-value" id="responseRate">0%</div><div>Response Rate</div></div>
            </div>

            <div id="feedbackList"></div>
        </main>
    </div>

    <script>
        let feedbacks = [
            { id: 1, student: 'Rishabh Sharma', roll: 'IT181', subject: 'Data Structures', rating: 5, comment: 'Excellent teaching style. Very clear explanations.', date: '2024-03-15' },
            { id: 2, student: 'Jenish Patel', roll: 'IT095', subject: 'Data Structures', rating: 4, comment: 'Good course content. Would like more practical examples.', date: '2024-03-14' },
            { id: 3, student: 'Vasu Mehta', roll: 'IT124', subject: 'Database Management', rating: 5, comment: 'Very helpful faculty. Always available for doubts.', date: '2024-03-13' },
            { id: 4, student: 'Hetvi Shah', roll: 'IT131', subject: 'Web Development', rating: 4, comment: 'Good practical sessions. Projects are challenging.', date: '2024-03-12' }
        ];
        
        function loadFeedbacks() {
            const total = feedbacks.length;
            const avg = feedbacks.reduce((sum, f) => sum + f.rating, 0) / total;
            document.getElementById('avgRating').textContent = avg.toFixed(1);
            document.getElementById('totalFeedback').textContent = total;
            document.getElementById('responseRate').textContent = '95%';
            document.getElementById('avgStars').innerHTML = generateStars(avg);
            
            const feedbackHTML = feedbacks.map(f => `
                <div class="feedback-card">
                    <div class="feedback-header">
                        <div><span class="student-name">${f.student}</span> (${f.roll}) • ${f.subject}</div>
                        <div>${new Date(f.date).toLocaleDateString()}</div>
                    </div>
                    <div class="feedback-rating">${generateStars(f.rating)}</div>
                    <div class="feedback-comment">"${f.comment}"</div>
                </div>
            `).join('');
            document.getElementById('feedbackList').innerHTML = feedbackHTML || '<p>No feedback received yet.</p>';
        }
        
        function generateStars(rating) {
            let stars = '';
            for(let i = 1; i <= 5; i++) stars += `<i class="fas fa-star" style="color:${i <= rating ? '#ffc107' : '#e9ecef'}"></i>`;
            return stars;
        }
        
        const sidebar = document.getElementById('sidebar'), mainContent = document.getElementById('mainContent'), toggleBtn = document.getElementById('toggleSidebar'), mobileMenuBtn = document.getElementById('mobileMenuBtn');
        if(toggleBtn) toggleBtn.addEventListener('click', function() { sidebar.classList.toggle('collapsed'); mainContent.classList.toggle('expanded'); });
        if(mobileMenuBtn) mobileMenuBtn.addEventListener('click', function() { sidebar.classList.toggle('active'); });
        function handleResponsive() { if(window.innerWidth <= 768) { sidebar.classList.add('collapsed'); mainContent.classList.add('expanded'); } else { sidebar.classList.remove('collapsed', 'active'); mainContent.classList.remove('expanded'); } }
        window.addEventListener('resize', handleResponsive); handleResponsive();
        loadFeedbacks();
    </script>
</body>
</html>