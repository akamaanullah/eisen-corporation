<?php
namespace App\Controllers\Admin;

use App\Helpers\AdminStats;

class DashboardController extends AdminController {
    
    public function index() {
        $stats = AdminStats::getDashboardStats();

        $this->view('admin/index', [
            'pageTitle' => 'Dashboard | Eisen Admin',
            'pageScript' => 'dashboard.js',
            'stats' => $stats,
            'auctionAlerts' => $stats['auction_alerts'] ?? [],
        ]);
    }
}
