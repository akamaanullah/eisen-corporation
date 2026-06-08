<?php 
$pageTitle = "Frontstore Content Management | Eisen Admin";
$pageScript = "content-management.js";
include dirname(__DIR__) . '/admin/partials/header.php'; 
?>

<div class="content-management-page">
    <div class="page-header-container mb-30">
        <div class="header-title-group">
            <h1 class="page-title">Frontstore Content Management</h1>
            <p style="color: var(--color-text-muted); margin: 4px 0 0 0;">Control dynamic sliders, directory partners, shipping parameters, and vehicle options.</p>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="tabs-container mb-30" style="background: var(--color-white); border-radius: var(--radius-md); border: 1px solid var(--color-silver-200); padding: 5px 20px;">
        <div class="modal-tabs" style="margin-bottom: 0; border-bottom: none;">
            <button class="modal-tab-btn active" data-tab="sliders">
                <i data-lucide="layout" style="width: 16px; height: 16px; display: inline-block; vertical-align: text-bottom; margin-right: 6px;"></i>
                Hero Sliders
            </button>
            <button class="modal-tab-btn" data-tab="partners">
                <i data-lucide="users" style="width: 16px; height: 16px; display: inline-block; vertical-align: text-bottom; margin-right: 6px;"></i>
                Directory Partners
            </button>
            <button class="modal-tab-btn" data-tab="shipping">
                <i data-lucide="ship" style="width: 16px; height: 16px; display: inline-block; vertical-align: text-bottom; margin-right: 6px;"></i>
                Shipping Destinations
            </button>
            <button class="modal-tab-btn" data-tab="make-models">
                <i data-lucide="car" style="width: 16px; height: 16px; display: inline-block; vertical-align: text-bottom; margin-right: 6px;"></i>
                Makes & Models
            </button>
        </div>
    </div>

    <!-- TAB 1: HERO SLIDERS PANEL -->
    <div class="tab-panel active" id="panel-sliders">
        <div class="card">
            <div class="card-header-flex">
                <h3 class="card-title-sm">Hero Slide Banners</h3>
                <button class="btn btn-primary btn-sm" id="btn-add-slider">
                    <i data-lucide="plus-circle"></i>
                    <span>Add Hero Slide</span>
                </button>
            </div>
            
            <div class="table-responsive">
                <table class="data-table-minimal">
                    <thead>
                        <tr>
                            <th style="width: 60px;">ID</th>
                            <th style="width: 120px;">Banner Image</th>
                            <th>Title / Subtitle</th>
                            <th>Link URL</th>
                            <th style="width: 100px; text-align: center;">Sort Order</th>
                            <th style="width: 100px; text-align: center;">Status</th>
                            <th style="width: 120px; text-align: right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($sliders)): ?>
                            <tr>
                                <td colspan="7" style="text-align: center; color: var(--color-text-muted); padding: 30px;">
                                    No hero sliders found. Add your first slider banner.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($sliders as $slide): ?>
                                <tr data-id="<?= $slide['id'] ?>" 
                                    data-title="<?= htmlspecialchars($slide['title']) ?>" 
                                    data-subtitle="<?= htmlspecialchars($slide['subtitle']) ?>"
                                    data-link_url="<?= htmlspecialchars($slide['link_url']) ?>"
                                    data-sort_order="<?= $slide['sort_order'] ?>"
                                    data-status="<?= $slide['status'] ?>"
                                    data-image_url="<?= htmlspecialchars($slide['image_url']) ?>">
                                    <td><?= $slide['id'] ?></td>
                                    <td>
                                        <img src="<?= htmlspecialchars($slide['image_url']) ?>" alt="Slide Image" style="width: 100px; height: 50px; object-fit: cover; border-radius: var(--radius-sm); border: 1px solid var(--color-silver-300);">
                                    </td>
                                    <td>
                                        <div style="font-weight: 700; color: var(--color-navy-950);"><?= htmlspecialchars($slide['title']) ?: 'Untitled Slide' ?></div>
                                        <div style="font-size: 11px; color: var(--color-text-muted); margin-top: 3px; max-width: 400px; text-overflow: ellipsis; overflow: hidden; white-space: nowrap;"><?= htmlspecialchars($slide['subtitle']) ?></div>
                                    </td>
                                    <td><code><?= htmlspecialchars($slide['link_url']) ?></code></td>
                                    <td style="text-align: center;"><strong><?= $slide['sort_order'] ?></strong></td>
                                    <td style="text-align: center;">
                                        <?php if ($slide['status'] == 1): ?>
                                            <span class="badge badge-success">Active</span>
                                        <?php else: ?>
                                            <span class="badge badge-danger">Inactive</span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="text-align: right;">
                                        <button class="btn-icon-sm edit-slider-btn" title="Edit">
                                            <i data-lucide="edit"></i>
                                        </button>
                                        <form method="POST" action="<?= BASE_URL ?>/admin/content/slider/delete/<?= $slide['id'] ?>" style="display:inline-block;" onsubmit="return confirm('Are you sure you want to delete this hero slider banner?');">
                                            <?= $this->csrf_field() ?>
                                            <button type="submit" class="btn-icon-sm" style="color: var(--color-danger); border-color: rgba(239, 68, 68, 0.2); background: var(--color-danger-soft);" title="Delete">
                                                <i data-lucide="trash-2"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- TAB 2: DIRECTORY PARTNERS PANEL -->
    <div class="tab-panel" id="panel-partners" style="display: none;">
        <div class="card">
            <div class="card-header-flex">
                <h3 class="card-title-sm">Directory Partners</h3>
                <button class="btn btn-primary btn-sm" id="btn-add-partner">
                    <i data-lucide="plus-circle"></i>
                    <span>Add Partner</span>
                </button>
            </div>
            
            <div class="table-responsive">
                <table class="data-table-minimal">
                    <thead>
                        <tr>
                            <th style="width: 60px;">ID</th>
                            <th style="width: 100px;">Logo</th>
                            <th>Partner Name</th>
                            <th>Partner Type</th>
                            <th style="width: 120px; text-align: center;">Sort Order</th>
                            <th style="width: 120px; text-align: right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($partners)): ?>
                            <tr>
                                <td colspan="6" style="text-align: center; color: var(--color-text-muted); padding: 30px;">
                                    No directory partners found. Add your first partner.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($partners as $partner): ?>
                                <tr data-id="<?= $partner['id'] ?>" 
                                    data-name="<?= htmlspecialchars($partner['name']) ?>" 
                                    data-type="<?= $partner['type'] ?>"
                                    data-sort_order="<?= $partner['sort_order'] ?>"
                                    data-logo_url="<?= htmlspecialchars($partner['logo_url']) ?>">
                                    <td><?= $partner['id'] ?></td>
                                    <td>
                                        <div style="background: #050d1a; padding: 4px; border-radius: var(--radius-sm); display: inline-block;">
                                            <img src="<?= htmlspecialchars($partner['logo_url']) ?>" alt="Logo" style="height: 30px; max-width: 80px; object-fit: contain; display: block;">
                                        </div>
                                    </td>
                                    <td><strong><?= htmlspecialchars($partner['name']) ?></strong></td>
                                    <td>
                                        <?php if ($partner['type'] === 'dealer'): ?>
                                            <span class="badge badge-info">Dealer Partner</span>
                                        <?php elseif ($partner['type'] === 'service'): ?>
                                            <span class="badge badge-success">Service Center</span>
                                        <?php else: ?>
                                            <span class="badge badge-warning">Insurance Partner</span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="text-align: center;"><strong><?= $partner['sort_order'] ?></strong></td>
                                    <td style="text-align: right;">
                                        <button class="btn-icon-sm edit-partner-btn" title="Edit">
                                            <i data-lucide="edit"></i>
                                        </button>
                                        <form method="POST" action="<?= BASE_URL ?>/admin/content/partner/delete/<?= $partner['id'] ?>" style="display:inline-block;" onsubmit="return confirm('Are you sure you want to delete this directory partner?');">
                                            <?= $this->csrf_field() ?>
                                            <button type="submit" class="btn-icon-sm" style="color: var(--color-danger); border-color: rgba(239, 68, 68, 0.2); background: var(--color-danger-soft);" title="Delete">
                                                <i data-lucide="trash-2"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- TAB 3: SHIPPING DESTINATIONS PANEL -->
    <div class="tab-panel" id="panel-shipping" style="display: none;">
        <div class="card">
            <div class="card-header-flex">
                <h3 class="card-title-sm">Shipping Ports & Destinations</h3>
                <button class="btn btn-primary btn-sm" id="btn-add-shipping">
                    <i data-lucide="plus-circle"></i>
                    <span>Add Destination</span>
                </button>
            </div>
            
            <div class="table-responsive">
                <table class="data-table-minimal">
                    <thead>
                        <tr>
                            <th style="width: 80px;">ID</th>
                            <th>Country</th>
                            <th>Port Name</th>
                            <th style="width: 120px; text-align: center;">Status</th>
                            <th style="width: 120px; text-align: right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($shipping)): ?>
                            <tr>
                                <td colspan="5" style="text-align: center; color: var(--color-text-muted); padding: 30px;">
                                    No shipping destinations defined. Add a destination.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($shipping as $dest): ?>
                                <tr data-id="<?= $dest['id'] ?>" 
                                    data-country="<?= htmlspecialchars($dest['country']) ?>" 
                                    data-port="<?= htmlspecialchars($dest['port']) ?>"
                                    data-status="<?= $dest['status'] ?>">
                                    <td><?= $dest['id'] ?></td>
                                    <td><strong><?= htmlspecialchars($dest['country']) ?></strong></td>
                                    <td><code><?= htmlspecialchars($dest['port']) ?></code></td>
                                    <td style="text-align: center;">
                                        <?php if ($dest['status'] == 1): ?>
                                            <span class="badge badge-success">Active</span>
                                        <?php else: ?>
                                            <span class="badge badge-danger">Inactive</span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="text-align: right;">
                                        <button class="btn-icon-sm edit-shipping-btn" title="Edit">
                                            <i data-lucide="edit"></i>
                                        </button>
                                        <form method="POST" action="<?= BASE_URL ?>/admin/content/shipping/delete/<?= $dest['id'] ?>" style="display:inline-block;" onsubmit="return confirm('Are you sure you want to delete this shipping destination?');">
                                            <?= $this->csrf_field() ?>
                                            <button type="submit" class="btn-icon-sm" style="color: var(--color-danger); border-color: rgba(239, 68, 68, 0.2); background: var(--color-danger-soft);" title="Delete">
                                                <i data-lucide="trash-2"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- TAB 4: MAKES & MODELS PANEL -->
    <div class="tab-panel" id="panel-make-models" style="display: none;">
        <div class="card">
            <div class="card-header-flex">
                <h3 class="card-title-sm">Master Vehicles Make & Model Catalog</h3>
                <button class="btn btn-primary btn-sm" id="btn-add-make-model">
                    <i data-lucide="plus-circle"></i>
                    <span>Add Make & Model</span>
                </button>
            </div>
            
            <div class="table-responsive">
                <table class="data-table-minimal">
                    <thead>
                        <tr>
                            <th style="width: 80px;">ID</th>
                            <th>Manufacturer (Make)</th>
                            <th>Model Name</th>
                            <th style="width: 120px; text-align: right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($makeModels)): ?>
                            <tr>
                                <td colspan="4" style="text-align: center; color: var(--color-text-muted); padding: 30px;">
                                    No vehicle make & models registered. Add a mapping.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($makeModels as $mm): ?>
                                <tr data-id="<?= $mm['id'] ?>" 
                                    data-make="<?= htmlspecialchars($mm['make']) ?>" 
                                    data-model="<?= htmlspecialchars($mm['model']) ?>">
                                    <td><?= $mm['id'] ?></td>
                                    <td><strong><?= htmlspecialchars($mm['make']) ?></strong></td>
                                    <td><?= htmlspecialchars($mm['model']) ?></td>
                                    <td style="text-align: right;">
                                        <button class="btn-icon-sm edit-make-model-btn" title="Edit">
                                            <i data-lucide="edit"></i>
                                        </button>
                                        <form method="POST" action="<?= BASE_URL ?>/admin/content/make-model/delete/<?= $mm['id'] ?>" style="display:inline-block;" onsubmit="return confirm('Are you sure you want to delete this Make/Model mapping?');">
                                            <?= $this->csrf_field() ?>
                                            <button type="submit" class="btn-icon-sm" style="color: var(--color-danger); border-color: rgba(239, 68, 68, 0.2); background: var(--color-danger-soft);" title="Delete">
                                                <i data-lucide="trash-2"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- ==========================================
     MODALS
     ========================================== -->

