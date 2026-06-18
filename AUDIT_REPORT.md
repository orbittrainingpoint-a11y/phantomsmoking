# Phantom Smoking — Full Application Audit Report

**Date:** June 2026  
**Scope:** Complete codebase — security, functionality, access control, bugs, UX  
**Scanner:** Amazon Q Code Review (full scan) + manual deep analysis  
**Files Reviewed:** 107 PHP files, 8 JS files, 14 CSS files, routes, middleware, DB schema

---

## Executive Summary

| Category | Count |
|---|---|
| 🔴 Critical Issues | 6 |
| 🟠 High Severity | 9 |
| 🟡 Medium Severity | 11 |
| 🟢 Low / Informational | 14 |
| **Total** | **40** |

The application is **reasonably well-built** for a custom PHP e-commerce system. Core security patterns (prepared statements, CSRF, session hardening, output escaping) are correctly implemented. However, several **critical and high severity** issues need to be fixed before the production site handles real customer orders and payments.

---

## 🔴 CRITICAL ISSUES

---

### CRIT-01 — Admin OTP Bypass: Hardcoded Email Whitelist

**File:** `app/controllers/AuthController.php` — Line ~47  
**Risk:** Account takeover, full admin access without 2FA

```php
// VULNERABLE — hardcoded bypass
$otpBypass = ['admin@sultanssmokedubai.com', 'solsedigital@gmail.com'];
if (in_array($email, $otpBypass)) {
    Auth::login($user);
    ...
}
```

Anyone who compromises those email accounts can log in as admin with **no OTP challenge**. The old domain `sultanssmokedubai.com` is in the bypass list but the live site is `phantomsmoking.ae` — this is a stale hardcode.

**Fix:** Remove the bypass entirely. All users including admins must go through OTP.

```php
// DELETE the entire $otpBypass block (lines ~47-52)
// Let all users fall through to OTP flow normally
```

---

### CRIT-02 — Age Gate Cookie Not Secure in Production

**File:** `app/controllers/AgeGateController.php` — Line ~25  
**Risk:** Cookie is transmitted over HTTP, bypassable by setting `age_verified=1` cookie

```php
// VULNERABLE — secure flag hardcoded to false
setcookie('age_verified', '1', time() + (30 * 86400), '/', '', false, true);
//                                                              ^^^^^
//                                                              secure=false!
```

In production (HTTPS), the `secure` flag must be `true`. A user can also set this cookie manually in DevTools to bypass age verification entirely — there is no server-side signature/validation of the cookie value.

**Fix:**

```php
$isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https';

setcookie('age_verified', '1', [
    'expires'  => time() + (30 * 86400),
    'path'     => '/',
    'secure'   => $isHttps,
    'httponly' => true,
    'samesite' => 'Lax',
]);
```

For stronger protection, sign the cookie value with HMAC so it cannot be forged.

---

### CRIT-03 — Payment Callback Has No Authentication / Order Ownership Check

**File:** `app/controllers/PaymentController.php` — Lines ~14–80  
**Risk:** Any user can confirm any order as paid by guessing/knowing the order ID

```php
public function stripeSuccess(): void
{
    $orderId   = (int)$this->request->get('order_id'); // ← from URL, no ownership check
    $sessionId = $this->request->get('session_id', '');
    $order     = (new Order())->find($orderId);
    if (!$order) { $this->redirect('/'); }
    // No check: does this order belong to the current user?
    $gateway  = new StripeGateway();
    $result   = $gateway->verifyPayment(['session_id' => $sessionId]);
    $this->finalisePayment($order, $result, 'stripe');
}
```

A logged-in user who knows order #5 belongs to someone else can visit `/payment/stripe/success?order_id=5&session_id=THEIR_VALID_SESSION` and potentially mark another user's order as paid if the gateway verify logic has a flaw.

**Fix:** Add user ownership validation in every payment callback:

```php
// After $order = (new Order())->find($orderId);
if (!$order) { $this->redirect('/'); }
if ($order['user_id'] && Auth::check() && $order['user_id'] !== Auth::id()) {
    $this->redirect('/');
}
```

---

### CRIT-04 — Tamara Webhook Has No Signature Verification

**File:** `app/controllers/PaymentController.php` — `tamaraWebhook()` method  
**Risk:** Anyone can send a forged POST to mark any order as paid

