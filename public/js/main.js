(function () {
  "use strict";

  const yearEl = document.querySelector("[data-year]");
  if (yearEl) yearEl.textContent = String(new Date().getFullYear());

  /* Mobile navigation */
  const navToggle = document.querySelector(".nav-toggle");
  const siteNav = document.querySelector(".site-nav");
  const navBackdrop = document.querySelector("[data-nav-backdrop]");

  function setNavOpen(open) {
    if (!navToggle || !siteNav) return;
    navToggle.setAttribute("aria-expanded", String(open));
    siteNav.classList.toggle("is-open", open);
    document.body.classList.toggle("nav-open", open);
    if (navBackdrop) {
      navBackdrop.classList.toggle("is-visible", open);
      navBackdrop.hidden = !open;
      navBackdrop.setAttribute("aria-hidden", String(!open));
    }
  }

  if (navToggle && siteNav) {
    const navClose = siteNav.querySelector("[data-nav-close]");

    navToggle.addEventListener("click", () => {
      const open = navToggle.getAttribute("aria-expanded") === "true";
      setNavOpen(!open);
    });

    navClose?.addEventListener("click", () => setNavOpen(false));

    siteNav.querySelector(".site-nav__drawer-logo")?.addEventListener("click", () => setNavOpen(false));

    siteNav.querySelectorAll(".site-nav__link").forEach((link) => {
      link.addEventListener("click", () => setNavOpen(false));
    });

    navBackdrop?.addEventListener("click", () => setNavOpen(false));

    document.addEventListener("keydown", (event) => {
      if (event.key === "Escape" && siteNav.classList.contains("is-open")) {
        setNavOpen(false);
      }
    });
  }

  /* Active nav link (fallback if server-side class missing) */
  if (siteNav) {
    const navLinks = [...siteNav.querySelectorAll(".site-nav__link")];
    const currentPath = window.location.pathname.replace(/\/+$/, "") || "/";
    let bestMatch = null;
    let bestLength = -1;

    navLinks.forEach((link) => {
      const href = link.getAttribute("href");
      if (!href || href.startsWith("#")) return;

      let linkPath;
      try {
        linkPath = new URL(href, window.location.href).pathname.replace(/\/+$/, "") || "/";
      } catch {
        return;
      }

      let matches = false;
      const navKey = link.dataset.navKey || "";

      if (navKey === "home") {
        matches = currentPath === linkPath;
      } else if (navKey === "listing") {
        const listingRoot = linkPath.replace(/\/listings?$/, "");
        const productPath = `${listingRoot}/product`.replace(/\/+/g, "/");
        matches =
          currentPath === linkPath ||
          currentPath.startsWith(`${linkPath}/`) ||
          currentPath === productPath ||
          currentPath.startsWith(`${productPath}/`);
      } else if (linkPath === "/") {
        matches = currentPath === "/";
      } else if (currentPath === linkPath || currentPath.startsWith(`${linkPath}/`)) {
        matches = true;
      }

      if (matches && linkPath.length > bestLength) {
        bestMatch = link;
        bestLength = linkPath.length;
      }
    });

    if (bestMatch) {
      navLinks.forEach((link) => {
        const active = link === bestMatch;
        link.classList.toggle("is-active", active);
        if (active) link.setAttribute("aria-current", "page");
        else link.removeAttribute("aria-current");
      });
    }
  }

  /* Directory tabs */
  const directoryTabs = document.querySelector("[data-directory-tabs]");
  if (directoryTabs) {
    const tabButtons = [...directoryTabs.querySelectorAll(".directory-tabs__btn")];
    const panels = [...directoryTabs.querySelectorAll(".directory-panel")];

    tabButtons.forEach((btn) => {
      btn.addEventListener("click", () => {
        const id = btn.getAttribute("data-tab");
        if (!id) return;

        tabButtons.forEach((b) => {
          const active = b === btn;
          b.classList.toggle("is-active", active);
          b.setAttribute("aria-selected", String(active));
        });

        panels.forEach((panel) => {
          const active = panel.getAttribute("data-panel") === id;
          panel.classList.toggle("is-active", active);
          panel.hidden = !active;
        });
      });
    });
  }

  /* Recent Listings Slider Carousel */
  const listSlider = document.querySelector(".listings-slider");
  const listPrevBtn = document.querySelector(".listings-slider-btn--prev");
  const listNextBtn = document.querySelector(".listings-slider-btn--next");

  if (listSlider && listPrevBtn && listNextBtn) {
    const updateButtons = () => {
      const scrollLeft = listSlider.scrollLeft;
      const maxScroll = listSlider.scrollWidth - listSlider.clientWidth;
      
      listPrevBtn.disabled = scrollLeft <= 2;
      listNextBtn.disabled = scrollLeft >= maxScroll - 2;
    };

    const getScrollAmount = () => {
      const firstItem = listSlider.firstElementChild;
      if (firstItem) {
        const style = window.getComputedStyle(listSlider);
        const gap = parseInt(style.gap) || 24;
        return firstItem.offsetWidth + gap;
      }
      return 300;
    };

    listPrevBtn.addEventListener("click", () => {
      listSlider.scrollBy({
        left: -getScrollAmount(),
        behavior: "smooth"
      });
    });

    listNextBtn.addEventListener("click", () => {
      listSlider.scrollBy({
        left: getScrollAmount(),
        behavior: "smooth"
      });
    });

    listSlider.addEventListener("scroll", updateButtons);
    window.addEventListener("resize", updateButtons);
    
    updateButtons();
    setTimeout(updateButtons, 500);
    window.addEventListener("load", updateButtons);
  }

  /* Hero slider */
  const slider = document.querySelector("[data-slider]");
  if (!slider) return;

  const slides = [...slider.querySelectorAll(".hero-slide")];
  const dots = [...slider.querySelectorAll(".hero-slider__dot")];
  const prevBtn = slider.querySelector("[data-prev]");
  const nextBtn = slider.querySelector("[data-next]");
  let index = 0;
  let timer = null;
  const INTERVAL = 6000;

  function goTo(i) {
    index = (i + slides.length) % slides.length;

    slides.forEach((slide, n) => {
      const active = n === index;
      slide.classList.toggle("is-active", active);
      slide.hidden = !active;
    });

    dots.forEach((dot, n) => {
      const active = n === index;
      dot.classList.toggle("is-active", active);
      dot.setAttribute("aria-selected", String(active));
    });
  }

  function next() {
    goTo(index + 1);
  }

  function prev() {
    goTo(index - 1);
  }

  function startAutoplay() {
    stopAutoplay();
    timer = window.setInterval(next, INTERVAL);
  }

  function stopAutoplay() {
    if (timer) {
      window.clearInterval(timer);
      timer = null;
    }
  }

  dots.forEach((dot) => {
    dot.addEventListener("click", () => {
      const i = Number(dot.getAttribute("data-goto"));
      if (!Number.isNaN(i)) {
        goTo(i);
        startAutoplay();
      }
    });
  });

  prevBtn?.addEventListener("click", () => {
    prev();
    startAutoplay();
  });

  nextBtn?.addEventListener("click", () => {
    next();
    startAutoplay();
  });

  slider.addEventListener("mouseenter", stopAutoplay);
  slider.addEventListener("mouseleave", startAutoplay);
  slider.addEventListener("focusin", stopAutoplay);
  slider.addEventListener("focusout", startAutoplay);

  document.addEventListener("visibilitychange", () => {
    if (document.hidden) stopAutoplay();
    else startAutoplay();
  });

  goTo(0);
  startAutoplay();
})();

