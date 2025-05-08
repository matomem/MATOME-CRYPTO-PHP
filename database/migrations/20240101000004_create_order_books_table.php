<?php

use App\Core\Database;

class CreateOrderBooksTable
{
    public function up()
    {
        $db = Database::getInstance();
        
        $sql = "CREATE TABLE IF NOT EXISTS order_books (
            id INT AUTO_INCREMENT PRIMARY KEY,
            pair VARCHAR(20) NOT NULL,
            asks JSON NOT NULL,
            bids JSON NOT NULL,
            timestamp BIGINT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_pair_timestamp (pair, timestamp)
        )";
        
        $db->query($sql);
    }

    public function down()
    {
        $db = Database::getInstance();
        $db->query("DROP TABLE IF EXISTS order_books");
    }
} 