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