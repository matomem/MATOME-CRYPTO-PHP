<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class LunoService
{
    private $client;
    private $apiKey;
    private $apiSecret;
    private $baseUrl = 'https://api.luno.com/api/1';
    private $isProduction;

    public function __construct()
    {
        $this->isProduction = getenv('APP_ENV') === 'production';
        $this->apiKey = getenv('LUNO_KEY');
        $this->apiSecret = getenv('LUNO_SECRET');
        
        if (empty($this->apiKey) || empty($this->apiSecret)) {
            throw new \Exception('Luno API credentials are not configured');
        }
        
        $this->client = new Client([
            'base_uri' => $this->baseUrl,
            'timeout' => 30,
            'headers' => [
                'User-Agent' => 'MatomeCrypto/1.0'
            ],
            'verify' => $this->isProduction // Enable SSL verification in production
        ]);
    }

    /**
     * Account Methods
     */
    public function getBalance()
    {
        return $this->request('GET', '/balance');
    }

    public function getTransactions($minRow = 0, $maxRow = 100)
    {
        return $this->request('GET', '/accounts/transactions', [
            'min_row' => $minRow,
            'max_row' => $maxRow
        ]);
    }

    public function getPendingTransactions()
    {
        return $this->request('GET', '/accounts/pending');
    }

    /**
     * Trading Methods
     */
    public function createOrder($pair, $type, $volume, $price = null)
    {
        $params = [
            'pair' => $pair,
            'type' => $type,
            'volume' => $volume
        ];

        if ($price !== null) {
            $params['price'] = $price;
        }

        return $this->request('POST', '/postorder', $params);
    }

    public function cancelOrder($orderId)
    {
        return $this->request('POST', '/stoporder', [
            'order_id' => $orderId
        ]);
    }

    public function getOrder($orderId)
    {
        return $this->request('GET', '/orders/' . $orderId);
    }

    public function listOrders($pair = null, $state = null, $limit = 100)
    {
        $params = ['limit' => $limit];
        
        if ($pair) {
            $params['pair'] = $pair;
        }
        
        if ($state) {
            $params['state'] = $state;
        }

        return $this->request('GET', '/listorders', $params);
    }

    public function getTrades($pair, $since = null, $limit = 100)
    {
        $params = [
            'pair' => $pair,
            'limit' => $limit
        ];

        if ($since) {
            $params['since'] = $since;
        }

        return $this->request('GET', '/trades', $params);
    }

    /**
     * Market Data Methods
     */
    public function getTicker($pair)
    {
        return $this->request('GET', '/ticker', ['pair' => $pair]);
    }

    public function getAllTickers()
    {
        return $this->request('GET', '/tickers');
    }

    public function getOrderBook($pair)
    {
        return $this->request('GET', '/orderbook', ['pair' => $pair]);
    }

    public function getTradesHistory($pair, $since = null)
    {
        $params = ['pair' => $pair];
        
        if ($since) {
            $params['since'] = $since;
        }

        return $this->request('GET', '/trades', $params);
    }

    /**
     * Funding Methods
     */
    public function getFundingAddress($asset)
    {
        return $this->request('GET', '/funding_address', ['asset' => $asset]);
    }

    public function createFundingAddress($asset)
    {
        return $this->request('POST', '/funding_address', ['asset' => $asset]);
    }

    public function getWithdrawals($asset = null)
    {
        $params = [];
        if ($asset) {
            $params['asset'] = $asset;
        }
        return $this->request('GET', '/withdrawals', $params);
    }

    public function requestWithdrawal($asset, $amount, $address, $description = null)
    {
        $params = [
            'asset' => $asset,
            'amount' => $amount,
            'address' => $address
        ];

        if ($description) {
            $params['description'] = $description;
        }

        return $this->request('POST', '/withdrawals', $params);
    }

    public function getDeposits($asset = null)
    {
        $params = [];
        if ($asset) {
            $params['asset'] = $asset;
        }
        return $this->request('GET', '/deposits', $params);
    }

    /**
     * Fee Methods
     */
    public function getFees()
    {
        return $this->request('GET', '/fee_info');
    }

    /**
     * Send Methods
     */
    public function send($asset, $amount, $address, $description = null)
    {
        $params = [
            'asset' => $asset,
            'amount' => $amount,
            'address' => $address
        ];

        if ($description) {
            $params['description'] = $description;
        }

        return $this->request('POST', '/send', $params);
    }

    /**
     * Receive Methods
     */
    public function createReceiveAddress($asset)
    {
        return $this->request('POST', '/receive_address', ['asset' => $asset]);
    }

    public function getReceiveAddresses($asset = null)
    {
        $params = [];
        if ($asset) {
            $params['asset'] = $asset;
        }
        return $this->request('GET', '/receive_addresses', $params);
    }

    /**
     * Quote Methods
     */
    public function createQuote($type, $baseAmount, $pair, $counterAmount = null)
    {
        $params = [
            'type' => $type,
            'base_amount' => $baseAmount,
            'pair' => $pair
        ];

        if ($counterAmount !== null) {
            $params['counter_amount'] = $counterAmount;
        }

        return $this->request('POST', '/quotes', $params);
    }

    public function getQuote($quoteId)
    {
        return $this->request('GET', '/quotes/' . $quoteId);
    }

    public function exerciseQuote($quoteId)
    {
        return $this->request('PUT', '/quotes/' . $quoteId . '/exercise');
    }

    public function discardQuote($quoteId)
    {
        return $this->request('PUT', '/quotes/' . $quoteId . '/discard');
    }

    /**
     * Market Info Methods
     */
    public function getMarkets()
    {
        return $this->request('GET', '/markets');
    }

    public function getOrderTypes()
    {
        return $this->request('GET', '/order_types');
    }

    /**
     * Private request method with authentication and error handling
     */
    private function request($method, $endpoint, $params = [])
    {
        try {
            $timestamp = time() * 1000;
            $params['timestamp'] = $timestamp;

            $query = http_build_query($params);
            $signature = hash_hmac('sha256', $query, $this->apiSecret);

            $response = $this->client->request($method, $endpoint, [
                'headers' => [
                    'Authorization' => 'Basic ' . base64_encode($this->apiKey . ':' . $signature)
                ],
                'query' => $params
            ]);

            $result = json_decode($response->getBody(), true);
            
            if (isset($result['error'])) {
                throw new \Exception('Luno API Error: ' . $result['error']);
            }

            return $result;
        } catch (GuzzleException $e) {
            error_log('Luno API Request Error: ' . $e->getMessage());
            throw new \Exception('Failed to communicate with Luno API. Please try again later.');
        }
    }

    /**
     * Helper Methods
     */
    public function getAvailablePairs()
    {
        $markets = $this->getMarkets();
        return array_column($markets['markets'], 'name');
    }

    /**
     * Enhanced Balance Methods
     */
    public function getDetailedBalance()
    {
        try {
            $balance = $this->request('GET', '/balance');
            $detailedBalance = [];
            
            foreach ($balance['balance'] as $asset) {
                $detailedBalance[$asset['asset']] = [
                    'balance' => $asset['balance'],
                    'reserved' => $asset['reserved'],
                    'unconfirmed' => $asset['unconfirmed'],
                    'available' => $asset['balance'] - $asset['reserved']
                ];
            }
            
            return $detailedBalance;
        } catch (\Exception $e) {
            error_log('Balance Error: ' . $e->getMessage());
            throw new \Exception('Failed to retrieve balance information');
        }
    }

    public function getAssetBalance($asset)
    {
        try {
            $balance = $this->getDetailedBalance();
            return $balance[$asset] ?? null;
        } catch (\Exception $e) {
            error_log('Asset Balance Error: ' . $e->getMessage());
            throw new \Exception('Failed to retrieve asset balance');
        }
    }

    /**
     * Enhanced Transaction Methods
     */
    public function getTransactionHistory($asset = null, $minRow = 0, $maxRow = 100)
    {
        try {
            $params = [
                'min_row' => $minRow,
                'max_row' => $maxRow
            ];
            
            if ($asset) {
                $params['asset'] = $asset;
            }
            
            return $this->request('GET', '/accounts/transactions', $params);
        } catch (\Exception $e) {
            error_log('Transaction History Error: ' . $e->getMessage());
            throw new \Exception('Failed to retrieve transaction history');
        }
    }

    public function getPendingTransactions($asset = null)
    {
        try {
            $params = [];
            if ($asset) {
                $params['asset'] = $asset;
            }
            
            return $this->request('GET', '/accounts/pending', $params);
        } catch (\Exception $e) {
            error_log('Pending Transactions Error: ' . $e->getMessage());
            throw new \Exception('Failed to retrieve pending transactions');
        }
    }

    public function createMarketOrder($pair, $type, $volume, $price = null)
    {
        try {
            if ($this->isProduction) {
                // Additional validation for production
                if ($volume <= 0) {
                    throw new \Exception('Invalid volume amount');
                }
                
                // Check if user has sufficient balance
                $balance = $this->getAssetBalance($type === 'BUY' ? explode('_', $pair)[1] : explode('_', $pair)[0]);
                if (!$balance || $balance['available'] < $volume) {
                    throw new \Exception('Insufficient balance');
                }
            }

            $params = [
                'pair' => $pair,
                'type' => strtoupper($type),
                'volume' => $volume
            ];
            
            if ($price !== null) {
                $params['price'] = $price;
            }
            
            return $this->request('POST', '/marketorder', $params);
        } catch (\Exception $e) {
            error_log('Market Order Error: ' . $e->getMessage());
            throw new \Exception('Failed to create market order: ' . $e->getMessage());
        }
    }

    public function createLimitOrder($pair, $type, $volume, $price)
    {
        try {
            if ($this->isProduction) {
                // Additional validation for production
                if ($volume <= 0 || $price <= 0) {
                    throw new \Exception('Invalid volume or price amount');
                }
                
                // Check if user has sufficient balance
                $balance = $this->getAssetBalance($type === 'BUY' ? explode('_', $pair)[1] : explode('_', $pair)[0]);
                if (!$balance || $balance['available'] < $volume) {
                    throw new \Exception('Insufficient balance');
                }
            }

            $params = [
                'pair' => $pair,
                'type' => strtoupper($type),
                'volume' => $volume,
                'price' => $price
            ];
            
            return $this->request('POST', '/postorder', $params);
        } catch (\Exception $e) {
            error_log('Limit Order Error: ' . $e->getMessage());
            throw new \Exception('Failed to create limit order: ' . $e->getMessage());
        }
    }

    public function getOrderDetails($orderId)
    {
        try {
            return $this->request('GET', '/orders/' . $orderId);
        } catch (\Exception $e) {
            error_log('Order Details Error: ' . $e->getMessage());
            throw new \Exception('Failed to retrieve order details');
        }
    }

    public function getOrderStatus($orderId)
    {
        try {
            $order = $this->getOrderDetails($orderId);
            return [
                'order_id' => $orderId,
                'status' => $order['status'],
                'filled_volume' => $order['filled_volume'] ?? 0,
                'remaining_volume' => $order['remaining_volume'] ?? 0,
                'average_price' => $order['average_price'] ?? 0,
                'creation_timestamp' => $order['creation_timestamp'],
                'expiration_timestamp' => $order['expiration_timestamp'] ?? null
            ];
        } catch (\Exception $e) {
            error_log('Order Status Error: ' . $e->getMessage());
            throw new \Exception('Failed to retrieve order status');
        }
    }

    public function getOrderBookDepth($pair, $depth = 100)
    {
        $orderBook = $this->getOrderBook($pair);
        return [
            'asks' => array_slice($orderBook['asks'], 0, $depth),
            'bids' => array_slice($orderBook['bids'], 0, $depth)
        ];
    }

    public function get24hStats($pair)
    {
        $ticker = $this->getTicker($pair);
        return [
            'last_trade' => $ticker['last_trade'],
            'high' => $ticker['high'],
            'low' => $ticker['low'],
            'volume' => $ticker['volume'],
            'timestamp' => $ticker['timestamp']
        ];
    }
} 