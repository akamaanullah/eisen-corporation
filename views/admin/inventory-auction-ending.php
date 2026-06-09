<?php
$pageTitle = 'Auction Ending Soon | Eisen Admin';
include dirname(__DIR__) . '/admin/partials/header.php';

$listings = $listings ?? [];
$days = (int) ($days ?? \App\Helpers\AdminStats::AUCTION_ALERT_DAYS);
$filter = $filter ?? 'all';
$totalCount = (int) ($totalCount ?? 0);
$overdueCount = (int) ($overdueCount ?? 0);
$todayCount = (int) ($todayCount ?? 0);
$upcomingCount = (int) ($upcomingCount ?? 0);

$basePageUrl = BASE_URL . '/admin/inventory/auction-ending';

function auctionEndingQuery(string $baseUrl, int $days, string $filter): string
{
    return $baseUrl . '?days=' . $days . '&filter=' . urlencode($filter);
}
?>

<?php include dirname(__DIR__) . '/admin/partials/auction-alerts.php'; ?>

<div class="inventory-page-content">
    <div class="page-header-container mb-30">
        <div class="header-title-group">
            <h1 class="page-title">Auction Ending Soon</h1>
            <p style="color: var(--color-text-muted); margin: 4px 0 0 0;">
                Listings with auction end dates in the next <?= $days ?> days
                (includes overdue up to <?= (int) \App\Helpers\AdminStats::AUCTION_OVERDUE_DAYS ?> days)
            </p>
        </div>
        <div class="header-actions" style="display: flex; gap: 12px;">
            <a class="btn btn-outline" href="<?= BASE_URL ?>/admin/inventory">
                <i data-lucide="arrow-left"></i>
                <span>Back to Inventory</span>
            </a>
        </div>
    </div>

    <div class="dashboard-stats-grid mb-30">
        <div class="stat-card">
            <div class="stat-card-header">
                <div class="stat-icon-box bg-soft-warning">
                    <i data-lucide="calendar-clock"></i>
                </div>
                <div class="stat-info">
                    <span class="stat-label">Total in Window</span>
                    <span class="stat-change" style="color: var(--color-warning);">Next <?= $days ?> days</span>
                </div>
            </div>
            <div class="stat-card-body">
                <h2 class="stat-value"><?= $totalCount ?></h2>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-card-header">
                <div class="stat-icon-box" style="background: rgba(239, 68, 68, 0.12); color: #b91c1c;">
                    <i data-lucide="alert-circle"></i>
                </div>
                <div class="stat-info">
                    <span class="stat-label">Overdue</span>
                </div>
            </div>
            <div class="stat-card-body">
                <h2 class="stat-value"><?= $overdueCount ?></h2>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-card-header">
                <div class="stat-icon-box bg-soft-warning">
                    <i data-lucide="alarm-clock"></i>
                </div>
                <div class="stat-info">
                    <span class="stat-label">Ends Today</span>
                </div>
            </div>
            <div class="stat-card-body">
                <h2 class="stat-value"><?= $todayCount ?></h2>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-card-header">
                <div class="stat-icon-box bg-soft-info">
                    <i data-lucide="timer"></i>
                </div>
                <div class="stat-info">
                    <span class="stat-label">Upcoming</span>
                </div>
            </div>
            <div class="stat-card-body">
                <h2 class="stat-value"><?= $upcomingCount ?></h2>
            </div>
        </div>
    </div>

    <div class="card mb-30" style="padding: 16px;">
        <div style="display: flex; flex-wrap: wrap; gap: 16px; align-items: center; justify-content: space-between;">
            <div class="inventory-tabs" style="margin-bottom: 0;">
                <a class="tab-btn <?= $filter === 'all' ? 'active' : '' ?>" href="<?= auctionEndingQuery($basePageUrl, $days, 'all') ?>">
                    All <span class="count-badge"><?= $totalCount ?></span>
                </a>
                <a class="tab-btn <?= $filter === 'overdue' ? 'active' : '' ?>" href="<?= auctionEndingQuery($basePageUrl, $days, 'overdue') ?>">
                    Overdue <span class="count-badge"><?= $overdueCount ?></span>
                </a>
                <a class="tab-btn <?= $filter === 'today' ? 'active' : '' ?>" href="<?= auctionEndingQuery($basePageUrl, $days, 'today') ?>">
                    Today <span class="count-badge"><?= $todayCount ?></span>
                </a>
                <a class="tab-btn <?= $filter === 'upcoming' ? 'active' : '' ?>" href="<?= auctionEndingQuery($basePageUrl, $days, 'upcoming') ?>">
                    Upcoming <span class="count-badge"><?= $upcomingCount ?></span>
                </a>
            </div>
            <form method="get" action="<?= $basePageUrl ?>" style="display: flex; gap: 10px; align-items: center;">
                <input type="hidden" name="filter" value="<?= htmlspecialchars($filter) ?>">
                <label for="daysFilter" style="font-size: 13px; color: var(--color-text-muted); white-space: nowrap;">Show next</label>
                <select class="form-control" id="daysFilter" name="days" style="width: 120px; margin-bottom: 0;" onchange="this.form.submit()">
                    <?php foreach ([7, 14, 30, 60, 90] as $option): ?>
                    <option value="<?= $option ?>" <?= $days === $option ? 'selected' : '' ?>><?= $option ?> days</option>
                    <?php endforeach; ?>
                </select>
            </form>
        </div>
    </div>

    <?php if (empty($listings)): ?>
    <div class="card text-center" style="padding: 48px 24px; text-align: center;">
        <div style="display: inline-flex; align-items: center; justify-content: center; width: 64px; height: 64px; background: rgba(201, 162, 39, 0.1); border-radius: 50%; margin-bottom: 16px;">
            <i data-lucide="calendar-check" style="width: 32px; height: 32px; color: var(--color-gold-500);"></i>
        </div>
        <h3 style="font-size: 18px; font-weight: 600; margin-bottom: 8px;">No listings in this window</h3>
        <p style="color: var(--color-text-muted); max-width: 420px; margin: 0 auto; font-size: 14px;">
            No active listings match the selected filter. Try a longer date range or check inventory auction end dates.
        </p>
    </div>
    <?php else: ?>
    <div class="card" style="padding: 0;">
        <div class="table-responsive">
            <table class="data-table-minimal">
                <thead>
                    <tr>
                        <th>Stock ID</th>
                        <th>Vehicle</th>
                        <th>Chassis</th>
                        <th>Auction House / Lot</th>
                        <th>End Date</th>
                        <th>Time Left</th>
                        <th>Status</th>
                        <th style="text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($listings as $item): ?>
                    <tr class="auction-ending-row auction-ending-row--<?= htmlspecialchars($item['urgency']) ?>">
                        <td>
                            <?php if (($item['type'] ?? '') === 'In-Stock'): ?>
                                <span class="badge badge-success" style="font-size: 10px;"><?= htmlspecialchars($item['stock_id']) ?></span>
                            <?php else: ?>
                                <span class="badge badge-info" style="font-size: 10px;"><?= htmlspecialchars($item['stock_id']) ?></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div style="display: flex; align-items: center; gap: 12px;">
                                <div class="vehicle-thumbnail" style="display: flex; align-items: center; justify-content: center; width: 48px; height: 36px; background: #ebebeb; border-radius: 4px; overflow: hidden; border: 1px solid var(--color-border);">
                                    <?php
                                    $hasValidImage = !empty($item['image']) && strpos($item['image'], '/public/uploads/') !== false;
                                    $imgSrc = $hasValidImage
                                        ? BASE_URL . htmlspecialchars($item['image'])
                                        : BASE_URL . '/public/image/car-placeholder.png';
                                    ?>
                                    <img src="<?= $imgSrc ?>" alt="Vehicle" style="width: 100%; height: 100%; object-fit: cover;">
                                </div>
                                <div>
                                    <strong><?= htmlspecialchars($item['vehicle']) ?></strong>
                                    <?php if (!empty($item['fob_price'])): ?>
                                    <div style="font-size: 11px; color: var(--color-text-muted);">$<?= number_format((float) $item['fob_price']) ?> FOB</div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </td>
                        <td><code><?= htmlspecialchars($item['chassis_number']) ?></code></td>
                        <td>
                            <?php if (!empty($item['auction_house']) || !empty($item['lot_number'])): ?>
                                <span><?= htmlspecialchars($item['auction_house']) ?></span>
                                <?php if (!empty($item['lot_number'])): ?>
                                <div style="font-size: 11px; color: var(--color-text-muted);">Lot <?= htmlspecialchars($item['lot_number']) ?></div>
                                <?php endif; ?>
                            <?php else: ?>
                                <span style="color: var(--color-text-muted);">—</span>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($item['auction_end_display']) ?></td>
                        <td>
                            <span class="admin-auction-alerts__badge admin-auction-alerts__badge--<?= htmlspecialchars($item['urgency']) ?>">
                                <?= htmlspecialchars($item['label']) ?>
                            </span>
                        </td>
                        <td>
                            <?php
                            $badge = 'badge-active';
                            if ($item['status'] === 'Reserved') {
                                $badge = 'badge-warning';
                            }
                            ?>
                            <span class="badge <?= $badge ?>"><?= htmlspecialchars($item['status']) ?></span>
                        </td>
                        <td style="text-align: right;">
                            <a class="btn btn-primary btn-sm" href="<?= htmlspecialchars($item['edit_url']) ?>">Edit listing</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php include dirname(__DIR__) . '/admin/partials/footer.php'; ?>
