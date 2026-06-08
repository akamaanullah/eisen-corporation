<?php
$fob = isset($car) ? (float) $car['fob_price'] : '';
$freight = isset($car) ? (float) $car['freight_price'] : '';
$vanning = isset($car) ? (float) $car['vanning_price'] : '';
$inspection = isset($car) ? (float) $car['inspection_price'] : '';
$insurance = isset($car) ? (float) $car['insurance_price'] : '';
$cfTotal = isset($car) ? (float) $car['cf_price'] : 0;
?>
<div class="card mb-24" style="padding: 20px;">
    <h3 class="options-group-title" style="border: none; margin-bottom: 15px; padding: 0; display: flex; align-items: center; gap: 8px;">
        <i data-lucide="dollar-sign" style="color: var(--color-gold-600);"></i>
        <span>Pricing Breakdown (USD only)</span>
    </h3>
    <p style="font-size: 12px; color: var(--color-text-muted); margin: 0 0 16px 0;">
        Enter all amounts in USD. JPY is calculated live on the storefront using the current exchange rate (same as customer-facing pages).
    </p>

    <div class="pricing-fields-grid">
        <div class="form-group" style="margin-bottom: 0;">
            <label class="form-label" for="price_vehicle">Base Vehicle Price (FOB) USD *</label>
            <input class="form-control" type="number" id="price_vehicle" name="price_vehicle" min="0" step="0.01" placeholder="e.g. 5000" value="<?= $fob !== '' ? htmlspecialchars((string) $fob) : '' ?>" required>
        </div>
        <div class="form-group" style="margin-bottom: 0;">
            <label class="form-label" for="live_jpy_fob">Live JPY equivalent (FOB)</label>
            <output class="form-control" id="live_jpy_fob" style="display: block; background: var(--color-silver-100); font-weight: 600;">¥0</output>
        </div>
        <div class="form-group" style="margin-bottom: 0;">
            <label class="form-label" for="price_freight">Estimated Freight Charges (USD)</label>
            <input class="form-control" type="number" id="price_freight" name="price_freight" min="0" step="0.01" placeholder="e.g. 1200" value="<?= $freight !== '' ? htmlspecialchars((string) $freight) : '' ?>">
        </div>
        <div class="form-group" style="margin-bottom: 0;">
            <label class="form-label" for="price_vanning">Vanning Packaging Cost (USD)</label>
            <input class="form-control" type="number" id="price_vanning" name="price_vanning" min="0" step="0.01" placeholder="e.g. 0" value="<?= $vanning !== '' ? htmlspecialchars((string) $vanning) : '' ?>">
        </div>
        <div class="form-group" style="margin-bottom: 0;">
            <label class="form-label" for="price_inspection">Inspection Certification Cost (USD)</label>
            <input class="form-control" type="number" id="price_inspection" name="price_inspection" min="0" step="0.01" placeholder="e.g. 450" value="<?= $inspection !== '' ? htmlspecialchars((string) $inspection) : '' ?>">
        </div>
        <div class="form-group" style="margin-bottom: 0;">
            <label class="form-label" for="price_insurance">Marine Insurance Premium (USD)</label>
            <input class="form-control" type="number" id="price_insurance" name="price_insurance" min="0" step="0.01" placeholder="e.g. 50" value="<?= $insurance !== '' ? htmlspecialchars((string) $insurance) : '' ?>">
        </div>
    </div>

    <div class="total-calc-box">
        <h4 class="total-calc-title">Total Calculated C&amp;F Price (USD)</h4>
        <p class="total-calc-amount" id="total_price_display">$<?= $cfTotal > 0 ? number_format($cfTotal, 0) : '0' ?></p>
        <p style="font-size: 12px; color: var(--color-silver-400); margin: 10px 0 0;">
            Live JPY reference: <strong id="live_jpy_total" style="color: var(--color-white);">¥0</strong>
            <span id="live_rate_label" style="opacity: 0.75;"></span>
        </p>
    </div>
</div>
