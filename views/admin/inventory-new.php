<?php 
$pageTitle = "Add New Vehicle | Eisen Admin";
$pageScript = "inventory-new.js";
include dirname(__DIR__) . '/admin/partials/header.php'; 
?>

<style>
    .inventory-new-content {
        padding: 10px 0;
    }
    .form-grid-2x {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 24px;
        align-items: start;
    }
    .options-group-card {
        background: var(--color-white);
        border: 1px solid var(--color-silver-200);
        border-radius: var(--radius-md);
        padding: 20px;
        margin-bottom: 24px;
    }
    .options-group-title {
        margin: 0 0 16px 0;
        font-size: 15px;
        font-weight: 700;
        color: var(--color-navy-950);
        border-bottom: 1px solid var(--color-silver-200);
        padding-bottom: 10px;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .checkbox-chips-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
        gap: 12px;
    }
    .checkbox-chip-label {
        display: flex;
        align-items: center;
        gap: 10px;
        background: var(--color-silver-100);
        border: 1px solid var(--color-silver-300);
        border-radius: var(--radius-sm);
        padding: 10px 14px;
        cursor: pointer;
        user-select: none;
        transition: all var(--transition-fast);
    }
    .checkbox-chip-label:hover {
        background: var(--color-silver-200);
        border-color: var(--color-silver-400);
    }
    .checkbox-chip-label input[type="checkbox"] {
        width: 16px;
        height: 16px;
        accent-color: var(--color-gold-500);
        cursor: pointer;
    }
    .checkbox-chip-label.is-active {
        background: rgba(201, 162, 39, 0.08);
        border-color: var(--color-gold-500);
    }
    .checkbox-chip-label.is-active .chip-label-text {
        font-weight: 600;
        color: var(--color-gold-700);
    }
    .pricing-fields-grid {
        display: flex;
        flex-direction: column;
        gap: 14px;
    }
    .photo-slots-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 8px;
        margin-top: 12px;
    }
    .photo-slot {
        aspect-ratio: 1;
        background: var(--color-silver-100);
        border: 1px dashed var(--color-silver-300);
        border-radius: var(--radius-sm);
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        color: var(--color-text-muted);
        font-size: 11px;
        position: relative;
        cursor: move;
        overflow: hidden;
        transition: all var(--transition-fast);
    }
    .photo-slot:hover {
        border-color: var(--color-gold-500);
        background: rgba(201, 162, 39, 0.02);
    }
    .photo-slot.drag-over {
        border-color: var(--color-gold-500);
        background: rgba(201, 162, 39, 0.08);
        transform: scale(1.02);
    }
    .photo-slot.dragging {
        opacity: 0.65;
        border-color: var(--color-gold-600);
    }
    .photo-slot[draggable="true"] {
        cursor: grab;
    }
    .photo-slot[draggable="true"]:active {
        cursor: grabbing;
    }
    .upload-dropzone.is-dragover {
        border-color: var(--color-gold-500);
        background: rgba(201, 162, 39, 0.06);
    }
    .slot-number {
        font-size: 9px;
        font-weight: 700;
        text-transform: uppercase;
        color: var(--color-text-muted);
        z-index: 2;
        background: rgba(255, 255, 255, 0.85);
        padding: 1px 5px;
        border-radius: 10px;
        border: 1px solid var(--color-silver-200);
        position: absolute;
        top: 4px;
        left: 4px;
        pointer-events: none;
    }
    .slot-preview {
        width: 100%;
        height: 100%;
        position: absolute;
        top: 0;
        left: 0;
        z-index: 1;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .slot-preview img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .total-calc-box {
        background: var(--color-navy-950);
        color: var(--color-white);
        border-radius: var(--radius-md);
        padding: 20px;
        margin-top: 20px;
    }
    .total-calc-title {
        font-size: 13px;
        font-weight: 600;
        text-transform: uppercase;
        color: var(--color-silver-400);
        margin: 0 0 8px 0;
        letter-spacing: 0.5px;
    }
    .total-calc-amount {
        font-size: 28px;
        font-weight: 800;
        color: var(--color-gold-500);
        margin: 0;
    }
</style>

<div class="inventory-new-content">
    <div class="page-header-container mb-30">
        <div class="header-title-group" style="display: flex; align-items: center; gap: 14px;">
            <a href="<?= BASE_URL ?>/admin/inventory" class="btn btn-outline btn-sm">
                <i data-lucide="arrow-left" style="width: 14px; height: 14px; margin-right: 4px;"></i>
                <span>Back</span>
            </a>
            <div>
                <h1 class="page-title" style="font-size: 20px; margin-bottom: 0;">Add New Vehicle Listing</h1>
                <p style="color: var(--color-text-muted); margin: 2px 0 0 0; font-size: 12px;">Create detailed stock configurations, set custom options checkpoints, and assign export freight matrix.</p>
            </div>
        </div>
    </div>

    <form id="addVehicleDetailedForm" action="<?= BASE_URL ?>/admin/inventory/new" method="POST" enctype="multipart/form-data">
        <?= $this->csrf_field() ?>

        <div class="form-grid-2x">
            
            <!-- Left Column: Specs and Options Checkboxes -->
            <div>
                
                <!-- Card 1: Specifications -->
                <div class="card mb-24" style="padding: 24px;">
                    <h3 class="options-group-title" style="border: none; margin-bottom: 20px; padding: 0;">
                        <i data-lucide="car" style="color: var(--color-gold-600);"></i>
                        <span>Vehicle Specifications</span>
                    </h3>

                    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px;">
                        <?php include __DIR__ . '/partials/inventory-make-model.php'; ?>
                        <div class="form-group">
                            <label class="form-label" for="year">Model Year *</label>
                            <input class="form-control" type="number" id="year" name="year" min="1980" max="<?= (int) date('Y') + 1 ?>" placeholder="e.g. 2018" required>
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px;">
                        <div class="form-group">
                            <label class="form-label" for="chassis">Chassis VIN *</label>
                            <input class="form-control" type="text" id="chassis" name="chassis" placeholder="e.g. DBA-GK3" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="grade">Inspection Grade</label>
                            <input class="form-control" type="text" id="grade" name="grade" placeholder="e.g. 4.5">
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="car_grade">Car Grade</label>
                            <input class="form-control" type="text" id="car_grade" name="car_grade" placeholder="e.g. S, X, G">
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="mileage">Mileage (KM)</label>
                            <input class="form-control" type="number" id="mileage" name="mileage" min="0" placeholder="e.g. 76000">
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px;">
                        <div class="form-group">
                            <label class="form-label" for="engine">Engine Size (CC)</label>
                            <input class="form-control" type="number" id="engine" name="engine" min="0" placeholder="e.g. 1300">
                        </div>
                        <?php $renderSpecFields = ['transmission']; include __DIR__ . '/partials/inventory-spec-fields.php'; ?>
                        <div class="form-group">
                            <label class="form-label" for="drive">Drive Train</label>
                            <input class="form-control" type="text" id="drive" name="drive" placeholder="e.g. 2WD">
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px;">
                        <div class="form-group">
                            <label class="form-label" for="steering">Steering Direction</label>
                            <select class="form-control" id="steering" name="steering">
                                <option value="RHD">Right Hand Drive (RHD)</option>
                                <option value="LHD">Left Hand Drive (LHD)</option>
                            </select>
                        </div>
                        <?php $renderSpecFields = ['fuel', 'body_type']; include __DIR__ . '/partials/inventory-spec-fields.php'; ?>
                    </div>

                    <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px;">
                        <div class="form-group">
                            <label class="form-label" for="doors">Doors Count</label>
                            <input class="form-control" type="number" id="doors" name="doors" min="0" placeholder="e.g. 5">
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="seats">Seats Count</label>
                            <input class="form-control" type="number" id="seats" name="seats" min="0" placeholder="e.g. 5">
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="stock_type">Listing Sourcing Type *</label>
                            <select class="form-control" id="stock_type" name="stock_type" required>
                                <option value="In-Stock">In-Stock Imports</option>
                                <option value="Auction">Live Auction Lot</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="location">Storage Location</label>
                            <input class="form-control" type="text" id="location" name="location" placeholder="e.g. KOBE, JAPAN">
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; margin-top: 16px;">
                        <div class="form-group">
                            <label class="form-label" for="auction_house">Auction House Name</label>
                            <input class="form-control" type="text" id="auction_house" name="auction_house" placeholder="e.g. USS Tokyo" maxlength="100">
                            <small style="font-size: 11px; color: var(--color-text-muted);">Admin only — not shown on the website.</small>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="lot_number">Lot Number</label>
                            <input class="form-control" type="text" id="lot_number" name="lot_number" placeholder="e.g. A-1234" maxlength="50">
                            <small style="font-size: 11px; color: var(--color-text-muted);">Admin only — not shown on the website.</small>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="auction_end_date">Auction End Date</label>
                            <input class="form-control" type="date" id="auction_end_date" name="auction_end_date">
                            <small style="font-size: 11px; color: var(--color-text-muted);">Admin only — not shown on the website.</small>
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: repeat(5, 1fr); gap: 16px; margin-top: 16px;">
                        <div class="form-group">
                            <label class="form-label" for="color">Exterior Color</label>
                            <input class="form-control" type="text" id="color" name="color" placeholder="e.g. White">
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="dimension">Dimension</label>
                            <input class="form-control" type="text" id="dimension" name="dimension" placeholder="e.g. 4.40m × 1.69m × 1.52m">
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="m3">M3 Volume</label>
                            <input class="form-control" type="text" id="m3" name="m3" placeholder="e.g. 11.30">
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="views">Views Count</label>
                            <input class="form-control" type="number" id="views" name="views" value="0">
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="status">Listing Status *</label>
                            <select class="form-control" id="status" name="status" required>
                                <option value="Available" selected>Available</option>
                                <option value="Reserved">Reserved</option>
                                <option value="Sold">Sold</option>
                                <option value="Archived">Archived</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group" style="margin-top: 16px; margin-bottom: 0;">
                        <label class="form-label" for="description">Overview Description (Optional)</label>
                        <textarea class="form-control" id="description" name="description" rows="4" placeholder="Enter custom overview paragraph..." style="resize: vertical; min-height: 80px; padding: 10px;"></textarea>
                    </div>
                </div>

                <!-- Card 2: Dynamically categorized Option Checklist checkboxes -->
                <div class="card mb-24" style="padding: 24px;">
                    <h3 class="options-group-title" style="border: none; margin-bottom: 4px; padding: 0;">
                        <i data-lucide="check-square" style="color: var(--color-gold-600);"></i>
                        <span>Car Equipment Options Checklist</span>
                    </h3>
                    <p style="font-size: 12px; color: var(--color-text-muted); margin: 0 0 20px 0;">Select all options that are installed in the vehicle. Active options will display with checkmarks on the product page.</p>

                    <?php foreach ($optionGroups as $group): ?>
                        <div class="options-group-card" style="padding: 16px; border: 1px solid var(--color-silver-200); margin-bottom: 20px;">
                            <h4 class="options-group-title" style="font-size: 13.5px; border: none; margin-bottom: 12px; padding: 0;">
                                <i data-lucide="folder" style="width: 14px; height: 14px;"></i>
                                <span><?= htmlspecialchars($group['title']) ?></span>
                            </h4>
                            <div class="checkbox-chips-grid">
                                <?php foreach ($group['items'] as $item): ?>
                                    <label class="checkbox-chip-label">
                                        <input type="checkbox" name="options[]" class="car-option-check" value="<?= htmlspecialchars($item['label']) ?>">
                                        <span class="chip-label-text" style="font-size: 12.5px;"><?= htmlspecialchars($item['label']) ?></span>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

            </div>

            <!-- Right Column: Pricing, Uploads, and Totals -->
            <div>

                <?php include __DIR__ . '/partials/inventory-pricing-jpy.php'; ?>

                <!-- Card 4: Uploads Dropzone -->
                <div class="card mb-24" style="padding: 20px;">
                    <h3 class="options-group-title" style="border: none; margin-bottom: 15px; padding: 0;">
                        <i data-lucide="image" style="color: var(--color-gold-600);"></i>
                        <span>Gallery & Documents</span>
                    </h3>

                    <div class="form-group">
                        <label class="form-label">Vehicle Photos (Drag and Drop)</label>
                        <div class="upload-dropzone" style="padding: 20px 10px; text-align: center; border: 2px dashed var(--color-silver-300);" onclick="document.getElementById('gallery_uploader').click();">
                            <i data-lucide="upload-cloud" style="width: 28px; height: 28px; color: var(--color-silver-400); margin-bottom: 6px;"></i>
                            <p style="margin: 0; font-size: 11.5px; font-weight: 600; color: var(--color-navy-950);">Click to browse gallery photos</p>
                            <input type="file" name="images[]" multiple style="display: none;" id="gallery_uploader" accept="image/jpeg,image/png,image/webp">
                        </div>
                        <div class="photo-slots-grid" id="sortable-photo-slots">
                            <?php for ($i = 1; $i <= 8; $i++): ?>
                                <div class="photo-slot" draggable="false" data-index="<?= $i - 1 ?>">
                                    <span class="slot-number">Slot <?= $i ?></span>
                                    <div class="slot-preview"></div>
                                </div>
                            <?php endfor; ?>
                        </div>
                    </div>

                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label" for="inspection_pdf">Inspection Sheet PDF</label>
                        <input class="form-control" type="file" id="inspection_pdf" name="inspection_pdf" accept="application/pdf,.pdf">
                    </div>
                </div>

                <!-- Submit / Cancel Controls -->
                <div class="card" style="padding: 20px;">
                    <button class="btn btn-gold btn-block mb-10" type="submit" id="saveListingBtn">
                        <i data-lucide="shield-check" style="width: 16px; height: 16px; margin-right: 4px;"></i>
                        <span>Save Listing</span>
                    </button>
                    <button class="btn btn-outline btn-block" type="button" onclick="window.location.href='<?= BASE_URL ?>/admin/inventory'">
                        <span>Cancel</span>
                    </button>
                </div>

            </div>

        </div>
    </form>
</div>

<script>
window.EisenMakeModels = <?= json_encode($makeToModels ?? [], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
window.EisenSelectedModel = <?= json_encode($selectedModel ?? '', JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
</script>

<?php include dirname(__DIR__) . '/admin/partials/footer.php'; ?>
