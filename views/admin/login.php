<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Login | Eisen Corporation</title>
  <!-- Google Fonts: Montserrat & Inter -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Montserrat:wght@600;700;800&display=swap" rel="stylesheet">
  
  <!-- Lucide Icons -->
  <script src="https://unpkg.com/lucide@latest"></script>
  
  <style>
    :root {
      --bg-dark: #050d1a;
      --bg-card: #0b1528;
      --primary: #c9a227;
      --primary-hover: #b08d20;
      --text-white: #ffffff;
      --text-gray: #a0aec0;
      --border-color: #1e2d4a;
      --error-bg: #4a151b;
      --error-text: #feb2b2;
      --success-bg: #1a4a2b;
      --success-text: #c6f6d5;
    }

    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }

    body {
      font-family: 'Inter', sans-serif;
      background-color: var(--bg-dark);
      color: var(--text-white);
      display: flex;
      justify-content: center;
      align-items: center;
      min-height: 100vh;
      padding: 20px;
      overflow-x: hidden;
    }

    .login-container {
      width: 100%;
      max-width: 440px;
      animation: fadeIn 0.6s ease-out;
    }

    @keyframes fadeIn {
      from {
        opacity: 0;
        transform: translateY(20px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    .logo-wrapper {
      text-align: center;
      margin-bottom: 24px;
    }

    .logo-img {
      max-width: 200px;
      height: auto;
    }

    .login-card {
      background-color: var(--bg-card);
      border: 1px solid var(--border-color);
      border-radius: 12px;
      padding: 40px;
      box-shadow: 0 10px 25px rgba(0, 0, 0, 0.4);
    }

    .login-header {
      margin-bottom: 30px;
      text-align: center;
    }

    .login-title {
      font-family: 'Montserrat', sans-serif;
      font-size: 24px;
      font-weight: 700;
      color: var(--text-white);
      margin-bottom: 8px;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }

    .login-subtitle {
      color: var(--text-gray);
      font-size: 14px;
    }

    .form-group {
      margin-bottom: 20px;
    }

    .form-label {
      display: block;
      font-size: 13px;
      font-weight: 500;
      color: var(--text-gray);
      margin-bottom: 8px;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }

    .form-input-wrapper {
      position: relative;
    }

    .form-input-icon {
      position: absolute;
      left: 12px;
      top: 50%;
      transform: translateY(-50%);
      color: var(--text-gray);
      width: 18px;
      height: 18px;
      pointer-events: none;
    }

    .form-control {
      width: 100%;
      background-color: var(--bg-dark);
      border: 1px solid var(--border-color);
      border-radius: 6px;
      color: var(--text-white);
      padding: 12px 12px 12px 42px;
      font-size: 14px;
      transition: all 0.3s ease;
      outline: none;
    }

    .form-control:focus {
      border-color: var(--primary);
      box-shadow: 0 0 0 2px rgba(201, 162, 39, 0.2);
    }

    .alert {
      padding: 12px;
      border-radius: 6px;
      font-size: 13px;
      margin-bottom: 20px;
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .alert-error {
      background-color: var(--error-bg);
      color: var(--error-text);
      border: 1px solid rgba(254, 178, 178, 0.2);
    }

    .alert-success {
      background-color: var(--success-bg);
      color: var(--success-text);
      border: 1px solid rgba(198, 246, 213, 0.2);
    }

    .btn-submit {
      width: 100%;
      background-color: var(--primary);
      color: var(--bg-dark);
      border: none;
      border-radius: 6px;
      padding: 12px;
      font-size: 15px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s ease;
      display: flex;
      justify-content: center;
      align-items: center;
      gap: 8px;
    }

    .btn-submit:hover {
      background-color: var(--primary-hover);
    }

    .back-link-wrapper {
      text-align: center;
      margin-top: 24px;
    }

    .back-link {
      color: var(--text-gray);
      text-decoration: none;
      font-size: 13px;
      transition: color 0.2s ease;
      display: inline-flex;
      align-items: center;
      gap: 4px;
    }

    .back-link:hover {
      color: var(--primary);
    }
  </style>
</head>
<body>

  <div class="login-container">
    <div class="logo-wrapper">
      <img src="<?= BASE_URL ?>/public/image/eisen-logo.png" alt="Eisen Corporation" class="logo-img">
    </div>

    <div class="login-card">
      <div class="login-header">
        <h1 class="login-title">Control Room</h1>
        <p class="login-subtitle">Administrator Portal Access</p>
      </div>

      <?php if (isset($flash) && $flash): ?>
        <div class="alert alert-error">
          <i data-lucide="alert-circle" style="width: 18px; height: 18px;"></i>
          <span><?= htmlspecialchars($flash['message']) ?></span>
        </div>
      <?php endif; ?>

      <form action="<?= BASE_URL ?>/admin/login" method="POST" autocomplete="off">
        <?= $this->csrf_field() ?>
        <div class="form-group">
          <label class="form-label" for="admin-email">Email Address</label>
          <div class="form-input-wrapper">
            <i data-lucide="mail" class="form-input-icon"></i>
            <input 
              type="email" 
              id="admin-email" 
              name="email" 
              class="form-control" 
              placeholder="admin@eisen.com" 
              required 
              autocomplete="email"
            >
          </div>
        </div>

        <div class="form-group">
          <label class="form-label" for="admin-password">Password</label>
          <div class="form-input-wrapper">
            <i data-lucide="lock" class="form-input-icon"></i>
            <input 
              type="password" 
              id="admin-password" 
              name="password" 
              class="form-control" 
              placeholder="••••••••" 
              required
              autocomplete="current-password"
            >
          </div>
        </div>

        <button type="submit" class="btn-submit">
          <span>Sign In</span>
          <i data-lucide="arrow-right" style="width: 16px; height: 16px;"></i>
        </button>
      </form>
    </div>

    <div class="back-link-wrapper">
      <a href="<?= BASE_URL ?>/" class="back-link">
        <i data-lucide="arrow-left" style="width: 14px; height: 14px;"></i>
        <span>Back to Marketplace</span>
      </a>
    </div>
  </div>

  <script>
    // Initialize Lucide Icons
    lucide.createIcons();
  </script>
</body>
</html>
