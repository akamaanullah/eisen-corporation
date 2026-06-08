
<footer id="contacts" class="site-footer">
    <div class="container site-footer__grid">
      <div class="site-footer__col site-footer__brand">
        <a href="<?= BASE_URL ?>/" class="site-footer__logo" aria-label="Eisen Corporation home">
          <img
            class="site-footer__logo-img"
            src="<?= BASE_URL ?>/public/image/eisen-logo.png"
            alt="Eisen Corporation"
            width="200"
            height="58"
            loading="lazy"
          />
        </a>
        <p class="site-footer__tagline" data-i18n="footer.tagline">
          Premium imported vehicles from Japan auctions. Inspection, logistics, and worldwide export for dealers and private buyers.
        </p>
      </div>

      <div class="site-footer__col">
        <h3 class="site-footer__heading" data-i18n="footer.quickLinks">Quick Links</h3>
        <ul class="site-footer__links site-footer__links--arrow-hover">
          <li><a href="<?= BASE_URL ?>/" data-i18n="nav.home">Home</a></li>
          <li><a href="<?= BASE_URL ?>/about" data-i18n="nav.about">About Us</a></li>
          <li><a href="<?= BASE_URL ?>/why-choose-eisen" data-i18n="whyChoose.title">Why Choose Eisen</a></li>
          <li><a href="<?= BASE_URL ?>/account-guide" data-i18n="accountGuide.title">How to create your account</a></li>
          <li><a href="<?= BASE_URL ?>/blogs" data-i18n="nav.blog">Blog</a></li>
        </ul>
      </div>

      <div class="site-footer__col">
        <h3 class="site-footer__heading" data-i18n="footer.services">Our Services</h3>
        <ul class="site-footer__links site-footer__links--arrow-hover">
          <li><a href="<?= BASE_URL ?>/listing" data-i18n="nav.sellers">Available Stock</a></li>
          <li><a href="<?= BASE_URL ?>/price-calculation" data-i18n="priceCalc.title">Price Calculation</a></li>
          <li><a href="<?= BASE_URL ?>/faq" data-i18n="nav.faq">FAQ</a></li>
          <li><a href="<?= BASE_URL ?>/contact" data-i18n="footer.urgent">Urgent Purchase</a></li>
        </ul>
      </div>

      <div class="site-footer__col">
        <h3 class="site-footer__heading" data-i18n="footer.contact">Contact Us</h3>
        <p class="site-footer__text">
          <strong>Eisen Inc.</strong><br />
          3-22-32 Tanaka, Matsubushi Machi<br />
          Kitakatsushika Gun, Saitama Prefecture 343-0117
        </p>
        <p class="site-footer__text">
          <a href="mailto:sales@eisenwheels.com">sales@eisenwheels.com</a><br />
          <a href="tel:09033508523">090 3350 8523</a>
        </p>
        <p class="site-footer__text">
          <strong data-i18n="footer.hoursTitle">Business hours</strong><br />
          <span data-i18n="footer.hoursLine1">Mon – Fri: 09:00 – 18:00 (JST)</span><br />
          <span data-i18n="footer.hoursLine2">Sat: 09:00 – 13:00 (JST)</span>
        </p>
      </div>
    </div>
    <div class="site-footer__bottom">
      <div class="container site-footer__bottom-inner">
        <p class="site-footer__copy">&copy; <span data-year></span> <span data-i18n="footer.copy">Eisen Corporation. All rights reserved.</span></p>
        <nav class="site-footer__legal" aria-label="Legal">
          <a href="<?= BASE_URL ?>/privacy-policy" data-i18n="footer.privacy">Privacy Policy</a>
          <span class="site-footer__legal-sep" aria-hidden="true">|</span>
          <a href="<?= BASE_URL ?>/terms-and-condition" data-i18n="footer.terms">Terms &amp; Conditions</a>
        </nav>
      </div>
    </div>
  </footer>

  <script>window.BASE_URL = <?= json_encode(BASE_URL) ?>;</script>
  <script src="<?= BASE_URL ?>/public/js/locale-i18n.js" defer></script>
  <script src="<?= BASE_URL ?>/public/js/currency.js" defer></script>
  <script src="<?= BASE_URL ?>/public/js/main.js" defer></script>
  <script src="<?= BASE_URL ?>/public/js/listing.js?v=1.4" defer></script>
<?php if (!empty($loadProductScript)): ?>
  <script src="<?= BASE_URL ?>/public/js/product.js?v=1.5" defer></script>
<?php endif; ?>
<?php if (!empty($loadAccountScripts)): ?>
  <?php if (!empty($loadAccountPaymentPicker)): ?>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/litepicker/dist/css/litepicker.css" />
  <script src="https://cdn.jsdelivr.net/npm/litepicker/dist/litepicker.js" defer></script>
  <?php endif; ?>
  <script src="<?= BASE_URL ?>/public/js/account.js?v=1.4" defer></script>
<?php endif; ?>
</body>
</html>
