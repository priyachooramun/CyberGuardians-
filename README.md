# InkCorner – Online Bookstore & Stationery Shop

ICT 2213Y(3) Web Technologies and Security – group project, University of Mauritius.

InkCorner is a family bookstore and stationery shop in Port Louis that has worked on paper for 40 years.
This web application lets **guests** browse the catalogue, **customers** buy online (payments are simulated)
and track orders, and the **administrator** manage products, stock, orders and customers.

**Stack:** PHP 8.2 (plain PHP + PDO, modular MVC-style folders) · MySQL/MariaDB (XAMPP) · HTML5 · CSS3 ·
Bootstrap 5 · jQuery / AJAX · JSON + JSON Schema · Figma for UI · GitHub for version control.

---

## 1. Run it on your computer (XAMPP)

1. Install XAMPP (PHP 8.1 or newer). Start **Apache** and **MySQL** in the XAMPP Control Panel.
2. Put this folder in `C:\xampp\htdocs\inkcorner` (Windows) or `/Applications/XAMPP/htdocs/inkcorner` (macOS).
   Any folder name works – the site detects its own folder (`BASE_URL`) automatically.
3. Open **phpMyAdmin** → `http://localhost/phpmyadmin`
   - **If you already have the `stationery real` database** with the 11 tables: select it, then
     **Import** `database/02_upgrade.sql`, then **Import** `database/03_seed.sql`.
   - **Starting from nothing:** create a database called `stationery real` (collation `utf8mb4_general_ci`),
     then import `01_original_schema.sql`, `02_upgrade.sql` and `03_seed.sql` in that order.
4. Open `http://localhost/inkcorner/setup-check.php`. It tests PHP, the folder name, the database, the
   tables and the folders, and tells you exactly what to change if anything is wrong.
5. When every line is green, open `http://localhost/inkcorner`.

**No internet in the demo room?** Download the four library files listed in `assets/vendor/README.txt`
and add `define('USE_LOCAL_LIBS', true);` to `config/config.local.php`.

**Demo logins** (from `03_seed.sql`):

| Role | Where | Email | Password |
|---|---|---|---|
| Customer | `/auth/login.php` | `demo@inkcorner.mu` | `Demo@1234` |
| Administrator | `/admin/login.php` | `admin@inkcorner.mu` | `Admin@123` |

**Test cards** for the simulated payment (any future expiry, any 3-digit CVV):

| Card number | Result |
|---|---|
| 4242 4242 4242 4242 | Approved (Visa) |
| 5555 5555 5555 4444 | Approved (Mastercard) |
| 4000 0000 0000 0002 | Declined |
| 4000 0000 0000 9995 | Insufficient funds |

### Your own settings (password, port, folder)

Copy `config/config.local.example.php` to `config/config.local.php` and change only what differs on your
computer (for example `DB_PASS` or `DB_PORT`). That file is git-ignored, so nobody's password ends up on GitHub.

### Styles, logo and background

Every page loads `assets/css/base.css` (shared design: colours, fonts, the desk background photo, header,
footer, forms, buttons). Admin pages also load `assets/css/admin.css`. Each page then loads its own file from
`assets/css/pages/`, chosen by `$pageCss = 'shop';` before the layout is included. Page rules are scoped with the
body class (`.page-shop …`) so they cannot affect other pages.

The logo (`assets/images/logo.png`, `logo-sm.png`) and the desk background (`assets/images/bg-desk.jpg`) are
already included. To change the background, replace `bg-desk.jpg` with another image of the same name.

---|---|
| `assets/images/logo.png` | The InkCorner logo (layer “image 17” in the top-left of the *Landing page* frame) |
| `assets/images/bg-desk.jpg` | The desk background photo used behind every frame (select the background image fill → Export as JPG) |

Until they are added, the site shows the text logo “InkCorner” and a plain paper-coloured background.

---

## 2. Folder structure

