/**
 * EduTrack Pro - Notifications Module
 * Handles toast notifications and alerts
 */

const Notifications = {
    // Container for notifications
    container: null,
    
    // Default options
    defaults: {
        duration: 3000,
        position: 'top-right',
        animation: 'slide',
        closeButton: true
    },
    
    // Initialize notification container
    init: function() {
        if (!this.container) {
            this.container = document.createElement('div');
            this.container.className = 'notification-container';
            this.container.style.cssText = `
                position: fixed;
                z-index: 9999;
                pointer-events: none;
            `;
            document.body.appendChild(this.container);
        }
        this.setPosition(this.defaults.position);
    },
    
    // Set notification position
    setPosition: function(position) {
        if (!this.container) this.init();
        
        const positions = {
            'top-right': { top: '20px', right: '20px', left: 'auto', bottom: 'auto' },
            'top-left': { top: '20px', left: '20px', right: 'auto', bottom: 'auto' },
            'bottom-right': { bottom: '20px', right: '20px', top: 'auto', left: 'auto' },
            'bottom-left': { bottom: '20px', left: '20px', top: 'auto', right: 'auto' },
            'top-center': { top: '20px', left: '50%', transform: 'translateX(-50%)', right: 'auto', bottom: 'auto' },
            'bottom-center': { bottom: '20px', left: '50%', transform: 'translateX(-50%)', top: 'auto', right: 'auto' }
        };
        
        const pos = positions[position] || positions['top-right'];
        Object.assign(this.container.style, pos);
    },
    
    // Show notification
    show: function(message, type = 'info', options = {}) {
        this.init();
        
        const settings = { ...this.defaults, ...options };
        
        // Create notification element
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        notification.style.cssText = `
            pointer-events: auto;
            margin-bottom: 10px;
            padding: 12px 20px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            gap: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            animation: slideInRight 0.3s ease;
            background: ${this.getBackgroundColor(type)};
            color: ${type === 'warning' ? '#333' : '#fff'};
            min-width: 280px;
            max-width: 400px;
        `;
        
        // Add icon
        const icon = this.getIcon(type);
        notification.innerHTML = `
            <i class="fas ${icon}" style="font-size: 1.1rem;"></i>
            <span style="flex: 1; font-size: 0.875rem;">${message}</span>
            ${settings.closeButton ? '<i class="fas fa-times" style="cursor: pointer; opacity: 0.7;"></i>' : ''}
        `;
        
        // Add close button functionality
        if (settings.closeButton) {
            const closeBtn = notification.querySelector('.fa-times');
            closeBtn.addEventListener('click', () => this.remove(notification));
        }
        
        // Add to container
        this.container.appendChild(notification);
        
        // Auto remove after duration
        if (settings.duration > 0) {
            setTimeout(() => this.remove(notification), settings.duration);
        }
        
        return notification;
    },
    
    // Remove notification
    remove: function(notification) {
        if (!notification || !notification.parentNode) return;
        notification.style.animation = 'slideOutRight 0.3s ease';
        setTimeout(() => {
            if (notification.parentNode) notification.parentNode.removeChild(notification);
        }, 300);
    },
    
    // Clear all notifications
    clearAll: function() {
        if (this.container) {
            this.container.innerHTML = '';
        }
    },
    
    // Get background color based on type
    getBackgroundColor: function(type) {
        const colors = {
            success: '#06d6a0',
            error: '#f72585',
            warning: '#ffd166',
            info: '#4361ee'
        };
        return colors[type] || colors.info;
    },
    
    // Get icon based on type
    getIcon: function(type) {
        const icons = {
            success: 'fa-check-circle',
            error: 'fa-exclamation-circle',
            warning: 'fa-exclamation-triangle',
            info: 'fa-info-circle'
        };
        return icons[type] || icons.info;
    },
    
    // Success notification
    success: function(message, options = {}) {
        return this.show(message, 'success', options);
    },
    
    // Error notification
    error: function(message, options = {}) {
        return this.show(message, 'error', options);
    },
    
    // Warning notification
    warning: function(message, options = {}) {
        return this.show(message, 'warning', options);
    },
    
    // Info notification
    info: function(message, options = {}) {
        return this.show(message, 'info', options);
    },
    
    // Loading notification
    loading: function(message = 'Loading...', options = {}) {
        const notification = this.show(message, 'info', { ...options, duration: 0 });
        const content = notification.querySelector('span');
        const spinner = document.createElement('i');
        spinner.className = 'fas fa-spinner fa-pulse';
        spinner.style.marginRight = '8px';
        content.insertBefore(spinner, content.firstChild);
        return notification;
    },
    
    // Update loading notification
    updateLoading: function(notification, message, type = 'success') {
        if (notification) {
            const spinner = notification.querySelector('.fa-spinner');
            if (spinner) spinner.remove();
            
            const icon = notification.querySelector('i:first-child');
            if (icon) {
                icon.className = `fas ${this.getIcon(type)}`;
            }
            
            const content = notification.querySelector('span');
            if (content) {
                content.innerHTML = message;
            }
            
            notification.style.background = this.getBackgroundColor(type);
            notification.style.color = type === 'warning' ? '#333' : '#fff';
            
            setTimeout(() => this.remove(notification), 2000);
        }
    },
    
    // Confirm dialog
    confirm: function(message, title = 'Confirm', options = {}) {
        return new Promise((resolve) => {
            const modal = document.createElement('div');
            modal.className = 'modal confirm-modal';
            modal.style.cssText = `
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0, 0, 0, 0.5);
                display: flex;
                align-items: center;
                justify-content: center;
                z-index: 10000;
            `;
            
            modal.innerHTML = `
                <div style="background: white; border-radius: 12px; padding: 24px; max-width: 400px; width: 90%;">
                    <h3 style="margin-bottom: 16px;">${title}</h3>
                    <p style="margin-bottom: 24px; color: #666;">${message}</p>
                    <div style="display: flex; gap: 12px; justify-content: flex-end;">
                        <button class="confirm-cancel" style="padding: 8px 16px; border: 1px solid #ddd; background: white; border-radius: 6px; cursor: pointer;">Cancel</button>
                        <button class="confirm-ok" style="padding: 8px 16px; background: #4361ee; color: white; border: none; border-radius: 6px; cursor: pointer;">OK</button>
                    </div>
                </div>
            `;
            
            document.body.appendChild(modal);
            
            modal.querySelector('.confirm-cancel').addEventListener('click', () => {
                modal.remove();
                resolve(false);
            });
            
            modal.querySelector('.confirm-ok').addEventListener('click', () => {
                modal.remove();
                resolve(true);
            });
        });
    },
    
    // Alert dialog
    alert: function(message, title = 'Alert') {
        return new Promise((resolve) => {
            const modal = document.createElement('div');
            modal.className = 'modal alert-modal';
            modal.style.cssText = `
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0, 0, 0, 0.5);
                display: flex;
                align-items: center;
                justify-content: center;
                z-index: 10000;
            `;
            
            modal.innerHTML = `
                <div style="background: white; border-radius: 12px; padding: 24px; max-width: 400px; width: 90%;">
                    <h3 style="margin-bottom: 16px;">${title}</h3>
                    <p style="margin-bottom: 24px; color: #666;">${message}</p>
                    <div style="display: flex; justify-content: flex-end;">
                        <button class="alert-ok" style="padding: 8px 16px; background: #4361ee; color: white; border: none; border-radius: 6px; cursor: pointer;">OK</button>
                    </div>
                </div>
            `;
            
            document.body.appendChild(modal);
            
            modal.querySelector('.alert-ok').addEventListener('click', () => {
                modal.remove();
                resolve(true);
            });
        });
    }
};

// Initialize notifications
document.addEventListener('DOMContentLoaded', () => {
    Notifications.init();
});

// Add animation styles
const notificationStyles = document.createElement('style');
notificationStyles.textContent = `
    @keyframes slideInRight {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    @keyframes slideOutRight {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(100%);
            opacity: 0;
        }
    }
    
    @keyframes slideInLeft {
        from {
            transform: translateX(-100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    @keyframes slideOutLeft {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(-100%);
            opacity: 0;
        }
    }
    
    @keyframes fadeIn {
        from {
            opacity: 0;
        }
        to {
            opacity: 1;
        }
    }
    
    @keyframes fadeOut {
        from {
            opacity: 1;
        }
        to {
            opacity: 0;
        }
    }
`;
document.head.appendChild(notificationStyles);