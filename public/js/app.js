// =============================================
// Employee Management System - Main JavaScript
// =============================================

// Initialize on DOM ready
document.addEventListener('DOMContentLoaded', function() {
    // Initialize theme
    initTheme();
    
    // Initialize sidebar
    initSidebar();
    
    // Initialize modals
    initModals();
    
    // Initialize tooltips
    initTooltips();
    
    // Initialize form validation
    initFormValidation();
    
    // Initialize data tables
    initDataTables();
    
    // Initialize charts
    initCharts();
});

// =============================================
// Theme Management
// =============================================
function initTheme() {
    const themeToggle = document.getElementById('theme-toggle');
    const savedTheme = localStorage.getItem('theme') || 'light';
    
    document.documentElement.setAttribute('data-theme', savedTheme);
    
    if (themeToggle) {
        themeToggle.addEventListener('click', function() {
            const currentTheme = document.documentElement.getAttribute('data-theme');
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            
            document.documentElement.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);
            
            // Update icon
            this.innerHTML = newTheme === 'dark' 
                ? '<i class="fas fa-sun"></i>' 
                : '<i class="fas fa-moon"></i>';
        });
        
        // Set initial icon
        themeToggle.innerHTML = savedTheme === 'dark' 
            ? '<i class="fas fa-sun"></i>' 
            : '<i class="fas fa-moon"></i>';
    }
}

// =============================================
// Sidebar Management
// =============================================
function initSidebar() {
    const menuToggle = document.getElementById('menu-toggle');
    const sidebar = document.getElementById('sidebar');
    const sidebarOverlay = document.getElementById('sidebar-overlay');
    
    if (menuToggle && sidebar) {
        menuToggle.addEventListener('click', function() {
            sidebar.classList.toggle('active');
            if (sidebarOverlay) {
                sidebarOverlay.classList.toggle('active');
            }
        });
        
        // Close sidebar on overlay click
        if (sidebarOverlay) {
            sidebarOverlay.addEventListener('click', function() {
                sidebar.classList.remove('active');
                this.classList.remove('active');
            });
        }
    }
    
    // Set active nav link
    const currentPath = window.location.pathname;
    const navLinks = document.querySelectorAll('.nav-link');
    
    navLinks.forEach(link => {
        const href = link.getAttribute('href');
        if (href && currentPath.includes(href)) {
            link.classList.add('active');
        }
    });
}

// =============================================
// Modal Management
// =============================================
function initModals() {
    const modalTriggers = document.querySelectorAll('[data-modal]');
    
    modalTriggers.forEach(trigger => {
        trigger.addEventListener('click', function(e) {
            e.preventDefault();
            const modalId = this.getAttribute('data-modal');
            openModal(modalId);
        });
    });
    
    // Close modal on close button click
    document.querySelectorAll('.modal-close').forEach(btn => {
        btn.addEventListener('click', function() {
            const modal = this.closest('.modal-overlay');
            if (modal) {
                closeModal(modal.id);
            }
        });
    });
    
    // Close modal on overlay click
    document.querySelectorAll('.modal-overlay').forEach(overlay => {
        overlay.addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal(this.id);
            }
        });
    });
    
    // Close modal on escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            document.querySelectorAll('.modal-overlay.active').forEach(modal => {
                closeModal(modal.id);
            });
        }
    });
}

function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
        
        // Focus first input
        setTimeout(() => {
            const input = modal.querySelector('input:not([type="hidden"]), select, textarea');
            if (input) input.focus();
        }, 100);
    }
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('active');
        document.body.style.overflow = '';
    }
}

