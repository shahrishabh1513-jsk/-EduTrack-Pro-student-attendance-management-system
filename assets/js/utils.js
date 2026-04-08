/**
 * EduTrack Pro - Utilities Module
 * Helper functions for common tasks
 */

const Utils = {
    // Format date
    formatDate: function(date, format = 'DD/MM/YYYY') {
        const d = new Date(date);
        const day = String(d.getDate()).padStart(2, '0');
        const month = String(d.getMonth() + 1).padStart(2, '0');
        const year = d.getFullYear();
        
        switch(format) {
            case 'DD/MM/YYYY':
                return `${day}/${month}/${year}`;
            case 'MM/DD/YYYY':
                return `${month}/${day}/${year}`;
            case 'YYYY-MM-DD':
                return `${year}-${month}-${day}`;
            default:
                return `${day}/${month}/${year}`;
        }
    },
    
    // Format time
    formatTime: function(time, format = '12h') {
        if (format === '12h') {
            let [hours, minutes] = time.split(':');
            const ampm = hours >= 12 ? 'PM' : 'AM';
            hours = hours % 12 || 12;
            return `${hours}:${minutes} ${ampm}`;
        }
        return time;
    },
    
    // Format currency
    formatCurrency: function(amount, currency = '₹') {
        return `${currency}${amount.toLocaleString('en-IN')}`;
    },
    
    // Format number with suffix (K, L, Cr)
    formatNumber: function(num) {
        if (num >= 10000000) return (num / 10000000).toFixed(1) + 'Cr';
        if (num >= 100000) return (num / 100000).toFixed(1) + 'L';
        if (num >= 1000) return (num / 1000).toFixed(1) + 'K';
        return num.toString();
    },
    
    // Calculate percentage
    calculatePercentage: function(part, total) {
        if (total === 0) return 0;
        return Math.round((part / total) * 100);
    },
    
    // Calculate grade based on marks
    calculateGrade: function(marks, maxMarks = 100) {
        const percentage = (marks / maxMarks) * 100;
        
        if (percentage >= 90) return { grade: 'A+', points: 10, status: 'Outstanding' };
        if (percentage >= 80) return { grade: 'A', points: 9, status: 'Excellent' };
        if (percentage >= 70) return { grade: 'B+', points: 8, status: 'Very Good' };
        if (percentage >= 60) return { grade: 'B', points: 7, status: 'Good' };
        if (percentage >= 50) return { grade: 'C+', points: 6, status: 'Satisfactory' };
        if (percentage >= 40) return { grade: 'C', points: 5, status: 'Pass' };
        return { grade: 'F', points: 0, status: 'Fail' };
    },
    
    // Calculate CGPA from grades
    calculateCGPA: function(grades) {
        if (!grades.length) return 0;
        const totalPoints = grades.reduce((sum, g) => sum + g.points, 0);
        return (totalPoints / grades.length).toFixed(2);
    },
    
    // Generate random ID
    generateId: function(prefix = '') {
        const timestamp = Date.now().toString(36);
        const random = Math.random().toString(36).substring(2, 8);
        return `${prefix}${timestamp}${random}`.toUpperCase();
    },
    
    // Debounce function
    debounce: function(func, delay = 300) {
        let timeout;
        return function(...args) {
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(this, args), delay);
        };
    },
    
    // Throttle function
    throttle: function(func, limit = 300) {
        let inThrottle;
        return function(...args) {
            if (!inThrottle) {
                func.apply(this, args);
                inThrottle = true;
                setTimeout(() => inThrottle = false, limit);
            }
        };
    },
    
    // Deep clone object
    deepClone: function(obj) {
        return JSON.parse(JSON.stringify(obj));
    },
    
    // Merge objects
    mergeObjects: function(target, ...sources) {
        return Object.assign({}, target, ...sources);
    },
    
    // Check if object is empty
    isEmpty: function(obj) {
        return Object.keys(obj).length === 0 && obj.constructor === Object;
    },
    
    // Get query parameter from URL
    getQueryParam: function(param) {
        const urlParams = new URLSearchParams(window.location.search);
        return urlParams.get(param);
    },
    
    // Set query parameter in URL
    setQueryParam: function(params) {
        const url = new URL(window.location.href);
        Object.keys(params).forEach(key => {
            if (params[key]) {
                url.searchParams.set(key, params[key]);
            } else {
                url.searchParams.delete(key);
            }
        });
        window.history.pushState({}, '', url);
    },
    
    // Scroll to element
    scrollToElement: function(elementId, offset = 0) {
        const element = document.getElementById(elementId);
        if (element) {
            const yOffset = offset;
            const y = element.getBoundingClientRect().top + window.pageYOffset + yOffset;
            window.scrollTo({ top: y, behavior: 'smooth' });
        }
    },
    
    // Download file
    downloadFile: function(content, filename, type = 'text/plain') {
        const blob = new Blob([content], { type: type });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = filename;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);
    },
    
    // Copy to clipboard
    copyToClipboard: async function(text) {
        try {
            await navigator.clipboard.writeText(text);
            Notifications.show('Copied to clipboard!', 'success');
            return true;
        } catch (error) {
            console.error('Copy failed:', error);
            Notifications.show('Failed to copy', 'error');
            return false;
        }
    },
    
    // Validate email
    validateEmail: function(email) {
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(email);
    },
    
    // Validate phone number (Indian)
    validatePhone: function(phone) {
        const re = /^[6-9]\d{9}$/;
        return re.test(phone);
    },
    
    // Validate roll number
    validateRollNumber: function(roll) {
        const re = /^[A-Z0-9]{4,10}$/i;
        return re.test(roll);
    },
    
    // Truncate text
    truncate: function(text, length = 100) {
        if (text.length <= length) return text;
        return text.substring(0, length) + '...';
    },
    
    // Capitalize first letter
    capitalize: function(str) {
        return str.charAt(0).toUpperCase() + str.slice(1).toLowerCase();
    },
    
    // Title case
    titleCase: function(str) {
        return str.toLowerCase().split(' ').map(word => 
            word.charAt(0).toUpperCase() + word.slice(1)
        ).join(' ');
    },
    
    // Slugify string
    slugify: function(str) {
        return str.toLowerCase()
            .replace(/[^\w\s-]/g, '')
            .replace(/\s+/g, '-')
            .replace(/--+/g, '-')
            .trim();
    },
    
    // Get file extension
    getFileExtension: function(filename) {
        return filename.slice((filename.lastIndexOf('.') - 1 >>> 0) + 2);
    },
    
    // Format file size
    formatFileSize: function(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    },
    
    // Get initials from name
    getInitials: function(name) {
        return name.split(' ').map(n => n[0]).join('').toUpperCase().substring(0, 2);
    },
    
    // Random color from string
    stringToColor: function(str) {
        let hash = 0;
        for (let i = 0; i < str.length; i++) {
            hash = str.charCodeAt(i) + ((hash << 5) - hash);
        }
        let color = '#';
        for (let i = 0; i < 3; i++) {
            const value = (hash >> (i * 8)) & 0xFF;
            color += ('00' + value.toString(16)).substr(-2);
        }
        return color;
    },
    
    // Sleep/delay
    sleep: function(ms) {
        return new Promise(resolve => setTimeout(resolve, ms));
    },
    
    // Retry function
    retry: async function(fn, retries = 3, delay = 1000) {
        try {
            return await fn();
        } catch (error) {
            if (retries === 0) throw error;
            await this.sleep(delay);
            return this.retry(fn, retries - 1, delay);
        }
    }
};