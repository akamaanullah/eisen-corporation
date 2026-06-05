<?php
namespace App\Models;

use App\Core\Database;
use PDO;

class Consignee {
    public static function findByUserId($userId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM consignees WHERE user_id = ? LIMIT 1");
        $stmt->execute([$userId]);
        return $stmt->fetch() ?: [];
    }

    public static function existsForUser($userId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT id FROM consignees WHERE user_id = ? LIMIT 1");
        $stmt->execute([$userId]);
        return (bool)$stmt->fetch();
    }

    public static function update($userId, $data) {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            UPDATE consignees 
            SET consignee_name = ?, consignee_country = ?, consignee_state = ?, consignee_city = ?, consignee_address = ?, consignee_ref_address = ?,
                consignee_phone_1 = ?, consignee_phone_2 = ?, consignee_phone_3 = ?, consignee_email_1 = ?, consignee_email_2 = ?, consignee_email_3 = ?,
                notify_name = ?, notify_country = ?, notify_state = ?, notify_city = ?, notify_address = ?, notify_ref_address = ?,
                notify_phone_1 = ?, notify_phone_2 = ?, notify_phone_3 = ?, notify_email_1 = ?, notify_email_2 = ?, notify_email_3 = ?,
                notify_same = ?, permanent = ?
            WHERE user_id = ?
        ");
        return $stmt->execute([
            $data['consignee_name'], $data['consignee_country'], $data['consignee_state'], $data['consignee_city'], $data['consignee_address'], $data['consignee_ref_address'],
            $data['consignee_phone_1'], $data['consignee_phone_2'], $data['consignee_phone_3'], $data['consignee_email_1'], $data['consignee_email_2'], $data['consignee_email_3'],
            $data['notify_name'], $data['notify_country'], $data['notify_state'], $data['notify_city'], $data['notify_address'], $data['notify_ref_address'],
            $data['notify_phone_1'], $data['notify_phone_2'], $data['notify_phone_3'], $data['notify_email_1'], $data['notify_email_2'], $data['notify_email_3'],
            $data['notify_same'], $data['permanent'], $userId
        ]);
    }

    public static function create($userId, $data) {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            INSERT INTO consignees (
                user_id, consignee_name, consignee_country, consignee_state, consignee_city, consignee_address, consignee_ref_address,
                consignee_phone_1, consignee_phone_2, consignee_phone_3, consignee_email_1, consignee_email_2, consignee_email_3,
                notify_name, notify_country, notify_state, notify_city, notify_address, notify_ref_address,
                notify_phone_1, notify_phone_2, notify_phone_3, notify_email_1, notify_email_2, notify_email_3,
                notify_same, permanent
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        return $stmt->execute([
            $userId, $data['consignee_name'], $data['consignee_country'], $data['consignee_state'], $data['consignee_city'], $data['consignee_address'], $data['consignee_ref_address'],
            $data['consignee_phone_1'], $data['consignee_phone_2'], $data['consignee_phone_3'], $data['consignee_email_1'], $data['consignee_email_2'], $data['consignee_email_3'],
            $data['notify_name'], $data['notify_country'], $data['notify_state'], $data['notify_city'], $data['notify_address'], $data['notify_ref_address'],
            $data['notify_phone_1'], $data['notify_phone_2'], $data['notify_phone_3'], $data['notify_email_1'], $data['notify_email_2'], $data['notify_email_3'],
            $data['notify_same'], $data['permanent']
        ]);
    }
}
