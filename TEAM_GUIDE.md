# InkCorner – Team guide: who owns what, and how to commit it

We are marked **individually from GitHub commits**, so each member commits **only their own files**, in
several small commits with clear messages. Every file says who owns it in its first comment
(`Owner: Member X`). Replace “Member 1…6” below with your names, matching slide 4 of the Week 10 deck.

---

## 1. File ownership

### Member 1 – Setup & database
```
.gitignore
.htaccess
config/config.php
config/database.php
config/config.local.example.php
config/.htaccess
database/01_original_schema.sql
database/02_upgrade.sql
database/03_seed.sql
database/.htaccess
includes/bootstrap.php
includes/helpers.php
includes/.htaccess
models/Model.php
models/Admin.php
models/Bookstore.php
models/Customer.php
models/Product.php
models/Inventory.php
models/ProductVariant.php
models/Order.php
models/OrderItem.php
models/Payment.php
models/Receipt.php
models/Query.php
models/.htaccess
setup-check.php
assets/css/setup-check.css
```

### Member 2 – Authentication & roles (FR5, FR7, FR8, FR12, FR15, FR16, FR25, FR28)
```
auth/session.php
auth/guards.php
auth/register.php
auth/login.php
auth/logout.php
admin/login.php
account/profile.php
assets/css/pages/login.css
assets/css/pages/register.css
assets/css/pages/profile.css
assets/css/pages/admin-login.css
```

### Member 3 – Catalogue & admin products (FR2, FR3, FR17, FR18, FR19, FR29, FR30)
```
shop/index.php
shop/product.php
shop/_product_card.php
admin/products/index.php
admin/products/_form.php
admin/products/create.php
admin/products/edit.php
admin/products/actions.php
services/ProductForm.php
services/ImageUpload.php
uploads/.htaccess
uploads/products/.gitkeep
assets/js/admin.js
assets/css/pages/shop.css
assets/css/pages/product.css
assets/css/pages/admin-products.css
assets/css/pages/admin-product-form.css
```

### Member 4 – Cart, checkout & orders (FR9–FR14, FR20–FR22)
```
services/Cart.php
services/PaymentSimulator.php
services/.htaccess
cart/index.php
cart/actions.php
checkout/index.php
checkout/receipt.php
account/orders.php
account/order.php
admin/orders/index.php
admin/orders/view.php
admin/customers/index.php
assets/js/cart.js
assets/css/pages/cart.css
assets/css/pages/checkout.css
assets/css/pages/receipt.css
assets/css/pages/orders.css
assets/css/pages/order.css
assets/css/pages/admin-orders.css
assets/css/pages/admin-order.css
assets/css/pages/admin-customers.css
```

### Member 5 – Security & AJAX/JSON (FR23, FR24, FR26, FR27)
```
security/escape.php
security/csrf.php
security/validator.php
security/password.php
security/headers.php
security/log.php
security/.htaccess
storage/.htaccess
storage/logs/.gitkeep
api/products.php
api/cart.php
api/stock.php
api/schema/products-response.schema.json
api/schema/cart-request.schema.json
api/schema/cart-response.schema.json
assets/js/main.js
assets/js/validation.js
```

### Member 6 – UI, QA & demo (FR1, FR4, layouts, dashboard)
```
index.php
about.php
contact.php
admin/index.php
admin/queries/index.php
includes/header.php
includes/footer.php
includes/admin_header.php
includes/admin_footer.php
assets/css/base.css
assets/css/admin.css
assets/css/pages/home.css
assets/css/pages/about.css
assets/css/pages/contact.css
assets/css/pages/error.css
assets/css/pages/admin-dashboard.css
assets/css/pages/admin-queries.css
assets/images/placeholder.svg
assets/vendor/README.txt
assets/images/logo.png
assets/images/logo-sm.png
assets/images/bg-desk.jpg
README.md
docs/TEAM_GUIDE.md
```

---

## 2. One-time setup

**Member 1** creates the repository and makes the first push:

```bash
cd C:/xampp/htdocs
git clone https://github.com/<team>/inkcorner.git
cd inkcorner
# copy in ONLY Member 1's files from the zip (list above), keeping the same folders
git add .gitignore .htaccess config database includes/bootstrap.php includes/helpers.php includes/.htaccess models
git commit -m "Set up project structure, configuration and PDO connection"
git push origin main
```

