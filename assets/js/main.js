/**
 * EduTrack Pro - Main JavaScript
 * Core application initialization and global functions
 */

// Global app object
const EduTrack = {
    // Configuration
    config: {
        apiUrl: '/edutrack-pro/api/',
        siteName: 'EduTrack Pro',
        version: '1.0.0',
        debug: true
    },
    
    // State management
    state: {
        user: null,
        isLoggedIn: false,
        currentPage: '',
        sidebarCollapsed: false,
        notifications: []
    },
    
    // Initialize application
    init: function() {
        console.log('EduTrack Pro initialized');
        
        // Check authentication status
        this.checkAuth();
        
        // Initialize components
        this.initSidebar();
        this.initMobileMenu();
        this.initSearch();
        this.initDropdowns();
        this.initTooltips();
        
        // Load page specific scripts
        this.loadPageScripts();
        
        // Add event listeners
        this.addEventListeners();
    },
    
    // Check authentication
    checkAuth: async function() {
        try {
            const response = await fetch(this.config.apiUrl + 'auth.php?action=check_session');
            const data = await response.json();
            
            if (data.success && data.logged_in) {
                this.state.isLoggedIn = true;
                this.state.user = data.user;
                
                // Update UI for logged in user
                this.updateUserUI();
            } else {
                this.state.isLoggedIn = false;
                this.state.user = null;
                
                // Redirect to login if on protected page
                const protectedPages = ['dashboard', 'profile', 'attendance', 'academics', 'requests', 'exams', 'resources'];
                const currentPath = window.location.pathname;
                const isProtected = protectedPages.some(page => currentPath.includes(page));
                
                if (isProtected && !currentPath.includes('login')) {
                    window.location.href = '/edutrack-pro/login.php';
                }
            }
        } catch (error) {
            console.error('Auth check failed:', error);
            if (this.config.debug) console.log(error);
        }
    },
    
    // Update UI with user data
    updateUserUI: function() {
        if (!this.state.user) return;
        
        // Update user name elements
        const userNameElements = document.querySelectorAll('#userName, .user-name, .welcome-name');
        userNameElements.forEach(el => {
            if (el) el.textContent = this.state.user.name;
        });
        
        // Update user role elements
        const userRoleElements = document.querySelectorAll('#userRole, .user-role');
        userRoleElements.forEach(el => {
            if (el) el.textContent = this.getRoleDisplay(this.state.user.role);
        });
        
        // Update user avatar
        const avatarElements = document.querySelectorAll('.user-avatar');
        avatarElements.forEach(el => {
            if (el) {
                const initial = this.state.user.name.charAt(0).toUpperCase();
                el.textContent = initial;
                el.title = this.state.user.name;
            }
        });
        
        // Update profile image
        const profileImages = document.querySelectorAll('.profile-img');
        profileImages.forEach(el => {
            if (el) {
                el.src = `https://ui-avatars.com/api/?name=${encodeURIComponent(this.state.user.name)}&background=4361ee&color=fff`;
            }
        });
    },
    
    // Get role display name
    getRoleDisplay: function(role) {
        const roles = {
            'student': 'Student',
            'faculty': 'Faculty Member',
            'admin': 'Administrator'
        };
        return roles[role] || role;
    },
    
    // Initialize sidebar
    initSidebar: function() {
        const sidebar = document.getElementById('sidebar');
        const toggleBtn = document.getElementById('toggleSidebar');
        
        if (toggleBtn && sidebar) {
            toggleBtn.addEventListener('click', () => {
                sidebar.classList.toggle('collapsed');
                this.state.sidebarCollapsed = sidebar.classList.contains('collapsed');
                
                // Save state to localStorage
                localStorage.setItem('sidebarCollapsed', this.state.sidebarCollapsed);
            });
            
            // Load saved state
            const savedState = localStorage.getItem('sidebarCollapsed');
            if (savedState === 'true') {
                sidebar.classList.add('collapsed');
                this.state.sidebarCollapsed = true;
            }
        }
        
        // Initialize submenus
        const dropdownToggles = document.querySelectorAll('.dropdown-toggle');
        dropdownToggles.forEach(toggle => {
            toggle.addEventListener('click', (e) => {
                e.preventDefault();
                const submenuId = toggle.getAttribute('href')?.substring(1);
                const submenu = document.getElementById(submenuId);
                const icon = toggle.querySelector('.dropdown-icon');
                
                if (submenu) {
                    submenu.classList.toggle('show');
                    if (icon) {
                        icon.style.transform = submenu.classList.contains('show') ? 'rotate(90deg)' : 'rotate(0)';
                    }
                }
            });
        });
    },
    
    // Initialize mobile menu
    initMobileMenu: function() {
        const mobileMenuBtn = document.getElementById('mobileMenuBtn');
        const sidebar = document.getElementById('sidebar');
        
        if (mobileMenuBtn && sidebar) {
            mobileMenuBtn.addEventListener('click', () => {
                sidebar.classList.toggle('active');
            });
        }
        
        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', (e) => {
            if (window.innerWidth <= 768 && sidebar && sidebar.classList.contains('active')) {
                if (!sidebar.contains(e.target) && !mobileMenuBtn?.contains(e.target)) {
                    sidebar.classList.remove('active');
                }
            }
        });
    },
    
    // Initialize search functionality
    initSearch: function() {
        const searchInputs = document.querySelectorAll('.search-input, .header-search input');
        
        searchInputs.forEach(input => {
            input.addEventListener('keyup', (e) => {
                const searchTerm = e.target.value.toLowerCase();
                const searchableElements = document.querySelectorAll('[data-searchable]');
                
                searchableElements.forEach(el => {
                    const text = el.textContent.toLowerCase();
                    if (text.includes(searchTerm)) {
                        el.style.display = '';
                    } else {
                        el.style.display = 'none';
                    }
                });
            });
        });
    },
    
    // Initialize dropdowns
    initDropdowns: function() {
        const dropdowns = document.querySelectorAll('.dropdown');
        
        dropdowns.forEach(dropdown => {
            const trigger = dropdown.querySelector('.dropdown-trigger');
            const menu = dropdown.querySelector('.dropdown-menu');
            
            if (trigger && menu) {
                trigger.addEventListener('click', (e) => {
                    e.stopPropagation();
                    menu.classList.toggle('show');
                });
            }
        });
        
        // Close dropdowns when clicking outside
        document.addEventListener('click', () => {
            document.querySelectorAll('.dropdown-menu.show').forEach(menu => {
                menu.classList.remove('show');
            });
        });
    },
    
    // Initialize tooltips
    initTooltips: function() {
        const tooltips = document.querySelectorAll('[data-tooltip]');
        
        tooltips.forEach(el => {
            const tooltipText = el.getAttribute('data-tooltip');
            if (tooltipText) {
                const tooltip = document.createElement('span');
                tooltip.className = 'tooltip-text';
                tooltip.textContent = tooltipText;
                el.classList.add('tooltip');
                el.appendChild(tooltip);
            }
        });
    },
    
    // Load page specific scripts
    loadPageScripts: function() {
        const page = this.getCurrentPage();
        
        switch(page) {
            case 'dashboard':
                this.loadDashboardScripts();
                break;
            case 'attendance':
                this.loadAttendanceScripts();
                break;
            case 'profile':
                this.loadProfileScripts();
                break;
            case 'results':
                this.loadResultsScripts();
                break;
        }
    },
    
    // Get current page name
    getCurrentPage: function() {
        const path = window.location.pathname;
        const page = path.split('/').pop().replace('.php', '').replace('.html', '');
        return page;
    },
    
    // Load dashboard specific scripts
    loadDashboardScripts: function() {
        // Load charts if Chart.js is available
        if (typeof Chart !== 'undefined') {
            this.initDashboardCharts();
        }
        
        // Load statistics
        this.loadStatistics();
    },
    
    // Initialize dashboard charts
    initDashboardCharts: function() {
        const attendanceCtx = document.getElementById('attendanceChart');
        if (attendanceCtx) {
            new Chart(attendanceCtx, {
                type: 'line',
                data: {
                    labels: ['Week 1', 'Week 2', 'Week 3', 'Week 4'],
                    datasets: [{
                        label: 'Attendance %',
                        data: [85, 87, 86, 89],
                        borderColor: '#4361ee',
                        backgroundColor: 'rgba(67, 97, 238, 0.1)',
                        fill: true,
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'top' }
                    },
                    scales: {
                        y: { beginAtZero: true, max: 100 }
                    }
                }
            });
        }
    },
    
    // Load statistics
    loadStatistics: async function() {
        try {
            const response = await fetch(this.config.apiUrl + 'students.php?action=statistics');
            const data = await response.json();
            
            if (data.success) {
                this.updateStatsUI(data.data);
            }
        } catch (error) {
            console.error('Failed to load statistics:', error);
        }
    },
    
    // Update statistics UI
    updateStatsUI: function(stats) {
        const elements = {
            totalStudents: stats.overall?.total,
            maleStudents: stats.overall?.male,
            femaleStudents: stats.overall?.female,
            avgCgpa: stats.overall?.avg_cgpa
        };
        
        for (const [id, value] of Object.entries(elements)) {
            const el = document.getElementById(id);
            if (el && value) {
                el.textContent = value;
            }
        }
    },
    
    // Load attendance scripts
    loadAttendanceScripts: function() {
        // Initialize date picker
        const dateInput = document.getElementById('attendanceDate');
        if (dateInput) {
            dateInput.value = new Date().toISOString().split('T')[0];
        }
    },
    
    // Load profile scripts
    loadProfileScripts: function() {
        // Initialize edit profile functionality
        const editBtn = document.getElementById('editProfileBtn');
        if (editBtn) {
            editBtn.addEventListener('click', () => this.toggleEditMode());
        }
    },
    
    // Load results scripts
    loadResultsScripts: function() {
        // Initialize grade charts
        if (typeof Chart !== 'undefined') {
            const gradeCtx = document.getElementById('gradeChart');
            if (gradeCtx) {
                new Chart(gradeCtx, {
                    type: 'doughnut',
                    data: {
                        labels: ['A+', 'A', 'B+', 'B', 'C', 'D', 'F'],
                        datasets: [{
                            data: [15, 25, 30, 20, 5, 3, 2],
                            backgroundColor: ['#4cc9f0', '#4361ee', '#3f37c9', '#4895ef', '#f72585', '#ffc107', '#6c757d']
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: { legend: { position: 'bottom' } }
                    }
                });
            }
        }
    },
    
    // Toggle edit mode for profile
    toggleEditMode: function() {
        const personalCard = document.getElementById('personalInfoCard');
        const editBtn = document.getElementById('editProfileBtn');
        
        if (personalCard) {
            const isEditMode = personalCard.classList.contains('edit-mode');
            
            if (!isEditMode) {
                personalCard.classList.add('edit-mode');
                if (editBtn) editBtn.innerHTML = '<i class="fas fa-times"></i> Cancel Edit';
            } else {
                personalCard.classList.remove('edit-mode');
                if (editBtn) editBtn.innerHTML = '<i class="fas fa-edit"></i> Edit Profile';
            }
        }
    },
    
    // Add global event listeners
    addEventListeners: function() {
        // Handle window resize
        window.addEventListener('resize', () => {
            this.handleResize();
        });
        
        // Handle escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                this.closeAllModals();
            }
        });
    },
    
    // Handle window resize
    handleResize: function() {
        const sidebar = document.getElementById('sidebar');
        if (window.innerWidth > 768 && sidebar) {
            sidebar.classList.remove('active');
        }
    },
    
    // Close all modals
    closeAllModals: function() {
        document.querySelectorAll('.modal').forEach(modal => {
            modal.style.display = 'none';
        });
    },
    
    // Show loading indicator
    showLoading: function(containerId) {
        const container = document.getElementById(containerId);
        if (container) {
            const loadingHtml = `
                <div class="loading-state">
                    <div class="loading-spinner"></div>
                    <p>Loading...</p>
                </div>
            `;
            container.innerHTML = loadingHtml;
        }
    },
    
    // Hide loading indicator
    hideLoading: function(containerId) {
        const container = document.getElementById(containerId);
        if (container) {
            const loadingDiv = container.querySelector('.loading-state');
            if (loadingDiv) {
                loadingDiv.remove();
            }
        }
    }
};

// Initialize app when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    EduTrack.init();
});

// Export for use in other scripts
window.EduTrack = EduTrack;