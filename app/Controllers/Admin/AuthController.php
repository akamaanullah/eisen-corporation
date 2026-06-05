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



    public function showForgotPasswordForm() {
        $flash = Session::getFlash();
        $this->view('admin/forgot-password', [
            'flash' => $flash,
        ]);
    }

    public function sendForgotPassword() {
        $email = trim($_POST['email'] ?? '');

        if ($email === '') {
            Session::setFlash('error', 'Please enter your email address.');
            $this->redirect('/admin/forgot-password');
            return;
        }

        try {
            $db = Database::getConnection();
            $stmt = $db->prepare("SELECT id, role FROM users WHERE email = ? LIMIT 1");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            // Only proceed if user exists and is an admin/staff (don't reveal if email not found)
            if ($user && in_array($user['role'], ['admin', 'finance_officer', 'caller_agent'])) {
                $token = bin2hex(random_bytes(32));
                $expires = date('Y-m-d H:i:s', time() + 3600); // 1 hour expiry

                $upd = $db->prepare("UPDATE users SET reset_token = ?, reset_token_expires = ? WHERE id = ?");
                $upd->execute([$token, $expires, $user['id']]);

                Mailer::sendPasswordReset($email, BASE_URL . '/admin/reset-password?token=' . urlencode($token));
            }
        } catch (\Exception $e) {
            error_log('Forgot password error: ' . $e->getMessage());
        }

        // Always show success to prevent email enumeration
        Session::setFlash('success', 'If an account exists for that email, a reset link has been sent.');
        $this->redirect('/admin/forgot-password');
    }

    public function showResetPasswordForm() {
        $token = trim($_GET['token'] ?? '');
        $flash = Session::getFlash();

        if ($token === '') {
            $this->redirect('/admin/forgot-password');
            return;
        }

        // Validate token exists and is not expired
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare("SELECT id FROM users WHERE reset_token = ? AND reset_token_expires > NOW() LIMIT 1");
            $stmt->execute([$token]);
            $user = $stmt->fetch();

            if (!$user) {
                Session::setFlash('error', 'This password reset link is invalid or has expired.');
                $this->redirect('/admin/forgot-password');
                return;
            }
        } catch (\Exception $e) {
            error_log('Reset password validation error: ' . $e->getMessage());
            $this->redirect('/admin/forgot-password');
            return;
        }

        $this->view('admin/reset-password', [
            'flash' => $flash,
            'token' => htmlspecialchars($token),
        ]);
    }

    public function resetPassword() {
        $token = trim($_POST['token'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if ($token === '' || $password === '' || $confirmPassword === '') {
            Session::setFlash('error', 'All fields are required.');
            $this->redirect('/admin/reset-password?token=' . urlencode($token));
            return;
        }

        if ($password !== $confirmPassword) {
            Session::setFlash('error', 'Passwords do not match.');
            $this->redirect('/admin/reset-password?token=' . urlencode($token));
            return;
        }

        if (strlen($password) < 8) {
            Session::setFlash('error', 'Password must be at least 8 characters long.');
            $this->redirect('/admin/reset-password?token=' . urlencode($token));
            return;
        }

        try {
            $db = Database::getConnection();
            $stmt = $db->prepare("SELECT id FROM users WHERE reset_token = ? AND reset_token_expires > NOW() LIMIT 1");
            $stmt->execute([$token]);
            $user = $stmt->fetch();

            if (!$user) {
                Session::setFlash('error', 'This password reset link is invalid or has expired.');
                $this->redirect('/admin/forgot-password');
                return;
            }

            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
            $upd = $db->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_token_expires = NULL WHERE id = ?");
            $upd->execute([$hashedPassword, $user['id']]);

            Session::setFlash('success', 'Password reset successfully! You can now sign in with your new password.');
            $this->redirect('/admin/login');
        } catch (\Exception $e) {
            error_log('Reset password error: ' . $e->getMessage());
            Session::setFlash('error', 'An error occurred. Please try again.');
            $this->redirect('/admin/reset-password?token=' . urlencode($token));
        }
    }

    public function logout() {
        Session::destroy();
        $this->redirect('/admin/login');
    }
}
