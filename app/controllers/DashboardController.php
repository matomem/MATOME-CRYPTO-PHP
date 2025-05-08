<?php

namespace App\Controllers;

use App\Services\LunoService;
use App\Models\Trade;
use App\Models\Market;
use App\Models\Bot;

class DashboardController extends BaseController
{
    private $lunoService;
    private $tradeModel;
    private $marketModel;
    private $botModel;
    private $isProduction;

    public function __construct()
    {
        parent::__construct();
        $this->lunoService = new LunoService();
        $this->tradeModel = new Trade();
        $this->marketModel = new Market();
        $this->botModel = new Bot();
        $this->isProduction = getenv('APP_ENV') === 'production';
    }

    public function index()
    {
        if (!Auth::check()) {
            Utils::redirect('/login');
        }

        try {
            $userId = Auth::id();

            // Get detailed balance information
            $balances = $this->lunoService->getDetailedBalance();
            
            // Get recent transactions
            $transactions = $this->lunoService->getTransactionHistory(null, 0, 10);
            
            // Get pending transactions
            $pendingTransactions = $this->lunoService->getPendingTransactions();

            // Get market overview
            $marketOverview = $this->marketModel->getMarketOverview();

            // Get user's trading statistics
            $dailyPnL = $this->tradeModel->getDailyPnL($userId);
            $dailyPnLPercentage = $this->tradeModel->getDailyPnLPercentage($userId);
            $openOrders = $this->tradeModel->getOpenOrdersCount($userId);

            // Get active bots
            $activeBots = $this->botModel->getActiveBots($userId);
            $activeBotsCount = count($activeBots);

            // Sync orders in production
            if ($this->isProduction) {
                $this->tradeModel->syncOrders($userId);
            }

            // Calculate total balance in default currency
            $defaultCurrency = $this->getSetting('default_currency', 'USD');
            $totalBalance = $this->calculateTotalBalance($balances, $defaultCurrency);

            return [
                'balances' => $balances,
                'transactions' => $transactions,
                'pendingTransactions' => $pendingTransactions,
                'marketOverview' => $marketOverview,
                'dailyPnL' => $dailyPnL,
                'dailyPnLPercentage' => $dailyPnLPercentage,
                'openOrders' => $openOrders,
                'activeBots' => $activeBots,
                'activeBotsCount' => $activeBotsCount,
                'totalBalance' => $totalBalance,
                'defaultCurrency' => $defaultCurrency
            ];
        } catch (\Exception $e) {
            error_log('Dashboard Error: ' . $e->getMessage());
            return [
                'error' => 'Failed to load dashboard information. Please try again later.',
                'balances' => [],
                'transactions' => [],
                'pendingTransactions' => [],
                'marketOverview' => [],
                'dailyPnL' => 0,
                'dailyPnLPercentage' => 0,
                'openOrders' => 0,
                'activeBots' => [],
                'activeBotsCount' => 0,
                'totalBalance' => 0,
                'defaultCurrency' => 'USD'
            ];
        }
    }

    private function calculateTotalBalance($balances, $defaultCurrency)
    {
        try {
            $total = 0;
            foreach ($balances as $asset => $balance) {
                if ($asset === $defaultCurrency) {
                    $total += $balance['available'];
                } else {
                    // Get current price for the asset
                    $ticker = $this->lunoService->getTicker($asset . '_' . $defaultCurrency);
                    if (isset($ticker['last_trade'])) {
                        $total += $balance['available'] * $ticker['last_trade'];
                    }
                }
            }
            return $total;
        } catch (\Exception $e) {
            error_log('Calculate Total Balance Error: ' . $e->getMessage());
            return 0;
        }
    }

    private function getSetting($key, $default = null)
    {
        try {
            $setting = $this->systemSetting->get($key);
            return $setting ? $setting['value'] : $default;
        } catch (\Exception $e) {
            error_log('Get Setting Error: ' . $e->getMessage());
            return $default;
        }
    }
} 