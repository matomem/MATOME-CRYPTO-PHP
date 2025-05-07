<?php

namespace App\Core;

use Predis\Client;

class Cache
{
    private static $instance = null;
    private $redis;

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

    public function get($key)
    {
        $value = $this->redis->get($key);
        return $value ? json_decode($value, true) : null;
    }

    public function set($key, $value, $ttl = 3600)
    {
        return $this->redis->setex(
            $key,
            $ttl,
            json_encode($value)
        );
    }

    public function delete($key)
    {
        return $this->redis->del($key);
    }

    public function exists($key)
    {
        return $this->redis->exists($key);
    }

    public function increment($key)
    {
        return $this->redis->incr($key);
    }

    public function decrement($key)
    {
        return $this->redis->decr($key);
    }

    public function clear()
    {
        return $this->redis->flushdb();
    }

    public function remember($key, $ttl, $callback)
    {
        $value = $this->get($key);

        if ($value !== null) {
            return $value;
        }

        $value = $callback();
        $this->set($key, $value, $ttl);

        return $value;
    }
} 