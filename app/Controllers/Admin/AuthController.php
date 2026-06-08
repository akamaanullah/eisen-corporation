<?php
namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Session;
use App\Core\Database;
use App\Helpers\Mailer;

class AuthController extends Controller {
    
    public function showLoginForm() {
        $flash = Session::getFlash();
        $activeTab = ($_GET['tab'] ?? '') === 'signup' ? 'signup' : 'login';

        $this->view('admin/login', [
            'flash' => $flash,
            'activeTab' => $activeTab,
        ]);
    }

    public function login() {
        try {
            $this->validateCsrf();
        } catch (\Exception $e) {
            Session::setFlash('error', 'CSRF token validation failed. Please try again.');
            $this->redirect('/admin/login');
            return;
        }

        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($email === '' || $password === '') {
            Session::setFlash('error', 'Please fill in all fields.');
            $this->redirect('/admin/login');
            return;
        }

        // Rate limiting: max 10 attempts per 60 seconds
        $rateLimitKey = 'admin_login_' . md5($_SERVER['REMOTE_ADDR'] ?? 'unknown');
        if (!$this->checkRateLimit($rateLimitKey, 10, 60)) {
            Session::setFlash('error', 'Too many login attempts. Please wait a minute and try again.');
            $this->redirect('/admin/login');
            return;
        }

        try {
            $db = Database::getConnection();
            $stmt = $db->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if (!$user || !password_verify($password, $user['password'])) {
                Session::setFlash('error', 'Invalid email or password.');
                $this->redirect('/admin/login');
                return;
            }

            // Verify if user has an administrative/staff role
            if (!in_array($user['role'], ['admin', 'finance_officer', 'caller_agent'])) {
                Session::setFlash('error', 'Access denied. Unauthorized area.');
                $this->redirect('/admin/login');
                return;
            }

            // Harden session: regenerate ID and CSRF token to prevent fixation
            Session::regenerateId();
            Session::regenerateCsrfToken();

            Session::set('is_logged_in', true);
            Session::set('user_id', $user['id']);
            Session::set('user_role', $user['role']);
            Session::set('user_name', $user['name']);
            Session::set('user_email', $user['email']);

            $this->redirect('/admin');
        } catch (\Exception $e) {
            error_log('Admin login error: ' . $e->getMessage());
            Session::setFlash('error', 'An error occurred during sign in. Please try again.');
            $this->redirect('/admin/login');
        }
    }
    public function logout() {
        Session::destroy();
        $this->redirect('/admin/login');
    }
}
