<?php
namespace App\Models;

use App\Core\Database;
use PDO;

class User {
    public static function findById($id) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM users WHERE id = ? LIMIT 1");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public static function findByEmail($email) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        return $stmt->fetch();
    }

    public static function updateProfile($userId, $fullName, $firstName, $lastName, $address, $address2, $city, $state, $zip, $country, $port) {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            UPDATE users 
            SET name = ?, first_name = ?, last_name = ?, address = ?, address2 = ?, city = ?, state = ?, zip = ?, country = ?, destination_port = ?
            WHERE id = ?
        ");
        return $stmt->execute([
            $fullName,
            $firstName,
            $lastName,
            $address,
            $address2,
            $city,
            $state,
            $zip,
            $country,
            $port,
            $userId
        ]);
    }

    public static function getUsdPaymentSummary($userId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT 
                SUM(amount) as total_pay,
                SUM(CASE WHEN vehicle_id IS NOT NULL AND payment_type IN ('Full Car Payment', 'Auction Balance') THEN amount ELSE 0 END) as total_allocated,
                SUM(CASE WHEN payment_type = 'Auction Deposit' THEN amount ELSE 0 END) as total_deposit
            FROM payments 
            WHERE user_id = ? AND status = 'Confirmed' AND currency = 'USD'
        ");
        $stmt->execute([$userId]);
        return $stmt->fetch();
    }

    public static function getJpyPaymentSummary($userId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT 
                SUM(amount) as total_pay,
                SUM(CASE WHEN vehicle_id IS NOT NULL AND payment_type IN ('Full Car Payment', 'Auction Balance') THEN amount ELSE 0 END) as total_allocated,
                SUM(CASE WHEN payment_type = 'Auction Deposit' THEN amount ELSE 0 END) as total_deposit
            FROM payments 
            WHERE user_id = ? AND status = 'Confirmed' AND currency = 'JPY'
        ");
        $stmt->execute([$userId]);
        return $stmt->fetch();
    }

    public static function getUsdSecurityDeposit($userId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT SUM(amount) FROM security_deposits WHERE user_id = ? AND status = 'Approved' AND amount < 5000");
        $stmt->execute([$userId]);
        return (float)$stmt->fetchColumn();
    }

    public static function getJpySecurityDeposit($userId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT SUM(amount) FROM security_deposits WHERE user_id = ? AND status = 'Approved' AND amount >= 5000");
        $stmt->execute([$userId]);
        return (float)$stmt->fetchColumn();
    }

    public static function getLedgerLog($userId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT p.*, v.stock_id, v.chassis_number 
            FROM payments p 
            LEFT JOIN vehicles v ON p.vehicle_id = v.id 
            WHERE p.user_id = ?
            ORDER BY p.created_at DESC
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public static function getActiveBids($userId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT ab.*, v.stock_id, v.make, v.model, v.year, v.fob_price,
                   (SELECT image_url FROM vehicle_images WHERE vehicle_id = v.id ORDER BY sort_order ASC LIMIT 1) as image
            FROM auction_bids ab
            JOIN vehicles v ON ab.vehicle_id = v.id
            WHERE ab.user_id = ?
            ORDER BY ab.placed_at DESC
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public static function getActiveReservations($userId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT r.*, v.stock_id, v.make, v.model, v.year, v.fob_price,
                   (SELECT image_url FROM vehicle_images WHERE vehicle_id = v.id ORDER BY sort_order ASC LIMIT 1) as image
            FROM reservations r
            JOIN vehicles v ON r.vehicle_id = v.id
            WHERE r.user_id = ?
            ORDER BY r.created_at DESC
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }
}
