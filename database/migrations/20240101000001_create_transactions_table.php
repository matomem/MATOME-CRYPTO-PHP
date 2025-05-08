<?php

use App\Core\Database;

class CreateTransactionsTable
{
    public function up()
    {
        $db = Database::getInstance();
        
        $sql = "CREATE TABLE IF NOT EXISTS transactions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            type ENUM('buy', 'sell') NOT NULL,
            amount DECIMAL(20,8) NOT NULL,
            price DECIMAL(20,2) NOT NULL,
            status ENUM('pending', 'completed', 'failed') NOT NULL,
            pair VARCHAR(20) NOT NULL,
            order_id VARCHAR(255),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id)
        )";
        
        $db->query($sql);
    }

    public function down()
    {
        $db = Database::getInstance();
        $db->query("DROP TABLE IF EXISTS transactions");
    }
} 