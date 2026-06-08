<?php
// config/migrate_makes_models.php
require_once dirname(__DIR__) . '/vendor/autoload.php';
require_once dirname(__DIR__) . '/config/config.php';

use App\Core\Database;

try {
    $db = Database::getConnection();
    echo "Connected to database.\n";

    $db->exec("
        CREATE TABLE IF NOT EXISTS master_makes_models (
            id INT AUTO_INCREMENT PRIMARY KEY,
            make VARCHAR(50) NOT NULL,
            model VARCHAR(50) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY unique_make_model (make, model)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");
    echo "Table 'master_makes_models' ready.\n";

    $catalogPath = __DIR__ . '/data/master_makes_models_catalog.php';
    if (!is_file($catalogPath)) {
        throw new \RuntimeException('Catalog file missing: ' . $catalogPath);
    }

    /** @var array<string, list<string>> $catalog */
    $catalog = require $catalogPath;

    $beforeCount = (int) $db->query('SELECT COUNT(*) FROM master_makes_models')->fetchColumn();

    $stmt = $db->prepare('INSERT IGNORE INTO master_makes_models (make, model) VALUES (?, ?)');
    $attempted = 0;

    foreach ($catalog as $make => $models) {
        $make = trim((string) $make);
        if ($make === '') {
            continue;
        }

        foreach ($models as $model) {
            $model = trim((string) $model);
            if ($model === '' || strcasecmp($model, 'Any') === 0) {
                continue;
            }

            if (mb_strlen($make) > 50 || mb_strlen($model) > 50) {
                echo "Skip (too long): {$make} / {$model}\n";
                continue;
            }

            $stmt->execute([$make, $model]);
            $attempted++;
        }
    }

    $afterCount = (int) $db->query('SELECT COUNT(*) FROM master_makes_models')->fetchColumn();
    $inserted = $afterCount - $beforeCount;

    echo "Catalog pairs processed: {$attempted}\n";
    echo "New rows inserted: {$inserted}\n";
    echo "Total rows in master_makes_models: {$afterCount}\n";

    $summary = $db->query('SELECT make, COUNT(*) AS model_count FROM master_makes_models GROUP BY make ORDER BY make ASC')->fetchAll(PDO::FETCH_ASSOC);
    foreach ($summary as $row) {
        echo "  - {$row['make']}: {$row['model_count']} models\n";
    }

} catch (\Exception $e) {
    echo "Migration error: " . $e->getMessage() . "\n";
}
