<?php
class PriceAlert {
    private $pdo;
    private $userId;

    public function __construct($pdo, $userId) {
        $this->pdo = $pdo;
        $this->userId = $userId;
    }

    public function createAlert($baseCurrency, $quoteCurrency, $targetPrice, $condition) {
        $stmt = $this->pdo->prepare('
            INSERT INTO price_alerts (
                user_id, base_currency, quote_currency, 
                target_price, condition
            ) VALUES (?, ?, ?, ?, ?)
        ');
        return $stmt->execute([
            $this->userId, $baseCurrency, $quoteCurrency,
            $targetPrice, $condition
        ]);
    }

    public function updateAlert($alertId, $targetPrice, $condition, $isActive) {
        $stmt = $this->pdo->prepare('
            UPDATE price_alerts 
            SET target_price = ?, condition = ?, is_active = ? 
            WHERE id = ? AND user_id = ?
        ');
        return $stmt->execute([
            $targetPrice, $condition, $isActive,
            $alertId, $this->userId
        ]);
    }

    public function deleteAlert($alertId) {
        $stmt = $this->pdo->prepare('
            DELETE FROM price_alerts 
            WHERE id = ? AND user_id = ?
        ');
        return $stmt->execute([$alertId, $this->userId]);
    }

    public function getActiveAlerts() {
        $stmt = $this->pdo->prepare('
            SELECT * FROM price_alerts 
            WHERE user_id = ? AND is_active = TRUE
        ');
        $stmt->execute([$this->userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllAlerts() {
        $stmt = $this->pdo->prepare('
            SELECT * FROM price_alerts 
            WHERE user_id = ?
        ');
        $stmt->execute([$this->userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function recordAlertTrigger($alertId, $triggeredPrice) {
        $stmt = $this->pdo->prepare('
            INSERT INTO alert_history (
                alert_id, triggered_price
            ) VALUES (?, ?)
        ');
        return $stmt->execute([$alertId, $triggeredPrice]);
    }

    public function getAlertHistory($alertId) {
        $stmt = $this->pdo->prepare('
            SELECT * FROM alert_history 
            WHERE alert_id = ? 
            ORDER BY triggered_at DESC
        ');
        $stmt->execute([$alertId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} 