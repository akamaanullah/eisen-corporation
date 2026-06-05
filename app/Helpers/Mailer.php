<?php
namespace App\Helpers;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Mailer {
    
    /**
     * Send a 6-digit verification code to the recipient.
     * 
     * @param string $toEmail Recipient's email address
     * @param string $otp 6-digit numerical OTP code
     * @return bool True on success, False on failure
     */
    public static function sendOTP($toEmail, $otp) {
        $mail = new PHPMailer(true);
        try {
            // Server settings from config constants
            $mail->isSMTP();
            $mail->Host       = SMTP_HOST;
            $mail->SMTPAuth   = true;
            $mail->Username   = SMTP_USER;
            $mail->Password   = SMTP_PASS;
            $mail->SMTPSecure = (SMTP_SECURE === 'ssl') ? PHPMailer::ENCRYPTION_SMTPS : PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = SMTP_PORT;

            // Recipients
            $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
            $mail->addAddress($toEmail);

            // Content
            $mail->isHTML(true);
            $mail->Subject = 'Verify Your Email - Eisen Corporation';
            
            // Clean, premium light-themed email layout matching the frontend UI
            $mail->Body    = "
                <div style=\"font-family: 'Inter', 'Helvetica Neue', Helvetica, Arial, sans-serif; background-color: #f4f6f8; padding: 40px 20px; color: #2d3748;\">
                    <div style=\"max-width: 540px; margin: 0 auto; background-color: #ffffff; border-radius: 12px; border: 1px solid #e2e8f0; box-shadow: 0 4px 6px rgba(0,0,0,0.05); overflow: hidden;\">
                        
                        <!-- Header -->
                        <div style=\"background-color: #050d1a; padding: 30px; text-align: center; border-bottom: 3px solid #c9a227;\">
                            <h1 style=\"color: #ffffff; margin: 0; font-size: 22px; font-family: 'Montserrat', sans-serif; font-weight: 700; letter-spacing: 1px;\">
                                EISEN CORPORATION
                            </h1>
                            <p style=\"color: #a0aec0; margin: 5px 0 0 0; font-size: 13px; font-weight: 500; text-transform: uppercase; letter-spacing: 1.5px;\">
                                Premium Imported Vehicles
                            </p>
                        </div>
                        
                        <!-- Body -->
                        <div style=\"padding: 40px 30px;\">
                            <h2 style=\"margin-top: 0; margin-bottom: 20px; font-size: 18px; color: #050d1a; font-weight: 600;\">
                                Email Verification Code
                            </h2>
                            <p style=\"font-size: 15px; line-height: 1.6; color: #4a5568; margin-bottom: 30px;\">
                                Thank you for choosing Eisen Corporation. Please use the following 6-digit One-Time Password (OTP) to complete your account registration. This code is valid for 10 minutes.
                            </p>
                            
                            <!-- OTP Code Box -->
                            <div style=\"text-align: center; margin: 30px 0;\">
                                <div style=\"display: inline-block; font-size: 32px; font-weight: 700; color: #050d1a; background-color: #f7fafc; border: 2px dashed #cbd5e0; padding: 12px 35px; border-radius: 8px; letter-spacing: 6px; font-family: monospace;\">
                                    {$otp}
                                </div>
                            </div>
                            
                            <p style=\"font-size: 13px; line-height: 1.5; color: #718096; margin-top: 30px; border-top: 1px solid #edf2f7; padding-top: 20px;\">
                                If you did not request this verification code, please ignore this message. Your email address is safe.
                            </p>
                        </div>
                        
                        <!-- Footer -->
                        <div style=\"background-color: #f7fafc; padding: 20px; text-align: center; font-size: 12px; color: #a0aec0; border-top: 1px solid #edf2f7;\">
                            <p style=\"margin: 0 0 5px 0;\">&copy; " . date('Y') . " Eisen Corporation. All rights reserved.</p>
                            <p style=\"margin: 0;\">This is an automated system email. Please do not reply directly.</p>
                        </div>
                        
                    </div>
                </div>
            ";

            $mail->send();
            return true;
        } catch (Exception $e) {
            // Log mailer error
            error_log("PHPMailer Exception: " . $mail->ErrorInfo . " | Code: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send a password reset link to a user.
     *
     * @param string $toEmail   Recipient's email address
     * @param string $resetUrl  Full URL of the reset page (e.g. BASE_URL . '/reset-password?token=...')
     * @return bool True on success, False on failure
     */
    public static function sendPasswordReset($toEmail, $resetUrl) {
        $mail = new PHPMailer(true);
        try {
            // Server settings from config constants
            $mail->isSMTP();
            $mail->Host       = SMTP_HOST;
            $mail->SMTPAuth   = true;
            $mail->Username   = SMTP_USER;
            $mail->Password   = SMTP_PASS;
            $mail->SMTPSecure = (SMTP_SECURE === 'ssl') ? PHPMailer::ENCRYPTION_SMTPS : PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = SMTP_PORT;

            $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
            $mail->addAddress($toEmail);
            $mail->isHTML(true);
            $mail->Subject = 'Password Reset Request - Eisen Corporation Control Room';

            $mail->Body = "
                <div style=\"font-family: 'Inter', 'Helvetica Neue', Helvetica, Arial, sans-serif; background-color: #f4f6f8; padding: 40px 20px; color: #2d3748;\">
                    <div style=\"max-width: 540px; margin: 0 auto; background-color: #ffffff; border-radius: 12px; border: 1px solid #e2e8f0; box-shadow: 0 4px 6px rgba(0,0,0,0.05); overflow: hidden;\">
                        <div style=\"background-color: #050d1a; padding: 30px; text-align: center; border-bottom: 3px solid #c9a227;\">
                            <h1 style=\"color: #ffffff; margin: 0; font-size: 22px; font-weight: 700; letter-spacing: 1px;\">EISEN CORPORATION</h1>
                            <p style=\"color: #a0aec0; margin: 5px 0 0 0; font-size: 13px; text-transform: uppercase; letter-spacing: 1.5px;\">Control Room</p>
                        </div>
                        <div style=\"padding: 40px 30px;\">
                            <h2 style=\"margin-top: 0; margin-bottom: 20px; font-size: 18px; color: #050d1a; font-weight: 600;\">Password Reset Request</h2>
                            <p style=\"font-size: 15px; line-height: 1.6; color: #4a5568; margin-bottom: 30px;\">We received a request to reset the password for your Eisen Control Room account. Click the button below to set a new password. This link is valid for <strong>1 hour</strong>.</p>
                            <div style=\"text-align: center; margin: 30px 0;\">
                                <a href=\"{$resetUrl}\" style=\"display: inline-block; background-color: #c9a227; color: #050d1a; text-decoration: none; padding: 14px 32px; border-radius: 8px; font-weight: 700; font-size: 15px; letter-spacing: 0.5px;\">Reset My Password</a>
                            </div>
                            <p style=\"font-size: 13px; line-height: 1.5; color: #718096; margin-top: 30px;\">If you did not request a password reset, please ignore this email. Your account remains secure.</p>
                            <p style=\"font-size: 12px; color: #a0aec0; word-break: break-all;\">Or copy this link: {$resetUrl}</p>
                        </div>
                        <div style=\"background-color: #f7fafc; padding: 20px; text-align: center; font-size: 12px; color: #a0aec0; border-top: 1px solid #edf2f7;\">
                            <p style=\"margin: 0 0 5px 0;\">&copy; " . date('Y') . " Eisen Corporation. All rights reserved.</p>
                            <p style=\"margin: 0;\">This is an automated security email. Please do not reply.</p>
                        </div>
                    </div>
                </div>
            ";

            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log("PHPMailer sendPasswordReset Exception: " . $mail->ErrorInfo . " | Code: " . $e->getMessage());
            return false;
        }
    }
}
