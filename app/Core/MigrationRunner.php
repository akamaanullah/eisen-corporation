<?php
namespace App\Core;

use PDO;

class MigrationRunner
{
    private PDO $db;
    private string $migrationsDir;

    public function __construct(PDO $db, string $migrationsDir)
    {
        $this->db = $db;
        $this->migrationsDir = rtrim($migrationsDir, '/\\');
    }

    public function runPending(): array
    {
        $this->ensureMigrationsTable();

        $applied = $this->getAppliedVersions();
        $files = glob($this->migrationsDir . '/*.php') ?: [];
        sort($files);

        $ran = [];

        foreach ($files as $file) {
            $version = basename($file, '.php');
            if (in_array($version, $applied, true)) {
                continue;
            }

            $migration = require $file;
            if (!is_callable($migration)) {
                throw new \RuntimeException("Migration {$version} must return a callable.");
            }

            echo "Applying migration: {$version}...\n";
            try {
                $migration($this->db);
                $stmt = $this->db->prepare(
                    'INSERT INTO schema_migrations (version, applied_at) VALUES (?, NOW())'
                );
                $stmt->execute([$version]);
                $ran[] = $version;
                echo "  OK\n";
            } catch (\Throwable $e) {
                throw new \RuntimeException(
                    "Migration {$version} failed: " . $e->getMessage(),
                    0,
                    $e
                );
            }
        }

        return $ran;
    }

    private function ensureMigrationsTable(): void
    {
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS schema_migrations (
                version VARCHAR(191) NOT NULL PRIMARY KEY,
                applied_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }

    private function getAppliedVersions(): array
    {
        $stmt = $this->db->query('SELECT version FROM schema_migrations ORDER BY version ASC');
        return $stmt->fetchAll(PDO::FETCH_COLUMN) ?: [];
    }

    public static function columnExists(PDO $db, string $table, string $column): bool
    {
        $stmt = $db->prepare(
            'SELECT COUNT(*) FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?'
        );
        $stmt->execute([$table, $column]);
        return (int) $stmt->fetchColumn() > 0;
    }

    public static function tableExists(PDO $db, string $table): bool
    {
        $stmt = $db->prepare(
            'SELECT COUNT(*) FROM information_schema.TABLES
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?'
        );
        $stmt->execute([$table]);
        return (int) $stmt->fetchColumn() > 0;
    }
}
