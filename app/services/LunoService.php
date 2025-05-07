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

    public function __construct()
    {
        $this->apiKey = getenv('LUNO_KEY');
        $this->apiSecret = getenv('LUNO_SECRET');
        
        $this->client = new Client([
            'base_uri' => $this->baseUrl,
            'timeout' => 30,
            'headers' => [
                'User-Agent' => 'MatomeCrypto/1.0'
            ]
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
     * Private request method with authentication
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

            return json_decode($response->getBody(), true);
        } catch (GuzzleException $e) {
            throw new \Exception('Luno API Error: ' . $e->getMessage());
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

    public function getAssetBalance($asset)
    {
        $balance = $this->getBalance();
        foreach ($balance['balance'] as $item) {
            if ($item['asset'] === $asset) {
                return $item;
            }
        }
        return null;
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