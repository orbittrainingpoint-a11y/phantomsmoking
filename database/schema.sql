-- Sultan's Smoke — Complete MySQL Schema
-- Database: sultans_smoke_db | Charset: utf8mb4 | Engine: InnoDB

CREATE DATABASE IF NOT EXISTS `sultans_smoke_db`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `sultans_smoke_db`;

-- --------------------------------------------------------
-- TABLE: categories
-- --------------------------------------------------------
CREATE TABLE `categories` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `parent_id` INT UNSIGNED NULL,
  `name` VARCHAR(150) NOT NULL,
  `slug` VARCHAR(160) NOT NULL UNIQUE,
  `description` TEXT NULL,
  `image` VARCHAR(255) NULL,
  `banner_image` VARCHAR(255) NULL,
  `icon` VARCHAR(100) NULL,
  `meta_title` VARCHAR(160) NULL,
  `meta_description` VARCHAR(320) NULL,
  `position` SMALLINT UNSIGNED DEFAULT 0,
  `is_active` TINYINT(1) DEFAULT 1,
  `show_in_menu` TINYINT(1) DEFAULT 1,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_parent` (`parent_id`),
  INDEX `idx_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- TABLE: brands
-- --------------------------------------------------------
CREATE TABLE `brands` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `slug` VARCHAR(110) NOT NULL UNIQUE,
  `logo` VARCHAR(255) NULL,
  `banner_image` VARCHAR(255) NULL,
  `description` TEXT NULL,
  `country_of_origin` VARCHAR(100) NULL,
  `website_url` VARCHAR(255) NULL,
  `meta_title` VARCHAR(160) NULL,
  `meta_description` VARCHAR(320) NULL,
  `is_active` TINYINT(1) DEFAULT 1,
  `is_featured` TINYINT(1) DEFAULT 0,
  `position` SMALLINT UNSIGNED DEFAULT 0,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_active` (`is_active`),
  INDEX `idx_featured` (`is_featured`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- TABLE: products
-- --------------------------------------------------------
CREATE TABLE `products` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `category_id` INT UNSIGNED NOT NULL,
  `brand_id` INT UNSIGNED NULL,
  `name` VARCHAR(255) NOT NULL,
  `slug` VARCHAR(270) NOT NULL UNIQUE,
  `sku` VARCHAR(100) NOT NULL UNIQUE,
  `barcode` VARCHAR(100) NULL,
  `short_description` TEXT NULL,
  `description` LONGTEXT NULL,
  `price` DECIMAL(10,2) NOT NULL,
  `compare_at_price` DECIMAL(10,2) NULL,
  `cost_price` DECIMAL(10,2) NULL,
  `tax_rate` DECIMAL(5,2) DEFAULT 5.00,
  `tax_included` TINYINT(1) DEFAULT 1,
  `weight_grams` INT UNSIGNED NULL,
  `stock_quantity` INT DEFAULT 0,
  `low_stock_threshold` INT DEFAULT 5,
  `track_inventory` TINYINT(1) DEFAULT 1,
  `allow_backorder` TINYINT(1) DEFAULT 0,
  `product_type` ENUM('simple','variable','bundle') DEFAULT 'simple',
  `status` ENUM('active','draft','archived') DEFAULT 'active',
  `is_featured` TINYINT(1) DEFAULT 0,
  `is_new_arrival` TINYINT(1) DEFAULT 0,
  `is_best_seller` TINYINT(1) DEFAULT 0,
  `is_age_restricted` TINYINT(1) DEFAULT 1,
  `nicotine_content_mg` DECIMAL(5,2) NULL,
  `puff_count` INT NULL,
  `volume_ml` DECIMAL(8,2) NULL,
  `flavor_profile` VARCHAR(255) NULL,
  `cigar_size` VARCHAR(100) NULL,
  `cigar_strength` ENUM('mild','medium','medium-full','full') NULL,
  `cigar_wrapper` VARCHAR(100) NULL,
  `cigar_origin` VARCHAR(100) NULL,
  `meta_title` VARCHAR(160) NULL,
  `meta_description` VARCHAR(320) NULL,
  `meta_keywords` VARCHAR(255) NULL,
  `total_sold` INT UNSIGNED DEFAULT 0,
  `average_rating` DECIMAL(3,2) DEFAULT 0.00,
  `review_count` INT UNSIGNED DEFAULT 0,
  `reward_points` INT UNSIGNED DEFAULT 0,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_category` (`category_id`),
  INDEX `idx_brand` (`brand_id`),
  INDEX `idx_status` (`status`),
  INDEX `idx_featured` (`is_featured`),
  INDEX `idx_new_arrival` (`is_new_arrival`),
  INDEX `idx_best_seller` (`is_best_seller`),
  FULLTEXT INDEX `ft_search` (`name`, `sku`, `flavor_profile`),
  FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE RESTRICT,
  FOREIGN KEY (`brand_id`) REFERENCES `brands`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- TABLE: product_images
