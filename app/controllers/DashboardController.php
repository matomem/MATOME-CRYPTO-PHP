<?php
class DashboardController {
    public function index() {
        global $luno_key, $luno_secret;
        if (!Auth::check()) {
            Utils::redirect('/login');
        }
        $luno = new LunoAPI($luno_key, $luno_secret);
        $wallet = new Wallet($luno);
        $balances = $wallet->getBalance();
        $history = $wallet->getHistory();
        require __DIR__ . '/../views/dashboard/index.php';
    }
} 