<?php

namespace App\Models;

use PDO;
use App\Services\LunoService;

class Bot extends BaseModel
{
    private $lunoService;

    public function __construct()
    {
        parent::__construct();
        $this->lunoService = new LunoService();
    }

    public function create($userId, $data)
    {
        try {
            $query = "INSERT INTO bots (
                        user_id, name, strategy, pair, 
                        parameters, status, created_at
                     ) VALUES (
                        :user_id, :name, :strategy, :pair,
                        :parameters, 'ACTIVE', NOW()
                     )";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute([
                'user_id' => $userId,
                'name' => $data['name'],
                'strategy' => $data['strategy'],
                'pair' => $data['pair'],
                'parameters' => json_encode($data['parameters'])
            ]);

            return $this->db->lastInsertId();
        } catch (\Exception $e) {
            throw new \Exception('Failed to create bot: ' . $e->getMessage());
        }
    }

    public function update($userId, $botId, $data)
    {
        try {
            $query = "UPDATE bots SET 
                        name = :name,
                        strategy = :strategy,
                        pair = :pair,
                        parameters = :parameters,
                        updated_at = NOW()
                     WHERE id = :id AND user_id = :user_id";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute([
                'id' => $botId,
                'user_id' => $userId,
                'name' => $data['name'],
                'strategy' => $data['strategy'],
                'pair' => $data['pair'],
                'parameters' => json_encode($data['parameters'])
            ]);

            return true;
        } catch (\Exception $e) {
            throw new \Exception('Failed to update bot: ' . $e->getMessage());
        }
    }

    public function delete($userId, $botId)
    {
        try {
            $query = "DELETE FROM bots 
                     WHERE id = :id AND user_id = :user_id";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute([
                'id' => $botId,
                'user_id' => $userId
            ]);

            return true;
        } catch (\Exception $e) {
            throw new \Exception('Failed to delete bot: ' . $e->getMessage());
        }
    }

    public function getById($userId, $botId)
    {
        $query = "SELECT * FROM bots 
                 WHERE id = :id AND user_id = :user_id";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute([
            'id' => $botId,
            'user_id' => $userId
        ]);
        
        $bot = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($bot) {
            $bot['parameters'] = json_decode($bot['parameters'], true);
        }
        
        return $bot;
    }

    public function getActiveBots($userId)
    {
        $query = "SELECT b.*, 
                    (SELECT SUM(
                        CASE 
                            WHEN o.type = 'BUY' THEN -t.volume * t.price
                            ELSE t.volume * t.price
                        END
                    )
                    FROM trades t
                    JOIN orders o ON t.order_id = o.order_id
                    WHERE o.bot_id = b.id
                    AND t.created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)) as daily_pnl
                 FROM bots b
                 WHERE b.user_id = :user_id AND b.status = 'ACTIVE'
                 ORDER BY b.created_at DESC";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute(['user_id' => $userId]);
        
        $bots = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($bots as &$bot) {
            $bot['parameters'] = json_decode($bot['parameters'], true);
        }
        
        return $bots;
    }

    public function getBotStats($userId, $botId)
    {
        $query = "SELECT 
                    COUNT(DISTINCT t.id) as total_trades,
                    SUM(
                        CASE 
                            WHEN o.type = 'BUY' THEN -t.volume * t.price
                            ELSE t.volume * t.price
                        END
                    ) as total_pnl,
                    AVG(
                        CASE 
                            WHEN o.type = 'BUY' THEN -t.volume * t.price
                            ELSE t.volume * t.price
                        END
                    ) as avg_trade_pnl,
                    MAX(
                        CASE 
                            WHEN o.type = 'BUY' THEN -t.volume * t.price
                            ELSE t.volume * t.price
                        END
                    ) as best_trade,
                    MIN(
                        CASE 
                            WHEN o.type = 'BUY' THEN -t.volume * t.price
                            ELSE t.volume * t.price
                        END
                    ) as worst_trade
                 FROM trades t
                 JOIN orders o ON t.order_id = o.order_id
                 WHERE o.bot_id = :bot_id AND o.user_id = :user_id";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute([
            'bot_id' => $botId,
            'user_id' => $userId
        ]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getBotTrades($userId, $botId, $page = 1, $perPage = 20)
    {
        $offset = ($page - 1) * $perPage;
        
        $query = "SELECT t.*, o.pair, o.type 
                 FROM trades t
                 JOIN orders o ON t.order_id = o.order_id
                 WHERE o.bot_id = :bot_id AND o.user_id = :user_id
                 ORDER BY t.created_at DESC
                 LIMIT :offset, :per_page";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':bot_id', $botId, PDO::PARAM_INT);
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->bindValue(':per_page', $perPage, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function countBotTrades($userId, $botId)
    {
        $query = "SELECT COUNT(*) 
                 FROM trades t
                 JOIN orders o ON t.order_id = o.order_id
                 WHERE o.bot_id = :bot_id AND o.user_id = :user_id";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute([
            'bot_id' => $botId,
            'user_id' => $userId
        ]);
        
        return $stmt->fetchColumn();
    }

    public function updateStatus($userId, $botId, $status)
    {
        try {
            $query = "UPDATE bots SET 
                        status = :status,
                        updated_at = NOW()
                     WHERE id = :id AND user_id = :user_id";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute([
                'id' => $botId,
                'user_id' => $userId,
                'status' => $status
            ]);

            return true;
        } catch (\Exception $e) {
            throw new \Exception('Failed to update bot status: ' . $e->getMessage());
        }
    }

    public function getAvailableStrategies()
    {
        return [
            'GRID' => [
                'name' => 'Grid Trading',
                'description' => 'Places buy and sell orders at regular price intervals',
                'parameters' => [
                    'grid_size' => ['type' => 'number', 'label' => 'Grid Size', 'required' => true],
                    'price_range' => ['type' => 'number', 'label' => 'Price Range (%)', 'required' => true],
                    'investment' => ['type' => 'number', 'label' => 'Investment Amount', 'required' => true]
                ]
            ],
            'DCA' => [
                'name' => 'Dollar Cost Averaging',
                'description' => 'Buys at regular intervals regardless of price',
                'parameters' => [
                    'interval' => ['type' => 'select', 'label' => 'Interval', 'options' => ['1h', '4h', '1d'], 'required' => true],
                    'amount' => ['type' => 'number', 'label' => 'Amount per Interval', 'required' => true]
                ]
            ],
            'RSI' => [
                'name' => 'RSI Strategy',
                'description' => 'Trades based on RSI indicator signals',
                'parameters' => [
                    'period' => ['type' => 'number', 'label' => 'RSI Period', 'required' => true],
                    'overbought' => ['type' => 'number', 'label' => 'Overbought Level', 'required' => true],
                    'oversold' => ['type' => 'number', 'label' => 'Oversold Level', 'required' => true],
                    'amount' => ['type' => 'number', 'label' => 'Trade Amount', 'required' => true]
                ]
            ],
            'MACD' => [
                'name' => 'MACD Strategy',
                'description' => 'Trades based on MACD indicator signals',
                'parameters' => [
                    'fast_period' => ['type' => 'number', 'label' => 'Fast Period', 'required' => true],
                    'slow_period' => ['type' => 'number', 'label' => 'Slow Period', 'required' => true],
                    'signal_period' => ['type' => 'number', 'label' => 'Signal Period', 'required' => true],
                    'amount' => ['type' => 'number', 'label' => 'Trade Amount', 'required' => true]
                ]
            ]
        ];
    }
} 