<!-- Modal 1: Hero Slider Modal -->
<div class="modal-backdrop" id="sliderModal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3 style="margin: 0;" id="sliderModalTitle">Add Hero Slide</h3>
            <button class="modal-close-btn">&times;</button>
        </div>
        <form action="<?= BASE_URL ?>/admin/content/slider/save" method="POST" enctype="multipart/form-data">
            <?= $this->csrf_field() ?>
            <input type="hidden" name="id" id="slider-id" value="0">
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Slide Title</label>
                    <input type="text" name="title" id="slider-title" class="form-control" placeholder="e.g. Eisen Corporation | Premium Quality Used Cars">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Slide Subtitle</label>
                    <input type="text" name="subtitle" id="slider-subtitle" class="form-control" placeholder="e.g. Exporting top-tier Japanese vehicles directly...">
                </div>

                <div class="form-group">
                    <label class="form-label">Link URL (Routing Path)</label>
                    <input type="text" name="link_url" id="slider-link_url" class="form-control" placeholder="e.g. /listing or https://google.com">
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                    <div class="form-group">
                        <label class="form-label">Sort Order</label>
                        <input type="number" name="sort_order" id="slider-sort_order" class="form-control" value="0" min="0">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Visibility Status</label>
                        <select name="status" id="slider-status" class="form-control">
                            <option value="1">Active (Visible)</option>
                            <option value="0">Inactive (Hidden)</option>
                        </select>
                    </div>
                </div>

                <div class="form-group" style="border-top: 1px solid var(--color-silver-200); padding-top: 16px;">
                    <label class="form-label">Upload Slide Banner Image *</label>
                    <input type="file" name="image_file" class="form-control" accept="image/*">
                    <p style="font-size: 11px; color: var(--color-text-muted); margin: 4px 0 0 0;">Recommended aspect ratio: 21:9 or similar (e.g. 1920x820). Max 5MB.</p>
                </div>

                <div class="form-group" style="text-align: center; margin: 12px 0;">
                    <strong style="color: var(--color-text-muted); font-size: 11px;">— OR —</strong>
                </div>

                <div class="form-group">
                    <label class="form-label">External Image URL</label>
                    <input type="text" name="image_url" id="slider-image_url" class="form-control" placeholder="https://example.com/banner.jpg">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline btn-close-modal">Cancel</button>
                <button type="submit" class="btn btn-gold">Save Slide</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal 2: Directory Partner Modal -->
