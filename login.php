<?php
require_once 'includes/config.php';

if(isLoggedIn()) {
    if(hasRole('student')) redirect('student/dashboard.php');
    elseif(hasRole('faculty')) redirect('faculty/dashboard.php');
    elseif(hasRole('admin')) redirect('admin/dashboard.php');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EduTrack Pro - Login</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Poppins', sans-serif; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; }
        .login-container { min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 20px; }
        .login-wrapper { display: flex; max-width: 1200px; width: 100%; background: white; border-radius: 30px; overflow: hidden; box-shadow: 0 20px 25px rgba(0,0,0,0.15); }
        .login-left { flex: 1; background: linear-gradient(135deg, #4361ee, #3f37c9); padding: 60px 40px; color: white; }
        .login-left h1 { font-size: 2.5rem; margin-bottom: 20px; }
        .login-left p { margin-bottom: 40px; opacity: 0.9; }
        .features { display: flex; flex-direction: column; gap: 15px; }
        .feature-item { display: flex; align-items: center; gap: 12px; background: rgba(255,255,255,0.1); padding: 12px 18px; border-radius: 50px; }
        .login-right { flex: 1; padding: 60px 50px; }
        .logo { text-align: center; margin-bottom: 40px; }
        .logo i { font-size: 2.5rem; color: #4361ee; }
        .logo h2 { font-size: 1.8rem; background: linear-gradient(135deg, #4361ee, #3f37c9); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .role-selector { display: flex; gap: 12px; margin-bottom: 30px; background: #f0f0f0; padding: 8px; border-radius: 50px; }
        .role-btn { flex: 1; padding: 12px; border: none; background: transparent; border-radius: 50px; font-size: 0.95rem; font-weight: 500; cursor: pointer; transition: all 0.3s; }
        .role-btn.active { background: white; color: #4361ee; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 500; color: #333; }
        .form-group input { width: 100%; padding: 14px 16px; border: 2px solid #e0e0e0; border-radius: 12px; font-size: 0.95rem; transition: all 0.3s; }
        .form-group input:focus { outline: none; border-color: #4361ee; }
        .login-btn { width: 100%; padding: 16px; background: linear-gradient(135deg, #4361ee, #3f37c9); color: white; border: none; border-radius: 12px; font-size: 1rem; font-weight: 600; cursor: pointer; transition: all 0.3s; }
        .login-btn:hover { transform: translateY(-2px); box-shadow: 0 10px 20px rgba(67,97,238,0.3); }
        .demo-credentials { margin-top: 20px; padding: 15px; background: #f8f9fa; border-radius: 12px; font-size: 0.85rem; }
        .demo-credentials p { margin: 5px 0; }
        .notification { position: fixed; top: 20px; right: 20px; padding: 15px 25px; border-radius: 10px; display: flex; align-items: center; gap: 10px; z-index: 9999; animation: slideIn 0.3s ease; color: white; }
        @keyframes slideIn { from { transform: translateX(100%); opacity: 0; } to { transform: translateX(0); opacity: 1; } }
        @keyframes slideOut { from { transform: translateX(0); opacity: 1; } to { transform: translateX(100%); opacity: 0; } }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-wrapper">
            <div class="login-left">
                <h1>Welcome to EduTrack Pro</h1>
                <p>Smart Academic Management System for Modern Education</p>
                <div class="features">
                    <div class="feature-item"><i class="fas fa-check-circle"></i><span>Real-time Attendance Tracking</span></div>
                    <div class="feature-item"><i class="fas fa-check-circle"></i><span>Comprehensive Academic Management</span></div>
                    <div class="feature-item"><i class="fas fa-check-circle"></i><span>24/7 Access to Educational Resources</span></div>
                </div>
            </div>
            <div class="login-right">
                <div class="logo"><i class="fas fa-graduation-cap"></i><h2>EduTrack Pro</h2></div>
                <div class="role-selector">
                    <button class="role-btn active" data-role="student">Student</button>
                    <button class="role-btn" data-role="faculty">Faculty</button>
                    <button class="role-btn" data-role="admin">Admin</button>
                </div>
                <form id="loginForm">
                    <div class="form-group"><label>Username / Email</label><input type="text" id="username" placeholder="Enter username or email" required></div>
                    <div class="form-group"><label>Password</label><input type="password" id="password" placeholder="Enter password" required></div>
                    <button type="submit" class="login-btn">Login</button>
                </form>
                <div class="demo-credentials">
                    <p><strong>Demo Credentials:</strong></p>
                    <p>Student: jenish / password123</p>
                    <p>Faculty: aakash / password123</p>
                    <p>Admin: admin / admin123</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        let selectedRole = 'student';
        document.querySelectorAll('.role-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                document.querySelectorAll('.role-btn').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                selectedRole = this.dataset.role;
            });
        });
        
        document.getElementById('loginForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            const username = document.getElementById('username').value;
            const password = document.getElementById('password').value;
            if(!username || !password) { showNotification('Please enter username and password', 'error'); return; }
            
            const formData = new FormData();
            formData.append('action', 'login');
            formData.append('username', username);
            formData.append('password', password);
            
            try {
                const response = await fetch('api/auth.php', { method: 'POST', body: formData });
                const data = await response.json();
                if(data.success) {
                    showNotification('Login successful! Redirecting...', 'success');
                    setTimeout(() => { window.location.href = data.role + '/dashboard.php'; }, 1000);
                } else { showNotification(data.message, 'error'); }
            } catch(error) { showNotification('Login failed. Please try again.', 'error'); }
        });
        
        function showNotification(message, type) {
            const notification = document.createElement('div');
            notification.className = 'notification';
            notification.style.background = type === 'success' ? '#4cc9f0' : '#f72585';
            notification.innerHTML = `<i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i> ${message}`;
            document.body.appendChild(notification);
            setTimeout(() => { notification.style.animation = 'slideOut 0.3s ease'; setTimeout(() => notification.remove(), 300); }, 3000);
        }
    </script>
</body>
</html>