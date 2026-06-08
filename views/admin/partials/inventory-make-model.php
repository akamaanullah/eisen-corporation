<?php
$selectedMake = $selectedMake ?? '';
$selectedModel = $selectedModel ?? '';
$makes = $makes ?? [];
?>
<div class="form-group">
    <label class="form-label" for="make">Make / Manufacturer *</label>
    <select class="form-control" id="make" name="make" required>
        <option value="">Select make</option>
        <?php foreach ($makes as $makeName): ?>
            <option value="<?= htmlspecialchars($makeName) ?>"<?= $selectedMake === $makeName ? ' selected' : '' ?>>
                <?= htmlspecialchars($makeName) ?>
            </option>
        <?php endforeach; ?>
        <?php if ($selectedMake !== '' && !in_array($selectedMake, $makes, true)): ?>
            <option value="<?= htmlspecialchars($selectedMake) ?>" selected>
                <?= htmlspecialchars($selectedMake) ?> (legacy)
            </option>
        <?php endif; ?>
    </select>
</div>
<div class="form-group">
    <label class="form-label" for="model">Model *</label>
    <select class="form-control" id="model" name="model" required>
        <option value="">Select model</option>
        <?php if ($selectedModel !== ''): ?>
            <option value="<?= htmlspecialchars($selectedModel) ?>" selected>
                <?= htmlspecialchars($selectedModel) ?>
            </option>
        <?php endif; ?>
    </select>
    <p class="form-hint" style="font-size: 11px; color: var(--color-text-muted); margin: 6px 0 0;">
        Models are loaded from the master catalog after you choose a make.
    </p>
</div>
