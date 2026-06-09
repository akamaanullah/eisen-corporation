<?php
namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Session;
use App\Helpers\AdminStats;

class AdminController extends Controller {
    
    public function __construct() {
        if (!Session::isAdminLoggedIn()) {
            $this->redirect('/admin/login');
        }
    }

    protected function view($view, $data = []) {
        if (!array_key_exists('auctionAlertsTotal', $data)) {
            $data['auctionAlertsTotal'] = AdminStats::countAuctionEndingSoon();
        }

        $alertViews = ['admin/index', 'admin/inventory-auction-ending'];
        if (!array_key_exists('auctionAlerts', $data) && in_array($view, $alertViews, true)) {
            $data['auctionAlerts'] = AdminStats::getAuctionEndAlerts();
        }

        parent::view($view, $data);
    }
}
