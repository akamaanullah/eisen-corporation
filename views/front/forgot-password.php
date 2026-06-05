<?php include dirname(__DIR__) . '/front/partials/header.php'; ?>

  <main id="main" class="login-page">

    <section class="login-page__main section" aria-labelledby="forgot-page-title">
      <div class="container login-page__inner">
        <div class="login-card card">
          <div class="login-card__head">
            <h1 id="forgot-page-title" class="login-card__title" data-i18n="auth.forgot.title">Forgot password</h1>
            <p class="login-card__text" data-i18n="auth.forgot.desc">Enter your email and we will send you a reset link.</p>
          </div>

          <?php if (isset($flash) && $flash): ?>
            <div class="login-flash login-flash--<?= htmlspecialchars($flash['type']) ?>" role="alert">
              <?= htmlspecialchars($flash['message']) ?>
            </div>
          <?php endif; ?>

          <form class="login-form" action="<?= BASE_URL ?>/forgot-password" method="POST" novalidate>
            <?= $this->csrf_field() ?>
            <div class="form-field">
              <label class="form-label" for="forgot-email" data-i18n="auth.email">Email</label>
              <input
                class="form-control form-control--text"
                type="email"
                id="forgot-email"
                name="email"
                placeholder="you@example.com"
                required
                autocomplete="email"
              />
            </div>

            <button class="btn btn--primary btn--block login-form__submit" type="submit" data-i18n="auth.forgot.submit">Send reset link</button>
          </form>

          <p class="login-back">
            <a href="<?= BASE_URL ?>/login" data-i18n="auth.forgot.back">Back to login</a>
          </p>
        </div>
      </div>
    </section>

  </main>

<?php include dirname(__DIR__) . '/front/partials/footer.php'; ?>
