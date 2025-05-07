<?php
require_once 'vendor/autoload.php';
use RobThree\Auth\TwoFactorAuth;

class TwoFactorAuthHelper {
    private $tfa;
    private $issuer = 'MatomeCrypto';

    public function __construct() {
        $this->tfa = new TwoFactorAuth($this->issuer);
    }

    public function generateSecret() {
        return $this->tfa->createSecret();
    }

    public function getQRCode($secret, $username) {
        return $this->tfa->getQRCodeImageAsDataUri($username, $secret);
    }

    public function verifyCode($secret, $code) {
        return $this->tfa->verifyCode($secret, $code);
    }

    public function generateBackupCodes() {
        $codes = [];
        for ($i = 0; $i < 10; $i++) {
            $codes[] = bin2hex(random_bytes(4));
        }
        return $codes;
    }

    public function verifyBackupCode($code, $usedCodes) {
        if (in_array($code, $usedCodes)) {
            return false;
        }
        return true;
    }
} 