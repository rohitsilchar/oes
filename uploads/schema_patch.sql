-- Schema patch: brings a database imported from uploads/install.sql up to what
-- the current code expects (store/pharmacist tables, new users columns, etc.).
-- Run after importing install.sql, e.g.:
--   mysql -u root <database_name> < uploads/schema_patch.sql
-- Idempotent: safe to re-run. Works on MySQL 5.7+ and MariaDB: each column/index is added
-- only when information_schema shows it missing (no MariaDB-only IF NOT EXISTS syntax).

-- ---------- missing columns ----------
SET @sql = (SELECT IF(COUNT(*) = 0, 'ALTER TABLE `users` ADD COLUMN `plain_password` VARCHAR(255) NULL DEFAULT NULL', 'DO 0') FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND COLUMN_NAME = 'plain_password');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
SET @sql = (SELECT IF(COUNT(*) = 0, 'ALTER TABLE `users` ADD COLUMN `employee_id` VARCHAR(100) NULL DEFAULT NULL', 'DO 0') FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND COLUMN_NAME = 'employee_id');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
SET @sql = (SELECT IF(COUNT(*) = 0, 'ALTER TABLE `users` ADD COLUMN `gender` VARCHAR(20) NULL DEFAULT NULL', 'DO 0') FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND COLUMN_NAME = 'gender');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
SET @sql = (SELECT IF(COUNT(*) = 0, 'ALTER TABLE `users` ADD COLUMN `store_id` INT UNSIGNED NULL DEFAULT NULL', 'DO 0') FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND COLUMN_NAME = 'store_id');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
SET @sql = (SELECT IF(COUNT(*) = 0, 'ALTER TABLE `users` ADD COLUMN `licence_no` VARCHAR(100) NULL DEFAULT NULL', 'DO 0') FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND COLUMN_NAME = 'licence_no');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
SET @sql = (SELECT IF(COUNT(*) = 0, 'ALTER TABLE `users` ADD COLUMN `licence_start_date` VARCHAR(100) NULL DEFAULT NULL', 'DO 0') FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND COLUMN_NAME = 'licence_start_date');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
SET @sql = (SELECT IF(COUNT(*) = 0, 'ALTER TABLE `users` ADD COLUMN `licence_end_date` VARCHAR(100) NULL DEFAULT NULL', 'DO 0') FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND COLUMN_NAME = 'licence_end_date');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
SET @sql = (SELECT IF(COUNT(*) = 0, 'ALTER TABLE `users` ADD COLUMN `mac_address` VARCHAR(255) NULL DEFAULT NULL', 'DO 0') FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND COLUMN_NAME = 'mac_address');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
SET @sql = (SELECT IF(COUNT(*) = 0, 'ALTER TABLE `users` ADD COLUMN `state` VARCHAR(100) NULL DEFAULT NULL', 'DO 0') FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND COLUMN_NAME = 'state');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
SET @sql = (SELECT IF(COUNT(*) = 0, 'ALTER TABLE `users` ADD COLUMN `pharmacy_name` VARCHAR(255) NULL DEFAULT NULL', 'DO 0') FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND COLUMN_NAME = 'pharmacy_name');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
SET @sql = (SELECT IF(COUNT(*) = 0, 'ALTER TABLE `users` ADD COLUMN `designation` VARCHAR(150) NULL DEFAULT NULL', 'DO 0') FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND COLUMN_NAME = 'designation');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
SET @sql = (SELECT IF(COUNT(*) = 0, 'ALTER TABLE `users` ADD COLUMN `region` VARCHAR(100) NULL DEFAULT NULL', 'DO 0') FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND COLUMN_NAME = 'region');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
SET @sql = (SELECT IF(COUNT(*) = 0, 'ALTER TABLE `users` ADD COLUMN `email_sent` TINYINT(1) NOT NULL DEFAULT 0', 'DO 0') FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND COLUMN_NAME = 'email_sent');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
SET @sql = (SELECT IF(COUNT(*) = 0, 'ALTER TABLE `quiz_results` ADD COLUMN `credential_email_sent` TINYINT(1) NOT NULL DEFAULT 0', 'DO 0') FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'quiz_results' AND COLUMN_NAME = 'credential_email_sent');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
SET @sql = (SELECT IF(COUNT(*) = 0, 'ALTER TABLE `lesson` ADD COLUMN `audio_url_for_mobile_application` VARCHAR(400) NULL DEFAULT NULL', 'DO 0') FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'lesson' AND COLUMN_NAME = 'audio_url_for_mobile_application');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

ALTER TABLE `users`
  MODIFY `skills`       LONGTEXT NULL,
  MODIFY `payment_keys` LONGTEXT NULL,
  MODIFY `sessions`     LONGTEXT NULL;