// =============================================
// Tooltips
// =============================================
function initTooltips() {
    const tooltipTriggers = document.querySelectorAll('[data-tooltip]');
    
    tooltipTriggers.forEach(trigger => {
        trigger.addEventListener('mouseenter', function() {
            const text = this.getAttribute('data-tooltip');
            const tooltip = document.createElement('div');
            tooltip.className = 'tooltip';
            tooltip.textContent = text;
            tooltip.style.cssText = `
                position: absolute;
                background: #1e293b;
                color: #fff;
                padding: 0.5rem 0.75rem;
                border-radius: 0.375rem;
                font-size: 0.75rem;
                white-space: nowrap;
                z-index: 9999;
                pointer-events: none;
                animation: fadeIn 0.2s ease;
            `;
            
            document.body.appendChild(tooltip);
            
            const rect = this.getBoundingClientRect();
            tooltip.style.top = (rect.top - tooltip.offsetHeight - 8) + 'px';
            tooltip.style.left = (rect.left + (rect.width / 2) - (tooltip.offsetWidth / 2)) + 'px';
            
            this._tooltip = tooltip;
        });
        
        trigger.addEventListener('mouseleave', function() {
            if (this._tooltip) {
                this._tooltip.remove();
                delete this._tooltip;
            }
        });
    });
}

// =============================================
// Form Validation
// =============================================
function initFormValidation() {
    const forms = document.querySelectorAll('.ajax-form, .needs-validation');
    
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            if (form.classList.contains('needs-validation')) {
                e.preventDefault();
                if (!validateForm(form)) {
                    return false;
                }
            }
            
            // Handle ajax form submission
            if (form.classList.contains('ajax-form')) {
                e.preventDefault();
                submitForm(form);
            }
        });
    });
}

function validateForm(form) {
    let isValid = true;
    const inputs = form.querySelectorAll('[required]');
    
    inputs.forEach(input => {
        if (!input.value.trim()) {
            isValid = false;
            input.classList.add('is-invalid');
        } else {
            input.classList.remove('is-invalid');
        }
    });
    
    return isValid;
}

function submitForm(form) {
    const formData = new FormData(form);
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalBtnText = submitBtn ? submitBtn.innerHTML : '';
    
    // Show loading
    if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner spinner-sm"></span>';
    }
    
    // Add CSRF token
    formData.append('csrf_token', getCsrfToken());
    
    fetch(form.action, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert(data.message || 'Success!', 'success');
            
            // Reset form if specified
            if (data.reset) {
                form.reset();
            }
            
            // Reload page if specified
            if (data.reload) {
                setTimeout(() => location.reload(), 1500);
            }
            
            // Redirect if specified
            if (data.redirect) {
                setTimeout(() => window.location.href = data.redirect, 1500);
            }
            
            // Close modal if specified
            if (data.closeModal) {
                closeModal(data.closeModal);
            }
            
            // Custom callback
            if (data.callback) {
                window[data.callback](data);
            }
        } else {
            showAlert(data.error || 'An error occurred', 'danger');
        }
    })
    .catch(error => {
        console.error('Form submission error:', error);
        showAlert('An error occurred. Please try again.', 'danger');
    })
    .finally(() => {
        if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalBtnText;
        }
    });
}

// =============================================
// Data Tables
// =============================================
function initDataTables() {
    const tables = document.querySelectorAll('.data-table');
    
    tables.forEach(table => {
        // Add search functionality
        const searchInput = table.parentElement.querySelector('.table-search');
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                filterTable(table, this.value);
            });
        }
    });
}

function filterTable(table, searchTerm) {
    const rows = table.querySelectorAll('tbody tr');
    const term = searchTerm.toLowerCase();
    
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(term) ? '' : 'none';
    });
}

// =============================================
// Charts (using Chart.js)
// =============================================
function initCharts() {
    // Check if Chart.js is loaded
    if (typeof Chart === 'undefined') return;
    
    // Set default options
    Chart.defaults.font.family = "'Inter', sans-serif";
    Chart.defaults.color = '#64748b';
    
    // Initialize dashboard charts
    initAttendanceChart();
    initSalaryChart();
    initDepartmentChart();
}

function initAttendanceChart() {
    const canvas = document.getElementById('attendanceChart');
    if (!canvas) return;
    
    const ctx = canvas.getContext('2d');
    
    // Sample data - in production, this would come from the server
    const data = {
        labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
        datasets: [{
            label: 'Present',
            data: [45, 48, 47, 46, 44, 20, 0],
            backgroundColor: 'rgba(16, 185, 129, 0.8)',
            borderRadius: 6,
        }, {
            label: 'Absent',
            data: [3, 2, 3, 4, 6, 30, 50],
            backgroundColor: 'rgba(239, 68, 68, 0.8)',
            borderRadius: 6,
        }]
    };
    
    new Chart(ctx, {
        type: 'bar',
        data: data,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                }
            },
            scales: {
                x: {
                    grid: { display: false }
                },
                y: {
                    beginAtZero: true,
                    grid: { color: 'rgba(0,0,0,0.05)' }
                }
            }
        }
    });
}

