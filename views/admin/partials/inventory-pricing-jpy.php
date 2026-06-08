<?php

use App\Helpers\ExchangeRate;

$displayRate = ExchangeRate::getUsdJpyRate();
$fobUsd = isset($car) ? (float) $car['fob_price'] : 0;
$freightUsd = isset($car) ? (float) $car['freight_price'] : 0;
$vanningUsd = isset($car) ? (float) $car['vanning_price'] : 0;
$inspectionUsd = isset($car) ? (float) $car['inspection_price'] : 0;
$insuranceUsd = isset($car) ? (float) $car['insurance_price'] : 0;

$storedJpy = isset($car) ? (float) ($car['price_jpy'] ?? 0) : 0;
$fobJpy = $storedJpy > 0 ? $storedJpy : ($fobUsd > 0 ? round($fobUsd * $displayRate) : '');
$freightJpy = $freightUsd > 0 ? round($freightUsd * $displayRate) : '';
$vanningJpy = $vanningUsd > 0 ? round($vanningUsd * $displayRate) : '';
$inspectionJpy = $inspectionUsd > 0 ? round($inspectionUsd * $displayRate) : '';
$insuranceJpy = $insuranceUsd > 0 ? round($insuranceUsd * $displayRate) : '';
$cfTotalJpy = ($fobJpy !== '' ? (float) $fobJpy : 0) + (float) $freightJpy + (float) $vanningJpy + (float) $inspectionJpy + (float) $insuranceJpy;
?>
<div class="card mb-24" style="padding: 20px;" data-live-rate="<?= htmlspecialchars(number_format($displayRate, 4, '.', '')) ?>">
    <h3 class="options-group-title" style="border: none; margin-bottom: 15px; padding: 0; display: flex; align-items: center; gap: 8px;">
        <i data-lucide="yen" style="color: var(--color-gold-600);"></i>
        <span>Pricing Breakdown (JPY)</span>
    </h3>
    <p style="font-size: 12px; color: var(--color-text-muted); margin: 0 0 16px 0;">
        Enter all amounts in Japanese Yen. USD is calculated with the live USD/JPY rate (refreshed hourly, same source as the storefront) and saved when you submit.
    </p>

    <input type="hidden" id="exchange_rate" name="exchange_rate" value="<?= htmlspecialchars(number_format($displayRate, 4, '.', '')) ?>">

    <div class="pricing-fields-grid">
        <div class="form-group" style="margin-bottom: 0;">
            <label class="form-label" for="price_vehicle_jpy">Base Vehicle Price (FOB) JPY *</label>
            <input class="form-control jpy-price-input" type="number" id="price_vehicle_jpy" name="price_vehicle_jpy" min="0" step="1" placeholder="e.g. 750000" value="<?= $fobJpy !== '' ? htmlspecialchars((string) (int) $fobJpy) : '' ?>" required>
        </div>
        <div class="form-group" style="margin-bottom: 0;">
            <label class="form-label" for="live_usd_fob">Live USD equivalent (FOB)</label>
            <output class="form-control" id="live_usd_fob" style="display: block; background: var(--color-silver-100); font-weight: 600;">$0</output>
        </div>
        <div class="form-group" style="margin-bottom: 0;">
            <label class="form-label" for="price_freight_jpy">Estimated Freight Charges (JPY)</label>
            <input class="form-control jpy-price-input" type="number" id="price_freight_jpy" name="price_freight_jpy" min="0" step="1" placeholder="e.g. 180000" value="<?= $freightJpy !== '' ? htmlspecialchars((string) (int) $freightJpy) : '' ?>">
        </div>
        <div class="form-group" style="margin-bottom: 0;">
            <label class="form-label" for="price_vanning_jpy">Vanning Packaging Cost (JPY)</label>
            <input class="form-control jpy-price-input" type="number" id="price_vanning_jpy" name="price_vanning_jpy" min="0" step="1" placeholder="e.g. 0" value="<?= $vanningJpy !== '' ? htmlspecialchars((string) (int) $vanningJpy) : '' ?>">
        </div>
        <div class="form-group" style="margin-bottom: 0;">
            <label class="form-label" for="price_inspection_jpy">Inspection Certification Cost (JPY)</label>
            <input class="form-control jpy-price-input" type="number" id="price_inspection_jpy" name="price_inspection_jpy" min="0" step="1" placeholder="e.g. 67500" value="<?= $inspectionJpy !== '' ? htmlspecialchars((string) (int) $inspectionJpy) : '' ?>">
        </div>
        <div class="form-group" style="margin-bottom: 0;">
            <label class="form-label" for="price_insurance_jpy">Marine Insurance Premium (JPY)</label>
            <input class="form-control jpy-price-input" type="number" id="price_insurance_jpy" name="price_insurance_jpy" min="0" step="1" placeholder="e.g. 7500" value="<?= $insuranceJpy !== '' ? htmlspecialchars((string) (int) $insuranceJpy) : '' ?>">
        </div>
    </div>

    <div class="total-calc-box">
        <h4 class="total-calc-title">Total Calculated C&amp;F Price (JPY)</h4>
        <p class="total-calc-amount" id="total_price_jpy_display">¥<?= $cfTotalJpy > 0 ? number_format($cfTotalJpy) : '0' ?></p>
        <p style="font-size: 12px; color: var(--color-silver-400); margin: 10px 0 0;">
            Live USD reference: <strong id="live_usd_total" style="color: var(--color-white);">$0</strong>
            <span id="live_rate_label" style="opacity: 0.75;"> @ <?= number_format($displayRate, 2) ?> JPY/USD</span>
        </p>
    </div>
</div>
