<?php
// config/config.php

// Set local timezone
date_default_timezone_set('Asia/Tokyo');

ini_set('default_charset', 'UTF-8');
if (function_exists('mb_internal_encoding')) {
    mb_internal_encoding('UTF-8');
}

// Base URLs
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$scriptName = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
// Remove /public if we are already in the public folder to avoid duplication
$basePath = str_replace('/public', '', $scriptName);
$dynamicBaseUrl = $protocol . "://" . $host . $basePath;

// Strip trailing slash if any
$dynamicBaseUrl = rtrim($dynamicBaseUrl, '/');

define('BASE_URL', $dynamicBaseUrl);
define('ASSET_URL', BASE_URL . '/public/assets');

// Directories
define('ROOT_DIR', dirname(__DIR__));
define('APP_DIR', ROOT_DIR . '/app');
define('VIEW_DIR', ROOT_DIR . '/views');

// Application environment: development | production
if (!defined('APP_ENV')) {
    define('APP_ENV', 'development');
}

// Load local configurations / secrets if they exist
if (file_exists(__DIR__ . '/config_local.php')) {
    require_once __DIR__ . '/config_local.php';
}

// Database Configuration
if (!defined('DB_HOST')) define('DB_HOST', 'localhost');
if (!defined('DB_USER')) define('DB_USER', 'root');
if (!defined('DB_PASS')) define('DB_PASS', '');
if (!defined('DB_NAME')) define('DB_NAME', 'eisen_db');

// SMTP Settings Fallbacks
if (!defined('SMTP_HOST')) define('SMTP_HOST', 'smtp.gmail.com');
if (!defined('SMTP_PORT')) define('SMTP_PORT', 587);
if (!defined('SMTP_USER')) define('SMTP_USER', 'your_smtp_email@gmail.com');
if (!defined('SMTP_PASS')) define('SMTP_PASS', 'your_smtp_app_password');
if (!defined('SMTP_SECURE')) define('SMTP_SECURE', 'tls');
if (!defined('SMTP_FROM_EMAIL')) define('SMTP_FROM_EMAIL', 'your_smtp_email@gmail.com');
if (!defined('SMTP_FROM_NAME')) define('SMTP_FROM_NAME', 'Eisen Corporation');

// Google OAuth Configuration
if (!defined('GOOGLE_CLIENT_ID')) define('GOOGLE_CLIENT_ID', 'YOUR_GOOGLE_CLIENT_ID');
if (!defined('GOOGLE_CLIENT_SECRET')) define('GOOGLE_CLIENT_SECRET', 'YOUR_GOOGLE_CLIENT_SECRET');
define('GOOGLE_REDIRECT_URI', BASE_URL . '/auth/google/callback');
