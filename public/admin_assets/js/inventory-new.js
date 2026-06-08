document.addEventListener('DOMContentLoaded', function() {
    "use strict";

    // ==========================================
    // 1. Make / Model catalog dropdowns
    // ==========================================
    const makeSelect = document.getElementById('make');
    const modelSelect = document.getElementById('model');
    const makeModels = window.EisenMakeModels || {};
    const initialModel = window.EisenSelectedModel || '';

    function populateModels(make, selectedModel) {
        if (!modelSelect) return;

        const models = makeModels[make] || [];
        modelSelect.innerHTML = '<option value="">Select model</option>';

        models.forEach(function(modelName) {
            const opt = document.createElement('option');
            opt.value = modelName;
            opt.textContent = modelName;
            if (selectedModel && selectedModel === modelName) {
                opt.selected = true;
            }
            modelSelect.appendChild(opt);
        });

        if (selectedModel && !models.includes(selectedModel)) {
            const legacy = document.createElement('option');
            legacy.value = selectedModel;
            legacy.textContent = selectedModel + ' (legacy)';
            legacy.selected = true;
            modelSelect.appendChild(legacy);
        }
    }

    if (makeSelect && modelSelect) {
        if (makeSelect.value) {
            populateModels(makeSelect.value, initialModel || modelSelect.value);
        }

        makeSelect.addEventListener('change', function() {
            populateModels(this.value, '');
        });
    }

    // ==========================================
    // 2. Toggle Active Styling for Option Chips
    // ==========================================
    const checkboxes = document.querySelectorAll('.car-option-check');

    checkboxes.forEach(checkbox => {
        const label = checkbox.closest('.checkbox-chip-label');

        if (checkbox.checked && label) {
            label.classList.add('is-active');
        }

        checkbox.addEventListener('change', function() {
            if (label) {
                label.classList.toggle('is-active', this.checked);
            }
        });
    });

    // ==========================================
    // 3. JPY pricing with live USD reference
    // ==========================================
    const priceVehicleJpy = document.getElementById('price_vehicle_jpy');
    const priceFreightJpy = document.getElementById('price_freight_jpy');
    const priceVanningJpy = document.getElementById('price_vanning_jpy');
    const priceInspectionJpy = document.getElementById('price_inspection_jpy');
    const priceInsuranceJpy = document.getElementById('price_insurance_jpy');
    const exchangeRateInput = document.getElementById('exchange_rate');
    const totalJpyDisplay = document.getElementById('total_price_jpy_display');
    const liveUsdFob = document.getElementById('live_usd_fob');
    const liveUsdTotal = document.getElementById('live_usd_total');
    const liveRateLabel = document.getElementById('live_rate_label');

    const jpyInputs = [priceVehicleJpy, priceFreightJpy, priceVanningJpy, priceInspectionJpy, priceInsuranceJpy];
    const pricingCard = document.querySelector('[data-live-rate]');
    let exchangeRate = parseFloat(exchangeRateInput && exchangeRateInput.value ? exchangeRateInput.value : (pricingCard ? pricingCard.getAttribute('data-live-rate') : ''));
    let rateReady = !isNaN(exchangeRate) && exchangeRate > 0;

    async function fetchExchangeRate() {
        try {
            const response = await fetch('https://open.er-api.com/v6/latest/USD');
            if (response.ok) {
                const data = await response.json();
                const rate = data.rates && data.rates.JPY;
                if (rate && !isNaN(rate) && rate >= 50 && rate <= 300) {
                    exchangeRate = parseFloat(rate);
                    rateReady = true;
                }
            }
        } catch (e) {
            // keep server-provided or cached rate
        }

        if (!rateReady && pricingCard) {
            const serverRate = parseFloat(pricingCard.getAttribute('data-live-rate'));
            if (!isNaN(serverRate) && serverRate > 0) {
                exchangeRate = serverRate;
                rateReady = true;
            }
        }

        if (exchangeRateInput && rateReady) {
            exchangeRateInput.value = exchangeRate.toFixed(4);
        }
        if (liveRateLabel && rateReady) {
            liveRateLabel.textContent = ' @ ' + exchangeRate.toFixed(2) + ' JPY/USD (live)';
        }

        calculateTotal();
    }

    function parseJpyInput(input) {
        if (!input) {
            return 0;
        }
        const val = parseFloat(input.value);
        return !isNaN(val) && val > 0 ? val : 0;
    }

    function formatUsd(amount) {
        return '$' + amount.toLocaleString('en-US', { minimumFractionDigits: 0, maximumFractionDigits: 2 });
    }

    function formatJpy(amount) {
        return '¥' + Math.round(amount).toLocaleString('en-US');
    }

    function jpyToUsd(jpy) {
        return exchangeRate > 0 ? jpy / exchangeRate : 0;
    }

    function calculateTotal() {
        let totalJpy = 0;
        jpyInputs.forEach(function(input) {
            totalJpy += parseJpyInput(input);
        });

        if (totalJpyDisplay) {
            totalJpyDisplay.textContent = formatJpy(totalJpy);
        }

        const fobJpy = parseJpyInput(priceVehicleJpy);
        if (liveUsdFob) {
            liveUsdFob.textContent = fobJpy > 0 ? formatUsd(jpyToUsd(fobJpy)) : '$0';
        }
        if (liveUsdTotal) {
            liveUsdTotal.textContent = formatUsd(jpyToUsd(totalJpy));
        }
    }

    fetchExchangeRate();

    jpyInputs.forEach(function(input) {
        if (input) {
            input.addEventListener('input', calculateTotal);
            input.addEventListener('change', calculateTotal);
        }
    });

    calculateTotal();

    // ==========================================
    // 4. Photo Upload Slots, Drag-Drop & Reordering
    // ==========================================
    const photoUploader = document.getElementById('gallery_uploader');
    const photoSlots = document.querySelectorAll('.photo-slot');
    const previews = document.querySelectorAll('.slot-preview');
    const uploadDropzone = document.querySelector('.upload-dropzone');
    const slotCount = previews.length;
    let slotFiles = new Array(slotCount).fill(null);
    let dragSourceIndex = null;

    function isImageFile(file) {
        return file && file.type && file.type.startsWith('image/');
    }

    function syncGalleryInput() {
        if (!photoUploader || typeof DataTransfer === 'undefined') {
            return;
        }

        const transfer = new DataTransfer();
        slotFiles.forEach(function(file) {
            if (file) {
                transfer.items.add(file);
            }
        });
        photoUploader.files = transfer.files;
    }

    function updateSlotDragState() {
        photoSlots.forEach(function(slot, index) {
            const hasFile = !!slotFiles[index];
            slot.setAttribute('draggable', hasFile ? 'true' : 'false');
        });
    }

    function renderGalleryPreviews() {
        previews.forEach(function(preview, index) {
            preview.innerHTML = '';
            const file = slotFiles[index];
            if (!file) {
                return;
            }

            const reader = new FileReader();
            reader.onload = function(event) {
                preview.innerHTML = '<img src="' + event.target.result + '" alt="Preview" draggable="false">';
            };
            reader.readAsDataURL(file);
        });
        updateSlotDragState();
    }

    function addFilesToGallery(fileList) {
        const files = Array.from(fileList || []).filter(isImageFile);
        if (!files.length) {
            return 0;
        }

        let added = 0;
        files.forEach(function(file) {
            const emptyIndex = slotFiles.findIndex(function(item) {
                return !item;
            });
            if (emptyIndex === -1) {
                return;
            }
            slotFiles[emptyIndex] = file;
            added++;
        });

        if (added > 0) {
            renderGalleryPreviews();
            syncGalleryInput();
        }

        return added;
    }

    if (photoUploader) {
        photoUploader.addEventListener('change', function() {
            const added = addFilesToGallery(this.files);
            this.value = '';

            if (added > 0 && typeof toastr !== 'undefined') {
                toastr.options = { closeButton: true, timeOut: '2000' };
                toastr.success('Added ' + added + ' photo(s) to the gallery.', 'Gallery Updated');
            } else if (added === 0 && typeof toastr !== 'undefined') {
                toastr.warning('All photo slots are full or no valid images were selected.', 'Gallery Full');
            }
        });
    }

    if (uploadDropzone) {
        ['dragenter', 'dragover'].forEach(function(eventName) {
            uploadDropzone.addEventListener(eventName, function(e) {
                e.preventDefault();
                e.stopPropagation();
                uploadDropzone.classList.add('is-dragover');
            });
        });

        ['dragleave', 'drop'].forEach(function(eventName) {
            uploadDropzone.addEventListener(eventName, function(e) {
                e.preventDefault();
                e.stopPropagation();
                uploadDropzone.classList.remove('is-dragover');
            });
        });

        uploadDropzone.addEventListener('drop', function(e) {
            const added = addFilesToGallery(e.dataTransfer.files);
            if (added > 0 && typeof toastr !== 'undefined') {
                toastr.options = { closeButton: true, timeOut: '2000' };
                toastr.success('Dropped ' + added + ' photo(s) into the gallery.', 'Gallery Updated');
            }
        });
    }

    photoSlots.forEach(function(slot) {
        slot.addEventListener('dragstart', function(e) {
            const sourceIndex = parseInt(this.getAttribute('data-index'), 10);
            if (Number.isNaN(sourceIndex) || !slotFiles[sourceIndex]) {
                e.preventDefault();
                return;
            }

            dragSourceIndex = sourceIndex;
            e.dataTransfer.effectAllowed = 'move';
            e.dataTransfer.setData('text/plain', String(sourceIndex));
            this.classList.add('dragging');
        });

        slot.addEventListener('dragend', function() {
            dragSourceIndex = null;
            this.classList.remove('dragging');
            photoSlots.forEach(function(s) {
                s.classList.remove('drag-over');
            });
        });

        slot.addEventListener('dragover', function(e) {
            e.preventDefault();
            e.dataTransfer.dropEffect = 'move';
            this.classList.add('drag-over');
        });

        slot.addEventListener('dragleave', function() {
            this.classList.remove('drag-over');
        });

        slot.addEventListener('drop', function(e) {
            e.preventDefault();
            this.classList.remove('drag-over');

            const targetIndex = parseInt(this.getAttribute('data-index'), 10);
            let sourceIndex = dragSourceIndex;

            if (sourceIndex === null || Number.isNaN(sourceIndex)) {
                const fromData = parseInt(e.dataTransfer.getData('text/plain'), 10);
                sourceIndex = Number.isNaN(fromData) ? null : fromData;
            }

            if (sourceIndex === null || Number.isNaN(targetIndex) || sourceIndex === targetIndex) {
                return;
            }

            if (!slotFiles[sourceIndex]) {
                return;
            }

            const tempFile = slotFiles[sourceIndex];
            slotFiles[sourceIndex] = slotFiles[targetIndex];
            slotFiles[targetIndex] = tempFile;

            renderGalleryPreviews();
            syncGalleryInput();

            if (typeof toastr !== 'undefined') {
                toastr.options = { closeButton: true, timeOut: '1500' };
                toastr.success('Photo slots reordered successfully!', 'Gallery Sorted');
            }
        });
    });

    // ==========================================
    // 5. Form Submit Validation
    // ==========================================
    const addVehicleForm = document.getElementById('addVehicleDetailedForm');

    if (addVehicleForm) {
        addVehicleForm.addEventListener('submit', function(e) {
            syncGalleryInput();
            if (exchangeRateInput) {
                exchangeRateInput.value = exchangeRate.toFixed(4);
            }

            const make = document.getElementById('make').value.trim();
            const model = document.getElementById('model').value.trim();
            const year = document.getElementById('year').value.trim();
            const chassis = document.getElementById('chassis').value.trim();
            const fobJpy = priceVehicleJpy ? priceVehicleJpy.value.trim() : '';

            if (!rateReady || !exchangeRate || exchangeRate <= 0) {
                e.preventDefault();
                if (typeof toastr !== 'undefined') {
                    toastr.error('Exchange rate is still loading. Please wait a moment and try again.', 'Validation Error');
                }
                return;
            }

            if (make === '' || model === '' || year === '' || chassis === '' || fobJpy === '') {
                e.preventDefault();
                if (typeof toastr !== 'undefined') {
                    toastr.error('Please enter make, model, year, chassis, and JPY price (*).', 'Validation Error');
                }
            }
        });
    }
});
