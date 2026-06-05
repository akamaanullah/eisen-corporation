<?php
namespace App\Core;

class Session {
    public static function init() {
        if (session_status() == PHP_SESSION_NONE) {
            // Set secure session parameters
            ini_set('session.use_only_cookies', 1);
            ini_set('session.use_strict_mode', 1);
            ini_set('session.cookie_httponly', 1);
            ini_set('session.cookie_samesite', 'Lax');
            
            // Only use secure cookies if on HTTPS
            if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
                ini_set('session.cookie_secure', 1);
            }
            
            session_start();
        }
    }

    public static function set($key, $value) {
        $_SESSION[$key] = $value;
    }

    public static function get($key) {
        return $_SESSION[$key] ?? null;
    }

    public static function remove($key) {
        if (isset($_SESSION[$key])) {
            unset($_SESSION[$key]);
        }
    }

    public static function destroy() {
        session_unset();
        session_destroy();
    }
    
    // Flash messages (exist only for the next request)
    public static function setFlash($type, $message) {
        self::setFlashData('flash', [
            'type' => $type, // 'success', 'error', 'warning'
            'message' => $message
        ]);
    }
    
    public static function getFlash() {
        return self::getFlashData('flash');
    }

    /**
     * Set arbitrary data that will only be available for the next request.
     */
    public static function setFlashData($key, $value) {
        $_SESSION['flash_data'][$key] = $value;
    }

    /**
     * Get flash data and remove it so it's only read once.
     */
    public static function getFlashData($key) {
        if (isset($_SESSION['flash_data'][$key])) {
            $value = $_SESSION['flash_data'][$key];
            unset($_SESSION['flash_data'][$key]);
            return $value;
        }
        return null;
    }

    /**
     * Check if flash data exists without removing it.
     */
    public static function hasFlashData($key) {
        return isset($_SESSION['flash_data'][$key]);
    }

    public static function isLoggedIn() {
        return self::get('is_logged_in') === true;
    }

    public static function getUserFirstName() {
        $name = trim((string) self::get('user_name'));
        if ($name === '') {
            return '';
        }

        $parts = preg_split('/\s+/', $name);

        return $parts[0] ?? $name;
    }

    // Check if an admin is logged in
    public static function isAdminLoggedIn() {
        return (self::get('is_logged_in') === true && in_array(self::get('user_role'), ['admin', 'finance_officer', 'caller_agent']));
    }

    /**
     * Generate and store a CSRF token
     */
    public static function generateCsrfToken($force = false) {
        if ($force || empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    /**
     * Regenerate the CSRF token
     */
    public static function regenerateCsrfToken() {
        return self::generateCsrfToken(true);
    }

    /**
     * Get the current CSRF token
     */
    public static function getCsrfToken() {
        return $_SESSION['csrf_token'] ?? self::generateCsrfToken();
    }

    /**
     * Validate a submitted CSRF token
     */
    public static function validateCsrfToken($token) {
        $storedToken = self::getCsrfToken();
        return !empty($token) && hash_equals($storedToken, $token);
    }

    /**
     * Regenerate session ID safely
     */
    public static function regenerateId() {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
        }
    }
}
