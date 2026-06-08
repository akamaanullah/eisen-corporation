<?php

return function (PDO $db): void {
    $db->exec("
        ALTER TABLE `vehicles`
        MODIFY COLUMN `status` ENUM(
            'Available',
            'Reserved',
            'Reservation Expired',
            'Payment Pending',
            'Payment Received',
            'Shipping In Progress',
            'Delivered',
            'Sold',
            'Archived'
        ) NOT NULL DEFAULT 'Available'
    ");
};
