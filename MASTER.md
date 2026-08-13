# FOOD FACTORY --- MASTER DEVELOPMENT PROMPT

## Restaurant Ordering Platform + Admin Panel + Production-Ready Upgrade Specification

> **Purpose:** This document is the master implementation
> prompt/specification for upgrading the existing **Food Factory** PHP
> restaurant website into a complete, modern, database-driven restaurant
> ordering and administration platform.
>
> **Important:** Treat the existing project as the starting point. Do
> not blindly replace working frontend content. Preserve the restaurant
> identity and existing public pages while rebuilding the backend,
> ordering flow, data model, security, validation, UX, and
> administration features properly.

------------------------------------------------------------------------

# 1. PROJECT CONTEXT

The uploaded project is a simple PHP restaurant website named **Food
Factory**.

Current public pages/features include:

-   `index.php` --- Home page
-   `about.php` --- About page
-   `menu.php` --- Static menu page
-   `review.php` --- Reviews + review form
-   `gallery.php` --- Gallery
-   `reservation.php` --- Table reservation form
-   `reservation_success.php` --- Reservation confirmation
-   `contact.php` --- Contact form
-   `contact_success.php` --- Contact confirmation
-   `order.php` --- currently empty and must be transformed into the
    complete ordering system
-   `script.js` --- mobile navigation toggle
-   `style4.css` --- shared frontend styling
-   `reservations.txt` --- current reservation storage
-   `messages.txt` --- current contact-message storage
-   Food/image assets such as pizza, burger, pasta, coffee, fries,
    juice, dessert, restaurant interior and chef images.

Current menu examples:

  Item                Current Price
  ----------------- ---------------
  Cheese Pizza                 ₹299
  Veg Burger                   ₹199
  Mix Sauce Pasta              ₹249
  Cold Coffee                  ₹129
  French Fries                 ₹149
  Fresh Juice                   ₹99

Current reservation fields:

-   Full name
-   Email
-   Phone
-   Date
-   Time
-   Number of guests

Current contact fields:

-   Name
-   Email
-   Message

Current review fields:

-   Name
-   Rating
-   Review text

The current application stores reservations/messages in text files. The
upgraded system must move to a proper relational database and structured
application architecture.

------------------------------------------------------------------------

# 2. PRIMARY GOAL

Build a complete restaurant management and online ordering system around
the existing Food Factory website.

The final application should support:

1.  Customer-facing restaurant website
2.  Online food ordering
3.  Shopping cart
4.  Checkout
5.  Customer accounts
6.  Guest checkout
7.  Order tracking
8.  Order history
9.  Table reservations
10. Reviews and ratings
11. Contact/support messages
12. Coupon and discount management
13. Menu/category/item management
14. Inventory/stock management
15. Restaurant settings
16. Staff/admin accounts
17. Role-based permissions
18. Full admin dashboard
19. Order management
20. Reservation management
21. Customer management
22. Review moderation
23. Sales analytics
24. Reports
25. Notifications
26. Audit logs
27. Security controls
28. Responsive UI
29. Search/filter/sort functionality
30. Production-quality validation and error handling.

The application must feel like a real restaurant SaaS/admin system
rather than a college-project CRUD page.

------------------------------------------------------------------------

# 3. CORE DEVELOPMENT PRINCIPLES

Follow these principles throughout implementation:

-   Preserve the Food Factory branding.
-   Keep the public website visually attractive.
-   Make the application fully responsive.
-   Use reusable components wherever possible.
-   Avoid duplicated HTML/PHP.
-   Use a proper database instead of text-file persistence.
-   Use PDO and prepared statements.
-   Never concatenate untrusted user input into SQL.
-   Validate input on both client and server.
-   Escape output to prevent XSS.
-   Use CSRF protection for state-changing forms.
-   Use secure password hashing.
-   Use sessions securely.
-   Never store plaintext passwords.
-   Never expose database credentials in public files.
-   Keep configuration in a protected environment/config layer.
-   Use role-based access control.
-   Log important administrative actions.
-   Build graceful error pages.
-   Do not expose PHP warnings, SQL errors, stack traces or secrets to
    customers.
-   Use transactions for operations that modify multiple related
    records.
-   Maintain order history even when menu items later change.
-   Never calculate order totals only from frontend-submitted prices.
-   Always recalculate prices server-side from database records.
-   Make all important operations auditable.

------------------------------------------------------------------------

# 4. RECOMMENDED TECHNOLOGY

If the existing environment must remain PHP-based, implement using:

### Backend

-   PHP 8.2+
-   PDO
-   MySQL 8+ / MariaDB
-   PHP sessions
-   REST-style internal endpoints where useful

### Frontend

-   HTML5
-   CSS3
-   Modern responsive layout
-   Vanilla JavaScript or a lightweight frontend layer
-   AJAX/fetch for dynamic cart/order/admin actions
-   Accessible forms and controls

### Optional libraries

Use established libraries only when they provide real value:

-   Chart.js for dashboard charts
-   PHPMailer for email notifications
-   a trusted payment SDK/gateway integration
-   a UUID/ULID library if required

Do not introduce a large framework unless the existing deployment
environment supports it and the migration is justified.

------------------------------------------------------------------------

# 5. PROJECT ARCHITECTURE

Refactor the project toward a maintainable structure such as:

``` text
food-factory/
│
├── public/
│   ├── index.php
│   ├── about.php
│   ├── menu.php
│   ├── order.php
│   ├── cart.php
│   ├── checkout.php
│   ├── orders.php
│   ├── order-details.php
│   ├── review.php
│   ├── gallery.php
│   ├── reservation.php
│   ├── contact.php
│   └── ...
│
├── admin/
│   ├── index.php
│   ├── login.php
│   ├── dashboard.php
│   ├── orders/
│   ├── menu/
│   ├── categories/
│   ├── customers/
│   ├── reservations/
│   ├── reviews/
│   ├── messages/
│   ├── coupons/
│   ├── inventory/
│   ├── reports/
│   ├── staff/
│   ├── settings/
│   ├── notifications/
│   └── audit-logs/
│
├── config/
│   ├── database.php
│   ├── app.php
│   └── permissions.php
│
├── includes/
│   ├── auth.php
│   ├── csrf.php
│   ├── functions.php
│   ├── validation.php
│   ├── header.php
│   ├── footer.php
│   └── admin-header.php
│
├── api/
│   ├── cart/
│   ├── orders/
│   ├── menu/
│   ├── reservations/
│   └── notifications/
│
├── assets/
│   ├── css/
│   ├── js/
│   ├── images/
│   └── uploads/
│
├── database/
│   ├── schema.sql
│   ├── seed.sql
│   └── migrations/
│
├── storage/
│   ├── logs/
│   └── uploads/
│
├── .env.example
├── README.md
└── MASTER.md
```

If keeping the current flat-file structure temporarily, create the
database migration first and then migrate existing records.

------------------------------------------------------------------------

# 6. DATABASE DESIGN

Create a normalized relational database.

Minimum tables:

## users

Fields:

-   id
-   first_name
-   last_name
-   email
-   phone
-   password_hash
-   role_id
-   status
-   email_verified_at
-   last_login_at
-   created_at
-   updated_at

Roles:

-   customer
-   staff
-   manager
-   admin
-   super_admin

------------------------------------------------------------------------

## roles

Fields:

-   id
-   name
-   description
-   created_at

------------------------------------------------------------------------

## permissions

Fields:

-   id
-   permission_key
-   description

Examples:

``` text
dashboard.view
orders.view
orders.create
orders.update
orders.cancel
orders.refund
menu.view
menu.create
menu.update
menu.delete
inventory.view
inventory.update
customers.view
customers.update
reservations.view
reservations.update
reviews.moderate
reports.view
coupons.manage
settings.manage
staff.manage
audit.view
```

------------------------------------------------------------------------

## role_permissions

Fields:

-   role_id
-   permission_id

------------------------------------------------------------------------

## categories

Fields:

-   id
-   name
-   slug
-   description
-   image
-   display_order
-   status
-   created_at
-   updated_at

Example categories:

-   Pizza
-   Burgers
-   Pasta
-   Snacks
-   Beverages
-   Desserts
-   Combos
-   Special Offers

------------------------------------------------------------------------

## menu_items

Fields:

-   id
-   category_id
-   name
-   slug
-   description
-   image
-   price
-   compare_at_price
-   cost_price
-   tax_rate
-   sku
-   preparation_time
-   calories
-   vegetarian
-   vegan
-   spicy_level
-   featured
-   bestseller
-   available
-   stock_tracking_enabled
-   status
-   created_at
-   updated_at

------------------------------------------------------------------------

## menu_item_variants

Support sizes/options:

-   id
-   menu_item_id
-   name
-   price
-   sku
-   stock
-   status

Examples:

``` text
Small
Medium
Large
```

------------------------------------------------------------------------

## addons

Fields:

-   id
-   name
-   price
-   description
-   status

Examples:

-   Extra Cheese
-   Jalapeno
-   Extra Sauce
-   Extra Patty
-   Ice
-   Whipped Cream

------------------------------------------------------------------------

## menu_item_addons

Fields:

-   menu_item_id
-   addon_id
-   max_quantity
-   required

------------------------------------------------------------------------

## carts

Fields:

-   id
-   user_id nullable
-   session_id nullable
-   expires_at
-   created_at
-   updated_at

------------------------------------------------------------------------

## cart_items

Fields:

-   id
-   cart_id
-   menu_item_id
-   variant_id nullable
-   quantity
-   unit_price
-   special_instructions
-   created_at
-   updated_at

------------------------------------------------------------------------

## cart_item_addons

Fields:

