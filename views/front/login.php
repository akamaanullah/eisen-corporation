<?php
$activeTab = ($activeTab ?? 'login') === 'signup' ? 'signup' : 'login';
$signupStep = $signupStep ?? 'email'; // 'email', 'otp', 'complete'
$signupEmail = $signupEmail ?? '';
$googleName = $googleName ?? '';
?>
<?php include dirname(__DIR__) . '/front/partials/header.php'; ?>

<style>
  /* Premium Signup & Login Layout styling */
  .auth-grid {
    display: grid;
    grid-template-columns: 1.2fr 1fr;
    gap: 40px;
    margin: 40px 0;
    align-items: start;
  }

  @media (max-width: 992px) {
    .auth-grid {
      grid-template-columns: 1fr;
      gap: 30px;
    }
  }

  .auth-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 35px;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.03);
  }

  .auth-info-panel {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 35px;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.03);
  }

  .auth-info-title {
    font-family: 'Montserrat', sans-serif;
    color: #050d1a;
    font-size: 20px;
    font-weight: 700;
    margin-bottom: 12px;
    border-left: 4px solid #c9a227;
    padding-left: 10px;
  }

  .auth-info-text {
    color: #4a5568;
    font-size: 14px;
    line-height: 1.6;
    margin-bottom: 25px;
  }

  /* Comparison Table */
  .comparison-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 15px;
    font-size: 13.5px;
  }

  .comparison-table th {
    background-color: #f7fafc;
    color: #050d1a;
    font-weight: 600;
    text-align: left;
    padding: 12px 10px;
    border-bottom: 2px solid #edf2f7;
  }

  .comparison-table td {
    padding: 12px 10px;
    border-bottom: 1px solid #edf2f7;
    color: #4a5568;
  }

  .comparison-table tr:hover td {
    background-color: #f8fafc;
  }

  .check-icon {
    color: #38a169;
    font-weight: bold;
  }

  .cross-icon {
    color: #e53e3e;
  }

  /* Multi-step Signup Panels */
  .signup-step-panel {
    display: none;
    animation: fadeIn 0.4s ease-out;
  }

  .signup-step-panel.is-active {
    display: block;
  }

  @keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
  }

  .asf-box {
    background: #f7fafc;
    border: 1px solid #cbd5e0;
    padding: 12px;
    border-radius: 6px;
    height: 100px;
    overflow-y: scroll;
    font-size: 11px;
    color: #718096;
    margin-bottom: 15px;
    line-height: 1.5;
  }

  .form-control--disabled {
    background-color: #edf2f7;
    cursor: not-allowed;
  }

  .otp-digit-wrapper {
    display: flex;
    justify-content: center;
    gap: 15px;
    margin: 25px 0;
  }

  .otp-input-field {
    width: 100%;
    max-width: 250px;
    letter-spacing: 12px;
    font-size: 24px;
    text-align: center;
    font-weight: 700;
    font-family: monospace;
    border-radius: 6px;
    border: 1px solid #cbd5e0;
    padding: 10px;
  }

  .form-row {
    display: grid;
    grid-template-columns: 1fr 1.5fr;
    gap: 15px;
  }

  @media (max-width: 576px) {
    .form-row {
      grid-template-columns: 1fr;
      gap: 10px;
    }
  }

  .error-alert {
    background-color: #fff5f5;
    border: 1px solid #feb2b2;
    color: #c53030;
    padding: 12px;
    border-radius: 6px;
    font-size: 13.5px;
    margin-bottom: 20px;
    display: none;
  }

  .success-alert {
    background-color: #f0fff4;
    border: 1px solid #c6f6d5;
    color: #22543d;
    padding: 12px;
    border-radius: 6px;
    font-size: 13.5px;
    margin-bottom: 20px;
    display: none;
  }
</style>

