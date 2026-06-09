<?php 
$pageTitle = "Dashboard | Eisen Admin";
$pageScript = "dashboard.js";
include dirname(__DIR__) . '/admin/partials/header.php'; 

$s = $stats ?? [];
$reservations = $s['today_reservations'] ?? [];
$activities = $s['recent_activities'] ?? [];
$auctionAlerts = $auctionAlerts ?? ($s['auction_alerts'] ?? []);
?>

<?php include dirname(__DIR__) . '/admin/partials/auction-alerts.php'; ?>

<div class="dashboard-page-content">
    <div class="page-header-container mb-30">
        <div class="header-title-group">
            <h1 class="page-title">Dashboard</h1>
            <p style="color: var(--color-text-muted); margin: 4px 0 0 0;">Eisen Corporation — live inventory and system overview</p>
        </div>
        <div class="header-actions">
            <button class="btn btn-primary" onclick="window.location.href='<?= BASE_URL ?>/admin/inventory/new'">
                <i data-lucide="plus-circle"></i>
                <span>Add Listing</span>
            </button>
        </div>
    </div>

    <div class="dashboard-stats-grid mb-30">
        <div class="stat-card">
            <div class="stat-card-header">
                <div class="stat-icon-box bg-soft-primary">
                    <i data-lucide="car"></i>
                </div>
                <div class="stat-info">
                    <span class="stat-label">Total Listings</span>
                    <span class="stat-change text-success">+<?= (int) ($s['new_this_week'] ?? 0) ?> this week</span>
                </div>
            </div>
            <div class="stat-card-body">
                <h2 class="stat-value"><?= number_format((int) ($s['total_listings'] ?? 0)) ?></h2>
                <p style="margin: 6px 0 0 0; font-size: 11px; color: var(--color-text-muted);">
                    <strong><?= (int) ($s['active_in_stock'] ?? 0) ?></strong> In-Stock ·
                    <strong><?= (int) ($s['active_auction'] ?? 0) ?></strong> Auction ·
                    <strong><?= (int) ($s['available_stock'] ?? 0) ?></strong> Available
                </p>
            </div>
        </div>

        <div class="stat-card" style="cursor: pointer;" onclick="window.location.href='<?= BASE_URL ?>/admin/inventory/auction-ending'">
            <div class="stat-card-header">
                <div class="stat-icon-box bg-soft-warning">
                    <i data-lucide="calendar-clock"></i>
                </div>
                <div class="stat-info">
                    <span class="stat-label">Auction Ending Soon</span>
                    <span class="stat-change" style="color: var(--color-warning);">Next <?= (int) \App\Helpers\AdminStats::AUCTION_ALERT_DAYS ?> days</span>
                </div>
            </div>
            <div class="stat-card-body">
                <h2 class="stat-value"><?= (int) ($s['auction_ending_soon'] ?? 0) ?></h2>
                <p style="margin: 6px 0 0 0; font-size: 11px; color: var(--color-text-muted);">
                    Click to view full auction ending list
                </p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-card-header">
                <div class="stat-icon-box bg-soft-info">
                    <i data-lucide="users"></i>
                </div>
                <div class="stat-info">
                    <span class="stat-label">Registered Buyers</span>
                    <span class="stat-change text-success">+<?= (int) ($s['new_users_week'] ?? 0) ?> this week</span>
                </div>
            </div>
            <div class="stat-card-body">
                <h2 class="stat-value"><?= number_format((int) ($s['total_users'] ?? 0)) ?></h2>
                <p style="margin: 6px 0 0 0; font-size: 11px; color: var(--color-text-muted);">
                    <strong><?= (int) ($s['favorites_count'] ?? 0) ?></strong> saved favorites ·
                    <strong><?= (int) ($s['blog_posts'] ?? 0) ?></strong> blog posts
                </p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-card-header">
                <div class="stat-icon-box bg-soft-success">
                    <i data-lucide="credit-card"></i>
                </div>
                <div class="stat-info">
                    <span class="stat-label">Confirmed Revenue (USD)</span>
                    <span class="stat-change text-success">Live from payments</span>
                </div>
            </div>
            <div class="stat-card-body">
                <h2 class="stat-value text-gold" style="color: var(--color-gold-500);">$<?= number_format((float) ($s['monthly_revenue'] ?? 0)) ?></h2>
                <p style="margin: 6px 0 0 0; font-size: 11px; color: var(--color-text-muted);">
                    This month · YTD <strong>$<?= number_format((float) ($s['yearly_revenue'] ?? 0)) ?></strong>
                </p>
            </div>
        </div>
    </div>

    <div class="dashboard-main-grid">
        <div class="grid-span-2">
            <div class="card">
                <div class="card-header-flex">
                    <h3 class="card-title-sm">Active Holds & Reservations</h3>
                    <span class="badge badge-info"><?= count($reservations) ?> active</span>
                </div>
                <?php if (empty($reservations)): ?>
                <p style="padding: 24px; color: var(--color-text-muted); margin: 0;">No active reservations in the system right now.</p>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="data-table-minimal">
                        <thead>
                            <tr>
                                <th>Buyer Name</th>
                                <th>Vehicle Detail</th>
                                <th>Chassis Number</th>
                                <th>Remaining Time</th>
                                <th style="text-align: right;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($reservations as $res): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($res['buyer_name']) ?></strong></td>
                                <td><?= htmlspecialchars($res['car']) ?></td>
                                <td><code><?= htmlspecialchars($res['chassis']) ?></code></td>
                                <td>
                                    <span class="timer-pill" data-countdown="<?= (int) $res['time_remaining'] ?>">--:--:--</span>
                                </td>
                                <td style="text-align: right;">
                                    <button class="btn btn-primary btn-sm" onclick="window.location.href='<?= BASE_URL ?>/admin/reservations'">
                                        View
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>

            <div class="card">
                <div class="card-header-flex">
                    <h3 class="card-title-sm">Recent Activity</h3>
                    <span class="pulse-indicator">
                        <span class="pulse-dot"></span> Live from database
                    </span>
                </div>
                <?php if (empty($activities)): ?>
                <p style="padding: 24px; color: var(--color-text-muted); margin: 0;">No recent activity yet.</p>
                <?php else: ?>
                <div class="activity-timeline">
                    <?php foreach ($activities as $act): ?>
                    <?php
                    $icon = 'activity';
                    $colorClass = 'bg-soft-primary';
                    if ($act['type'] === 'inventory') { $icon = 'car'; $colorClass = 'bg-soft-primary'; }
                    elseif ($act['type'] === 'reservation') { $icon = 'clock'; $colorClass = 'bg-soft-warning'; }
                    elseif ($act['type'] === 'bid') { $icon = 'gavel'; $colorClass = 'bg-soft-info'; }
                    elseif ($act['type'] === 'document') { $icon = 'user-plus'; $colorClass = 'bg-soft-info'; }
                    elseif ($act['type'] === 'payment') { $icon = 'file-text'; $colorClass = 'bg-soft-success'; }
                    elseif ($act['type'] === 'shipping') { $icon = 'ship'; $colorClass = 'bg-soft-success'; }
                    ?>
                    <div class="activity-item">
                        <div class="activity-icon-container <?= $colorClass ?>">
                            <i data-lucide="<?= $icon ?>"></i>
                        </div>
                        <div class="activity-content">
                            <p class="activity-text">
                                <strong><?= htmlspecialchars($act['title']) ?></strong>: <?= htmlspecialchars($act['detail']) ?>
                            </p>
                            <span class="activity-time"><?= htmlspecialchars($act['time']) ?></span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <div>
            <div class="card">
                <h3 class="card-title-sm mb-20">Quick Operations</h3>
                <div class="quick-actions-list">
                    <a class="btn-action" href="<?= BASE_URL ?>/admin/inventory/new">
                        <i data-lucide="plus-circle" style="color: var(--color-success);"></i>
                        <span>Add In-Stock Car</span>
                    </a>
                    <a class="btn-action" href="<?= BASE_URL ?>/admin/inventory">
                        <i data-lucide="list" style="color: var(--color-info);"></i>
                        <span>Manage Inventory</span>
                    </a>
                    <a class="btn-action" href="<?= BASE_URL ?>/admin/customers">
                        <i data-lucide="user-check" style="color: var(--color-warning);"></i>
                        <span>Customer Registry</span>
                    </a>
                    <a class="btn-action" href="<?= BASE_URL ?>/admin/blog">
                        <i data-lucide="file-text" style="color: var(--color-gold-500);"></i>
                        <span>Manage Blog</span>
                    </a>
                </div>
            </div>

            <div class="card" style="border-left: 4px solid var(--color-warning);">
                <div style="display: flex; gap: 12px; align-items: flex-start; margin-bottom: 14px;">
                    <i data-lucide="alert-triangle" style="color: var(--color-warning); flex-shrink: 0; width: 22px; height: 22px;"></i>
                    <h3 class="card-title-sm" style="margin: 0;">Attention Needed</h3>
                </div>
                <ul style="display: flex; flex-direction: column; gap: 10px; font-size: 13px;">
                    <li style="display: flex; justify-content: space-between;">
                        <span>Auction ending soon:</span>
                        <strong class="text-gold" style="color: var(--color-gold-600);"><?= (int) ($s['auction_ending_soon'] ?? 0) ?> listings</strong>
                    </li>
                    <li style="display: flex; justify-content: space-between;">
                        <span>Pending bid requests:</span>
                        <strong class="text-gold" style="color: var(--color-gold-600);"><?= (int) ($s['pending_bids'] ?? 0) ?></strong>
                    </li>
                    <li style="display: flex; justify-content: space-between;">
                        <span>Pending payments:</span>
                        <strong class="text-gold" style="color: var(--color-gold-600);"><?= (int) ($s['pending_payments'] ?? 0) ?></strong>
                    </li>
                    <li style="display: flex; justify-content: space-between;">
                        <span>Deposit verifications:</span>
                        <strong class="text-gold" style="color: var(--color-gold-600);"><?= (int) ($s['pending_deposits'] ?? 0) ?></strong>
                    </li>
                    <li style="display: flex; justify-content: space-between;">
                        <span>Active reservations:</span>
                        <strong class="text-gold" style="color: var(--color-gold-600);"><?= (int) ($s['active_reservations'] ?? 0) ?></strong>
                    </li>
                </ul>
                <button class="btn btn-gold btn-sm mt-20" style="width: 100%;" onclick="window.location.href='<?= BASE_URL ?>/admin/inventory'">
                    Open Inventory
                </button>
            </div>
        </div>
    </div>
</div>

<?php include dirname(__DIR__) . '/admin/partials/footer.php'; ?>
