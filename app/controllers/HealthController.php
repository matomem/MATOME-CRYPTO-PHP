<?php

namespace App\Controllers;

use App\Core\Database;
use App\Core\Cache;
use App\Services\LunoService;

class HealthController
{
    private $db;
    private $cache;
    private $lunoService;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->cache = Cache::getInstance();
        $this->lunoService = new LunoService();
    }

    public function check()
    {
        $status = [
            'status' => 'healthy',
            'timestamp' => time(),
            'services' => []
        ];

        // Check database connection
        try {
            $this->db->query('SELECT 1');
            $status['services']['database'] = 'healthy';
        } catch (\Exception $e) {
            $status['services']['database'] = 'unhealthy';
            $status['status'] = 'degraded';
        }

        // Check Redis cache
        try {
            $this->cache->set('health_check', 'ok', 60);
            $status['services']['cache'] = 'healthy';
        } catch (\Exception $e) {
            $status['services']['cache'] = 'unhealthy';
            $status['status'] = 'degraded';
        }

        // Check Luno API
        try {
            $this->lunoService->getTicker('XBTZAR');
            $status['services']['luno_api'] = 'healthy';
        } catch (\Exception $e) {
            $status['services']['luno_api'] = 'unhealthy';
            $status['status'] = 'degraded';
        }

        // Check disk space
        $freeSpace = disk_free_space('/');
        $totalSpace = disk_total_space('/');
        $usedSpace = $totalSpace - $freeSpace;
        $usedPercentage = ($usedSpace / $totalSpace) * 100;

        $status['disk'] = [
            'free' => $freeSpace,
            'total' => $totalSpace,
            'used_percentage' => round($usedPercentage, 2)
        ];

        if ($usedPercentage > 90) {
            $status['status'] = 'degraded';
        }

        // Check memory usage
        $memoryUsage = memory_get_usage(true);
        $memoryLimit = ini_get('memory_limit');
        $status['memory'] = [
            'usage' => $memoryUsage,
            'limit' => $memoryLimit
        ];

        // Set response headers
        header('Content-Type: application/json');
        if ($status['status'] === 'healthy') {
            http_response_code(200);
        } else {
            http_response_code(503);
        }

        echo json_encode($status);
    }
} 