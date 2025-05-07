<?php
$routes = [
    '' => ['DashboardController', 'index'],
    'dashboard' => ['DashboardController', 'index'],
    'wallet' => ['WalletController', 'index'],
    'wallet/deposit' => ['WalletController', 'deposit'],
    'wallet/withdraw' => ['WalletController', 'withdraw'],
    'trade' => ['TradeController', 'index'],
    'trade/buy' => ['TradeController', 'buy'],
    'trade/sell' => ['TradeController', 'sell'],
    'history' => ['HistoryController', 'index'],
    'login' => ['UserController', 'login'],
    'logout' => ['UserController', 'logout'],
    'register' => ['UserController', 'register'],
    'profile' => ['UserController', 'profile'],
    'default' => ['DashboardController', 'index'],
]; 