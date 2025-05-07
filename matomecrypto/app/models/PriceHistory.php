<?php

class PriceHistory {
    private $db;
    private $id;
    private $currency;
    private $price;
    private $timestamp;
    private $source;
    private $created_at;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function create($data) {
        $sql = "INSERT INTO price_history (currency, price, timestamp, source, created_at) 
                VALUES (?, ?, ?, ?, NOW())";
        
        $params = [
            $data['currency'],
            $data['price'],
            $data['timestamp'] ?? date('Y-m-d H:i:s'),
            $data['source'] ?? 'LUNO'
        ];

        return $this->db->execute($sql, $params);
    }

    public function getLatestPrice($currency) {
        $sql = "SELECT * FROM price_history 
                WHERE currency = ? 
                ORDER BY timestamp DESC 
                LIMIT 1";
        return $this->db->query($sql, [$currency])->fetch();
    }

    public function getPriceHistory($currency, $startDate = null, $endDate = null, $limit = 1000) {
        $sql = "SELECT * FROM price_history WHERE currency = ?";
        $params = [$currency];

        if ($startDate) {
            $sql .= " AND timestamp >= ?";
            $params[] = $startDate;
        }

        if ($endDate) {
            $sql .= " AND timestamp <= ?";
            $params[] = $endDate;
        }

        $sql .= " ORDER BY timestamp DESC LIMIT ?";
        $params[] = $limit;

        return $this->db->query($sql, $params)->fetchAll();
    }

    public function getPriceRange($currency, $startDate, $endDate) {
        $sql = "SELECT 
                MIN(price) as min_price,
                MAX(price) as max_price,
                AVG(price) as avg_price
                FROM price_history 
                WHERE currency = ? 
                AND timestamp BETWEEN ? AND ?";
        
        return $this->db->query($sql, [$currency, $startDate, $endDate])->fetch();
    }

    public function getPriceChange($currency, $period = '24h') {
        $endTime = date('Y-m-d H:i:s');
        $startTime = date('Y-m-d H:i:s', strtotime("-{$period}"));

        $sql = "SELECT 
                (SELECT price FROM price_history 
                 WHERE currency = ? AND timestamp <= ? 
                 ORDER BY timestamp DESC LIMIT 1) as current_price,
                (SELECT price FROM price_history 
                 WHERE currency = ? AND timestamp <= ? 
                 ORDER BY timestamp DESC LIMIT 1) as previous_price";
        
        $result = $this->db->query($sql, [$currency, $endTime, $currency, $startTime])->fetch();
        
        if ($result && $result['previous_price'] && $result['current_price']) {
            $change = (($result['current_price'] - $result['previous_price']) / $result['previous_price']) * 100;
            return [
                'current_price' => $result['current_price'],
                'previous_price' => $result['previous_price'],
                'change_percent' => $change
            ];
        }
        
        return null;
    }

    public function cleanupOldData($days = 30) {
        $sql = "DELETE FROM price_history 
                WHERE timestamp < DATE_SUB(NOW(), INTERVAL ? DAY)";
        return $this->db->execute($sql, [$days]);
    }
} 