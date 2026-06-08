<?php

use App\Core\MigrationRunner;

return function (PDO $db): void {
    if (MigrationRunner::tableExists($db, 'consignees')) {
        return;
    }

    $db->exec("
        CREATE TABLE `consignees` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `user_id` INT NOT NULL,
            `consignee_name` VARCHAR(150) NOT NULL DEFAULT '',
            `consignee_country` VARCHAR(100) NOT NULL DEFAULT '',
            `consignee_state` VARCHAR(100) NOT NULL DEFAULT '',
            `consignee_city` VARCHAR(100) NOT NULL DEFAULT '',
            `consignee_address` VARCHAR(255) NOT NULL DEFAULT '',
            `consignee_ref_address` VARCHAR(255) NOT NULL DEFAULT '',
            `consignee_phone_1` VARCHAR(30) NOT NULL DEFAULT '',
            `consignee_phone_2` VARCHAR(30) NOT NULL DEFAULT '',
            `consignee_phone_3` VARCHAR(30) NOT NULL DEFAULT '',
            `consignee_email_1` VARCHAR(150) NOT NULL DEFAULT '',
            `consignee_email_2` VARCHAR(150) NOT NULL DEFAULT '',
            `consignee_email_3` VARCHAR(150) NOT NULL DEFAULT '',
            `notify_name` VARCHAR(150) NOT NULL DEFAULT '',
            `notify_country` VARCHAR(100) NOT NULL DEFAULT '',
            `notify_state` VARCHAR(100) NOT NULL DEFAULT '',
            `notify_city` VARCHAR(100) NOT NULL DEFAULT '',
            `notify_address` VARCHAR(255) NOT NULL DEFAULT '',
            `notify_ref_address` VARCHAR(255) NOT NULL DEFAULT '',
            `notify_phone_1` VARCHAR(30) NOT NULL DEFAULT '',
            `notify_phone_2` VARCHAR(30) NOT NULL DEFAULT '',
            `notify_phone_3` VARCHAR(30) NOT NULL DEFAULT '',
            `notify_email_1` VARCHAR(150) NOT NULL DEFAULT '',
            `notify_email_2` VARCHAR(150) NOT NULL DEFAULT '',
            `notify_email_3` VARCHAR(150) NOT NULL DEFAULT '',
            `notify_same` TINYINT(1) NOT NULL DEFAULT 0,
            `permanent` TINYINT(1) NOT NULL DEFAULT 0,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY `uk_consignees_user` (`user_id`),
            CONSTRAINT `fk_consignees_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
};
