/**
 * EduTrack Pro - Authentication Module
 * Handles user authentication, login, logout, and session management
 */

const Auth = {
    // Login user
    login: async function(username, password, role) {
        try {
            const formData = new FormData();
            formData.append('action', 'login');
            formData.append('username', username);
            formData.append('password', password);
            
            const response = await fetch(EduTrack.config.apiUrl + 'auth.php', {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success) {
                // Store user data in session
                sessionStorage.setItem('eduTrackUser', JSON.stringify(data.user));
                localStorage.setItem('userRole', data.user.role);
                
                // Show success message
                Notifications.show('Login successful! Redirecting...', 'success');
                
                // Redirect based on role
                setTimeout(() => {
                    this.redirectToDashboard(data.user.role);
                }, 1000);
                
                return { success: true, data: data.user };
            } else {
                Notifications.show(data.message || 'Login failed', 'error');
                return { success: false, message: data.message };
            }
        } catch (error) {
            console.error('Login error:', error);
            Notifications.show('Network error. Please try again.', 'error');
            return { success: false, message: 'Network error' };
        }
    },
    
    // Logout user
    logout: async function() {
        try {
            const response = await fetch(EduTrack.config.apiUrl + 'auth.php?action=logout');
            const data = await response.json();
            
            if (data.success) {
                // Clear session storage
                sessionStorage.removeItem('eduTrackUser');
                localStorage.removeItem('userRole');
                localStorage.removeItem('sidebarCollapsed');
                
                // Show message and redirect
                Notifications.show('Logged out successfully!', 'success');
                
                setTimeout(() => {
                    window.location.href = '/edutrack-pro/login.php';
                }, 1000);
            }
        } catch (error) {
            console.error('Logout error:', error);
            // Force redirect even if API fails
            window.location.href = '/edutrack-pro/login.php';
        }
    },
    
    // Redirect to appropriate dashboard
    redirectToDashboard: function(role) {
        const dashboards = {
            'student': '/edutrack-pro/student/dashboard.php',
            'faculty': '/edutrack-pro/faculty/dashboard.php',
            'admin': '/edutrack-pro/admin/dashboard.php'
        };
        
        const url = dashboards[role] || '/edutrack-pro/login.php';
        window.location.href = url;
    },
    
    // Check if user is logged in
    isLoggedIn: function() {
        const user = sessionStorage.getItem('eduTrackUser');
        return user !== null;
    },
    
    // Get current user
    getCurrentUser: function() {
        const user = sessionStorage.getItem('eduTrackUser');
        return user ? JSON.parse(user) : null;
    },
    
    // Get user role
    getUserRole: function() {
        return localStorage.getItem('userRole') || '';
    },
    
    // Check if user has specific role
    hasRole: function(role) {
        const userRole = this.getUserRole();
        if (Array.isArray(role)) {
            return role.includes(userRole);
        }
        return userRole === role;
    },
    
    // Change password
    changePassword: async function(oldPassword, newPassword) {
        try {
            const response = await fetch(EduTrack.config.apiUrl + 'auth.php?action=change_password', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    old_password: oldPassword,
                    new_password: newPassword
                })
            });
            
            const data = await response.json();
            
            if (data.success) {
                Notifications.show('Password changed successfully!', 'success');
                return { success: true };
            } else {
                Notifications.show(data.message || 'Failed to change password', 'error');
                return { success: false, message: data.message };
            }
        } catch (error) {
            console.error('Password change error:', error);
            Notifications.show('Network error. Please try again.', 'error');
            return { success: false, message: 'Network error' };
        }
    },
    
    // Reset password (forgot password)
    resetPassword: async function(email) {
        try {
            const response = await fetch(EduTrack.config.apiUrl + 'auth.php?action=reset_password', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ email: email })
            });
            
            const data = await response.json();
            
            if (data.success) {
                Notifications.show('Password reset link sent to your email!', 'success');
                return { success: true };
            } else {
                Notifications.show(data.message || 'Email not found', 'error');
                return { success: false, message: data.message };
            }
        } catch (error) {
            console.error('Password reset error:', error);
            Notifications.show('Network error. Please try again.', 'error');
            return { success: false, message: 'Network error' };
        }
    },
    
    // Validate login form
    validateLoginForm: function(username, password) {
        if (!username || username.trim() === '') {
            Notifications.show('Please enter username or email', 'error');
            return false;
        }
        
        if (!password || password.trim() === '') {
            Notifications.show('Please enter password', 'error');
            return false;
        }
        
        return true;
    },
    
    // Auto-fill demo credentials
    fillDemoCredentials: function(role) {
        const credentials = {
            student: { username: 'jenish', password: 'password123' },
            faculty: { username: 'aakash', password: 'password123' },
            admin: { username: 'admin', password: 'admin123' }
        };
        
        const cred = credentials[role];
        if (cred) {
            const usernameInput = document.getElementById('username');
            const passwordInput = document.getElementById('password');
            
            if (usernameInput) usernameInput.value = cred.username;
            if (passwordInput) passwordInput.value = cred.password;
        }
    },
    
    // Initialize login page
    initLoginPage: function() {
        // Role selector
        const roleBtns = document.querySelectorAll('.role-btn');
        let selectedRole = 'student';
        
        roleBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                roleBtns.forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                selectedRole = btn.dataset.role;
                this.fillDemoCredentials(selectedRole);
            });
        });
        
        // Login form submission
        const loginForm = document.getElementById('loginForm');
        if (loginForm) {
            loginForm.addEventListener('submit', async (e) => {
                e.preventDefault();
                
                const username = document.getElementById('username').value;
                const password = document.getElementById('password').value;
                
                if (this.validateLoginForm(username, password)) {
                    await this.login(username, password, selectedRole);
                }
            });
        }
        
        // Fill default demo credentials
        this.fillDemoCredentials('student');
    }
};

// Initialize login page if on login page
if (window.location.pathname.includes('login.php')) {
    document.addEventListener('DOMContentLoaded', () => {
        Auth.initLoginPage();
    });
}