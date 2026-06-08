# 🔥 SULTAN'S SMOKE — Project Development Document
**Premium Tobacco, Cigars, Vape & Shisha Store | Dubai, UAE**
> Confidential | Version 1.0 | April 2025

---

## Table of Contents

1. [Project Overview & Business Requirements](#1-project-overview--business-requirements)
2. [Technology Stack & Architecture](#2-technology-stack--architecture)
3. [Website Structure & Navigation Design](#3-website-structure--navigation-design)
4. [Database Design — MySQL Schema](#4-database-design--mysql-schema)
5. [Frontend Design System (HTML/CSS/JS)](#5-frontend-design-system-htmlcssjs)
6. [All Page Specifications & Wireframes](#6-all-page-specifications--wireframes)
7. [PHP Backend Architecture](#7-php-backend-architecture)
8. [API Endpoints Documentation](#8-api-endpoints-documentation)
9. [User Authentication & Security System](#9-user-authentication--security-system)
10. [Product Management Module](#10-product-management-module)
11. [Shopping Cart & Checkout Flow](#11-shopping-cart--checkout-flow)
12. [Order Management System](#12-order-management-system)
13. [Age Verification & Compliance](#13-age-verification--compliance)
14. [Admin Panel Specifications](#14-admin-panel-specifications)
15. [Search & Filter Engine](#15-search--filter-engine)
16. [Loyalty & Rewards System](#16-loyalty--rewards-system)
17. [Delivery & Shipping Management](#17-delivery--shipping-management)
18. [Performance, SEO & Mobile Optimization](#18-performance-seo--mobile-optimization)
19. [File & Folder Structure](#19-file--folder-structure)
20. [Deployment & Hosting Requirements](#20-deployment--hosting-requirements)

---

## 1. Project Overview & Business Requirements

### 1.1 Project Identity

| Field | Detail |
|---|---|
| **Project Name** | Sultan's Smoke — Premium Tobacco, Vape & Shisha Store |
| **Domain** | www.sultanssmokedubai.com (example) |
| **Business Type** | B2C E-Commerce Retail — Tobacco, Cigars, Vape, Shisha |
| **Target Market** | UAE — Dubai, Abu Dhabi, Sharjah, Al Ain, Ras Al Khaimah |
| **Currency** | AED (UAE Dirham) |
| **Languages** | English (Primary), Arabic (Secondary) |
| **Platform Base** | Custom PHP + MySQL (No WordPress / Magento) |
| **Frontend Stack** | HTML5, CSS3 (vanilla + custom framework), Vanilla JavaScript |
| **Backend Stack** | PHP 8.2+ with MVC architecture |
| **Database** | MySQL 8.0+ |
| **Reference Design** | www.myvapery.shop (layout & UX inspiration) |

### 1.2 Business Goals

- Provide a premium, luxury-feel online shopping experience for tobacco, cigar, vape and shisha products in UAE
- Support fast 1-hour delivery and next-day delivery within Dubai and other Emirates
- Enable comprehensive product catalog with rich filtering by brand, type, flavor, nicotine strength, price
- Drive customer retention via a loyalty/reward points system
- Ensure full legal compliance with UAE age restrictions (18+) for tobacco and related products
- Support high-volume order processing with real-time inventory management
- Provide a powerful admin panel for store owners with sales analytics and inventory control

### 1.3 Product Categories

| Category | Sub-Categories | Key Attributes |
|---|---|---|
| **Cigars** | Premium, Mild, Full Body, Cuban, Dominican, Honduran | Size (Robusto/Churchill/Torpedo), Ring Gauge, Strength, Wrapper, Origin |
| **Cigarettes & Tobacco** | Premium Tobacco, Pipe Tobacco, Rolling Tobacco, Cigarettes | Brand, Cut, Flavor, Nicotine % |
| **Vape Devices** | Disposables, Pod Systems, Mods, Starter Kits | Puff Count, Battery mAh, Wattage Range, Coil Type |
| **E-Liquids** | Nic Salts, Freebase, Shortfills | Volume (30ml/50ml), Nicotine (3mg/6mg/12mg/20mg), Flavor, VG/PG Ratio |
| **Shisha / Hookah** | Devices, Charcoal, Molasses, Bowls, Hoses, Accessories | Size, Material, Flavor, Brand |
| **Nic Pouches** | Zyn, Velo, UBBS, Swag, Vito and others | Strength (mg), Flavor, Format, Count |
| **Hardware & Accessories** | Coils, Pods, Tanks, Batteries, Chargers, Tool Kits | Compatibility, Material, Resistance |
| **Deals & Bundles** | Clearance, Bundle Deals, Flash Sales | Discount %, Bundle Contents |

### 1.4 Key Features Summary

- Age verification gate (mandatory 18+ confirmation at entry)
- Multi-level mega navigation menu with category images
- Hero banner slider with promotional campaigns
- Brand showcase carousel
- Featured products, new arrivals, best sellers sections
- Advanced search with autocomplete and smart filters
- Product detail pages with image gallery, variant selector, related products
- Wishlist and product comparison
- Shopping cart with AJAX real-time updates
- Multi-step checkout with address management
- Multiple payment gateways (Cash on Delivery, Card, Apple Pay)
- Order tracking with real-time status updates
- Customer accounts with order history and saved addresses
- Loyalty reward points system
- Admin panel with full CRUD on products, orders, customers
- Mobile-first fully responsive design
- SEO optimized URLs, meta tags, structured data

---

## 2. Technology Stack & Architecture

### 2.1 Complete Technology Stack

| Layer | Technology | Version | Purpose |
|---|---|---|---|
| Frontend | HTML5 | Latest | Semantic page structure |
| Frontend | CSS3 | Latest | Styling, animations, responsive grid |
| Frontend | JavaScript (ES6+) | ES2022+ | Interactivity, AJAX, DOM manipulation |
| Frontend | Swiper.js | 11.x | Hero sliders, product carousels |
| Frontend | AOS.js | 2.x | Scroll animations |
| Frontend | Font Awesome | 6.x | Icon set |
| Backend | PHP | 8.2+ | Server-side logic, MVC framework |
| Backend | PHP-JWT | Latest | JSON Web Token auth |
| Backend | PHPMailer | 6.x | Email notifications |
| Database | MySQL | 8.0+ | Relational database |
| Web Server | Apache/Nginx | Latest | HTTP server |
| Session | PHP Sessions | — | Cart, auth, flash messages |
| Cache | PHP APCu / File Cache | — | Product & page caching |
| Image | GD Library / Imagick | — | Image resize, thumbnail generation |
| Payment | Telr / Network Intl. | — | UAE payment gateway |
| Maps | Google Maps JS API | — | Store locator, delivery areas |
| SMS | Unifonic / Twilio | — | Order SMS notifications |

### 2.2 MVC Architecture Overview

The backend follows a strict Model-View-Controller (MVC) pattern implemented in plain PHP without a heavy framework dependency, giving full control and performance optimization.

| MVC Layer | Responsibility |
|---|---|
| **Models** | Database interaction, business logic, data validation. One model per entity (Product, Order, User, Cart, Category). |
| **Views** | HTML templates with minimal PHP. Separate layout files for header, footer, sidebar. Template inheritance via PHP includes. |
| **Controllers** | Handle HTTP requests, call Models, pass data to Views. RESTful routing via custom Router class. |
| **Router** | URL parsing, route definitions, middleware execution (auth check, age gate, CSRF). |
| **Middleware** | Auth guard, age verification, CSRF protection, rate limiting, admin guard. |
| **Helpers** | Utility functions: `format_price()`, `slugify()`, `truncate()`, `generate_token()`. |

### 2.3 Application Flow Diagram

```
Browser → Apache/Nginx → index.php (Bootstrap) → Router → Middleware Stack
       → Controller → Model (DB Query) → View (Template) → Response
```

### 2.4 Hosting & Server Requirements

| Requirement | Specification |
|---|---|
| **Server Type** | VPS or Dedicated (NOT shared hosting) |
| **RAM** | Minimum 4GB RAM (8GB recommended) |
| **CPU** | 2 vCPU minimum, 4 vCPU recommended |
| **Storage** | 50GB SSD minimum (for product images & DB) |
| **OS** | Ubuntu 22.04 LTS |
| **Web Server** | Nginx 1.24+ (preferred) or Apache 2.4+ |
| **PHP** | PHP 8.2 with extensions: pdo, pdo_mysql, gd, mbstring, curl, json, zip, openssl |
| **MySQL** | MySQL 8.0+ or MariaDB 10.11+ |
| **SSL** | Let's Encrypt or commercial SSL (mandatory for payments) |
| **Backups** | Daily automated DB + files backup to cloud storage |
| **CDN** | Cloudflare (free tier minimum for static assets) |

---

## 3. Website Structure & Navigation Design

### 3.1 Site Map — All Pages

| Page Type | Pages Included |
|---|---|
| **Public — Main** | Home, About Us, Contact Us, Store Locator, Age Verification Gate |
| **Public — Products** | Category Listing, Sub-Category Listing, Product Detail, Brand Page, All Brands |
| **Public — Commerce** | Search Results, Wishlist (guest & logged in), Compare Products |
| **Public — Deals** | Deals & Offers, Flash Sale, Bundle Deals, Clearance |
| **Auth — Customer** | Login, Register, Forgot Password, Reset Password, Verify Email |
| **Account — Logged In** | Dashboard, My Orders, Order Detail, Addresses, Wishlist, Reward Points, Profile Edit, Change Password |
| **Checkout Flow** | Cart, Checkout Step 1 (Info), Checkout Step 2 (Delivery), Checkout Step 3 (Payment), Order Confirmation |
| **CMS / Info Pages** | FAQ, Shipping Policy, Returns Policy, Privacy Policy, Terms & Conditions, Blog (optional) |
| **Admin Panel** | Dashboard, Products, Categories, Orders, Customers, Coupons, Banners, Reports, Settings |

### 3.2 Mega Navigation Menu Structure

| Nav Item | Sub-Categories | Filters/Attributes |
|---|---|---|
| Home | — | — |
| **Cigars** | Premium / Mild / Medium / Full Body / Cuban / Dominican / Honduran / Torpedo / Robusto / Churchill | Strength, Size, Origin, Brand, Price Range |
| **Cigarettes & Tobacco** | Cigarettes / Premium Tobacco / Pipe Tobacco / Rolling Tobacco / Filter Tips | Brand, Flavor, Nicotine, Format |
| **Vapes & Disposables** | Disposable Vapes / Pod Systems / Box Mods / Starter Kits / Replacement Pods | Puff Count, Brand, Flavor, Nicotine, Battery |
| **E-Liquids** | Nic Salts / Freebase / Shortfills / 30ml / 50ml / 100ml | Flavor Profile, Nicotine Strength, VG/PG, Brand, Volume |
| **Shisha & Hookah** | Hookah Devices / Molasses & Flavors / Charcoal / Bowls / Hoses / Accessories | Brand, Size, Flavor, Material |
| **Nic Pouches** | Zyn / Velo / UBBS / Swag / Vito / Other Brands | Strength, Flavor, Count per Tin |
| **Hardware** | Devices / Coils / Pods / Tanks / Batteries / Chargers / Accessories | Brand, Compatibility, Material |
| **Deals** | Clearance / Bundles / Flash Sales / Today's Deals | Discount %, Category, Brand |
| **Shop By Brand** | All Brands A–Z grid with logo cards | — |

### 3.3 Header Layout Specification

- **Row 1 — Announcement Bar:** Full-width dark background, scrolling promotional text (e.g., "FREE DELIVERY OVER AED 100"), contact phone number right-aligned
- **Row 2 — Main Header:** Logo (left), Search bar with category filter (center), Account/Wishlist/Cart icons with badges (right). Sticky with box shadow on scroll.
- **Row 3 — Primary Navigation:** Horizontal mega menu bar, sticky on scroll, mobile hamburger toggle
- **Trust Badges Row** (below nav on home): Free Shipping | 1-Hour Delivery | Reward Points | Age Verified Store — with icons

### 3.4 Footer Layout Specification

- **Column 1 — Brand:** Logo, tagline, social media icons (Instagram, TikTok, Facebook, X/Twitter), newsletter signup
- **Column 2 — Quick Links:** Home, About Us, Contact, Blog, Store Locator, Careers
- **Column 3 — Customer Service:** FAQ, Track Order, Shipping Policy, Returns Policy, Privacy Policy, T&Cs
- **Column 4 — Contact Info:** Address (Dubai, UAE), Phone, Email, WhatsApp link, Business Hours
- **Bottom Bar:** Copyright, payment method icons, age warning badge (18+), UAE made stamp

---

## 4. Database Design — MySQL Schema

**Database:** `sultans_smoke_db` | **Charset:** `utf8mb4` | **Collation:** `utf8mb4_unicode_ci` | **Engine:** `InnoDB`
**Total Tables:** 38 | All tables use `AUTO_INCREMENT` primary keys | All timestamps use `DATETIME` with `DEFAULT CURRENT_TIMESTAMP`

### 4.1 Core Tables

#### TABLE: `categories`

| Column | Definition |
|---|---|
| id | INT UNSIGNED AUTO_INCREMENT PRIMARY KEY |
| parent_id | INT UNSIGNED NULL — References categories(id), NULL for top-level |
| name | VARCHAR(150) NOT NULL |
| slug | VARCHAR(160) NOT NULL UNIQUE |
| description | TEXT NULL |
| image | VARCHAR(255) NULL — Path to category image |
| banner_image | VARCHAR(255) NULL — Category page banner |
| icon | VARCHAR(100) NULL — Icon class or SVG name |
| meta_title | VARCHAR(160) NULL |
| meta_description | VARCHAR(320) NULL |
| position | SMALLINT UNSIGNED DEFAULT 0 — Sort order |
| is_active | TINYINT(1) DEFAULT 1 |
| show_in_menu | TINYINT(1) DEFAULT 1 |
| created_at | DATETIME DEFAULT CURRENT_TIMESTAMP |
| updated_at | DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP |

#### TABLE: `brands`

| Column | Definition |
|---|---|
| id | INT UNSIGNED AUTO_INCREMENT PRIMARY KEY |
| name | VARCHAR(100) NOT NULL |
| slug | VARCHAR(110) NOT NULL UNIQUE |
| logo | VARCHAR(255) NULL |
| banner_image | VARCHAR(255) NULL |
| description | TEXT NULL |
| country_of_origin | VARCHAR(100) NULL |
| website_url | VARCHAR(255) NULL |
| meta_title | VARCHAR(160) NULL |
| meta_description | VARCHAR(320) NULL |
| is_active | TINYINT(1) DEFAULT 1 |
| is_featured | TINYINT(1) DEFAULT 0 — Show in brand carousel |
| position | SMALLINT UNSIGNED DEFAULT 0 |
| created_at | DATETIME DEFAULT CURRENT_TIMESTAMP |
| updated_at | DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP |

#### TABLE: `products`

| Column | Definition |
|---|---|
| id | INT UNSIGNED AUTO_INCREMENT PRIMARY KEY |
| category_id | INT UNSIGNED NOT NULL — FK → categories(id) |
| brand_id | INT UNSIGNED NULL — FK → brands(id) |
| name | VARCHAR(255) NOT NULL |
| slug | VARCHAR(270) NOT NULL UNIQUE |
| sku | VARCHAR(100) NOT NULL UNIQUE |
| barcode | VARCHAR(100) NULL — EAN/UPC barcode |
| short_description | TEXT NULL |
| description | LONGTEXT NULL — Full HTML product description |
| price | DECIMAL(10,2) NOT NULL |
| compare_at_price | DECIMAL(10,2) NULL — Strike-through price |
| cost_price | DECIMAL(10,2) NULL — For margin calculation |
| tax_rate | DECIMAL(5,2) DEFAULT 5.00 — UAE VAT |
| tax_included | TINYINT(1) DEFAULT 1 |
| weight_grams | INT UNSIGNED NULL |
| stock_quantity | INT DEFAULT 0 |
| low_stock_threshold | INT DEFAULT 5 — Alert when below this |
| track_inventory | TINYINT(1) DEFAULT 1 |
| allow_backorder | TINYINT(1) DEFAULT 0 |
| product_type | ENUM('simple','variable','bundle') DEFAULT 'simple' |
| status | ENUM('active','draft','archived') DEFAULT 'active' |
| is_featured | TINYINT(1) DEFAULT 0 |
| is_new_arrival | TINYINT(1) DEFAULT 0 |
| is_best_seller | TINYINT(1) DEFAULT 0 |
| is_age_restricted | TINYINT(1) DEFAULT 1 — All products here are 18+ |
| nicotine_content_mg | DECIMAL(5,2) NULL — For vape/e-liquids |
| puff_count | INT NULL — For disposable vapes |
| volume_ml | DECIMAL(8,2) NULL — For e-liquids |
| flavor_profile | VARCHAR(255) NULL — Comma-separated flavors |
| cigar_size | VARCHAR(100) NULL — Robusto / Churchill etc. |
| cigar_strength | ENUM('mild','medium','medium-full','full') NULL |
| cigar_wrapper | VARCHAR(100) NULL |
| cigar_origin | VARCHAR(100) NULL |
| meta_title | VARCHAR(160) NULL |
| meta_description | VARCHAR(320) NULL |
| meta_keywords | VARCHAR(255) NULL |
| total_sold | INT UNSIGNED DEFAULT 0 |
| average_rating | DECIMAL(3,2) DEFAULT 0.00 |
| review_count | INT UNSIGNED DEFAULT 0 |
| reward_points | INT UNSIGNED DEFAULT 0 — Points earned on purchase |
| created_at | DATETIME DEFAULT CURRENT_TIMESTAMP |
| updated_at | DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP |

#### TABLE: `product_images`

| Column | Definition |
|---|---|
| id | INT UNSIGNED AUTO_INCREMENT PRIMARY KEY |
| product_id | INT UNSIGNED NOT NULL — FK → products(id) ON DELETE CASCADE |
| image_path | VARCHAR(255) NOT NULL |
| alt_text | VARCHAR(255) NULL |
| is_primary | TINYINT(1) DEFAULT 0 |
| position | SMALLINT UNSIGNED DEFAULT 0 |
| created_at | DATETIME DEFAULT CURRENT_TIMESTAMP |

#### TABLE: `product_variants`

| Column | Definition |
|---|---|
| id | INT UNSIGNED AUTO_INCREMENT PRIMARY KEY |
| product_id | INT UNSIGNED NOT NULL — FK → products(id) ON DELETE CASCADE |
| variant_name | VARCHAR(255) NOT NULL — e.g., Mango Ice 20mg |
| sku | VARCHAR(100) NOT NULL UNIQUE |
| price | DECIMAL(10,2) NOT NULL |
| compare_at_price | DECIMAL(10,2) NULL |
| stock_quantity | INT DEFAULT 0 |
| image_id | INT UNSIGNED NULL — FK → product_images(id) |
| is_active | TINYINT(1) DEFAULT 1 |
| position | SMALLINT UNSIGNED DEFAULT 0 |

#### TABLE: `product_variant_options`

| Column | Definition |
|---|---|
| id | INT UNSIGNED AUTO_INCREMENT PRIMARY KEY |
| variant_id | INT UNSIGNED NOT NULL — FK → product_variants(id) ON DELETE CASCADE |
| option_name | VARCHAR(100) NOT NULL — e.g., 'Flavor', 'Nicotine', 'Size' |
| option_value | VARCHAR(100) NOT NULL — e.g., 'Mango Ice', '20mg', 'Robusto' |

#### TABLE: `product_categories` (Many-to-Many)

| Column | Definition |
|---|---|
| product_id | INT UNSIGNED NOT NULL — FK → products(id) ON DELETE CASCADE |
| category_id | INT UNSIGNED NOT NULL — FK → categories(id) ON DELETE CASCADE |
| PRIMARY KEY | (product_id, category_id) |

#### TABLE: `product_attributes`

| Column | Definition |
|---|---|
| id | INT UNSIGNED AUTO_INCREMENT PRIMARY KEY |
| product_id | INT UNSIGNED NOT NULL — FK → products(id) ON DELETE CASCADE |
| attribute_name | VARCHAR(100) NOT NULL — e.g., 'VG/PG Ratio', 'Ring Gauge' |
| attribute_value | VARCHAR(255) NOT NULL |
| position | SMALLINT UNSIGNED DEFAULT 0 |

### 4.2 User & Authentication Tables

#### TABLE: `users`

| Column | Definition |
|---|---|
| id | INT UNSIGNED AUTO_INCREMENT PRIMARY KEY |
| first_name | VARCHAR(80) NOT NULL |
| last_name | VARCHAR(80) NOT NULL |
| email | VARCHAR(180) NOT NULL UNIQUE |
| phone | VARCHAR(20) NULL |
| password_hash | VARCHAR(255) NOT NULL — bcrypt |
| role | ENUM('customer','admin','manager','staff') DEFAULT 'customer' |
| email_verified | TINYINT(1) DEFAULT 0 |
| email_verify_token | VARCHAR(100) NULL |
| age_verified | TINYINT(1) DEFAULT 0 — 18+ confirmed |
| age_verified_at | DATETIME NULL |
| date_of_birth | DATE NULL |
| gender | ENUM('male','female','prefer_not_to_say') NULL |
| avatar | VARCHAR(255) NULL |
| reward_points | INT UNSIGNED DEFAULT 0 |
| total_spent | DECIMAL(12,2) DEFAULT 0.00 |
| total_orders | INT UNSIGNED DEFAULT 0 |
| newsletter_subscribed | TINYINT(1) DEFAULT 0 |
| sms_subscribed | TINYINT(1) DEFAULT 0 |
| last_login_at | DATETIME NULL |
| last_login_ip | VARCHAR(45) NULL |
| is_active | TINYINT(1) DEFAULT 1 |
| banned_reason | TEXT NULL |
| notes | TEXT NULL — Admin-only internal notes |
| created_at | DATETIME DEFAULT CURRENT_TIMESTAMP |
| updated_at | DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP |

#### TABLE: `user_addresses`

| Column | Definition |
|---|---|
| id | INT UNSIGNED AUTO_INCREMENT PRIMARY KEY |
| user_id | INT UNSIGNED NOT NULL — FK → users(id) ON DELETE CASCADE |
| label | VARCHAR(50) DEFAULT 'Home' — Home / Work / Other |
| full_name | VARCHAR(160) NOT NULL |
| phone | VARCHAR(20) NOT NULL |
| address_line1 | VARCHAR(255) NOT NULL |
| address_line2 | VARCHAR(255) NULL |
| area | VARCHAR(100) NULL — Dubai Marina, JBR, Downtown etc. |
| city | VARCHAR(100) NOT NULL DEFAULT 'Dubai' |
| emirate | VARCHAR(100) NOT NULL DEFAULT 'Dubai' |
| country | VARCHAR(100) NOT NULL DEFAULT 'UAE' |
| latitude | DECIMAL(10,8) NULL — For map pin |
| longitude | DECIMAL(11,8) NULL — For map pin |
| is_default | TINYINT(1) DEFAULT 0 |
| created_at | DATETIME DEFAULT CURRENT_TIMESTAMP |

#### TABLE: `password_resets`

| Column | Definition |
|---|---|
| id | INT UNSIGNED AUTO_INCREMENT PRIMARY KEY |
| email | VARCHAR(180) NOT NULL |
| token | VARCHAR(100) NOT NULL UNIQUE |
| expires_at | DATETIME NOT NULL |
| used | TINYINT(1) DEFAULT 0 |
| created_at | DATETIME DEFAULT CURRENT_TIMESTAMP |

### 4.3 Cart & Session Tables

#### TABLE: `carts`

| Column | Definition |
|---|---|
| id | INT UNSIGNED AUTO_INCREMENT PRIMARY KEY |
| user_id | INT UNSIGNED NULL — FK → users(id), NULL for guest |
| session_id | VARCHAR(128) NOT NULL — PHP session ID for guests |
| coupon_id | INT UNSIGNED NULL — FK → coupons(id) |
| discount_amount | DECIMAL(10,2) DEFAULT 0.00 |
| expires_at | DATETIME NULL — Guest carts expire after 30 days |
| created_at | DATETIME DEFAULT CURRENT_TIMESTAMP |
| updated_at | DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP |

#### TABLE: `cart_items`

| Column | Definition |
|---|---|
| id | INT UNSIGNED AUTO_INCREMENT PRIMARY KEY |
| cart_id | INT UNSIGNED NOT NULL — FK → carts(id) ON DELETE CASCADE |
| product_id | INT UNSIGNED NOT NULL — FK → products(id) |
| variant_id | INT UNSIGNED NULL — FK → product_variants(id) |
| quantity | SMALLINT UNSIGNED NOT NULL DEFAULT 1 |
| unit_price | DECIMAL(10,2) NOT NULL — Price at time of add |
| created_at | DATETIME DEFAULT CURRENT_TIMESTAMP |
| updated_at | DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP |

### 4.4 Order Tables

#### TABLE: `orders`

| Column | Definition |
|---|---|
| id | INT UNSIGNED AUTO_INCREMENT PRIMARY KEY |
| order_number | VARCHAR(20) NOT NULL UNIQUE — e.g., SS-20250401-0001 |
| user_id | INT UNSIGNED NULL — FK → users(id), NULL if guest |
| guest_email | VARCHAR(180) NULL — For guest orders |
| shipping_address_id | INT UNSIGNED NULL — FK → user_addresses(id) |
| shipping_name | VARCHAR(160) NOT NULL — Snapshot at order time |
| shipping_phone | VARCHAR(20) NOT NULL |
| shipping_address_line1 | VARCHAR(255) NOT NULL |
| shipping_address_line2 | VARCHAR(255) NULL |
| shipping_area | VARCHAR(100) NULL |
| shipping_city | VARCHAR(100) NOT NULL |
| shipping_emirate | VARCHAR(100) NOT NULL |
| shipping_country | VARCHAR(100) DEFAULT 'UAE' |
| billing_same_as_shipping | TINYINT(1) DEFAULT 1 |
| subtotal | DECIMAL(10,2) NOT NULL |
| discount_amount | DECIMAL(10,2) DEFAULT 0.00 |
| shipping_cost | DECIMAL(10,2) DEFAULT 0.00 |
| tax_amount | DECIMAL(10,2) DEFAULT 0.00 |
| total_amount | DECIMAL(10,2) NOT NULL |
| reward_points_earned | INT UNSIGNED DEFAULT 0 |
| reward_points_used | INT UNSIGNED DEFAULT 0 |
| reward_points_discount | DECIMAL(10,2) DEFAULT 0.00 |
| coupon_id | INT UNSIGNED NULL — FK → coupons(id) |
| coupon_code | VARCHAR(50) NULL — Snapshot |
| payment_method | ENUM('cod','card','apple_pay','bank_transfer') NOT NULL |
| payment_status | ENUM('pending','paid','failed','refunded','partially_refunded') DEFAULT 'pending' |
| payment_transaction_id | VARCHAR(255) NULL |
| payment_gateway_response | JSON NULL |
| order_status | ENUM('pending','confirmed','processing','packed','out_for_delivery','delivered','cancelled','returned') DEFAULT 'pending' |
| delivery_type | ENUM('standard','express_1hr','next_day','pickup') DEFAULT 'standard' |
| delivery_slot | VARCHAR(100) NULL — e.g., '2PM - 4PM' |
| estimated_delivery_at | DATETIME NULL |
| delivered_at | DATETIME NULL |
| driver_id | INT UNSIGNED NULL — FK → drivers(id) |
| tracking_number | VARCHAR(100) NULL |
| customer_notes | TEXT NULL |
| admin_notes | TEXT NULL |
| cancellation_reason | TEXT NULL |
| is_gift | TINYINT(1) DEFAULT 0 |
| gift_message | TEXT NULL |
| ip_address | VARCHAR(45) NULL |
| created_at | DATETIME DEFAULT CURRENT_TIMESTAMP |
| updated_at | DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP |

#### TABLE: `order_items`

| Column | Definition |
|---|---|
| id | INT UNSIGNED AUTO_INCREMENT PRIMARY KEY |
| order_id | INT UNSIGNED NOT NULL — FK → orders(id) ON DELETE CASCADE |
| product_id | INT UNSIGNED NOT NULL — FK → products(id) |
| variant_id | INT UNSIGNED NULL — FK → product_variants(id) |
| product_name | VARCHAR(255) NOT NULL — Snapshot |
| product_sku | VARCHAR(100) NOT NULL — Snapshot |
| variant_name | VARCHAR(255) NULL — Snapshot |
| quantity | SMALLINT UNSIGNED NOT NULL |
| unit_price | DECIMAL(10,2) NOT NULL |
| discount_amount | DECIMAL(10,2) DEFAULT 0.00 |
| tax_amount | DECIMAL(10,2) DEFAULT 0.00 |
| total_price | DECIMAL(10,2) NOT NULL |
| product_image | VARCHAR(255) NULL — Snapshot of image path |

#### TABLE: `order_status_history`

| Column | Definition |
|---|---|
| id | INT UNSIGNED AUTO_INCREMENT PRIMARY KEY |
| order_id | INT UNSIGNED NOT NULL — FK → orders(id) ON DELETE CASCADE |
| status | VARCHAR(50) NOT NULL |
| note | TEXT NULL |
| created_by_user_id | INT UNSIGNED NULL — Admin who made change |
| created_at | DATETIME DEFAULT CURRENT_TIMESTAMP |

### 4.5 Promotions & Coupons Tables

#### TABLE: `coupons`

| Column | Definition |
|---|---|
| id | INT UNSIGNED AUTO_INCREMENT PRIMARY KEY |
| code | VARCHAR(50) NOT NULL UNIQUE |
| description | VARCHAR(255) NULL |
| type | ENUM('percentage','fixed_amount','free_shipping') NOT NULL |
| value | DECIMAL(10,2) NOT NULL — % or AED amount |
| min_order_amount | DECIMAL(10,2) DEFAULT 0.00 |
| max_discount_amount | DECIMAL(10,2) NULL — Cap on % discounts |
| usage_limit | INT NULL — NULL = unlimited |
| usage_per_user | INT DEFAULT 1 |
| used_count | INT DEFAULT 0 |
| applies_to | ENUM('all','category','product','brand') DEFAULT 'all' |
| applies_to_ids | JSON NULL — Array of IDs |
| start_date | DATETIME NULL |
| end_date | DATETIME NULL |
| is_active | TINYINT(1) DEFAULT 1 |
| created_at | DATETIME DEFAULT CURRENT_TIMESTAMP |

#### TABLE: `banners`

| Column | Definition |
|---|---|
| id | INT UNSIGNED AUTO_INCREMENT PRIMARY KEY |
| title | VARCHAR(255) NOT NULL |
| subtitle | VARCHAR(255) NULL |
| image_desktop | VARCHAR(255) NOT NULL |
| image_mobile | VARCHAR(255) NULL |
| link_url | VARCHAR(500) NULL |
| link_text | VARCHAR(100) NULL — CTA button text |
| position | ENUM('hero','secondary','category_top','popup') DEFAULT 'hero' |
| sort_order | SMALLINT UNSIGNED DEFAULT 0 |
| start_date | DATETIME NULL |
| end_date | DATETIME NULL |
| is_active | TINYINT(1) DEFAULT 1 |
| created_at | DATETIME DEFAULT CURRENT_TIMESTAMP |

### 4.6 Reviews, Wishlist & Loyalty Tables

#### TABLE: `product_reviews`

| Column | Definition |
|---|---|
| id | INT UNSIGNED AUTO_INCREMENT PRIMARY KEY |
| product_id | INT UNSIGNED NOT NULL — FK → products(id) ON DELETE CASCADE |
| user_id | INT UNSIGNED NOT NULL — FK → users(id) ON DELETE CASCADE |
| order_item_id | INT UNSIGNED NULL — Verified purchase link |
| rating | TINYINT UNSIGNED NOT NULL — 1 to 5 |
| title | VARCHAR(255) NULL |
| body | TEXT NULL |
| is_verified_purchase | TINYINT(1) DEFAULT 0 |
| status | ENUM('pending','approved','rejected') DEFAULT 'pending' |
| admin_reply | TEXT NULL |
| helpful_count | INT UNSIGNED DEFAULT 0 |
| created_at | DATETIME DEFAULT CURRENT_TIMESTAMP |

#### TABLE: `wishlists`

| Column | Definition |
|---|---|
| id | INT UNSIGNED AUTO_INCREMENT PRIMARY KEY |
| user_id | INT UNSIGNED NOT NULL — FK → users(id) ON DELETE CASCADE |
| product_id | INT UNSIGNED NOT NULL — FK → products(id) ON DELETE CASCADE |
| variant_id | INT UNSIGNED NULL |
| added_at | DATETIME DEFAULT CURRENT_TIMESTAMP |
| UNIQUE KEY | (user_id, product_id, variant_id) |

#### TABLE: `reward_points_log`

| Column | Definition |
|---|---|
| id | INT UNSIGNED AUTO_INCREMENT PRIMARY KEY |
| user_id | INT UNSIGNED NOT NULL — FK → users(id) ON DELETE CASCADE |
| type | ENUM('earned','redeemed','expired','adjusted','bonus') NOT NULL |
| points | INT NOT NULL — Positive or negative |
| balance_after | INT NOT NULL — Running balance |
| order_id | INT UNSIGNED NULL — FK → orders(id) |
| description | VARCHAR(255) NOT NULL |
| expires_at | DATETIME NULL |
| created_at | DATETIME DEFAULT CURRENT_TIMESTAMP |

### 4.7 Delivery & Notification Tables

#### TABLE: `delivery_zones`

| Column | Definition |
|---|---|
| id | INT UNSIGNED AUTO_INCREMENT PRIMARY KEY |
| zone_name | VARCHAR(100) NOT NULL — e.g., Dubai Marina, Downtown Dubai |
| emirate | VARCHAR(100) NOT NULL |
| standard_delivery_fee | DECIMAL(8,2) DEFAULT 10.00 |
| express_delivery_fee | DECIMAL(8,2) DEFAULT 25.00 |
| free_shipping_threshold | DECIMAL(10,2) DEFAULT 100.00 |
| standard_days | VARCHAR(50) DEFAULT '1-2 Days' |
| express_hours | VARCHAR(50) DEFAULT '1 Hour' |
| is_express_available | TINYINT(1) DEFAULT 1 |
| is_active | TINYINT(1) DEFAULT 1 |

#### TABLE: `notifications`

| Column | Definition |
|---|---|
| id | INT UNSIGNED AUTO_INCREMENT PRIMARY KEY |
| user_id | INT UNSIGNED NULL — FK → users(id), NULL = broadcast |
| type | ENUM('order','delivery','promotion','system','review','reward') NOT NULL |
| title | VARCHAR(255) NOT NULL |
| message | TEXT NOT NULL |
| link_url | VARCHAR(500) NULL |
| is_read | TINYINT(1) DEFAULT 0 |
| sent_email | TINYINT(1) DEFAULT 0 |
| sent_sms | TINYINT(1) DEFAULT 0 |
| created_at | DATETIME DEFAULT CURRENT_TIMESTAMP |

### 4.8 Key Indexes & Foreign Key Summary

All foreign keys use `ON DELETE CASCADE` where child data is meaningless without parent (cart_items, order_items, product_images). Use `ON DELETE RESTRICT` for critical records (orders → users).

| Table | Index Column(s) | Type |
|---|---|---|
| products | slug, category_id, brand_id, status, is_featured | UNIQUE + INDEX |
| categories | slug, parent_id, is_active | UNIQUE + INDEX |
| orders | order_number, user_id, order_status, payment_status, created_at | UNIQUE + INDEX |
| users | email, role, is_active | UNIQUE + INDEX |
| cart_items | cart_id, product_id | INDEX |
| product_reviews | product_id, user_id, status | INDEX |
| reward_points_log | user_id, type, created_at | INDEX |
| coupons | code, is_active, end_date | UNIQUE + INDEX |

---

## 5. Frontend Design System (HTML/CSS/JS)

### 5.1 Design Language & Visual Identity

| Design Element | Specification |
|---|---|
| **Overall Aesthetic** | Premium Dark Luxury — Inspired by high-end tobacco boutiques. Rich dark backgrounds, gold/amber accents, crisp white typography |
| **Primary Color** | `#1A1A2E` — Deep Navy/Charcoal (backgrounds, headers, footer) |
| **Secondary Color** | `#C8963C` — Rich Amber/Gold (accents, CTAs, badges, hover states) |
| **Tertiary Color** | `#8B2635` — Deep Burgundy/Cigar Red (cigar category highlights) |
| **Success Color** | `#2D7A4F` — Forest Green (in stock, success messages) |
| **Warning Color** | `#D4832B` — Warm Amber (low stock, warnings) |
| **Error Color** | `#C0392B` — Red (errors, out of stock) |
| **Light BG** | `#F5F0E8` — Warm Cream (section backgrounds) |
| **Card BG** | `#FFFFFF` with subtle box shadow |
| **Primary Font** | Playfair Display — Headings (serif, luxury feel, Google Fonts) |
| **Body Font** | DM Sans — Body text, UI elements (clean, modern, readable) |
| **Mono Font** | JetBrains Mono — Prices, codes, SKUs |
| **Base Font Size** | 16px body, fluid type scale |
| **Border Radius** | 4px (subtle, professional — not bubble-like) |
| **Box Shadow** | `0 2px 8px rgba(0,0,0,0.08)` card / `0 8px 30px rgba(0,0,0,0.12)` modal |
| **Grid System** | 12-column CSS Grid + Flexbox hybrid |
| **Breakpoints** | Mobile: 480px / Tablet: 768px / Desktop: 1024px / Wide: 1440px |
| **Max Content Width** | 1320px centered with auto margins |
| **Transitions** | `all 0.25s ease` (hover) / `0.4s ease` (panels, modals) |

### 5.2 CSS Architecture

| File | Purpose |
|---|---|
| `/assets/css/root.css` | CSS custom properties (variables), reset, typography scale |
| `/assets/css/layout.css` | Grid, flex utilities, container, section spacing |
| `/assets/css/components.css` | Buttons, cards, badges, forms, modals, tabs, accordion |
| `/assets/css/header.css` | Announcement bar, header, mega nav, mobile menu |
| `/assets/css/footer.css` | Footer layout, social icons, newsletter form |
| `/assets/css/home.css` | Hero slider, trust badges, brand carousel, product grids |
| `/assets/css/product.css` | Product cards, detail page, gallery, variant selector |
| `/assets/css/shop.css` | Category listing, sidebar filters, sort bar, pagination |
| `/assets/css/cart.css` | Cart drawer, cart page, coupon input, order summary |
| `/assets/css/checkout.css` | Multi-step form, address cards, payment selector |
| `/assets/css/account.css` | Dashboard, order history, address book, rewards |
| `/assets/css/admin.css` | Admin panel sidebar, data tables, charts, forms |
| `/assets/css/age-gate.css` | Full-screen age verification overlay |
| `/assets/css/responsive.css` | All media query overrides, mobile-first adjustments |

### 5.3 CSS Custom Properties (Root Variables)

```css
:root {
  --color-primary:     #1A1A2E;
  --color-secondary:   #C8963C;
  --color-burgundy:    #8B2635;
  --color-success:     #2D7A4F;
  --color-warning:     #D4832B;
  --color-error:       #C0392B;
  --color-bg-light:    #F5F0E8;
  --color-text:        #1A1A2E;
  --color-text-muted:  #6B7280;

  --font-heading: 'Playfair Display', serif;
  --font-body:    'DM Sans', sans-serif;
  --font-mono:    'JetBrains Mono', monospace;

  --shadow-card:  0 2px 8px rgba(0,0,0,0.08);
  --shadow-modal: 0 8px 30px rgba(0,0,0,0.15);

  --radius:     4px;
  --radius-lg:  8px;
  --transition: 0.25s ease;
  --max-width:  1320px;
}
```

### 5.4 JavaScript Modules

| JS File | Responsibilities | Libraries Used |
|---|---|---|
| `main.js` | App init, global event listeners, mobile menu, sticky header, scroll to top | Vanilla JS |
| `age-gate.js` | Age verification overlay, session storage, DOB validation | Vanilla JS |
| `cart.js` | AJAX add-to-cart, cart drawer open/close, qty update, remove item, totals recalc | Fetch API |
| `wishlist.js` | Toggle wishlist, local storage for guests, AJAX for logged-in users | Fetch API |
| `product-gallery.js` | Main image switch, zoom on hover, lightbox, thumbnail scroll | Vanilla JS |
| `variant-selector.js` | Attribute selection (flavor, nicotine, size), price & stock update, image switch | Vanilla JS |
| `filters.js` | Category page sidebar: price range slider, checkbox filters, AJAX product reload | noUiSlider |
| `search.js` | Search autocomplete, live results dropdown, search history, category filter | Fetch API + Debounce |
| `checkout.js` | Multi-step form navigation, address selection, delivery slot picker, validation | Vanilla JS |
| `payment.js` | Payment method selection UI, card form styling, Telr/gateway integration | Gateway SDK |
| `slider.js` | Hero banner slider, brand carousel, product carousels, related products slider | Swiper.js |
| `reviews.js` | Star rating input, review form submit, helpful voting, pagination | Vanilla JS |
| `animations.js` | Scroll-triggered reveals, counter animations for stats, hero text effects | AOS.js |
| `notifications.js` | Toast notifications (success/error/info), cart confirmation popup | Vanilla JS |
| `admin.js` | Admin table sorting, bulk actions, image upload preview, chart initialization | Chart.js |

---

## 6. All Page Specifications & Wireframes

### 6.1 Home Page (`index.php`)

| Section | Description |
|---|---|
| **1 — Age Verification Gate** | Full-screen overlay with store logo, 18+ warning, two CTA buttons (I am 18+ / Exit), optional DOB form. Sets cookie/session on confirm. Cannot be bypassed. |
| **2 — Announcement Bar** | Scrolling marquee: 'FREE NEXT DAY DELIVERY ON ORDERS OVER AED 100 \| 1 HOUR DELIVERY AVAILABLE \| AGE RESTRICTED — 18+ ONLY'. Dark background, amber text. |
| **3 — Header** | Logo (left), Search bar with category filter (center), Account/Wishlist/Cart icons with badges (right). Sticky with box shadow on scroll. |
| **4 — Mega Nav** | Full-width dropdown menus per category with sub-category links, featured brands, category image. Mobile: slide-in hamburger panel. |
| **5 — Trust Badges** | 4-column row: Free Shipping / 1-Hour Delivery / Reward Points / 18+ Verified Store. Subtle background, icon + heading + subtext. |
| **6 — Hero Slider** | Full-width banner with 5 rotating slides, auto-play 5s, smooth fade/slide transition, CTA buttons, mobile-optimized images, lazy loading. Swiper.js. |
| **7 — Brand Carousel** | 'OUR BRANDS' heading, horizontal scrollable brand logos with names. Click → Brand page. |
| **8 — Category Showcase** | 2-row grid of main category cards with large background image, category name overlay, hover zoom effect. |
| **9 — New Arrivals** | 'JUST ARRIVED' section, horizontal product carousel. 4 cards per row (desktop), 2 per row (mobile). |
| **10 — Featured / Best Sellers** | Tabbed section: Best Sellers / Featured / On Sale. Tab switching is instant (JS, no page reload). |
| **11 — Promotional Banner** | Full-width single CTA banner. Large background image, headline, subtext, CTA button. |
| **12 — Cigar Spotlight** | Dedicated section for premium cigars — rich dark background, featured cigar image, curated picks carousel. |
| **13 — Shisha & Hookah Section** | Feature area with hookah imagery, sub-category quick links (Devices / Molasses / Accessories), flavor grid. |
| **14 — Why Choose Us** | 4-column feature block: Authenticity Guaranteed / Fast Delivery / Reward Points / Expert Support. Icons + headline + text. |
| **15 — Customer Reviews** | Stars summary widget, 3 featured review cards with name, rating, text, product bought. |
| **16 — Newsletter/SMS Signup** | Email + phone fields, 'Get AED 20 off your first order' incentive. Dark background. |
| **17 — Footer** | 4-column layout as specified in Section 3.4. |

### 6.2 Category / Shop Page (`shop.php?cat={slug}`)

- **URL Structure:** `/shop/cigars` / `/shop/vapes` / `/shop/e-liquids` etc.
- **Layout:** 2-column — Left sidebar (280px) filters + Right main content area
- **Sidebar Filters:** Price range slider (AED 0–500), Brand checkboxes, Flavor checkboxes, Nicotine Strength, Puff Count range, Cigar Strength, In Stock Only toggle, New Arrivals toggle, On Sale toggle
- **Sort Bar:** Results count, Sort by dropdown (Featured / Price Low-High / Price High-Low / Newest / Best Sellers / Rating), Grid/List view toggle
- **Product Grid:** 4 columns desktop, 3 tablet, 2 mobile. Infinite scroll OR pagination (12 per page)
- **Category Header:** Banner image, breadcrumb, category name, description, subcategory tiles
- **AJAX Filtering:** Filter changes update products without full page reload. URL params update for shareable filtered URLs.

### 6.3 Product Detail Page (`product.php?id={id}&slug={slug}`)

- **Left:** Product image gallery — main large image, 4 thumbnails below, navigation arrows, zoom on hover, click to open lightbox
- **Right:** Brand name (link), Product name (H1), Rating stars + review count, SKU, Price (with strike-through compare price if on sale), Stock status badge
- **Variant Selector:** Flavor/Nicotine/Size buttons — visual swatch selection, updates price/stock/image dynamically
- **Quantity Selector:** minus / input / plus. Max = available stock.
- **CTA Buttons:** 'Add to Cart' (primary gold) + 'Add to Wishlist' (outline). Wide full-width on mobile.
- **Product Details Tabs:** Description | Specifications | Reviews (with count) | Shipping Info
- **Specifications Table:** Dynamic key-value pairs from `product_attributes`
- **Reviews Section:** Star distribution bar chart, individual review cards, verified purchase badge, write review CTA
- **Related Products:** Horizontal carousel — same category/brand, same flavor profile
- **Recently Viewed:** Cookie-based, last 8 products viewed
- **Age Warning Banner:** Prominent 18+ reminder on all product pages

### 6.4 Cart Page (`cart.php`)

- Two-column layout: Cart Items (left 60%) | Order Summary (right 40%)
- Each item: product image, name, variant, unit price, qty stepper (AJAX update), item total, remove button
- Coupon Code input field with Apply button, inline success/error message
- Order Summary: Subtotal, Discount, Shipping, VAT (5%), Total. Reward Points balance shown if logged in.
- Delivery estimate: Select delivery type (Standard / Express 1-Hour) with fee update
- Cart is also available as a slide-in drawer on all pages (triggered by cart icon)
- 'Proceed to Checkout' button (sticky on mobile), 'Continue Shopping' link
- Upsell: 'Customers also bought' mini carousel below cart

### 6.5 Checkout (`checkout.php`) — 3 Steps

- **Step 1 — Your Details:** Email (if guest), First Name, Last Name, Phone. Option to log in or continue as guest.
- **Step 2 — Delivery:** Saved addresses for logged-in users, or new address form. Emirates dropdown, area input. Delivery type: Standard / Express 1-Hour. Delivery slot picker for express. Order Notes textarea.
- **Step 3 — Payment:** Order summary panel, Payment method: COD / Card (inline card form) / Apple Pay. Terms checkbox. Place Order button.
- Progress bar with step indicators. Back navigation between steps. Form validation at each step before proceeding.
- On success → Order Confirmation page with order number, summary, delivery estimate, email confirmation auto-sent.

### 6.6 Customer Account Pages

| Page | Content |
|---|---|
| **Account Dashboard** | Welcome message, quick stats (total orders, reward points, wishlist count), recent orders mini table, account menu |
| **My Orders** | Paginated order table: order number, date, status badge (color coded), total, View Details button |
| **Order Detail** | Full order breakdown: items, addresses, payment info, status timeline, download invoice PDF, reorder button |
| **My Addresses** | Address cards with label (Home/Work), edit/delete, Set Default. Add New Address form. |
| **Wishlist** | Product grid of saved items with Add to Cart, Remove. Share wishlist link. |
| **Reward Points** | Points balance, tier level, history log table (earned/redeemed), how to earn guide, redeem form |
| **My Profile** | Edit personal details, upload avatar, newsletter/SMS preferences, date of birth |
| **Change Password** | Current password + New password + Confirm password form with strength meter |

---

## 7. PHP Backend Architecture

### 7.1 Folder Structure

```
/public_html/                     ← Web Root (Apache/Nginx Document Root)
├── index.php                     ← Application bootstrap, router initialization
├── .htaccess                     ← URL rewriting, security headers, gzip compression
├── assets/                       ← CSS, JS, images, fonts (all public static files)
└── uploads/                      ← Product images, user avatars, banner images

/app/                             ← PHP Application (NOT web accessible)
├── core/
│   ├── Router.php
│   ├── Controller.php            ← Base controller
│   ├── Model.php                 ← Base model
│   ├── Database.php
│   ├── View.php
│   ├── Middleware.php
│   ├── Request.php
│   ├── Response.php
│   ├── Session.php
│   └── Auth.php
├── controllers/
│   ├── HomeController.php
│   ├── ProductController.php
│   ├── CategoryController.php
│   ├── CartController.php
│   ├── CheckoutController.php
│   ├── OrderController.php
│   ├── AccountController.php
│   ├── AuthController.php
│   ├── AdminController.php
│   ├── SearchController.php
│   ├── ApiController.php
│   └── AgeGateController.php
├── models/
│   ├── Product.php
│   ├── Category.php
│   ├── Brand.php
│   ├── User.php
│   ├── Cart.php
│   ├── CartItem.php
│   ├── Order.php
│   ├── OrderItem.php
│   ├── Coupon.php
│   ├── Banner.php
│   ├── Review.php
│   ├── Wishlist.php
│   ├── RewardPoint.php
│   ├── DeliveryZone.php
│   └── Notification.php
├── middleware/
│   ├── AuthMiddleware.php
│   ├── AdminMiddleware.php
│   ├── AgeGateMiddleware.php
│   ├── CsrfMiddleware.php
│   └── RateLimitMiddleware.php
├── helpers/
│   ├── functions.php
│   ├── validators.php
│   ├── formatters.php
│   ├── image_helper.php
│   ├── email_helper.php
│   └── sms_helper.php
├── views/
│   ├── layouts/                  ← main.php, admin.php, minimal.php
│   ├── components/               ← header, footer, nav, product-card, pagination, breadcrumb, modals
│   ├── pages/
│   │   ├── home/
│   │   ├── shop/
│   │   ├── product/
│   │   ├── cart/
│   │   ├── checkout/
│   │   ├── auth/
│   │   ├── account/
│   │   └── admin/
│   ├── age-gate.php
│   └── emails/                   ← Email templates
├── config/
│   ├── app.php
│   ├── database.php
│   ├── mail.php
│   ├── payment.php
│   ├── sms.php
│   └── routes.php
└── lang/
    ├── en/                       ← English strings
    └── ar/                       ← Arabic strings

/vendor/                          ← Composer dependencies
/database/
├── schema.sql
├── seeds.sql
└── migrations/
/cron/
├── cleanup_carts.php
├── expire_coupons.php
└── expire_reward_points.php
/logs/
/.env
/.env.example
/.gitignore
/composer.json
```

### 7.2 Router — Route Definitions

| Method | URI Pattern | Handler |
|---|---|---|
| GET | `/` | HomeController@index |
| GET | `/shop/{category}` | CategoryController@show |
| GET | `/product/{slug}` | ProductController@show |
| GET | `/brand/{slug}` | BrandController@show |
| GET | `/brands` | BrandController@index |
| GET | `/search` | SearchController@index |
| GET/POST | `/cart` | CartController@index / update |
| POST | `/cart/add` | CartController@add |
| POST | `/cart/remove` | CartController@remove |
| GET | `/checkout` | CheckoutController@index |
| POST | `/checkout/place-order` | CheckoutController@placeOrder |
| GET | `/order/confirm/{id}` | OrderController@confirm |
| GET | `/track/{order_number}` | OrderController@track |
| GET/POST | `/login` | AuthController@login |
| GET/POST | `/register` | AuthController@register |
| GET/POST | `/forgot-password` | AuthController@forgotPassword |
| GET/POST | `/reset-password/{token}` | AuthController@resetPassword |
| GET | `/logout` | AuthController@logout |
| GET | `/account` | AccountController@dashboard *(auth required)* |
| GET | `/account/orders` | AccountController@orders *(auth required)* |
| GET | `/account/order/{id}` | AccountController@orderDetail *(auth required)* |
| GET/POST | `/account/profile` | AccountController@profile *(auth required)* |
| GET | `/account/wishlist` | AccountController@wishlist *(auth required)* |
| GET | `/account/rewards` | AccountController@rewards *(auth required)* |
| GET | `/age-verify` | AgeGateController@show |
| POST | `/age-verify` | AgeGateController@verify |
| GET | `/admin` | AdminController@dashboard *(admin required)* |
| GET/POST | `/admin/products` | AdminController@products *(admin required)* |
| GET/POST | `/admin/orders` | AdminController@orders *(admin required)* |

### 7.3 Core PHP Classes

| Class | Key Methods |
|---|---|
| `Database.php` | PDO singleton, prepared statements, query builder helpers (select/insert/update/delete), transaction support |
| `Router.php` | Route registration, URI parsing, middleware execution, 404/405 handling |
| `Controller.php` | `render($view, $data)`, `json($data, $code)`, `redirect($url)`, `flash($type, $msg)`, `requireAuth()`, `requireAdmin()` |
| `Model.php` | `find($id)`, `findBy($col, $val)`, `findAll($conditions)`, `create($data)`, `update($id, $data)`, `delete($id)`, `paginate($page, $perPage)` |
| `Auth.php` | `login($email, $pass)`, `logout()`, `check()`, `user()`, `id()`, `attempt()`, `generateToken()`, `verifyToken()` |
| `Session.php` | `set()`, `get()`, `delete()`, `flash()`, `has()`, `regenerate()` |
| `Request.php` | `input($key)`, `file($key)`, `all()`, `validate($rules)`, `isPost()`, `isAjax()`, `ip()`, `userAgent()` |
| `View.php` | `render($template, $data)`, `escape($str)`, include partial templates, layouts inheritance |

---

## 8. API Endpoints Documentation

### 8.1 AJAX / Internal API Endpoints

Base path: `/api/` — All return JSON. POST requests require CSRF token header: `X-CSRF-Token`. Auth-required endpoints return `401` if not logged in.

| Method | Endpoint | Auth | Description |
|---|---|---|---|
| POST | `/api/cart/add` | No | Add product/variant to cart. Body: `{product_id, variant_id, qty}` |
| POST | `/api/cart/update` | No | Update cart item qty. Body: `{cart_item_id, qty}` |
| POST | `/api/cart/remove` | No | Remove cart item. Body: `{cart_item_id}` |
| GET | `/api/cart` | No | Get full cart with items and totals |
| POST | `/api/cart/coupon` | No | Apply coupon code. Body: `{code}` |
| DELETE | `/api/cart/coupon` | No | Remove applied coupon |
| GET | `/api/products/search` | No | Search products. Query: `?q={term}&cat={id}&limit={n}` |
| GET | `/api/products/{id}/variants` | No | Get variant data for a product |
| POST | `/api/wishlist/toggle` | Yes | Add/remove from wishlist. Body: `{product_id, variant_id}` |
| GET | `/api/wishlist` | Yes | Get user's wishlist items |
| POST | `/api/reviews` | Yes | Submit product review. Body: `{product_id, rating, title, body}` |
| POST | `/api/reviews/{id}/helpful` | Yes | Mark review as helpful |
| GET | `/api/delivery/estimate` | No | Get delivery fee. Query: `?area={area}&type={standard\|express}` |
| POST | `/api/coupon/validate` | No | Validate coupon before checkout. Body: `{code, cart_total}` |
| GET | `/api/account/notifications` | Yes | Get unread notifications count + list |
| POST | `/api/account/notifications/read` | Yes | Mark notification(s) as read |
| GET | `/api/age-verify/status` | No | Check if current session is age-verified |
| POST | `/api/newsletter/subscribe` | No | Subscribe email to newsletter. Body: `{email}` |

### 8.2 Admin API Endpoints

| Method | Endpoint | Description | Response |
|---|---|---|---|
| GET | `/api/admin/dashboard/stats` | Revenue today/week/month, orders count, new customers, low stock items | JSON stats object |
| GET | `/api/admin/orders` | Paginated orders with filters (status, date, search) | JSON order list + pagination |
| PUT | `/api/admin/orders/{id}/status` | Update order status. Body: `{status, note}` | JSON updated order |
| GET | `/api/admin/products` | Paginated products with filters | JSON product list |
| POST | `/api/admin/products` | Create new product with all fields + images | JSON created product |
| PUT | `/api/admin/products/{id}` | Update product | JSON updated product |
| DELETE | `/api/admin/products/{id}` | Delete/archive product | JSON status |
| POST | `/api/admin/products/{id}/images` | Upload product images (multipart) | JSON image paths |
| DELETE | `/api/admin/products/images/{id}` | Delete product image | JSON status |
| GET | `/api/admin/customers` | Paginated customers list | JSON customer list |
| PUT | `/api/admin/customers/{id}/ban` | Ban/unban customer. Body: `{reason}` | JSON status |
| POST | `/api/admin/coupons` | Create coupon | JSON created coupon |
| PUT | `/api/admin/banners/sort` | Reorder banners. Body: `{ids: []}` | JSON status |
| GET | `/api/admin/reports/sales` | Sales report. Query: `?from={date}&to={date}&group={day\|week\|month}` | JSON chart data |
| GET | `/api/admin/inventory/low-stock` | Products below `low_stock_threshold` | JSON product list |

---

## 9. User Authentication & Security System

### 9.1 Age Verification System

Age verification is the **first and mandatory gate** before ANY product browsing or purchase. UAE regulations require confirmation of 18+ age for tobacco, vaping, and related products.

- On first visit: full-screen overlay (cannot be scrolled past or dismissed)
- Two options: **'I AM 18 OR OLDER – ENTER'** and **'I AM UNDER 18 – EXIT SITE'**
- Optional: Date of birth input form for stricter verification. Validates minimum age of 18 years.
- On confirm: Set server-side session variable `age_verified = 1` + cookie `age_verified = 1` (expires 30 days)
- Every page PHP middleware checks this session/cookie. If not verified, redirect to age gate.
- Admin-configurable: can enable/disable DOB input, customize gate messaging, bypass for admin IP addresses
- **Never skippable via URL manipulation** — server-side enforcement, not just JavaScript

### 9.2 Authentication Security

| Security Measure | Implementation |
|---|---|
| **Password Hashing** | `password_hash()` with `PASSWORD_BCRYPT`, cost factor 12. Never store plain text. |
| **Password Verification** | `password_verify()` with timing-safe comparison |
| **Session Security** | Session regeneration on login (`session_regenerate_id(true)`), HttpOnly and Secure session cookies |
| **CSRF Protection** | Unique CSRF token per form session, validated server-side on every POST request |
| **Brute Force Protection** | Max 5 failed login attempts per IP per 15 minutes, stored in DB. Exponential backoff. |
| **SQL Injection Prevention** | ALL queries use PDO prepared statements with bound parameters. Zero raw SQL interpolation. |
| **XSS Prevention** | All user input sanitized with `htmlspecialchars()` on output. Content-Security-Policy headers. |
| **Input Validation** | Server-side validation on all inputs. Never trust client-side validation alone. |
| **File Upload Security** | Validate MIME type (not just extension), resize with GD library, store with hashed filenames |
| **HTTPS** | Forced HTTPS via `.htaccess` redirect. HSTS header. Minimum TLS 1.2. |
| **Security Headers** | `X-Frame-Options: DENY`, `X-Content-Type-Options: nosniff`, `Referrer-Policy: strict-origin`, `Permissions-Policy` |
| **Admin Protection** | Admin routes protected by role check middleware. Admin IP whitelist option in config. |
| **Sensitive Data** | Payment card data NEVER stored — use tokenization via payment gateway. PCI DSS compliance. |

### 9.3 Password Reset Flow

1. User submits email on forgot-password page
2. Server checks if email exists (always shows 'if email exists, check inbox' to prevent email enumeration)
3. Generate cryptographically secure token: `bin2hex(random_bytes(32))`
4. Store token in `password_resets` table with 1-hour expiry
5. Send email with reset link: `/reset-password/{token}`
6. On reset page: validate token exists, not used, not expired. Show new password form.
7. On submit: validate passwords match and meet strength requirements. Update hash. Mark token used. Log in user automatically.

---

## 10. Product Management Module

### 10.1 Product Model — Key Methods

| Method | Description |
|---|---|
| `getProductById($id)` | Full product with images, variants, brand, category, attributes |
| `getProductBySlug($slug)` | Same as above by slug. Used on product pages. |
| `getProductsByCategory($catId, $filters, $sort, $page)` | Category listing with all filters applied, paginated |
| `getProductsByBrand($brandId, $page)` | Brand page products |
| `getFeaturedProducts($limit)` | is_featured = 1, active, in stock |
| `getNewArrivals($limit)` | is_new_arrival = 1, ordered by created_at DESC |
| `getBestSellers($limit)` | total_sold DESC, active products |
| `getOnSaleProducts($limit)` | compare_at_price > price, active products |
| `searchProducts($query, $filters, $page)` | Full-text search + filters combined |
| `getRelatedProducts($productId, $limit)` | Same category, same brand, similar attributes, excluding current |
| `updateStock($productId, $variantId, $qty, $operation)` | Increment/decrement stock with validation |
| `getProductVariants($productId)` | All variants with options for selector UI |
| `updateProductRating($productId)` | Recalculate average_rating and review_count from reviews table |
| `getLowStockProducts($threshold)` | Admin: products where stock <= threshold |

### 10.2 Image Management

- **Upload endpoint:** `POST /api/admin/products/{id}/images`
- **Accepted formats:** JPG, PNG, WebP only (validate via `getimagesize()` MIME check)
- **Max file size:** 5MB per image, max 10 images per product
- **Processing pipeline:** Validate → Resize to 800×800 max (GD) → Generate thumbnail 300×300 → Generate WebP version → Save to `/uploads/products/{product_id}/`
- **Filename:** `{product_id}_{timestamp}_{random4}.jpg`
- First uploaded image auto-set as primary (`is_primary = 1`)
- Admin can drag-reorder images, set primary, delete individual images

### 10.3 Category Management

- Unlimited category depth (parent_id self-reference); UI/navigation shows 2 levels max
- Each category has: name, slug, description, image (card), banner_image (page header), icon, meta tags, position, active status
- Category tree built with recursive query or adjacency list pattern in PHP
- Category slug used in URLs: `/shop/cigars` / `/shop/vapes/disposables`
- Deactivating a parent category hides it and all children from frontend

---

## 11. Shopping Cart & Checkout Flow

### 11.1 Cart System Design

Dual cart system: guest carts (session/DB) merge with user cart on login.

- **Cart Identification:** Guest carts identified by PHP `session_id` stored in `carts.session_id`. Logged-in user carts identified by `user_id`.
- **Cart Merge on Login:** When user logs in, system finds guest cart by `session_id` and merges items into user cart (combine quantities or take max). Guest cart deleted after merge.
- **Cart Persistence:** Guest carts persist in DB for 30 days (cleanup cron job). User carts persist indefinitely until checkout or manual clear.
- **Real-time Updates:** All cart mutations done via AJAX. Cart drawer slides in with updated items and total. Cart badge count in header updates instantly.
- **Stock Validation:** On 'Add to Cart' and on checkout, validate requested quantity against current stock.
- **Price Snapshot:** `unit_price` saved in `cart_items` at time of adding. If product price changes, cart shows saved price + 'Price changed' notice.

### 11.2 Checkout Flow — Detailed Steps

| Step | Details |
|---|---|
| **Pre-Checkout Validation** | Age verified check, cart not empty, all items in stock, user logged in OR guest email provided |
| **Step 1 — Contact** | Email address (guest) or confirm logged-in email. First name, last name, phone. 'Create account' checkbox for guests. |
| **Step 2 — Delivery** | Load saved addresses for logged-in users with select UI. Or new address form. Delivery type: Standard / Express 1-Hour. Delivery slot picker for express. Gift option + message. |
| **Step 3 — Payment** | Order summary (read-only). Reward Points: show balance, toggle to apply. Payment: COD / Card (Telr iframe or redirect) / Apple Pay. Terms checkbox. Place Order button. |
| **Order Creation** | Validate all data server-side. Begin DB transaction. Create order record. Create order_items records. Deduct stock. Mark coupon as used. Deduct/credit reward points. Clear cart. Send order confirmation email + SMS. Commit transaction. Redirect to confirmation page. |
| **Confirmation Page** | Order number, summary card, delivery estimate, email sent notice, 'Track Order' button, 'Continue Shopping' button. |

### 11.3 Payment Gateway Integration

| Gateway | Integration Method |
|---|---|
| **Telr (UAE)** | Redirect to Telr hosted payment page OR iFrame integration. Webhook for payment status updates. Supports Visa/MC/AmEx. |
| **Network International** | Alternative UAE card gateway. API integration. 3DS2 authentication. |
| **Apple Pay** | Payment Request API + gateway tokenization. Available on Safari/iOS only. Shows conditionally. |
| **Cash on Delivery** | No payment API. Order created with `payment_status = pending`. Staff confirms on delivery. |
| **Tabby / Tamara (BNPL)** | Optional: Buy Now Pay Later — UAE-popular. Redirect integration. |

---

## 12. Order Management System

### 12.1 Order Status Lifecycle

| Status | Meaning | Triggered By |
|---|---|---|
| `pending` | Order placed, payment not yet confirmed | System on order creation |
| `confirmed` | Payment confirmed OR COD accepted | Payment webhook / Admin manual |
| `processing` | Being picked and packed in store | Admin / Staff update |
| `packed` | Packed, awaiting pickup by driver | Staff update |
| `out_for_delivery` | Driver picked up, en route | Driver app / Admin |
| `delivered` | Successfully delivered to customer | Driver / Admin confirmation |
| `cancelled` | Order cancelled (before delivery) | Customer or Admin with reason |
| `returned` | Delivered but returned by customer | Admin with reason and refund note |

### 12.2 Order Notifications

| Status Change | Notifications Sent |
|---|---|
| **Order Placed (pending)** | Email: order confirmation + Admin alert: new order |
| **Order Confirmed** | Email + SMS to customer: 'Your order has been confirmed' |
| **Out for Delivery** | SMS to customer: 'Your order is on the way!' |
| **Delivered** | Email + SMS to customer: 'Order delivered! Leave a review' |
| **Cancelled** | Email to customer with cancellation reason + refund info |

### 12.3 Refund & Returns

- Returns handled entirely through admin panel. No automated refund flow in v1.
- Admin marks order as 'returned', adds refund note, manually processes refund through gateway portal.
- Refund amount recorded in `payment_gateway_response` JSON.
- Reward points adjustment: if points were earned on refunded order, deduct from user balance with log entry.
- Stock restocked manually by admin or via 'Restock' button in admin order detail view.

---

## 13. Age Verification & Compliance

### 13.1 UAE Regulatory Requirements

- Minimum age for tobacco, vaping, shisha and related products in UAE: **18 years**
- Mandatory age verification gate on all pages, every visit (session-based with 30-day cookie option)
- All product pages display 18+ warning badge
- Footer must include UAE health warning text about tobacco products
- Compliance with **UAE Federal Law No. 15 of 2009 on Tobacco Control**
- No marketing materials targeting minors. All marketing content assumes 18+ audience.
- WhatsApp/SMS marketing: opt-in only with age confirmation in opt-in flow.

### 13.2 VAT Compliance (UAE)

- UAE VAT rate: **5%** on all sales
- All prices displayed inclusive of VAT (`tax_included = 1` default)
- Invoice/receipts clearly show: subtotal, VAT amount (5%), total
- Store VAT registration number displayed in footer and on invoices
- Tax summary available in admin reports for VAT filing

---

## 14. Admin Panel Specifications

### 14.1 Admin Dashboard

- **URL:** `/admin` (requires admin/manager role)
- **KPI Cards Row:** Today's Revenue / Today's Orders / New Customers Today / Pending Orders — with trend vs yesterday
- **Revenue Chart:** Line chart (last 30 days daily revenue). Chart.js. Toggle: Revenue / Orders / Customers.
- **Top Products Table:** Top 10 by revenue this month with image, name, units sold, revenue.
- **Recent Orders Table:** Last 10 orders with status badge, quick action buttons.
- **Low Stock Alert Panel:** Products at or below `low_stock_threshold` with restock link.
- **Quick Actions:** Add Product / Add Coupon / View Pending Orders / Export Report.

### 14.2 Admin Modules

| Admin Module | Features |
|---|---|
| **Products** | Paginated table with image, name, category, price, stock, status. Add/Edit/Archive. Bulk actions: activate, deactivate, delete. CSV export. Image upload manager. Variant manager. |
| **Categories** | Tree view with drag-and-drop reordering. Add/Edit/Delete. Category image upload. Toggle active/menu visibility. |
| **Brands** | Table with logo preview. Add/Edit/Toggle featured. Brand page content editor. |
| **Orders** | Full orders table with filters: status, payment, date range, emirate, search. View detail. Update status with note. Print invoice PDF. Export orders CSV. |
| **Customers** | Customer list with stats. View profile, order history, reward points history. Ban/Unban with reason. Add admin notes. Manually adjust reward points. |
| **Coupons** | Create/Edit/Delete coupons. Set type, value, min order, usage limits, date range, applicable categories/brands. Usage report per coupon. |
| **Banners** | Drag-and-drop banner ordering. Upload desktop + mobile images. Set link URL, start/end dates. Toggle active. |
| **Reviews** | Moderate pending reviews: Approve / Reject. Add admin reply. Filter by product, rating, status. |
| **Delivery Zones** | Add/Edit UAE delivery zones with fees and delivery time estimates. |
| **Reports** | Sales Summary (date range selector), Revenue by Category, Revenue by Brand, Best Selling Products, Customer Acquisition, Inventory Value, VAT Summary. Export to CSV/PDF. |
| **Settings** | Store name/logo/contact, SEO defaults, SMTP mail config, SMS gateway, payment gateway keys, reward points config, age gate settings, maintenance mode toggle. |

---

## 15. Search & Filter Engine

### 15.1 Search Architecture

- **Search bar:** Present in header on all pages. Debounced live search (300ms) shows autocomplete dropdown with up to 8 results + 'See all results for X' link.
- **Autocomplete results:** Product image thumbnail, name, price, category badge. Keyboard navigable.
- **Full search results page:** `/search?q={query}`. Shows product grid + pagination + category filter sidebar.
- **Search algorithm:** MySQL `FULLTEXT` search on `products(name, description, sku, flavor_profile)`. `MATCH() AGAINST()` in `BOOLEAN MODE`. Fallback to `LIKE %query%` for short terms.
- **Search result ranking:** exact name match > name FULLTEXT score > category match > description match. Boosted by: `is_featured`, review rating, `total_sold`.
- **Search history:** Last 5 searches saved in `localStorage`. Shown in search dropdown before typing.

### 15.2 Filter System

| Filter Type | Implementation |
|---|---|
| **Price Range** | Dual-handle slider (noUiSlider). AED 0 — max product price in category. AJAX applies filter. |
| **Brand** | Checkbox list with product count in brackets. Dynamic: only shows brands with products in current category. |
| **Flavor Profile** | Checkbox list for vape/e-liquid/shisha categories. Values from `product.flavor_profile` column. |
| **Nicotine Strength** | Checkbox (3mg, 6mg, 12mg, 20mg, Nicotine Free) for vape/e-liquid categories. |
| **Puff Count** | Range slider or checkbox brackets for disposable vape category. |
| **Cigar Strength** | Radio/checkbox: Mild / Medium / Medium-Full / Full Body for cigar category. |
| **In Stock Only** | Toggle switch. Filters `stock_quantity > 0`. |
| **On Sale** | Toggle switch. Filters `compare_at_price > price`. |
| **New Arrivals** | Toggle switch. Filters `is_new_arrival = 1`. |
| **Rating** | Star rating checkboxes: 4+ / 3+ / 2+ |

- All filters are combinable and applied simultaneously via single AJAX request.
- Filter state reflected in URL query params for shareable/bookmarkable filtered URLs.
- 'Clear All Filters' button resets to unfiltered state.
- Active filter pills shown below search/sort bar for easy individual filter removal.

---

## 16. Loyalty & Rewards System

### 16.1 Rewards Program Rules

| Rule | Detail |
|---|---|
| **Earn Rate** | 1 Point per AED 1 spent (configurable in admin settings) |
| **Redeem Rate** | 100 Points = AED 10 discount (configurable) |
| **Minimum Redeem** | 500 Points minimum to redeem |
| **Maximum Redeem per Order** | 30% of order subtotal maximum |
| **Points on Shipping** | Points earned on product subtotal only, not shipping or tax |
| **Points Expiry** | Points expire after 12 months of inactivity (no purchase) |
| **Bonus Points** | Admin can award bonus points manually or via promotions (e.g., 2x points weekend) |
| **Points on Return/Refund** | Points earned on returned items are deducted from balance |
| **Registration Bonus** | Configurable welcome points on first purchase (e.g., 200 bonus points) |
| **Review Bonus** | 50 points for leaving a verified purchase review (once per product) |

### 16.2 Loyalty Tiers *(V2 Feature — DB designed now)*

| Tier | Requirement | Benefits |
|---|---|---|
| **Silver** (Base) | 0 — 999 AED spent | Standard 1pt per AED |
| **Gold** | 1,000 — 4,999 AED spent | 1.5x points, priority support |
| **Platinum** | 5,000 — 14,999 AED spent | 2x points, free express delivery, early access to deals |
| **Black** | 15,000+ AED spent | 3x points, dedicated account manager, VIP events, custom gifts |

---

## 17. Delivery & Shipping Management

### 17.1 Delivery Options

| Delivery Type | Details |
|---|---|
| **Standard Delivery** | 1-2 business days. Free for orders over AED 100. AED 15 flat fee below threshold. Available all Emirates. |
| **Express 1-Hour Delivery** | Delivery within 1 hour. AED 25 flat. Orders placed before 10PM. Dubai only (selected areas). Delivery slot selection required. |
| **Next Day Delivery** | Guaranteed next business day. AED 20 flat. All UAE. Orders placed before 5PM. |
| **Click & Collect** | Customer collects from store (Dubai location). Free. Ready in 2 hours. |

### 17.2 Shipping Calculation Logic

- Customer selects delivery type on checkout step 2.
- System looks up `delivery_zones` table by emirate/area.
- If cart subtotal >= `free_shipping_threshold` AND delivery type = standard → fee = 0.
- Otherwise use zone-specific fee from `delivery_zones` table.
- AJAX endpoint `/api/delivery/estimate` calculates and returns fee before order placement.
- Free shipping badge shown on product pages and cart when order qualifies or is close to threshold.
- 'Add AED X more for free delivery' message in cart for orders approaching threshold.

---

## 18. Performance, SEO & Mobile Optimization

### 18.1 Performance Optimizations

- **Images:** WebP format with JPG fallback using `<picture>` tag. Lazy loading (`loading="lazy"`) on all below-fold images. Thumbnail generation on upload.
- **CSS:** All CSS minified in production. Critical CSS inlined in `<head>` for above-fold render. Non-critical CSS loaded asynchronously.
- **JS:** All scripts deferred or loaded at bottom of body. JS minified and concatenated. No jQuery dependency.
- **Caching:** PHP APCu for database query results (product lists, categories). Page-level cache for homepage and category pages (5-minute TTL). Browser cache headers via Nginx: 1 year for assets.
- **Database:** All queries use indexed columns. N+1 query problems solved with JOINs. Slow query log enabled for monitoring.
- **CDN:** Cloudflare for static assets (images, CSS, JS). Cloudflare caching rules configured.
- **Compression:** Gzip compression via Nginx for all text responses. Brotli where supported.
- **Target:** PageSpeed Insights score **90+ mobile**, **95+ desktop**. Core Web Vitals all green.

### 18.2 SEO Strategy

| SEO Element | Implementation |
|---|---|
| **URL Structure** | `/shop/{category-slug}` / `/product/{product-slug}` — Clean, keyword-rich, no parameters |
| **Title Tags** | Product: `{Product Name} │ Buy Online UAE │ Sultan's Smoke`. Category: `Buy {Category} Online UAE │ Free Delivery │ Sultan's Smoke` |
| **Meta Descriptions** | Dynamic per page, 120-160 chars, includes price, delivery promise, UAE focus |
| **Canonical URLs** | Self-referencing canonical on all pages. Pagination: rel=prev/next. |
| **Structured Data** | Product schema (name, price, availability, rating, brand, image). BreadcrumbList. Organization. LocalBusiness. |
| **Open Graph / Twitter Card** | Dynamic OG tags per page for social sharing. Product image, name, price in OG data. |
| **Sitemap** | Auto-generated XML sitemap: `/sitemap.xml`. Includes all active products, categories, brands, static pages. |
| **Robots.txt** | Allow all pages except `/admin/`, `/api/`, `/checkout/`, `/account/`. Disallow parameterized filter URLs. |
| **Heading Hierarchy** | H1: page title (one per page). H2: section headings. H3: product cards in lists. |
| **Image Alt Text** | All product images have descriptive alt text including product name, brand, flavor. |
| **Internal Linking** | Related products, category cross-links, brand pages, breadcrumb navigation. |
| **Page Speed** | Core Web Vitals — LCP <2.5s, FID <100ms, CLS <0.1 |

### 18.3 Mobile Optimization

- Mobile-first CSS approach — base styles for mobile, breakpoints add desktop styles
- Touch-friendly tap targets: minimum 48×48px for all interactive elements
- Swipe gestures on product gallery, hero slider, category carousels
- Mobile mega menu: full-screen slide-in panel with accordion sub-categories
- Sticky bottom bar on mobile: Cart total + Checkout button on cart/product pages
- Product images: aspect-ratio locked to prevent layout shift
- Forms: correct input types (`tel`, `email`, `number`) for native mobile keyboards
- Apple Pay and Google Pay shown where supported on device

---

## 19. File & Folder Structure

| Path | Type | Description |
|---|---|---|
| `/public_html/` | DIR | Web root — Apache/Nginx serves from here |
| `/public_html/index.php` | FILE | Application entry point — bootstrap, router init |
| `/public_html/.htaccess` | FILE | URL rewriting, security headers, cache rules |
| `/public_html/assets/` | DIR | All public static assets |
| `/public_html/assets/css/` | DIR | Stylesheets (see Section 5.2 for all files) |
| `/public_html/assets/js/` | DIR | JavaScript modules (see Section 5.4 for all files) |
| `/public_html/assets/images/` | DIR | Site images: logo, icons, placeholders, banners |
| `/public_html/assets/fonts/` | DIR | Self-hosted fonts (WOFF2) |
| `/public_html/uploads/` | DIR | User-uploaded files (product images, avatars) |
| `/public_html/uploads/products/` | DIR | Product images organized by product ID |
| `/public_html/uploads/brands/` | DIR | Brand logos |
| `/public_html/uploads/banners/` | DIR | Homepage/promo banner images |
| `/public_html/uploads/avatars/` | DIR | Customer profile pictures |
| `/app/` | DIR | PHP application — NOT accessible from web |
| `/app/core/Router.php` | FILE | URL routing, middleware pipeline |
| `/app/core/Database.php` | FILE | PDO singleton, query builder |
| `/app/core/Controller.php` | FILE | Base controller class |
| `/app/core/Model.php` | FILE | Base model class with CRUD |
| `/app/core/Auth.php` | FILE | Authentication service |
| `/app/core/Session.php` | FILE | Session management wrapper |
| `/app/core/View.php` | FILE | Template rendering engine |
| `/app/core/Request.php` | FILE | HTTP request abstraction |
| `/app/controllers/` | DIR | All controller classes (see Section 7.2) |
| `/app/models/` | DIR | All model classes (see Section 7.1) |
| `/app/middleware/` | DIR | Middleware classes (Auth, Admin, AgeGate, CSRF) |
| `/app/views/` | DIR | HTML template files |
| `/app/views/layouts/` | DIR | main.php, admin.php, minimal.php (age gate, errors) |
| `/app/views/components/` | DIR | Reusable partials: header, footer, nav, product-card, breadcrumb, pagination, modals |
| `/app/views/pages/` | DIR | Full page templates |
| `/app/views/pages/home/` | DIR | index.php |
| `/app/views/pages/shop/` | DIR | category.php, brand.php, search.php |
| `/app/views/pages/product/` | DIR | detail.php |
| `/app/views/pages/cart/` | DIR | index.php |
| `/app/views/pages/checkout/` | DIR | index.php (multi-step), confirm.php |
| `/app/views/pages/auth/` | DIR | login.php, register.php, forgot.php, reset.php |
| `/app/views/pages/account/` | DIR | dashboard.php, orders.php, order-detail.php, profile.php, addresses.php, wishlist.php, rewards.php |
| `/app/views/pages/admin/` | DIR | dashboard.php, products.php, product-edit.php, orders.php, customers.php, coupons.php, banners.php, reports.php, settings.php |
| `/app/views/pages/age-gate.php` | FILE | Age verification full-screen overlay page |
| `/app/views/emails/` | DIR | Email templates: order-confirm.php, order-status.php, password-reset.php, welcome.php |
| `/app/config/app.php` | FILE | App name, URL, environment, debug, timezone |
| `/app/config/database.php` | FILE | DB host, name, user, password, charset |
| `/app/config/mail.php` | FILE | SMTP settings, from address |
| `/app/config/payment.php` | FILE | Gateway API keys and endpoints |
| `/app/config/routes.php` | FILE | All route definitions |
| `/app/helpers/functions.php` | FILE | `slugify()`, `format_price()`, `truncate()`, `generate_token()`, etc. |
| `/app/helpers/validators.php` | FILE | `validate_email()`, `validate_phone_uae()`, `validate_password_strength()` |
| `/app/helpers/image_helper.php` | FILE | `resize_image()`, `generate_thumbnail()`, `delete_image()` |
| `/app/helpers/email_helper.php` | FILE | `send_email()`, email templates loader |
| `/app/lang/en/` | DIR | English language strings |
| `/app/lang/ar/` | DIR | Arabic language strings |
| `/database/schema.sql` | FILE | Complete MySQL schema — all 38 tables |
| `/database/seeds.sql` | FILE | Sample data: categories, brands, demo products |
| `/database/migrations/` | DIR | Version-numbered schema change files |
| `/vendor/` | DIR | Composer dependencies |
| `/composer.json` | FILE | PHP dependency manifest |
| `/.env` | FILE | Environment variables (DB creds, API keys) — NOT in git |
| `/.env.example` | FILE | Template for .env — committed to git |
| `/.gitignore` | FILE | Exclude: .env, /vendor/, /uploads/, /logs/ |
| `/cron/cleanup_carts.php` | FILE | Remove expired guest carts (run daily) |
| `/cron/expire_coupons.php` | FILE | Mark expired coupons as inactive (run daily) |
| `/cron/expire_reward_points.php` | FILE | Expire stale reward points (run monthly) |
| `/logs/` | DIR | Application error logs (not web accessible) |
| `/scripts/` | DIR | Utility scripts: DB backup, image optimization, import CSV |

---

## 20. Deployment & Hosting Requirements

### 20.1 Recommended Hosting — UAE Region

| Provider | Recommendation |
|---|---|
| **AWS (Middle East - UAE)** | EC2 t3.medium (2 vCPU, 4GB RAM) + RDS MySQL 8.0 + S3 for image storage + CloudFront CDN. Best for scalability. |
| **Google Cloud (Dubai Region)** | e2-standard-2 Compute Engine + Cloud SQL MySQL. Comparable performance. |
| **Azure UAE North** | B2s VM + Azure Database for MySQL. Good if client already on Azure. |
| **Contabo UAE / DigitalOcean** | Budget-friendly VPS. 4GB RAM plan sufficient for start. Manual server setup required. |
| **Hetzner (dedicated)** | Excellent value dedicated servers. Requires CDN for UAE performance. |

### 20.2 Nginx Server Configuration (Key Points)

- Document root: `/public_html/`
- All requests not matching a real file → rewrite to `/index.php` (for PHP router)
- Gzip compression for `text/html`, `text/css`, `application/javascript`, `application/json`
- Static asset cache headers: 1 year for `/assets/` and `/uploads/` directories
- PHP-FPM with Unix socket (faster than TCP for same-server PHP)
- Client max body size: 10MB (for product image uploads)
- Security headers in `nginx.conf`: `X-Frame-Options`, `X-Content-Type-Options`, HSTS

### 20.3 Cron Jobs Schedule

| Cron Expression | Script & Purpose |
|---|---|
| `0 2 * * *` | `cleanup_carts.php` — Delete expired guest carts (2 AM daily) |
| `0 0 * * *` | `expire_coupons.php` — Auto-deactivate expired coupons (midnight daily) |
| `0 1 1 * *` | `expire_reward_points.php` — Expire stale reward points (1st of month, 1 AM) |
| `*/5 * * * *` | `check_payment_status.php` — Poll pending COD orders for payment confirmation |
| `0 6 * * *` | `low_stock_report.php` — Email admin low stock alert (6 AM daily) |
| `0 23 * * *` | `db_backup.sh` — Automated MySQL dump to cloud storage (11 PM daily) |

### 20.4 Development Workflow & Version Control

- **Git repository:** GitHub / GitLab with branching strategy — `main` (production), `staging`, `feature/*` branches
- **Environments:** Local (XAMPP/WAMP or Docker) → Staging server → Production
- `.env` files never committed. Separate `.env` for each environment.
- Database changes via migration files in `/database/migrations/` — never edit `schema.sql` directly after initial setup
- **Deployment:** Git pull on staging/production. Composer install. Run pending migrations. Clear cache.
- **Monitoring:** UptimeRobot for availability alerts. PHP error logging to `/logs/` with daily rotation.

---

## Project Summary

| Metric | Count |
|---|---|
| Database Tables | **38** |
| Website Pages | **20+** |
| JavaScript Modules | **15** |
| CSS Files | **14** |
| PHP Controllers | **12** |
| PHP Models | **15** |
| API Endpoints (Public) | **18** |
| API Endpoints (Admin) | **15** |
| Cron Jobs | **6** |

---

*Sultan's Smoke — Premium Tobacco, Cigars, Vape & Shisha Store | Dubai, UAE*
*Document Version 1.0 | April 2025 | Confidential*
