<?php
// config/migrate_shipping.php
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/app/Core/Database.php';

use App\Core\Database;

try {
    $db = Database::getConnection();
    echo "Connected to database.\n";

    // 1. Create table if not exists (non-destructive)
    $db->exec("
        CREATE TABLE IF NOT EXISTS shipping_destinations (
            id INT AUTO_INCREMENT PRIMARY KEY,
            country VARCHAR(50) NOT NULL,
            port VARCHAR(100) NOT NULL,
            status TINYINT(1) DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");
    echo "Table 'shipping_destinations' ready.\n";

    $existingCount = (int) $db->query("SELECT COUNT(*) FROM shipping_destinations")->fetchColumn();
    if ($existingCount > 0) {
        echo "Skipping shipping seed — {$existingCount} destination(s) already exist.\n";
        return;
    }

    // 2. Default shipping destinations data
    $destinations = [
        ['country' => 'PAKISTAN', 'port' => 'KARACHI'],
        ['country' => 'PAKISTAN', 'port' => 'ISLAMABAD'],
        ['country' => 'KENYA', 'port' => 'MOMBASA'],
        ['country' => 'TANZANIA', 'port' => 'DAR ES SALAAM'],
        ['country' => 'BANGLADESH', 'port' => 'CHITTAGONG'],
        ['country' => 'BANGLADESH', 'port' => 'MONGLA'],
        ['country' => 'SRI LANKA', 'port' => 'COLOMBO'],
        ['country' => 'SRI LANKA', 'port' => 'HAMBANTOTA'],
    ];

    // 3. Insert items
    $stmt = $db->prepare("
        INSERT INTO shipping_destinations (country, port, status)
        VALUES (:country, :port, 1)
    ");

    foreach ($destinations as $dest) {
        $stmt->execute([
            ':country' => $dest['country'],
            ':port' => $dest['port'],
        ]);
    }
    echo "Seed data for 'shipping_destinations' inserted successfully.\n";

} catch (\Exception $e) {
    echo "Migration error: " . $e->getMessage() . "\n";
}