<main id="main" class="login-page">
  <div class="container">
    <div class="auth-grid">
      
      <!-- Left Column: Authentication Card -->
      <div class="auth-card">
        <div class="login-card__head">
          <h1 id="login-page-title" class="login-card__title">Welcome</h1>
          <p class="login-card__text">Sign in or create a customer account to continue.</p>
        </div>

        <div class="login-tabs" role="tablist" aria-label="Authentication">
          <button
            type="button"
            class="login-tabs__btn<?= $activeTab === 'login' ? ' is-active' : '' ?>"
            role="tab"
            id="login-tab-login"
            aria-selected="<?= $activeTab === 'login' ? 'true' : 'false' ?>"
            aria-controls="login-panel-login"
            data-login-tab="login"
          >Login</button>
          <button
            type="button"
            class="login-tabs__btn<?= $activeTab === 'signup' ? ' is-active' : '' ?>"
            role="tab"
            id="login-tab-signup"
            aria-selected="<?= $activeTab === 'signup' ? 'true' : 'false' ?>"
            aria-controls="login-panel-signup"
            data-login-tab="signup"
          >Sign up</button>
        </div>

        <!-- Global Alert Display -->
        <div id="auth-error-alert" class="error-alert"></div>
        <div id="auth-success-alert" class="success-alert"></div>
        
        <?php if (isset($flash) && $flash): ?>
          <div class="login-flash" role="alert" style="margin-bottom: 20px;">
            <?= htmlspecialchars($flash['message']) ?>
          </div>
        <?php endif; ?>

        <!-- LOGIN PANEL -->
        <div
          class="login-panel<?= $activeTab === 'login' ? ' is-active' : '' ?>"
          id="login-panel-login"
          role="tabpanel"
          aria-labelledby="login-tab-login"
          data-login-panel="login"
          <?= $activeTab !== 'login' ? 'hidden' : '' ?>
        >
          <form class="login-form" action="<?= BASE_URL ?>/login" method="POST" novalidate>
            <?= $this->csrf_field() ?>
            <div class="form-field">
              <label class="form-label" for="login-email">Email</label>
              <input
                class="form-control form-control--text"
                type="email"
                id="login-email"
                name="email"
                placeholder="you@example.com"
                required
                autocomplete="email"
              />
            </div>

            <div class="form-field">
              <div class="login-form__label-row">
                <label class="form-label" for="login-password">Password</label>
                <a class="login-forgot" href="<?= BASE_URL ?>/forgot-password">Forgot password?</a>
              </div>
              <input
                class="form-control form-control--text"
                type="password"
                id="login-password"
                name="password"
                placeholder="Enter your password"
                required
                autocomplete="current-password"
              />
            </div>

            <button class="btn btn--primary btn--block login-form__submit" type="submit">Sign in</button>
          </form>
        </div>

        <!-- SIGNUP PANEL (Multi-step flow) -->
        <div
          class="login-panel<?= $activeTab === 'signup' ? ' is-active' : '' ?>"
          id="login-panel-signup"
          role="tabpanel"
          aria-labelledby="login-tab-signup"
          data-login-panel="signup"
          <?= $activeTab !== 'signup' ? 'hidden' : '' ?>
        >
          
          <!-- STEP 1: Enter Email -->
          <div id="signup-step-email" class="signup-step-panel <?= $signupStep === 'email' ? 'is-active' : '' ?>">
            <form id="form-send-otp" novalidate>
              <div class="form-field">
                <label class="form-label" for="signup-email">Email *</label>
                <input
                  class="form-control form-control--text"
                  type="email"
                  id="signup-email"
                  name="email"
                  value="<?= htmlspecialchars($signupEmail) ?>"
                  placeholder="Enter your email"
                  required
                />
              </div>

              <button class="btn btn--primary btn--block login-form__submit" type="submit" id="btn-send-otp">
                Continue with email
              </button>
            </form>
          </div>

          <!-- STEP 2: Enter OTP Verification Code -->
          <div id="signup-step-otp" class="signup-step-panel <?= $signupStep === 'otp' ? 'is-active' : '' ?>">
            <div style="text-align: center; margin-bottom: 20px;">
              <p style="font-size: 14px; color: #4a5568;">
                We sent a 6-digit verification code to <strong id="otp-target-email"><?= htmlspecialchars($signupEmail) ?></strong>. Please check your inbox.
              </p>
            </div>
            
            <form id="form-verify-otp" novalidate>
              <div class="form-field" style="text-align: center;">
                <label class="form-label" for="otp-code">Verification Code</label>
                <input
                  class="otp-input-field"
                  type="text"
                  id="otp-code"
                  name="otp"
                  placeholder="••••••"
                  maxlength="6"
                  required
                  autocomplete="off"
                />
              </div>

              <button class="btn btn--primary btn--block login-form__submit" type="submit" id="btn-verify-otp" style="margin-top: 15px;">
                Verify Code
              </button>
              
              <button type="button" class="btn btn--link btn--block" id="btn-resend-otp" style="margin-top: 10px; font-size: 13px; color: #718096; text-decoration: underline; background: none; border: none; cursor: pointer;">
                Resend verification email
              </button>
            </form>
          </div>

          <!-- STEP 3: Complete Account details -->
          <div id="signup-step-complete" class="signup-step-panel <?= $signupStep === 'complete' ? 'is-active' : '' ?>">
            <div style="margin-bottom: 20px; border-bottom: 1px solid #edf2f7; padding-bottom: 10px;">
              <h2 style="font-size: 16px; color: #050d1a; font-weight: 600; margin: 0;">Create an account</h2>
              <p style="font-size: 13px; color: #718096; margin: 5px 0 0 0;">Please enter your details to register.</p>
            </div>

            <form action="<?= BASE_URL ?>/signup/complete" method="POST" novalidate id="form-complete-signup">
              <?= $this->csrf_field() ?>
              <div class="form-field">
                <label class="form-label" for="reg-email">Email</label>
                <input
                  class="form-control form-control--text form-control--disabled"
                  type="email"
                  id="reg-email"
                  value="<?= htmlspecialchars($signupEmail) ?>"
                  disabled
                />
              </div>

              <div class="form-field">
                <label class="form-label" for="reg-name">Your Name *</label>
                <input
                  class="form-control form-control--text"
                  type="text"
                  id="reg-name"
                  name="name"
                  value="<?= htmlspecialchars($googleName) ?>"
                  placeholder="Enter your name"
                  required
                />
              </div>

              <div class="form-field">
                <label class="form-label" for="reg-country">Destination Country *</label>
                <select class="form-control" id="reg-country" name="country" required style="width: 100%; padding: 10px; border-radius: 6px; border: 1px solid #cbd5e0;">
                  <option value="" disabled selected>Select destination country</option>
                  <option value="PAKISTAN">Pakistan</option>
                  <option value="KENYA">Kenya</option>
                  <option value="UNITED ARAB EMIRATES">United Arab Emirates</option>
                  <option value="TANZANIA">Tanzania</option>
                  <option value="UGANDA">Uganda</option>
                  <option value="ZAMBIA">Zambia</option>
                  <option value="JAPAN">Japan</option>
                  <option value="UNITED KINGDOM">United Kingdom</option>
                </select>
              </div>

              <div class="form-field">
                <label class="form-label" for="reg-type">Account Type *</label>
                <select class="form-control" id="reg-type" name="account_type" required style="width: 100%; padding: 10px; border-radius: 6px; border: 1px solid #cbd5e0;">
                  <option value="Individual Buyer" selected>Individual Buyer</option>
                  <option value="Corporate Buyer">Corporate Buyer</option>
                </select>
              </div>

              <div class="form-field">
                <label class="form-label" for="reg-phone">Phone Number *</label>
                <div class="form-row">
                  <select class="form-control" id="reg-phone-code" style="width: 100%; padding: 10px; border-radius: 6px; border: 1px solid #cbd5e0;">
                    <option value="+92" selected>Pakistan (+92)</option>
                    <option value="+254">Kenya (+254)</option>
                    <option value="+971">UAE (+971)</option>
                    <option value="+81">Japan (+81)</option>
                    <option value="+255">Tanzania (+255)</option>
                    <option value="+44">UK (+44)</option>
                  </select>
                  <input
                    class="form-control form-control--text"
                    type="tel"
                    id="reg-phone"
                    placeholder="Enter phone number"
                    required
                  />
                </div>
                <input type="hidden" name="phone" id="hidden-phone">
              </div>

              <div class="form-field">
                <div class="form-label-row" style="display: flex; align-items: center; gap: 8px; margin-bottom: 6px;">
                  <input type="checkbox" id="whatsapp-same" checked value="1" style="width: 15px; height: 15px; cursor: pointer;">
                  <label for="whatsapp-same" style="font-size: 13px; color: #4a5568; cursor: pointer;">The WhatsApp number is the same.</label>
                </div>
                
                <div id="whatsapp-input-group" style="display: none;">
                  <label class="form-label" for="reg-whatsapp">WhatsApp Number</label>
                  <input
                    class="form-control form-control--text"
                    type="tel"
                    id="reg-whatsapp"
                    placeholder="Enter WhatsApp number"
                  />
                </div>
                <input type="hidden" name="whatsapp" id="hidden-whatsapp">
              </div>

              <div class="form-field">
                <label class="form-label" for="reg-password">Password *</label>
                <input
                  class="form-control form-control--text"
                  type="password"
                  id="reg-password"
                  name="password"
                  placeholder="Enter your password (min. 8 chars)"
                  required
                  minlength="8"
                />
              </div>

              <!-- ASF Policy Box -->
              <div class="form-field">
                <label class="form-label">Basic policy against Anti-Social Forces *</label>
                <div class="asf-box">
                  EISEN CO., LTD. declares the following basic policy in order to prevent damages caused by groups or individuals (so-called "Anti-Social Forces (ASF)") that pursue economic benefit by making full use of violence, force and fraudulent means.
                  <br><br>
                  1. Response as an organization:
                  We will stand against any demands or actions from ASF as a complete organization, ensuring safety of our employees.
                  <br><br>
                  2. Cooperation with outside agencies:
                  We maintain close contact with legal counsels, police departments, and specialized centers to protect our customer registry and transaction integrity.
                  <br><br>
                  3. Complete ban of transactions:
                  We will never form alliances or execute business deals with any individuals associated with ASF.
                </div>
                <div style="display: flex; align-items: flex-start; gap: 8px;">
                  <input type="checkbox" id="reg-asf" name="asf_confirmed" value="1" required style="width: 15px; height: 15px; margin-top: 2px; cursor: pointer;">
                  <label for="reg-asf" style="font-size: 12.5px; color: #4a5568; cursor: pointer;">I confirm that I am not a member of, or involved with any individuals or groups that would be classified as ASF.</label>
                </div>
              </div>

              <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 25px;">
                <input type="checkbox" id="reg-newsletter" name="newsletter" value="1" checked style="width: 15px; height: 15px; cursor: pointer;">
                <label for="reg-newsletter" style="font-size: 13px; color: #4a5568; cursor: pointer;">Receive special offers from sbtjapan.com in our newsletter.</label>
              </div>

              <button class="btn btn--primary btn--block login-form__submit" type="submit">Create account</button>
            </form>
          </div>

        </div>

        <div class="login-divider" aria-hidden="true"><span>or</span></div>

        <a class="login-google" href="<?= BASE_URL ?>/auth/google">
          <svg class="login-google__icon" width="18" height="18" viewBox="0 0 18 18" aria-hidden="true">
            <path fill="#4285F4" d="M17.64 9.2c0-.637-.057-1.251-.164-1.84H9v3.481h4.844a4.14 4.14 0 0 1-1.796 2.716v2.259h2.908c1.702-1.567 2.684-3.875 2.684-6.616z" />
            <path fill="#34A853" d="M9 18c2.43 0 4.467-.806 5.956-2.18l-2.908-2.259c-.806.54-1.837.86-3.048.86-2.344 0-4.328-1.584-5.036-3.711H.957v2.332A8.997 8.997 0 0 0 9 18z" />
            <path fill="#FBBC05" d="M3.964 10.71A5.41 5.41 0 0 1 3.682 9c0-.593.102-1.17.282-1.71V4.958H.957A8.996 8.996 0 0 0 0 9c0 1.452.348 2.827.957 4.042l3.007-2.332z" />
            <path fill="#EA4335" d="M9 3.58c1.321 0 2.508.454 3.44 1.345l2.582-2.58C13.463.891 11.426 0 9 0A8.997 8.997 0 0 0 .957 4.958L3.964 7.29C4.672 5.163 6.656 3.58 9 3.58z" />
          </svg>
          <span>Continue with Google</span>
        </a>
      </div>

      <!-- Right Column: Info Panel -->
      <div class="auth-info-panel">
        <h2 class="auth-info-title">Why Sign Up?</h2>
        <p class="auth-info-text">
          Signing up gives you access to premium tools that help you save money, track available auction inventory, check chassis histories, and connect directly with our specialized export agents.
        </p>

        <h3 style="font-size: 15px; color: #050d1a; font-weight: 600; margin-bottom: 12px;">Comparison: Non-member vs Member</h3>
        <table class="comparison-table">
          <thead>
            <tr>
              <th>Benefit Options</th>
              <th style="width: 80px; text-align: center;">Guest</th>
              <th style="width: 80px; text-align: center;">Member</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>Get vehicle FOB cost estimates</td>
              <td style="text-align: center;" class="check-icon">&#10003;</td>
              <td style="text-align: center;" class="check-icon">&#10003;</td>
            </tr>
            <tr>
              <td>Add favorite items to stock watch</td>
              <td style="text-align: center;" class="check-icon">&#10003;</td>
              <td style="text-align: center;" class="check-icon">&#10003;</td>
            </tr>
            <tr>
              <td>Get discount alerts on favorite stock</td>
              <td style="text-align: center;" class="cross-icon">&#10007;</td>
              <td style="text-align: center;" class="check-icon">&#10003;</td>
            </tr>
            <tr>
              <td>Submit vehicle reservation holds (24 hours)</td>
              <td style="text-align: center;" class="cross-icon">&#10007;</td>
              <td style="text-align: center;" class="check-icon">&#10003;</td>
            </tr>
            <tr>
              <td>Place bids on live auctions</td>
              <td style="text-align: center;" class="cross-icon">&#10007;</td>
              <td style="text-align: center;" class="check-icon">&#10003;</td>
            </tr>
            <tr>
              <td>Negotiate direct export shipping rates</td>
              <td style="text-align: center;" class="cross-icon">&#10007;</td>
              <td style="text-align: center;" class="check-icon">&#10003;</td>
            </tr>
            <tr>
              <td>Access statement ledger & tracking tools</td>
              <td style="text-align: center;" class="cross-icon">&#10007;</td>
              <td style="text-align: center;" class="check-icon">&#10003;</td>
            </tr>
          </tbody>
        </table>
      </div>

    </div>
  </div>
