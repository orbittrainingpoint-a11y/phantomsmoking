-- ============================================================
-- Product Variation System — Full Migration
-- Run this file once in phpMyAdmin or MySQL CLI:
--   SOURCE /path/to/product_variations.sql;
-- ============================================================

SET FOREIGN_KEY_CHECKS = 0;

-- ── Table 1: Variation Types
-- Stores the "axes" of variation for a product.
-- Example rows: (product_id=5, type_name='Nicotine Strength', display_order=0)
--               (product_id=5, type_name='Puff Count',        display_order=1)
--               (product_id=5, type_name='Flavour',           display_order=2)
CREATE TABLE IF NOT EXISTS `product_variation_types` (
  `id`            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `product_id`    INT UNSIGNED NOT NULL,
  `type_name`     VARCHAR(100) NOT NULL,
  `display_order` TINYINT UNSIGNED DEFAULT 0,
  `created_at`    DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at`    DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_pvt_product` (`product_id`),
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Table 2: Variation Options
-- Stores the individual values under each type.
-- Example rows: (variation_type_id=1, option_value='0mg',  display_order=0)
--               (variation_type_id=1, option_value='6mg',  display_order=1)
--               (variation_type_id=2, option_value='5000 puff', display_order=0)
CREATE TABLE IF NOT EXISTS `product_variation_options` (
  `id`                INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `variation_type_id` INT UNSIGNED NOT NULL,
  `product_id`        INT UNSIGNED NOT NULL,
  `option_value`      VARCHAR(100) NOT NULL,
  `display_order`     TINYINT UNSIGNED DEFAULT 0,
  `created_at`        DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at`        DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_pvo_type`    (`variation_type_id`),
  INDEX `idx_pvo_product` (`product_id`),
  FOREIGN KEY (`variation_type_id`) REFERENCES `product_variation_types`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`product_id`)        REFERENCES `products`(`id`)                ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Table 3: Variation Combinations
-- Each row = one purchasable SKU (leaf node).
-- option_id_level1 = first axis value (required)
-- option_id_level2..5 = deeper axis values (nullable if product has fewer levels)
-- Example row: (product_id=5, option_id_level1=2 [6mg],
--               option_id_level2=4 [5000 puff], option_id_level3=6 [Strawberry],
--               price=55.00, stock=10, sku='CLOUDX-6MG-5K-STRAW')
CREATE TABLE IF NOT EXISTS `product_variation_combinations` (
  `id`              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `product_id`      INT UNSIGNED NOT NULL,
  `option_id_level1` INT UNSIGNED NOT NULL     COMMENT 'Level 1 option (e.g. Nicotine)',
  `option_id_level2` INT UNSIGNED NULL         COMMENT 'Level 2 option (e.g. Puff Count)',
  `option_id_level3` INT UNSIGNED NULL         COMMENT 'Level 3 option (e.g. Flavour)',
  `option_id_level4` INT UNSIGNED NULL         COMMENT 'Level 4 option (optional)',
  `option_id_level5` INT UNSIGNED NULL         COMMENT 'Level 5 option (optional)',
  `price`           DECIMAL(10,2) NOT NULL,
  `stock`           INT DEFAULT 0,
  `sku`             VARCHAR(100) NULL,
  `is_active`       TINYINT(1) DEFAULT 1,
  `created_at`      DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at`      DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_pvc_product` (`product_id`),
  INDEX `idx_pvc_l1`      (`option_id_level1`),
  INDEX `idx_pvc_l2`      (`option_id_level2`),
  INDEX `idx_pvc_l3`      (`option_id_level3`),
  FOREIGN KEY (`product_id`)       REFERENCES `products`(`id`)                  ON DELETE CASCADE,
  FOREIGN KEY (`option_id_level1`) REFERENCES `product_variation_options`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`option_id_level2`) REFERENCES `product_variation_options`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`option_id_level3`) REFERENCES `product_variation_options`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`option_id_level4`) REFERENCES `product_variation_options`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`option_id_level5`) REFERENCES `product_variation_options`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Add combination_id to cart_items so cart knows exactly which combo was chosen
ALTER TABLE `cart_items`
  ADD COLUMN `combination_id` INT UNSIGNED NULL AFTER `variant_id`,
  ADD COLUMN `selected_options` VARCHAR(500) NULL AFTER `combination_id`;