```php
public function tamaraWebhook(): void
{
    $payload = json_decode(file_get_contents('php://input'), true);
    // NO SIGNATURE VERIFICATION!
    if (!empty($payload['order_id']) && $payload['event_type'] === 'order_approved') {
        // marks order as paid directly
    }
}
```

**Fix:** Verify the Tamara webhook signature using the `notification_key` from settings:

```php
$signature = $_SERVER['HTTP_X_TAMARA_SIGNATURE'] ?? '';
$body      = file_get_contents('php://input');
$secret    = $this->db->fetch("SELECT setting_value FROM settings WHERE setting_key='tamara_notification_key'")['setting_value'] ?? '';
$expected  = hash_hmac('sha256', $body, $secret);
if (!hash_equals($expected, $signature)) {
    http_response_code(401);
    exit('Unauthorized');
}
```

---

### CRIT-05 — XSS in Report Print/Invoice Output (Unescaped DB Data)

**File:** `app/controllers/ReportController.php` — `exportPrint()` and `buildInvoiceHtml()` methods  
**Risk:** Stored XSS — if a product name, customer name, or order field contains HTML, it renders directly

```php
// VULNERABLE — raw DB data injected into HTML
$rows .= "<tr><td>{$r['name']}</td><td>{$r['sku']}</td>...";
// and in buildInvoiceHtml():
$rows .= "<tr><td>{$i['product_name']}</td><td>{$i['variant_name']}</td>...";
```

Any admin-created product with name `<script>alert(1)</script>` would execute when the invoice/report is printed.

**Fix:** Wrap every value with `htmlspecialchars()` or the existing `e()` helper:

```php
$rows .= "<tr><td>" . e($r['name']) . "</td><td>" . e($r['sku']) . "</td>...";
```

---

### CRIT-06 — Leftover Debug/Admin Scripts Exposed in `public_html`

**File:** `public_html/` directory  
**Risk:** Information disclosure, unauthorized admin creation, DB reset

The following files are publicly accessible and should be **deleted immediately** from production:

| File | Risk |
|---|---|
| `add_gmail_admin.php` | Creates admin accounts |
| `add_test_admin.php` | Creates test admin accounts |
| `reset_admin.php` | Resets admin password |
| `check_pass.php` | Reveals password hash info |
| `debug_flavours.php` | Exposes DB structure |
| `migrate_*.php` | Runs DB migrations publicly |
| `test_mail.php` | Exposes SMTP credentials |
| `test_smtp.php` | Exposes SMTP credentials |
| `update_settings.php` | Modifies settings without auth |

**Fix:** Delete all of these files from the production server immediately.

```bash
# On server via FTP/SSH — delete all of these:
add_gmail_admin.php, add_test_admin.php, reset_admin.php,
check_pass.php, debug_flavours.php, migrate_*.php,
test_mail.php, test_smtp.php, update_settings.php
```

---

## 🟠 HIGH SEVERITY ISSUES

---

### HIGH-01 — Order Confirmation Page Accessible by Anyone (Guest Orders)

**File:** `app/controllers/OrderController.php` — `confirm()` method  
**Risk:** IDOR — order details exposed if user_id is null (guest order)

```php
public function confirm(string $id): void
{
    $order = (new Order())->getOrderWithItems((int)$id);
    if (!$order) { $this->redirect('/'); }
    // Only checks ownership IF user_id is set — guest orders have user_id = null
    if ($order['user_id'] && $order['user_id'] !== Auth::id()) { $this->redirect('/'); }
    // A guest order (user_id=null) is visible to ANY visitor who knows the order ID!
```

**Fix:** For guest orders, verify via session token stored at checkout time, or only show for the current session:

```php
// Store order ID in session at checkout completion
if (!$order['user_id']) {
    $recentOrders = Session::get('guest_order_ids', []);
    if (!in_array((int)$id, $recentOrders)) {
        $this->redirect('/');
    }
}
```

---

### HIGH-02 — Order Tracking Exposes Full Order Details Without Auth

**File:** `app/controllers/OrderController.php` — `track()` method  
**Risk:** Anyone knowing the order number can see full customer details