-- ---------- missing indexes ----------
SET @sql = (SELECT IF(COUNT(*) = 0, 'ALTER TABLE `users` ADD INDEX `idx_users_employee_id` (`employee_id`)', 'DO 0') FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND INDEX_NAME = 'idx_users_employee_id');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
SET @sql = (SELECT IF(COUNT(*) = 0, 'ALTER TABLE `users` ADD INDEX `idx_users_store_role` (`store_id`, `role_id`)', 'DO 0') FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND INDEX_NAME = 'idx_users_store_role');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- ---------- new tables ----------
CREATE TABLE IF NOT EXISTS `store_categories` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `category_name` VARCHAR(255) NOT NULL,
  `code` VARCHAR(50) NULL DEFAULT NULL,
  `description` TEXT NULL,
  `status` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` INT UNSIGNED NULL DEFAULT NULL,
  `updated_at` INT UNSIGNED NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_category_name` (`category_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `store_roles` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `role_name` VARCHAR(150) NOT NULL,
  `description` TEXT NULL,
  `status` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` VARCHAR(30) NULL DEFAULT NULL, -- code writes both time() and date('Y-m-d H:i:s')
  `updated_at` VARCHAR(30) NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_role_name` (`role_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `stores` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `store_name` VARCHAR(255) NOT NULL,
  `store_code` VARCHAR(100) NULL DEFAULT NULL,
  `category_id` INT UNSIGNED NULL DEFAULT NULL,
  `store_category` VARCHAR(255) NULL DEFAULT NULL,
  `zone` VARCHAR(50) NULL DEFAULT NULL,
  `contact_person` VARCHAR(255) NULL DEFAULT NULL,
  `live_date` VARCHAR(50) NULL DEFAULT NULL,
  `phone` VARCHAR(50) NULL DEFAULT NULL,
  `mobile` VARCHAR(50) NULL DEFAULT NULL,
  `email` VARCHAR(255) NULL DEFAULT NULL,
  `portal_url` VARCHAR(500) NULL DEFAULT NULL,
  `assigned_role_ids` TEXT NULL,
  `address` TEXT NULL,
  `city` VARCHAR(100) NULL DEFAULT NULL,
  `state` VARCHAR(100) NULL DEFAULT NULL,
  `pin_code` VARCHAR(20) NULL DEFAULT NULL,
  `status` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` INT UNSIGNED NULL DEFAULT NULL,
  `updated_at` INT UNSIGNED NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_store_code` (`store_code`),
  KEY `idx_category_id` (`category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `store_users` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `store_id` INT UNSIGNED NOT NULL,
  `pharmacist_id` INT UNSIGNED NULL DEFAULT NULL,
  `role_id` INT UNSIGNED NULL DEFAULT NULL,
  `role_title` VARCHAR(150) NULL DEFAULT NULL,
  `designation` VARCHAR(150) NULL DEFAULT NULL,
  `username` VARCHAR(150) NOT NULL,
  `password` VARCHAR(255) NULL DEFAULT NULL,
  `portal_link` VARCHAR(500) NULL DEFAULT NULL,
  `notes` TEXT NULL,
  `status` TINYINT(1) NOT NULL DEFAULT 1,
  `email_sent` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` INT UNSIGNED NULL DEFAULT NULL,
  `updated_at` INT UNSIGNED NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_store_username` (`store_id`, `username`),
  KEY `idx_pharmacist_id` (`pharmacist_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `user_passkeys` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED NOT NULL,
  `credential_id` TEXT NOT NULL,          -- base64url WebAuthn credential ID
  `public_key` TEXT NOT NULL,             -- PEM
  `sign_count` INT UNSIGNED NOT NULL DEFAULT 0,
  `backup_eligible` TINYINT(1) NOT NULL DEFAULT 0,
  `device_name` VARCHAR(255) NULL DEFAULT NULL, -- user agent at registration
  `created_at` INT UNSIGNED NULL DEFAULT NULL,
  `last_used_at` INT UNSIGNED NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_user_passkeys_user_id` (`user_id`) -- one device per user
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------- required seed row ----------
INSERT INTO `notification_settings`
  (`type`, `is_editable`, `addon_identifier`, `user_types`, `system_notification`, `email_notification`, `subject`, `template`, `setting_title`, `setting_sub_title`, `date_updated`)
SELECT 'pharmacist_test_passed_credentials', 1, NULL, '["student","admin"]',
  '{"student":"1","admin":"1"}', '{"student":"0","admin":"0"}',
  '{"student":"Pharmacist Test Passed - Store Credentials","admin":"Pharmacist Test Passed - Store Credentials"}',
  '{"student":"Hi [pharmacist_name],<br>Congratulations! You passed <b>[quiz_title]</b> in [course_title] with [score] (pass mark: [pass_mark]).<br>Store: [shop]<br>Role: [role]<br>Portal: [link]<br>Username: [username]<br>Password: [password]","admin":"[pharmacist_name] passed [quiz_title] in [course_title] with [score] (pass mark: [pass_mark]). Store credentials for [shop] ([role]) were issued."}',
  'Pharmacist test passed', 'Send store credentials when a pharmacist passes the test', UNIX_TIMESTAMP()
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `notification_settings` WHERE `type` = 'pharmacist_test_passed_credentials');
