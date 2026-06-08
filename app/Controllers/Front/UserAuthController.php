<?php
namespace App\Controllers\Front;

use App\Core\Controller;
use App\Core\Session;
use App\Core\Database;
use App\Helpers\Mailer;

class UserAuthController extends Controller {

    public function showLoginForm() {
        // If already logged in, redirect to home
        if (Session::get('is_logged_in') === true) {
            $this->redirect('/');
        }

        $flash = Session::getFlash();
        $activeTab = ($_GET['tab'] ?? '') === 'signup' ? 'signup' : 'login';

        // Pass signup progress states to view
        $signupEmail = Session::get('signup_email');
        $signupStep = 'email';
        if ($signupEmail) {
            if (Session::get('signup_otp_verified') === true) {
                $signupStep = 'complete';
            } else {
                $signupStep = 'otp';
            }
        }

        $this->view('front/login', [
            'flash' => $flash,
            'activeTab' => $activeTab,
            'signupStep' => $signupStep,
            'signupEmail' => $signupEmail,
            'googleName' => Session::get('google_signup_name') ?? '',
        ]);
    }

    public function login() {
        try {
            $this->validateCsrf();
        } catch (\Exception $e) {
            Session::setFlash('error', 'CSRF token validation failed. Please try again.');
            $this->redirect('/login');
            return;
        }

        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($email === '' || $password === '') {
            Session::setFlash('error', 'Please fill in all required fields.');
            $this->redirect('/login');
            return;
        }

        // Rate limiting: max 10 login attempts per 60 seconds
        $rateLimitKey = 'login_' . md5($_SERVER['REMOTE_ADDR'] ?? 'unknown');
        if (!$this->checkRateLimit($rateLimitKey, 10, 60)) {
            Session::setFlash('error', 'Too many login attempts. Please wait a minute and try again.');
            $this->redirect('/login');
            return;
        }

        try {
            $db = Database::getConnection();
            $stmt = $db->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if (!$user || !password_verify($password, $user['password'])) {
                Session::setFlash('error', 'Invalid email or password.');
                $this->redirect('/login');
                return;
            }

            // Verify user has customer/buyer permissions
            if ($user['role'] !== 'registered_buyer') {
                Session::setFlash('error', 'Staff must sign in through the Admin Control Room.');
                $this->redirect('/login');
                return;
            }

            // Harden session: regenerate ID and CSRF token to prevent fixation
            Session::regenerateId();
            Session::regenerateCsrfToken();

            Session::set('is_logged_in', true);
            Session::set('user_id', $user['id']);
            Session::set('user_role', $user['role']);
            Session::set('user_name', $user['name']);
            Session::set('user_email', $user['email']);

            Session::setFlash('success', 'Welcome back, ' . $user['name'] . '!');
            $this->redirect('/');
        } catch (\Exception $e) {
            error_log('Login error: ' . $e->getMessage());
            Session::setFlash('error', 'An error occurred during sign in. Please try again.');
            $this->redirect('/login');
        }
    }

