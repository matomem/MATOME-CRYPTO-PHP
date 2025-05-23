<?php

namespace App\Models;

use PDO;
use App\Services\LunoService;

class Trade extends BaseModel
{
    private $lunoService;
    private $isProduction;

    public function __construct()
    {
        parent::__construct();
        $this->lunoService = new LunoService();
        $this->isProduction = getenv('APP_ENV') === 'production';
    }

    public function createOrder($userId, $pair, $type, $volume, $price = null)
    {
        try {
            // Validate input
            if (!in_array(strtoupper($type), ['BUY', 'SELL'])) {
                throw new \Exception('Invalid order type. Must be BUY or SELL.');
            }

            if ($this->isProduction) {
                // Additional production validations
                if ($volume <= 0) {
                    throw new \Exception('Invalid volume amount');
                }

                if ($price !== null && $price <= 0) {
                    throw new \Exception('Invalid price amount');
                }

                // Validate trading pair
                $availablePairs = $this->lunoService->getAvailablePairs();
                if (!in_array($pair, $availablePairs)) {
                    throw new \Exception('Invalid trading pair');
                }
            }

            // Check if it's a market or limit order
            $result = $price === null 
                ? $this->lunoService->createMarketOrder($pair, $type, $volume)
                : $this->lunoService->createLimitOrder($pair, $type, $volume, $price);
            
            // Store order in database
            $query = "INSERT INTO orders (
                        user_id, order_id, pair, type, volume, price, 
                        status, order_type, created_at, updated_at
                     ) VALUES (
                        :user_id, :order_id, :pair, :type, :volume, :price,
                        :status, :order_type, NOW(), NOW()
                     )";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute([
                'user_id' => $userId,
                'order_id' => $result['order_id'],
                'pair' => $pair,
                'type' => $type,
                'volume' => $volume,
                'price' => $price,
                'status' => 'PENDING',
                'order_type' => $price === null ? 'MARKET' : 'LIMIT'
            ]);

            // Create transaction record
            $this->createTransactionRecord($userId, $result['order_id'], $pair, $type, $volume, $price);

            return $result;
        } catch (\Exception $e) {
            error_log('Create Order Error: ' . $e->getMessage());
            throw new \Exception('Failed to create order: ' . $e->getMessage());
        }
    }

    private function createTransactionRecord($userId, $orderId, $pair, $type, $volume, $price)
    {
        try {
            $query = "INSERT INTO transactions (
                        user_id, order_id, pair, type, volume, price,
                        status, created_at, updated_at
                     ) VALUES (
                        :user_id, :order_id, :pair, :type, :volume, :price,
                        'PENDING', NOW(), NOW()
                     )";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute([
                'user_id' => $userId,
                'order_id' => $orderId,
                'pair' => $pair,
                'type' => $type,
                'volume' => $volume,
                'price' => $price
            ]);
        } catch (\Exception $e) {
            error_log('Create Transaction Record Error: ' . $e->getMessage());
            throw new \Exception('Failed to create transaction record');
        }
    }

    public function cancelOrder($userId, $orderId)
    {
        try {
            // Verify order belongs to user
            $order = $this->getOrder($userId, $orderId);
            if (!$order) {
                throw new \Exception('Order not found');
            }

            // Cancel order on Luno
            $result = $this->lunoService->cancelOrder($orderId);
            
            // Update order status in database
            $query = "UPDATE orders SET 
                        status = 'CANCELLED', 
                        updated_at = NOW() 
                     WHERE user_id = :user_id AND order_id = :order_id";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute([
                'user_id' => $userId,
                'order_id' => $orderId
            ]);

            // Update transaction status
            $this->updateTransactionStatus($userId, $orderId, 'CANCELLED');

            return $result;
        } catch (\Exception $e) {
            error_log('Cancel Order Error: ' . $e->getMessage());
            throw new \Exception('Failed to cancel order: ' . $e->getMessage());
        }
    }

    public function getOrder($userId, $orderId)
    {
        try {
            // Get order from Luno
            $order = $this->lunoService->getOrder($orderId);
            
            // Verify order belongs to user
            $query = "SELECT * FROM orders 
                     WHERE user_id = :user_id AND order_id = :order_id";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute([
                'user_id' => $userId,
                'order_id' => $orderId
            ]);
            
            $dbOrder = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$dbOrder) {
                throw new \Exception('Order not found');
            }
            
            // Update local database
            $query = "UPDATE orders SET 
                        status = :status,
                        filled_volume = :filled_volume,
                        average_price = :average_price,
                        updated_at = NOW()
                     WHERE user_id = :user_id AND order_id = :order_id";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute([
                'user_id' => $userId,
                'order_id' => $orderId,
                'status' => $order['status'],
                'filled_volume' => $order['filled_volume'] ?? 0,
                'average_price' => $order['average_price'] ?? 0
            ]);

            return $order;
        } catch (\Exception $e) {
            error_log('Get Order Error: ' . $e->getMessage());
            throw new \Exception('Failed to get order: ' . $e->getMessage());
        }
    }

    public function getOrderStatus($userId, $orderId)
    {
        try {
            // Get order status from Luno
            $lunoStatus = $this->lunoService->getOrderStatus($orderId);
            
            // Verify order belongs to user
            $query = "SELECT * FROM orders 
                     WHERE user_id = :user_id AND order_id = :order_id";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute([
                'user_id' => $userId,
                'order_id' => $orderId
            ]);
            
            $dbOrder = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$dbOrder) {
                throw new \Exception('Order not found');
            }
            
            // Update local database
            $query = "UPDATE orders SET 
                        status = :status,
                        filled_volume = :filled_volume,
                        remaining_volume = :remaining_volume,
                        average_price = :average_price,
                        updated_at = NOW()
                     WHERE user_id = :user_id AND order_id = :order_id";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute([
                'user_id' => $userId,
                'order_id' => $orderId,
                'status' => $lunoStatus['status'],
                'filled_volume' => $lunoStatus['filled_volume'],
                'remaining_volume' => $lunoStatus['remaining_volume'],
                'average_price' => $lunoStatus['average_price']
            ]);

            // Update transaction status if order is completed
            if (in_array($lunoStatus['status'], ['COMPLETE', 'CANCELLED'])) {
                $this->updateTransactionStatus($userId, $orderId, $lunoStatus['status']);
            }

            return $lunoStatus;
        } catch (\Exception $e) {
            error_log('Get Order Status Error: ' . $e->getMessage());
            throw new \Exception('Failed to get order status: ' . $e->getMessage());
        }
    }

    private function updateTransactionStatus($userId, $orderId, $status)
    {
        try {
            $query = "UPDATE transactions SET 
                        status = :status,
                        updated_at = NOW()
                     WHERE user_id = :user_id AND order_id = :order_id";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute([
                'user_id' => $userId,
                'order_id' => $orderId,
                'status' => $status
            ]);
        } catch (\Exception $e) {
            error_log('Update Transaction Status Error: ' . $e->getMessage());
            throw new \Exception('Failed to update transaction status');
        }
    }

    public function getRecentTrades($userId, $limit = 10)
    {
        try {
            $query = "SELECT t.*, o.pair, o.type, o.order_type 
                     FROM transactions t
                     JOIN orders o ON t.order_id = o.order_id
                     WHERE o.user_id = :user_id
                     ORDER BY t.created_at DESC
                     LIMIT :limit";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log('Get Recent Trades Error: ' . $e->getMessage());
            throw new \Exception('Failed to get recent trades');
        }
    }

    public function getOpenOrdersCount($userId)
    {
        $query = "SELECT COUNT(*) FROM orders 
                 WHERE user_id = :user_id AND status IN ('PENDING', 'PARTIALLY_FILLED')";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchColumn();
    }

    public function getDailyPnL($userId)
    {
        $query = "SELECT SUM(
                    CASE 
                        WHEN o.type = 'BUY' THEN -t.volume * t.price
                        ELSE t.volume * t.price
                    END
                 ) as pnl
                 FROM trades t
                 JOIN orders o ON t.order_id = o.order_id
                 WHERE o.user_id = :user_id
                 AND t.created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchColumn() ?? 0;
    }

    public function getDailyPnLPercentage($userId)
    {
        $query = "SELECT 
                    (SELECT SUM(
                        CASE 
                            WHEN o.type = 'BUY' THEN -t.volume * t.price
                            ELSE t.volume * t.price
                        END
                    )
                    FROM trades t
                    JOIN orders o ON t.order_id = o.order_id
                    WHERE o.user_id = :user_id
                    AND t.created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)) /
                    (SELECT SUM(
                        CASE 
                            WHEN o.type = 'BUY' THEN t.volume * t.price
                            ELSE 0
                        END
                    )
                    FROM trades t
                    JOIN orders o ON t.order_id = o.order_id
                    WHERE o.user_id = :user_id
                    AND t.created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)) * 100 as pnl_percentage";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchColumn() ?? 0;
    }
       //Function to get trading history 

    public function getTradeHistory($userId, $pair = null, $page = 1, $perPage = 20)
    {
        $offset = ($page - 1) * $perPage;
        $where = ['o.user_id = :user_id'];
        $params = ['user_id' => $userId];

        if ($pair) {
            $where[] = 'o.pair = :pair';
            $params['pair'] = $pair;
        }

        $whereClause = implode(' AND ', $where);

        $query = "SELECT t.*, o.pair, o.type 
                 FROM trades t
                 JOIN orders o ON t.order_id = o.order_id
                 WHERE {$whereClause}
                 ORDER BY t.created_at DESC
                 LIMIT :offset, :per_page";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->bindValue(':per_page', $perPage, PDO::PARAM_INT);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue(":{$key}", $value);
        }
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function countTradeHistory($userId, $pair = null)
    {
        $where = ['o.user_id = :user_id'];
        $params = ['user_id' => $userId];

        if ($pair) {
            $where[] = 'o.pair = :pair';
            $params['pair'] = $pair;
        }

        $whereClause = implode(' AND ', $where);

        $query = "SELECT COUNT(*) 
                 FROM trades t
                 JOIN orders o ON t.order_id = o.order_id
                 WHERE {$whereClause}";
        
        $stmt = $this->db->prepare($query);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue(":{$key}", $value);
        }
        
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    public function syncOrders($userId)
    {
        try {
            // Get all pending orders from database
            $query = "SELECT order_id FROM orders 
                     WHERE user_id = :user_id AND status IN ('PENDING', 'PARTIALLY_FILLED')";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute(['user_id' => $userId]);
            $orders = $stmt->fetchAll(PDO::FETCH_COLUMN);

            // Update each order status
            foreach ($orders as $orderId) {
                $this->getOrderStatus($userId, $orderId);
            }

            return true;
        } catch (\Exception $e) {
            error_log('Sync Orders Error: ' . $e->getMessage());
            throw new \Exception('Failed to sync orders');
        }
    }
} 