<!DOCTYPE html>
<html <?= \App\Core\Language::getHtmlAttributes() ?>>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'Login - Spare Parts Management System') ?></title>
    
    <!-- CSRF Token -->
    <?= \App\Core\CSRF::metaTag() ?>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link href="/css/auth.css" rel="stylesheet">
    
    <?php if (\App\Core\Language::isRTL()): ?>
    <!-- RTL CSS -->
    <link href="/css/rtl.css" rel="stylesheet">
    <?php endif; ?>
</head>
<body class="auth-body <?= \App\Core\Language::getDirectionClass() ?>">
    
    <div class="auth-container">
        <div class="auth-wrapper">
            
            <!-- Language Switcher -->
            <div class="language-switcher-top">
                <?= \App\Core\Language::getSwitcherHTML() ?>
            </div>
            
            <!-- Logo and Title -->
            <div class="auth-header text-center mb-4">
                <div class="auth-logo">
                    <i class="fas fa-cogs fa-3x text-primary mb-3"></i>
                </div>
                <h1 class="auth-title h3"><?= __('app_name') ?></h1>
                <p class="auth-subtitle text-muted"><?= __('welcome') ?></p>
            </div>
            
            <!-- Flash Messages -->
            <?php if (isset($flash_messages) && !empty($flash_messages)): ?>
                <div class="mb-4">
                    <?php foreach ($flash_messages as $type => $message): ?>
                        <div class="alert alert-<?= $type === 'error' ? 'danger' : $type ?> alert-dismissible fade show" role="alert">
                            <i class="fas fa-<?= $type === 'error' ? 'exclamation-triangle' : ($type === 'success' ? 'check-circle' : 'info-circle') ?> me-2"></i>
                            <?= htmlspecialchars($message) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <!-- Auth Content -->
            <div class="auth-content">
                <?= $content ?>
            </div>
            
            <!-- Footer -->
            <div class="auth-footer text-center mt-4">
                <p class="text-muted small">
                    &copy; <?= date('Y') ?> <?= __('app_name') ?>. All rights reserved.
                </p>
            </div>
            
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    
    <!-- Custom Auth JS -->
    <script src="/js/auth.js"></script>
    
    <!-- CSRF Setup for AJAX -->
    <script>
        <?= \App\Core\CSRF::ajaxSetup() ?>
    </script>
    
</body>
</html>
