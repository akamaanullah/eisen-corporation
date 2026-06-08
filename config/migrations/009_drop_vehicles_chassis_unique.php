<?php

use App\Core\MigrationRunner;

return function (PDO $db): void {
    if (!MigrationRunner::tableExists($db, 'vehicles')) {
        return;
    }

    $stmt = $db->query("
        SELECT INDEX_NAME
        FROM information_schema.STATISTICS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'vehicles'
          AND COLUMN_NAME = 'chassis_number'
          AND NON_UNIQUE = 0
        LIMIT 1
    ");
    $indexName = $stmt->fetchColumn();

    if ($indexName) {
        $db->exec("ALTER TABLE `vehicles` DROP INDEX `{$indexName}`");
    }
};