<div class="modal-backdrop" id="partnerModal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3 style="margin: 0;" id="partnerModalTitle">Add Partner</h3>
            <button class="modal-close-btn">&times;</button>
        </div>
        <form action="<?= BASE_URL ?>/admin/content/partner/save" method="POST" enctype="multipart/form-data">
            <?= $this->csrf_field() ?>
            <input type="hidden" name="id" id="partner-id" value="0">
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Partner Name *</label>
                    <input type="text" name="name" id="partner-name" class="form-control" required placeholder="e.g. Toyota, Nissan">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Partner Category *</label>
                    <select name="type" id="partner-type" class="form-control" required>
                        <option value="dealer">Dealers Directory</option>
                        <option value="service">Service Stations Directory</option>
                        <option value="insurance">Insurance Partners Directory</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Sort Order</label>
                    <input type="number" name="sort_order" id="partner-sort_order" class="form-control" value="0" min="0">
                </div>

                <div class="form-group" style="border-top: 1px solid var(--color-silver-200); padding-top: 16px;">
                    <label class="form-label">Upload Partner Logo *</label>
                    <input type="file" name="logo_file" class="form-control" accept="image/*">
                    <p style="font-size: 11px; color: var(--color-text-muted); margin: 4px 0 0 0;">Prefer transparent PNG logos. Max 2MB.</p>
                </div>

                <div class="form-group" style="text-align: center; margin: 12px 0;">
                    <strong style="color: var(--color-text-muted); font-size: 11px;">— OR —</strong>
                </div>

                <div class="form-group">
                    <label class="form-label">External Logo URL</label>
                    <input type="text" name="logo_url" id="partner-logo_url" class="form-control" placeholder="https://example.com/logo.png">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline btn-close-modal">Cancel</button>
                <button type="submit" class="btn btn-gold">Save Partner</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal 3: Shipping Destination Modal -->
