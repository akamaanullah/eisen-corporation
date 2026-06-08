(function () {
  'use strict';

  function initPaymentDateRange() {
    var rangeInput = document.getElementById('payment-date-range');
    if (!rangeInput || typeof Litepicker === 'undefined') {
      return;
    }

    var fromInput = document.getElementById('payment-date-from');
    var toInput = document.getElementById('payment-date-to');
    if (!fromInput || !toInput) {
      return;
    }

    function t(key, fallback) {
      if (window.EisenI18n && typeof window.EisenI18n.t === 'function') {
        var value = window.EisenI18n.t(key);
        if (value && value !== key) {
          return value;
        }
      }
      return fallback;
    }

    if (rangeInput.dataset.pickerReady === '1') {
      return;
    }
    rangeInput.dataset.pickerReady = '1';

    var picker = new Litepicker({
      element: rangeInput,
      singleMode: false,
      numberOfColumns: 2,
      numberOfMonths: 2,
      autoApply: false,
      format: 'MM/DD/YYYY',
      delimiter: ' - ',
      showTooltip: false,
      dropdowns: {
        minYear: 2020,
        maxYear: new Date().getFullYear() + 1,
        months: true,
        years: true,
      },
      buttonText: {
        apply: t('account.paymentHistory.apply', 'Apply'),
        cancel: t('account.paymentHistory.cancel', 'Cancel'),
      },
      setup: function (instance) {
        instance.on('selected', function (date1, date2) {
          fromInput.value = date1 ? date1.format('YYYY-MM-DD') : '';
          toInput.value = date2 ? date2.format('YYYY-MM-DD') : (date1 ? date1.format('YYYY-MM-DD') : '');
        });

        instance.on('clear:selection', function () {
          fromInput.value = '';
          toInput.value = '';
          rangeInput.value = '';
        });
      },
    });

    if (fromInput.value && toInput.value) {
      picker.setDateRange(fromInput.value, toInput.value);
    }

    rangeInput.litepickerInstance = picker;
  }

  function initConsigneeForm() {
    var form = document.querySelector('[data-consignee-form]');
    if (!form) {
      return;
    }

    var notifyCol = form.querySelector('.account-consignee-form__col--notify');
    var notifySame = form.querySelector('[data-notify-same]');
    if (!notifyCol || !notifySame) {
      return;
    }

    var fieldMap = {
      name: 'name',
      country: 'country',
      state: 'state',
      city: 'city',
      address: 'address',
      ref_address: 'ref_address',
      phone_1: 'phone_1',
      phone_2: 'phone_2',
      phone_3: 'phone_3',
      email_1: 'email_1',
      email_2: 'email_2',
      email_3: 'email_3',
    };

    function syncNotifyFields() {
      Object.keys(fieldMap).forEach(function (key) {
        var source = form.querySelector('[data-consignee-field="' + key + '"]');
        var target = form.querySelector('[data-notify-field="' + fieldMap[key] + '"]');
        if (source && target) {
          target.value = source.value;
        }
      });
    }

    function setNotifyLocked(locked) {
      notifyCol.classList.toggle('is-synced', locked);
    }

    function handleNotifySame() {
      if (notifySame.checked) {
        syncNotifyFields();
        setNotifyLocked(true);
      } else {
        setNotifyLocked(false);
      }
    }

    notifySame.addEventListener('change', handleNotifySame);

    form.querySelectorAll('[data-consignee-field]').forEach(function (input) {
      input.addEventListener('input', function () {
        if (notifySame.checked) {
          syncNotifyFields();
        }
      });
      input.addEventListener('change', function () {
        if (notifySame.checked) {
          syncNotifyFields();
        }
      });
    });

    handleNotifySame();
  }

  function initFavoritesList() {
    var root = document.querySelector('[data-account-favorites]');
    if (!root) {
      return;
    }

    var list = root.querySelector('[data-favorites-list]');
    var countEl = root.querySelector('[data-favorites-count]');
    var emptyEl = null;

    function t(key, fallback) {
      if (window.EisenI18n && typeof window.EisenI18n.t === 'function') {
        var value = window.EisenI18n.t(key);
        if (value && value !== key) {
          return value;
        }
      }
      return fallback;
    }

    function getVisibleItems() {
      if (!list) {
        return [];
      }
      return Array.prototype.filter.call(
        list.querySelectorAll('[data-favorite-item]'),
        function (item) {
          return !item.hidden;
        }
      );
    }

    function ensureEmptyState() {
      if (emptyEl) {
        return emptyEl;
      }

      emptyEl = document.createElement('div');
      emptyEl.className = 'account-empty-state account-favorites__empty account-empty-state--favorites';
      emptyEl.setAttribute('data-favorites-empty', '');
      emptyEl.innerHTML =
        '<div class="account-empty-state__icon" aria-hidden="true">' +
        '<svg width="32" height="32" viewBox="0 0 24 24" fill="none">' +
        '<path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z" stroke="currentColor" stroke-width="1.5" fill="none"/>' +
        '</svg></div>' +
        '<h2 class="account-empty-state__title" data-i18n="account.favorites.empty">' +
        t('account.favorites.empty', 'No Favorites Found') +
        '</h2>';

      if (list && list.parentNode) {
        list.parentNode.insertBefore(emptyEl, list);
      }

      return emptyEl;
    }

    function updateCount() {
      if (!countEl) {
        return;
      }
      var count = getVisibleItems().length;
      var label = count === 1
        ? t('account.favorites.vehicle', 'vehicle')
        : t('account.favorites.vehicles', 'vehicles');
      countEl.textContent = count + ' ' + label;
    }

    function updateEmptyState() {
      var hasItems = getVisibleItems().length > 0;
      root.classList.toggle('is-empty', !hasItems);

      if (!hasItems) {
        ensureEmptyState();
      }
    }

    function removeItem(item) {
      if (!item) {
        return;
      }

      item.classList.add('is-removing');

      window.setTimeout(function () {
        item.hidden = true;
        item.classList.remove('is-removing');
        updateCount();
        updateEmptyState();
      }, 220);
    }

    root.querySelectorAll('[data-favorite-remove]').forEach(function (button) {
      button.addEventListener('click', function (event) {
        event.preventDefault();
        event.stopPropagation();
        
        var item = button.closest('[data-favorite-item]');
        if (!item) return;

        var stockId = item.getAttribute('data-stock-id');
        // Retrieve CSRF token from field inside the form or meta tag
        var csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute("content") || '';
        var baseUrl = window.EisenBaseUrl || '';

        if (!csrfToken) {
          console.error("CSRF token not found.");
          return;
        }

        button.disabled = true;

        var formData = new FormData();
        formData.append("stock_id", stockId);
        formData.append("csrf_token", csrfToken);

        fetch(baseUrl + "/account/favorites/remove", {
          method: "POST",
          body: formData,
          headers: {
            "X-Requested-With": "XMLHttpRequest"
          }
        })
        .then(function (response) {
          if (!response.ok) {
            throw new Error("HTTP error " + response.status);
          }
          return response.json();
        })
        .then(function (data) {
          if (data && data.status === "success") {
            removeItem(item);
            if (window.toastr) {
              toastr.success("Removed from favorites.", "Favorites");
            }
          } else {
            if (window.toastr && data && data.message) {
              toastr.error(data.message, "Error");
            }
          }
        })
        .catch(function (error) {
          console.error("Error removing favorite:", error);
          if (window.toastr) {
            toastr.error("An error occurred. Please try again.", "Error");
          }
        })
        .finally(function () {
          button.disabled = false;
        });
      });
    });

    try {
      localStorage.removeItem('eisen_favorites_removed');
    } catch (error) {
      /* ignore */
    }

    updateCount();
    updateEmptyState();
  }

  document.addEventListener('DOMContentLoaded', function () {
    initPaymentDateRange();
    initConsigneeForm();
    initFavoritesList();
  });
})();
