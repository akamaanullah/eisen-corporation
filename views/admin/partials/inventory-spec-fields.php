<?php

use App\Helpers\VehicleSpecOptions;

$selectedTransmission = $selectedTransmission ?? 'AT';
$selectedFuel = $selectedFuel ?? 'PETROL';
$selectedBodyType = $selectedBodyType ?? 'Hatchback';
$renderSpecFields = $renderSpecFields ?? ['transmission', 'fuel', 'body_type'];

$transmissionOptions = VehicleSpecOptions::transmissionOptions();
$fuelOptions = VehicleSpecOptions::fuelOptions();
$bodyTypeOptions = VehicleSpecOptions::bodyTypeOptions();
?>
<?php if (in_array('transmission', $renderSpecFields, true)): ?>
<div class="form-group">
    <label class="form-label" for="transmission">Transmission</label>
    <select class="form-control" id="transmission" name="transmission">
        <?php foreach ($transmissionOptions as $value => $label): ?>
            <option value="<?= htmlspecialchars($value) ?>"<?= strtoupper($selectedTransmission) === $value ? ' selected' : '' ?>>
                <?= htmlspecialchars($label) ?>
            </option>
        <?php endforeach; ?>
        <?php
        $legacyTransmission = strtoupper(trim((string) $selectedTransmission));
        if ($legacyTransmission !== '' && !isset($transmissionOptions[$legacyTransmission])): ?>
            <option value="<?= htmlspecialchars($selectedTransmission) ?>" selected>
                <?= htmlspecialchars($selectedTransmission) ?> (legacy)
            </option>
        <?php endif; ?>
    </select>
</div>
<?php endif; ?>
<?php if (in_array('fuel', $renderSpecFields, true)): ?>
<div class="form-group">
    <label class="form-label" for="fuel">Fuel Type</label>
    <select class="form-control" id="fuel" name="fuel">
        <?php foreach ($fuelOptions as $value => $label): ?>
            <option value="<?= htmlspecialchars($value) ?>"<?= strtoupper($selectedFuel) === $value ? ' selected' : '' ?>>
                <?= htmlspecialchars($label) ?>
            </option>
        <?php endforeach; ?>
        <?php
        $legacyFuel = strtoupper(trim((string) $selectedFuel));
        if ($legacyFuel !== '' && !isset($fuelOptions[$legacyFuel])): ?>
            <option value="<?= htmlspecialchars($selectedFuel) ?>" selected>
                <?= htmlspecialchars($selectedFuel) ?> (legacy)
            </option>
        <?php endif; ?>
    </select>
</div>
<?php endif; ?>
<?php if (in_array('body_type', $renderSpecFields, true)): ?>
<div class="form-group">
    <label class="form-label" for="body_type">Body Type</label>
    <select class="form-control" id="body_type" name="body_type">
        <?php foreach ($bodyTypeOptions as $bodyType): ?>
            <option value="<?= htmlspecialchars($bodyType) ?>"<?= $selectedBodyType === $bodyType ? ' selected' : '' ?>>
                <?= htmlspecialchars($bodyType) ?>
            </option>
        <?php endforeach; ?>
        <?php if ($selectedBodyType !== '' && !in_array($selectedBodyType, $bodyTypeOptions, true)): ?>
            <option value="<?= htmlspecialchars($selectedBodyType) ?>" selected>
                <?= htmlspecialchars($selectedBodyType) ?> (legacy)
            </option>
        <?php endif; ?>
    </select>
</div>
<?php endif; ?>