-- ── Add combination_id to order_items for order history
ALTER TABLE `order_items`
  ADD COLUMN `combination_id` INT UNSIGNED NULL AFTER `variant_id`,
  ADD COLUMN `selected_options` VARCHAR(500) NULL AFTER `combination_id`;

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
-- SEED DATA — CloudX Vape Liquid test product
-- HOW TO USE:
--   1. Create a product called "CloudX Vape Liquid" in your admin panel
--   2. Note its ID from the products table
--   3. Replace the SET @pid line below with that ID
--   4. Uncomment the block and run it
-- ============================================================

/*
SET @pid = 1; -- ← CHANGE THIS to your actual product ID

-- Step A: Insert variation types
INSERT INTO `product_variation_types` (`product_id`, `type_name`, `display_order`) VALUES
  (@pid, 'Nicotine Strength', 0),
  (@pid, 'Puff Count',        1),
  (@pid, 'Flavour',           2);

-- Step B: Get the type IDs we just inserted
SET @t_nicotine = (SELECT `id` FROM `product_variation_types` WHERE `product_id` = @pid AND `type_name` = 'Nicotine Strength');
SET @t_puff     = (SELECT `id` FROM `product_variation_types` WHERE `product_id` = @pid AND `type_name` = 'Puff Count');
SET @t_flavour  = (SELECT `id` FROM `product_variation_types` WHERE `product_id` = @pid AND `type_name` = 'Flavour');

-- Step C: Insert options for each type
INSERT INTO `product_variation_options` (`variation_type_id`, `product_id`, `option_value`, `display_order`) VALUES
  -- Nicotine Strength options
  (@t_nicotine, @pid, '0mg', 0),
  (@t_nicotine, @pid, '6mg', 1),
  -- Puff Count options
  (@t_puff, @pid, '3000 puff', 0),
  (@t_puff, @pid, '5000 puff', 1),
  (@t_puff, @pid, '7000 puff', 2),
  -- Flavour options
  (@t_flavour, @pid, 'Strawberry', 0),
  (@t_flavour, @pid, 'Ice',        1),
  (@t_flavour, @pid, 'Mango',      2),
  (@t_flavour, @pid, 'Blueberry',  3),
  (@t_flavour, @pid, 'Watermelon', 4);

-- Step D: Get option IDs by value
SET @o_0mg        = (SELECT `id` FROM `product_variation_options` WHERE `product_id` = @pid AND `option_value` = '0mg');
SET @o_6mg        = (SELECT `id` FROM `product_variation_options` WHERE `product_id` = @pid AND `option_value` = '6mg');
SET @o_3000       = (SELECT `id` FROM `product_variation_options` WHERE `product_id` = @pid AND `option_value` = '3000 puff');
SET @o_5000       = (SELECT `id` FROM `product_variation_options` WHERE `product_id` = @pid AND `option_value` = '5000 puff');
SET @o_7000       = (SELECT `id` FROM `product_variation_options` WHERE `product_id` = @pid AND `option_value` = '7000 puff');
SET @o_strawberry = (SELECT `id` FROM `product_variation_options` WHERE `product_id` = @pid AND `option_value` = 'Strawberry');
SET @o_ice        = (SELECT `id` FROM `product_variation_options` WHERE `product_id` = @pid AND `option_value` = 'Ice');
SET @o_mango      = (SELECT `id` FROM `product_variation_options` WHERE `product_id` = @pid AND `option_value` = 'Mango');
SET @o_blueberry  = (SELECT `id` FROM `product_variation_options` WHERE `product_id` = @pid AND `option_value` = 'Blueberry');
SET @o_watermelon = (SELECT `id` FROM `product_variation_options` WHERE `product_id` = @pid AND `option_value` = 'Watermelon');

-- Step E: Insert the 5 valid combinations
INSERT INTO `product_variation_combinations`
  (`product_id`, `option_id_level1`, `option_id_level2`, `option_id_level3`, `price`, `stock`, `sku`) VALUES
  (@pid, @o_6mg, @o_5000, @o_strawberry, 55.00, 10, 'CLOUDX-6MG-5K-STRAW'),
  (@pid, @o_6mg, @o_5000, @o_ice,        55.00,  0, 'CLOUDX-6MG-5K-ICE'),
  (@pid, @o_6mg, @o_7000, @o_mango,      75.00,  5, 'CLOUDX-6MG-7K-MANGO'),
  (@pid, @o_6mg, @o_7000, @o_blueberry,  75.00,  8, 'CLOUDX-6MG-7K-BLUE'),
  (@pid, @o_0mg, @o_3000, @o_watermelon, 40.00,  3, 'CLOUDX-0MG-3K-WATER');
*/
