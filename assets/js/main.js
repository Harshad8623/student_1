// Main JavaScript for Student Management System

class StudentManagementSystem {
    constructor() {
        this.init();
    }

    init() {
        console.log('🚀 Student Management System Initialized');
        
        // Initialize all components
        this.initNavigation();
        this.initModals();
        this.initNotifications();
        this.initSidebar();
        this.initTheme();
        this.initAjax();
        this.initPrint();
        this.initExport();
    }

    // Navigation handling
    initNavigation() {
        // Active link highlighting
        this.highlightActiveNav();
        
        // Back to top button
        this.initBackToTop();
        
        // Smooth scrolling
        this.initSmoothScroll();
    }

    highlightActiveNav() {
        const currentPage = window.location.pathname.split('/').pop();
        const navLinks = document.querySelectorAll('.nav-links a');
        
        navLinks.forEach(link => {
            const linkPage = link.getAttribute('href');
            if (linkPage === currentPage || 
                (currentPage === '' && linkPage === 'index.html')) {
                link.classList.add('active');
            } else {
                link.classList.remove('active');
            }
        });
    }

    initBackToTop() {
        const backToTop = document.createElement('button');
        backToTop.innerHTML = '↑';
        backToTop.className = 'back-to-top';
        backToTop.style.cssText = `
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            font-size: 1.2rem;
            cursor: pointer;
            display: none;
            z-index: 1000;
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
        `;
        
        document.body.appendChild(backToTop);

        backToTop.addEventListener('click', () => {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });

        window.addEventListener('scroll', () => {
            backToTop.style.display = window.scrollY > 300 ? 'block' : 'none';
        });
    }

    initSmoothScroll() {
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({ behavior: 'smooth' });
                }
            });
        });
    }

    // Modal handling
    initModals() {
        // Close modals on outside click
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('modal')) {
                this.closeModal(e.target);
            }
        });

        // Close modals on ESC key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                this.closeAllModals();
            }
        });

        // Modal triggers
        document.addEventListener('click', (e) => {
            if (e.target.dataset.modalTarget) {
                this.openModal(e.target.dataset.modalTarget);
            }
            if (e.target.classList.contains('modal-close')) {
                this.closeModal(e.target.closest('.modal'));
            }
        });
    }

    openModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.style.display = 'block';
            document.body.style.overflow = 'hidden';
        }
    }

    closeModal(modal) {
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
    }

    closeAllModals() {
        document.querySelectorAll('.modal').forEach(modal => {
            this.closeModal(modal);
        });
    }

    // Notification system
    initNotifications() {
        this.notificationContainer = document.createElement('div');
        this.notificationContainer.className = 'notification-container';
        this.notificationContainer.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
        `;
        document.body.appendChild(this.notificationContainer);
    }

    showNotification(message, type = 'info', duration = 5000) {
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        notification.style.cssText = `
            background: ${this.getNotificationColor(type)};
            color: white;
            padding: 15px 20px;
            margin-bottom: 10px;
            border-radius: 5px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
            animation: slideInRight 0.3s ease;
        `;
        
        notification.innerHTML = `
            <div style="display: flex; align-items: center; justify-content: space-between;">
                <span>${message}</span>
                <button class="notification-close" style="background: none; border: none; color: white; cursor: pointer;">
                    ×
                </button>
            </div>
        `;

        this.notificationContainer.appendChild(notification);

        // Close button
        notification.querySelector('.notification-close').addEventListener('click', () => {
            this.removeNotification(notification);
        });

        // Auto remove
        if (duration > 0) {
            setTimeout(() => {
                this.removeNotification(notification);
            }, duration);
        }

        return notification;
    }

    removeNotification(notification) {
        notification.style.animation = 'slideOutRight 0.3s ease';
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 300);
    }

    getNotificationColor(type) {
        const colors = {
            success: '#28a745',
            error: '#dc3545',
            warning: '#ffc107',
            info: '#17a2b8'
        };
        return colors[type] || colors.info;
    }

    // Sidebar functionality
    initSidebar() {
        // Mobile sidebar toggle
        this.initMobileSidebar();
        
        // Sidebar collapse/expand
        this.initSidebarCollapse();
    }

    initMobileSidebar() {
        const sidebarToggle = document.createElement('button');
        sidebarToggle.innerHTML = '☰';
        sidebarToggle.className = 'sidebar-toggle';
        sidebarToggle.style.cssText = `
            position: fixed;
            top: 20px;
            left: 20px;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 5px;
            padding: 10px 15px;
            cursor: pointer;
            display: none;
            z-index: 1001;
        `;
        
        document.body.appendChild(sidebarToggle);

        sidebarToggle.addEventListener('click', () => {
            document.querySelector('.sidebar').classList.toggle('mobile-open');
        });

        // Hide on desktop, show on mobile
        this.checkSidebarVisibility();
        window.addEventListener('resize', () => this.checkSidebarVisibility());
    }

    checkSidebarVisibility() {
        const sidebarToggle = document.querySelector('.sidebar-toggle');
        if (window.innerWidth <= 768) {
            sidebarToggle.style.display = 'block';
        } else {
            sidebarToggle.style.display = 'none';
            document.querySelector('.sidebar').classList.remove('mobile-open');
        }
    }

    initSidebarCollapse() {
        const sidebar = document.querySelector('.sidebar');
        if (!sidebar) return;

        const collapseBtn = document.createElement('button');
        collapseBtn.innerHTML = '‹';
        collapseBtn.className = 'sidebar-collapse';
        collapseBtn.style.cssText = `
            position: absolute;
            top: 20px;
            right: -15px;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            cursor: pointer;
            z-index: 1000;
        `;

        sidebar.appendChild(collapseBtn);

        collapseBtn.addEventListener('click', () => {
            sidebar.classList.toggle('collapsed');
            collapseBtn.innerHTML = sidebar.classList.contains('collapsed') ? '›' : '‹';
        });
    }

    // Theme handling
    initTheme() {
        // Dark mode toggle
        this.initDarkMode();
        
        // Theme preference saving
        this.loadThemePreference();
    }

    initDarkMode() {
        const themeToggle = document.createElement('button');
        themeToggle.innerHTML = '🌙';
        themeToggle.className = 'theme-toggle';
        themeToggle.style.cssText = `
            position: fixed;
            bottom: 80px;
            right: 20px;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            cursor: pointer;
            z-index: 1000;
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
        `;
        
        document.body.appendChild(themeToggle);

        themeToggle.addEventListener('click', () => {
            document.body.classList.toggle('dark-mode');
            const isDark = document.body.classList.contains('dark-mode');
            themeToggle.innerHTML = isDark ? '☀️' : '🌙';
            this.saveThemePreference(isDark);
        });
    }

    saveThemePreference(isDark) {
        localStorage.setItem('sms-theme', isDark ? 'dark' : 'light');
    }

    loadThemePreference() {
        const savedTheme = localStorage.getItem('sms-theme');
        if (savedTheme === 'dark') {
            document.body.classList.add('dark-mode');
            document.querySelector('.theme-toggle').innerHTML = '☀️';
        }
    }

    // AJAX functionality
    initAjax() {
        // Global AJAX setup
        this.setupAjax();
    }

    setupAjax() {
        // Add CSRF token to all AJAX requests
        const token = this.getCSRFToken();
        if (token) {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': token
                }
            });
        }
    }

    getCSRFToken() {
        return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    }

    // Print functionality
    initPrint() {
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('print-btn')) {
                this.printPage();
            }
        });
    }

    printPage() {
        window.print();
    }

    // Export functionality
    initExport() {
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('export-btn')) {
                const format = e.target.dataset.format || 'csv';
                this.exportTable(format);
            }
        });
    }

    exportTable(format) {
        const table = document.querySelector('table');
        if (!table) return;

        let data = '';
        const rows = table.querySelectorAll('tr');

        if (format === 'csv') {
            rows.forEach(row => {
                const cells = row.querySelectorAll('th, td');
                const rowData = Array.from(cells).map(cell => 
                    `"${cell.textContent.trim().replace(/"/g, '""')}"`
                ).join(',');
                data += rowData + '\n';
            });
        }

        this.downloadFile(data, `export.${format}`, 'text/csv');
    }

    downloadFile(content, filename, contentType) {
        const blob = new Blob([content], { type: contentType });
        const url = URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = url;
        link.download = filename;
        link.click();
        URL.revokeObjectURL(url);
    }

    // Utility methods
    formatDate(date) {
        return new Date(date).toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric'
        });
    }

    formatNumber(number) {
        return new Intl.NumberFormat('en-US').format(number);
    }

    debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    // Error handling
    handleError(error, userMessage = 'An error occurred') {
        console.error('SMS Error:', error);
        this.showNotification(userMessage, 'error');
    }
}

