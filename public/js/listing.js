/* Listing page — filters UI and AJAX search */
(function () {
  "use strict";

  const filtersPanel = document.querySelector("[data-inventory-filters]");
  if (!filtersPanel) return;

  const tagsContainer = filtersPanel.querySelector("[data-filter-tags]");
  const countEl = filtersPanel.querySelector("[data-filter-count]");
  const clearBtn = filtersPanel.querySelector("[data-filter-clear]");
  const toggleBtn = document.querySelector("[data-filter-toggle]");
  const toggleCountEl = document.querySelector("[data-filter-toggle-count]");
  const backdrop = document.querySelector("[data-filter-backdrop]");
  const closeBtn = filtersPanel.querySelector("[data-filter-close]");
  const checkboxes = [
    ...filtersPanel.querySelectorAll('.inventory-check input[type="checkbox"]:not([data-condition-input])'),
  ];
  const conditionInputs = [...filtersPanel.querySelectorAll("[data-condition-input]")];
  const priceRangeRoot = filtersPanel.querySelector("[data-price-range]");
  const priceMinInput = priceRangeRoot?.querySelector("[data-price-min-input]");
  const priceMaxInput = priceRangeRoot?.querySelector("[data-price-max-input]");
  const priceFill = priceRangeRoot?.querySelector("[data-price-fill]");
  const priceMinDisplay = priceRangeRoot?.querySelector("[data-price-min-display]");
  const priceMaxDisplay = priceRangeRoot?.querySelector("[data-price-max-display]");
  const yearMinSelect = filtersPanel.querySelector("[data-year-min-select]");
  const yearMaxSelect = filtersPanel.querySelector("[data-year-max-select]");
  const engineCcMinSelect = filtersPanel.querySelector("[data-engine-cc-min-select]");
  const engineCcMaxSelect = filtersPanel.querySelector("[data-engine-cc-max-select]");
  const mileageRangeRoot = filtersPanel.querySelector("[data-mileage-range]");
  const mileageMinInput = mileageRangeRoot?.querySelector("[data-mileage-min-input]");
  const mileageMaxInput = mileageRangeRoot?.querySelector("[data-mileage-max-input]");
  const mileageFill = mileageRangeRoot?.querySelector("[data-mileage-fill]");
  const mileageMinDisplay = mileageRangeRoot?.querySelector("[data-mileage-min-display]");
  const mileageMaxDisplay = mileageRangeRoot?.querySelector("[data-mileage-max-display]");

  const grid = document.querySelector("[data-inventory-grid]");
  const pagination = document.querySelector("[data-inventory-pagination]");
  const pagesContainer = document.querySelector("[data-pagination-pages]");
  const prevBtn = document.querySelector("[data-page-prev]");
  const nextBtn = document.querySelector("[data-page-next]");

  if (!tagsContainer || !countEl || !clearBtn || !grid || !pagination || !pagesContainer || !prevBtn || !nextBtn) return;

  const MOBILE_MQ = window.matchMedia("(max-width: 1023px)");
  const PRICE_ABS_MIN = priceRangeRoot ? Number(priceRangeRoot.dataset.priceMin) || 5000 : 5000;
  const PRICE_ABS_MAX = priceRangeRoot ? Number(priceRangeRoot.dataset.priceMax) || 80000 : 80000;
  const PRICE_STEP = priceRangeRoot ? Number(priceRangeRoot.dataset.priceStep) || 1000 : 1000;
  const MILEAGE_ABS_MIN = mileageRangeRoot ? Number(mileageRangeRoot.dataset.mileageMin) || 0 : 0;
  const MILEAGE_ABS_MAX = mileageRangeRoot ? Number(mileageRangeRoot.dataset.mileageMax) || 300 : 300;
  const MILEAGE_STEP = mileageRangeRoot ? Number(mileageRangeRoot.dataset.mileageStep) || 5 : 5;

  const PER_PAGE = 9;

  // Search state variables
  let listings = [];
  let totalListings = 0;
  let totalPages = 1;
  let currentPage = 1;

  function getLang() {
    return document.documentElement.lang === "ja" ? "ja" : "en";
  }

  function t(key) {
    if (window.EisenI18n && typeof window.EisenI18n.t === "function") {
      return window.EisenI18n.t(key, getLang());
    }
    return key;
  }

  function getFilterLabel(input) {
    const label = input.closest(".inventory-check");
    if (!label) return input.value;
    const textEl = label.querySelector("[data-i18n]") || label.querySelector("span");
    return textEl ? textEl.textContent.trim() : label.textContent.trim();
  }

  function getFilterId(input) {
    return `${input.name}:${input.value}`;
  }

  function getActiveFilters() {
    return checkboxes.filter((input) => input.checked);
  }

  function formatPriceAmount(amount) {
    if (window.EisenCurrency) {
      return window.EisenCurrency.formatPrice(amount, window.EisenCurrency.getCurrency());
    }
    return "$" + Number(amount).toLocaleString("en-US");
  }

  function isPriceRangeActive() {
    if (!priceMinInput || !priceMaxInput) return false;
    const min = Number(priceMinInput.value);
    const max = Number(priceMaxInput.value);
    return min > PRICE_ABS_MIN || max < PRICE_ABS_MAX;
  }

  function getPriceRangeLabel() {
    if (!priceMinInput || !priceMaxInput) return "";
    return `${formatPriceAmount(priceMinInput.value)} – ${formatPriceAmount(priceMaxInput.value)}`;
  }

  function updatePriceRangeUi() {
    if (!priceMinInput || !priceMaxInput || !priceFill) return;

    let min = Number(priceMinInput.value);
    let max = Number(priceMaxInput.value);

    if (min > max - PRICE_STEP) {
      if (document.activeElement === priceMinInput) {
        min = max - PRICE_STEP;
        priceMinInput.value = String(min);
      } else {
        max = min + PRICE_STEP;
        priceMaxInput.value = String(max);
      }
    }

    const range = PRICE_ABS_MAX - PRICE_ABS_MIN;
    const left = ((min - PRICE_ABS_MIN) / range) * 100;
    const right = ((max - PRICE_ABS_MIN) / range) * 100;

    priceFill.style.left = `${left}%`;
    priceFill.style.width = `${right - left}%`;

    if (priceMinDisplay) priceMinDisplay.textContent = formatPriceAmount(min);
    if (priceMaxDisplay) priceMaxDisplay.textContent = formatPriceAmount(max);
  }

  function resetPriceRange() {
    if (!priceMinInput || !priceMaxInput) return;
    priceMinInput.value = String(PRICE_ABS_MIN);
    priceMaxInput.value = String(PRICE_ABS_MAX);
    updatePriceRangeUi();
  }

  function formatMileageK(k) {
    const value = Number(k);
    if (getLang() === "ja") {
      const man = (value / 10).toFixed(1);
      return t("inventory.mileageRangeUnit").replace("{value}", man);
    }
    return t("inventory.mileageRangeUnit").replace("{value}", String(value));
  }

  function isMileageRangeActive() {
    if (!mileageMinInput || !mileageMaxInput) return false;
    const min = Number(mileageMinInput.value);
    const max = Number(mileageMaxInput.value);
    return min > MILEAGE_ABS_MIN || max < MILEAGE_ABS_MAX;
  }

  function getMileageRangeLabel() {
    if (!mileageMinInput || !mileageMaxInput) return "";
    return `${formatMileageK(mileageMinInput.value)} – ${formatMileageK(mileageMaxInput.value)}`;
  }

  function updateMileageRangeUi() {
    if (!mileageMinInput || !mileageMaxInput || !mileageFill) return;

    let min = Number(mileageMinInput.value);
    let max = Number(mileageMaxInput.value);

    if (min > max - MILEAGE_STEP) {
      if (document.activeElement === mileageMinInput) {
        min = max - MILEAGE_STEP;
        mileageMinInput.value = String(min);
      } else {
        max = min + MILEAGE_STEP;
        mileageMaxInput.value = String(max);
      }
    }

    const range = MILEAGE_ABS_MAX - MILEAGE_ABS_MIN;
    const left = ((min - MILEAGE_ABS_MIN) / range) * 100;
    const right = ((max - MILEAGE_ABS_MIN) / range) * 100;

    mileageFill.style.left = `${left}%`;
    mileageFill.style.width = `${right - left}%`;

    if (mileageMinDisplay) mileageMinDisplay.textContent = formatMileageK(min);
    if (mileageMaxDisplay) mileageMaxDisplay.textContent = formatMileageK(max);
  }

  function resetMileageRange() {
    if (!mileageMinInput || !mileageMaxInput) return;
    mileageMinInput.value = String(MILEAGE_ABS_MIN);
    mileageMaxInput.value = String(MILEAGE_ABS_MAX);
    updateMileageRangeUi();
  }

  function isYearRangeActive() {
    if (!yearMinSelect || !yearMaxSelect) return false;
    return yearMinSelect.value !== "" || yearMaxSelect.value !== "";
  }

  function syncYearRange() {
    if (!yearMinSelect || !yearMaxSelect) return;

    const min = yearMinSelect.value ? Number(yearMinSelect.value) : null;
    const max = yearMaxSelect.value ? Number(yearMaxSelect.value) : null;

    if (min !== null && max !== null && min > max) {
      if (document.activeElement === yearMinSelect) {
        yearMaxSelect.value = String(min);
      } else {
        yearMinSelect.value = String(max);
      }
    }
  }

  function getYearRangeLabel() {
    if (!yearMinSelect || !yearMaxSelect) return "";

    const min = yearMinSelect.value;
    const max = yearMaxSelect.value;

    if (min && max) return `${min} – ${max}`;
    if (min) return `${min}+`;
    if (max) return `– ${max}`;
    return "";
  }

  function resetYearRange() {
    if (!yearMinSelect || !yearMaxSelect) return;
    yearMinSelect.value = "";
    yearMaxSelect.value = "";
  }

  function formatEngineCc(cc) {
    const value = Number(cc);
    const formatted = value.toLocaleString(getLang() === "ja" ? "ja-JP" : "en-US");
    return t("inventory.engineCcUnit").replace("{value}", formatted);
  }

  function updateEngineCcSelectLabels() {
    if (!engineCcMinSelect || !engineCcMaxSelect) return;
    [engineCcMinSelect, engineCcMaxSelect].forEach((select) => {
      [...select.options].forEach((opt) => {
        if (!opt.value) return;
        opt.textContent = formatEngineCc(opt.value);
      });
    });
  }

  function isEngineCcRangeActive() {
    if (!engineCcMinSelect || !engineCcMaxSelect) return false;
    return engineCcMinSelect.value !== "" || engineCcMaxSelect.value !== "";
  }

  function syncEngineCcRange() {
    if (!engineCcMinSelect || !engineCcMaxSelect) return;

    const min = engineCcMinSelect.value ? Number(engineCcMinSelect.value) : null;
    const max = engineCcMaxSelect.value ? Number(engineCcMaxSelect.value) : null;

    if (min !== null && max !== null && min > max) {
      if (document.activeElement === engineCcMinSelect) {
        engineCcMaxSelect.value = String(min);
      } else {
        engineCcMinSelect.value = String(max);
      }
    }
  }

  function getEngineCcRangeLabel() {
    if (!engineCcMinSelect || !engineCcMaxSelect) return "";

    const min = engineCcMinSelect.value;
    const max = engineCcMaxSelect.value;

    if (min && max) return `${formatEngineCc(min)} – ${formatEngineCc(max)}`;
    if (min) return `${formatEngineCc(min)}+`;
    if (max) return `– ${formatEngineCc(max)}`;
    return "";
  }

  function resetEngineCcRange() {
    if (!engineCcMinSelect || !engineCcMaxSelect) return;
    engineCcMinSelect.value = "";
    engineCcMaxSelect.value = "";
  }

  function getConditionValue() {
    const active = conditionInputs.find((input) => input.checked && input.value !== "all");
    return active ? active.value : "";
  }

  function isConditionActive() {
    return getConditionValue() !== "";
  }

  function getConditionLabel() {
    const active = conditionInputs.find((input) => input.checked && input.value !== "all");
    if (!active) return "";
    const textEl = active.closest(".inventory-check")?.querySelector("[data-i18n]") || active.closest(".inventory-check")?.querySelector("span");
    return textEl ? textEl.textContent.trim() : active.value;
  }

  function setConditionValue(value) {
    const next = value || "all";
    conditionInputs.forEach((input) => {
      input.checked = input.value === next;
    });
  }

  function resetCondition() {
    setConditionValue("all");
  }

  function handleConditionChange(changedInput) {
    if (!conditionInputs.length) return;

    if (changedInput.value === "all" && changedInput.checked) {
      conditionInputs.forEach((input) => {
        if (input.value !== "all") input.checked = false;
      });
    } else if (changedInput.checked) {
      conditionInputs.forEach((input) => {
        if (input === changedInput) return;
        input.checked = false;
      });
      if (!conditionInputs.some((input) => input.checked)) {
        const allInput = conditionInputs.find((input) => input.value === "all");
        if (allInput) allInput.checked = true;
      }
    } else if (!conditionInputs.some((input) => input.checked)) {
      const allInput = conditionInputs.find((input) => input.value === "all");
      if (allInput) allInput.checked = true;
    }

    renderTags();
  }

  function getActiveFilterCount() {
    return (
      getActiveFilters().length +
      (isConditionActive() ? 1 : 0) +
      (isPriceRangeActive() ? 1 : 0) +
      (isMileageRangeActive() ? 1 : 0) +
      (isYearRangeActive() ? 1 : 0) +
      (isEngineCcRangeActive() ? 1 : 0)
    );
  }

  function updateCount(count) {
    const countText = count > 0 ? `(${count})` : "";

    if (count > 0) {
      countEl.hidden = false;
      countEl.textContent = countText;
    } else {
      countEl.hidden = true;
      countEl.textContent = "";
    }

    if (toggleCountEl) {
      toggleCountEl.hidden = count === 0;
      toggleCountEl.textContent = countText;
    }

    clearBtn.hidden = count === 0;
  }

  function openFilters() {
    filtersPanel.classList.add("is-open");
    toggleBtn?.setAttribute("aria-expanded", "true");
    backdrop?.removeAttribute("hidden");
    document.body.style.overflow = "hidden";
  }

  function closeFilters() {
    filtersPanel.classList.remove("is-open");
    toggleBtn?.setAttribute("aria-expanded", "false");
    backdrop?.setAttribute("hidden", "");
    document.body.style.overflow = "";
  }

  function renderTags() {
    const active = getActiveFilters();
    tagsContainer.innerHTML = "";

    if (isConditionActive()) {
      const conditionLabel = `${t("inventory.conditionTag")}: ${getConditionLabel()}`;
      const conditionTag = document.createElement("button");
      conditionTag.type = "button";
      conditionTag.className = "inventory-tag";
      conditionTag.dataset.filterId = "condition";
      conditionTag.setAttribute(
        "aria-label",
        `${t("inventory.filter.remove")} ${conditionLabel} ${t("inventory.filter.suffix")}`
      );
      conditionTag.innerHTML = `${conditionLabel} <span aria-hidden="true">×</span>`;
      conditionTag.addEventListener("click", () => {
        resetCondition();
        renderTags();
        fetchFilteredListings(true);
      });
      tagsContainer.appendChild(conditionTag);
    }

    if (isEngineCcRangeActive()) {
      const ccLabel = `${t("inventory.engineCcTag")}: ${getEngineCcRangeLabel()}`;
      const ccTag = document.createElement("button");
      ccTag.type = "button";
      ccTag.className = "inventory-tag";
      ccTag.dataset.filterId = "engine-cc-range";
      ccTag.setAttribute(
        "aria-label",
        `${t("inventory.filter.remove")} ${ccLabel} ${t("inventory.filter.suffix")}`
      );
      ccTag.innerHTML = `${ccLabel} <span aria-hidden="true">×</span>`;
      ccTag.addEventListener("click", () => {
        resetEngineCcRange();
        renderTags();
        fetchFilteredListings(true);
      });
      tagsContainer.appendChild(ccTag);
    }

    if (isYearRangeActive()) {
      const yearLabel = `${t("inventory.yearRangeTag")}: ${getYearRangeLabel()}`;
      const yearTag = document.createElement("button");
      yearTag.type = "button";
      yearTag.className = "inventory-tag";
      yearTag.dataset.filterId = "year-range";
      yearTag.setAttribute(
        "aria-label",
        `${t("inventory.filter.remove")} ${yearLabel} ${t("inventory.filter.suffix")}`
      );
      yearTag.innerHTML = `${yearLabel} <span aria-hidden="true">×</span>`;
      yearTag.addEventListener("click", () => {
        resetYearRange();
        renderTags();
        fetchFilteredListings(true);
      });
      tagsContainer.appendChild(yearTag);
    }

    if (isMileageRangeActive()) {
      const mileageLabel = `${t("inventory.mileageRangeTag")}: ${getMileageRangeLabel()}`;
      const mileageTag = document.createElement("button");
      mileageTag.type = "button";
      mileageTag.className = "inventory-tag";
      mileageTag.dataset.filterId = "mileage-range";
      mileageTag.setAttribute(
        "aria-label",
        `${t("inventory.filter.remove")} ${mileageLabel} ${t("inventory.filter.suffix")}`
      );
      mileageTag.innerHTML = `${mileageLabel} <span aria-hidden="true">×</span>`;
      mileageTag.addEventListener("click", () => {
        resetMileageRange();
        renderTags();
        fetchFilteredListings(true);
      });
      tagsContainer.appendChild(mileageTag);
    }

    if (isPriceRangeActive()) {
      const priceLabel = `${t("inventory.priceRangeTag")}: ${getPriceRangeLabel()}`;
      const priceTag = document.createElement("button");
      priceTag.type = "button";
      priceTag.className = "inventory-tag";
      priceTag.dataset.filterId = "price-range";
      priceTag.setAttribute(
        "aria-label",
        `${t("inventory.filter.remove")} ${priceLabel} ${t("inventory.filter.suffix")}`
      );
      priceTag.innerHTML = `${priceLabel} <span aria-hidden="true">×</span>`;
      priceTag.addEventListener("click", () => {
        resetPriceRange();
        renderTags();
        fetchFilteredListings(true);
      });
      tagsContainer.appendChild(priceTag);
    }

    active.forEach((input) => {
      const id = getFilterId(input);
      const label = getFilterLabel(input);
      const tag = document.createElement("button");
      tag.type = "button";
      tag.className = "inventory-tag";
      tag.dataset.filterId = id;
      tag.setAttribute(
        "aria-label",
        `${t("inventory.filter.remove")} ${label} ${t("inventory.filter.suffix")}`
      );
      tag.innerHTML = `${label} <span aria-hidden="true">×</span>`;

      tag.addEventListener("click", () => {
        input.checked = false;
        renderTags();
        fetchFilteredListings(true);
      });

      tagsContainer.appendChild(tag);
    });

    const totalActive = getActiveFilterCount();
    tagsContainer.hidden = totalActive === 0;
    updateCount(totalActive);
  }

  // Bind Events for Filter panel
  checkboxes.forEach((input) => {
    input.addEventListener("change", () => {
      renderTags();
      fetchFilteredListings(true);
    });
  });

  clearBtn.addEventListener("click", () => {
    checkboxes.forEach((input) => {
      input.checked = false;
    });
    resetPriceRange();
    resetMileageRange();
    resetYearRange();
    resetEngineCcRange();
    resetCondition();
    renderTags();
    fetchFilteredListings(true);
  });

  conditionInputs.forEach((input) => {
    input.addEventListener("change", () => {
      handleConditionChange(input);
      fetchFilteredListings(true);
    });
  });

  if (yearMinSelect && yearMaxSelect) {
    yearMinSelect.addEventListener("change", () => {
      syncYearRange();
      renderTags();
      fetchFilteredListings(true);
    });
    yearMaxSelect.addEventListener("change", () => {
      syncYearRange();
      renderTags();
      fetchFilteredListings(true);
    });
  }

  if (engineCcMinSelect && engineCcMaxSelect) {
    engineCcMinSelect.addEventListener("change", () => {
      syncEngineCcRange();
      renderTags();
      fetchFilteredListings(true);
    });
    engineCcMaxSelect.addEventListener("change", () => {
      syncEngineCcRange();
      renderTags();
      fetchFilteredListings(true);
    });
  }

  if (priceMinInput && priceMaxInput) {
    priceMinInput.addEventListener("input", () => {
      updatePriceRangeUi();
      renderTags();
    });
    priceMaxInput.addEventListener("input", () => {
      updatePriceRangeUi();
      renderTags();
    });
    // Trigger AJAX requests on change (mouse up/release) to optimize server queries
    priceMinInput.addEventListener("change", () => fetchFilteredListings(true));
    priceMaxInput.addEventListener("change", () => fetchFilteredListings(true));
    updatePriceRangeUi();
  }

  if (mileageMinInput && mileageMaxInput) {
    mileageMinInput.addEventListener("input", () => {
      updateMileageRangeUi();
      renderTags();
    });
    mileageMaxInput.addEventListener("input", () => {
      updateMileageRangeUi();
      renderTags();
    });
    mileageMinInput.addEventListener("change", () => fetchFilteredListings(true));
    mileageMaxInput.addEventListener("change", () => fetchFilteredListings(true));
    updateMileageRangeUi();
  }

  toggleBtn?.addEventListener("click", () => {
    if (filtersPanel.classList.contains("is-open")) {
      closeFilters();
    } else {
      openFilters();
    }
  });

  closeBtn?.addEventListener("click", closeFilters);
  backdrop?.addEventListener("click", closeFilters);

  document.addEventListener("keydown", (event) => {
    if (event.key === "Escape" && filtersPanel.classList.contains("is-open")) {
      closeFilters();
    }
  });

  MOBILE_MQ.addEventListener("change", () => {
    if (!MOBILE_MQ.matches) {
      closeFilters();
    }
  });

  document.addEventListener("visibilitychange", () => {
    if (!document.hidden && filtersPanel.classList.contains("is-open") && MOBILE_MQ.matches) {
      // keep sync
    }
  });

  // AJAX Search Grid & Pagination Logic
  function formatMileage(k) {
    if (getLang() === "ja") {
      const man = (k / 10).toFixed(1);
      return t("inventory.mileageUnit").replace("{value}", man);
    }
    return t("inventory.mileageUnit").replace("{value}", String(k));
  }

  function formatLocation(location) {
    const value = String(location || "").trim();
    if (!value || value === "-" || value.toLowerCase() === "n/a") {
      return "";
    }
    return value;
  }

  function escapeHtml(value) {
    return String(value)
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;");
  }

  function formatListingPrice(usdAmount) {
    if (window.EisenCurrency) {
      return window.EisenCurrency.formatPrice(usdAmount, window.EisenCurrency.getCurrency());
    }
    return "$" + Number(usdAmount).toLocaleString("en-US");
  }

  function renderCard(item) {
    const location = formatLocation(item.location);
    const year =
      item.year > 0 ? String(item.year) : t("spec.val.na") || "N/A";

    return `
      <li>
        <article class="inventory-card">
          <button type="button" class="inventory-card__favorite-btn${item.isFavorited ? ' is-active' : ''}" data-favorite-toggle data-vehicle-id="${item.id}" aria-label="Toggle favorite">
            <svg class="inventory-card__favorite-icon" width="18" height="18" viewBox="0 0 24 24">
              <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
            </svg>
          </button>
          <a href="${(window.BASE_URL || "") + "/product/" + item.stockId}" class="inventory-card__link">
            <div class="inventory-card__media">
              <img src="${item.image}" alt="${escapeHtml(item.alt)}" width="600" height="400" loading="lazy" />
            </div>
            <div class="inventory-card__body">
              <span class="inventory-card__make">${escapeHtml(item.make)}</span>
              <h3 class="inventory-card__model">${escapeHtml(item.modelDisplay || item.model)}</h3>
              <p class="inventory-card__price-line"><strong>${escapeHtml(formatListingPrice(item.priceUsd))}</strong> · ${escapeHtml(year)}</p>
              ${location ? `<p class="inventory-card__location">${escapeHtml(location)}</p>` : ""}
            </div>
          </a>
        </article>
      </li>
    `;
  }

  function getPageItems(page, total) {
    if (total <= 7) {
      return Array.from({ length: total }, (_, i) => i + 1);
    }

    const items = new Set([1, total, page, page - 1, page + 1]);
    const sorted = [...items].filter((n) => n >= 1 && n <= total).sort((a, b) => a - b);
    const result = [];

    sorted.forEach((num, index) => {
      if (index > 0 && num - sorted[index - 1] > 1) {
        result.push("…");
      }
      result.push(num);
    });

    return result;
  }

  function renderPagination() {
    const items = getPageItems(currentPage, totalPages);

    pagesContainer.innerHTML = items
      .map((item) => {
        if (item === "…") {
          return `<span class="inventory-pagination__ellipsis" aria-hidden="true">…</span>`;
        }

        const isActive = item === currentPage;
        return `
          <button
            class="inventory-pagination__btn inventory-pagination__btn--page${isActive ? " is-active" : ""}"
            type="button"
            data-page="${item}"
            ${isActive ? 'aria-current="page"' : ""}
          >${item}</button>
        `;
      })
      .join("");

    prevBtn.disabled = currentPage === 1;
    nextBtn.disabled = currentPage === totalPages;
  }

  function goToPage(page) {
    const nextPage = Math.min(Math.max(1, page), totalPages);
    if (nextPage === currentPage) return;

    currentPage = nextPage;
    fetchFilteredListings(false);

    const resultsHead = document.querySelector(".inventory-results__head");
    if (resultsHead) {
      resultsHead.scrollIntoView({ behavior: "smooth", block: "nearest" });
    }
  }

  function fetchFilteredListings(resetPage = false) {
    if (resetPage) {
      currentPage = 1;
    }

    // 1. Gather all checked filters
    const selectedMakes = checkboxes.filter(c => c.name === 'make' && c.checked).map(c => c.value);
    const selectedModels = checkboxes.filter(c => c.name === 'model' && c.checked).map(c => c.value);
    const selectedFuels = checkboxes.filter(c => c.name === 'fuel' && c.checked).map(c => c.value);
    const selectedTransmissions = checkboxes.filter(c => c.name === 'transmission' && c.checked).map(c => c.value);
    const selectedColors = checkboxes.filter(c => c.name === 'color' && c.checked).map(c => c.value);
    const condition = getConditionValue();

    // 2. Build URL parameters
    const params = new URLSearchParams();
    params.append('page', String(currentPage));
    params.append('per_page', String(PER_PAGE));

    if (selectedMakes.length) params.append('make', selectedMakes.join(','));
    if (selectedModels.length) params.append('model', selectedModels.join(','));
    if (selectedFuels.length) params.append('fuel', selectedFuels.join(','));
    if (selectedTransmissions.length) params.append('transmission', selectedTransmissions.join(','));
    if (selectedColors.length) params.append('color', selectedColors.join(','));
    if (condition) params.append('condition', condition);

    if (isPriceRangeActive()) {
      params.append('price_min', priceMinInput.value);
      params.append('price_max', priceMaxInput.value);
    }
    if (isYearRangeActive()) {
      if (yearMinSelect.value) params.append('year_min', yearMinSelect.value);
      if (yearMaxSelect.value) params.append('year_max', yearMaxSelect.value);
    }
    if (isEngineCcRangeActive()) {
      if (engineCcMinSelect.value) params.append('engine_cc_min', engineCcMinSelect.value);
      if (engineCcMaxSelect.value) params.append('engine_cc_max', engineCcMaxSelect.value);
    }
    if (isMileageRangeActive()) {
      params.append('mileage_min', mileageMinInput.value);
      params.append('mileage_max', mileageMaxInput.value);
    }

    // 3. Render dynamic loading skeleton
    grid.innerHTML = `
      <li class="inventory-loading" style="grid-column: 1 / -1; text-align: center; padding: 4rem 2rem;">
        <div style="font-size: 1.25rem; color: var(--text-muted);">Loading listings...</div>
      </li>
    `;

    // 4. Fire fetch request
    const url = `${window.BASE_URL || ""}/api/listings?${params.toString()}`;
    fetch(url)
      .then((res) => {
        if (!res.ok) throw new Error("Search request failed");
        return res.json();
      })
      .then((resData) => {
        listings = resData.data;
        totalListings = resData.total;
        totalPages = resData.last_page;
        currentPage = resData.current_page;

        // Update counts in header
        const totalCountEl = document.querySelector(".inventory-results__total");
        if (totalCountEl) totalCountEl.textContent = Number(totalListings).toLocaleString("en-US");

        if (listings.length === 0) {
          grid.innerHTML = `
            <li class="inventory-no-results" style="grid-column: 1 / -1; text-align: center; padding: 5rem 2rem;">
              <p style="font-size: 1.5rem; font-weight: bold; margin-bottom: 0.5rem;">No matching vehicles found</p>
              <p style="color: var(--text-muted);">Try adjusting your filter parameters to see more listings.</p>
            </li>
          `;
          pagination.hidden = true;
        } else {
          grid.innerHTML = listings.map(renderCard).join("");
          renderPagination();
          pagination.hidden = totalPages <= 1;
        }
      })
      .catch((err) => {
        console.error("Error loading vehicles:", err);
        grid.innerHTML = `
          <li class="inventory-error" style="grid-column: 1 / -1; text-align: center; padding: 5rem 2rem; color: #ef4444;">
            <p style="font-size: 1.5rem; font-weight: bold; margin-bottom: 0.5rem;">Failed to load listings</p>
            <p>Please check your network and try refreshing the page.</p>
          </li>
        `;
        pagination.hidden = true;
      });
  }

  pagesContainer.addEventListener("click", (event) => {
    const btn = event.target.closest("[data-page]");
    if (!btn) return;
    goToPage(Number(btn.getAttribute("data-page")));
  });

  prevBtn.addEventListener("click", () => goToPage(currentPage - 1));
  nextBtn.addEventListener("click", () => goToPage(currentPage + 1));

  // Catalog favorites delegation listener
  grid.addEventListener("click", function (event) {
    const favBtn = event.target.closest("[data-favorite-toggle]");
    if (!favBtn) return;
    
    event.preventDefault();
    event.stopPropagation();
    
    const vehicleId = favBtn.getAttribute("data-vehicle-id");
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute("content");
    const baseUrl = window.EisenBaseUrl || "";

    if (!csrfToken) {
      console.error("CSRF token not found.");
      return;
    }

    favBtn.disabled = true;

    const formData = new FormData();
    formData.append("vehicle_id", vehicleId);
    formData.append("csrf_token", csrfToken);

    fetch(baseUrl + "/product/favorite/toggle", {
      method: "POST",
      body: formData,
      headers: {
        "X-Requested-With": "XMLHttpRequest"
      }
    })
    .then(response => {
      if (response.status === 401) {
        window.location.href = baseUrl + "/login";
        return;
      }
      if (!response.ok) {
        throw new Error("HTTP error " + response.status);
      }
      return response.json();
    })
    .then(data => {
      if (data && data.status === "success") {
        const allMatchingBtns = document.querySelectorAll(`[data-favorite-toggle][data-vehicle-id="${vehicleId}"]`);
        allMatchingBtns.forEach(btn => {
          if (data.action === "added") {
            btn.classList.add("is-active");
          } else {
            btn.classList.remove("is-active");
          }
        });

        // Sync local listings state array
        const listingItem = listings.find(item => String(item.id) === String(vehicleId));
        if (listingItem) {
          listingItem.isFavorited = (data.action === "added");
        }

        if (window.toastr) {
          if (data.action === "added") {
            toastr.success("Added to favorites!", "Favorites");
          } else {
            toastr.success("Removed from favorites.", "Favorites");
          }
        }
      } else {
        if (window.toastr && data && data.message) {
          toastr.error(data.message, "Error");
        }
      }
    })
    .catch(error => {
      console.error("Error toggling favorite:", error);
      if (window.toastr) {
        toastr.error("An error occurred. Please try again.", "Error");
      }
    })
    .finally(() => {
      favBtn.disabled = false;
    });
  });

  function initializeFiltersFromUrl() {
    const urlParams = new URLSearchParams(window.location.search);

    // 1. Checkboxes (make, model, fuel, transmission, color)
    const paramNames = ['make', 'model', 'fuel', 'transmission', 'color'];
    paramNames.forEach(name => {
      const val = urlParams.get(name);
      if (val) {
        const vals = val.split(',');
        vals.forEach(v => {
          const checkbox = checkboxes.find(c => c.name === name && c.value === v.toLowerCase());
          if (checkbox) checkbox.checked = true;
        });
      }
    });

    // 2. Condition
    const cond = urlParams.get('condition');
    if (cond) {
      conditionInputs.forEach(input => {
        input.checked = (input.value === cond);
      });
    }

    // 3. Price range
    const priceMin = urlParams.get('price_min');
    const priceMax = urlParams.get('price_max');
    if (priceMin && priceMinInput) priceMinInput.value = priceMin;
    if (priceMax && priceMaxInput) priceMaxInput.value = priceMax;
    if (priceRangeRoot) updatePriceRangeUi();

    // 4. Year range
    const yearMin = urlParams.get('year_min');
    const yearMax = urlParams.get('year_max');
    if (yearMin && yearMinSelect) yearMinSelect.value = yearMin;
    if (yearMax && yearMaxSelect) yearMaxSelect.value = yearMax;

    // 5. Engine CC range
    const ccMin = urlParams.get('engine_cc_min');
    const ccMax = urlParams.get('engine_cc_max');
    if (ccMin && engineCcMinSelect) engineCcMinSelect.value = ccMin;
    if (ccMax && engineCcMaxSelect) engineCcMaxSelect.value = ccMax;

    // 6. Mileage range
    const milMin = urlParams.get('mileage_min');
    const milMax = urlParams.get('mileage_max');
    if (milMin && mileageMinInput) mileageMinInput.value = milMin;
    if (milMax && mileageMaxInput) mileageMaxInput.value = milMax;
    if (mileageRangeRoot) updateMileageRangeUi();
  }

  function initListingGrid() {
    initializeFiltersFromUrl();
    fetchFilteredListings(true);
  }

  document.addEventListener("eisen:language-change", () => {
    if (listings.length > 0) {
      grid.innerHTML = listings.map(renderCard).join("");
    }
  });

  document.addEventListener("eisen:currency-change", () => {
    if (listings.length > 0) {
      grid.innerHTML = listings.map(renderCard).join("");
    }
  });

  // Initial bootstrap
  updateEngineCcSelectLabels();
  renderTags();

  if (window.EisenCurrency) {
    window.EisenCurrency.ready.then(initListingGrid);
  } else {
    initListingGrid();
  }
})();
