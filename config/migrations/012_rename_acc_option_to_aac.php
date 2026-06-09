<?php

use App\Core\MigrationRunner;

return function (PDO $db): void {
    if (!MigrationRunner::tableExists($db, 'options')) {
        return;
    }

    $stmt = $db->prepare("UPDATE `options` SET `label` = 'AAC' WHERE `label` = 'ACC'");
    $stmt->execute();
};
