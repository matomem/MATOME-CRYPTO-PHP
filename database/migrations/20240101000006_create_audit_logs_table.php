<?php

use App\Core\Database;

class CreateAuditLogsTable
{
    public function up()
    {
        $db = Database::getInstance();
        
        $sql = "CREATE TABLE IF NOT EXISTS audit_logs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT,
            action VARCHAR(255) NOT NULL,
            entity_type VARCHAR(50),
            entity_id INT,
            old_values JSON,
            new_values JSON,
            ip_address VARCHAR(45),
            user_agent TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id),
            INDEX idx_user_action (user_id, action),
            INDEX idx_entity (entity_type, entity_id)
        )";
        
        $db->query($sql);
    }

    public function down()
    {
        $db = Database::getInstance();
        $db->query("DROP TABLE IF EXISTS audit_logs");
    }
} 