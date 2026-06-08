<?php
// config/seed_users.php

require_once dirname(__DIR__) . '/vendor/autoload.php';
require_once dirname(__DIR__) . '/config/config.php';

use App\Core\Database;

if (!defined('APP_ENV')) {
    define('APP_ENV', 'development');
}

if (APP_ENV === 'production') {
    fwrite(STDERR, "Refusing to seed users in production. Set APP_ENV=development to seed locally.\n");
    exit(1);
}

try {
    $db = Database::getConnection();
    
    $db->exec("SET FOREIGN_KEY_CHECKS = 0");
    $db->exec("TRUNCATE TABLE users");
    $db->exec("SET FOREIGN_KEY_CHECKS = 1");
    
    $stmt = $db->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
    
    $adminPassword = password_hash('admin123', PASSWORD_BCRYPT);
    $stmt->execute(['Eisen Admin', 'admin@eisen.com', $adminPassword, 'admin']);
    
    $buyerPassword = password_hash('password123', PASSWORD_BCRYPT);
    $stmt->execute(['Tariq Mahmood', 'tariq.m@example.com', $buyerPassword, 'registered_buyer']);
    
    echo "Seeded development users (see README for credentials).\n";
    
} catch (\Exception $e) {
    echo "Error seeding users: " . $e->getMessage() . "\n";
    exit(1);
}
