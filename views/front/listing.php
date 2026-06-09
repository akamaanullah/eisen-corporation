<?php
include __DIR__ . '/partials/header.php';

$inventoryMakes = [['value' => 'others', 'label' => 'Others', 'i18n' => 'inventory.make.others']];
if (!empty($makes)) {
    foreach ($makes as $m) {
        $inventoryMakes[] = [
            'value' => strtolower($m),
            'label' => $m,
            'i18n' => 'spec.val.' . strtolower($m)
        ];
    }
}

$inventoryModels = [['value' => 'others', 'label' => 'Others', 'i18n' => 'inventory.model.others']];
if (!empty($models)) {
    foreach ($models as $m) {
        $inventoryModels[] = [
            'value' => strtolower(str_replace(' ', '-', $m)),
            'label' => $m,
            'i18n' => 'spec.val.' . strtolower(str_replace(' ', '-', $m))
        ];
    }
}

$inventoryYearMax = (int) date('Y');
$inventoryYearMin = $inventoryYearMax - 30;
$inventoryYears = range($inventoryYearMax, $inventoryYearMin);

$inventoryFuelTypes = [['value' => 'others', 'label' => 'Others', 'i18n' => 'inventory.fuel.others']];
if (!empty($fuels)) {
    foreach ($fuels as $f) {
        $val = strtolower(str_replace(' ', '-', $f));
        $inventoryFuelTypes[] = [
            'value' => $val,
            'label' => ucwords(strtolower($f)),
            'i18n' => 'inventory.fuel.' . ($val === 'lpg-petrol' ? 'lpgPetrol' : $val)
        ];
    }
}

$inventoryColors = [['value' => 'others', 'label' => 'Others', 'i18n' => 'inventory.color.others']];
if (!empty($colors)) {
    foreach ($colors as $c) {
        $val = strtolower(str_replace(' ', '-', $c));
        $inventoryColors[] = [
            'value' => $val,
            'label' => ucwords(strtolower($c)),
            'i18n' => 'inventory.color.' . $val
        ];
    }
}