-   id
-   cart_item_id
-   addon_id
-   quantity
-   unit_price

------------------------------------------------------------------------

## orders

Fields:

-   id
-   order_number
-   user_id nullable
-   customer_name
-   customer_email
-   customer_phone
-   order_type
-   delivery_address
-   delivery_latitude nullable
-   delivery_longitude nullable
-   subtotal
-   discount_amount
-   tax_amount
-   delivery_fee
-   packaging_fee
-   service_fee
-   tip_amount
-   total_amount
-   coupon_id nullable
-   payment_method
-   payment_status
-   order_status
-   customer_note
-   estimated_delivery_minutes
-   placed_at
-   accepted_at
-   preparing_at
-   ready_at
-   dispatched_at
-   delivered_at
-   cancelled_at
-   cancellation_reason
-   created_at
-   updated_at

Order types:

-   delivery
-   pickup
-   dine_in

Order statuses:

``` text
pending
confirmed
preparing
ready
out_for_delivery
delivered
completed
cancelled
rejected
refunded
```

------------------------------------------------------------------------

## order_items

Important: preserve a snapshot of the purchased item.

Fields:

-   id
-   order_id
-   menu_item_id nullable
-   item_name_snapshot
-   item_description_snapshot
-   variant_name_snapshot
-   quantity
-   unit_price
-   tax_amount
-   discount_amount
-   total_price

------------------------------------------------------------------------

## order_item_addons

Fields:

-   id
-   order_item_id
-   addon_name_snapshot
-   quantity
-   unit_price
-   total_price

------------------------------------------------------------------------

## payments

Fields:

-   id
-   order_id
-   transaction_id
-   provider
-   method
-   amount
-   currency
-   status
-   gateway_response
-   paid_at
-   created_at

------------------------------------------------------------------------

## coupons

Fields:

-   id
-   code
-   name
-   description
-   discount_type
-   discount_value
-   minimum_order_amount
-   maximum_discount
-   usage_limit
-   per_user_limit
-   used_count
-   starts_at
-   expires_at
-   active
-   created_at
-   updated_at

Discount types:

-   percentage
-   fixed
-   free_delivery

------------------------------------------------------------------------

## coupon_usages

Fields:

-   id
-   coupon_id
-   user_id nullable
-   order_id
-   discount_amount
-   used_at

------------------------------------------------------------------------

## reservations

Replace `reservations.txt`.

Fields:

-   id
-   reservation_number
-   user_id nullable
-   name
-   email
-   phone
-   date
-   time
-   guests
-   table_id nullable
-   special_request
-   status
-   confirmation_code
-   created_at
-   updated_at

Statuses:

-   pending
-   confirmed
-   seated
-   completed
-   cancelled
-   no_show
-   rejected

------------------------------------------------------------------------

## tables

Fields:

-   id
-   table_number
-   capacity
-   location
-   status
-   notes

------------------------------------------------------------------------

## reviews

Replace static reviews with database-driven reviews.

Fields:

-   id
-   user_id nullable
-   order_id nullable
-   menu_item_id nullable
-   name
-   rating
-   title
-   review
-   admin_reply
-   status
-   verified_purchase
-   created_at
-   updated_at

Statuses:

-   pending
-   approved
-   rejected
-   hidden

------------------------------------------------------------------------

## contact_messages

Replace `messages.txt`.

Fields:

-   id
-   name
-   email
-   phone nullable
-   subject
-   message
-   status
-   assigned_to nullable
-   admin_notes
-   replied_at
-   created_at
-   updated_at

Statuses:

-   new
-   read
-   in_progress
-   resolved
-   spam

------------------------------------------------------------------------

## inventory_items

Fields:

-   id
-   name
-   sku
-   unit
-   current_stock
-   minimum_stock
-   reorder_level
-   cost_per_unit
-   supplier
-   status
-   created_at
-   updated_at

------------------------------------------------------------------------

## inventory_movements

Fields:

-   id
-   inventory_item_id
-   movement_type
-   quantity
-   previous_stock
-   new_stock
-   reference_type
-   reference_id
-   note
-   created_by
-   created_at

Movement types:

-   purchase
-   sale
-   adjustment
-   waste
-   return
-   restock

------------------------------------------------------------------------

## notifications

Fields:

-   id
-   user_id
-   type
-   title
-   message
-   link
-   read_at
-   created_at

------------------------------------------------------------------------

## favorites

Fields:

-   id
-   user_id
-   menu_item_id
-   created_at

------------------------------------------------------------------------

## addresses

Fields:

-   id
-   user_id
-   label
-   recipient_name
-   phone
-   address_line_1
-   address_line_2
-   landmark
-   city
-   state
-   postal_code
-   latitude
-   longitude
-   is_default
-   created_at
-   updated_at

------------------------------------------------------------------------

## audit_logs

Fields:

-   id
-   user_id
-   action
-   entity_type
-   entity_id
-   old_values
-   new_values
-   ip_address
-   user_agent
-   created_at

Record critical admin actions.

------------------------------------------------------------------------

# 7. CUSTOMER ORDERING SYSTEM

The currently empty `order.php` must become a complete online ordering
experience.

## Ordering flow

``` text
Menu
  ↓
Category/filter/search
  ↓
Select item
  ↓
Select variant
  ↓
Select addons
  ↓
Set quantity
  ↓
Add special instructions
  ↓
Add to cart
  ↓
Cart
  ↓
Apply coupon
  ↓
Choose Delivery / Pickup / Dine-in
  ↓
Customer information
  ↓
Address if delivery
  ↓
Payment method
  ↓
Order confirmation
  ↓
Order tracking
```

------------------------------------------------------------------------

# 8. MENU EXPERIENCE

Create a professional ordering menu.

Features:

-   Category tabs
-   Search
-   Price filter
-   Vegetarian filter
-   Vegan filter
-   Spicy filter
-   Bestseller filter
-   Featured items
-   Availability status
-   Item cards
-   Item details modal/page
-   Add-to-cart button
-   Quantity controls
-   Add-ons
-   Variants
-   Special instructions
-   Favorite button
-   Rating display
-   Review count
-   Preparation time
-   "Sold Out" state
-   "New" badge
-   "Bestseller" badge
-   "Chef Special" badge

Example item card:

``` text
[Image]

Bestseller
Cheese Pizza

★★★★★ 4.8 (124)

Fresh mozzarella, tomato sauce and crispy crust.

₹299

[Customize] [Add to Cart]
```

------------------------------------------------------------------------

# 9. PRODUCT CUSTOMIZATION

When a customer clicks an item, show a customization modal/page.

Support:

### Size

-   Small
-   Medium
-   Large

### Add-ons

-   Extra cheese
-   Jalapeno
-   Olives
-   Extra sauce
-   Extra patty
-   etc.

### Quantity

-   minus
-   quantity
-   plus

### Special instructions

Example:

> "Please make it less spicy."

Display live price calculation.

------------------------------------------------------------------------

# 10. SHOPPING CART

Cart must support:

-   Add item
-   Remove item
-   Increase quantity
-   Decrease quantity
-   Change variant
-   Add/remove addons
-   Special instructions
-   Coupon
-   Subtotal
-   Discount
-   Tax
-   Delivery fee
-   Packaging fee
-   Service fee
-   Tip
-   Grand total

Cart should update dynamically without unnecessary page reloads.

Show:

``` text
Subtotal
Discount
Tax
Delivery
Packaging
Tip
----------------
Total
```

Use server-side recalculation for final totals.

------------------------------------------------------------------------

# 11. CHECKOUT

Checkout must be professional and secure.

Sections:

## Customer

-   Name
-   Email
-   Phone

## Order type

-   Delivery
-   Pickup
-   Dine-in

## Delivery address

If delivery:

-   Recipient
-   Phone
-   Address
-   Landmark
-   City
-   State
-   Postal code

Allow saved addresses for logged-in customers.

## Pickup

Show:

-   Restaurant address
-   Expected ready time

## Dine-in

Allow:

-   Table number / reservation reference
-   Guest count
-   Optional note

## Payment

Support architecture for:

-   Cash on Delivery
-   Cash at pickup
-   UPI
-   Card
-   Online payment gateway

Do not claim payment success until verified server-side.

------------------------------------------------------------------------

# 12. ORDER CONFIRMATION

After successful order:

Show:

``` text
🎉 Order Confirmed!

Order #FF-2026-000123

Thank you, Kaushal!

Estimated preparation:
25–35 minutes

Order total:
₹699
```

Actions:

-   Track Order
-   View Order Details
-   Download Invoice
-   Continue Shopping

------------------------------------------------------------------------

# 13. ORDER TRACKING

Create a visual timeline:

``` text
✓ Order Placed
    ↓
✓ Confirmed
    ↓
● Preparing
    ↓
○ Ready
    ↓
○ Out for Delivery
    ↓
○ Delivered
```

For pickup:

``` text
Order Placed
Confirmed
Preparing
Ready for Pickup
Picked Up
```

For dine-in:

``` text
Order Placed
Confirmed
Preparing
Ready
Served
Completed
```

------------------------------------------------------------------------

# 14. CUSTOMER ACCOUNT

Create a customer dashboard.

Sections:

-   Overview
-   My Orders
-   Order Details
-   Track Order
-   Saved Addresses
-   Favorites
-   Reviews
-   Reservations
-   Coupons
-   Notifications
-   Profile
-   Password
-   Logout

------------------------------------------------------------------------

# 15. CUSTOMER ORDER HISTORY

Display:

-   Order number
-   Date
-   Items
-   Total
-   Payment status
-   Order status
-   Reorder button
-   View details
-   Download invoice

