<?php

class UserSettings {
    private $db;
    private $id;
    private $user_id;
    private $theme;
    private $language;
    private $timezone;
    private $currency;
    private $notifications;
    private $two_factor_enabled;
    private $api_access;
    private $created_at;
    private $updated_at;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function create($data) {
        $sql = "INSERT INTO user_settings (user_id, theme, language, timezone, currency, 
                notifications, two_factor_enabled, api_access, created_at, updated_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";
        
        $params = [
            $data['user_id'],
            $data['theme'] ?? 'light',
            $data['language'] ?? 'en',
            $data['timezone'] ?? 'UTC',
            $data['currency'] ?? 'ZAR',
            json_encode($data['notifications'] ?? []),
            $data['two_factor_enabled'] ?? false,
            $data['api_access'] ?? false
        ];

        return $this->db->execute($sql, $params);
    }

    public function findByUserId($userId) {
        $sql = "SELECT * FROM user_settings WHERE user_id = ?";
        return $this->db->query($sql, [$userId])->fetch();
    }

    public function update($userId, $data) {
        $sql = "UPDATE user_settings SET 
                theme = ?, 
                language = ?, 
                timezone = ?, 
                currency = ?, 
                notifications = ?, 
                two_factor_enabled = ?, 
                api_access = ?, 
                updated_at = NOW() 
                WHERE user_id = ?";
        
        $params = [
            $data['theme'] ?? 'light',
            $data['language'] ?? 'en',
            $data['timezone'] ?? 'UTC',
            $data['currency'] ?? 'ZAR',
            json_encode($data['notifications'] ?? []),
            $data['two_factor_enabled'] ?? false,
            $data['api_access'] ?? false,
            $userId
        ];

        return $this->db->execute($sql, $params);
    }

    public function updateTheme($userId, $theme) {
        $sql = "UPDATE user_settings SET theme = ?, updated_at = NOW() WHERE user_id = ?";
        return $this->db->execute($sql, [$theme, $userId]);
    }

    public function updateLanguage($userId, $language) {
        $sql = "UPDATE user_settings SET language = ?, updated_at = NOW() WHERE user_id = ?";
        return $this->db->execute($sql, [$language, $userId]);
    }

    public function updateTimezone($userId, $timezone) {
        $sql = "UPDATE user_settings SET timezone = ?, updated_at = NOW() WHERE user_id = ?";
        return $this->db->execute($sql, [$timezone, $userId]);
    }

    public function updateCurrency($userId, $currency) {
        $sql = "UPDATE user_settings SET currency = ?, updated_at = NOW() WHERE user_id = ?";
        return $this->db->execute($sql, [$currency, $userId]);
    }

    public function updateNotifications($userId, $notifications) {
        $sql = "UPDATE user_settings SET notifications = ?, updated_at = NOW() WHERE user_id = ?";
        return $this->db->execute($sql, [json_encode($notifications), $userId]);
    }

    public function updateTwoFactor($userId, $enabled) {
        $sql = "UPDATE user_settings SET two_factor_enabled = ?, updated_at = NOW() WHERE user_id = ?";
        return $this->db->execute($sql, [$enabled, $userId]);
    }

    public function updateApiAccess($userId, $enabled) {
        $sql = "UPDATE user_settings SET api_access = ?, updated_at = NOW() WHERE user_id = ?";
        return $this->db->execute($sql, [$enabled, $userId]);
    }

    public function getDefaultSettings() {
        return [
            'theme' => 'light',
            'language' => 'en',
            'timezone' => 'UTC',
            'currency' => 'ZAR',
            'notifications' => [
                'email' => true,
                'price_alerts' => true,
                'trade_notifications' => true,
                'security_alerts' => true
            ],
            'two_factor_enabled' => false,
            'api_access' => false
        ];
    }
} 