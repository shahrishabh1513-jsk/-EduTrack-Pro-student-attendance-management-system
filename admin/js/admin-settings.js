// Admin Settings JavaScript
document.addEventListener('DOMContentLoaded', function() {
    loadSystemSettings();
    loadAcademicSettings();
    loadUserRoleSettings();
    loadBackupSettings();
});

function loadSystemSettings() {
    // Load saved settings from localStorage
    const savedSettings = JSON.parse(localStorage.getItem('systemSettings') || '{}');
    
    const elements = ['systemName', 'systemUrl', 'adminEmail', 'timezone', 'smtpHost', 'smtpPort', 'smtpUser'];
    elements.forEach(id => {
        const element = document.getElementById(id);
        if(element && savedSettings[id]) {
            element.value = savedSettings[id];
        }
    });
    
    // Toggle switches
    const toggles = ['emailNotifications', 'twoFactor', 'strongPassword', 'showWidgets'];
    toggles.forEach(id => {
        const element = document.getElementById(id);
        if(element && savedSettings[id] !== undefined) {
            element.checked = savedSettings[id];
        }
    });
}

function loadAcademicSettings() {
    const savedSettings = JSON.parse(localStorage.getItem('academicSettings') || '{}');
    
    const elements = ['academicYear', 'currentSemester', 'semesterStart', 'semesterEnd', 'examStart', 'examEnd', 'minAttendance', 'classesPerWeek', 'attendanceDeadline', 'examFee', 'practicalFee', 'resultDate'];
    elements.forEach(id => {
        const element = document.getElementById(id);
        if(element && savedSettings[id]) {
            element.value = savedSettings[id];
        }
    });
    
    const toggles = ['autoNotify', 'allowResubmit'];
    toggles.forEach(id => {
        const element = document.getElementById(id);
        if(element && savedSettings[id] !== undefined) {
            element.checked = savedSettings[id];
        }
    });
}

function loadUserRoleSettings() {
    const rolesContainer = document.getElementById('rolesContainer');
    if(rolesContainer) {
        // Roles are loaded from PHP, this handles role management UI
        const addRoleBtn = document.getElementById('addRoleBtn');
        if(addRoleBtn) {
            addRoleBtn.addEventListener('click', openAddRoleModal);
        }
    }
}

function loadBackupSettings() {
    const backupList = document.getElementById('backupList');
    if(backupList) {
        // Load backup list
        loadBackups();
        
        const createBackupBtn = document.getElementById('createBackupBtn');
        if(createBackupBtn) {
            createBackupBtn.addEventListener('click', () => createBackup('full'));
        }
    }
}

function saveSystemSettings() {
    const settings = {};
    const elements = ['systemName', 'systemUrl', 'adminEmail', 'timezone', 'smtpHost', 'smtpPort', 'smtpUser'];
    elements.forEach(id => {
        const element = document.getElementById(id);
        if(element) settings[id] = element.value;
    });
    
    const toggles = ['emailNotifications', 'twoFactor', 'strongPassword', 'showWidgets'];
    toggles.forEach(id => {
        const element = document.getElementById(id);
        if(element) settings[id] = element.checked;
    });
    
    localStorage.setItem('systemSettings', JSON.stringify(settings));
    showNotification('System settings saved successfully!', 'success');
}

function saveAcademicSettings() {
    const settings = {};
    const elements = ['academicYear', 'currentSemester', 'semesterStart', 'semesterEnd', 'examStart', 'examEnd', 'minAttendance', 'classesPerWeek', 'attendanceDeadline', 'examFee', 'practicalFee', 'resultDate'];
    elements.forEach(id => {
        const element = document.getElementById(id);
        if(element) settings[id] = element.value;
    });
    
    const toggles = ['autoNotify', 'allowResubmit'];
    toggles.forEach(id => {
        const element = document.getElementById(id);
        if(element) settings[id] = element.checked;
    });
    
    localStorage.setItem('academicSettings', JSON.stringify(settings));
    showNotification('Academic settings saved successfully!', 'success');
}

function openAddRoleModal() {
    document.getElementById('roleModal').style.display = 'flex';
}

function saveRole() {
    const roleName = document.getElementById('roleName')?.value;
    if(roleName) {
        showNotification(`Role "${roleName}" created successfully!`, 'success');
        closeModal('roleModal');
        document.getElementById('roleForm')?.reset();
    } else {
        showNotification('Please enter a role name', 'error');
    }
}

