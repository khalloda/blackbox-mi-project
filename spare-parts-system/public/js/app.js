/**
 * Spare Parts Management System - Main JavaScript
 * 
 * This file contains the main JavaScript functionality for the application.
 */

// Global App Object
window.SPMS = window.SPMS || {};

// Application Configuration
SPMS.config = {
    baseUrl: window.location.origin,
    apiUrl: window.location.origin + '/api',
    language: document.documentElement.lang || 'en',
    direction: document.documentElement.dir || 'ltr',
    csrfToken: document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
};

// Utility Functions
SPMS.utils = {
    
    /**
     * Format currency
     */
    formatCurrency: function(amount, currency = 'AED') {
        const formatter = new Intl.NumberFormat(SPMS.config.language === 'ar' ? 'ar-AE' : 'en-US', {
            style: 'currency',
            currency: currency,
            minimumFractionDigits: 2
        });
        return formatter.format(amount);
    },
    
    /**
     * Format number
     */
    formatNumber: function(number, decimals = 2) {
        const formatter = new Intl.NumberFormat(SPMS.config.language === 'ar' ? 'ar-AE' : 'en-US', {
            minimumFractionDigits: decimals,
            maximumFractionDigits: decimals
        });
        return formatter.format(number);
    },
    
    /**
     * Format date
     */
    formatDate: function(date, options = {}) {
        const defaultOptions = {
            year: 'numeric',
            month: '2-digit',
            day: '2-digit'
        };
        const formatOptions = { ...defaultOptions, ...options };
        
        const formatter = new Intl.DateTimeFormat(
            SPMS.config.language === 'ar' ? 'ar-AE' : 'en-US',
            formatOptions
        );
        
        return formatter.format(new Date(date));
    },
    
    /**
     * Show loading state
     */
    showLoading: function(element, text = 'Loading...') {
        if (typeof element === 'string') {
            element = document.querySelector(element);
        }
        
        if (element) {
            element.disabled = true;
            element.classList.add('loading');
            
            const originalText = element.textContent;
            element.setAttribute('data-original-text', originalText);
            element.innerHTML = `<i class="fas fa-spinner fa-spin me-2"></i>${text}`;
        }
    },
    
    /**
     * Hide loading state
     */
    hideLoading: function(element) {
        if (typeof element === 'string') {
            element = document.querySelector(element);
        }
        
        if (element) {
            element.disabled = false;
            element.classList.remove('loading');
            
            const originalText = element.getAttribute('data-original-text');
            if (originalText) {
                element.textContent = originalText;
                element.removeAttribute('data-original-text');
            }
        }
    },
    
    /**
     * Show toast notification
     */
    showToast: function(message, type = 'info', duration = 5000) {
        // Create toast container if it doesn't exist
        let toastContainer = document.getElementById('toast-container');
        if (!toastContainer) {
            toastContainer = document.createElement('div');
            toastContainer.id = 'toast-container';
            toastContainer.className = 'toast-container position-fixed top-0 end-0 p-3';
            toastContainer.style.zIndex = '9999';
            document.body.appendChild(toastContainer);
        }
        
        // Create toast element
        const toastId = 'toast-' + Date.now();
        const toastHtml = `
            <div id="${toastId}" class="toast align-items-center text-white bg-${type === 'error' ? 'danger' : type} border-0" role="alert">
                <div class="d-flex">
                    <div class="toast-body">
                        <i class="fas fa-${this.getToastIcon(type)} me-2"></i>
                        ${message}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            </div>
        `;
        
        toastContainer.insertAdjacentHTML('beforeend', toastHtml);
        
        // Initialize and show toast
        const toastElement = document.getElementById(toastId);
        const toast = new bootstrap.Toast(toastElement, { delay: duration });
        toast.show();
        
        // Remove toast element after it's hidden
        toastElement.addEventListener('hidden.bs.toast', function() {
            toastElement.remove();
        });
    },
    
    /**
     * Get toast icon based on type
     */
    getToastIcon: function(type) {
        const icons = {
            success: 'check-circle',
            error: 'exclamation-triangle',
            warning: 'exclamation-triangle',
            info: 'info-circle'
        };
        return icons[type] || 'info-circle';
    },
    
    /**
     * Confirm dialog
     */
    confirm: function(message, title = 'Confirm', callback = null) {
        if (callback && typeof callback === 'function') {
            // Custom confirm dialog (you can implement a modal here)
            const result = window.confirm(title + '\n\n' + message);
            callback(result);
        } else {
            return window.confirm(title + '\n\n' + message);
        }
    },
    
    /**
     * Debounce function
     */
    debounce: function(func, wait, immediate) {
        let timeout;
        return function executedFunction() {
            const context = this;
            const args = arguments;
            const later = function() {
                timeout = null;
                if (!immediate) func.apply(context, args);
            };
            const callNow = immediate && !timeout;
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
            if (callNow) func.apply(context, args);
        };
    }
};

