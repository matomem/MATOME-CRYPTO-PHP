<?php
class WalletController {
    public function index() {
        global $luno_key, $luno_secret;
        if (!Auth::check()) {
            Utils::redirect('/login');
        }
        $luno = new LunoAPI($luno_key, $luno_secret);
        $wallet = new Wallet($luno);
        $balances = $wallet->getBalance();
        require __DIR__ . '/../views/wallet/index.php';
    }
    public function deposit() {
        if (!Auth::check()) {
            Utils::redirect('/login');
        }
        require __DIR__ . '/../views/wallet/deposit.php';
    }
    public function withdraw() {
        if (!Auth::check()) {
            Utils::redirect('/login');
        }
        require __DIR__ . '/../views/wallet/withdraw.php';
    }
} 