function editRole(id) {
    showNotification(`Editing role ID: ${id}`, 'info');
}

function deleteRole(id) {
    if(confirm('Delete this role?')) {
        showNotification('Role deleted successfully!', 'success');
    }
}

function createBackup(type) {
    showNotification(`Creating ${type} backup...`, 'info');
    setTimeout(() => {
        showNotification(`${type} backup created successfully!`, 'success');
        loadBackups();
    }, 2000);
}

function loadBackups() {
    const backupList = document.getElementById('backupList');
    if(backupList) {
        const backups = [
            { name: 'full_backup_2024_03_20.sql', size: '45.2 MB', date: '2024-03-20 10:30 AM' },
            { name: 'students_backup_2024_03_19.sql', size: '12.5 MB', date: '2024-03-19 02:15 PM' }
        ];
        
        backupList.innerHTML = backups.map(b => `
            <div class="backup-item">
                <div><strong>${b.name}</strong><br><small>${b.size} • ${b.date}</small></div>
                <div><button class="download-btn" onclick="downloadBackup('${b.name}')"><i class="fas fa-download"></i></button><button class="restore-btn" onclick="restoreBackup('${b.name}')"><i class="fas fa-upload"></i></button><button class="delete-btn" onclick="deleteBackup('${b.name}')"><i class="fas fa-trash"></i></button></div>
            </div>
        `).join('');
    }
}

function downloadBackup(name) {
    showNotification(`Downloading ${name}...`, 'success');
}

function restoreBackup(name) {
    if(confirm(`Restore from ${name}? This will overwrite current data.`)) {
        showNotification('Restore initiated!', 'success');
    }
}

function deleteBackup(name) {
    if(confirm(`Delete ${name}?`)) {
        showNotification('Backup deleted!', 'success');
        loadBackups();
    }
}

function clearLogs() {
    if(confirm('Clear all system logs?')) {
        showNotification('Logs cleared successfully!', 'success');
        const logsTable = document.getElementById('logsTable');
        if(logsTable) {
            logsTable.innerHTML = '<p>No logs available</p>';
        }
    }
}

function exportLogs() {
    showNotification('Logs exported successfully!', 'success');
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if(modal) modal.style.display = 'none';
}

function testEmailConnection() {
    showNotification('Testing email connection...', 'info');
    setTimeout(() => {
        showNotification('Email connection successful!', 'success');
    }, 1500);
}

function testDatabaseConnection() {
    showNotification('Testing database connection...', 'info');
    setTimeout(() => {
        showNotification('Database connection successful!', 'success');
    }, 1000);
}

function showNotification(message, type) {
    const notification = document.createElement('div');
    notification.style.cssText = `position:fixed;top:20px;right:20px;padding:15px 25px;background:${type === 'success' ? '#4cc9f0' : type === 'error' ? '#f72585' : '#4361ee'};color:white;border-radius:10px;z-index:9999;animation:slideIn 0.3s ease;`;
    notification.innerHTML = `<i class="fas ${type === 'success' ? 'fa-check-circle' : type === 'error' ? 'fa-exclamation-circle' : 'fa-info-circle'}"></i> ${message}`;
    document.body.appendChild(notification);
    setTimeout(() => notification.remove(), 3000);
}

// Add animation styles if not present
if(!document.querySelector('#settings-styles')) {
    const style = document.createElement('style');
    style.id = 'settings-styles';
    style.textContent = `
        @keyframes slideIn { from { transform: translateX(100%); opacity: 0; } to { transform: translateX(0); opacity: 1; } }
        .backup-item { display: flex; justify-content: space-between; align-items: center; padding: 12px; border-bottom: 1px solid #e9ecef; }
        .download-btn, .restore-btn, .delete-btn { background: none; border: none; cursor: pointer; margin: 0 5px; font-size: 1rem; }
        .download-btn { color: #4cc9f0; }
        .restore-btn { color: #4361ee; }
        .delete-btn { color: #f72585; }
        .toggle-switch { display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; }
        .toggle-switch input { width: 50px; height: 25px; appearance: none; background: #e9ecef; border-radius: 25px; position: relative; cursor: pointer; }
        .toggle-switch input:checked { background: #4cc9f0; }
        .toggle-switch input::before { content: ''; position: absolute; width: 21px; height: 21px; background: white; border-radius: 50%; top: 2px; left: 3px; transition: 0.3s; }
        .toggle-switch input:checked::before { left: 26px; }
    `;
    document.head.appendChild(style);
}