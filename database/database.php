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


--from this we need to add for new login panel--
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



CREATE TABLE `topup_confirm_attempts` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `request_id` VARCHAR(64) NOT NULL,
    `layer` VARCHAR(32) NOT NULL DEFAULT 'frontend',
    `provider` VARCHAR(32) NOT NULL,
    `user_id` INT UNSIGNED NULL,
    `username` VARCHAR(100) NULL,
    `mobile` VARCHAR(32) NOT NULL,
    `amount` VARCHAR(32) NULL,
    `status` VARCHAR(32) NOT NULL,
    `lock_key` VARCHAR(191) NULL,
    `internal_reference` VARCHAR(100) NULL,
    `provider_reference` VARCHAR(191) NULL,
    `order_id` INT UNSIGNED NULL,
    `transaction_id` INT UNSIGNED NULL,
    `ip` VARCHAR(45) NULL,
    `user_agent` TEXT NULL,
    `referer` VARCHAR(191) NULL,
    `origin` VARCHAR(191) NULL,
    `error_message` TEXT NULL,
    `metadata` TEXT NULL,
    `locked_at` TIMESTAMP NULL DEFAULT NULL,
    `completed_at` TIMESTAMP NULL DEFAULT NULL,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,

    PRIMARY KEY (`id`),

    UNIQUE KEY `topup_confirm_attempts_request_id_unique` (`request_id`),

    KEY `topup_confirm_attempts_user_id_index` (`user_id`),
    KEY `topup_confirm_attempts_mobile_index` (`mobile`),
    KEY `topup_confirm_attempts_status_index` (`status`),
    KEY `topup_confirm_attempts_lock_key_index` (`lock_key`),
    KEY `topup_confirm_attempts_internal_reference_index` (`internal_reference`),
    KEY `topup_confirm_attempts_provider_reference_index` (`provider_reference`),
    KEY `topup_confirm_attempts_order_id_index` (`order_id`),
    KEY `topup_confirm_attempts_transaction_id_index` (`transaction_id`),

    KEY `topup_confirm_attempts_mobile_status_index` (`mobile`, `status`),
    KEY `topup_confirm_attempts_provider_mobile_index` (`provider`, `mobile`)
) ENGINE=InnoDB
DEFAULT CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;