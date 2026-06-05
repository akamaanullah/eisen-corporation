document.addEventListener('DOMContentLoaded', function() {
    "use strict";

    // Modal management logic has been removed as vehicle creation is handled on its own page

    // ==========================================
    // 3. Search and Filtering Logic
    // ==========================================
    const searchInput = document.getElementById('searchFilter');
    const statusSelect = document.getElementById('statusFilter');
    const clearBtn = document.getElementById('clearFiltersBtn');
    const tableRows = document.querySelectorAll('#inventoryTable tbody tr');
    const tabBtnsInventory = document.querySelectorAll('.inventory-tabs .tab-btn');

    function filterTable() {
        const query = searchInput.value.toLowerCase().trim();
        const selectedStatus = statusSelect.value;
        
        let activeTabType = 'all';
        const activeTab = document.querySelector('.inventory-tabs .tab-btn.active');
        if (activeTab) {
            activeTabType = activeTab.getAttribute('data-filter-type');
        }

        tableRows.forEach(row => {
            const rowType = row.getAttribute('data-type');
            const rowStatus = row.getAttribute('data-status');
            const textContent = row.textContent.toLowerCase();

            const matchesSearch = query === '' || textContent.includes(query);
            const matchesType = activeTabType === 'all' || rowType === activeTabType;
            const matchesStatus = selectedStatus === '' || rowStatus === selectedStatus;

            if (matchesSearch && matchesType && matchesStatus) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    // Add Tab event listeners
    tabBtnsInventory.forEach(btn => {
        btn.addEventListener('click', function() {
            tabBtnsInventory.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            filterTable();
        });
    });

    if (searchInput) searchInput.addEventListener('keyup', filterTable);
    if (statusSelect) statusSelect.addEventListener('change', filterTable);

    if (clearBtn) {
        clearBtn.addEventListener('click', function() {
            searchInput.value = '';
            statusSelect.value = '';
            
            // Reset active tab to 'all'
            tabBtnsInventory.forEach(b => b.classList.remove('active'));
            if (tabBtnsInventory[0]) tabBtnsInventory[0].classList.add('active');
            
            filterTable();
        });
    }

    // ==========================================
    // 4. Action Button Event Simulations
    // ==========================================
    
    
    // Featured Switch Handler
    const featuredToggles = document.querySelectorAll('.featured-toggle-btn');
    featuredToggles.forEach(toggle => {
        toggle.addEventListener('change', function() {
            const row = this.closest('tr');
            const carId = row.getAttribute('data-db-id');
            const carName = row.querySelector('td:nth-child(2) strong').textContent;
            const isFeatured = this.checked;
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            
            const formData = new FormData();
            formData.append('csrf_token', csrfToken);
            
            fetch(window.BASE_URL + '/admin/inventory/toggle-featured/' + carId, {
                method: 'POST',
                body: formData
            })
            .then(res => {
                if (!res.ok) throw new Error('CSRF or Network Error');
                return res.json();
            })
            .then(data => {
                toastr.options = { "closeButton": true, "timeOut": "2000" };
                if (data.status === 'success') {
                    if (data.featured) {
                        toastr.success(`"${carName}" is now featured on the front homepage.`, 'Featured Status');
                    } else {
                        toastr.info(`"${carName}" removed from homepage featured list.`, 'Featured Status');
                    }
                } else {
                    toastr.error(data.message || 'Failed to update featured status.', 'Error');
                    this.checked = !isFeatured; // revert UI
                }
            })
            .catch(err => {
                toastr.error('CSRF verification failed or network issue.', 'Error');
                this.checked = !isFeatured; // revert UI
            });
        });
    });

    // Row Actions: Edit, Duplicate, Archive, Delete
    document.querySelectorAll('.edit-car-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const row = this.closest('tr');
            const carId = row.getAttribute('data-db-id');
            const carName = row.querySelector('td:nth-child(2) strong').textContent;
            
            toastr.info(`Redirecting to edit form for "${carName}"...`, 'Edit Action');
            window.location.href = window.BASE_URL + '/admin/inventory/edit/' + carId;
        });
    });

    document.querySelectorAll('.duplicate-car-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const row = this.closest('tr');
            const carId = row.getAttribute('data-db-id');
            const carName = row.querySelector('td:nth-child(2) strong').textContent;
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            
            Swal.fire({
                title: 'Duplicate Listing?',
                text: `Create a copy of "${carName}"? Specs will be prefilled.`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: 'var(--color-navy-700)',
                cancelButtonColor: 'var(--color-silver-400)',
                confirmButtonText: 'Yes, duplicate'
            }).then((result) => {
                if (result.isConfirmed) {
                    const formData = new FormData();
                    formData.append('csrf_token', csrfToken);

                    fetch(window.BASE_URL + '/admin/inventory/duplicate/' + carId, {
                        method: 'POST',
                        body: formData
                    })
                    .then(res => {
                        if (!res.ok) throw new Error('Network response not ok');
                        return res.json();
                    })
                    .then(data => {
                        if (data.status === 'success') {
                            Swal.fire({
                                title: 'Duplicated!',
                                text: data.message,
                                icon: 'success',
                                confirmButtonColor: 'var(--color-navy-700)'
                            }).then(() => {
                                window.location.reload();
                            });
                        } else {
                            Swal.fire({
                                title: 'Error!',
                                text: data.message || 'Failed to duplicate listing.',
                                icon: 'error',
                                confirmButtonColor: 'var(--color-navy-700)'
                            });
                        }
                    })
                    .catch(err => {
                        Swal.fire({
                            title: 'Error!',
                            text: 'Failed to duplicate listing (CSRF or Network Error).',
                            icon: 'error',
                            confirmButtonColor: 'var(--color-navy-700)'
                        });
                    });
                }
            });
        });
    });

    document.querySelectorAll('.archive-car-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const row = this.closest('tr');
            const carId = row.getAttribute('data-db-id');
            const carName = row.querySelector('td:nth-child(2) strong').textContent;
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            
            Swal.fire({
                title: 'Archive Vehicle?',
                text: `This will hide "${carName}" from the frontend store but preserve transaction histories.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: 'var(--color-navy-700)',
                cancelButtonColor: 'var(--color-silver-400)',
                confirmButtonText: 'Archive Listing'
            }).then((result) => {
                if (result.isConfirmed) {
                    const formData = new FormData();
                    formData.append('csrf_token', csrfToken);

                    fetch(window.BASE_URL + '/admin/inventory/archive/' + carId, {
                        method: 'POST',
                        body: formData
                    })
                    .then(res => {
                        if (!res.ok) throw new Error('Network response not ok');
                        return res.json();
                    })
                    .then(data => {
                        if (data.status === 'success') {
                            Swal.fire({
                                title: 'Archived!',
                                text: data.message,
                                icon: 'success',
                                confirmButtonColor: 'var(--color-navy-700)'
                            }).then(() => {
                                window.location.reload();
                            });
                        } else {
                            Swal.fire({
                                title: 'Error!',
                                text: data.message || 'Failed to archive listing.',
                                icon: 'error',
                                confirmButtonColor: 'var(--color-navy-700)'
                            });
                        }
                    })
                    .catch(err => {
                        Swal.fire({
                            title: 'Error!',
                            text: 'Failed to archive listing (CSRF or Network Error).',
                            icon: 'error',
                            confirmButtonColor: 'var(--color-navy-700)'
                        });
                    });
                }
            });
        });
    });

    document.querySelectorAll('.delete-car-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const row = this.closest('tr');
            const carId = row.getAttribute('data-db-id');
            const carName = row.querySelector('td:nth-child(2) strong').textContent;
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            
            Swal.fire({
                title: 'Delete Vehicle?',
                text: `Warning: This action is permanent! Are you sure you want to delete "${carName}"?`,
                icon: 'error',
                showCancelButton: true,
                confirmButtonColor: 'var(--color-danger)',
                cancelButtonColor: 'var(--color-silver-400)',
                confirmButtonText: 'Yes, delete permanently'
            }).then((result) => {
                if (result.isConfirmed) {
                    const formData = new FormData();
                    formData.append('csrf_token', csrfToken);

                    fetch(window.BASE_URL + '/admin/inventory/delete/' + carId, {
                        method: 'POST',
                        body: formData
                    })
                    .then(res => {
                        if (!res.ok) throw new Error('Network response not ok');
                        return res.json();
                    })
                    .then(data => {
                        if (data.status === 'success') {
                            row.remove();
                            Swal.fire({
                                title: 'Deleted!',
                                text: `"${carName}" has been removed from catalog listings.`,
                                icon: 'success',
                                confirmButtonColor: 'var(--color-navy-700)'
                            });
                            // Reload page to update counts & KPIs
                            setTimeout(() => {
                                window.location.reload();
                            }, 1200);
                        } else {
                            Swal.fire({
                                title: 'Error!',
                                text: data.message || 'Failed to delete listing.',
                                icon: 'error',
                                confirmButtonColor: 'var(--color-navy-700)'
                            });
                        }
                    })
                    .catch(err => {
                        Swal.fire({
                            title: 'Error!',
                            text: 'Failed to delete listing (CSRF or Network Error).',
                            icon: 'error',
                            confirmButtonColor: 'var(--color-navy-700)'
                        });
                    });
                }
            });
        });
    });

    // ==========================================
    // 5. Sync Auction API Simulation
    // ==========================================
    const syncBtn = document.getElementById('syncAuctionsBtn');
    if (syncBtn) {
        syncBtn.addEventListener('click', function() {
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            
            Swal.fire({
                title: 'Synchronizing Auctions',
                html: 'Connecting to <strong>jpcenter.ru</strong> API feeds...',
                timer: 2000,
                timerProgressBar: true,
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            }).then((result) => {
                const formData = new FormData();
                formData.append('csrf_token', csrfToken);

                fetch(window.BASE_URL + '/admin/inventory/sync-auctions', {
                    method: 'POST',
                    body: formData
                })
                .then(res => {
                    if (!res.ok) throw new Error('Network response not ok');
                    return res.json();
                })
                .then(data => {
                    toastr.options = {
                        "closeButton": true,
                        "progressBar": true,
                        "positionClass": "toast-top-right",
                        "timeOut": "4000"
                    };
                    if (data.status === 'success') {
                        toastr.success(data.message, 'API Sync Complete');
                        setTimeout(() => {
                            window.location.reload();
                        }, 1500);
                    } else if (data.status === 'info') {
                        toastr.info(data.message, 'Under Development');
                    } else {
                        toastr.error(data.message || 'Synchronization failed.', 'Error');
                    }
                })
                .catch(err => {
                    toastr.error('Failed to sync auction lots (CSRF or Network Error).', 'Error');
                });
            });
        });
    }

});
