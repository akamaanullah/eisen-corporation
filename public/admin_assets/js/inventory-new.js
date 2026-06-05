document.addEventListener('DOMContentLoaded', function() {
    "use strict";

    // ==========================================
    // 1. Toggle Active Styling for Option Chips
    // ==========================================
    const checkboxes = document.querySelectorAll('.car-option-check');
    
    checkboxes.forEach(checkbox => {
        const label = checkbox.closest('.checkbox-chip-label');
        
        // Initial state check
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
    // 2. Real-time Pricing Calculator & Currency Selector
    // ==========================================
    const currencySelector = document.getElementById('pricing_currency_selector');
    const priceVehicle = document.getElementById('price_vehicle');
    const priceJpy = document.getElementById('price_jpy');
    const priceFreight = document.getElementById('price_freight');
    const priceVanning = document.getElementById('price_vanning');
    const priceInspection = document.getElementById('price_inspection');
    const priceInsurance = document.getElementById('price_insurance');
    const totalDisplay = document.getElementById('total_price_display');

    const priceInputs = [priceVehicle, priceFreight, priceVanning, priceInspection, priceInsurance];

    // Select the main title element of the pricing card to update its header text
    let usdTitle = null;
    if (priceVehicle) {
        const pricingCard = priceVehicle.closest('.card');
        if (pricingCard) {
            usdTitle = pricingCard.querySelector('.options-group-title span');
        }
    }

    let exchangeRate = 150; // standard default fallback

    async function fetchExchangeRate() {
        try {
            const response = await fetch("https://open.er-api.com/v6/latest/USD");
            if (response.ok) {
                const data = await response.json();
                const rate = data.rates?.JPY;
                if (rate && !isNaN(rate)) {
                    exchangeRate = parseFloat(rate);
                    console.log("Live JPY rate loaded:", exchangeRate);
                }
            }
        } catch (e) {
            console.error("Failed to load live exchange rate:", e);
        }

        // Set hidden form input field
        const rateInput = document.getElementById('exchange_rate_input');
        if (rateInput) {
            rateInput.value = exchangeRate;
        }

        // Recalculate total with the live rate
        calculateTotal();
    }

    function getExchangeRate() {
        return exchangeRate;
    }

    // Call live rate fetcher immediately
    fetchExchangeRate();

    function updateLabelsAndPlaceholders(currency) {
        if (usdTitle) {
            usdTitle.textContent = `Pricing Breakdown (${currency})`;
        }
        
        if (priceVehicle) {
            const pricingCard = priceVehicle.closest('.card');
            if (pricingCard) {
                const labels = pricingCard.querySelectorAll('.form-label');
                labels.forEach(label => {
                    const labelFor = label.getAttribute('for');
                    if (labelFor === 'price_vehicle') {
                        label.textContent = `Base Vehicle Price (FOB) (${currency === 'USD' ? '$' : '¥'}) *`;
                    } else if (labelFor === 'price_jpy') {
                        label.textContent = `Vehicle Price (JPY) *`;
                    } else if (labelFor === 'price_freight') {
                        label.textContent = `Estimated Freight Charges (${currency === 'USD' ? '$' : '¥'})`;
                    } else if (labelFor === 'price_vanning') {
                        label.textContent = `Vanning Packaging Cost (${currency === 'USD' ? '$' : '¥'})`;
                    } else if (labelFor === 'price_inspection') {
                        label.textContent = `Inspection Certification Cost (${currency === 'USD' ? '$' : '¥'})`;
                    } else if (labelFor === 'price_insurance') {
                        label.textContent = `Marine Insurance Premium (${currency === 'USD' ? '$' : '¥'})`;
                    }
                });
            }
        }
    }

    function calculateTotal() {
        let total = 0;
        priceInputs.forEach(input => {
            if (input) {
                const val = parseFloat(input.value);
                if (!isNaN(val)) {
                    total += val;
                }
            }
        });

        const currency = currencySelector ? currencySelector.value : 'USD';
        if (totalDisplay) {
            if (currency === 'USD') {
                totalDisplay.textContent = '$' + total.toLocaleString('en-US', { minimumFractionDigits: 0, maximumFractionDigits: 2 });
            } else {
                totalDisplay.textContent = '¥' + total.toLocaleString('en-US', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
            }
        }
    }

    // Auto-calculate JPY price from USD FOB or USD FOB from JPY price
    if (priceVehicle && priceJpy) {
        priceVehicle.addEventListener('input', function() {
            const currency = currencySelector ? currencySelector.value : 'USD';
            if (currency === 'USD') {
                const usdVal = parseFloat(priceVehicle.value);
                if (!isNaN(usdVal)) {
                    priceJpy.value = Math.round(usdVal * getExchangeRate());
                } else {
                    priceJpy.value = '';
                }
            }
        });

        priceJpy.addEventListener('input', function() {
            const currency = currencySelector ? currencySelector.value : 'USD';
            if (currency === 'JPY') {
                const jpyVal = parseFloat(priceJpy.value);
                if (!isNaN(jpyVal)) {
                    priceVehicle.value = Math.round(jpyVal / getExchangeRate());
                } else {
                    priceVehicle.value = '';
                }
            }
        });
    }

    if (currencySelector) {
        currencySelector.addEventListener('change', function() {
            const currency = this.value;
            const rate = getExchangeRate();

            // Convert values in inputs
            priceInputs.forEach(input => {
                if (input && input.value !== '') {
                    const val = parseFloat(input.value);
                    if (!isNaN(val)) {
                        if (currency === 'JPY') {
                            input.value = Math.round(val * rate);
                        } else {
                            input.value = Math.round(val / rate);
                        }
                    }
                }
            });

            updateLabelsAndPlaceholders(currency);
            calculateTotal();
        });
    }

    priceInputs.forEach(input => {
        if (input) {
            input.addEventListener('input', calculateTotal);
            input.addEventListener('change', calculateTotal);
        }
    });

    // Run initial label updates and calculation once on load
    if (currencySelector) {
        updateLabelsAndPlaceholders(currencySelector.value);
    }
    calculateTotal();

    // ==========================================
    // 3. Photo Upload Slots & Reordering Logic
    // ==========================================
    const photoUploader = document.getElementById('gallery_uploader');
    const photoSlots = document.querySelectorAll('.photo-slot');
    const previews = document.querySelectorAll('.slot-preview');

    if (photoUploader) {
        photoUploader.addEventListener('change', function(e) {
            const files = e.target.files;
            if (files.length > 0) {
                // Clear previous previews first
                previews.forEach(p => p.innerHTML = '');
                
                // Populate up to 8 previews
                for (let i = 0; i < Math.min(files.length, previews.length); i++) {
                    const file = files[i];
                    const reader = new FileReader();
                    const targetPreview = previews[i];
                    
                    reader.onload = function(event) {
                        targetPreview.innerHTML = `<img src="${event.target.result}" alt="Preview">`;
                    };
                    reader.readAsDataURL(file);
                }
                
                toastr.options = { "closeButton": true, "timeOut": "2000" };
                toastr.success(`Loaded ${Math.min(files.length, previews.length)} photos into slots.`, 'Gallery Updated');
            }
        });
    }

    // Drag and drop event listeners for sorting
    let dragEl = null;

    photoSlots.forEach(slot => {
        slot.addEventListener('dragstart', function(e) {
            dragEl = this;
            e.dataTransfer.effectAllowed = 'move';
            this.classList.add('dragging');
        });

        slot.addEventListener('dragend', function() {
            this.classList.remove('dragging');
            photoSlots.forEach(s => s.classList.remove('drag-over'));
        });

        slot.addEventListener('dragover', function(e) {
            e.preventDefault();
            this.classList.add('drag-over');
        });

        slot.addEventListener('dragleave', function() {
            this.classList.remove('drag-over');
        });

        slot.addEventListener('drop', function(e) {
            e.preventDefault();
            this.classList.remove('drag-over');
            
            if (dragEl && dragEl !== this) {
                const targetPreview = this.querySelector('.slot-preview');
                const sourcePreview = dragEl.querySelector('.slot-preview');
                
                if (targetPreview && sourcePreview) {
                    // Swap innerHTML of the preview containers (swapping image or empty state text)
                    const tempHTML = targetPreview.innerHTML;
                    targetPreview.innerHTML = sourcePreview.innerHTML;
                    sourcePreview.innerHTML = tempHTML;
                    
                    toastr.options = { "closeButton": true, "timeOut": "1500" };
                    toastr.success('Photo slots reordered successfully!', 'Gallery Sorted');
                }
            }
        });
    });

    // ==========================================
    // 4. Form Submit Action
    // ==========================================
    const addVehicleForm = document.getElementById('addVehicleDetailedForm');

    if (addVehicleForm) {
        addVehicleForm.addEventListener('submit', function(e) {
            const make = document.getElementById('make').value.trim();
            const model = document.getElementById('model').value.trim();
            const year = document.getElementById('year').value.trim();
            const chassis = document.getElementById('chassis').value.trim();
            const grade = document.getElementById('grade').value.trim();
            const mileage = document.getElementById('mileage').value.trim();
            const engine = document.getElementById('engine').value.trim();

            if (make === '' || model === '' || year === '' || chassis === '' || grade === '' || mileage === '' || engine === '') {
                e.preventDefault();
                toastr.error('Please enter all required specifications (*).', 'Validation Error');
                return;
            }

            // Let the form submit naturally to the server which redirects and displays final flash alert
        });
    }
});
