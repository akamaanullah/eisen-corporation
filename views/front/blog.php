<?php
use App\Helpers\ImageUrl;
?>
<?php include __DIR__ . '/partials/header.php'; ?>

  <main id="main" class="blog-page">

    <section class="blog-page__hero" aria-labelledby="blog-page-title">
      <div class="blog-page__hero-inner">
        <p class="blog-page__eyebrow" data-i18n="blogPage.eyebrow">Eisen insights</p>
        <h1 id="blog-page-title" class="blog-page__title" data-i18n="blogPage.title">Insights &amp; Industry News</h1>
        <p class="blog-page__lead" data-i18n="blogPage.lead">
          Expert guides on Japan auctions, vehicle imports, and the global auto market — written for dealers and private buyers.
        </p>
      </div>
    </section>

    <section class="blog-page__content section" aria-label="Blog articles">
      <div class="container">
        <div class="blog-page__layout">

          <div class="blog-page__main">

            <?php if ($featured): ?>
            <article class="blog-featured card" data-blog-card data-category="<?= htmlspecialchars($featured['categoryKey']) ?>">
              <a href="<?= BASE_URL ?>/blog/<?= htmlspecialchars($featured['slug']) ?>" class="blog-featured__link">
                <div class="blog-featured__media">
                  <img
                    src="<?= htmlspecialchars(ImageUrl::resolve($featured['image'], 900)) ?>"
                    alt=""
                    width="900"
                    height="506"
                    loading="eager"
                  />
                  <span class="blog-featured__badge" data-i18n="blogPage.featured">Featured</span>
                </div>
                <div class="blog-featured__body">
                  <span class="blog-card__category"><?= htmlspecialchars($featured['category']) ?></span>
                  <h2 class="blog-featured__title"><?= htmlspecialchars($featured['title']) ?></h2>
                  <p class="blog-featured__meta">
                    <time datetime="<?= htmlspecialchars($featured['date']) ?>"><?= htmlspecialchars($featured['dateLabel']) ?></time>
                    <span aria-hidden="true">·</span>
                    <span><?= (int) $featured['readMin'] ?> <span data-i18n="blogPage.minRead">min read</span></span>
                  </p>
                  <p class="blog-featured__excerpt"><?= htmlspecialchars($featured['excerpt']) ?></p>
                  <span class="blog-card__cta section-link" data-i18n="blogPage.readArticle">Read article</span>
                </div>
              </a>
            </article>
            <?php endif; ?>

            <div class="blog-page__toolbar" style="justify-content: flex-end;">
              <p class="blog-page__count" data-blog-count aria-live="polite">
                <span data-blog-count-num><?= count($gridPosts) ?></span>
                <span data-i18n="blogPage.articles">articles</span>
              </p>
            </div>

            <div class="blog-cards blog-cards--page" data-blog-grid>
              <?php foreach ($gridPosts as $post): ?>
              <article class="blog-card" data-blog-card data-category="<?= htmlspecialchars($post['categoryKey']) ?>">
                <a href="<?= BASE_URL ?>/blog/<?= htmlspecialchars($post['slug']) ?>" class="blog-card__link">
                  <div class="blog-card__media">
                    <img
                      src="<?= htmlspecialchars(ImageUrl::resolve($post['image'], 600)) ?>"
                      alt=""
                      width="600"
                      height="360"
                      loading="lazy"
                    />
                    <span class="blog-card__category"><?= htmlspecialchars($post['category']) ?></span>
                  </div>
                  <div class="blog-card__body">
                    <h3 class="blog-card__title"><?= htmlspecialchars($post['title']) ?></h3>
                    <p class="blog-card__meta">
                      <time class="blog-card__date" datetime="<?= htmlspecialchars($post['date']) ?>"><?= htmlspecialchars($post['dateLabel']) ?></time>
                      <span aria-hidden="true">·</span>
                      <span><?= (int) $post['readMin'] ?> <span data-i18n="blogPage.minRead">min read</span></span>
                    </p>
                    <p class="blog-card__excerpt"><?= htmlspecialchars($post['excerpt']) ?></p>
                    <span class="blog-card__cta section-link" data-i18n="blogPage.readArticle">Read article</span>
                  </div>
                </a>
              </article>
              <?php endforeach; ?>
            </div>

            <p class="blog-page__empty" data-blog-empty hidden data-i18n="blogPage.noResults">No articles match your search. Try another category or keyword.</p>

          </div>

          <aside class="blog-page__sidebar" aria-label="Blog sidebar">

            <div class="blog-widget card">
              <h2 class="blog-widget__title" data-i18n="blogPage.search">Search articles</h2>
              <form class="blog-widget__search" role="search" data-blog-search-form>
                <label class="visually-hidden" for="blog-search-input" data-i18n="blogPage.searchLabel">Search blog</label>
                <input
                  id="blog-search-input"
                  class="blog-widget__input"
                  type="search"
                  name="q"
                  placeholder="Auction, export, guides…"
                  data-i18n-placeholder="blogPage.searchPlaceholder"
                  data-blog-search
                  autocomplete="off"
                />
                <button class="btn btn--primary blog-widget__submit" type="submit" data-i18n="search.btn">search</button>
              </form>
            </div>

            <div class="blog-widget card">
              <h2 class="blog-widget__title" data-i18n="blogPage.categories">Categories</h2>
              <ul class="blog-widget__cat-list">
                <?php foreach ($categories as $cat): ?>
                <li>
                  <button type="button" class="blog-widget__cat-btn<?= $cat['key'] === 'all' ? ' is-active' : '' ?>" data-category-filter="<?= htmlspecialchars($cat['key']) ?>">
                    <?= htmlspecialchars($cat['label']) ?>
                  </button>
                </li>
                <?php endforeach; ?>
              </ul>
            </div>

          </aside>

        </div>
      </div>
    </section>

  </main>

  <script src="<?= BASE_URL ?>/public/js/blog.js" defer></script>
<?php include __DIR__ . '/partials/footer.php'; ?>
