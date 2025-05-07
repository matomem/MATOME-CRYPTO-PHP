<?php

class Transaction {
    private $db;
    private $id;
    private $user_id;
    private $type;
    private $base_currency;
    private $quote_currency;
    private $amount;
    private $price;
    private $total_value;
    private $fee;
    private $fee_currency;
    private $status;
    private $error_message;
    private $luno_order_id;
    private $notes;
    private $created_at;
    private $updated_at;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function create($data) {
        $sql = "INSERT INTO transactions (user_id, type, base_currency, quote_currency, 
                amount, price, total_value, fee, fee_currency, status, error_message, 
                luno_order_id, notes, created_at, updated_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";
        
        $params = [
            $data['user_id'],
            $data['type'],
            $data['base_currency'],
            $data['quote_currency'],
            $data['amount'],
            $data['price'],
            $data['total_value'],
            $data['fee'] ?? null,
            $data['fee_currency'] ?? null,
            $data['status'] ?? 'PENDING',
            $data['error_message'] ?? null,
            $data['luno_order_id'] ?? null,
            $data['notes'] ?? null
        ];

        return $this->db->execute($sql, $params);
    }

    public function findByUserId($userId, $limit = 100) {
        $sql = "SELECT * FROM transactions WHERE user_id = ? ORDER BY created_at DESC LIMIT ?";
        return $this->db->query($sql, [$userId, $limit])->fetchAll();
    }

    public function findByUserIdAndCurrency($userId, $currency, $limit = 100) {
        $sql = "SELECT * FROM transactions 
                WHERE user_id = ? AND (base_currency = ? OR quote_currency = ?) 
                ORDER BY created_at DESC LIMIT ?";
        return $this->db->query($sql, [$userId, $currency, $currency, $limit])->fetchAll();
    }

    public function findById($id) {
        $sql = "SELECT * FROM transactions WHERE id = ?";
        return $this->db->query($sql, [$id])->fetch();
    }

    public function findByLunoOrderId($lunoOrderId) {
        $sql = "SELECT * FROM transactions WHERE luno_order_id = ?";
        return $this->db->query($sql, [$lunoOrderId])->fetch();
    }

    public function updateStatus($id, $status, $errorMessage = null) {
        $sql = "UPDATE transactions SET 
                status = ?, 
                error_message = ?, 
                updated_at = NOW() 
                WHERE id = ?";
        
        return $this->db->execute($sql, [$status, $errorMessage, $id]);
    }

    public function updateLunoOrderId($id, $lunoOrderId) {
        $sql = "UPDATE transactions SET luno_order_id = ?, updated_at = NOW() WHERE id = ?";
        return $this->db->execute($sql, [$lunoOrderId, $id]);
    }

    public function getTransactionHistory($userId, $startDate = null, $endDate = null, $currency = null) {
        $sql = "SELECT * FROM transactions WHERE user_id = ?";
        $params = [$userId];

        if ($startDate) {
            $sql .= " AND created_at >= ?";
            $params[] = $startDate;
        }

        if ($endDate) {
            $sql .= " AND created_at <= ?";
            $params[] = $endDate;
        }

        if ($currency) {
            $sql .= " AND (base_currency = ? OR quote_currency = ?)";
            $params[] = $currency;
            $params[] = $currency;
        }

        $sql .= " ORDER BY created_at DESC";
        
        return $this->db->query($sql, $params)->fetchAll();
    }

    public function getTotalVolume($userId, $currency, $type = null) {
        $sql = "SELECT SUM(total_value) as total_volume FROM transactions 
                WHERE user_id = ? AND (base_currency = ? OR quote_currency = ?)";
        $params = [$userId, $currency, $currency];

        if ($type) {
            $sql .= " AND type = ?";
            $params[] = $type;
        }

        $result = $this->db->query($sql, $params)->fetch();
        return $result['total_volume'] ?? 0;
    }
} 