```php
public function track(string $orderNumber): void
{
    $order = (new Order())->getByOrderNumber($orderNumber);
    // No auth check, no ownership check — fully public
```

Order numbers follow a predictable pattern: `PS-20260601-0042`. Phone number, address, items, and total are all exposed.

**Fix:** Require login OR verify against the guest email entered at checkout:

```php
if ($order['user_id']) {
    if (!Auth::check() || $order['user_id'] !== Auth::id()) {
        $this->redirect('/login');
    }
} else {
    // Guest — require them to enter their email to verify
}
```

---

### HIGH-03 — CSRF Not Enforced on All State-Changing API Endpoints

**File:** `app/controllers/ApiController.php` — multiple methods  
**Risk:** Cross-site request forgery on cart, coupon, wishlist operations

The `CsrfMiddleware` runs on every POST, but the API endpoints use `X-CSRF-Token` header. However, the middleware checks `$_POST['_csrf_token']` first, then `HTTP_X_CSRF_TOKEN`. If neither is present, it still blocks — but the CSRF token is not rotated after successful API calls properly:

```php
// CsrfMiddleware rotates token on every POST
Session::set('csrf_token', bin2hex(random_bytes(32)));
```

This means after one API call, the token in the meta tag is stale. Subsequent JS calls with the original token will fail. The cart.js reads the token once at call time from the meta tag — this works, but if the page is cached or loaded via SPF, the token goes stale.

**Fix:** Stop rotating the CSRF token on every request (use a per-session token). Only regenerate on login/logout:

```php
// In CsrfMiddleware — REMOVE the rotation line
// Session::set('csrf_token', bin2hex(random_bytes(32))); // DELETE THIS
```

---

### HIGH-04 — Stock Decrement Not Atomic (Race Condition)

**File:** `app/models/Product.php` — `updateStock()` method  
**File:** `app/controllers/CheckoutController.php` — `placeOrder()` method  
**Risk:** Overselling — two simultaneous orders can both succeed for the last item

```php
// NOT ATOMIC — two concurrent requests both read stock=1, both proceed
public function updateStock(int $productId, ?int $variantId, int $qty, string $operation = 'decrement'): void
{
    $col = $operation === 'decrement' ? 'stock_quantity - ?' : 'stock_quantity + ?';
    $this->db->query("UPDATE products SET stock_quantity = $col WHERE id = ?", [$qty, $productId]);
}
```

There is no check to prevent stock going negative, and the stock check (if any) happens in a separate query before the UPDATE.

**Fix:** Use a single UPDATE with a WHERE clause to prevent negative stock:

```php
public function updateStock(int $productId, ?int $variantId, int $qty, string $operation = 'decrement'): bool
{
    if ($operation === 'decrement') {
        $rows = $this->db->query(
            "UPDATE products SET stock_quantity = stock_quantity - ? WHERE id = ? AND stock_quantity >= ?",
            [$qty, $productId, $qty]
        )->rowCount();
        return $rows > 0; // false = insufficient stock
    }
    $this->db->query("UPDATE products SET stock_quantity = stock_quantity + ? WHERE id = ?", [$qty, $productId]);
    return true;
}
```

---

### HIGH-05 — Password Reset Token Not Invalidated After Single Use in All Paths

**File:** `app/controllers/AuthController.php` — `resetForm()` method  
**Risk:** Reset link can be clicked multiple times (token not marked used until POST)

The `resetForm()` (GET) only checks `used = 0` but does not lock/mark the token. Between the user opening the reset form and submitting, the token remains valid. If the email is intercepted by a third party who clicks it first, both parties see the form.

**Fix:** Mark the token as "in-use" by storing the token in the session when the form is shown, and verify the session match on POST. Or immediately mark `used = 1` on GET and use a short session window.

---

### HIGH-06 — Missing Rate Limiting on Password Reset Endpoint

**File:** `app/controllers/AuthController.php` — `forgot()` method  
**Risk:** Email flooding — attacker can trigger unlimited password reset emails for any user

```php
public function forgot(): void
{
    $email = strtolower(trim($this->request->post('email', '')));
    $user  = $this->userModel->findByEmail($email);
    if ($user) {
        // Sends email every single time — no rate limit
        send_password_reset($email, $token);
    }
}
```

**Fix:** Add IP-based rate limiting (reuse the login attempts pattern):