</main>

<script>
document.addEventListener("DOMContentLoaded", function () {
  const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute("content") || "";

  function otpFetchBody(params) {
    const body = new URLSearchParams(params);
    body.set("csrf_token", csrfToken);
    return body.toString();
  }

  function otpFetchHeaders() {
    return {
      "Content-Type": "application/x-www-form-urlencoded",
      "X-CSRF-TOKEN": csrfToken
    };
  }

  const formSendOtp = document.getElementById("form-send-otp");
  const formVerifyOtp = document.getElementById("form-verify-otp");
  const formCompleteSignup = document.getElementById("form-complete-signup");
  
  const stepEmail = document.getElementById("signup-step-email");
  const stepOtp = document.getElementById("signup-step-otp");
  const stepComplete = document.getElementById("signup-step-complete");
  
  const targetEmailSpan = document.getElementById("otp-target-email");
  const regEmailInput = document.getElementById("reg-email");
  
  const errorAlert = document.getElementById("auth-error-alert");
  const successAlert = document.getElementById("auth-success-alert");
  
  const whatsappSame = document.getElementById("whatsapp-same");
  const whatsappGroup = document.getElementById("whatsapp-input-group");
  
  const btnResendOtp = document.getElementById("btn-resend-otp");

  function showAlert(type, message) {
    if (type === "error") {
      errorAlert.textContent = message;
      errorAlert.style.display = "block";
      successAlert.style.display = "none";
    } else {
      successAlert.textContent = message;
      successAlert.style.display = "block";
      errorAlert.style.display = "none";
    }
    // Scroll to alert
    document.querySelector(".login-card__head").scrollIntoView({ behavior: 'smooth' });
  }

  function hideAlerts() {
    errorAlert.style.display = "none";
    successAlert.style.display = "none";
  }

  // Step 1: Send OTP
  if (formSendOtp) {
    formSendOtp.addEventListener("submit", function (e) {
      e.preventDefault();
      hideAlerts();
      
      const emailVal = document.getElementById("signup-email").value.trim();
      if (!emailVal) {
        showAlert("error", "Email address is required.");
        return;
      }

      const btn = document.getElementById("btn-send-otp");
      btn.disabled = true;
      btn.textContent = "Sending code...";

      fetch("<?= BASE_URL ?>/signup/send-otp", {
        method: "POST",
        headers: otpFetchHeaders(),
        body: otpFetchBody({ email: emailVal })
      })
      .then(res => res.json())
      .then(data => {
        btn.disabled = false;
        btn.textContent = "Continue with email";

        if (data.status === "success") {
          targetEmailSpan.textContent = emailVal;
          regEmailInput.value = emailVal;
          
          stepEmail.classList.remove("is-active");
          stepOtp.classList.add("is-active");
          showAlert("success", data.message);
        } else {
          showAlert("error", data.message);
        }
      })
      .catch(err => {
        btn.disabled = false;
        btn.textContent = "Continue with email";
        showAlert("error", "An error occurred. Please try again.");
      });
    });
  }

  // Step 2: Verify OTP
  if (formVerifyOtp) {
    formVerifyOtp.addEventListener("submit", function (e) {
      e.preventDefault();
      hideAlerts();

      const otpVal = document.getElementById("otp-code").value.trim();
      if (!otpVal || otpVal.length !== 6) {
        showAlert("error", "Please enter the 6-digit verification code.");
        return;
      }

      const btn = document.getElementById("btn-verify-otp");
      btn.disabled = true;
      btn.textContent = "Checking code...";

      fetch("<?= BASE_URL ?>/signup/verify-otp", {
        method: "POST",
        headers: otpFetchHeaders(),
        body: otpFetchBody({ otp: otpVal })
      })
      .then(res => res.json())
      .then(data => {
        btn.disabled = false;
        btn.textContent = "Verify Code";

        if (data.status === "success") {
          stepOtp.classList.remove("is-active");
          stepComplete.classList.add("is-active");
          showAlert("success", "Email verified. Please complete your profile registration.");
        } else {
          showAlert("error", data.message);
        }
      })
      .catch(err => {
        btn.disabled = false;
        btn.textContent = "Verify Code";
        showAlert("error", "Verification failed. Please try again.");
      });
    });

    // Resend OTP handler
    if (btnResendOtp) {
      btnResendOtp.addEventListener("click", function () {
        hideAlerts();
        const emailVal = targetEmailSpan.textContent.trim();
        
        btnResendOtp.disabled = true;
        btnResendOtp.textContent = "Resending...";

        fetch("<?= BASE_URL ?>/signup/send-otp", {
          method: "POST",
          headers: otpFetchHeaders(),
          body: otpFetchBody({ email: emailVal })
        })
        .then(res => res.json())
        .then(data => {
          btnResendOtp.disabled = false;
          btnResendOtp.textContent = "Resend verification email";
          if (data.status === "success") {
            showAlert("success", "A new verification code has been sent!");
          } else {
            showAlert("error", data.message);
          }
        })
        .catch(() => {
          btnResendOtp.disabled = false;
          btnResendOtp.textContent = "Resend verification email";
          showAlert("error", "Failed to resend code.");
        });
      });
    }
  }

  // Step 3: Handle Whatsapp Sync
  if (whatsappSame && whatsappGroup) {
    whatsappSame.addEventListener("change", function () {
      if (this.checked) {
        whatsappGroup.style.display = "none";
      } else {
        whatsappGroup.style.display = "block";
      }
    });
  }

  // Step 3: Form Complete Validation & Assembly
  if (formCompleteSignup) {
    formCompleteSignup.addEventListener("submit", function (e) {
      const name = document.getElementById("reg-name").value.trim();
      const country = document.getElementById("reg-country").value;
      const phoneVal = document.getElementById("reg-phone").value.trim();
      const phoneCode = document.getElementById("reg-phone-code").value;
      const pass = document.getElementById("reg-password").value;
      const asf = document.getElementById("reg-asf").checked;

      hideAlerts();

      if (!name || !country || !phoneVal || !pass || !asf) {
        e.preventDefault();
        showAlert("error", "Please fill in all required fields and accept the policies.");
        return;
      }

      if (pass.length < 8) {
        e.preventDefault();
        showAlert("error", "Password must be at least 8 characters long.");
        return;
      }

      // Compile phone number
      const fullPhone = phoneCode + " " + phoneVal;
      document.getElementById("hidden-phone").value = fullPhone;

      // Compile whatsapp number
      if (whatsappSame.checked) {
        document.getElementById("hidden-whatsapp").value = fullPhone;
      } else {
        const waVal = document.getElementById("reg-whatsapp").value.trim();
        if (waVal) {
          document.getElementById("hidden-whatsapp").value = phoneCode + " " + waVal;
        } else {
          document.getElementById("hidden-whatsapp").value = fullPhone;
        }
      }
    });
  }
});
</script>

  <script src="<?= BASE_URL ?>/public/js/login.js" defer></script>
<?php include dirname(__DIR__) . '/front/partials/footer.php'; ?>
