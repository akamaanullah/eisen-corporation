<?php
namespace App\Controllers\Admin;

use App\Core\Database;
use App\Core\Session;
use PDO;

class InventoryController extends AdminController {
    
    public function index() {
        try {
            $db = Database::getConnection();
            $stmt = $db->query("
                SELECT v.*, 
                       (SELECT image_url FROM vehicle_images WHERE vehicle_id = v.id ORDER BY sort_order ASC LIMIT 1) as image
                FROM vehicles v 
                ORDER BY v.id DESC
            ");
            $dbCars = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            $dbCars = [];
        }

        $cars = [];
        foreach ($dbCars as $car) {
            $cars[] = [
                'db_id' => $car['id'],
                'id' => $car['stock_id'],
                'type' => $car['type'],
                'make' => $car['make'],
                'model' => $car['model'],
                'year' => $car['year'],
                'chassis' => $car['chassis_number'],
                'price' => (float)$car['fob_price'],
                'grade' => $car['grade'],
                'mileage' => number_format($car['mileage_km']) . ' km',
                'transmission' => $car['transmission'] === 'AT' ? 'Auto' : 'Manual',
                'status' => $car['status'],
                'featured' => (bool)$car['featured'],
                'image' => $car['image'],
                'arrival_date' => $car['arrival_date'] ? $car['arrival_date'] : ($car['type'] === 'In-Stock' ? 'Available Now' : 'Pending')
            ];
        }

        $this->view('admin/inventory', [
            'pageTitle' => 'Inventory Management | Eisen Admin',
            'pageScript' => 'inventory.js',
            'cars' => $cars
        ]);
    }

    public function create() {
        try {
            $db = Database::getConnection();
            $stmt = $db->query("SELECT * FROM options ORDER BY category, label");
            $allOptions = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $optionGroups = [];
            foreach ($allOptions as $opt) {
                $category = $opt['category'];
                if (!isset($optionGroups[$category])) {
                    $optionGroups[$category] = [
                        'title' => $category,
                        'items' => []
                    ];
                }
                $optionGroups[$category]['items'][] = [
                    'label' => $opt['label']
                ];
            }
            
            // Sort by predefined category order to match UX look
            $order = [
                'Comfort & Convenience' => 1,
                'Dress Up' => 2,
                'Exterior' => 3,
                'Safety' => 4,
                'Other' => 5
            ];
            uksort($optionGroups, function($a, $b) use ($order) {
                return ($order[$a] ?? 99) <=> ($order[$b] ?? 99);
            });
        } catch (\Exception $e) {
            $optionGroups = [];
        }

        $this->view('admin/inventory-new', [
            'pageTitle' => 'Add New Vehicle | Eisen Admin',
            'pageScript' => 'inventory-new.js',
            'optionGroups' => $optionGroups
        ]);
    }

    public function store() {
        try {
            $this->validateCsrf();
        } catch (\Exception $e) {
            Session::setFlash('error', 'CSRF token validation failed. Please try again.');
            $this->redirect('/admin/inventory/new');
            return;
        }

        // 1. Retrieve and sanitize input fields
        $make = trim($_POST['make'] ?? '');
        $model = trim($_POST['model'] ?? '');
        $year = (int)($_POST['year'] ?? 0);
        $chassis = trim($_POST['chassis'] ?? '');
        $grade = trim($_POST['grade'] ?? '');
        $mileage = (int)($_POST['mileage'] ?? 0);
        $engine = (int)($_POST['engine'] ?? 0);
        $transmission = trim($_POST['transmission'] ?? 'AT');
        $drive = trim($_POST['drive'] ?? '');
        $steering = trim($_POST['steering'] ?? 'RHD');
        $fuel = trim($_POST['fuel'] ?? 'PETROL');
        $body_type = trim($_POST['body_type'] ?? 'Hatchback');
        $doors = (int)($_POST['doors'] ?? 5);
        $seats = (int)($_POST['seats'] ?? 5);
        $stock_type = trim($_POST['stock_type'] ?? 'In-Stock');
        $location = trim($_POST['location'] ?? 'KOBE, JAPAN');
        $color = trim($_POST['color'] ?? 'White');
        $dimension = trim($_POST['dimension'] ?? '0.00m × 0.00m × 0.00m');
        $m3 = trim($_POST['m3'] ?? '10.167');
        $views = (int)($_POST['views'] ?? 0);
        $description = trim($_POST['description'] ?? '');
        if ($description === '') {
            $description = null;
        }

        $pricing_currency = trim($_POST['pricing_currency_selector'] ?? 'USD');
        $exchange_rate = (float)($_POST['exchange_rate'] ?? 150.0);
        if ($exchange_rate <= 0.0) {
            $exchange_rate = 150.0;
        }
        $price_vehicle = (float)($_POST['price_vehicle'] ?? 0);
        $price_jpy = (float)($_POST['price_jpy'] ?? 0);
        $price_freight = (float)($_POST['price_freight'] ?? 0);
        $price_vanning = (float)($_POST['price_vanning'] ?? 0);
        $price_inspection = (float)($_POST['price_inspection'] ?? 0);
        $price_insurance = (float)($_POST['price_insurance'] ?? 0);

        if ($pricing_currency === 'JPY') {
            $price_vehicle = round($price_vehicle / $exchange_rate, 4);
            $price_freight = round($price_freight / $exchange_rate, 4);
            $price_vanning = round($price_vanning / $exchange_rate, 4);
            $price_inspection = round($price_inspection / $exchange_rate, 4);
            $price_insurance = round($price_insurance / $exchange_rate, 4);
        }

        // Calculate total C&F Price
        $cf_price = $price_vehicle + $price_freight + $price_vanning + $price_inspection + $price_insurance;

        // Basic validation
        if ($make === '' || $model === '' || $year === 0 || $chassis === '' || $grade === '' || $price_vehicle === 0.0 || $price_jpy === 0.0) {
            Session::setFlash('error', 'Please fill in all required specifications, vehicle price and JPY price.');
            $this->redirect('/admin/inventory/new');
            return;
        }

        // Length validation checks
        if (mb_strlen($make) > 50 || mb_strlen($model) > 50 || mb_strlen($chassis) > 50 || mb_strlen($grade) > 50 || mb_strlen($color) > 50 || mb_strlen($dimension) > 50) {
            Session::setFlash('error', 'Fields make, model, chassis, grade, color, and dimension must not exceed 50 characters.');
            $this->redirect('/admin/inventory/new');
            return;
        }
        if (mb_strlen($location) > 100) {
            Session::setFlash('error', 'Location must not exceed 100 characters.');
            $this->redirect('/admin/inventory/new');
            return;
        }
        if (mb_strlen($m3) > 20) {
            Session::setFlash('error', 'M3 volume must not exceed 20 characters.');
            $this->redirect('/admin/inventory/new');
            return;
        }
        if ($description && mb_strlen($description) > 1000) {
            Session::setFlash('error', 'Description must not exceed 1000 characters.');
            $this->redirect('/admin/inventory/new');
            return;
        }

        try {
            $db = Database::getConnection();
            $db->beginTransaction();

            // 2. Generate unique stock_id using random_int() and max attempts limit
            $stock_id = '';
            $prefix = ($stock_type === 'Auction') ? 'AUC-' : 'ST-';
            $is_unique = false;
            $attempts = 0;
            $maxAttempts = 100;
            while (!$is_unique && $attempts < $maxAttempts) {
                $attempts++;
                $rand = random_int(1000, 9999);
                $stock_id = $prefix . $rand;
                $chk = $db->prepare("SELECT id FROM vehicles WHERE stock_id = ?");
                $chk->execute([$stock_id]);
                if (!$chk->fetch()) {
                    $is_unique = true;
                }
            }
            if (!$is_unique) {
                throw new \Exception("Failed to generate a unique stock ID after {$maxAttempts} attempts.");
            }

            // 3. Handle Inspection PDF upload
            $damage_report_url = null;
            if (isset($_FILES['inspection_pdf']) && $_FILES['inspection_pdf']['error'] === UPLOAD_ERR_OK) {
                $fileTmpPath = $_FILES['inspection_pdf']['tmp_name'];
                $fileName = $_FILES['inspection_pdf']['name'];
                
                // File size check: limit to 10MB
                if ($_FILES['inspection_pdf']['size'] > 10 * 1024 * 1024) {
                    throw new \Exception("The inspection report file exceeds the 10MB size limit.");
                }

                // MIME type check: must be application/pdf
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mimeType = finfo_file($finfo, $fileTmpPath);
                finfo_close($finfo);
                if ($mimeType !== 'application/pdf') {
                    throw new \Exception("Invalid file type. The inspection report must be a PDF file.");
                }

                $uploadFileDir = ROOT_DIR . '/public/uploads/vehicles/';
                if (!is_dir($uploadFileDir)) {
                    mkdir($uploadFileDir, 0755, true);
                }
                $newFileName = 'report_' . $stock_id . '_' . time() . '.pdf';
                $dest_path = $uploadFileDir . $newFileName;

                if (move_uploaded_file($fileTmpPath, $dest_path)) {
                    $damage_report_url = '/public/uploads/vehicles/' . $newFileName;
                }
            }

            // 4. Insert vehicle record
            $stmt = $db->prepare("
                INSERT INTO vehicles (
                    stock_id, type, make, model, year, chassis_number, grade, mileage_km, engine_cc, transmission, 
                    steering, fuel, doors, seats, location, color, body_type, drive_type, 
                    fob_price, freight_price, vanning_price, inspection_price, insurance_price, cf_price, 
                    damage_report_url, status, featured, arrival_date, dimension, m3, description, views, price_jpy
                ) VALUES (
                    ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 
                    ?, ?, ?, ?, ?, ?, ?, ?, 
                    ?, ?, ?, ?, ?, ?, 
                    ?, 'Available', 0, NULL, ?, ?, ?, ?, ?
                )
            ");
            $stmt->execute([
                $stock_id,
                $stock_type,
                $make,
                $model,
                $year,
                $chassis,
                $grade,
                $mileage,
                $engine,
                $transmission,
                $steering,
                $fuel,
                $doors,
                $seats,
                $location,
                $color,
                $body_type,
                $drive,
                $price_vehicle,
                $price_freight,
                $price_vanning,
                $price_inspection,
                $price_insurance,
                $cf_price,
                $damage_report_url,
                $dimension,
                $m3,
                $description,
                $views,
                $price_jpy
            ]);

            $vehicle_id = $db->lastInsertId();

            // 5. Handle equipment options mapping
            $options = $_POST['options'] ?? [];
            if (!empty($options)) {
                // Fetch options from DB to map names to IDs
                $optQuery = $db->query("SELECT id, label FROM options");
                $optionsDb = [];
                while ($row = $optQuery->fetch(PDO::FETCH_ASSOC)) {
                    $optionsDb[$row['label']] = $row['id'];
                }

                $optionMapStmt = $db->prepare("INSERT INTO vehicle_options (vehicle_id, option_id) VALUES (?, ?)");
                foreach ($options as $optLabel) {
                    if (isset($optionsDb[$optLabel])) {
                        $optionMapStmt->execute([$vehicle_id, $optionsDb[$optLabel]]);
                    }
                }
            }

            // 6. Handle Photos upload
            if (isset($_FILES['images'])) {
                $uploadFileDir = ROOT_DIR . '/public/uploads/vehicles/';
                if (!is_dir($uploadFileDir)) {
                    mkdir($uploadFileDir, 0755, true);
                }

                $imageStmt = $db->prepare("INSERT INTO vehicle_images (vehicle_id, image_url, sort_order) VALUES (?, ?, ?)");

                $fileCount = count($_FILES['images']['name']);
                $sort_order = 0;
                for ($i = 0; $i < $fileCount; $i++) {
                    if ($_FILES['images']['error'][$i] === UPLOAD_ERR_OK) {
                        $fileTmpPath = $_FILES['images']['tmp_name'][$i];
                        $fileName = $_FILES['images']['name'][$i];
                        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

                        $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
                        if (in_array($fileExtension, $allowedExtensions)) {
                            $newFileName = 'img_' . $stock_id . '_' . $i . '_' . time() . '.' . $fileExtension;
                            $dest_path = $uploadFileDir . $newFileName;

                            if (move_uploaded_file($fileTmpPath, $dest_path)) {
                                $image_url = '/public/uploads/vehicles/' . $newFileName;
                                $imageStmt->execute([$vehicle_id, $image_url, $sort_order]);
                                $sort_order++;
                            }
                        }
                    }
                }
            }

            $db->commit();
            Session::setFlash('success', "Vehicle listing \"{$make} {$model}\" was successfully created as {$stock_id}!");
            $this->redirect('/admin/inventory');

        } catch (\Exception $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            Session::setFlash('error', 'Database Error occurred: ' . $e->getMessage());
            $this->redirect('/admin/inventory/new');
        }
    }

    public function delete($id) {
        try {
            $this->validateCsrf();
        } catch (\Exception $e) {
            $this->jsonResponse(['status' => 'error', 'message' => 'CSRF verification failed.'], 403);
            return;
        }
        
        try {
            $db = Database::getConnection();
            
            // 1. Fetch images to delete them from disk
            $imgStmt = $db->prepare("SELECT image_url FROM vehicle_images WHERE vehicle_id = ?");
            $imgStmt->execute([$id]);
            $images = $imgStmt->fetchAll(PDO::FETCH_COLUMN);
            
            // Fetch inspection pdf path to delete from disk
            $pdfStmt = $db->prepare("SELECT damage_report_url FROM vehicles WHERE id = ?");
            $pdfStmt->execute([$id]);
            $pdfPath = $pdfStmt->fetchColumn();

            $db->beginTransaction();

            // 2. Delete database rows (foreign key constraints on cascade delete options and images)
            $stmt = $db->prepare("DELETE FROM vehicles WHERE id = ?");
            $stmt->execute([$id]);
            
            $db->commit();

            // 3. Delete files from disk after database delete succeeds
            foreach ($images as $url) {
                if (strpos($url, '/public/uploads/') === 0) {
                    $filePath = ROOT_DIR . $url;
                    if (file_exists($filePath)) {
                        unlink($filePath);
                    }
                }
            }
            if ($pdfPath && strpos($pdfPath, '/public/uploads/') === 0) {
                $filePath = ROOT_DIR . $pdfPath;
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            }
            
            $this->jsonResponse(['status' => 'success', 'message' => 'Vehicle deleted successfully.']);
        } catch (\Exception $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            $this->jsonResponse(['status' => 'error', 'message' => 'Failed to delete vehicle: ' . $e->getMessage()], 500);
        }
    }

    public function toggleFeatured($id) {
        try {
            $this->validateCsrf();
        } catch (\Exception $e) {
            $this->jsonResponse(['status' => 'error', 'message' => 'CSRF verification failed.'], 403);
            return;
        }

        try {
            $db = Database::getConnection();
            
            // Get current featured status
            $stmt = $db->prepare("SELECT featured FROM vehicles WHERE id = ?");
            $stmt->execute([$id]);
            $featured = $stmt->fetchColumn();
            
            if ($featured === false) {
                $this->jsonResponse(['status' => 'error', 'message' => 'Vehicle not found.'], 404);
                return;
            }

            $newFeatured = $featured ? 0 : 1;
            
            $update = $db->prepare("UPDATE vehicles SET featured = ? WHERE id = ?");
            $update->execute([$newFeatured, $id]);
            
            $this->jsonResponse([
                'status' => 'success', 
                'message' => 'Featured status updated successfully.',
                'featured' => (bool)$newFeatured
            ]);
        } catch (\Exception $e) {
            $this->jsonResponse(['status' => 'error', 'message' => 'Database Error: ' . $e->getMessage()], 500);
        }
    }

    public function edit($id) {
        try {
            $db = Database::getConnection();
            
            // 1. Fetch vehicle details
            $stmt = $db->prepare("SELECT * FROM vehicles WHERE id = ? LIMIT 1");
            $stmt->execute([$id]);
            $car = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$car) {
                Session::setFlash('error', 'Vehicle not found.');
                $this->redirect('/admin/inventory');
                return;
            }

            // 2. Fetch options mapped to this vehicle
            $optMapStmt = $db->prepare("SELECT option_id FROM vehicle_options WHERE vehicle_id = ?");
            $optMapStmt->execute([$id]);
            $checkedOptionIds = $optMapStmt->fetchAll(PDO::FETCH_COLUMN);

            // 3. Fetch all options and group them
            $optStmt = $db->query("SELECT * FROM options ORDER BY category, label");
            $allOptions = $optStmt->fetchAll(PDO::FETCH_ASSOC);

            $optionGroups = [];
            foreach ($allOptions as $opt) {
                $category = $opt['category'];
                if (!isset($optionGroups[$category])) {
                    $optionGroups[$category] = [
                        'title' => $category,
                        'items' => []
                    ];
                }
                $optionGroups[$category]['items'][] = [
                    'label' => $opt['label'],
                    'active' => in_array($opt['id'], $checkedOptionIds)
                ];
            }
            
            // Sort option groups
            $order = [
                'Comfort & Convenience' => 1,
                'Dress Up' => 2,
                'Exterior' => 3,
                'Safety' => 4,
                'Other' => 5
            ];
            uksort($optionGroups, function($a, $b) use ($order) {
                return ($order[$a] ?? 99) <=> ($order[$b] ?? 99);
            });

            // 4. Fetch existing images
            $imgStmt = $db->prepare("SELECT image_url FROM vehicle_images WHERE vehicle_id = ? ORDER BY sort_order ASC");
            $imgStmt->execute([$id]);
            $existingImages = $imgStmt->fetchAll(PDO::FETCH_COLUMN);

        } catch (\Exception $e) {
            Session::setFlash('error', 'Database Error occurred: ' . $e->getMessage());
            $this->redirect('/admin/inventory');
            return;
        }

        $this->view('admin/inventory-edit', [
            'pageTitle' => 'Edit Vehicle Listing | Eisen Admin',
            'pageScript' => 'inventory-new.js',
            'car' => $car,
            'optionGroups' => $optionGroups,
            'existingImages' => $existingImages
        ]);
    }

    public function update($id) {
        try {
            $this->validateCsrf();
        } catch (\Exception $e) {
            Session::setFlash('error', 'CSRF token validation failed. Please try again.');
            $this->redirect('/admin/inventory/edit/' . $id);
            return;
        }

        try {
            $db = Database::getConnection();
            
            // 1. Check if vehicle exists
            $chk = $db->prepare("SELECT id, stock_id, damage_report_url FROM vehicles WHERE id = ? LIMIT 1");
            $chk->execute([$id]);
            $existingCar = $chk->fetch(PDO::FETCH_ASSOC);

            if (!$existingCar) {
                Session::setFlash('error', 'Vehicle not found.');
                $this->redirect('/admin/inventory');
                return;
            }

            // 2. Retrieve and sanitize input fields
            $make = trim($_POST['make'] ?? '');
            $model = trim($_POST['model'] ?? '');
            $year = (int)($_POST['year'] ?? 0);
            $chassis = trim($_POST['chassis'] ?? '');
            $grade = trim($_POST['grade'] ?? '');
            $mileage = (int)($_POST['mileage'] ?? 0);
            $engine = (int)($_POST['engine'] ?? 0);
            $transmission = trim($_POST['transmission'] ?? 'AT');
            $drive = trim($_POST['drive'] ?? '');
            $steering = trim($_POST['steering'] ?? 'RHD');
            $fuel = trim($_POST['fuel'] ?? 'PETROL');
            $body_type = trim($_POST['body_type'] ?? 'Hatchback');
            $doors = (int)($_POST['doors'] ?? 5);
            $seats = (int)($_POST['seats'] ?? 5);
            $stock_type = trim($_POST['stock_type'] ?? 'In-Stock');
            $location = trim($_POST['location'] ?? 'KOBE, JAPAN');
            $color = trim($_POST['color'] ?? 'White');
            $dimension = trim($_POST['dimension'] ?? '0.00m × 0.00m × 0.00m');
            $m3 = trim($_POST['m3'] ?? '10.167');
            $views = (int)($_POST['views'] ?? 0);
            $description = trim($_POST['description'] ?? '');
            if ($description === '') {
                $description = null;
            }

            $pricing_currency = trim($_POST['pricing_currency_selector'] ?? 'USD');
            $exchange_rate = (float)($_POST['exchange_rate'] ?? 150.0);
            if ($exchange_rate <= 0.0) {
                $exchange_rate = 150.0;
            }
            $price_vehicle = (float)($_POST['price_vehicle'] ?? 0);
            $price_jpy = (float)($_POST['price_jpy'] ?? 0);
            $price_freight = (float)($_POST['price_freight'] ?? 0);
            $price_vanning = (float)($_POST['price_vanning'] ?? 0);
            $price_inspection = (float)($_POST['price_inspection'] ?? 0);
            $price_insurance = (float)($_POST['price_insurance'] ?? 0);

            if ($pricing_currency === 'JPY') {
                $price_vehicle = round($price_vehicle / $exchange_rate, 4);
                $price_freight = round($price_freight / $exchange_rate, 4);
                $price_vanning = round($price_vanning / $exchange_rate, 4);
                $price_inspection = round($price_inspection / $exchange_rate, 4);
                $price_insurance = round($price_insurance / $exchange_rate, 4);
            }

            // Calculate total C&F Price
            $cf_price = $price_vehicle + $price_freight + $price_vanning + $price_inspection + $price_insurance;

            if ($make === '' || $model === '' || $year === 0 || $chassis === '' || $grade === '' || $price_vehicle === 0.0 || $price_jpy === 0.0) {
                Session::setFlash('error', 'Please fill in all required specifications, vehicle price and JPY price.');
                $this->redirect('/admin/inventory/edit/' . $id);
                return;
            }

            // Length validation checks
            if (mb_strlen($make) > 50 || mb_strlen($model) > 50 || mb_strlen($chassis) > 50 || mb_strlen($grade) > 50 || mb_strlen($color) > 50 || mb_strlen($dimension) > 50) {
                Session::setFlash('error', 'Fields make, model, chassis, grade, color, and dimension must not exceed 50 characters.');
                $this->redirect('/admin/inventory/edit/' . $id);
                return;
            }
            if (mb_strlen($location) > 100) {
                Session::setFlash('error', 'Location must not exceed 100 characters.');
                $this->redirect('/admin/inventory/edit/' . $id);
                return;
            }
            if (mb_strlen($m3) > 20) {
                Session::setFlash('error', 'M3 volume must not exceed 20 characters.');
                $this->redirect('/admin/inventory/edit/' . $id);
                return;
            }
            if ($description && mb_strlen($description) > 1000) {
                Session::setFlash('error', 'Description must not exceed 1000 characters.');
                $this->redirect('/admin/inventory/edit/' . $id);
                return;
            }

            $db->beginTransaction();

            // 3. Handle Inspection PDF upload
            $damage_report_url = $existingCar['damage_report_url'];
            if (isset($_FILES['inspection_pdf']) && $_FILES['inspection_pdf']['error'] === UPLOAD_ERR_OK) {
                $fileTmpPath = $_FILES['inspection_pdf']['tmp_name'];
                $fileName = $_FILES['inspection_pdf']['name'];
                
                // File size check: limit to 10MB
                if ($_FILES['inspection_pdf']['size'] > 10 * 1024 * 1024) {
                    throw new \Exception("The inspection report file exceeds the 10MB size limit.");
                }

                // MIME type check: must be application/pdf
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mimeType = finfo_file($finfo, $fileTmpPath);
                finfo_close($finfo);
                if ($mimeType !== 'application/pdf') {
                    throw new \Exception("Invalid file type. The inspection report must be a PDF file.");
                }

                $uploadFileDir = ROOT_DIR . '/public/uploads/vehicles/';
                if (!is_dir($uploadFileDir)) {
                    mkdir($uploadFileDir, 0755, true);
                }
                if ($damage_report_url && strpos($damage_report_url, '/public/uploads/') === 0) {
                    $oldPath = ROOT_DIR . $damage_report_url;
                    if (file_exists($oldPath)) {
                        unlink($oldPath);
                    }
                }
                $newFileName = 'report_' . $existingCar['stock_id'] . '_' . time() . '.pdf';
                $dest_path = $uploadFileDir . $newFileName;

                if (move_uploaded_file($fileTmpPath, $dest_path)) {
                    $damage_report_url = '/public/uploads/vehicles/' . $newFileName;
                }
            }

            // 4. Update vehicle record
            $stmt = $db->prepare("
                UPDATE vehicles SET
                    type = ?, make = ?, model = ?, year = ?, chassis_number = ?, grade = ?, 
                    mileage_km = ?, engine_cc = ?, transmission = ?, steering = ?, fuel = ?, 
                    doors = ?, seats = ?, location = ?, color = ?, body_type = ?, drive_type = ?, 
                    fob_price = ?, freight_price = ?, vanning_price = ?, inspection_price = ?, 
                    insurance_price = ?, cf_price = ?, damage_report_url = ?, dimension = ?, m3 = ?, description = ?, views = ?, price_jpy = ?
                WHERE id = ?
            ");
            $stmt->execute([
                $stock_type,
                $make,
                $model,
                $year,
                $chassis,
                $grade,
                $mileage,
                $engine,
                $transmission,
                $steering,
                $fuel,
                $doors,
                $seats,
                $location,
                $color,
                $body_type,
                $drive,
                $price_vehicle,
                $price_freight,
                $price_vanning,
                $price_inspection,
                $price_insurance,
                $cf_price,
                $damage_report_url,
                $dimension,
                $m3,
                $description,
                $views,
                $price_jpy,
                $id
            ]);

            // 5. Update options mapping
            $db->prepare("DELETE FROM vehicle_options WHERE vehicle_id = ?")->execute([$id]);
            $options = $_POST['options'] ?? [];
            if (!empty($options)) {
                $optQuery = $db->query("SELECT id, label FROM options");
                $optionsDb = [];
                while ($row = $optQuery->fetch(PDO::FETCH_ASSOC)) {
                    $optionsDb[$row['label']] = $row['id'];
                }

                $optionMapStmt = $db->prepare("INSERT INTO vehicle_options (vehicle_id, option_id) VALUES (?, ?)");
                foreach ($options as $optLabel) {
                    if (isset($optionsDb[$optLabel])) {
                        $optionMapStmt->execute([$id, $optionsDb[$optLabel]]);
                    }
                }
            }

            // 6. Handle Photos
            $existing_images = $_POST['existing_images'] ?? [];
            
            $imgStmt = $db->prepare("SELECT id, image_url FROM vehicle_images WHERE vehicle_id = ?");
            $imgStmt->execute([$id]);
            $currentImages = $imgStmt->fetchAll(PDO::FETCH_ASSOC);

            $deleteStmt = $db->prepare("DELETE FROM vehicle_images WHERE id = ?");
            foreach ($currentImages as $currImg) {
                if (!in_array($currImg['image_url'], $existing_images)) {
                    $deleteStmt->execute([$currImg['id']]);
                    if (strpos($currImg['image_url'], '/public/uploads/') === 0) {
                        $filePath = ROOT_DIR . $currImg['image_url'];
                        if (file_exists($filePath)) {
                            unlink($filePath);
                        }
                    }
                }
            }

            $uploadedImages = [];
            if (isset($_FILES['images'])) {
                $uploadFileDir = ROOT_DIR . '/public/uploads/vehicles/';
                if (!is_dir($uploadFileDir)) {
                    mkdir($uploadFileDir, 0755, true);
                }

                $fileCount = count($_FILES['images']['name']);
                for ($i = 0; $i < $fileCount; $i++) {
                    if ($_FILES['images']['error'][$i] === UPLOAD_ERR_OK) {
                        $fileTmpPath = $_FILES['images']['tmp_name'][$i];
                        $fileName = $_FILES['images']['name'][$i];
                        
                        // File size check: limit to 5MB
                        if ($_FILES['images']['size'][$i] > 5 * 1024 * 1024) {
                            throw new \Exception("Vehicle image {$fileName} exceeds the 5MB size limit.");
                        }

                        // MIME type check: must be jpeg, png, webp
                        $finfo = finfo_open(FILEINFO_MIME_TYPE);
                        $mimeType = finfo_file($finfo, $fileTmpPath);
                        finfo_close($finfo);
                        if (!in_array($mimeType, ['image/jpeg', 'image/png', 'image/webp'])) {
                            throw new \Exception("Vehicle image {$fileName} has an invalid file format (only JPG, PNG, and WebP are allowed).");
                        }

                        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                        $newFileName = 'img_' . $existingCar['stock_id'] . '_u' . $i . '_' . time() . '.' . $fileExtension;
                        $dest_path = $uploadFileDir . $newFileName;

                        if (move_uploaded_file($fileTmpPath, $dest_path)) {
                            $uploadedImages[] = '/public/uploads/vehicles/' . $newFileName;
                        }
                    }
                }
            }

            $finalImages = array_merge($existing_images, $uploadedImages);
            
            $db->prepare("DELETE FROM vehicle_images WHERE vehicle_id = ?")->execute([$id]);
            
            $insertImgStmt = $db->prepare("INSERT INTO vehicle_images (vehicle_id, image_url, sort_order) VALUES (?, ?, ?)");
            foreach ($finalImages as $index => $imgUrl) {
                $insertImgStmt->execute([$id, $imgUrl, $index]);
            }

            $db->commit();
            Session::setFlash('success', "Vehicle listing \"{$make} {$model}\" has been successfully updated!");
            $this->redirect('/admin/inventory');

        } catch (\Exception $e) {
            if (isset($db) && $db->inTransaction()) {
                $db->rollBack();
            }
            Session::setFlash('error', 'Error updating vehicle: ' . $e->getMessage());
            $this->redirect('/admin/inventory/edit/' . $id);
        }
    }

    public function duplicate($id) {
        header('Content-Type: application/json');
        try {
            $this->validateCsrf();
        } catch (\Exception $e) {
            return $this->jsonResponse(['status' => 'error', 'message' => 'CSRF validation failed. Please reload.'], 400);
        }

        try {
            $db = Database::getConnection();
            $db->beginTransaction();

            // 1. Fetch original vehicle
            $stmt = $db->prepare("SELECT * FROM vehicles WHERE id = ? LIMIT 1");
            $stmt->execute([$id]);
            $car = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$car) {
                return $this->jsonResponse(['status' => 'error', 'message' => 'Vehicle not found.'], 404);
            }

            // 2. Generate new unique stock ID
            $newStockId = '';
            $attempts = 0;
            while ($attempts < 100) {
                $candidate = 'EC' . random_int(100000, 999999);
                $dupCheck = $db->prepare("SELECT id FROM vehicles WHERE stock_id = ? LIMIT 1");
                $dupCheck->execute([$candidate]);
                if (!$dupCheck->fetch()) {
                    $newStockId = $candidate;
                    break;
                }
                $attempts++;
            }
            if ($newStockId === '') {
                throw new \Exception("Could not generate a unique Stock ID after multiple attempts.");
            }

            // 3. Generate new unique chassis number
            $newChassis = $car['chassis_number'] . '-DUP' . random_int(10, 99);
            $chassisCheck = $db->prepare("SELECT id FROM vehicles WHERE chassis_number = ? LIMIT 1");
            $chassisCheck->execute([$newChassis]);
            if ($chassisCheck->fetch()) {
                $newChassis = $car['chassis_number'] . '-DUP' . random_int(100, 999);
            }

            // 4. Insert duplicated vehicle
            $insertSql = "
                INSERT INTO vehicles (
                    stock_id, chassis_number, type, make, model, year, grade, mileage_km, engine_cc,
                    transmission, steering, fuel, doors, seats, location, color, body_type, drive_type,
                    fob_price, freight_price, vanning_price, inspection_price, insurance_price, cf_price,
                    damage_report_url, status, featured, arrival_date, dimension, m3, description, views, price_jpy
                ) VALUES (
                    ?, ?, ?, ?, ?, ?, ?, ?, ?,
                    ?, ?, ?, ?, ?, ?, ?, ?, ?,
                    ?, ?, ?, ?, ?, ?,
                    ?, 'Available', 0, ?, ?, ?, ?, 0, ?
                )
            ";

            $stmtInsert = $db->prepare($insertSql);
            $stmtInsert->execute([
                $newStockId,
                $newChassis,
                $car['type'],
                $car['make'],
                $car['model'],
                $car['year'],
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
                $car['damage_report_url'],
                $car['arrival_date'],
                $car['dimension'],
                $car['m3'],
                $car['description'],
                $car['price_jpy']
            ]);

            $newVehicleId = $db->lastInsertId();

            // 5. Duplicate vehicle options
            $optsStmt = $db->prepare("SELECT option_id FROM vehicle_options WHERE vehicle_id = ?");
            $optsStmt->execute([$id]);
            $options = $optsStmt->fetchAll(PDO::FETCH_COLUMN);

            if (!empty($options)) {
                $optInsert = $db->prepare("INSERT INTO vehicle_options (vehicle_id, option_id) VALUES (?, ?)");
                foreach ($options as $optId) {
                    $optInsert->execute([$newVehicleId, $optId]);
                }
            }

            // 6. Duplicate vehicle images
            $imgsStmt = $db->prepare("SELECT image_url, sort_order FROM vehicle_images WHERE vehicle_id = ?");
            $imgsStmt->execute([$id]);
            $images = $imgsStmt->fetchAll(PDO::FETCH_ASSOC);

            if (!empty($images)) {
                $imgInsert = $db->prepare("INSERT INTO vehicle_images (vehicle_id, image_url, sort_order) VALUES (?, ?, ?)");
                foreach ($images as $img) {
                    $imgInsert->execute([$newVehicleId, $img['image_url'], $img['sort_order']]);
                }
            }

            $db->commit();
            return $this->jsonResponse([
                'status' => 'success',
                'message' => "Successfully duplicated \"{$car['make']} {$car['model']}\". New Stock ID: {$newStockId}."
            ]);
        } catch (\Exception $e) {
            if (isset($db) && $db->inTransaction()) {
                $db->rollBack();
            }
            error_log("Vehicle duplicate error: " . $e->getMessage());
            return $this->jsonResponse(['status' => 'error', 'message' => 'Failed to duplicate listing: ' . $e->getMessage()], 500);
        }
    }

    public function archive($id) {
        header('Content-Type: application/json');
        try {
            $this->validateCsrf();
        } catch (\Exception $e) {
            return $this->jsonResponse(['status' => 'error', 'message' => 'CSRF validation failed. Please reload.'], 400);
        }

        try {
            $db = Database::getConnection();
            
            // Check existence
            $stmt = $db->prepare("SELECT make, model FROM vehicles WHERE id = ? LIMIT 1");
            $stmt->execute([$id]);
            $car = $stmt->fetch();
            if (!$car) {
                return $this->jsonResponse(['status' => 'error', 'message' => 'Vehicle not found.'], 404);
            }

            // Update status to 'Archived'
            $upd = $db->prepare("UPDATE vehicles SET status = 'Archived' WHERE id = ?");
            $upd->execute([$id]);

            return $this->jsonResponse([
                'status' => 'success',
                'message' => "Successfully archived \"{$car['make']} {$car['model']}\"."
            ]);
        } catch (\Exception $e) {
            error_log("Vehicle archive error: " . $e->getMessage());
            return $this->jsonResponse(['status' => 'error', 'message' => 'Failed to archive listing: ' . $e->getMessage()], 500);
        }
    }

    public function syncAuctions() {
        header('Content-Type: application/json');
        try {
            $this->validateCsrf();
        } catch (\Exception $e) {
            return $this->jsonResponse(['status' => 'error', 'message' => 'CSRF validation failed. Please reload.'], 400);
        }

        try {
            $db = Database::getConnection();
            $db->beginTransaction();

            // Sample Auction feed lots to insert dynamically
            $lots = [
                [
                    'make' => 'Toyota',
                    'model' => 'Prius',
                    'year' => 2021,
                    'grade' => 'S-Touring',
                    'mileage_km' => 42000,
                    'engine_cc' => 1800,
                    'transmission' => 'AT',
                    'steering' => 'RHD',
                    'fuel' => 'HYBRID',
                    'location' => 'TOKYO, JAPAN',
                    'color' => 'Pearl White',
                    'body_type' => 'Hatchback',
                    'drive_type' => '2WD',
                    'fob_price' => 12500.00,
                    'price_jpy' => 1875000.00
                ],
                [
                    'make' => 'Nissan',
                    'model' => 'Leaf',
                    'year' => 2020,
                    'grade' => 'X-Edition',
                    'mileage_km' => 31000,
                    'engine_cc' => 0,
                    'transmission' => 'AT',
                    'steering' => 'RHD',
                    'fuel' => 'ELECTRIC',
                    'location' => 'YOKOHAMA, JAPAN',
                    'color' => 'Metallic Grey',
                    'body_type' => 'Hatchback',
                    'drive_type' => '2WD',
                    'fob_price' => 9800.00,
                    'price_jpy' => 1470000.00
                ],
                [
                    'make' => 'Honda',
                    'model' => 'Vezel',
                    'year' => 2022,
                    'grade' => 'e-HEV Z',
                    'mileage_km' => 15000,
                    'engine_cc' => 1500,
                    'transmission' => 'AT',
                    'steering' => 'RHD',
                    'fuel' => 'HYBRID',
                    'location' => 'NAGOYA, JAPAN',
                    'color' => 'Crystal Black',
                    'body_type' => 'SUV',
                    'drive_type' => '4WD',
                    'fob_price' => 16500.00,
                    'price_jpy' => 2475000.00
                ]
            ];

            $syncCount = 0;
            foreach ($lots as $lot) {
                // Generate unique stock id and chassis number
                $stockId = 'EC' . random_int(100000, 999999);
                $chassis = 'ZA' . random_int(100, 999) . '-' . random_int(1000000, 9999999);
                
                // Confirm no duplicate
                $check = $db->prepare("SELECT id FROM vehicles WHERE chassis_number = ? OR stock_id = ?");
                $check->execute([$chassis, $stockId]);
                if ($check->fetch()) {
                    continue; // Skip if somehow hit duplicate
                }

                $insertSql = "
                    INSERT INTO vehicles (
                        stock_id, chassis_number, type, make, model, year, grade, mileage_km, engine_cc,
                        transmission, steering, fuel, doors, seats, location, color, body_type, drive_type,
                        fob_price, freight_price, vanning_price, inspection_price, insurance_price, cf_price,
                        status, featured, dimension, m3, price_jpy
                    ) VALUES (
                        ?, ?, 'Auction', ?, ?, ?, ?, ?, ?,
                        ?, ?, ?, 5, 5, ?, ?, ?, ?,
                        ?, 0, 0, 0, 0, ?,
                        'Available', 0, '4.20m × 1.69m × 1.49m', '10.58', ?
                    )
                ";
                
                $stmt = $db->prepare($insertSql);
                $stmt->execute([
                    $stockId,
                    $chassis,
                    $lot['make'],
                    $lot['model'],
                    $lot['year'],
                    $lot['grade'],
                    $lot['mileage_km'],
                    $lot['engine_cc'],
                    $lot['transmission'],
                    $lot['steering'],
                    $lot['fuel'],
                    $lot['location'],
                    $lot['color'],
                    $lot['body_type'],
                    $lot['drive_type'],
                    $lot['fob_price'],
                    $lot['fob_price'],
                    $lot['price_jpy']
                ]);
                $syncCount++;
            }

            $db->commit();
            return $this->jsonResponse([
                'status' => 'success',
                'count' => $syncCount,
                'message' => "Successfully synchronized {$syncCount} new Japan auction lots."
            ]);
        } catch (\Exception $e) {
            if (isset($db) && $db->inTransaction()) {
                $db->rollBack();
            }
            error_log("Auction API sync error: " . $e->getMessage());
            return $this->jsonResponse(['status' => 'error', 'message' => 'Failed to synchronize auction lots: ' . $e->getMessage()], 500);
        }
    }
}