$inventoryEngineCc = [660, 800, 1000, 1200, 1300, 1500, 1600, 1800, 2000, 2200, 2500, 3000, 3500, 4000, 4500, 5000, 6000];
?>

  <main id="main">

    <section class="inventory-page section" aria-labelledby="inventory-page-title">
      <div class="container">
        <h1 id="inventory-page-title" class="visually-hidden" data-i18n="inventory.pageTitle" data-page-title="inventory.pageTitle">Vehicle listings</h1>

        <div class="inventory-layout">
          <button
            class="inventory-filters-toggle btn btn--primary"
            type="button"
            data-filter-toggle
            aria-expanded="false"
            aria-controls="inventory-filters-panel"
          >
            <span data-i18n="inventory.filters">Filters</span><span class="inventory-filters-toggle__count" data-filter-toggle-count hidden></span>
          </button>

          <div class="inventory-filters-backdrop" data-filter-backdrop hidden></div>

          <aside class="inventory-filters card" id="inventory-filters-panel" data-i18n-aria-label="inventory.filtersAria" aria-label="Filters" data-inventory-filters>
            <div class="inventory-filters__head">
              <h2 class="inventory-filters__title"><span data-i18n="inventory.filters">Filters</span> <span class="inventory-filters__count" data-filter-count hidden></span></h2>
              <div class="inventory-filters__head-actions">
                <button class="inventory-filters__clear" type="button" data-filter-clear hidden data-i18n="inventory.clearFilter">clear filter</button>
                <button class="inventory-filters__close" type="button" data-filter-close data-i18n-aria-label="inventory.closeFilters" aria-label="Close filters">×</button>
              </div>
            </div>

            <div class="inventory-filters__tags" data-filter-tags hidden></div>

            <div class="inventory-filters__groups">
              <details class="inventory-filter-group">
                <summary class="inventory-filter-group__summary" data-i18n="inventory.group.make">Make</summary>
                <div class="inventory-filter-group__body">
                  <?php foreach ($inventoryMakes as $make): ?>
                  <label class="inventory-check">
                    <input type="checkbox" name="make" value="<?= htmlspecialchars($make['value']) ?>" />
                    <?php if (!empty($make['i18n'])): ?>
                    <span data-i18n="<?= htmlspecialchars($make['i18n']) ?>"><?= htmlspecialchars($make['label']) ?></span>
                    <?php else: ?>
                    <span><?= htmlspecialchars($make['label']) ?></span>
                    <?php endif; ?>
                  </label>
                  <?php endforeach; ?>
                </div>
              </details>
              <details class="inventory-filter-group inventory-filter-group--scroll">
                <summary class="inventory-filter-group__summary" data-i18n="inventory.group.model">Model</summary>
                <div class="inventory-filter-group__body">
                  <?php foreach ($inventoryModels as $model): ?>
                  <label class="inventory-check">
                    <input type="checkbox" name="model" value="<?= htmlspecialchars($model['value']) ?>" />
                    <?php if (!empty($model['i18n'])): ?>
                    <span data-i18n="<?= htmlspecialchars($model['i18n']) ?>"><?= htmlspecialchars($model['label']) ?></span>
                    <?php else: ?>
                    <span><?= htmlspecialchars($model['label']) ?></span>
                    <?php endif; ?>
                  </label>
                  <?php endforeach; ?>
                </div>
              </details>
              <details class="inventory-filter-group">
                <summary class="inventory-filter-group__summary" data-i18n="inventory.group.price">Price Range</summary>
                <div class="inventory-filter-group__body">
                  <div class="inventory-price-range" data-price-range data-price-min="5000" data-price-max="80000" data-price-step="1000">
                    <p class="inventory-price-range__values">
                      <span data-price-min-display>$5,000</span>
                      <span class="inventory-price-range__sep" aria-hidden="true">–</span>
                      <span data-price-max-display>$80,000</span>
                    </p>
                    <div class="inventory-price-range__slider">
                      <div class="inventory-price-range__track" aria-hidden="true">
                        <div class="inventory-price-range__fill" data-price-fill></div>
                      </div>
                      <input type="range" class="inventory-price-range__input inventory-price-range__input--min" name="price_min" min="5000" max="80000" step="1000" value="5000" data-price-min-input aria-label="Minimum price" data-i18n-aria-label="inventory.priceMinAria" />
                      <input type="range" class="inventory-price-range__input inventory-price-range__input--max" name="price_max" min="5000" max="80000" step="1000" value="80000" data-price-max-input aria-label="Maximum price" data-i18n-aria-label="inventory.priceMaxAria" />
                    </div>
                  </div>
                </div>
              </details>
              <details class="inventory-filter-group">
                <summary class="inventory-filter-group__summary" data-i18n="inventory.condition">Condition</summary>
                <div class="inventory-filter-group__body" data-condition-filters>
                  <label class="inventory-check">
                    <input type="checkbox" name="condition" value="all" data-condition-input checked />
                    <span data-i18n="inventory.conditionOptionAll">All</span>
                  </label>
                  <label class="inventory-check">
                    <input type="checkbox" name="condition" value="new" data-condition-input />
                    <span data-i18n="inventory.conditionOptionNew">New</span>
                  </label>
                  <label class="inventory-check">
                    <input type="checkbox" name="condition" value="used" data-condition-input />
                    <span data-i18n="inventory.conditionOptionUsed">Used</span>
                  </label>
                </div>
              </details>
              <details class="inventory-filter-group">
                <summary class="inventory-filter-group__summary" data-i18n="inventory.group.year">Year</summary>
                <div class="inventory-filter-group__body">
                  <div class="inventory-year-range" data-year-range>
                    <div class="inventory-year-range__field">
                      <label class="inventory-year-range__label" for="inventory-year-min" data-i18n="inventory.yearMin">Min</label>
                      <select class="inventory-year-range__select form-control" id="inventory-year-min" name="year_min" data-year-min-select>
                        <option value="" data-i18n="inventory.yearSelect">Select</option>
                        <?php foreach ($inventoryYears as $year): ?>
                        <option value="<?= (int) $year ?>"><?= (int) $year ?></option>
                        <?php endforeach; ?>
                      </select>
                    </div>
                    <div class="inventory-year-range__field">
                      <label class="inventory-year-range__label" for="inventory-year-max" data-i18n="inventory.yearMax">Max</label>
                      <select class="inventory-year-range__select form-control" id="inventory-year-max" name="year_max" data-year-max-select>
                        <option value="" data-i18n="inventory.yearSelect">Select</option>
                        <?php foreach ($inventoryYears as $year): ?>
                        <option value="<?= (int) $year ?>"><?= (int) $year ?></option>
                        <?php endforeach; ?>
                      </select>
                    </div>
                  </div>
                </div>
              </details>
              <details class="inventory-filter-group">
                <summary class="inventory-filter-group__summary" data-i18n="inventory.group.fuel">Fuel Type</summary>
                <div class="inventory-filter-group__body">
                  <?php foreach ($inventoryFuelTypes as $fuel): ?>
                  <label class="inventory-check">
                    <input type="checkbox" name="fuel" value="<?= htmlspecialchars($fuel['value']) ?>" />
                    <?php if (!empty($fuel['i18n'])): ?>
                    <span data-i18n="<?= htmlspecialchars($fuel['i18n']) ?>"><?= htmlspecialchars($fuel['label']) ?></span>
                    <?php else: ?>
                    <span><?= htmlspecialchars($fuel['label']) ?></span>
                    <?php endif; ?>
                  </label>
                  <?php endforeach; ?>
                </div>
              </details>
              <details class="inventory-filter-group">
                <summary class="inventory-filter-group__summary" data-i18n="inventory.group.transmission">Transmission</summary>
                <div class="inventory-filter-group__body">
                  <label class="inventory-check"><input type="checkbox" name="transmission" value="auto" /> <span data-i18n="inventory.trans.auto">Automatic</span></label>
                  <label class="inventory-check"><input type="checkbox" name="transmission" value="manual" /> <span data-i18n="inventory.trans.manual">Manual</span></label>
                </div>
              </details>
              <details class="inventory-filter-group inventory-filter-group--scroll">
                <summary class="inventory-filter-group__summary" data-i18n="inventory.group.color">Color</summary>
                <div class="inventory-filter-group__body">
                  <?php foreach ($inventoryColors as $color): ?>
                  <label class="inventory-check">
                    <input type="checkbox" name="color" value="<?= htmlspecialchars($color['value']) ?>" />
                    <?php if (!empty($color['i18n'])): ?>
                    <span data-i18n="<?= htmlspecialchars($color['i18n']) ?>"><?= htmlspecialchars($color['label']) ?></span>
                    <?php else: ?>
                    <span><?= htmlspecialchars($color['label']) ?></span>
                    <?php endif; ?>
                  </label>
                  <?php endforeach; ?>
                </div>
              </details>
              <details class="inventory-filter-group">
                <summary class="inventory-filter-group__summary" data-i18n="inventory.group.engineCc">Engine CC</summary>
                <div class="inventory-filter-group__body">
                  <div class="inventory-year-range" data-engine-cc-range>
                    <div class="inventory-year-range__field">
                      <label class="inventory-year-range__label" for="inventory-engine-cc-min" data-i18n="inventory.yearMin">Min</label>
                      <select class="inventory-year-range__select form-control" id="inventory-engine-cc-min" name="engine_cc_min" data-engine-cc-min-select>
                        <option value="" data-i18n="inventory.yearSelect">Select</option>
                        <?php foreach ($inventoryEngineCc as $cc): ?>
                        <option value="<?= (int) $cc ?>"><?= number_format((int) $cc) ?> cc</option>
                        <?php endforeach; ?>
                      </select>
                    </div>
                    <div class="inventory-year-range__field">
                      <label class="inventory-year-range__label" for="inventory-engine-cc-max" data-i18n="inventory.yearMax">Max</label>
                      <select class="inventory-year-range__select form-control" id="inventory-engine-cc-max" name="engine_cc_max" data-engine-cc-max-select>
                        <option value="" data-i18n="inventory.yearSelect">Select</option>
                        <?php foreach ($inventoryEngineCc as $cc): ?>
                        <option value="<?= (int) $cc ?>"><?= number_format((int) $cc) ?> cc</option>
                        <?php endforeach; ?>
                      </select>
                    </div>
                  </div>
                </div>
              </details>
              <details class="inventory-filter-group">
                <summary class="inventory-filter-group__summary" data-i18n="inventory.group.mileage">Mileage (KM)</summary>
                <div class="inventory-filter-group__body">
                  <div class="inventory-price-range" data-mileage-range data-mileage-min="0" data-mileage-max="300" data-mileage-step="5">
                    <p class="inventory-price-range__values">
                      <span data-mileage-min-display>0K km</span>
                      <span class="inventory-price-range__sep" aria-hidden="true">–</span>
                      <span data-mileage-max-display>300K km</span>
                    </p>
                    <div class="inventory-price-range__slider">
                      <div class="inventory-price-range__track" aria-hidden="true">
                        <div class="inventory-price-range__fill" data-mileage-fill></div>
                      </div>
                      <input type="range" class="inventory-price-range__input inventory-price-range__input--min" name="mileage_min" min="0" max="300" step="5" value="0" data-mileage-min-input aria-label="Minimum mileage in kilometers" data-i18n-aria-label="inventory.mileageMinAria" />
                      <input type="range" class="inventory-price-range__input inventory-price-range__input--max" name="mileage_max" min="0" max="300" step="5" value="300" data-mileage-max-input aria-label="Maximum mileage in kilometers" data-i18n-aria-label="inventory.mileageMaxAria" />
                    </div>
                  </div>
                </div>
              </details>
            </div>
          </aside>

          <div class="inventory-results">
            <header class="inventory-results__head">
              <h2 class="inventory-results__title"><span class="inventory-results__total"><?= number_format((int) ($totalListings ?? 0)) ?></span> <span data-i18n="inventory.results.listings">Listings</span></h2>
              <div class="inventory-results__actions">
                <a class="inventory-results__compare" href="#" data-i18n="inventory.compare">Compare</a>
                <span class="inventory-results__sort">
                  <span data-i18n="inventory.sortBy">Sort By:</span>
                  <button class="inventory-results__sort-btn" type="button" aria-haspopup="listbox" data-i18n="inventory.sortBest">Best match</button>
                </span>
              </div>
            </header>

            <ul class="inventory-grid" data-inventory-grid></ul>

            <nav class="inventory-pagination" data-i18n-aria-label="inventory.paginationAria" aria-label="Listings pagination" data-inventory-pagination hidden>
              <button class="inventory-pagination__btn inventory-pagination__btn--arrow" type="button" data-page-prev data-i18n-aria-label="inventory.pagePrev" aria-label="Previous page" disabled>
                <span aria-hidden="true">‹</span>
              </button>
              <div class="inventory-pagination__pages" data-pagination-pages></div>
              <button class="inventory-pagination__btn inventory-pagination__btn--arrow" type="button" data-page-next data-i18n-aria-label="inventory.pageNext" aria-label="Next page">
                <span aria-hidden="true">›</span>
              </button>
            </nav>
          </div>
        </div>
      </div>
    </section>

  </main>

 <?php include __DIR__ . '/partials/footer.php'; ?>
