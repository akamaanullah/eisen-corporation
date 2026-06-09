<?php

use App\Core\MigrationRunner;

return function (PDO $db): void {
    if (!MigrationRunner::tableExists($db, 'vehicles')) {
        return;
    }

    if (!MigrationRunner::columnExists($db, 'vehicles', 'auction_end_date')) {
        $db->exec("ALTER TABLE `vehicles` ADD COLUMN `auction_end_date` DATE NULL AFTER `lot_number`");
    }
};
