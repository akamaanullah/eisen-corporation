<?php
$v = $vehicle;
$loadProductScript = true;
$galleryTotal = count($gallery);
$galleryFirst = $gallery[0];
$firstSrc = $galleryFirst['src'];
if (strpos($firstSrc, 'http') === 0) {
    $galleryFirstLarge = $firstSrc;
    $galleryFirstThumb = $firstSrc;
} elseif (strpos($firstSrc, '/') === 0) {
    $galleryFirstLarge = BASE_URL . $firstSrc;
    $galleryFirstThumb = BASE_URL . $firstSrc;
} else {
    $galleryFirstLarge = "https://images.unsplash.com/{$firstSrc}?w=1200&q=80";
    $galleryFirstThumb = "https://images.unsplash.com/{$firstSrc}?w=200&q=80";
}
$galleryFirstAlt = "{$v['title']} — {$galleryFirst['label']}";
include __DIR__ . '/partials/header.php';
?>
<script>
  window.EisenVehicleData = {
    year: <?= json_encode($v['year']) ?>,
    make: <?= json_encode($vehicleDetails[0]['value'] ?? '') ?>,
    model: <?= json_encode($v['modelDisplay'] ?? $v['model']) ?>,
    title: <?= json_encode($v['title']) ?>,
    bodyType: <?= json_encode($v['bodyType']) ?>,
    location: <?= json_encode($v['locationDisplay'] ?? $v['location']) ?>,
    mileageKm: <?= (int)$v['mileageKm'] ?>,
    engineCc: <?= (int)$v['engineCc'] ?>,
    fuel: <?= json_encode($v['fuel']) ?>,
    transmission: <?= json_encode($v['transmission']) ?>,
    color: <?= json_encode($vehicleDetails[2]['value'] ?? '') ?>,
    doors: <?= (int)$v['doors'] ?>,
    seats: <?= (int)$v['seats'] ?>,
    steering: <?= json_encode($v['steering']) ?>,
    stockId: <?= json_encode($v['stockId']) ?>,
  };

  window.EisenPricingData = {
    vehicle:    <?= (float)($pricingBreakdown[0]['usd'] ?? 0) ?>,
    freight:    <?= (float)($pricingBreakdown[1]['usd'] ?? 0) ?>,
    vanning:    <?= (float)($pricingBreakdown[2]['usd'] ?? 0) ?>,
    inspection: <?= (float)($pricingBreakdown[3]['usd'] ?? 0) ?>,
    insurance:  <?= (float)($pricingBreakdown[4]['usd'] ?? 0) ?>,
    coupon:     <?= (float)($pricingBreakdown[5]['usd'] ?? 0) ?>,
  };
  window.EisenCountryToPorts = <?= json_encode($estimate['countryToPorts'] ?? []) ?>;
