// Admin Reports JavaScript
document.addEventListener('DOMContentLoaded', function() {
    loadReportFilters();
    initializeReportCharts();
});

let currentReportType = 'attendance';
let reportCharts = {};

function loadReportFilters() {
    // Set default dates
    const today = new Date();
    const lastMonth = new Date();
    lastMonth.setMonth(lastMonth.getMonth() - 1);
    
    const fromDateInput = document.getElementById('fromDate');
    const toDateInput = document.getElementById('toDate');
    
    if(fromDateInput) {
        fromDateInput.value = lastMonth.toISOString().split('T')[0];
    }
    if(toDateInput) {
        toDateInput.value = today.toISOString().split('T')[0];
    }
    
    // Add event listeners for filters
    const deptFilter = document.getElementById('deptFilter');
    const semesterFilter = document.getElementById('semesterFilter');
    const yearFilter = document.getElementById('yearFilter');
    
    if(deptFilter) deptFilter.addEventListener('change', () => generateReport(currentReportType));
    if(semesterFilter) semesterFilter.addEventListener('change', () => generateReport(currentReportType));
    if(yearFilter) yearFilter.addEventListener('change', () => generateReport(currentReportType));
}

function initializeReportCharts() {
    // Attendance Chart
    const attendanceCtx = document.getElementById('attendanceChart');
    if(attendanceCtx && typeof Chart !== 'undefined') {
        reportCharts.attendance = new Chart(attendanceCtx, {
            type: 'line',
            data: {
                labels: ['Week 1', 'Week 2', 'Week 3', 'Week 4'],
                datasets: [{
                    label: 'Attendance %',
                    data: [85, 87, 86, 89],
                    borderColor: '#4361ee',
                    backgroundColor: 'rgba(67,97,238,0.1)',
                    fill: true,
                    tension: 0.4
                }]
            },
            options: { responsive: true, maintainAspectRatio: true, scales: { y: { beginAtZero: true, max: 100 } } }
        });
    }
    
    // Performance Chart
    const performanceCtx = document.getElementById('performanceChart');
    if(performanceCtx && typeof Chart !== 'undefined') {
        reportCharts.performance = new Chart(performanceCtx, {
            type: 'bar',
            data: {
                labels: ['Sem 1', 'Sem 2', 'Sem 3', 'Sem 4', 'Sem 5', 'Sem 6'],
                datasets: [{ label: 'Average CGPA', data: [7.8, 8.0, 8.5, 8.3, 8.2, 8.4], backgroundColor: '#4361ee', borderRadius: 8 }]
            },
            options: { responsive: true, maintainAspectRatio: true, scales: { y: { beginAtZero: true, max: 10 } } }
        });
    }
    
    // Revenue Chart
    const revenueCtx = document.getElementById('revenueChart');
    if(revenueCtx && typeof Chart !== 'undefined') {
        reportCharts.revenue = new Chart(revenueCtx, {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                datasets: [{ label: 'Revenue (₹ Lakhs)', data: [28, 32, 35, 38, 42, 45], borderColor: '#4cc9f0', backgroundColor: 'rgba(76,201,240,0.1)', fill: true, tension: 0.4 }]
            },
            options: { responsive: true, maintainAspectRatio: true }
        });
    }
}

function generateReport(type) {
    currentReportType = type;
    const dept = document.getElementById('deptFilter')?.value || 'all';
    const semester = document.getElementById('semesterFilter')?.value || 'all';
    const year = document.getElementById('yearFilter')?.value || '2024';
    const fromDate = document.getElementById('fromDate')?.value || '';
    const toDate = document.getElementById('toDate')?.value || '';
    
    showNotification(`Generating ${type} report...`, 'info');
    
    // Simulate data loading
    setTimeout(() => {
        updateReportData(type, dept, semester);
        showNotification('Report generated successfully!', 'success');
    }, 1000);
}

function updateReportData(type, dept, semester) {
    if(type === 'attendance') {
        updateAttendanceReport(dept, semester);
    } else if(type === 'academic') {
        updateAcademicReport(dept, semester);
    } else if(type === 'financial') {
        updateFinancialReport();
    }
}

function updateAttendanceReport(dept, semester) {
    const stats = {
        totalStudents: 2547,
        avgAttendance: 87,
        above75: 2150,
        below75: 397
    };
    
    document.getElementById('totalStudents')?.setAttribute('data-value', stats.totalStudents);
    document.getElementById('avgAttendance')?.setAttribute('data-value', stats.avgAttendance + '%');
    document.getElementById('above75')?.setAttribute('data-value', stats.above75);
    document.getElementById('below75')?.setAttribute('data-value', stats.below75);
    
    if(reportCharts.attendance) {
        reportCharts.attendance.data.datasets[0].data = [85, 87, 86, 89];
        reportCharts.attendance.update();
    }
}

function updateAcademicReport(dept, semester) {
    const stats = {
        avgCgpa: 8.5,
        passPercent: 92,
        distinction: 1250,
        firstClass: 980
    };
    
    document.getElementById('avgCgpa')?.setAttribute('data-value', stats.avgCgpa);
    document.getElementById('passPercent')?.setAttribute('data-value', stats.passPercent + '%');
    document.getElementById('distinctionCount')?.setAttribute('data-value', stats.distinction);
    document.getElementById('firstClassCount')?.setAttribute('data-value', stats.firstClass);
    
    if(reportCharts.performance) {
        reportCharts.performance.data.datasets[0].data = [7.8, 8.0, 8.5, 8.3, 8.2, 8.4];
        reportCharts.performance.update();
    }
}

function updateFinancialReport() {
    const stats = {
        totalRevenue: 3850000,
        totalExpenses: 3200000,
        netProfit: 650000,
        pendingPayments: 450000
    };
    
    document.getElementById('totalRevenue')?.setAttribute('data-value', '₹' + (stats.totalRevenue / 100000).toFixed(1) + 'L');
    document.getElementById('totalExpenses')?.setAttribute('data-value', '₹' + (stats.totalExpenses / 100000).toFixed(1) + 'L');
    document.getElementById('netProfit')?.setAttribute('data-value', '₹' + (stats.netProfit / 100000).toFixed(1) + 'L');
    document.getElementById('pendingPayments')?.setAttribute('data-value', '₹' + (stats.pendingPayments / 1000).toFixed(0) + 'K');
    
    if(reportCharts.revenue) {
        reportCharts.revenue.data.datasets[0].data = [28, 32, 35, 38, 42, 45];
        reportCharts.revenue.update();
    }
}

function exportReport(format) {
    showNotification(`Exporting report as ${format.toUpperCase()}...`, 'success');
    setTimeout(() => {
        showNotification(`Report exported successfully!`, 'success');
    }, 1500);
}

function printReport() {
    window.print();
}

function showNotification(message, type) {
    const notification = document.createElement('div');
    notification.style.cssText = `position:fixed;top:20px;right:20px;padding:15px 25px;background:${type === 'success' ? '#4cc9f0' : type === 'error' ? '#f72585' : '#4361ee'};color:white;border-radius:10px;z-index:9999;animation:slideIn 0.3s ease;`;
    notification.innerHTML = `<i class="fas ${type === 'success' ? 'fa-check-circle' : type === 'error' ? 'fa-exclamation-circle' : 'fa-info-circle'}"></i> ${message}`;
    document.body.appendChild(notification);
    setTimeout(() => notification.remove(), 3000);
}