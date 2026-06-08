(function () {
  "use strict";

  const gallery = document.querySelector("[data-product-gallery]");
  const lightbox = document.querySelector("[data-gallery-lightbox]");
  if (!gallery || !lightbox) return;

  const mainImg = gallery.querySelector("[data-gallery-main]");
  const openBtn = gallery.querySelector("[data-gallery-open]");
  const thumbsTrack = gallery.querySelector("[data-gallery-thumbs]");
  const thumbs = [...gallery.querySelectorAll("[data-gallery-index]")];
  const countBadge = gallery.querySelector("[data-gallery-count]");
  const thumbPrev = gallery.querySelector("[data-thumb-prev]");
  const thumbNext = gallery.querySelector("[data-thumb-next]");
  const lightboxImg = lightbox.querySelector("[data-gallery-lightbox-img]");
  const counter = lightbox.querySelector("[data-gallery-counter]");
  const prevBtn = lightbox.querySelector("[data-gallery-prev]");
  const nextBtn = lightbox.querySelector("[data-gallery-next]");
  const closeTriggers = [...lightbox.querySelectorAll("[data-gallery-close]")];

  if (!mainImg || !openBtn || !thumbs.length || !lightboxImg || !thumbsTrack) return;

  let currentIndex = 0;
  let lastFocus = null;

  function getSlide(index) {
    const thumb = thumbs[index];
    if (!thumb) return null;
    return {
      src: thumb.dataset.gallerySrc,
      alt: thumb.dataset.galleryAlt || "",
    };
  }

  function updateCount() {
    const text = `${currentIndex + 1} / ${thumbs.length}`;
    if (countBadge) countBadge.textContent = text;
  }

  function scrollThumbIntoView(index) {
    const thumb = thumbs[index];
    if (!thumb) return;
    thumb.scrollIntoView({ behavior: "smooth", block: "nearest", inline: "center" });
  }

  function setActiveThumb(index) {
    thumbs.forEach((thumb, i) => {
      const active = i === index;
      thumb.classList.toggle("is-active", active);
      thumb.setAttribute("aria-selected", String(active));
    });
    scrollThumbIntoView(index);
    updateCount();
  }

  function showSlide(index) {
    const slide = getSlide(index);
    if (!slide) return;

    currentIndex = index;
    mainImg.src = slide.src;
    mainImg.alt = slide.alt;
    setActiveThumb(index);
  }

  function updateLightbox() {
    const slide = getSlide(currentIndex);
    if (!slide) return;

    lightboxImg.src = slide.src;
    lightboxImg.alt = slide.alt;
    counter.textContent = `${currentIndex + 1} / ${thumbs.length}`;
    prevBtn.disabled = currentIndex === 0;
    nextBtn.disabled = currentIndex === thumbs.length - 1;
  }

  function openLightbox(index) {
    lastFocus = document.activeElement;
    currentIndex = index;
    updateLightbox();
    lightbox.hidden = false;
    document.body.style.overflow = "hidden";
    lightbox.querySelector(".product-lightbox__close")?.focus();
  }

  function closeLightbox() {
    lightbox.hidden = true;
    document.body.style.overflow = "";
    lastFocus?.focus();
  }

  function stepLightbox(delta) {
    const next = Math.min(Math.max(0, currentIndex + delta), thumbs.length - 1);
    if (next === currentIndex) return;
    currentIndex = next;
    showSlide(currentIndex);
    updateLightbox();
  }

  function scrollThumbs(direction) {
    const amount = Math.max(thumbsTrack.clientWidth * 0.75, 180);
    thumbsTrack.scrollBy({ left: direction * amount, behavior: "smooth" });
  }

  thumbs.forEach((thumb) => {
    thumb.addEventListener("click", () => {
      showSlide(Number(thumb.dataset.galleryIndex));
    });

    thumb.addEventListener("dblclick", () => {
      openLightbox(Number(thumb.dataset.galleryIndex));
    });
  });

  openBtn.addEventListener("click", () => {
    openLightbox(currentIndex);
  });

  thumbPrev?.addEventListener("click", () => scrollThumbs(-1));
  thumbNext?.addEventListener("click", () => scrollThumbs(1));

  prevBtn.addEventListener("click", () => stepLightbox(-1));
  nextBtn.addEventListener("click", () => stepLightbox(1));

  closeTriggers.forEach((el) => {
    el.addEventListener("click", closeLightbox);
  });

  document.addEventListener("keydown", (event) => {
    if (lightbox.hidden) return;

    if (event.key === "Escape") {
      closeLightbox();
      return;
    }

    if (event.key === "ArrowLeft") stepLightbox(-1);
    if (event.key === "ArrowRight") stepLightbox(1);
  });

  updateCount();

  const calcCurrency = document.querySelector("[data-product-calc-currency]");

  function applyVehiclePrices() {
    if (!window.EisenCurrency) return;
    const cells = [
      ...document.querySelectorAll(".product-vehicle-price"),
      ...document.querySelectorAll("[data-estimate-price-usd]"),
    ];
    if (!cells.length) return;
    const currency =
      calcCurrency?.value ||
      (window.EisenCurrency.getCurrency() === "jpy" ? "jpy" : "usd");
    cells.forEach((cell) => {
      // Prefer data-price-usd (set by calculateTotal or template)
      const usdAttr = cell.dataset.priceUsd ?? cell.dataset.estimatePriceUsd;
      if (usdAttr !== undefined) {
        const usd = Number(usdAttr);
        if (Number.isFinite(usd)) {
          cell.textContent = window.EisenCurrency.formatPrice(usd, currency);
        }
      } else if (cell.dataset.priceJpy !== undefined) {
        // Legacy fallback: JPY stored directly (e.g. shipping table rows)
        const jpy = Number(cell.dataset.priceJpy);
        if (Number.isFinite(jpy)) {
          cell.textContent = window.EisenCurrency.formatFromJpy(jpy, currency);
        }
      }
    });
  }

  function updateDynamicDescription(lang) {
    const data = window.EisenVehicleData;
    if (!data) return;

    const isJa = lang === "ja";

    // Map steering values
    let steeringText = data.steering;
    if (data.steering === "RHD") {
      steeringText = isJa ? "右ハンドル" : "right-hand drive (RHD)";
    } else if (data.steering === "LHD") {
      steeringText = isJa ? "左ハンドル" : "left-hand drive (LHD)";
    }

    // Map transmission values
    let transText = data.transmission;
    if (data.transmission === "AT") {
      transText = isJa ? "AT" : "automatic";
    } else if (data.transmission === "MT") {
      transText = isJa ? "MT" : "manual";
    }

    // Map fuel values
    let fuelText = data.fuel ? data.fuel.toLowerCase() : "";
    if (isJa) {
      if (fuelText === "petrol") fuelText = "ガソリン";
      else if (fuelText === "diesel") fuelText = "ディーゼル";
      else if (fuelText === "hybrid") fuelText = "ハイブリッド";
      else if (fuelText === "electric") fuelText = "電気";
      else fuelText = data.fuel || "";
    }

    // Map location (remove ", JAPAN" for cleaner Ja text)
    let locText = data.location || "";
    if (locText === "N/A") {
      locText = isJa ? "未登録" : "not listed";
    } else if (isJa) {
      locText = locText.replace(", JAPAN", "").trim();
    }

    // Format mileage
    const formattedMileage = Number(data.mileageKm || 0) > 0
      ? Number(data.mileageKm).toLocaleString()
      : "N/A";
    const formattedEngine = Number(data.engineCc || 0) > 0
      ? Number(data.engineCc).toLocaleString()
      : "N/A";

    // Map body type
    let bodyText = data.bodyType || "";
    if (isJa) {
      const bodyMap = {
        "hatchback": "ハッチバック",
        "sedan": "セダン",
        "suv": "SUV",
        "coupe": "クーペ",
        "wagon": "ワゴン",
        "van": "バン",
        "truck": "トラック",
        "hybrid": "ハイブリッド"
      };
      bodyText = bodyMap[bodyText.toLowerCase()] || bodyText;
    }

    // Paragraph 1
    const p1El = document.querySelector("[data-dynamic-desc='p1']");
    if (p1El) {
      if (isJa) {
        p1El.textContent = `この${data.title}は、日本市場向けの実用的な${bodyText}で、都市部の走行や輸出に適しています。この車両は${locText}の在庫から提供されており、ご要望に応じてオークション評価書を提供いたします。`;
      } else {
        p1El.textContent = `This ${data.title} is a practical Japan-market ${bodyText.toLowerCase()} ideal for city driving and export. The vehicle is offered from our ${locText} inventory with auction-grade documentation available on request.`;
      }
    }

    // Paragraph 2
    const p2El = document.querySelector("[data-dynamic-desc='p2']");
    if (p2El) {
      if (isJa) {
        p2El.textContent = formattedMileage === "N/A" || formattedEngine === "N/A"
          ? `${data.title}は優れた燃費効率、コンパクトなサイズ、そして多目的に使えるキャビンで知られています。走行距離または排気量の詳細は未登録ですが、${fuelText}エンジンと${transText}を搭載しており、日常の使用や海外市場での再販に最適です。`
          : `${data.title}は優れた燃費効率、コンパクトなサイズ、そして多目的に使えるキャビンで知られています。この車両の走行距離は${formattedMileage} kmで、排気量${formattedEngine} ccの${fuelText}エンジンとスムーズな${transText}を搭載しており、日常の使用や海外市場での再販に最適です。`;
      } else {
        p2El.textContent = formattedMileage === "N/A" || formattedEngine === "N/A"
          ? `The ${data.title} is known for excellent fuel economy, a compact footprint, and a versatile cabin. Mileage or engine details are not listed; it has a ${fuelText} engine with ${transText} transmission — well suited for daily use or resale in overseas markets.`
          : `The ${data.title} is known for excellent fuel economy, a compact footprint, and a versatile cabin. This example shows ${formattedMileage} km on the odometer with a ${formattedEngine} cc ${fuelText} engine and smooth ${transText} transmission — well suited for daily use or resale in overseas markets.`;
      }
    }

    // Paragraph 3
    const p3El = document.querySelector("[data-dynamic-desc='p3']");
    if (p3El) {
      if (isJa) {
        p3El.textContent = `外装色は${data.color ? data.color.toLowerCase() : ""}で、${data.doors}ドアの${bodyText}仕様です。日本仕様の標準である${steeringText}で、乗車定員は${data.seats}名です。ご購入前にオークション会場からの評価点や検査レポートを共有可能です。`;
      } else {
        p3El.textContent = `Exterior colour is ${data.color ? data.color.toLowerCase() : ""} with a ${data.doors}-door ${bodyText.toLowerCase()} body. The vehicle is ${steeringText} as standard for Japan, with seating for ${data.seats} passengers. Grade and inspection reports from the auction house can be shared before purchase.`;
      }
    }
  }

  function calculateTotal() {
    const pricing = window.EisenPricingData;
    if (!pricing) return;

    const freightRadio = document.querySelector("input[name='freight']:checked")?.value;
    const inspectionRadio = document.querySelector("input[name='inspection']:checked")?.value;
    const insuranceRadio = document.querySelector("input[name='insurance']:checked")?.value;

    // Accumulate in USD — currency.js converts to JPY live using the real exchange rate
    let totalUsd = pricing.vehicle + pricing.vanning;

    if (freightRadio === "prepaid") {
      totalUsd += pricing.freight;
    }
    if (inspectionRadio === "yes") {
      totalUsd += pricing.inspection;
    }
    if (insuranceRadio === "yes") {
      totalUsd += pricing.insurance;
    }
    totalUsd += pricing.coupon;

    const totalRow = document.querySelector(".product-estimate__total-row");
    if (totalRow) {
      let valEl = totalRow.querySelector(".product-estimate__total-value");
      if (!valEl) {
        const askEl = totalRow.querySelector(".product-estimate__ask");
        if (askEl) askEl.remove();

        valEl = document.createElement("strong");
        valEl.className = "product-estimate__total-value product-vehicle-price";
        totalRow.appendChild(valEl);
      }

      // Use USD data attribute so applyVehiclePrices() applies live conversion
      valEl.dataset.priceUsd = totalUsd;
      delete valEl.dataset.priceJpy;  // Remove stale JPY if previously set
      applyVehiclePrices();
    }
  }

  if (calcCurrency) {
    calcCurrency.addEventListener("change", applyVehiclePrices);
  }

  if (window.EisenCurrency) {
    window.EisenCurrency.ready.then(() => {
      if (calcCurrency) {
        calcCurrency.value = window.EisenCurrency.getCurrency();
      }
      applyVehiclePrices();
    });
    document.addEventListener("eisen:currency-change", () => {
      if (calcCurrency) {
        calcCurrency.value = window.EisenCurrency.getCurrency();
      }
      applyVehiclePrices();
    });
  }

  // Initial runs
  const initialLang = document.documentElement.lang || "en";
  updateDynamicDescription(initialLang);

  document.addEventListener("eisen:language-change", (event) => {
    updateDynamicDescription(event.detail.lang);
  });

  const estimateForm = document.querySelector(".product-estimate__form");
  if (estimateForm) {
    estimateForm.addEventListener("change", calculateTotal);
    calculateTotal();
  }

  // ==========================================
  // Favorites Toggle Interaction
  // ==========================================
  const favBtn = document.querySelector("[data-favorite-toggle]");
  if (favBtn) {
    favBtn.addEventListener("click", function (event) {
      event.preventDefault();
      
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
          const countSpan = favBtn.querySelector("[data-favorite-count]");
          if (countSpan) {
            countSpan.textContent = data.count;
          }
          if (data.action === "added") {
            favBtn.classList.add("is-active");
            if (window.toastr) {
              toastr.success("Added to favorites!", "Favorites");
            }
          } else {
            favBtn.classList.remove("is-active");
            if (window.toastr) {
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
  }

  // Country to Port dynamic filtering in estimator
  const countrySelect = document.getElementById("estimate-country");
  const portSelect = document.getElementById("estimate-port");
  if (countrySelect && portSelect && window.EisenCountryToPorts) {
    function filterPorts() {
      const selectedCountry = countrySelect.value.toUpperCase();
      const ports = window.EisenCountryToPorts[selectedCountry] || [];
      
      // Clear existing options
      portSelect.innerHTML = "";
      
      // Re-populate ports
      ports.forEach(port => {
        const opt = document.createElement("option");
        opt.value = port;
        opt.textContent = port;
        portSelect.appendChild(opt);
      });
      
      // Trigger estimator total calculation
      calculateTotal();
    }
    
    countrySelect.addEventListener("change", filterPorts);
    // Initial run to align ports
    filterPorts();
  }
})();
