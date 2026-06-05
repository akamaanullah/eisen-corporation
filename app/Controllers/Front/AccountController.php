<?php
namespace App\Controllers\Front;

use App\Core\Controller;
use App\Core\Session;
use App\Models\User;
use App\Models\Consignee;
use App\Models\Vehicle;

class AccountController extends Controller
{
    public function index()
    {
        if (!Session::isLoggedIn()) {
            $this->redirect('/login');
            return;
        }

        $userId = Session::get('user_id');
        if (!$userId) {
            // Fallback: search by email if session user_id is missing
            $u = User::findByEmail(Session::get('user_email'));
            $userId = $u ? $u['id'] : null;
            if ($userId) {
                Session::set('user_id', $userId);
            } else {
                $this->redirect('/logout');
                return;
            }
        }

        // Load static structure details
        $page = require dirname(__DIR__, 2) . '/Data/account_page.php';
        
        $activeTab = $_GET['tab'] ?? 'profile';
        $validTabs = array_column($page['tabs'], 'key');
        if (!in_array($activeTab, $validTabs, true)) {
            $activeTab = 'profile';
        }

        // 1. Fetch Dynamic Profile Details
        $dbUser = User::findById($userId);
        if (!$dbUser) {
            $this->redirect('/logout');
            return;
        }

        // Parse first name / last name fallback
        $firstName = $dbUser['first_name'] ?? '';
        $lastName = $dbUser['last_name'] ?? '';
        if (empty($firstName)) {
            $parts = preg_split('/\s+/', trim($dbUser['name']));
            $firstName = $parts[0] ?? '';
            $lastName = isset($parts[1]) ? implode(' ', array_slice($parts, 1)) : '';
        }

        $page['profile'] = [
            'firstName' => $firstName,
            'lastName' => $lastName,
            'accountType' => $dbUser['account_type'] ?? 'Individual Buyer',
            'address' => $dbUser['address'] ?? '',
            'address2' => $dbUser['address2'] ?? '',
            'city' => $dbUser['city'] ?? '',
            'state' => $dbUser['state'] ?? '',
            'zip' => $dbUser['zip'] ?? '',
            'importCountry' => $dbUser['country'] ?? 'PAKISTAN',
            'port' => $dbUser['destination_port'] ?? 'KARACHI',
        ];

        // Override user name session to sync header greetings
        Session::set('user_name', $dbUser['name']);

        // 2. Fetch Consignee Details
        $consignee = Consignee::findByUserId($userId);

        // 3. Fetch Dynamic Favorites
        $favRows = Vehicle::findFavoritesByUserId($userId);
        
        $favorites = [];
        foreach ($favRows as $row) {
            $cityKey = 'kobe';
            if (!empty($row['location'])) {
                $parts = explode(',', $row['location']);
                $cityKey = strtolower(trim($parts[0]));
            }

            // Format Unsplash / URL images correctly
            $imgUrl = $row['image'];
            if (empty($imgUrl)) {
                $imageSrc = 'https://images.unsplash.com/photo-1549317661-bd32c8ce0db2?w=600&q=80';
            } elseif (strpos($imgUrl, 'http') === 0) {
                $imageSrc = $imgUrl;
            } elseif (strpos($imgUrl, '/') === 0) {
                $imageSrc = BASE_URL . $imgUrl;
            } else {
                $imageSrc = "https://images.unsplash.com/{$imgUrl}?w=600&q=80";
            }

            $favorites[] = [
                'stockId' => $row['stock_id'],
                'make' => $row['make'],
                'model' => $row['model'],
                'year' => $row['year'],
                'mileage' => number_format($row['mileage_km']) . ' km',
                'priceUsd' => (int)$row['fob_price'],
                'image' => $imageSrc,
                'alt' => $row['make'] . ' ' . $row['model'],
                'cityKey' => $cityKey
            ];
        }

        // 4. Fetch Dynamic Payment Details (USD & JPY Totals)
        $usdPay = User::getUsdPaymentSummary($userId);
        $usdSec = User::getUsdSecurityDeposit($userId);

        $jpyPay = User::getJpyPaymentSummary($userId);
        $jpySec = User::getJpySecurityDeposit($userId);

        $page['paymentSummary'] = [
            [
                'currency' => 'USD',
                'paymentTotal' => number_format((float)($usdPay['total_pay'] ?? 0), 2),
                'allocatedTotal' => number_format((float)($usdPay['total_allocated'] ?? 0), 2),
                'depositTotal' => number_format((float)($usdPay['total_deposit'] ?? 0), 2),
                'securityDeposit' => number_format($usdSec, 2)
            ],
            [
                'currency' => 'JPY',
                'paymentTotal' => number_format((float)($jpyPay['total_pay'] ?? 0), 0),
                'allocatedTotal' => number_format((float)($jpyPay['total_allocated'] ?? 0), 0),
                'depositTotal' => number_format((float)($jpyPay['total_deposit'] ?? 0), 0),
                'securityDeposit' => number_format($jpySec, 0)
            ]
        ];

        // 5. Fetch Dynamic Ledger Log (Payments History)
        $ledgerRows = User::getLedgerLog($userId);

        $paymentHistory = [];
        foreach ($ledgerRows as $row) {
            $formattedAmt = number_format($row['amount'], $row['currency'] === 'JPY' ? 0 : 2);
            $isAllocated = in_array($row['payment_type'], ['Full Car Payment', 'Auction Balance'], true);
            $isDeposit = ($row['payment_type'] === 'Auction Deposit');
            
            $paymentHistory[] = [
                'paymentId' => 'PAY-' . date('Ymd', strtotime($row['created_at'])) . '-' . str_pad($row['id'], 3, '0', STR_PAD_LEFT),
                'receivedDate' => date('F j, Y', strtotime($row['created_at'])),
                'receivedAt' => date('Y-m-d', strtotime($row['created_at'])),
                'currency' => $row['currency'],
                'amount' => $formattedAmt,
                'allocated' => $isAllocated ? $formattedAmt : ($row['currency'] === 'JPY' ? '0' : '0.00'),
                'deposit' => $isDeposit ? $formattedAmt : ($row['currency'] === 'JPY' ? '0' : '0.00'),
                'securityDeposit' => $row['currency'] === 'JPY' ? '0' : '0.00',
                'stockId' => $row['stock_id'] ?? '',
                'chassis' => $row['chassis_number'] ?? '',
            ];
        }

        // Apply ledger search filters if set
        $stockId = trim((string) ($_GET['stock_id'] ?? ''));
        $chassis = trim((string) ($_GET['chassis'] ?? ''));
        $dateFrom = trim((string) ($_GET['date_from'] ?? ''));
        $dateTo = trim((string) ($_GET['date_to'] ?? ''));

        if ($dateFrom !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateFrom)) {
            $fromTs = strtotime($dateFrom . ' 00:00:00');
            $toDate = ($dateTo !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateTo)) ? $dateTo : $dateFrom;
            $toTs = strtotime($toDate . ' 23:59:59');
            $paymentHistory = array_values(array_filter(
                $paymentHistory,
                static function (array $row) use ($fromTs, $toTs): bool {
                    $rowTs = strtotime($row['receivedAt'] ?? '');
                    return $rowTs !== false && $rowTs >= $fromTs && $rowTs <= $toTs;
                }
            ));
        }
        if ($stockId !== '') {
            $paymentHistory = array_values(array_filter(
                $paymentHistory,
                static fn (array $row): bool => stripos($row['stockId'] ?? '', $stockId) !== false
            ));
        }
        if ($chassis !== '') {
            $paymentHistory = array_values(array_filter(
                $paymentHistory,
                static fn (array $row): bool => stripos($row['chassis'] ?? '', $chassis) !== false
            ));
        }

        $paymentDateRangeDisplay = '';
        if ($dateFrom !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateFrom)) {
            $rangeEnd = ($dateTo !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateTo)) ? $dateTo : $dateFrom;
            $paymentDateRangeDisplay = date('m/d/Y', strtotime($dateFrom))
                . ' - '
                . date('m/d/Y', strtotime($rangeEnd));
        }

        // 6. Fetch Active Bids
        $bidRows = User::getActiveBids($userId);
        $bids = [];
        foreach ($bidRows as $row) {
            $img = $row['image'];
            $imageSrc = empty($img) ? 'https://images.unsplash.com/photo-1549317661-bd32c8ce0db2?w=600&q=80' : 
                       (str_starts_with($img, 'http') ? $img : (str_starts_with($img, '/') ? BASE_URL . $img : "https://images.unsplash.com/{$img}?w=600&q=80"));
            
            $bids[] = [
                'id' => $row['id'],
                'stockId' => $row['stock_id'],
                'make' => $row['make'],
                'model' => $row['model'],
                'year' => $row['year'],
                'priceUsd' => $row['fob_price'],
                'bidAmount' => $row['max_bid_amount'],
                'status' => $row['status'],
                'placedAt' => date('M j, Y', strtotime($row['placed_at'])),
                'image' => $imageSrc
            ];
        }

        // 7. Fetch Active Reservations
        $resRows = User::getActiveReservations($userId);
        $reservations = [];
        foreach ($resRows as $row) {
            $img = $row['image'];
            $imageSrc = empty($img) ? 'https://images.unsplash.com/photo-1549317661-bd32c8ce0db2?w=600&q=80' : 
                       (str_starts_with($img, 'http') ? $img : (str_starts_with($img, '/') ? BASE_URL . $img : "https://images.unsplash.com/{$img}?w=600&q=80"));
            
            $reservations[] = [
                'id' => $row['id'],
                'stockId' => $row['stock_id'],
                'make' => $row['make'],
                'model' => $row['model'],
                'year' => $row['year'],
                'priceUsd' => $row['fob_price'],
                'status' => $row['status'],
                'expiresAt' => date('M j, Y H:i', strtotime($row['expires_at'])),
                'image' => $imageSrc
            ];
        }

        // 8. Fetch Purchased Vehicles (Linked to Confirmed Payments)
        $purchasedRows = Vehicle::findPurchasedByUserId($userId);
        $purchasedVehicles = [];
        foreach ($purchasedRows as $row) {
            $img = $row['image'];
            $imageSrc = empty($img) ? 'https://images.unsplash.com/photo-1549317661-bd32c8ce0db2?w=600&q=80' : 
                       (str_starts_with($img, 'http') ? $img : (str_starts_with($img, '/') ? BASE_URL . $img : "https://images.unsplash.com/{$img}?w=600&q=80"));
            
            $purchasedVehicles[] = [
                'id' => $row['id'],
                'stockId' => $row['stock_id'],
                'make' => $row['make'],
                'model' => $row['model'],
                'year' => $row['year'],
                'priceUsd' => $row['fob_price'],
                'image' => $imageSrc,
                'shipmentStatus' => $row['shipment_status'] ?: 'Preparing',
                'blNumber' => $row['bl_number'] ?: '-',
                'vessel' => $row['vessel_name'] ?: '-',
                'etd' => $row['etd'] ? date('M j, Y', strtotime($row['etd'])) : '-',
                'eta' => $row['eta'] ? date('M j, Y', strtotime($row['eta'])) : '-'
            ];
        }

        // 9. Override Account Info Customer & Bank details dynamically
        $page['accountInfo']['left'][0]['value'] = 'EIS-' . str_pad($userId, 7, '0', STR_PAD_LEFT);
        $page['accountInfo']['left'][1]['value'] = $dbUser['name'];
        $page['accountInfo']['left'][2]['value'] = 'MUFG Bank, Ltd.';
        $page['accountInfo']['left'][3]['value'] = 'Eisen Corporation Co., Ltd.';
        $page['accountInfo']['left'][4]['value'] = 'Kobe Main Branch';
        $page['accountInfo']['left'][5]['value'] = '402-9840219-0';
        $page['accountInfo']['left'][6]['value'] = 'MUFGJPJT';
        $page['accountInfo']['bankAddress']['value'] = '1-1-5, Sakaemachidori, Chuo Ward, Kobe, Hyogo 650-0023, Japan';

        $page['accountInfo']['right'][0]['value'] = 'Eisen Inc.';
        $page['accountInfo']['right'][1]['value'] = '3-22-32 Tanaka, Matsubushi Machi, Kitakatsushika Gun, Saitama Prefecture, 343-0117';
        $page['accountInfo']['right'][2]['value'] = '090 3350 8523';
        $page['accountInfo']['right'][3]['value'] = '-';
        $page['accountInfo']['right'][4]['value'] = 'sales@eisenwheels.com';
        $page['accountInfo']['right'][5]['value'] = 'https://eisenwheels.com';

        // Render view
        $this->view('front/account', array_merge($page, [
            'activeTab' => $activeTab,
            'displayName' => $dbUser['name'],
            'flash' => Session::getFlash(),
            'consignee' => $consignee,
            'favorites' => $favorites,
            'bids' => $bids,
            'reservations' => $reservations,
            'purchasedVehicles' => $purchasedVehicles,
            'paymentHistory' => $paymentHistory,
            'paymentStockId' => $stockId,
            'paymentChassis' => $chassis,
            'paymentDateFrom' => $dateFrom,
            'paymentDateTo' => $dateTo,
            'paymentDateRangeDisplay' => $paymentDateRangeDisplay,
        ]));
    }

    public function updateProfile()
    {
        if (!Session::isLoggedIn()) {
            $this->redirect('/login');
            return;
        }

        $userId = Session::get('user_id');
        try {
            $this->validateCsrf();
        } catch (\Exception $e) {
            Session::setFlash('error', 'CSRF token validation failed. Please try again.');
            $this->redirect('/account?tab=profile');
            return;
        }

        $firstName = trim((string)($_POST['first_name'] ?? ''));
        $lastName = trim((string)($_POST['last_name'] ?? ''));
        $address = trim((string)($_POST['address'] ?? ''));
        $address2 = trim((string)($_POST['address2'] ?? ''));
        $city = trim((string)($_POST['city'] ?? ''));
        $state = trim((string)($_POST['state'] ?? ''));
        $zip = trim((string)($_POST['zip'] ?? ''));
        $importCountry = trim((string)($_POST['import_country'] ?? ''));
        $port = trim((string)($_POST['port'] ?? ''));

        if ($firstName === '') {
            Session::setFlash('error', 'First name is required.');
            $this->redirect('/account?tab=profile');
            return;
        }

        // Input validation checks
        if (mb_strlen($firstName) > 50 || mb_strlen($lastName) > 50) {
            Session::setFlash('error', 'First name and last name must not exceed 50 characters.');
            $this->redirect('/account?tab=profile');
            return;
        }
        if (mb_strlen($address) > 255 || mb_strlen($address2) > 255) {
            Session::setFlash('error', 'Address fields must not exceed 255 characters.');
            $this->redirect('/account?tab=profile');
            return;
        }
        if (mb_strlen($city) > 100 || mb_strlen($state) > 100 || mb_strlen($importCountry) > 100 || mb_strlen($port) > 100) {
            Session::setFlash('error', 'City, State, Country, and Port must not exceed 100 characters.');
            $this->redirect('/account?tab=profile');
            return;
        }
        if (mb_strlen($zip) > 20) {
            Session::setFlash('error', 'Zip code must not exceed 20 characters.');
            $this->redirect('/account?tab=profile');
            return;
        }

        $fullName = trim($firstName . ' ' . $lastName);

        User::updateProfile($userId, $fullName, $firstName, $lastName, $address, $address2, $city, $state, $zip, $importCountry, $port);

        Session::set('user_name', $fullName);
        Session::setFlash('success', 'Profile updated successfully!');
        $this->redirect('/account?tab=profile');
    }

    public function updateConsignee()
    {
        if (!Session::isLoggedIn()) {
            $this->redirect('/login');
            return;
        }

        $userId = Session::get('user_id');
        try {
            $this->validateCsrf();
        } catch (\Exception $e) {
            Session::setFlash('error', 'CSRF token validation failed. Please try again.');
            $this->redirect('/account?tab=consignee');
            return;
        }

        // Get fields
        $consigneeName = trim((string)($_POST['consignee_name'] ?? ''));
        $consigneeCountry = trim((string)($_POST['consignee_country'] ?? ''));
        $consigneeState = trim((string)($_POST['consignee_state'] ?? ''));
        $consigneeCity = trim((string)($_POST['consignee_city'] ?? ''));
        $consigneeAddress = trim((string)($_POST['consignee_address'] ?? ''));
        $consigneeRefAddress = trim((string)($_POST['consignee_ref_address'] ?? ''));
        
        $consigneePhone1 = trim((string)($_POST['consignee_phone_1'] ?? ''));
        $consigneePhone2 = trim((string)($_POST['consignee_phone_2'] ?? ''));
        $consigneePhone3 = trim((string)($_POST['consignee_phone_3'] ?? ''));
        
        $consigneeEmail1 = trim((string)($_POST['consignee_email_1'] ?? ''));
        $consigneeEmail2 = trim((string)($_POST['consignee_email_2'] ?? ''));
        $consigneeEmail3 = trim((string)($_POST['consignee_email_3'] ?? ''));

        $notifyName = trim((string)($_POST['notify_name'] ?? ''));
        $notifyCountry = trim((string)($_POST['notify_country'] ?? ''));
        $notifyState = trim((string)($_POST['notify_state'] ?? ''));
        $notifyCity = trim((string)($_POST['notify_city'] ?? ''));
        $notifyAddress = trim((string)($_POST['notify_address'] ?? ''));
        $notifyRefAddress = trim((string)($_POST['notify_ref_address'] ?? ''));
        
        $notifyPhone1 = trim((string)($_POST['notify_phone_1'] ?? ''));
        $notifyPhone2 = trim((string)($_POST['notify_phone_2'] ?? ''));
        $notifyPhone3 = trim((string)($_POST['notify_phone_3'] ?? ''));
        
        $notifyEmail1 = trim((string)($_POST['notify_email_1'] ?? ''));
        $notifyEmail2 = trim((string)($_POST['notify_email_2'] ?? ''));
        $notifyEmail3 = trim((string)($_POST['notify_email_3'] ?? ''));

        $notifySame = isset($_POST['notify_same']) ? 1 : 0;
        $permanent = isset($_POST['permanent']) ? 1 : 0;

        if ($consigneeName === '' || $consigneeCountry === '' || $consigneeState === '' || $consigneeCity === '' || $consigneeAddress === '') {
            Session::setFlash('error', 'Please fill in all required Consignee fields marked with an asterisk (*).');
            $this->redirect('/account?tab=consignee');
            return;
        }

        if (!$notifySame && ($notifyName === '' || $notifyCountry === '' || $notifyState === '' || $notifyCity === '' || $notifyAddress === '')) {
            Session::setFlash('error', 'Please fill in all required Notify fields marked with an asterisk (*) or check "Notify Same as Consignee".');
            $this->redirect('/account?tab=consignee');
            return;
        }

        if ($notifySame) {
            $notifyName = $consigneeName;
            $notifyCountry = $consigneeCountry;
            $notifyState = $consigneeState;
            $notifyCity = $consigneeCity;
            $notifyAddress = $consigneeAddress;
            $notifyRefAddress = $consigneeRefAddress;
            $notifyPhone1 = $consigneePhone1;
            $notifyPhone2 = $consigneePhone2;
            $notifyPhone3 = $consigneePhone3;
            $notifyEmail1 = $consigneeEmail1;
            $notifyEmail2 = $consigneeEmail2;
            $notifyEmail3 = $consigneeEmail3;
        }

        // Input validation & length checks
        if (mb_strlen($consigneeName) > 100 || mb_strlen($notifyName) > 100) {
            Session::setFlash('error', 'Names must not exceed 100 characters.');
            $this->redirect('/account?tab=consignee');
            return;
        }
        if (mb_strlen($consigneeCountry) > 100 || mb_strlen($notifyCountry) > 100 ||
            mb_strlen($consigneeState) > 100 || mb_strlen($notifyState) > 100 ||
            mb_strlen($consigneeCity) > 100 || mb_strlen($notifyCity) > 100) {
            Session::setFlash('error', 'Country, State, and City fields must not exceed 100 characters.');
            $this->redirect('/account?tab=consignee');
            return;
        }
        if (mb_strlen($consigneeAddress) > 255 || mb_strlen($notifyAddress) > 255 ||
            mb_strlen($consigneeRefAddress) > 255 || mb_strlen($notifyRefAddress) > 255) {
            Session::setFlash('error', 'Address fields must not exceed 255 characters.');
            $this->redirect('/account?tab=consignee');
            return;
        }
        
        // Email validations
        $emails = [
            'Consignee Email 1' => $consigneeEmail1,
            'Consignee Email 2' => $consigneeEmail2,
            'Consignee Email 3' => $consigneeEmail3,
            'Notify Email 1' => $notifyEmail1,
            'Notify Email 2' => $notifyEmail2,
            'Notify Email 3' => $notifyEmail3
        ];
        foreach ($emails as $label => $val) {
            if ($val !== '') {
                if (mb_strlen($val) > 100) {
                    Session::setFlash('error', "{$label} must not exceed 100 characters.");
                    $this->redirect('/account?tab=consignee');
                    return;
                }
                if (!filter_var($val, FILTER_VALIDATE_EMAIL)) {
                    Session::setFlash('error', "{$label} is not a valid email address.");
                    $this->redirect('/account?tab=consignee');
                    return;
                }
            }
        }

        // Phone validations
        $phones = [
            'Consignee Phone 1' => $consigneePhone1,
            'Consignee Phone 2' => $consigneePhone2,
            'Consignee Phone 3' => $consigneePhone3,
            'Notify Phone 1' => $notifyPhone1,
            'Notify Phone 2' => $notifyPhone2,
            'Notify Phone 3' => $notifyPhone3
        ];
        foreach ($phones as $label => $val) {
            if ($val !== '') {
                if (mb_strlen($val) > 30) {
                    Session::setFlash('error', "{$label} must not exceed 30 characters.");
                    $this->redirect('/account?tab=consignee');
                    return;
                }
                if (!preg_match('/^[0-9+\-\s()]+$/', $val)) {
                    Session::setFlash('error', "{$label} contains invalid characters. Only digits, spaces, -, +, and parentheses are allowed.");
                    $this->redirect('/account?tab=consignee');
                    return;
                }
            }
        }

        $consigneeData = [
            'consignee_name' => $consigneeName,
            'consignee_country' => $consigneeCountry,
            'consignee_state' => $consigneeState,
            'consignee_city' => $consigneeCity,
            'consignee_address' => $consigneeAddress,
            'consignee_ref_address' => $consigneeRefAddress,
            'consignee_phone_1' => $consigneePhone1,
            'consignee_phone_2' => $consigneePhone2,
            'consignee_phone_3' => $consigneePhone3,
            'consignee_email_1' => $consigneeEmail1,
            'consignee_email_2' => $consigneeEmail2,
            'consignee_email_3' => $consigneeEmail3,
            'notify_name' => $notifyName,
            'notify_country' => $notifyCountry,
            'notify_state' => $notifyState,
            'notify_city' => $notifyCity,
            'notify_address' => $notifyAddress,
            'notify_ref_address' => $notifyRefAddress,
            'notify_phone_1' => $notifyPhone1,
            'notify_phone_2' => $notifyPhone2,
            'notify_phone_3' => $notifyPhone3,
            'notify_email_1' => $notifyEmail1,
            'notify_email_2' => $notifyEmail2,
            'notify_email_3' => $notifyEmail3,
            'notify_same' => $notifySame,
            'permanent' => $permanent
        ];

        if (Consignee::existsForUser($userId)) {
            Consignee::update($userId, $consigneeData);
        } else {
            Consignee::create($userId, $consigneeData);
        }

        Session::setFlash('success', 'Consignee details saved successfully!');
        $this->redirect('/account?tab=consignee');
    }

    public function removeFavorite()
    {
        if (!Session::isLoggedIn()) {
            return $this->jsonResponse(['status' => 'error', 'message' => 'Unauthorized'], 401);
        }

        $userId = Session::get('user_id');
        try {
            $this->validateCsrf();
        } catch (\Exception $e) {
            return $this->jsonResponse(['status' => 'error', 'message' => 'CSRF verification failed.'], 403);
        }

        $stockId = trim((string)($_POST['stock_id'] ?? ''));
        if ($stockId === '') {
            return $this->jsonResponse(['status' => 'error', 'message' => 'Invalid Stock ID'], 400);
        }

        $vehicle = Vehicle::findByStockId($stockId);
        if (!$vehicle) {
            return $this->jsonResponse(['status' => 'error', 'message' => 'Vehicle not found.'], 404);
        }

        Vehicle::removeFavorite($userId, $vehicle['id']);

        return $this->jsonResponse(['status' => 'success', 'message' => 'Favorite removed successfully.']);
    }
}
