<!DOCTYPE html>
<html <?= \App\Core\Language::getHtmlAttributes() ?>>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'Spare Parts Management System') ?></title>
    
    <!-- CSRF Token -->
    <?= \App\Core\CSRF::metaTag() ?>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link href="/css/app.css" rel="stylesheet">
    
    <?php if (\App\Core\Language::isRTL()): ?>
    <!-- RTL CSS -->
    <link href="/css/rtl.css" rel="stylesheet">
    <?php endif; ?>
    
    <!-- Additional CSS -->
    <?php if (isset($css) && is_array($css)): ?>
        <?php foreach ($css as $cssFile): ?>
            <link href="<?= htmlspecialchars($cssFile) ?>" rel="stylesheet">
        <?php endforeach; ?>
    <?php endif; ?>
</head>
<body class="<?= \App\Core\Language::getDirectionClass() ?>">
    
    <!-- Navigation -->
    <?php if (isset($user) && $user): ?>
        <?php include __DIR__ . '/../partials/navbar.php'; ?>
    <?php endif; ?>
    
    <!-- Main Content -->
    <div class="<?= isset($user) && $user ? 'main-content' : 'auth-content' ?>">
        
        <!-- Flash Messages -->
        <?php if (isset($flash_messages) && !empty($flash_messages)): ?>
            <div class="container-fluid mt-3">
                <?php foreach ($flash_messages as $type => $message): ?>
                    <div class="alert alert-<?= $type === 'error' ? 'danger' : $type ?> alert-dismissible fade show" role="alert">
                        <?= htmlspecialchars($message) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <!-- Page Content -->
        <?= $content ?>
        
    </div>
    
    <!-- Footer -->
    <?php if (isset($user) && $user): ?>
        <?php include __DIR__ . '/../partials/footer.php'; ?>
    <?php endif; ?>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    
    <!-- Custom JS -->
    <script src="/js/app.js"></script>
    
    <!-- CSRF Setup for AJAX -->
    <script>
        <?= \App\Core\CSRF::ajaxSetup() ?>
    </script>
    
    <!-- Additional JS -->
    <?php if (isset($js) && is_array($js)): ?>
        <?php foreach ($js as $jsFile): ?>
            <script src="<?= htmlspecialchars($jsFile) ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
    
</body>
</html>
