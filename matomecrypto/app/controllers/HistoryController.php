<?php
class HistoryController {
    public function index() {
        global $luno_key, $luno_secret;
        if (!Auth::check()) {
            Utils::redirect('/login');
        }
        $luno = new LunoAPI($luno_key, $luno_secret);
        $wallet = new Wallet($luno);
        $history = $wallet->getHistory();
        require __DIR__ . '/../views/history/index.php';
    }
} 