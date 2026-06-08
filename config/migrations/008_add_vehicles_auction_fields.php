<?php

use App\Core\MigrationRunner;

return function (PDO $db): void {
    $columns = [
        'auction_house' => "VARCHAR(100) NOT NULL DEFAULT '' AFTER `type`",
        'lot_number' => "VARCHAR(50) NOT NULL DEFAULT '' AFTER `auction_house`",
    ];

    foreach ($columns as $name => $definition) {
        if (!MigrationRunner::columnExists($db, 'vehicles', $name)) {
            $db->exec("ALTER TABLE `vehicles` ADD COLUMN `{$name}` {$definition}");
        }
    }
};