function initSalaryChart() {
    const canvas = document.getElementById('salaryChart');
    if (!canvas) return;
    
    const ctx = canvas.getContext('2d');
    
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Paid', 'Pending'],
            datasets: [{
                data: [75, 25],
                backgroundColor: [
                    'rgba(16, 185, 129, 0.8)',
                    'rgba(245, 158, 11, 0.8)'
                ],
                borderWidth: 0,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                }
            },
            cutout: '70%',
        }
    });
}

function initDepartmentChart() {
    const canvas = document.getElementById('departmentChart');
    if (!canvas) return;
    
    const ctx = canvas.getContext('2d');
    
    new Chart(ctx, {
        type: 'pie',
        data: {
            labels: ['IT', 'HR', 'Finance', 'Marketing', 'Sales'],
            datasets: [{
                data: [25, 15, 20, 18, 22],
                backgroundColor: [
                    'rgba(79, 70, 229, 0.8)',
                    'rgba(16, 185, 129, 0.8)',
                    'rgba(245, 158, 11, 0.8)',
                    'rgba(6, 182, 212, 0.8)',
                    'rgba(239, 68, 68, 0.8)'
                ],
                borderWidth: 0,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'right',
                }
            }
        }
    });
}

// =============================================
// Utility Functions
// =============================================

// Show alert message
function showAlert(message, type = 'info') {
    const alert = document.createElement('div');
    alert.className = `alert alert-${type}`;
    alert.innerHTML = `
        <i class="fas fa-${getAlertIcon(type)}"></i>
        <span>${message}</span>
    `;
    
    const container = document.querySelector('.page-content') || document.body;
    container.insertBefore(alert, container.firstChild);
    
    // Auto dismiss after 5 seconds
    setTimeout(() => {
        alert.style.opacity = '0';
        setTimeout(() => alert.remove(), 300);
    }, 5000);
}

function getAlertIcon(type) {
    const icons = {
        success: 'check-circle',
        danger: 'exclamation-circle',
        warning: 'exclamation-triangle',
        info: 'info-circle'
    };
    return icons[type] || 'info-circle';
}

// Get CSRF token
function getCsrfToken() {
    const tokenInput = document.querySelector('input[name="csrf_token"]');
    return tokenInput ? tokenInput.value : '';
}

// AJAX request helper
function ajaxRequest(url, method = 'GET', data = null) {
    const options = {
        method: method,
        headers: {
            'Content-Type': 'application/json',
        }
    };
    
    if (data && method !== 'GET') {
        options.body = JSON.stringify(data);
    }
    
    return fetch(url, options)
        .then(response => response.json())
        .catch(error => {
            console.error('AJAX Error:', error);
            throw error;
        });
}

// Loading overlay
function showLoading() {
    let overlay = document.querySelector('.loading-overlay');
    if (!overlay) {
        overlay = document.createElement('div');
        overlay.className = 'loading-overlay';
        overlay.innerHTML = '<div class="spinner spinner-lg"></div>';
        document.body.appendChild(overlay);
    }
    overlay.classList.add('active');
}

function hideLoading() {
    const overlay = document.querySelector('.loading-overlay');
    if (overlay) {
        overlay.classList.remove('active');
    }
}

// Confirm dialog
function confirmAction(message, callback) {
    if (confirm(message)) {
        callback();
    }
}

// Format currency
function formatCurrency(amount) {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD'
    }).format(amount);
}

// Format date
function formatDate(dateString) {
    const options = { year: 'numeric', month: 'short', day: 'numeric' };
    return new Date(dateString).toLocaleDateString('en-US', options);
}

// Debounce function
function debounce(func, wait) {
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

// =============================================
// Page Specific Functions
// =============================================

// Attendance functions
function checkIn() {
    showLoading();
    
    const formData = new FormData();
    formData.append('action', 'check_in');
    formData.append('csrf_token', getCsrfToken());
    
    fetch('attendance.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        showAlert(data.message, data.success ? 'success' : 'danger');
        if (data.success) {
            setTimeout(() => location.reload(), 1500);
        }
    })
    .finally(() => hideLoading());
}

