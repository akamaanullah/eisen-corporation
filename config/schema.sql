-- ========================================================
-- Database Schema for Japanese Used Car Export Platform
-- Target RDBMS: MySQL 5.7+ / 8.0+
-- Project name: Eisen Corporation
-- ========================================================

-- 1. Create Database if needed
CREATE DATABASE IF NOT EXISTS `eisen_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `eisen_db`;

-- Set foreign key checks on
SET FOREIGN_KEY_CHECKS = 1;

-- ========================================================
-- TABLE: `users`
-- Roles: 'admin', 'finance_officer', 'caller_agent', 'registered_buyer', 'inventory_manager'
-- ========================================================
CREATE TABLE IF NOT EXISTS `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `first_name` VARCHAR(100) NULL,
    `last_name` VARCHAR(100) NULL,
    `email` VARCHAR(100) UNIQUE NOT NULL,
    `password` VARCHAR(255) NOT NULL,
    `reset_token` VARCHAR(255) NULL,
    `reset_token_expires` DATETIME NULL,
    `role` ENUM('admin', 'finance_officer', 'caller_agent', 'registered_buyer', 'inventory_manager') NOT NULL DEFAULT 'registered_buyer',
    `account_type` ENUM('Individual Buyer', 'Corporate Buyer', 'Inventory Manager') NOT NULL DEFAULT 'Individual Buyer',
    `phone` VARCHAR(25) NULL,
    `whatsapp` VARCHAR(25) NULL,
    `country` VARCHAR(50) NULL,
    `destination_port` VARCHAR(100) NULL,
    `address` VARCHAR(255) NULL,
    `address2` VARCHAR(255) NULL,
    `city` VARCHAR(100) NULL,
    `state` VARCHAR(100) NULL,
    `zip` VARCHAR(20) NULL,
    `company_name` VARCHAR(100) NULL,
    `preferred_currency` ENUM('USD', 'JPY') NOT NULL DEFAULT 'USD',
    `asf_confirmed` TINYINT(1) NOT NULL DEFAULT 0, -- Anti-Social Forces compliance
    `newsletter_subscribed` TINYINT(1) NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_users_role` (`role`),
    INDEX `idx_users_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================================
-- TABLE: `vehicles`
-- Stores both manually managed in-stock imports and API lot feeds
-- ========================================================
CREATE TABLE IF NOT EXISTS `vehicles` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `stock_id` VARCHAR(30) UNIQUE NOT NULL, -- e.g. ST-2094, AUC-9824
    `chassis_number` VARCHAR(50) NOT NULL,
    `type` ENUM('In-Stock', 'Auction') NOT NULL,
    `auction_house` VARCHAR(100) NOT NULL DEFAULT '',
    `lot_number` VARCHAR(50) NOT NULL DEFAULT '',
    `make` VARCHAR(50) NOT NULL,
    `model` VARCHAR(50) NOT NULL,
    `year` INT NOT NULL,
    `grade` VARCHAR(15) NOT NULL, -- e.g. 4.5, 5.0, R (auction inspection grade)
    `car_grade` VARCHAR(30) NOT NULL DEFAULT '', -- e.g. S, X, G (trim / model grade)
    `mileage_km` INT NOT NULL,
    `engine_cc` INT NOT NULL,
    `transmission` ENUM('AT', 'MT') NOT NULL DEFAULT 'AT',
    `steering` ENUM('RHD', 'LHD') NOT NULL DEFAULT 'RHD',
    `fuel` ENUM('PETROL', 'DIESEL', 'HYBRID', 'ELECTRIC') NOT NULL DEFAULT 'PETROL',
    `doors` INT NOT NULL DEFAULT 5,
    `seats` INT NOT NULL DEFAULT 5,
    `location` VARCHAR(100) NOT NULL DEFAULT 'KOBE, JAPAN',
    `color` VARCHAR(30) NOT NULL DEFAULT 'White',
    `body_type` VARCHAR(50) NOT NULL,
    `drive_type` VARCHAR(30) NOT NULL, -- e.g. 2WD, 4WD, RHD, LHD
    `fob_price` DECIMAL(12, 2) NOT NULL,
    `price_jpy` DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
    `freight_price` DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
    `vanning_price` DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
    `inspection_price` DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
    `insurance_price` DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
    `cf_price` DECIMAL(12, 2) NULL,
    `dimension` VARCHAR(50) NOT NULL DEFAULT '0.00m × 0.00m × 0.00m',
    `m3` VARCHAR(20) NOT NULL DEFAULT '10.167',
    `description` TEXT NULL,
    `views` INT NOT NULL DEFAULT 0,
    `damage_report_url` VARCHAR(255) NULL, -- Path to inspection check-sheet PDF
    `status` ENUM('Available', 'Reserved', 'Reservation Expired', 'Payment Pending', 'Payment Received', 'Shipping In Progress', 'Delivered', 'Sold', 'Archived') NOT NULL DEFAULT 'Available',
    `featured` TINYINT(1) NOT NULL DEFAULT 0, -- Show on hero homepage list
    `arrival_date` DATE NULL, -- For in-transit stock
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_vehicles_type` (`type`),
    INDEX `idx_vehicles_status` (`status`),
    INDEX `idx_vehicles_make_model` (`make`, `model`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================================
-- TABLE: `vehicle_images`
-- Stores multiple photo urls per vehicle, with user-sorted order indexes
-- ========================================================
CREATE TABLE IF NOT EXISTS `vehicle_images` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `vehicle_id` INT NOT NULL,
    `image_url` VARCHAR(255) NOT NULL,
    `sort_order` INT NOT NULL DEFAULT 0, -- Drag and Drop reordering index
    CONSTRAINT `fk_images_vehicle` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`id`) ON DELETE CASCADE,
    INDEX `idx_images_vehicle_sort` (`vehicle_id`, `sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================================