```php
$ip    = $this->request->ip();
$since = date('Y-m-d H:i:s', strtotime('-15 minutes'));
$count = (int)$this->db->fetch(
    'SELECT COUNT(*) as cnt FROM login_attempts WHERE ip_address = ? AND attempted_at >= ?',
    [$ip . '_reset', $since]
)['cnt'];
if ($count >= 3) {
    $this->flash('error', 'Too many requests. Please wait 15 minutes.');
    $this->redirect('/forgot-password');
}
$this->db->insert('login_attempts', ['ip_address' => $ip . '_reset', 'email' => $email]);
```

---

### HIGH-07 — `redirect()` Helper in functions.php Has No Open Redirect Protection

**File:** `app/helpers/functions.php` — `redirect()` function  
**Risk:** Open redirect — if this helper is used anywhere instead of `$this->redirect()`, there is no host validation

```php
// VULNERABLE global helper
function redirect(string $url): void
{
    header("Location: $url"); // no validation!
    exit;
}
```

The `Controller::redirect()` has proper open-redirect protection, but this global helper does not. If any code uses `redirect($userInput)`, the app is vulnerable.

**Fix:**

```php
function redirect(string $url): void
{
    $parsed  = parse_url($url);
    $appHost = parse_url($_ENV['APP_URL'] ?? '', PHP_URL_HOST);
    if (!empty($parsed['host']) && $parsed['host'] !== $appHost) {
        $url = '/';
    }
    header("Location: $url");
    exit;
}
```

---

### HIGH-08 — `variations_json` POST Field Not Size-Limited (DoS Vector)

**File:** `app/controllers/AdminController.php` — `handleVariationsJson()` method  
**Risk:** Admin could accidentally or maliciously POST a massive JSON blob causing memory exhaustion

```php
$json = trim($this->request->post('variations_json', ''));
$payload = json_decode($json, true); // no size check before decode
```

**Fix:** Add a size check:

```php
if (strlen($json) > 512 * 1024) { // 512KB max
    throw new \RuntimeException('Variations data too large');
}
```

---

### HIGH-09 — No Content-Security-Policy Header (XSS Amplifier)

**File:** `public_html/index.php`  
**Risk:** Without CSP, any XSS vulnerability has full impact — cookie theft, DOM manipulation

The app sets a `frame-ancestors` CSP but **no full Content-Security-Policy** to prevent inline script injection.

**Fix:** Add to `index.php`:

```php
header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://unpkg.com https://cdnjs.cloudflare.com https://js.stripe.com; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdnjs.cloudflare.com; font-src 'self' https://fonts.gstatic.com https://cdnjs.cloudflare.com; img-src 'self' data: https:; connect-src 'self'; frame-src https://js.stripe.com;");
```

---

## 🟡 MEDIUM SEVERITY ISSUES

---

### MED-01 — Coupon Discount Applied Before Stock Check

**File:** `app/controllers/CheckoutController.php`  
**Risk:** User applies coupon, checkout fails due to stock, coupon `used_count` is still incremented

The `markUsed()` call happens after `createOrder()` succeeds but the stock check is silent — if `updateStock()` fails there is no rollback of the order or coupon usage.

**Fix:** Wrap the entire order creation, stock decrement, and coupon marking in a single database transaction.

---

### MED-02 — User Can Add Unlimited Quantities Bypassing Stock Check

**File:** `app/controllers/CartController.php` — `add()` method  
**Risk:** Users can add 999 units of a product with 1 in stock

There is no stock validation when adding to cart. The stock is only decremented at checkout, but there is no guard against a cart quantity exceeding available stock.

**Fix:** Add a stock check in `CartController::add()`:

```php
$stock = $product['stock_quantity'] ?? 0;
if (!$product['allow_backorder'] && $qty > $stock) {
    $this->json(['success' => false, 'error' => "Only {$stock} units available"], 400);
}
```

---

### MED-03 — Session Cookie `secure` Flag Disabled

**File:** `app/core/Session.php` — Line ~17  
**Risk:** Session cookie sent over HTTP if `SESSION_SECURE` env var is not set

```php
$secure = $isHttps && filter_var($_ENV['SESSION_SECURE'] ?? false, FILTER_VALIDATE_BOOLEAN);
```