Reorder should recreate the cart from currently available products and
current prices rather than blindly restoring old prices.

------------------------------------------------------------------------

# 16. ADMIN PANEL --- MASTER REQUIREMENT

Create a completely separate professional admin panel.

Suggested route:

``` text
/admin/
```

Admin login must be required for all protected pages.

Admin panel should have:

-   Sidebar
-   Topbar
-   Search
-   Notifications
-   User profile
-   Breadcrumbs
-   Responsive sidebar
-   Mobile navigation
-   Dark/light theme if practical
-   Dashboard widgets
-   Tables
-   Filters
-   Pagination
-   Bulk actions
-   Modals
-   Toast notifications
-   Confirmation dialogs

------------------------------------------------------------------------

# 17. ADMIN DASHBOARD

Dashboard should provide a high-level business overview.

## KPI cards

Display:

-   Today's Sales
-   Today's Orders
-   Pending Orders
-   Completed Orders
-   Cancelled Orders
-   Today's Reservations
-   Total Customers
-   Average Order Value
-   Low Stock Items
-   New Messages
-   Pending Reviews

## Sales chart

Allow:

-   Today
-   7 days
-   30 days
-   90 days
-   Custom range

Charts:

-   Revenue
-   Orders
-   Average order value

## Order status chart

Show:

-   Pending
-   Confirmed
-   Preparing
-   Ready
-   Delivered
-   Cancelled

## Top products

Display:

-   Product
-   Units sold
-   Revenue

## Recent orders

Columns:

-   Order number
-   Customer
-   Type
-   Total
-   Payment
-   Status
-   Date
-   Actions

## Recent reservations

Display:

-   Customer
-   Date
-   Time
-   Guests
-   Status

------------------------------------------------------------------------

# 18. ADMIN ORDER MANAGEMENT

Create a powerful order-management page.

Features:

-   List orders
-   Search order number
-   Search customer
-   Filter status
-   Filter payment
-   Filter order type
-   Date range
-   Sort
-   Pagination
-   View order
-   Update status
-   Cancel order
-   Refund workflow
-   Print order
-   Print kitchen ticket
-   Download invoice
-   Add internal note
-   Customer note
-   Assign staff
-   Timeline

Order detail page should show:

``` text
Order information
Customer information
Items
Variants
Addons
Pricing
Payment
Delivery/pickup information
Status timeline
Internal notes
Audit history
```

------------------------------------------------------------------------

# 19. KITCHEN ORDER MANAGEMENT / KDS

Add a Kitchen Display System.

Route example:

``` text
/admin/kitchen.php
```

Columns:

``` text
NEW
CONFIRMED
PREPARING
READY
```

Each order card:

-   Order number
-   Customer
-   Items
-   Modifiers
-   Special instructions
-   Time elapsed
-   Priority
-   Order type

Buttons:

-   Accept
-   Start Preparing
-   Mark Ready
-   Complete

Add overdue highlighting based on preparation time.

Optional sound notification for new orders.

------------------------------------------------------------------------

# 20. MENU MANAGEMENT

Admin must be able to:

-   Add category
-   Edit category
-   Delete/deactivate category
-   Reorder categories
-   Add menu item
-   Edit menu item
-   Delete/deactivate menu item
-   Upload image
-   Set price
-   Set tax
-   Set SKU
-   Set preparation time
-   Mark featured
-   Mark bestseller
-   Mark spicy
-   Mark vegetarian
-   Mark vegan
-   Mark available/unavailable
-   Configure variants
-   Configure addons

Never hard-delete products that are referenced by historical orders
unless the data model safely preserves historical snapshots.

Prefer:

``` text
active = false
```

for discontinued items.

------------------------------------------------------------------------

# 21. INVENTORY MANAGEMENT

Create an inventory system.

Features:

-   Inventory dashboard
-   Ingredient/product stock
-   Current stock
-   Low-stock alerts
-   Out-of-stock alerts
-   Stock adjustment
-   Restock
-   Waste tracking
-   Supplier field
-   Cost tracking
-   Stock movement history

Example:

``` text
Cheese
Current: 8 kg
Minimum: 5 kg
Status: Healthy
```

When possible, support recipe-based inventory deduction.

Example:

``` text
Cheese Pizza
requires:
150g cheese
100g sauce
1 pizza base
```

When an order is completed, deduct inventory according to configured
recipe quantities.

------------------------------------------------------------------------

# 22. RESERVATION MANAGEMENT

Admin page:

``` text
/admin/reservations/
```

Features:

-   Calendar view
-   List view
-   Search
-   Date filter
-   Status filter
-   Guest count
-   Customer details
-   Confirm
-   Reject
-   Seat
-   Complete
-   Cancel
-   Mark no-show
-   Add internal notes

Add table management:

-   Table number
-   Capacity
-   Location
-   Status

Prevent obvious double booking.

------------------------------------------------------------------------

# 23. CUSTOMER MANAGEMENT

Admin customer list:

-   Name
-   Email
-   Phone
-   Registration date
-   Orders
-   Total spent
-   Last order
-   Reservations
-   Status

Customer profile:

``` text
Customer information
Order history
Reservation history
Reviews
Saved addresses
Total spending
Average order value
Last activity
```

Admin should not be able to see or modify passwords.

------------------------------------------------------------------------

# 24. REVIEW MANAGEMENT

Admin can:

-   View reviews
-   Approve
-   Reject
-   Hide
-   Restore
-   Reply
-   Filter by rating
-   Filter verified purchases
-   Search customer

Public reviews should display only approved reviews.

Add verified purchase badge when a review is connected to a completed
order.

------------------------------------------------------------------------

# 25. CONTACT MESSAGE MANAGEMENT

Replace `messages.txt`.

Admin can:

-   View messages
-   Search
-   Filter
-   Mark read
-   Mark in progress
-   Mark resolved
-   Mark spam
-   Assign staff
-   Add internal note
-   Reply via email if email integration exists

------------------------------------------------------------------------

# 26. COUPON SYSTEM

Create a flexible coupon engine.

Support:

### Percentage

``` text
FOOD20 = 20% off
```

### Fixed

``` text
WELCOME100 = ₹100 off
```

### Free delivery

``` text
FREEDELIVERY
```

Rules:

-   Minimum order
-   Maximum discount
-   Start date
-   End date
-   Usage limit
-   Per-user limit
-   Active/inactive
-   Category restrictions
-   Product restrictions
-   First-order-only
-   Customer eligibility

Validate all coupon conditions server-side.

------------------------------------------------------------------------

# 27. PROMOTIONS

Add promotional features:

-   Featured products
-   Homepage banners
-   Limited-time offers
-   Combo deals
-   Buy-one-get-one
-   Happy-hour pricing
-   Weekend offers
-   First-order discount
-   Free-delivery campaign

Admin should control these from the panel.

------------------------------------------------------------------------

# 28. REPORTING

Create a Reports section.

Reports:

## Sales report

-   Gross sales
-   Discounts
-   Taxes
-   Delivery fees
-   Net sales
-   Number of orders
-   Average order value

## Product report

-   Best sellers
-   Worst sellers
-   Units sold
-   Revenue

## Customer report

-   New customers
-   Returning customers
-   Top customers
-   Customer lifetime value

## Reservation report

-   Total reservations
-   Completed
-   Cancelled
-   No-show
-   Average guests

## Payment report

-   Cash
-   UPI
-   Card
-   Other

## Inventory report

-   Low stock
-   Stock movement
-   Waste
-   Purchase cost

Add export:

-   CSV
-   Excel-compatible CSV
-   PDF if practical

------------------------------------------------------------------------

# 29. NOTIFICATION SYSTEM

Create notifications for:

### Customer

-   Order confirmed
-   Order preparing
-   Order ready
-   Order dispatched
-   Order delivered
-   Order cancelled
-   Reservation confirmed
-   Reservation cancelled
-   Coupon available

### Admin

-   New order
-   New reservation
-   New review
-   New contact message
-   Low stock
-   Payment failure
-   Refund request

Use in-app notifications first.

Optional:

-   Email
-   SMS
-   WhatsApp
-   Browser notifications

External integrations should be configurable and never hardcoded.

------------------------------------------------------------------------

# 30. AUTHENTICATION

Customer authentication:

-   Register
-   Login
-   Logout
-   Forgot password
-   Reset password
-   Change password
-   Email verification if email service exists

Admin authentication:

-   Separate admin login
-   Role-based permissions
-   Session timeout
-   Secure logout
-   Optional 2FA architecture
-   Login attempt protection
-   Audit logging

Use:

``` php
password_hash()
password_verify()
```

Never use MD5/SHA1 for passwords.

------------------------------------------------------------------------

# 31. SECURITY

Mandatory security requirements:

## SQL injection

Use PDO prepared statements.

## XSS

Escape output using appropriate HTML escaping.

## CSRF

Use CSRF tokens for:

-   Login-sensitive actions where applicable
-   Profile changes
-   Orders
-   Reviews
-   Reservations
-   Admin mutations
-   Delete/update operations

## Session security

Use:

-   Secure cookies
-   HttpOnly
-   SameSite
-   Session regeneration after login

## File uploads

Validate:

-   MIME type
-   File extension
-   File size
-   Image dimensions

Never execute uploaded files.

Store uploads outside executable PHP directories when possible.

## Authorization

Never rely only on hidden buttons.

Every protected action must verify permission server-side.

------------------------------------------------------------------------

# 32. INPUT VALIDATION

Validate:

### Name

Reasonable length and character rules.

### Email

Use valid email validation.

### Phone

Validate Indian phone numbers where appropriate, but do not make
validation unnecessarily restrictive.

### Price

