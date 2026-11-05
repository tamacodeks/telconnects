CREATE TABLE `night_maintenance_settings` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` BIGINT UNSIGNED NULL,
    `enabled` TINYINT(1) NOT NULL DEFAULT 1,
    `allowed_start` TIME NOT NULL DEFAULT '07:30:00',
    `allowed_end` TIME NOT NULL DEFAULT '00:00:00',
    `timezone` VARCHAR(64) NOT NULL DEFAULT 'Europe/Paris',
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,

    PRIMARY KEY (`id`),
    UNIQUE KEY `night_maintenance_settings_user_id_unique` (`user_id`),
    KEY `night_maintenance_settings_enabled_index` (`enabled`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `night_maintenance_settings`
(
    `user_id`,
    `enabled`,
    `allowed_start`,
    `allowed_end`,
    `timezone`,
    `created_at`,
    `updated_at`
)
VALUES
(
    NULL,
    1,
    '07:30:00',
    '00:00:00',
    'Europe/Paris',
    NOW(),
    NOW()
);


CREATE TABLE `topup_processing_settings` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` INT UNSIGNED NOT NULL,
    `mode` VARCHAR(20) NOT NULL DEFAULT 'synchronous',
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,

    PRIMARY KEY (`id`),
    UNIQUE KEY `topup_processing_settings_user_id_unique` (`user_id`),
    KEY `topup_processing_settings_mode_index` (`mode`),

    CONSTRAINT `topup_processing_settings_user_id_foreign`
        FOREIGN KEY (`user_id`)
        REFERENCES `users` (`id`)
        ON DELETE CASCADE
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;


  CREATE TABLE `queued_topups` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `request_id` VARCHAR(64) NOT NULL,
    `provider` VARCHAR(20) NOT NULL,
    `user_id` INT UNSIGNED NOT NULL,
    `order_id` INT UNSIGNED NOT NULL,
    `track_order_id` INT UNSIGNED NULL,
    `mobile` VARCHAR(32) NULL,
    `local_reference` VARCHAR(100) NOT NULL,
    `provider_reference` VARCHAR(191) NULL,
    `status` VARCHAR(20) NOT NULL DEFAULT 'pending',
    `payload` LONGTEXT NOT NULL,
    `provider_response` LONGTEXT NULL,
    `error_message` TEXT NULL,
    `client_ip` VARCHAR(45) NULL,
    `user_agent` TEXT NULL,
    `attempts` SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    `dispatched_at` TIMESTAMP NULL DEFAULT NULL,
    `processed_at` TIMESTAMP NULL DEFAULT NULL,
    `failed_at` TIMESTAMP NULL DEFAULT NULL,
    `refunded_at` TIMESTAMP NULL DEFAULT NULL,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,

    PRIMARY KEY (`id`),
    UNIQUE KEY `queued_topups_request_id_unique` (`request_id`),
    KEY `queued_topups_provider_index` (`provider`),
    KEY `queued_topups_user_id_index` (`user_id`),
    KEY `queued_topups_order_id_index` (`order_id`),
    KEY `queued_topups_track_order_id_index` (`track_order_id`),
    KEY `queued_topups_mobile_index` (`mobile`),
    KEY `queued_topups_local_reference_index` (`local_reference`),
    KEY `queued_topups_provider_reference_index` (`provider_reference`),
    KEY `queued_topups_status_index` (`status`),
    KEY `queued_topups_dispatched_at_index` (`dispatched_at`),
    KEY `queued_topups_status_created_at_index` (`status`, `created_at`)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;