<?php
namespace App\Controllers\Front;

use App\Core\Controller;
use App\Core\Database;
use App\Helpers\VehicleDisplay;
use PDO;

class ProductController extends Controller {
    public function show($id = null) {
        // Fallback file path
        $fallbackPath = dirname(__DIR__, 2) . '/Data/product_detail.php';

        // Helper to fetch recommendations safely
        $getRecommendations = function($excludeId = null) {
            try {
                $db = Database::getConnection();
                if ($excludeId !== null) {
                    $recStmt = $db->prepare("SELECT v.*, (SELECT image_url FROM vehicle_images WHERE vehicle_id = v.id ORDER BY sort_order ASC LIMIT 1) as main_image FROM vehicles v WHERE v.status = 'Available' AND v.id != ? LIMIT 3");
                    $recStmt->execute([$excludeId]);
                } else {
                    $recStmt = $db->query("SELECT v.*, (SELECT image_url FROM vehicle_images WHERE vehicle_id = v.id ORDER BY sort_order ASC LIMIT 1) as main_image FROM vehicles v WHERE v.status = 'Available' LIMIT 3");
                }
                $recs = $recStmt->fetchAll(PDO::FETCH_ASSOC);
                $recommendations = [];
                foreach ($recs as $rec) {
                    $recommendations[] = [
                        'id' => $rec['id'],
                        'stockId' => $rec['stock_id'],
                        'title' => VehicleDisplay::title((int) $rec['year'], $rec['make'], $rec['model'], $rec['car_grade'] ?? ''),
                        'priceJpy' => (int)(!empty($rec['price_jpy']) && (float)$rec['price_jpy'] > 0 ? $rec['price_jpy'] : ($rec['fob_price'] * 150)),
                        'priceUsd' => (float)$rec['fob_price'],
                        'mileageKm' => $rec['mileage_km'],
                        'location' => VehicleDisplay::location($rec['location']),
                        'image' => $rec['main_image'] ?: '/public/image/car-placeholder.png',
                    ];
                }
                return $recommendations;
            } catch (\Exception $e) {
                return [];
            }
        };

        if ($id === null) {
            $detail = require $fallbackPath;
            $this->view('front/product', array_merge($detail, [
                'id' => $id,
                'recommendations' => $getRecommendations()
            ]));
            return;
        }

        try {
            $db = Database::getConnection();
            
            // Try fetching by stock_id or auto-increment id
            $stmt = $db->prepare("SELECT * FROM vehicles WHERE stock_id = ? OR id = ? LIMIT 1");
            $stmt->execute([$id, (int)$id]);
            $car = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$car) {
                // If not found, fall back to mock data
                $detail = require $fallbackPath;
                $this->view('front/product', array_merge($detail, [
                    'id' => $id,
                    'recommendations' => $getRecommendations()
                ]));
                return;
            }

            // Calculate reviews count and average rating
            $reviewsStmt = $db->prepare("SELECT COUNT(*) FROM vehicle_reviews WHERE vehicle_id = ?");
            $reviewsStmt->execute([$car['id']]);
            $reviewsCount = (int)$reviewsStmt->fetchColumn();

            $ratingStmt = $db->prepare("SELECT AVG(rating) FROM vehicle_reviews WHERE vehicle_id = ?");
            $ratingStmt->execute([$car['id']]);
            $avgRating = $ratingStmt->fetchColumn();
            $avgRating = $avgRating ? round((float)$avgRating, 1) : 5.0; // default to 5.0 if no reviews

            $stars = str_repeat('★', (int)round($avgRating)) . str_repeat('☆', 5 - (int)round($avgRating));

            // Calculate favorites count
            $favStmt = $db->prepare("SELECT COUNT(*) FROM vehicle_favorites WHERE vehicle_id = ?");
            $favStmt->execute([$car['id']]);
            $favoritesCount = (int)$favStmt->fetchColumn();

            // Increment views in DB
            $db->prepare("UPDATE vehicles SET views = views + 1 WHERE id = ?")->execute([$car['id']]);
            $viewsCount = (int)$car['views'] + 1;

            $modelDisplay = VehicleDisplay::modelWithGrade($car['model'], $car['car_grade'] ?? '');

            // Map database columns to the frontend keys
            $vehicle = [
                'id' => $car['id'],
                'stockId' => $car['stock_id'],
                'location' => $car['location'],
                'locationDisplay' => VehicleDisplay::location($car['location']),
                'title' => VehicleDisplay::title((int) $car['year'], $car['make'], $car['model'], $car['car_grade'] ?? ''),
                'model' => $car['model'],
                'modelDisplay' => $modelDisplay,
                'carGrade' => trim((string) ($car['car_grade'] ?? '')),
                'modelCode' => $car['chassis_number'],
                'year' => $car['year'],
                'manufactureYear' => $car['year'],
                'bodyType' => $car['body_type'],
                'priceJpy' => (int)(!empty($car['price_jpy']) && (float)$car['price_jpy'] > 0 ? $car['price_jpy'] : ($car['fob_price'] * 150)), // convert to JPY using a standard export rate of 150 or use manual JPY price if set
                'priceUsd' => (float)$car['fob_price'],
                'priceMode' => 'fob',
                'reviews' => $reviewsCount,
                'views' => $viewsCount,
                'favorites' => $favoritesCount,
                'rating' => $avgRating,
                'stars' => $stars,
                'description' => $car['description'],
                'mileageKm' => $car['mileage_km'],
                'engineCc' => $car['engine_cc'],
                'mileageDisplay' => VehicleDisplay::mileageKm((int) $car['mileage_km']),
                'engineDisplay' => VehicleDisplay::engineCc((int) $car['engine_cc']),
                'transmission' => $car['transmission'],
                'transmissionDisplay' => VehicleDisplay::transmission($car['transmission']),
                'drive' => $car['drive_type'],
                'driveDisplay' => VehicleDisplay::drive($car['drive_type']),
                'steering' => $car['steering'],
                'steeringDisplay' => VehicleDisplay::steering($car['steering']),
                'fuel' => $car['fuel'],
                'fuelDisplay' => VehicleDisplay::upperText($car['fuel']),
                'doors' => $car['doors'],
                'seats' => $car['seats'],
                'doorsDisplay' => VehicleDisplay::count((int) $car['doors']),
                'seatsDisplay' => VehicleDisplay::count((int) $car['seats']),
            ];

            $isFavorited = false;
            if (\App\Core\Session::get('is_logged_in') === true) {
                $isFavorited = \App\Models\Vehicle::isFavorited(\App\Core\Session::get('user_id'), $car['id']);
            }

            // Fetch images
            $imgStmt = $db->prepare("SELECT image_url FROM vehicle_images WHERE vehicle_id = ? ORDER BY sort_order ASC");
            $imgStmt->execute([$car['id']]);
            $dbImages = $imgStmt->fetchAll(PDO::FETCH_COLUMN);

            $gallery = [];
            if (!empty($dbImages)) {
                foreach ($dbImages as $index => $imgUrl) {
                    $gallery[] = [
                        'label' => $index === 0 ? 'Front exterior' : 'Vehicle view ' . ($index + 1),
                        'src' => $imgUrl
                    ];
                }
            } else {
                // Fallback to local vehicle silhouette placeholder if no images uploaded
                $gallery = [
                    ['label' => 'Main View', 'src' => '/public/image/car-placeholder.png']
                ];
            }

            // Create vehicle details list
            $vehicleDetails = [
                ['label' => 'Make', 'value' => strtoupper($car['make'])],
                ['label' => 'Model', 'value' => $modelDisplay],
                ['label' => 'Body color', 'value' => VehicleDisplay::upperText($car['color'])],
                ['label' => 'Body type', 'value' => VehicleDisplay::text($car['body_type'])],
                ['label' => 'Doors', 'value' => VehicleDisplay::count((int) $car['doors'])],
                ['label' => 'Seats', 'value' => VehicleDisplay::count((int) $car['seats'])],
            ];

            // Specifications
            $specifications = [
                ['label' => 'Dimension', 'value' => VehicleDisplay::dimension($car['dimension'])],
                ['label' => 'M3', 'value' => VehicleDisplay::cubicMeters($car['m3'])],
                ['label' => 'Transmission', 'value' => VehicleDisplay::transmission($car['transmission'])],
                ['label' => 'Drive Type', 'value' => VehicleDisplay::drive($car['drive_type'])],
                ['label' => 'Steering', 'value' => VehicleDisplay::steering($car['steering'])],
                ['label' => 'Fuel', 'value' => VehicleDisplay::upperText($car['fuel'])],
            ];

            // Fetch only options selected for this vehicle
            $optStmt = $db->prepare("
                SELECT o.label, o.category
                FROM options o
                INNER JOIN vehicle_options vo ON vo.option_id = o.id
                WHERE vo.vehicle_id = ?
                ORDER BY o.category, o.label
            ");
            $optStmt->execute([$car['id']]);
            $vehicleOptions = $optStmt->fetchAll(PDO::FETCH_ASSOC);

            $optionGroups = [];
            foreach ($vehicleOptions as $opt) {
                $category = $opt['category'];
                if (!isset($optionGroups[$category])) {
                    $optionGroups[$category] = [
                        'title' => $category,
                        'i18n' => 'product.options.' . match($category) {
                            'Comfort & Convenience' => 'comfort',
                            'Dress Up' => 'dressUp',
                            'Exterior' => 'exterior',
                            'Safety' => 'safety',
                            default => 'other'
                        },
                        'items' => []
                    ];
                }
                $optionGroups[$category]['items'][] = [
                    'label' => $opt['label'],
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

            // Fetch shipping destinations
            $destCountries = [];
            $destPorts = [];
            $countryToPorts = [];
            try {
                $destStmt = $db->query("SELECT * FROM shipping_destinations WHERE status = 1 ORDER BY country ASC, port ASC");
                $destData = $destStmt->fetchAll(PDO::FETCH_ASSOC);
                
                foreach ($destData as $row) {
                    $country = strtoupper($row['country']);
                    $port = strtoupper($row['port']);
                    
                    if (!in_array($country, $destCountries)) {
                        $destCountries[] = $country;
                    }
                    if (!in_array($port, $destPorts)) {
                        $destPorts[] = $port;
                    }
                    if (!isset($countryToPorts[$country])) {
                        $countryToPorts[$country] = [];
                    }
                    $countryToPorts[$country][] = $port;
                }
            } catch (\Exception $ex) {
                $destCountries = ['PAKISTAN', 'KENYA', 'TANZANIA', 'BANGLADESH', 'SRI LANKA'];
                $destPorts = ['ISLAMABAD', 'KARACHI', 'MOMBASA', 'DAR ES SALAAM'];
                $countryToPorts = [
                    'PAKISTAN' => ['KARACHI', 'ISLAMABAD'],
                    'KENYA' => ['MOMBASA'],
                    'TANZANIA' => ['DAR ES SALAAM']
                ];
            }

            $estimate = [
                'countries' => $destCountries,
                'ports' => $destPorts,
                'countryToPorts' => $countryToPorts,
                'shipments' => ['roro', 'container'],
            ];

            // Pricing Breakdown in USD — live JPY conversion is handled client-side by currency.js
            // Storing static price_jpy (fob_price × 150) was incorrect because the live rate fluctuates.
            $pricingBreakdown = [
                ['label' => 'Vehicle Price',    'i18n' => 'product.pricing.vehicle',    'usd' => (float)$car['fob_price']],
                ['label' => 'Freight Amount',   'i18n' => 'product.pricing.freight',    'usd' => (float)$car['freight_price']],
                ['label' => 'Vanning Amount',   'i18n' => 'product.pricing.vanning',    'usd' => (float)$car['vanning_price']],
                ['label' => 'Inspection Amount','i18n' => 'product.pricing.inspection', 'usd' => (float)$car['inspection_price']],
                ['label' => 'Insurance Amount', 'i18n' => 'product.pricing.insurance',  'usd' => (float)$car['insurance_price']],
                ['label' => 'Coupon',           'i18n' => 'product.pricing.coupon',     'usd' => 0.0],
            ];

            $this->view('front/product', [
                'id' => $id,
                'vehicle' => $vehicle,
                'gallery' => $gallery,
                'vehicleDetails' => $vehicleDetails,
                'specifications' => $specifications,
                'optionGroups' => $optionGroups,
                'estimate' => $estimate,
                'pricingBreakdown' => $pricingBreakdown,
                'recommendations' => $getRecommendations($car['id']),
                'isFavorited' => $isFavorited
            ]);

        } catch (\Exception $e) {
            // Log error and fallback
            $detail = require $fallbackPath;
            $this->view('front/product', array_merge($detail, [
                'id' => $id,
                'recommendations' => $getRecommendations()
            ]));
        }
    }
}
