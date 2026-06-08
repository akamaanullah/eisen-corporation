            </main>
        </div>
    </div>

    <!-- jQuery & Toastr (Alert alerts) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <!-- Base Common Admin JS -->
    <script src="<?= BASE_URL ?>/public/admin_assets/js/common.js?v=1.3"></script>
    
    <!-- Page Specific Script Loader -->
    <?php if (isset($pageScript)): ?>
        <?php if (is_array($pageScript)): ?>
            <?php foreach ($pageScript as $script): ?>
                <script src="<?= BASE_URL ?>/public/admin_assets/js/<?= $script ?>?v=1.3"></script>
            <?php endforeach; ?>
        <?php else: ?>
            <script src="<?= BASE_URL ?>/public/admin_assets/js/<?= $pageScript ?>?v=1.3"></script>
        <?php endif; ?>
    <?php endif; ?>

    <!-- Initialize Lucide Icons -->
    <script>
      lucide.createIcons();
    </script>
</body>
</html>