// Initialize the system
document.addEventListener('DOMContentLoaded', function() {
    window.SMS = new StudentManagementSystem();
});

// Global utility functions
const SMSUtils = {
    // Date utilities
    getCurrentAcademicYear() {
        const now = new Date();
        const year = now.getFullYear();
        return now.getMonth() >= 6 ? `${year}-${year + 1}` : `${year - 1}-${year}`;
    },

    // Validation utilities
    isValidEmail(email) {
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(email);
    },

    isValidPhone(phone) {
        const re = /^[\+]?[1-9][\d]{0,15}$/;
        return re.test(phone.replace(/\D/g, ''));
    },

    // Storage utilities
    setStorage(key, value) {
        try {
            localStorage.setItem(`sms-${key}`, JSON.stringify(value));
        } catch (e) {
            console.warn('LocalStorage not available');
        }
    },

    getStorage(key, defaultValue = null) {
        try {
            const item = localStorage.getItem(`sms-${key}`);
            return item ? JSON.parse(item) : defaultValue;
        } catch (e) {
            return defaultValue;
        }
    },

    // API utilities
    async apiCall(endpoint, options = {}) {
        try {
            const response = await fetch(endpoint, {
                headers: {
                    'Content-Type': 'application/json',
                    ...options.headers
                },
                ...options
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            return await response.json();
        } catch (error) {
            console.error('API call failed:', error);
            throw error;
        }
    }
};

// Add CSS animations
const style = document.createElement('style');
style.textContent = `
    @keyframes slideInRight {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
    
    @keyframes slideOutRight {
        from { transform: translateX(0); opacity: 1; }
        to { transform: translateX(100%); opacity: 0; }
    }
    
    .sidebar.collapsed {
        width: 70px;
    }
    
    .sidebar.collapsed .nav-links span {
        display: none;
    }
    
    .sidebar.mobile-open {
        transform: translateX(0);
    }
    
    @media (max-width: 768px) {
        .sidebar {
            transform: translateX(-100%);
            transition: transform 0.3s ease;
        }
    }
    
    .dark-mode {
        --bg-color: #1a1a1a;
        --text-color: #ffffff;
        --card-bg: #2d2d2d;
        --border-color: #404040;
    }
`;
document.head.appendChild(style);