<?php
$navRequestPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$navBasePath = parse_url(BASE_URL, PHP_URL_PATH) ?: '';
if ($navBasePath !== '' && $navBasePath !== '/' && str_starts_with($navRequestPath, $navBasePath)) {
    $navRequestPath = substr($navRequestPath, strlen($navBasePath)) ?: '/';
}
$navRequestPath = '/' . trim($navRequestPath, '/');
if ($navRequestPath !== '/') {
    $navRequestPath = rtrim($navRequestPath, '/') ?: '/';
}

if (!function_exists('eisen_nav_is_active')) {
    function eisen_nav_is_active(string $key, string $path): bool
    {
        switch ($key) {
            case 'home':
                return $path === '/';
            case 'about':
                return str_starts_with($path, '/about');
            case 'why-choose':
                return str_starts_with($path, '/why-choose-eisen');
            case 'account-guide':
                return str_starts_with($path, '/account-guide');
            case 'blog':
                return str_starts_with($path, '/blog');
            case 'listing':
                return str_starts_with($path, '/listing')
                    || str_starts_with($path, '/listings')
                    || str_starts_with($path, '/product');
            case 'contact':
                return str_starts_with($path, '/contact');
            case 'chassis':
                return str_starts_with($path, '/chassis-check');
            case 'faq':
                return str_starts_with($path, '/faq');
            case 'price-calc':
                return str_starts_with($path, '/price-calculation');
            default:
                return false;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="description" content="Eisen Corporation — premium imported vehicles, auction-grade stock, and expert export services." />
  <title>Eisen Corporation | Premium Auto Dealership</title>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Inter:ital,wght@0,400;0,500;0,600;1,400;1,500;1,600&family=Montserrat:ital,wght@0,600;0,700;0,800;1,600;1,700;1,800&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="<?= BASE_URL ?>/public/css/global.css?v=1.7" />
  <meta name="csrf-token" content="<?= \App\Core\Session::getCsrfToken() ?>" />
  <script>
    window.EisenBaseUrl = <?= json_encode(BASE_URL) ?>;
  </script>
</head>
<body>
  <a class="skip-link" href="#main" data-i18n="skip">Skip to content</a>

  <header class="site-header">
    <div class="header-brand">
      <div class="container header-brand__inner">
        <a href="<?= BASE_URL ?>/" class="logo notranslate" aria-label="Eisen Corporation home">
          <img
            class="logo__img"
            src="<?= BASE_URL ?>/public/image/eisen-logo.png"
            alt="Eisen Corporation"
            width="220"
            height="64"
            fetchpriority="high"
          />
        </a>

        <div class="header-brand__actions">
        <div class="header-locale notranslate" data-header-locale>
          <div class="header-locale__dropdown" data-locale-dropdown="language">
            <label class="visually-hidden" id="header-language-label" data-i18n="locale.language">Language</label>
            <button
              type="button"
              class="header-locale__trigger"
              id="header-language-btn"
              data-locale-trigger
              aria-expanded="false"
              aria-haspopup="listbox"
              aria-labelledby="header-language-label"
            >
              <span class="header-locale__trigger-icon" data-locale-trigger-icon aria-hidden="true">
                <svg class="header-locale__flag-svg" width="22" height="15" viewBox="0 0 21 15" xmlns="http://www.w3.org/2000/svg"><rect width="21" height="15" fill="#fff" /><path fill="#B22234" d="M0 0h21v1.154H0Zm0 2.308h21v1.154H0Zm0 2.308h21v1.154H0Zm0 2.308h21v1.154H0Zm0 2.308h21v1.154H0Zm0 2.308h21v1.154H0Zm0 2.308h21H0Z" /><rect width="8.4" height="8.05" fill="#3C3B6E" /></svg>
              </span>
              <span class="header-locale__trigger-label" data-locale-trigger-label>English</span>
              <svg class="header-locale__chevron" width="10" height="6" viewBox="0 0 10 6" fill="none" aria-hidden="true"><path d="M1 1L5 5L9 1" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" /></svg>
            </button>
            <ul class="header-locale__menu" role="listbox" data-locale-menu hidden>
              <li role="presentation">
                <button type="button" class="header-locale__option is-active" role="option" data-locale-value="en" aria-selected="true">
                  <span class="header-locale__option-icon" aria-hidden="true">
                    <svg class="header-locale__flag-svg" width="22" height="15" viewBox="0 0 21 15" xmlns="http://www.w3.org/2000/svg"><rect width="21" height="15" fill="#fff" /><path fill="#B22234" d="M0 0h21v1.154H0Zm0 2.308h21v1.154H0Zm0 2.308h21v1.154H0Zm0 2.308h21v1.154H0Zm0 2.308h21v1.154H0Zm0 2.308h21v1.154H0Zm0 2.308h21H0Z" /><rect width="8.4" height="8.05" fill="#3C3B6E" /></svg>
                  </span>
                  <span class="header-locale__option-label">English</span>
                </button>
              </li>
              <li role="presentation">
                <button type="button" class="header-locale__option" role="option" data-locale-value="ja" aria-selected="false">
                  <span class="header-locale__option-icon" aria-hidden="true">
                    <svg class="header-locale__flag-svg" width="22" height="15" viewBox="0 0 21 15" xmlns="http://www.w3.org/2000/svg"><rect width="21" height="15" fill="#fff" /><circle cx="10.5" cy="7.5" r="4.2" fill="#BC002D" /></svg>
                  </span>
                  <span class="header-locale__option-label">Japanese</span>
                </button>
              </li>
            </ul>
            <select class="visually-hidden" id="header-language" name="language" data-locale-language tabindex="-1" aria-hidden="true">
              <option value="en" selected>English</option>
              <option value="ja">Japanese</option>
            </select>
          </div>

          <span class="header-locale__divider" aria-hidden="true"></span>

          <div class="header-locale__dropdown" data-locale-dropdown="currency">
            <label class="visually-hidden" id="header-currency-label" data-i18n="locale.currency">Currency</label>
            <button
              type="button"
              class="header-locale__trigger"
              id="header-currency-btn"
              data-locale-trigger
              aria-expanded="false"
              aria-haspopup="listbox"
              aria-labelledby="header-currency-label"
            >
              <span class="header-locale__trigger-icon header-locale__trigger-icon--symbol" data-locale-trigger-icon aria-hidden="true">$</span>
              <span class="header-locale__trigger-label" data-locale-trigger-label>USD</span>
              <svg class="header-locale__chevron" width="10" height="6" viewBox="0 0 10 6" fill="none" aria-hidden="true"><path d="M1 1L5 5L9 1" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" /></svg>
            </button>
            <ul class="header-locale__menu" role="listbox" data-locale-menu hidden>
              <li role="presentation">
                <button type="button" class="header-locale__option is-active" role="option" data-locale-value="usd" aria-selected="true">
                  <span class="header-locale__option-icon header-locale__option-icon--symbol" aria-hidden="true">$</span>
                  <span class="header-locale__option-label">USD</span>
                </button>
              </li>
              <li role="presentation">
                <button type="button" class="header-locale__option" role="option" data-locale-value="jpy" aria-selected="false">
                  <span class="header-locale__option-icon header-locale__option-icon--symbol" aria-hidden="true">¥</span>
                  <span class="header-locale__option-label">JPY</span>
                </button>
              </li>
            </ul>
            <select class="visually-hidden" id="header-currency" name="currency" data-locale-currency tabindex="-1" aria-hidden="true">
              <option value="usd" selected>USD</option>
              <option value="jpy">JPY</option>
            </select>
          </div>
        </div>

        <?php if (\App\Core\Session::get('is_logged_in') === true): ?>
            <?php if (\App\Core\Session::get('user_role') === 'admin'): ?>
                <a class="header-login header-login--desktop" href="<?= BASE_URL ?>/admin" aria-label="Go to Admin Dashboard">
                  <svg class="header-login__icon" width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" />
                  </svg>
                  <span class="header-login__label">Admin Panel</span>
                </a>
                <a class="header-login header-login--desktop" href="<?= BASE_URL ?>/admin/logout" style="margin-left: 15px;" aria-label="Logout">
                  <span class="header-login__label">Logout</span>
                </a>
            <?php else: ?>
                <a class="header-login header-login--desktop header-login--user" href="<?= BASE_URL ?>/account" aria-label="<?= htmlspecialchars(\App\Core\Session::getUserFirstName()) ?> account">
                  <svg class="header-login__icon" width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <circle cx="12" cy="8" r="4" stroke="currentColor" stroke-width="1.75" />
                    <path d="M5 20c0-3.314 3.134-6 7-6s7 2.686 7 6" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" />
                  </svg>
                  <span class="header-login__label" style="font-weight: 600; color: #c9a227;"><?= htmlspecialchars(\App\Core\Session::getUserFirstName()) ?></span>
                </a>
                <a class="header-login header-login--desktop" href="<?= BASE_URL ?>/logout" style="margin-left: 15px;" aria-label="Logout">
                  <span class="header-login__label">Logout</span>
                </a>
            <?php endif; ?>
        <?php else: ?>
            <a
              class="header-login header-login--desktop"
              href="<?= BASE_URL ?>/login"
              data-i18n-aria="nav.loginAria"
              aria-label="Log in to your account"
            >
              <svg class="header-login__icon" width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <circle cx="12" cy="8" r="4" stroke="currentColor" stroke-width="1.75" />
                <path d="M5 20c0-3.314 3.134-6 7-6s7 2.686 7 6" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" />
              </svg>
              <span class="header-login__label" data-i18n="nav.login">Login</span>
            </a>
        <?php endif; ?>
        </div>
      </div>
    </div>

    <div class="header-bar">
      <div class="container header-bar__inner">
        <button class="nav-toggle" type="button" aria-expanded="false" aria-controls="site-nav">
          <span class="nav-toggle__bar" aria-hidden="true"></span>
          <span class="visually-hidden" data-i18n="nav.menu">Menu</span>
        </button>

        <nav id="site-nav" class="site-nav" aria-label="Primary">
          <div class="site-nav__drawer-head">
            <a href="<?= BASE_URL ?>/" class="site-nav__drawer-logo" aria-label="Eisen Corporation home">
              <img
                class="site-nav__drawer-logo-img"
                src="<?= BASE_URL ?>/public/image/eisen-logo.png"
                alt="Eisen Corporation"
                width="220"
                height="64"
              />
            </a>
            <button type="button" class="site-nav__close" data-nav-close aria-label="Close menu">
              <svg width="18" height="18" viewBox="0 0 18 18" fill="none" aria-hidden="true">
                <path d="M2 2L16 16M16 2L2 16" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
              </svg>
            </button>
          </div>
          <ul class="site-nav__list">
            <li class="site-nav__item"><a class="site-nav__link<?= eisen_nav_is_active('home', $navRequestPath) ? ' is-active' : '' ?>" href="<?= BASE_URL ?>/" data-nav-key="home"<?= eisen_nav_is_active('home', $navRequestPath) ? ' aria-current="page"' : '' ?> data-i18n="nav.home">Home</a></li>
            <li class="site-nav__item"><a class="site-nav__link<?= eisen_nav_is_active('listing', $navRequestPath) ? ' is-active' : '' ?>" href="<?= BASE_URL ?>/listing" data-nav-key="listing"<?= eisen_nav_is_active('listing', $navRequestPath) ? ' aria-current="page"' : '' ?> data-i18n="nav.sellers">Available Stock</a></li>
            <!-- <li class="site-nav__item"><a class="site-nav__link<?= eisen_nav_is_active('chassis', $navRequestPath) ? ' is-active' : '' ?>" href="<?= BASE_URL ?>/chassis-check" data-nav-key="chassis"<?= eisen_nav_is_active('chassis', $navRequestPath) ? ' aria-current="page"' : '' ?> data-i18n="chassis.title">Chassis Check</a></li> -->
            <li class="site-nav__item"><a class="site-nav__link<?= eisen_nav_is_active('price-calc', $navRequestPath) ? ' is-active' : '' ?>" href="<?= BASE_URL ?>/price-calculation" data-nav-key="price-calc"<?= eisen_nav_is_active('price-calc', $navRequestPath) ? ' aria-current="page"' : '' ?> data-i18n="priceCalc.title">Price Calculation</a></li>
            <li class="site-nav__item"><a class="site-nav__link<?= eisen_nav_is_active('about', $navRequestPath) ? ' is-active' : '' ?>" href="<?= BASE_URL ?>/about" data-nav-key="about"<?= eisen_nav_is_active('about', $navRequestPath) ? ' aria-current="page"' : '' ?> data-i18n="nav.about">About Us</a></li>
            <li class="site-nav__item"><a class="site-nav__link<?= eisen_nav_is_active('why-choose', $navRequestPath) ? ' is-active' : '' ?>" href="<?= BASE_URL ?>/why-choose-eisen" data-nav-key="why-choose"<?= eisen_nav_is_active('why-choose', $navRequestPath) ? ' aria-current="page"' : '' ?> data-i18n="whyChoose.title">Why Choose Eisen</a></li>
            <li class="site-nav__item"><a class="site-nav__link<?= eisen_nav_is_active('contact', $navRequestPath) ? ' is-active' : '' ?>" href="<?= BASE_URL ?>/contact" data-nav-key="contact"<?= eisen_nav_is_active('contact', $navRequestPath) ? ' aria-current="page"' : '' ?> data-i18n="nav.contacts">Contacts</a></li>
          </ul>
        </nav>

        <form class="header-search" role="search" data-i18n-aria="search.aria">
          <label class="visually-hidden" for="header-search-input" data-i18n="search.label">Search on site</label>
          <input
            id="header-search-input"
            class="header-search__input"
            type="search"
            name="q"
            placeholder="Search On Site"
            data-i18n-placeholder="search.placeholder"
            autocomplete="off"
          />
          <button class="btn btn--primary header-search__btn" type="submit" data-i18n="search.btn">search</button>
        </form>

        <?php if (\App\Core\Session::get('is_logged_in') === true): ?>
            <?php if (\App\Core\Session::get('user_role') === 'admin'): ?>
                <a class="header-login header-login--mobile" href="<?= BASE_URL ?>/admin" aria-label="Go to Admin Dashboard">
                  <svg class="header-login__icon" width="20" height="20" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" />
                  </svg>
                </a>
            <?php else: ?>
                <a class="header-login header-login--mobile header-login--user" href="<?= BASE_URL ?>/account" aria-label="<?= htmlspecialchars(\App\Core\Session::getUserFirstName()) ?> account">
                  <svg class="header-login__icon" width="20" height="20" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <circle cx="12" cy="8" r="4" stroke="currentColor" stroke-width="1.75" />
                    <path d="M5 20c0-3.314 3.134-6 7-6s7 2.686 7 6" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" />
                  </svg>
                  <span class="header-login__label header-login__label--mobile"><?= htmlspecialchars(\App\Core\Session::getUserFirstName()) ?></span>
                </a>
            <?php endif; ?>
        <?php else: ?>
            <a
              class="header-login header-login--mobile"
              href="<?= BASE_URL ?>/login"
              data-i18n-aria="nav.loginAria"
              aria-label="Log in to your account"
            >
              <svg class="header-login__icon" width="20" height="20" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <circle cx="12" cy="8" r="4" stroke="currentColor" stroke-width="1.75" />
                <path d="M5 20c0-3.314 3.134-6 7-6s7 2.686 7 6" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" />
              </svg>
              <span class="visually-hidden" data-i18n="nav.login">Login</span>
            </a>
        <?php endif; ?>
      </div>
    </div>
    <div class="site-nav-backdrop" data-nav-backdrop hidden aria-hidden="true"></div>
  </header>
