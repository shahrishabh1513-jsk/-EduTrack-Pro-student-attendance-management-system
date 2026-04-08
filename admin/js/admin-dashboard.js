// Admin Dashboard JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // Check authentication
    const userData = JSON.parse(localStorage.getItem('edutrack_user') || sessionStorage.getItem('eduTrackUser'));
    if (!userData || userData.role !== 'admin') {
        window.location.href = '../../login.php';
        return;
    }

    // Set user info
    document.getElementById('adminName').textContent = userData.full_name || 'Administrator';
    document.getElementById('adminRole').textContent = 'System Administrator';
    
    // Sidebar toggle
    const sidebar = document.getElementById('sidebar');
    const toggleBtn = document.getElementById('toggleSidebar');
    const mobileMenuBtn = document.getElementById('mobileMenuBtn');
    
    if(toggleBtn) {
        toggleBtn.addEventListener('click', function() {
            sidebar.classList.toggle('collapsed');
            document.getElementById('mainContent').classList.toggle('expanded');
        });
    }
    
    if(mobileMenuBtn) {
        mobileMenuBtn.addEventListener('click', function() {
            sidebar.classList.toggle('active');
        });
    }
    
    // Load dashboard stats
    loadDashboardStats();
    loadRecentActivities();
    loadSystemHealth();
    
    // Auto-refresh data every 30 seconds
    setInterval(function() {
        loadDashboardStats();
        loadRecentActivities();
    }, 30000);
    
    // Responsive handling
    function handleResponsive() {
        if(window.innerWidth <= 768) {
            sidebar.classList.add('collapsed');
            document.getElementById('mainContent').classList.add('expanded');
        } else {
            sidebar.classList.remove('collapsed', 'active');
            document.getElementById('mainContent').classList.remove('expanded');
        }
    }
    
    window.addEventListener('resize', handleResponsive);
    handleResponsive();
});

function loadDashboardStats() {
    // Update statistics with animation
    const stats = [
        { id: 'totalStudents', target: 2547 },
        { id: 'totalFaculty', target: 128 },
        { id: 'activeCourses', target: 45 },
        { id: 'todayAttendance', target: 87 }
    ];
    
    stats.forEach(stat => {
        animateValue(stat.id, 0, stat.target, 1000);
    });
}

function animateValue(id, start, end, duration) {
    const element = document.getElementById(id);
    if(!element) return;
    const range = end - start;
    const increment = range / (duration / 16);
    let current = start;
    const timer = setInterval(function() {
        current += increment;
        if(current >= end) {
            clearInterval(timer);
            current = end;
        }
        element.textContent = Math.round(current);
        if(id === 'todayAttendance') element.textContent = Math.round(current) + '%';
    }, 16);
}

function loadRecentActivities() {
    const activities = [
        { icon: 'fa-user-plus', title: 'New Student Registration', detail: '25 students enrolled in BSC IT', time: '2 hours ago' },
        { icon: 'fa-file-alt', title: 'Exam Form Submissions', detail: '156 forms pending verification', time: '5 hours ago' },
        { icon: 'fa-calendar-check', title: 'Attendance Marked', detail: '12 classes completed today', time: '8 hours ago' },
        { icon: 'fa-chalkboard-teacher', title: 'Faculty Leave Request', detail: '3 new leave applications', time: '1 day ago' },
        { icon: 'fa-star', title: 'Feedback Received', detail: '45 new feedback submissions', time: '1 day ago' }
    ];
    
    const container = document.getElementById('recentActivities');
    if(container) {
        container.innerHTML = activities.map(a => `
            <div class="activity-item">
                <div class="activity-icon"><i class="fas ${a.icon}"></i></div>
                <div><strong>${a.title}</strong><br><small>${a.detail} - ${a.time}</small></div>
            </div>
        `).join('');
    }
}

function loadSystemHealth() {
    const healthMetrics = [
        { name: 'CPU Usage', value: 45, status: 'good' },
        { name: 'Memory Usage', value: 62, status: 'good' },
        { name: 'Disk Space', value: 38, status: 'good' },
        { name: 'Database', value: 99, status: 'excellent' }
    ];
    
    const container = document.getElementById('systemHealth');
    if(container) {
        container.innerHTML = healthMetrics.map(m => `
            <div class="health-item">
                <span>${m.name}</span>
                <div class="progress-bar" style="width:100px;">
                    <div class="progress-fill" style="width:${m.value}%; background:${getHealthColor(m.value)}"></div>
                </div>
                <span>${m.value}%</span>
            </div>
        `).join('');
    }
}

function getHealthColor(value) {
    if(value < 50) return '#4cc9f0';
    if(value < 80) return '#ffc107';
    return '#f72585';
}

function showNotification(message, type) {
    const notification = document.createElement('div');
    notification.className = 'notification';
    notification.style.cssText = `position:fixed;top:20px;right:20px;padding:15px 25px;background:${type === 'success' ? '#4cc9f0' : type === 'error' ? '#f72585' : '#4361ee'};color:white;border-radius:10px;z-index:9999;animation:slideIn 0.3s ease;`;
    notification.innerHTML = `<i class="fas ${type === 'success' ? 'fa-check-circle' : type === 'error' ? 'fa-exclamation-circle' : 'fa-info-circle'}"></i> ${message}`;
    document.body.appendChild(notification);
    setTimeout(() => notification.remove(), 3000);
}