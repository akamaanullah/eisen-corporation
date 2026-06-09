<?php

use App\Core\MigrationRunner;

return function (PDO $db): void {
    if (!MigrationRunner::tableExists($db, 'vehicles')) {
        return;
    }

    $db->exec("ALTER TABLE `vehicles` MODIFY `transmission` VARCHAR(20) NOT NULL DEFAULT 'AT'");
    $db->exec("ALTER TABLE `vehicles` MODIFY `fuel` VARCHAR(30) NOT NULL DEFAULT 'PETROL'");
};
