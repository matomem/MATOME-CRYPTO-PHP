<?php
class SettingsController {
    private $user;
    private $pdo;
    private $userId;

    public function __construct($pdo, $userId) {
        $this->user = new User($pdo);
        $this->pdo = $pdo;
        $this->userId = $userId;
    }

    public function index() {
        $userData = $this->user->findById($this->userId);
        require_once 'app/views/settings/index.php';
    }
// Change password 
    public function updateProfile() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'] ?? '';
            $currentPassword = $_POST['current_password'] ?? '';
            $newPassword = $_POST['new_password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';

            $user = $this->user->findById($this->userId);
            
            if (!empty($newPassword)) {
                if (empty($currentPassword)) {
                    $_SESSION['error'] = 'Current password is required to change password';
                    header('Location: /settings');
                    exit;
                }

                if ($newPassword !== $confirmPassword) {
                    $_SESSION['error'] = 'New passwords do not match';
                    header('Location: /settings');
                    exit;
                }

                if (!password_verify($currentPassword, $user['password'])) {
                    $_SESSION['error'] = 'Current password is incorrect';
                    header('Location: /settings');
                    exit;
                }

                $this->user->resetPassword($user['password_reset_token'], $newPassword);
            }

            if (!empty($email) && $email !== $user['email']) {
                // Update email and send verification
                $stmt = $this->pdo->prepare('
                    UPDATE users 
                    SET email = ?, email_verified = FALSE, verification_token = ? 
                    WHERE id = ?
                ');
                $verificationToken = bin2hex(random_bytes(32));
                $stmt->execute([$email, $verificationToken, $this->userId]);
                
                // Send verification email
                // This would be implemented in the EmailService
            }

            $_SESSION['success'] = 'Settings updated successfully';
            header('Location: /settings');
            exit;
        }
    }

    public function setup2FA() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $secret = $_POST['secret'] ?? '';
            $code = $_POST['code'] ?? '';

            // Verify the 2FA code
            // This would be implemented in the TwoFactorAuth class
            if (verify2FACode($secret, $code)) {
                $this->user->enable2FA($this->userId, $secret);
                $_SESSION['success'] = 'Two-factor authentication enabled successfully';
            } else {
                $_SESSION['error'] = 'Invalid verification code';
            }
            header('Location: /settings');
            exit;
        }
        require_once 'app/views/settings/2fa.php';
    }

    public function disable2FA() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $code = $_POST['code'] ?? '';
            $user = $this->user->findById($this->userId);

            // Verify the 2FA code
            if (verify2FACode($user['two_factor_secret'], $code)) {
                $this->user->disable2FA($this->userId);
                $_SESSION['success'] = 'Two-factor authentication disabled successfully';
            } else {
                $_SESSION['error'] = 'Invalid verification code';
            }
            header('Location: /settings');
            exit;
        }
    }
//notification 
    public function updateNotifications() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $notificationsEnabled = isset($_POST['notifications_enabled']) ? 1 : 0;
            $priceAlertThreshold = $_POST['price_alert_threshold'] ?? null;

            $stmt = $this->pdo->prepare('
                INSERT INTO user_settings (user_id, notifications_enabled, price_alert_threshold) 
                VALUES (?, ?, ?)
                ON DUPLICATE KEY UPDATE 
                    notifications_enabled = VALUES(notifications_enabled),
                    price_alert_threshold = VALUES(price_alert_threshold)
            ');
            
            if ($stmt->execute([$this->userId, $notificationsEnabled, $priceAlertThreshold])) {
                $_SESSION['success'] = 'Notification settings updated successfully';
            } else {
                $_SESSION['error'] = 'Failed to update notification settings';
            }
            header('Location: /settings');
            exit;
        }
    }
} 