Positive decimal.

### Quantity

Integer with sensible max.

### Date/time

Reject invalid/past dates where business rules require.

### Rating

Integer 1--5.

### Order status

Only allow valid state transitions.

------------------------------------------------------------------------

# 33. ORDER STATE MACHINE

Do not allow arbitrary status changes.

Recommended flow:

``` text
pending
  ↓
confirmed
  ↓
preparing
  ↓
ready
  ↓
out_for_delivery
  ↓
delivered
  ↓
completed
```

Cancellation:

``` text
pending → cancelled
confirmed → cancelled
preparing → cancelled (permission required)
```

Refund:

``` text
completed → refunded
```

Prevent invalid transitions.

------------------------------------------------------------------------

# 34. PRICING ENGINE

The server must calculate:

``` text
item subtotal
+ addon total
+ delivery fee
+ packaging fee
+ service fee
+ tax
+ tip
- coupon discount
= final total
```

Do not trust:

-   frontend subtotal
-   frontend discount
-   frontend tax
-   frontend item price

All totals must be recomputed on the server.

Handle rounding consistently.

------------------------------------------------------------------------

# 35. DELIVERY FEATURES

Admin settings should control:

-   Delivery enabled
-   Delivery fee
-   Free delivery threshold
-   Delivery radius
-   Estimated delivery time
-   Minimum delivery order
-   Delivery zones

Optional advanced feature:

``` text
Zone A → ₹30
Zone B → ₹50
Zone C → ₹70
```

Do not claim real-time maps or live driver tracking unless an actual
mapping/driver integration exists.

------------------------------------------------------------------------

# 36. PICKUP FEATURES

Support:

-   Pickup order
-   Estimated pickup time
-   Customer pickup note
-   Ready notification
-   Pickup completion

Optional:

-   Scheduled pickup time

------------------------------------------------------------------------

# 37. DINE-IN ORDERING

Add optional QR-based table ordering.

Each table can have:

``` text
table_id
table_number
QR code
```

Customer scans QR:

``` text
Table 12
↓
Menu
↓
Order
↓
Kitchen
↓
Serve
```

Admin can see table number on the order.

------------------------------------------------------------------------

# 38. INVOICE SYSTEM

Generate professional invoices.

Invoice must include:

-   Food Factory logo
-   Restaurant details
-   Invoice/order number
-   Customer
-   Items
-   Quantity
-   Price
-   Tax
-   Discount
-   Total
-   Payment status
-   Order type
-   Date/time

Provide:

-   Print invoice
-   Download invoice

------------------------------------------------------------------------

# 39. ADMIN SETTINGS

Create a comprehensive settings module.

## Restaurant

-   Restaurant name
-   Logo
-   Address
-   Phone
-   Email
-   GST/tax information if applicable
-   Currency
-   Timezone

## Business hours

Configure each day:

``` text
Monday
10:00 AM – 11:00 PM
```

Support closed days.

## Ordering

-   Online ordering on/off
-   Delivery on/off
-   Pickup on/off
-   Dine-in on/off
-   Minimum order
-   Delivery fee
-   Free delivery threshold

## Tax

-   Tax enabled
-   Tax rate
-   Tax label

## Payment

-   Cash
-   UPI
-   Card
-   Gateway settings

Never expose secret gateway credentials to frontend.

## Notifications

Enable/disable:

-   Email
-   SMS
-   In-app

------------------------------------------------------------------------

# 40. ADMIN SIDEBAR

Recommended navigation:

``` text
Dashboard

Orders
  ├── All Orders
  ├── Pending
  ├── Preparing
  ├── Ready
  ├── Completed
  └── Cancelled

Kitchen

Menu
  ├── Categories
  ├── Items
  ├── Variants
  └── Add-ons

Reservations
Tables

Customers

Reviews

Messages

Coupons
Promotions

Inventory
  ├── Stock
  ├── Movements
  └── Low Stock

Reports
  ├── Sales
  ├── Products
  ├── Customers
  └── Reservations

Staff
Roles
Permissions

Notifications

Settings

Audit Logs
```

------------------------------------------------------------------------

# 41. ADMIN UI DESIGN

The admin panel should look like a modern SaaS dashboard.

Design characteristics:

-   Clean spacing
-   Card-based KPI widgets
-   Professional tables
-   Responsive sidebar
-   Sticky header
-   Status badges
-   Confirmation dialogs
-   Toast notifications
-   Empty states
-   Loading states
-   Skeleton states where useful
-   Error states
-   Accessible forms
-   Keyboard navigation
-   Clear typography

Do not reuse the public website's black/brown layout directly for the
entire admin panel. The admin should have a distinct professional
management UI while still using Food Factory branding.

------------------------------------------------------------------------

# 42. RESPONSIVE DESIGN

Must work on:

-   Desktop
-   Laptop
-   Tablet
-   Mobile

Test widths approximately:

``` text
360px
390px
480px
768px
1024px
1280px
1440px+
```

Mobile requirements:

-   Hamburger navigation
-   Responsive cart
-   Sticky checkout summary where practical
-   Touch-friendly buttons
-   Responsive admin tables
-   Horizontal scrolling only where unavoidable
-   No broken layouts

------------------------------------------------------------------------

# 43. SEARCH AND FILTER SYSTEM

Implement reusable search/filter components.

Examples:

Orders:

``` text
Search
Status
Payment
Order Type
Date
```

Menu:

``` text
Search
Category
Price
Availability
Featured
```

Customers:

``` text
Search
Status
Registration date
```

Reservations:

``` text
Date
Status
Guests
Search
```

Reviews:

``` text
Rating
Status
Verified
Search
```

------------------------------------------------------------------------

# 44. PAGINATION

Use server-side pagination for large datasets.

Show:

``` text
Previous
1
2
3
...
Next
```

Allow configurable page size:

-   10
-   25
-   50
-   100

------------------------------------------------------------------------

# 45. BULK ACTIONS

Admin lists should support bulk actions where useful:

-   Bulk approve reviews
-   Bulk hide reviews
-   Bulk update availability
-   Bulk status updates where safe
-   Bulk export
-   Bulk archive messages

Always show confirmation before destructive actions.

------------------------------------------------------------------------

# 46. AUDIT LOGS

Record:

-   Login
-   Logout
-   Menu created
-   Menu updated
-   Menu disabled
-   Order status changed
-   Order cancelled
-   Refund initiated
-   Coupon created
-   Coupon changed
-   Customer status changed
-   Reservation changed
-   Review moderated
-   Settings changed
-   Staff permissions changed

Audit record should contain:

``` text
Who
What
When
Entity
Before
After
IP
User agent
```

------------------------------------------------------------------------

# 47. ERROR HANDLING

Create friendly pages:

-   404 Not Found
-   403 Forbidden
-   419/CSRF failure
-   500 Server Error

Production mode:

-   Do not display stack traces.
-   Log technical details server-side.
-   Show a user-friendly message.

Example:

``` text
Something went wrong.

We could not complete your request.
Please try again.
```

------------------------------------------------------------------------

# 48. DATABASE MIGRATION

Existing text files:

``` text
reservations.txt
messages.txt
```

must not remain the primary storage system.

Create migration logic that can import existing records.

Preserve existing contact/reservation information where possible.

After migration, disable direct text-file writes.

------------------------------------------------------------------------

# 49. DATA SEEDING

Create demo/seed data for:

-   Admin
-   Manager
-   Staff
-   Categories
-   Menu items
-   Add-ons
-   Variants
-   Coupons
-   Tables
-   Sample reviews

Use safe demo credentials documented in development setup only.

Never ship real passwords in production.

------------------------------------------------------------------------

# 50. IMAGE MANAGEMENT

Admin should be able to upload menu images.

Requirements:

-   Preview
-   Replace
-   Delete
-   Alt text
-   File size validation
-   Image type validation
-   Automatic filename generation
-   Safe storage

Use the existing images as seed/demo assets where appropriate.

------------------------------------------------------------------------

# 51. ACCESSIBILITY

Implement:

-   Proper labels
-   Form error messages
-   Alt text
-   Keyboard navigation
-   Focus states
-   Semantic HTML
-   Accessible modal behavior
-   Sufficient contrast
-   ARIA only where needed

Do not rely only on color to communicate status.

------------------------------------------------------------------------

# 52. SEO

Public website should include:

-   Unique title
-   Meta description
-   Open Graph metadata
-   Semantic headings
-   Descriptive image alt text
-   Clean URLs where possible
-   Restaurant structured data if practical

Add restaurant schema/structured data only with accurate information.

------------------------------------------------------------------------

# 53. PERFORMANCE

Optimize:

-   Images
-   CSS
-   JavaScript
-   Database queries
-   Pagination
-   API calls

Use:

-   Lazy loading for non-critical images
-   Proper image dimensions
-   Database indexes
-   Query optimization

Avoid loading all orders/customers/products when only 10 records are
shown.

------------------------------------------------------------------------

# 54. DATABASE INDEXES

Add indexes to frequently queried fields:

-   users.email
-   users.phone
-   orders.order_number
-   orders.user_id
-   orders.status
-   orders.created_at
-   orders.payment_status
-   reservations.date
-   reservations.status
-   menu_items.category_id
-   menu_items.slug
-   menu_items.status
-   reviews.status
-   coupons.code
-   audit_logs.created_at

Add unique constraints where required.

------------------------------------------------------------------------

# 55. ADMIN SEARCH

Global admin search should optionally find:

-   Order number
-   Customer
-   Phone
-   Email
-   Reservation
-   Menu item
-   Coupon

Results should indicate entity type.

------------------------------------------------------------------------

# 56. DASHBOARD QUICK ACTIONS

