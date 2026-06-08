<?php
use App\Helpers\ImageUrl;

$postUrl = BASE_URL . '/blog/' . $post['slug'];
$heroImg = ImageUrl::resolve($post['image'], 1200);
?>
<?php include __DIR__ . '/partials/header.php'; ?>

  <main id="main" class="blog-detail-page">

    <section class="blog-detail-hero" aria-labelledby="blog-detail-title">
      <div class="blog-detail-hero__media">
        <img
          src="<?= htmlspecialchars($heroImg) ?>"
          alt=""
          width="1200"
          height="675"
          fetchpriority="high"
        />
        <div class="blog-detail-hero__overlay" aria-hidden="true"></div>
      </div>
      <div class="blog-detail-hero__content">
        <div class="blog-detail-hero__inner">
          <nav class="blog-detail-breadcrumb" aria-label="Breadcrumb">
            <ol class="blog-detail-breadcrumb__list">
              <li><a href="<?= BASE_URL ?>/" data-i18n="nav.home">Home</a></li>
              <li><a href="<?= BASE_URL ?>/blogs" data-i18n="nav.blog">Blog</a></li>
              <li aria-current="page"><?= htmlspecialchars($post['title']) ?></li>
            </ol>
          </nav>
          <span class="blog-detail-hero__category"><?= htmlspecialchars($post['category']) ?></span>
          <h1 id="blog-detail-title" class="blog-detail-hero__title"><?= htmlspecialchars($post['title']) ?></h1>
          <p class="blog-detail-hero__meta">
            <time datetime="<?= htmlspecialchars($post['date']) ?>"><?= htmlspecialchars($post['dateLabel']) ?></time>
            <span aria-hidden="true">·</span>
            <span><?= (int) $post['readMin'] ?> <span data-i18n="blogPage.minRead">min read</span></span>
            <span aria-hidden="true">·</span>
            <span><?= htmlspecialchars($post['author']) ?></span>
          </p>
        </div>
      </div>
    </section>

    <section class="blog-detail-body section" aria-label="Article content">
      <div class="container">
        <div class="blog-detail-layout">

          <article class="blog-detail-article">
            <div class="blog-detail-article__content">
              <?php foreach ($post['body'] as $block): ?>
                <?php if ($block['type'] === 'raw_html'): ?>
                  <?= $block['html'] ?>
                <?php elseif ($block['type'] === 'p'): ?>
                  <p><?= htmlspecialchars($block['text']) ?></p>
                <?php elseif ($block['type'] === 'h2'): ?>
                  <h2><?= htmlspecialchars($block['text']) ?></h2>
                <?php elseif ($block['type'] === 'ul'): ?>
                  <ul>
                    <?php foreach ($block['items'] as $item): ?>
                      <li><?= htmlspecialchars($item) ?></li>
                    <?php endforeach; ?>
                  </ul>
                <?php elseif ($block['type'] === 'tip'): ?>
                  <aside class="blog-detail-tip">
                    <p class="blog-detail-tip__label"><?= htmlspecialchars($block['title']) ?></p>
                    <p class="blog-detail-tip__text"><?= htmlspecialchars($block['text']) ?></p>
                  </aside>
                <?php endif; ?>
              <?php endforeach; ?>
            </div>

            <footer class="blog-detail-article__footer">
              <a class="section-link" href="<?= BASE_URL ?>/blogs" data-i18n="blogDetail.back">Back to all articles</a>
              <a class="btn btn--primary" href="<?= BASE_URL ?>/listing" data-i18n="blogPage.browseInventory">Browse vehicle inventory</a>
            </footer>
          </article>

          <aside class="blog-detail-sidebar" aria-label="Article sidebar">
            <div class="blog-widget card blog-detail-author">
              <div class="blog-detail-author__body">
                <h2 class="blog-widget__title" data-i18n="blogDetail.aboutAuthor">About the author</h2>
                <p class="blog-detail-author__name"><?= htmlspecialchars($post['author']) ?></p>
                <p class="blog-detail-author__bio" data-i18n="blogDetail.authorBio">
                  Eisen's export specialists help dealers and private buyers source, inspect, and ship vehicles from Japan auctions worldwide.
                </p>
              </div>
            </div>

            <?php if (!empty($related)): ?>
            <div class="blog-widget card blog-detail-related-widget">
              <h2 class="blog-widget__title" data-i18n="blogDetail.related">Related articles</h2>
              <ul class="blog-detail-related">
                <?php foreach ($related as $rel): ?>
                <li>
                  <a class="blog-detail-related__link" href="<?= BASE_URL ?>/blog/<?= htmlspecialchars($rel['slug']) ?>">
                    <div class="blog-detail-related__media">
                      <img
                        src="<?= htmlspecialchars(ImageUrl::resolve($rel['image'], 400)) ?>"
                        alt=""
                        width="400"
                        height="240"
                        loading="lazy"
                      />
                    </div>
                    <div class="blog-detail-related__text">
                      <span class="blog-detail-related__title"><?= htmlspecialchars($rel['title']) ?></span>
                      <time class="blog-detail-related__date" datetime="<?= htmlspecialchars($rel['date']) ?>"><?= htmlspecialchars($rel['dateLabel']) ?></time>
                    </div>
                  </a>
                </li>
                <?php endforeach; ?>
              </ul>
            </div>
            <?php endif; ?>
          </aside>

        </div>
      </div>
    </section>

    <?php if (!empty($related)): ?>
    <section class="blog-detail-recs section" aria-labelledby="blog-detail-recs-title">
      <div class="container">
        <header class="section-header">
          <h2 id="blog-detail-recs-title" class="section-title" data-i18n="blogDetail.moreArticles">More from the blog</h2>
          <a class="section-link" href="<?= BASE_URL ?>/blogs" data-i18n="blog.viewAll">View all blog</a>
        </header>
        <div class="blog-cards blog-cards--page">
          <?php foreach ($related as $rel): ?>
          <article class="blog-card">
            <a href="<?= BASE_URL ?>/blog/<?= htmlspecialchars($rel['slug']) ?>" class="blog-card__link">
              <div class="blog-card__media">
                <img
                  src="<?= htmlspecialchars(ImageUrl::resolve($rel['image'], 600)) ?>"
                  alt=""
                  width="600"
                  height="360"
                  loading="lazy"
                />
                <span class="blog-card__category"><?= htmlspecialchars($rel['category']) ?></span>
              </div>
              <div class="blog-card__body">
                <h3 class="blog-card__title"><?= htmlspecialchars($rel['title']) ?></h3>
                <p class="blog-card__meta">
                  <time class="blog-card__date" datetime="<?= htmlspecialchars($rel['date']) ?>"><?= htmlspecialchars($rel['dateLabel']) ?></time>
                  <span aria-hidden="true">·</span>
                  <span><?= (int) $rel['readMin'] ?> <span data-i18n="blogPage.minRead">min read</span></span>
                </p>
                <p class="blog-card__excerpt"><?= htmlspecialchars($rel['excerpt']) ?></p>
                <span class="blog-card__cta section-link" data-i18n="blogPage.readArticle">Read article</span>
              </div>
            </a>
          </article>
          <?php endforeach; ?>
        </div>
      </div>
    </section>
    <?php endif; ?>

  </main>

<?php include __DIR__ . '/partials/footer.php'; ?>
