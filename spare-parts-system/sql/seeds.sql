-- Spare Parts Management System Seed Data
USE spare_parts_system;

-- Insert default admin user
INSERT INTO users (username, email, password_hash, full_name, role, is_active) VALUES
('admin', 'admin@spareparts.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'System Administrator', 'admin', TRUE),
('manager', 'manager@spareparts.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Store Manager', 'manager', TRUE),
('user', 'user@spareparts.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Store User', 'user', TRUE);

-- Insert classifications
INSERT INTO classifications (code, name_en, name_ar, description_en, description_ar) VALUES
('ENG', 'Engine Parts', 'قطع المحرك', 'Engine related spare parts', 'قطع غيار متعلقة بالمحرك'),
('BDY', 'Body Parts', 'قطع الهيكل', 'Body and exterior parts', 'قطع الهيكل والأجزاء الخارجية'),
('INT', 'Interior Parts', 'قطع داخلية', 'Interior and cabin parts', 'قطع داخلية وقطع المقصورة'),
('ELE', 'Electrical Parts', 'قطع كهربائية', 'Electrical and electronic parts', 'قطع كهربائية وإلكترونية'),
('BRK', 'Brake Parts', 'قطع الفرامل', 'Brake system parts', 'قطع نظام الفرامل'),
('SUS', 'Suspension Parts', 'قطع التعليق', 'Suspension system parts', 'قطع نظام التعليق'),
('TRA', 'Transmission Parts', 'قطع ناقل الحركة', 'Transmission system parts', 'قطع نظام ناقل الحركة'),
('FUE', 'Fuel System', 'نظام الوقود', 'Fuel system parts', 'قطع نظام الوقود'),
('COO', 'Cooling System', 'نظام التبريد', 'Cooling system parts', 'قطع نظام التبريد'),
('EXH', 'Exhaust System', 'نظام العادم', 'Exhaust system parts', 'قطع نظام العادم');

-- Insert colors
INSERT INTO colors (name_en, name_ar, hex_code) VALUES
('Black', 'أسود', '#000000'),
('White', 'أبيض', '#FFFFFF'),
('Red', 'أحمر', '#FF0000'),
('Blue', 'أزرق', '#0000FF'),
('Green', 'أخضر', '#008000'),
('Yellow', 'أصفر', '#FFFF00'),
('Silver', 'فضي', '#C0C0C0'),
('Gray', 'رمادي', '#808080'),
('Brown', 'بني', '#A52A2A'),
('Orange', 'برتقالي', '#FFA500');

-- Insert brands
INSERT INTO brands (name, website) VALUES
('Toyota', 'https://www.toyota.com'),
('Honda', 'https://www.honda.com'),
('Ford', 'https://www.ford.com'),
('Chevrolet', 'https://www.chevrolet.com'),
('BMW', 'https://www.bmw.com'),
('Mercedes-Benz', 'https://www.mercedes-benz.com'),
('Audi', 'https://www.audi.com'),
('Volkswagen', 'https://www.volkswagen.com'),
('Nissan', 'https://www.nissan.com'),
('Hyundai', 'https://www.hyundai.com'),
('Kia', 'https://www.kia.com'),
('Mazda', 'https://www.mazda.com'),
('Subaru', 'https://www.subaru.com'),
('Mitsubishi', 'https://www.mitsubishi.com'),
('Lexus', 'https://www.lexus.com');

-- Insert car makes
INSERT INTO car_makes (name, country) VALUES
('Toyota', 'Japan'),
('Honda', 'Japan'),
('Ford', 'USA'),
('Chevrolet', 'USA'),
('BMW', 'Germany'),
('Mercedes-Benz', 'Germany'),
('Audi', 'Germany'),
('Volkswagen', 'Germany'),
('Nissan', 'Japan'),
('Hyundai', 'South Korea'),
('Kia', 'South Korea'),
('Mazda', 'Japan'),
('Subaru', 'Japan'),
('Mitsubishi', 'Japan'),
('Lexus', 'Japan');

-- Insert car models for Toyota
INSERT INTO car_models (car_make_id, name, year_from, year_to, engine_type) VALUES
(1, 'Camry', 2010, 2024, 'Petrol'),
(1, 'Corolla', 2010, 2024, 'Petrol'),
(1, 'RAV4', 2010, 2024, 'Petrol'),
(1, 'Highlander', 2010, 2024, 'Petrol'),
(1, 'Prius', 2010, 2024, 'Hybrid'),
(1, 'Land Cruiser', 2010, 2024, 'Petrol'),
(1, 'Hilux', 2010, 2024, 'Diesel');

-- Insert car models for Honda
INSERT INTO car_models (car_make_id, name, year_from, year_to, engine_type) VALUES
(2, 'Civic', 2010, 2024, 'Petrol'),
(2, 'Accord', 2010, 2024, 'Petrol'),
(2, 'CR-V', 2010, 2024, 'Petrol'),
(2, 'Pilot', 2010, 2024, 'Petrol'),
(2, 'Fit', 2010, 2024, 'Petrol'),
(2, 'HR-V', 2015, 2024, 'Petrol');

-- Insert car models for Ford
INSERT INTO car_models (car_make_id, name, year_from, year_to, engine_type) VALUES
(3, 'F-150', 2010, 2024, 'Petrol'),
(3, 'Mustang', 2010, 2024, 'Petrol'),
(3, 'Explorer', 2010, 2024, 'Petrol'),
(3, 'Focus', 2010, 2020, 'Petrol'),
(3, 'Escape', 2010, 2024, 'Petrol'),
(3, 'Edge', 2010, 2024, 'Petrol');

-- Insert warehouses
INSERT INTO warehouses (code, name_en, name_ar, address_en, address_ar, city, country, phone, email, manager_name) VALUES
('WH001', 'Main Warehouse', 'المستودع الرئيسي', '123 Industrial Street', '123 شارع الصناعة', 'Dubai', 'UAE', '+971-4-1234567', 'main@warehouse.com', 'Ahmed Al-Mansouri'),
('WH002', 'Secondary Warehouse', 'المستودع الثانوي', '456 Storage Avenue', '456 شارع التخزين', 'Abu Dhabi', 'UAE', '+971-2-7654321', 'secondary@warehouse.com', 'Mohammed Al-Zaabi'),
('WH003', 'Spare Parts Center', 'مركز قطع الغيار', '789 Parts Boulevard', '789 شارع قطع الغيار', 'Sharjah', 'UAE', '+971-6-9876543', 'parts@warehouse.com', 'Khalid Al-Qasimi');

-- Insert warehouse locations
INSERT INTO warehouse_locations (warehouse_id, code, name_en, name_ar, description_en, description_ar) VALUES
(1, 'A01', 'Section A - Row 1', 'القسم أ - الصف 1', 'Engine parts section', 'قسم قطع المحرك'),
(1, 'A02', 'Section A - Row 2', 'القسم أ - الصف 2', 'Body parts section', 'قسم قطع الهيكل'),
(1, 'B01', 'Section B - Row 1', 'القسم ب - الصف 1', 'Electrical parts section', 'قسم القطع الكهربائية'),
(1, 'B02', 'Section B - Row 2', 'القسم ب - الصف 2', 'Interior parts section', 'قسم القطع الداخلية'),
(2, 'A01', 'Section A - Row 1', 'القسم أ - الصف 1', 'Brake parts section', 'قسم قطع الفرامل'),
(2, 'A02', 'Section A - Row 2', 'القسم أ - الصف 2', 'Suspension parts section', 'قسم قطع التعليق'),
(3, 'A01', 'Section A - Row 1', 'القسم أ - الصف 1', 'Fast moving parts', 'القطع سريعة الحركة'),
(3, 'A02', 'Section A - Row 2', 'القسم أ - الصف 2', 'Slow moving parts', 'القطع بطيئة الحركة');

-- Insert sample clients
INSERT INTO clients (code, type, company_name, contact_person, email, phone, mobile, address_en, address_ar, city, country, credit_limit, payment_terms) VALUES
('CL001', 'company', 'Al-Rashid Auto Parts LLC', 'Ahmed Al-Rashid', 'ahmed@alrashidauto.com', '+971-4-2345678', '+971-50-1234567', '123 Business Bay', '123 خليج الأعمال', 'Dubai', 'UAE', 50000.00, 30),
('CL002', 'company', 'Emirates Motors Trading', 'Mohammed Al-Emirati', 'mohammed@emiratesmotor.com', '+971-2-3456789', '+971-55-2345678', '456 Sheikh Zayed Road', '456 شارع الشيخ زايد', 'Abu Dhabi', 'UAE', 75000.00, 45),
('CL003', 'individual', NULL, 'Khalid Al-Mansoori', 'khalid.mansoori@email.com', '+971-6-4567890', '+971-56-3456789', '789 Al-Nahda Street', '789 شارع النهضة', 'Sharjah', 'UAE', 10000.00, 15),
('CL004', 'company', 'Gulf Auto Services', 'Fatima Al-Zahra', 'fatima@gulfauto.com', '+971-4-5678901', '+971-50-4567890', '321 Jumeirah Road', '321 شارع الجميرا', 'Dubai', 'UAE', 25000.00, 30),
('CL005', 'individual', NULL, 'Omar Al-Qasimi', 'omar.qasimi@email.com', '+971-6-6789012', '+971-55-5678901', '654 University City', '654 المدينة الجامعية', 'Sharjah', 'UAE', 5000.00, 7);

-- Insert sample suppliers
INSERT INTO suppliers (code, company_name, contact_person, email, phone, mobile, address_en, address_ar, city, country, payment_terms) VALUES
('SUP001', 'Toyota Parts International', 'Hiroshi Tanaka', 'hiroshi@toyotaparts.com', '+81-3-1234567', '+81-90-1234567', '1-1 Toyota Town', '1-1 تويوتا تاون', 'Tokyo', 'Japan', 60),
('SUP002', 'Honda Genuine Parts', 'Kenji Yamamoto', 'kenji@hondaparts.com', '+81-3-2345678', '+81-90-2345678', '2-1 Honda City', '2-1 هوندا سيتي', 'Tokyo', 'Japan', 45),
('SUP003', 'Ford Motor Parts', 'John Smith', 'john@fordparts.com', '+1-313-3456789', '+1-555-3456789', '1 American Road', '1 الطريق الأمريكي', 'Detroit', 'USA', 30),
('SUP004', 'BMW Parts Division', 'Hans Mueller', 'hans@bmwparts.com', '+49-89-4567890', '+49-170-4567890', '1 BMW Strasse', '1 شارع بي إم دبليو', 'Munich', 'Germany', 45),
('SUP005', 'Universal Auto Parts', 'Ahmed Hassan', 'ahmed@universalparts.com', '+971-4-7890123', '+971-50-7890123', '999 Industrial Area', '999 المنطقة الصناعية', 'Dubai', 'UAE', 30);

-- Insert sample products
INSERT INTO products (code, name_en, name_ar, description_en, description_ar, classification_id, brand_id, color_id, car_make_id, car_model_id, part_number, unit_of_measure, cost_price, selling_price, min_stock_level, max_stock_level, reorder_level) VALUES
('ENG0001', 'Engine Oil Filter', 'فلتر زيت المحرك', 'High quality engine oil filter', 'فلتر زيت محرك عالي الجودة', 1, 1, NULL, 1, 1, 'TOY-OF-001', 'PCS', 15.50, 25.00, 10, 100, 20),
('ENG0002', 'Air Filter', 'فلتر الهواء', 'Engine air filter element', 'عنصر فلتر هواء المحرك', 1, 1, NULL, 1, 1, 'TOY-AF-001', 'PCS', 22.00, 35.00, 5, 50, 15),
('BDY0001', 'Front Bumper', 'الصدام الأمامي', 'Front bumper assembly', 'مجموعة الصدام الأمامي', 2, 1, 1, 1, 1, 'TOY-FB-001', 'PCS', 450.00, 650.00, 2, 10, 3),
('BDY0002', 'Headlight Assembly', 'مجموعة المصباح الأمامي', 'Complete headlight assembly', 'مجموعة المصباح الأمامي الكاملة', 2, 1, NULL, 1, 1, 'TOY-HL-001', 'PCS', 280.00, 420.00, 2, 8, 3),
('ELE0001', 'Spark Plug', 'شمعة الإشعال', 'Iridium spark plug', 'شمعة إشعال إيريديوم', 4, 1, NULL, 1, 1, 'TOY-SP-001', 'PCS', 12.00, 18.00, 20, 200, 40),
('BRK0001', 'Brake Pad Set', 'طقم فحمات الفرامل', 'Front brake pad set', 'طقم فحمات الفرامل الأمامية', 5, 1, NULL, 1, 1, 'TOY-BP-001', 'SET', 85.00, 125.00, 5, 30, 10),
('BRK0002', 'Brake Disc', 'قرص الفرامل', 'Front brake disc rotor', 'قرص فرامل أمامي', 5, 1, NULL, 1, 1, 'TOY-BD-001', 'PCS', 120.00, 180.00, 3, 15, 5),
('SUS0001', 'Shock Absorber', 'ماص الصدمات', 'Front shock absorber', 'ماص صدمات أمامي', 6, 1, NULL, 1, 1, 'TOY-SA-001', 'PCS', 95.00, 145.00, 4, 20, 8),
('INT0001', 'Floor Mat Set', 'طقم سجاد الأرضية', 'Rubber floor mat set', 'طقم سجاد أرضية مطاطي', 3, 1, 1, 1, 1, 'TOY-FM-001', 'SET', 35.00, 55.00, 10, 50, 15),
('ENG0003', 'Timing Belt', 'سير التوقيت', 'Engine timing belt', 'سير توقيت المحرك', 1, 2, NULL, 2, 8, 'HON-TB-001', 'PCS', 45.00, 70.00, 5, 25, 10);

-- Insert initial stock for products
INSERT INTO stock (product_id, warehouse_location_id, quantity, reserved_quantity) VALUES
(1, 1, 50, 0),  -- Engine Oil Filter in WH001-A01
(2, 1, 30, 0),  -- Air Filter in WH001-A01
(3, 2, 5, 0),   -- Front Bumper in WH001-A02
(4, 2, 4, 0),   -- Headlight Assembly in WH001-A02
(5, 3, 100, 0), -- Spark Plug in WH002-A01
(6, 5, 15, 0),  -- Brake Pad Set in WH002-A01
(7, 5, 8, 0),   -- Brake Disc in WH002-A01
(8, 6, 12, 0),  -- Shock Absorber in WH002-A02
(9, 4, 25, 0),  -- Floor Mat Set in WH001-B02
(10, 1, 20, 0); -- Timing Belt in WH001-A01

-- Insert sequences for auto-generated codes
INSERT INTO sequences (sequence_name, current_value, prefix, padding) VALUES
('client_code', 5, 'CL', 3),
('supplier_code', 5, 'SUP', 3),
('warehouse_code', 3, 'WH', 3),
('product_code_ENG', 3, 'ENG', 4),
('product_code_BDY', 2, 'BDY', 4),
('product_code_INT', 1, 'INT', 4),
('product_code_ELE', 1, 'ELE', 4),
('product_code_BRK', 2, 'BRK', 4),
('product_code_SUS', 1, 'SUS', 4),
('product_code_TRA', 0, 'TRA', 4),
('product_code_FUE', 0, 'FUE', 4),
('product_code_COO', 0, 'COO', 4),
('product_code_EXH', 0, 'EXH', 4),
('quote_number', 0, 'QT', 4),
('order_number', 0, 'SO', 4),
('invoice_number', 0, 'INV', 4),
('payment_number', 0, 'PAY', 4),
('po_number', 0, 'PO', 4),
('grn_number', 0, 'GRN', 4);

-- Insert system settings
INSERT INTO system_settings (setting_key, setting_value, description) VALUES
('company_name_en', 'Spare Parts Management System', 'Company name in English'),
('company_name_ar', 'نظام إدارة قطع الغيار', 'Company name in Arabic'),
('company_address_en', '123 Business Street, Dubai, UAE', 'Company address in English'),
('company_address_ar', '123 شارع الأعمال، دبي، الإمارات العربية المتحدة', 'Company address in Arabic'),
('company_phone', '+971-4-1234567', 'Company phone number'),
('company_email', 'info@spareparts.com', 'Company email address'),
('company_website', 'www.spareparts.com', 'Company website'),
('tax_number', '123456789', 'Company tax registration number'),
('default_currency', 'AED', 'Default currency code'),
('default_tax_rate', '5.00', 'Default tax rate percentage'),
('low_stock_threshold', '10', 'Low stock alert threshold'),
('quote_validity_days', '30', 'Default quote validity in days'),
('default_payment_terms', '30', 'Default payment terms in days'),
('smtp_host', 'smtp.gmail.com', 'SMTP server host'),
('smtp_port', '587', 'SMTP server port'),
('smtp_username', '', 'SMTP username'),
('smtp_password', '', 'SMTP password'),
('smtp_encryption', 'tls', 'SMTP encryption type'),
('backup_frequency', 'daily', 'Database backup frequency'),
('session_timeout', '3600', 'Session timeout in seconds'),
('max_login_attempts', '5', 'Maximum login attempts before lockout'),
('password_min_length', '8', 'Minimum password length'),
('enable_client_portal', '1', 'Enable client portal feature'),
('enable_barcode_scanning', '1', 'Enable barcode scanning feature'),
('enable_multi_warehouse', '1', 'Enable multi-warehouse feature'),
('enable_multi_currency', '0', 'Enable multi-currency feature'),
('default_language', 'en', 'Default system language'),
('rtl_support', '1', 'Enable RTL language support');

-- Sample quote data
INSERT INTO quotes (quote_number, client_id, quote_date, valid_until, status, subtotal, tax_percentage, tax_amount, total_amount, notes, created_by) VALUES
('QT0001', 1, '2024-01-15', '2024-02-14', 'sent', 500.00, 5.00, 25.00, 525.00, 'Initial quote for engine parts', 1),
('QT0002', 2, '2024-01-20', '2024-02-19', 'approved', 1200.00, 5.00, 60.00, 1260.00, 'Bulk order for brake parts', 1);

-- Sample quote items
INSERT INTO quote_items (quote_id, product_id, quantity, unit_price, line_total) VALUES
(1, 1, 10, 25.00, 250.00),
(1, 2, 5, 35.00, 175.00),
(1, 5, 4, 18.00, 72.00),
(2, 6, 8, 125.00, 1000.00),
(2, 7, 2, 180.00, 360.00);

-- Update sequences to reflect current data
UPDATE sequences SET current_value = 2 WHERE sequence_name = 'quote_number';
