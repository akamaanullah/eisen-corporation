<?php

use App\Core\MigrationRunner;

return function (PDO $db): void {
    $columns = [
        'first_name' => "VARCHAR(100) NULL AFTER `name`",
        'last_name' => "VARCHAR(100) NULL AFTER `first_name`",
        'address' => "VARCHAR(255) NULL AFTER `destination_port`",
        'address2' => "VARCHAR(255) NULL AFTER `address`",
        'city' => "VARCHAR(100) NULL AFTER `address2`",
        'state' => "VARCHAR(100) NULL AFTER `city`",
        'zip' => "VARCHAR(20) NULL AFTER `state`",
    ];

    foreach ($columns as $name => $definition) {
        if (!MigrationRunner::columnExists($db, 'users', $name)) {
            $db->exec("ALTER TABLE `users` ADD COLUMN `{$name}` {$definition}");
        }
    }
};
