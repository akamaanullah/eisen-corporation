<?php
namespace App\Models;

use App\Core\Database;
use PDO;

class Vehicle {
    public static function findFavoritesByUserId($userId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT v.*, 
                   (SELECT image_url FROM vehicle_images WHERE vehicle_id = v.id ORDER BY sort_order ASC LIMIT 1) as image
            FROM vehicle_favorites vf
            JOIN vehicles v ON vf.vehicle_id = v.id
            WHERE vf.user_id = ?
            ORDER BY vf.created_at DESC
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public static function findPurchasedByUserId($userId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT DISTINCT v.*, s.bl_number, s.vessel_name, s.etd, s.eta, s.status as shipment_status,
                   (SELECT image_url FROM vehicle_images WHERE vehicle_id = v.id ORDER BY sort_order ASC LIMIT 1) as image
            FROM vehicles v
            JOIN payments p ON p.vehicle_id = v.id
            LEFT JOIN shipments s ON s.vehicle_id = v.id
            WHERE p.user_id = ? AND p.status = 'Confirmed' AND p.payment_type IN ('Full Car Payment', 'Auction Balance')
            ORDER BY p.created_at DESC
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public static function findByStockId($stockId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT id FROM vehicles WHERE stock_id = ? LIMIT 1");
        $stmt->execute([$stockId]);
        return $stmt->fetch();
    }

    public static function removeFavorite($userId, $vehicleId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("DELETE FROM vehicle_favorites WHERE user_id = ? AND vehicle_id = ?");
        return $stmt->execute([$userId, $vehicleId]);
    }

    public static function addFavorite($userId, $vehicleId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("INSERT IGNORE INTO vehicle_favorites (user_id, vehicle_id) VALUES (?, ?)");
        return $stmt->execute([$userId, $vehicleId]);
    }

    public static function isFavorited($userId, $vehicleId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT 1 FROM vehicle_favorites WHERE user_id = ? AND vehicle_id = ? LIMIT 1");
        $stmt->execute([$userId, $vehicleId]);
        return (bool)$stmt->fetchColumn();
    }
}
