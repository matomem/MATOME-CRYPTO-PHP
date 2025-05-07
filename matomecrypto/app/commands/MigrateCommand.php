<?php

namespace App\Commands;

use App\Core\Database;

class MigrateCommand
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function migrate()
    {
        echo "Running migrations...\n";
        $this->db->migrate();
        echo "Migrations completed.\n";
    }

    public function rollback()
    {
        echo "Rolling back migrations...\n";
        $this->db->rollback();
        echo "Rollback completed.\n";
    }

    public function refresh()
    {
        echo "Refreshing migrations...\n";
        $this->rollback();
        $this->migrate();
        echo "Refresh completed.\n";
    }
} 