// Admin Attendance JavaScript
document.addEventListener('DOMContentLoaded', function() {
    loadAttendanceOverview();
    loadAttendanceCharts();
    loadStudentAttendance();
    loadFacultyAttendance();
});

function loadAttendanceOverview() {
    const overviewContainer = document.getElementById('attendanceOverview');
    if(!overviewContainer) return;
    
    // Department-wise attendance data
    const deptData = [
        { name: 'Computer Science', total: 850, present: 765, percent: 90 },
        { name: 'Information Technology', total: 620, present: 558, percent: 90 },
        { name: 'Electronics', total: 450, present: 378, percent: 84 },
        { name: 'Mechanical', total: 380, present: 304, percent: 80 },
        { name: 'Civil', total: 247, present: 185, percent: 75 }
    ];
    
    const total = deptData.reduce((s, d) => s + d.total, 0);
    const totalPresent = deptData.reduce((s, d) => s + d.present, 0);
    const avgPercent = Math.round((totalPresent / total) * 100);
    
    // Update stats
    document.getElementById('totalStudents')?.setAttribute('data-value', total);
    document.getElementById('avgAttendance')?.setAttribute('data-value', avgPercent + '%');
    document.getElementById('above75')?.setAttribute('data-value', deptData.filter(d => d.percent >= 75).length);
    document.getElementById('below75')?.setAttribute('data-value', deptData.filter(d => d.percent < 75).length);
    
    // Build table
    const tableHTML = `
        <table class="data-table">
            <thead><tr><th>Department</th><th>Total Students</th><th>Present Today</th><th>Attendance %</th></tr></thead>
            <tbody>
                ${deptData.map(d => `
                    <tr>
                        <td><strong>${d.name}</strong></td>
                        <td>${d.total}</td>
                        <td>${d.present}</td>
                        <td>${d.percent}%<div class="progress-bar"><div class="progress-fill" style="width:${d.percent}%"></div></div></td>
                    </tr>
                `).join('')}
            </tbody>
        </table>
    `;
    overviewContainer.innerHTML = tableHTML;
}

function loadAttendanceCharts() {
    const chartCanvas = document.getElementById('attendanceChart');
    if(!chartCanvas || typeof Chart === 'undefined') return;
    
    // Destroy existing chart if any
    if(window.attendanceChartInstance) {
        window.attendanceChartInstance.destroy();
    }
    
    window.attendanceChartInstance = new Chart(chartCanvas, {
        type: 'line',
        data: {
            labels: ['Week 1', 'Week 2', 'Week 3', 'Week 4', 'Week 5', 'Week 6'],
            datasets: [{
                label: 'Overall Attendance %',
                data: [85, 87, 86, 89, 88, 90],
                borderColor: '#4361ee',
                backgroundColor: 'rgba(67,97,238,0.1)',
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: { position: 'top' }
            },
            scales: {
                y: { beginAtZero: true, max: 100 }
            }
        }
    });
}

function loadStudentAttendance() {
    const studentTable = document.getElementById('studentAttendanceTable');
    if(!studentTable) return;
    
    const searchInput = document.getElementById('searchStudent');
    if(searchInput) {
        searchInput.addEventListener('keyup', function() {
            filterTable('studentAttendanceTable', this.value);
        });
    }
}

function loadFacultyAttendance() {
    const facultyTable = document.getElementById('facultyAttendanceTable');
    if(!facultyTable) return;
    
    const searchInput = document.getElementById('searchFaculty');
    if(searchInput) {
        searchInput.addEventListener('keyup', function() {
            filterTable('facultyAttendanceTable', this.value);
        });
    }
}

function filterTable(tableId, searchTerm) {
    const table = document.getElementById(tableId);
    if(!table) return;
    const rows = table.getElementsByTagName('tbody')[0]?.getElementsByTagName('tr') || [];
    const term = searchTerm.toLowerCase();
    
    for(let row of rows) {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(term) ? '' : 'none';
    }
}

function generateAttendanceReport() {
    const dept = document.getElementById('deptFilter')?.value || 'all';
    const semester = document.getElementById('semesterFilter')?.value || 'all';
    const fromDate = document.getElementById('fromDate')?.value || '';
    const toDate = document.getElementById('toDate')?.value || '';
    
    showNotification(`Generating report for ${dept} department, Semester ${semester}...`, 'info');
    setTimeout(() => {
        showNotification('Report generated successfully!', 'success');
    }, 2000);
}

function exportAttendanceReport() {
    showNotification('Attendance report exported!', 'success');
}

function viewStudentAttendance(id) {
    showNotification(`Viewing attendance for student ID: ${id}`, 'info');
    window.location.href = `student-attendance.php?id=${id}`;
}

function viewFacultyAttendance(id) {
    showNotification(`Viewing attendance for faculty ID: ${id}`, 'info');
    window.location.href = `faculty-attendance.php?id=${id}`;
}

function showNotification(message, type) {
    const notification = document.createElement('div');
    notification.style.cssText = `position:fixed;top:20px;right:20px;padding:15px 25px;background:${type === 'success' ? '#4cc9f0' : type === 'error' ? '#f72585' : '#4361ee'};color:white;border-radius:10px;z-index:9999;animation:slideIn 0.3s ease;`;
    notification.innerHTML = `<i class="fas ${type === 'success' ? 'fa-check-circle' : type === 'error' ? 'fa-exclamation-circle' : 'fa-info-circle'}"></i> ${message}`;
    document.body.appendChild(notification);
    setTimeout(() => notification.remove(), 3000);
}

// Add styles if not present
if(!document.querySelector('#attendance-styles')) {
    const style = document.createElement('style');
    style.id = 'attendance-styles';
    style.textContent = `
        .data-table { width: 100%; border-collapse: collapse; }
        .data-table th, .data-table td { padding: 12px; text-align: left; border-bottom: 1px solid #e9ecef; }
        .data-table th { background: #f8f9fa; font-weight: 600; }
        .progress-bar { height: 6px; background: #e9ecef; border-radius: 10px; width: 80px; display: inline-block; margin-left: 10px; }
        .progress-fill { height: 100%; background: linear-gradient(90deg, #4361ee, #4cc9f0); border-radius: 10px; }
    `;
    document.head.appendChild(style);
}