Add buttons:

``` text
+ New Menu Item
+ New Coupon
View Pending Orders
View Reservations
View Messages
View Low Stock
```

------------------------------------------------------------------------

# 57. CUSTOMER UX ENHANCEMENTS

Add:

-   Toast after adding to cart
-   Cart count in navbar
-   Sticky cart button on mobile
-   Recently viewed products
-   Favorites
-   Reorder
-   Coupon suggestions
-   Recommended items
-   "You may also like"
-   "Frequently ordered together"
-   Estimated preparation time

Recommendations should be simple rule-based initially; do not build an
unnecessary AI system.

------------------------------------------------------------------------

# 58. ORDER CONFIRMATION COMMUNICATION

After order placement:

1.  Save order
2.  Save order items
3.  Save payment information
4.  Update inventory where appropriate
5.  Create notification
6.  Send email if configured
7.  Show confirmation

Use a database transaction so partial orders are not created.

------------------------------------------------------------------------

# 59. TRANSACTION SAFETY

Order creation should follow:

``` text
BEGIN TRANSACTION

Validate customer
Validate items
Load current prices
Validate stock
Validate coupon
Calculate totals
Create order
Create order items
Create payment record
Create notification
Update stock if required

COMMIT
```

If any required operation fails:

``` text
ROLLBACK
```

------------------------------------------------------------------------

# 60. PAYMENT ARCHITECTURE

Design payment integration so the application can support a gateway
without coupling the entire system to one provider.

Example abstraction:

``` text
PaymentProviderInterface
    ├── CashPaymentProvider
    ├── UpiPaymentProvider
    └── OnlineGatewayProvider
```

Payment states:

``` text
pending
processing
paid
failed
cancelled
refunded
partially_refunded
```

Never trust a client-side "payment successful" flag.

------------------------------------------------------------------------

# 61. REFUND SYSTEM

Admin should be able to initiate refund according to permission.

Store:

-   Original payment
-   Refund amount
-   Refund reason
-   Provider transaction ID
-   Refund status
-   Requested by
-   Approved by
-   Timestamp

Do not automatically claim money has been refunded unless the payment
provider confirms it.

------------------------------------------------------------------------

# 62. FRAUD / ABUSE PROTECTION

Basic protection:

-   Rate limit login attempts
-   Rate limit contact/review submissions
-   Prevent duplicate form submissions
-   Validate coupons
-   Detect suspicious repeated checkout attempts
-   Require CSRF
-   Server-side authorization
-   Log suspicious actions

------------------------------------------------------------------------

# 63. DUPLICATE ORDER PROTECTION

Use an idempotency mechanism for checkout.

If a customer double-clicks "Place Order" or retries a request, do not
create duplicate orders.

Use an idempotency key tied to the checkout attempt.

------------------------------------------------------------------------

# 64. ADMIN NOTIFICATION CENTER

Topbar notification icon:

``` text
🔔 8
```

Example:

``` text
New order #FF-1023
2 minutes ago

Low stock: Cheese
10 minutes ago

New reservation
25 minutes ago
```

Allow:

-   Mark read
-   Mark all read
-   Open related record

------------------------------------------------------------------------

# 65. CUSTOMER NOTIFICATION CENTER

Customer should see:

-   Order updates
-   Reservation updates
-   Promotions
-   Coupon notifications

------------------------------------------------------------------------

# 66. REST/API-STYLE ENDPOINTS

Where dynamic frontend behavior is required, use clean endpoints.

Examples:

``` text
GET  /api/menu
GET  /api/menu/{id}

POST /api/cart/add
POST /api/cart/update
POST /api/cart/remove
GET  /api/cart

POST /api/coupons/validate

POST /api/orders
GET  /api/orders
GET  /api/orders/{id}

POST /api/orders/{id}/cancel

POST /api/reviews
POST /api/reservations
```

Admin endpoints should require admin authentication and permission
checks.

------------------------------------------------------------------------

# 67. API RESPONSE FORMAT

Use consistent JSON:

Success:

``` json
{
  "success": true,
  "message": "Order created successfully.",
  "data": {}
}
```

Error:

``` json
{
  "success": false,
  "message": "Unable to create order.",
  "errors": {}
}
```

Do not leak SQL errors or internal stack traces.

------------------------------------------------------------------------

# 68. FRONTEND FORM UX

Forms should have:

-   Labels
-   Required indicators
-   Inline validation
-   Loading state
-   Disabled submit during request
-   Success message
-   Error message
-   Server-side validation fallback

Example:

``` text
[Place Order]

Placing order...
```

Prevent multiple submissions.

------------------------------------------------------------------------

# 69. EMPTY STATES

Create meaningful empty states.

Example:

``` text
No orders found.

When customers place orders, they will appear here.
```

Buttons where useful:

``` text
View Menu
Create Menu Item
```

------------------------------------------------------------------------

# 70. LOADING STATES

Use:

-   Skeleton cards
-   Spinner
-   Disabled buttons
-   Progress indicators

Do not make the UI appear frozen during AJAX requests.

------------------------------------------------------------------------

# 71. ADMIN TABLE DESIGN

Every large table should support:

-   Search
-   Filter
-   Sort
-   Pagination
-   Row actions
-   Bulk actions where safe
-   Responsive behavior

Use status badges such as:

``` text
Pending
Confirmed
Preparing
Completed
Cancelled
```

------------------------------------------------------------------------

# 72. ADMIN DATA VALIDATION

Never allow admin to save:

-   Negative price
-   Invalid tax
-   Invalid stock
-   Empty required name
-   Duplicate SKU
-   Duplicate coupon code
-   Invalid date ranges

------------------------------------------------------------------------

# 73. BUSINESS RULES

Make business rules configurable where practical.

Examples:

``` text
Minimum order amount
Maximum order amount
Delivery fee
Free delivery threshold
Tax rate
Packaging fee
Service fee
Order cutoff time
Reservation advance window
Maximum guests
```

------------------------------------------------------------------------

# 74. RESERVATION RULES

Support configurable:

-   Minimum advance booking
-   Maximum advance booking
-   Opening hours
-   Table capacity
-   Maximum guests
-   Reservation duration
-   Buffer time
-   Blackout dates

Prevent reservations outside business hours.

------------------------------------------------------------------------

# 75. MENU AVAILABILITY

Allow admins to quickly toggle:

``` text
Available
Sold Out
Temporarily Unavailable
```

Customer UI should update accordingly.

If sold out:

``` text
Sold Out
```

instead of an active Add to Cart button.

------------------------------------------------------------------------

# 76. ORDER PRIORITY

Allow staff to mark:

-   Normal
-   High priority
-   Urgent

Show priority clearly in kitchen/admin.

------------------------------------------------------------------------

# 77. KITCHEN PRINTING

Optional feature:

-   Print kitchen ticket
-   Hide unnecessary customer billing information
-   Show item modifiers
-   Show special instructions
-   Show table/order type
-   Show order number

------------------------------------------------------------------------

# 78. DELIVERY STAFF SUPPORT

Optional future-ready module:

``` text
Delivery staff
Assigned order
Pickup time
Delivery status
Customer phone
Delivery address
```

Keep architecture ready for this without requiring real-time GPS
initially.

------------------------------------------------------------------------

# 79. STAFF MANAGEMENT

Admin can:

-   Add staff
-   Edit staff
-   Disable staff
-   Assign role
-   Reset password
-   View last login
-   View activity

Do not allow lower-level staff to modify their own permissions.

------------------------------------------------------------------------

# 80. ROLE PERMISSIONS EXAMPLE

### Customer

-   Browse menu
-   Order
-   View own orders
-   Review own eligible orders
-   Manage own profile

### Staff

-   View orders
-   Update order status
-   View reservations
-   View customers

### Manager

-   Staff capabilities
-   Menu management
-   Reservation management
-   Review moderation
-   Reports

### Admin

-   Full operational access
-   Coupons
-   Inventory
-   Settings

### Super Admin

-   Admin management
-   Roles
-   Permissions
-   Audit logs
-   System settings

------------------------------------------------------------------------

# 81. ADMIN PROFILE

Admin profile:

-   Name
-   Email
-   Phone
-   Role
-   Avatar
-   Last login
-   Change password
-   Security settings

------------------------------------------------------------------------

# 82. PUBLIC NAVIGATION

Update the existing navbar to include:

``` text
Home
About
Menu
Order
Reviews
Gallery
Reservation
Contact
```

For authenticated users:

``` text
Account
Cart
```

For admins:

``` text
Admin
```

Do not expose admin links to unauthorized users.

------------------------------------------------------------------------

# 83. CART NAVIGATION

Show:

``` text
🛒 Cart (3)
```

The cart count should update dynamically.

------------------------------------------------------------------------

# 84. FOOTER

Keep the current Food Factory footer but make it database/config driven.

Show:

-   Restaurant name
-   Tagline
-   Quick links
-   Contact
-   Business hours
-   Social links
-   Copyright
-   Privacy policy
-   Terms
-   Refund/cancellation policy

------------------------------------------------------------------------

# 85. LEGAL/POLICY PAGES

Add:

-   Privacy Policy
-   Terms & Conditions
-   Refund/Cancellation Policy
-   Delivery Policy
-   Cookie notice if required

Do not invent legal claims; use clearly editable placeholders for
business-specific policy details.

------------------------------------------------------------------------

# 86. ADMIN CONTENT MANAGEMENT

Allow admin to manage selected homepage content:

-   Hero title
-   Hero subtitle
-   Hero image
-   CTA labels
-   About text
-   Featured products
-   Homepage banners
-   Contact information
-   Opening hours

