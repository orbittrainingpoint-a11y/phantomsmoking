-- Sultan's Smoke — Seed Data
USE `sultans_smoke_db`;

-- Categories
INSERT INTO `categories` (`name`, `slug`, `description`, `icon`, `position`, `is_active`, `show_in_menu`) VALUES
('Cigars', 'cigars', 'Premium cigars from around the world', 'fa-fire', 1, 1, 1),
('Cigarettes & Tobacco', 'cigarettes-tobacco', 'Premium cigarettes and tobacco products', 'fa-smoking', 2, 1, 1),
('Vapes & Disposables', 'vapes', 'Vape devices, disposables, pod systems and mods', 'fa-wind', 3, 1, 1),
('E-Liquids', 'e-liquids', 'Nic salts, freebase, shortfills and more', 'fa-tint', 4, 1, 1),
('Shisha & Hookah', 'shisha', 'Hookah devices, molasses, charcoal and accessories', 'fa-cloud', 5, 1, 1),
('Nic Pouches', 'nic-pouches', 'Tobacco-free nicotine pouches', 'fa-circle', 6, 1, 1),
('Hardware & Accessories', 'hardware', 'Coils, pods, tanks, batteries and accessories', 'fa-cog', 7, 1, 1),
('Deals & Bundles', 'deals', 'Clearance, bundle deals and flash sales', 'fa-tag', 8, 1, 1);

-- Sub-categories for Cigars
INSERT INTO `categories` (`parent_id`, `name`, `slug`, `position`) VALUES
(1, 'Premium Cigars', 'cigars-premium', 1),
(1, 'Cuban Cigars', 'cigars-cuban', 2),
(1, 'Dominican Cigars', 'cigars-dominican', 3),
(1, 'Honduran Cigars', 'cigars-honduran', 4);

-- Sub-categories for Vapes
INSERT INTO `categories` (`parent_id`, `name`, `slug`, `position`) VALUES
(3, 'Disposable Vapes', 'vapes-disposables', 1),
(3, 'Pod Systems', 'vapes-pods', 2),
(3, 'Box Mods', 'vapes-mods', 3),
(3, 'Starter Kits', 'vapes-starter-kits', 4);

-- Sub-categories for E-Liquids
INSERT INTO `categories` (`parent_id`, `name`, `slug`, `position`) VALUES
(4, 'Nic Salts', 'e-liquids-nic-salts', 1),
(4, 'Freebase', 'e-liquids-freebase', 2),
(4, 'Shortfills', 'e-liquids-shortfills', 3);

-- Brands
INSERT INTO `brands` (`name`, `slug`, `country_of_origin`, `is_active`, `is_featured`, `position`) VALUES
('Cohiba', 'cohiba', 'Cuba', 1, 1, 1),
('Marlboro', 'marlboro', 'USA', 1, 1, 2),
('IQOS', 'iqos', 'Japan', 1, 1, 3),
('Vuse', 'vuse', 'UK', 1, 1, 4),
('Nasty Juice', 'nasty-juice', 'Malaysia', 1, 1, 5),
('Al Fakher', 'al-fakher', 'UAE', 1, 1, 6),
('Zyn', 'zyn', 'Sweden', 1, 1, 7),
('Velo', 'velo', 'UK', 1, 1, 8),
('Smok', 'smok', 'China', 1, 1, 9),
('Voopoo', 'voopoo', 'China', 1, 0, 10),
('Lost Mary', 'lost-mary', 'China', 1, 1, 11),
('Elf Bar', 'elf-bar', 'China', 1, 1, 12);

