document.addEventListener('DOMContentLoaded', function() {
    "use strict";

    // 1. Tab Switching
    const tabBtns = document.querySelectorAll('.modal-tab-btn');
    const tabPanels = document.querySelectorAll('.tab-panel');

    tabBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const targetTab = this.getAttribute('data-tab');

            // Toggle active button
            tabBtns.forEach(b => b.classList.remove('active'));
            this.classList.add('active');

            // Show/Hide panels
            tabPanels.forEach(panel => {
                if (panel.id === `panel-${targetTab}`) {
                    panel.style.display = 'block';
                } else {
                    panel.style.display = 'none';
                }
            });
        });
    });

    // 2. Modal Controls
    const modals = document.querySelectorAll('.modal-backdrop');

    // Helper to open modal
    function openModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.style.display = 'flex';
        }
    }

    // Helper to close all modals
    function closeAllModals() {
        modals.forEach(modal => {
            modal.style.display = 'none';
            // Clear file inputs when modal closes
            const fileInputs = modal.querySelectorAll('input[type="file"]');
            fileInputs.forEach(input => input.value = '');
        });
    }

    // Close buttons event binding
    document.querySelectorAll('.modal-close-btn, .btn-close-modal').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            closeAllModals();
        });
    });

    // Close on clicking backdrop/outside content box
    modals.forEach(modal => {
        modal.addEventListener('click', function(e) {
            if (e.target === this) {
                closeAllModals();
            }
        });
    });


    // ==========================================
    // HERO SLIDERS CRUD
    // ==========================================
    const btnAddSlider = document.getElementById('btn-add-slider');
    if (btnAddSlider) {
        btnAddSlider.addEventListener('click', function() {
            document.getElementById('sliderModalTitle').innerText = 'Add Hero Slide';
            document.getElementById('slider-id').value = '0';
            document.getElementById('slider-title').value = '';
            document.getElementById('slider-subtitle').value = '';
            document.getElementById('slider-link_url').value = '';
            document.getElementById('slider-sort_order').value = '0';
            document.getElementById('slider-status').value = '1';
            document.getElementById('slider-image_url').value = '';
            openModal('sliderModal');
        });
    }

    const editSliderBtns = document.querySelectorAll('.edit-slider-btn');
    editSliderBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const tr = this.closest('tr');
            const id = tr.getAttribute('data-id');
            const title = tr.getAttribute('data-title');
            const subtitle = tr.getAttribute('data-subtitle');
            const link_url = tr.getAttribute('data-link_url');
            const sort_order = tr.getAttribute('data-sort_order');
            const status = tr.getAttribute('data-status');
            const image_url = tr.getAttribute('data-image_url');

            document.getElementById('sliderModalTitle').innerText = `Edit Hero Slide [ID: ${id}]`;
            document.getElementById('slider-id').value = id;
            document.getElementById('slider-title').value = title;
            document.getElementById('slider-subtitle').value = subtitle;
            document.getElementById('slider-link_url').value = link_url;
            document.getElementById('slider-sort_order').value = sort_order;
            document.getElementById('slider-status').value = status;
            document.getElementById('slider-image_url').value = image_url;

            openModal('sliderModal');
        });
    });


    // ==========================================
    // DIRECTORY PARTNERS CRUD
    // ==========================================
    const btnAddPartner = document.getElementById('btn-add-partner');
    if (btnAddPartner) {
        btnAddPartner.addEventListener('click', function() {
            document.getElementById('partnerModalTitle').innerText = 'Add Partner';
            document.getElementById('partner-id').value = '0';
            document.getElementById('partner-name').value = '';
            document.getElementById('partner-type').value = 'dealer';
            document.getElementById('partner-sort_order').value = '0';
            document.getElementById('partner-logo_url').value = '';
            openModal('partnerModal');
        });
    }

    const editPartnerBtns = document.querySelectorAll('.edit-partner-btn');
    editPartnerBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const tr = this.closest('tr');
            const id = tr.getAttribute('data-id');
            const name = tr.getAttribute('data-name');
            const type = tr.getAttribute('data-type');
            const sort_order = tr.getAttribute('data-sort_order');
            const logo_url = tr.getAttribute('data-logo_url');

            document.getElementById('partnerModalTitle').innerText = `Edit Partner [ID: ${id}]`;
            document.getElementById('partner-id').value = id;
            document.getElementById('partner-name').value = name;
            document.getElementById('partner-type').value = type;
            document.getElementById('partner-sort_order').value = sort_order;
            document.getElementById('partner-logo_url').value = logo_url;

            openModal('partnerModal');
        });
    });


    // ==========================================
    // SHIPPING DESTINATIONS CRUD
    // ==========================================
    const btnAddShipping = document.getElementById('btn-add-shipping');
    if (btnAddShipping) {
        btnAddShipping.addEventListener('click', function() {
            document.getElementById('shippingModalTitle').innerText = 'Add Shipping Destination';
            document.getElementById('shipping-id').value = '0';
            document.getElementById('shipping-country').value = '';
            document.getElementById('shipping-port').value = '';
            document.getElementById('shipping-status').value = '1';
            openModal('shippingModal');
        });
    }

    const editShippingBtns = document.querySelectorAll('.edit-shipping-btn');
    editShippingBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const tr = this.closest('tr');
            const id = tr.getAttribute('data-id');
            const country = tr.getAttribute('data-country');
            const port = tr.getAttribute('data-port');
            const status = tr.getAttribute('data-status');

            document.getElementById('shippingModalTitle').innerText = `Edit Destination [ID: ${id}]`;
            document.getElementById('shipping-id').value = id;
            document.getElementById('shipping-country').value = country;
            document.getElementById('shipping-port').value = port;
            document.getElementById('shipping-status').value = status;

            openModal('shippingModal');
        });
    });


    // ==========================================
    // MAKES & MODELS CRUD
    // ==========================================
    const btnAddMakeModel = document.getElementById('btn-add-make-model');
    if (btnAddMakeModel) {
        btnAddMakeModel.addEventListener('click', function() {
            document.getElementById('makeModelModalTitle').innerText = 'Add Make & Model';
            document.getElementById('makeModel-id').value = '0';
            document.getElementById('makeModel-make').value = '';
            document.getElementById('makeModel-model').value = '';
            openModal('makeModelModal');
        });
    }

    const editMakeModelBtns = document.querySelectorAll('.edit-make-model-btn');
    editMakeModelBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const tr = this.closest('tr');
            const id = tr.getAttribute('data-id');
            const make = tr.getAttribute('data-make');
            const model = tr.getAttribute('data-model');

            document.getElementById('makeModelModalTitle').innerText = `Edit Make & Model [ID: ${id}]`;
            document.getElementById('makeModel-id').value = id;
            document.getElementById('makeModel-make').value = make;
            document.getElementById('makeModel-model').value = model;

            openModal('makeModelModal');
        });
    });
});