/* Header language & currency */
(function () {
  "use strict";

  const localeRoot = document.querySelector("[data-header-locale]");
  if (!localeRoot) return;

  const langSelect = localeRoot.querySelector("[data-locale-language]");
  const currencySelect = localeRoot.querySelector("[data-locale-currency]");
  const dropdowns = [...localeRoot.querySelectorAll("[data-locale-dropdown]")];
  if (!langSelect || !currencySelect || !dropdowns.length) return;

  const STORAGE_LANG = "eisen-locale-lang";
  const priceSelector = ".listing-card__price, .product-price[data-price-usd]";

  function applyLanguage(lang) {
    if (window.EisenI18n) window.EisenI18n.apply(lang);
  }

  function syncDropdownUI(dropdown, value) {
    const options = [...dropdown.querySelectorAll("[data-locale-value]")];
    const triggerIcon = dropdown.querySelector("[data-locale-trigger-icon]");
    const triggerLabel = dropdown.querySelector("[data-locale-trigger-label]");
    const selected = options.find((opt) => opt.getAttribute("data-locale-value") === value) || options[0];
    if (!selected) return;

    options.forEach((opt) => {
      const active = opt === selected;
      opt.classList.toggle("is-active", active);
      opt.setAttribute("aria-selected", String(active));
    });

    const optionIcon = selected.querySelector(".header-locale__option-icon");
    const optionLabel = selected.querySelector(".header-locale__option-label");
    if (triggerIcon && optionIcon) triggerIcon.innerHTML = optionIcon.innerHTML;
    if (triggerLabel && optionLabel) triggerLabel.textContent = optionLabel.textContent;
  }

  function closeDropdown(dropdown) {
    const trigger = dropdown.querySelector("[data-locale-trigger]");
    const menu = dropdown.querySelector("[data-locale-menu]");
    if (!trigger || !menu) return;
    trigger.setAttribute("aria-expanded", "false");
    menu.hidden = true;
  }

  function closeAllDropdowns(except) {
    dropdowns.forEach((dropdown) => {
      if (dropdown !== except) closeDropdown(dropdown);
    });
  }

  function setLanguage(value) {
    if (!langSelect.querySelector(`option[value="${value}"]`)) return;
    langSelect.value = value;
    localStorage.setItem(STORAGE_LANG, value);
    applyLanguage(value);
    syncDropdownUI(localeRoot.querySelector('[data-locale-dropdown="language"]'), value);
  }

  function setCurrency(value) {
    if (!currencySelect.querySelector(`option[value="${value}"]`)) return;
    currencySelect.value = value;
    if (window.EisenCurrency) window.EisenCurrency.applyCurrency(value);
    syncDropdownUI(localeRoot.querySelector('[data-locale-dropdown="currency"]'), value);
  }

  dropdowns.forEach((dropdown) => {
    const trigger = dropdown.querySelector("[data-locale-trigger]");
    const menu = dropdown.querySelector("[data-locale-menu]");
    const type = dropdown.getAttribute("data-locale-dropdown");
    const options = [...dropdown.querySelectorAll("[data-locale-value]")];
    if (!trigger || !menu) return;

    trigger.addEventListener("click", (event) => {
      event.stopPropagation();
      const isOpen = trigger.getAttribute("aria-expanded") === "true";
      closeAllDropdowns();
      if (!isOpen) {
        trigger.setAttribute("aria-expanded", "true");
        menu.hidden = false;
      }
    });

    options.forEach((option) => {
      option.addEventListener("click", () => {
        const value = option.getAttribute("data-locale-value");
        if (!value) return;
        if (type === "language") setLanguage(value);
        if (type === "currency") setCurrency(value);
        closeDropdown(dropdown);
      });
    });
  });

  document.addEventListener("click", () => closeAllDropdowns());
  document.addEventListener("keydown", (event) => {
    if (event.key === "Escape") closeAllDropdowns();
  });

  const savedLang = localStorage.getItem(STORAGE_LANG);
  const savedCurrency = localStorage.getItem("eisen-locale-currency");

  if (savedLang && langSelect.querySelector(`option[value="${savedLang}"]`)) {
    langSelect.value = savedLang;
  }
  if (savedCurrency && currencySelect.querySelector(`option[value="${savedCurrency}"]`)) {
    currencySelect.value = savedCurrency;
  }

  syncDropdownUI(localeRoot.querySelector('[data-locale-dropdown="language"]'), langSelect.value);
  syncDropdownUI(localeRoot.querySelector('[data-locale-dropdown="currency"]'), currencySelect.value);
  applyLanguage(langSelect.value);

  if (window.EisenCurrency) {
    window.EisenCurrency.ready.then(() => {
      window.EisenCurrency.scanPrices(priceSelector);
      window.EisenCurrency.applyCurrency(currencySelect.value);
    });
  }
})();

