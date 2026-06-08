<?php
// config/seed_vehicles.php

// Register PSR-4 Autoloader
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $base_dir = dirname(__DIR__) . '/app/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return; 
    }

    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});

// Load Configuration
require_once dirname(__DIR__) . '/config/config.php';

use App\Core\Database;

try {
    $db = Database::getConnection();
    
    // Temporarily disable foreign keys to safely truncate
    $db->exec("SET FOREIGN_KEY_CHECKS = 0");
    $db->exec("TRUNCATE TABLE vehicle_options");
    $db->exec("TRUNCATE TABLE vehicle_images");
    $db->exec("TRUNCATE TABLE vehicles");
    $db->exec("SET FOREIGN_KEY_CHECKS = 1");
    
    $cars = [
        [
            'stock_id' => 'ST-2094',
            'type' => 'In-Stock',
            'make' => 'Toyota',
            'model' => 'Prius',
            'year' => 2022,
            'chassis_number' => 'ZVW50-5192842',
            'grade' => '4.5',
            'mileage_km' => 28400,
            'engine_cc' => 1800,
            'transmission' => 'AT',
            'steering' => 'RHD',
            'fuel' => 'HYBRID',
            'doors' => 5,
            'seats' => 5,
            'location' => 'KOBE, JAPAN',
            'color' => 'Pearl White',
            'body_type' => 'Hatchback',
            'drive_type' => '2WD',
            'fob_price' => 18500.00,
            'freight_price' => 1200.00,
            'vanning_price' => 200.00,
            'inspection_price' => 450.00,
            'insurance_price' => 50.00,
            'cf_price' => 20400.00,
            'status' => 'Available',
            'featured' => 1,
            'arrival_date' => null,
            'images' => [
                'photo-1549317661-bd32c8ce0db2',
                'photo-1580273916550-e323be2ae537',
                'photo-1606664515524-ed2f786a0bd6'
            ],
            'options' => ['Air Conditioner', 'Navigation System', 'LED Light', 'Back Camera', 'Push Start', 'Alloy Wheels']
        ],
        [
            'stock_id' => 'ST-2095',
            'type' => 'In-Stock',
            'make' => 'Toyota',
            'model' => 'Aqua',
            'year' => 2021,
            'chassis_number' => 'NHP10-2394851',
            'grade' => '4.0',
            'mileage_km' => 34500,
            'engine_cc' => 1500,
            'transmission' => 'AT',
            'steering' => 'RHD',
            'fuel' => 'HYBRID',
            'doors' => 5,
            'seats' => 5,
            'location' => 'YOKOHAMA, JAPAN',
            'color' => 'Blue Metallic',
            'body_type' => 'Hatchback',
            'drive_type' => '2WD',
            'fob_price' => 12400.00,
            'freight_price' => 1100.00,
            'vanning_price' => 200.00,
            'inspection_price' => 450.00,
            'insurance_price' => 50.00,
            'cf_price' => 14200.00,
            'status' => 'Available',
            'featured' => 1,
            'arrival_date' => null,
            'images' => [
                'photo-1580273916550-e323be2ae537',
                'photo-1502877338535-766e1452684a'
            ],
            'options' => ['Air Conditioner', 'Navigation System', 'Power Steering', 'Air Bag', 'ABS']
        ],
        [
            'stock_id' => 'ST-2096',
            'type' => 'In-Stock',
            'make' => 'Honda',
            'model' => 'Vezel',
            'year' => 2023,
            'chassis_number' => 'RU3-1204928',
            'grade' => '5.0',
            'mileage_km' => 12200,
            'engine_cc' => 1500,
            'transmission' => 'AT',
            'steering' => 'RHD',
            'fuel' => 'HYBRID',
            'doors' => 5,
            'seats' => 5,
            'location' => 'KOBE, JAPAN',
            'color' => 'Crystal Black',
            'body_type' => 'SUV',
            'drive_type' => '2WD',
            'fob_price' => 23800.00,
            'freight_price' => 1250.00,
            'vanning_price' => 200.00,
            'inspection_price' => 450.00,
            'insurance_price' => 70.00,
            'cf_price' => 25770.00,
            'status' => 'Reserved',
            'featured' => 1,
            'arrival_date' => '2026-06-15',
            'images' => [
                'photo-1606664515524-ed2f786a0bd6',
                'photo-1605559424843-9e4c228bf1c2',
                'photo-1533473359331-0135ef1b58bf'
            ],
            'options' => ['Air Conditioner', 'Alloy Wheels', 'LED Light', 'Back Camera', 'Push Start', 'Cruise Control']
        ],
        [
            'stock_id' => 'ST-2097',
            'type' => 'In-Stock',
            'make' => 'Nissan',
            'model' => 'Note',
            'year' => 2021,
            'chassis_number' => 'HE12-8392019',
            'grade' => '4.5',
            'mileage_km' => 24100,
            'engine_cc' => 1200,
            'transmission' => 'AT',
            'steering' => 'RHD',
            'fuel' => 'HYBRID',
            'doors' => 5,
            'seats' => 5,
            'location' => 'NAGOYA, JAPAN',
            'color' => 'Sonic Orange',
            'body_type' => 'Hatchback',
            'drive_type' => '2WD',
            'fob_price' => 14900.00,
            'freight_price' => 1150.00,
            'vanning_price' => 200.00,
            'inspection_price' => 450.00,
            'insurance_price' => 50.00,
            'cf_price' => 16750.00,
            'status' => 'Available',
            'featured' => 1,
            'arrival_date' => null,
            'images' => [
                'photo-1502877338535-766e1452684a',
                'photo-1549317661-bd32c8ce0db2'
            ],
            'options' => ['Air Conditioner', 'Navigation System', 'Push Start', 'Back Camera', 'Air Bag']
        ],
        [
            'stock_id' => 'ST-2098',
            'type' => 'In-Stock',
            'make' => 'Nissan',
            'model' => 'Leaf',
            'year' => 2022,
            'chassis_number' => 'ZE1-0492812',
            'grade' => '4.5',
            'mileage_km' => 16800,
            'engine_cc' => 0,
            'transmission' => 'AT',
            'steering' => 'RHD',
            'fuel' => 'ELECTRIC',
            'doors' => 5,
            'seats' => 5,
            'location' => 'YOKOHAMA, JAPAN',
            'color' => 'Silver Metallic',
            'body_type' => 'Hatchback',
            'drive_type' => '2WD',
            'fob_price' => 19800.00,
            'freight_price' => 1200.00,
            'vanning_price' => 300.00,
            'inspection_price' => 450.00,
            'insurance_price' => 60.00,
            'cf_price' => 21810.00,
            'status' => 'Available',
            'featured' => 0,
            'arrival_date' => null,
            'images' => [
                'photo-1549317661-bd32c8ce0db2',
                'photo-1580273916550-e323be2ae537'
            ],
            'options' => ['Air Conditioner', 'Navigation System', 'LED Light', 'Back Camera', 'Push Start']
        ],
        [
            'stock_id' => 'ST-2099',
            'type' => 'In-Stock',
            'make' => 'Suzuki',
            'model' => 'Swift',
            'year' => 2020,
            'chassis_number' => 'ZC83S-4829104',
            'grade' => '4.0',
            'mileage_km' => 42100,
            'engine_cc' => 1200,
            'transmission' => 'AT',
            'steering' => 'RHD',
            'fuel' => 'PETROL',
            'doors' => 5,
            'seats' => 5,
            'location' => 'KOBE, JAPAN',
            'color' => 'Burning Red',
            'body_type' => 'Hatchback',
            'drive_type' => '2WD',
            'fob_price' => 8900.00,
            'freight_price' => 1100.00,
            'vanning_price' => 200.00,
            'inspection_price' => 450.00,
            'insurance_price' => 40.00,
            'cf_price' => 10690.00,
            'status' => 'Available',
            'featured' => 0,
            'arrival_date' => null,
            'images' => [
                'photo-1502877338535-766e1452684a',
                'photo-1542282088-72c9c27ed0cd'
            ],
            'options' => ['Air Conditioner', 'Alloy Wheels', 'Power Steering', 'Air Bag', 'ABS']
        ],
        [
            'stock_id' => 'ST-2100',
            'type' => 'In-Stock',
            'make' => 'Toyota',
            'model' => 'Land Cruiser',
            'year' => 2021,
            'chassis_number' => 'URJ202-0948271',
            'grade' => '4.5',
            'mileage_km' => 38900,
            'engine_cc' => 4600,
            'transmission' => 'AT',
            'steering' => 'RHD',
            'fuel' => 'PETROL',
            'doors' => 5,
            'seats' => 8,
            'location' => 'KOBE, JAPAN',
            'color' => 'Attitude Black',
            'body_type' => 'SUV',
            'drive_type' => '4WD',
            'fob_price' => 54500.00,
            'freight_price' => 1600.00,
            'vanning_price' => 400.00,
            'inspection_price' => 500.00,
            'insurance_price' => 120.00,
            'cf_price' => 57120.00,
            'status' => 'Available',
            'featured' => 1,
            'arrival_date' => null,
            'images' => [
                'photo-1533473359331-0135ef1b58bf',
                'photo-1605559424843-9e4c228bf1c2',
                'photo-1606664515524-ed2f786a0bd6'
            ],
            'options' => ['Leather Seat', 'Air Conditioner', 'Navigation System', 'Alloy Wheels', 'LED Light', 'Sun Roof', 'Back Camera', 'Push Start', 'Cruise Control']
        ],
        [
            'stock_id' => 'ST-2101',
            'type' => 'In-Stock',
            'make' => 'Honda',
            'model' => 'Fit',
            'year' => 2021,
            'chassis_number' => 'GP5-3928104',
            'grade' => '4.0',
            'mileage_km' => 28700,
            'engine_cc' => 1500,
            'transmission' => 'AT',
            'steering' => 'RHD',
            'fuel' => 'HYBRID',
            'doors' => 5,
            'seats' => 5,
            'location' => 'YOKOHAMA, JAPAN',
            'color' => 'Orchid White',
            'body_type' => 'Hatchback',
            'drive_type' => '2WD',
            'fob_price' => 11200.00,
            'freight_price' => 1100.00,
            'vanning_price' => 200.00,
            'inspection_price' => 450.00,
            'insurance_price' => 50.00,
            'cf_price' => 13000.00,
            'status' => 'Available',
            'featured' => 0,
            'arrival_date' => null,
            'images' => [
                'photo-1580273916550-e323be2ae537',
                'photo-1549317661-bd32c8ce0db2'
            ],
            'options' => ['Air Conditioner', 'Navigation System', 'Push Start', 'Back Camera', 'Air Bag', 'ABS']
        ],
        [
            'stock_id' => 'ST-2102',
            'type' => 'In-Stock',
            'make' => 'Mazda',
            'model' => 'CX-5',
            'year' => 2022,
            'chassis_number' => 'KF2P-3029104',
            'grade' => '4.5',
            'mileage_km' => 23400,
            'engine_cc' => 2200,
            'transmission' => 'AT',
            'steering' => 'RHD',
            'fuel' => 'DIESEL',
            'doors' => 5,
            'seats' => 5,
            'location' => 'KOBE, JAPAN',
            'color' => 'Soul Red Crystal',
            'body_type' => 'SUV',
            'drive_type' => '2WD',
            'fob_price' => 21900.00,
            'freight_price' => 1300.00,
            'vanning_price' => 200.00,
            'inspection_price' => 450.00,
            'insurance_price' => 60.00,
            'cf_price' => 23910.00,
            'status' => 'Available',
            'featured' => 0,
            'arrival_date' => null,
            'images' => [
                'photo-1605559424843-9e4c228bf1c2',
                'photo-1606664515524-ed2f786a0bd6'
            ],
            'options' => ['Air Conditioner', 'Alloy Wheels', 'LED Light', 'Back Camera', 'Push Start', 'Cruise Control', 'ESC']
        ],
        [
            'stock_id' => 'ST-2103',
            'type' => 'In-Stock',
            'make' => 'Subaru',
            'model' => 'Forester',
            'year' => 2021,
            'chassis_number' => 'SJ5-0849201',
            'grade' => '4.5',
            'mileage_km' => 31200,
            'engine_cc' => 2000,
            'transmission' => 'AT',
            'steering' => 'RHD',
            'fuel' => 'PETROL',
            'doors' => 5,
            'seats' => 5,
            'location' => 'OSAKA, JAPAN',
            'color' => 'Bronze Metallic',
            'body_type' => 'SUV',
            'drive_type' => '4WD',
            'fob_price' => 19500.00,
            'freight_price' => 1350.00,
            'vanning_price' => 200.00,
            'inspection_price' => 450.00,
            'insurance_price' => 60.00,
            'cf_price' => 21560.00,
            'status' => 'Available',
            'featured' => 0,
            'arrival_date' => null,
            'images' => [
                'photo-1533473359331-0135ef1b58bf',
                'photo-1605559424843-9e4c228bf1c2'
            ],
            'options' => ['Air Conditioner', 'Alloy Wheels', 'LED Light', 'Back Camera', 'Push Start', 'ABS', 'ESC']
        ],
        [
            'stock_id' => 'ST-2104',
            'type' => 'In-Stock',
            'make' => 'Toyota',
            'model' => 'Corolla',
            'year' => 2022,
            'chassis_number' => 'NKE165-492810',
            'grade' => '4.5',
            'mileage_km' => 25800,
            'engine_cc' => 1500,
            'transmission' => 'AT',
            'steering' => 'RHD',
            'fuel' => 'HYBRID',
            'doors' => 5,
            'seats' => 5,
            'location' => 'KOBE, JAPAN',
            'color' => 'Super White',
            'body_type' => 'Wagon',
            'drive_type' => '2WD',
            'fob_price' => 13500.00,
            'freight_price' => 1200.00,
            'vanning_price' => 200.00,
            'inspection_price' => 450.00,
            'insurance_price' => 50.00,
            'cf_price' => 15400.00,
            'status' => 'Available',
            'featured' => 1,
            'arrival_date' => null,
            'images' => [
                'photo-1549317661-bd32c8ce0db2',
                'photo-1580273916550-e323be2ae537'
            ],
            'options' => ['Air Conditioner', 'Navigation System', 'Power Steering', 'Air Bag', 'ABS']
        ],
        [
            'stock_id' => 'ST-2105',
            'type' => 'In-Stock',
            'make' => 'Toyota',
            'model' => 'Alphard',
            'year' => 2023,
            'chassis_number' => 'AGH30-0194821',
            'grade' => '5.0',
            'mileage_km' => 8400,
            'engine_cc' => 2500,
            'transmission' => 'AT',
            'steering' => 'RHD',
            'fuel' => 'PETROL',
            'doors' => 5,
            'seats' => 7,
            'location' => 'YOKOHAMA, JAPAN',
            'color' => 'Pearl Black',
            'body_type' => 'Van',
            'drive_type' => '2WD',
            'fob_price' => 48500.00,
            'freight_price' => 1650.00,
            'vanning_price' => 400.00,
            'inspection_price' => 500.00,
            'insurance_price' => 100.00,
            'cf_price' => 51150.00,
            'status' => 'Available',
            'featured' => 1,
            'arrival_date' => null,
            'images' => [
                'photo-1555215695-3004980ad54e',
                'photo-1533473359331-0135ef1b58bf'
            ],
            'options' => ['Leather Seat', 'Air Conditioner', 'Navigation System', 'Alloy Wheels', 'LED Light', 'Sun Roof', 'Double Sun Roof', 'Back Camera', 'Push Start', 'Cruise Control', 'Both Power Slide Door']
        ],
        [
            'stock_id' => 'ST-2106',
            'type' => 'In-Stock',
            'make' => 'Toyota',
            'model' => 'Hiace',
            'year' => 2021,
            'chassis_number' => 'KDH201-9402910',
            'grade' => '4.0',
            'mileage_km' => 51000,
            'engine_cc' => 3000,
            'transmission' => 'AT',
            'steering' => 'RHD',
            'fuel' => 'DIESEL',
            'doors' => 5,
            'seats' => 6,
            'location' => 'KOBE, JAPAN',
            'color' => 'White',
            'body_type' => 'Van',
            'drive_type' => '2WD',
            'fob_price' => 19500.00,
            'freight_price' => 1500.00,
            'vanning_price' => 300.00,
            'inspection_price' => 450.00,
            'insurance_price' => 70.00,
            'cf_price' => 21820.00,
            'status' => 'Available',
            'featured' => 0,
            'arrival_date' => null,
            'images' => [
                'photo-1605559424843-9e4c228bf1c2',
                'photo-1533473359331-0135ef1b58bf'
            ],
            'options' => ['Air Conditioner', 'Power Steering', 'Power Window All', 'Air Bag', 'ABS']
        ],
        [
            'stock_id' => 'ST-2107',
            'type' => 'In-Stock',
            'make' => 'Suzuki',
            'model' => 'Jimny',
            'year' => 2022,
            'chassis_number' => 'JB64W-1940291',
            'grade' => '4.5',
            'mileage_km' => 15600,
            'engine_cc' => 660,
            'transmission' => 'MT',
            'steering' => 'RHD',
            'fuel' => 'PETROL',
            'doors' => 3,
            'seats' => 4,
            'location' => 'KOBE, JAPAN',
            'color' => 'Jungle Green',
            'body_type' => 'SUV',
            'drive_type' => '4WD',
            'fob_price' => 16800.00,
            'freight_price' => 1250.00,
            'vanning_price' => 200.00,
            'inspection_price' => 450.00,
            'insurance_price' => 50.00,
            'cf_price' => 18750.00,
            'status' => 'Available',
            'featured' => 0,
            'arrival_date' => null,
            'images' => [
                'photo-1533473359331-0135ef1b58bf',
                'photo-1580273916550-e323be2ae537'
            ],
            'options' => ['Air Conditioner', 'Alloy Wheels', 'Push Start', 'Air Bag', 'ABS', 'ESC']
        ],
        [
            'stock_id' => 'ST-2108',
            'type' => 'In-Stock',
            'make' => 'Honda',
            'model' => 'Civic',
            'year' => 2022,
            'chassis_number' => 'FK8-0492019',
            'grade' => '4.5',
            'mileage_km' => 18900,
            'engine_cc' => 2000,
            'transmission' => 'MT',
            'steering' => 'RHD',
            'fuel' => 'PETROL',
            'doors' => 5,
            'seats' => 5,
            'location' => 'YOKOHAMA, JAPAN',
            'color' => 'Championship White',
            'body_type' => 'Hatchback',
            'drive_type' => '2WD',
            'fob_price' => 38500.00,
            'freight_price' => 1300.00,
            'vanning_price' => 200.00,
            'inspection_price' => 450.00,
            'insurance_price' => 80.00,
            'cf_price' => 40530.00,
            'status' => 'Available',
            'featured' => 1,
            'arrival_date' => null,
            'images' => [
                'photo-1542282088-72c9c27ed0cd',
                'photo-1502877338535-766e1452684a',
                'photo-1580273916550-e323be2ae537'
            ],
            'options' => ['Air Conditioner', 'Alloy Wheels', 'LED Light', 'Back Camera', 'Push Start', 'Rear Spoiler', 'Paddle Shift', 'ABS', 'ESC']
        ],
        [
            'stock_id' => 'ST-2109',
            'type' => 'In-Stock',
            'make' => 'BMW',
            'model' => '3 Series',
            'year' => 2021,
            'chassis_number' => 'WBA5A31009K28',
            'grade' => '4.5',
            'mileage_km' => 28400,
            'engine_cc' => 2000,
            'transmission' => 'AT',
            'steering' => 'RHD',
            'fuel' => 'DIESEL',
            'doors' => 4,
            'seats' => 5,
            'location' => 'KOBE, JAPAN',
            'color' => 'Portimao Blue',
            'body_type' => 'Sedan',
            'drive_type' => '2WD',
            'fob_price' => 28900.00,
            'freight_price' => 1400.00,
            'vanning_price' => 200.00,
            'inspection_price' => 450.00,
            'insurance_price' => 80.00,
            'cf_price' => 31030.00,
            'status' => 'Available',
            'featured' => 0,
            'arrival_date' => null,
            'images' => [
                'photo-1555215695-3004980ad54e',
                'photo-1618843479313-40f8afb4b4d8'
            ],
            'options' => ['Leather Seat', 'Air Conditioner', 'Navigation System', 'Alloy Wheels', 'LED Light', 'Back Camera', 'Push Start', 'Cruise Control', 'Corner Sensor']
        ],
        [
            'stock_id' => 'ST-2110',
            'type' => 'In-Stock',
            'make' => 'Mercedes-Benz',
            'model' => 'C-Class',
            'year' => 2021,
            'chassis_number' => 'WDD20500412B',
            'grade' => '4.0',
            'mileage_km' => 32400,
            'engine_cc' => 1500,
            'transmission' => 'AT',
            'steering' => 'RHD',
            'fuel' => 'PETROL',
            'doors' => 4,
            'seats' => 5,
            'location' => 'OSAKA, JAPAN',
            'color' => 'Polar White',
            'body_type' => 'Sedan',
            'drive_type' => '2WD',
            'fob_price' => 31500.00,
            'freight_price' => 1400.00,
            'vanning_price' => 200.00,
            'inspection_price' => 450.00,
            'insurance_price' => 80.00,
            'cf_price' => 33630.00,
            'status' => 'Available',
            'featured' => 0,
            'arrival_date' => null,
            'images' => [
                'photo-1492144534655-ae79c964c9d7',
                'photo-1618843479313-40f8afb4b4d8'
            ],
            'options' => ['Air Conditioner', 'Navigation System', 'Alloy Wheels', 'LED Light', 'Back Camera', 'Push Start', 'Cruise Control', 'Air Bag', 'ABS']
        ],
        [
            'stock_id' => 'ST-2111',
            'type' => 'In-Stock',
            'make' => 'Nissan',
            'model' => 'X-Trail',
            'year' => 2021,
            'chassis_number' => 'NT32-8492019',
            'grade' => '4.5',
            'mileage_km' => 31200,
            'engine_cc' => 2000,
            'transmission' => 'AT',
            'steering' => 'RHD',
            'fuel' => 'HYBRID',
            'doors' => 5,
            'seats' => 5,
            'location' => 'KOBE, JAPAN',
            'color' => 'Solid Red',
            'body_type' => 'SUV',
            'drive_type' => '4WD',
            'fob_price' => 17800.00,
            'freight_price' => 1300.00,
            'vanning_price' => 200.00,
            'inspection_price' => 450.00,
            'insurance_price' => 60.00,
            'cf_price' => 19810.00,
            'status' => 'Available',
            'featured' => 0,
            'arrival_date' => null,
            'images' => [
                'photo-1605559424843-9e4c228bf1c2',
                'photo-1533473359331-0135ef1b58bf'
            ],
            'options' => ['Air Conditioner', 'Alloy Wheels', 'LED Light', 'Back Camera', 'Push Start', 'ESC', 'ABS']
        ],
        [
            'stock_id' => 'ST-2112',
            'type' => 'In-Stock',
            'make' => 'Toyota',
            'model' => 'RAV4',
            'year' => 2022,
            'chassis_number' => 'MXAA54-039281',
            'grade' => '4.5',
            'mileage_km' => 21800,
            'engine_cc' => 2000,
            'transmission' => 'AT',
            'steering' => 'RHD',
            'fuel' => 'PETROL',
            'doors' => 5,
            'seats' => 5,
            'location' => 'YOKOHAMA, JAPAN',
            'color' => 'Urban Khaki',
            'body_type' => 'SUV',
            'drive_type' => '4WD',
            'fob_price' => 25800.00,
            'freight_price' => 1300.00,
            'vanning_price' => 200.00,
            'inspection_price' => 450.00,
            'insurance_price' => 70.00,
            'cf_price' => 27820.00,
            'status' => 'Available',
            'featured' => 1,
            'arrival_date' => null,
            'images' => [
                'photo-1605559424843-9e4c228bf1c2',
                'photo-1533473359331-0135ef1b58bf',
                'photo-1606664515524-ed2f786a0bd6'
            ],
            'options' => ['Air Conditioner', 'Alloy Wheels', 'LED Light', 'Back Camera', 'Push Start', 'ABS', 'ESC', 'ACC']
        ],
        [
            'stock_id' => 'ST-2113',
            'type' => 'In-Stock',
            'make' => 'Audi',
            'model' => 'A4',
            'year' => 2021,
            'chassis_number' => 'WA1BUB8W7L209',
            'grade' => '4.5',
            'mileage_km' => 29800,
            'engine_cc' => 2000,
            'transmission' => 'AT',
            'steering' => 'RHD',
            'fuel' => 'PETROL',
            'doors' => 5,
            'seats' => 5,
            'location' => 'KOBE, JAPAN',
            'color' => 'Ibis White',
            'body_type' => 'Wagon',
            'drive_type' => '2WD',
            'fob_price' => 26500.00,
            'freight_price' => 1350.00,
            'vanning_price' => 250.00,
            'inspection_price' => 450.00,
            'insurance_price' => 70.00,
            'cf_price' => 28620.00,
            'status' => 'Available',
            'featured' => 0,
            'arrival_date' => null,
            'images' => [
                'photo-1553440569-bcc63803a83d',
                'photo-1606664515524-ed2f786a0bd6'
            ],
            'options' => ['Air Conditioner', 'Navigation System', 'Alloy Wheels', 'LED Light', 'Back Camera', 'Push Start', 'Air Bag', 'ABS']
        ],
        [
            'stock_id' => 'ST-2114',
            'type' => 'In-Stock',
            'make' => 'Mazda',
            'model' => 'Demio',
            'year' => 2019,
            'chassis_number' => 'DJ5FS-4810291',
            'grade' => '4.0',
            'mileage_km' => 48200,
            'engine_cc' => 1500,
            'transmission' => 'AT',
            'steering' => 'RHD',
            'fuel' => 'PETROL',
            'doors' => 5,
            'seats' => 5,
            'location' => 'NAGOYA, JAPAN',
            'color' => 'Deep Crystal Blue',
            'body_type' => 'Hatchback',
            'drive_type' => '2WD',
            'fob_price' => 7900.00,
            'freight_price' => 1100.00,
            'vanning_price' => 200.00,
            'inspection_price' => 450.00,
            'insurance_price' => 40.00,
            'cf_price' => 9690.00,
            'status' => 'Available',
            'featured' => 0,
            'arrival_date' => null,
            'images' => [
                'photo-1549317661-bd32c8ce0db2',
                'photo-1502877338535-766e1452684a'
            ],
            'options' => ['Air Conditioner', 'Navigation System', 'Power Steering', 'Air Bag', 'ABS']
        ],
        [
            'stock_id' => 'ST-2115',
            'type' => 'In-Stock',
            'make' => 'BMW',
            'model' => 'X5',
            'year' => 2020,
            'chassis_number' => 'WBAKS21040L94',
            'grade' => '4.5',
            'mileage_km' => 39200,
            'engine_cc' => 3000,
            'transmission' => 'AT',
            'steering' => 'RHD',
            'fuel' => 'DIESEL',
            'doors' => 5,
            'seats' => 5,
            'location' => 'KOBE, JAPAN',
            'color' => 'Mineral White',
            'body_type' => 'SUV',
            'drive_type' => '4WD',
            'fob_price' => 42500.00,
            'freight_price' => 1500.00,
            'vanning_price' => 300.00,
            'inspection_price' => 450.00,
            'insurance_price' => 100.00,
            'cf_price' => 44850.00,
            'status' => 'Available',
            'featured' => 0,
            'arrival_date' => null,
            'images' => [
                'photo-1553440569-bcc63803a83d',
                'photo-1533473359331-0135ef1b58bf'
            ],
            'options' => ['Leather Seat', 'Air Conditioner', 'Navigation System', 'Alloy Wheels', 'LED Light', 'Sun Roof', 'Back Camera', 'Push Start', 'Cruise Control', 'ESC']
        ],
        [
            'stock_id' => 'ST-2116',
            'type' => 'In-Stock',
            'make' => 'Mercedes-Benz',
            'model' => 'E-Class',
            'year' => 2022,
            'chassis_number' => 'WDD21300412C',
            'grade' => '5.0',
            'mileage_km' => 11400,
            'engine_cc' => 2000,
            'transmission' => 'AT',
            'steering' => 'RHD',
            'fuel' => 'PETROL',
            'doors' => 4,
            'seats' => 5,
            'location' => 'KOBE, JAPAN',
            'color' => 'Selenite Grey',
            'body_type' => 'Sedan',
            'drive_type' => '2WD',
            'fob_price' => 46900.00,
            'freight_price' => 1400.00,
            'vanning_price' => 200.00,
            'inspection_price' => 450.00,
            'insurance_price' => 90.00,
            'cf_price' => 49040.00,
            'status' => 'Available',
            'featured' => 1,
            'arrival_date' => null,
            'images' => [
                'photo-1618843479313-40f8afb4b4d8',
                'photo-1492144534655-ae79c964c9d7'
            ],
            'options' => ['Leather Seat', 'Air Conditioner', 'Navigation System', 'Alloy Wheels', 'LED Light', 'Back Camera', 'Push Start', 'Cruise Control', 'Corner Sensor', 'ESC']
        ],
        [
            'stock_id' => 'ST-2117',
            'type' => 'In-Stock',
            'make' => 'Audi',
            'model' => 'Q5',
            'year' => 2022,
            'chassis_number' => 'WA1BUAFY6N204',
            'grade' => '4.5',
            'mileage_km' => 19500,
            'engine_cc' => 2000,
            'transmission' => 'AT',
            'steering' => 'RHD',
            'fuel' => 'PETROL',
            'doors' => 5,
            'seats' => 5,
            'location' => 'YOKOHAMA, JAPAN',
            'color' => 'Mythos Black',
            'body_type' => 'SUV',
            'drive_type' => '4WD',
            'fob_price' => 36800.00,
            'freight_price' => 1400.00,
            'vanning_price' => 200.00,
            'inspection_price' => 450.00,
            'insurance_price' => 70.00,
            'cf_price' => 38920.00,
            'status' => 'Available',
            'featured' => 0,
            'arrival_date' => null,
            'images' => [
                'photo-1553440569-bcc63803a83d',
                'photo-1606664515524-ed2f786a0bd6'
            ],
            'options' => ['Air Conditioner', 'Alloy Wheels', 'LED Light', 'Back Camera', 'Push Start', 'Air Bag', 'ABS', 'ESC']
        ],
        [
            'stock_id' => 'ST-2118',
            'type' => 'In-Stock',
            'make' => 'Toyota',
            'model' => 'Camry',
            'year' => 2021,
            'chassis_number' => 'AXVH70-0284910',
            'grade' => '4.5',
            'mileage_km' => 27500,
            'engine_cc' => 2500,
            'transmission' => 'AT',
            'steering' => 'RHD',
            'fuel' => 'HYBRID',
            'doors' => 4,
            'seats' => 5,
            'location' => 'KOBE, JAPAN',
            'color' => 'Platinum White',
            'body_type' => 'Sedan',
            'drive_type' => '2WD',
            'fob_price' => 22800.00,
            'freight_price' => 1250.00,
            'vanning_price' => 200.00,
            'inspection_price' => 450.00,
            'insurance_price' => 60.00,
            'cf_price' => 24760.00,
            'status' => 'Available',
            'featured' => 0,
            'arrival_date' => null,
            'images' => [
                'photo-1580273916550-e323be2ae537',
                'photo-1549317661-bd32c8ce0db2'
            ],
            'options' => ['Air Conditioner', 'Navigation System', 'Alloy Wheels', 'LED Light', 'Back Camera', 'Push Start', 'Cruise Control', 'ABS']
        ],
        [
            'stock_id' => 'ST-2119',
            'type' => 'In-Stock',
            'make' => 'Honda',
            'model' => 'Accord',
            'year' => 2020,
            'chassis_number' => 'CR7-1049281',
            'grade' => '4.0',
            'mileage_km' => 38900,
            'engine_cc' => 2000,
            'transmission' => 'AT',
            'steering' => 'RHD',
            'fuel' => 'HYBRID',
            'doors' => 4,
            'seats' => 5,
            'location' => 'YOKOHAMA, JAPAN',
            'color' => 'Modern Steel Metallic',
            'body_type' => 'Sedan',
            'drive_type' => '2WD',
            'fob_price' => 18900.00,
            'freight_price' => 1250.00,
            'vanning_price' => 200.00,
            'inspection_price' => 450.00,
            'insurance_price' => 50.00,
            'cf_price' => 20850.00,
            'status' => 'Available',
            'featured' => 0,
            'arrival_date' => null,
            'images' => [
                'photo-1542282088-72c9c27ed0cd',
                'photo-1580273916550-e323be2ae537'
            ],
            'options' => ['Air Conditioner', 'Navigation System', 'Alloy Wheels', 'LED Light', 'Back Camera', 'Push Start', 'ABS', 'ESC']
        ],
        [
            'stock_id' => 'ST-2120',
            'type' => 'In-Stock',
            'make' => 'Nissan',
            'model' => 'Skyline',
            'year' => 2021,
            'chassis_number' => 'RV37-0194827',
            'grade' => '4.5',
            'mileage_km' => 19200,
            'engine_cc' => 3000,
            'transmission' => 'AT',
            'steering' => 'RHD',
            'fuel' => 'PETROL',
            'doors' => 4,
            'seats' => 5,
            'location' => 'KOBE, JAPAN',
            'color' => 'Slate Grey',
            'body_type' => 'Sedan',
            'drive_type' => '2WD',
            'fob_price' => 34500.00,
            'freight_price' => 1350.00,
            'vanning_price' => 200.00,
            'inspection_price' => 450.00,
            'insurance_price' => 80.00,
            'cf_price' => 36580.00,
            'status' => 'Available',
            'featured' => 1,
            'arrival_date' => null,
            'images' => [
                'photo-1503376780353-7e6692767b70',
                'photo-1553440569-bcc63803a83d',
                'photo-1618843479313-40f8afb4b4d8'
            ],
            'options' => ['Leather Seat', 'Air Conditioner', 'Navigation System', 'Alloy Wheels', 'LED Light', 'Back Camera', 'Push Start', 'Paddle Shift', 'Cruise Control', 'ESC']
        ],
        [
            'stock_id' => 'ST-2121',
            'type' => 'In-Stock',
            'make' => 'Subaru',
            'model' => 'Impreza',
            'year' => 2021,
            'chassis_number' => 'GT7-0294812',
            'grade' => '4.5',
            'mileage_km' => 25600,
            'engine_cc' => 2000,
            'transmission' => 'AT',
            'steering' => 'RHD',
            'fuel' => 'PETROL',
            'doors' => 5,
            'seats' => 5,
            'location' => 'KOBE, JAPAN',
            'color' => 'Quartz Blue',
            'body_type' => 'Hatchback',
            'drive_type' => '2WD',
            'fob_price' => 13800.00,
            'freight_price' => 1200.00,
            'vanning_price' => 200.00,
            'inspection_price' => 450.00,
            'insurance_price' => 50.00,
            'cf_price' => 15700.00,
            'status' => 'Available',
            'featured' => 0,
            'arrival_date' => null,
            'images' => [
                'photo-1580273916550-e323be2ae537',
                'photo-1549317661-bd32c8ce0db2'
            ],
            'options' => ['Air Conditioner', 'Navigation System', 'Alloy Wheels', 'LED Light', 'Back Camera', 'Push Start', 'Air Bag', 'ABS']
        ],
        [
            'stock_id' => 'ST-2122',
            'type' => 'In-Stock',
            'make' => 'Daihatsu',
            'model' => 'Mira',
            'year' => 2020,
            'chassis_number' => 'LA350S-3819281',
            'grade' => '4.0',
            'mileage_km' => 38200,
            'engine_cc' => 660,
            'transmission' => 'AT',
            'steering' => 'RHD',
            'fuel' => 'PETROL',
            'doors' => 5,
            'seats' => 4,
            'location' => 'KOBE, JAPAN',
            'color' => 'Bright Silver',
            'body_type' => 'Hatchback',
            'drive_type' => '2WD',
            'fob_price' => 5800.00,
            'freight_price' => 1100.00,
            'vanning_price' => 200.00,
            'inspection_price' => 450.00,
            'insurance_price' => 30.00,
            'cf_price' => 7580.00,
            'status' => 'Available',
            'featured' => 0,
            'arrival_date' => null,
            'images' => [
                'photo-1549317661-bd32c8ce0db2',
                'photo-1502877338535-766e1452684a'
            ],
            'options' => ['Air Conditioner', 'Power Steering', 'Power Window Front', 'Air Bag', 'ABS']
        ],
        [
            'stock_id' => 'ST-2123',
            'type' => 'In-Stock',
            'make' => 'BMW',
            'model' => '5 Series',
            'year' => 2020,
            'chassis_number' => 'WBA5A31000K88',
            'grade' => '4.5',
            'mileage_km' => 36200,
            'engine_cc' => 2000,
            'transmission' => 'AT',
            'steering' => 'RHD',
            'fuel' => 'PETROL',
            'doors' => 4,
            'seats' => 5,
            'location' => 'KOBE, JAPAN',
            'color' => 'Mineral Grey',
            'body_type' => 'Sedan',
            'drive_type' => '2WD',
            'fob_price' => 27500.00,
            'freight_price' => 1400.00,
            'vanning_price' => 200.00,
            'inspection_price' => 450.00,
            'insurance_price' => 80.00,
            'cf_price' => 29630.00,
            'status' => 'Available',
            'featured' => 0,
            'arrival_date' => null,
            'images' => [
                'photo-1618843479313-40f8afb4b4d8',
                'photo-1555215695-3004980ad54e'
            ],
            'options' => ['Leather Seat', 'Air Conditioner', 'Navigation System', 'Alloy Wheels', 'LED Light', 'Back Camera', 'Push Start', 'ACC']
        ]
    ];
    
    // Prepare inserts
    $vehicleStmt = $db->prepare("
        INSERT INTO vehicles (
            stock_id, type, make, model, year, chassis_number, grade, mileage_km, engine_cc, transmission, 
            steering, fuel, doors, seats, location, color, body_type, drive_type, 
            fob_price, freight_price, vanning_price, inspection_price, insurance_price, cf_price, 
            status, featured, arrival_date, dimension, m3, description, price_jpy
        ) VALUES (
            ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 
            ?, ?, ?, ?, ?, ?, ?, ?, 
            ?, ?, ?, ?, ?, ?, 
            ?, ?, ?, ?, ?, ?, ?
        )
    ");
    
    $imageStmt = $db->prepare("INSERT INTO vehicle_images (vehicle_id, image_url, sort_order) VALUES (?, ?, ?)");
    $optionMapStmt = $db->prepare("INSERT INTO vehicle_options (vehicle_id, option_id) VALUES (?, ?)");
    
    // Get all options with their IDs for mapping
    $optStmt = $db->query("SELECT id, label FROM options");
    $optionsDb = [];
    while ($row = $optStmt->fetch(PDO::FETCH_ASSOC)) {
        $optionsDb[$row['label']] = $row['id'];
    }
    
    foreach ($cars as $car) {
        // Calculate dynamic dimensions and volume (m3) based on body type
        $bodyType = strtolower($car['body_type']);
        if (strpos($bodyType, 'hatchback') !== false || strpos($bodyType, 'compact') !== false) {
            $dimension = '4.05m × 1.69m × 1.48m';
            $m3 = '10.150';
        } elseif (strpos($bodyType, 'sedan') !== false) {
            $dimension = '4.85m × 1.84m × 1.44m';
            $m3 = '12.800';
        } elseif (strpos($bodyType, 'suv') !== false) {
            $dimension = '4.60m × 1.85m × 1.68m';
            $m3 = '14.300';
        } elseif (strpos($bodyType, 'wagon') !== false) {
            $dimension = '4.40m × 1.69m × 1.48m';
            $m3 = '11.000';
        } elseif (strpos($bodyType, 'van') !== false || strpos($bodyType, 'minivan') !== false) {
            $dimension = '4.69m × 1.69m × 1.98m';
            $m3 = '15.700';
        } else {
            $dimension = '4.40m × 1.70m × 1.50m';
            $m3 = '11.200';
        }

        // price_jpy is no longer used for frontend display — currency.js handles live USD→JPY
        // conversion using the real-time exchange rate from open.er-api.com.
        // Storing a static conversion here would be stale and produce incorrect prices.
        $priceJpy = 0;

        // Generate realistic description
        $transmissionName = $car['transmission'] === 'AT' ? 'automatic' : 'manual';
        $description = "Pristine condition {$car['year']} {$car['make']} {$car['model']} {$car['grade']} grade. Features fuel-efficient " . strtolower($car['fuel']) . " engine, {$transmissionName} transmission, and clean interior. Fully inspected by Eisen Corporation certified mechanics.";

        // Insert vehicle
        $vehicleStmt->execute([
            $car['stock_id'],
            $car['type'],
            $car['make'],
            $car['model'],
            $car['year'],
            $car['chassis_number'],
            $car['grade'],
            $car['mileage_km'],
            $car['engine_cc'],
            $car['transmission'],
            $car['steering'],
            $car['fuel'],
            $car['doors'],
            $car['seats'],
            $car['location'],
            $car['color'],
            $car['body_type'],
            $car['drive_type'],
            $car['fob_price'],
            $car['freight_price'],
            $car['vanning_price'],
            $car['inspection_price'],
            $car['insurance_price'],
            $car['cf_price'],
            $car['status'],
            $car['featured'],
            $car['arrival_date'],
            $dimension,
            $m3,
            $description,
            $priceJpy
        ]);
        
        $vehicleId = $db->lastInsertId();
        echo "Inserted vehicle {$car['make']} {$car['model']} (ID: $vehicleId)\n";
        
        // Insert images
        foreach ($car['images'] as $order => $src) {
            $imageStmt->execute([$vehicleId, $src, $order]);
        }
        echo "  - Added " . count($car['images']) . " images\n";
        
        // Insert options mapping
        $optCount = 0;
        foreach ($car['options'] as $label) {
            if (isset($optionsDb[$label])) {
                $optionMapStmt->execute([$vehicleId, $optionsDb[$label]]);
                $optCount++;
            }
        }
        echo "  - Mapped $optCount options\n";
    }
    
    echo "\nAll 30 vehicles seeded successfully!\n";
    
} catch (\Exception $e) {
    echo "Error seeding vehicles: " . $e->getMessage() . "\n";
}