Then **every member** (including Member 1 for later work):

```bash
cd C:/xampp/htdocs
git clone https://github.com/<team>/inkcorner.git     # skip if you already have it
cd inkcorner
git config user.name  "Your Name"
git config user.email "the-email-on-your-github-account"   # must match GitHub, or commits won't count for you
git checkout -b feature/<your-area>                        # e.g. feature/auth, feature/catalogue
```

Copy **only your own files** from the zip into the same folders, then commit them in small steps (section 3).

```bash
git push -u origin feature/<your-area>
```

Open a Pull Request on GitHub → another member reviews → merge.

**Merge order.** Every page loads `security/` and `auth/` through `bootstrap.php`, so the site only runs
once these are merged. Merge in this order: **M1 → M5 → M2 → M6 → M3 → M4**. The whole site works after the
last merge. Before that, test locally with the full zip.

After someone else's branch is merged, update yours:
```bash
git checkout main && git pull
git checkout feature/<your-area> && git merge main
```

---

## 3. Suggested commits per member

Use these as the order of your commits (`git add <files>` then `git commit -m "..."`). Several meaningful
commits look much better in the contribution graph than one huge commit.

| Member | Commit | Files |
|---|---|---|
| **M1** | Set up project structure, configuration and PDO connection | `.gitignore`, `.htaccess`, `config/*`, `includes/bootstrap.php`, `includes/helpers.php`, `includes/.htaccess` |
| | Add original schema, upgrade script and demo data | `database/*` |
| | Add base model and customer/admin/bookstore models | `models/Model.php`, `Admin.php`, `Bookstore.php`, `Customer.php`, `models/.htaccess` |
| | Add product, inventory and variant models | `models/Product.php`, `Inventory.php`, `ProductVariant.php` |
| | Add setup check page for new computers | `setup-check.php`, `assets/css/setup-check.css` |
| | Add order, payment, receipt and query models | `models/Order.php`, `OrderItem.php`, `Payment.php`, `Receipt.php`, `Query.php` |
| **M2** | Add secure session handling (FR25) | `auth/session.php` |
| | Add role-based access guards (FR28) | `auth/guards.php` |
| | Add customer registration with validation (FR5) | `auth/register.php` |
| | Add customer login, throttling and one logout for both roles (FR7, FR8) | `auth/login.php`, `auth/logout.php` |
| | Add separate administrator login (FR16) | `admin/login.php` |
| | Add profile and password update (FR12, FR15) | `account/profile.php` |
| | Add styles for login, sign-up, profile and staff login | `assets/css/pages/login.css`, `register.css`, `profile.css`, `admin-login.css` |
| **M3** | Add catalogue browsing with sections and categories (FR2) | `shop/index.php`, `shop/_product_card.php` |
| | Add product detail page with variants (FR3) | `shop/product.php` |
| | Add secure image upload and product form validation | `services/ImageUpload.php`, `services/ProductForm.php`, `uploads/.htaccess`, `uploads/products/.gitkeep` |
| | Add admin product add/edit (FR17, FR18) | `admin/products/_form.php`, `create.php`, `edit.php` |
| | Add admin product list, remove/restore and stock update (FR19, FR30) | `admin/products/index.php`, `admin/products/actions.php`, `assets/js/admin.js` |
| | Add styles for shop, product and admin product pages | `assets/css/pages/shop.css`, `product.css`, `admin-products.css`, `admin-product-form.css` |
| **M4** | Add session cart service | `services/Cart.php`, `services/.htaccess` |
| | Add cart page and cart actions (FR9, FR10) | `cart/index.php`, `cart/actions.php`, `assets/js/cart.js` |
| | Add simulated payment gateway with Luhn check | `services/PaymentSimulator.php` |
| | Add checkout with transactional order placement (FR11, FR13) | `checkout/index.php` |
| | Add printable receipt and order history (FR11, FR14) | `checkout/receipt.php`, `account/orders.php`, `account/order.php` |
| | Add admin order management and customer list (FR20–FR22) | `admin/orders/*`, `admin/customers/index.php` |
| | Add styles for cart, checkout, receipt, orders and admin order pages | `assets/css/pages/cart.css`, `checkout.css`, `receipt.css`, `orders.css`, `order.css`, `admin-orders.css`, `admin-order.css`, `admin-customers.css` |
| **M5** | Add output escaping helpers (FR26) | `security/escape.php`, `security/.htaccess` |
| | Add CSRF token protection (FR27) | `security/csrf.php` |
| | Add server-side validator and password hashing (FR24) | `security/validator.php`, `security/password.php` |
| | Add Content-Security-Policy headers and security event log | `security/headers.php`, `security/log.php`, `storage/.htaccess`, `storage/logs/.gitkeep` |
| | Add JSON product search and stock endpoints | `api/products.php`, `api/stock.php` |
| | Add JSON cart endpoint and JSON Schemas | `api/cart.php`, `api/schema/*` |
| | Add jQuery AJAX cart, live search and client-side validation | `assets/js/main.js`, `assets/js/validation.js` |
| **M6** | Add customer and admin page layouts from Figma | `includes/header.php`, `footer.php`, `admin_header.php`, `admin_footer.php` |
| | Add base and admin stylesheets, logo and background photo | `assets/css/base.css`, `assets/css/admin.css`, `assets/images/*` |
| | Add styles for landing, about, contact, error and dashboard pages | `assets/css/pages/home.css`, `about.css`, `contact.css`, `error.css`, `admin-dashboard.css` |
| | Add landing, about and contact pages (FR1, FR4) | `index.php`, `about.php`, `contact.php` |
| | Add admin dashboard and customer queries page | `admin/index.php`, `admin/queries/index.php`, `assets/css/pages/admin-queries.css` |
| | Add README, team guide and offline library instructions | `README.md`, `docs/TEAM_GUIDE.md`, `assets/vendor/README.txt` |

