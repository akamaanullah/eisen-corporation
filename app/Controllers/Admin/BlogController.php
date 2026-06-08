<?php
namespace App\Controllers\Admin;

use App\Core\Database;
use App\Core\Session;
use App\Helpers\HtmlSanitizer;
use PDO;

class BlogController extends AdminController {

    public function index() {
        try {
            $db = Database::getConnection();
            $stmt = $db->query("SELECT * FROM blog_posts ORDER BY published_date DESC, id DESC");
            $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            $posts = [];
            Session::setFlash('error', 'Database error: ' . $e->getMessage());
        }

        $this->view('admin/blog-management', [
            'pageTitle' => 'Blog Management | Eisen Admin',
            'posts' => $posts
        ]);
    }

    public function create() {
        $this->view('admin/blog-form', [
            'pageTitle' => 'Create Blog Post | Eisen Admin',
            'post' => null
        ]);
    }

    public function edit($id) {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare("SELECT * FROM blog_posts WHERE id = ? LIMIT 1");
            $stmt->execute([$id]);
            $post = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$post) {
                Session::setFlash('error', 'Blog post not found.');
                $this->redirect('/admin/blog');
                return;
            }
        } catch (\Exception $e) {
            Session::setFlash('error', 'Error fetching blog post: ' . $e->getMessage());
            $this->redirect('/admin/blog');
            return;
        }

        $this->view('admin/blog-form', [
            'pageTitle' => 'Edit Blog Post | Eisen Admin',
            'post' => $post
        ]);
    }

    public function save() {
        try {
            $this->validateCsrf();
        } catch (\Exception $e) {
            Session::setFlash('error', 'CSRF validation failed.');
            $this->redirect('/admin/blog');
            return;
        }

        try {
            $db = Database::getConnection();

            $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
            $title = trim($_POST['title'] ?? '');
            $excerpt = trim($_POST['excerpt'] ?? '');
            $content = HtmlSanitizer::sanitizeBlogHtml(trim($_POST['content'] ?? ''));
            $category = trim($_POST['category'] ?? 'Buying Guides');
            $read_min = isset($_POST['read_min']) ? (int)$_POST['read_min'] : 5;
            $author = trim($_POST['author'] ?? 'Eisen Export Team');
            $published_date = trim($_POST['published_date'] ?? '');
            $custom_slug = trim($_POST['slug'] ?? '');

            if (empty($title) || empty($content)) {
                throw new \Exception('Title and Content are required fields.');
            }

            // Generate category_key based on category value
            $categoryMap = [
                'Japan Auctions' => 'auctions',
                'Import & Export' => 'export',
                'Buying Guides' => 'guides',
                'Market & Pricing' => 'market',
                'Vehicle Spotlights' => 'spotlights',
                'Company' => 'company'
            ];
            $category_key = $categoryMap[$category] ?? 'guides';

            // Establish slug
            $slug = !empty($custom_slug) ? $this->slugify($custom_slug) : $this->slugify($title);
            if (empty($slug)) {
                $slug = 'post-' . time();
            }

            // Check slug uniqueness (excluding current post if editing)
            $slugCheck = $db->prepare("SELECT id FROM blog_posts WHERE slug = ? AND id != ? LIMIT 1");
            $slugCheck->execute([$slug, $id]);
            if ($slugCheck->fetch()) {
                $slug .= '-' . time();
            }

            if (empty($published_date)) {
                $published_date = date('Y-m-d');
            }

            $image = trim($_POST['image_url'] ?? '');

            // Handle file upload for article banner image
            if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] === UPLOAD_ERR_OK) {
                \App\Helpers\UploadValidator::validateImageUpload($_FILES['image_file']);
                $fileTmpPath = $_FILES['image_file']['tmp_name'];
                $fileName = $_FILES['image_file']['name'];
                $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

                $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
                if (!in_array($fileExtension, $allowedExtensions)) {
                    throw new \Exception('Invalid image extension. Allowed: jpg, jpeg, png, webp.');
                }

                if ($_FILES['image_file']['size'] > 5 * 1024 * 1024) {
                    throw new \Exception('Banner image exceeds maximum allowed size (5MB).');
                }

                $uploadFileDir = ROOT_DIR . '/public/uploads/blog/';
                if (!is_dir($uploadFileDir)) {
                    mkdir($uploadFileDir, 0755, true);
                }

                $newFileName = 'blog_' . time() . '_' . random_int(100, 999) . '.' . $fileExtension;
                $dest_path = $uploadFileDir . $newFileName;

                if (move_uploaded_file($fileTmpPath, $dest_path)) {
                    $image = '/public/uploads/blog/' . $newFileName;
                } else {
                    throw new \Exception('Failed to move uploaded banner image file.');
                }
            }

            if ($id > 0) {
                // Update
                if (empty($image)) {
                    // Keep existing image
                    $checkStmt = $db->prepare("SELECT image FROM blog_posts WHERE id = ?");
                    $checkStmt->execute([$id]);
                    $image = $checkStmt->fetchColumn() ?: '';
                }

                $stmt = $db->prepare("
                    UPDATE blog_posts 
                    SET slug = ?, title = ?, excerpt = ?, content = ?, image = ?, category = ?, category_key = ?, read_min = ?, author = ?, published_date = ?
                    WHERE id = ?
                ");
                $stmt->execute([$slug, $title, $excerpt, $content, $image, $category, $category_key, $read_min, $author, $published_date, $id]);
                Session::setFlash('success', 'Blog post updated successfully.');
            } else {
                // Insert
                if (empty($image)) {
                    $image = 'photo-1618843479313-40f8afb4b4d8'; // fallback unsplash image key
                }

                $stmt = $db->prepare("
                    INSERT INTO blog_posts (slug, title, excerpt, content, image, category, category_key, read_min, author, published_date)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([$slug, $title, $excerpt, $content, $image, $category, $category_key, $read_min, $author, $published_date]);
                Session::setFlash('success', 'Blog post created successfully.');
            }

        } catch (\Exception $e) {
            Session::setFlash('error', 'Error saving blog post: ' . $e->getMessage());
            if ($id > 0) {
                $this->redirect('/admin/blog/edit/' . $id);
                return;
            } else {
                $this->redirect('/admin/blog/new');
                return;
            }
        }

        $this->redirect('/admin/blog');
    }

    public function delete($id) {
        try {
            $this->validateCsrf();
        } catch (\Exception $e) {
            Session::setFlash('error', 'CSRF validation failed.');
            $this->redirect('/admin/blog');
            return;
        }

        try {
            $db = Database::getConnection();
            $stmt = $db->prepare("DELETE FROM blog_posts WHERE id = ?");
            $stmt->execute([$id]);
            Session::setFlash('success', 'Blog post deleted successfully.');
        } catch (\Exception $e) {
            Session::setFlash('error', 'Error deleting blog post: ' . $e->getMessage());
        }

        $this->redirect('/admin/blog');
    }

    private function slugify($text) {
        // replace non letter or digits by -
        $text = preg_replace('~[^\pL\d]+~u', '-', $text);
        // transliterate
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
        // remove unwanted characters
        $text = preg_replace('~[^-\w]+~', '', $text);
        // trim
        $text = trim($text, '-');
        // remove duplicate -
        $text = preg_replace('~-+~', '-', $text);
        // lowercase
        $text = strtolower($text);
        return $text;
    }
}