If `SESSION_SECURE` is not set in `.env`, `secure=false` even on HTTPS. The default should be `true` when HTTPS is detected.

**Fix:**

```php
$secure = $isHttps; // Always use secure when HTTPS is detected, no env var needed
```

---

### MED-04 — Reward Points Race Condition

**File:** `app/models/User.php` — `addRewardPoints()` / `deductRewardPoints()`  
**Risk:** Two concurrent checkouts can both read the same balance and apply a double-spend of reward points

```php
$user    = $this->find($userId);
$balance = ($user['reward_points'] ?? 0) + $points; // read-then-write, not atomic
$this->update($userId, ['reward_points' => $balance]);
```

**Fix:** Use atomic SQL:

```php
$this->db->query(
    "UPDATE users SET reward_points = reward_points + ? WHERE id = ?",
    [$points, $userId]
);
```

---

### MED-05 — Admin Brand/Banner/Coupon Update Routes Have No CSRF Token in Forms

**File:** `app/views/pages/admin/brands.php`, `banners.php`, `coupons.php`  
**Risk:** Admin forms without `csrf_field()` are vulnerable to CSRF attacks

Some admin update forms use inline JS `fetch()` calls that include the CSRF token, but the HTML forms (for update/delete) need to be verified to have `<?= csrf_field() ?>` included.

---

### MED-06 — `php://input` Read Twice Can Return Empty on Second Read

**File:** `app/controllers/CartController.php` — `add()` and `editItem()` methods  
**Risk:** Intermittent failures when JSON body is large

```php
$raw  = file_get_contents('php://input');
$json = $raw ? (json_decode($raw, true) ?? []) : [];
$data = !empty($json) ? $json : $this->request->all();
```

`php://input` is a stream — if `Request` already read it, this second read returns empty. This pattern exists in multiple controllers.

**Fix:** Read the request body once in `Request::json()` and cache it:

```php
// In Request.php — cache the body
private ?string $rawBody = null;
public function rawBody(): string {
    return $this->rawBody ??= (file_get_contents('php://input') ?: '');
}
public function json(): array {
    return json_decode($this->rawBody(), true) ?? [];
}
```

---

### MED-07 — Predictable Order Number Format

**File:** `app/controllers/CheckoutController.php`  
**Risk:** Order numbers are guessable (`PS-20260601-0042`) — enables order enumeration

```php
$orderNumber = 'PS-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
```

`rand()` is not cryptographically secure and the format is highly predictable.

**Fix:**

```php
$orderNumber = 'PS-' . date('Ymd') . '-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));
```

---

### MED-08 — Contact Form Has No Rate Limiting or Spam Protection

**File:** `app/controllers/HomeController.php` — `contactSubmit()` method  
**Risk:** Spam flood, email bombing the store's inbox

No CAPTCHA, no rate limit, no honeypot field on the contact form.

**Fix:** Add IP-based rate limiting (3 submissions per hour) and a honeypot hidden field.

---

### MED-09 — `product_type` Field Not Used to Enforce Variant Logic

**File:** `app/controllers/CheckoutController.php`, `CartController.php`  
**Risk:** A "simple" product can be added to cart with a `combination_id` of another product

The `product_type` column exists but is never validated when adding to cart. A `combination_id` is not verified to belong to the specified `product_id`.

**Fix:** In `CartController::add()`, verify `combination_id` belongs to `product_id`:

```php
if ($combinationId) {
    $combo = $this->db->fetch(
        'SELECT id FROM product_variation_combinations WHERE id = ? AND product_id = ? AND is_active = 1',
        [$combinationId, $productId]
    );
    if (!$combo) {
        $this->json(['success' => false, 'error' => 'Invalid product option'], 400);
    }
}
```

---

### MED-10 — Newsletter Subscription Stores Emails Without Consent Timestamp

**File:** `app/controllers/ApiController.php` — `newsletterSubscribe()`  
**Risk:** GDPR non-compliance — no consent record, no IP, no timestamp per subscription

```php
$this->db->insert('newsletter_subscribers', ['email' => $email]);
// No: ip_address, consent_given_at, source_page
```

**Fix:** Store IP and consent timestamp:

```php
$this->db->insert('newsletter_subscribers', [
    'email'            => $email,
    'ip_address'       => $this->request->ip(),
    'consent_given_at' => date('Y-m-d H:i:s'),
]);
```

---

### MED-11 — Error Messages Reveal Whether an Email Exists (Registration)

**File:** `app/controllers/AuthController.php` — `register()` method  
**Risk:** User enumeration — attacker can check if any email is registered

```php
if ($this->userModel->findByEmail($email)) {
    $errors['email'] = 'Email already registered.'; // ← reveals existence
}
```

**Fix:** Use a generic message: `'An account with this email may already exist. Try logging in.'`

---

## 🟢 LOW SEVERITY / INFORMATIONAL

---

### LOW-01 — `debug` Mode May Be Enabled in Production

**File:** `public_html/index.php` / `app/config/app.php`  
If `APP_DEBUG=true` in `.env`, full error stack traces are displayed to users. Verify `.env` has `APP_DEBUG=false` in production.

---

### LOW-02 — Session Name is Generic (`SS_SESS`)

**File:** `app/core/Session.php`  
The session name `SS_SESS` was likely inherited from "Sultan's Smoke" (old project name). Update to `PS_SESS` to match the Phantom Smoking branding.

---

### LOW-03 — DB Error Exposes Connection String on Failure

**File:** `app/core/Database.php`  
```php
die(json_encode(['error' => 'Database connection failed']));
```
This is acceptable for API calls but returns JSON for HTML page requests. Should redirect to a friendly error page for non-API requests.

---

### LOW-04 — `payment_method` Column Type Too Restrictive

**File:** `database/schema.sql` — `orders` table  
The `payment_method` column is `ENUM('cod','card','apple_pay','bank_transfer')` but the app inserts values like `'stripe'`, `'telr'`, `'tabby'`, `'tamara'`, `'card_on_delivery'`, `'payment_link_on_delivery'`. This causes silent data truncation on some MySQL configurations.  
**Fix:** Change to `VARCHAR(50)`.

---

### LOW-05 — No `robots.txt` to Block Admin/API from Search Engines

`/admin`, `/api/`, `/account` should be blocked from crawlers.

**Fix:** Create `public_html/robots.txt`:

```
User-agent: *
Disallow: /admin/
Disallow: /api/
Disallow: /account/
Disallow: /checkout/
Disallow: /cart/
Disallow: /login
Disallow: /register
Disallow: /otp/
```

---

### LOW-06 — All Admin Actions Use the Same Role Check (`isAdmin`)

**File:** `app/core/Auth.php`  
The `isAdmin()` method returns `true` for both `admin` and `manager` roles. There is no granular permission system — managers have full admin access including destructive operations (product delete, customer ban). Consider separating these privileges.

---

### LOW-07 — OTP Stored in Plain Text in Database

**File:** `app/controllers/AuthController.php` — `sendOtp()` method  
OTP codes are stored as plain text in `otp_verifications`. If the DB is breached, all active OTPs are exposed.  
**Fix:** Store as `hash('sha256', $code)` and compare with `hash_equals(hash('sha256', $inputCode), $stored)`.

---

### LOW-08 — `sw.js` (Service Worker) Has No Cache Invalidation Strategy

**File:** `public_html/sw.js`  
Users on the PWA may see stale content after deployments. Ensure the service worker version is bumped with each deployment.

---

### LOW-09 — Product Slug Generated from Name Only, Not Unique Across Brands

**File:** `app/controllers/AdminController.php` — `uniqueSlug()`  
Two products from different brands with the same name could conflict during slug generation (race condition on simultaneous creates). The existing `uniqueSlug()` loop handles eventual uniqueness but is not atomic.

---

### LOW-10 — Missing `alt` Text on Some Dynamically Generated Images

**File:** `app/views/components/product-card.php`, cart drawer  
Accessibility issue — product images in the cart drawer use `alt="${item.name}"` in JS (good), but some server-side rendered product cards use `alt=""` fallback.

---

### LOW-11 — Admin Product Destroy Does Not Delete New Variation System Data

**File:** `app/controllers/ApiController.php` — `adminProductDestroy()`  
```php
$this->db->delete('product_images', ...);
$this->db->delete('product_flavours', ...);
$this->db->delete('product_variant_types', ...); // OLD system only
// Missing: product_variation_types (cascade should handle it, but explicit is safer)
```
Cascade FK constraints should handle this, but it's worth being explicit.

