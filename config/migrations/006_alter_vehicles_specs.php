<?php

use App\Core\MigrationRunner;

return function (PDO $db): void {
    $db->exec("ALTER TABLE `vehicles` MODIFY COLUMN `color` VARCHAR(30) NOT NULL DEFAULT 'White'");

    $columns = [
        'steering' => "ENUM('RHD', 'LHD') NOT NULL DEFAULT 'RHD' AFTER `transmission`",
        'fuel' => "ENUM('PETROL', 'DIESEL', 'HYBRID', 'ELECTRIC') NOT NULL DEFAULT 'PETROL' AFTER `steering`",
        'doors' => "INT NOT NULL DEFAULT 5 AFTER `fuel`",
        'seats' => "INT NOT NULL DEFAULT 5 AFTER `doors`",
        'location' => "VARCHAR(100) NOT NULL DEFAULT 'KOBE, JAPAN' AFTER `seats`",
        'freight_price' => "DECIMAL(12, 2) NOT NULL DEFAULT 0.00 AFTER `fob_price`",
        'vanning_price' => "DECIMAL(12, 2) NOT NULL DEFAULT 0.00 AFTER `freight_price`",
        'inspection_price' => "DECIMAL(12, 2) NOT NULL DEFAULT 0.00 AFTER `vanning_price`",
        'insurance_price' => "DECIMAL(12, 2) NOT NULL DEFAULT 0.00 AFTER `inspection_price`",
        'dimension' => "VARCHAR(50) NOT NULL DEFAULT '0.00m × 0.00m × 0.00m'",
        'm3' => "VARCHAR(20) NOT NULL DEFAULT '10.167'",
        'description' => "TEXT NULL",
        'views' => "INT NOT NULL DEFAULT 0",
        'price_jpy' => "DECIMAL(12, 2) NOT NULL DEFAULT 0.00 AFTER `fob_price`",
    ];

    foreach ($columns as $name => $definition) {
        if (!MigrationRunner::columnExists($db, 'vehicles', $name)) {
            $db->exec("ALTER TABLE `vehicles` ADD COLUMN `{$name}` {$definition}");
        }
    }
};
