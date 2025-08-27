/**
 * Authentication JavaScript
 * 
 * Handles authentication-related functionality including login forms,
 * password validation, and security features.
 */

// Authentication Module
window.SPMS = window.SPMS || {};
SPMS.Auth = {
    
    // Configuration
    config: {
        passwordMinLength: 8,
        maxLoginAttempts: 5,
        lockoutTime: 900000, // 15 minutes in milliseconds
        sessionTimeout: 3600000, // 1 hour in milliseconds
        sessionWarningTime: 300000 // 5 minutes before timeout
    },
    
    // Initialize authentication features
    init: function() {
        this.initLoginForm();
        this.initPasswordToggle();
        this.initPasswordStrength();
        this.initSessionTimeout();
        this.initRememberMe();
        this.initFormValidation();
        
        console.log('SPMS Auth module initialized');
    },
    
    // Initialize login form
    initLoginForm: function() {
        const loginForm = document.getElementById('loginForm');
        if (!loginForm) return;
        
        loginForm.addEventListener('submit', this.handleLogin.bind(this));
        
        // Auto-focus username field
        const usernameField = loginForm.querySelector('#username');
        if (usernameField) {
            usernameField.focus();
        }
        
        // Handle Enter key navigation
        const inputs = loginForm.querySelectorAll('input');
        inputs.forEach((input, index) => {
            input.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    const nextInput = inputs[index + 1];
                    if (nextInput) {
                        nextInput.focus();
                    } else {
                        loginForm.querySelector('button[type="submit"]').click();
                    }
                }
            });
        });
    },
    
    // Handle login form submission
    handleLogin: function(e) {
        const form = e.target;
        const submitBtn = form.querySelector('button[type="submit"]');
        const username = form.querySelector('#username').value;
        const password = form.querySelector('#password').value;
        
        // Basic validation
        if (!username.trim() || !password.trim()) {
            this.showError('Please enter both username and password');
            return;
        }
        
        // Check if account is locked
        if (this.isAccountLocked(username)) {
            const remainingTime = this.getRemainingLockoutTime(username);
            this.showError(`Account is locked. Try again in ${Math.ceil(remainingTime / 60000)} minutes.`);
            e.preventDefault();
            return;
        }
        
        // Show loading state
        this.setLoadingState(submitBtn, true);
        
        // Let the form submit normally
        // The server will handle the actual authentication
    },
    
    // Initialize password toggle functionality
    initPasswordToggle: function() {
        const toggleButtons = document.querySelectorAll('[id$="togglePassword"]');
        
        toggleButtons.forEach(button => {
            button.addEventListener('click', function() {
                const targetId = this.id.replace('toggle', '').toLowerCase();
                const passwordField = document.getElementById(targetId);
                
                if (passwordField) {
                    const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
                    passwordField.setAttribute('type', type);
                    
                    const icon = this.querySelector('i');
                    if (icon) {
                        icon.classList.toggle('fa-eye');
                        icon.classList.toggle('fa-eye-slash');
                    }
                    
                    // Update button title
                    this.title = type === 'password' ? 'Show Password' : 'Hide Password';
                }
            });
        });
    },
    
    // Initialize password strength indicator
    initPasswordStrength: function() {
        const passwordFields = document.querySelectorAll('input[type="password"][data-strength]');
        
        passwordFields.forEach(field => {
            // Create strength indicator
            const strengthIndicator = document.createElement('div');
            strengthIndicator.className = 'password-strength mt-2';
            strengthIndicator.innerHTML = `
                <div class="strength-bar">
                    <div class="strength-fill"></div>
                </div>
                <div class="strength-text text-muted small"></div>
            `;
            
            field.parentNode.appendChild(strengthIndicator);
            
            // Add event listener
            field.addEventListener('input', () => {
                this.updatePasswordStrength(field, strengthIndicator);
            });
        });
    },
    
    // Update password strength indicator
    updatePasswordStrength: function(field, indicator) {
        const password = field.value;
        const strength = this.calculatePasswordStrength(password);
        
        const fill = indicator.querySelector('.strength-fill');
        const text = indicator.querySelector('.strength-text');
        
        // Update visual indicator
        fill.style.width = strength.percentage + '%';
        fill.className = `strength-fill bg-${strength.color}`;
        text.textContent = strength.text;
        text.className = `strength-text small text-${strength.color}`;
        
        // Update field validation state
        if (password.length > 0) {
            if (strength.score >= 3) {
                field.classList.remove('is-invalid');
                field.classList.add('is-valid');
            } else {
                field.classList.remove('is-valid');
                field.classList.add('is-invalid');
            }
        } else {
            field.classList.remove('is-valid', 'is-invalid');
        }
    },
    
    // Calculate password strength
    calculatePasswordStrength: function(password) {
        let score = 0;
        let feedback = [];
        
        if (password.length === 0) {
            return { score: 0, percentage: 0, color: 'secondary', text: '' };
        }
        
        // Length check
        if (password.length >= 8) score++;
        else feedback.push('At least 8 characters');
        
        // Uppercase check
        if (/[A-Z]/.test(password)) score++;
        else feedback.push('One uppercase letter');
        
        // Lowercase check
        if (/[a-z]/.test(password)) score++;
        else feedback.push('One lowercase letter');
        
        // Number check
        if (/\d/.test(password)) score++;
        else feedback.push('One number');
        
        // Special character check
        if (/[^A-Za-z0-9]/.test(password)) score++;
        else feedback.push('One special character');
        
        // Determine strength level
        const levels = [
            { score: 0, color: 'danger', text: 'Very Weak', percentage: 20 },
            { score: 1, color: 'danger', text: 'Weak', percentage: 40 },
            { score: 2, color: 'warning', text: 'Fair', percentage: 60 },
            { score: 3, color: 'info', text: 'Good', percentage: 80 },
            { score: 4, color: 'success', text: 'Strong', percentage: 100 },
            { score: 5, color: 'success', text: 'Very Strong', percentage: 100 }
        ];
        
        const level = levels[Math.min(score, 5)];
        
        if (feedback.length > 0 && score < 3) {
            level.text += ` (Need: ${feedback.slice(0, 2).join(', ')})`;
        }
        
        return { ...level, score };
    },
    
    // Initialize session timeout handling
    initSessionTimeout: function() {
        if (!document.body.classList.contains('auth-body')) {
            this.startSessionTimer();
        }
    },
    
    // Start session timeout timer
    startSessionTimer: function() {
        // Warning timer
        setTimeout(() => {
            this.showSessionWarning();
        }, this.config.sessionTimeout - this.config.sessionWarningTime);
        
        // Timeout timer
        setTimeout(() => {
            this.handleSessionTimeout();
        }, this.config.sessionTimeout);
    },
    
    // Show session timeout warning
    showSessionWarning: function() {
        const warningTime = Math.ceil(this.config.sessionWarningTime / 60000);
        
        if (confirm(`Your session will expire in ${warningTime} minutes. Do you want to extend it?`)) {
            // Extend session by making a keep-alive request
            fetch('/api/keep-alive', {
                method: 'POST',
                headers: {
                    'X-CSRF-Token': SPMS.config.csrfToken,
                    'Content-Type': 'application/json'
                }
            }).then(() => {
                this.startSessionTimer(); // Restart timer
            }).catch(() => {
                this.handleSessionTimeout();
            });
        }
    },
    
    // Handle session timeout
    handleSessionTimeout: function() {
        alert('Your session has expired. You will be redirected to the login page.');
        window.location.href = '/login';
    },
    
    // Initialize remember me functionality
    initRememberMe: function() {
        const rememberCheckbox = document.getElementById('remember_me');
        if (!rememberCheckbox) return;
        
        // Load saved preference
        const savedPreference = localStorage.getItem('rememberMePreference');
        if (savedPreference === 'true') {
            rememberCheckbox.checked = true;
        }
        
        // Save preference on change
        rememberCheckbox.addEventListener('change', function() {
            localStorage.setItem('rememberMePreference', this.checked);
        });
    },
    
    // Initialize form validation
    initFormValidation: function() {
        const forms = document.querySelectorAll('.needs-validation');
        
        forms.forEach(form => {
            form.addEventListener('submit', function(e) {
                if (!form.checkValidity()) {
                    e.preventDefault();
                    e.stopPropagation();
                }
                form.classList.add('was-validated');
            });
            
            // Real-time validation
            const inputs = form.querySelectorAll('input, select, textarea');
            inputs.forEach(input => {
                input.addEventListener('blur', function() {
                    this.classList.add('was-validated');
                });
                
                input.addEventListener('input', function() {
                    if (this.classList.contains('was-validated')) {
                        if (this.checkValidity()) {
                            this.classList.remove('is-invalid');
                            this.classList.add('is-valid');
                        } else {
                            this.classList.remove('is-valid');
                            this.classList.add('is-invalid');
                        }
                    }
                });
            });
        });
    },
    
    // Check if account is locked
    isAccountLocked: function(username) {
        const lockData = localStorage.getItem(`lockout_${username}`);
        if (!lockData) return false;
        
        const data = JSON.parse(lockData);
        const now = Date.now();
        
        if (data.attempts >= this.config.maxLoginAttempts) {
            if (now - data.lastAttempt < this.config.lockoutTime) {
                return true;
            } else {
                // Lockout expired, clear data
                localStorage.removeItem(`lockout_${username}`);
                return false;
            }
        }
        
        return false;
    },
    
    // Get remaining lockout time
    getRemainingLockoutTime: function(username) {
        const lockData = localStorage.getItem(`lockout_${username}`);
        if (!lockData) return 0;
        
        const data = JSON.parse(lockData);
        const elapsed = Date.now() - data.lastAttempt;
        return Math.max(0, this.config.lockoutTime - elapsed);
    },
    
    // Record failed login attempt
    recordFailedAttempt: function(username) {
        const key = `lockout_${username}`;
        const existing = localStorage.getItem(key);
        let data = existing ? JSON.parse(existing) : { attempts: 0, lastAttempt: 0 };
        
        data.attempts++;
        data.lastAttempt = Date.now();
        
        localStorage.setItem(key, JSON.stringify(data));
    },
    
    // Clear failed attempts
    clearFailedAttempts: function(username) {
        localStorage.removeItem(`lockout_${username}`);
    },
    
    // Set loading state for button
    setLoadingState: function(button, loading) {
        if (loading) {
            button.disabled = true;
            button.dataset.originalText = button.textContent;
            button.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Please wait...';
        } else {
            button.disabled = false;
            button.textContent = button.dataset.originalText || 'Submit';
        }
    },
    
    // Show error message
    showError: function(message) {
        // Remove existing alerts
        const existingAlerts = document.querySelectorAll('.auth-alert');
        existingAlerts.forEach(alert => alert.remove());
        
        // Create new alert
        const alert = document.createElement('div');
        alert.className = 'alert alert-danger alert-dismissible fade show auth-alert';
        alert.innerHTML = `
            <i class="fas fa-exclamation-triangle me-2"></i>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        // Insert alert
        const form = document.querySelector('.auth-content form');
        if (form) {
            form.insertBefore(alert, form.firstChild);
        }
    },
    
    // Show success message
    showSuccess: function(message) {
        // Remove existing alerts
        const existingAlerts = document.querySelectorAll('.auth-alert');
        existingAlerts.forEach(alert => alert.remove());
        
        // Create new alert
        const alert = document.createElement('div');
        alert.className = 'alert alert-success alert-dismissible fade show auth-alert';
        alert.innerHTML = `
            <i class="fas fa-check-circle me-2"></i>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        // Insert alert
        const form = document.querySelector('.auth-content form');
        if (form) {
            form.insertBefore(alert, form.firstChild);
        }
    },
    
    // Validate password match
    validatePasswordMatch: function(password, confirmPassword) {
        return password === confirmPassword;
    },
    
    // Generate secure password
    generateSecurePassword: function(length = 12) {
        const charset = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*';
        let password = '';
        
        for (let i = 0; i < length; i++) {
            password += charset.charAt(Math.floor(Math.random() * charset.length));
        }
        
        return password;
    }
};

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    SPMS.Auth.init();
});

