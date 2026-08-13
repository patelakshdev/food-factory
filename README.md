# Food Factory — Restaurant Ordering Platform

Upgraded from the original static/flat-file PHP site into a database-driven
ordering and administration platform, per `MASTER.md`.

## 1. What's implemented in this pass

Built following the priority order in `MASTER.md` section 136:

- ✅ **Database** — normalized MySQL schema (`database/schema.sql`) covering
  users/roles/permissions (RBAC), categories, menu items + variants + addons,
  carts, orders + order items + status history, coupons, reservations,
  reviews, contact messages, settings, notifications, audit logs, and a basic
  inventory ledger.
- ✅ **Auth & security** — registration, login with lockout after repeated
  failures, bcrypt password hashing, CSRF tokens on every state-changing
  form, secure session cookies (httpOnly, SameSite=Lax, regenerated on
  login), PDO prepared statements everywhere, output escaping via `e()`,
  a simple session-based rate limiter, and role-based permission checks
  (`user_can()` / `require_permission()`).
- ✅ **Dynamic menu** — `public/menu.php` reads categories/items from the
  database with search + category filtering, replacing the hard-coded menu.
- ✅ **Cart** — session/user-bound cart, AJAX add/update via `api/cart/*`,
  coupon application.
- ✅ **Checkout & orders** — guest or logged-in checkout, delivery/pickup/
  dine-in, **all prices are recalculated server-side inside a DB transaction**
  (client-submitted prices are never trusted), idempotency key prevents
  double-submission, order confirmation + status timeline
  (`public/order-details.php`), order history (`public/orders.php`).
- ✅ **Admin: orders** — list/filter, status updates restricted to valid
  transitions (`admin/orders/index.php`), each change written to
  `order_status_history` and `audit_logs`.
- ✅ **Admin: dashboard** — KPI cards (orders today, revenue today, active
  orders, pending reservations/reviews/messages) + recent orders table.
- ✅ **Reservations** — moved off `reservations.txt` into the `reservations`
  table; admin can confirm/seat/complete/cancel/mark no-show.
- ✅ **Reviews** — moved off hard-coded testimonials into the `reviews`
  table with a moderation queue (approve/reject/hide + admin reply); only
  `approved` reviews show on the public site.
- ✅ **Contact messages** — moved off `messages.txt` into `contact_messages`,
  with an admin inbox.
- ✅ Public pages (`about`, `gallery`) preserved with original content and
  branding, now sharing one `includes/header.php` / `includes/footer.php`
  instead of duplicated markup.

## 2. Not yet built (honest status — do not assume these exist)

The schema and permission keys are in place for these, but there is **no
UI/logic yet**. Treat these as the next milestones, in the order the master
spec recommends:

- Admin CRUD for menu items/categories/variants/addons
- Coupon management UI (table + evaluation logic already exist and work at
  checkout; there's just no admin screen to create/edit coupons yet)
- Customer management screen
- Inventory management UI (ledger tables exist; no deduction-on-order logic
  wired up yet)
- Reports/analytics beyond the dashboard KPIs
- Staff management / role assignment UI
- Settings management UI (settings table exists and is read at checkout for
  delivery fee/free-delivery threshold; no edit screen yet)
- Notifications delivery (table exists; nothing writes to it yet)
- Audit log viewer (logging is already happening; no screen to read it)
- Email notifications (PHPMailer), payment gateway integration
- Product customization UI for variants/addons on the menu page (backend
  supports it; the "Add to Cart" button currently adds the base item only)

## 3. Installation

1. Create a MySQL 8+ / MariaDB database and user.
2. `cp .env.example .env` and fill in `DB_*` values.
3. Import the schema, then the seed data:
   ```bash
   mysql -u youruser -p yourdb < database/schema.sql
   mysql -u youruser -p yourdb < database/seed.sql
   ```
4. **Set a real admin password** (the seed file ships a placeholder hash
   that will not work):
   ```bash
   php database/make_admin_hash.php "YourStrongPassword123!"
   # paste the output into users.password_hash for admin@foodfactory.local
   ```
5. Point your web server's document root at this project root (an
   `.htaccess` blocks direct access to `config/`, `includes/`, `database/`,
   `storage/`). Ensure `mod_rewrite` and `AllowOverride All` are enabled, or
   translate the rules to your server (nginx/etc).
6. Visit `/public/index.php` for the site, `/admin/login.php` for the admin
   panel.

## 4. Default admin login

- Email: `admin@foodfactory.local`
- Password: **you must set one** — see step 4 above. There is no working
  default password shipped in source control on purpose.

## 5. Testing status

⚠️ This sandbox has no PHP runtime or MySQL server available, so the code
in this delivery has been **written and reviewed carefully but not executed
end-to-end**. Before going live:

- Run `php -l` over every file (syntax check).
- Walk through `MASTER.md` §135 "Final Acceptance Checklist" manually —
  register, login, browse menu, add to cart, apply `WELCOME10`, checkout as
  guest and as a logged-in user, confirm the order total matches DB
  calculations, then move an order through its full status lifecycle in
  admin.
- Test CSRF (submit a form with a stale/missing token → expect a 419 page).
- Test unauthorized admin access (log in as a `staff` role and hit a
  `manager`-only permission → expect a 403 page).

## 6. Known limitations

- No product customization UI yet (variants/addons are modeled but not
  exposed as a picker before "Add to Cart").
- No payment gateway wired up; `payment_method`/`payment_status` are
  tracked but orders are marked `unpaid` at creation regardless of method.
- No email/SMS notifications sent on status changes yet.
- Inventory is not yet decremented when an order is placed.

## 7. Suggested next session

Pick up at admin Menu/Category CRUD (`admin/menu/`, `admin/categories/`),
then Coupon management, then Settings — in that order they unblock the
most remaining checklist items with the least new schema work.

## 8. Deploying to Vercel (PHP + managed MySQL web service)

Vercel does not run PHP natively, so this repo uses the community
`vercel-php@0.9.0` runtime (PHP 8.5) via a single socket function:

- `vercel.json` — one serverless function (`api/index.php`) with a catch-all
  route; static assets under `/assets/*` are served by Vercel's CDN and every
  other path is dispatched by the front controller to the real `.php`
  entrypoint under `public/`, `admin/`, or `api/`.
- `api/index.php` — strict path whitelist; `config/`, `includes/`,
  `database/`, `storage/`, `.env` are never reachable over HTTP.
- `config/app.php` — sessions are stored in a new `sessions` table
  (DB-backed session handler in `includes/session-handler.php`) so logins
  survive serverless cold starts.
- `config/database.php` — idempotent first-boot provisioning: on the first
  request after you point the app at an empty database it applies
  `database/schema.sql` + `database/seed.sql` and creates the admin account
  from `ADMIN_EMAIL` / `ADMIN_PASSWORD`. Disable with `APP_DB_PROVISION=false`.

To deploy:

1. Provision a **managed MySQL/MariaDB web service** reachable over the
   internet (Aiven Hobby, PlanetScale, Railway, Clever Cloud, etc.), and
   create a database + DB user (grant the user `CREATE`, `ALTER`, `INDEX`
   for one-time auto-provisioning, or import `schema.sql` + `seed.sql`
   yourself and set `APP_DB_PROVISION=false`).
2. Push this repo to GitHub and import it into Vercel (or `vercel deploy`
   from the CLI).
3. In the Vercel project settings set **Environment Variables**:
   `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`,
   `ADMIN_EMAIL`, `ADMIN_PASSWORD`.
4. Visit the site — the first request initialises the schema/seed/admin,
   then everything (menu, cart, checkout, admin) is fully live.
