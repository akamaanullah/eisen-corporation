<?php
/**
 * Unified migration runner for schema changes.
 *
 * Usage: php config/migrate_all.php
 */

require_once dirname(__DIR__) . '/vendor/autoload.php';
require_once __DIR__ . '/config.php';

use App\Core\Database;
use App\Core\MigrationRunner;

if (!defined('APP_ENV')) {
    define('APP_ENV', 'development');
}

echo "=== Eisen Database Migrations ===\n";
echo "Environment: " . APP_ENV . "\n\n";

try {
    $db = Database::getConnection();
    $runner = new MigrationRunner($db, __DIR__ . '/migrations');
    $ran = $runner->runPending();

    if (empty($ran)) {
        echo "No pending schema migrations.\n";
    } else {
        echo "\nApplied " . count($ran) . " migration(s):\n";
        foreach ($ran as $version) {
            echo "  - {$version}\n";
        }
    }

    echo "\n--- Content seeds (idempotent) ---\n";
    $contentScripts = [
        'migrate_sliders.php',
        'migrate_directory.php',
        'migrate_shipping.php',
        'migrate_makes_models.php',
        'migrate_blog.php',
    ];

    foreach ($contentScripts as $script) {
        $path = __DIR__ . '/' . $script;
        if (!file_exists($path)) {
            echo "Skip missing: {$script}\n";
            continue;
        }
        echo "Running {$script}...\n";
        include $path;
        echo "--------------------------------------------------\n";
    }

    echo "\n=== Migration run completed ===\n";
} catch (\Throwable $e) {
    fwrite(STDERR, "Migration failed: " . $e->getMessage() . "\n");
    exit(1);
}