// Handle login failures from server
window.addEventListener('load', function() {
    // Check for login failure in URL or flash messages
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('login') === 'failed') {
        const username = document.getElementById('username')?.value;
        if (username) {
            SPMS.Auth.recordFailedAttempt(username);
        }
    }
    
    // Check for login success
    if (urlParams.get('login') === 'success') {
        const username = document.getElementById('username')?.value;
        if (username) {
            SPMS.Auth.clearFailedAttempts(username);
        }
    }
});

// Add CSS for password strength indicator
const style = document.createElement('style');
style.textContent = `
    .password-strength {
        margin-top: 0.5rem;
    }
    
    .strength-bar {
        height: 4px;
        background-color: #e9ecef;
        border-radius: 2px;
        overflow: hidden;
        margin-bottom: 0.25rem;
    }
    
    .strength-fill {
        height: 100%;
        transition: width 0.3s ease, background-color 0.3s ease;
        border-radius: 2px;
    }
    
    .auth-alert {
        margin-bottom: 1.5rem;
    }
    
    .was-validated .form-control:valid {
        border-color: #198754;
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 8 8'%3e%3cpath fill='%23198754' d='m2.3 6.73.94-.94 1.44 1.44L7.4 4.5l.94.94L4.66 9.2z'/%3e%3c/svg%3e");
    }
    
    .was-validated .form-control:invalid {
        border-color: #dc3545;
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 12' width='12' height='12' fill='none' stroke='%23dc3545'%3e%3ccircle cx='6' cy='6' r='4.5'/%3e%3cpath d='m5.8 3.6.4.4.4-.4M5.8 8.4l.4-.4.4.4m-2-.4h4'/%3e%3c/svg%3e");
    }
`;
document.head.appendChild(style);

// Export for global use
window.SPMS = window.SPMS || {};
window.SPMS.Auth = SPMS.Auth;
