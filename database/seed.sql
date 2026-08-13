-- ============================================================
-- FOOD FACTORY - Seed Data
-- Run after schema.sql
-- ============================================================

SET NAMES utf8mb4;

-- Roles
INSERT INTO roles (id, name, description) VALUES
    (1, 'customer',    'Registered customer account'),
    (2, 'staff',       'Kitchen / front-of-house staff'),
    (3, 'manager',     'Restaurant manager'),
    (4, 'admin',       'Administrator'),
    (5, 'super_admin', 'Full system access')
ON DUPLICATE KEY UPDATE name = VALUES(name);

-- Permissions
INSERT INTO permissions (permission_key, description) VALUES
    ('dashboard.view',      'View admin dashboard'),
    ('orders.view',         'View orders'),
    ('orders.create',       'Create orders manually'),
    ('orders.update',       'Update order status'),
    ('orders.cancel',       'Cancel orders'),
    ('orders.refund',       'Refund orders'),
    ('menu.view',           'View menu'),
    ('menu.create',         'Create menu items'),
    ('menu.update',         'Update menu items'),
    ('menu.delete',         'Delete menu items'),
    ('inventory.view',      'View inventory'),
    ('inventory.update',    'Adjust inventory'),
    ('customers.view',      'View customers'),
    ('customers.update',    'Update customers'),
    ('reservations.view',   'View reservations'),
    ('reservations.update', 'Update reservations'),
    ('reviews.moderate',    'Approve / reject reviews'),
    ('reports.view',        'View reports & analytics'),
    ('coupons.manage',      'Manage coupons'),
    ('settings.manage',     'Manage restaurant settings'),
    ('staff.manage',        'Manage staff accounts & roles'),
    ('audit.view',          'View audit logs')
ON DUPLICATE KEY UPDATE description = VALUES(description);

-- Role -> Permission mapping
-- staff: order + kitchen operations only
INSERT INTO role_permissions (role_id, permission_id)
SELECT 2, id FROM permissions WHERE permission_key IN
    ('dashboard.view','orders.view','orders.update','menu.view','reservations.view');

-- manager: staff perms + menu/inventory/customers/reservations/reviews/reports/coupons
INSERT INTO role_permissions (role_id, permission_id)
SELECT 3, id FROM permissions WHERE permission_key IN
    ('dashboard.view','orders.view','orders.create','orders.update','orders.cancel',
     'menu.view','menu.create','menu.update','menu.delete',
     'inventory.view','inventory.update',
     'customers.view','customers.update',
     'reservations.view','reservations.update',
     'reviews.moderate','reports.view','coupons.manage');

-- admin: everything manager has + refunds + settings + staff
INSERT INTO role_permissions (role_id, permission_id)
SELECT 4, id FROM permissions;

-- super_admin: everything (identical to admin here; reserved for future system-level perms)
INSERT INTO role_permissions (role_id, permission_id)
SELECT 5, id FROM permissions;

-- Default super admin account.
-- Email: admin@foodfactory.local | Password: ChangeMe!123
-- Generate your own hash with: password_hash('yourpassword', PASSWORD_DEFAULT)
INSERT INTO users (first_name, last_name, email, phone, password_hash, role_id, status, email_verified_at)
VALUES ('Food Factory', 'Admin', 'admin@foodfactory.local', '+918141214421',
        '$2y$10$92rE0m0m2Y1I0nq9x0nq9uQhE1sQeXG1yq0h0m0y0y0y0y0y0y0y0', 5, 'active', NOW())
ON DUPLICATE KEY UPDATE email = VALUES(email);
-- NOTE: the hash above is a PLACEHOLDER. Run `php database/make_admin_hash.php`
-- (see includes/functions.php helper) or reset via the admin setup script
-- before relying on this account. Never ship a real hash in source control.