-- --------------------------------------------------------
CREATE TABLE `product_images` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `product_id` INT UNSIGNED NOT NULL,
  `image_path` VARCHAR(255) NOT NULL,
  `alt_text` VARCHAR(255) NULL,
  `is_primary` TINYINT(1) DEFAULT 0,
  `position` SMALLINT UNSIGNED DEFAULT 0,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_product` (`product_id`),
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- TABLE: product_variants
-- --------------------------------------------------------
CREATE TABLE `product_variants` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `product_id` INT UNSIGNED NOT NULL,
  `variant_name` VARCHAR(255) NOT NULL,
  `sku` VARCHAR(100) NOT NULL UNIQUE,
  `price` DECIMAL(10,2) NOT NULL,
  `compare_at_price` DECIMAL(10,2) NULL,
  `stock_quantity` INT DEFAULT 0,
  `image_id` INT UNSIGNED NULL,
  `is_active` TINYINT(1) DEFAULT 1,
  `position` SMALLINT UNSIGNED DEFAULT 0,
  INDEX `idx_product` (`product_id`),
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- TABLE: product_variant_options
-- --------------------------------------------------------
CREATE TABLE `product_variant_options` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `variant_id` INT UNSIGNED NOT NULL,
  `option_name` VARCHAR(100) NOT NULL,
  `option_value` VARCHAR(100) NOT NULL,
  INDEX `idx_variant` (`variant_id`),
  FOREIGN KEY (`variant_id`) REFERENCES `product_variants`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- TABLE: product_categories (Many-to-Many)
-- --------------------------------------------------------
CREATE TABLE `product_categories` (
  `product_id` INT UNSIGNED NOT NULL,
  `category_id` INT UNSIGNED NOT NULL,
  PRIMARY KEY (`product_id`, `category_id`),
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- TABLE: product_attributes
-- --------------------------------------------------------
CREATE TABLE `product_attributes` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `product_id` INT UNSIGNED NOT NULL,
  `attribute_name` VARCHAR(100) NOT NULL,
  `attribute_value` VARCHAR(255) NOT NULL,
  `position` SMALLINT UNSIGNED DEFAULT 0,
  INDEX `idx_product` (`product_id`),
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- TABLE: users
-- --------------------------------------------------------
CREATE TABLE `users` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `first_name` VARCHAR(80) NOT NULL,
  `last_name` VARCHAR(80) NOT NULL,
  `email` VARCHAR(180) NOT NULL UNIQUE,
  `phone` VARCHAR(20) NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `role` ENUM('customer','admin','manager','staff') DEFAULT 'customer',
  `email_verified` TINYINT(1) DEFAULT 0,
  `email_verify_token` VARCHAR(100) NULL,
  `age_verified` TINYINT(1) DEFAULT 0,
  `age_verified_at` DATETIME NULL,
  `date_of_birth` DATE NULL,
  `gender` ENUM('male','female','prefer_not_to_say') NULL,
  `avatar` VARCHAR(255) NULL,
  `reward_points` INT UNSIGNED DEFAULT 0,
  `total_spent` DECIMAL(12,2) DEFAULT 0.00,
  `total_orders` INT UNSIGNED DEFAULT 0,
  `newsletter_subscribed` TINYINT(1) DEFAULT 0,
  `sms_subscribed` TINYINT(1) DEFAULT 0,
  `last_login_at` DATETIME NULL,
  `last_login_ip` VARCHAR(45) NULL,
  `is_active` TINYINT(1) DEFAULT 1,
  `banned_reason` TEXT NULL,
  `notes` TEXT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_role` (`role`),
  INDEX `idx_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- TABLE: user_addresses
-- --------------------------------------------------------
CREATE TABLE `user_addresses` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NOT NULL,
  `label` VARCHAR(50) DEFAULT 'Home',
  `full_name` VARCHAR(160) NOT NULL,
  `phone` VARCHAR(20) NOT NULL,
  `address_line1` VARCHAR(255) NOT NULL,
  `address_line2` VARCHAR(255) NULL,
  `area` VARCHAR(100) NULL,
  `city` VARCHAR(100) NOT NULL DEFAULT 'Dubai',
  `emirate` VARCHAR(100) NOT NULL DEFAULT 'Dubai',
  `country` VARCHAR(100) NOT NULL DEFAULT 'UAE',
  `latitude` DECIMAL(10,8) NULL,
  `longitude` DECIMAL(11,8) NULL,
  `is_default` TINYINT(1) DEFAULT 0,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_user` (`user_id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- TABLE: password_resets
-- --------------------------------------------------------
CREATE TABLE `password_resets` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `email` VARCHAR(180) NOT NULL,
  `token` VARCHAR(100) NOT NULL UNIQUE,
  `expires_at` DATETIME NOT NULL,
  `used` TINYINT(1) DEFAULT 0,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- TABLE: login_attempts
-- --------------------------------------------------------
CREATE TABLE `login_attempts` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `ip_address` VARCHAR(45) NOT NULL,
  `email` VARCHAR(180) NULL,
  `attempted_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_ip` (`ip_address`),
  INDEX `idx_attempted` (`attempted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- TABLE: carts
-- --------------------------------------------------------
CREATE TABLE `carts` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NULL,
  `session_id` VARCHAR(128) NOT NULL,
  `coupon_id` INT UNSIGNED NULL,
  `discount_amount` DECIMAL(10,2) DEFAULT 0.00,
  `expires_at` DATETIME NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_user` (`user_id`),
  INDEX `idx_session` (`session_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- TABLE: cart_items
-- --------------------------------------------------------
CREATE TABLE `cart_items` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `cart_id` INT UNSIGNED NOT NULL,
  `product_id` INT UNSIGNED NOT NULL,
  `variant_id` INT UNSIGNED NULL,
  `quantity` SMALLINT UNSIGNED NOT NULL DEFAULT 1,
  `unit_price` DECIMAL(10,2) NOT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_cart` (`cart_id`),
  INDEX `idx_product` (`product_id`),
  FOREIGN KEY (`cart_id`) REFERENCES `carts`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- TABLE: coupons
-- --------------------------------------------------------
CREATE TABLE `coupons` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `code` VARCHAR(50) NOT NULL UNIQUE,
  `description` VARCHAR(255) NULL,
  `type` ENUM('percentage','fixed_amount','free_shipping') NOT NULL,
  `value` DECIMAL(10,2) NOT NULL,
  `min_order_amount` DECIMAL(10,2) DEFAULT 0.00,
  `max_discount_amount` DECIMAL(10,2) NULL,
  `usage_limit` INT NULL,
  `usage_per_user` INT DEFAULT 1,
  `used_count` INT DEFAULT 0,
  `applies_to` ENUM('all','category','product','brand') DEFAULT 'all',
  `applies_to_ids` JSON NULL,
  `start_date` DATETIME NULL,
  `end_date` DATETIME NULL,
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_code` (`code`),
  INDEX `idx_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- TABLE: orders
-- --------------------------------------------------------
CREATE TABLE `orders` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `order_number` VARCHAR(20) NOT NULL UNIQUE,
  `user_id` INT UNSIGNED NULL,
  `guest_email` VARCHAR(180) NULL,
  `shipping_address_id` INT UNSIGNED NULL,
  `shipping_name` VARCHAR(160) NOT NULL,
  `shipping_phone` VARCHAR(20) NOT NULL,
  `shipping_address_line1` VARCHAR(255) NOT NULL,
  `shipping_address_line2` VARCHAR(255) NULL,
  `shipping_area` VARCHAR(100) NULL,
  `shipping_city` VARCHAR(100) NOT NULL,
  `shipping_emirate` VARCHAR(100) NOT NULL,
  `shipping_country` VARCHAR(100) DEFAULT 'UAE',
  `billing_same_as_shipping` TINYINT(1) DEFAULT 1,
  `subtotal` DECIMAL(10,2) NOT NULL,
  `discount_amount` DECIMAL(10,2) DEFAULT 0.00,
  `shipping_cost` DECIMAL(10,2) DEFAULT 0.00,
  `tax_amount` DECIMAL(10,2) DEFAULT 0.00,
  `total_amount` DECIMAL(10,2) NOT NULL,
  `reward_points_earned` INT UNSIGNED DEFAULT 0,
  `reward_points_used` INT UNSIGNED DEFAULT 0,
  `reward_points_discount` DECIMAL(10,2) DEFAULT 0.00,
  `coupon_id` INT UNSIGNED NULL,
  `coupon_code` VARCHAR(50) NULL,
  `payment_method` ENUM('cod','card','apple_pay','bank_transfer') NOT NULL,
  `payment_status` ENUM('pending','paid','failed','refunded','partially_refunded') DEFAULT 'pending',
  `payment_transaction_id` VARCHAR(255) NULL,
  `payment_gateway_response` JSON NULL,
  `order_status` ENUM('pending','confirmed','processing','packed','out_for_delivery','delivered','cancelled','returned') DEFAULT 'pending',
  `delivery_type` ENUM('standard','express_1hr','next_day','pickup') DEFAULT 'standard',
  `delivery_slot` VARCHAR(100) NULL,
  `estimated_delivery_at` DATETIME NULL,
  `delivered_at` DATETIME NULL,
  `driver_id` INT UNSIGNED NULL,
  `tracking_number` VARCHAR(100) NULL,
  `customer_notes` TEXT NULL,
  `admin_notes` TEXT NULL,
  `cancellation_reason` TEXT NULL,
  `is_gift` TINYINT(1) DEFAULT 0,
  `gift_message` TEXT NULL,
  `ip_address` VARCHAR(45) NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_user` (`user_id`),
  INDEX `idx_status` (`order_status`),
  INDEX `idx_payment_status` (`payment_status`),
  INDEX `idx_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- TABLE: order_items
-- --------------------------------------------------------
CREATE TABLE `order_items` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `order_id` INT UNSIGNED NOT NULL,
  `product_id` INT UNSIGNED NOT NULL,
  `variant_id` INT UNSIGNED NULL,
  `product_name` VARCHAR(255) NOT NULL,
  `product_sku` VARCHAR(100) NOT NULL,
  `variant_name` VARCHAR(255) NULL,
  `quantity` SMALLINT UNSIGNED NOT NULL,
  `unit_price` DECIMAL(10,2) NOT NULL,
  `discount_amount` DECIMAL(10,2) DEFAULT 0.00,
  `tax_amount` DECIMAL(10,2) DEFAULT 0.00,
  `total_price` DECIMAL(10,2) NOT NULL,
  `product_image` VARCHAR(255) NULL,
  INDEX `idx_order` (`order_id`),
  FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- TABLE: order_status_history
-- --------------------------------------------------------
CREATE TABLE `order_status_history` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `order_id` INT UNSIGNED NOT NULL,
  `status` VARCHAR(50) NOT NULL,
  `note` TEXT NULL,
  `created_by_user_id` INT UNSIGNED NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_order` (`order_id`),
  FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- TABLE: banners
-- --------------------------------------------------------
CREATE TABLE `banners` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(255) NOT NULL,
  `subtitle` VARCHAR(255) NULL,
  `image_desktop` VARCHAR(255) NOT NULL,
  `image_mobile` VARCHAR(255) NULL,
  `link_url` VARCHAR(500) NULL,
  `link_text` VARCHAR(100) NULL,
  `position` ENUM('hero','secondary','category_top','popup') DEFAULT 'hero',
  `sort_order` SMALLINT UNSIGNED DEFAULT 0,
  `start_date` DATETIME NULL,
  `end_date` DATETIME NULL,
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_position` (`position`),
  INDEX `idx_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- TABLE: product_reviews
-- --------------------------------------------------------
CREATE TABLE `product_reviews` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `product_id` INT UNSIGNED NOT NULL,
  `user_id` INT UNSIGNED NOT NULL,
  `order_item_id` INT UNSIGNED NULL,
  `rating` TINYINT UNSIGNED NOT NULL,
  `title` VARCHAR(255) NULL,
  `body` TEXT NULL,
  `is_verified_purchase` TINYINT(1) DEFAULT 0,
  `status` ENUM('pending','approved','rejected') DEFAULT 'pending',
  `admin_reply` TEXT NULL,
  `helpful_count` INT UNSIGNED DEFAULT 0,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_product` (`product_id`),
  INDEX `idx_user` (`user_id`),
  INDEX `idx_status` (`status`),
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- TABLE: wishlists
-- --------------------------------------------------------
CREATE TABLE `wishlists` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NOT NULL,
  `product_id` INT UNSIGNED NOT NULL,
  `variant_id` INT UNSIGNED NULL,
  `added_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `uq_wishlist` (`user_id`, `product_id`, `variant_id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- TABLE: reward_points_log
-- --------------------------------------------------------
CREATE TABLE `reward_points_log` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NOT NULL,
  `type` ENUM('earned','redeemed','expired','adjusted','bonus') NOT NULL,
  `points` INT NOT NULL,
  `balance_after` INT NOT NULL,
  `order_id` INT UNSIGNED NULL,
  `description` VARCHAR(255) NOT NULL,
  `expires_at` DATETIME NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_user` (`user_id`),
  INDEX `idx_type` (`type`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- TABLE: delivery_zones
-- --------------------------------------------------------
CREATE TABLE `delivery_zones` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `zone_name` VARCHAR(100) NOT NULL,
  `emirate` VARCHAR(100) NOT NULL,
  `standard_delivery_fee` DECIMAL(8,2) DEFAULT 10.00,
  `express_delivery_fee` DECIMAL(8,2) DEFAULT 25.00,
  `free_shipping_threshold` DECIMAL(10,2) DEFAULT 100.00,
  `standard_days` VARCHAR(50) DEFAULT '1-2 Days',
  `express_hours` VARCHAR(50) DEFAULT '1 Hour',
  `is_express_available` TINYINT(1) DEFAULT 1,
  `is_active` TINYINT(1) DEFAULT 1,
  INDEX `idx_emirate` (`emirate`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- TABLE: notifications
-- --------------------------------------------------------
CREATE TABLE `notifications` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NULL,
  `type` ENUM('order','delivery','promotion','system','review','reward') NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `message` TEXT NOT NULL,
  `link_url` VARCHAR(500) NULL,
  `is_read` TINYINT(1) DEFAULT 0,
  `sent_email` TINYINT(1) DEFAULT 0,
  `sent_sms` TINYINT(1) DEFAULT 0,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_user` (`user_id`),
  INDEX `idx_read` (`is_read`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- TABLE: newsletter_subscribers
-- --------------------------------------------------------
CREATE TABLE `newsletter_subscribers` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `email` VARCHAR(180) NOT NULL UNIQUE,
  `subscribed_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `is_active` TINYINT(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- TABLE: settings
-- --------------------------------------------------------
CREATE TABLE `settings` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `setting_key` VARCHAR(100) NOT NULL UNIQUE,
  `setting_value` TEXT NULL,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- TABLE: coupon_usage
-- --------------------------------------------------------
CREATE TABLE `coupon_usage` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `coupon_id` INT UNSIGNED NOT NULL,
  `user_id` INT UNSIGNED NULL,
  `order_id` INT UNSIGNED NOT NULL,
  `used_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_coupon` (`coupon_id`),
  INDEX `idx_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
