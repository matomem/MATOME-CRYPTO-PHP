<?php

namespace App\Controllers;

use App\Services\LunoService;

class TradingController
{
    private $lunoService;

    public function __construct()
    {
        $this->lunoService = new LunoService();
    }

    public function index()
    {
        // Get available trading pairs
        $pairs = $this->lunoService->getAvailablePairs();
        
        // Get current market data for default pair (XBTZAR)
        $defaultPair = 'XBTZAR';
        $orderBook = $this->lunoService->getOrderBookDepth($defaultPair);
        $marketStats = $this->lunoService->get24hStats($defaultPair);
        
        // Get user's open orders
        $openOrders = $this->lunoService->listOrders($defaultPair, 'PENDING');
        
        return [
            'pairs' => $pairs,
            'orderBook' => $orderBook,
            'marketStats' => $marketStats,
            'openOrders' => $openOrders
        ];
    }

    public function getOrderBook($pair)
    {
        try {
            $orderBook = $this->lunoService->getOrderBookDepth($pair);
            return json_encode([
                'success' => true,
                'data' => $orderBook
            ]);
        } catch (\Exception $e) {
            return json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    public function getMarketStats($pair)
    {
        try {
            $stats = $this->lunoService->get24hStats($pair);
            return json_encode([
                'success' => true,
                'data' => $stats
            ]);
        } catch (\Exception $e) {
            return json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    public function createOrder()
    {
        try {
            $pair = $_POST['pair'] ?? null;
            $type = $_POST['type'] ?? null;
            $volume = $_POST['volume'] ?? null;
            $price = $_POST['price'] ?? null;

            if (!$pair || !$type || !$volume) {
                throw new \Exception('Missing required parameters');
            }

            $order = $this->lunoService->createOrder($pair, $type, $volume, $price);
            
            return json_encode([
                'success' => true,
                'data' => $order
            ]);
        } catch (\Exception $e) {
            return json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    public function cancelOrder()
    {
        try {
            $orderId = $_POST['order_id'] ?? null;

            if (!$orderId) {
                throw new \Exception('Order ID is required');
            }

            $result = $this->lunoService->cancelOrder($orderId);
            
            return json_encode([
                'success' => true,
                'data' => $result
            ]);
        } catch (\Exception $e) {
            return json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    public function getOpenOrders($pair = null)
    {
        try {
            $orders = $this->lunoService->listOrders($pair, 'PENDING');
            return json_encode([
                'success' => true,
                'data' => $orders
            ]);
        } catch (\Exception $e) {
            return json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }
} 