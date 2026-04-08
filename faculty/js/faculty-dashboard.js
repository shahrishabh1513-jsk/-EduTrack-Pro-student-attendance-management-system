// Faculty Dashboard JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // Check authentication
    const userData = JSON.parse(localStorage.getItem('edutrack_user') || sessionStorage.getItem('eduTrackUser'));
    if (!userData || userData.role !== 'faculty') {
        window.location.href = '../../login.php';
        return;
    }

    // Set user info
    document.getElementById('facultyName').textContent = userData.full_name || 'Faculty User';
    document.getElementById('facultyId').textContent = userData.id_number || 'FAC001';
    
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
    loadTodaySchedule();
    loadPendingLeaves();
    
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
    // Stats are loaded from PHP, this is for dynamic updates
    console.log('Dashboard stats loaded');
}

function loadTodaySchedule() {
    const schedule = [
        { time: '09:00 - 10:00', subject: 'Data Structures', class: 'BSC IT Sem 3', students: 45 },
        { time: '11:00 - 12:00', subject: 'Database Management', class: 'BSC IT Sem 5', students: 38 },
        { time: '14:00 - 15:00', subject: 'Web Development', class: 'BSC IT Sem 3', students: 45 }
    ];
    
    const scheduleHTML = schedule.map(item => `
        <div class="schedule-item">
            <div class="schedule-time">${item.time}</div>
            <div><strong>${item.subject}</strong><br><small>${item.class} • ${item.students} students</small></div>
            <button class="take-attendance-btn" onclick="window.location.href='attendance/mark-attendance.php?subject=${encodeURIComponent(item.subject)}'">Take Attendance</button>
        </div>
    `).join('');
    
    const scheduleContainer = document.getElementById('todaySchedule');
    if(scheduleContainer) scheduleContainer.innerHTML = scheduleHTML;
}

async function loadPendingLeaves() {
    try {
        const response = await fetch('../api/leave.php?action=get_pending');
        const data = await response.json();
        const container = document.getElementById('pendingLeaves');
        if(container) {
            if(data.success && data.data.length > 0) {
                container.innerHTML = data.data.slice(0, 3).map(leave => `
                    <div class="request-item">
                        <div><strong>${leave.student_name}</strong><br><small>${leave.leave_type} • ${new Date(leave.from_date).toLocaleDateString()}</small></div>
                        <div><button class="approve-btn" onclick="approveLeave(${leave.id})">Approve</button><button class="reject-btn" onclick="rejectLeave(${leave.id})">Reject</button></div>
                    </div>
                `).join('');
            } else {
                container.innerHTML = '<p>No pending leave requests.</p>';
            }
        }
    } catch(error) {
        console.error('Error loading pending leaves:', error);
    }
}

async function approveLeave(id) {
    try {
        const response = await fetch('../api/leave.php?action=approve', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ leave_id: id, status: 'approved' })
        });
        const data = await response.json();
        if(data.success) {
            showNotification('Leave approved successfully', 'success');
            loadPendingLeaves();
        }
    } catch(error) {
        showNotification('Error approving leave', 'error');
    }
}

async function rejectLeave(id) {
    const remarks = prompt('Enter reason for rejection:');
    if(remarks) {
        try {
            const response = await fetch('../api/leave.php?action=approve', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ leave_id: id, status: 'rejected', remarks: remarks })
            });
            const data = await response.json();
            if(data.success) {
                showNotification('Leave rejected', 'success');
                loadPendingLeaves();
            }
        } catch(error) {
            showNotification('Error rejecting leave', 'error');
        }
    }
}

function showNotification(message, type) {
    const notification = document.createElement('div');
    notification.className = 'notification';
    notification.style.cssText = `position:fixed;top:20px;right:20px;padding:15px 25px;background:${type === 'success' ? '#4cc9f0' : '#f72585'};color:white;border-radius:10px;z-index:9999;animation:slideIn 0.3s ease;`;
    notification.innerHTML = `<i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i> ${message}`;
    document.body.appendChild(notification);
    setTimeout(() => notification.remove(), 3000);
}