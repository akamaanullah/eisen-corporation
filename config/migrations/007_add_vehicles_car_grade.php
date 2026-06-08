<?php

use App\Core\MigrationRunner;

return function (PDO $db): void {
    if (!MigrationRunner::columnExists($db, 'vehicles', 'car_grade')) {
        $db->exec(
            "ALTER TABLE `vehicles` ADD COLUMN `car_grade` VARCHAR(30) NOT NULL DEFAULT '' AFTER `grade`"
        );
    }
};
