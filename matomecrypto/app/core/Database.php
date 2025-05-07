<?php

namespace App\Core;

use PDO;
use PDOException;

class Database {
    private static $instance = null;
    private $pdo;
    private $migrationsPath;

    private function __construct() {
        $this->migrationsPath = __DIR__ . '/../../database/migrations';
        $this->connect();
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function connect() {
        try {
            $this->pdo = new PDO(
                "mysql:host={$_ENV['DB_HOST']};dbname={$_ENV['DB_DATABASE']}",
                $_ENV['DB_USERNAME'],
                $_ENV['DB_PASSWORD'],
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]
            );
        } catch (PDOException $e) {
            throw new \Exception("Connection failed: " . $e->getMessage());
        }
    }

    public function query($sql, $params = []) {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            throw new \Exception("Query failed: " . $e->getMessage());
        }
    }

    public function createMigrationsTable() {
        $sql = "CREATE TABLE IF NOT EXISTS migrations (
            id INT AUTO_INCREMENT PRIMARY KEY,
            migration VARCHAR(255) NOT NULL,
            batch INT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        
        $this->query($sql);
    }

    public function getMigrations() {
        $this->createMigrationsTable();
        
        $sql = "SELECT migration FROM migrations ORDER BY batch, migration";
        $stmt = $this->query($sql);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function getMigrationFiles() {
        $files = glob($this->migrationsPath . '/*.php');
        sort($files);
        return $files;
    }

    public function getNextBatchNumber() {
        $sql = "SELECT MAX(batch) as batch FROM migrations";
        $stmt = $this->query($sql);
        $result = $stmt->fetch();
        return ($result['batch'] ?? 0) + 1;
    }

    public function runMigration($file) {
        require_once $file;
        
        $className = pathinfo($file, PATHINFO_FILENAME);
        $migration = new $className();
        
        $migration->up();
        
        $sql = "INSERT INTO migrations (migration, batch) VALUES (?, ?)";
        $this->query($sql, [$className, $this->getNextBatchNumber()]);
    }

    public function rollbackMigration($file) {
        require_once $file;
        
        $className = pathinfo($file, PATHINFO_FILENAME);
        $migration = new $className();
        
        $migration->down();
        
        $sql = "DELETE FROM migrations WHERE migration = ?";
        $this->query($sql, [$className]);
    }

    public function migrate() {
        $this->createMigrationsTable();
        
        $migrations = $this->getMigrations();
        $files = $this->getMigrationFiles();
        
        foreach ($files as $file) {
            $className = pathinfo($file, PATHINFO_FILENAME);
            
            if (!in_array($className, $migrations)) {
                $this->runMigration($file);
                echo "Migrated: $className\n";
            }
        }
    }

    public function rollback() {
        $migrations = $this->getMigrations();
        $files = $this->getMigrationFiles();
        
        foreach (array_reverse($files) as $file) {
            $className = pathinfo($file, PATHINFO_FILENAME);
            
            if (in_array($className, $migrations)) {
                $this->rollbackMigration($file);
                echo "Rolled back: $className\n";
            }
        }
    }

    public function execute($sql, $params = []) {
        try {
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            die("Execute failed: " . $e->getMessage());
        }
    }

    public function lastInsertId() {
        return $this->pdo->lastInsertId();
    }

    public function beginTransaction() {
        return $this->pdo->beginTransaction();
    }

    public function commit() {
        return $this->pdo->commit();
    }

    public function rollBack() {
        return $this->pdo->rollBack();
    }

    public function inTransaction() {
        return $this->pdo->inTransaction();
    }

    public function quote($string) {
        return $this->pdo->quote($string);
    }

    public function getErrorInfo() {
        return $this->pdo->errorInfo();
    }

    public function getErrorCode() {
        return $this->pdo->errorCode();
    }
} 