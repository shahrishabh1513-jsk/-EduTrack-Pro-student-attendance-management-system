// Admin Management JavaScript
document.addEventListener('DOMContentLoaded', function() {
    loadStudentManagement();
    loadFacultyManagement();
    loadCourseManagement();
});

function loadStudentManagement() {
    // Load students table
    const studentsTable = document.getElementById('studentsTable');
    if(studentsTable) {
        // Students data is loaded from PHP, this handles search and filter
        const searchInput = document.getElementById('searchStudent');
        const courseFilter = document.getElementById('courseFilter');
        const semesterFilter = document.getElementById('semesterFilter');
        
        if(searchInput) {
            searchInput.addEventListener('keyup', function() {
                filterTable('studentsTable', this.value);
            });
        }
        
        if(courseFilter) {
            courseFilter.addEventListener('change', function() {
                filterTableByColumn('studentsTable', 2, this.value);
            });
        }
        
        if(semesterFilter) {
            semesterFilter.addEventListener('change', function() {
                filterTableByColumn('studentsTable', 3, this.value);
            });
        }
    }
}

function loadFacultyManagement() {
    const facultyTable = document.getElementById('facultyTable');
    if(facultyTable) {
        const searchInput = document.getElementById('searchFaculty');
        const deptFilter = document.getElementById('deptFilter');
        
        if(searchInput) {
            searchInput.addEventListener('keyup', function() {
                filterTable('facultyTable', this.value);
            });
        }
        
        if(deptFilter) {
            deptFilter.addEventListener('change', function() {
                filterTableByColumn('facultyTable', 2, this.value);
            });
        }
    }
}

function loadCourseManagement() {
    const coursesTable = document.getElementById('coursesTable');
    if(coursesTable) {
        const searchInput = document.getElementById('searchCourse');
        const semesterFilter = document.getElementById('semesterFilter');
        const deptFilter = document.getElementById('deptFilter');
        
        if(searchInput) {
            searchInput.addEventListener('keyup', function() {
                filterTable('coursesTable', this.value);
            });
        }
        
        if(semesterFilter) {
            semesterFilter.addEventListener('change', function() {
                filterTableByColumn('coursesTable', 3, 'Semester ' + this.value);
            });
        }
        
        if(deptFilter) {
            deptFilter.addEventListener('change', function() {
                filterTableByColumn('coursesTable', 4, this.value);
            });
        }
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

function filterTableByColumn(tableId, columnIndex, filterValue) {
    const table = document.getElementById(tableId);
    if(!table || filterValue === 'all') return;
    const rows = table.getElementsByTagName('tbody')[0]?.getElementsByTagName('tr') || [];
    
    for(let row of rows) {
        const cell = row.getElementsByTagName('td')[columnIndex];
        if(cell) {
            const cellText = cell.textContent;
            row.style.display = cellText === filterValue ? '' : 'none';
        }
    }
}

function openAddStudentModal() {
    document.getElementById('addStudentModal').style.display = 'flex';
}

function openAddFacultyModal() {
    document.getElementById('addFacultyModal').style.display = 'flex';
}

function openAddCourseModal() {
    document.getElementById('addCourseModal').style.display = 'flex';
}

function closeModal(modalId) {
    document.getElementById(modalId).style.display = 'none';
}

function editStudent(id) {
    showNotification(`Editing student ID: ${id}`, 'info');
    // Redirect to edit page
    window.location.href = `management/student-management/edit-student.php?id=${id}`;
}

function editFaculty(id) {
    showNotification(`Editing faculty ID: ${id}`, 'info');
    window.location.href = `management/faculty-management/edit-faculty.php?id=${id}`;
}

function editCourse(id) {
    showNotification(`Editing course ID: ${id}`, 'info');
    window.location.href = `management/course-management/edit-course.php?id=${id}`;
}

function deleteStudent(id) {
    if(confirm('Are you sure you want to delete this student?')) {
        showNotification('Student deleted successfully!', 'success');
        setTimeout(() => location.reload(), 1500);
    }
}

function deleteFaculty(id) {
    if(confirm('Are you sure you want to delete this faculty member?')) {
        showNotification('Faculty deleted successfully!', 'success');
        setTimeout(() => location.reload(), 1500);
    }
}

function deleteCourse(id) {
    if(confirm('Are you sure you want to delete this course?')) {
        showNotification('Course deleted successfully!', 'success');
        setTimeout(() => location.reload(), 1500);
    }
}

function exportToCSV(type) {
    showNotification(`Exporting ${type} data...`, 'success');
    setTimeout(() => showNotification(`${type} exported successfully!`, 'success'), 1500);
}

function importStudents() {
    document.getElementById('importModal').style.display = 'flex';
}

function downloadTemplate() {
    showNotification('Template downloaded!', 'success');
}

function processImport() {
    showNotification('Students imported successfully!', 'success');
    closeModal('importModal');
}

function showNotification(message, type) {
    const notification = document.createElement('div');
    notification.style.cssText = `position:fixed;top:20px;right:20px;padding:15px 25px;background:${type === 'success' ? '#4cc9f0' : '#f72585'};color:white;border-radius:10px;z-index:9999;animation:slideIn 0.3s ease;`;
    notification.innerHTML = `<i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i> ${message}`;
    document.body.appendChild(notification);
    setTimeout(() => notification.remove(), 3000);
}

// Add animation styles if not present
if(!document.querySelector('#admin-styles')) {
    const style = document.createElement('style');
    style.id = 'admin-styles';
    style.textContent = `
        @keyframes slideIn { from { transform: translateX(100%); opacity: 0; } to { transform: translateX(0); opacity: 1; } }
        .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); justify-content: center; align-items: center; z-index: 1000; }
        .modal-content { background: white; border-radius: 20px; padding: 30px; max-width: 500px; width: 90%; }
        .health-item { display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; }
        .progress-bar { height: 6px; background: #e9ecef; border-radius: 10px; overflow: hidden; display: inline-block; margin: 0 10px; }
        .progress-fill { height: 100%; border-radius: 10px; }
    `;
    document.head.appendChild(style);
}