```
inkcorner/
├── index.php              Landing page (FR1)                                 M6
├── about.php, contact.php About / contact + question form (FR4)              M6
├── config/                App settings + PDO connection                      M1
├── database/              01 original schema · 02 upgrade · 03 demo data    M1
├── models/                One class per table (prepared statements only)     M1
├── includes/              bootstrap.php, helpers.php (M1) · page layouts (M6)
├── security/              escape (XSS), csrf, validator, passwords, CSP headers, security log  M5
├── storage/logs/          security.log.php (private, written by the site)    M5
├── auth/                  sessions, role guards, register/login/logout       M2
├── account/               profile (M2) · order history (M4)
├── shop/                  catalogue, product page, sorting                   M3
├── cart/                  session cart                                       M4
├── checkout/              simulated payment, order placement, receipt        M4
├── services/              Cart, PaymentSimulator (M4) · ImageUpload, ProductForm (M3)
├── api/                   JSON endpoints for AJAX + JSON Schemas             M5
├── admin/                 dashboard + queries (M6) · login (M2) · products (M3) · orders & customers (M4)
├── assets/css/           base.css + admin.css (M6) · pages/*.css – one per page, owned by that page's member
├── assets/js, images     main/validation JS (M5) · cart JS (M4) · admin JS (M3) · logo + background (M6)
├── uploads/products/      Product images (PHP execution blocked)             M3
└── docs/TEAM_GUIDE.md     Who owns what + how to commit                      M6
```

Every page starts with `require_once __DIR__ . '/../includes/bootstrap.php';`, which loads the
configuration, the database connection, the security helpers, the secure session and an autoloader for
`models/` and `services/`.

---

## 3. Functional requirements → files

| ID | Requirement | Main files |
|---|---|---|
| FR1 | View homepage | `index.php` |
| FR2 | Browse Books and Stationery | `shop/index.php`, `models/Product.php` |
| FR3 | View product details | `shop/product.php` |
| FR4 | About / Contact, customer questions and replies | `about.php`, `contact.php`, `admin/queries/index.php` |
| FR5 | Register | `auth/register.php` |
| FR6 | Block guests from cart and checkout | `auth/guards.php` → `require_customer()`, `api/cart.php` (401) |
| FR7 / FR8 | Log in / log out | `auth/login.php`, `auth/logout.php` |
| FR9 / FR10 | Add, update, remove cart items | `services/Cart.php`, `cart/*`, `api/cart.php`, `assets/js/cart.js` |
| FR11 | Checkout, payment and receipt | `checkout/*`, `services/PaymentSimulator.php` |
| FR12 | Store personal details | `models/Customer.php`, `account/profile.php` |
| FR13 / FR14 | Order history, past orders and status | `checkout/process.php`, `account/orders.php`, `account/order.php` |
| FR15 | View and update profile | `account/profile.php` |
| FR16 | Admin log in | `admin/login.php` |
| FR17–FR19 | Add / edit / remove product | `admin/products/*`, `services/ProductForm.php`, `services/ImageUpload.php` |
| FR20 / FR21 | Manage orders, update status | `admin/orders/*`, `models/Order.php` |
| FR22 | View registered customers | `admin/customers/index.php` |
| FR23 | SQL injection prevention | `config/database.php`, every file in `models/` |
| FR24 | Password hashing | `security/password.php` |
| FR25 | Session protection | `auth/session.php` |
| FR26 | XSS output escaping | `security/escape.php` (`e()` everywhere) |
| FR27 | CSRF tokens | `security/csrf.php` |
| FR28 | Role-based access control | `auth/guards.php` |
| FR29 | Sort items | `Product::SORTS`, `shop/index.php` |
| FR30 | Update stock | `admin/products/index.php` (AJAX → `api/stock.php`), `models/Inventory.php` |

---

## 4. Security measures (for the demo questions)

