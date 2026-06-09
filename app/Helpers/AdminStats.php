<?php

namespace App\Helpers;

use App\Core\Database;
use PDO;

class AdminStats
{
    public const AUCTION_ALERT_DAYS = 7;
    public const AUCTION_OVERDUE_DAYS = 30;
    public const AUCTION_BANNER_PREVIEW = 3;

    /** @return array<string, mixed> */
    public static function getDashboardStats(): array
    {
        try {
            $db = Database::getConnection();
        } catch (\Throwable $e) {
            return self::emptyStats();
        }

        $count = static function (string $sql, array $params = []) use ($db): int {
            try {
                $stmt = $db->prepare($sql);
                $stmt->execute($params);
                return (int) $stmt->fetchColumn();
            } catch (\Throwable $e) {
                return 0;
            }
        };

        $hasAuctionEndDate = self::columnExists($db, 'vehicles', 'auction_end_date');

        $totalListings = $count('SELECT COUNT(*) FROM vehicles');
        $activeInStock = $count("SELECT COUNT(*) FROM vehicles WHERE type = 'In-Stock'");
        $activeAuction = $count("SELECT COUNT(*) FROM vehicles WHERE type = 'Auction'");
        $availableStock = $count("SELECT COUNT(*) FROM vehicles WHERE status = 'Available'");
        $newThisWeek = $count('SELECT COUNT(*) FROM vehicles WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)');
        $featuredCount = $count('SELECT COUNT(*) FROM vehicles WHERE featured = 1');
        $auctionEndingSoon = $hasAuctionEndDate
            ? self::countAuctionEndingSoon(self::AUCTION_ALERT_DAYS, self::AUCTION_OVERDUE_DAYS, $db)
            : 0;

        $totalUsers = $count("SELECT COUNT(*) FROM users WHERE role = 'registered_buyer'");
        $newUsersWeek = $count(
            "SELECT COUNT(*) FROM users WHERE role = 'registered_buyer' AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)"
        );
        $blogPosts = $count('SELECT COUNT(*) FROM blog_posts');
        $favoritesCount = $count('SELECT COUNT(*) FROM vehicle_favorites');

        $pendingBids = $count("SELECT COUNT(*) FROM auction_bids WHERE status = 'Pending Confirmation'");
        $pendingPayments = $count("SELECT COUNT(*) FROM payments WHERE status = 'Pending'");
        $pendingDeposits = $count("SELECT COUNT(*) FROM security_deposits WHERE status = 'Pending Verification'");
        $activeShipments = $count("SELECT COUNT(*) FROM shipments WHERE status != 'Delivered'");

        $activeReservations = $count(
            "SELECT COUNT(*) FROM reservations WHERE status != 'Released' AND expires_at > NOW()"
        );

        $monthlyRevenue = 0.0;
        $yearlyRevenue = 0.0;
        try {
            $revStmt = $db->query("
                SELECT
                    COALESCE(SUM(CASE WHEN created_at >= DATE_FORMAT(CURDATE(), '%Y-%m-01') THEN amount ELSE 0 END), 0) AS month_total,
                    COALESCE(SUM(CASE WHEN created_at >= DATE_FORMAT(CURDATE(), '%Y-01-01') THEN amount ELSE 0 END), 0) AS year_total
                FROM payments
                WHERE status = 'Confirmed' AND currency = 'USD'
            ");
            $rev = $revStmt->fetch(PDO::FETCH_ASSOC) ?: [];
            $monthlyRevenue = (float) ($rev['month_total'] ?? 0);
            $yearlyRevenue = (float) ($rev['year_total'] ?? 0);
        } catch (\Throwable $e) {
            // payments table may be empty
        }

        return [
            'total_listings' => $totalListings,
            'active_in_stock' => $activeInStock,
            'active_auction' => $activeAuction,
            'available_stock' => $availableStock,
            'new_this_week' => $newThisWeek,
            'featured_count' => $featuredCount,
            'auction_ending_soon' => $auctionEndingSoon,
            'total_users' => $totalUsers,
            'new_users_week' => $newUsersWeek,
            'blog_posts' => $blogPosts,
            'favorites_count' => $favoritesCount,
            'pending_bids' => $pendingBids,
            'pending_payments' => $pendingPayments,
            'pending_deposits' => $pendingDeposits,
            'active_shipments' => $activeShipments,
            'active_reservations' => $activeReservations,
            'monthly_revenue' => $monthlyRevenue,
            'yearly_revenue' => $yearlyRevenue,
            'today_reservations' => self::getActiveReservations($db),
            'recent_activities' => self::getRecentActivities($db),
            'auction_alerts' => self::getAuctionEndAlerts($db),
        ];
    }

    public static function countAuctionEndingSoon(
        int $daysAhead = self::AUCTION_ALERT_DAYS,
        int $overdueDays = self::AUCTION_OVERDUE_DAYS,
        ?PDO $db = null
    ): int {
        try {
            $db = $db ?? Database::getConnection();
        } catch (\Throwable $e) {
            return 0;
        }

        if (!self::columnExists($db, 'vehicles', 'auction_end_date')) {
            return 0;
        }

        try {
            $stmt = $db->prepare('
                SELECT COUNT(*) FROM vehicles
                WHERE auction_end_date IS NOT NULL
                  AND auction_end_date >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
                  AND auction_end_date <= DATE_ADD(CURDATE(), INTERVAL ? DAY)
                  AND status NOT IN (\'Sold\', \'Archived\', \'Delivered\')
            ');
            $stmt->execute([$overdueDays, $daysAhead]);
            return (int) $stmt->fetchColumn();
        } catch (\Throwable $e) {
            return 0;
        }
    }

    /**
     * @return list<array<string, mixed>>
     */
    public static function getAuctionEndAlerts(
        ?PDO $db = null,
        int $daysAhead = self::AUCTION_ALERT_DAYS,
        int $limit = self::AUCTION_BANNER_PREVIEW
    ): array {
        $rows = self::fetchAuctionEndingRows($db, $daysAhead, self::AUCTION_OVERDUE_DAYS, $limit);
        $today = new \DateTimeImmutable('today');
        $alerts = [];

        foreach ($rows as $row) {
            $mapped = self::mapAuctionEndRow($row, $today);
            if ($mapped !== null) {
                $alerts[] = $mapped;
            }
        }

        return $alerts;
    }

    /**
     * @return array{listings: list<array<string, mixed>>, counts: array<string, int>, totalCount: int}
     */
    public static function getAuctionEndingPageData(
        int $daysAhead = self::AUCTION_ALERT_DAYS,
        int $overdueDays = self::AUCTION_OVERDUE_DAYS,
        string $filter = 'all'
    ): array {
        $all = self::getAuctionEndingListings($daysAhead, $overdueDays, 'all');
        $counts = [
            'all' => count($all),
            'overdue' => 0,
            'today' => 0,
            'upcoming' => 0,
        ];

        foreach ($all as $item) {
            if ($item['urgency'] === 'overdue') {
                $counts['overdue']++;
            } elseif ($item['urgency'] === 'today') {
                $counts['today']++;
            } elseif (in_array($item['urgency'], ['soon', 'upcoming'], true)) {
                $counts['upcoming']++;
            }
        }

        if ($filter === 'all') {
            $listings = $all;
        } else {
            $listings = array_values(array_filter(
                $all,
                static fn(array $item): bool => self::matchesAuctionEndFilter($item['urgency'], $filter)
            ));
        }

        return [
            'listings' => $listings,
            'counts' => $counts,
            'totalCount' => $counts['all'],
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public static function getAuctionEndingListings(
        int $daysAhead = self::AUCTION_ALERT_DAYS,
        int $overdueDays = self::AUCTION_OVERDUE_DAYS,
        string $filter = 'all'
    ): array {
        $rows = self::fetchAuctionEndingRows(null, $daysAhead, $overdueDays);
        $today = new \DateTimeImmutable('today');
        $listings = [];

        foreach ($rows as $row) {
            $mapped = self::mapAuctionEndRow($row, $today, true);
            if ($mapped === null || !self::matchesAuctionEndFilter($mapped['urgency'], $filter)) {
                continue;
            }
            $listings[] = $mapped;
        }

        return $listings;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private static function fetchAuctionEndingRows(
        ?PDO $db,
        int $daysAhead,
        int $overdueDays,
        ?int $limit = null
    ): array {
        try {
            $db = $db ?? Database::getConnection();
        } catch (\Throwable $e) {
            return [];
        }

        if (!self::columnExists($db, 'vehicles', 'auction_end_date')) {
            return [];
        }

        $sql = '
            SELECT
                v.id,
                v.stock_id,
                v.make,
                v.model,
                v.year,
                v.chassis_number,
                v.auction_house,
                v.lot_number,
                v.auction_end_date,
                v.status,
                v.type,
                v.fob_price,
                v.mileage_km,
                (
                    SELECT image_url FROM vehicle_images
                    WHERE vehicle_id = v.id
                    ORDER BY sort_order ASC
                    LIMIT 1
                ) AS image
            FROM vehicles v
            WHERE v.auction_end_date IS NOT NULL
              AND v.auction_end_date >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
              AND v.auction_end_date <= DATE_ADD(CURDATE(), INTERVAL ? DAY)
              AND v.status NOT IN (\'Sold\', \'Archived\', \'Delivered\')
            ORDER BY v.auction_end_date ASC, v.id DESC
        ';

        if ($limit !== null && $limit > 0) {
            $sql .= ' LIMIT ' . (int) $limit;
        }

        try {
            $stmt = $db->prepare($sql);
            $stmt->execute([$overdueDays, $daysAhead]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (\Throwable $e) {
            return [];
        }
    }

    /** @return array<string, mixed>|null */
    private static function mapAuctionEndRow(array $row, \DateTimeImmutable $today, bool $extended = false): ?array
    {
        $endDate = \DateTimeImmutable::createFromFormat('Y-m-d', (string) $row['auction_end_date']);
        if (!$endDate) {
            return null;
        }

        $daysLeft = (int) $today->diff($endDate)->format('%r%a');

        if ($daysLeft < 0) {
            $urgency = 'overdue';
            $label = abs($daysLeft) === 1 ? '1 day overdue' : abs($daysLeft) . ' days overdue';
        } elseif ($daysLeft === 0) {
            $urgency = 'today';
            $label = 'Ends today';
        } elseif ($daysLeft === 1) {
            $urgency = 'soon';
            $label = 'Ends tomorrow';
        } else {
            $urgency = 'upcoming';
            $label = 'Ends in ' . $daysLeft . ' days';
        }

        $baseUrl = defined('BASE_URL') ? BASE_URL : '';
        $mapped = [
            'id' => (int) $row['id'],
            'stock_id' => $row['stock_id'],
            'vehicle' => trim($row['year'] . ' ' . $row['make'] . ' ' . $row['model']),
            'make' => $row['make'],
            'model' => $row['model'],
            'year' => (int) $row['year'],
            'chassis_number' => $row['chassis_number'] ?? '',
            'auction_house' => self::cleanUtf8(trim((string) ($row['auction_house'] ?? ''))),
            'lot_number' => self::cleanUtf8(trim((string) ($row['lot_number'] ?? ''))),
            'auction_end_date' => $row['auction_end_date'],
            'auction_end_display' => $endDate->format('M j, Y'),
            'days_left' => $daysLeft,
            'urgency' => $urgency,
            'label' => $label,
            'status' => $row['status'],
            'type' => $row['type'] ?? '',
            'edit_url' => $baseUrl . '/admin/inventory/edit/' . (int) $row['id'],
        ];

        if ($extended) {
            $mapped['fob_price'] = (float) ($row['fob_price'] ?? 0);
            $mapped['mileage_km'] = (int) ($row['mileage_km'] ?? 0);
            $mapped['image'] = $row['image'] ?? '';
        }

        return $mapped;
    }

    private static function matchesAuctionEndFilter(string $urgency, string $filter): bool
    {
        return match ($filter) {
            'overdue' => $urgency === 'overdue',
            'today' => $urgency === 'today',
            'upcoming' => in_array($urgency, ['soon', 'upcoming'], true),
            default => true,
        };
    }

    private static function cleanUtf8(string $value): string
    {
        if ($value === '') {
            return '';
        }

        if (!function_exists('mb_convert_encoding')) {
            return $value;
        }

        return trim((string) mb_convert_encoding($value, 'UTF-8', 'UTF-8'));
    }

    /** @return list<array<string, mixed>> */
    private static function getActiveReservations(PDO $db): array
    {
        try {
            $stmt = $db->query("
                SELECT
                    r.id,
                    u.name AS buyer_name,
                    v.stock_id,
                    v.make,
                    v.model,
                    v.year,
                    v.chassis_number,
                    GREATEST(TIMESTAMPDIFF(SECOND, NOW(), r.expires_at), 0) AS time_remaining
                FROM reservations r
                INNER JOIN users u ON u.id = r.user_id
                INNER JOIN vehicles v ON v.id = r.vehicle_id
                WHERE r.status != 'Released' AND r.expires_at > NOW()
                ORDER BY r.expires_at ASC
                LIMIT 10
            ");
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (\Throwable $e) {
            return [];
        }

        $reservations = [];
        foreach ($rows as $row) {
            $reservations[] = [
                'buyer_name' => $row['buyer_name'],
                'car' => trim($row['year'] . ' ' . $row['make'] . ' ' . $row['model']),
                'chassis' => $row['chassis_number'],
                'time_remaining' => (int) $row['time_remaining'],
            ];
        }

        return $reservations;
    }

    /** @return list<array<string, mixed>> */
    private static function getRecentActivities(PDO $db): array
    {
        $activities = [];

        try {
            $vehicleStmt = $db->query("
                SELECT stock_id, make, model, created_at
                FROM vehicles
                ORDER BY created_at DESC
                LIMIT 5
            ");
            foreach ($vehicleStmt->fetchAll(PDO::FETCH_ASSOC) ?: [] as $row) {
                $activities[] = [
                    'title' => 'New listing added',
                    'detail' => $row['stock_id'] . ' — ' . $row['make'] . ' ' . $row['model'],
                    'time' => self::timeAgo($row['created_at']),
                    'type' => 'inventory',
                    'sort' => strtotime((string) $row['created_at']),
                ];
            }
        } catch (\Throwable $e) {
        }

        try {
            $userStmt = $db->query("
                SELECT name, email, created_at
                FROM users
                WHERE role = 'registered_buyer'
                ORDER BY created_at DESC
                LIMIT 3
            ");
            foreach ($userStmt->fetchAll(PDO::FETCH_ASSOC) ?: [] as $row) {
                $activities[] = [
                    'title' => 'New user registered',
                    'detail' => $row['name'] . ' (' . $row['email'] . ')',
                    'time' => self::timeAgo($row['created_at']),
                    'type' => 'document',
                    'sort' => strtotime((string) $row['created_at']),
                ];
            }
        } catch (\Throwable $e) {
        }

        try {
            $blogStmt = $db->query("
                SELECT title, published_date
                FROM blog_posts
                ORDER BY published_date DESC
                LIMIT 3
            ");
            foreach ($blogStmt->fetchAll(PDO::FETCH_ASSOC) ?: [] as $row) {
                $timestamp = $row['published_date'] ?? null;
                $activities[] = [
                    'title' => 'Blog post published',
                    'detail' => $row['title'],
                    'time' => self::timeAgo($timestamp),
                    'type' => 'payment',
                    'sort' => $timestamp ? strtotime((string) $timestamp) : 0,
                ];
            }
        } catch (\Throwable $e) {
        }

        usort($activities, static fn(array $a, array $b): int => ($b['sort'] ?? 0) <=> ($a['sort'] ?? 0));

        return array_map(static function (array $item): array {
            unset($item['sort']);
            return $item;
        }, array_slice($activities, 0, 8));
    }

    private static function timeAgo(?string $datetime): string
    {
        if (!$datetime) {
            return 'Recently';
        }

        $ts = strtotime($datetime);
        if ($ts === false) {
            return 'Recently';
        }

        $diff = time() - $ts;
        if ($diff < 60) {
            return 'Just now';
        }
        if ($diff < 3600) {
            return (int) floor($diff / 60) . ' mins ago';
        }
        if ($diff < 86400) {
            return (int) floor($diff / 3600) . ' hours ago';
        }
        if ($diff < 604800) {
            return (int) floor($diff / 86400) . ' days ago';
        }

        return date('M j, Y', $ts);
    }

    private static function columnExists(PDO $db, string $table, string $column): bool
    {
        $stmt = $db->prepare(
            'SELECT COUNT(*) FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?'
        );
        $stmt->execute([$table, $column]);
        return (int) $stmt->fetchColumn() > 0;
    }

    /** @return array<string, mixed> */
    private static function emptyStats(): array
    {
        return [
            'total_listings' => 0,
            'active_in_stock' => 0,
            'active_auction' => 0,
            'available_stock' => 0,
            'new_this_week' => 0,
            'featured_count' => 0,
            'auction_ending_soon' => 0,
            'total_users' => 0,
            'new_users_week' => 0,
            'blog_posts' => 0,
            'favorites_count' => 0,
            'pending_bids' => 0,
            'pending_payments' => 0,
            'pending_deposits' => 0,
            'active_shipments' => 0,
            'active_reservations' => 0,
            'monthly_revenue' => 0,
            'yearly_revenue' => 0,
            'today_reservations' => [],
            'recent_activities' => [],
            'auction_alerts' => [],
        ];
    }
}
