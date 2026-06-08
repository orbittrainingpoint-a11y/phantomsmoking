-- Product Variant Types & Options
-- Run this migration once

CREATE TABLE IF NOT EXISTS `product_variant_types` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `product_id` INT UNSIGNED NOT NULL,
  `label` VARCHAR(100) NOT NULL COMMENT 'e.g. Nicotine Strength, Size, Flavour',
  `position` SMALLINT UNSIGNED DEFAULT 0,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_product` (`product_id`),
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `variant_type_options` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `variant_type_id` INT UNSIGNED NOT NULL,
  `option_label` VARCHAR(150) NOT NULL COMMENT 'e.g. 3mg, Large, Mango',
  `price_override` DECIMAL(10,2) NULL COMMENT 'Fixed price — overrides base price',
  `price_modifier` DECIMAL(10,2) NULL COMMENT '+/- added to base price',
  `sku` VARCHAR(100) NULL,
  `stock_qty` INT DEFAULT 0,
  `image_url` VARCHAR(255) NULL,
  `position` SMALLINT UNSIGNED DEFAULT 0,
  INDEX `idx_type` (`variant_type_id`),
  FOREIGN KEY (`variant_type_id`) REFERENCES `product_variant_types`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add variant_option_ids to cart_items
ALTER TABLE `cart_items` ADD COLUMN `variant_option_ids` VARCHAR(255) NULL;
ALTER TABLE `cart_items` ADD COLUMN `selected_flavours` VARCHAR(500) NULL;

-- Add variant_option_ids to order_items
ALTER TABLE `order_items` ADD COLUMN `variant_option_ids` VARCHAR(255) NULL;
