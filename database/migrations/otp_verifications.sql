-- OTP Verifications Table
CREATE TABLE IF NOT EXISTS `otp_verifications` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `email` VARCHAR(180) NOT NULL,
  `otp_code` VARCHAR(6) NOT NULL,
  `purpose` ENUM('login','register') NOT NULL DEFAULT 'login',
  `attempts` TINYINT UNSIGNED DEFAULT 0,
  `expires_at` DATETIME NOT NULL,
  `used` TINYINT(1) DEFAULT 0,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_email` (`email`),
  INDEX `idx_expires` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