---

### LOW-12 — `manifest.json` References `icon-192.png` Which May Not Exist on Live

**File:** `public_html/manifest.json`  
Before the favicon generation script was run, `icon-192.png` and `icon-512.png` did not exist. These are now generated locally but must be uploaded to live.

---

### LOW-13 — No HTTP Strict Transport Security (HSTS) Header

**File:** `public_html/index.php`  
HSTS forces browsers to always use HTTPS. Add:

```php
if ($isHttps) {
    header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
}
```

---

### LOW-14 — `flash_get()` Called Multiple Times Clears on First Read

**File:** `app/helpers/functions.php`  
`flash_get('errors')` in a view will return the errors on first call and `null` on second call in the same request. Some views call it twice for different checks. This is by design (flash = one-read), but the pattern is fragile if views are refactored.

---

## Access Control Matrix

| Route | Auth Required | Admin Only | Current State |
|---|---|---|---|
| `/admin/*` | ✅ | ✅ | ✅ Correct |
| `/api/admin/*` | ✅ | ✅ | ✅ Correct |
| `/account/*` | ✅ | ❌ | ✅ Correct |
| `/account/order/{id}` | ✅ | ❌ | ✅ Checks ownership |
| `/order/confirm/{id}` | ❌ (guest) | ❌ | ⚠️ See CRIT-03 / HIGH-01 |
| `/track/{order_number}` | ❌ | ❌ | 🔴 Exposes PII — HIGH-02 |
| `/api/products/{id}/variations` POST | ❌ | Should be ✅ | ✅ Has `requireAdmin()` |
| `/api/wishlist/toggle` | ✅ | ❌ | ✅ Correct |
| `/api/reviews` POST | ✅ | ❌ | ✅ Correct |
| `/api/cart/*` | ❌ (session) | ❌ | ✅ Correct (session-based) |
| `/payment/*/success` | ❌ | ❌ | 🔴 No ownership check — CRIT-03 |
| `/payment/tamara/webhook` | ❌ | ❌ | 🔴 No signature — CRIT-04 |

---

## Functionality Test Results

| Feature | Status | Notes |
|---|---|---|
| User Registration + OTP | ✅ Pass | OTP flow complete, rate-limited |
| Login + OTP | ✅ Pass | 5-attempt lockout works |
| Login Brute Force Protection | ✅ Pass | 5 attempts / 15 min per IP |
| Password Reset | ✅ Pass | Token expiry works, used=1 on POST |
| Age Gate | ⚠️ Partial | Cookie not secure in prod — CRIT-02 |
| Product Listing / Filtering | ✅ Pass | Pagination, sort, filters work |
| Product Detail + Variants | ✅ Pass | New variation system works correctly |
| Add to Cart (product card popup) | ✅ Pass | Fixed in last session |
| Cart Page | ✅ Pass | Qty update, remove, coupon work |
| Variant Price in Cart | ✅ Pass | Fixed in last session |
| Variant Label in Cart/Checkout | ✅ Pass | Fixed in last session |
| Checkout Flow | ✅ Pass | Address, delivery, payment selection |
| COD Order Placement | ✅ Pass | Creates order, clears cart |
| Online Payment Redirect | ✅ Pass | Gateway redirects work |
| Payment Callback Security | 🔴 Fail | No ownership check — CRIT-03 |
| Order Confirmation Page | ⚠️ Partial | Guest order IDOR — HIGH-01 |
| Order Tracking | 🔴 Fail | No auth — HIGH-02 |
| Account Dashboard | ✅ Pass | Stats, recent orders shown |
| Account Orders | ✅ Pass | Pagination, detail page |
| Address Management | ✅ Pass | Add/edit/delete with ownership |
| Wishlist | ✅ Pass | Toggle works, login required |
| Reward Points | ⚠️ Partial | Race condition possible — MED-04 |
| Admin Dashboard | ✅ Pass | Stats, charts, low stock |
| Admin Products CRUD | ✅ Pass | Create with variations works now |
| Admin Variations (Edit) | ✅ Pass | Pill UI, price correct |
| Admin Orders Management | ✅ Pass | Status update, detail view |
| Admin Customer Management | ✅ Pass | Ban/unban, view orders |
| Admin Reports + Export | ⚠️ Partial | XSS in print output — CRIT-05 |
| Admin Invoice PDF | ⚠️ Partial | XSS in output — CRIT-05 |
| Admin Settings | ✅ Pass | Payment toggles, store settings |
| Coupon System | ⚠️ Partial | Race condition on stock — MED-01 |
| Delivery Zone Fees | ✅ Pass | Calculates correctly by emirate |
| PWA / Service Worker | ✅ Pass | Install banner, manifest set |
| Favicon | ✅ Pass | Generated, all sizes present |
| Email (OTP, Order Confirm) | ✅ Pass | Templates look correct |
| Search | ✅ Pass | Full-text + LIKE fallback |
| Responsive / Mobile | ✅ Pass | Mobile nav, cart drawer work |

