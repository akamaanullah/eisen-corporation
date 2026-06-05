<?php
// This view uses the front/partials/header.php from the storefront (shared login page styling)
include dirname(__DIR__) . '/front/partials/header.php';
?>

  <main id="main" class="login-page">

    <section class="login-page__main section" aria-labelledby="reset-page-title">
      <div class="container login-page__inner">
        <div class="login-card card">
          <div class="login-card__head">
            <h1 id="reset-page-title" class="login-card__title">Reset password</h1>
            <p class="login-card__text">Enter your new password below.</p>
          </div>

          <?php if (isset($flash) && $flash): ?>
            <div class="login-flash login-flash--<?= htmlspecialchars($flash['type']) ?>" role="alert">
              <?= htmlspecialchars($flash['message']) ?>
            </div>
          <?php endif; ?>

          <form class="login-form" action="<?= BASE_URL ?>/admin/reset-password" method="POST" novalidate>
            <input type="hidden" name="csrf_token" value="<?= \App\Core\Session::getCsrfToken() ?>">
            <input type="hidden" name="token" value="<?= htmlspecialchars($token ?? '') ?>">

            <div class="form-field">
              <label class="form-label" for="reset-password">New Password</label>
              <input
                class="form-control form-control--text"
                type="password"
                id="reset-password"
                name="password"
                placeholder="At least 8 characters"
                required
                minlength="8"
                autocomplete="new-password"
              />
            </div>

            <div class="form-field">
              <label class="form-label" for="reset-confirm">Confirm New Password</label>
              <input
                class="form-control form-control--text"
                type="password"
                id="reset-confirm"
                name="confirm_password"
                placeholder="Repeat your password"
                required
                minlength="8"
                autocomplete="new-password"
              />
            </div>

            <button class="btn btn--primary btn--block login-form__submit" type="submit">Set new password</button>
          </form>

          <p class="login-back">
            <a href="<?= BASE_URL ?>/admin/login">Back to login</a>
          </p>
        </div>
      </div>
    </section>

  </main>

<?php include dirname(__DIR__) . '/front/partials/footer.php'; ?>
