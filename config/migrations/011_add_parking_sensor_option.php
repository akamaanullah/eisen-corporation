<?php

use App\Core\MigrationRunner;

return function (PDO $db): void {
    if (!MigrationRunner::tableExists($db, 'options')) {
        return;
    }

    $stmt = $db->prepare("INSERT IGNORE INTO `options` (`category`, `label`) VALUES ('Safety', ?)");
    $stmt->execute(['Parking Sensor']);
};
