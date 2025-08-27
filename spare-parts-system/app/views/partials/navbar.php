<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
    <div class="container-fluid">
        
        <!-- Brand -->
        <a class="navbar-brand" href="/dashboard">
            <i class="fas fa-cogs me-2"></i>
            <span class="d-none d-md-inline"><?= __('app_name') ?></span>
            <span class="d-md-none">SPMS</span>
        </a>
        
        <!-- Mobile Toggle -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <!-- Navigation Links -->
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                
                <!-- Dashboard -->
                <li class="nav-item">
                    <a class="nav-link" href="/dashboard">
                        <i class="fas fa-tachometer-alt me-1"></i>
                        <?= __('nav.dashboard') ?>
                    </a>
                </li>
                
                <!-- Masters Dropdown -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="mastersDropdown" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-database me-1"></i>
                        <?= __('nav.masters') ?>
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="/clients">
                            <i class="fas fa-users me-2"></i><?= __('nav.clients') ?>
                        </a></li>
                        <li><a class="dropdown-item" href="/suppliers">
                            <i class="fas fa-truck me-2"></i><?= __('nav.suppliers') ?>
                        </a></li>
                        <li><a class="dropdown-item" href="/warehouses">
                            <i class="fas fa-warehouse me-2"></i><?= __('nav.warehouses') ?>
                        </a></li>
                        <li><a class="dropdown-item" href="/products">
                            <i class="fas fa-boxes me-2"></i><?= __('nav.products') ?>
                        </a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="/dropdowns">
                            <i class="fas fa-list me-2"></i><?= __('nav.dropdowns') ?>
                        </a></li>
                    </ul>
                </li>
                
                <!-- Sales Dropdown -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="salesDropdown" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-shopping-cart me-1"></i>
                        <?= __('nav.sales') ?>
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="/quotes">
                            <i class="fas fa-file-alt me-2"></i><?= __('nav.quotes') ?>
                        </a></li>
                        <li><a class="dropdown-item" href="/sales-orders">
                            <i class="fas fa-shopping-cart me-2"></i><?= __('nav.sales_orders') ?>
                        </a></li>
                        <li><a class="dropdown-item" href="/invoices">
                            <i class="fas fa-file-invoice me-2"></i><?= __('nav.invoices') ?>
                        </a></li>
                        <li><a class="dropdown-item" href="/payments">
                            <i class="fas fa-credit-card me-2"></i><?= __('nav.payments') ?>
                        </a></li>
                    </ul>
                </li>
                
                <!-- Inventory Dropdown -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="inventoryDropdown" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-cubes me-1"></i>
                        <?= __('nav.inventory') ?>
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="/stock">
                            <i class="fas fa-cubes me-2"></i><?= __('nav.stock') ?>
                        </a></li>
                        <li><a class="dropdown-item" href="/purchase-orders">
                            <i class="fas fa-file-invoice me-2"></i><?= __('nav.purchase_orders') ?>
                        </a></li>
                        <li><a class="dropdown-item" href="/grn">
                            <i class="fas fa-clipboard-check me-2"></i><?= __('nav.goods_receipt') ?>
                        </a></li>
                        <li><a class="dropdown-item" href="/stock/adjustments">
                            <i class="fas fa-edit me-2"></i><?= __('nav.stock_adjustments') ?>
                        </a></li>
                    </ul>
                </li>
                
                <!-- Reports -->
                <li class="nav-item">
                    <a class="nav-link" href="/reports">
                        <i class="fas fa-chart-bar me-1"></i>
                        <?= __('nav.reports') ?>
                    </a>
                </li>
                
            </ul>
            
            <!-- Right Side Navigation -->
            <ul class="navbar-nav">
                
                <!-- Language Switcher -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="languageDropdown" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-globe me-1"></i>
                        <?= \App\Core\Language::getLanguageName(\App\Core\Language::getCurrentLanguage()) ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <?php foreach (\App\Core\Language::getSupportedLanguages() as $lang): ?>
                            <li>
                                <a class="dropdown-item <?= $lang === \App\Core\Language::getCurrentLanguage() ? 'active' : '' ?>" 
                                   href="/language/<?= $lang ?>">
                                    <?= \App\Core\Language::getLanguageName($lang) ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </li>
                
                <!-- Notifications -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle position-relative" href="#" id="notificationsDropdown" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-bell"></i>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" id="notificationCount">
                            <?= ($stats['low_stock_items'] ?? 0) + count($pending_items ?? []) ?>
                        </span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" style="width: 300px;">
                        <li><h6 class="dropdown-header">Notifications</h6></li>
                        
                        <?php if (!empty($low_stock_items)): ?>
                            <?php foreach (array_slice($low_stock_items, 0, 3) as $item): ?>
                                <li>
                                    <a class="dropdown-item" href="/stock">
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-exclamation-triangle text-danger me-2"></i>
                                            <div>
                                                <div class="fw-bold"><?= htmlspecialchars($item['code']) ?></div>
                                                <small class="text-muted">Low stock: <?= $item['current_stock'] ?></small>
                                            </div>
                                        </div>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        
                        <?php if (!empty($pending_items)): ?>
                            <?php foreach (array_slice($pending_items, 0, 3) as $item): ?>
                                <li>
                                    <a class="dropdown-item" href="/<?= $item['type'] ?>s/<?= $item['id'] ?>">
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-clock text-warning me-2"></i>
                                            <div>
                                                <div class="fw-bold"><?= htmlspecialchars($item['number']) ?></div>
                                                <small class="text-muted"><?= htmlspecialchars($item['description']) ?></small>
                                            </div>
                                        </div>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        
                        <?php if (empty($low_stock_items) && empty($pending_items)): ?>
                            <li><span class="dropdown-item-text text-muted">No notifications</span></li>
                        <?php endif; ?>
                        
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-center" href="/notifications">View All</a></li>
                    </ul>
                </li>
                
                <!-- User Dropdown -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-user-circle me-1"></i>
                        <?= htmlspecialchars($user['full_name'] ?? 'User') ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><h6 class="dropdown-header">
                            <?= htmlspecialchars($user['full_name'] ?? 'User') ?><br>
                            <small class="text-muted"><?= htmlspecialchars($user['role'] ?? 'user') ?></small>
                        </h6></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="/profile">
                            <i class="fas fa-user me-2"></i><?= __('nav.profile') ?>
                        </a></li>
                        <li><a class="dropdown-item" href="/change-password">
                            <i class="fas fa-key me-2"></i><?= __('auth.change_password') ?>
                        </a></li>
                        
                        <?php if (\App\Core\Auth::hasAnyRole(['admin', 'manager'])): ?>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="/settings">
                                <i class="fas fa-cog me-2"></i><?= __('nav.settings') ?>
                            </a></li>
                            <?php if (\App\Core\Auth::hasRole('admin')): ?>
                                <li><a class="dropdown-item" href="/users">
                                    <i class="fas fa-users-cog me-2"></i><?= __('nav.users') ?>
                                </a></li>
                            <?php endif; ?>
                        <?php endif; ?>
                        
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="/logout">
                            <i class="fas fa-sign-out-alt me-2"></i><?= __('auth.logout') ?>
                        </a></li>
                    </ul>
                </li>
                
            </ul>
        </div>
    </div>
</nav>

<!-- Add some spacing after navbar -->
<div class="navbar-spacer" style="height: 20px;"></div>
