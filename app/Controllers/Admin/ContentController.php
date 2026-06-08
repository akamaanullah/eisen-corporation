<?php
namespace App\Controllers\Admin;

use App\Core\Database;
use App\Core\Session;
use PDO;

class ContentController extends AdminController {

    public function index() {
        try {
            $db = Database::getConnection();

            // 1. Fetch sliders
            $sliderStmt = $db->query("SELECT * FROM hero_sliders ORDER BY sort_order ASC, id DESC");
            $sliders = $sliderStmt->fetchAll(PDO::FETCH_ASSOC);

            // 2. Fetch partners
            $partnerStmt = $db->query("SELECT * FROM directory_partners ORDER BY type ASC, sort_order ASC, id DESC");
            $partners = $partnerStmt->fetchAll(PDO::FETCH_ASSOC);

            // 3. Fetch shipping destinations
            $shippingStmt = $db->query("SELECT * FROM shipping_destinations ORDER BY country ASC, port ASC, id DESC");
            $shipping = $shippingStmt->fetchAll(PDO::FETCH_ASSOC);

            // 4. Fetch makes & models
            $makeModelStmt = $db->query("SELECT * FROM master_makes_models ORDER BY make ASC, model ASC, id DESC");
            $makeModels = $makeModelStmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (\Exception $e) {
            $sliders = [];
            $partners = [];
            $shipping = [];
            $makeModels = [];
            Session::setFlash('error', 'Database error: ' . $e->getMessage());
        }

        $this->view('admin/content-management', [
            'pageTitle' => 'Frontstore Content | Eisen Admin',
            'pageScript' => 'content-management.js',
            'sliders' => $sliders,
            'partners' => $partners,
            'shipping' => $shipping,
            'makeModels' => $makeModels
        ]);
    }

    public function saveSlider() {
        try {
            $this->validateCsrf();
        } catch (\Exception $e) {
            Session::setFlash('error', 'CSRF validation failed. Please try again.');
            $this->redirect('/admin/content');
        }

        try {
            $db = Database::getConnection();
            $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
            $title = trim($_POST['title'] ?? '');
            $subtitle = trim($_POST['subtitle'] ?? '');
            $link_url = trim($_POST['link_url'] ?? '');
            $sort_order = isset($_POST['sort_order']) ? (int)$_POST['sort_order'] : 0;
            $status = isset($_POST['status']) ? (int)$_POST['status'] : 1;

            $image_url = trim($_POST['image_url'] ?? '');

            // Handle file upload
            if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] === UPLOAD_ERR_OK) {
                $fileTmpPath = $_FILES['image_file']['tmp_name'];
                $fileName = $_FILES['image_file']['name'];
                $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

                $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
                if (!in_array($fileExtension, $allowedExtensions)) {
                    throw new \Exception('Invalid slide image file extension. Allowed: jpg, jpeg, png, webp.');
                }

                if ($_FILES['image_file']['size'] > 5 * 1024 * 1024) {
                    throw new \Exception('Slide image exceeds maximum allowed size (5MB).');
                }

                $uploadFileDir = ROOT_DIR . '/public/uploads/sliders/';
                if (!is_dir($uploadFileDir)) {
                    mkdir($uploadFileDir, 0755, true);
                }

                $newFileName = 'slide_' . time() . '_' . random_int(100, 999) . '.' . $fileExtension;
                $dest_path = $uploadFileDir . $newFileName;

                if (move_uploaded_file($fileTmpPath, $dest_path)) {
                    $image_url = '/public/uploads/sliders/' . $newFileName;
                } else {
                    throw new \Exception('Failed to move uploaded slide image file.');
                }
            }

            if (empty($image_url) && $id === 0) {
                throw new \Exception('Please select an image file or provide an image URL.');
            }

            if ($id > 0) {
                // Update
                if (empty($image_url)) {
                    // Retain existing image if not changing
                    $checkStmt = $db->prepare("SELECT image_url FROM hero_sliders WHERE id = ?");
                    $checkStmt->execute([$id]);
                    $image_url = $checkStmt->fetchColumn() ?: '';
                }

                $stmt = $db->prepare("
                    UPDATE hero_sliders 
                    SET image_url = ?, title = ?, subtitle = ?, link_url = ?, sort_order = ?, status = ?
                    WHERE id = ?
                ");
                $stmt->execute([$image_url, $title, $subtitle, $link_url, $sort_order, $status, $id]);
                Session::setFlash('success', 'Hero Slider updated successfully.');
            } else {
                // Insert
                $stmt = $db->prepare("
                    INSERT INTO hero_sliders (image_url, title, subtitle, link_url, sort_order, status)
                    VALUES (?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([$image_url, $title, $subtitle, $link_url, $sort_order, $status]);
                Session::setFlash('success', 'New Hero Slider added successfully.');
            }

        } catch (\Exception $e) {
            Session::setFlash('error', 'Error saving hero slider: ' . $e->getMessage());
        }

        $this->redirect('/admin/content');
    }

    public function deleteSlider($id) {
        try {
            $this->validateCsrf();
        } catch (\Exception $e) {
            Session::setFlash('error', 'CSRF validation failed.');
            $this->redirect('/admin/content');
        }

        try {
            $db = Database::getConnection();
            $stmt = $db->prepare("DELETE FROM hero_sliders WHERE id = ?");
            $stmt->execute([$id]);
            Session::setFlash('success', 'Hero Slider deleted successfully.');
        } catch (\Exception $e) {
            Session::setFlash('error', 'Error deleting slider: ' . $e->getMessage());
        }

        $this->redirect('/admin/content');
    }

    public function savePartner() {
        try {
            $this->validateCsrf();
        } catch (\Exception $e) {
            Session::setFlash('error', 'CSRF validation failed. Please try again.');
            $this->redirect('/admin/content');
        }

        try {
            $db = Database::getConnection();
            $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
            $name = trim($_POST['name'] ?? '');
            $type = trim($_POST['type'] ?? 'dealer');
            $sort_order = isset($_POST['sort_order']) ? (int)$_POST['sort_order'] : 0;

            if (!in_array($type, ['dealer', 'service', 'insurance'])) {
                $type = 'dealer';
            }

            $logo_url = trim($_POST['logo_url'] ?? '');

            // Handle logo upload
            if (isset($_FILES['logo_file']) && $_FILES['logo_file']['error'] === UPLOAD_ERR_OK) {
                $fileTmpPath = $_FILES['logo_file']['tmp_name'];
                $fileName = $_FILES['logo_file']['name'];
                $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

                $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
                if (!in_array($fileExtension, $allowedExtensions)) {
                    throw new \Exception('Invalid logo image file extension. Allowed: jpg, jpeg, png, webp.');
                }

                if ($_FILES['logo_file']['size'] > 2 * 1024 * 1024) {
                    throw new \Exception('Logo file exceeds maximum allowed size (2MB).');
                }

                $uploadFileDir = ROOT_DIR . '/public/uploads/partners/';
                if (!is_dir($uploadFileDir)) {
                    mkdir($uploadFileDir, 0755, true);
                }

                $newFileName = 'partner_' . time() . '_' . random_int(100, 999) . '.' . $fileExtension;
                $dest_path = $uploadFileDir . $newFileName;

                if (move_uploaded_file($fileTmpPath, $dest_path)) {
                    $logo_url = '/public/uploads/partners/' . $newFileName;
                } else {
                    throw new \Exception('Failed to move uploaded logo file.');
                }
            }

            if (empty($logo_url) && $id === 0) {
                throw new \Exception('Please select a logo file or provide a logo URL.');
            }

            if ($id > 0) {
                // Update
                if (empty($logo_url)) {
                    // Retain existing logo
                    $checkStmt = $db->prepare("SELECT logo_url FROM directory_partners WHERE id = ?");
                    $checkStmt->execute([$id]);
                    $logo_url = $checkStmt->fetchColumn() ?: '';
                }

                $stmt = $db->prepare("
                    UPDATE directory_partners 
                    SET name = ?, logo_url = ?, type = ?, sort_order = ?
                    WHERE id = ?
                ");
                $stmt->execute([$name, $logo_url, $type, $sort_order, $id]);
                Session::setFlash('success', 'Directory Partner updated successfully.');
            } else {
                // Insert
                $stmt = $db->prepare("
                    INSERT INTO directory_partners (name, logo_url, type, sort_order)
                    VALUES (?, ?, ?, ?)
                ");
                $stmt->execute([$name, $logo_url, $type, $sort_order]);
                Session::setFlash('success', 'New Directory Partner added successfully.');
            }

        } catch (\Exception $e) {
            Session::setFlash('error', 'Error saving directory partner: ' . $e->getMessage());
        }

        $this->redirect('/admin/content');
    }

    public function deletePartner($id) {
        try {
            $this->validateCsrf();
        } catch (\Exception $e) {
            Session::setFlash('error', 'CSRF validation failed.');
            $this->redirect('/admin/content');
        }

        try {
            $db = Database::getConnection();
            $stmt = $db->prepare("DELETE FROM directory_partners WHERE id = ?");
            $stmt->execute([$id]);
            Session::setFlash('success', 'Directory Partner deleted successfully.');
        } catch (\Exception $e) {
            Session::setFlash('error', 'Error deleting partner: ' . $e->getMessage());
        }

        $this->redirect('/admin/content');
    }

    public function saveShipping() {
        try {
            $this->validateCsrf();
        } catch (\Exception $e) {
            Session::setFlash('error', 'CSRF validation failed. Please try again.');
            $this->redirect('/admin/content');
        }

        try {
            $db = Database::getConnection();
            $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
            $country = strtoupper(trim($_POST['country'] ?? ''));
            $port = strtoupper(trim($_POST['port'] ?? ''));
            $status = isset($_POST['status']) ? (int)$_POST['status'] : 1;

            if (empty($country) || empty($port)) {
                throw new \Exception('Country and Port fields are required.');
            }

            if ($id > 0) {
                // Update
                $stmt = $db->prepare("
                    UPDATE shipping_destinations 
                    SET country = ?, port = ?, status = ?
                    WHERE id = ?
                ");
                $stmt->execute([$country, $port, $status, $id]);
                Session::setFlash('success', 'Shipping Destination updated successfully.');
            } else {
                // Insert
                $stmt = $db->prepare("
                    INSERT INTO shipping_destinations (country, port, status)
                    VALUES (?, ?, ?)
                ");
                $stmt->execute([$country, $port, $status]);
                Session::setFlash('success', 'New Shipping Destination added successfully.');
            }

        } catch (\Exception $e) {
            Session::setFlash('error', 'Error saving shipping destination: ' . $e->getMessage());
        }

        $this->redirect('/admin/content');
    }

    public function deleteShipping($id) {
        try {
            $this->validateCsrf();
        } catch (\Exception $e) {
            Session::setFlash('error', 'CSRF validation failed.');
            $this->redirect('/admin/content');
        }

        try {
            $db = Database::getConnection();
            $stmt = $db->prepare("DELETE FROM shipping_destinations WHERE id = ?");
            $stmt->execute([$id]);
            Session::setFlash('success', 'Shipping Destination deleted successfully.');
        } catch (\Exception $e) {
            Session::setFlash('error', 'Error deleting shipping destination: ' . $e->getMessage());
        }

        $this->redirect('/admin/content');
    }

    public function saveMakeModel() {
        try {
            $this->validateCsrf();
        } catch (\Exception $e) {
            Session::setFlash('error', 'CSRF validation failed. Please try again.');
            $this->redirect('/admin/content');
        }

        try {
            $db = Database::getConnection();
            $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
            
            // Format makes nicely (e.g. Toyota, Honda)
            $make = ucwords(strtolower(trim($_POST['make'] ?? '')));
            $model = trim($_POST['model'] ?? '');

            if (empty($make) || empty($model)) {
                throw new \Exception('Manufacturer (Make) and Model fields are required.');
            }

            if ($id > 0) {
                // Update
                $stmt = $db->prepare("
                    UPDATE master_makes_models 
                    SET make = ?, model = ?
                    WHERE id = ?
                ");
                $stmt->execute([$make, $model, $id]);
                Session::setFlash('success', 'Make/Model mapping updated successfully.');
            } else {
                // Insert
                $stmt = $db->prepare("
                    INSERT INTO master_makes_models (make, model)
                    VALUES (?, ?)
                ");
                $stmt->execute([$make, $model]);
                Session::setFlash('success', 'New Make/Model mapping added successfully.');
            }

        } catch (\Exception $e) {
            Session::setFlash('error', 'Error saving Make/Model mapping: ' . $e->getMessage());
        }

        $this->redirect('/admin/content');
    }

    public function deleteMakeModel($id) {
        try {
            $this->validateCsrf();
        } catch (\Exception $e) {
            Session::setFlash('error', 'CSRF validation failed.');
            $this->redirect('/admin/content');
        }

        try {
            $db = Database::getConnection();
            $stmt = $db->prepare("DELETE FROM master_makes_models WHERE id = ?");
            $stmt->execute([$id]);
            Session::setFlash('success', 'Make/Model mapping deleted successfully.');
        } catch (\Exception $e) {
            Session::setFlash('error', 'Error deleting Make/Model mapping: ' . $e->getMessage());
        }

        $this->redirect('/admin/content');
    }
}
