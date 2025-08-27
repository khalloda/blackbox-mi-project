<div class="login-form">
    <div class="card shadow-lg border-0">
        <div class="card-body p-5">
            
            <h2 class="card-title text-center mb-4">
                <i class="fas fa-sign-in-alt me-2"></i>
                <?= __('auth.login') ?>
            </h2>
            
            <form method="POST" action="/login" id="loginForm">
                <?= \App\Core\CSRF::field() ?>
                
                <!-- Username Field -->
                <div class="mb-3">
                    <label for="username" class="form-label">
                        <i class="fas fa-user me-1"></i>
                        <?= __('auth.username') ?>
                    </label>
                    <input type="text" 
                           class="form-control form-control-lg" 
                           id="username" 
                           name="username" 
                           placeholder="<?= __('auth.username') ?>"
                           required 
                           autofocus
                           autocomplete="username">
                </div>
                
                <!-- Password Field -->
                <div class="mb-3">
                    <label for="password" class="form-label">
                        <i class="fas fa-lock me-1"></i>
                        <?= __('auth.password') ?>
                    </label>
                    <div class="input-group">
                        <input type="password" 
                               class="form-control form-control-lg" 
                               id="password" 
                               name="password" 
                               placeholder="<?= __('auth.password') ?>"
                               required
                               autocomplete="current-password">
                        <button class="btn btn-outline-secondary" 
                                type="button" 
                                id="togglePassword"
                                title="<?= __('Show/Hide Password') ?>">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
                
                <!-- Remember Me -->
                <div class="mb-4">
                    <div class="form-check">
                        <input class="form-check-input" 
                               type="checkbox" 
                               id="remember_me" 
                               name="remember_me" 
                               value="1">
                        <label class="form-check-label" for="remember_me">
                            <?= __('auth.remember_me') ?>
                        </label>
                    </div>
                </div>
                
                <!-- Login Button -->
                <div class="d-grid mb-3">
                    <button type="submit" class="btn btn-primary btn-lg" id="loginBtn">
                        <i class="fas fa-sign-in-alt me-2"></i>
                        <?= __('auth.login') ?>
                    </button>
                </div>
                
                <!-- Forgot Password Link -->
                <div class="text-center">
                    <a href="/forgot-password" class="text-decoration-none">
                        <i class="fas fa-question-circle me-1"></i>
                        <?= __('auth.forgot_password') ?>
                    </a>
                </div>
                
            </form>
            
        </div>
    </div>
    
    <!-- Demo Credentials (Remove in production) -->
    <?php if (defined('APP_DEBUG') && APP_DEBUG): ?>
    <div class="demo-credentials mt-4">
        <div class="card bg-light">
            <div class="card-body">
                <h6 class="card-title">
                    <i class="fas fa-info-circle me-1"></i>
                    Demo Credentials
                </h6>
                <div class="row">
                    <div class="col-md-4">
                        <strong>Admin:</strong><br>
                        Username: admin<br>
                        Password: admin123
                    </div>
                    <div class="col-md-4">
                        <strong>Manager:</strong><br>
                        Username: manager<br>
                        Password: admin123
                    </div>
                    <div class="col-md-4">
                        <strong>User:</strong><br>
                        Username: user<br>
                        Password: admin123
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Toggle password visibility
    const togglePassword = document.getElementById('togglePassword');
    const passwordField = document.getElementById('password');
    
    if (togglePassword && passwordField) {
        togglePassword.addEventListener('click', function() {
            const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordField.setAttribute('type', type);
            
            const icon = this.querySelector('i');
            icon.classList.toggle('fa-eye');
            icon.classList.toggle('fa-eye-slash');
        });
    }
    
    // Form submission handling
    const loginForm = document.getElementById('loginForm');
    const loginBtn = document.getElementById('loginBtn');
    
    if (loginForm && loginBtn) {
        loginForm.addEventListener('submit', function() {
            loginBtn.disabled = true;
            loginBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i><?= __("please_wait") ?>';
        });
    }
    
    // Auto-fill demo credentials (development only)
    <?php if (defined('APP_DEBUG') && APP_DEBUG): ?>
    document.addEventListener('click', function(e) {
        if (e.target.closest('.demo-credentials')) {
            const text = e.target.textContent || e.target.innerText;
            if (text.includes('admin')) {
                document.getElementById('username').value = 'admin';
                document.getElementById('password').value = 'admin123';
            } else if (text.includes('manager')) {
                document.getElementById('username').value = 'manager';
                document.getElementById('password').value = 'admin123';
            } else if (text.includes('user')) {
                document.getElementById('username').value = 'user';
                document.getElementById('password').value = 'admin123';
            }
        }
    });
    <?php endif; ?>
});
</script>
