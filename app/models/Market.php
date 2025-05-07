<?php

namespace App\Models;

use PDO;
use App\Services\LunoService;

class Market extends BaseModel
{
    private $lunoService;

    public function __construct()
    {
        parent::__construct();
        $this->lunoService = new LunoService();
    }

    public function getTicker($pair)
    {
        try {
            $ticker = $this->lunoService->getTicker($pair);
            
            // Cache the ticker data
            $query = "INSERT INTO market_data (
                        pair, last_price, bid, ask, 
                        volume_24h, high_24h, low_24h, 
                        timestamp, created_at
                     ) VALUES (
                        :pair, :last_price, :bid, :ask,
                        :volume_24h, :high_24h, :low_24h,
                        :timestamp, NOW()
                     )";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute([
                'pair' => $pair,
                'last_price' => $ticker['last_trade'],
                'bid' => $ticker['bid'],
                'ask' => $ticker['ask'],
                'volume_24h' => $ticker['volume'],
                'high_24h' => $ticker['high'],
                'low_24h' => $ticker['low'],
                'timestamp' => $ticker['timestamp']
            ]);

            return $ticker;
        } catch (\Exception $e) {
            throw new \Exception('Failed to get ticker: ' . $e->getMessage());
        }
    }

    public function getAllTickers()
    {
        try {
            $tickers = $this->lunoService->getAllTickers();
            
            // Cache all tickers
            $query = "INSERT INTO market_data (
                        pair, last_price, bid, ask, 
                        volume_24h, high_24h, low_24h, 
                        timestamp, created_at
                     ) VALUES (
                        :pair, :last_price, :bid, :ask,
                        :volume_24h, :high_24h, :low_24h,
                        :timestamp, NOW()
                     )";
            
            $stmt = $this->db->prepare($query);
            
            foreach ($tickers['tickers'] as $ticker) {
                $stmt->execute([
                    'pair' => $ticker['pair'],
                    'last_price' => $ticker['last_trade'],
                    'bid' => $ticker['bid'],
                    'ask' => $ticker['ask'],
                    'volume_24h' => $ticker['volume'],
                    'high_24h' => $ticker['high'],
                    'low_24h' => $ticker['low'],
                    'timestamp' => $ticker['timestamp']
                ]);
            }

            return $tickers;
        } catch (\Exception $e) {
            throw new \Exception('Failed to get all tickers: ' . $e->getMessage());
        }
    }

    public function getOrderBook($pair, $depth = 100)
    {
        try {
            $orderBook = $this->lunoService->getOrderBook($pair);
            
            // Cache order book data
            $query = "INSERT INTO order_books (
                        pair, asks, bids, timestamp, created_at
                     ) VALUES (
                        :pair, :asks, :bids, :timestamp, NOW()
                     )";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute([
                'pair' => $pair,
                'asks' => json_encode(array_slice($orderBook['asks'], 0, $depth)),
                'bids' => json_encode(array_slice($orderBook['bids'], 0, $depth)),
                'timestamp' => time() * 1000
            ]);

            return [
                'asks' => array_slice($orderBook['asks'], 0, $depth),
                'bids' => array_slice($orderBook['bids'], 0, $depth)
            ];
        } catch (\Exception $e) {
            throw new \Exception('Failed to get order book: ' . $e->getMessage());
        }
    }

    public function getTradeHistory($pair, $since = null)
    {
        try {
            $trades = $this->lunoService->getTradesHistory($pair, $since);
            
            // Cache trade history
            $query = "INSERT INTO market_trades (
                        pair, price, volume, timestamp, 
                        side, created_at
                     ) VALUES (
                        :pair, :price, :volume, :timestamp,
                        :side, NOW()
                     )";
            
            $stmt = $this->db->prepare($query);
            
            foreach ($trades['trades'] as $trade) {
                $stmt->execute([
                    'pair' => $pair,
                    'price' => $trade['price'],
                    'volume' => $trade['volume'],
                    'timestamp' => $trade['timestamp'],
                    'side' => $trade['side']
                ]);
            }

            return $trades;
        } catch (\Exception $e) {
            throw new \Exception('Failed to get trade history: ' . $e->getMessage());
        }
    }

    public function getMarketOverview()
    {
        try {
            $tickers = $this->lunoService->getAllTickers();
            $markets = $this->lunoService->getMarkets();
            
            $overview = [];
            foreach ($tickers['tickers'] as $ticker) {
                $market = array_filter($markets['markets'], function($m) use ($ticker) {
                    return $m['name'] === $ticker['pair'];
                });
                $market = reset($market);
                
                $overview[] = [
                    'pair' => $ticker['pair'],
                    'last_price' => $ticker['last_trade'],
                    'volume_24h' => $ticker['volume'],
                    'change_24h' => $this->calculate24hChange($ticker),
                    'base_currency' => $market['base_currency'],
                    'counter_currency' => $market['counter_currency']
                ];
            }

            return $overview;
        } catch (\Exception $e) {
            throw new \Exception('Failed to get market overview: ' . $e->getMessage());
        }
    }

    public function getHistoricalData($pair, $interval = '1h', $limit = 100)
    {
        try {
            $query = "SELECT 
                        DATE_FORMAT(created_at, '%Y-%m-%d %H:00:00') as time,
                        AVG(last_price) as price,
                        SUM(volume_24h) as volume
                     FROM market_data
                     WHERE pair = :pair
                     GROUP BY time
                     ORDER BY time DESC
                     LIMIT :limit";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindValue(':pair', $pair);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            
            return array_reverse($stmt->fetchAll(PDO::FETCH_ASSOC));
        } catch (\Exception $e) {
            throw new \Exception('Failed to get historical data: ' . $e->getMessage());
        }
    }

    public function getAvailablePairs()
    {
        try {
            $markets = $this->lunoService->getMarkets();
            return array_column($markets['markets'], 'name');
        } catch (\Exception $e) {
            throw new \Exception('Failed to get available pairs: ' . $e->getMessage());
        }
    }

    public function getMarketInfo($pair)
    {
        try {
            $markets = $this->lunoService->getMarkets();
            $market = array_filter($markets['markets'], function($m) use ($pair) {
                return $m['name'] === $pair;
            });
            
            if (empty($market)) {
                throw new \Exception('Market not found');
            }
            
            return reset($market);
        } catch (\Exception $e) {
            throw new \Exception('Failed to get market info: ' . $e->getMessage());
        }
    }

    private function calculate24hChange($ticker)
    {
        if (!isset($ticker['last_trade']) || !isset($ticker['high']) || !isset($ticker['low'])) {
            return 0;
        }

        $midPrice = ($ticker['high'] + $ticker['low']) / 2;
        if ($midPrice === 0) {
            return 0;
        }

        return (($ticker['last_trade'] - $midPrice) / $midPrice) * 100;
    }

    public function cleanupOldData()
    {
        try {
            // Delete market data older than 7 days
            $query = "DELETE FROM market_data 
                     WHERE created_at < DATE_SUB(NOW(), INTERVAL 7 DAY)";
            $this->db->exec($query);

            // Delete order books older than 1 day
            $query = "DELETE FROM order_books 
                     WHERE created_at < DATE_SUB(NOW(), INTERVAL 1 DAY)";
            $this->db->exec($query);

            // Delete market trades older than 30 days
            $query = "DELETE FROM market_trades 
                     WHERE created_at < DATE_SUB(NOW(), INTERVAL 30 DAY)";
            $this->db->exec($query);

            return true;
        } catch (\Exception $e) {
            throw new \Exception('Failed to cleanup old data: ' . $e->getMessage());
        }
    }
} 