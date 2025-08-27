<?php
/**
 * Application Bootstrap
 * 
 * This is the main entry point for the Spare Parts Management System.
 * It initializes the application, sets up routing, and handles requests.
 */

// Define application constants
define('APP_START_TIME', microtime(true));
define('APP_ROOT', dirname(__DIR__));
define('APP_DEBUG', true); // Set to false in production

// Error reporting
if (APP_DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Set timezone
date_default_timezone_set('Asia/Dubai');

// Include autoloader
require_once APP_ROOT . '/app/core/Autoloader.php';

// Register autoloader
App\Core\Autoloader::register();

// Load configuration
use App\Core\Config;
use App\Core\Database;
use App\Core\Language;
use App\Core\Auth;
use App\Core\CSRF;
use App\Core\Router;

try {
    // Load database configuration
    $dbConfig = Config::load('database');
    Database::setConfig($dbConfig['default']);
    
    // Initialize language system
    Language::init(Config::get('app.locale', 'en'));
    
    // Start authentication
    Auth::startSession();
    
    // Initialize CSRF protection
    CSRF::init();
    
    // Set up routes
    setupRoutes();
    
    // Dispatch request
    $output = Router::dispatch();
    
    // Output response
    if ($output) {
        echo $output;
    }
    
} catch (Exception $e) {
    // Handle errors
    handleError($e);
}

/**
 * Set up application routes
 */
function setupRoutes()
{
    // Add CSRF middleware for POST requests
    Router::addGlobalMiddleware(CSRF::middleware());
    
    // Authentication routes
    Router::get('/', 'HomeController@index');
    Router::get('/login', 'AuthController@showLogin');
    Router::post('/login', 'AuthController@login');
    Router::get('/logout', 'AuthController@logout');
    
    // Dashboard
    Router::get('/dashboard', 'DashboardController@index');
    
    // Masters routes
    Router::get('/clients', 'ClientController@index');
    Router::get('/clients/create', 'ClientController@create');
    Router::post('/clients', 'ClientController@store');
    Router::get('/clients/{id}', 'ClientController@show');
    Router::get('/clients/{id}/edit', 'ClientController@edit');
    Router::post('/clients/{id}', 'ClientController@update');
    Router::post('/clients/{id}/delete', 'ClientController@delete');
    
    Router::get('/suppliers', 'SupplierController@index');
    Router::get('/suppliers/create', 'SupplierController@create');
    Router::post('/suppliers', 'SupplierController@store');
    Router::get('/suppliers/{id}', 'SupplierController@show');
    Router::get('/suppliers/{id}/edit', 'SupplierController@edit');
    Router::post('/suppliers/{id}', 'SupplierController@update');
    Router::post('/suppliers/{id}/delete', 'SupplierController@delete');
    
    Router::get('/warehouses', 'WarehouseController@index');
    Router::get('/warehouses/create', 'WarehouseController@create');
    Router::post('/warehouses', 'WarehouseController@store');
    Router::get('/warehouses/{id}', 'WarehouseController@show');
    Router::get('/warehouses/{id}/edit', 'WarehouseController@edit');
    Router::post('/warehouses/{id}', 'WarehouseController@update');
    Router::post('/warehouses/{id}/delete', 'WarehouseController@delete');
    
    Router::get('/products', 'ProductController@index');
    Router::get('/products/create', 'ProductController@create');
    Router::post('/products', 'ProductController@store');
    Router::get('/products/{id}', 'ProductController@show');
    Router::get('/products/{id}/edit', 'ProductController@edit');
    Router::post('/products/{id}', 'ProductController@update');
    Router::post('/products/{id}/delete', 'ProductController@delete');
    
    // Dropdown management
    Router::get('/dropdowns', 'DropdownController@index');
    Router::get('/dropdowns/{type}', 'DropdownController@manage');
    Router::post('/dropdowns/{type}', 'DropdownController@store');
    Router::post('/dropdowns/{type}/{id}', 'DropdownController@update');
    Router::post('/dropdowns/{type}/{id}/delete', 'DropdownController@delete');
    
    // AJAX routes for dependent dropdowns
    Router::get('/api/car-models/{makeId}', 'ApiController@getCarModels');
    Router::get('/api/products/search', 'ApiController@searchProducts');
    Router::get('/api/clients/search', 'ApiController@searchClients');
    
    // Sales routes
    Router::get('/quotes', 'QuoteController@index');
    Router::get('/quotes/create', 'QuoteController@create');
    Router::post('/quotes', 'QuoteController@store');
    Router::get('/quotes/{id}', 'QuoteController@show');
    Router::get('/quotes/{id}/edit', 'QuoteController@edit');
    Router::post('/quotes/{id}', 'QuoteController@update');
    Router::post('/quotes/{id}/delete', 'QuoteController@delete');
    Router::post('/quotes/{id}/send', 'QuoteController@send');
    Router::post('/quotes/{id}/approve', 'QuoteController@approve');
    Router::post('/quotes/{id}/reject', 'QuoteController@reject');
    Router::get('/quotes/{id}/pdf', 'QuoteController@pdf');
    Router::post('/quotes/{id}/convert', 'QuoteController@convertToOrder');
    
    Router::get('/sales-orders', 'SalesOrderController@index');
    Router::get('/sales-orders/create', 'SalesOrderController@create');
    Router::post('/sales-orders', 'SalesOrderController@store');
    Router::get('/sales-orders/{id}', 'SalesOrderController@show');
    Router::get('/sales-orders/{id}/edit', 'SalesOrderController@edit');
    Router::post('/sales-orders/{id}', 'SalesOrderController@update');
    Router::post('/sales-orders/{id}/delete', 'SalesOrderController@delete');
    Router::post('/sales-orders/{id}/ship', 'SalesOrderController@ship');
    Router::post('/sales-orders/{id}/deliver', 'SalesOrderController@deliver');
    Router::get('/sales-orders/{id}/pdf', 'SalesOrderController@pdf');
    Router::post('/sales-orders/{id}/invoice', 'SalesOrderController@createInvoice');
    
    Router::get('/invoices', 'InvoiceController@index');
    Router::get('/invoices/create', 'InvoiceController@create');
    Router::post('/invoices', 'InvoiceController@store');
    Router::get('/invoices/{id}', 'InvoiceController@show');
    Router::get('/invoices/{id}/edit', 'InvoiceController@edit');
    Router::post('/invoices/{id}', 'InvoiceController@update');
    Router::post('/invoices/{id}/delete', 'InvoiceController@delete');
    Router::post('/invoices/{id}/send', 'InvoiceController@send');
    Router::get('/invoices/{id}/pdf', 'InvoiceController@pdf');
    
    Router::get('/payments', 'PaymentController@index');
    Router::get('/payments/create', 'PaymentController@create');
    Router::post('/payments', 'PaymentController@store');
    Router::get('/payments/{id}', 'PaymentController@show');
    Router::get('/payments/{id}/edit', 'PaymentController@edit');
    Router::post('/payments/{id}', 'PaymentController@update');
    Router::post('/payments/{id}/delete', 'PaymentController@delete');
    
    // Inventory routes
    Router::get('/stock', 'StockController@index');
    Router::get('/stock/adjustments', 'StockController@adjustments');
    Router::get('/stock/adjustments/create', 'StockController@createAdjustment');
    Router::post('/stock/adjustments', 'StockController@storeAdjustment');
    Router::get('/stock/movements', 'StockController@movements');
    
    Router::get('/purchase-orders', 'PurchaseOrderController@index');
    Router::get('/purchase-orders/create', 'PurchaseOrderController@create');
    Router::post('/purchase-orders', 'PurchaseOrderController@store');
    Router::get('/purchase-orders/{id}', 'PurchaseOrderController@show');
    Router::get('/purchase-orders/{id}/edit', 'PurchaseOrderController@edit');
    Router::post('/purchase-orders/{id}', 'PurchaseOrderController@update');
    Router::post('/purchase-orders/{id}/delete', 'PurchaseOrderController@delete');
    Router::post('/purchase-orders/{id}/send', 'PurchaseOrderController@send');
    Router::get('/purchase-orders/{id}/pdf', 'PurchaseOrderController@pdf');
    Router::get('/purchase-orders/{id}/receive', 'PurchaseOrderController@receive');
    
    Router::get('/grn', 'GRNController@index');
    Router::get('/grn/create', 'GRNController@create');
    Router::post('/grn', 'GRNController@store');
    Router::get('/grn/{id}', 'GRNController@show');
    Router::get('/grn/{id}/edit', 'GRNController@edit');
    Router::post('/grn/{id}', 'GRNController@update');
    Router::post('/grn/{id}/complete', 'GRNController@complete');
    
    // Reports routes
    Router::get('/reports', 'ReportController@index');
    Router::get('/reports/sales', 'ReportController@sales');
    Router::get('/reports/inventory', 'ReportController@inventory');
    Router::get('/reports/financial', 'ReportController@financial');
    Router::post('/reports/generate', 'ReportController@generate');
    Router::get('/reports/export/{type}', 'ReportController@export');
    
    // Settings routes
    Router::get('/settings', 'SettingController@index');
    Router::post('/settings', 'SettingController@update');
    Router::get('/users', 'UserController@index');
    Router::get('/users/create', 'UserController@create');
    Router::post('/users', 'UserController@store');
    Router::get('/users/{id}/edit', 'UserController@edit');
    Router::post('/users/{id}', 'UserController@update');
    Router::post('/users/{id}/delete', 'UserController@delete');
    
    // Profile routes
    Router::get('/profile', 'ProfileController@index');
    Router::post('/profile', 'ProfileController@update');
    Router::post('/profile/password', 'ProfileController@changePassword');
    
    // Language switching
    Router::get('/language/{lang}', 'LanguageController@switch');
    
    // Error routes
    Router::get('/404', 'ErrorController@notFound');
    Router::get('/403', 'ErrorController@forbidden');
    Router::get('/500', 'ErrorController@serverError');
}

/**
 * Handle application errors
 * 
 * @param Exception $e Exception
 */
function handleError($e)
{
    // Log error
    error_log($e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine());
    
    if (APP_DEBUG) {
        // Show detailed error in development
        echo '<h1>Application Error</h1>';
        echo '<p><strong>Message:</strong> ' . htmlspecialchars($e->getMessage()) . '</p>';
        echo '<p><strong>File:</strong> ' . htmlspecialchars($e->getFile()) . '</p>';
        echo '<p><strong>Line:</strong> ' . $e->getLine() . '</p>';
        echo '<h2>Stack Trace:</h2>';
        echo '<pre>' . htmlspecialchars($e->getTraceAsString()) . '</pre>';
    } else {
        // Show generic error in production
        http_response_code(500);
        echo '<h1>500 - Internal Server Error</h1>';
        echo '<p>Something went wrong. Please try again later.</p>';
    }
}

/**
 * Get application execution time
 * 
 * @return float Execution time in seconds
 */
function getExecutionTime()
{
    return microtime(true) - APP_START_TIME;
}

/**
 * Get memory usage
 * 
 * @return string Formatted memory usage
 */
function getMemoryUsage()
{
    $bytes = memory_get_usage(true);
    $units = ['B', 'KB', 'MB', 'GB'];
    
    for ($i = 0; $bytes > 1024; $i++) {
        $bytes /= 1024;
    }
    
    return round($bytes, 2) . ' ' . $units[$i];
}

// Add debug information in development
if (APP_DEBUG && !headers_sent()) {
    header('X-Execution-Time: ' . number_format(getExecutionTime() * 1000, 2) . 'ms');
    header('X-Memory-Usage: ' . getMemoryUsage());
}
