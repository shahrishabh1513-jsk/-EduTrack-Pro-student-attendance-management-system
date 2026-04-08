// Faculty Classes JavaScript
document.addEventListener('DOMContentLoaded', function() {
    loadClasses();
    loadTimetable();
    
    // Search functionality
    const searchInput = document.getElementById('searchInput');
    if(searchInput) {
        searchInput.addEventListener('keyup', filterStudents);
    }
});

function loadClasses() {
    const classes = [
        { code: 'CS301', name: 'Data Structures', semester: 3, students: 45, schedule: 'Mon/Wed 9:00 AM', room: 'Room 101' },
        { code: 'CS302', name: 'Database Management', semester: 3, students: 38, schedule: 'Tue/Thu 11:00 AM', room: 'Room 203' },
        { code: 'CS303', name: 'Web Development', semester: 3, students: 45, schedule: 'Mon/Wed 2:00 PM', room: 'Lab 3' }
    ];
    
    const classesGrid = document.getElementById('classesGrid');
    if(classesGrid) {
        classesGrid.innerHTML = classes.map(c => `
            <div class="class-card" onclick="viewClass('${c.code}')">
                <div class="class-header"><h3>${c.name}</h3><p>${c.code}</p></div>
                <div class="class-body">
                    <p><strong>Schedule:</strong> ${c.schedule}</p>
                    <p><strong>Room:</strong> ${c.room}</p>
                    <div class="class-stats"><div class="class-stat"><div class="class-stat-value">${c.students}</div><small>Students</small></div></div>
                    <button class="btn-view">View Class</button>
                </div>
            </div>
        `).join('');
    }
}

function loadTimetable() {
    const timetable = {
        Monday: ['Data Structures (9:00-10:00)', 'Free', 'Web Development (14:00-15:00)'],
        Tuesday: ['Database Management (11:00-12:00)', 'Free', 'Free'],
        Wednesday: ['Data Structures (9:00-10:00)', 'Free', 'Web Development (14:00-15:00)'],
        Thursday: ['Database Management (11:00-12:00)', 'Free', 'Free'],
        Friday: ['Free', 'Free', 'Faculty Meeting (15:00-16:00)']
    };
    
    const timetableGrid = document.getElementById('timetableGrid');
    if(timetableGrid) {
        let html = '<div class="grid-cell time-slot"><strong>Time / Day</strong></div>';
        const days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
        days.forEach(day => html += `<div class="grid-header">${day}</div>`);
        
        const slots = ['09:00-10:00', '11:00-12:00', '14:00-15:00'];
        slots.forEach((slot, idx) => {
            html += `<div class="grid-cell time-slot">${slot}</div>`;
            days.forEach(day => {
                const cls = timetable[day][idx];
                html += `<div class="grid-cell">${cls !== 'Free' ? `<div class="class-item">${cls}</div>` : '-'}</div>`;
            });
        });
        timetableGrid.innerHTML = html;
    }
}

function viewClass(classCode) {
    window.location.href = `class-detail.php?class=${classCode}`;
}

function filterStudents() {
    const searchTerm = document.getElementById('searchInput')?.value.toLowerCase();
    const rows = document.querySelectorAll('#studentTableBody tr');
    
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(searchTerm) ? '' : 'none';
    });
}

function exportStudentList() {
    showNotification('Student list exported successfully!', 'success');
}

function showNotification(message, type) {
    const notification = document.createElement('div');
    notification.style.cssText = `position:fixed;top:20px;right:20px;padding:15px 25px;background:${type === 'success' ? '#4cc9f0' : '#f72585'};color:white;border-radius:10px;z-index:9999;animation:slideIn 0.3s ease;`;
    notification.innerHTML = `<i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i> ${message}`;
    document.body.appendChild(notification);
    setTimeout(() => notification.remove(), 3000);
}