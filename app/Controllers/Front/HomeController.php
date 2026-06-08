<?php
namespace App\Controllers\Front;

use App\Core\Controller;
use App\Core\Database;
use App\Helpers\VehicleDisplay;
use PDO;

class HomeController extends Controller {
    public function index() {
        try {
            $db = Database::getConnection();
            
            // 1. Fetch available listings (limit 8)
            $stmt = $db->query("SELECT * FROM vehicles WHERE status = 'Available' ORDER BY id DESC LIMIT 8");
            $cars = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Load main thumbnail image for each vehicle
            foreach ($cars as &$car) {
                $imgStmt = $db->prepare("SELECT image_url FROM vehicle_images WHERE vehicle_id = ? ORDER BY sort_order ASC LIMIT 1");
                $imgStmt->execute([$car['id']]);
                $car['image_url'] = $imgStmt->fetchColumn();
                $car['display_model'] = VehicleDisplay::modelWithGrade($car['model'], $car['car_grade'] ?? '');
            }
        } catch (\Exception $e) {
            $cars = [];
        }

        // 2. Fetch dynamic hero sliders
        $sliders = [];
        try {
            $db = Database::getConnection();
            $slideStmt = $db->query("SELECT * FROM hero_sliders WHERE status = 1 ORDER BY sort_order ASC");
            $sliders = $slideStmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            // fallback
        }

        // 3. Fetch latest 5 blog posts (3 for main, 2 for sidebar)
        $posts = [];
        $sidebarPosts = [];
        try {
            $db = Database::getConnection();
            $blogStmt = $db->query("SELECT * FROM blog_posts ORDER BY published_date DESC LIMIT 5");
            $allPosts = $blogStmt->fetchAll(PDO::FETCH_ASSOC);
            // Format dates
            foreach ($allPosts as &$post) {
                $post['dateLabel'] = date('F j, Y', strtotime($post['published_date']));
            }
            $posts = array_slice($allPosts, 0, 3);
            $sidebarPosts = array_slice($allPosts, 3, 2);
        } catch (\Exception $e) {
            // fallback
        }

        // 4. Fetch directory partners
        $dealers = [];
        $services = [];
        $insurances = [];
        try {
            $db = Database::getConnection();
            $partnerStmt = $db->query("SELECT * FROM directory_partners ORDER BY sort_order ASC");
            $partners = $partnerStmt->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($partners as $p) {
                if ($p['type'] === 'dealer') {
                    $dealers[] = $p;
                } elseif ($p['type'] === 'service') {
                    $services[] = $p;
                } elseif ($p['type'] === 'insurance') {
                    $insurances[] = $p;
                }
            }
        } catch (\Exception $e) {
            // fallback
        }

        // 5. Fetch unique makes & model mapping from master table
        $makes = [];
        $makeToModels = [];
        try {
            $db = Database::getConnection();
            $mmStmt = $db->query("SELECT DISTINCT make FROM master_makes_models ORDER BY make ASC");
            $makes = $mmStmt->fetchAll(PDO::FETCH_COLUMN);

            $modelStmt = $db->query("SELECT * FROM master_makes_models ORDER BY model ASC");
            $modelsData = $modelStmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($modelsData as $row) {
                $makeKey = strtolower($row['make']);
                if (!isset($makeToModels[$makeKey])) {
                    $makeToModels[$makeKey] = [];
                }
                $makeToModels[$makeKey][] = [
                    'value' => strtolower($row['model']),
                    'label' => $row['model']
                ];
            }
        } catch (\Exception $e) {
            // fallback
        }

        $this->view('front/index', [
            'cars' => $cars,
            'sliders' => $sliders,
            'posts' => $posts,
            'sidebarPosts' => $sidebarPosts,
            'dealers' => $dealers,
            'services' => $services,
            'insurances' => $insurances,
            'makes' => $makes,
            'makeToModels' => $makeToModels
        ]);
    }
}

