<?php
/**
 * Application Configuration
 * 
 * This file contains the main application settings for the Spare Parts Management System.
 */

return [
    // Application Information
    'name' => 'Spare Parts Management System',
    'version' => '1.0.0',
    'description' => 'Complete spare parts management system with bilingual support',
    'author' => 'SPMS Development Team',
    
    // Environment Settings
    'environment' => 'production', // development, testing, production
    'debug' => false, // Set to true for development
    'timezone' => 'Asia/Dubai',
    'locale' => 'en',
    'fallback_locale' => 'en',
    
    // Security Settings
    'key' => 'your-32-character-secret-key-here', // Change this to a random 32 character string
    'cipher' => 'AES-256-CBC',
    'hash_algo' => 'sha256',
    
    // Session Configuration
    'session' => [
        'lifetime' => 3600, // 1 hour in seconds
        'expire_on_close' => false,
        'encrypt' => true,
        'cookie_name' => 'SPMS_SESSION',
        'cookie_path' => '/',
        'cookie_domain' => null,
        'cookie_secure' => false, // Set to true if using HTTPS
        'cookie_httponly' => true,
        'cookie_samesite' => 'Strict',
    ],
    
    // Database Settings
    'database' => [
        'default' => 'default',
        'log_queries' => false, // Set to true for development
        'slow_query_threshold' => 1000, // milliseconds
    ],
    
    // Cache Settings
    'cache' => [
        'default' => 'file',
        'stores' => [
            'file' => [
                'driver' => 'file',
                'path' => __DIR__ . '/../../cache/',
            ],
            'redis' => [
                'driver' => 'redis',
                'host' => '127.0.0.1',
                'port' => 6379,
                'database' => 0,
            ],
        ],
        'prefix' => 'spms_cache',
        'default_ttl' => 3600, // 1 hour
    ],
    
    // File Upload Settings
    'upload' => [
        'max_size' => 10485760, // 10MB in bytes
        'allowed_types' => [
            'images' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
            'documents' => ['pdf', 'doc', 'docx', 'xls', 'xlsx'],
            'archives' => ['zip', 'rar'],
        ],
        'paths' => [
            'products' => '/public/uploads/products/',
            'documents' => '/public/uploads/documents/',
            'temp' => '/temp/',
        ],
    ],
    
    // Pagination Settings
    'pagination' => [
        'default_per_page' => 20,
        'max_per_page' => 100,
        'page_name' => 'page',
    ],
    
    // Language Settings
    'languages' => [
        'supported' => ['en', 'ar'],
        'default' => 'en',
        'fallback' => 'en',
        'auto_detect' => true,
        'rtl_languages' => ['ar', 'he', 'fa', 'ur'],
    ],
    
    // Business Settings
    'business' => [
        'company_name_en' => 'Spare Parts Management System',
        'company_name_ar' => 'نظام إدارة قطع الغيار',
        'address_en' => '123 Business Street, Dubai, UAE',
        'address_ar' => '123 شارع الأعمال، دبي، الإمارات العربية المتحدة',
        'phone' => '+971-4-1234567',
        'email' => 'info@spareparts.com',
        'website' => 'www.spareparts.com',
        'tax_number' => '123456789',
        'currency' => 'AED',
        'currency_symbol' => 'د.إ',
        'tax_rate' => 5.00, // VAT percentage
        'logo' => '/public/images/logo.png',
    ],
    
    // System Features
    'features' => [
        'multi_warehouse' => true,
        'multi_currency' => false,
        'barcode_scanning' => true,
        'client_portal' => true,
        'email_notifications' => true,
        'sms_notifications' => false,
        'api_access' => true,
        'mobile_app' => false,
        'advanced_reporting' => true,
        'data_export' => true,
        'backup_restore' => true,
    ],
    
    // Security Features
    'security' => [
        'csrf_protection' => true,
        'xss_protection' => true,
        'sql_injection_protection' => true,
        'rate_limiting' => true,
        'login_attempts' => [
            'max_attempts' => 5,
            'lockout_time' => 900, // 15 minutes
        ],
        'password_policy' => [
            'min_length' => 8,
            'require_uppercase' => true,
            'require_lowercase' => true,
            'require_numbers' => true,
            'require_symbols' => false,
        ],
        'session_security' => [
            'regenerate_id' => true,
            'regenerate_interval' => 300, // 5 minutes
        ],
    ],
    
    // API Settings
    'api' => [
        'enabled' => true,
        'version' => 'v1',
        'rate_limit' => [
            'requests_per_minute' => 60,
            'requests_per_hour' => 1000,
        ],
        'authentication' => [
            'method' => 'token', // token, oauth, jwt
            'token_expiry' => 86400, // 24 hours
        ],
    ],
    
    // Logging Settings
    'logging' => [
        'enabled' => true,
        'level' => 'info', // debug, info, warning, error, critical
        'channels' => [
            'application' => [
                'driver' => 'file',
                'path' => __DIR__ . '/../../logs/application.log',
                'max_size' => 10485760, // 10MB
                'max_files' => 5,
            ],
            'security' => [
                'driver' => 'file',
                'path' => __DIR__ . '/../../logs/security.log',
                'max_size' => 10485760, // 10MB
                'max_files' => 10,
            ],
            'database' => [
                'driver' => 'file',
                'path' => __DIR__ . '/../../logs/database.log',
                'max_size' => 10485760, // 10MB
                'max_files' => 3,
            ],
        ],
    ],
    
    // Backup Settings
    'backup' => [
        'enabled' => true,
        'schedule' => 'daily', // hourly, daily, weekly, monthly
        'retention_days' => 30,
        'path' => __DIR__ . '/../../backups/',
        'include_uploads' => true,
        'compress' => true,
    ],
    
    // Performance Settings
    'performance' => [
        'enable_compression' => true,
        'cache_views' => true,
        'cache_routes' => true,
        'cache_config' => true,
        'minify_html' => false,
        'minify_css' => false,
        'minify_js' => false,
    ],
    
    // Development Settings (only used when environment = 'development')
    'development' => [
        'show_errors' => true,
        'log_queries' => true,
        'debug_toolbar' => true,
        'fake_email' => true, // Don't send real emails in development
        'seed_database' => true,
    ],
    
    // Third-party Integrations
    'integrations' => [
        'google_analytics' => [
            'enabled' => false,
            'tracking_id' => '',
        ],
        'sms_gateway' => [
            'enabled' => false,
            'provider' => 'twilio', // twilio, nexmo, etc.
            'api_key' => '',
            'api_secret' => '',
        ],
        'payment_gateway' => [
            'enabled' => false,
            'provider' => 'stripe', // stripe, paypal, etc.
            'api_key' => '',
            'webhook_secret' => '',
        ],
    ],
];
