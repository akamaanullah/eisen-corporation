<?php 
$pageTitle = "Inventory Management | Eisen Admin";
$pageScript = "inventory.js";
include dirname(__DIR__) . '/admin/partials/header.php'; 

// Calculate portfolio values and inventory counts dynamically
$totalCount = count($cars);
$inStockCount = 0;
$auctionCount = 0;
$totalValue = 0;
foreach ($cars as $car) {
    $totalValue += $car['price'];
    if ($car['type'] === 'In-Stock') {
        $inStockCount++;
    } else if ($car['type'] === 'Auction') {
        $auctionCount++;
    }
}
?>

<div class="inventory-page-content">
    <div class="page-header-container mb-30">
        <div class="header-title-group">
            <h1 class="page-title">Inventory Catalog</h1>
            <p style="color: var(--color-text-muted); margin: 4px 0 0 0;">Manage direct In-Stock imports and live Auction lots</p>
        </div>
        <div class="header-actions" style="display: flex; gap: 12px;">
            <button class="btn btn-outline" id="syncAuctionsBtn">
                <i data-lucide="refresh-cw"></i>
                <span>Sync Auction API</span>
            </button>
            <button class="btn btn-primary" id="openAddCarModalBtn" onclick="window.location.href='<?= BASE_URL ?>/admin/inventory/new'">
                <i data-lucide="plus-circle"></i>
                <span>Add New Vehicle</span>
            </button>
        </div>
    </div>

    <!-- Inventory Stats KPI Row -->
    <div class="dashboard-stats-grid mb-30">
        <div class="stat-card">
            <div class="stat-card-header">
                <div class="stat-icon-box bg-soft-primary">
                    <i data-lucide="car"></i>
                </div>
                <div class="stat-info">
                    <span class="stat-label">Total Listings</span>
                    <span class="stat-change text-success">Active Catalog</span>
                </div>
            </div>
            <div class="stat-card-body">
                <h2 class="stat-value"><?= $totalCount ?></h2>
                <p style="margin: 6px 0 0 0; font-size: 11px; color: var(--color-text-muted);">
                    Vehicles currently in the system
                </p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-card-header">
                <div class="stat-icon-box bg-soft-success">
                    <i data-lucide="warehouse"></i>
                </div>
                <div class="stat-info">
                    <span class="stat-label">In-Stock Imports</span>
                    <span class="stat-change text-success">+<?= count(array_filter($cars, fn($c) => $c['type'] === 'In-Stock' && $c['status'] === 'Available')) ?> Available</span>
                </div>
            </div>
            <div class="stat-card-body">
                <h2 class="stat-value"><?= $inStockCount ?></h2>
                <p style="margin: 6px 0 0 0; font-size: 11px; color: var(--color-text-muted);">
                    Direct imports in local yard
                </p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-card-header">
                <div class="stat-icon-box bg-soft-info">
                    <i data-lucide="gavel"></i>
                </div>
                <div class="stat-info">
                    <span class="stat-label">Auction Lots</span>
                    <span class="stat-change text-success">Live Bidding</span>
                </div>
            </div>
            <div class="stat-card-body">
                <h2 class="stat-value"><?= $auctionCount ?></h2>
                <p style="margin: 6px 0 0 0; font-size: 11px; color: var(--color-text-muted);">
                    Lots linked via external API
                </p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-card-header">
                <div class="stat-icon-box bg-soft-warning">
                    <i data-lucide="dollar-sign"></i>
                </div>
                <div class="stat-info">
                    <span class="stat-label">Inventory Valuation</span>
                    <span class="stat-change text-gold" style="color: var(--color-gold-500);">FOB Portfolio</span>
                </div>
            </div>
            <div class="stat-card-body">
                <h2 class="stat-value text-gold" style="color: var(--color-gold-500);">$<?= number_format($totalValue) ?></h2>
                <p style="margin: 6px 0 0 0; font-size: 11px; color: var(--color-text-muted);">
                    Cumulative listing valuation
                </p>
            </div>
        </div>
    </div>

    <!-- Filters Toolbar -->
    <div class="card mb-30" style="padding: 16px;">
        <div style="display: grid; grid-template-columns: 2fr 1.2fr 0.8fr; gap: 16px; align-items: center;">
            <div class="form-group" style="margin-bottom: 0; position: relative;">
                <input type="text" class="form-control" id="searchFilter" placeholder="Search by Make, Model, or Chassis VIN...">
                <i data-lucide="search" style="position: absolute; right: 14px; top: 12px; color: var(--color-silver-400); width: 18px; height: 18px;"></i>
            </div>
            
            <div class="form-group" style="margin-bottom: 0;">
                <select class="form-control" id="statusFilter">
                    <option value="">All Statuses</option>
                    <option value="Available">Available</option>
                    <option value="Reserved">Reserved</option>
                    <option value="Sold">Sold</option>
                </select>
            </div>

            <button class="btn btn-outline" id="clearFiltersBtn" style="height: 100%;">
                <i data-lucide="filter-x"></i>
                <span>Reset</span>
            </button>
        </div>
    </div>

    <!-- Switcher Tabs for In-Stock and Auction separation -->
    <div class="inventory-tabs mb-20">
        <button class="tab-btn active" data-filter-type="all">
            <i data-lucide="layers" style="width: 15px; height: 15px;"></i>
            <span>All Vehicles</span>
            <span class="count-badge"><?= $totalCount ?></span>
        </button>
        <button class="tab-btn" data-filter-type="In-Stock">
            <i data-lucide="warehouse" style="width: 15px; height: 15px;"></i>
            <span>In-Stock Imports</span>
            <span class="count-badge"><?= $inStockCount ?></span>
        </button>
        <button class="tab-btn" data-filter-type="Auction">
            <i data-lucide="gavel" style="width: 15px; height: 15px;"></i>
            <span>Live Auction Lots</span>
            <span class="count-badge"><?= $auctionCount ?></span>
        </button>
    </div>

    <!-- Empty State / Under Development for Auction tab -->
    <div id="auctionUnderDevelopmentCard" class="card text-center mb-30" style="display: none; padding: 45px; text-align: center; background: var(--color-bg-light); border: 1px solid var(--color-border);">
        <div style="display: inline-flex; align-items: center; justify-content: center; width: 64px; height: 64px; background: rgba(201, 162, 39, 0.1); border-radius: 50%; margin-bottom: 16px;">
            <i data-lucide="gavel" style="width: 32px; height: 32px; color: var(--color-gold-500);"></i>
        </div>
        <h3 style="font-size: 18px; font-weight: 600; margin-bottom: 8px; font-family: 'Montserrat', sans-serif;">Japan Live Auction Feed</h3>
        <p style="color: var(--color-text-muted); max-width: 400px; margin: 0 auto 20px auto; font-size: 14px; line-height: 1.6;">
            The Japan live auction system API integration is currently under development. Stay tuned for real-time bidding!
        </p>
        <div>
            <span class="badge" style="background-color: #ecc94b; color: #744210; padding: 6px 12px; font-size: 11px; font-weight: 600; text-transform: uppercase; border-radius: 4px; letter-spacing: 0.5px;">Under Development</span>
        </div>
    </div>

    <!-- Inventory Table Grid -->
    <div class="card" id="inventoryTableCard" style="padding: 0;">
        <div class="table-responsive">
            <table class="data-table-minimal" id="inventoryTable">
                <thead>
                    <tr>
                        <th>Stock ID</th>
                        <th>Vehicle Name</th>
                        <th>Chassis Number</th>
                        <th>Specs (Mileage/Trans)</th>
                        <th>FOB Price</th>
                        <th>Grade</th>
                        <th>Status</th>
                        <th>Featured</th>
                        <th style="text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cars as $car): ?>
                    <tr data-db-id="<?= $car['db_id'] ?>" data-type="<?= $car['type'] ?>" data-status="<?= $car['status'] ?>">
                        <td>
                            <?php if ($car['type'] === 'In-Stock'): ?>
                                <span class="badge badge-success" style="font-size: 10px;"><?= $car['id'] ?></span>
                            <?php else: ?>
                                <span class="badge badge-info" style="font-size: 10px;"><?= $car['id'] ?></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div style="display: flex; align-items: center; gap: 12px;">
                                <div class="vehicle-thumbnail" style="display: flex; align-items: center; justify-content: center; width: 48px; height: 36px; background: #ebebeb; border-radius: 4px; overflow: hidden; border: 1px solid var(--color-border);">
                                    <?php 
                                    $hasValidImage = !empty($car['image']) && strpos($car['image'], '/public/uploads/') !== false;
                                    if ($hasValidImage): 
                                    ?>
                                        <img src="<?= BASE_URL . htmlspecialchars($car['image']) ?>" alt="Vehicle" style="width: 100%; height: 100%; object-fit: cover;">
                                    <?php else: ?>
                                        <!-- Exact replica of user's uploaded "No Image" placeholder -->
                                        <svg viewBox="0 0 100 70" style="width: 100%; height: 100%; background: #ebebeb;" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M 15,35 C 17,29 25,23 38,20 C 43,20 52,20 60,20 C 73,20 81,28 85,34 C 87,37 88,40 88,43 L 88,46 L 81,46 C 80,41 75,37 70,37 C 65,37 60,41 59,46 L 41,46 C 40,41 35,37 30,37 C 25,37 20,41 19,46 L 12,46 L 12,42 C 12,38 13,36 15,35 Z" fill="#b0b0b0" />
                                            <path d="M 39,23 L 52,23 L 52,31 L 37,31 Q 36,27 39,23 Z" fill="#ebebeb" />
                                            <path d="M 55,23 L 66,23 Q 72,23 76,29 L 77,31 L 55,31 Z" fill="#ebebeb" />
                                            <circle cx="30" cy="46" r="6.5" fill="#ebebeb" stroke="#b0b0b0" stroke-width="2.5" />
                                            <circle cx="30" cy="46" r="3" fill="#ebebeb" />
                                            <circle cx="70" cy="46" r="6.5" fill="#ebebeb" stroke="#b0b0b0" stroke-width="2.5" />
                                            <circle cx="70" cy="46" r="3" fill="#ebebeb" />
                                            <text x="50" y="62" font-size="10" font-family="'Segoe UI', sans-serif" font-weight="600" fill="#7d7d7d" text-anchor="middle">No Image</text>
                                        </svg>
                                    <?php endif; ?>
                                </div>
                                <div>
                                    <strong><?= htmlspecialchars($car['make'] . ' ' . $car['model']) ?></strong>
                                    <div style="font-size: 11px; color: var(--color-text-muted);"><?= $car['year'] ?> Model</div>
                                </div>
                            </div>
                        </td>
                        <td><code><?= htmlspecialchars($car['chassis']) ?></code></td>
                        <td>
                            <span><?= htmlspecialchars($car['mileage']) ?></span>
                            <div style="font-size: 11px; color: var(--color-text-muted);"><?= $car['transmission'] ?></div>
                        </td>
                        <td><strong>$<?= number_format($car['price']) ?></strong></td>
                        <td><span style="font-weight: 600;"><?= htmlspecialchars($car['grade']) ?></span></td>
                        <td>
                            <?php 
                            $badge = 'badge-active';
                            $extraStyle = '';
                            if ($car['status'] === 'Reserved') $badge = 'badge-warning';
                            else if ($car['status'] === 'Sold') $badge = 'badge-danger';
                            else if ($car['status'] === 'Archived') {
                                $badge = 'badge-outline';
                                $extraStyle = 'background-color: #718096; color: white; border: none;';
                            }
                            ?>
                            <span class="badge <?= $badge ?>" style="<?= $extraStyle ?>"><?= $car['status'] ?></span>
                        </td>
                        <td>
                            <label class="switch-toggle">
                                <input type="checkbox" class="featured-toggle-btn" <?= $car['featured'] ? 'checked' : '' ?>>
                                <span class="slider-round"></span>
                            </label>
                        </td>
                        <td style="text-align: right;">
                            <div style="display: flex; justify-content: flex-end; gap: 8px; padding-right: 16px;">
                                <button class="btn-icon-sm edit-car-btn" title="Edit Listing">
                                    <i data-lucide="edit-3"></i>
                                </button>
                                <button class="btn-icon-sm duplicate-car-btn" title="Duplicate Listing">
                                    <i data-lucide="copy"></i>
                                </button>
                                <button class="btn-icon-sm archive-car-btn" title="Archive Listing" style="color: var(--color-text-muted);">
                                    <i data-lucide="archive"></i>
                                </button>
                                <button class="btn-icon-sm delete-car-btn" title="Delete Listing" style="color: var(--color-danger);">
                                    <i data-lucide="trash-2"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>


<?php include dirname(__DIR__) . '/admin/partials/footer.php'; ?>
