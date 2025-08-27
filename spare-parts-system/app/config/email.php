<?php
/**
 * Email Configuration
 * 
 * This file contains email settings for the Spare Parts Management System.
 * Configure SMTP settings for sending emails (quotes, invoices, notifications).
 */

return [
    // SMTP Configuration
    'smtp' => [
        'host' => 'smtp.gmail.com',
        'port' => 587,
        'username' => '', // Your email address
        'password' => '', // Your email password or app password
        'encryption' => 'tls', // tls or ssl
        'auth' => true,
        'timeout' => 30,
    ],
    
    // Default sender information
    'from' => [
        'email' => 'noreply@spareparts.com',
        'name' => 'Spare Parts Management System',
    ],
    
    // Email templates configuration
    'templates' => [
        'quote' => [
            'subject_en' => 'Quote #:number from :company',
            'subject_ar' => 'عرض سعر رقم :number من :company',
            'template_en' => 'quote_email_en',
            'template_ar' => 'quote_email_ar',
        ],
        'invoice' => [
            'subject_en' => 'Invoice #:number from :company',
            'subject_ar' => 'فاتورة رقم :number من :company',
            'template_en' => 'invoice_email_en',
            'template_ar' => 'invoice_email_ar',
        ],
        'payment_reminder' => [
            'subject_en' => 'Payment Reminder - Invoice #:number',
            'subject_ar' => 'تذكير بالدفع - فاتورة رقم :number',
            'template_en' => 'payment_reminder_en',
            'template_ar' => 'payment_reminder_ar',
        ],
        'welcome' => [
            'subject_en' => 'Welcome to :company',
            'subject_ar' => 'مرحباً بك في :company',
            'template_en' => 'welcome_email_en',
            'template_ar' => 'welcome_email_ar',
        ],
    ],
    
    // Email queue settings
    'queue' => [
        'enabled' => false, // Set to true to enable email queuing
        'max_attempts' => 3,
        'retry_delay' => 300, // 5 minutes
    ],
    
    // Email logging
    'log' => [
        'enabled' => true,
        'log_file' => __DIR__ . '/../../logs/email.log',
        'log_level' => 'info', // debug, info, warning, error
    ],
    
    // Email validation
    'validation' => [
        'verify_ssl' => true,
        'allow_self_signed' => false,
    ],
    
    // Attachment settings
    'attachments' => [
        'max_size' => 10485760, // 10MB in bytes
        'allowed_types' => ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'jpg', 'jpeg', 'png'],
        'temp_dir' => __DIR__ . '/../../temp/',
    ],
    
    // Rate limiting
    'rate_limit' => [
        'enabled' => true,
        'max_emails_per_hour' => 100,
        'max_emails_per_day' => 1000,
    ],
    
    // Backup email settings (fallback SMTP)
    'backup_smtp' => [
        'enabled' => false,
        'host' => 'backup-smtp.example.com',
        'port' => 587,
        'username' => '',
        'password' => '',
        'encryption' => 'tls',
    ],
];
