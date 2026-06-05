<?php
// public/index.php

// Load Composer Autoloader
require_once dirname(__DIR__) . '/vendor/autoload.php';

// 1. PSR-4 Class Autoloader
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $base_dir = dirname(__DIR__) . '/app/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return; 
    }

    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});

// 2. Load Configuration
require_once dirname(__DIR__) . '/config/config.php';

// 3. Initialize Core Session
use App\Core\Router;
use App\Core\Session;

Session::init();

// 4. Router Initialization
$router = new Router();

// 4. Frontstore Routes
$router->get('/', 'Front\HomeController@index');
$router->get('/listing', 'Front\ListingController@index');
$router->get('/listings', 'Front\ListingController@index');
$router->get('/product', 'Front\ProductController@show');
$router->get('/product/{id}', 'Front\ProductController@show');
$router->get('/blog', 'Front\BlogController@index');
$router->get('/blog/{slug}', 'Front\BlogController@show');
$router->get('/about', 'Front\AboutController@index');
$router->get('/contact', 'Front\ContactController@index');
$router->get('/contacts', 'Front\ContactController@index');
$router->get('/privacy-policy', 'Front\LegalController@privacy');
$router->get('/terms-and-condition', 'Front\LegalController@terms');
$router->get('/why-choose-eisen', 'Front\LegalController@whyChooseEisen');
$router->get('/chassis-check', 'Front\ChassisController@index');
$router->get('/price-calculation', 'Front\PriceCalculationController@index');
$router->get('/faq/{slug}', 'Front\FaqController@index');
$router->get('/faq', 'Front\FaqController@index');
$router->get('/api/listings', 'Front\ListingController@api');
$router->get('/account', 'Front\AccountController@index');
$router->post('/account/profile', 'Front\AccountController@updateProfile');
$router->post('/account/consignee', 'Front\AccountController@updateConsignee');
$router->post('/account/favorites/remove', 'Front\AccountController@removeFavorite');

// 5. User Authentication Routes
$router->get('/login', 'Front\UserAuthController@showLoginForm');
$router->post('/login', 'Front\UserAuthController@login');
$router->get('/forgot-password', 'Front\UserAuthController@showForgotPasswordForm');
$router->post('/forgot-password', 'Front\UserAuthController@sendForgotPassword');
$router->get('/reset-password', 'Front\UserAuthController@showResetPasswordForm');
$router->post('/reset-password', 'Front\UserAuthController@resetPassword');
$router->post('/signup/send-otp', 'Front\UserAuthController@sendOtp');
$router->post('/signup/verify-otp', 'Front\UserAuthController@verifyOtp');
$router->post('/signup/complete', 'Front\UserAuthController@completeSignup');
$router->get('/auth/google', 'Front\UserAuthController@googleLogin');
$router->get('/auth/google/callback', 'Front\UserAuthController@googleCallback');
$router->get('/logout', 'Front\UserAuthController@logout');

// 6. Admin UI Routes
$router->get('/admin', 'Admin\DashboardController@index');
$router->get('/admin/login', 'Admin\AuthController@showLoginForm');
$router->post('/admin/login', 'Admin\AuthController@login');
$router->get('/admin/forgot-password', 'Admin\AuthController@showForgotPasswordForm');
$router->post('/admin/forgot-password', 'Admin\AuthController@sendForgotPassword');
$router->get('/admin/reset-password', 'Admin\AuthController@showResetPasswordForm');
$router->post('/admin/reset-password', 'Admin\AuthController@resetPassword');
$router->get('/admin/logout', 'Admin\AuthController@logout');
$router->get('/admin/inventory', 'Admin\InventoryController@index');
$router->get('/admin/inventory/new', 'Admin\InventoryController@create');
$router->post('/admin/inventory/new', 'Admin\InventoryController@store');
$router->get('/admin/inventory/edit/{id}', 'Admin\InventoryController@edit');
$router->post('/admin/inventory/edit/{id}', 'Admin\InventoryController@update');
$router->post('/admin/inventory/delete/{id}', 'Admin\InventoryController@delete');
$router->post('/admin/inventory/toggle-featured/{id}', 'Admin\InventoryController@toggleFeatured');
$router->get('/admin/bids', 'Admin\BidController@index');
$router->get('/admin/reservations', 'Admin\ReservationController@index');
$router->get('/admin/customers', 'Admin\CustomerController@index');
$router->get('/admin/customers/detail', 'Admin\CustomerController@detail'); // Standard detail view fallback for UI
$router->get('/admin/shipping', 'Admin\ShippingController@index');
$router->get('/admin/reports', 'Admin\ReportController@index');

// 6. Static pages (no controller)
$url = isset($_GET['url']) ? rtrim($_GET['url'], '/') : '';
$url = ($url === '') ? '/' : '/' . ltrim($url, '/');
if ($url === '/account-guide') {
    require VIEW_DIR . '/front/account-guide.php';
    exit;
}

// 7. Dispatch Request
$router->dispatch($url);
