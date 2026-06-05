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

          <form class="login-form" id="forgot-password-form" action="<?= BASE_URL ?>/forgot-password" method="POST" novalidate>
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

            <button class="btn btn--primary btn--block login-form__submit" id="submit-btn" type="submit" data-i18n="auth.forgot.submit">Send reset link</button>
          </form>

          <p class="login-back">
            <a href="<?= BASE_URL ?>/login" data-i18n="auth.forgot.back">Back to login</a>
          </p>
        </div>
      </div>
    </section>

  </main>

  <style>
    /* Premium Alert Styling */
    .login-flash {
      padding: 12px 16px;
      border-radius: 6px;
      margin-bottom: 20px;
      font-size: 14px;
      line-height: 1.5;
    }
    .login-flash--success {
      background-color: #f0fff4;
      border: 1px solid #c6f6d5;
      color: #22543d;
    }
    .login-flash--error {
      background-color: #fff5f5;
      border: 1px solid #fed7d7;
      color: #742a2a;
    }

    /* Premium Button Loading Spinner */
    .btn-loading {
      position: relative;
      color: transparent !important;
      pointer-events: none;
      cursor: not-allowed;
    }
    .btn-loading::after {
      content: "";
      position: absolute;
      width: 20px;
      height: 20px;
      top: 50%;
      left: 50%;
      margin-top: -10px;
      margin-left: -10px;
      border: 3px solid rgba(255, 255, 255, 0.3);
      border-radius: 50%;
      border-top-color: #ffffff;
      animation: spin 0.8s ease-in-out infinite;
    }
    @keyframes spin {
      to { transform: rotate(360deg); }
    }
  </style>

  <script>
    document.addEventListener("DOMContentLoaded", function () {
      const form = document.getElementById("forgot-password-form");
      const submitBtn = document.getElementById("submit-btn");
      const emailInput = document.getElementById("forgot-email");

      if (form && submitBtn && emailInput) {
        form.addEventListener("submit", function (e) {
          // Native pattern check or trim check
          const emailVal = emailInput.value.trim();
          if (!emailVal || !emailInput.checkValidity()) {
            // Let HTML5 validator show browser-native tooltip
            return;
          }

          // Disable and show premium spinner
          submitBtn.disabled = true;
          submitBtn.classList.add("btn-loading");
        });
      }
    });
  </script>

<?php include dirname(__DIR__) . '/front/partials/footer.php'; ?>
