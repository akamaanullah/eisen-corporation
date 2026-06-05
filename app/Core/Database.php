<?php
namespace App\Core;

use PDO;
use PDOException;

class Database {
    private static $instance = null;

    /**
     * Get the PDO connection instance (Singleton)
     * 
     * @return PDO
     */
    public static function getConnection() {
        if (self::$instance === null) {
            try {
                $host = DB_HOST;
                $db = DB_NAME;
                $user = DB_USER;
                $pass = DB_PASS;
                
                self::$instance = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]);
            } catch (PDOException $e) {
                error_log("Database connection failed: " . $e->getMessage());
                http_response_code(500);
                die("A database connection error occurred. Please contact the system administrator.");
            }
        }
        return self::$instance;
    }
}
