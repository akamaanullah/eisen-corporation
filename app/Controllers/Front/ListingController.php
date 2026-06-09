<?php
namespace App\Controllers\Front;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Session;
use App\Helpers\VehicleDisplay;
use App\Helpers\VehicleSpecOptions;
use PDO;

class ListingController extends Controller {
    public function index() {
        try {
            $db = Database::getConnection();
            
            // Fetch unique makes
            $makesStmt = $db->query("SELECT DISTINCT make FROM master_makes_models ORDER BY make ASC");
            $dbMakes = $makesStmt->fetchAll(PDO::FETCH_COLUMN);
            
            // Fetch unique models
            $modelsStmt = $db->query("SELECT DISTINCT model FROM master_makes_models ORDER BY model ASC");
            $dbModels = $modelsStmt->fetchAll(PDO::FETCH_COLUMN);
            
            // Fetch unique fuel types from vehicles
            $fuelStmt = $db->query("SELECT DISTINCT fuel FROM vehicles WHERE fuel IS NOT NULL AND fuel != '' ORDER BY fuel ASC");
            $dbFuels = $fuelStmt->fetchAll(PDO::FETCH_COLUMN);
            
            // Fetch unique colors from vehicles
            $colorStmt = $db->query("SELECT DISTINCT color FROM vehicles WHERE color IS NOT NULL AND color != '' ORDER BY color ASC");
            $dbColors = $colorStmt->fetchAll(PDO::FETCH_COLUMN);

            $countStmt = $db->query("SELECT COUNT(*) FROM vehicles WHERE status = 'Available'");
            $totalListings = (int) $countStmt->fetchColumn();
            
        } catch (\Exception $e) {
            $dbMakes = [];
            $dbModels = [];
            $dbFuels = [];
            $dbColors = [];
            $totalListings = 0;
        }

        $this->view('front/listing', [
            'makes' => $dbMakes,
            'models' => $dbModels,
            'fuels' => $dbFuels,
            'colors' => $dbColors,
            'totalListings' => $totalListings,
        ]);
    }

