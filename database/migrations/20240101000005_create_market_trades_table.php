<?php

use App\Core\Database;

class CreateMarketTradesTable
{
    public function up()
    {
        $db = Database::getInstance();
        
        $sql = "CREATE TABLE IF NOT EXISTS market_trades (
            id INT AUTO_INCREMENT PRIMARY KEY,
            pair VARCHAR(20) NOT NULL,
            price DECIMAL(20,2) NOT NULL,
            volume DECIMAL(20,8) NOT NULL,
            timestamp BIGINT NOT NULL,
            side ENUM('buy', 'sell') NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_pair_timestamp (pair, timestamp)
        )";
        
        $db->query($sql);
    }

    public function down()
    {
        $db = Database::getInstance();
        $db->query("DROP TABLE IF EXISTS market_trades");
    }
} 