    public function sendOtp() {
        try {
            $this->validateCsrf();
        } catch (\Exception $e) {
            return $this->jsonResponse(['status' => 'error', 'message' => 'CSRF token validation failed.'], 403);
        }

        $email = trim($_POST['email'] ?? '');

        if ($email === '') {
            return $this->jsonResponse(['status' => 'error', 'message' => 'Email address is required.'], 400);
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->jsonResponse(['status' => 'error', 'message' => 'Please enter a valid email address.'], 400);
        }

        // Rate limiting: max 3 OTP sends per 60 seconds
        $rateLimitKey = 'otp_' . md5($_SERVER['REMOTE_ADDR'] ?? 'unknown');
        if (!$this->checkRateLimit($rateLimitKey, 3, 60)) {
            return $this->jsonResponse(['status' => 'error', 'message' => 'Too many requests. Please wait before requesting another code.'], 429);
        }

        try {
            $db = Database::getConnection();
            
            // Check if email already registered
            $stmt = $db->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                return $this->jsonResponse(['status' => 'error', 'message' => 'This email is already registered. Please sign in instead.'], 400);
            }

            // Generate 6-digit random code
            $otp = str_pad(random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
            
            // Store details in session (expires in 10 minutes)
            Session::set('signup_email', $email);
            Session::set('signup_otp', $otp);
            Session::set('signup_otp_expires', time() + 600);
            Session::set('signup_otp_verified', false);
            Session::remove('google_signup_name'); // Clear if any previous state

            // Send via PHPMailer
            $sent = Mailer::sendOTP($email, $otp);
            if (!$sent) {
                return $this->jsonResponse(['status' => 'error', 'message' => 'Failed to send verification email. Please try again later.'], 500);
            }

            return $this->jsonResponse(['status' => 'success', 'message' => 'Verification code sent successfully to ' . htmlspecialchars($email)]);
        } catch (\Exception $e) {
            error_log('sendOtp error: ' . $e->getMessage());
            return $this->jsonResponse(['status' => 'error', 'message' => 'A server error occurred. Please try again later.'], 500);
        }
    }

    public function verifyOtp() {
        try {
            $this->validateCsrf();
        } catch (\Exception $e) {
            return $this->jsonResponse(['status' => 'error', 'message' => 'CSRF token validation failed.'], 403);
        }

        $otp = trim($_POST['otp'] ?? '');
        $storedOtp = Session::get('signup_otp');
        $expires = Session::get('signup_otp_expires');

        if ($otp === '') {
            return $this->jsonResponse(['status' => 'error', 'message' => 'Verification code is required.'], 400);
        }

        if (!$storedOtp || !$expires) {
            return $this->jsonResponse(['status' => 'error', 'message' => 'No active verification session. Please enter your email first.'], 400);
        }

        if (time() > $expires) {
            return $this->jsonResponse(['status' => 'error', 'message' => 'Verification code has expired. Please request a new one.'], 400);
        }

        if ($otp !== $storedOtp) {
            return $this->jsonResponse(['status' => 'error', 'message' => 'Invalid verification code. Please check your email and try again.'], 400);
        }

        // Mark as verified
        Session::set('signup_otp_verified', true);

        return $this->jsonResponse(['status' => 'success', 'message' => 'OTP verified successfully.']);
    }