// AJAX Helper
SPMS.ajax = {
    
    /**
     * Make AJAX request
     */
    request: function(options) {
        const defaults = {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin'
        };
        
        // Add CSRF token for non-GET requests
        if (options.method && options.method.toUpperCase() !== 'GET' && SPMS.config.csrfToken) {
            defaults.headers['X-CSRF-Token'] = SPMS.config.csrfToken;
        }
        
        const config = { ...defaults, ...options };
        
        return fetch(options.url, config)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .catch(error => {
                console.error('AJAX Error:', error);
                throw error;
            });
    },
    
    /**
     * GET request
     */
    get: function(url, params = {}) {
        const urlParams = new URLSearchParams(params);
        const fullUrl = url + (Object.keys(params).length ? '?' + urlParams : '');
        
        return this.request({
            url: fullUrl,
            method: 'GET'
        });
    },
    
    /**
     * POST request
     */
    post: function(url, data = {}) {
        return this.request({
            url: url,
            method: 'POST',
            body: JSON.stringify(data)
        });
    },
    
    /**
     * PUT request
     */
    put: function(url, data = {}) {
        return this.request({
            url: url,
            method: 'PUT',
            body: JSON.stringify(data)
        });
    },
    
    /**
     * DELETE request
     */
    delete: function(url) {
        return this.request({
            url: url,
            method: 'DELETE'
        });
    }
};

// Form Helper
SPMS.forms = {
    
    /**
     * Serialize form data
     */
    serialize: function(form) {
        if (typeof form === 'string') {
            form = document.querySelector(form);
        }
        
        const formData = new FormData(form);
        const data = {};
        
        for (let [key, value] of formData.entries()) {
            if (data[key]) {
                if (Array.isArray(data[key])) {
                    data[key].push(value);
                } else {
                    data[key] = [data[key], value];
                }
            } else {
                data[key] = value;
            }
        }
        
        return data;
    },
    
    /**
     * Validate form
     */
    validate: function(form) {
        if (typeof form === 'string') {
            form = document.querySelector(form);
        }
        
        const inputs = form.querySelectorAll('input[required], select[required], textarea[required]');
        let isValid = true;
        
        inputs.forEach(input => {
            if (!input.value.trim()) {
                input.classList.add('is-invalid');
                isValid = false;
            } else {
                input.classList.remove('is-invalid');
            }
        });
        
        return isValid;
    },
    
    /**
     * Clear form validation
     */
    clearValidation: function(form) {
        if (typeof form === 'string') {
            form = document.querySelector(form);
        }
        
        const inputs = form.querySelectorAll('.is-invalid, .is-valid');
        inputs.forEach(input => {
            input.classList.remove('is-invalid', 'is-valid');
        });
        
        const feedback = form.querySelectorAll('.invalid-feedback, .valid-feedback');
        feedback.forEach(element => {
            element.remove();
        });
    }
};