-- Sample Products
INSERT INTO `products` (`category_id`, `brand_id`, `name`, `slug`, `sku`, `short_description`, `price`, `compare_at_price`, `stock_quantity`, `status`, `is_featured`, `is_new_arrival`, `is_best_seller`, `reward_points`) VALUES
(1, 1, 'Cohiba Robusto Premium Cigar', 'cohiba-robusto-premium', 'CIG-COH-ROB-001', 'Iconic Cuban cigar with rich, complex flavors', 185.00, NULL, 50, 'active', 1, 0, 1, 185),
(3, 11, 'Lost Mary BM5000 Disposable Vape', 'lost-mary-bm5000', 'VAP-LM-BM5000', '5000 puffs, rechargeable, multiple flavors', 45.00, 55.00, 200, 'active', 1, 1, 1, 45),
(4, 5, 'Nasty Juice Slow Blow Nic Salt 30ml', 'nasty-juice-slow-blow-30ml', 'ELQ-NJ-SB-30', 'Pineapple lemonade nic salt, 20mg', 35.00, NULL, 150, 'active', 0, 1, 0, 35),
(5, 6, 'Al Fakher Double Apple Shisha 250g', 'al-fakher-double-apple-250g', 'SHI-AF-DA-250', 'Classic double apple molasses, 250g', 28.00, NULL, 100, 'active', 1, 0, 1, 28),
(6, 7, 'Zyn Cool Mint 6mg Nic Pouches', 'zyn-cool-mint-6mg', 'NIC-ZYN-CM-6', 'Cool mint nicotine pouches, 6mg, 20 pouches', 22.00, NULL, 300, 'active', 0, 0, 1, 22),
(3, 12, 'Elf Bar BC5000 Disposable', 'elf-bar-bc5000', 'VAP-EB-BC5000', '5000 puffs disposable vape, rechargeable', 42.00, 50.00, 180, 'active', 1, 1, 1, 42);

-- Product Images (placeholder paths)
INSERT INTO `product_images` (`product_id`, `image_path`, `alt_text`, `is_primary`, `position`) VALUES
(1, '/uploads/products/1/cohiba-robusto-main.jpg', 'Cohiba Robusto Premium Cigar', 1, 0),
(2, '/uploads/products/2/lost-mary-bm5000-main.jpg', 'Lost Mary BM5000 Disposable Vape', 1, 0),
(3, '/uploads/products/3/nasty-juice-slow-blow-main.jpg', 'Nasty Juice Slow Blow 30ml', 1, 0),
(4, '/uploads/products/4/al-fakher-double-apple-main.jpg', 'Al Fakher Double Apple 250g', 1, 0),
(5, '/uploads/products/5/zyn-cool-mint-main.jpg', 'Zyn Cool Mint 6mg', 1, 0),
(6, '/uploads/products/6/elf-bar-bc5000-main.jpg', 'Elf Bar BC5000', 1, 0);

-- Product Variants for Lost Mary BM5000
INSERT INTO `product_variants` (`product_id`, `variant_name`, `sku`, `price`, `stock_quantity`, `is_active`) VALUES
(2, 'Mango Ice', 'VAP-LM-BM5000-MI', 45.00, 50, 1),
(2, 'Blueberry Ice', 'VAP-LM-BM5000-BI', 45.00, 40, 1),
(2, 'Watermelon Ice', 'VAP-LM-BM5000-WI', 45.00, 60, 1),
(2, 'Strawberry Kiwi', 'VAP-LM-BM5000-SK', 45.00, 30, 1);

-- Variant Options
INSERT INTO `product_variant_options` (`variant_id`, `option_name`, `option_value`) VALUES
(1, 'Flavor', 'Mango Ice'), (2, 'Flavor', 'Blueberry Ice'),
(3, 'Flavor', 'Watermelon Ice'), (4, 'Flavor', 'Strawberry Kiwi');

-- Product Attributes
INSERT INTO `product_attributes` (`product_id`, `attribute_name`, `attribute_value`, `position`) VALUES
(1, 'Size', 'Robusto (5" x 50)', 1),
(1, 'Strength', 'Full Body', 2),
(1, 'Wrapper', 'Colorado Claro', 3),
(1, 'Origin', 'Cuba', 4),
(2, 'Puff Count', '5000', 1),
(2, 'Battery', '650mAh', 2),
(2, 'Nicotine', '20mg', 3),
(3, 'Volume', '30ml', 1),
(3, 'Nicotine', '20mg', 2),
(3, 'VG/PG', '50/50', 3);

