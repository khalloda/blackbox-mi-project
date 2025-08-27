<?php
/**
 * Database Configuration
 * 
 * This file contains database connection settings for the Spare Parts Management System.
 * Update the credentials below with your actual database information.
 */

return [
    // Default database connection
    'default' => [
        'driver' => 'mysql',
        'host' => 'localhost',
        'port' => 3306,
        'database' => 'spare_parts_system',
        'username' => 'root',
        'password' => '',
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
        'options' => [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    ],
    
    // Test database connection (optional)
    'test' => [
        'driver' => 'mysql',
        'host' => 'localhost',
        'port' => 3306,
        'database' => 'spare_parts_system_test',
        'username' => 'root',
        'password' => '',
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
    ],
    
    // Backup database connection (optional)
    'backup' => [
        'driver' => 'mysql',
        'host' => 'backup_host',
        'port' => 3306,
        'database' => 'spare_parts_system_backup',
        'username' => 'backup_username',
        'password' => 'backup_password',
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
    ]
];
