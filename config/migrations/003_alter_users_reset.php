<?php

use App\Core\MigrationRunner;

return function (PDO $db): void {
    if (!MigrationRunner::columnExists($db, 'users', 'reset_token')) {
        $db->exec("ALTER TABLE `users` ADD COLUMN `reset_token` VARCHAR(255) NULL AFTER `password`");
    }
    if (!MigrationRunner::columnExists($db, 'users', 'reset_token_expires')) {
        $db->exec("ALTER TABLE `users` ADD COLUMN `reset_token_expires` DATETIME NULL AFTER `reset_token`");
    }
};