-- TABLE: `options`
-- Global definition catalog of options checklist items
-- ========================================================
CREATE TABLE IF NOT EXISTS `options` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `category` ENUM('Comfort & Convenience', 'Dress Up', 'Exterior', 'Safety', 'Other') NOT NULL,
    `label` VARCHAR(100) UNIQUE NOT NULL,
    INDEX `idx_options_category` (`category`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================================
-- TABLE: `vehicle_options`
-- Junction table mapping checked options to specific vehicles
-- ========================================================
CREATE TABLE IF NOT EXISTS `vehicle_options` (
    `vehicle_id` INT NOT NULL,
    `option_id` INT NOT NULL,
    PRIMARY KEY (`vehicle_id`, `option_id`),
    CONSTRAINT `fk_vopts_vehicle` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_vopts_option` FOREIGN KEY (`option_id`) REFERENCES `options` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================================
-- TABLE: `vehicle_reviews`
-- Tracks star ratings and text comments left by buyers on vehicles
-- ========================================================
CREATE TABLE IF NOT EXISTS `vehicle_reviews` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `vehicle_id` INT NOT NULL,
    `user_id` INT NOT NULL,
    `rating` TINYINT NOT NULL,
    `comment` TEXT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_reviews_vehicle` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_reviews_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================================
-- TABLE: `vehicle_favorites`
-- Junction table mapping users to favorited vehicles
-- ========================================================
CREATE TABLE IF NOT EXISTS `vehicle_favorites` (
    `user_id` INT NOT NULL,
    `vehicle_id` INT NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`user_id`, `vehicle_id`),
    CONSTRAINT `fk_favorites_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_favorites_vehicle` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================================
-- TABLE: `security_deposits`
-- Holds wire transaction slips validating customer bidding limits
-- ========================================================
CREATE TABLE IF NOT EXISTS `security_deposits` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `amount` DECIMAL(10, 2) NOT NULL,
    `requested_limit` DECIMAL(12, 2) NOT NULL,
    `slip_url` VARCHAR(255) NOT NULL,
    `status` ENUM('Pending Verification', 'Approved', 'Rejected') NOT NULL DEFAULT 'Pending Verification',
    `rejection_reason` TEXT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_deposits_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    INDEX `idx_deposits_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================================
-- TABLE: `auction_bids`
-- Records user bid requests on live auction lots
-- ========================================================
CREATE TABLE IF NOT EXISTS `auction_bids` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `vehicle_id` INT NOT NULL,
    `max_bid_amount` DECIMAL(12, 2) NOT NULL,
    `status` ENUM('Pending Confirmation', 'Bid Confirmed', 'Won', 'Lost') NOT NULL DEFAULT 'Pending Confirmation',
    `placed_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_bids_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_bids_vehicle` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`id`) ON DELETE CASCADE,
    INDEX `idx_bids_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================================
-- TABLE: `reservations`
-- Manages 24-Hour customer vehicle locks
-- ========================================================
CREATE TABLE IF NOT EXISTS `reservations` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `vehicle_id` INT NOT NULL,
    `agent_id` INT NULL, -- Assigned caller agent
    `expires_at` DATETIME NOT NULL, -- 24hr deadline
    `status` ENUM('Pending Call', 'Contacted - Awaiting Wire Deposit', 'Deposit Received - Booking Locked', 'Released') NOT NULL DEFAULT 'Pending Call',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_res_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_res_vehicle` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_res_agent` FOREIGN KEY (`agent_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
    INDEX `idx_res_status` (`status`),
    INDEX `idx_res_expires` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================================
-- TABLE: `call_logs`
-- Tracks conversation notes registered by calling agents
-- ========================================================
CREATE TABLE IF NOT EXISTS `call_logs` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `reservation_id` INT NOT NULL,
    `agent_id` INT NOT NULL,
    `outcome` ENUM('Reached', 'Not Reached', 'Scheduled Callback') NOT NULL,
    `notes` TEXT NOT NULL,
    `next_action` TEXT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_calls_res` FOREIGN KEY (`reservation_id`) REFERENCES `reservations` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_calls_agent` FOREIGN KEY (`agent_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================================
-- TABLE: `shipments`
-- Tracks transit milestones of purchased cargo
-- ========================================================
CREATE TABLE IF NOT EXISTS `shipments` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `vehicle_id` INT NOT NULL,
    `bl_number` VARCHAR(50) NOT NULL, -- Bill of Lading tracker
    `vessel_name` VARCHAR(100) NOT NULL,
    `etd` DATE NOT NULL, -- Estimated departure date
    `eta` DATE NOT NULL, -- Estimated arrival date
    `status` ENUM('Preparing', 'Dispatched', 'At Port', 'Delivered') NOT NULL DEFAULT 'Preparing',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_ships_vehicle` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`id`) ON DELETE CASCADE,
    INDEX `idx_ships_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================================
-- TABLE: `payments`
-- Records ledger audit of incoming bank wires and balance sheets
-- ========================================================
CREATE TABLE IF NOT EXISTS `payments` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `vehicle_id` INT NOT NULL,
    `payment_type` ENUM('Full Car Payment', 'Auction Deposit', 'Auction Balance', 'Refund') NOT NULL,
    `amount` DECIMAL(12, 2) NOT NULL,
    `currency` ENUM('USD', 'JPY') NOT NULL DEFAULT 'USD',
    `payment_method` VARCHAR(50) NOT NULL DEFAULT 'Bank Wire',
    `bank_reference` VARCHAR(100) NOT NULL, -- Bank confirmation reference number
    `status` ENUM('Pending', 'Confirmed', 'Refunded') NOT NULL DEFAULT 'Pending',
    `invoice_url` VARCHAR(255) NULL, -- PDF download
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_pays_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_pays_vehicle` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`id`) ON DELETE CASCADE,
    INDEX `idx_pays_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================================
-- TABLE: `consignees`
-- Buyer consignee and notify-party details for export documents
-- ========================================================
CREATE TABLE IF NOT EXISTS `consignees` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `consignee_name` VARCHAR(150) NOT NULL DEFAULT '',
    `consignee_country` VARCHAR(100) NOT NULL DEFAULT '',
    `consignee_state` VARCHAR(100) NOT NULL DEFAULT '',
    `consignee_city` VARCHAR(100) NOT NULL DEFAULT '',
    `consignee_address` VARCHAR(255) NOT NULL DEFAULT '',
    `consignee_ref_address` VARCHAR(255) NOT NULL DEFAULT '',
    `consignee_phone_1` VARCHAR(30) NOT NULL DEFAULT '',
    `consignee_phone_2` VARCHAR(30) NOT NULL DEFAULT '',
    `consignee_phone_3` VARCHAR(30) NOT NULL DEFAULT '',
    `consignee_email_1` VARCHAR(150) NOT NULL DEFAULT '',
    `consignee_email_2` VARCHAR(150) NOT NULL DEFAULT '',
    `consignee_email_3` VARCHAR(150) NOT NULL DEFAULT '',
    `notify_name` VARCHAR(150) NOT NULL DEFAULT '',
    `notify_country` VARCHAR(100) NOT NULL DEFAULT '',
    `notify_state` VARCHAR(100) NOT NULL DEFAULT '',
    `notify_city` VARCHAR(100) NOT NULL DEFAULT '',
    `notify_address` VARCHAR(255) NOT NULL DEFAULT '',
    `notify_ref_address` VARCHAR(255) NOT NULL DEFAULT '',
    `notify_phone_1` VARCHAR(30) NOT NULL DEFAULT '',
    `notify_phone_2` VARCHAR(30) NOT NULL DEFAULT '',
    `notify_phone_3` VARCHAR(30) NOT NULL DEFAULT '',
    `notify_email_1` VARCHAR(150) NOT NULL DEFAULT '',
    `notify_email_2` VARCHAR(150) NOT NULL DEFAULT '',
    `notify_email_3` VARCHAR(150) NOT NULL DEFAULT '',
    `notify_same` TINYINT(1) NOT NULL DEFAULT 0,
    `permanent` TINYINT(1) NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uk_consignees_user` (`user_id`),
    CONSTRAINT `fk_consignees_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================================
-- DATABASE SEEDING: Default Options Definition Catalog
-- Populate all checklist tags categorized for UI displays
-- ========================================================

-- A. Category: Comfort & Convenience
INSERT INTO `options` (`category`, `label`) VALUES
('Comfort & Convenience', 'Air Conditioner'),
('Comfort & Convenience', 'Audio Player'),
('Comfort & Convenience', 'Navigation System'),
('Comfort & Convenience', 'Power Steering'),
('Comfort & Convenience', 'Power Window All'),
('Comfort & Convenience', 'Push Start'),
('Comfort & Convenience', 'Steering Switch'),
('Comfort & Convenience', 'ACC'),
('Comfort & Convenience', 'Both Power Slide Door'),
('Comfort & Convenience', 'Cooler'),
('Comfort & Convenience', 'Cruise Control'),
('Comfort & Convenience', 'Double Air Conditioner'),
('Comfort & Convenience', 'Left Side Power Slide Door'),
('Comfort & Convenience', 'Paddle Shift'),
('Comfort & Convenience', 'Power Back Door'),
('Comfort & Convenience', 'Power Seat'),
('Comfort & Convenience', 'Power Window Driver'),
('Comfort & Convenience', 'Power Window Front');

-- B. Category: Dress Up
INSERT INTO `options` (`category`, `label`) VALUES
('Dress Up', 'Alloy Wheels'),
('Dress Up', 'Aero Front'),
('Dress Up', 'Aero Rear'),
('Dress Up', 'Aero Side'),
('Dress Up', 'Grill Guard'),
('Dress Up', 'Rear Spoiler'),
('Dress Up', 'Wood Combination Steering');

-- C. Category: Exterior
INSERT INTO `options` (`category`, `label`) VALUES
('Exterior', 'LED Light'),
('Exterior', 'Carrier Base'),
('Exterior', 'Roof Box'),
('Exterior', 'Roof Carrier'),
('Exterior', 'Roof Rails'),
('Exterior', 'Sun Roof'),
('Exterior', 'Double Sun Roof'),
('Exterior', 'Glass Roof'),
('Exterior', 'Fog Light'),
('Exterior', 'HID Light'),
('Exterior', 'High Roof'),
('Exterior', 'New Shape'),
('Exterior', 'Slide Glass');

-- D. Category: Safety
INSERT INTO `options` (`category`, `label`) VALUES
('Safety', 'Air Bag'),
('Safety', 'Back Camera'),
('Safety', 'ABS'),
('Safety', 'Around View Monitor'),
('Safety', 'Front Camera'),
('Safety', 'Side Camera'),
('Safety', 'Corner Sensor'),
('Safety', 'ESC');

-- E. Category: Other
INSERT INTO `options` (`category`, `label`) VALUES
('Other', 'Auto Door'),
('Other', 'Back Tire'),
('Other', 'Just Low'),
('Other', 'Half Leather Seat'),
('Other', 'Leather Seat'),
('Other', 'Same Tire'),
('Other', 'Side Lift Up Seat'),
('Other', 'Sloper');
