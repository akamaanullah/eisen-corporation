<?php
// config/alter_users_reset.php

require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/app/Core/Database.php';

use App\Core\Database;

try {
    $db = Database::getConnection();
    
    echo "Altering users table to add password reset columns...\n";
    
    // 1. Fetch existing columns
    $stmt = $db->query("DESCRIBE `users`");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    // 2. Add reset_token if not exists
    if (!in_array('reset_token', $columns)) {
        $db->exec("ALTER TABLE `users` ADD COLUMN `reset_token` VARCHAR(255) NULL AFTER `password`");
        echo "- Added reset_token column\n";
    } else {
        echo "- reset_token column already exists\n";
    }
    
    // 3. Add reset_token_expires if not exists
    if (!in_array('reset_token_expires', $columns)) {
        $db->exec("ALTER TABLE `users` ADD COLUMN `reset_token_expires` DATETIME NULL AFTER `reset_token`");
        echo "- Added reset_token_expires column\n";
    } else {
        echo "- reset_token_expires column already exists\n";
    }
    
    echo "Alteration completed successfully!\n";
    
} catch (\Exception $e) {
    echo "Error altering table: " . $e->getMessage() . "\n";
}