---

## Priority Fix Order

### Immediate (Before going live / before real transactions)

1. **CRIT-06** — Delete all debug scripts from `public_html`
2. **CRIT-01** — Remove OTP bypass hardcoded emails
3. **CRIT-04** — Add Tamara webhook signature verification
4. **CRIT-03** — Add ownership check to all payment callbacks
5. **CRIT-02** — Fix age gate cookie `secure` flag
6. **CRIT-05** — Escape all output in reports and invoice HTML
7. **HIGH-01** — Fix guest order confirmation IDOR
8. **HIGH-02** — Require auth or email verification for order tracking
9. **HIGH-04** — Make stock decrement atomic
10. **LOW-04** — Fix `payment_method` ENUM to VARCHAR(50)

### Short Term (Within 1 week)

11. **HIGH-03** — Stop rotating CSRF token on every POST
12. **HIGH-06** — Add rate limiting to password reset
13. **HIGH-07** — Fix open redirect in global `redirect()` helper
14. **MED-02** — Add stock validation when adding to cart
15. **MED-03** — Fix session cookie `secure` flag default
16. **MED-07** — Use `random_bytes()` for order number generation
17. **LOW-05** — Create `robots.txt`
18. **LOW-13** — Add HSTS header

### Medium Term

19. **MED-04** — Atomic reward points updates
20. **MED-08** — Add rate limiting to contact form
21. **MED-09** — Validate `combination_id` belongs to `product_id`
22. **MED-10** — Add GDPR consent fields to newsletter
23. **HIGH-09** — Implement Content-Security-Policy header
24. **LOW-07** — Hash OTP codes in database
25. **LOW-02** — Update session name to `PS_SESS`

---

## Positive Security Findings

The following security controls are correctly implemented and should be maintained:

- ✅ All database queries use **PDO prepared statements** — no SQL injection possible through standard query methods
- ✅ All output in views uses `e()` / `htmlspecialchars()` — XSS protected in normal views (reports are the exception)
- ✅ **CSRF middleware** applied to all POST requests globally
- ✅ **Session hardening** — `httponly`, `use_strict_mode`, `use_only_cookies`, ID regeneration
- ✅ **Bcrypt with cost 12** for password hashing
- ✅ **Open redirect protection** in `Controller::redirect()`
- ✅ **Login brute-force protection** — 5 attempts / 15 min per IP
- ✅ **OTP brute-force protection** — 5 attempts then invalidate, 3 resends / 10 min
- ✅ **Image upload validation** — MIME type via magic bytes + `getimagesize()` polyglot check
- ✅ **Address ownership** — `user_id` always verified in address CRUD
- ✅ **Order ownership** — checked in `AccountController::orderDetail()`
- ✅ **Password strength** validation on register/reset/change
- ✅ **Admin middleware** applied via `$this->requireAdmin()` in constructor of all admin controllers
- ✅ `X-Frame-Options`, `X-Content-Type-Options`, `Referrer-Policy`, `X-XSS-Protection` headers set
- ✅ PHP version header removed (`header_remove('X-Powered-By')`)
- ✅ Request size limited to 20MB before processing
- ✅ `.env` blocked by `.htaccess` rule
- ✅ `vendor/`, `logs/`, `scripts/` directories blocked by `.htaccess`

---

*Report generated by Amazon Q — Full codebase scan + manual analysis*  
*Phantom Smoking — phantomsmoking.ae*