-- Delivery Zones
INSERT INTO `delivery_zones` (`zone_name`, `emirate`, `standard_delivery_fee`, `express_delivery_fee`, `free_shipping_threshold`, `standard_days`, `express_hours`, `is_express_available`) VALUES
('Dubai Marina', 'Dubai', 10.00, 25.00, 100.00, '1-2 Days', '1 Hour', 1),
('Downtown Dubai', 'Dubai', 10.00, 25.00, 100.00, '1-2 Days', '1 Hour', 1),
('JBR', 'Dubai', 10.00, 25.00, 100.00, '1-2 Days', '1 Hour', 1),
('Deira', 'Dubai', 10.00, 25.00, 100.00, '1-2 Days', '1 Hour', 1),
('Abu Dhabi City', 'Abu Dhabi', 15.00, 0.00, 150.00, '2-3 Days', 'N/A', 0),
('Sharjah', 'Sharjah', 15.00, 0.00, 150.00, '2-3 Days', 'N/A', 0),
('Al Ain', 'Al Ain', 20.00, 0.00, 200.00, '3-4 Days', 'N/A', 0),
('Ras Al Khaimah', 'Ras Al Khaimah', 20.00, 0.00, 200.00, '3-4 Days', 'N/A', 0);

-- Banners
INSERT INTO `banners` (`title`, `subtitle`, `image_desktop`, `image_mobile`, `link_url`, `link_text`, `position`, `sort_order`, `is_active`) VALUES
('Premium Cigars Collection', 'Discover the finest cigars from Cuba and beyond', '/uploads/banners/hero-cigars.jpg', '/uploads/banners/hero-cigars-mobile.jpg', '/shop/cigars', 'Shop Cigars', 'hero', 1, 1),
('New Vape Arrivals', 'Latest disposables and pod systems in stock', '/uploads/banners/hero-vapes.jpg', '/uploads/banners/hero-vapes-mobile.jpg', '/shop/vapes', 'Shop Vapes', 'hero', 2, 1),
('Shisha Season', 'Premium Al Fakher and more — free delivery over AED 100', '/uploads/banners/hero-shisha.jpg', '/uploads/banners/hero-shisha-mobile.jpg', '/shop/shisha', 'Shop Shisha', 'hero', 3, 1);

-- Coupons
INSERT INTO `coupons` (`code`, `description`, `type`, `value`, `min_order_amount`, `usage_limit`, `is_active`) VALUES
('WELCOME20', 'Welcome discount - AED 20 off first order', 'fixed_amount', 20.00, 100.00, NULL, 1),
('SAVE10', '10% off your order', 'percentage', 10.00, 50.00, 500, 1),
('FREESHIP', 'Free shipping on any order', 'free_shipping', 0.00, 0.00, 200, 1);

-- Admin User (password: Admin@123)
INSERT INTO `users` (`first_name`, `last_name`, `email`, `password_hash`, `role`, `email_verified`, `age_verified`, `is_active`) VALUES
('Sultan', 'Admin', 'admin@sultanssmokedubai.com', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uHCQd8/Gy', 'admin', 1, 1, 1);

-- Settings
INSERT INTO `settings` (`setting_key`, `setting_value`) VALUES
('store_name', 'Sultan\'s Smoke'),
('store_email', 'info@sultanssmokedubai.com'),
('store_phone', '+971 4 000 0000'),
('store_address', 'Dubai, UAE'),
('currency', 'AED'),
('vat_number', 'TRN000000000000'),
('reward_earn_rate', '1'),
('reward_redeem_rate', '100'),
('reward_min_redeem', '500'),
('reward_max_redeem_percent', '30'),
('free_shipping_threshold', '100'),
('age_gate_enabled', '1'),
('age_gate_require_dob', '0'),
('maintenance_mode', '0'),
('points_expiry_months', '12'),
('welcome_bonus_points', '200'),
('review_bonus_points', '50');
