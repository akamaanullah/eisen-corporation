<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= \App\Core\Session::getCsrfToken() ?>">
    <title><?php echo isset($pageTitle) ? $pageTitle : 'Eisen Admin Dashboard'; ?></title>
    
    <!-- Google Fonts: Montserrat & Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Montserrat:wght@600;700;800&display=swap" rel="stylesheet">
    
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    <!-- ChartJS for reports -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- Custom Admin Style -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/admin_assets/css/style.css?v=1.2">

    <script>
        window.BASE_URL = '<?= BASE_URL ?>';
    </script>
</head>
<body>
    <div class="app-container">
        <?php include dirname(__DIR__) . '/partials/sidebar.php'; ?>
        
        <div class="main-wrapper">
            <header class="topbar">
                <div class="topbar-left">
                    <button class="menu-toggle" id="sidebarToggle" aria-label="Toggle Sidebar">
                        <i data-lucide="menu" class="icon-open"></i>
                        <i data-lucide="chevron-right" class="icon-closed"></i>
                    </button>
                    <span class="topbar-welcome">Eisen Corporation — Control Room</span>
                </div>
                
                <div class="topbar-right">
                    <div class="user-profile-container">
                        <div class="user-profile" id="userProfileToggle">
                            <img src="https://ui-avatars.com/api/?name=Admin+User&background=c9a227&color=050d1a&bold=true" alt="Admin Profile">
                        </div>
                        
                        <div class="user-dropdown" id="userDropdown">
                            <div class="dropdown-header">
                                <img src="https://ui-avatars.com/api/?name=Admin+User&background=c9a227&color=050d1a&bold=true" alt="Admin Profile">
                                <div class="user-meta">
                                    <p class="user-name">Eisen Admin</p>
                                    <p class="user-role">Super Administrator</p>
                                </div>
                            </div>
                            <div class="dropdown-divider"></div>
                            <a href="<?= BASE_URL ?>/" class="dropdown-item">
                                <i data-lucide="external-link"></i>
                                <span>Go to Frontend</span>
                            </a>
                            <a href="<?= BASE_URL ?>/admin/logout" class="dropdown-item logout-item">
                                <i data-lucide="log-out"></i>
                                <span>Logout</span>
                            </a>
                        </div>
                    </div>
                </div>
            </header>

            <main class="content-body">
                <?php
                $adminFlash = \App\Core\Session::getFlash();
                if ($adminFlash):
                    $alertBg = ($adminFlash['type'] === 'success') ? '#1a4a2b' : '#4a151b';
                    $alertColor = ($adminFlash['type'] === 'success') ? '#c6f6d5' : '#feb2b2';
                    $alertBorder = ($adminFlash['type'] === 'success') ? 'rgba(198, 246, 213, 0.2)' : 'rgba(254, 178, 178, 0.2)';
                    $icon = ($adminFlash['type'] === 'success') ? 'check-circle' : 'alert-circle';
                ?>
                    <div class="alert alert-<?= htmlspecialchars($adminFlash['type']) ?> mb-30" role="alert" style="padding: 14px 18px; border-radius: 8px; font-size: 14px; margin-bottom: 24px; display: flex; align-items: center; gap: 12px; line-height: 1.5; border: 1px solid <?= $alertBorder ?>; background: <?= $alertBg ?>; color: <?= $alertColor ?>;">
                        <i data-lucide="<?= $icon ?>" style="width: 20px; height: 20px; flex-shrink: 0;"></i>
                        <span><?= htmlspecialchars($adminFlash['message']) ?></span>
                    </div>
                    <script>
                        document.addEventListener("DOMContentLoaded", function() {
                            if (typeof lucide !== 'undefined') {
                                lucide.createIcons();
                            }
                        });
                    </script>
                <?php endif; ?>
