<?php
class ApiCredentials {
    private $pdo;
    private $userId;

    public function __construct($pdo, $userId) {
        $this->pdo = $pdo;
        $this->userId = $userId;
    }

    public function getCredentials($exchange) {
        $stmt = $this->pdo->prepare('
            SELECT api_key, api_secret 
            FROM api_credentials 
            WHERE user_id = ? AND exchange = ? AND is_active = TRUE
        ');
        $stmt->execute([$this->userId, $exchange]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function saveCredentials($exchange, $apiKey, $apiSecret) {
        $stmt = $this->pdo->prepare('
            INSERT INTO api_credentials (user_id, exchange, api_key, api_secret) 
            VALUES (?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE 
                api_key = VALUES(api_key),
                api_secret = VALUES(api_secret),
                is_active = TRUE,
                updated_at = CURRENT_TIMESTAMP
        ');
        return $stmt->execute([$this->userId, $exchange, $apiKey, $apiSecret]);
    }

    public function deactivateCredentials($exchange) {
        $stmt = $this->pdo->prepare('
            UPDATE api_credentials 
            SET is_active = FALSE, updated_at = CURRENT_TIMESTAMP 
            WHERE user_id = ? AND exchange = ?
        ');
        return $stmt->execute([$this->userId, $exchange]);
    }

    public function getAllCredentials() {
        $stmt = $this->pdo->prepare('
            SELECT exchange, is_active, created_at, updated_at 
            FROM api_credentials 
            WHERE user_id = ?
        ');
        $stmt->execute([$this->userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} 