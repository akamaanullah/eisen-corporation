<?php
// config/migrate_directory.php
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/app/Core/Database.php';

use App\Core\Database;

try {
    $db = Database::getConnection();
    echo "Connected to database.\n";

    // 1. Create table if not exists (non-destructive)
    $db->exec("
        CREATE TABLE IF NOT EXISTS directory_partners (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            logo_url VARCHAR(255) NOT NULL,
            type ENUM('dealer', 'service', 'insurance') NOT NULL,
            sort_order INT DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");
    echo "Table 'directory_partners' ready.\n";

    $existingCount = (int) $db->query("SELECT COUNT(*) FROM directory_partners")->fetchColumn();
    if ($existingCount > 0) {
        echo "Skipping directory seed — {$existingCount} partner(s) already exist.\n";
        return;
    }

    // 2. Default directory partners data
    $partners = [
        // Dealers
        ['name' => 'Mira Daihatsu', 'logo_url' => '/public/image/mira-daihatsu.png', 'type' => 'dealer', 'sort_order' => 1],
        ['name' => 'Toyota', 'logo_url' => '/public/image/toyota.png', 'type' => 'dealer', 'sort_order' => 2],
        ['name' => 'Nissan', 'logo_url' => '/public/image/nissan.png', 'type' => 'dealer', 'sort_order' => 3],
        ['name' => 'Daihatsu', 'logo_url' => '/public/image/daihatsu.png', 'type' => 'dealer', 'sort_order' => 4],
        
        // Service Stations
        ['name' => 'Mira Daihatsu', 'logo_url' => '/public/image/mira-daihatsu.png', 'type' => 'service', 'sort_order' => 1],
        ['name' => 'Toyota', 'logo_url' => '/public/image/toyota.png', 'type' => 'service', 'sort_order' => 2],
        ['name' => 'Nissan', 'logo_url' => '/public/image/nissan.png', 'type' => 'service', 'sort_order' => 3],
        ['name' => 'Daihatsu', 'logo_url' => '/public/image/daihatsu.png', 'type' => 'service', 'sort_order' => 4],
        
        // Insurance Partners
        ['name' => 'Mira Daihatsu', 'logo_url' => '/public/image/mira-daihatsu.png', 'type' => 'insurance', 'sort_order' => 1],
        ['name' => 'Toyota', 'logo_url' => '/public/image/toyota.png', 'type' => 'insurance', 'sort_order' => 2],
        ['name' => 'Nissan', 'logo_url' => '/public/image/nissan.png', 'type' => 'insurance', 'sort_order' => 3],
        ['name' => 'Daihatsu', 'logo_url' => '/public/image/daihatsu.png', 'type' => 'insurance', 'sort_order' => 4]
    ];

    // 3. Insert items
    $stmt = $db->prepare("
        INSERT INTO directory_partners (name, logo_url, type, sort_order)
        VALUES (:name, :logo_url, :type, :sort_order)
    ");

    foreach ($partners as $partner) {
        $stmt->execute([
            ':name' => $partner['name'],
            ':logo_url' => $partner['logo_url'],
            ':type' => $partner['type'],
            ':sort_order' => $partner['sort_order'],
        ]);
    }
    echo "Seed data for 'directory_partners' inserted successfully.\n";

} catch (\Exception $e) {
    echo "Migration error: " . $e->getMessage() . "\n";
}