| Threat | What we do | Where |
|---|---|---|
| **SQL injection** | PDO prepared statements with real server-side prepares (`ATTR_EMULATE_PREPARES = false`). Sort options come from a whitelist, never from raw input. | `config/database.php`, `models/*`, `Product::SORTS` |
| **XSS** | Every value printed with `e()` (`htmlspecialchars`, `ENT_QUOTES`). AJAX results are inserted with jQuery `.text()`, never as HTML. Names are validated. | `security/escape.php`, `assets/js/main.js` |
| **CSRF** | Random 64-character token per session, a hidden field in every POST form, an `X-CSRF-Token` header on every AJAX call, compared with `hash_equals`. Logout and delete are POST-only. | `security/csrf.php` |
| **Session hijacking** | HttpOnly + SameSite cookie, strict mode, new session id at login and every 15 min, 30-min idle timeout, session tied to the browser's User-Agent. | `auth/session.php` |
| **Passwords** | bcrypt via `password_hash`, constant-time check, login lock after 5 wrong tries, same error for wrong email and wrong password. | `security/password.php`, `auth/login.php` |
| **Broken access control** | Role guards on every page, ownership checks so customers only see their own orders and receipts. | `auth/guards.php`, `Order::findForCustomer` |
| **Price tampering** | Cart stores ids and quantities only. Prices are re-read from the database at checkout. | `services/Cart.php` |
| **Overselling** | Stock taken with `UPDATE … WHERE quantity >= ?` inside one transaction, with rollback on failure. | `models/Inventory.php`, `checkout/process.php` |
| **Malicious uploads** | Real file type checked with `finfo`, 2 MB limit, random file names, PHP disabled in `uploads/`. | `services/ImageUpload.php`, `uploads/.htaccess` |
| **Search engines** | `noindex` on account and admin pages. | `includes/header.php`, `includes/admin_header.php` |
| **XSS (second layer)** | Content-Security-Policy only allows script and style FILES from our site and the two CDNs, so an injected `<script>` or `onclick=` would not run. The site has no inline scripts, event attributes or `style=` attributes. | `security/headers.php` |
| **Clickjacking** | `frame-ancestors 'self'` and `X-Frame-Options: SAMEORIGIN` stop other sites framing our pages. | `security/headers.php` |
| **Information leaks** | `X-Powered-By` removed, `nosniff`, `Referrer-Policy`, account/admin pages sent with `Cache-Control: no-store`. | `security/headers.php`, `auth/guards.php` |
| **Monitoring** | Failed logins, lock-outs, rejected CSRF tokens, blocked admin access, session hijack attempts and rejected uploads are logged and shown under *System Logs* on the dashboard. Passwords are never logged; the log file cannot be opened from a browser. | `security/log.php`, `admin/index.php` |

---

## 5. Pages and navigation

All pages are reachable from the menus; every link was clicked in an automated browser test.

| Area | Pages | Reached from |
|---|---|---|
| Public | `index.php`, `shop/index.php` (all filters), `shop/product.php`, `about.php`, `contact.php` | Top menu, category bar, footer, landing tiles |
| Account | `auth/login.php`, `auth/register.php`, `account/profile.php`, `account/orders.php`, `account/order.php` | Login button, account menu (person icon), footer |
| Buying | `cart/index.php`, `checkout/index.php`, `checkout/receipt.php` | Cart icon, Buy now, Checkout, order details |
| Admin | `admin/login.php`, `admin/index.php`, `admin/products/*`, `admin/orders/*`, `admin/customers/index.php`, `admin/queries/index.php` | Footer “Staff login”, black sidebar, dashboard tiles |
| Form handlers (no page of their own) | `cart/actions.php`, `admin/products/actions.php`, `auth/logout.php`, `api/*` | Buttons and AJAX only |

**Merged pages:** `cart/add.php` + `update.php` + `remove.php` → `cart/actions.php`;
`admin/products/stock.php` + `delete.php` → `admin/products/actions.php`; `admin/logout.php` → `auth/logout.php`;
`checkout/process.php` → `checkout/index.php`.

**Not built (marked `TODO` in the code with instructions):** wishlist heart from the Figma header
(`includes/header.php`), password reset by email (`auth/login.php`), emailing replies to customers
(`admin/queries/index.php`).

---

## 6. AJAX / JSON endpoints

| Endpoint | Method | Who | Purpose | Schema |
|---|---|---|---|---|
| `api/products.php` | GET | anyone | Live search, filter, sort | `api/schema/products-response.schema.json` |
| `api/cart.php` | GET / POST | customers | Cart summary, add, update, remove | `cart-request` / `cart-response.schema.json` |
| `api/stock.php` | GET / POST | GET anyone, POST admin | Stock check, update stock | – |

---

## 7. Changes to the database

`02_upgrade.sql` keeps every original table and column and only **adds** what the application needs:
AUTO_INCREMENT keys, `password_hash`, product section/category/brand/image/sale/featured flags, order totals
and shipping address, payment status and reference, receipt numbers, and `orderitem.variant_id`.
Each change is explained at the top of the file.
