<?php
class TradeController {
    public function index() {
        if (!Auth::check()) {
            Utils::redirect('/login');
        }
        require __DIR__ . '/../views/trade/index.php';
    }
    public function buy() {
        global $luno_key, $luno_secret;
        if (!Auth::check()) {
            Utils::redirect('/login');
        }
        $luno = new LunoAPI($luno_key, $luno_secret);
        $trade = new Trade($luno);
        $result = null;
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $pair = $_POST['pair'] ?? 'XBTZAR';
            $amount = $_POST['amount'] ?? 0;
            $result = $trade->buy($pair, $amount);
        }
        require __DIR__ . '/../views/trade/index.php';
    }
    public function sell() {
        global $luno_key, $luno_secret;
        if (!Auth::check()) {
            Utils::redirect('/login');
        }
        $luno = new LunoAPI($luno_key, $luno_secret);
        $trade = new Trade($luno);
        $result = null;
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $pair = $_POST['pair'] ?? 'XBTZAR';
            $amount = $_POST['amount'] ?? 0;
            $result = $trade->sell($pair, $amount);
        }
        require __DIR__ . '/../views/trade/index.php';
    }
} 