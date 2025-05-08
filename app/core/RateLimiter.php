<?php

namespace App\Core;

use Predis\Client;

class RateLimiter
{
    private static $instance = null;
    private $redis;
    private $defaultLimit = 60; // requests per minute
    private $defaultWindow = 60; // seconds

    private function __construct()
    {
        $this->redis = new Client([
            'scheme' => 'tcp',
            'host'   => $_ENV['REDIS_HOST'] ?? '127.0.0.1',
            'port'   => $_ENV['REDIS_PORT'] ?? 6379,
        ]);
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function check($key, $limit = null, $window = null)
    {
        $limit = $limit ?? $this->defaultLimit;
        $window = $window ?? $this->defaultWindow;
        
        $current = $this->redis->get($key);
        
        if (!$current) {
            $this->redis->setex($key, $window, 1);
            return true;
        }
        
        if ($current >= $limit) {
            return false;
        }
        
        $this->redis->incr($key);
        return true;
    }

    public function getRemainingAttempts($key)
    {
        $current = $this->redis->get($key);
        return $this->defaultLimit - ($current ?? 0);
    }

    public function getResetTime($key)
    {
        return $this->redis->ttl($key);
    }

    public function reset($key)
    {
        return $this->redis->del($key);
    }
} 