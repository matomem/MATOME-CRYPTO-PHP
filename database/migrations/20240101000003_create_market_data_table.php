<?php

use App\Core\Database;

class CreateMarketDataTable
{
    public function up()
    {
        $db = Database::getInstance();
        
        $sql = "CREATE TABLE IF NOT EXISTS market_data (
            id INT AUTO_INCREMENT PRIMARY KEY,
            pair VARCHAR(20) NOT NULL,
            last_price DECIMAL(20,2) NOT NULL,
            bid DECIMAL(20,2) NOT NULL,
            ask DECIMAL(20,2) NOT NULL,
            volume_24h DECIMAL(20,8) NOT NULL,
            high_24h DECIMAL(20,2) NOT NULL,
            low_24h DECIMAL(20,2) NOT NULL,
            timestamp BIGINT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_pair_timestamp (pair, timestamp)
        )";
        
        $db->query($sql);
    }

    public function down()
    {
        $db = Database::getInstance();
        $db->query("DROP TABLE IF EXISTS market_data");
    }
} 