**Commit messages:** start with a verb, say what and why (“Add CSRF token to cart AJAX requests”).
Never commit `config/config.local.php` or test uploads (already in `.gitignore`).

---

## 4. What each member should be ready to explain in the demo

| Member | Be ready to explain |
|---|---|
| M1 | Why PDO and prepared statements; `ATTR_EMULATE_PREPARES`; one model per table; the upgrade script and why each column was added; transactions |
| M2 | `password_hash` / `password_verify`; session fixation and `session_regenerate_id`; HttpOnly/SameSite cookies; idle timeout; role guards; login throttling |
| M3 | How sorting uses a whitelist (`Product::SORTS`); category filtering; `finfo` image checks; soft delete vs delete; AJAX stock update |
| M4 | Why the cart stores ids only; Luhn algorithm; the checkout transaction and rollback; the `quantity >= ?` stock check; ownership check on receipts (IDOR) |
| M5 | `htmlspecialchars` and XSS; Content-Security-Policy; the security log; CSRF tokens in forms and AJAX headers; JSON responses and JSON Schema; jQuery `$.post` / `$.getJSON`; client vs server validation |
| M6 | Figma → Bootstrap layout; responsive design; dashboard queries; the test checklist and demo video |

---

## 5. Test checklist before each demo (Member 6 leads, everyone helps)

- [ ] Guest: browse Books/Stationery, sort by price, open a product, live search, try Add to cart → sent to login
- [ ] Register with a bad email/weak password → errors; register properly → logged in
- [ ] Customer: add items (with an option/colour), change quantity on the cart page, remove an item
- [ ] Checkout with `4000 0000 0000 0002` → declined, no order created; then `4242 4242 4242 4242` → receipt
- [ ] My orders → order detail → receipt prints
- [ ] Another customer cannot open your receipt by changing `?order=` in the URL
- [ ] Admin: dashboard numbers, add a product with image, edit price, change stock inline, remove a product
- [ ] Admin: set an order to Shipped (customer sees it), cancel an order (stock goes back)
- [ ] Type `<script>alert(1)</script>` into a product name and the search box → shown as text, nothing runs
- [ ] Contact page: send a question → admin Queries → reply → customer sees the reply
- [ ] Admin dashboard → System Logs shows failed logins and blocked attacks
- [ ] Wait 30 minutes idle (or lower `SESSION_TIMEOUT`) → logged out