<div class="modal-backdrop" id="shippingModal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3 style="margin: 0;" id="shippingModalTitle">Add Shipping Destination</h3>
            <button class="modal-close-btn">&times;</button>
        </div>
        <form action="<?= BASE_URL ?>/admin/content/shipping/save" method="POST">
            <?= $this->csrf_field() ?>
            <input type="hidden" name="id" id="shipping-id" value="0">
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Country Name *</label>
                    <input type="text" name="country" id="shipping-country" class="form-control" required placeholder="e.g. PAKISTAN, KENYA" style="text-transform: uppercase;">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Port Name *</label>
                    <input type="text" name="port" id="shipping-port" class="form-control" required placeholder="e.g. KARACHI, MOMBASA" style="text-transform: uppercase;">
                </div>

                <div class="form-group">
                    <label class="form-label">Status</label>
                    <select name="status" id="shipping-status" class="form-control">
                        <option value="1">Active (Selectable)</option>
                        <option value="0">Inactive (Hidden)</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline btn-close-modal">Cancel</button>
                <button type="submit" class="btn btn-gold">Save Destination</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal 4: Make & Model Modal -->
<div class="modal-backdrop" id="makeModelModal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3 style="margin: 0;" id="makeModelModalTitle">Add Make & Model</h3>
            <button class="modal-close-btn">&times;</button>
        </div>
        <form action="<?= BASE_URL ?>/admin/content/make-model/save" method="POST">
            <?= $this->csrf_field() ?>
            <input type="hidden" name="id" id="makeModel-id" value="0">
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Manufacturer (Make) *</label>
                    <input type="text" name="make" id="makeModel-make" class="form-control" required placeholder="e.g. Toyota, Honda, Nissan">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Model Name *</label>
                    <input type="text" name="model" id="makeModel-model" class="form-control" required placeholder="e.g. Prius, Civic, Skyline">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline btn-close-modal">Cancel</button>
                <button type="submit" class="btn btn-gold">Save Mapping</button>
            </div>
        </form>
    </div>
</div>

<?php include dirname(__DIR__) . '/admin/partials/footer.php'; ?>