Keep core code structure stable.

------------------------------------------------------------------------

# 87. HOMEPAGE ENHANCEMENTS

Upgrade homepage with:

1.  Hero
2.  CTA buttons
3.  Featured categories
4.  Bestseller products
5.  Special offer banner
6.  About section
7.  Why choose us
8.  Popular items
9.  Customer reviews
10. Gallery preview
11. Reservation CTA
12. Newsletter/promotional signup if desired
13. Footer

------------------------------------------------------------------------

# 88. "WHY CHOOSE US"

Add cards:

``` text
Fresh Ingredients
Fast Service
Hygienic Kitchen
Affordable Prices
Friendly Staff
Quality Guaranteed
```

Keep claims accurate and editable.

------------------------------------------------------------------------

# 89. PRODUCT DETAIL PAGE

Each product should have:

-   Large image
-   Name
-   Rating
-   Description
-   Price
-   Variants
-   Add-ons
-   Quantity
-   Special instructions
-   Availability
-   Preparation time
-   Add to cart
-   Favorite
-   Reviews
-   Related products

------------------------------------------------------------------------

# 90. REVIEW ELIGIBILITY

Prefer allowing verified reviews only after a customer has a completed
order containing the relevant item.

If guest reviews remain supported, they must go through moderation.

------------------------------------------------------------------------

# 91. CUSTOMER PRIVACY

Customer data must be protected.

Admin should only see information necessary for business operations.

Avoid logging:

-   Passwords
-   Payment secrets
-   Full card numbers
-   Authentication tokens

------------------------------------------------------------------------

# 92. LOGGING

Application logs should include:

-   Error
-   Warning
-   Security event
-   Payment event
-   Order event
-   Admin action

Logs should not contain sensitive secrets.

------------------------------------------------------------------------

# 93. CONFIGURATION

Create `.env.example`:

``` env
APP_ENV=development
APP_DEBUG=true

DB_HOST=localhost
DB_NAME=food_factory
DB_USER=root
DB_PASSWORD=

APP_URL=http://localhost/food-factory

MAIL_HOST=
MAIL_PORT=
MAIL_USERNAME=
MAIL_PASSWORD=

PAYMENT_PROVIDER=
PAYMENT_KEY=
PAYMENT_SECRET=
```

Never commit real production secrets.

------------------------------------------------------------------------

# 94. INSTALLATION

README must explain:

1.  PHP requirements
2.  MySQL setup
3.  Database creation
4.  Import `schema.sql`
5.  Import `seed.sql`
6.  Configure `.env`
7.  Configure web server
8.  Configure uploads
9.  Configure permissions
10. Create admin account
11. Test checkout
12. Test reservation
13. Test contact form
14. Test review
15. Test admin login

------------------------------------------------------------------------

# 95. TESTING REQUIREMENTS

Test all major flows.

## Customer

-   Registration
-   Login
-   Logout
-   Browse menu
-   Search
-   Filter
-   Add to cart
-   Update cart
-   Remove item
-   Coupon
-   Checkout
-   Payment state
-   Order tracking
-   Order history
-   Reorder
-   Review
-   Reservation
-   Contact
-   Profile
-   Address management

## Admin

-   Login
-   Dashboard
-   Orders
-   Kitchen
-   Menu CRUD
-   Category CRUD
-   Addons
-   Variants
-   Inventory
-   Customers
-   Reservations
-   Tables
-   Reviews
-   Messages
-   Coupons
-   Promotions
-   Reports
-   Staff
-   Roles
-   Settings
-   Audit logs

------------------------------------------------------------------------

# 96. EDGE CASES

Handle:

-   Empty cart
-   Sold-out item during checkout
-   Item deleted after being added to cart
-   Price changed after cart creation
-   Expired coupon
-   Coupon usage exceeded
-   Invalid coupon
-   Duplicate checkout request
-   Payment failure
-   Payment timeout
-   Customer closes browser during payment
-   Reservation conflict
-   Restaurant closed
-   Invalid date
-   Invalid time
-   Unauthorized admin access
-   Expired session
-   CSRF failure
-   Database outage
-   Missing image
-   Invalid upload
-   Network timeout

------------------------------------------------------------------------

# 97. SECURITY TEST CHECKLIST

Test:

-   SQL injection
-   XSS
-   CSRF
-   Broken access control
-   Session fixation
-   Session hijacking protections
-   File upload vulnerabilities
-   IDOR
-   Brute-force login
-   Coupon abuse
-   Duplicate orders
-   Unauthorized refund
-   Unauthorized status change

------------------------------------------------------------------------

# 98. ADMIN DASHBOARD KPI CALCULATION

Clearly define metrics.

For example:

### Today's Sales

Sum successful/completed/paid orders according to configured business
rules for current restaurant day.

### Today's Orders

Count orders placed today excluding invalid test records.

### Average Order Value

``` text
sales / number of qualifying orders
```

### Customer Count

Count active registered customers.

Metrics should use indexed queries and appropriate date boundaries.

------------------------------------------------------------------------

# 99. BUSINESS DAY SUPPORT

Restaurant operating days may cross midnight.

The system should be designed so restaurant business hours can be
configured correctly.

Do not assume every restaurant day is exactly 00:00--23:59.

------------------------------------------------------------------------

# 100. TIMEZONE

Use a configured timezone.

For this restaurant's current setup, the default can be:

``` text
Asia/Kolkata
```

But keep it configurable.

Store timestamps consistently and display them in the configured
restaurant timezone.

------------------------------------------------------------------------

# 101. CURRENCY

Default:

``` text
INR ₹
```

Keep currency configurable.

------------------------------------------------------------------------

# 102. TAX

Tax implementation must be configurable.

Do not hardcode a tax percentage unless it is explicitly provided by the
restaurant administrator.

Support:

-   Tax label
-   Rate
-   Included/excluded pricing
-   Per-item or order-level calculation

------------------------------------------------------------------------

# 103. ORDER NUMBER

Generate human-friendly unique order numbers.

Example:

``` text
FF-2026-000001
FF-2026-000002
```

Do not expose raw database IDs as the only order identifier.

------------------------------------------------------------------------

# 104. RESERVATION NUMBER

Example:

``` text
RES-2026-000123
```

------------------------------------------------------------------------

# 105. INVOICE NUMBER

Example:

``` text
INV-2026-000123
```

Ensure uniqueness.

------------------------------------------------------------------------

# 106. ADMIN DATA EXPORT

Allow authorized admins to export:

-   Orders
-   Customers
-   Reservations
-   Reviews
-   Contact messages
-   Sales reports

Apply permissions and avoid exporting sensitive credentials.

------------------------------------------------------------------------

# 107. BACKUP STRATEGY

Document:

-   Database backup
-   Uploaded image backup
-   Configuration backup

Do not include `.env` secrets in public backups.

------------------------------------------------------------------------

# 108. MAINTAINABILITY

Use reusable functions/classes for:

-   Database access
-   Authentication
-   Authorization
-   Validation
-   Pricing
-   Coupons
-   Notifications
-   Order state transitions
-   Logging

Avoid copying business logic across pages.

------------------------------------------------------------------------

# 109. CODE QUALITY

The final implementation must:

-   Avoid undefined variables
-   Avoid duplicate IDs
-   Avoid invalid HTML
-   Avoid unused dead code
-   Avoid duplicated database queries
-   Avoid inline credentials
-   Avoid raw SQL interpolation
-   Avoid unsafe file handling
-   Avoid business logic hidden only in JavaScript

------------------------------------------------------------------------

# 110. MIGRATION OF CURRENT PROJECT

Specifically inspect and upgrade:

### `index.php`

Preserve:

-   Hero
-   About
-   Menu preview
-   Reviews
-   Footer

Upgrade:

-   Dynamic featured menu
-   Dynamic reviews
-   Cart access
-   Authentication
-   Promotions
-   Better CTA

### `about.php`

Preserve restaurant story but allow admin-managed content.

### `menu.php`

Convert static products into database-driven products.

### `order.php`

Completely implement the online ordering system.

### `review.php`

Convert static reviews into database-driven moderated reviews.

### `gallery.php`

Keep existing gallery images but optionally make gallery admin-managed.

### `reservation.php`

Connect to database and reservation management.

### `reservation_success.php`

Convert into a dynamic confirmation page.

### `contact.php`

Connect to database.

### `contact_success.php`

Show message confirmation and reference ID.

### `reservations.txt`

Migrate data to database.

### `messages.txt`

Migrate data to database.

### `style4.css`

Refactor into reusable public/admin styles or separate admin stylesheet.

### `script.js`

Expand for:

-   Mobile menu
-   Cart
-   AJAX
-   Modals
-   Toasts
-   Form validation
-   Dynamic UI

------------------------------------------------------------------------

# 111. ADMIN PAGE ACCESS RULE

Every admin page must begin with authentication and authorization
checks.

Conceptually:

``` php
requireAdmin();
requirePermission('orders.view');
```

Do not protect admin pages only through frontend navigation.

------------------------------------------------------------------------

# 112. DESTRUCTIVE ACTION RULE

For:

-   Delete
-   Cancel
-   Refund
-   Disable
-   Reject
-   Remove stock

require:

1.  Correct permission
2.  Server-side validation
3.  Confirmation
4.  Audit log

------------------------------------------------------------------------

# 113. NOTIFICATION UX

Use non-blocking toast notifications for normal operations:

``` text
✓ Item added to cart
✓ Menu item updated
✓ Order status updated
✕ Unable to save changes
```

Use confirmation modal for destructive actions.

------------------------------------------------------------------------

# 114. MOBILE ADMIN

Admin panel must remain usable on phones.

