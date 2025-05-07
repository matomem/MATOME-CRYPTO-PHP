<?php

class Security {
    private static $instance = null;
    private $db;
    private $allowedOrigins = ['https://yourdomain.com']; // Add your production domain

    private function __construct() {
        $this->db = Database::getInstance();
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function sanitizeInput($input) {
        if (is_array($input)) {
            return array_map([$this, 'sanitizeInput'], $input);
        }
        return htmlspecialchars(strip_tags($input), ENT_QUOTES, 'UTF-8');
    }

    public function validateCSRF() {
        if (!isset($_SESSION['csrf_token']) || !isset($_POST['csrf_token'])) {
            throw new Exception('CSRF token missing');
        }
        if ($_SESSION['csrf_token'] !== $_POST['csrf_token']) {
            throw new Exception('CSRF token mismatch');
        }
        return true;
    }

    public function generateCSRFToken() {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public function preventSQLInjection($query, $params = []) {
        $stmt = $this->db->prepare($query);
        if (!empty($params)) {
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value, $this->getPDOType($value));
            }
        }
        return $stmt;
    }

    private function getPDOType($value) {
        if (is_int($value)) return PDO::PARAM_INT;
        if (is_bool($value)) return PDO::PARAM_BOOL;
        if (is_null($value)) return PDO::PARAM_NULL;
        return PDO::PARAM_STR;
    }

    public function validateRequestOrigin() {
        if (!isset($_SERVER['HTTP_ORIGIN'])) {
            return false;
        }
        return in_array($_SERVER['HTTP_ORIGIN'], $this->allowedOrigins);
    }

    public function setSecurityHeaders() {
        header('X-Frame-Options: SAMEORIGIN');
        header('X-XSS-Protection: 1; mode=block');
        header('X-Content-Type-Options: nosniff');
        header('Referrer-Policy: strict-origin-when-cross-origin');
        header('Content-Security-Policy: default-src \'self\'; script-src \'self\' \'unsafe-inline\' \'unsafe-eval\' https://cdn.jsdelivr.net; style-src \'self\' \'unsafe-inline\' https://cdn.jsdelivr.net; img-src \'self\' data: https:; font-src \'self\' https://cdn.jsdelivr.net;');
    }

    public function validateFileUpload($file, $allowedTypes = ['jpg', 'jpeg', 'png', 'pdf']) {
        if (!isset($file['error']) || is_array($file['error'])) {
            throw new Exception('Invalid file upload');
        }

        switch ($file['error']) {
            case UPLOAD_ERR_OK:
                break;
            case UPLOAD_ERR_NO_FILE:
                throw new Exception('No file sent');
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                throw new Exception('Exceeded filesize limit');
            default:
                throw new Exception('Unknown errors');
        }

        if ($file['size'] > MAX_FILE_SIZE) {
            throw new Exception('Exceeded filesize limit');
        }

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($file['tmp_name']);
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if (!in_array($ext, $allowedTypes)) {
            throw new Exception('Invalid file format');
        }

        return true;
    }

    public function generateSecureToken($length = 32) {
        return bin2hex(random_bytes($length));
    }

    public function hashPassword($password) {
        return password_hash($password, PASSWORD_ARGON2ID, ['cost' => HASH_COST]);
    }

    public function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }

    public function checkBruteForce($userId) {
        $sql = "SELECT login_attempts, last_attempt FROM users WHERE id = ?";
        $stmt = $this->preventSQLInjection($sql, [1 => $userId]);
        $result = $stmt->fetch();

        if ($result['login_attempts'] >= MAX_LOGIN_ATTEMPTS) {
            $lockoutTime = strtotime($result['last_attempt']) + LOCKOUT_TIME;
            if (time() < $lockoutTime) {
                throw new Exception('Account locked. Please try again later.');
            }
        }
        return true;
    }

    public function logSecurityEvent($userId, $action, $details = []) {
        $sql = "INSERT INTO audit_log (user_id, action, details, ip_address, user_agent) 
                VALUES (?, ?, ?, ?, ?)";
        $params = [
            1 => $userId,
            2 => $action,
            3 => json_encode($details),
            4 => $_SERVER['REMOTE_ADDR'],
            5 => $_SERVER['HTTP_USER_AGENT']
        ];
        $this->preventSQLInjection($sql, $params)->execute();
    }
} 