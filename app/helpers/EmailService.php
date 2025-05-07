<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class EmailService {
    private $mailer;
    private $fromEmail;
    private $fromName;

    public function __construct() {
        $this->mailer = new PHPMailer(true);
        $this->fromEmail = getenv('SMTP_FROM_EMAIL') ?: 'noreply@matomecrypto.com';
        $this->fromName = getenv('SMTP_FROM_NAME') ?: 'MatomeCrypto';

        // SMTP Configuration
        $this->mailer->isSMTP();
        $this->mailer->Host = getenv('SMTP_HOST');
        $this->mailer->SMTPAuth = true;
        $this->mailer->Username = getenv('SMTP_USERNAME');
        $this->mailer->Password = getenv('SMTP_PASSWORD');
        $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $this->mailer->Port = getenv('SMTP_PORT') ?: 587;
    }

    public function sendVerificationEmail($email, $token) {
        $verificationUrl = getenv('APP_URL') . '/verify-email?token=' . $token;
        
        $this->mailer->setFrom($this->fromEmail, $this->fromName);
        $this->mailer->addAddress($email);
        $this->mailer->isHTML(true);
        $this->mailer->Subject = 'Verify your MatomeCrypto email address';
        
        $body = file_get_contents('app/views/emails/verification.html');
        $body = str_replace('{{verification_url}}', $verificationUrl, $body);
        
        $this->mailer->Body = $body;
        $this->mailer->AltBody = "Please verify your email by visiting: $verificationUrl";
        
        return $this->mailer->send();
    }

    public function sendPasswordResetEmail($email, $token) {
        $resetUrl = getenv('APP_URL') . '/reset-password?token=' . $token;
        
        $this->mailer->setFrom($this->fromEmail, $this->fromName);
        $this->mailer->addAddress($email);
        $this->mailer->isHTML(true);
        $this->mailer->Subject = 'Reset your MatomeCrypto password';
        
        $body = file_get_contents('app/views/emails/password-reset.html');
        $body = str_replace('{{reset_url}}', $resetUrl, $body);
        
        $this->mailer->Body = $body;
        $this->mailer->AltBody = "Reset your password by visiting: $resetUrl";
        
        return $this->mailer->send();
    }

    public function sendPriceAlertEmail($email, $alert) {
        $this->mailer->setFrom($this->fromEmail, $this->fromName);
        $this->mailer->addAddress($email);
        $this->mailer->isHTML(true);
        $this->mailer->Subject = "Price Alert: {$alert['base_currency']}/{$alert['quote_currency']}";
        
        $body = file_get_contents('app/views/emails/price-alert.html');
        $body = str_replace([
            '{{base_currency}}',
            '{{quote_currency}}',
            '{{target_price}}',
            '{{current_price}}'
        ], [
            $alert['base_currency'],
            $alert['quote_currency'],
            $alert['target_price'],
            $alert['current_price']
        ], $body);
        
        $this->mailer->Body = $body;
        $this->mailer->AltBody = "Price alert triggered for {$alert['base_currency']}/{$alert['quote_currency']}";
        
        return $this->mailer->send();
    }

    public function sendSecurityAlertEmail($email, $type, $details) {
        $this->mailer->setFrom($this->fromEmail, $this->fromName);
        $this->mailer->addAddress($email);
        $this->mailer->isHTML(true);
        $this->mailer->Subject = "Security Alert: $type";
        
        $body = file_get_contents('app/views/emails/security-alert.html');
        $body = str_replace([
            '{{alert_type}}',
            '{{alert_details}}',
            '{{timestamp}}'
        ], [
            $type,
            $details,
            date('Y-m-d H:i:s')
        ], $body);
        
        $this->mailer->Body = $body;
        $this->mailer->AltBody = "Security alert: $type - $details";
        
        return $this->mailer->send();
    }
} 