    public function api() {
        header('Content-Type: application/json');
        
        try {
            $db = Database::getConnection();
            
            $conditions = ["status = 'Available'"];
            $params = [];
            
            // 1. Make Filter
            if (!empty($_GET['make'])) {
                $makes = explode(',', $_GET['make']);
                $makeConds = [];
                $hasOthers = false;
                $idx = 0;
                foreach ($makes as $m) {
                    $m = trim($m);
                    if (strtolower($m) === 'others') {
                        $hasOthers = true;
                    } else {
                        $paramName = 'make_' . $idx++;
                        $makeConds[] = "LOWER(make) = :{$paramName}";
                        $params[$paramName] = strtolower($m);
                    }
                }
                
                $clause = '';
                if (!empty($makeConds)) {
                    $clause = implode(' OR ', $makeConds);
                }
                
                if ($hasOthers) {
                    // Exclude all standard makes in listing
                    $standardMakes = [];
                    try {
                        $stdMakesStmt = $db->query("SELECT DISTINCT LOWER(make) FROM master_makes_models");
                        $standardMakes = $stdMakesStmt->fetchAll(PDO::FETCH_COLUMN);
                    } catch (\Exception $ex) {}
                    if (empty($standardMakes)) {
                        $standardMakes = ['audi', 'bmw', 'daihatsu', 'ford', 'hino', 'honda', 'isuzu', 'lexus', 'mazda', 'mercedes', 'mitsubishi', 'nissan', 'porsche', 'subaru', 'suzuki', 'toyota', 'volkswagen', 'volvo'];
                    }
                    $excludeList = [];
                    foreach ($standardMakes as $sm) {
                        $paramName = 'std_make_' . $idx++;
                        $excludeList[] = ":{$paramName}";
                        $params[$paramName] = $sm;
                    }
                    $excludeClause = "LOWER(make) NOT IN (" . implode(', ', $excludeList) . ")";
                    if ($clause) {
                        $clause = "({$clause} OR {$excludeClause})";
                    } else {
                        $clause = $excludeClause;
                    }
                }
                
                if ($clause) {
                    $conditions[] = "({$clause})";
                }
            }
            
            // 2. Model Filter
            if (!empty($_GET['model'])) {
                $models = explode(',', $_GET['model']);
                $modelConds = [];
                $hasOthers = false;
                $idx = 0;
                foreach ($models as $m) {
                    $m = trim($m);
                    if (strtolower($m) === 'others') {
                        $hasOthers = true;
                    } else {
                        $paramName = 'model_' . $idx++;
                        // Use wildcard matching for trim names
                        $modelConds[] = "LOWER(model) LIKE :{$paramName}";
                        $params[$paramName] = '%' . strtolower(str_replace('-', '%', $m)) . '%';
                    }
                }
                
                $clause = '';
                if (!empty($modelConds)) {
                    $clause = implode(' OR ', $modelConds);
                }
                
                if ($hasOthers) {
                    $standardModels = [];
                    try {
                        $stdModelsStmt = $db->query("SELECT DISTINCT LOWER(model) FROM master_makes_models");
                        $rawModels = $stdModelsStmt->fetchAll(PDO::FETCH_COLUMN);
                        foreach ($rawModels as $rm) {
                            $standardModels[] = str_replace(' ', '-', $rm);
                        }
                    } catch (\Exception $ex) {}
                    if (empty($standardModels)) {
                        $standardModels = ['prius', 'aqua', 'corolla', 'camry', 'highlander', 'rav4', 'land-cruiser', 'alphard', 'hiace', 'fit', 'civic', 'cr-v', 'accord', 'vezel', 'note', 'leaf', 'x-trail', 'skyline', 'cx-5', 'demio', 'forester', 'impreza', 'swift', 'jimny', 'x5', '3-series', 'c-class', 'e-class', 'q5', 'a4'];
                    }
                    $excludeConds = [];
                    foreach ($standardModels as $sm) {
                        $paramName = 'std_model_' . $idx++;
                        $excludeConds[] = "LOWER(model) NOT LIKE :{$paramName}";
                        $params[$paramName] = '%' . $sm . '%';
                    }
                    $excludeClause = implode(' AND ', $excludeConds);
                    if ($clause) {
                        $clause = "({$clause} OR ({$excludeClause}))";
                    } else {
                        $clause = $excludeClause;
                    }
                }
                
                if ($clause) {
                    $conditions[] = "({$clause})";
                }
            }
            
            // 3. Fuel Filter
            if (!empty($_GET['fuel'])) {
                $fuels = explode(',', $_GET['fuel']);
                $fuelConds = [];
                $hasOthers = false;
                $idx = 0;
                foreach ($fuels as $f) {
                    $f = trim($f);
                    if (strtolower($f) === 'others') {
                        $hasOthers = true;
                    } else {
                        $paramName = 'fuel_' . $idx++;
                        $fuelConds[] = "LOWER(fuel) = :{$paramName}";
                        $dbFuel = strtoupper($f); // electric, hybrid, petrol
                        if ($f === 'lpg-petrol') $dbFuel = 'PETROL';
                        $params[$paramName] = $dbFuel;
                    }
                }
                
                $clause = '';
                if (!empty($fuelConds)) {
                    $clause = implode(' OR ', $fuelConds);
                }
                
                if ($hasOthers) {
                    $primaryFuels = VehicleSpecOptions::primaryFuelCodes();
                    $excludeParts = array_map(
                        static fn(string $fuel): string => "'" . str_replace("'", "''", $fuel) . "'",
                        $primaryFuels
                    );
                    $excludeClause = 'fuel NOT IN (' . implode(', ', $excludeParts) . ')';
                    if ($clause) {
                        $clause = "({$clause} OR {$excludeClause})";
                    } else {
                        $clause = $excludeClause;
                    }
                }
                
                if ($clause) {
                    $conditions[] = "({$clause})";
                }
            }
            
            // 4. Transmission Filter
            if (!empty($_GET['transmission'])) {
                $trans = explode(',', $_GET['transmission']);
                $transConds = [];
                $idx = 0;
                $manualCodes = VehicleSpecOptions::manualTransmissionCodes();
                foreach ($trans as $t) {
                    $t = trim(strtolower($t));
                    if ($t === 'manual') {
                        $manualPlaceholders = [];
                        foreach ($manualCodes as $code) {
                            $paramName = 'trans_' . $idx++;
                            $manualPlaceholders[] = ':' . $paramName;
                            $params[$paramName] = $code;
                        }
                        if (!empty($manualPlaceholders)) {
                            $transConds[] = 'transmission IN (' . implode(', ', $manualPlaceholders) . ')';
                        }
                        continue;
                    }

                    if ($t === 'auto') {
                        $autoPlaceholders = [];
                        foreach (array_keys(VehicleSpecOptions::transmissionOptions()) as $code) {
                            if (in_array($code, $manualCodes, true)) {
                                continue;
                            }
                            $paramName = 'trans_' . $idx++;
                            $autoPlaceholders[] = ':' . $paramName;
                            $params[$paramName] = $code;
                        }
                        if (!empty($autoPlaceholders)) {
                            $transConds[] = 'transmission IN (' . implode(', ', $autoPlaceholders) . ')';
                        }
                    }
                }
                if (!empty($transConds)) {
                    $conditions[] = '(' . implode(' OR ', $transConds) . ')';
                }
            }
            
            // 5. Color Filter
            if (!empty($_GET['color'])) {
                $colors = explode(',', $_GET['color']);
                $colorConds = [];
                $hasOthers = false;
                $idx = 0;
                foreach ($colors as $c) {
                    $c = trim($c);
                    if (strtolower($c) === 'others') {
                        $hasOthers = true;
                    } else {
                        $paramName = 'color_' . $idx++;
                        $colorConds[] = "LOWER(color) LIKE :{$paramName}";
                        $params[$paramName] = '%' . strtolower(str_replace('-', '%', $c)) . '%';
                    }
                }
                
                $clause = '';
                if (!empty($colorConds)) {
                    $clause = implode(' OR ', $colorConds);
                }
                
                if ($hasOthers) {
                    $standardColors = ['beige', 'black', 'blue', 'bronze', 'brown', 'cream', 'gold', 'green', 'grey', 'khaki', 'maroon', 'orange', 'pearl', 'pink', 'purple', 'red', 'silver', 'white', 'yellow'];
                    $excludeConds = [];
                    foreach ($standardColors as $sc) {
                        $paramName = 'std_color_' . $idx++;
                        $excludeConds[] = "LOWER(color) NOT LIKE :{$paramName}";
                        $params[$paramName] = '%' . $sc . '%';
                    }
                    $excludeClause = implode(' AND ', $excludeConds);
                    if ($clause) {
                        $clause = "({$clause} OR ({$excludeClause}))";
                    } else {
                        $clause = $excludeClause;
                    }
                }
                
                if ($clause) {
                    $conditions[] = "({$clause})";
                }
            }
            
            // 6. Condition Filter (mileage-based)
            if (!empty($_GET['condition']) && $_GET['condition'] !== 'all') {
                if ($_GET['condition'] === 'new') {
                    $conditions[] = "mileage_km <= 100";
                } elseif ($_GET['condition'] === 'used') {
                    $conditions[] = "mileage_km > 100";
                }
            }
            
            // 7. Price Min/Max (USD)
            if (isset($_GET['price_min']) && $_GET['price_min'] !== '') {
                $conditions[] = "fob_price >= :price_min";
                $params['price_min'] = (float)$_GET['price_min'];
            }
            if (isset($_GET['price_max']) && $_GET['price_max'] !== '') {
                $conditions[] = "fob_price <= :price_max";
                $params['price_max'] = (float)$_GET['price_max'];
            }
            
            // 8. Year Min/Max
            if (isset($_GET['year_min']) && $_GET['year_min'] !== '') {
                $conditions[] = "year >= :year_min";
                $params['year_min'] = (int)$_GET['year_min'];
            }
            if (isset($_GET['year_max']) && $_GET['year_max'] !== '') {
                $conditions[] = "year <= :year_max";
                $params['year_max'] = (int)$_GET['year_max'];
            }
            
            // 9. Engine CC Min/Max
            if (isset($_GET['engine_cc_min']) && $_GET['engine_cc_min'] !== '') {
                $conditions[] = "engine_cc >= :engine_cc_min";
                $params['engine_cc_min'] = (int)$_GET['engine_cc_min'];
            }
            if (isset($_GET['engine_cc_max']) && $_GET['engine_cc_max'] !== '') {
                $conditions[] = "engine_cc <= :engine_cc_max";
                $params['engine_cc_max'] = (int)$_GET['engine_cc_max'];
            }
            
            // 10. Mileage Min/Max (UI scale: 0 to 300 representing thousands of km)
            if (isset($_GET['mileage_min']) && $_GET['mileage_min'] !== '') {
                $conditions[] = "mileage_km >= :mileage_min";
                $params['mileage_min'] = (int)$_GET['mileage_min'] * 1000;
            }
            if (isset($_GET['mileage_max']) && $_GET['mileage_max'] !== '') {
                $conditions[] = "mileage_km <= :mileage_max";
                $params['mileage_max'] = (int)$_GET['mileage_max'] * 1000;
            }
            
            // Query construction
            $whereClause = implode(' AND ', $conditions);
            
            // Get total count
            $countSql = "SELECT COUNT(*) FROM vehicles WHERE {$whereClause}";
            $countStmt = $db->prepare($countSql);
            $countStmt->execute($params);
            $total = (int)$countStmt->fetchColumn();
            
            // Pagination settings
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $perPage = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 9;
            if ($page < 1) $page = 1;
            if ($perPage < 1) $perPage = 9;
            $lastPage = ceil($total / $perPage);
            if ($lastPage < 1) $lastPage = 1;
            if ($page > $lastPage) $page = $lastPage;
            
            $offset = ($page - 1) * $perPage;
            
            // Fetch rows with optimized subquery to avoid N+1 query loop
            $dataSql = "SELECT v.*, 
                               (SELECT image_url FROM vehicle_images WHERE vehicle_id = v.id ORDER BY sort_order ASC LIMIT 1) AS image_url 
                        FROM vehicles v 
                        WHERE {$whereClause} 
                        ORDER BY v.id DESC 
                        LIMIT {$perPage} OFFSET {$offset}";
            $dataStmt = $db->prepare($dataSql);
            $dataStmt->execute($params);
            $cars = $dataStmt->fetchAll(PDO::FETCH_ASSOC);

            // Fetch logged-in user's favorites to tag listing items
            $userFavoriteIds = [];
            $userId = Session::isLoggedIn() ? Session::get('user_id') : null;
            if ($userId) {
                $favStmt = $db->prepare("SELECT vehicle_id FROM vehicle_favorites WHERE user_id = ?");
                $favStmt->execute([$userId]);
                $userFavoriteIds = array_map('intval', $favStmt->fetchAll(PDO::FETCH_COLUMN));
            }
            
            $result = [];
            foreach ($cars as $car) {
                $imgUrl = $car['image_url'];
                
                if (empty($imgUrl)) {
                    $imageSrc = 'https://images.unsplash.com/photo-1549317661-bd32c8ce0db2?w=600&q=80';
                } elseif (strpos($imgUrl, 'http') === 0) {
                    $imageSrc = $imgUrl;
                } elseif (strpos($imgUrl, '/') === 0) {
                    $imageSrc = BASE_URL . $imgUrl;
                } else {
                    $imageSrc = "https://images.unsplash.com/{$imgUrl}?w=600&q=80";
                }
                
                $result[] = [
                    'id' => (int)$car['id'],
                    'stockId' => $car['stock_id'],
                    'make' => $car['make'],
                    'model' => $car['model'],
                    'modelDisplay' => VehicleDisplay::modelWithGrade($car['model'], $car['car_grade'] ?? ''),
                    'carGrade' => trim((string) ($car['car_grade'] ?? '')),
                    'year' => (int)$car['year'],
                    'priceUsd' => (float)$car['fob_price'],
                    'mileageK' => (float)($car['mileage_km'] / 1000),
                    'location' => VehicleDisplay::location($car['location'] ?? ''),
                    'image' => $imageSrc,
                    'alt' => $car['make'] . ' ' . VehicleDisplay::modelWithGrade($car['model'], $car['car_grade'] ?? ''),
                    'isFavorited' => in_array((int)$car['id'], $userFavoriteIds, true)
                ];
            }
            
            echo json_encode([
                'total' => $total,
                'per_page' => $perPage,
                'current_page' => $page,
                'last_page' => $lastPage,
                'data' => $result
            ]);
            
        } catch (\Exception $e) {
            error_log('Listing API error: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                'error' => true,
                'message' => 'Unable to load listings. Please try again later.'
            ]);
        }
    }
}