Convert large tables into:

-   responsive cards
-   horizontal scroll
-   expandable rows

Do not allow important actions to disappear on mobile.

------------------------------------------------------------------------

# 115. DARK MODE

Optional but recommended.

If implemented:

-   Public site dark mode
-   Admin dark mode
-   Persist preference
-   Maintain contrast/accessibility

------------------------------------------------------------------------

# 116. ANALYTICS

Admin dashboard can support:

-   Sales trend
-   Orders trend
-   Top categories
-   Top products
-   Repeat customer rate
-   Reservation trends
-   Peak ordering times

Avoid external analytics until privacy requirements are considered.

------------------------------------------------------------------------

# 117. RECOMMENDATION ENGINE

Start with deterministic recommendations:

``` text
If customer orders Pizza:
recommend Fries + Cold Coffee
```

Use:

-   Same category
-   Frequently purchased together
-   Bestseller
-   Similar price
-   Recently viewed

Keep recommendation logic replaceable.

------------------------------------------------------------------------

# 118. FAVORITES

Customers can favorite products.

UI:

``` text
♡ Add to favorites
♥ Favorited
```

Admin can see aggregate favorite counts.

------------------------------------------------------------------------

# 119. RECENTLY VIEWED

Store recently viewed products per user/session.

Limit list size, e.g.:

``` text
10 products
```

------------------------------------------------------------------------

# 120. CUSTOMER LOYALTY --- OPTIONAL ADVANCED FEATURE

Prepare architecture for:

-   Points
-   Rewards
-   Membership tiers

Example:

``` text
₹100 spent = 1 point
```

Do not hardcode final loyalty rules; make them configurable.

------------------------------------------------------------------------

# 121. GIFT / PROMO CODES --- OPTIONAL

Support future:

-   Gift cards
-   Store credits
-   Promotional vouchers

Keep these separate from standard coupon logic if introduced.

------------------------------------------------------------------------

# 122. SUBSCRIPTION / MEMBERSHIP --- FUTURE READY

Do not implement unless requested, but architecture should not prevent:

-   Premium membership
-   Free delivery membership
-   Monthly restaurant plans

------------------------------------------------------------------------

# 123. MULTI-BRANCH FUTURE SUPPORT

Optional future-ready design:

``` text
branches
branch_id on orders/menu/inventory/reservations
```

Do not implement multi-branch complexity unless needed, but avoid
hardcoding architecture that makes it impossible later.

------------------------------------------------------------------------

# 124. API SECURITY

If APIs are added:

-   Authenticate requests
-   Validate content type
-   CSRF for cookie-based browser APIs
-   Authorization
-   Rate limiting
-   Consistent responses
-   No secret exposure

------------------------------------------------------------------------

# 125. ADMIN AUDIT VIEW

Audit page filters:

-   User
-   Action
-   Entity
-   Date
-   IP

Example:

``` text
Admin: Rahul
Action: ORDER_STATUS_CHANGED
Order: FF-2026-000123
Old: preparing
New: ready
Time: 10 Aug 2026 10:20
```

------------------------------------------------------------------------

# 126. SYSTEM HEALTH

Admin-only system health page:

-   PHP version
-   Database connection
-   Storage availability
-   Upload directory
-   Mail configuration status
-   Application environment
-   Last backup timestamp if available

Do not expose secrets.

------------------------------------------------------------------------

# 127. CRON / SCHEDULED TASKS

Future-ready scheduled jobs:

-   Expire coupons
-   Expire carts
-   Send reminder notifications
-   Low-stock reminders
-   Reservation reminders
-   Cleanup temporary files
-   Generate periodic reports

------------------------------------------------------------------------

# 128. EMAIL TEMPLATES

Create reusable email templates for:

-   Welcome
-   Order confirmation
-   Order status update
-   Reservation confirmation
-   Reservation cancellation
-   Password reset
-   Contact response

Use placeholders:

``` text
{{customer_name}}
{{order_number}}
{{order_total}}
{{restaurant_name}}
```

------------------------------------------------------------------------

# 129. ADMIN EMAIL SETTINGS

Allow configured sender:

``` text
Food Factory
foodfactory@gmail.com
```

but store actual credentials securely.

------------------------------------------------------------------------

# 130. CUSTOMER EXPERIENCE TARGET

The final public site should feel like:

``` text
Modern restaurant website
+
Food delivery ordering platform
+
Reservation system
```

The final admin should feel like:

``` text
Modern restaurant operations dashboard
+
POS-style order management
+
Inventory
+
CRM
+
Analytics
```

------------------------------------------------------------------------

# 131. DEFINITION OF DONE

The project is NOT complete if only the UI exists.

It is complete only when:

-   Database exists
-   Authentication works
-   Authorization works
-   Menu is dynamic
-   Cart works
-   Checkout works
-   Orders persist
-   Order status works
-   Admin can manage orders
-   Kitchen screen works
-   Reservations persist
-   Reviews persist
-   Contact messages persist
-   Coupons work
-   Inventory works at the intended scope
-   Dashboard uses real database data
-   Reports work
-   Security protections are implemented
-   Validation works
-   Responsive design works
-   Error handling works
-   Existing content/assets are preserved appropriately
-   Installation documentation exists

------------------------------------------------------------------------

# 132. IMPLEMENTATION PHASES

Implement in phases rather than attempting an uncontrolled rewrite.

## Phase 1 --- Foundation

-   Database
-   Configuration
-   Shared PHP functions
-   Authentication
-   Roles
-   CSRF
-   Security
-   Shared layout

## Phase 2 --- Dynamic Menu

-   Categories
-   Menu items
-   Variants
-   Addons
-   Admin CRUD

## Phase 3 --- Ordering

-   Cart
-   Checkout
-   Pricing
-   Orders
-   Order details
-   Order tracking

## Phase 4 --- Admin Operations

-   Dashboard
-   Order management
-   Kitchen display
-   Customer management

## Phase 5 --- Reservations / Reviews / Messages

-   Database migration
-   Admin management
-   Moderation

## Phase 6 --- Business Features

-   Coupons
-   Promotions
-   Inventory
-   Reports

## Phase 7 --- Advanced Features

-   Notifications
-   Invoices
-   Favorites
-   Recommendations
-   QR dine-in
-   Optional payment gateway

## Phase 8 --- Hardening

-   Security audit
-   Performance
-   Responsive QA
-   Accessibility
-   Error handling
-   Backup documentation
-   Deployment documentation

------------------------------------------------------------------------

# 133. DEVELOPMENT WORKFLOW

Before coding:

1.  Inspect every existing file.
2.  Understand current public UI.
3.  Identify reusable assets.
4.  Identify broken/empty features.
5.  Design database.
6.  Create migration.
7.  Create shared architecture.
8.  Implement authentication.
9.  Implement menu.
10. Implement cart.
11. Implement checkout.
12. Implement admin.
13. Test each phase.

Do not destroy the existing site before understanding it.

------------------------------------------------------------------------

# 134. MASTER IMPLEMENTATION PROMPT

Use the following as the actual prompt for an AI coding agent/developer:

------------------------------------------------------------------------

## MASTER PROMPT START

You are a senior full-stack PHP/MySQL engineer, UI/UX designer, security
engineer and restaurant-management-system architect.

You are working on the existing **Food Factory** restaurant website
provided in the project files.

First inspect the entire existing codebase and assets.

The current project is a simple PHP website with:

-   Home
-   About
-   Menu
-   Reviews
-   Gallery
-   Reservation
-   Contact
-   Empty Order page
-   Static text-file storage for reservations/messages

Your task is to transform this project into a **production-quality
restaurant ordering and management platform** while preserving the
existing Food Factory identity, content and visual character.

### NON-NEGOTIABLE RULE

Do not create a fake frontend-only demo.

All important functionality must be connected to a real database and
real backend logic.

Do not use hardcoded fake order data in the final implementation.

Do not rely on frontend-submitted prices.

Do not store passwords as plaintext.

Do not use unsafe SQL.

Do not expose secrets.

Do not remove useful existing assets without a reason.

------------------------------------------------------------------------

## STEP A --- INSPECT THE EXISTING PROJECT

Before modifying files:

-   Read all PHP files.
-   Read CSS.
-   Read JavaScript.
-   Inspect all image assets.
-   Inspect text storage files.
-   Identify existing navigation.
-   Identify forms.
-   Identify existing business information.
-   Identify broken features.
-   Identify duplicate markup.
-   Identify security weaknesses.
-   Identify the empty `order.php`.

Create a short internal implementation plan based on what is actually
present.

------------------------------------------------------------------------

## STEP B --- PRESERVE THE BRAND

Keep:

-   Food Factory name
-   Existing logo/image
-   Restaurant images
-   Existing restaurant story
-   Existing menu examples
-   Brown/black/white visual identity where appropriate
-   Existing public pages

Improve the UI instead of replacing the brand with an unrelated
template.

------------------------------------------------------------------------

## STEP C --- BUILD A REAL DATABASE

Create a normalized MySQL database with at least:

-   users
-   roles
-   permissions
-   role_permissions
-   categories
-   menu_items
-   menu_item_variants
-   addons
-   menu_item_addons
-   carts
-   cart_items
-   cart_item_addons
-   orders
-   order_items
-   order_item_addons
-   payments
-   coupons
-   coupon_usages
-   reservations
-   tables
-   reviews
-   contact_messages
-   inventory_items
-   inventory_movements
-   notifications
-   addresses
-   favorites
-   audit_logs

Use foreign keys, indexes, constraints and timestamps.

------------------------------------------------------------------------

## STEP D --- BUILD THE CUSTOMER ORDERING SYSTEM

