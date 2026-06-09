<?php
$auctionAlerts = $auctionAlerts ?? [];
$auctionAlertsTotal = (int) ($auctionAlertsTotal ?? count($auctionAlerts));

if ($auctionAlertsTotal <= 0) {
    return;
}

$previewAlerts = !empty($auctionAlerts)
    ? $auctionAlerts
    : \App\Helpers\AdminStats::getAuctionEndAlerts(null, \App\Helpers\AdminStats::AUCTION_ALERT_DAYS, \App\Helpers\AdminStats::AUCTION_BANNER_PREVIEW);
$viewAllUrl = BASE_URL . '/admin/inventory/auction-ending';
$remaining = max(0, $auctionAlertsTotal - count($previewAlerts));
?>
<div class="admin-auction-alerts mb-30" role="region" aria-label="Auction end date alerts">
    <div class="admin-auction-alerts__head">
        <div class="admin-auction-alerts__title-wrap">
            <i data-lucide="bell-ring"></i>
            <div>
                <h2 class="admin-auction-alerts__title">Auction End Date Alerts</h2>
                <p class="admin-auction-alerts__subtitle">
                    <?= $auctionAlertsTotal ?> listing<?= $auctionAlertsTotal === 1 ? '' : 's' ?> ending within the next <?= (int) \App\Helpers\AdminStats::AUCTION_ALERT_DAYS ?> days
                </p>
            </div>
        </div>
        <a class="btn btn-outline btn-sm" href="<?= htmlspecialchars($viewAllUrl) ?>">
            View all (<?= $auctionAlertsTotal ?>)
        </a>
    </div>
    <?php if (!empty($previewAlerts)): ?>
    <ul class="admin-auction-alerts__list">
        <?php foreach ($previewAlerts as $alert): ?>
        <li class="admin-auction-alerts__item admin-auction-alerts__item--<?= htmlspecialchars($alert['urgency']) ?>">
            <div class="admin-auction-alerts__meta">
                <strong><?= htmlspecialchars($alert['stock_id']) ?></strong>
                <span><?= htmlspecialchars($alert['vehicle']) ?></span>
                <?php if (!empty($alert['auction_house']) || !empty($alert['lot_number'])): ?>
                <span class="admin-auction-alerts__auction">
                    <?php if (!empty($alert['auction_house'])): ?>
                        <?= htmlspecialchars($alert['auction_house'], ENT_QUOTES, 'UTF-8') ?>
                    <?php endif; ?>
                    <?php if (!empty($alert['lot_number'])): ?>
                        <?php if (!empty($alert['auction_house'])): ?> | <?php endif; ?>
                        Lot <?= htmlspecialchars($alert['lot_number'], ENT_QUOTES, 'UTF-8') ?>
                    <?php endif; ?>
                </span>
                <?php endif; ?>
            </div>
            <div class="admin-auction-alerts__actions">
                <span class="admin-auction-alerts__badge admin-auction-alerts__badge--<?= htmlspecialchars($alert['urgency']) ?>">
                    <?= htmlspecialchars($alert['label']) ?>
                </span>
                <span class="admin-auction-alerts__date"><?= htmlspecialchars($alert['auction_end_display']) ?></span>
                <a class="btn btn-primary btn-sm" href="<?= htmlspecialchars($alert['edit_url']) ?>">Edit listing</a>
            </div>
        </li>
        <?php endforeach; ?>
    </ul>
    <?php if ($remaining > 0): ?>
    <div class="admin-auction-alerts__footer">
        <a href="<?= htmlspecialchars($viewAllUrl) ?>">+ <?= $remaining ?> more listing<?= $remaining === 1 ? '' : 's' ?> - open full list</a>
    </div>
    <?php endif; ?>
    <?php endif; ?>
</div>
