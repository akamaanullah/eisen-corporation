<?php
namespace App\Core;

class Controller {
    
    /**
     * Load security response headers
     */
    protected function sendSecurityHeaders() {
        if (!headers_sent()) {
            header("X-Frame-Options: SAMEORIGIN");
            header("X-Content-Type-Options: nosniff");
            header("Referrer-Policy: strict-origin-when-cross-origin");
            if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
                header("Strict-Transport-Security: max-age=31536000; includeSubDomains");
            }
        }
    }

    /**
     * Load a view file and pass data to it.
     * 
     * @param string $view Path to the view (e.g., 'front/home' or 'admin/dashboard')
     * @param array $data Data to extract and pass to the view
     */
    protected function view($view, $data = []) {
        $this->sendSecurityHeaders();
        if (!headers_sent()) {
            header('Content-Type: text/html; charset=UTF-8');
        }
        // Extract array keys into variables for the view
        extract($data);
        
        $viewFile = dirname(__DIR__, 2) . '/views/' . $view . '.php';
        
        if (file_exists($viewFile)) {
            require $viewFile;
        } else {
            throw new \Exception("View does not exist: " . $view);
        }
    }

    /**
     * Load a model instance
     * 
     * @param string $model Name of the model class
     * @return object
     */
    protected function model($model) {
        $modelClass = "App\\Models\\" . $model;
        if (class_exists($modelClass)) {
            return new $modelClass();
        } else {
            throw new \Exception("Model does not exist: " . $modelClass);
        }
    }

    /**
     * Redirect to a specific URL
     */
    protected function redirect($url) {
        header("Location: " . BASE_URL . $url);
        exit;
    }

    /**
     * Send a JSON response
     */
    protected function jsonResponse($data, $status = 200) {
        $this->sendSecurityHeaders();
        header('Content-Type: application/json');
        http_response_code($status);
        echo json_encode($data);
        exit;
    }

    /**
     * Generate a CSRF hidden input field for forms
     */
    protected function csrf_field() {
        $token = \App\Core\Session::getCsrfToken();
        return '<input type="hidden" name="csrf_token" value="' . $token . '">';
    }

    /**
     * Validate CSRF token from POST request
     */
    protected function validateCsrf() {
        if (in_array($_SERVER['REQUEST_METHOD'], ['POST', 'PUT', 'DELETE', 'PATCH'])) {
            $token = $_POST['csrf_token'] ?? '';
            
            if (empty($token)) {
                // Try various header formats
                $headers = function_exists('getallheaders') ? getallheaders() : [];
                $token = $headers['X-CSRF-TOKEN'] ?? $headers['x-csrf-token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
            }

            if (!\App\Core\Session::validateCsrfToken($token)) {
                throw new \Exception("CSRF token validation failed.", 403);
            }
        }
    }

    /**
     * Simple session-based IP / Action rate limiting
     */
    protected function checkRateLimit($key, $maxAttempts = 5, $decaySeconds = 60) {
        if (session_status() === PHP_SESSION_NONE) {
            \App\Core\Session::init();
        }
        $now = time();
        $attempts = $_SESSION["rate_limit_{$key}"] ?? [];
        
        // Remove expired timestamps
        $attempts = array_filter($attempts, function($time) use ($now, $decaySeconds) {
            return ($now - $time) < $decaySeconds;
        });
        
        if (count($attempts) >= $maxAttempts) {
            return false;
        }
        
        $attempts[] = $now;
        $_SESSION["rate_limit_{$key}"] = $attempts;
        return true;
    }
}
