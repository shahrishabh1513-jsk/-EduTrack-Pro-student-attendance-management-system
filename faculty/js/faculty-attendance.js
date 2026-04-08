// Faculty Attendance JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // Initialize date picker
    const dateInput = document.getElementById('attendanceDate');
    if(dateInput) {
        dateInput.value = new Date().toISOString().split('T')[0];
    }
    
    // Load students for the selected course
    loadStudents();
    
    // Event listeners
    const courseSelect = document.getElementById('courseSelect');
    if(courseSelect) {
        courseSelect.addEventListener('change', loadStudents);
    }
});

function loadStudents() {
    const courseId = document.getElementById('courseSelect')?.value;
    if(!courseId) return;
    
    // Sample student data
    const students = [
        { id: 1, roll: 'IT181', name: 'Rishabh Sharma' },
        { id: 2, roll: 'IT095', name: 'Jenish Patel' },
        { id: 3, roll: 'IT124', name: 'Vasu Mehta' },
        { id: 4, roll: 'IT131', name: 'Hetvi Shah' }
    ];
    
    const tableBody = document.getElementById('studentsTableBody');
    if(tableBody) {
        tableBody.innerHTML = students.map(s => `
            <tr>
                <td>${s.roll}</td>
                <td>${s.name}</td>
                <td>
                    <select name="status[${s.id}]" class="status-select">
                        <option value="present">Present</option>
                        <option value="absent">Absent</option>
                        <option value="late">Late</option>
                    </select>
                </td>
                <td><input type="text" name="remarks[${s.id}]" placeholder="Optional" style="padding:5px;border-radius:5px;border:1px solid #e9ecef;"></td>
            </tr>
        `).join('');
    }
}

function submitAttendance() {
    const form = document.getElementById('attendanceForm');
    if(form) {
        const formData = new FormData(form);
        showNotification('Attendance submitted successfully!', 'success');
        form.reset();
    }
}

function markBulkAttendance(status) {
    const selects = document.querySelectorAll('.status-select');
    selects.forEach(select => select.value = status);
    showNotification(`All students marked as ${status}`, 'success');
}

function generateQRCode() {
    const subject = document.getElementById('qrSubject')?.value;
    if(subject) {
        showNotification('QR Code generated! Students can now scan.', 'success');
    }
}

function showNotification(message, type) {
    const notification = document.createElement('div');
    notification.style.cssText = `position:fixed;top:20px;right:20px;padding:15px 25px;background:${type === 'success' ? '#4cc9f0' : '#f72585'};color:white;border-radius:10px;z-index:9999;animation:slideIn 0.3s ease;`;
    notification.innerHTML = `<i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i> ${message}`;
    document.body.appendChild(notification);
    setTimeout(() => notification.remove(), 3000);
}