function checkOut() {
    showLoading();
    
    const formData = new FormData();
    formData.append('action', 'check_out');
    formData.append('csrf_token', getCsrfToken());
    
    fetch('attendance.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        showAlert(data.message, data.success ? 'success' : 'danger');
        if (data.success) {
            setTimeout(() => location.reload(), 1500);
        }
    })
    .finally(() => hideLoading());
}

// Leave request
function submitLeaveRequest(formId) {
    const form = document.getElementById(formId);
    if (!form) return;
    
    submitForm(form);
}

// Salary payment
function markSalaryPaid(salaryId) {
    confirmAction('Are you sure you want to mark this salary as paid?', () => {
        showLoading();
        
        const formData = new FormData();
        formData.append('action', 'mark_paid');
        formData.append('salary_id', salaryId);
        formData.append('csrf_token', getCsrfToken());
        
        fetch('salaries.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            showAlert(data.message, data.success ? 'success' : 'danger');
            if (data.success) {
                setTimeout(() => location.reload(), 1500);
            }
        })
        .finally(() => hideLoading());
    });
}

// Leave approval
function approveLeave(leaveId) {
    const formData = new FormData();
    formData.append('action', 'approve');
    formData.append('leave_id', leaveId);
    formData.append('csrf_token', getCsrfToken());
    
    fetch('leaves.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        showAlert(data.message, data.success ? 'success' : 'danger');
        if (data.success) {
            setTimeout(() => location.reload(), 1500);
        }
    });
}

function rejectLeave(leaveId) {
    const reason = prompt('Please enter rejection reason:');
    if (reason === null) return;
    
    const formData = new FormData();
    formData.append('action', 'reject');
    formData.append('leave_id', leaveId);
    formData.append('reason', reason);
    formData.append('csrf_token', getCsrfToken());
    
    fetch('leaves.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        showAlert(data.message, data.success ? 'success' : 'danger');
        if (data.success) {
            setTimeout(() => location.reload(), 1500);
        }
    });
}

// Employee management
function deleteEmployee(employeeId) {
    confirmAction('Are you sure you want to delete this employee? This action cannot be undone.', () => {
        const formData = new FormData();
        formData.append('action', 'delete');
        formData.append('employee_id', employeeId);
        formData.append('csrf_token', getCsrfToken());
        
        fetch('employees.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            showAlert(data.message, data.success ? 'success' : 'danger');
            if (data.success) {
                setTimeout(() => location.reload(), 1500);
            }
        });
    });
}

// Export to CSV
function exportToCSV(tableId, filename) {
    const table = document.getElementById(tableId);
    if (!table) return;
    
    let csv = [];
    const rows = table.querySelectorAll('tr');
    
    rows.forEach(row => {
        const cols = row.querySelectorAll('td, th');
        const rowData = [];
        cols.forEach(col => {
            rowData.push('"' + col.textContent.trim() + '"');
        });
        csv.push(rowData.join(','));
    });
    
    const csvFile = new Blob([csv.join('\n')], { type: 'text/csv' });
    const downloadLink = document.createElement('a');
    downloadLink.download = filename || 'export.csv';
    downloadLink.href = window.URL.createObjectURL(csvFile);
    downloadLink.style.display = 'none';
    document.body.appendChild(downloadLink);
    downloadLink.click();
    document.body.removeChild(downloadLink);
}

// Search functionality
const searchInput = document.getElementById('global-search');
if (searchInput) {
    searchInput.addEventListener('input', debounce(function() {
        const term = this.value.toLowerCase();
        const tables = document.querySelectorAll('.data-table tbody tr');
        
        tables.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(term) ? '' : 'none';
        });
    }, 300));
}

// Logout confirmation
const logoutLink = document.getElementById('logout-link');
if (logoutLink) {
    logoutLink.addEventListener('click', function(e) {
        e.preventDefault();
        if (confirm('Are you sure you want to logout?')) {
            window.location.href = this.getAttribute('href');
        }
    });
}