// Table Helper
SPMS.tables = {
    
    /**
     * Initialize sortable table
     */
    initSortable: function(tableSelector) {
        const table = document.querySelector(tableSelector);
        if (!table) return;
        
        const headers = table.querySelectorAll('th[data-sortable]');
        
        headers.forEach(header => {
            header.style.cursor = 'pointer';
            header.innerHTML += ' <i class="fas fa-sort text-muted"></i>';
            
            header.addEventListener('click', function() {
                const column = this.dataset.sortable;
                const currentOrder = this.dataset.order || 'asc';
                const newOrder = currentOrder === 'asc' ? 'desc' : 'asc';
                
                // Reset all headers
                headers.forEach(h => {
                    h.dataset.order = '';
                    const icon = h.querySelector('i');
                    if (icon) {
                        icon.className = 'fas fa-sort text-muted';
                    }
                });
                
                // Set current header
                this.dataset.order = newOrder;
                const icon = this.querySelector('i');
                if (icon) {
                    icon.className = `fas fa-sort-${newOrder === 'asc' ? 'up' : 'down'} text-primary`;
                }
                
                // Trigger sort event
                table.dispatchEvent(new CustomEvent('sort', {
                    detail: { column, order: newOrder }
                }));
            });
        });
    },
    
    /**
     * Filter table rows
     */
    filter: function(tableSelector, searchTerm) {
        const table = document.querySelector(tableSelector);
        if (!table) return;
        
        const rows = table.querySelectorAll('tbody tr');
        const term = searchTerm.toLowerCase();
        
        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            if (text.includes(term)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }
};

// Initialize Application
document.addEventListener('DOMContentLoaded', function() {
    
    // Initialize tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Initialize popovers
    const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    popoverTriggerList.map(function(popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });
    
    // Auto-hide alerts after 5 seconds
    const alerts = document.querySelectorAll('.alert:not(.alert-permanent)');
    alerts.forEach(alert => {
        setTimeout(() => {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }, 5000);
    });
    
    // Confirm delete actions
    document.addEventListener('click', function(e) {
        if (e.target.matches('[data-confirm]') || e.target.closest('[data-confirm]')) {
            const element = e.target.matches('[data-confirm]') ? e.target : e.target.closest('[data-confirm]');
            const message = element.dataset.confirm || 'Are you sure?';
            
            if (!confirm(message)) {
                e.preventDefault();
                return false;
            }
        }
    });
    
    // Auto-submit forms on change (for filters, etc.)
    document.addEventListener('change', function(e) {
        if (e.target.matches('[data-auto-submit]')) {
            const form = e.target.closest('form');
            if (form) {
                form.submit();
            }
        }
    });
    
    // Search functionality
    const searchInputs = document.querySelectorAll('[data-search-target]');
    searchInputs.forEach(input => {
        const targetSelector = input.dataset.searchTarget;
        const debouncedSearch = SPMS.utils.debounce(function() {
            SPMS.tables.filter(targetSelector, input.value);
        }, 300);
        
        input.addEventListener('input', debouncedSearch);
    });
    
    // Initialize sortable tables
    const sortableTables = document.querySelectorAll('[data-sortable-table]');
    sortableTables.forEach(table => {
        SPMS.tables.initSortable('#' + table.id);
    });
    
    // Number formatting
    const numberInputs = document.querySelectorAll('input[type="number"][data-format]');
    numberInputs.forEach(input => {
        input.addEventListener('blur', function() {
            if (this.value) {
                const formatted = SPMS.utils.formatNumber(parseFloat(this.value));
                this.setAttribute('data-formatted', formatted);
            }
        });
    });
    
    // Currency formatting
    const currencyInputs = document.querySelectorAll('input[data-currency]');
    currencyInputs.forEach(input => {
        input.addEventListener('blur', function() {
            if (this.value) {
                const formatted = SPMS.utils.formatCurrency(parseFloat(this.value));
                this.setAttribute('data-formatted', formatted);
            }
        });
    });
    
    console.log('SPMS Application initialized');
});

// Export for global use
window.SPMS = SPMS;