</script>

  <main id="main" class="product-page">

    <section class="section" aria-labelledby="product-title">
      <div class="container">

        <nav class="product-breadcrumb" aria-label="Breadcrumb">
          <ol class="product-breadcrumb__list">
            <li><a href="<?= BASE_URL ?>/" data-i18n="nav.home">Home</a></li>
            <li><a href="<?= BASE_URL ?>/listing" data-i18n="nav.sellers">Available Stock</a></li>
            <li aria-current="page"><?= htmlspecialchars($v['title']) ?></li>
          </ol>
        </nav>

        <div class="product-hero">
          <div class="product-gallery-wrap" data-product-gallery>
            <figure class="product-gallery card">
              <button type="button" class="product-gallery__main" data-gallery-open aria-label="Open full-size photo">
                <img
                  class="product-gallery__img"
                  data-gallery-main
                  src="<?= htmlspecialchars($galleryFirstLarge) ?>"
                  alt="<?= htmlspecialchars($galleryFirstAlt) ?>"
                  width="1200"
                  height="675"
                  fetchpriority="high"
                />
                <span class="product-gallery__badge" data-gallery-count>1 / <?= (int) $galleryTotal ?></span>
                <span class="product-gallery__zoom" aria-hidden="true">
                  <svg width="22" height="22" viewBox="0 0 24 24" fill="none">
                    <circle cx="11" cy="11" r="7" stroke="currentColor" stroke-width="2" />
                    <line x1="16.5" y1="16.5" x2="21" y2="21" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                    <line x1="11" y1="8" x2="11" y2="14" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                    <line x1="8" y1="11" x2="14" y2="11" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                  </svg>
                </span>
              </button>
            </figure>

            <div class="product-gallery__thumbs-wrap">
              <button type="button" class="product-gallery__scroll product-gallery__scroll--prev" data-thumb-prev aria-label="Scroll photos left">
                <span aria-hidden="true">‹</span>
              </button>
              <ul class="product-gallery__thumbs" data-gallery-thumbs role="tablist" aria-label="Vehicle photos">
                <?php foreach ($gallery as $index => $photo):
                  $pSrc = $photo['src'];
                  if (strpos($pSrc, 'http') === 0) {
                      $large = $pSrc;
                      $thumb = $pSrc;
                  } elseif (strpos($pSrc, '/') === 0) {
                      $large = BASE_URL . $pSrc;
                      $thumb = BASE_URL . $pSrc;
                  } else {
                      $large = "https://images.unsplash.com/{$pSrc}?w=1200&q=80";
                      $thumb = "https://images.unsplash.com/{$pSrc}?w=200&q=80";
                  }
                  $alt = "{$v['title']} — {$photo['label']}";
                  $isActive = $index === 0;
                ?>
                <li>
                  <button
                    type="button"
                    class="product-gallery__thumb<?= $isActive ? ' is-active' : '' ?>"
                    role="tab"
                    aria-selected="<?= $isActive ? 'true' : 'false' ?>"
                    aria-label="Photo <?= $index + 1 ?> — <?= htmlspecialchars($photo['label']) ?>"
                    data-gallery-index="<?= $index ?>"
                    data-gallery-src="<?= htmlspecialchars($large) ?>"
                    data-gallery-alt="<?= htmlspecialchars($alt) ?>"
                  >
                    <img src="<?= htmlspecialchars($thumb) ?>" alt="" width="200" height="133" loading="lazy" />
                  </button>
                </li>
                <?php endforeach; ?>
              </ul>
              <button type="button" class="product-gallery__scroll product-gallery__scroll--next" data-thumb-next aria-label="Scroll photos right">
                <span aria-hidden="true">›</span>
              </button>
            </div>

            <?php if (!empty($v['description'])): ?>
            <div class="product-gallery-desc">
              <h2 class="product-gallery-desc__title" data-i18n="product.overviewTitle">Overview</h2>
              <div class="product-gallery-desc__body">
                <p data-translate-paragraph data-orig-text="<?= htmlspecialchars($v['description']) ?>"><?= nl2br(htmlspecialchars($v['description'])) ?></p>
              </div>
            </div>
            <?php endif; ?>
          </div>

          <aside class="product-buybox card" aria-label="Vehicle summary">
            <div class="product-buybox__meta">
              <span><span data-i18n="product.stockId">Stock Id</span>: <strong><?= htmlspecialchars($v['stockId']) ?></strong></span>
              <span class="product-buybox__location">
                <span data-i18n="product.inventoryLocation">Inventory location</span>:
                <strong <?php if (($v['locationDisplay'] ?? '') === 'N/A'): ?>data-i18n="spec.val.na"<?php endif; ?>><?= htmlspecialchars($v['locationDisplay'] ?? $v['location']) ?></strong>
              </span>
            </div>

            <h1 id="product-title" class="product-buybox__title" data-page-title="product.pageTitle"><?= htmlspecialchars($v['title']) ?></h1>
            <p class="product-buybox__codes">
              <span><?= htmlspecialchars($v['modelCode']) ?></span>
              <span><?= htmlspecialchars($v['year']) ?> <span data-i18n="product.manufactureYear">Manufacture Year</span></span>
              <span><?= htmlspecialchars($v['bodyType']) ?></span>
            </p>

            <div class="product-buybox__social">
              <span class="product-buybox__stars" aria-label="<?= htmlspecialchars($v['rating']) ?> out of 5 stars"><?= htmlspecialchars($v['stars']) ?></span>
              <span><strong><?= (int) $v['reviews'] ?></strong> <span data-i18n="product.reviews">Reviews</span></span>
              <span class="product-buybox__stat"><?= (int) $v['views'] ?> <span data-i18n="product.views">views</span></span>
            </div>

            <div class="product-buybox__pricing">
              <?php if ($v['priceMode'] === 'ask'): ?>
              <p class="product-buybox__ask" data-i18n="product.ask">Ask</p>
              <?php endif; ?>
              <p class="product-buybox__price-row">
                 <span class="product-buybox__price-label" data-i18n="product.vehiclePrice">Vehicle Price</span>
                <strong class="product-buybox__price product-vehicle-price" data-price-usd="<?= (float)$v['priceUsd'] ?>" data-price-jpy="<?= (int) $v['priceJpy'] ?>">¥<?= number_format((int) $v['priceJpy']) ?></strong>
              </p>
            </div>

            <ul class="product-quick-specs">
              <li>
                <span class="product-quick-specs__label" data-i18n="product.spec.mileage">Mileage</span>
                <strong <?php if ($v['mileageDisplay'] === 'N/A'): ?>data-i18n="spec.val.na"<?php endif; ?>><?= htmlspecialchars($v['mileageDisplay']) ?></strong>
              </li>
              <li>
                <span class="product-quick-specs__label" data-i18n="product.spec.engine">Engine</span>
                <strong <?php if ($v['engineDisplay'] === 'N/A'): ?>data-i18n="spec.val.na"<?php endif; ?>><?= htmlspecialchars($v['engineDisplay']) ?></strong>
              </li>
              <li>
                <span class="product-quick-specs__label" data-i18n="product.spec.transmission">Transmission</span>
                <strong <?php if ($v['transmissionDisplay'] === 'N/A'): ?>data-i18n="spec.val.na"<?php else: ?>data-i18n="spec.val.<?= strtolower(str_replace([' ', '(', ')'], ['_', '', ''], $v['transmission'])) ?>"<?php endif; ?>><?= htmlspecialchars($v['transmissionDisplay']) ?></strong>
              </li>
              <li>
                <span class="product-quick-specs__label" data-i18n="product.spec.drive">Drive</span>
                <strong <?php if ($v['driveDisplay'] === 'N/A'): ?>data-i18n="spec.val.na"<?php else: ?>data-i18n="spec.val.<?= strtolower($v['drive']) ?>"<?php endif; ?>><?= htmlspecialchars($v['driveDisplay']) ?></strong>
              </li>
              <li>
                <span class="product-quick-specs__label" data-i18n="product.spec.steering">Steering</span>
                <strong <?php if ($v['steeringDisplay'] === 'N/A'): ?>data-i18n="spec.val.na"<?php else: ?>data-i18n="spec.val.<?= strtolower(str_replace([' ', '(', ')'], ['_', '', ''], $v['steering'])) ?>"<?php endif; ?>><?= htmlspecialchars($v['steeringDisplay']) ?></strong>
              </li>
              <li>
                <span class="product-quick-specs__label" data-i18n="product.spec.fuel">Fuel</span>
                <strong <?php if ($v['fuelDisplay'] === 'N/A'): ?>data-i18n="spec.val.na"<?php else: ?>data-i18n="spec.val.<?= strtolower($v['fuel']) ?>"<?php endif; ?>><?= htmlspecialchars($v['fuelDisplay']) ?></strong>
              </li>
              <li>
                <span class="product-quick-specs__label" data-i18n="product.spec.doors">Door</span>
                <strong <?php if ($v['doorsDisplay'] === 'N/A'): ?>data-i18n="spec.val.na"<?php endif; ?>><?= htmlspecialchars($v['doorsDisplay']) ?></strong>
              </li>
              <li>
                <span class="product-quick-specs__label" data-i18n="product.spec.seats">Seats</span>
                <strong <?php if ($v['seatsDisplay'] === 'N/A'): ?>data-i18n="spec.val.na"<?php endif; ?>><?= htmlspecialchars($v['seatsDisplay']) ?></strong>
              </li>
            </ul>

            <?php if (\App\Core\Session::get('is_logged_in') === true): ?>
              <button type="button" class="btn btn--outline btn--block product-buybox__alert" id="discountAlertBtn" data-i18n="product.discountAlertLoggedIn">Get Discount Alerts</button>
            <?php else: ?>
              <a class="btn btn--outline btn--block product-buybox__alert" href="<?= BASE_URL ?>/login" data-i18n="product.discountAlert">Get Discount Alerts After Login</a>
            <?php endif; ?>

            <div class="product-calculator card">
              <h2 class="product-calculator__title" data-i18n="product.calculatorTitle">Total Price Calculator</h2>
              <label class="product-calculator__field">
                <span class="product-calculator__label" data-i18n="product.calculatorCurrency">Currency</span>
                <select class="form-control" data-product-calc-currency aria-label="Currency">
                  <option value="jpy">JPY</option>
                  <option value="usd">USD</option>
                </select>
              </label>
              <p class="product-calculator__row">
                 <span data-i18n="product.vehiclePrice">Vehicle Price</span>
                <strong class="product-vehicle-price product-calculator__amount" data-price-usd="<?= (float)$v['priceUsd'] ?>" data-price-jpy="<?= (int) $v['priceJpy'] ?>">¥<?= number_format((int) $v['priceJpy']) ?></strong>
              </p>
              <a class="btn btn--primary btn--block product-calculator__whatsapp" href="https://wa.me/" target="_blank" rel="noopener noreferrer">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.435 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                <span data-i18n="product.whatsapp">WhatsApp</span>
              </a>
            </div>
          </aside>
        </div>

        <div class="product-body">
          <div class="product-body__main">
            <div class="product-detail-cols">
              <section class="product-section card" aria-labelledby="product-vehicle-details">
                <h2 id="product-vehicle-details" class="product-section__title" data-i18n="product.vehicleDetails">Vehicle Details</h2>
                <dl class="product-detail-list">
                  <?php foreach ($vehicleDetails as $row):
                    $lblKey = "spec.label." . strtolower(str_replace([' ', '&', '/'], '_', $row['label']));
                    $valKey = "";
                    if ($row['value'] === 'N/A') {
                        $valKey = 'spec.val.na';
                    } elseif (!is_numeric($row['value']) && strpos($row['value'], '.') === false && !preg_match('/^[0-9]+[a-zA-Z\s]+$/', $row['value'])) {
                        if (!str_ends_with(strtolower($row['value']), 'cc') && !str_ends_with(strtolower($row['value']), 'km')) {
                            $valKey = "spec.val." . strtolower(str_replace([' ', '(', ')', '&', '/'], ['_', '', '', '_', '_'], trim($row['value'])));
                        }
                    }
                  ?>
                  <div class="product-detail-list__row">
                    <dt data-i18n="<?= $lblKey ?>"><?= htmlspecialchars($row['label']) ?></dt>
                    <dd <?php if ($valKey): ?>data-i18n="<?= $valKey ?>"<?php endif; ?>><?= htmlspecialchars($row['value']) ?></dd>
                  </div>
                  <?php endforeach; ?>
                </dl>
              </section>

              <section class="product-section card" aria-labelledby="product-specifications">
                <h2 id="product-specifications" class="product-section__title" data-i18n="product.specifications">Specifications</h2>
                <dl class="product-detail-list">
                  <?php foreach ($specifications as $row):
                    $lblKey = "spec.label." . strtolower(str_replace([' ', '&', '/'], '_', $row['label']));
                    $valKey = "";
                    if ($row['value'] === 'N/A') {
                        $valKey = 'spec.val.na';
                    } elseif (!is_numeric($row['value']) && strpos($row['value'], '.') === false && !preg_match('/^[0-9]+[a-zA-Z\s]+$/', $row['value'])) {
                        if (!str_ends_with(strtolower($row['value']), 'cc') && !str_ends_with(strtolower($row['value']), 'km')) {
                            $valKey = "spec.val." . strtolower(str_replace([' ', '(', ')', '&', '/'], ['_', '', '', '_', '_'], trim($row['value'])));
                        }
                    }
                  ?>
                  <div class="product-detail-list__row">
                    <dt data-i18n="<?= $lblKey ?>"><?= htmlspecialchars($row['label']) ?></dt>
                    <dd <?php if ($valKey): ?>data-i18n="<?= $valKey ?>"<?php endif; ?>><?= htmlspecialchars($row['value']) ?></dd>
                  </div>
                  <?php endforeach; ?>
                </dl>
              </section>
            </div>

            <?php if (!empty($optionGroups)): ?>
            <section class="product-section product-section--plain" aria-labelledby="product-car-options">
              <h2 id="product-car-options" class="product-section__title" data-i18n="product.carOptions">Car Options</h2>
              <?php foreach ($optionGroups as $group): ?>
              <?php if (empty($group['items'])) continue; ?>
              <div class="product-options-group">
                <h3 class="product-options-group__title" <?php if (!empty($group['i18n'])): ?>data-i18n="<?= htmlspecialchars($group['i18n']) ?>"<?php endif; ?>><?= htmlspecialchars($group['title']) ?></h3>
                <ul class="product-options-tags">
                  <?php foreach ($group['items'] as $item):
                    $optKey = "option." . strtolower(str_replace([' ', '&', '/'], '_', $item['label']));
                  ?>
                  <li>
                    <span class="product-options-tag is-active">
                      <span class="product-options-tag__label" data-i18n="<?= $optKey ?>"><?= htmlspecialchars($item['label']) ?></span>
                      <span class="product-options-tag__check" aria-hidden="true">✓</span>
                    </span>
                  </li>
                  <?php endforeach; ?>
                </ul>
              </div>
              <?php endforeach; ?>
            </section>
            <?php endif; ?>

            <section class="product-section card" style="margin-top: 2rem;" aria-labelledby="product-guarantee-title">
              <h2 id="product-guarantee-title" class="product-section__title" data-i18n="product.guarantee.title">Eisen Certified Quality Guarantee</h2>
              <div style="display: flex; gap: 1.5rem; align-items: flex-start; flex-wrap: wrap;">
                <div style="flex: 1; min-width: 250px;">
                  <p style="font-weight: bold; margin-bottom: 0.5rem;" data-i18n="product.guarantee.subtitle">100% Inspected & Approved Stock</p>
                  <p style="color: var(--text-muted); font-size: 0.95rem; line-height: 1.6;" data-i18n="product.guarantee.desc">
                    Every vehicle sourced by Eisen Corporation undergoes a rigorous 150-point physical inspection at our Kobe yard. We verify the chassis number, odometer authenticity, engine performance, transmission smoothness, and authenticate the USS auction sheet before booking ocean shipment.
                  </p>
                </div>
                <div style="background: rgba(var(--primary-rgb), 0.05); padding: 1.5rem; border-radius: 8px; border: 1px solid rgba(var(--primary-rgb), 0.1); width: 100%; max-width: 320px;">
                  <h4 style="margin-bottom: 0.5rem; display: flex; align-items: center; gap: 0.5rem;">
                    <span style="color: var(--primary); font-size: 1.2rem;">🛡️</span>
                    <span data-i18n="product.guarantee.cardTitle">Certified Grade</span>
                  </h4>
                  <ul style="list-style: none; padding: 0; font-size: 0.9rem; display: flex; flex-direction: column; gap: 0.5rem;">
                    <li>✓ <strong data-i18n="product.guarantee.item1">Chassis Verified</strong></li>
                    <li>✓ <strong data-i18n="product.guarantee.item2">Odometer Authenticated</strong></li>
                    <li>✓ <strong data-i18n="product.guarantee.item3">Export Compliance Passed</strong></li>
                  </ul>
                </div>
              </div>
            </section>
          </div>

          <aside class="product-estimate card" aria-label="Export estimate" data-product-estimate>
            <form class="product-estimate__form" action="<?= BASE_URL ?>/contact" method="get">
              <div class="form-field">
                <label class="form-label" for="estimate-country">
                  <span data-i18n="product.estimate.country">Destination Country</span>
                  <span class="product-estimate__req" aria-hidden="true">*</span>
                </label>
                <select class="form-control" id="estimate-country" name="country" required>
                  <?php foreach ($estimate['countries'] as $country): ?>
                  <option value="<?= htmlspecialchars($country) ?>"<?= $country === 'PAKISTAN' ? ' selected' : '' ?>><?= htmlspecialchars($country) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="form-field">
                <label class="form-label" for="estimate-port">
                  <span data-i18n="product.estimate.port">Destination Port</span>
                  <span class="product-estimate__req" aria-hidden="true">*</span>
                </label>
                <select class="form-control" id="estimate-port" name="port" required>
                  <?php foreach ($estimate['ports'] as $port): ?>
                  <option value="<?= htmlspecialchars($port) ?>"<?= $port === 'ISLAMABAD' ? ' selected' : '' ?>><?= htmlspecialchars($port) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="form-field">
                <label class="form-label" for="estimate-shipment">
                  <span data-i18n="product.estimate.shipment">Shipment</span>
                  <span class="product-estimate__req" aria-hidden="true">*</span>
                </label>
                <select class="form-control" id="estimate-shipment" name="shipment" required>
                  <?php foreach ($estimate['shipments'] as $shipment): ?>
                  <option value="<?= htmlspecialchars($shipment) ?>"<?= $shipment === 'roro' ? ' selected' : '' ?>><?= strtoupper(htmlspecialchars($shipment)) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>

              <fieldset class="product-estimate__radios">
                <legend class="product-estimate__legend" data-i18n="product.estimate.freight">Freight</legend>
                <label class="product-estimate__radio"><input type="radio" name="freight" value="prepaid" checked /> <span data-i18n="product.estimate.prepaid">Prepaid</span></label>
                <label class="product-estimate__radio"><input type="radio" name="freight" value="collect" /> <span data-i18n="product.estimate.collect">Collect</span></label>
              </fieldset>
              <fieldset class="product-estimate__radios">
                <legend class="product-estimate__legend" data-i18n="product.estimate.inspection">Inspection</legend>
                <label class="product-estimate__radio"><input type="radio" name="inspection" value="yes" checked /> <span data-i18n="product.estimate.yes">YES</span></label>
                <label class="product-estimate__radio"><input type="radio" name="inspection" value="no" /> <span data-i18n="product.estimate.no">NO</span></label>
              </fieldset>
              <fieldset class="product-estimate__radios">
                <legend class="product-estimate__legend" data-i18n="product.estimate.insurance">Insurance</legend>
                <label class="product-estimate__radio"><input type="radio" name="insurance" value="yes" checked /> <span data-i18n="product.estimate.yes">YES</span></label>
                <label class="product-estimate__radio"><input type="radio" name="insurance" value="no" /> <span data-i18n="product.estimate.no">NO</span></label>
              </fieldset>

              <div class="product-estimate__total">
                <div class="product-estimate__total-row">
                  <span class="product-estimate__total-label" data-i18n="product.estimate.total">Total Amount</span>
                  <span class="product-estimate__ask" data-i18n="product.ask">ASK</span>
                </div>
              </div>

              <details class="product-estimate__details" data-estimate-pricing>
                <summary class="product-estimate__details-toggle">
                  <span data-i18n="product.pricing.details">Pricing Details</span>
                  <span class="product-estimate__details-chevron" aria-hidden="true"></span>
                </summary>
                <dl class="product-estimate__breakdown">
                  <?php foreach ($pricingBreakdown as $row): ?>
                  <div class="product-estimate__breakdown-row">
                    <dt<?php if (!empty($row['i18n'])): ?> data-i18n="<?= htmlspecialchars($row['i18n']) ?>"<?php endif; ?>><?= htmlspecialchars($row['label']) ?></dt>
                    <dd>
                      <?php if (!empty($row['mode']) && $row['mode'] === 'ask'): ?>
                      <span class="product-estimate__value-ask" data-i18n="product.ask">ASK</span>
                      <?php else: ?>
                      <span class="product-estimate__value-jpy" data-estimate-price-usd="<?= (float) ($row['usd'] ?? 0) ?>"><?= number_format((float) ($row['usd'] ?? 0), 2) ?></span>
                      <?php endif; ?>
                    </dd>
                  </div>
                  <?php endforeach; ?>
                </dl>
              </details>

              <p class="product-estimate__hint" data-i18n="product.pricing.askHint">Ask the price</p>

              <button type="submit" class="btn btn--primary btn--block product-estimate__submit" data-i18n="product.estimate.cta">Get an estimate</button>
            </form>
          </aside>
        </div>

        <div class="product-recs-wrap">
          <section class="product-recs" aria-labelledby="recs-this-title">
            <header class="product-recs__head">
              <h2 id="recs-this-title" class="product-recs__title" data-i18n="product.recs.similar">Recommended for you</h2>
              <p class="product-recs__subtitle" data-i18n="product.recs.similarSub">Based on this vehicle</p>
            </header>
            <ul class="product-recs-grid">
              <?php if (!empty($recommendations)): ?>
                <?php foreach ($recommendations as $rec):
                  $recImg = $rec['image'];
                  if (strpos($recImg, 'http') === 0) {
                      $imgUrl = $recImg;
                  } elseif (strpos($recImg, '/') === 0) {
                      $imgUrl = BASE_URL . $recImg;
                  } else {
                      $imgUrl = "https://images.unsplash.com/{$recImg}?w=600&q=80";
                  }
                ?>
                <li>
                  <a href="<?= BASE_URL ?>/product/<?= htmlspecialchars($rec['stockId']) ?>" class="product-rec-card">
                    <div class="product-rec-card__media">
                      <img src="<?= htmlspecialchars($imgUrl) ?>" alt="<?= htmlspecialchars($rec['title']) ?>" width="600" height="400" loading="lazy" />
                    </div>
                    <div class="product-rec-card__body">
                      <h3 class="product-rec-card__name"><?= htmlspecialchars($rec['title']) ?></h3>
                       <p class="product-rec-card__meta">
                        <span class="product-vehicle-price" data-price-usd="<?= (float)$rec['priceUsd'] ?>" data-price-jpy="<?= (int)$rec['priceJpy'] ?>">¥<?= number_format((int)$rec['priceJpy']) ?></span> · <?= number_format((int)($rec['mileageKm'] / 1000)) ?>K km
                      </p>
                      <p class="product-rec-card__location"><?= htmlspecialchars($rec['location']) ?></p>
                    </div>
                  </a>
                </li>
                <?php endforeach; ?>
              <?php else: ?>
                <li style="grid-column: 1 / -1; text-align: center; color: var(--text-muted); padding: 2rem;" data-i18n="product.recs.none">No recommendations available at this time.</li>
              <?php endif; ?>
            </ul>
          </section>
        </div>

      </div>
    </section>

  </main>

  <div class="product-lightbox" data-gallery-lightbox hidden>
    <div class="product-lightbox__backdrop" data-gallery-close></div>
    <div class="product-lightbox__dialog" role="dialog" aria-modal="true" aria-label="Vehicle photo gallery">
      <button class="product-lightbox__close" type="button" data-gallery-close aria-label="Close gallery">×</button>
      <button class="product-lightbox__nav product-lightbox__nav--prev" type="button" data-gallery-prev aria-label="Previous photo">‹</button>
      <img class="product-lightbox__img" data-gallery-lightbox-img src="" alt="" />
      <button class="product-lightbox__nav product-lightbox__nav--next" type="button" data-gallery-next aria-label="Next photo">›</button>
      <p class="product-lightbox__counter" data-gallery-counter aria-live="polite"></p>
    </div>
  </div>

<?php include __DIR__ . '/partials/footer.php'; ?>