/* Manufacturer and model dynamic dependent select boxes */
(function () {
  "use strict";
  document.addEventListener("DOMContentLoaded", () => {
    const makeSelect = document.getElementById("manufacturer");
    const modelSelect = document.getElementById("model");
    if (makeSelect && modelSelect) {
      makeSelect.addEventListener("change", () => {
        const selectedMake = makeSelect.value.toLowerCase();
        // Reset models select
        modelSelect.innerHTML = '';
        
        // Add "All models" option
        const allOpt = document.createElement("option");
        allOpt.value = "";
        allOpt.setAttribute("data-i18n", "filter.allModels");
        allOpt.textContent = "All models";
        modelSelect.appendChild(allOpt);

        if (selectedMake && window.EisenMakeToModels && window.EisenMakeToModels[selectedMake]) {
          window.EisenMakeToModels[selectedMake].forEach(m => {
            const opt = document.createElement("option");
            opt.value = m.value;
            opt.setAttribute("data-i18n", "spec.val." + m.value);
            opt.textContent = m.label;
            modelSelect.appendChild(opt);
          });
        }

        // Re-apply translation
        const lang = localStorage.getItem("eisen-locale-lang") || "en";
        if (window.EisenI18n) {
          window.EisenI18n.apply(lang);
        }
      });
    }
  });
})();
