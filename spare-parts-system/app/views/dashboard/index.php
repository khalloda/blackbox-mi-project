<div class="container-fluid py-4">
    
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">
                        <i class="fas fa-tachometer-alt me-2"></i>
                        <?= __('dashboard.title') ?>
                    </h1>
                    <p class="text-muted mb-0"><?= __('dashboard.overview') ?></p>
                </div>
                <div>
                    <span class="text-muted">
                        <i class="fas fa-clock me-1"></i>
                        <?= date('Y-m-d H:i') ?>
                    </span>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                <?= __('dashboard.total_clients') ?>
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= number_format($stats['total_clients'] ?? 0) ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                <?= __('dashboard.total_products') ?>
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= number_format($stats['total_products'] ?? 0) ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-boxes fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                <?= __('dashboard.total_stock_value') ?>
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= \App\Core\Language::formatCurrency($stats['total_stock_value'] ?? 0) ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                <?= __('dashboard.pending_orders') ?>
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= number_format($stats['pending_orders'] ?? 0) ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-shopping-cart fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Secondary Statistics -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                <?= __('dashboard.low_stock_items') ?>
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= number_format($stats['low_stock_items'] ?? 0) ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                <?= __('dashboard.monthly_sales') ?>
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= \App\Core\Language::formatCurrency($stats['monthly_sales'] ?? 0) ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-chart-line fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Outstanding Amount
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= \App\Core\Language::formatCurrency($stats['outstanding_amount'] ?? 0) ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-file-invoice-dollar fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Monthly Quotes
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= number_format($stats['monthly_quotes'] ?? 0) ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-file-alt fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Content Row -->
    <div class="row">
        
        <!-- Recent Activities -->
        <div class="col-xl-8 col-lg-7">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-history me-2"></i>
                        <?= __('dashboard.recent_sales') ?>
                    </h6>
                    <div class="dropdown no-arrow">
                        <a class="dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right shadow">
                            <a class="dropdown-item" href="/quotes">View All Quotes</a>
                            <a class="dropdown-item" href="/sales-orders">View All Orders</a>
                            <a class="dropdown-item" href="/invoices">View All Invoices</a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (!empty($recent_activities)): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Type</th>
                                        <th>Document</th>
                                        <th>Client</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach (array_slice($recent_activities, 0, 10) as $activity): ?>
                                        <tr>
                                            <td>
                                                <i class="<?= htmlspecialchars($activity['icon']) ?> me-1"></i>
                                                <?= ucfirst($activity['type']) ?>
                                            </td>
                                            <td>
                                                <strong><?= htmlspecialchars($activity['title']) ?></strong>
                                            </td>
                                            <td><?= htmlspecialchars($activity['description']) ?></td>
                                            <td><?= \App\Core\Language::formatCurrency($activity['amount']) ?></td>
                                            <td>
                                                <span class="badge bg-<?= $activity['status'] === 'approved' ? 'success' : ($activity['status'] === 'rejected' ? 'danger' : 'warning') ?>">
                                                    <?= ucfirst($activity['status']) ?>
                                                </span>
                                            </td>
                                            <td><?= \App\Core\Language::formatDate($activity['date']) ?></td>
                                            <td>
                                                <a href="<?= htmlspecialchars($activity['url']) ?>" class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="fas fa-inbox fa-3x text-gray-300 mb-3"></i>
                            <p class="text-muted">No recent activities found</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Quick Actions & Alerts -->
        <div class="col-xl-4 col-lg-5">
            
            <!-- Quick Actions -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-bolt me-2"></i>
                        <?= __('dashboard.quick_actions') ?>
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="/quotes/create" class="btn btn-primary">
                            <i class="fas fa-file-alt me-2"></i>
                            <?= __('dashboard.create_quote') ?>
                        </a>
                        <a href="/products/create" class="btn btn-success">
                            <i class="fas fa-plus me-2"></i>
                            <?= __('dashboard.add_product') ?>
                        </a>
                        <a href="/clients/create" class="btn btn-info">
                            <i class="fas fa-user-plus me-2"></i>
                            Add New Client
                        </a>
                        <a href="/reports/inventory" class="btn btn-warning">
                            <i class="fas fa-chart-bar me-2"></i>
                            <?= __('dashboard.stock_report') ?>
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Low Stock Alert -->
            <?php if (!empty($low_stock_items)): ?>
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <?= __('dashboard.low_stock_items') ?>
                    </h6>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        <?php foreach (array_slice($low_stock_items, 0, 5) as $item): ?>
                            <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                                <div>
                                    <strong><?= htmlspecialchars($item['code']) ?></strong><br>
                                    <small class="text-muted">
                                        <?= htmlspecialchars(\App\Core\Language::getCurrentLanguage() === 'ar' ? $item['name_ar'] : $item['name_en']) ?>
                                    </small>
                                </div>
                                <div class="text-end">
                                    <span class="badge bg-danger"><?= $item['current_stock'] ?></span><br>
                                    <small class="text-muted">Min: <?= $item['min_stock_level'] ?></small>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="text-center mt-3">
                        <a href="/stock" class="btn btn-sm btn-outline-danger">View All</a>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Pending Items -->
            <?php if (!empty($pending_items)): ?>
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-warning">
                        <i class="fas fa-clock me-2"></i>
                        Pending Items
                    </h6>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        <?php foreach (array_slice($pending_items, 0, 5) as $item): ?>
                            <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                                <div>
                                    <strong><?= htmlspecialchars($item['number']) ?></strong><br>
                                    <small class="text-muted"><?= htmlspecialchars($item['description']) ?></small>
                                </div>
                                <div class="text-end">
                                    <span class="badge bg-warning"><?= ucfirst($item['type']) ?></span><br>
                                    <small class="text-muted"><?= \App\Core\Language::formatDate($item['date']) ?></small>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
        </div>
    </div>
    
</div>

<!-- Custom Dashboard Styles -->
<style>
.border-left-primary {
    border-left: 0.25rem solid #4e73df !important;
}
.border-left-success {
    border-left: 0.25rem solid #1cc88a !important;
}
.border-left-info {
    border-left: 0.25rem solid #36b9cc !important;
}
.border-left-warning {
    border-left: 0.25rem solid #f6c23e !important;
}
.border-left-danger {
    border-left: 0.25rem solid #e74a3b !important;
}
.text-xs {
    font-size: 0.7rem;
}
.text-gray-300 {
    color: #dddfeb !important;
}
.text-gray-800 {
    color: #5a5c69 !important;
}
</style>
