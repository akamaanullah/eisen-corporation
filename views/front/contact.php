<?php
$imgBase = 'https://images.unsplash.com/';
?>
<?php include __DIR__ . '/partials/header.php'; ?>

  <main id="main" class="contact-page">

    <section class="contact-hero" aria-labelledby="contact-page-title">
      <div class="contact-hero__media" aria-hidden="true">
        <img src="<?= htmlspecialchars($imgBase) ?>photo-1486406146926-c627a92ad1ab?w=1600&q=80" alt="" width="1600" height="900" fetchpriority="high" />
        <div class="contact-hero__overlay"></div>
      </div>
      <div class="container contact-hero__inner">
        <div class="contact-hero__content">
          <p class="contact-hero__eyebrow" data-i18n="contact.eyebrow">Get in touch</p>
          <h1 id="contact-page-title" class="contact-hero__title" data-i18n="contact.title">Contact Eisen Corporation</h1>
          <p class="contact-hero__lead" data-i18n="contact.lead">
            Questions about Japan auctions, export logistics, or dealer partnerships? Our team responds within one business day.
          </p>
        </div>
      </div>
    </section>

    <section class="contact-main section" aria-labelledby="contact-form-title">
      <div class="container">
        <div class="contact-layout">

          <div class="contact-form-wrap card">
            <div class="contact-form-head">
              <h2 id="contact-form-title" class="contact-form-head__title" data-i18n="contact.form.title">Send us a message</h2>
              <p class="contact-form-head__text" data-i18n="contact.form.text">Tell us what you are looking for and we will connect you with the right specialist.</p>
            </div>

            <form class="contact-form" data-contact-form novalidate>
              <div class="form-row">
                <div class="form-field">
                  <label class="form-label" for="contact-name" data-i18n="contact.form.name">Full name</label>
                  <input class="form-control form-control--text" type="text" id="contact-name" name="name" autocomplete="name" required />
                </div>
                <div class="form-field">
                  <label class="form-label" for="contact-email" data-i18n="contact.form.email">Email address</label>
                  <input class="form-control form-control--text" type="email" id="contact-email" name="email" autocomplete="email" required />
                </div>
              </div>

              <div class="form-row">
                <div class="form-field">
                  <label class="form-label" for="contact-phone" data-i18n="contact.form.phone">Phone (optional)</label>
                  <input class="form-control form-control--text" type="tel" id="contact-phone" name="phone" autocomplete="tel" />
                </div>
                <div class="form-field">
                  <label class="form-label" for="contact-subject" data-i18n="contact.form.subject">Subject</label>
                  <select class="form-control" id="contact-subject" name="subject" required>
                    <?php foreach ($subjects as $subject): ?>
                    <option value="<?= htmlspecialchars($subject['value']) ?>" data-i18n="<?= htmlspecialchars($subject['key']) ?>"><?= htmlspecialchars($subject['label']) ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
              </div>

              <div class="form-field">
                <label class="form-label" for="contact-message" data-i18n="contact.form.message">Message</label>
                <textarea class="form-control form-control--textarea" id="contact-message" name="message" rows="6" required data-i18n-placeholder="contact.form.messagePlaceholder" placeholder="Describe the vehicles, destination port, or support you need…"></textarea>
              </div>

              <button class="btn btn--primary btn--block contact-form__submit" type="submit" data-i18n="contact.form.submit">Send message</button>

              <p class="contact-form__note" data-i18n="contact.form.note">We typically reply within 24 hours on business days (JST).</p>

              <div class="contact-form__success" data-contact-success hidden>
                <p class="contact-form__success-title" data-i18n="contact.form.successTitle">Message sent</p>
                <p class="contact-form__success-text" data-i18n="contact.form.successText">Thank you — our export team will get back to you shortly.</p>
              </div>
            </form>
          </div>

          <aside class="contact-aside" aria-label="Contact information">
            <div class="contact-info-card card">
              <h3 class="contact-info-card__title" data-i18n="contact.office.title">Head office</h3>
              <address class="contact-info-card__address">
                <strong>Eisen Inc.</strong><br />
                <span data-i18n="contact.office.line1">3-22-32 Tanaka, Matsubushi Machi</span><br />
                <span data-i18n="contact.office.line2">Kitakatsushika Gun, Saitama Prefecture 343-0117</span>
              </address>
            </div>

            <div class="contact-info-card card">
              <h3 class="contact-info-card__title" data-i18n="contact.hours.title">Business hours</h3>
              <ul class="contact-info-card__list">
                <li><span data-i18n="contact.hours.weekdays">Mon – Fri</span><span>09:00 – 18:00 JST</span></li>
                <li><span data-i18n="contact.hours.saturday">Saturday</span><span>09:00 – 13:00 JST</span></li>
                <li><span data-i18n="contact.hours.sunday">Sunday &amp; holidays</span><span data-i18n="contact.hours.closed">Closed</span></li>
              </ul>
            </div>

            <?php foreach ($channels as $channel): ?>
            <div class="contact-info-card card">
              <h3 class="contact-info-card__title" data-i18n="<?= htmlspecialchars($channel['titleKey']) ?>"><?= htmlspecialchars($channel['title']) ?></h3>
              <p class="contact-info-card__text" data-i18n="<?= htmlspecialchars($channel['textKey']) ?>"><?= htmlspecialchars($channel['text']) ?></p>
              <p class="contact-info-card__links">
                <a href="mailto:<?= htmlspecialchars($channel['email']) ?>"><?= htmlspecialchars($channel['email']) ?></a><br />
                <a href="tel:<?= htmlspecialchars(preg_replace('/\s+/', '', $channel['phone'])) ?>"><?= htmlspecialchars($channel['phone']) ?></a>
              </p>
            </div>
            <?php endforeach; ?>
          </aside>

        </div>
      </div>
    </section>

  </main>

  <script src="<?= BASE_URL ?>/public/js/contact.js" defer></script>
<?php include __DIR__ . '/partials/footer.php'; ?>
