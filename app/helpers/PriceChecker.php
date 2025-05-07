<?php
class PriceChecker {
    private $pdo;
    private $luno;
    private $emailService;

    public function __construct($pdo, $luno, $emailService) {
        $this->pdo = $pdo;
        $this->luno = $luno;
        $this->emailService = $emailService;
    }

    public function checkPriceAlerts() {
        // Get all active alerts
        $stmt = $this->pdo->prepare('
            SELECT pa.*, u.email, us.notifications_enabled 
            FROM price_alerts pa
            JOIN users u ON pa.user_id = u.id
            LEFT JOIN user_settings us ON u.id = us.user_id
            WHERE pa.is_active = TRUE
        ');
        $stmt->execute();
        $alerts = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($alerts as $alert) {
            // Skip if user has disabled notifications
            if (!$alert['notifications_enabled']) {
                continue;
            }

            // Get current price from Luno
            $pair = $alert['base_currency'] . '_' . $alert['quote_currency'];
            $ticker = $this->luno->getTicker($pair);
            $currentPrice = $ticker['last_trade'] ?? null;

            if (!$currentPrice) {
                continue;
            }

            // Check if alert condition is met
            $shouldTrigger = false;
            if ($alert['condition'] === 'ABOVE' && $currentPrice >= $alert['target_price']) {
                $shouldTrigger = true;
            } elseif ($alert['condition'] === 'BELOW' && $currentPrice <= $alert['target_price']) {
                $shouldTrigger = true;
            }

            if ($shouldTrigger) {
                // Record the alert trigger
                $this->recordAlertTrigger($alert['id'], $currentPrice);

                // Send email notification
                $this->emailService->sendPriceAlertEmail($alert['email'], [
                    'base_currency' => $alert['base_currency'],
                    'quote_currency' => $alert['quote_currency'],
                    'target_price' => $alert['target_price'],
                    'current_price' => $currentPrice
                ]);

                // Deactivate the alert if it's a one-time alert
                // This would be determined by an additional field in the price_alerts table
                // For now, we'll keep all alerts active
            }
        }
    }

    private function recordAlertTrigger($alertId, $triggeredPrice) {
        $stmt = $this->pdo->prepare('
            INSERT INTO alert_history (
                alert_id, triggered_price
            ) VALUES (?, ?)
        ');
        return $stmt->execute([$alertId, $triggeredPrice]);
    }

    public function updatePriceHistory() {
        // Get all supported trading pairs
        $pairs = ['XBT_ZAR', 'ETH_ZAR', 'XRP_ZAR']; // Add more pairs as needed

        foreach ($pairs as $pair) {
            $ticker = $this->luno->getTicker($pair);
            if (isset($ticker['last_trade'])) {
                list($baseCurrency, $quoteCurrency) = explode('_', $pair);
                
                $stmt = $this->pdo->prepare('
                    INSERT INTO price_history (
                        base_currency, quote_currency, price
                    ) VALUES (?, ?, ?)
                ');
                $stmt->execute([
                    $baseCurrency,
                    $quoteCurrency,
                    $ticker['last_trade']
                ]);
            }
        }
    }
} 