-- 1. Add enable_2fa after enable_ip
ALTER TABLE `users`
ADD COLUMN `enable_2fa` INT(8) NOT NULL DEFAULT '0' AFTER `enable_ip`;

-- 2. Add verify_2fa (added at the end if you want to avoid column ordering issues)
ALTER TABLE `users`
ADD COLUMN `verify_2fa` INT(8) NOT NULL DEFAULT '0' AFTER `enable_2fa`;

-- 3. Add secret after verify_2fa (now that verify_2fa exists)
ALTER TABLE `users`
ADD COLUMN `secret` VARCHAR(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL AFTER `verify_2fa`;

-- 4. Add daily after web_hook_token
ALTER TABLE `users`
ADD COLUMN `daily` INT(11) DEFAULT NULL AFTER `web_hook_token`;

-- 5. Add weekly after daily
ALTER TABLE `users`
ADD COLUMN `weekly` INT(11) DEFAULT NULL AFTER `daily`;

-- 6. Add monthly after weekly
ALTER TABLE `users`
ADD COLUMN `monthly` INT(11) DEFAULT NULL AFTER `weekly`;

ALTER TABLE `users`
ADD COLUMN `tt_v2_refresh_popup_seen` TINYINT(1) NOT NULL DEFAULT 0
AFTER `can_process_order`;



ALTER TABLE users ADD login_attempts TINYINT UNSIGNED DEFAULT 0;
ALTER TABLE users ADD otp_attempts TINYINT UNSIGNED DEFAULT 0;
ALTER TABLE users ADD otp_hash VARCHAR(255) NULL;
ALTER TABLE users ADD otp_expires_at TIMESTAMP NULL;
ALTER TABLE users ADD enable_2fa TINYINT UNSIGNED DEFAULT 0;
ALTER TABLE users ADD verify_2fa TINYINT UNSIGNED DEFAULT 0;
ALTER TABLE users ADD secret VARCHAR(255) NULL;




CREATE TABLE `menu_audit_logs` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` INT UNSIGNED NULL,
    `action` VARCHAR(80) NOT NULL,
    `module` VARCHAR(80) NOT NULL,
    `old_values` LONGTEXT NULL,
    `new_values` LONGTEXT NULL,
    `ip_address` VARCHAR(45) NULL,
    `user_agent` TEXT NULL,
    `created_at` DATETIME NULL,
    
    PRIMARY KEY (`id`),
    
    INDEX `menu_audit_logs_module_created_at_index` (`module`, `created_at`),
    INDEX `menu_audit_logs_user_id_index` (`user_id`)
    
) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;


ALTER TABLE `users`
ADD COLUMN `max_active_sessions` TINYINT UNSIGNED NOT NULL DEFAULT 1 AFTER `last_session_id`,
ADD COLUMN `active_session_ids` TEXT NULL AFTER `max_active_sessions`;