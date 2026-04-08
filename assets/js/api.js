/**
 * EduTrack Pro - API Module
 * Handles all API calls to the backend
 */

const API = {
    // Base URL
    baseUrl: EduTrack.config.apiUrl,
    
    // Generic fetch wrapper
    async fetch(endpoint, options = {}) {
        const defaultOptions = {
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        };
        
        const mergedOptions = { ...defaultOptions, ...options };
        
        try {
            const response = await fetch(this.baseUrl + endpoint, mergedOptions);
            const data = await response.json();
            
            if (!response.ok) {
                throw new Error(data.message || 'API request failed');
            }
            
            return data;
        } catch (error) {
            console.error('API Error:', error);
            throw error;
        }
    },
    
    // ==================== AUTH API ====================
    auth: {
        login: (username, password) => {
            const formData = new FormData();
            formData.append('action', 'login');
            formData.append('username', username);
            formData.append('password', password);
            return fetch(API.baseUrl + 'auth.php', { method: 'POST', body: formData }).then(r => r.json());
        },
        
        logout: () => {
            return fetch(API.baseUrl + 'auth.php?action=logout').then(r => r.json());
        },
        
        checkSession: () => {
            return fetch(API.baseUrl + 'auth.php?action=check_session').then(r => r.json());
        },
        
        changePassword: (oldPassword, newPassword) => {
            return API.fetch('auth.php?action=change_password', {
                method: 'POST',
                body: JSON.stringify({ old_password: oldPassword, new_password: newPassword })
            });
        }
    },
    
    // ==================== STUDENTS API ====================
    students: {
        list: (params = {}) => {
            const queryString = new URLSearchParams(params).toString();
            return API.fetch(`students.php?action=list&${queryString}`);
        },
        
        get: (id) => {
            return API.fetch(`students.php?action=get&id=${id}`);
        },
        
        add: (data) => {
            return API.fetch('students.php?action=add', {
                method: 'POST',
                body: JSON.stringify(data)
            });
        },
        
        update: (id, data) => {
            return API.fetch(`students.php?action=update&id=${id}`, {
                method: 'PUT',
                body: JSON.stringify(data)
            });
        },
        
        delete: (id) => {
            return API.fetch(`students.php?action=delete&id=${id}`, { method: 'DELETE' });
        },
        
        promote: (studentIds, newSemester) => {
            return API.fetch('students.php?action=promote', {
                method: 'POST',
                body: JSON.stringify({ student_ids: studentIds, new_semester: newSemester })
            });
        },
        
        statistics: () => {
            return API.fetch('students.php?action=statistics');
        }
    },
    
    // ==================== FACULTY API ====================
    faculty: {
        list: (params = {}) => {
            const queryString = new URLSearchParams(params).toString();
            return API.fetch(`faculty.php?action=list&${queryString}`);
        },
        
        get: (id) => {
            return API.fetch(`faculty.php?action=get&id=${id}`);
        },
        
        add: (data) => {
            return API.fetch('faculty.php?action=add', {
                method: 'POST',
                body: JSON.stringify(data)
            });
        },
        
        update: (id, data) => {
            return API.fetch(`faculty.php?action=update&id=${id}`, {
                method: 'PUT',
                body: JSON.stringify(data)
            });
        },
        
        delete: (id) => {
            return API.fetch(`faculty.php?action=delete&id=${id}`, { method: 'DELETE' });
        }
    },
    
    // ==================== COURSES API ====================
    courses: {
        list: (params = {}) => {
            const queryString = new URLSearchParams(params).toString();
            return API.fetch(`courses.php?action=list&${queryString}`);
        },
        
        get: (id) => {
            return API.fetch(`courses.php?action=get&id=${id}`);
        },
        
        add: (data) => {
            return API.fetch('courses.php?action=add', {
                method: 'POST',
                body: JSON.stringify(data)
            });
        },
        
        update: (id, data) => {
            return API.fetch(`courses.php?action=update&id=${id}`, {
                method: 'PUT',
                body: JSON.stringify(data)
            });
        },
        
        delete: (id) => {
            return API.fetch(`courses.php?action=delete&id=${id}`, { method: 'DELETE' });
        },
        
        assignFaculty: (data) => {
            return API.fetch('courses.php?action=assign_faculty', {
                method: 'POST',
                body: JSON.stringify(data)
            });
        }
    },
    
    // ==================== ATTENDANCE API ====================
    attendance: {
        mark: (data) => {
            return API.fetch('attendance.php?action=mark', {
                method: 'POST',
                body: JSON.stringify(data)
            });
        },
        
        getStudent: (studentId, courseId = null) => {
            let url = `attendance.php?action=get_student&student_id=${studentId}`;
            if (courseId) url += `&course_id=${courseId}`;
            return API.fetch(url);
        },
        
        getCourse: (courseId, date = null) => {
            let url = `attendance.php?action=get_course&course_id=${courseId}`;
            if (date) url += `&date=${date}`;
            return API.fetch(url);
        },
        
        getStatistics: (studentId) => {
            return API.fetch(`attendance.php?action=get_statistics&student_id=${studentId}`);
        },
        
        getOverview: (department = null) => {
            let url = 'attendance.php?action=get_overview';
            if (department) url += `&department=${department}`;
            return API.fetch(url);
        },
        
        getStudentsByCourse: (courseId) => {
            return API.fetch(`attendance.php?action=get_students_by_course&course_id=${courseId}`);
        }
    },
    
    // ==================== LEAVES API ====================
    leaves: {
        apply: (data) => {
            return API.fetch('leaves.php?action=apply', {
                method: 'POST',
                body: JSON.stringify(data)
            });
        },
        
        getMyLeaves: () => {
            return API.fetch('leaves.php?action=get_my');
        },
        
        getPending: () => {
            return API.fetch('leaves.php?action=get_pending');
        },
        
        getAll: (params = {}) => {
            const queryString = new URLSearchParams(params).toString();
            return API.fetch(`leaves.php?action=get_all&${queryString}`);
        },
        
        approve: (leaveId, status, remarks = '') => {
            return API.fetch('leaves.php?action=approve', {
                method: 'POST',
                body: JSON.stringify({ leave_id: leaveId, status: status, remarks: remarks })
            });
        },
        
        statistics: () => {
            return API.fetch('leaves.php?action=statistics');
        }
    },
    
    // ==================== GRIEVANCES API ====================
    grievances: {
        submit: (data) => {
            return API.fetch('grievances.php?action=submit', {
                method: 'POST',
                body: JSON.stringify(data)
            });
        },
        
        getMy: () => {
            return API.fetch('grievances.php?action=get_my');
        },
        
        getAll: (params = {}) => {
            const queryString = new URLSearchParams(params).toString();
            return API.fetch(`grievances.php?action=get_all&${queryString}`);
        },
        
        resolve: (grievanceId, resolution) => {
            return API.fetch('grievances.php?action=resolve', {
                method: 'POST',
                body: JSON.stringify({ grievance_id: grievanceId, resolution: resolution })
            });
        },
        
        assign: (grievanceId, assignedTo) => {
            return API.fetch('grievances.php?action=assign', {
                method: 'POST',
                body: JSON.stringify({ grievance_id: grievanceId, assigned_to: assignedTo })
            });
        },
        
        statistics: () => {
            return API.fetch('grievances.php?action=statistics');
        }
    },
    
    // ==================== FEEDBACK API ====================
    feedback: {
        submit: (data) => {
            return API.fetch('feedback.php?action=submit', {
                method: 'POST',
                body: JSON.stringify(data)
            });
        },
        
        getMy: () => {
            return API.fetch('feedback.php?action=get_my');
        },
        
        getAll: (params = {}) => {
            const queryString = new URLSearchParams(params).toString();
            return API.fetch(`feedback.php?action=get_all&${queryString}`);
        },
        
        getForFaculty: () => {
            return API.fetch('feedback.php?action=get_for_faculty');
        },
        
        statistics: () => {
            return API.fetch('feedback.php?action=statistics');
        }
    },
    
    // ==================== EXAMS API ====================
    exams: {
        submitForm: (data) => {
            return API.fetch('exams.php?action=submit_form', {
                method: 'POST',
                body: JSON.stringify(data)
            });
        },
        
        getMyForms: () => {
            return API.fetch('exams.php?action=get_my_forms');
        },
        
        getPendingForms: () => {
            return API.fetch('exams.php?action=get_pending_forms');
        },
        
        verifyForm: (formId, status) => {
            return API.fetch('exams.php?action=verify_form', {
                method: 'POST',
                body: JSON.stringify({ form_id: formId, status: status })
            });
        },
        
        getSchedule: (semester = null) => {
            let url = 'exams.php?action=get_schedule';
            if (semester) url += `&semester=${semester}`;
            return API.fetch(url);
        },
        
        addSchedule: (data) => {
            return API.fetch('exams.php?action=add_schedule', {
                method: 'POST',
                body: JSON.stringify(data)
            });
        }
    },
    
    // ==================== RESULTS API ====================
    results: {
        getMy: (semester = null) => {
            let url = 'results.php?action=get_my';
            if (semester) url += `&semester=${semester}`;
            return API.fetch(url);
        },
        
        getStudent: (studentId, semester = null) => {
            let url = `results.php?action=get_student&student_id=${studentId}`;
            if (semester) url += `&semester=${semester}`;
            return API.fetch(url);
        },
        
        enterMarks: (data) => {
            return API.fetch('results.php?action=enter_marks', {
                method: 'POST',
                body: JSON.stringify(data)
            });
        },
        
        declare: (semester, resultDate) => {
            return API.fetch('results.php?action=declare', {
                method: 'POST',
                body: JSON.stringify({ semester: semester, result_date: resultDate })
            });
        }
    },
    
    // ==================== NOTICES API ====================
    notices: {
        getAll: (params = {}) => {
            const queryString = new URLSearchParams(params).toString();
            return API.fetch(`notices.php?action=get_all&${queryString}`);
        },
        
        getRecent: (limit = 5) => {
            return API.fetch(`notices.php?action=get_recent&limit=${limit}`);
        },
        
        add: (data) => {
            return API.fetch('notices.php?action=add', {
                method: 'POST',
                body: JSON.stringify(data)
            });
        },
        
        update: (id, data) => {
            return API.fetch(`notices.php?action=update&id=${id}`, {
                method: 'PUT',
                body: JSON.stringify(data)
            });
        },
        
        delete: (id) => {
            return API.fetch(`notices.php?action=delete&id=${id}`, { method: 'DELETE' });
        }
    },
    
    // ==================== REPORTS API ====================
    reports: {
        attendance: (params = {}) => {
            const queryString = new URLSearchParams(params).toString();
            return API.fetch(`reports.php?action=attendance&${queryString}`);
        },
        
        academic: (params = {}) => {
            const queryString = new URLSearchParams(params).toString();
            return API.fetch(`reports.php?action=academic&${queryString}`);
        },
        
        financial: (year = null) => {
            let url = 'reports.php?action=financial';
            if (year) url += `&year=${year}`;
            return API.fetch(url);
        },
        
        analytics: () => {
            return API.fetch('reports.php?action=analytics');
        }
    },
    
    // ==================== SETTINGS API ====================
    settings: {
        get: (key = null) => {
            let url = 'settings.php?action=get';
            if (key) url += `&key=${key}`;
            return API.fetch(url);
        },
        
        set: (key, value, type = 'text') => {
            return API.fetch('settings.php?action=set', {
                method: 'POST',
                body: JSON.stringify({ key: key, value: value, type: type })
            });
        },
        
        getAll: () => {
            return API.fetch('settings.php?action=get_all');
        },
        
        backup: (type = 'full') => {
            window.location.href = API.baseUrl + `settings.php?action=backup&type=${type}`;
        },
        
        getLogs: (level = null, limit = 100) => {
            let url = `settings.php?action=logs&limit=${limit}`;
            if (level) url += `&level=${level}`;
            return API.fetch(url);
        },
        
        clearLogs: () => {
            return API.fetch('settings.php?action=clear_logs', { method: 'POST' });
        }
    }
};