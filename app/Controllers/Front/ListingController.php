<?php
namespace App\Controllers\Front;

use App\Core\Controller;
use App\Core\Database;
use PDO;

class ListingController extends Controller {
    public function index() {
        $this->view('front/listing');
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
                    $standardMakes = ['audi', 'bmw', 'daihatsu', 'ford', 'hino', 'honda', 'isuzu', 'lexus', 'mazda', 'mercedes', 'mitsubishi', 'nissan', 'porsche', 'subaru', 'suzuki', 'toyota', 'volkswagen', 'volvo'];
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
                    $standardModels = ['prius', 'aqua', 'corolla', 'camry', 'highlander', 'rav4', 'land-cruiser', 'alphard', 'hiace', 'fit', 'civic', 'cr-v', 'accord', 'vezel', 'note', 'leaf', 'x-trail', 'skyline', 'cx-5', 'demio', 'forester', 'impreza', 'swift', 'jimny', 'x5', '3-series', 'c-class', 'e-class', 'q5', 'a4'];
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
                    $excludeClause = "fuel NOT IN ('PETROL', 'HYBRID', 'ELECTRIC')";
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
                foreach ($trans as $t) {
                    $t = trim($t);
                    $paramName = 'trans_' . $idx++;
                    $dbTrans = (strtolower($t) === 'manual') ? 'MT' : 'AT';
                    $transConds[] = "transmission = :{$paramName}";
                    $params[$paramName] = $dbTrans;
                }
                if (!empty($transConds)) {
                    $conditions[] = "(" . implode(' OR ', $transConds) . ")";
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
                        ORDER BY v.featured DESC, v.id DESC 
                        LIMIT {$perPage} OFFSET {$offset}";
            $dataStmt = $db->prepare($dataSql);
            $dataStmt->execute($params);
            $cars = $dataStmt->fetchAll(PDO::FETCH_ASSOC);
            
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
                
                // Determine city key for UI translation helper
                $city = strtolower(explode(',', $car['location'])[0]);
                if (strpos($city, 'tokyo') !== false) {
                    $cityKey = 'tokyo';
                } elseif (strpos($city, 'osaka') !== false) {
                    $cityKey = 'osaka';
                } elseif (strpos($city, 'yokohama') !== false) {
                    $cityKey = 'yokohama';
                } elseif (strpos($city, 'nagoya') !== false) {
                    $cityKey = 'nagoya';
                } elseif (strpos($city, 'kobe') !== false) {
                    $cityKey = 'kobe';
                } else {
                    $cityKey = 'kobe';
                }
                
                $result[] = [
                    'id' => (int)$car['id'],
                    'stockId' => $car['stock_id'],
                    'make' => $car['make'],
                    'model' => $car['model'],
                    'year' => (int)$car['year'],
                    'priceUsd' => (float)$car['fob_price'],
                    'mileageK' => (float)($car['mileage_km'] / 1000),
                    'cityKey' => $cityKey,
                    'image' => $imageSrc,
                    'alt' => $car['make'] . ' ' . $car['model']
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
            http_response_code(500);
            echo json_encode([
                'error' => true,
                'message' => $e->getMessage()
            ]);
        }
    }
}
