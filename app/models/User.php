<?php
class User {
    private $db;
    private $id;
    private $username;
    private $email;
    private $password;
    private $created_at;
    private $updated_at;
    private $is_active;
    private $two_factor_enabled;
    private $two_factor_secret;
    private $last_login;
    private $login_attempts;
    private $last_attempt;
    private $api_key;
    private $api_secret;
    private $notification_preferences;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function create($data) {
        $sql = "INSERT INTO users (username, email, password, created_at, updated_at, is_active, 
                two_factor_enabled, two_factor_secret, last_login, login_attempts, last_attempt, 
                api_key, api_secret, notification_preferences) 
                VALUES (?, ?, ?, NOW(), NOW(), ?, ?, ?, NULL, 0, NULL, ?, ?, ?)";
        
        $params = [
            $data['username'],
            $data['email'],
            password_hash($data['password'], PASSWORD_DEFAULT),
            $data['is_active'] ?? true,
            $data['two_factor_enabled'] ?? false,
            $data['two_factor_secret'] ?? null,
            $data['api_key'] ?? null,
            $data['api_secret'] ?? null,
            json_encode($data['notification_preferences'] ?? [])
        ];

        return $this->db->execute($sql, $params);
    }

    public function findByEmail($email) {
        $sql = "SELECT * FROM users WHERE email = ?";
        return $this->db->query($sql, [$email])->fetch();
    }

    public function findById($id) {
        $sql = "SELECT * FROM users WHERE id = ?";
        return $this->db->query($sql, [$id])->fetch();
    }

    public function update($id, $data) {
        $sql = "UPDATE users SET 
                username = ?, 
                email = ?, 
                is_active = ?, 
                two_factor_enabled = ?, 
                two_factor_secret = ?, 
                updated_at = NOW(),
                api_key = ?,
                api_secret = ?,
                notification_preferences = ?
                WHERE id = ?";
        
        $params = [
            $data['username'],
            $data['email'],
            $data['is_active'],
            $data['two_factor_enabled'],
            $data['two_factor_secret'],
            $data['api_key'],
            $data['api_secret'],
            json_encode($data['notification_preferences']),
            $id
        ];

        return $this->db->execute($sql, $params);
    }

    public function updatePassword($id, $newPassword) {
        $sql = "UPDATE users SET password = ?, updated_at = NOW() WHERE id = ?";
        return $this->db->execute($sql, [password_hash($newPassword, PASSWORD_DEFAULT), $id]);
    }

    public function updateLoginAttempts($id, $attempts) {
        $sql = "UPDATE users SET login_attempts = ?, last_attempt = NOW() WHERE id = ?";
        return $this->db->execute($sql, [$attempts, $id]);
    }

    public function updateLastLogin($id) {
        $sql = "UPDATE users SET last_login = NOW(), login_attempts = 0 WHERE id = ?";
        return $this->db->execute($sql, [$id]);
    }

    public function delete($id) {
        $sql = "DELETE FROM users WHERE id = ?";
        return $this->db->execute($sql, [$id]);
    }

    public function verifyPassword($password, $hashedPassword) {
        return password_verify($password, $hashedPassword);
    }

    public function isAccountLocked($loginAttempts, $lastAttempt) {
        if ($loginAttempts >= 5) {
            $lockoutTime = strtotime($lastAttempt) + (15 * 60); // 15 minutes lockout
            return time() < $lockoutTime;
        }
        return false;
    }

    public function findByUsername($username) {
        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE username = ?');
        $stmt->execute([$username]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function verify($username, $password) {
        $user = $this->findByUsername($username);
        if ($user && password_verify($password, $user['password'])) {
            // Update last login
            $this->updateLastLogin($user['id']);
            return $user;
        }
        return false;
    }

    public function verifyEmail($token) {
        $stmt = $this->pdo->prepare('
            UPDATE users 
            SET email_verified = TRUE, verification_token = NULL 
            WHERE verification_token = ?
        ');
        return $stmt->execute([$token]);
    }

    public function createPasswordResetToken($email) {
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
        
        $stmt = $this->pdo->prepare('
            UPDATE users 
            SET password_reset_token = ?, password_reset_expires = ? 
            WHERE email = ?
        ');
        return $stmt->execute([$token, $expires, $email]) ? $token : false;
    }

    public function resetPassword($token, $newPassword) {
        $hash = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $this->pdo->prepare('
            UPDATE users 
            SET password = ?, password_reset_token = NULL, password_reset_expires = NULL 
            WHERE password_reset_token = ? AND password_reset_expires > CURRENT_TIMESTAMP
        ');
        return $stmt->execute([$hash, $token]);
    }

    public function enable2FA($userId, $secret) {
        $stmt = $this->pdo->prepare('
            UPDATE users 
            SET two_factor_enabled = TRUE, two_factor_secret = ? 
            WHERE id = ?
        ');
        return $stmt->execute([$secret, $userId]);
    }

    public function disable2FA($userId) {
        $stmt = $this->pdo->prepare('
            UPDATE users 
            SET two_factor_enabled = FALSE, two_factor_secret = NULL 
            WHERE id = ?
        ');
        return $stmt->execute([$userId]);
    }
} 