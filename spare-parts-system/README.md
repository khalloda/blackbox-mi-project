# Spare Parts Management System

A comprehensive PHP-based spare parts management system with bilingual support (English/Arabic), complete sales workflow, inventory management, and advanced reporting features.

## System Requirements

- PHP 8.0 or higher
- MySQL 8.0 or higher
- Apache/Nginx web server
- SMTP server for email functionality

## Installation Steps

1. **Database Setup:**
   - Create a MySQL database
   - Import the schema from `/sql/schema.sql`
   - Import seed data from `/sql/seeds.sql`

2. **Configuration:**
   - Copy `/app/config/database.example.php` to `/app/config/database.php`
   - Update database credentials in the config file
   - Configure SMTP settings in `/app/config/email.php`

3. **Web Server:**
   - Point document root to `/public/` directory
   - Ensure mod_rewrite is enabled for Apache
   - Set appropriate file permissions

4. **Default Login:**
   - Username: admin
   - Password: admin123
   - Change password after first login

## System Features

### Phase 1: Core Infrastructure
- PSR-4 Autoloader with case-insensitive fallback
- MVC Router with parameter support
- Authentication system with session management
- Bilingual support (English/Arabic) with RTL
- CSRF protection on all forms
- Database abstraction layer
- Responsive UI design

### Phase 2: Masters CRUD
- Clients Management (Company/Individual)
- Suppliers Management
- Warehouses Management
- Products Management with auto-codes
- Dropdown Management
- Search and Pagination
- Profile tabs for clients/suppliers

### Phase 3: Sales Flow
- Quotes → Sales Orders → Invoices → Payments
- Stock reservations and availability
- Tax/discount calculations
- Multi-warehouse support

### Phase 4: Payments & Stock
- Multiple payment methods
- Balance tracking and credit limits
- Purchase Orders and GRNs
- Stock adjustments and audit trail
- Aging reports

### Phase 5: Email & PDF
- SMTP integration
- Bilingual email templates
- PDF generation (quotes, invoices, statements)
- Document attachments

### Phase 6: Reports & Advanced
- Sales, inventory, financial reports
- CSV exports
- Client portal
- Dashboard with metrics
- API integration

## Directory Structure

```
spare-parts-system/
├── app/
│   ├── core/           # Framework classes
│   ├── config/         # Configuration files
│   ├── lang/           # Language files
│   ├── controllers/    # MVC controllers
│   ├── models/         # Database models
│   └── views/          # Template files
├── public/             # Web root
│   ├── css/           # Stylesheets
│   ├── js/            # JavaScript
│   └── images/        # Assets
└── sql/               # Database files
```

## Support

For technical support or questions, refer to the inline documentation within each module.
