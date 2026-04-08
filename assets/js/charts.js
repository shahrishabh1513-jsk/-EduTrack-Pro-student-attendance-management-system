/**
 * EduTrack Pro - Charts Module
 * Handles all chart initializations and updates
 */

const Charts = {
    // Store chart instances
    instances: {},
    
    // Chart colors
    colors: {
        primary: '#4361ee',
        secondary: '#4cc9f0',
        success: '#06d6a0',
        danger: '#f72585',
        warning: '#ffd166',
        dark: '#1e293b',
        gray: '#6c757d',
        purple: '#3f37c9',
        orange: '#f8961e',
        teal: '#00b4d8'
    },
    
    // Color palette for multiple datasets
    palette: [
        '#4361ee', '#4cc9f0', '#f72585', '#06d6a0', '#ffd166',
        '#3f37c9', '#f8961e', '#00b4d8', '#9c27b0', '#ff5722'
    ],
    
    // Initialize attendance trend chart
    attendanceTrend: function(canvasId, data = null) {
        const ctx = document.getElementById(canvasId);
        if (!ctx) return null;
        
        const defaultData = {
            labels: ['Week 1', 'Week 2', 'Week 3', 'Week 4', 'Week 5', 'Week 6'],
            datasets: [{
                label: 'Attendance %',
                data: [85, 87, 86, 89, 88, 90],
                borderColor: this.colors.primary,
                backgroundColor: `rgba(${this.hexToRgb(this.colors.primary)}, 0.1)`,
                fill: true,
                tension: 0.4,
                pointBackgroundColor: this.colors.primary,
                pointBorderColor: '#fff',
                pointRadius: 4,
                pointHoverRadius: 6
            }]
        };
        
        const chartData = data || defaultData;
        
        this.instances[canvasId] = new Chart(ctx, {
            type: 'line',
            data: chartData,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'top' },
                    tooltip: { mode: 'index', intersect: false }
                },
                scales: {
                    y: { beginAtZero: true, max: 100, title: { display: true, text: 'Percentage (%)' } },
                    x: { title: { display: true, text: 'Week' } }
                }
            }
        });
        
        return this.instances[canvasId];
    },
    
    // Initialize department distribution chart
    departmentDistribution: function(canvasId, data = null) {
        const ctx = document.getElementById(canvasId);
        if (!ctx) return null;
        
        const defaultData = {
            labels: ['Computer Science', 'Information Technology', 'Electronics', 'Mechanical', 'Civil'],
            datasets: [{
                data: [850, 620, 450, 380, 247],
                backgroundColor: this.palette,
                borderWidth: 0
            }]
        };
        
        const chartData = data || defaultData;
        
        this.instances[canvasId] = new Chart(ctx, {
            type: 'doughnut',
            data: chartData,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom' },
                    tooltip: { callbacks: { label: (tooltipItem) => `${tooltipItem.label}: ${tooltipItem.raw} students` } }
                },
                cutout: '65%'
            }
        });
        
        return this.instances[canvasId];
    },
    
    // Initialize performance chart
    performanceChart: function(canvasId, data = null) {
        const ctx = document.getElementById(canvasId);
        if (!ctx) return null;
        
        const defaultData = {
            labels: ['Sem 1', 'Sem 2', 'Sem 3', 'Sem 4', 'Sem 5', 'Sem 6'],
            datasets: [{
                label: 'Average CGPA',
                data: [7.8, 8.0, 8.5, 8.3, 8.2, 8.4],
                backgroundColor: this.colors.primary,
                borderRadius: 8,
                barPercentage: 0.7
            }]
        };
        
        const chartData = data || defaultData;
        
        this.instances[canvasId] = new Chart(ctx, {
            type: 'bar',
            data: chartData,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'top' },
                    tooltip: { callbacks: { label: (tooltipItem) => `CGPA: ${tooltipItem.raw}` } }
                },
                scales: {
                    y: { beginAtZero: true, max: 10, title: { display: true, text: 'CGPA' } },
                    x: { title: { display: true, text: 'Semester' } }
                }
            }
        });
        
        return this.instances[canvasId];
    },
    
    // Initialize grade distribution chart
    gradeDistribution: function(canvasId, data = null) {
        const ctx = document.getElementById(canvasId);
        if (!ctx) return null;
        
        const defaultData = {
            labels: ['A+', 'A', 'B+', 'B', 'C', 'D', 'F'],
            datasets: [{
                data: [15, 25, 30, 20, 5, 3, 2],
                backgroundColor: this.palette,
                borderWidth: 0
            }]
        };
        
        const chartData = data || defaultData;
        
        this.instances[canvasId] = new Chart(ctx, {
            type: 'pie',
            data: chartData,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom' },
                    tooltip: { callbacks: { label: (tooltipItem) => `${tooltipItem.label}: ${tooltipItem.raw}%` } }
                }
            }
        });
        
        return this.instances[canvasId];
    },
    
    // Initialize revenue chart
    revenueChart: function(canvasId, data = null) {
        const ctx = document.getElementById(canvasId);
        if (!ctx) return null;
        
        const defaultData = {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
            datasets: [{
                label: 'Revenue (₹ Lakhs)',
                data: [28, 32, 35, 38, 42, 45, 48, 50, 52, 55, 58, 60],
                borderColor: this.colors.success,
                backgroundColor: `rgba(${this.hexToRgb(this.colors.success)}, 0.1)`,
                fill: true,
                tension: 0.4
            }]
        };
        
        const chartData = data || defaultData;
        
        this.instances[canvasId] = new Chart(ctx, {
            type: 'line',
            data: chartData,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'top' },
                    tooltip: { callbacks: { label: (tooltipItem) => `₹${tooltipItem.raw} Lakhs` } }
                }
            }
        });
        
        return this.instances[canvasId];
    },
    
    // Initialize enrollment trend chart
    enrollmentTrend: function(canvasId, data = null) {
        const ctx = document.getElementById(canvasId);
        if (!ctx) return null;
        
        const defaultData = {
            labels: ['2020', '2021', '2022', '2023', '2024'],
            datasets: [{
                label: 'Total Students',
                data: [1850, 2100, 2350, 2600, 2850],
                borderColor: this.colors.secondary,
                backgroundColor: `rgba(${this.hexToRgb(this.colors.secondary)}, 0.1)`,
                fill: true,
                tension: 0.4
            }]
        };
        
        const chartData = data || defaultData;
        
        this.instances[canvasId] = new Chart(ctx, {
            type: 'line',
            data: chartData,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'top' },
                    tooltip: { callbacks: { label: (tooltipItem) => `${tooltipItem.raw} students` } }
                }
            }
        });
        
        return this.instances[canvasId];
    },
    
    // Initialize subject-wise performance chart
    subjectPerformance: function(canvasId, data = null) {
        const ctx = document.getElementById(canvasId);
        if (!ctx) return null;
        
        const defaultData = {
            labels: ['Data Structures', 'DBMS', 'Web Dev', 'OS', 'CN'],
            datasets: [{
                label: 'Average Marks',
                data: [85, 92, 78, 88, 84],
                backgroundColor: this.colors.primary,
                borderRadius: 8
            }]
        };
        
        const chartData = data || defaultData;
        
        this.instances[canvasId] = new Chart(ctx, {
            type: 'bar',
            data: chartData,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                indexAxis: 'y',
                plugins: {
                    legend: { position: 'top' }
                },
                scales: {
                    x: { beginAtZero: true, max: 100, title: { display: true, text: 'Average Marks' } }
                }
            }
        });
        
        return this.instances[canvasId];
    },
    
    // Initialize comparison chart
    comparisonChart: function(canvasId, data = null) {
        const ctx = document.getElementById(canvasId);
        if (!ctx) return null;
        
        const defaultData = {
            labels: ['Computer Science', 'IT', 'Electronics', 'Mechanical', 'Civil'],
            datasets: [
                { label: '2023', data: [820, 590, 430, 360, 230], backgroundColor: this.colors.primary },
                { label: '2024', data: [850, 620, 450, 380, 247], backgroundColor: this.colors.secondary }
            ]
        };
        
        const chartData = data || defaultData;
        
        this.instances[canvasId] = new Chart(ctx, {
            type: 'bar',
            data: chartData,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'top' } },
                scales: { y: { beginAtZero: true, title: { display: true, text: 'Number of Students' } } }
            }
        });
        
        return this.instances[canvasId];
    },
    
    // Update chart data
    updateChart: function(chartId, newData) {
        if (this.instances[chartId]) {
            this.instances[chartId].data = newData;
            this.instances[chartId].update();
        }
    },
    
    // Destroy chart
    destroyChart: function(chartId) {
        if (this.instances[chartId]) {
            this.instances[chartId].destroy();
            delete this.instances[chartId];
        }
    },
    
    // Destroy all charts
    destroyAllCharts: function() {
        Object.keys(this.instances).forEach(chartId => {
            this.destroyChart(chartId);
        });
    },
    
    // Hex to RGB conversion
    hexToRgb: function(hex) {
        const result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
        return result ? `${parseInt(result[1], 16)}, ${parseInt(result[2], 16)}, ${parseInt(result[3], 16)}` : '67, 97, 238';
    }
};