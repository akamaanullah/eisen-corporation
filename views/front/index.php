<?php include __DIR__ . '/partials/header.php'; ?>
<script>
  window.EisenMakeToModels = <?= json_encode($makeToModels ?? []) ?>;
</script>

  <main id="main">

    <section class="hero" data-i18n-aria-label="hero.aria" aria-label="Featured vehicles and search">
      <div class="container hero__grid">
        <div class="hero-slider card" data-slider>
          <div class="hero-slider__track">
            <?php if (!empty($sliders)): ?>
              <?php foreach ($sliders as $index => $slide): ?>
                <article class="hero-slide<?= $index === 0 ? ' is-active' : '' ?>" data-slide="<?= $index ?>"<?= $index > 0 ? ' hidden' : '' ?>>
                  <img
                    class="hero-slide__img"
                    src="<?= htmlspecialchars($slide['image_url']) ?>"
                    alt="<?= htmlspecialchars($slide['title'] ?? 'Eisen Corporation promotional banner') ?>"
                    width="1200"
                    height="675"
                    <?= $index === 0 ? 'fetchpriority="high"' : 'loading="lazy"' ?>
                  />
                </article>
              <?php endforeach; ?>
            <?php else: ?>
              <article class="hero-slide is-active" data-slide="0">
                <img
                  class="hero-slide__img"
                  src="https://images.unsplash.com/photo-1606664515524-ed2f786a0bd6?w=1200&q=80"
                  alt="Eisen Corporation promotional banner 1"
                  width="1200"
                  height="675"
                  fetchpriority="high"
                />
              </article>
            <?php endif; ?>
          </div>

          <div class="hero-slider__aside-ui">
            <div class="hero-slider__dots" role="tablist" data-i18n-aria-label="slider.slides" aria-label="Featured slides">
              <?php if (!empty($sliders)): ?>
                <?php foreach ($sliders as $index => $slide): ?>
                  <button class="hero-slider__dot<?= $index === 0 ? ' is-active' : '' ?>" type="button" role="tab" aria-selected="<?= $index === 0 ? 'true' : 'false' ?>" aria-label="Slide <?= $index + 1 ?>" data-goto="<?= $index ?>"></button>
                <?php endforeach; ?>
              <?php else: ?>
                <button class="hero-slider__dot is-active" type="button" role="tab" aria-selected="true" aria-label="Slide 1" data-goto="0"></button>
              <?php endif; ?>
            </div>
          </div>

          <button class="hero-slider__arrow hero-slider__arrow--prev" type="button" data-i18n-aria-label="slider.prev" aria-label="Previous slide" data-prev></button>
          <button class="hero-slider__arrow hero-slider__arrow--next" type="button" data-i18n-aria-label="slider.next" aria-label="Next slide" data-next></button>
        </div>

        <aside class="search-filter card" aria-labelledby="search-filter-title">
          <h2 id="search-filter-title" class="search-filter__title" data-i18n="filter.title">Search Auto</h2>
          <form class="search-filter__form" action="<?= BASE_URL ?>/listing" method="GET">
            <div class="form-field">
              <label class="form-label" for="manufacturer" data-i18n="filter.manufacturer">Manufacturer</label>
              <select class="form-control" id="manufacturer" name="make">
                <option value="" data-i18n="filter.allManufacturers">All manufacturers</option>
                <?php if (!empty($makes)): ?>
                  <?php foreach ($makes as $make): 
                    $makeKey = strtolower($make);
                  ?>
                    <option value="<?= htmlspecialchars($makeKey) ?>" data-i18n="spec.val.<?= htmlspecialchars($makeKey) ?>"><?= htmlspecialchars($make) ?></option>
                  <?php endforeach; ?>
                <?php else: ?>
                  <option value="toyota">Toyota</option>
                  <option value="honda">Honda</option>
                  <option value="nissan">Nissan</option>
                  <option value="bmw">BMW</option>
                  <option value="mercedes">Mercedes-Benz</option>
                  <option value="audi">Audi</option>
                  <option value="lexus">Lexus</option>
                <?php endif; ?>
              </select>
            </div>

            <div class="form-field">
              <label class="form-label" for="model" data-i18n="filter.model">Model</label>
              <select class="form-control" id="model" name="model">
                <option value="" data-i18n="filter.allModels">All models</option>
              </select>
            </div>

            <div class="form-row">
              <div class="form-field">
                <label class="form-label" for="year-from" data-i18n="filter.yearFrom">Year from</label>
                <select class="form-control" id="year-from" name="year_min">
                  <option value="" data-i18n="filter.any">Any</option>
                  <option value="2025">2025</option>
                  <option value="2024">2024</option>
                  <option value="2023">2023</option>
                  <option value="2022">2022</option>
                  <option value="2021">2021</option>
                  <option value="2020">2020</option>
                  <option value="2019">2019</option>
                </select>
              </div>
              <div class="form-field">
                <label class="form-label" for="year-to" data-i18n="filter.yearTo">Year to</label>
                <select class="form-control" id="year-to" name="year_max">
                  <option value="" data-i18n="filter.any">Any</option>
                  <option value="2025">2025</option>
                  <option value="2024">2024</option>
                  <option value="2023">2023</option>
                  <option value="2022">2022</option>
                  <option value="2021">2021</option>
                  <option value="2020">2020</option>
                  <option value="2019">2019</option>
                </select>
              </div>
            </div>

            <div class="form-row">
              <div class="form-field">
                <label class="form-label" for="price-from" data-i18n="filter.priceFrom">Price from</label>
                <select class="form-control" id="price-from" name="price_min">
                  <option value="" data-i18n="filter.any">Any</option>
                  <option value="10000">$10,000</option>
                  <option value="20000">$20,000</option>
                  <option value="30000">$30,000</option>
                  <option value="50000">$50,000</option>
                </select>
              </div>
              <div class="form-field">
                <label class="form-label" for="price-to" data-i18n="filter.priceTo">Price to</label>
                <select class="form-control" id="price-to" name="price_max">
                  <option value="" data-i18n="filter.any">Any</option>
                  <option value="30000">$30,000</option>
                  <option value="50000">$50,000</option>
                  <option value="75000">$75,000</option>
                  <option value="100000">$100,000+</option>
                </select>
              </div>
            </div>

            <div class="form-row">
              <div class="form-field">
                <label class="form-label" for="mileage-from" data-i18n="filter.mileageFrom">Mileage from</label>
                <select class="form-control" id="mileage-from" name="mileage_min">
                  <option value="" data-i18n="filter.any">Any</option>
                  <option value="0">0 km</option>
                  <option value="10">10,000 km</option>
                  <option value="50">50,000 km</option>
                </select>
              </div>
              <div class="form-field">
                <label class="form-label" for="mileage-to" data-i18n="filter.mileageTo">Mileage to</label>
                <select class="form-control" id="mileage-to" name="mileage_max">
                  <option value="" data-i18n="filter.any">Any</option>
                  <option value="30">30,000 km</option>
                  <option value="80">80,000 km</option>
                  <option value="150">150,000 km</option>
                </select>
              </div>
            </div>

            <label class="form-checkbox">
              <input type="checkbox" name="condition" value="new" />
              <span data-i18n="filter.newOnly">Only new cars</span>
            </label>

            <button class="btn btn--primary btn--block search-filter__submit" type="submit" data-i18n="filter.submit">Search</button>
          </form>
        </aside>
      </div>
    </section>

    <section id="listings" class="listings section" aria-labelledby="listings-title">
      <div class="container">
        <header class="section-header">
          <h2 id="listings-title" class="section-title" data-i18n="listings.title">Recent Listings</h2>
          <a class="section-link" href="<?= BASE_URL ?>/listing" data-i18n="listings.viewAll">View all inventory</a>
        </header>

        <div class="listings-slider-container">
          <button class="listings-slider-btn listings-slider-btn--prev" type="button" aria-label="Previous listings">‹</button>
          <button class="listings-slider-btn listings-slider-btn--next" type="button" aria-label="Next listings">›</button>
          <ul class="listings-slider">
            <?php if (!empty($cars)): ?>
              <?php foreach ($cars as $car): 
                $imgUrl = $car['image_url'];
                if (empty($imgUrl)) {
                    $carImg = 'https://images.unsplash.com/photo-1549317661-bd32c8ce0db2?w=600&q=80';
                } elseif (strpos($imgUrl, 'http') === 0) {
                    $carImg = $imgUrl;
                } elseif (strpos($imgUrl, '/') === 0) {
                    $carImg = BASE_URL . $imgUrl;
                } else {
                    $carImg = "https://images.unsplash.com/{$imgUrl}?w=600&q=80";
                }
              ?>
              <li>
                <a href="<?= BASE_URL ?>/product/<?= htmlspecialchars($car['stock_id']) ?>" class="listing-card">
                  <div class="listing-card__media">
                    <img src="<?= htmlspecialchars($carImg) ?>" alt="<?= htmlspecialchars($car['make'] . ' ' . ($car['display_model'] ?? $car['model'])) ?>" width="600" height="400" loading="lazy" />
                  </div>
                  <div class="listing-card__footer">
                    <span class="listing-card__name"><?= htmlspecialchars($car['make'] . ' ' . ($car['display_model'] ?? $car['model'])) ?></span>
                    <span class="listing-card__price">$<?= number_format((float)$car['fob_price']) ?></span>
                  </div>
                </a>
              </li>
              <?php endforeach; ?>
            <?php else: ?>
              <li>
                <a href="#" class="listing-card">
                  <div class="listing-card__media">
                    <img src="https://images.unsplash.com/photo-1549317661-bd32c8ce0db2?w=600&q=80" alt="White compact SUV" width="600" height="400" loading="lazy" />
                  </div>
                  <div class="listing-card__footer">
                    <span class="listing-card__name">Honda Vezel</span>
                    <span class="listing-card__price">$24,500</span>
                  </div>
                </a>
              </li>
              <li>
                <a href="#" class="listing-card">
                  <div class="listing-card__media">
                    <img src="https://images.unsplash.com/photo-1552519507-da3b142c6e3d?w=600&q=80" alt="Blue Ford Mustang coupe" width="600" height="400" loading="lazy" />
                  </div>
                  <div class="listing-card__footer">
                    <span class="listing-card__name">Ford Mustang</span>
                    <span class="listing-card__price">$38,900</span>
                  </div>
                </a>
              </li>
              <li>
                <a href="#" class="listing-card">
                  <div class="listing-card__media">
                    <img src="https://images.unsplash.com/photo-1503376780353-7e6692767b70?w=600&q=80" alt="Black Porsche coupe" width="600" height="400" loading="lazy" />
                  </div>
                  <div class="listing-card__footer">
                    <span class="listing-card__name">Porsche 911</span>
                    <span class="listing-card__price">$89,200</span>
                  </div>
                </a>
              </li>
              <li>
                <a href="#" class="listing-card">
                  <div class="listing-card__media">
                    <img src="https://images.unsplash.com/photo-1553440569-bcc63803a83d?w=600&q=80" alt="Red Ferrari sports car" width="600" height="400" loading="lazy" />
                  </div>
                  <div class="listing-card__footer">
                    <span class="listing-card__name">Ferrari 488</span>
                    <span class="listing-card__price">$215,000</span>
                  </div>
                </a>
              </li>
            <?php endif; ?>
          </ul>
        </div>

        <div class="cta-banners__grid" aria-label="Quick actions">
          <article class="cta-banner cta-banner--buy">
            <div class="cta-banner__icon cta-banner__icon--search" aria-hidden="true">
              <svg class="cta-banner__svg" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                <circle cx="20" cy="20" r="11" stroke="currentColor" stroke-width="3.5" />
                <line x1="28.5" y1="28.5" x2="40" y2="40" stroke="currentColor" stroke-width="3.5" stroke-linecap="round" />
              </svg>
            </div>
            <div class="cta-banner__content">
              <h3 class="cta-banner__title" data-i18n="cta.buy.title">Looking for a car?</h3>
              <p class="cta-banner__text" data-i18n="cta.buy.text">Explore our active Japan inventory and calculate direct shipping costs to your destination port.</p>
            </div>
            <a class="btn btn--white" href="<?= BASE_URL ?>/listing" data-i18n="cta.buy.btn">Search</a>
          </article>

          <article class="cta-banner cta-banner--urgent">
            <div class="cta-banner__icon cta-banner__icon--urgent" aria-hidden="true">
              <svg class="cta-banner__svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"></circle>
                <polyline points="12 6 12 12 16 14"></polyline>
              </svg>
            </div>
            <div class="cta-banner__content">
              <h3 class="cta-banner__title" data-i18n="cta.urgent.title">Need a car urgently?</h3>
              <p class="cta-banner__text" data-i18n="cta.urgent.text">Can't find the model you want? Submit an urgent sourcing request and we'll bid for you at live USS auctions.</p>
            </div>
            <a class="btn btn--white" href="<?= BASE_URL ?>/contact" data-i18n="cta.urgent.btn">Inquire Now</a>
          </article>
        </div>
      </div>
    </section>

    <section id="blog" class="blog-hub section" aria-labelledby="blog-hub-title">
      <div class="container">
        <header class="section-header">
          <h2 id="blog-hub-title" class="section-title" data-i18n="blog.title">Recent from the blog</h2>
          <a class="section-link" href="<?= BASE_URL ?>/blogs" data-i18n="blog.viewAll">View all blog</a>
        </header>

        <div class="blog-hub__layout">
          <div class="blog-hub__main">
            <div class="blog-cards">
              <?php if (!empty($posts)): ?>
                <?php foreach ($posts as $post): 
                  $img = $post['image'];
                  $imageSrc = empty($img) ? 'https://images.unsplash.com/photo-1549317661-bd32c8ce0db2?w=600&q=80' : 
                             (str_starts_with($img, 'http') ? $img : (str_starts_with($img, '/') ? BASE_URL . $img : "https://images.unsplash.com/{$img}?w=600&q=80"));
                ?>
                <article class="blog-card">
                  <a href="<?= BASE_URL ?>/blog/<?= htmlspecialchars($post['slug']) ?>" class="blog-card__link">
                    <div class="blog-card__media">
                      <img src="<?= htmlspecialchars($imageSrc) ?>" alt="<?= htmlspecialchars($post['title']) ?>" width="600" height="360" loading="lazy" />
                    </div>
                    <div class="blog-card__body">
                      <h3 class="blog-card__title" data-translate-paragraph><?= htmlspecialchars($post['title']) ?></h3>
                      <time class="blog-card__date" datetime="<?= htmlspecialchars($post['published_date']) ?>"><?= htmlspecialchars($post['dateLabel']) ?></time>
                      <p class="blog-card__excerpt" data-translate-paragraph><?= htmlspecialchars($post['excerpt']) ?></p>
                    </div>
                  </a>
                </article>
                <?php endforeach; ?>
              <?php endif; ?>
            </div>

            <div id="directory" class="directory-tabs" data-directory-tabs>
              <div class="directory-tabs__nav" role="tablist" data-i18n-aria-label="directory.aria" aria-label="Directory">
                <button class="directory-tabs__btn is-active" type="button" role="tab" id="tab-dealers" aria-selected="true" aria-controls="panel-dealers" data-tab="dealers" data-i18n="directory.dealers">Dealers</button>
                <button class="directory-tabs__btn" type="button" role="tab" id="tab-service" aria-selected="false" aria-controls="panel-service" data-tab="service" data-i18n="directory.service">Service Stations</button>
                <button class="directory-tabs__btn" type="button" role="tab" id="tab-insurance" aria-selected="false" aria-controls="panel-insurance" data-tab="insurance" data-i18n="directory.insurance">Insurance</button>
              </div>

              <div class="directory-tabs__panels">
                <div class="directory-panel is-active" id="panel-dealers" role="tabpanel" aria-labelledby="tab-dealers" data-panel="dealers">
                  <p class="directory-panel__count" data-i18n="directory.countDealers">Found <?= count($dealers) ?> dealers</p>
                  <div class="dealer-logos-marquee">
                    <ul class="dealer-logos dealer-logos--slide">
                      <?php if (!empty($dealers)): ?>
                        <?php foreach ($dealers as $p): 
                          $logoSrc = str_starts_with($p['logo_url'], 'http') ? $p['logo_url'] : BASE_URL . $p['logo_url'];
                        ?>
                          <li class="dealer-logos__item">
                            <img class="dealer-logos__img" src="<?= htmlspecialchars($logoSrc) ?>" alt="<?= htmlspecialchars($p['name']) ?>" width="160" height="64" loading="lazy" decoding="async">
                          </li>
                        <?php endforeach; ?>
                        <?php foreach ($dealers as $p): 
                          $logoSrc = str_starts_with($p['logo_url'], 'http') ? $p['logo_url'] : BASE_URL . $p['logo_url'];
                        ?>
                          <li class="dealer-logos__item dealer-logos__item--clone" aria-hidden="true">
                            <img class="dealer-logos__img" src="<?= htmlspecialchars($logoSrc) ?>" alt="" width="160" height="64" loading="lazy" decoding="async">
                          </li>
                        <?php endforeach; ?>
                      <?php else: ?>
                        <li class="dealer-logos__item">
                          <img class="dealer-logos__img" src="<?= BASE_URL ?>/public/image/mira-daihatsu.png" alt="Mira Daihatsu" width="160" height="64" loading="lazy" decoding="async">
                        </li>
                        <li class="dealer-logos__item">
                          <img class="dealer-logos__img" src="<?= BASE_URL ?>/public/image/toyota.png" alt="Toyota" width="160" height="64" loading="lazy" decoding="async">
                        </li>
                        <li class="dealer-logos__item">
                          <img class="dealer-logos__img" src="<?= BASE_URL ?>/public/image/nissan.png" alt="Nissan" width="160" height="64" loading="lazy" decoding="async">
                        </li>
                        <li class="dealer-logos__item">
                          <img class="dealer-logos__img" src="<?= BASE_URL ?>/public/image/daihatsu.png" alt="Daihatsu" width="160" height="64" loading="lazy" decoding="async">
                        </li>
                        <li class="dealer-logos__item dealer-logos__item--clone" aria-hidden="true">
                          <img class="dealer-logos__img" src="<?= BASE_URL ?>/public/image/mira-daihatsu.png" alt="" width="160" height="64" loading="lazy" decoding="async">
                        </li>
                        <li class="dealer-logos__item dealer-logos__item--clone" aria-hidden="true">
                          <img class="dealer-logos__img" src="<?= BASE_URL ?>/public/image/toyota.png" alt="" width="160" height="64" loading="lazy" decoding="async">
                        </li>
                        <li class="dealer-logos__item dealer-logos__item--clone" aria-hidden="true">
                          <img class="dealer-logos__img" src="<?= BASE_URL ?>/public/image/nissan.png" alt="" width="160" height="64" loading="lazy" decoding="async">
                        </li>
                        <li class="dealer-logos__item dealer-logos__item--clone" aria-hidden="true">
                          <img class="dealer-logos__img" src="<?= BASE_URL ?>/public/image/daihatsu.png" alt="" width="160" height="64" loading="lazy" decoding="async">
                        </li>
                      <?php endif; ?>
                    </ul>
                  </div>
                </div>

                <div class="directory-panel" id="panel-service" role="tabpanel" aria-labelledby="tab-service" data-panel="service" hidden>
                  <p class="directory-panel__count" data-i18n="directory.countService">Found <?= count($services) ?> service stations</p>
                  <div class="dealer-logos-marquee">
                    <ul class="dealer-logos dealer-logos--slide">
                      <?php if (!empty($services)): ?>
                        <?php foreach ($services as $p): 
                          $logoSrc = str_starts_with($p['logo_url'], 'http') ? $p['logo_url'] : BASE_URL . $p['logo_url'];
                        ?>
                          <li class="dealer-logos__item">
                            <img class="dealer-logos__img" src="<?= htmlspecialchars($logoSrc) ?>" alt="<?= htmlspecialchars($p['name']) ?>" width="160" height="64" loading="lazy" decoding="async">
                          </li>
                        <?php endforeach; ?>
                        <?php foreach ($services as $p): 
                          $logoSrc = str_starts_with($p['logo_url'], 'http') ? $p['logo_url'] : BASE_URL . $p['logo_url'];
                        ?>
                          <li class="dealer-logos__item dealer-logos__item--clone" aria-hidden="true">
                            <img class="dealer-logos__img" src="<?= htmlspecialchars($logoSrc) ?>" alt="" width="160" height="64" loading="lazy" decoding="async">
                          </li>
                        <?php endforeach; ?>
                      <?php else: ?>
                        <li class="dealer-logos__item">
                          <img class="dealer-logos__img" src="<?= BASE_URL ?>/public/image/mira-daihatsu.png" alt="Mira Daihatsu" width="160" height="64" loading="lazy" decoding="async">
                        </li>
                        <li class="dealer-logos__item">
                          <img class="dealer-logos__img" src="<?= BASE_URL ?>/public/image/toyota.png" alt="Toyota" width="160" height="64" loading="lazy" decoding="async">
                        </li>
                        <li class="dealer-logos__item">
                          <img class="dealer-logos__img" src="<?= BASE_URL ?>/public/image/nissan.png" alt="Nissan" width="160" height="64" loading="lazy" decoding="async">
                        </li>
                        <li class="dealer-logos__item">
                          <img class="dealer-logos__img" src="<?= BASE_URL ?>/public/image/daihatsu.png" alt="Daihatsu" width="160" height="64" loading="lazy" decoding="async">
                        </li>
                        <li class="dealer-logos__item dealer-logos__item--clone" aria-hidden="true">
                          <img class="dealer-logos__img" src="<?= BASE_URL ?>/public/image/mira-daihatsu.png" alt="" width="160" height="64" loading="lazy" decoding="async">
                        </li>
                        <li class="dealer-logos__item dealer-logos__item--clone" aria-hidden="true">
                          <img class="dealer-logos__img" src="<?= BASE_URL ?>/public/image/toyota.png" alt="" width="160" height="64" loading="lazy" decoding="async">
                        </li>
                        <li class="dealer-logos__item dealer-logos__item--clone" aria-hidden="true">
                          <img class="dealer-logos__img" src="<?= BASE_URL ?>/public/image/nissan.png" alt="" width="160" height="64" loading="lazy" decoding="async">
                        </li>
                        <li class="dealer-logos__item dealer-logos__item--clone" aria-hidden="true">
                          <img class="dealer-logos__img" src="<?= BASE_URL ?>/public/image/daihatsu.png" alt="" width="160" height="64" loading="lazy" decoding="async">
                        </li>
                      <?php endif; ?>
                    </ul>
                  </div>
                </div>

                <div class="directory-panel" id="panel-insurance" role="tabpanel" aria-labelledby="tab-insurance" data-panel="insurance" hidden>
                  <p class="directory-panel__count" data-i18n="directory.countInsurance">Found <?= count($insurances) ?> insurance partners</p>
                  <div class="dealer-logos-marquee">
                    <ul class="dealer-logos dealer-logos--slide">
                      <?php if (!empty($insurances)): ?>
                        <?php foreach ($insurances as $p): 
                          $logoSrc = str_starts_with($p['logo_url'], 'http') ? $p['logo_url'] : BASE_URL . $p['logo_url'];
                        ?>
                          <li class="dealer-logos__item">
                            <img class="dealer-logos__img" src="<?= htmlspecialchars($logoSrc) ?>" alt="<?= htmlspecialchars($p['name']) ?>" width="160" height="64" loading="lazy" decoding="async">
                          </li>
                        <?php endforeach; ?>
                        <?php foreach ($insurances as $p): 
                          $logoSrc = str_starts_with($p['logo_url'], 'http') ? $p['logo_url'] : BASE_URL . $p['logo_url'];
                        ?>
                          <li class="dealer-logos__item dealer-logos__item--clone" aria-hidden="true">
                            <img class="dealer-logos__img" src="<?= htmlspecialchars($logoSrc) ?>" alt="" width="160" height="64" loading="lazy" decoding="async">
                          </li>
                        <?php endforeach; ?>
                      <?php else: ?>
                        <li class="dealer-logos__item">
                          <img class="dealer-logos__img" src="<?= BASE_URL ?>/public/image/mira-daihatsu.png" alt="Mira Daihatsu" width="160" height="64" loading="lazy" decoding="async">
                        </li>
                        <li class="dealer-logos__item">
                          <img class="dealer-logos__img" src="<?= BASE_URL ?>/public/image/toyota.png" alt="Toyota" width="160" height="64" loading="lazy" decoding="async">
                        </li>
                        <li class="dealer-logos__item">
                          <img class="dealer-logos__img" src="<?= BASE_URL ?>/public/image/nissan.png" alt="Nissan" width="160" height="64" loading="lazy" decoding="async">
                        </li>
                        <li class="dealer-logos__item">
                          <img class="dealer-logos__img" src="<?= BASE_URL ?>/public/image/daihatsu.png" alt="Daihatsu" width="160" height="64" loading="lazy" decoding="async">
                        </li>
                        <li class="dealer-logos__item dealer-logos__item--clone" aria-hidden="true">
                          <img class="dealer-logos__img" src="<?= BASE_URL ?>/public/image/mira-daihatsu.png" alt="" width="160" height="64" loading="lazy" decoding="async">
                        </li>
                        <li class="dealer-logos__item dealer-logos__item--clone" aria-hidden="true">
                          <img class="dealer-logos__img" src="<?= BASE_URL ?>/public/image/toyota.png" alt="" width="160" height="64" loading="lazy" decoding="async">
                        </li>
                        <li class="dealer-logos__item dealer-logos__item--clone" aria-hidden="true">
                          <img class="dealer-logos__img" src="<?= BASE_URL ?>/public/image/nissan.png" alt="" width="160" height="64" loading="lazy" decoding="async">
                        </li>
                        <li class="dealer-logos__item dealer-logos__item--clone" aria-hidden="true">
                          <img class="dealer-logos__img" src="<?= BASE_URL ?>/public/image/daihatsu.png" alt="" width="160" height="64" loading="lazy" decoding="async">
                        </li>
                      <?php endif; ?>
                    </ul>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <aside class="blog-sidebar" aria-labelledby="blog-sidebar-title">
            <div class="blog-sidebar__head">
              <h2 id="blog-sidebar-title" class="blog-sidebar__title" data-i18n="sidebar.title">Auto news</h2>
            </div>

            <div class="blog-sidebar__posts">
              <?php if (!empty($sidebarPosts)): ?>
                <?php foreach ($sidebarPosts as $post): 
                  $img = $post['image'];
                  $imageSrc = empty($img) ? 'https://images.unsplash.com/photo-1549317661-bd32c8ce0db2?w=600&q=80' : 
                             (str_starts_with($img, 'http') ? $img : (str_starts_with($img, '/') ? BASE_URL . $img : "https://images.unsplash.com/{$img}?w=600&q=80"));
                ?>
                <article class="news-item">
                  <a href="<?= BASE_URL ?>/blog/<?= htmlspecialchars($post['slug']) ?>" class="news-item__link">
                    <div class="news-item__media">
                      <img src="<?= htmlspecialchars($imageSrc) ?>" alt="<?= htmlspecialchars($post['title']) ?>" width="400" height="240" loading="lazy" />
                    </div>
                    <h3 class="news-item__title" data-translate-paragraph><?= htmlspecialchars($post['title']) ?></h3>
                    <time class="news-item__date" datetime="<?= htmlspecialchars($post['published_date']) ?>"><?= htmlspecialchars($post['dateLabel']) ?></time>
                    <p class="news-item__excerpt" data-translate-paragraph><?= htmlspecialchars($post['excerpt']) ?></p>
                  </a>
                </article>
                <?php endforeach; ?>
              <?php else: ?>
                <article class="news-item">
                  <a href="#news" class="news-item__link">
                    <div class="news-item__media">
                      <img src="https://images.unsplash.com/photo-1606664515524-ed2f786a0bd6?w=400&q=80" alt="Porsche sports car" width="400" height="240" loading="lazy" />
                    </div>
                    <h3 class="news-item__title" data-i18n="news.title">Unofficial Porsche 918 Spyder pricing pops up</h3>
                    <time class="news-item__date" datetime="2020-09-16" data-i18n="news.date">September, 16, 2020</time>
                    <p class="news-item__excerpt" data-i18n="news.excerpt1">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed ut perspiciatis unde omnis iste natus error.</p>
                  </a>
                </article>

                <article class="news-item">
                  <a href="#news" class="news-item__link">
                    <div class="news-item__media">
                      <img src="https://images.unsplash.com/photo-1555215695-3004980ad54e?w=400&q=80" alt="White sedan" width="400" height="240" loading="lazy" />
                    </div>
                    <h3 class="news-item__title" data-i18n="news.title">Unofficial Porsche 918 Spyder pricing pops up</h3>
                    <time class="news-item__date" datetime="2020-09-16" data-i18n="news.date">September, 16, 2020</time>
                    <p class="news-item__excerpt" data-i18n="news.excerpt2">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nemo enim ipsam voluptatem quia voluptas sit.</p>
                  </a>
                </article>
              <?php endif; ?>
            </div>

            <a class="btn btn--primary btn--block blog-sidebar__btn" href="<?= BASE_URL ?>/blogs" data-i18n="sidebar.allNews">All news</a>
          </aside>
        </div>
      </div>
    </section>

    <section id="process" class="export-process section" aria-labelledby="process-title">
      <div class="container">
        <header class="section-header section-header--center">
          <h2 id="process-title" class="section-title" data-i18n="process.title">Our Export Process</h2>
          <p class="section-subtitle" data-i18n="process.subtitle">Simple 4-Step Process to Import Your Car from Japan</p>
        </header>

        <div class="process-grid">
          <article class="process-card">
            <div class="process-card__number">01</div>
            <div class="process-card__media">
              <img src="https://images.unsplash.com/photo-1618843479313-40f8afb4b4d8?w=500&q=80" alt="Browse Stock & Auctions" width="500" height="300" loading="lazy" />
            </div>
            <div class="process-card__body">
              <h3 class="process-card__title" data-i18n="process.step1.title">1. Browse Stock & Auctions</h3>
              <p class="process-card__text" data-i18n="process.step1.desc">Choose from our active local inventory or query thousands of live Japanese auction listings online.</p>
            </div>
          </article>

          <article class="process-card">
            <div class="process-card__number">02</div>
            <div class="process-card__media">
              <img src="https://images.unsplash.com/photo-1554224155-8d04cb21cd6c?w=500&q=80" alt="Calculate CIF & Reserve" width="500" height="300" loading="lazy" />
            </div>
            <div class="process-card__body">
              <h3 class="process-card__title" data-i18n="process.step2.title">2. Calculate & Hold</h3>
              <p class="process-card__text" data-i18n="process.step2.desc">Use our built-in CIF calculator to estimate shipping, and reserve the vehicle to secure your purchase.</p>
            </div>
          </article>

          <article class="process-card">
            <div class="process-card__number">03</div>
            <div class="process-card__media">
              <img src="https://images.unsplash.com/photo-1507136566006-cfc505b114fc?w=500&q=80" alt="Rigorous Inspection" width="500" height="300" loading="lazy" />
            </div>
            <div class="process-card__body">
              <h3 class="process-card__title" data-i18n="process.step3.title">3. Inspection & Prep</h3>
              <p class="process-card__text" data-i18n="process.step3.desc">Our yard technicians carry out detailed physical checks, export-vanning, and custom compliance reports.</p>
            </div>
          </article>

          <article class="process-card">
            <div class="process-card__number">04</div>
            <div class="process-card__media">
              <img src="https://images.unsplash.com/photo-1578575437130-527eed3abbec?w=500&q=80" alt="Ocean Shipping & Documentation" width="500" height="300" loading="lazy" />
            </div>
            <div class="process-card__body">
              <h3 class="process-card__title" data-i18n="process.step4.title">4. Ocean Shipping</h3>
              <p class="process-card__text" data-i18n="process.step4.desc">We handle customs clearance, book fast ocean carriers, and dispatch original titles to your port of choice.</p>
            </div>
          </article>
        </div>
      </div>
    </section>

  </main>
<?php include __DIR__ . '/partials/footer.php'; ?>