    public function completeSignup() {
        try {
            $this->validateCsrf();
        } catch (\Exception $e) {
            Session::setFlash('error', 'CSRF token validation failed. Please try again.');
            $this->redirect('/login?tab=signup');
            return;
        }

        if (Session::get('signup_otp_verified') !== true) {
            Session::setFlash('error', 'Please verify your email address first.');
            $this->redirect('/login?tab=signup');
            return;
        }

        $email = Session::get('signup_email');
        $name = trim($_POST['name'] ?? '');
        $country = trim($_POST['country'] ?? '');
        $accountType = trim($_POST['account_type'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $whatsapp = trim($_POST['whatsapp'] ?? '');
        $password = $_POST['password'] ?? '';
        $asfConfirmed = isset($_POST['asf_confirmed']) && $_POST['asf_confirmed'] == '1';
        $newsletterSubscribed = isset($_POST['newsletter']) && $_POST['newsletter'] == '1';

        if ($email === '' || $name === '' || $country === '' || $accountType === '' || $phone === '' || $password === '' || !$asfConfirmed) {
            Session::setFlash('error', 'Please fill in all required fields and accept the terms.');
            $this->redirect('/login?tab=signup');
            return;
        }

        // Validate lengths and formats
        if (mb_strlen($name) > 100) {
            Session::setFlash('error', 'Full name must not exceed 100 characters.');
            $this->redirect('/login?tab=signup');
            return;
        }
        if (mb_strlen($country) > 50) {
            Session::setFlash('error', 'Country name must not exceed 50 characters.');
            $this->redirect('/login?tab=signup');
            return;
        }
        if (mb_strlen($phone) > 25 || mb_strlen($whatsapp) > 25) {
            Session::setFlash('error', 'Phone and WhatsApp numbers must not exceed 25 characters.');
            $this->redirect('/login?tab=signup');
            return;
        }
        if (!preg_match('/^[0-9+\-\s()]+$/', $phone) || ($whatsapp !== '' && !preg_match('/^[0-9+\-\s()]+$/', $whatsapp))) {
            Session::setFlash('error', 'Phone or WhatsApp contains invalid characters. Only digits, spaces, -, +, and parentheses are allowed.');
            $this->redirect('/login?tab=signup');
            return;
        }
        if (strlen($password) < 8) {
            Session::setFlash('error', 'Password must be at least 8 characters long.');
            $this->redirect('/login?tab=signup');
            return;
        }

        try {
            $db = Database::getConnection();
            
            // Double check email isn't registered already
            $stmt = $db->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                Session::setFlash('error', 'This email is already registered.');
                $this->redirect('/login');
                return;
            }

            // Create new user
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
            
            $stmt = $db->prepare("INSERT INTO users (name, email, password, role, account_type, phone, whatsapp, country, preferred_currency, asf_confirmed, newsletter_subscribed) VALUES (?, ?, ?, 'registered_buyer', ?, ?, ?, ?, 'USD', 1, ?)");
            $stmt->execute([
                $name,
                $email,
                $hashedPassword,
                $accountType,
                $phone,
                $whatsapp !== '' ? $whatsapp : $phone,
                $country,
                $newsletterSubscribed ? 1 : 0
            ]);

            $newUserId = $db->lastInsertId();

            // Harden session: regenerate ID and CSRF token to prevent fixation
            Session::regenerateId();
            Session::regenerateCsrfToken();

            // Set login session
            Session::set('is_logged_in', true);
            Session::set('user_id', $newUserId);
            Session::set('user_role', 'registered_buyer');
            Session::set('user_name', $name);
            Session::set('user_email', $email);

            // Clean up session signup variables
            Session::remove('signup_email');
            Session::remove('signup_otp');
            Session::remove('signup_otp_expires');
            Session::remove('signup_otp_verified');
            Session::remove('google_signup_name');

            Session::setFlash('success', 'Account created successfully! Welcome to Eisen Corporation.');
            $this->redirect('/');
        } catch (\Exception $e) {
            error_log('completeSignup error: ' . $e->getMessage());
            Session::setFlash('error', 'An error occurred during account creation. Please try again.');
            $this->redirect('/login?tab=signup');
        }
    }

    public function googleLogin() {
        try {
            // Generate random state token for security verification (CSRF mitigation)
            $state = bin2hex(random_bytes(16));
            Session::set('oauth_state', $state);

            // Construct Google Authorization URL parameters
            $params = [
                'client_id' => GOOGLE_CLIENT_ID,
                'redirect_uri' => GOOGLE_REDIRECT_URI,
                'response_type' => 'code',
                'scope' => 'openid email profile',
                'state' => $state,
                'prompt' => 'select_account'
            ];

            $authUrl = 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query($params);
            
            // Redirect user to Google OAuth screen
            header('Location: ' . $authUrl);
            exit;
        } catch (\Exception $e) {
            Session::setFlash('error', 'Google Sign-in initialisation failed: ' . $e->getMessage());
            $this->redirect('/login');
        }
    }

    public function googleCallback() {
        $code = $_GET['code'] ?? '';
        $state = $_GET['state'] ?? '';
        $storedState = Session::get('oauth_state');

        if (empty($code) || empty($state) || $state !== $storedState) {
            Session::setFlash('error', 'Google Sign-in validation failed (Invalid State).');
            $this->redirect('/login');
            return;
        }

        try {
            // Exchange authorization code for an Access Token
            $tokenUrl = 'https://oauth2.googleapis.com/token';
            $postFields = [
                'code' => $code,
                'client_id' => GOOGLE_CLIENT_ID,
                'client_secret' => GOOGLE_CLIENT_SECRET,
                'redirect_uri' => GOOGLE_REDIRECT_URI,
                'grant_type' => 'authorization_code'
            ];

            $ch = curl_init($tokenUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postFields));
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
            $response = curl_exec($ch);
            $curlError = curl_error($ch);
            curl_close($ch);

            $tokenData = json_decode($response, true);
            if (empty($tokenData['access_token'])) {
                $err = $tokenData['error_description'] ?? ($curlError !== '' ? 'cURL error' : 'No token returned');
                throw new \Exception($err);
            }

            // Request User Info profile details using the Access Token
            $userInfoUrl = 'https://www.googleapis.com/oauth2/v3/userinfo';
            $ch = curl_init($userInfoUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Authorization: Bearer ' . $tokenData['access_token'],
            ]);
            $userInfoResponse = curl_exec($ch);
            curl_close($ch);

            $userInfo = json_decode($userInfoResponse, true);
            if (empty($userInfo['email'])) {
                throw new \Exception("Could not retrieve email address from profile.");
            }

            $email = trim($userInfo['email']);
            $name = trim($userInfo['name'] ?? $email);

            // Connect to database to log in or register the user dynamically
            $db = Database::getConnection();
            $stmt = $db->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if (!$user) {
                // Google user is signing up for the first time
                // Bypass OTP verification since Google already authenticated their email address
                Session::set('signup_email', $email);
                Session::set('google_signup_name', $name);
                Session::set('signup_otp_verified', true); // Bypass Step 2 (OTP)
                
                Session::remove('oauth_state');
                Session::setFlash('success', 'Email authenticated via Google. Please complete your registration details below.');
                $this->redirect('/login?tab=signup');
                return;
            }

            // Harden session: regenerate ID and CSRF token to prevent fixation
            Session::regenerateId();
            Session::regenerateCsrfToken();

            // Establish the session
            Session::set('is_logged_in', true);
            Session::set('user_id', $user['id']);
            Session::set('user_role', $user['role']);
            Session::set('user_name', $user['name']);
            Session::set('user_email', $user['email']);
            Session::remove('oauth_state');

            // Route appropriately
            if (in_array($user['role'], ['admin', 'finance_officer', 'caller_agent'])) {
                Session::setFlash('success', 'Logged in as Control Room staff: ' . $user['name']);
                $this->redirect('/admin');
            } else {
                Session::setFlash('success', 'Successfully logged in with Google: ' . $user['name']);
                $this->redirect('/');
            }
        } catch (\Exception $e) {
            error_log('Google OAuth error: ' . $e->getMessage());
            Session::setFlash('error', 'Google authentication failed. Please try again.');
            $this->redirect('/login');
        }
    }

    public function showForgotPasswordForm() {
        if (Session::get('is_logged_in') === true) {
            $this->redirect('/');
            return;
        }

        $flash = Session::getFlash();
        $this->view('front/forgot-password', [
            'flash' => $flash,
        ]);
    }

    public function sendForgotPassword() {
        try {
            $this->validateCsrf();
        } catch (\Exception $e) {
            Session::setFlash('error', 'CSRF token validation failed. Please try again.');
            $this->redirect('/forgot-password');
            return;
        }

        $email = trim($_POST['email'] ?? '');

        if ($email === '') {
            Session::setFlash('error', 'Please enter your email address.');
            $this->redirect('/forgot-password');
            return;
        }

        // Rate limiting: max 3 requests per 5 minutes (300 seconds)
        $rateLimitKey = 'forgot_' . md5($_SERVER['REMOTE_ADDR'] ?? 'unknown');
        if (!$this->checkRateLimit($rateLimitKey, 3, 300)) {
            Session::setFlash('error', 'Too many requests. Please wait a few minutes before trying again.');
            $this->redirect('/forgot-password');
            return;
        }

        try {
            $db = Database::getConnection();
            $stmt = $db->prepare("SELECT id, role FROM users WHERE email = ? LIMIT 1");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if (!$user) {
                Session::setFlash('success', 'If an account exists with that email, a reset link has been sent.');
                $this->redirect('/forgot-password');
                return;
            }

            // Only proceed if user is a registered buyer (not admin/staff)
            if ($user['role'] !== 'registered_buyer') {
                Session::setFlash('success', 'If an account exists with that email, a reset link has been sent.');
                $this->redirect('/forgot-password');
                return;
            }

            $token = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', time() + 3600); // 1 hour expiry

            $upd = $db->prepare("UPDATE users SET reset_token = ?, reset_token_expires = ? WHERE id = ?");
            $upd->execute([$token, $expires, $user['id']]);

            Mailer::sendPasswordReset($email, BASE_URL . '/reset-password?token=' . urlencode($token));

            Session::setFlash('success', 'Reset link has been successfully sent to your email.');
            $this->redirect('/forgot-password');
            return;
        } catch (\Exception $e) {
            error_log('User Forgot password error: ' . $e->getMessage());
            Session::setFlash('error', 'An error occurred while processing your request. Please try again.');
            $this->redirect('/forgot-password');
            return;
        }
    }

    public function showResetPasswordForm() {
        $token = trim($_GET['token'] ?? '');
        $flash = Session::getFlash();

        if ($token === '') {
            $this->redirect('/forgot-password');
            return;
        }

        // Validate token exists and is not expired
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare("SELECT id FROM users WHERE reset_token = ? AND reset_token_expires > NOW() LIMIT 1");
            $stmt->execute([$token]);
            $user = $stmt->fetch();

            if (!$user) {
                Session::setFlash('error', 'This password reset link is invalid or has expired.');
                $this->redirect('/forgot-password');
                return;
            }
        } catch (\Exception $e) {
            error_log('User Reset password validation error: ' . $e->getMessage());
            $this->redirect('/forgot-password');
            return;
        }

        $this->view('front/reset-password', [
            'flash' => $flash,
            'token' => htmlspecialchars($token),
        ]);
    }

    public function resetPassword() {
        $token = trim($_POST['token'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if ($token === '' || $password === '' || $confirmPassword === '') {
            Session::setFlash('error', 'All fields are required.');
            $this->redirect('/reset-password?token=' . urlencode($token));
            return;
        }

        try {
            $this->validateCsrf();
        } catch (\Exception $e) {
            Session::setFlash('error', 'CSRF token validation failed. Please try again.');
            $this->redirect('/reset-password?token=' . urlencode($token));
            return;
        }

        if ($password !== $confirmPassword) {
            Session::setFlash('error', 'Passwords do not match.');
            $this->redirect('/reset-password?token=' . urlencode($token));
            return;
        }

        if (strlen($password) < 8) {
            Session::setFlash('error', 'Password must be at least 8 characters long.');
            $this->redirect('/reset-password?token=' . urlencode($token));
            return;
        }

        try {
            $db = Database::getConnection();
            $stmt = $db->prepare("SELECT id FROM users WHERE reset_token = ? AND reset_token_expires > NOW() LIMIT 1");
            $stmt->execute([$token]);
            $user = $stmt->fetch();

            if (!$user) {
                Session::setFlash('error', 'This password reset link is invalid or has expired.');
                $this->redirect('/forgot-password');
                return;
            }

            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
            $upd = $db->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_token_expires = NULL WHERE id = ?");
            $upd->execute([$hashedPassword, $user['id']]);

            Session::setFlash('success', 'Password reset successfully! You can now sign in with your new password.');
            $this->redirect('/login');
        } catch (\Exception $e) {
            error_log('User Reset password error: ' . $e->getMessage());
            Session::setFlash('error', 'An error occurred. Please try again.');
            $this->redirect('/reset-password?token=' . urlencode($token));
        }
    }

    public function logout() {
        Session::destroy();
        $this->redirect('/login');
    }
}
