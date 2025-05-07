<?php
class LunoAPI {
    private $key;
    private $secret;
    private $base = 'https://api.luno.com/api/1/';
    public function __construct($key, $secret) {
        $this->key = $key;
        $this->secret = $secret;
    }
    private function request($endpoint, $params = [], $method = 'GET') {
        $url = $this->base . $endpoint;
        $ch = curl_init();
        if ($method === 'GET' && $params) {
            $url .= '?' . http_build_query($params);
        }
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_USERPWD, $this->key . ':' . $this->secret);
        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
        }
        $result = curl_exec($ch);
        curl_close($ch);
        return json_decode($result, true);
    }
    public function getBalance() {
        return $this->request('balance');
    }
    public function getTicker($pair) {
        return $this->request('ticker', ['pair' => $pair]);
    }
    public function buy($pair, $amount) {
        return $this->request('marketorder', ['pair' => $pair, 'type' => 'BUY', 'counter_volume' => $amount], 'POST');
    }
    public function sell($pair, $amount) {
        return $this->request('marketorder', ['pair' => $pair, 'type' => 'SELL', 'base_volume' => $amount], 'POST');
    }
    public function getTransactions($asset) {
        return $this->request('transactions', ['asset' => $asset]);
    }
} 