<?php

class Wallet {
    private $db;
    private $id;
    private $user_id;
    private $currency;
    private $balance;
    private $available_balance;
    private $reserved_balance;
    private $last_updated;
    private $is_active;
    private $address;
    private $label;
    private $created_at;
    private $updated_at;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function create($data) {
        $sql = "INSERT INTO wallets (user_id, currency, balance, available_balance, reserved_balance, 
                last_updated, is_active, address, label, created_at, updated_at) 
                VALUES (?, ?, ?, ?, ?, NOW(), ?, ?, ?, NOW(), NOW())";
        
        $params = [
            $data['user_id'],
            $data['currency'],
            $data['balance'] ?? 0,
            $data['available_balance'] ?? 0,
            $data['reserved_balance'] ?? 0,
            $data['is_active'] ?? true,
            $data['address'] ?? null,
            $data['label'] ?? null
        ];

        return $this->db->execute($sql, $params);
    }

    public function findByUserId($userId) {
        $sql = "SELECT * FROM wallets WHERE user_id = ?";
        return $this->db->query($sql, [$userId])->fetchAll();
    }

    public function findByUserIdAndCurrency($userId, $currency) {
        $sql = "SELECT * FROM wallets WHERE user_id = ? AND currency = ?";
        return $this->db->query($sql, [$userId, $currency])->fetch();
    }

    public function updateBalance($id, $balance, $availableBalance, $reservedBalance) {
        $sql = "UPDATE wallets SET 
                balance = ?, 
                available_balance = ?, 
                reserved_balance = ?, 
                last_updated = NOW(),
                updated_at = NOW()
                WHERE id = ?";
        
        return $this->db->execute($sql, [$balance, $availableBalance, $reservedBalance, $id]);
    }

    public function updateAddress($id, $address) {
        $sql = "UPDATE wallets SET address = ?, updated_at = NOW() WHERE id = ?";
        return $this->db->execute($sql, [$address, $id]);
    }

    public function updateLabel($id, $label) {
        $sql = "UPDATE wallets SET label = ?, updated_at = NOW() WHERE id = ?";
        return $this->db->execute($sql, [$label, $id]);
    }

    public function updateStatus($id, $isActive) {
        $sql = "UPDATE wallets SET is_active = ?, updated_at = NOW() WHERE id = ?";
        return $this->db->execute($sql, [$isActive, $id]);
    }

    public function delete($id) {
        $sql = "DELETE FROM wallets WHERE id = ?";
        return $this->db->execute($sql, [$id]);
    }

    public function getTotalBalance($userId) {
        $sql = "SELECT SUM(balance) as total_balance FROM wallets WHERE user_id = ? AND is_active = true";
        $result = $this->db->query($sql, [$userId])->fetch();
        return $result['total_balance'] ?? 0;
    }

    public function getAvailableCurrencies($userId) {
        $sql = "SELECT DISTINCT currency FROM wallets WHERE user_id = ? AND is_active = true";
        return $this->db->query($sql, [$userId])->fetchAll(PDO::FETCH_COLUMN);
    }
} 