<?php
// config/migrate_blog.php
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/app/Core/Database.php';

use App\Core\Database;

try {
    $db = Database::getConnection();
    echo "Connected to database.\n";

    // 1. Create table if not exists (non-destructive)
    $db->exec("
        CREATE TABLE IF NOT EXISTS blog_posts (
            id INT AUTO_INCREMENT PRIMARY KEY,
            slug VARCHAR(100) UNIQUE NOT NULL,
            title VARCHAR(255) NOT NULL,
            excerpt TEXT,
            content TEXT,
            image VARCHAR(255),
            category VARCHAR(50),
            category_key VARCHAR(50),
            read_min INT DEFAULT 5,
            author VARCHAR(100),
            published_date DATE NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");
    echo "Table 'blog_posts' ready.\n";

    $existingCount = (int) $db->query("SELECT COUNT(*) FROM blog_posts")->fetchColumn();
    if ($existingCount > 0) {
        echo "Skipping blog seed — {$existingCount} post(s) already exist.\n";
        return;
    }

    // 2. Original static posts array
    $posts = [
        [
            'slug' => 'read-uss-auction-sheet',
            'title' => 'How to Read a USS Auction Sheet Before You Bid',
            'category' => 'Japan Auctions',
            'categoryKey' => 'auctions',
            'published_date' => '2025-05-12',
            'readMin' => 8,
            'excerpt' => 'Learn what auction grades, inspector notes, and chassis codes mean so you can shortlist vehicles with confidence before export.',
            'image' => 'photo-1618843479313-40f8afb4b4d8',
            'author' => 'Eisen Export Team',
            'content' => 'Every vehicle listed at USS Tokyo, TAA, and other major Japan auction houses ships with a standardized inspection sheet. For importers, that sheet is your first filter — long before you calculate freight or customs.'
        ],
        [
            'slug' => 'jpy-usd-import-budget',
            'title' => 'How JPY–USD Moves Affect Your Import Budget',
            'category' => 'Market & Pricing',
            'categoryKey' => 'market',
            'published_date' => '2025-05-02',
            'readMin' => 6,
            'excerpt' => 'A practical guide for dealers tracking yen volatility, landed cost, and when to lock in conversion for Japan auction purchases.',
            'image' => 'photo-1553440569-bcc63803a83d',
            'author' => 'Eisen Export Team',
            'content' => 'Japan auction sourcing rewards preparation. Review condition reports, confirm export documentation early, and align your bid with landed cost — not just hammer price at the lane.'
        ],
        [
            'slug' => 'first-time-japan-import-checklist',
            'title' => 'First-Time Japan Import Checklist for Private Buyers',
            'category' => 'Buying Guides',
            'categoryKey' => 'guides',
            'published_date' => '2025-04-18',
            'readMin' => 10,
            'excerpt' => 'From auction selection to port arrival — documents, inspection, and timelines importers should plan before placing a bid.',
            'image' => 'photo-1606664515524-ed2f786a0bd6',
            'author' => 'Eisen Export Team',
            'content' => 'Japan auction sourcing rewards preparation. Review condition reports, confirm export documentation early, and align your bid with landed cost — not just hammer price at the lane.'
        ],
        [
            'slug' => 'export-shipping-timelines',
            'title' => 'Export Shipping Timelines: RoRo vs Container Explained',
            'category' => 'Import & Export',
            'categoryKey' => 'export',
            'published_date' => '2025-04-05',
            'readMin' => 7,
            'excerpt' => 'Compare roll-on roll-off and container options, typical port schedules, and how Eisen coordinates logistics for global buyers.',
            'image' => 'photo-1549317661-bd32c8ce0db2',
            'author' => 'Eisen Export Team',
            'content' => 'Japan auction sourcing rewards preparation. Review condition reports, confirm export documentation early, and align your bid with landed cost — not just hammer price at the lane.'
        ],
        [
            'slug' => 'hybrid-suv-demand-2025',
            'title' => 'Why Hybrid SUVs Dominate Japan Auction Demand in 2025',
            'category' => 'Vehicle Spotlights',
            'categoryKey' => 'spotlights',
            'published_date' => '2025-03-22',
            'readMin' => 5,
            'excerpt' => 'Market trends behind Toyota and Honda hybrid stock — resale appeal, grade availability, and what dealers are stocking overseas.',
            'image' => 'photo-1503376780353-7e6692767b70',
            'author' => 'Eisen Export Team',
            'content' => 'Japan auction sourcing rewards preparation. Review condition reports, confirm export documentation early, and align your bid with landed cost — not just hammer price at the lane.'
        ],
        [
            'slug' => 'dealer-vs-private-auction',
            'title' => 'Dealer vs Private Buyer: Choosing the Right Auction Lane',
            'category' => 'Buying Guides',
            'categoryKey' => 'guides',
            'published_date' => '2025-03-08',
            'readMin' => 6,
            'excerpt' => 'Understand lane access, fees, and volume advantages so your sourcing strategy matches your business model.',
            'image' => 'photo-1552519507-da3b142c6e3d',
            'author' => 'Eisen Export Team',
            'content' => 'Japan auction sourcing rewards preparation. Review condition reports, confirm export documentation early, and align your bid with landed cost — not just hammer price at the lane.'
        ],
        [
            'slug' => 'eisen-inspection-process',
            'title' => 'Inside Eisen\'s Pre-Export Inspection Process',
            'category' => 'Company',
            'categoryKey' => 'company',
            'published_date' => '2025-02-14',
            'readMin' => 5,
            'excerpt' => 'How our team verifies auction listings, documents condition reports, and prepares vehicles for international handover.',
            'image' => 'photo-1555215695-3004980ad54e',
            'author' => 'Eisen Export Team',
            'content' => 'Japan auction sourcing rewards preparation. Review condition reports, confirm export documentation early, and align your bid with landed cost — not just hammer price at the lane.'
        ],
        [
            'slug' => 'grade-r-repair-history',
            'title' => 'Grade R and Repair History: What Importers Should Know',
            'category' => 'Japan Auctions',
            'categoryKey' => 'auctions',
            'published_date' => '2025-01-30',
            'readMin' => 9,
            'excerpt' => 'When a repaired auction vehicle is worth considering, red flags on sheets, and how to price refurbishment into landed cost.',
            'image' => 'photo-1519641471654-76ce0107ad1b',
            'author' => 'Eisen Export Team',
            'content' => 'Japan auction sourcing rewards preparation. Review condition reports, confirm export documentation early, and align your bid with landed cost — not just hammer price at the lane.'
        ],
        [
            'slug' => 'winter-auction-season-tips',
            'title' => 'Winter Auction Season: Bidding Tips for Snow-Belt Stock',
            'category' => 'Japan Auctions',
            'categoryKey' => 'auctions',
            'published_date' => '2025-01-12',
            'readMin' => 4,
            'excerpt' => 'Seasonal supply shifts, underbody rust checks, and models that hold value when sourced from northern Japan auctions.',
            'image' => 'photo-1603386329225-868f9b1ee6b9',
            'author' => 'Eisen Export Team',
            'content' => 'Japan auction sourcing rewards preparation. Review condition reports, confirm export documentation early, and align your bid with landed cost — not just hammer price at the lane.'
        ],
    ];

    // 3. Insert items
    $stmt = $db->prepare("
        INSERT INTO blog_posts (slug, title, excerpt, content, image, category, category_key, read_min, author, published_date)
        VALUES (:slug, :title, :excerpt, :content, :image, :category, :category_key, :read_min, :author, :published_date)
    ");

    foreach ($posts as $post) {
        $stmt->execute([
            ':slug' => $post['slug'],
            ':title' => $post['title'],
            ':excerpt' => $post['excerpt'],
            ':content' => $post['content'],
            ':image' => $post['image'],
            ':category' => $post['category'],
            ':category_key' => $post['categoryKey'],
            ':read_min' => $post['readMin'],
            ':author' => $post['author'],
            ':published_date' => $post['published_date'],
        ]);
    }
    echo "Seed data for 'blog_posts' inserted successfully.\n";

} catch (\Exception $e) {
    echo "Migration error: " . $e->getMessage() . "\n";
}