-- Categories (from existing static menu)
INSERT INTO categories (name, slug, display_order, status) VALUES
    ('Pizza',   'pizza',   1, 'active'),
    ('Burgers', 'burgers', 2, 'active'),
    ('Pasta',   'pasta',   3, 'active'),
    ('Snacks',  'snacks',  4, 'active'),
    ('Beverages','beverages',5, 'active'),
    ('Desserts','desserts',6, 'active');

-- Menu items (migrated from the original static prices in index.php / menu.php)
INSERT INTO menu_items (category_id, name, slug, description, price, image, is_veg, status) VALUES
    ((SELECT id FROM categories WHERE slug='pizza'),   'Cheese Pizza',    'cheese-pizza',
        'Loaded with fresh mozzarella cheese and crispy crust.', 299.00, 'pizza.jpg', 1, 'active'),
    ((SELECT id FROM categories WHERE slug='burgers'), 'Veg Burger',      'veg-burger',
        'Crispy veggie patty with cheese and fresh vegetables.', 199.00, 'burger.jpg', 1, 'active'),
    ((SELECT id FROM categories WHERE slug='pasta'),   'Mix Sauce Pasta', 'mix-sauce-pasta',
        'A delicious combination of creamy white and tangy red sauce.', 249.00, 'pasta.jpg', 1, 'active'),
    ((SELECT id FROM categories WHERE slug='beverages'),'Cold Coffee',    'cold-coffee',
        'Chilled, creamy coffee blended to perfection.', 129.00, 'coffee.jpg', 1, 'active'),
    ((SELECT id FROM categories WHERE slug='snacks'),  'French Fries',    'french-fries',
        'Golden, crispy fries served hot and salted.', 149.00, 'frenchfries.jpg', 1, 'active'),
    ((SELECT id FROM categories WHERE slug='beverages'),'Fresh Juice',    'fresh-juice',
        'Seasonal fresh fruit juice, no added sugar.', 99.00, 'juices.jpg', 1, 'active'),
    ((SELECT id FROM categories WHERE slug='desserts'), 'Chef Special Dessert', 'chef-special-dessert',
        'A daily-rotating dessert crafted by our chef.', 179.00, 'dessert.jpg', 1, 'active');

-- Pizza size variants example
INSERT INTO item_variants (menu_item_id, name, price_delta, is_default) VALUES
    ((SELECT id FROM menu_items WHERE slug='cheese-pizza'), 'Regular', 0.00, 1),
    ((SELECT id FROM menu_items WHERE slug='cheese-pizza'), 'Medium', 120.00, 0),
    ((SELECT id FROM menu_items WHERE slug='cheese-pizza'), 'Large',  240.00, 0);

INSERT INTO addons (menu_item_id, name, price) VALUES
    ((SELECT id FROM menu_items WHERE slug='cheese-pizza'), 'Extra Cheese', 40.00),
    ((SELECT id FROM menu_items WHERE slug='veg-burger'), 'Extra Patty', 60.00);

-- Sample restaurant tables for dine-in / reservations
INSERT INTO restaurant_tables (label, capacity) VALUES
    ('T1', 2), ('T2', 2), ('T3', 4), ('T4', 4), ('T5', 6), ('T6', 8);

-- Default settings
INSERT INTO settings (setting_key, setting_value) VALUES
    ('restaurant_name', 'Food Factory'),
    ('restaurant_phone', '+91 81412 14421'),
    ('restaurant_email', 'foodfactory@gmail.com'),
    ('restaurant_address', '123 Food Bazar, Nikol, Ahmedabad, Gujarat'),
    ('opening_hours', 'Monday - Sunday, 10:00 AM - 11:00 PM'),
    ('delivery_fee', '40.00'),
    ('free_delivery_above', '499.00'),
    ('currency_symbol', '₹');

-- A sample launch coupon
INSERT INTO coupons (code, type, value, min_order, max_discount, usage_limit, per_user_limit, first_order_only, status)
VALUES ('WELCOME10', 'percentage', 10.00, 199.00, 100.00, 500, 1, 1, 'active');