Turn the empty `order.php` into a full ordering platform.

Customer must be able to:

1.  Browse menu
2.  Search
3.  Filter
4.  Open item
5.  Select variants
6.  Select addons
7.  Select quantity
8.  Add instructions
9.  Add to cart
10. Edit cart
11. Remove cart items
12. Apply coupon
13. Choose delivery/pickup/dine-in
14. Enter customer information
15. Select saved address
16. Select payment method
17. Place order
18. See confirmation
19. Track order
20. View history
21. Reorder
22. Review eligible orders

All prices must be validated and calculated server-side.

------------------------------------------------------------------------

## STEP E --- BUILD THE ADMIN PANEL

Create `/admin/`.

Implement:

-   Admin login
-   Dashboard
-   Orders
-   Kitchen display
-   Menu
-   Categories
-   Variants
-   Addons
-   Reservations
-   Tables
-   Customers
-   Reviews
-   Messages
-   Coupons
-   Promotions
-   Inventory
-   Reports
-   Notifications
-   Staff
-   Roles
-   Permissions
-   Settings
-   Audit logs

Use role-based authorization.

------------------------------------------------------------------------

## STEP F --- BUILD THE DASHBOARD

Show real database metrics:

-   Sales
-   Orders
-   Pending orders
-   Completed orders
-   Cancelled orders
-   Reservations
-   Customers
-   Average order value
-   Low stock
-   Messages
-   Reviews

Add charts for sales/order trends and product performance.

------------------------------------------------------------------------

## STEP G --- BUILD THE KITCHEN DISPLAY

Create a kitchen screen with columns:

-   New
-   Confirmed
-   Preparing
-   Ready

Show:

-   Order number
-   Items
-   Modifiers
-   Instructions
-   Elapsed time
-   Priority
-   Order type

Allow authorized staff to update order state.

------------------------------------------------------------------------

## STEP H --- BUILD INVENTORY

Implement stock tracking.

Support:

-   Current stock
-   Minimum stock
-   Low stock
-   Restock
-   Waste
-   Adjustments
-   Movement history

Keep recipe-based deduction architecture ready.

------------------------------------------------------------------------

## STEP I --- BUILD RESERVATIONS

Move reservations from text files to database.

Implement:

-   Customer booking
-   Admin calendar
-   Confirmation
-   Rejection
-   Seating
-   Completion
-   Cancellation
-   No-show
-   Tables
-   Capacity
-   Conflict prevention

------------------------------------------------------------------------

## STEP J --- BUILD REVIEWS

Move reviews to database.

Implement:

-   Customer review
-   Rating
-   Moderation
-   Approval
-   Rejection
-   Hide
-   Admin reply
-   Verified purchase badge

------------------------------------------------------------------------

## STEP K --- BUILD COUPONS

Support:

-   Percentage
-   Fixed amount
-   Free delivery
-   Minimum order
-   Maximum discount
-   Start/end dates
-   Usage limit
-   Per-user limit
-   First-order-only
-   Product/category restrictions

------------------------------------------------------------------------

## STEP L --- SECURITY

Mandatory:

-   PDO prepared statements
-   Password hashing
-   CSRF
-   XSS protection
-   Secure sessions
-   Permission checks
-   File upload validation
-   Rate limiting where appropriate
-   Input validation
-   Output escaping
-   Audit logging
-   No secret exposure
-   No plaintext passwords
-   No SQL error exposure

------------------------------------------------------------------------

## STEP M --- ORDER SAFETY

Implement:

-   Database transactions
-   Server-side price calculation
-   Inventory validation
-   Coupon validation
-   Idempotency protection
-   Valid status transitions
-   Payment verification architecture
-   Duplicate submission prevention

------------------------------------------------------------------------

## STEP N --- PROFESSIONAL UX

Public website:

-   Responsive
-   Fast
-   Modern
-   Mobile friendly
-   Accessible
-   Searchable
-   Clear CTAs
-   Cart counter
-   Toasts
-   Loading states
-   Empty states
-   Error states

Admin:

-   SaaS-style dashboard
-   Sidebar
-   Topbar
-   KPI cards
-   Charts
-   Tables
-   Filters
-   Pagination
-   Modals
-   Toasts
-   Status badges
-   Responsive mobile layout

------------------------------------------------------------------------

## STEP O --- DOCUMENTATION

Create:

-   `README.md`
-   `MASTER.md`
-   `database/schema.sql`
-   `database/seed.sql`
-   `.env.example`

Document:

-   Installation
-   Database setup
-   Admin setup
-   Configuration
-   Payment configuration
-   Email configuration
-   File upload configuration
-   Security
-   Deployment
-   Backup
-   Testing

------------------------------------------------------------------------

## STEP P --- QUALITY CONTROL

After implementation:

1.  Test every public page.
2.  Test registration.
3.  Test login.
4.  Test cart.
5.  Test checkout.
6.  Test duplicate checkout.
7.  Test coupon.
8.  Test order tracking.
9.  Test reservation.
10. Test review.
11. Test contact message.
12. Test admin login.
13. Test admin permissions.
14. Test order status changes.
15. Test kitchen.
16. Test menu CRUD.
17. Test inventory.
18. Test reports.
19. Test mobile layout.
20. Test unauthorized access.
21. Test CSRF.
22. Test SQL injection protections.
23. Test XSS.
24. Test file uploads.
25. Test error handling.

Fix all obvious errors before declaring completion.

------------------------------------------------------------------------

## FINAL OUTPUT REQUIREMENT

When implementation is complete, provide:

1.  Summary of changes
2.  New file/folder structure
3.  Database schema summary
4.  Admin credentials/setup instructions for development
5.  Installation steps
6.  Testing results
7.  Known limitations
8.  Future enhancements

Do not claim a feature is implemented if it is only a visual
placeholder.

## MASTER PROMPT END

------------------------------------------------------------------------

# 135. FINAL ACCEPTANCE CHECKLIST

Use this checklist before delivery.

## Public Website

-   [ ] Home works
-   [ ] About works
-   [ ] Menu works
-   [ ] Order works
-   [ ] Reviews work
-   [ ] Gallery works
-   [ ] Reservations work
-   [ ] Contact works
-   [ ] Footer works
-   [ ] Mobile navigation works

## Ordering

-   [ ] Search
-   [ ] Filters
-   [ ] Product customization
-   [ ] Add to cart
-   [ ] Update cart
-   [ ] Remove cart item
-   [ ] Coupon
-   [ ] Checkout
-   [ ] Delivery
-   [ ] Pickup
-   [ ] Dine-in
-   [ ] Payment states
-   [ ] Order confirmation
-   [ ] Tracking
-   [ ] History
-   [ ] Reorder

## Admin

-   [ ] Login
-   [ ] Roles
-   [ ] Permissions
-   [ ] Dashboard
-   [ ] Orders
-   [ ] Kitchen
-   [ ] Menu
-   [ ] Categories
-   [ ] Addons
-   [ ] Variants
-   [ ] Customers
-   [ ] Reservations
-   [ ] Tables
-   [ ] Reviews
-   [ ] Messages
-   [ ] Coupons
-   [ ] Inventory
-   [ ] Reports
-   [ ] Notifications
-   [ ] Staff
-   [ ] Settings
-   [ ] Audit logs

## Security

-   [ ] Password hashing
-   [ ] Prepared SQL
-   [ ] CSRF
-   [ ] XSS protection
-   [ ] Secure sessions
-   [ ] Authorization
-   [ ] Upload security
-   [ ] Rate limiting
-   [ ] Audit logs
-   [ ] No secret exposure

## Production Quality

-   [ ] Error pages
-   [ ] Logging
-   [ ] Database indexes
-   [ ] Responsive UI
-   [ ] Accessibility
-   [ ] Performance optimization
-   [ ] Documentation
-   [ ] Backup strategy
-   [ ] Deployment instructions

------------------------------------------------------------------------

# 136. IMPORTANT IMPLEMENTATION PRIORITY

If time or resources are limited, prioritize in this exact order:

1.  Database
2.  Authentication/security
3.  Dynamic menu
4.  Cart
5.  Checkout
6.  Orders
7.  Admin order management
8.  Kitchen
9.  Reservations
10. Reviews/messages
11. Coupons
12. Customers
13. Inventory
14. Reports
15. Notifications
16. Advanced recommendations/favorites
17. QR dine-in
18. Optional payment/communication integrations

Do not prioritize animations over functional correctness.

------------------------------------------------------------------------

# 137. FINAL PRODUCT VISION

The finished Food Factory platform should be capable of operating a real
restaurant workflow:

``` text
Customer visits Food Factory
        ↓
Browses menu
        ↓
Customizes food
        ↓
Adds to cart
        ↓
Checks out
        ↓
Order is stored securely
        ↓
Admin receives order
        ↓
Kitchen prepares order
        ↓
Order becomes ready
        ↓
Customer receives update
        ↓
Order is delivered/picked up/served
        ↓
Customer reviews order
        ↓
Admin sees revenue + analytics
        ↓
Inventory is updated
        ↓
Management uses reports to improve business
```

The administration workflow should be:

``` text
Admin Login
    ↓
Dashboard
    ↓
Orders / Kitchen / Reservations
    ↓
Menu / Inventory / Customers
    ↓
Coupons / Promotions
    ↓
Reviews / Messages
    ↓
Reports
    ↓
Staff / Permissions
    ↓
Settings / Audit Logs
```

**Build the system as a real application, not a mockup. Preserve the
existing Food Factory website and assets, but upgrade the architecture,
data layer, ordering workflow, administration, security, analytics,
inventory, reservations and customer experience to a professional
production-ready standard.**
