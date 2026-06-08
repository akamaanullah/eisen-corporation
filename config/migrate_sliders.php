<?php
// config/migrate_sliders.php
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/app/Core/Database.php';

use App\Core\Database;

try {
    $db = Database::getConnection();
    echo "Connected to database.\n";

    // 1. Create table if not exists (non-destructive)
    $db->exec("
        CREATE TABLE IF NOT EXISTS hero_sliders (
            id INT AUTO_INCREMENT PRIMARY KEY,
            image_url VARCHAR(255) NOT NULL,
            title VARCHAR(255) DEFAULT NULL,
            subtitle VARCHAR(255) DEFAULT NULL,
            link_url VARCHAR(255) DEFAULT NULL,
            sort_order INT DEFAULT 0,
            status TINYINT(1) DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");
    echo "Table 'hero_sliders' ready.\n";

    $existingCount = (int) $db->query("SELECT COUNT(*) FROM hero_sliders")->fetchColumn();
    if ($existingCount > 0) {
        echo "Skipping slider seed — {$existingCount} slide(s) already exist.\n";
        return;
    }

    // 2. Default slider data
    $sliders = [
        [
            'image_url' => 'https://images.unsplash.com/photo-1606664515524-ed2f786a0bd6?w=1200&q=80',
            'title' => 'Eisen Corporation | Premium Quality Used Cars',
            'subtitle' => 'Exporting top-tier Japanese vehicles directly from auction centers to your port.',
            'link_url' => '/listing',
            'sort_order' => 1,
            'status' => 1
        ],
        [
            'image_url' => 'https://images.unsplash.com/photo-1618843479313-40f8afb4b4d8?w=1200&q=80',
            'title' => 'Live USS Auction Bidding Sourcing',
            'subtitle' => 'Can\'t find your dream model? Let our yard experts inspect and bid for you live.',
            'link_url' => '/listing',
            'sort_order' => 2,
            'status' => 1
        ],
        [
            'image_url' => 'https://images.unsplash.com/photo-1555215695-3004980ad54e?w=1200&q=80',
            'title' => 'Fast Marine RoRo & Container Shipping',
            'subtitle' => 'Insured logistics, customs clearance, and export document handover coordinated globally.',
            'link_url' => '/listing',
            'sort_order' => 3,
            'status' => 1
        ],
        [
            'image_url' => 'https://images.unsplash.com/photo-1603386329225-868f9b1ee6b9?w=1200&q=80',
            'title' => 'Rigorous Pre-Export Yard Checks',
            'subtitle' => 'Every single stock item goes through independent safety audits at our Kobe yard.',
            'link_url' => '/listing',
            'sort_order' => 4,
            'status' => 1
        ],
        [
            'image_url' => 'https://images.unsplash.com/photo-1519641471654-76ce0107ad1b?w=1200&q=80',
            'title' => 'Transparent landed C&F Price Calculation',
            'subtitle' => 'No hidden charges. Estimate freight, vanning, and marine insurance in real-time.',
            'link_url' => '/listing',
            'sort_order' => 5,
            'status' => 1
        ]
    ];

    // 3. Insert items
    $stmt = $db->prepare("
        INSERT INTO hero_sliders (image_url, title, subtitle, link_url, sort_order, status)
        VALUES (:image_url, :title, :subtitle, :link_url, :sort_order, :status)
    ");

    foreach ($sliders as $slide) {
        $stmt->execute([
            ':image_url' => $slide['image_url'],
            ':title' => $slide['title'],
            ':subtitle' => $slide['subtitle'],
            ':link_url' => $slide['link_url'],
            ':sort_order' => $slide['sort_order'],
            ':status' => $slide['status'],
        ]);
    }
    echo "Seed data for 'hero_sliders' inserted successfully.\n";

} catch (\Exception $e) {
    echo "Migration error: " . $e->getMessage() . "\n";
}
