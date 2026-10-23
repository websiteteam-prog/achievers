-- =====================================================================
--  Campaign Registration - MySQL schema + starter data
--  Compatible with MySQL 5.7+ / 8.x and MariaDB 10.3+
--
--  Import:
--    1. Create an empty database (e.g. via cPanel > MySQL Databases).
--    2. phpMyAdmin > select that database > Import > choose this file
--       OR  mysql -u DB_USER -p DB_NAME < database.sql
--
--  This file is safe to re-run: it never drops tables or deletes data.
--  Default admin login:  admin@example.com / Admin@123  (you are forced to
--  change the password on first login; change the email in admin_users).
--
--  Campaign name, dates, active status and banner paths are in the
--  campaign_settings table (row id = 1) - edit them in phpMyAdmin.
-- =====================================================================

SET NAMES utf8mb4;
SET time_zone = '+05:30';

-- ---------------------------------------------------------------------
--  Cities (City dropdown)
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `cities` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`       VARCHAR(100) NOT NULL,
  `is_active`  TINYINT(1)   NOT NULL DEFAULT 1,
  `created_at` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_cities_name` (`name`),
  KEY `idx_cities_active_name` (`is_active`, `name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
--  Stores (Store Name dropdown + auto-filled Store Code)
--  `code` is optional; when present it must be unique.
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `stores` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`       VARCHAR(150) NOT NULL,
  `code`       VARCHAR(50)  NULL DEFAULT NULL,
  `is_active`  TINYINT(1)   NOT NULL DEFAULT 1,
  `created_at` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_stores_code` (`code`),
  KEY `idx_stores_active_name` (`is_active`, `name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
--  Campaign settings (single row, id = 1)
--  is_active = 0 or an end date in the past shows "Campaign is over".
--  Banner paths are relative to the project root.
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `campaign_settings` (
  `id`             TINYINT UNSIGNED NOT NULL,
  `campaign_name`  VARCHAR(150) NOT NULL,
  `start_date`     DATE         NOT NULL,
  `end_date`       DATE         NOT NULL,
  `is_active`      TINYINT(1)   NOT NULL DEFAULT 0,
  `desktop_banner` VARCHAR(255) NULL DEFAULT NULL,
  `mobile_banner`  VARCHAR(255) NULL DEFAULT NULL,
  `updated_at`     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
--  Admin users (passwords stored with password_hash())
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `admin_users` (
  `id`                   INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `email`                VARCHAR(191) NOT NULL,
  `password_hash`        VARCHAR(255) NOT NULL,
  `full_name`            VARCHAR(100) NOT NULL DEFAULT '',
  `must_change_password` TINYINT(1)   NOT NULL DEFAULT 0,
  `failed_attempts`      TINYINT UNSIGNED NOT NULL DEFAULT 0,
  `locked_until`         DATETIME     NULL DEFAULT NULL,
  `last_login_at`        DATETIME     NULL DEFAULT NULL,
  `created_at`           DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`           DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_admin_users_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
--  OTP verification records
--  The OTP itself is never stored, only an HMAC-SHA256 hash of it.
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `otp_verifications` (
  `id`                INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `mobile`            CHAR(10)     NOT NULL,
  `otp_hash`          CHAR(64)     NOT NULL,
  `send_status`       ENUM('pending','sent','failed') NOT NULL DEFAULT 'pending',
  `attempts`          TINYINT UNSIGNED NOT NULL DEFAULT 0,
  `expires_at`        DATETIME     NOT NULL,
  `verified_at`       DATETIME     NULL DEFAULT NULL,
  `consumed_at`       DATETIME     NULL DEFAULT NULL,
  `ip_address`        VARCHAR(45)  NOT NULL,
  `provider_response` VARCHAR(500) NULL DEFAULT NULL,
  `created_at`        DATETIME     NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_otp_mobile_created` (`mobile`, `created_at`),
  KEY `idx_otp_ip_created` (`ip_address`, `created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
--  Customer registrations
--  UNIQUE keys on mobile, email and imei block duplicates even when two
--  submissions arrive at the same moment.
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `registrations` (
  `id`                  INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`                VARCHAR(100) NOT NULL,
  `mobile`              CHAR(10)     NOT NULL,
  `imei`                VARCHAR(15)  NOT NULL,
  `email`               VARCHAR(191) NOT NULL,
  `purchase_date`       DATE         NOT NULL,
  `store_id`            INT UNSIGNED NOT NULL,
  `store_code`          VARCHAR(50)  NULL DEFAULT NULL,
  `city_id`             INT UNSIGNED NOT NULL,
  `otp_verification_id` INT UNSIGNED NULL DEFAULT NULL,
  `ip_address`          VARCHAR(45)  NULL DEFAULT NULL,
  `user_agent`          VARCHAR(255) NULL DEFAULT NULL,
  `created_at`          DATETIME     NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_registrations_mobile` (`mobile`),
  UNIQUE KEY `uq_registrations_email` (`email`),
  UNIQUE KEY `uq_registrations_imei` (`imei`),
  KEY `idx_registrations_name` (`name`),
  KEY `idx_registrations_created` (`created_at`),
  KEY `idx_registrations_purchase` (`purchase_date`),
  KEY `idx_registrations_store` (`store_id`),
  KEY `idx_registrations_city` (`city_id`),
  KEY `idx_registrations_otp` (`otp_verification_id`),
  CONSTRAINT `fk_registrations_store` FOREIGN KEY (`store_id`) REFERENCES `stores` (`id`)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT `fk_registrations_city` FOREIGN KEY (`city_id`) REFERENCES `cities` (`id`)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT `fk_registrations_otp` FOREIGN KEY (`otp_verification_id`) REFERENCES `otp_verifications` (`id`)
    ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================================
--  Starter data (replace with your real stores / cities / dates)
-- =====================================================================

INSERT IGNORE INTO `campaign_settings`
  (`id`, `campaign_name`, `start_date`, `end_date`, `is_active`, `desktop_banner`, `mobile_banner`)
VALUES
  (1, 'Festive Buy & Win Campaign', '2026-10-01', '2026-12-31', 1,
   'assets/img/banner-desktop.jpg', 'assets/img/banner-mobile.jpg');

-- Default admin: email "admin@example.com", password "Admin@123" (must be changed on first login)
INSERT IGNORE INTO `admin_users` (`id`, `email`, `password_hash`, `full_name`, `must_change_password`)
VALUES
  (1, 'admin@example.com', '$2y$10$we78gFZgGZJX5nt55OA3YO9D6eoDKvgyoOK4RAMcZZsNQBz1uACdy', 'Administrator', 1);

INSERT IGNORE INTO `cities` (`id`, `name`) VALUES
  (1, 'Ahmedabad'),
  (2, 'Bengaluru'),
  (3, 'Chennai'),
  (4, 'Delhi'),
  (5, 'Hyderabad'),
  (6, 'Jaipur'),
  (7, 'Kolkata'),
  (8, 'Lucknow'),
  (9, 'Mumbai'),
  (10, 'Pune');

INSERT IGNORE INTO `stores` (`id`, `name`, `code`) VALUES
  (1, 'Croma - Andheri West',        'CRM-MUM-001'),
  (2, 'Croma - Koramangala',         'CRM-BLR-014'),
  (3, 'Reliance Digital - Saket',    'RD-DEL-022'),
  (4, 'Reliance Digital - Banjara Hills', 'RD-HYD-007'),
  (5, 'Vijay Sales - Shivaji Nagar', 'VS-PUN-003'),
  (6, 'Poorvika - T. Nagar',         'PVK-CHN-031'),
  (7, 'Great Eastern - Park Street', 'GE-KOL-002'),
  (8, 'Sangeetha Mobiles - Jayanagar', NULL),
  (9, 'Local Partner Store - Jaipur',  NULL);
