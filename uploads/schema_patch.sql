-- Schema patch: brings a database imported from uploads/install.sql up to what
-- the current code expects (store/pharmacist tables, new users columns, etc.).
-- Run after importing install.sql, e.g.:
--   mysql -u root <database_name> < uploads/schema_patch.sql
-- Idempotent: safe to re-run.

-- ---------- users: new columns ----------
ALTER TABLE `users`
  ADD COLUMN IF NOT EXISTS `plain_password`     VARCHAR(255) NULL DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `employee_id`        VARCHAR(100) NULL DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `gender`             VARCHAR(20)  NULL DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `store_id`           INT UNSIGNED NULL DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `licence_no`         VARCHAR(100) NULL DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `licence_start_date` VARCHAR(100) NULL DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `licence_end_date`   VARCHAR(100) NULL DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `mac_address`        VARCHAR(255) NULL DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `state`              VARCHAR(100) NULL DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `pharmacy_name`      VARCHAR(255) NULL DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `designation`        VARCHAR(150) NULL DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `region`             VARCHAR(100) NULL DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `email_sent`         TINYINT(1)   NOT NULL DEFAULT 0,
  MODIFY `skills`       LONGTEXT NULL,
  MODIFY `payment_keys` LONGTEXT NULL,
  MODIFY `sessions`     LONGTEXT NULL;
ALTER TABLE `users` ADD INDEX IF NOT EXISTS `idx_users_employee_id` (`employee_id`);
ALTER TABLE `users` ADD INDEX IF NOT EXISTS `idx_users_store_role` (`store_id`, `role_id`);

-- ---------- other existing tables ----------
ALTER TABLE `quiz_results` ADD COLUMN IF NOT EXISTS `credential_email_sent` TINYINT(1) NOT NULL DEFAULT 0;
ALTER TABLE `lesson` ADD COLUMN IF NOT EXISTS `audio_url_for_mobile_application` VARCHAR(400) NULL DEFAULT NULL;

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
  KEY `idx_user_passkeys_user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------- required seed row ----------
INSERT INTO `notification_settings`
  (`type`, `is_editable`, `addon_identifier`, `user_types`, `system_notification`, `email_notification`, `subject`, `template`, `setting_title`, `setting_sub_title`, `date_updated`)
SELECT 'pharmacist_test_passed_credentials', 1, NULL, '["student","admin"]',
  '{"student":"1","admin":"1"}', '{"student":"0","admin":"0"}',
  '{"student":"Pharmacist Test Passed - Store Credentials","admin":"Pharmacist Test Passed - Store Credentials"}',
  '{"student":"Hi [pharmacist_name],<br>Congratulations! You passed <b>[quiz_title]</b> in [course_title] with [score] (pass mark: [pass_mark]).<br>Store: [shop]<br>Role: [role]<br>Portal: [link]<br>Username: [username]<br>Password: [password]","admin":"[pharmacist_name] passed [quiz_title] in [course_title] with [score] (pass mark: [pass_mark]). Store credentials for [shop] ([role]) were issued."}',
  'Pharmacist test passed', 'Send store credentials when a pharmacist passes the test', UNIX_TIMESTAMP()
WHERE NOT EXISTS (SELECT 1 FROM `notification_settings` WHERE `type` = 'pharmacist_test_passed_credentials');
